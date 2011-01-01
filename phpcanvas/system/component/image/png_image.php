<?php
class PngImage extends AbstractImage {
    protected $fileSource;
    protected $type = IMAGETYPE_PNG;
    
    public function __construct($wild, $height = 100) {
        if (is_int($wild)) {
            parent::__construct($wild, $height);
        } elseif (is_object($wild) && method_exists($wild, 'getImage')) {
            $this->image = $wild->getImage();
        } else {
            $this->fileSource = $wild;
            $this->image = imagecreatefrompng($wild);
            imagealphablending($this->image, true);
        }
    }
    
    public function out($filename = null) {
        imagepng($this->image, $filename);
    }
}