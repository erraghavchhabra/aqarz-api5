<?php

namespace App\Providers;


use App\Elastic\Elastic;
use App\Models\v2\City;
use App\Models\v3\Estate;
use Elasticsearch\ClientBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal()) {
            // $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            // $this->app->register(TelescopeServiceProvider::class);
        }


    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }
}
