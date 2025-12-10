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

    private $allowedUsers = [
        1406129,
        1436181,
        1190497,
        10000000,
        10000001,
        10000002,
        10000003,
        10000004,
        10000005,
        10000006,
        10000007,
        10000008,
        10000009,
        10000010,

        //public checkers that requested
        /*811107,
        1326976,
        1656093,
        1083172,
        1716755,
        1761767,
        1761905,
        1443199,
        1226744,
        1693164,
        1588086,*/
        
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

        if(Auth::check() && in_array(Auth::id(), $this->allowedUsers)){
            return $response;
        } else if(!Auth::check()){
            return redirect(route('login'));
        }
        
        throw new HttpException(401);
    }
}