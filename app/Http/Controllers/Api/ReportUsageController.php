<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Str;

use Stripe\Stripe;

use App\Models\Company;
use App\Models\Plan;

class ReportUsageController extends Controller
{
    public function index(Request $request)
    {

        // Get STRIPE API's key from env
        $apiKey = env('STRIPE_API_KEY');
        $stripe = new \Stripe\StripeClient([
            'api_key' => $apiKey,
            'stripe_version' => '2022-08-01',
        ]);

        $companies = Company::whereNotNull('stripe_subscription_id')->get();

        foreach ($companies as $company) {
            $wordCountGpt4 = $company->getThisMonthsCharacterCount('gpt-4');
            $wordCountGpt3 = $company->getThisMonthsCharacterCount('gpt-3.5-turbo');
            
            $stripePriceIds = json_decode($company->plan->stripe_price_ids, true);
            $gpt4PriceId = $stripePriceIds['gpt-4'];
            $gpt3PriceId = $stripePriceIds['gpt-3.5-turbo'];
            $planPriceId = $stripePriceIds['base_plan'];

            $subscription = $stripe->subscriptions->retrieve($company->stripe_subscription_id);
            $gpt4_subscription_item_id = $this->getSubscriptionItemId($subscription,  $gpt4PriceId);
            $gpt3_subscription_item_id = $this->getSubscriptionItemId($subscription,  $gpt3PriceId);
            $fixed_subscription_item_id = $this->getSubscriptionItemId($subscription,  $planPriceId);

            $action = 'set';
            $date = date_create();
            $timestamp = date_timestamp_get($date);
            
            if($gpt4_subscription_item_id){
                $stripe->subscriptionItems->createUsageRecord(
                    $gpt4_subscription_item_id,
                    [
                        'quantity' => $wordCountGpt4,
                        'timestamp' => $timestamp,
                        'action' => $action,
                    ],
                );
            }
            if($gpt3_subscription_item_id){
                $stripe->subscriptionItems->createUsageRecord(
                    $gpt3_subscription_item_id,
                    [
                        'quantity' => $wordCountGpt3,
                        'timestamp' => $timestamp,
                        'action' => $action,
                    ],
                );
            }
            if($fixed_subscription_item_id){
                $stripe->subscriptionItems->createUsageRecord(
                    $fixed_subscription_item_id,
                    [
                        'quantity' => 1,
                        'timestamp' => $timestamp,
                        'action' => $action,
                    ],
                );
            }
        }
    }

    function getSubscriptionItemId($subscription, $priceId) {
        // Retrieve the line items from the subscription object
        $lineItems = $subscription->items->data;
      
        // Iterate over each line item to find the matching price ID
        foreach ($lineItems as $lineItem) {
            if ($lineItem->price->id === $priceId) {
                return $lineItem->id;
            }
        }
      
        // If no matching price ID is found, return null or an appropriate value
        return null;
    }

}
