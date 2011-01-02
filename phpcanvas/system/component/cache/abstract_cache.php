<?php
/**
 * Data caching abstraction class.
 * @author Gian Carlo Val Ebao
 * @version 1.0.0
 * @package PHPCanvas
 * @subpackage Cache
 */

/**
 * Data caching abstraction class.
 * @author Gian Carlo Val Ebao
 * @version 1.0.0
 * @package PHPCanvas
 * @subpackage Cache
 */
abstract class AbstractCache {

    /**
     * Creates a cache.
     * @param string $key Unique reference key of the cache.
     * @param string|array $value the value to be stored.
     * @return string
     */
    abstract public function set($key, $array);

    /**
     * Clears all the cache created.
     * @return boolean
     */
    abstract public function clear();
    
    /**
     * Gets a cached value.
     * @param string $key Unique reference key of the cache.
     * @param boolean $isObj <b>Optional</b>. returns result as object or array. disregarded if the value is of the cache is not an array.
     * @return string|array
     */
    abstract public function get($key, $isObj = true);
}