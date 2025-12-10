<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Response;

use App\Models\BlockedUser;

class BlockedUsers
{

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

        if(Auth::check()){
            $b = BlockedUser::where(['blocked_user_id'=>Auth::id()])->first();
            
            if($b){
                return response()->view('blockedview', ['full'=>true,'block'=>$b]);
            }   
        }
     
        return $next($request);
    }
}