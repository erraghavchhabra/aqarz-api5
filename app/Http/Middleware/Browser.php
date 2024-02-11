<?php

namespace App\Http\Middleware;

use Closure;


class Browser
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */

    public function handle($request, Closure $next, $guard='Admin')
    {




        $browsers = ['Opera', 'Mozilla', 'Firefox', 'Chrome', 'Edge','PostmanRuntime'];

        $userAgent = request()->header('User-Agent');
        $ipsArray=['127.0.0.1','172.17.0.1'];
      //  dd($userAgent);
        //dd(request()->ip());

     //   dd($request->fingerprint());


        $isBrowser = false;

        foreach($browsers as $browser){
            if(strpos($userAgent, $browser) !==  false && !str_starts_with($userAgent, 'PostmanRuntime')){
              return abort(404);
                return response()->json(['Message' => 'You do not access to this api.'], 403);
                $isBrowser = true;
                break;
            }
            elseif (strpos($userAgent, $browser) !==  false &&
                str_starts_with($userAgent, 'PostmanRuntime') &&
                !in_array($request->ip(),$ipsArray)

            )
            {
                return abort(404);
                return response()->json(['Message' => 'You do not access to this api.'], 403);
                $isBrowser = true;
                break;
            }
        }



        return $next($request);
    }
}
