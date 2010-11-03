<?php
/**
 * Bootstrap. Combines all required classes and files.
 * @author Gian Carlo Val Ebao
 * @version 1.3.2
 * @package PHPCanvas
 */

$ms = array_sum(explode(' ', microtime())); // setting the start time.

/**
 * Contains all the utilities function for the application.
 */
require 'system/util.php';
/**
 * The Master class.
 * Contains all system-related functions like, class monitoring,
 * program flow and standards maintenance.
 */
require 'system/app.php';

// Contains all application-specific configurations.
App::initialize('application/config.ini');

// Contains URL redirection rules.
App::initialize('application/route.ini', 'route');
// Contains all application-specific database configurations.
App::initialize('application/database.ini', 'database');
// Corrects the configuration if the application is trying to overwrite the system configurations.
App::initialize('system/config.ini');

// Automatically includes the required file when a class is being instantiated
spl_autoload_register(array('App', 'load'));
ini_set('unserialize_callback_func', 'spl_autoload_call');

App::$sysroot = $ini['system_root'];

// For accurate date transactions.
date_default_timezone_set(App::conf('timezone'));
// Activates/Deactivates debug mode.
App::debug();
// Instantiates all objects needed by the Application class.
App::startObjects();
// Overwritting the error handler will the one in App Class.
set_error_handler(array('App', 'errorHandler'));

if (!RestRoute::parse(App::conf('route'))) {
    App::throwError('404');
}
// Everything is OK so far. start executing the method.
$params = RestRoute::$params;
$controller = new RestRoute::$class(RestRoute::$method, $params);

call_user_func_array(array($controller, RestRoute::$method), $params);
        
$layout = $controller->layout;
$viewData = $controller->getViewData();
$scriptTime = $controller->scriptTime;

unset($controller);

// Loading the View module
if (!empty($layout)) {
    // assumes that layout is an HTML format if it's not specified.
    AbstractFormat::factory($layout, $viewData, App::conf('view.compress'));
}

if ($scriptTime) {
    $ms = round((array_sum(explode(' ', microtime())) - $ms) * 1000, 4); // setting the end time.
    echo '<pre>script execution: ', $ms, 'ms</pre>';
}
