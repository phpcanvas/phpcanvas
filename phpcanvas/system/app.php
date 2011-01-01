<?php
/**
 * The Master class.
 * Contains all system-related functions like, class monitoring,
 * program flow and standards maintenance.
 * @author Gian Carlo Val Ebao
 * @version 1.4.3
 * @package PHPCanvas
 */

/**
 * The Master class.
 * Contains all system-related functions like, class monitoring,
 * program flow and standards maintenance.
 * @author Gian Carlo Val Ebao
 * @version 1.4.3
 * @package PHPCanvas
 */
class App {
    /**
     * Application identifier.
     * @access private
     */
    public static $id = '';
    
    /**
     * Contains all the loaded configurations.
     */
    public static $conf = array();
    
    /**
     * framework directory path.
     */
    public static $sysroot = null;
    
    /**
     * The Log object.
     * @access private
     */
    private static $log = null;
    
    /**
     * Log file name whenever an error occurs in the application.
     * @access private
     */
    private static $logFile = 'error_app.log';
    
    /**
     * Contains the most recent error that occured.
     * @access private
     */
    private static $lastError = array(
            'type' => '',
            'message' => '',
            'file' => '',
            'line' => ''
        );
    /**
     * Contains all the loaded classes.
     * @access private
     */
    private static $classes = array();

    /**
     * Contains all declared include path.
     * @access private
     */
    public static $includePath = array();
    
