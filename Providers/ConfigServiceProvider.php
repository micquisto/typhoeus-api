<?php

namespace Typhoeus\Api\Providers;

use Illuminate\Support\ServiceProvider;
use Typhoeus\Api\Helpers\TyphoeusApiHelper as Helper;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->loadConfigs();
    }

    /**
     * Loads api configuration
     * @return void
     */
    private function loadConfigs() {

        $packageName = Helper::getPackageName();
        $workingPath = Helper::getWorkingPath($this) . DIRECTORY_SEPARATOR . 'config';

        foreach (scandir($workingPath) as $config) {

            if (pathinfo($config, PATHINFO_EXTENSION) == 'php') {

                $name = pathinfo($config, PATHINFO_FILENAME);
                $configName = "{$packageName}::{$name}";

                // Sets the template path if overridden in the template, and sets the working path if not overridden
                $path = Helper::getTemplatePath('config', $config) ?? $workingPath;

                $this->mergeConfigFrom($path . DIRECTORY_SEPARATOR . $config, $configName);

                // Insert api configs to existing configs if available
                if ($configValue = config($name)) {

                    config([$name => array_replace_recursive($configValue, config($configName))]);
                }
            }
        }
    }
}
