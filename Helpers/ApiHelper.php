<?php
/**
 * API Helper
 */

namespace Typhoeus\Api\Helpers;
use Typhoeus\Catalog\Checkout\ShippingMethod\Connectship\API;
use Typhoeus\Catalog\Checkout\ShippingMethod\Connectship\NameAddress;
class ApiHelper {

    private $dataType;

    /**
     * @var string $packageName
     */
    private static $packageName = 'api';

    /**
     * @var string
     */
    private $countrySymbol;

    /**
     * @var string
     */
    private $countryCode;

    /**
     * @var string[]
     */
    private $services;

    public function __construct()
    {
        $this->dataType = [
            "sku" => "productId",
            "id" => "productId",
            "mpn" => "mpn"
        ];
        $this->countrySymbol = "UNITED_STATES";
        $this->countryCode = "US";
        $this->services = array(
            //"TANDATA_UPS.UPS.GND",
            //"TANDATA_UPS.UPS.NDA",
            //"TANDATA_UPS.UPS.2DA",
            // "TANDATA_UPS.UPS.3DA",
            //"TANDATA_UPS.UPS.SPPS",
            //"TANDATA_UPS.UPS.SPSTD"
            "TANDATA_FEDEXFSMS.FEDEX.GND"
            //"TANDATA_FEDEXFSMS.FEDEX.STD",
            // "TANDATA_FEDEXFSMS.FEDEX.2DA",
            // "TANDATA_FEDEXFSMS.FEDEX.SP_PS",
            // "CONNECTSHIP_UPSMAILINNOVATIONS.UPS.EPD",
            // "CONNECTSHIP_UPSMAILINNOVATIONS.UPS.FIRST",
            //  "CONNECTSHIP_ENDICIA.USPS.FIRST",
            // "CONNECTSHIP_ENDICIA.USPS.PRIORITY",
            //"CONNECTSHIP_ENDICIA.USPS.PARCELPOST",
            // "CONNECTSHIP_ENDICIA.USPS.EXPR"
        );
    }

    /**
     * Gets the package name
     * @return string
     */
    public static function getPackageName(): string
    {
        return self::$packageName;
    }

    /**
     * Gets the working path of a certain package based on the object passed and used either from workbench or vendor
     * @param object $object
     * @return string
     */
    public static function getWorkingPath($object): string
    {

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
    public static function getTemplatePath(string $dir, string $filename) {

        $path = app_path() . DIRECTORY_SEPARATOR . 'Template' .  DIRECTORY_SEPARATOR . strtolower($dir) . DIRECTORY_SEPARATOR . self::getPackageName();
        $filepath = $path . DIRECTORY_SEPARATOR . $filename;

        return (file_exists($filepath)) ? $path : null;
    }

    /**
     * @param $key
     * @return false|string
     */
    public function getDataType($key) {
        if (array_key_exists($key, $this->dataType)) {
            return $this->dataType[$key];
        }
        return false;
    }

    /**
     * @param $zip
     * @param $weight
     * @param $country
     * @return mixed
     */
    public function estimateShipping($zip, $weight )
    {
        $nameAddress = new NameAddress();
        $nameAddress->postalCode	= $zip;
        $nameAddress->countrySymbol	= $this->countrySymbol;
        $nameAddress->countryCode	= $this->countryCode;
        if ($weight == 0) $weight = 12;
        $packageData	= array(
            "weight" => $weight
        );
        $services = $this->services;
        $shippings = json_decode(json_encode(API::rate($services, $nameAddress, $packageData)), true);
        return $shippings['item']['resultData']['base']['amount'];
    }

    /**
     * @param $string
     * @return bool
     */
    function isJson($string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

}
