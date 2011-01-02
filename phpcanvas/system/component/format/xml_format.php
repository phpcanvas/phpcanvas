<?php
/**
 * Renders the output in XML format.
 * Extends PHP's XMLWriter
 * @author Gian Carlo Val Ebao
 * @version 1.0.2
 * @package PHPCanvas
 * @subpackage Format
 */

/**
 * Renders the output in XML format.
 * Extends PHP's XMLWriter
 * @author Gian Carlo Val Ebao
 * @version 1.0.2
 * @package PHPCanvas
 * @subpackage Format
 */
class XmlFormat extends AbstractFormat {
    
    /**
     * Converts an associative array to a DOM tree.
     * @access private
     * @param object &$xml the xml object.
     * @param array &$arr the associative array.
     * $param string $prevElement <b>Optional</b>. Previous element name.
     */
    private function fromArray(&$xml, &$arr, $prevElement = 'item') {
        if (!is_array($arr)) {
            return false;
        }
        foreach ($arr as $index => $element) {
            $k = is_int($index) ? $prevElement: $index;
            
            if ('0' != $index) {
                $xml->startElement($k); 
            }
            
            if (is_array($element)) {
                $this->fromArray($xml, $element, $k); 
            } else {
                $xml->text($element); 
            }
            
            if (!(is_int($index) && $index == (count($arr) - 1))) {
                $xml->endElement();
            }
        }
    }
    
    /**
     * Sends the required raw HTTP header(s).
     */
    public function header() {
        header('Content-type: text/xml');
    }
    
    /**
     * Creates an XML tree of the view data.
     * @param array $data Array of keys and values to be interpreted.
     * @param string $xslt <b>Optional</b>. Path of the XSL template.
     * @param boolean $iscompressed <b>Optional</b>. compresses the output if <b>TRUE</b>.
     */
    public function build($data, $xslt = null, $iscompressed = true) {
        $xml = new XMLWriter();
        
        $xml->openMemory(); 
        $xml->setIndent(true); 
        $xml->setIndentString(' '); 
        $xml->startDocument('1.0', 'UTF-8'); 
        if (!empty($xslt)) {
            $xml->writePi('xml-stylesheet', "type=\"text/xsl\" href=\"$xslt\""); 
        }
        
        $xml->startElement('root');
        $this->fromArray($xml, $data);
        $xml->endDocument();
        
        $contents_x = $xml->outputMemory(); 
        
        return !$iscompressed ? $contents_x: $this->xmlCompress($contents_x);
    }
    

}