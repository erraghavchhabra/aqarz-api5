<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{





    public function __construct(Request $request)
    {
        // $request->merge(request_only(['Auth-Role'], getallheaders()));
        $this->request = $request;






        \Validator::extend('check_password', function ($attribute, $value, $parameters, $validator) {
            return app('hash')->check($value, current($parameters));
        }, 'The :attribute does not match.');

       // $this->preaperConstants();
    }


    private function preaperConstants()
    {
        $route = '';
        if (isset(app('request')->route()[1]['as']))
            $route = app('request')->route()[1]['as'];
        define('CURRENT_ROUTE', $route);
        // define('REQUEST_PARAMS', $this->request->except('photo', 'photos'));
        define('REQUEST_HEADER', app('request')->header());
        define('REQUEST', app('request'));
        // define('REQUEST_PARAMS',$this->request->all());
        //   define('REQUEST_PARAMS',$this->request->all());
    }

    public function image_extensions()
    {

        return array('jpg', 'png', 'jpeg', 'gif', 'bmp');

    }
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
