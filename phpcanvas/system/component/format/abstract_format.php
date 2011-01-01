<?php
/**
 * Abstract class for rendering output.
 * @author Gian Carlo Val Ebao
 * @version 1.0.2
 * @package PHPCanvas
 * @subpackage Format
 */

/**
 * Abstract class for rendering output.
 * @author Gian Carlo Val Ebao
 * @version 1.0.2
 * @package PHPCanvas
 * @subpackage Format
 */
abstract class AbstractFormat {

    /**
     * Determines which format to use.
     * @param string|array $layout a file or a list of files to output.
     * @param array $data array of variable names with their associated values.
     * @param boolean $isCompressed indicates if the the output will be compressed.
     */
    public static function factory($data, $layout, $isCompressed) {
        $layout = !is_array($layout) ? array('html' => $layout): $layout;
        list($format, $file) = each($layout);
        $format = strtolower($format);
        $view = $format . 'Format';
        $view = new $view();
        $files = empty($file) ? array(''): (!is_array($file) ? array($file): $file);
        
        $view->header();
        
        for ($i = 0, $cnt = count($files); $i < $cnt; $i++) {
            // pumping in the data to the view module.
            echo $view->build($data, $files[$i], $isCompressed);
        }
    }
    
    /**
     * Integrates the view variables with the layout file.
     * @param array $data array of variable names with their associated values.
     * @param string $file a file to output.
     * @return string
     */
    abstract public function build($data, $file);
    
    /**
     * Sends the required raw HTTP header(s).
     */
    public function header() {}

    /**
     * Strips all unecessary characters in an XML.
     * @param string $str xml markup
     * @return string
     */
    protected function xmlCompress($str) {
        $str = explode("\n", $str);
        for ($html = '', $i = 0, $count = count($str); $i < $count; $i++) {
            $html .= ( '>' != substr($html, -1) ? ' ': '') . trim($str[$i]);
        }
        return str_replace(array("\n", "\r", "\0", "\x0B", "\t"), '', trim($html));
    }
}