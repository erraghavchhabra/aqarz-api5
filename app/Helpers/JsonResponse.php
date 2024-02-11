<?php
/**
 * Created by PhpStorm.
 * User: Logan
 * Date: 2018-05-31
 * Time: 9:07 PM
 */

namespace App\Helpers;


use Illuminate\Support\Facades\Response;

class JsonResponse
{

    static function success($result = [], $message = "Success")
    {
        return Response::json([
            "status" => false,
            "message" => $message,
            "result" => $result,
            'errors' => null
        ]);
    }

    static function fail($message, $code = 400)
    {
        return Response::json([
            "status" => false,
            "message" => $message,
            'errors' => null,

        ], $code);
    }

    static function errors($errors)
    {
        return Response::json([
            'error' => true,
            'message' => 'Multiple errors',
            'errors' => $errors
        ]);
    }


}
