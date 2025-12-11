<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode as Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CheckForMaintenanceMode extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array
     */
    protected $except = [
        'api/*',
        'login',
        '/',
        'validate',
        'logout',
        'images/*'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle($request, Closure $next){

        if(!$this->app->isDownForMaintenance()){
            return $next($request);
        }

        if($this->inExceptArray($request)){
            return $next($request);
        }

        $response = $next($request);

        if(Auth::check() && in_array(Auth::id(), config('maintenance.allowed_users'))){
            return $response;
        } else if(!Auth::check()){
            return redirect(route('login'));
        }
        
        throw new HttpException(401);
    }
}