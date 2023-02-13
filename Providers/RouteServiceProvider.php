<?php

namespace Typhoeus\Api\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Typhoeus\Api\Helpers\TyphoeusApiHelper as Helper;

class RouteServiceProvider extends ServiceProvider
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

			$this->loadRoutes($router);
		});
    }

    /**
	 * Loads routes
	 * @param Illuminate\Routing\Router $router
	 * @return void
	 */
	private function loadRoutes(Router $router) {

		if ($this->app->routesAreCached()) {

			$this->app->booted(function() {

				require $this->app->getCachedRoutesPath();
			});
		}
		else {

			if (file_exists($path = Helper::getWorkingPath($this) . DIRECTORY_SEPARATOR . 'routes')) {

				foreach (scandir($path) as $route) {

					if (pathinfo($route, PATHINFO_EXTENSION) == 'php') {

						$this->loadRoutesFrom($path . DIRECTORY_SEPARATOR . $route);
					}
				}
			}

			$this->app->booted(function() use ($router) {

				$router->getRoutes()->refreshNameLookups();
				$router->getRoutes()->refreshActionLookups();
			});
		}
	}
}
