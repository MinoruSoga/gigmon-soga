<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckIpAddress
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();

        if($user) {
            $ips = $user->company->ipAddresses;
            if (!$ips->isEmpty()) {
                $allowedIps = $ips->pluck('ip_address')->toArray();
                if(!in_array($this->getIp($request), $allowedIps)) {
                    // IPアドレスが許可リストにない場合はログアウト
                    auth()->logout();
                    return redirect('/login')->withErrors(['ip' => '許可されていないIPアドレスからのアクセスです。']);
                }
            }
        }

        return $next($request);
    }

    public function getIp($request)
    {
        $xForwardedFor = $request->header('X-Forwarded-For');
        if ($xForwardedFor) {
            $ips = explode(',', $xForwardedFor);
            $clientIp = $ips[0];
            return $clientIp;
        }

        return $request->ip();
    }

}
