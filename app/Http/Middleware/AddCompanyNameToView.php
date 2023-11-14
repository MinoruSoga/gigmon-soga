<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class AddCompanyNameToView
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $companyName = Auth::user()->company->name ?? "";
            View::share('companyName', $companyName);
        }

        return $next($request);
    }
}
