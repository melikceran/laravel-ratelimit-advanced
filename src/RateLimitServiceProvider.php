<?php

namespace Melikceran\RateLimit;

use Melikceran\RateLimit\Http\Middleware\RateLimit;

use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
{
    public function boot()
    {
        
        
        $router = $this->app['router'];
        $router->aliasMiddleware('ratelimit', RateLimit::class);

        $this->publishes([
            __DIR__.'/config/ratelimit.php' => config_path('ratelimit.php'),
        ]);
        
        // dd("Its worked!");
    }

    public function register()
    {
        
        $this->mergeConfigFrom(
            __DIR__.'/config/ratelimit.php', 'ratelimit'
        );
    
    }
}
