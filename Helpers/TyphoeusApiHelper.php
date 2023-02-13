<?php

/**
 * Product Management Helper
 */

namespace Typhoeus\Api\Helpers;

class TyphoeusApiHelper {

    /**
     * @var string $packageName
     */
    private static $packageName = 'api';

    /**
     * Gets the package name
     * @return string
     */
    public static function getPackageName() {

        return self::$packageName;
    }

    /**
     * Gets the working path of a certain package based on the object passed and used either from workbench or vendor
     * @param object $object
     * @return string
     */
    public static function getWorkingPath($object) {

        $path = (new \ReflectionClass(get_class($object)))->getFileName();
        $path = str_replace('vendor', 'workbench', $path);

        $localWorkbenchFolder = base_path() . DIRECTORY_SEPARATOR . 'workbench';

        $isWorkbench = is_dir($localWorkbenchFolder) && file_exists($path);

        $path = base_path() . DIRECTORY_SEPARATOR . ($isWorkbench ? 'workbench' : 'vendor') . DIRECTORY_SEPARATOR . 'typhoeus'. DIRECTORY_SEPARATOR . self::getPackageName();

        return $path;
    }

    /**
     * Gets the template path if the file is being overridden in the template
     * @param string $dir
     * @param string $filename
     * @return string|null
     */
    public static function getTemplatePath($dir, $filename) {

        $path = app_path() . DIRECTORY_SEPARATOR . 'Template' .  DIRECTORY_SEPARATOR . strtolower($dir) . DIRECTORY_SEPARATOR . self::getPackageName();
        $filepath = $path . DIRECTORY_SEPARATOR . $filename;

        return (file_exists($filepath)) ? $path : null;
    }
}
