<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Str;

use App\Models\Company;
use App\Models\Plan;

use Stripe\Stripe;
use Carbon\Carbon;
use GuzzleHttp\Client;


class PaymentsController extends Controller
{

    public function index(Request $request)
    {

        $company = Auth::user()->company;
        $currentPlan = Auth::user()->company->plan;
        $allPlans = Plan::where('id', '!=', 1)->get();
        $subscription = null;
        $paymentMethod = null;
        $clientSecret = null;

        if($currentPlan && $currentPlan->id === 1){
            $trialEnd = new \DateTime();
            $trialEnd->modify('first day of next month');
            $trialEnd->setTime(0, 0);
            $subscription = [];
            $subscription['trial_end'] = $trialEnd->getTimestamp();
            $subscription['status'] = 'trialing';
        }
        if($company->stripe_customer_id && $company->stripe_subscription_id){
            $apiKey = env('STRIPE_API_KEY');
            $stripe = new \Stripe\StripeClient([
                'api_key' => $apiKey,
                'stripe_version' => '2022-08-01',
            ]);
            $subscription = $stripe->subscriptions->retrieve($company->stripe_subscription_id, ['expand' => ['latest_invoice.payment_intent', 'pending_setup_intent']]);
            if($subscription->default_payment_method){
                //get credit card info to display in ui
                $paymentMethod = $stripe->customers->retrievePaymentMethod(
                    $company->stripe_customer_id,
                    $subscription->default_payment_method,
                    []
                );
            }else if($subscription->collection_method === 'send_invoice'){
                //bank transfer
                $paymentMethod = 'bank-transfer';
            }else{
                //does not have payment method, getting client secret for collection cc details
                $clientSecret = $subscription->pending_setup_intent !== null
                ? $subscription->pending_setup_intent->client_secret
                : ($subscription->latest_invoice !== null && $subscription->latest_invoice->payment_intent !== null
                    ? $subscription->latest_invoice->payment_intent->client_secret
                    : null);
            }
        }


        return response()->json([
            'currentPlan' => $currentPlan,
            'allPlans' => $allPlans,
            'paymentMethod' => $paymentMethod,
            'currentSubscription' => $subscription,
            'deleted_at' => $company->deleted_at,
            'clientSecret' => $clientSecret
        ]);
    }


