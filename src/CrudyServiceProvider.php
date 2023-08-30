<?php

namespace devsi\Crudy;

use Illuminate\Support\ServiceProvider;
use devsi\Crudy\Console\MakeApiCrud;
use devsi\Crudy\Console\MakeCommentable;
use devsi\Crudy\Console\MakeCrud;
use devsi\Crudy\Console\MakeService;
use devsi\Crudy\Console\MakeViews;
use devsi\Crudy\Console\RemoveApiCrud;
use devsi\Crudy\Console\RemoveCommentable;
use devsi\Crudy\Console\RemoveCrud;
use devsi\Crudy\Console\RemoveService;

class CrudyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //publish config file
        $this->publishes([__DIR__ . '/../config/Crudy.php' => config_path('Crudy.php')]);

        //default-theme
        $this->publishes([__DIR__ . '/stubs/default-theme/' => resource_path('Crudy/views/default-theme/')]);

        //default-layout
        $this->publishes([__DIR__ . '/stubs/default-layout.stub' => resource_path('views/default.blade.php')]);

        //and commentable stub
        $this->publishes([
            __DIR__ . '/stubs/commentable/views/comment-block.stub' => resource_path('Crudy/commentable/comment-block.stub')
        ], 'commentable-stub');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/Crudy.php', 'Crudy');

        $this->commands(
            MakeCrud::class,
            MakeViews::class,
            RemoveCrud::class,
            MakeApiCrud::class,
            RemoveApiCrud::class,
            MakeCommentable::class,
            RemoveCommentable::class,
            MakeService::class,
            RemoveService::class
        );
    }
}
