<?php
class Cookie {
    private static $name;
    private $id = 'cookie';
    public $isOpener = false;
    
    public static function setName($name) {
        return session_name($name);
    }
    
    public function __construct($id) {
        if (!isset($_SESSION)) {
            session_start();
            $this->isOpener = true;
        }
    }
    
    public function set($value) {
        $_SESSION[$this->id] = $value;
    }
    
    public function get() {
        return $_SESSION[$this->id];
    }
    
    public function __deconstruct() {
        if ($this->isOpener && isset($_SESSION)) {
            session_write_close();
        }
    }
}