    public function createSubscription(Request $request){
        $apiKey = env('STRIPE_API_KEY');
        $stripe = new \Stripe\StripeClient([
           'api_key' => $apiKey,
           'stripe_version' => '2022-08-01',
        ]);

        $company = Auth::user()->company;
        $plan = Plan::find($request->planId);

        $stripePriceIds = json_decode($plan->stripe_price_ids, true);
        $gpt4PriceId = $stripePriceIds['gpt-4'];
        $gpt3PriceId = $stripePriceIds['gpt-3.5-turbo'];
        $planPriceId = $stripePriceIds['base_plan'];

        $clientSecret = null;
        $paymentMethod = $request->paymentMethod; // 'credit-card' or 'bank-transfer'

        if(!$company->stripe_customer_id){
            $customer = $stripe->customers->create([
                'email' => $company->accounting_email ? $company->accounting_email : Auth::user()->email,
                'name' => $company->name,
                'metadata' => ['company_id' => $company->id]
            ]);
            $company->stripe_customer_id = $customer->id;
            $company->save();
        }

        if($company->stripe_subscription_id){
            //get existing subscription
            $subscription = $stripe->subscriptions->retrieve($company->stripe_subscription_id, ['expand' => ['latest_invoice.payment_intent','pending_setup_intent']]);

            //check if plan needs to be updated
            if($subscription->items->data[0]->price->id !== $planPriceId){
                //update subscription
                $subscriptionItemId = $subscription->items->data[0]->id;
                if (!empty($planPriceId)) {
                    $stripe->subscriptionItems->update(
                        $subscriptionItemId,
                        ['price'=> $planPriceId]
                    );
                }
                $subscription = $stripe->subscriptions->retrieve($company->stripe_subscription_id, ['expand' => ['latest_invoice.payment_intent','pending_setup_intent']]);
            }

            //check if payment method needs to be updated
            if($subscription->collection_method === 'send_invoice' && $paymentMethod === 'credit-card'){
                //switching from bank transfer to credit card
                $subscriptionPaymentDetails = [
                    'collection_method' => 'charge_automatically',
                    'payment_behavior' => 'default_incomplete',
                    'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
                ];
                $stripe->subscriptions->update($company->stripe_subscription_id, $subscriptionPaymentDetails);

            }else if($subscription->collection_method !== 'send_invoice' && $paymentMethod === 'bank-transfer'){
                //switching from credit card to bank transfer
                $subscriptionPaymentDetails = [
                    'collection_method' => 'send_invoice',
                    'days_until_due' => 30,
                    'default_payment_method' => null,
                ];
                $stripe->subscriptions->update($company->stripe_subscription_id, $subscriptionPaymentDetails);
            }
        }else {
            //create new subscription
            $startOfNextMonth = new \DateTime();
            $startOfNextMonth->modify('first day of next month');
            $startOfNextMonth->setTime(0, 0);
            $subDetails = [
                'customer' => $company->stripe_customer_id,
                'items' => [
                    ['price' => $planPriceId],
                    ['price' => $gpt4PriceId],
                    ['price' => $gpt3PriceId]
                ],
                "billing_cycle_anchor" => $startOfNextMonth->getTimestamp()
            ];
            if($paymentMethod === 'credit-card'){
                $subDetails['collection_method'] = 'charge_automatically';
                $subDetails['payment_behavior'] = 'default_incomplete';
                $subDetails['payment_settings'] = ['save_default_payment_method' => 'on_subscription'];
            }else if($paymentMethod === 'bank-transfer'){
                $subDetails['collection_method'] = 'send_invoice';
                $subDetails['days_until_due'] = 30;
            }else{
                error_log('Payment method not specified');
            }

            if($company->plan_id === 1){
                //if they are currently on a free trial keep them on free trial till the end of the month
                $subDetails['trial_end'] = $startOfNextMonth->getTimestamp();
                $subDetails['expand'] = [ 'pending_setup_intent'];
            }else{
                $subDetails['expand'] = ['latest_invoice.payment_intent', 'pending_setup_intent'];
            }
            $subscription = $stripe->subscriptions->create($subDetails);
        }


        $company->stripe_subscription_id = $subscription->id;
        $company->stripe_subscription_status = $subscription->status;
        $company->deleted_at = null;
        $company->paused_at = null;
        if($paymentMethod === 'bank-transfer'){
            //save plan id when subscription is created for bank transfers since there is no webhook event when they add cc details
            $company->plan_id = $request->planId;
            //save payment method if it is bank transfer, if its a cc we save it when setup intent is completed, so when they have successfully added a cc.
            $company->payment_method = $paymentMethod;
        }
        $company->save();

        if($paymentMethod !== 'bank-transfer' && !$subscription->paymentMethod ){
            //getting client secret for collection cc details
            $clientSecret = $subscription->pending_setup_intent !== null
            ? $subscription->pending_setup_intent->client_secret
            : ($subscription->latest_invoice !== null && $subscription->latest_invoice->payment_intent !== null
                ? $subscription->latest_invoice->payment_intent->client_secret
                : null);
        }

        return response()->json(
            [
                'subscriptionId' => $subscription->id,
                'clientSecret' => $clientSecret
            ]
        );

    }

    public function resetPaymentMethod(Request $request){
        //called when back button is clicked on payment method page
        //needs to either set payment method on stripe subscription back to bank-transfer if that had previously been selected
        //or reset the stripe subscription if there is no payment method in the db (means they never actually added a payment method so we can just cancel the subscription)

        $apiKey = env('STRIPE_API_KEY');
        $stripe = new \Stripe\StripeClient([
           'api_key' => $apiKey,
           'stripe_version' => '2022-08-01',
        ]);

        $company = Auth::user()->company;
        $paymentMethod = $company->payment_method;
        if(!$paymentMethod){
            //they actually have never completed signup so we can remove the subscription
            $subscriptionId = $company->stripe_subscription_id;
            
            //remove subscriptionId from db first so we don't get a webhook event
            $company->stripe_subscription_id = null;
            $company->save();

            //cancel subscription
            $stripe->subscriptions->cancel($subscriptionId);
        } else if($paymentMethod === 'bank-transfer'){
            //set payment method on stripe subscription back to bank-transfer
            $stripe->subscriptions->update($company->stripe_subscription_id, [
                'collection_method' => 'send_invoice',
                'days_until_due' => 30,
                'default_payment_method' => null,
            ]);
        } else {
            //should never happen.... 
            error_log('Trying to reset payment method when it had never been set');
        }
    }

