<?php

namespace App\Http\Middleware;

use Closure;


class Admin
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */

    public function handle($request, Closure $next, $guard='Admin')
    {









        //  session()->flush();
        if(!auth()->guard('Admin')->check()){

           // return response()->fail("not_authorized", []);
            return response()->error("not authorized" ,null ,  401);
        }


        return $next($request);
    }
}
