<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Blueprint;

class MigrationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Blueprint::macro('commonFields', function ($softDeletes = true) {
            $this->boolean('lock')->nullable()->default(0);
            $this->boolean('status')->nullable()->default(1);
            $this->integer('created_by')->unsigned()->nullable();
            $this->integer('updated_by')->unsigned()->nullable();
            $this->integer('deleted_by')->unsigned()->nullable();
            $this->timestamps();
            $this->softDeletes();
        });
    }
}
