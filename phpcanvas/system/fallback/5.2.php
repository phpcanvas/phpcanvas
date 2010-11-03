<?php
/**
* Creates required functions that are not available in PHP 5.2

/** JSON **/

$JSONDecode = function_exists('json_decode');
$JSONEncode = function_exists('json_encode');


if (!$JSONDecode || !$JSONEncode) {
    include system_root . '/system/fallback/json.php';
}

/** JSON_DECODE: Supported since PHP 5.2 **/
if (!$JSONDecode) {
    function json_decode($str, $isArray = true) {
        $json = new Services_JSON();
        $arr = $json->decode($str);
        return $isArray ? $json->toArray($arr): $arr;
    }
}

/** JSON_ENCODE: Supported since PHP 5.2 **/
if (!$JSONEncode) {
    function json_encode($arr) {
        $json = new Services_JSON();
        return $json->encode($arr);
    }
} 