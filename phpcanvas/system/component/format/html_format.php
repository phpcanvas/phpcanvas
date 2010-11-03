<?php
/**
 * Renders the output in HTML format.
 * Other file formats can also be use as long as it has a (.htm) extension and is in the View folder.
 * @author Gian Carlo Val Ebao
 * @version 1.0.2
 * @package PHPCanvas
 * @subpackage Format
 */

/**
 * Renders the output in HTML format.
 * Other file formats can also be use as long as it has a (.htm) extension and is in the View folder.
 * @author Gian Carlo Val Ebao
 * @version 1.0.2
 * @package PHPCanvas
 * @subpackage Format
 */
class HtmlFormat extends AbstractFormat {

    /**
     * Sends the required raw HTTP header(s).
     */
    public function header() {
        header('Content-type: text/html');
        header('Connection: Keep-Alive');
        //header('Cache-Control: public, max-age=31536000');
        //header('Last-Modified: ' . date('r'));
        //header('Accept-Encoding: gzip, deflate');
        //header('Content-Encoding: gzip');
    }

    /**
     * Integrates the view variables with the html file.
     * @param array $applicationdata_x Array of keys and values to be interpreted.
     * @param string $applicationfile_x file to be interpreted.
     * @param boolean $iscompressed_x <b>Optional</b>. Compresses the output if <b>TRUE</b>.
     */
    public function build($applicationdata_x, $applicationfile_x, $iscompressed_x = true) {
        extract($applicationdata_x, EXTR_SKIP);
        unset($applicationdata_x);
        
        ob_start();
            if (!file_exists($applicationfile_x . '.htm')) {
                trigger_error("Cannot locate $applicationfile_x.htm in 'VIEW'.", E_USER_ERROR);
                return false;
            }
            include $applicationfile_x . '.htm';
            $contents_x = ob_get_contents();
        ob_end_clean();
        
        return  !$iscompressed_x ? $contents_x: $this->xmlCompress($contents_x);
    }
}