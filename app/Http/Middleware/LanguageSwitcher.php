<?php

namespace App\Http\Middleware;

use App\User;
use Closure;

class LanguageSwitcher
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
        // Check header request and determine localizaton
        $local='';

          //  $local = ($request->hasHeader('Accept-Language')) ? $request->header('Accept-Language') : 'ar';

        $local = (app('request')->hasHeader('Accept-Language')) ? app('request')->header('Accept-Language') : 'ar';


        // set laravel localization
        app()->setLocale($local);

        //if user is logged
        if (auth()->check()) {
            $user = User::find(auth()->user()->id);
            $user->update(['last_active' => now()]);
        }

        // continue request
        return $next($request);
    }
}
