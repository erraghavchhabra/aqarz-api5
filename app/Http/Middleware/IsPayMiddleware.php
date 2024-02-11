<?php

namespace App\Http\Middleware;

use Closure;



class IsPayMiddleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */

    public function handle($request, Closure $next, $guard = 'api')
    {




        //  session()->flush();
        if (!auth()->guard('web')->check()) {

            // return response()->fail("not_authorized", []);
            return response()->error("not authorized");
        }

        if (auth()->guard('web')->check()) {

            $user = auth()->guard('web')->user();
            $fdate =$user->created_at ;
            $tdate =date('Y-m-d H:i:s') ;




            $to = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $fdate);
            $from = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $tdate);

            $diff_in_days = $to->diffInDays($from);



            if ($diff_in_days > 10)
            {
                if ($user->is_pay != 1) {
                    return response()->error("not authorized");
                }


            }

            if ($user->type != 'provider') {
                return response()->error("not authorized");
            }
            // return response()->fail("not_authorized", []);
            // return response()->error("not authorized");
        }


        return $next($request);
    }
}
