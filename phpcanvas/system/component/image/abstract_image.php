<?php
class AbstractImage {
    protected $image;
    protected $type;
    
    public static function getInstance($wild = 100, $width = 100) {
        $info = getimagesize($filename);
        switch ($info[2]) {
        case IMAGETYPE_JPEG:
            return new JpegImage($wild, $width);

        case IMAGETYPE_GIF:
            return new GifImage($wild, $width);

        case IMAGETYPE_PNG:
            return new PngImage($wild, $width);
            
        default:
            return false;
        }
    }
    
    public final function getImage() {
        return $this->image;
    }
    
    public function __construct($height, $width) {
        $this->image = imagecreate($height, $width);
    }

    public function getWidth() {
        return imagesx($this->image);
    }

    public function getHeight() {
        return imagesy($this->image);
    }

    public function resizeToHeight($height) {
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width, $height);
    }

    public function resizeToWidth($width) {
        $ratio = $width / $this->getWidth();
        $height = $this->getHeight() * $ratio;
        $this->resize($width, $height);
    }

    public function scale($scale) {
        $width = $this->getWidth() * $scale/100;
        $height = $this->getHeight() * $scale/100; 
        $this->resize($width,$height);
    }

    public function resize($width,$height) {
        $new_image = imagecreatetruecolor($width, $height);
        imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        $this->image = $new_image;   
    }

    public function merge($imgObject) {
        $watermark = $imgObject->getImage(); 
        
        $imagewidth = ; 
        $imageheight = ;  
        
        $watermarkwidth =  imagesx($watermark); 
        $watermarkheight =  imagesy($watermark);
        
        $startwidth = (($this->getWidth() - $watermarkwidth)/2); 
        $startheight = (($this->getHeight() - $watermarkheight)/2); 
        imagecopy($image, $watermark,  $startwidth, $startheight, 0, 0, $watermarkwidth, $watermarkheight); 
    }
    
    public function __deconstruct() {
        imagedestroy($this->image);
    }
}