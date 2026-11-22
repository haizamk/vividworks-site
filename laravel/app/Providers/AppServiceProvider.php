<?php

namespace App\Providers;

use App\Support\DesignAgent\DesignAgentClient;
use App\Support\DesignAgent\DesignInvokeStub;
use App\Support\DesignAgent\HttpDesignAgentClient;
use App\Support\DesignAgent\UnknownDesignInvokeStub;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DesignAgentClient::class, HttpDesignAgentClient::class);
        $this->app->bind(DesignInvokeStub::class, UnknownDesignInvokeStub::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
