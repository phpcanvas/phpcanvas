<?php
class JpegImage extends AbstractImage {
    protected $fileSource;
    protected $type = IMAGETYPE_JPEG;
    
    public function __construct($wild, $width = 100) {
        if (is_int($wild)) {
            parent::__construct($wild, $width);
        } elseif (is_object($wild) && method_exists($wild, 'getImage')) {
            $this->image = $wild->getImage();
        } else {
            $this->fileSource = $wild;
            $this->image = imagecreatefromjpeg($wild);
        }
    }
    
    public function out($compression = 75, $filename= null) {
        imagejpeg($this->image, $filename, $compression);
    }
}