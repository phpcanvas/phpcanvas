<?php
/**
 * Extended by all controller classes.
 * Contains methods that help user-defined controllers
 * interact with the rest of the system.
 * @author Gian Carlo Val Ebao
 * @version 1.1.1
 * @package PHPCanvas
 * @subpackage Core
 */

/**
 * Extended by all controller classes.
 * Contains methods that help user-defined controllers
 * interact with the rest of the system.
 * @author Gian Carlo Val Ebao
 * @version 1.1.1
 * @package PHPCanvas
 * @subpackage Core
 */
class ControllerCore {
    /**
     * Contains all values to be displayed to the view component.
     * @access private
     */
    private $data = array();

    /**
     * The method to be executed.
     */
    protected $action = null;
    
    /**
     * URL parameters. Can also be accessed from the parameters to the active method.
     */
    protected $vars = null;
    
    /**
     * Displays the script execution time.
     */
    public $scriptTime = true;
    
    /**
     * View name to be used.
     * If the value is a string, it will output the view in an HTML format.
     * <br>If the value is an array, it will output the VALUE in a format corresponding to the KEY(format => filename). Filename can also be an array of file names to be loaded successively.
     * <br>If the value is null|FALSE|empty_string, it will output the VALUE in a format corresponding to the KEY(format => filename). Filename can also be an array of file names to be loaded successively.
     */
    public $layout = null;

    /**
     * Initializes the controller.
     * @access private
     * @param string $action method to be executed.
     * @param array &$vars URL parameters interpreted as data.
     */
    public function __construct($action, &$vars) {
        $this->action = $action;
        $this->vars = $vars;
    }
    
    /**
     * Exports variables for the VIEW to interpret.
     * @param string|array $key variable name or an array of variable names with their corresponding values.
     * @param mixed $data <b>Default</b>. Value of the variable.
     * @final
     */
    protected final function show($key, $data = null) {
        $key = !is_array($key) ? array($key => $data): $key;
        $this->data = array_merge_recursive_distinct($this->data, $key);
    }

    /**
     * Returns the variables for the VIEW to interpret.
     * @final
     */
    public final function getViewData() {
        return $this->data;
    }
}