    /**
     * Checks if the file/directory exists relative to the include_path.
     * @static
     * @param string $file file or directory path
     * @return boolean
     */
    public static function fileExists($file) {
        $path = self::$includePath;
        
        for ($i = 0, $count = count($path); $i < $count; $i++) {
            if (file_exists($path[$i] . '/' . $file)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Returns the real ip address of the client.
     * @return string
     */
    function getIP() {
        return (empty($_SERVER['HTTP_CLIENT_IP']) ? (empty($_SERVER['HTTP_X_FORWARDED_FOR']) ?
            $_SERVER['REMOTE_ADDR']: $_SERVER['HTTP_X_FORWARDED_FOR']): $_SERVER['HTTP_CLIENT_IP']);
    }
    
    /**
     * Loads the required filename of a class.
     * @param string $className the class name to be loaded.
     */
    public static function load($className) {
        self::$classes[] = $className;
        $fileName = App::toFileName($className);
        $file = App::file($fileName, substr($fileName, strrpos($fileName, '_') + 1), true);
        
        if ($file) {
            include $file;
        } else {
            $util = App::file($fileName, 'utility', true);
            if ($util) {
                include $util;
            } else {
                App::kill('Class \'' . $className . '\' cannot be loaded. File not found.');
            }
        }
    }
    
    public static function showLoaded() {
        return self::$classes;
    }
    
    /**
     * Loads the configurations to the class.
     * Calling this multiple times will append/overwrite the previous configuration keys.
     * @param array|string $conf configuration source.
     * @param string $namespace namespace of the contents.
     * @static
     */
    public static function initialize($conf, $namespace = '') {
        $conf = !is_array($conf) ? parse_ini_file($conf, true): $conf;
        
        if (empty($conf)) {
            return false;
        }

        if (!empty($namespace)) {
            $conf = array($namespace => $conf);
        }
        
        $conf['FILE_APPLICATION'] = empty($conf['FILE_APPLICATION']) ? array(): $conf['FILE_APPLICATION'];
        $conf['FILE_SYSTEM'] = empty($conf['FILE_SYSTEM']) ? array(): $conf['FILE_SYSTEM'];
        $conf['file'] = array_merge($conf['FILE_APPLICATION'], $conf['FILE_SYSTEM']);

        unset($conf['FILE_APPLICATION'], $conf['FILE_SYSTEM']);
        
        if (!empty($conf['DEFINE'])) {
            foreach ($conf['DEFINE'] as $k => $v) {
                if (!defined($k)) {
                    define($k, $v);
                }
            }
        }
        
        unset($conf['DEFINE']);

        self::$conf = array_merge_recursive_distinct(self::$conf, $conf);
    }
    
    /**
     * Initializes all required global objects.
     * @static
     * @return object
     */
    public static function startObjects() {
        self::$log = new FileLog(self::$conf['file']['log'] . '/');
        self::$logFile = 'error_' . date('ymd');
    }
    
    /**
     * Processes all system and application generated errors.
     * @access private
     * @static
     * @param integer $no error type code
     * @param string $msg error message
     * @param string $file file where the error occured.
     * @param string $line error line number
     * @return boolean
     */
    public static function errorHandler($no, $msg, $file, $line) {
        self::$lastError = array(
            'type' => $no,
            'message' => $msg,
            'file' => $file,
            'line' => $line
        );
        
        switch ($no) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $no = 'Notice';
                break;
            
            case E_WARNING:
            case E_USER_WARNING:
                $no = 'Warning';
                break;
            
            case E_ERROR:
            case E_USER_ERROR:
                $no = 'Fatal';
                break;
            
            default:
                $no = 'Unknown';
                break;
        }
        
        $file = pathinfo($file);
        $file = substr($file['dirname'], strrpos($file['dirname'], DIRECTORY_SEPARATOR) + 1) .
            '.' . $file['basename'];
        $logfile = self::$logFile . '_' . self::getIP() . '.' . $file . '.log';
        
        if (isset(self::$conf['debug'])) {
            if('explicit' == self::$conf['debug'] || 'Fatal' == $no || 'Unknown' == $no) {
                echo '<pre>', $no, ' : [', $file, ':', $line, '] ', $msg, '</pre>';
            }
        } else {
            self::log($logfile)->write(date('[H:i:s]') . $no . ' : [' . $line . '] ' . $msg. "\n");
        }
        
        return true;
    }

    /**
     * Returns the LogFile Object. Logs will be stored in the <b>tmp</b> folder
     * @uses Log Class
     * @param string $file <b>Optional</b>. Target log file.
     * @param boolean $isAppend <b>Optional</b>. if TRUE, appends the message, else overwrites.
     * @static
     * @return object
     */
    public static function log($file = null, $isAppend = true) {
        $log = self::$log;
        if (!empty($file)) {
            $log->file($file);
        }
        return $log->isAppend($isAppend);
    }

    /**
     * Returns the standard file path.
     * @param string $name Name of the class to be loaded.(exclude suffixes)
     * @param string $type Class type.
     * @param boolean $isRequired stops the application if the file does not exist, Default is false
     * @static
     * @return string
     */
    public static function file($name, $type) {
        $conf = self::$conf['file'];
        if (empty($conf)) {
            return $name;
        }
        
        if (false !== in_array($type, self::$conf['COMPONENTS']['item'])) {
            $path = $conf['component'] . '/' . $type;
        } elseif (!empty($conf[$type])) {
            $path = $conf[$type];
        } else {
            return false;
        }
        
        $path = $path . '/' . $name . ('view' == $type || 'error' == $type ? '': '.php');
        
        return self::fileExists($path) ? $path: false;
    }

    /**
     * Converts a string into a standard class name. 
     * @param string $path Subject string.
     * @static
     * @return string
     */
    public static function toMethodName($path) {
        $path = strtolower($path);
        
        if (false === strpos($path, '_') && false === strpos($path, '-')) {
            return $path;
        }
        
        $path = str_replace(array('_', '-'), ' ', trim($path, ' _-'));
        return str_replace(array('_', '-', ' '), '', ucwords('_' . strtolower($path)));
    }

    /**
     * Converst a string into a valid file name.
     * @static
     * @param string $path Subject string.
     * @return string
     */
    public static function toFileName($path) {
        $fileRule = array(
            'search' => array ('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
                'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'),
            'replace' => array ('_a', '_b', '_c', '_d', '_e', '_f', '_g', '_h', '_i', '_j','_k', '_l',
                '_m', '_n', '_o', '_p', '_q', '_r', '_s', '_t', '_u', '_v', '_w', '_x', '_y', '_z')
        );
        $fullPath = pathinfo($path);
		if (isset($fullPath['filename'])) { //PHP 5.3
			$path = $fullPath['filename'];
		} else { //PHP 5.1
			$pathpos = strpos($fullPath['basename'], '.');
			$path = substr($fullPath['basename'], 0, $pathpos ? $pathpos: strlen($fullPath['basename']));
		}
        
        $dir = '.' == isset($fullPath['dirname']) ? '': $fullPath['dirname'] . '/';
        
        if ($path === strtolower($path)) {
            return $dir . $path;
        }
        
        $path = trim(str_replace($fileRule['search'], $fileRule['replace'], $path), '_');
        return $dir . $path;
    }

    /**
     * Gets the configurations of a certain key.
     * To access the lower-level keys, delimit with a period:
     * <code>
     * App::conf('my_key.sub_key');
     * // returns 'value',
     * // [my_key]
     * //    sub_key = value
     * // in this configuration.
     * </code>
     * @param string $key Index of the configuration.
     * @static
     * @return array|string
     */
    public static function conf($key) {
        $key = explode('.', $key);
        $conf = self::$conf;
        
        do {
            $i = array_shift($key);
            
            if (!isset($conf[$i])) {
                continue;
            }
            
            if (empty($key)) {
                return $conf[$i];
            }
            
            $conf = $conf[$i];
            
        } while (!empty($key));
        
        return false;
    }
	
    /**
     * Initializes the error reporting.
     * @static
     */
    public static function debug() {
        if (isset(self::$conf['debug'])) {
            error_reporting(E_ALL);
            ini_set('display_errors', '2');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
    }

    /**
     * Stops the application and loads the corresponding error_page.
     * @param string $err error code.
     * @static
     */
    public static function throwError($err) {
        $site = self::conf('site_url');
        $link =  $site . '/' . $_SERVER['QUERY_STRING'];
        $homepage = $site . '/';
        $webmaster = self::$conf['application']['webmaster'];
        $date = date('r');
        include self::file($err . '.htm', 'error');
        exit;
    }

    /**
     * Returns the details of the latest error that occured.
     * <b>$key</b> Options:
     * <ul>
     * <li><b>type</b> error type code</li>
     * <li><b>message</b> error message</li>
     * <li><b>file</b> affected file</li>
     * <li><b>line</b> line number</li>
     * </ul>
     * @param string $key <b>Optional</b>. A definite part of the error.
     * @return array|string
     */
    public static function getLastError($key = null) {
        $r = error_get_last();
        if (empty($r)) {
            $r = self::$lastError;
        }
        $r['message'] = trim(substr($r['message'], strpos($r['message'], ':') + 1));
        return !empty($r) ? $r[$key]: $r;
    }

    
    /**
     * Prompts a message and aborts running the application.
     * @param string $msg message.
     * @static
     */
    public static function kill($msg) {
        exit('<pre><strong>Fatal error:</strong> ' . $msg . '</pre>');
    }
}