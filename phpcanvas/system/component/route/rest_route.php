<?php
/**
* Interprets the URL to determine which modules to use.
* @author Gian Carlo Val Ebao
* @package PHPCanvas
* @subpackage Route
* @version 1.0.3
**/

/**
* Interprets the URL to determine which modules to use.
* @author Gian Carlo Val Ebao
* @package PHPCanvas
* @subpackage Route
* @version 1.0.3
**/
class RestRoute {
    /**
    * Parsed class.
    * Value will only be set after 'parse' method was called.
    **/
    public static $class = null;
    
    /**
    * Parsed method.
    * Value will only be set after 'parse' method was called.
    **/
    public static $method = null;
    
    /**
    * Parsed parameters.
    * Value will only be set after 'parse' method was called.
    **/
    public static $params = null;
    
    /**
    * Parses the URL to determine what controller, method and properties will be used.
    * Returns <b>TRUE</b> when no error occured, else <b>FALSE</b>.
    * @static
    * @param array $routes re-routing rule.
    * @return boolean
    **/
    public static function parse($routes = array()) {
        self::$class = null;
        self::$method = null;
        self::$params = null;
    
        $url = strtok($_SERVER['QUERY_STRING'], '&');
        unset($_GET[$url]);
        
        $keys = array_keys($routes);
        sort($keys);
        
        $tmp = strtoupper($url);
        for ($i = count($keys) - 1; 0 <= $i; $i--) {
            if (0 === strpos($tmp, strtoupper($keys[$i]))) {
                $url = $routes[$keys[$i]] . substr($url, strlen($keys[$i]));
                break;
            }
        }
        
        $url = self::applyDefault($url, $routes);
        
        if (!empty($url[0])) {
            $controller = ucfirst(App::toMethodName(array_shift($url))) . 'Controller';
        }
        
        if (empty($controller)) {
            return false;
        }
        
        $defaultAction = App::conf('APPLICATION.default_method');
        $action = empty($url[0]) ? $defaultAction: App::toMethodName($url[0]);
        $allow = array_diff(get_class_methods($controller), get_class_methods(get_parent_class($controller)));

        // try to know which method to use.
        if (in_array($action, $allow) && method_exists($controller, $action)) {
            array_shift($url);
        } elseif (method_exists($controller, $defaultAction)) {
            $action = $defaultAction;
        } else {
            return false;
        }
        
        self::$class = $controller;
        self::$method = $action;
        self::$params = $url;
        
        return true;
    }

    /**
    * Checks if the url is correct and if not, applies <b>__DEFAULT__</b> route.
    * @static
    * @access private
    * @param string $url url.
    * @param array &$routes re-routing rule.
    * @return array|boolean
    **/
    private static function applyDefault($url, &$routes) {
        // try to know which controller to load.
        
        $url = explode('/', $url);
        $controller = !empty($url[0]) ? 
            ucfirst(App::toMethodName($url[0])) . 'Controller': null;
        
        if ((empty($controller) || 
            !file_exists(App::file(App::toFileName($controller), 'controller'))) &&
            !empty($routes['__DEFAULT__'])) {
            
            $default = explode('/', $routes['__DEFAULT__']);
            
            $controller = ucfirst(App::toMethodName($default[0])) . 'Controller';
            
            if (!file_exists(App::file(App::toFileName($controller), 'controller'))) {
                return false;
            }
            
            $url = array_merge($default, $url);
        }
        
        return $url;
    }
}