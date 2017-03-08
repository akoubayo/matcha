<?php
namespace App\Lib;

/**
*
*/
class RequestController
{

    function __construct(request $Req)
    {

    }

    public function controlInput($value)
    {
        $value =  stripslashes($value);
        $value =  addslashes($value);
        $value =  strip_tags($value);
        $value =  htmlspecialchars($value);
        return $value;
    }

    public static function control($value)
    {
        $value =  stripslashes($value);
        $value =  addslashes($value);
        $value =  strip_tags($value);
        $value =  htmlspecialchars($value);
        return $value;
    }

    public static function input($name = '')
    {
        if (!empty($name)) {
            if (isset($_POST[$name]) && !empty($_POST[$name])) {
                return $this->controlInput($_POST[$name]);
            }
            if (isset($_GET[$name]) && !empty($_GET[$name])) {
                return $this->controlInput($_GET[$name]);
            }
        } else {
            $ret = array();
            foreach ($_POST as $key => $value) {
                    $key = $this->controlInput($key);
                    $ret[$key] = $this->controlInput($value);
            }
            return $ret;
        }
        return '';
    }
}
