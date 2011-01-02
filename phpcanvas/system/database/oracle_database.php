<?php
/**
 * The Oracle database class
 * Uses {@link http://php.net/manual/en/book.oci8.php PHP's Oracle (OCI8) library}
 * to connect to an Oracle 11g, 10g, 9i and 8i database
 * @author Gian Carlo Val Ebao
 * @version 1.3.7
 * @package PHPCanvas
 * @subpackage Database
 */

/**
 * The Oracle database class
 * Uses {@link http://php.net/manual/en/book.oci8.php PHP's Oracle (OCI8) library}
 * to connect to an Oracle 11g, 10g, 9i and 8i database
 * @author Gian Carlo Val Ebao
 * @version 1.3.7
 * @package PHPCanvas
 * @subpackage Database
 */
class OracleDatabase extends AbstractDatabase {
    
    /**
     * Class alias.
     */
    private $name = 'Oracle';
    
    /**
     * Establishes a connection to the database.
     * @param array $conf <b>Optional</b>. Alternate database configuration. If not specified, uses 'default'.
     * @return boolean
     */
    public function connect($conf = null) {
        $conf = empty($conf) ? $this->conf: $conf;
        
        extract($conf);
        $index = $this->name . ':' . $username . ':' . $password . ':' . $db;
        
        if (isset(self::$connList[$index])) {
            $this->conn = self::$connList[$index]['connection'];
            $this->key = $index;
            return true;
        }
        
        $conn = oci_connect($username, $password, $db);
        
        if (false === $conn) {
            trigger_error("Cannot connect to database using ($index)", E_USER_WARNING);
            return false;
        }
        
        $this->conn = $conn;
        $this->key = $index;
        
        self::$connList[$index] = array('connection' => $conn);
        return true;
    }
   
    /**
     * Prepares statement for execution.
     * Returns the statement object or <b>FALSE</b> if an error occurs.
     * @param string $sql sql statement.
     * @return boolean|object
     */
    public function parse($sql) {
        $this->stmt = oci_parse($this->conn, $sql);
        return $this->stmt;
    }

    /**
     * Executes a statement.
     * $opts (execution constants) Options:
     * <ul>
     * <li><b>OCI_COMMIT_ON_SUCCESS</b> <b>Default</b>. Automatically commit all outstanding changes for this connection when the statement has succeeded. This is the default.</li>
     * <li><b>OCI_DEFAULT</b> Obsolete as of PHP 5.3.2 (PECL OCI8 1.4) but still available for backward compatibility. Use the equivalent OCI_NO_AUTO_COMMIT in new code.</li>
     * <li><b>OCI_DESCRIBE_ONLY</b> Make query meta data available to functions like oci_field_name() but do not create a result set. Any subsequent fetch call such as oci_fetch_array() will fail.</li>
     * <li><b>OCI_NO_AUTO_COMMIT</b> Do not automatically commit changes. Prior to PHP 5.3.2 (PECL OCI8 1.4) use OCI_DEFAULT which is an alias for OCI_NO_AUTO_COMMIT.</li>
     * </ul>
     * @param int $opts <b>Optional</b>. Execution mode like OCI_COMMIT_ON_SUCCESS. Default is OCI_COMMIT_ON_SUCCESS
     * @return boolean
     */
    public function execute($opts = OCI_COMMIT_ON_SUCCESS) {
        return oci_execute($this->stmt, $opts);
    }
    
    /**
    * Creates a new collection object.
    * Returns the collection object or <b>FALSE</b> if an error occurs.
    * @param string $name Should be a valid named type (uppercase). 
    * @return boolean|object 
    **/
    public function newCollection($name) {
        return oci_new_collection($this->conn, $name);
    }
    
    /**
    * Initializes a new empty LOB or FILE descriptor.
    * Returns the LOB or FILE descriptor object or <b>FALSE</b> if an error occurs.
    * <br><b>$opts</b> Options:
    * <ul>
    * <li><b>OCI_DTYPE_FILE</b> File</li>
    * <li><b>OCI_DTYPE_LOB</b> LOB</li>
    * <li><b>OCI_DTYPE_ROWID</b> Row id</li>
    * </ul>
    * @param int $opts <b>Optional</b>. Descriptor type. 
    * @return boolean|object
    **/
    public function newDescriptor($opts = OCI_D_LOB) {
        return oci_new_descriptor($this->conn, $opts);
    }
    
