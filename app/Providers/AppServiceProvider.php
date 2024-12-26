<?php

namespace App\Providers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * Allowed email validation
         */
        Validator::extend('email_with_domain', function ($attribute, $value, $parameters, $validator) {

            if (! is_string($value)) {
                return false;
            }

            // return filter_var($value, FILTER_VALIDATE_EMAIL) !== false && preg_match('/@.+?\./', $value);
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false && preg_match('/[A-Za-z0-9\._%+\-]+@[A-Za-z0-9\.\-]+\.[A-Za-z]{2,}/', $value);
        });
    }
}
