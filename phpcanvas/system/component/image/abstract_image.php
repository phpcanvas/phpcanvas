<?php
class AbstractImage {
    protected $image;
    protected $type;
    
    public static function create($wild = 100, $height = 100, $type = null) {
        if (empty($type)) {
            $info = getimagesize($wild);
            $type = $info[2];
        }
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                return new JpegImage($wild, $height);

            case IMAGETYPE_GIF:
                return new GifImage($wild, $height);

            case IMAGETYPE_PNG:
                return new PngImage($wild, $height);
                
            default:
                return false;
        }
    }
    
    public final function getImage() {
        return $this->image;
    }
    
    public function __construct($width, $height) {
        $this->image = imagecreatetruecolor($width, $height);
    }

    public function getWidth() {
        return imagesx($this->image);
    }

    public function getHeight() {
        return imagesy($this->image);
    }

    public function resizeToHeight($height) {
        $ratio = (int)$height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width, $height);
        return $this;
    }

    public function resizeToWidth($width) {
        $ratio = $width / $this->getWidth();
        $height = $this->getHeight() * $ratio;
        $this->resize($width, $height);
        return $this;
    }

    public function scale($scale) {
        $width = $this->getWidth() * $scale / 100;
        $height = $this->getHeight() * $scale / 100; 
        $this->resize($width, $height);
        return $this;
    }

    public function resize($width, $height) {
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image,true);
        $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
        
        imagefilledrectangle($image, 0, 0, $width, $height, $transparent);
        imagecopyresampled($image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        
        $this->image = $image;   
    }
    
    public function fit($image, $ratio = 1) {
        $height = $this->getHeight();
        $width = $this->getWidth();
        
        $imageHeight = $image->getHeight();
        $imageWidth = $image->getWidth();
        
        if ($width > $height && $imageHeight > $imageWidth) {
            $image->resizeToHeight($height / $ratio);
        } else {
            $image->resizeToWidth($width / $ratio);
        }
        
        return $this;
    }
    
    public function cloneCanvas() {
        $width = $this->getWidth(); 
        $height = $this->getHeight();
        return self::getInstance($width, $height, $this->type);
    }
    
    public function merge($imgObject, $posx = 0, $posy = 0, $alpha = 50) {
        $watermark = $imgObject->getImage(); 
        $alpha /= 100;
        
        $image = $this->cloneCanvas();
        $imageWidth = $image->getWidth(); 
        $imageHeight = $image->getHeight();
        
        $watermarkWidth = $imgObject->getWidth(); 
        $watermarkHeight = $imgObject->getHeight();
        
        if ('auto' == $posx) {
            $posx = ($imageWidth - $watermarkWidth) / 2;
        }
        
        if ('auto' == $posy) {
            $posy = ($imageHeight - $watermarkHeight) / 2;
        }

        for( $y = 0; $y < $imageHeight; $y++ ) {
            for( $x = 0, $pixel = null; $x < $imageWidth; $x++, $pixel = null) {
                $wmX = $x - $posx;
                $wmY = $y - $posy;
                
                $iRGB = imagecolorsforindex($this->image, imagecolorat($this->image, $x, $y));
                
                if ($wmX >= 0 && $wmX < $watermarkWidth && $wmY >= 0 && $wmY < $watermarkHeight) {
                    $wmRGB = imagecolorsforindex($watermark, imagecolorat($watermark, $wmX, $wmY));

                    $wmAlpha = round(((127 - $wmRGB['alpha']) / 127), 2);
                    $wmAlpha = $wmAlpha * $alpha;
                
                    $iR = self::getAveColor($iRGB['red'], $wmRGB['red'], $wmAlpha);
                    $iG = self::getAveColor($iRGB['green'], $wmRGB['green'], $wmAlpha);
                    $iB = self::getAveColor($iRGB['blue'], $wmRGB['blue'], $wmAlpha);
                    
                    $pixel = $image->getClosestColor($iR, $iG, $iB);
                } else {
                    $pixel = imagecolorat($this->image, $x, $y);
                }
        
                imagesetpixel($image->getImage(), $x, $y, $pixel);
            }
        }
        
        $this->image = $image->getImage();
    }
    
    /**
     * average two colors given an alpha
     **/
    private static function getAveColor($colorA, $colorB, $alpha = 100) {
        return round((($colorA * (1 - $alpha)) + ($colorB * $alpha)));
    }
        
    /**
     * Returns the closest pallette color match for RGB values
     **/
    public function getClosestColor($r, $g, $b) {
        $c = imagecolorexact($this->image, $r, $g, $b);
        if ($c != -1) {
            return $c;
        }
        
        $c = imagecolorallocate($this->image, $r, $g, $b);
        if ($c != -1) {
            return $c;
        }
        
        return imagecolorclosest($this->image, $r, $g, $b);
    } # EBD _get_image_color()
    
    public function __deconstruct() {
        imagedestroy($this->image);
    }
}