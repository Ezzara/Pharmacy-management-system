<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;

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
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /*
        DB::listen(function ($query) {
            Log::info("SQL Query: " . $query->sql);
            Log::info("Execution Time: " . $query->time);
        });
        */
        if (App::environment('local')) {
            DB::listen(function ($query) {
                //Log::channel('querylog')->info(
                //    "Query: {$query->sql}, Values: " . implode(',', $query->bindings) . ", Time: {$query->time}ms"
                //);

                if (strpos($query->sql, 'categories') !== false) {
                    Log::channel('querylog')->info(
                        "Query: {$query->sql}, Values: " . implode(',', $query->bindings) . ", Time: {$query->time}ms"
                    );
                }
            });

            
        }
        
    }
}
