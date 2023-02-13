<?php

namespace Selene;

use HaydenPierce\ClassFinder\ClassFinder;
use Selene\Controllers\SeleneResourceController;
use Selene\Controllers\SeleneViewController;
use Selene\Middleware\AccessSelene;
use Selene\Middleware\AccessResource;
use Selene\Middleware\AccessView;
use Selene\Middleware\BootSelene;
use Illuminate\Support\ServiceProvider;
use Composer\ClassMapGenerator\ClassMapGenerator;
use Illuminate\Container\Foundation\CachesRoutes;
//use Symfony\Component\ClassLoader\ClassMapGenerator;

class SeleneServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            
            __DIR__ . '/Config/config.php' => config_path('selene.php'),
            __DIR__ . '/Selene' => app_path('Selene'),
            __DIR__ . '/Assets/view' => resource_path('views/selene'),
            __DIR__ . '/Assets/dist' => public_path('selene'),
            __DIR__ . '/Assets/lang' => app_path('lang'),
           
            
        ]);

        $this->registerRoutes();
    }

    public function register()
    {
        $this->app->singleton('selene', function ($app) {
            return new Selene($app);
        });
    }

    private function registerRoutes()
    {
        config(['ziggy.groups' => [
            'selene' => [
                'selene.*',
                'login',
                'register',
                'logout'
            ],
        ]]);

        if (!$this->app->routesAreCached()) {
            $mids = config('selene.middleware', []);

            $this->app['router']->group([
                'as' => 'selene.',
                'prefix' => config('selene.route_prefix', 'selene'),
                'middleware' => array_merge($mids, [AccessSelene::class, BootSelene::class,])
            ], function () {
                $this->app['router']->get('app/{vue?}', SeleneResourceController::class . '@index')->name('index')->where('vue', '[\/\w\.-]*');

                $this->app['router']->group([
                    'as' => 'resources.',
                    'prefix' => 'resources/{selene_resource}',
                    'middleware' => [AccessResource::class]
                ], function () {
                    $this->app['router']->any('type/{type}', SeleneResourceController::class . '@typeRetrieve')->name('type-retrieve');
                    $this->app['router']->get('metric/{metric}', SeleneResourceController::class . '@metric')->name('metric');

                    $this->app['router']->group(['as' => 'action.', 'prefix' => 'action/{action}'], function () {
                        $this->app['router']->get('init', SeleneResourceController::class . '@initAction')->name('init');
                        $this->app['router']->post('handle', SeleneResourceController::class . '@handleAction')->name('handle');
                    });

                    $this->app['router']->get('rearrange', SeleneResourceController::class . '@rearrange')->name('rearrange');
                    $this->app['router']->post('rearrange', SeleneResourceController::class . '@doRearrange');

                    $this->app['router']->get('paginate', SeleneResourceController::class . '@paginate')->name('paginate');
                    $this->app['router']->post('create', SeleneResourceController::class . '@create')->name('create');

                    $this->app['router']->group(['prefix' => '{selene_resource_id}'], function () {
                        $this->app['router']->get('/', SeleneResourceController::class . '@details')->name('details');
                        $this->app['router']->get('edit', SeleneResourceController::class . '@edit')->name('edit');
                        $this->app['router']->post('update', SeleneResourceController::class . '@update')->name('update');
                        $this->app['router']->post('destroy', SeleneResourceController::class . '@destroy')->name('destroy');

                        $this->app['router']->any('type/{type}', SeleneResourceController::class . '@typeAction')->name('type-action');
                    });
                });

                $this->app['router']->group([
                    'as' => 'views.',
                    'prefix' => '{selene_view}',
                    'middleware' => [AccessView::class]
                ], function () {
                    $this->app['router']->get('/', SeleneViewController::class . '@render')->name('render');
                });
            });
        }
    }
}
