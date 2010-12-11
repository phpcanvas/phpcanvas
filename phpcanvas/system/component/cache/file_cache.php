<?php
/**
 * Caching system using file.
 * @author Gian Carlo Val Ebao
 * @version 1.0.4
 * @package PHPCanvas
 * @subpackage Cache
 */

/**
 * Caching system using file.
 * @author Gian Carlo Val Ebao
 * @version 1.0.4
 * @package PHPCanvas
 * @subpackage Cache
 */
class FileCache extends AbstractCache {
    
    /**
     * Cache file system path.
     * @access private
     */
    private $path = '';
    
    private static $fo = null;
    
    /**
     * Contains the timestamp when the cache will expire.
     * @access private
     */
    private $expires = null;
    
    /**
     * Initializes the properties of the class.
     * @param string $path Folder where the logs will be stored.
     * @param string $nameSpace <b>Optional</b>. recommended for a quicker access to the cache.
     * @param int $expires <b>Optional</b>. expiration date of the cache, default is 1 day.
     */
    public function __construct($path, $nameSpace = '', $expires = 86400) {
        $this->path = $path;
        $this->expires = (int) date('Ymd', time() + $expires);
        
        if (!empty($nameSpace)) {
            $this->path = $path . '/ns' . md5($nameSpace);
        }
		
        if (!empty(self::$fo)) {
            self::$fo = new File($this->path);
        }
    }
    
    /**
     * Stores data to the cache file.
     * @access private
     * @param string $file cache file name.
     * @param string $string data to be stored.
     * @return boolean
     */
    private function write($file, $string) {
        return self::$fo->file($file)->write($string);
    }
    
    /**
     * Retrieves the data from a cache file.
     * @access private
     * @param string $file cache file name.
     * @return string
     */
    private function read($file) {
        return self::$fo->file($file)->readAll();
    }
    
    /**
     * Creates a valid cache file name with path.
     * @access private
     * @param string $key unique identifier.
     * @return string
     */
    private function toFile($key) {
        return md5('cache' . $key . '.cch');
    }
    
    /**
     * Creates a cache.
     * @param string $key Unique reference key of the cache.
     * @param string|array $value the value to be stored.
     * @return string
     */
    public function set($key, $value) {
        if (empty($value)) {
            return false;
        }
        
        $s = $this->expires . (is_array($value) || is_object($value) ? ':O:' . json_encode($value): ':S:' . $value);
        return $this->write($this->toFile($key), $s);
    }
    
    /**
     * Clears all the cache created.
     * If a namespace was supplied, clears only the cache with the namespace.
     * @return boolean
     */
    public function clear() {
        $path = $this->path;
        
        if(empty($path) || !is_dir($path) || 2 >= count(scandir($path))) {
            return false;
        }
        
        $scan = glob($path . '/*');
        
        do {
            unlink(array_shift($scan));
        } while(!empty($scan));
        
        rmdir($path);
        return true;
    }
    
    /**
     * Gets a cached value.
     * @param string $key Unique reference key of the cache.
     * @param boolean $isObj <b>Optional</b>. returns result as object or array. disregarded if the value is of the cache is not an array.
     * @return string|array
     */
    public function get($key, $isObj = false) {
        $r = $this->read($this->toFile($key));
        
        $now = (int) date('Ymd');
        
        if(false === $r || $now >= (int) substr($r, 0, 8)) {
            return false;
        }
        
        switch (substr($r, 9, 1)) {
            case 'O':
                return json_decode(substr($r,11), !$isObj);
            default:
            case 'S':
                return substr($r,11);
        }
    }
}