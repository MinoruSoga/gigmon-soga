<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Notifications\TwoFactorCode;

class TwoFactorController extends Controller
{
    public function index()
    {
        return view('auth.twoFactor');
    }

    public function store(Request $request)
    {
        $request->validate([
            'two_factor_code' => 'integer|required',
        ]);

        $user = auth()->user();

        if($user->two_factor_expires_at->lt(now())){
            $user->resetTwoFactorCode();
            auth()->logout();

            return redirect()->route('login')
                ->withErrors(__('The two factor code has expired. Please login again.'));
        }

        if($request->input('two_factor_code') == $user->two_factor_code)
        {
            $user->resetTwoFactorCode();

            return redirect()->route('home');
        }

        return redirect()->back()
            ->withErrors(['two_factor_code' =>
                __('The two factor code you have entered does not match')]);
    }

    public function resend()
    {
        $user = auth()->user();
        $user->generateTwoFactorCode();
        $user->notify(new TwoFactorCode());

        return redirect()->back()->withMessage(__('The two factor code has been sent again'));
    }
}