    public function createSetupIntent(Request $request){
        $apiKey = env('STRIPE_API_KEY');
        $stripe = new \Stripe\StripeClient([
           'api_key' => $apiKey,
           'stripe_version' => '2022-08-01',
        ]);

        $company = Auth::user()->company;
        $plan = Plan::find($request->planId);
        $clientSecret = null;
        $subscription = $stripe->subscriptions->retrieve($company->stripe_subscription_id);
        if($subscription->default_payment_method){
            $setupIntent = $stripe->setupIntents->create([
                'customer' => $company->stripe_customer_id,
                'payment_method' => $subscription->default_payment_method
            ]);
            $company->deleted_at = null;
            $company->paused_at = null;
            $company->save();
        }


        return response()->json(
            [
                'clientSecret' => $setupIntent->client_secret
            ]
        );

    }

    public function cancelSubscription(Request $request){
        $apiKey = env('STRIPE_API_KEY');
        $stripe = new \Stripe\StripeClient([
            'api_key' => $apiKey,
            'stripe_version' => '2022-08-01',
        ]);

        $company = Auth::user()->company;
        $stripe->subscriptions->cancel($company->stripe_subscription_id);

        if($request->permanentlyDelete){
            $company->deleted_at = Carbon::now();
            $company->save();
        }else{
            $company->paused_at = Carbon::now();
            $company->save();
        }

        return response()->json(['status' => 'success']);
    }

    public function updateSubscription($subscription){
        //called from webhook will update the status and plan
        $company = Company::where('stripe_subscription_id', $subscription->id)->first();
        $company->stripe_subscription_status = $subscription->status;
        if($subscription->status === 'active' || $subscription->status === 'trialing'){
            $priceId = $subscription->items->data[0]->price->id;
            $plan = Plan::where('stripe_price_ids->base_plan', $priceId)->first();
            $company->plan_id = $plan->id;
        }
        $company->save();
    }

    public function updatePaymentMethod($object){
        //called from stipe wehbook when a user adds a new credit card
        $company = Company::where('stripe_customer_id', $object->customer)->first();
        $apiKey = env('STRIPE_API_KEY');
        $stripe = new \Stripe\StripeClient([
            'api_key' => $apiKey,
            'stripe_version' => '2022-08-01',
        ]);

        $stripe->subscriptions->update($company->stripe_subscription_id, [
            "default_payment_method" => $object->payment_method
        ] );

        $company->payment_method = 'credit-card';
        $company->save();
    }

    public function handleStripeWebhook(Request $request){
        $apiKey = env('STRIPE_API_KEY');
        $stripe = new \Stripe\StripeClient([
            'api_key' => $apiKey,
            'stripe_version' => '2022-08-01',
        ]);

        // Parse the message body (and check the signature if possible)
        $webhookSecret = env('STRIPE_WEBHOOK_SECRET');
        try {
            $event = \Stripe\Webhook::constructEvent(
                $request->getContent(),
                $request->server('HTTP_STRIPE_SIGNATURE'),
                $webhookSecret
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
        $type = $event['type'];
        $object = $event['data']['object'];
        // Handle the event
        switch ($type) {
            case 'customer.subscription.deleted':
                error_log('🔔  Webhook received! ' . $type);
                $company = Company::where('stripe_subscription_id', $object->id)->first();
                //check if there is a company with this subscription id, 
                //if not then this could have been they selected a plan and then clicked the back button
                if($company){
                    $company->stripe_subscription_id = null;
                    $company->stripe_subscription_status = 'canceled';
                    $company->plan_id = null;
                    $company->save();
                }
                break;
            case 'customer.subscription.updated':
                error_log('🔔  Webhook received! ' . $type);
                $this->updateSubscription($object);
                break;
            case 'setup_intent.succeeded':
                error_log('🔔  Webhook received! ' . $type);
                $this->updatePaymentMethod($object);
                break;
            // ... handle other event types
            default:
            // Unhandled event type

        }

        return response()->json(['status' => 'success']);
    }

    public function unsubscribe(Request $request){
        if (Auth::user()->role === 2) {
            $company = Auth::user()->company;
            $company->unsubscribed_at = Carbon::now();
            $company->save();

            // Slack通知用の設定があれば送信
            if (env('SLACK_WEBHOOK_UNSUBSCRIBED_URL')) {
                $client = new Client();
                $response = $client->post(env('SLACK_WEBHOOK_UNSUBSCRIBED_URL'), [
                    'json' => [
                        'text' =>
                            '法人：' . Auth::user()->company->name . "\n" .
                            "から退会申請がありました。\n"
                    ]
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }
}
