<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function mypage()
    {
        return view('mypage', ['user' => Auth::user()]);
    }

    public function mypageUpdate(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'password' => 'nullable|string|min:8|confirmed',
            'password_confirmation' => 'nullable|same:password',
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

    #    if($user->email !== $request->email){
    #        $user->email_verified_at = null;
    #        $user->email = $request->email;
    #        $user->save();
    #        $user->sendEmailVerificationNotification();
    #    } else{
            $user->save();
    #    }

        return redirect()->route('mypage')->with('success', __('mypage.Profile updated successfully'));
    }

    public function getPlanId()
{
    $company = Auth::user()->company;
    
    if($company && $company->plan_id) {
        return response()->json(['plan_id' => $company->plan_id]);
    }

    return response()->json(['message' => 'No plan found for this user'], 404);
    }
}
