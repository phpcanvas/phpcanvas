<?php
/**
 * The MySQL database class
 * Uses {@link http://php.net/manual/en/book.mysqli.php PHP's improved MySQL library}
 * to connect to a mysql database
 * @author Gian Carlo Val Ebao
 * @version 1.0.3
 * @package PHPCanvas
 * @subpackage Database
 */

/**
 * The MySQL database class
 * Uses {@link http://php.net/manual/en/book.mysqli.php PHP's improved MySQL library}
 * to connect to a mysql database
 * @author Gian Carlo Val Ebao
 * @version 1.0.3
 * @package PHPCanvas
 * @subpackage Database
 */
class MysqlDatabase extends AbstractDatabase {
    
    /**
     * Class alias.
     */
    public $name = 'Mysql';
    
    /**
     * Establishes a connection to the database.
     * @param array $conf <b>Optional</b>. Alternate database configuration. If not specified, uses 'default'.
     * @return boolean
     */
    public function connect($conf = null) {
        $conf = empty($conf) ? $this->conf: $conf;
        
        extract($conf);
        $index = $this->name . ':' .$username . ':' . $password . ':' . $db . ':' . $host;
        
        if (isset(self::$connList[$index])) {
            $this->conn = self::$connList[$index]['connection'];
            $this->key = $index;
            return true;
        }
        
        $conn = new mysqli($host, $username, $password, $db);
        
        if (false === $conn || !empty($conn->connect_errno)) {
            trigger_error('Cannot connect to database using (' . $index . ')', E_USER_WARNING);
            return false;
        }
        
        $this->conn = $conn;
        $this->key = $index;
        
        self::$connList[$index] = array('connection' => $conn);
        return true;
    }

    /**
     * Prepares statement for execution. Returns false if an error occurs.
     * Refer to {@link http://www.php.net/manual/en/class.mysqli-stmt.php MySQLi_STMT} for methods that can be used. (Including <b>variable binding</b>)
     * @param string $sql sql statement.
     * @return boolean|mysqli_stmt
     */
    public function parse($sql) {
        $this->stmt = $this->conn->prepare($sql);
        return $this->stmt;
    }

    /**
     * Executes the active statement object.
     * @return boolean
     */
    public function execute() {
        return $this->stmt->execute();
    }
    
    /**
     * Releases all resources associated with active statement object.
     * @return boolean
     */
    public function freeStatement() {
        if (empty($this->stmt)) {
            return false;
        }

        $this->stmt->free_result();
        unset($this->stmt);
        return true;
    }
    
    /**
     * Runs a SQL query then returns the result.
     * @param string $sql Query.
     * @param boolean $isObj <b>Optional</b>. If the query returns results as an object, else and array.
     * @return boolean|array|object
     */
    public function query($sql, $isObj = true) {
        $result = $this->conn->query($sql);
        $sql = strtoupper($sql);
        
        if (empty($result)) {
            return false;
        }
        
        if(0 === strpos($sql, 'SELECT') || 0 === strpos($sql, 'DESC') ||
           0 === strpos($sql, 'SHOW')) {
            $fetchType = 'fetch' . ($isObj ? 'Object': 'Assoc');
            return $this->{$fetchType}($result);
        }
        
        return $result;
    }
    
    /**
     * Formats the result of a MySQL query to a standard object.
     * @param object &$result Result of a MySQL Query
     * @return object
     */
    private function fetchObject(&$result) {
        $rows = new stdClass();
        $i = 0;
        
        while ($item = $result->fetch_object()) {
            $rows->{$i++} = $item;
        }
        
        return $rows;
    }

    /**
     * Formats the result of a MySQL query to an associative array.
     * @param object &$result Result of a MySQL Query
     * @return array
     */
    private function fetchAssoc(&$result) {
        $rows = array();
        
        while($item = $result->fetch_assoc()) {
            $rows[] = $item;
        }
        
        return $rows;
    }

    /**
     * Destroys the database connection.
     */
    public function close() {
        if (empty($this->conn)) {
            return false;
        }
        parent::close();
        return $this->conn->close();
    }
    
    /**
     * Performs clean up.
     * @ignore
     */
    public function __destruct() {
        $this->freeStatement();
    }
    
    /**
     * Converts special characters in a string for use in a SQL statement.
     * Follows the format of {@link http://php.net/manual/en/function.sprintf.php PHP's sprintf}.
     * @param string $sql Query.
     * @param variant $value <b>Optional</b>. Values.
     * @return string
     */
    public function escape($sql, $value = null) {
        $args = func_get_args();
        
        for ($i = 1, $count = count($args); $i < $count; $i++) {
            $args[$i] = $this->conn->real_escape_string($args[$i]);
        }
        
        return call_user_func_array('sprintf', $args);
    }
    
}