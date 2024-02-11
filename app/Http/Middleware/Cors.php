<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        return $next($request)
            ->header('Access-Control-Allow-Origin', 'http://localhost:3000')
            ->header('Access-Control-Allow-Origin', 'http://localhost:3333')
            ->header('Access-Control-Allow-Origin', 'http://127.0.0.1:8000')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->header('Access-Control-Allow-Origin', 'https://dashboardbeta.aqarz.sa')
            ->header('Access-Control-Allow-Origin', 'https://platformbeta.aqarz.sa')
            ->header('Access-Control-Allow-Origin', 'https://beta.aqarz.sa')
            ->header('Access-Control-Allow-Origin', 'https://platform2.aqarz.sa')
            ->header('Access-Control-Allow-Origin', 'https://api2.aqarz.sa')
            //      ->header('Access-Control-Allow-Origin', '*,*')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', '*')
            ->header('Access-Control-Allow-Headers', 'type, role ,Access-Control-Allow-Origin, Referer , v , Content-Type, Access-Control-Allow-Headers, Authorization,Origin, X-Requested-With, Accept , auth');


           // ->header('Content-Type: multipart/form-data');

    }
}
