<?php
/**
 * The Database abstraction class.
 * A generalized class for database object interfacing.
 * @author Gian Carlo Val Ebao
 * @abstract
 * @version 1.3.4
 * @package PHPCanvas
 * @subpackage Database
 */

/**
 * The Database abstraction class.
 * A generalized class for database object interfacing.
 * @author Gian Carlo Val Ebao
 * @abstract
 * @version 1.3.4
 * @package PHPCanvas
 * @subpackage Database
 */
abstract class AbstractDatabase {
    
    /**
     * List of all active connections.
     * @access private
     */
    protected static $connList = array();

    /**
     * Current configuration settings.
     * @access private
     */
    protected $conf = array();
    
    /**
     * Current active connection.
     * @access private
     */
    protected $conn = null;

    /**
     * Current active statement object.
     * @access private
     */
    protected $stmt = null;
    
    /**
     * Current configuration key.
     * @access private
     */
    protected $key = null;
    
    /**
     * Log object used for error handling.
     * @access private
     * @deprecated
     */
    protected static $log = null;
    
    /**
     * Initializes the object to a specific database configuration. Chainable.
     * @param array $conf The configuration array.
     * @return object
     */
    public function initialize($conf) {
        $this->conf = $conf;
        return $this;
    }

    /**
     * Establishes the connection to the database.
     * @param array $conf <b>Optional</b>. The configuration array.
     * @return object
     */
    abstract public function connect($conf = null);
    
    /**
     * Checks if a connection exists.
     * @return boolean
     */
    public function isConnected() {
        return !empty($this->conn);
    }
    
    /**
     * Returns the active statement object.
     * @return object
     */
    public function getStatement() {
        return $this->stmt;
    }
    
    /**
     * Returns the active connection object.
     * @return object
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Executes the active statement object.
     * @return boolean
     */
    abstract public function execute();
    
    /**
     * Runs a SQL query then returns the result.
     * @param string $sql Query.
     * @param boolean $isObj <b>Optional</b>. If the query returns results, it  as an object, else and array.
     * @return boolean|array|object
     */
    abstract public function query($sql, $isObj = true);
    
    /**
     * Destroys the database connection.
     */
    public function close() {
        unset(self::$connList[$this->key]);
    }
}