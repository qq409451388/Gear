<?php
class EzDrawer
{
    /**
     * @var int 宽度
     */
    private $width;

    /**
     * @var int 高度
     */
    private $height;

    /**
     * @var int|double 缩放因子
     */
    private $antiAliasingScale;

    /**
     * @var GdImage $resource gd资源
     */
    private $resource;

    /**
     * 颜色资源
     * @var array<string, int> $colorResource
     */
    private $colorResource;

    /**
     * 创建一个绘画器
     * @param int $width
     * @param int $height
     * @return EzDrawer
     */
    public static function create(int $width, int $height, int $antiAliasingScale = 1) {
        $obj = new EzDrawer();
        $obj->width = $width;
        $obj->height = $height;
        $obj->antiAliasingScale = $antiAliasingScale;
        $obj->resource = imagecreatetruecolor($width * $antiAliasingScale, $height * $antiAliasingScale);
        return $obj;
    }

    /**
     * 从指定绘画器重置大小
     * @param EzDrawer $sourceDrawer
     * @param int      $width
     * @param int      $height
     * @return EzDrawer
     */
    public static function resizeFromDrawer(EzDrawer $sourceDrawer, int $width, int $height) {
        $newDrawer = EzDrawer::create($width, $height);
        imagecopyresampled($newDrawer->resource, $sourceDrawer->getResource(), 0, 0,0, 0,
            $newDrawer->getWidth(), $newDrawer->getHeight(), $sourceDrawer->getWidth(), $sourceDrawer->getHeight());
        return $newDrawer;
    }

    private function resize($width, $height) {
        return self::resizeFromDrawer($this, $width, $height);
    }

    public function getWidth() {
        return $this->width;
    }

    public function getHeight() {
        return $this->height;
    }

    public function getResource() {
        return $this->resource;
    }

    /**
     * @param $name
     * @param $r
     * @param $g
     * @param $b
     * @return int|null
     */
    public function getColor($name, $r = null, $g = null, $b = null) {
        if (!isset($this->colorResource[$name])) {
            if (isset(EzDrawerColorEnum::RGB_COLORS[$name])) {
                $colorArr = EzDrawerColorEnum::RGB_COLORS[$name];
                $this->colorResource[$name] = $this->getColorRGB($colorArr[0], $colorArr[1], $colorArr[2]);
            } elseif (EzObjectUtils::isAllNotNull($r, $g, $b)) {
                $this->colorResource[$name] = $this->getColorRGB($r, $g, $b);
            }
        }
        return $this->colorResource[$name]??null;
    }

    public function getColorRGB(int $r, int $g, int $b) {
        $resource = imagecolorallocate($this->resource, $r, $g, $b);
        if (is_bool($resource)) {
            return null;
        }
        return $resource;
    }

    /**
     * 获取字体文件
     * @param string $fontName
     * @return string 字体文件地址
     * @throws Exception
     */
    public function getFontFile(string $fontName) {
        $fontFile = PROJECT_PATH."/scripts/files/$fontName.ttf";
        if (!is_file($fontFile)) {
            if (Env::isWin()) {
                $fontFile = "C:/Windows/Fonts/$fontName.ttf";
            } else {
                $fontFile = "/usr/share/fonts/truetype/$fontName.ttf";
            }
            DBC::assertTrue(is_file($fontFile), "[EzChart] font file not found!");
        }
        return $fontFile;
    }

    public function fill(int $colorResource) {
        imagefill($this->resource, 0, 0, $colorResource);
    }

    /**
     * @param $colorName {@see EzDrawerColorEnum}
     * @return void
     */
    public function fillFromName($colorName) {
        $this->fill($this->getColor($colorName));
    }

    public function writeText(EzDrawerText $drawerText) {
        if (EzObjectUtils::isArray($drawerText->color)) {
            $drawerText->color = $this->getColorRGB(...$drawerText->color);
        } elseif (EzObjectUtils::isString($drawerText->color)) {
            $drawerText->color = $this->getColor($drawerText->color);
        }
        imagettftext($this->resource,
            $drawerText->size * $this->antiAliasingScale, 0,
            $drawerText->positionX, $drawerText->positionY, $drawerText->color,
        $this->getFontFile($drawerText->getFont()), $drawerText->text);
    }

    public function output(string $filePath):bool {
        $_this = $this;
        if ($this->antiAliasingScale != 1) {
            $_this = $this->resize($this->width, $this->height);
        }
        $pathInfo = pathinfo($filePath);
        $fileExt = $pathInfo['extension'] ?? "";
        switch ($fileExt) {
            case "jpg":
            case "jpeg":
                return $_this->outputJpg($filePath);
            case "png":
                return $_this->outputPng($filePath);
            default:
                return false;
        }
    }

    public function outputJpg($filePath):bool {
        return imagejpeg($this->resource, $filePath);
    }

    public function outputPng($filePath):bool {
        return imagepng($this->resource, $filePath);
    }

    public function __destory() {
        imagedestroy($this->resource);
    }
}
