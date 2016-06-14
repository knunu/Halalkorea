<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once ('Message.php');
require_once ('Common.php');

class Util
{
    public static function StrRetCode($code) {
        switch($code) {

            case SUCCESS:
                return "SUCCESS";
            case ERR_FAIL:
                return "ERR_FAIL";
            case ERR_MSG_INVALID_VALUE:
                return "ERR_MSG_INVALID_VALUE";
            case ERR_MSG_INVALID_CATEGORY_NAME:
                return "ERR_MSG_INVALID_CATEGORY_NAME";
            case ERR_MSG_INVALID_SESSION_ID:
                return "ERR_MSG_INVALID_SESSION_ID";
            case ERR_MSG_INVALID_PARAMETER:
                return "ERR_MSG_INVALID_PARAMETER";
            case ERR_MSG_INVALID_PRODUCT_IMAGE:
                return "ERR_MSG_INVALID_PRODUCT_IMAGE";
            case ERR_MSG_INVALID_INGREDIENT_IMAGE:
                return "ERR_MSG_INVALID_INGREDIENT_IMAGE";

            case ERR_LOGIN_FAILED:
                return "ERR_LOGIN_FAILED";
            case ERR_LOGIN_FAILED_EMAIL:
                return "ERR_LOGIN_FAILED_EMAIL";
            case ERR_LOGIN_FAILED_PASSWORD:
                return "ERR_LOGIN_FAILED_PASSWORD";
            case ERR_LOGIN_UNAUTHORIZED_EMAIL:
                return "ERR_LOGIN_UNAUTHORIZED_EMAIL";

            case ERR_JOIN_DUP_EMAIL:
                return "ERR_JOIN_DUP_EMAIL";
            case ERR_JOIN_DUP_NICKNAME:
                return "ERR_JOIN_DUP_NICKNAME";

            case ERR_DB_NODATA:
                return "ERR_DB_NODATA";
            case ERR_DB_DUPLICATION_DATA:
                return "ERR_DB_DUPLICATION_DATA";
        }
    }

    public static function setRetCode($code)
    {
        $result = array(
            'code' => $code,
            'message' => Util::StrRetCode($code)
        );

        return $result;
    }

    public static function getDefaultLatLng($latLng)
    {
        $latitude  = $latLng['latitude'];
        $longitude = $latLng['longitude'];

        if ($latitude == ''    ||
            $latitude == '0.0' ||
            $latitude == '0'   ||
            $latitude == null  ||
            $latitude == 'null') $latitude = DEFAULT_X;

        if ($longitude == ''    ||
            $longitude == '0.0' ||
            $longitude == '0'   ||
            $longitude == null  ||
            $longitude == 'null') $longitude = DEFAULT_Y;

        $latLng['latitude']  = $latitude;
        $latLng['longitude'] = $longitude;

        return $latLng;
    }

    public static function writeLogFile($filename, $class, $result = array()) {
        $filepath = LOG_FILE_PATH . "$class/";
        if (!file_exists($filepath)) {
            mkdir($filepath, 0777);
        }
        $filepath .= $filename;

        if (!file_exists($filepath) || filesize($filepath) > LOG_FILE_MAX_SIZE) {
            $debug_file = fopen($filepath, "w");
        } else {
            $debug_file = fopen($filepath, "a");
        }

        fwrite($debug_file, "[" . date('Y-m-d H:i:s') . "] $class ) ");
        foreach ($result as $title => $value) {
            fwrite($debug_file, "$title : " . print_r($value, TRUE) . " / ");
        }
        fwrite($debug_file, "\n");
        fclose($debug_file);
    }
}
