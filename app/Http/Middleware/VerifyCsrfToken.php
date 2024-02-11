<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'payfort',
        'payfort/*',
        '/*',
        'api/*',
        'api/v3/*',
        'api/v3',
        'api',
        'api/dashboard/*',
        'api/platform/*',
        'api/dashboard',
        'api/platform',
        'sub.domain.zone' => [
            'prefix/*'
        ],
        "/api/*"=> [
      "target"=> "http://localhost:8000",
      "secure"=> false,
      "logLevel"=> "debug"
    ]
    ];
}
