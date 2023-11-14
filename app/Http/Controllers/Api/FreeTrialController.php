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

class FreeTrialController extends Controller
{
    public function index(Request $request)
    {   

        $companies = $companies = Company::where('plan_id', 1)->get();

        foreach ($companies as $company) {
            $company->plan_id = null;
            $company->stripe_subscription_status = 'canceled';
            $company->save();
        }
    }

}
