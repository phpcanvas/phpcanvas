<?php
/**
 * Extended by all model classes.
 * Contains standardized method for interacting with the database object.
 * @author Gian Carlo Val Ebao
 * @version 1.2.4
 * @package PHPCanvas
 * @subpackage Core
 */

/**
 * Extended by all model classes.
 * Contains standardized method for interacting with the database object.
 * @author Gian Carlo Val Ebao
 * @version 1.2.4
 * @package PHPCanvas
 * @subpackage Core
 */
class ModelCore {

    /**
     * Default database configuration to load.
     */
    public $conf = 'default';
    
    /**
     * The type of database object.
     */
    protected $type = 'mysql';

    /**
     * The loaded database object.
     */
    public $db = null;

    /**
     * Indicates if the model is currently connected to the database.
     * @access private
     */
    private $isConnected = false;

    /**
     * If set to true, caching will always be used when executing sql statements.
     */
    protected $alwaysCache = false;

    /**
     * cache's expiration for each query.
     * Set to <b>FALSE</b> if it does not expire.
     */
    protected $expiresCache = 86400;
    
    /**
     * Flag used to temporarily activate caching.
     * @ignore
     */
    private $activateCache = false;

    /**
     * Initializes the database model.
     */
    public function __construct() {
        $db = strtolower($this->type) . 'Database';
        $this->db = new $db();
    }

    /**
     * Connects to the database using a database connection.
     * @final
     * @param string|array $key <b>Optional</b>. database configuration Id. if array is given, tries all the keys until successful. uses $conf property if not specified.
     * @return boolean
     */
    public final function connect($key = '') {
        if (empty($key)) {
            $key = $this->conf;
        }
        if (!is_array($key)) {
            $key = array($key);
        }
        
        $confs = App::conf('database');
        
        foreach ($key as $v) {
            if (!empty($v) && isset($confs[$v])) {
                $conn = $this->db->initialize($confs[$v])->connect();
                if ($conn) {
                    return $conn;
                }
            }
        }
        return false;
    }

    /**
     * Initializes the cache object.
     * @final
     * @return object
     */
    public function startCache() {
        $key = $this->conf;
        
        if (!is_array($key)) {
            $key = array($key);
        }
        
        if (empty($key)) {
            App::kill('Cannot start caching without a database configuration.');
        }
        
        $confs = App::conf('database');
        $keyStr = array();
        
        foreach ($key as $v) {
            if (!empty($v) && isset($confs[$v])) {
                $keyStr[] = $confs[$v]['username'] . '@' . $confs[$v]['db'];
            }
        }
        
        if (empty($keyStr)) {
            App::kill('Configuration key cannot be found in database.ini.');
        }
        
        $key = $this->type . ':' . implode('-', $keyStr);
        
        $conf = App::conf('file.cache') . '/_modelcache';
        return new FileCache($conf, $key, $this->expiresCache);;
    }

    /**
     * Removes all the cache made by the connection.
     * @param string|array $key Optional. database configuration Id. uses $conf property if not specified.
     * @final
     * @return object
     */
    public final function clearCache($key = '') {
        return $this->startCache($key)->clear();
    }

    /**
     * Temporarily enforces caching in executing sql statements. Chainable.
     * @final
     * @return object
     */
    public function cache() {
        $this->activateCache = true;
        return $this;
    }

    /**
     * Executes a sql statement.
     * @param string $sql sql statement
     * @param boolean $isObj <b>Optional</b>. If the query returns a result, it returns it as an object, else and array.
     * @return array|object
     */
    public function query($sql, $isObj = false) {
        $activateCache = $this->activateCache || $this->alwaysCache;
        if ($activateCache) {
            $cache = $this->startCache();
            $r = $cache->get($sql, $isObj);
            if (false !== $r) {
                return $r;
            }
        }
        
        $this->connect();
        $r = $this->db->query($sql, $isObj);
        if (!$activateCache) {
            return $r;
        }
        
        if($r && !empty($r) && 0 < count($r)) {
            $cache = $this->startCache();
            $cache->set($sql, $r);
        }
        
        $this->activateCache = false;
        
        return $r;
    }
    
    /**
    * Releases all resources made by the Model.
    * @ignore
    */
    public final function __destruct() {
        $this->db->close();
    }
}