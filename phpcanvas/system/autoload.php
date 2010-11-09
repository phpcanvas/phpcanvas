<?php
/**
 * Contains generalized functions used by the system.
 * @author Gian Carlo Val Ebao
 * @package Utilities
 * @version 1.4.0
 */

 
 /* Always require these files */
 
 /**
 * Contains the file system helper class.
 */
require 'system/utilities/file.php';
 
/* Arrays */

/**
 * Merges an array recursively over writting the previous value of an identical associated key.
 * @param array $array1 Array which will be overwritten.
 * @param array $array2 Array who will overwrite.
 * @return array
 */
function array_merge_recursive_distinct($array1, $array2) {
    $merged = $array1;

    foreach ($array2 as $key => &$value) {
        $merged[$key] = (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) ?
            array_merge_recursive_distinct($merged[$key], $value): $value;
    }

    return $merged;
}



/** Supported  after PHP 5.2 **/
if (!function_exists('json_decode') || !function_exists('json_encode')) {
	require 'system/utilities/json.php';

	/**
	 * Decodes a JSON string
	 * @param string $str The json string being decoded.
	 * @param boolean $isArray When TRUE, returned objects will be converted into associative arrays.
	 * @return array
	 */
    function json_decode($str, $isArray = true) {
        $json = new JSON();
        $arr = $json->decode($str);
        return $isArray ? $json->toArray($arr): $arr;
    }

	/**
	 * Returns the JSON representation of a value.  This function only works with UTF-8 encoded data.
	 * @param variant $arr The value being encoded. Can be any type except a resource.
	 * @return string
	 */
    function json_encode(&$arr) {
        $json = new JSON();
        return $json->encode($arr);
    }
} 