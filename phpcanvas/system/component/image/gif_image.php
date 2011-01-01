<?php
class GifImage extends AbstractImage {
    protected $fileSource;
    protected $type = IMAGETYPE_GIF;
    
    public function __construct($wild, $height = 100) {
        if (is_int($wild)) {
            parent::__construct($wild, $height);
        } elseif (is_object($wild) && method_exists($wild, 'getImage')) {
            $this->image = $wild->getImage();
        } else {
            $this->fileSource = $wild;
            $this->image = imagecreatefromgif($wild);
        }
    }
    
    public function out($filename = null) {
        imagegif($this->image, $filename);
    }
}