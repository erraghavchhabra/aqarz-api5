<?php
namespace App\Providers;

use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class ResponseMacroServiceProvider extends ServiceProvider
{
    public function boot()
    {
        ResponseFactory::macro('success', function ($message, $data=null) {
            return Response::json([
                'status'  => true,
                'code'    => 200,
                'message' => $message,
                'data'    => $data,
            ]);
        });

        ResponseFactory::macro('error', function ($message, $data = null, $status = 400) {
            return Response::json([
                'status'  => false,
                'code'    => $status,
                'message' => $message,
                'data'    => $data,
            ], $status);
        });
    }
}
