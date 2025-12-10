<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;

class ApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  mixed  $editRights
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $args = '')
    {
        // Authenticate by searching for the key, check if middleware requires edit rights and compare to key access
        $key = ApiKey::find($request->bearerToken());

        
        /* key status is as following:
         * 0 - edit
         * 1 - read only
         * 2 - euroscope
         */

        if ($key == null 
            || ($args == 'edit' && $key->status != 0)
            || ($args == 'euroscope' && $key->status != 2)) {
                
            // Exception for open routes
            if ($request->getRequestUri() == '/api/bookings' || $request->getRequestUri() == '/api/positions') {
                $request->attributes->set('unauthenticated', true);

                return $next($request);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }
        }

        // Update last used
        $key->update(['last_used_at' => now()]);

        return $next($request);
    }
}
