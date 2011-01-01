<?php
/**
 * File Class.
 * @author Gian Carlo Val Ebao
 * @version 1.0.3
 * @package PHPCanvas
 * @subpackage Utilities
 */

/**
 * File system helper
 * @author Gian Carlo Val Ebao
 * @version 1.0.3
 * @package PHPCanvas
 * @subpackage Utilities
 */
class File {

    /**
     * Contains the target file name.
     * @access private
     */
    private $file = '';
    
    /**
     * Contains the target file system path.
     * @access private
     */
    private $path = '';
    
    /**
     * Contains the default file system path.
     * @access private
     */
    private static $defaultPath = null;
    
    /**
     * Indicates if the new string will be appended to the file or not.
     * @access private
     */
    private $isAppend = false;
    
    public static function defaultPath($path) {
        if (empty(self::$defaultPath)) {
            self::$defaultPath = $path;
        }
        
        return self::$defaultPath;
    }
    
    /**
     * Initializes the properties of the class.
     * @param string $path Folder where the files will be stored. a wildcard <temp> can be prepended
            to set the folder inside the default path.
     * @return object
     */
    public function __construct($path = '<temp>') {
    
        if(0 === strpos($path, '<temp>')) {
            $path = self::$defaultPath . substr($path, 6);
        }
        
        $path .= ('/' == substr($path, -1) || '\\' == substr($path, -1)) ? '': '/';
        if (!file_exists($path)) {
            $paths = explode('/', str_replace('\\', '/', $path));
            for ($path = $paths[0], $i = 1, $count = count($paths); $i < $count; $i++) {
                $path .= '/' . $paths[$i];

                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
            }
        }        
        $this->path = $path;
    }

    /**
     * Sets the target file name. Chainable.
     * @param string $file file name.
     * @return object
     */
    public function file($file) {
        $this->file = $file;
        return $this;
    }

    /**
     * Returns a list of all files inside the folder.
     * @return array
     */
    public function listFiles() {
        $handle = opendir($this->path);
        $ignore = array('.', '..', '.htaccess');
        $files = array();
        
        while (false !== ($file = readdir($handle))) {
            if (!in_array($file, $ignore)) {
                $files[] = $file;
            }
        }
        
        closedir($handle);
        
        return $files;
    }

    /**
     * Deletes the active file.
     * @return boolean
     */
    public function delete() {
        $path = $this->path . $this->file;
        $this->file = null;
        return unlink($path);
    }
    
    /**
     * Indicates if the new message will be appended or replace the previous message. Chainable.
     * @param boolean $isAppend true if the message will be appended.
     * @return object
     */
    public function isAppend($isAppend) {
        $this->isAppend = $isAppend;
        return $this;
    }
    
    /**
     * Checks if the file exists.
     * @return boolean
     */
    public function exists() {
        //echo '<pre>', $this->path . $this->file, '</pre>';
        return file_exists($this->path . $this->file);
    }

    /**
     * Returns the size of the file.
     * @return int
     */
    public function size() {
        return filesize($this->path . $this->file);
    }
    
    /**
     * Writes a message in the file.
     * @param string $txt message.
     * @return boolean
     */
    public function write($txt) {
        $file = $this->path . $this->file;
        
        if (file_exists($file) && !is_writable($file)) {
            return false;
        }
        
        if ($this->isAppend) {
            return file_put_contents($file, $txt, FILE_APPEND | LOCK_EX);
        }
        
        return file_put_contents($file, $txt);
    }
    
    /**
     * Reads the file.
     * @return string
     */
    public function readAll() {
        $file = $this->path . $this->file;
        
        if (!file_exists($file) || !is_readable($file)) {
            return false;
        }
        
        return file_get_contents($file);
    }
    
    /**
     * Reads the file as array.
     * @return array
     */
    public function readArray() {
        $file = $this->path.$this->file;
        
        if (!file_exists($file) || !is_readable($file)) {
            return false;
        }
        
        return file($file);
    }
}