    /**
    * Binds a variable to the Oracle placeholder.
    * $opts Options:
    * <ul>
    * <li><b>SQLT_FILE</b> for BFILEs</li>
    * <li><b>SQLT_CFILE</b> for CFILEs</li>
    * <li><b>SQLT_CLOB</b> for CLOBs</li>
    * <li><b>SQLT_BLOB</b> for BLOBs</li>
    * <li><b>SQLT_RDD</b> for ROWIDs</li>
    * <li><b>SQLT_NTY</b> for named datatypes</li>
    * <li><b>SQLT_INT</b> for integers</li>
    * <li><b>SQLT_CHR</b> <b>Default</b>. for VARCHARs</li>
    * <li><b>SQLT_BIN</b> for RAW columns</li>
    * <li><b>SQLT_LNG</b> for LONG columns</li>
    * <li><b>SQLT_LBI</b> for LONG RAW columns</li>
    * <li><b>SQLT_RSET</b> for cursors, that were created before with oci_new_cursor().</li>
    * </ul>
    * @param string $ph oracle place holder
    * @param mixed &$variable PHP variable. variable will be passed by reference.
    * @param int $len <b>Optional</b>. Data length of the variable. -1 for auto.
    * @param int $opts <b>Optional</b>. Bind type.
    * @return oracle_db
    **/
    public function bindByName($ph, &$variable, $len = -1, $opts = SQLT_CHR) {
        oci_bind_by_name($this->stmt, $ph, $variable, $len, $opts);
        return $this;
    }
    
    /**
     * Releases all resources associated with active statement object.
     * @return boolean
     */
    public function freeStatement() {
        if (empty($this->stmt)) {
            return false;
        }
        
        oci_free_statement($this->stmt);
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
        $stmt = $this->parse($sql);
        $result = $this->execute(OCI_COMMIT_ON_SUCCESS);
        
        if(0 === strpos(strtoupper($sql), 'SELECT') ||
            0 === strpos(strtoupper($sql), 'DESC') ||
            0 === strpos(strtoupper($sql), 'SHOW')) {
            
            $fetchType = 'fetch' . ($isObj ? 'Object': 'Assoc');
            return $this->{$fetchType}();
            
        }
            return $result;
    }

    /**
     * Formats the result of a MySQL query to a standard object.
     * @param object &$result Result of a MySQL Query
     * @return object
     */
    private function fetchObject() {
        $rows = new stdClass();
        $i = 0;
        
        while ($itm = oci_fetch_object($this->stmt)) {
            $rows->{$i++} = $itm;
        }
        return $rows;
    }
    
    /**
     * Formats the result of a MySQL query to an associative array.
     * @param object &$result Result of a MySQL Query
     * @return array
     */
    private function fetchAssoc() {
        $rows = array();
        
        while($itm = oci_fetch_assoc($this->stmt)) {
            $rows[] = $itm;
        }
        
        return $rows;
    }

    /**
    * Cancels the previous query. Chainable.
    * @return object
    **/
    public function rollback() {
        oci_rollback($this->conn);
        return $this;
    }

    /**
     * Applies the previous query. Chainable.
     * @return object
     */
    public function commit() {
        if (empty($this->conn)) {
            return false;
        }
        oci_commit($this->conn);
        return $this;
    }
    
    /**
     * Destroys the database connection.
     */
    public function close() {
        if (empty($this->conn)) {
            return false;
        }
        parent::close();
        return oci_close($this->conn);
    }
    
    /**
     * Performs clean up.
     * @ignore
     */
    public function __destruct() {
        $this->freeStatement();
    }
    
    /**
     * Creates a condition that works around Oracle's restriction in limiting the number of items in a list. [Not Recommended. Use only if really needed.]
     * @param string $field The table field.
     * @param array $data a list of items to be compared.
     * @return string
     */
    public function listLimitFix($field, $data) {
        $list = array();
        $data = array_chunk($data, 900);
        
        do {
            $list[] = "$field IN ('" . implode("','", array_shift($data)) . "')";
        } while(!empty($data));
        
        return '(' . implode(' OR ', $list) . ')';
    }
}