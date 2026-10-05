<?php

namespace App\Providers;

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
        if (class_exists(\Illuminate\Foundation\Console\ServeCommand::class)) {
            \Illuminate\Foundation\Console\ServeCommand::$passthroughVariables = array_merge(
                \Illuminate\Foundation\Console\ServeCommand::$passthroughVariables,
                [
                    'SystemRoot',
                    'WINDIR',
                    'LOCALAPPDATA',
                    'APPDATA',
                    'TEMP',
                    'TMP',
                    'ComSpec',
                    'OS',
                    'PATHEXT',
                    'SYSTEMDRIVE',
                    'ALLUSERSPROFILE',
                    'PROGRAMDATA',
                ]
            );
        }
    }
}
