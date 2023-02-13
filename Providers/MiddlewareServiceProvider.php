<?php

namespace Typhoeus\Api\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Typhoeus\Api\Helpers\TyphoeusApiHelper as Helper;

class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        // Boots api package after everything booted
		$this->app->booted(function() use ($router) {

			$this->loadMiddleware($router);
		});
    }

    /**
	 * Loads all middleware
	 * @param Illuminate\Routing\Router $router
	 * @return void
	 */
	private function loadMiddleware(Router $router) {

		$methodTypes = [
			'route'     =>  'aliasMiddleware',
			'groups'    =>  'middlewareGroup'
		];

		// Gets custom middleware to register from "config/middleware.php"
		$middlewares = config(Helper::getPackageName() . '::middleware');

		if ($middlewares) {

			foreach ($methodTypes as $type => $method) {

				if (isset($middlewares[$type]) && !empty($middleware = $middlewares[$type])) {

					foreach ($middleware as $alias => $class) {

						$router->$method($alias, $class);
					}
				}
			}
		}
	}
}
