<?php

namespace App\Http\Middleware;

use Closure;


class Fund
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */

    public function handle($request, Closure $next, $guard='Fund')
    {









        //  session()->flush();
        if(!auth()->guard('Fund')->check()){

           // return response()->fail("not_authorized", []);
            return response()->error("not authorized");
        }


        return $next($request);
    }
}
