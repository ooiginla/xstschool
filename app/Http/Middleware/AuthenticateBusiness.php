<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\InternalAppException;
use App\Models\Business;
use App\Exceptions\ErrorCode; 
use App\Models\ServiceConfiguration;

class AuthenticateBusiness
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        $business = Business::where('api_key', $token)->first();

        // Business Exists?
        if(empty($business)){
            throw new InternalAppException(ErrorCode::UNAUTHENTICATED_BUSINESS);
        }

        // Key is valid?
        if($token !== $business->api_key){
            throw new InternalAppException(ErrorCode::INVALID_AUTH_KEY);
        }

        // Business not disabled?
        if(! $business->status){
            throw new InternalAppException(ErrorCode::DISABLED_BUSINESS_ACCOUNT);
        }

        // Production?  Check Live Enabled
        if(config('app.env') == 'production') {
            if(! $business->live_enabled){
                throw new InternalAppException(ErrorCode::GOLIVE_NOT_ENABLED);
            }
        }

        // IP check

               
        $request->merge(['business' => $business]);

        return $next($request);
    }

    
}
