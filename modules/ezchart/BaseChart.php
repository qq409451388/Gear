<?php
abstract class BaseChart
{
    /**
     * @var EzDrawer $drawer 绘画器
     */
    protected $drawer;

    /**
     * @var int|double 缩放因子
     */
    protected $antiAliasingScale;

    public function __construct($width, $height, $antiAliasingScale = 1) {
        $this->antiAliasingScale = $antiAliasingScale;
        $this->drawer = $this->createDrawer($width, $height, $this->antiAliasingScale);
    }

    /**
     * 创建一个绘画器
     * @return EzDrawer
     */
    protected function createDrawer(int $width, int $height, $antiAliasingScale = 1) {
        return EzDrawer::create($width, $height, $antiAliasingScale);
    }

    /**
     * draw的前置校验
     * @throws Exception|GearIllegalArgumentException
     */
    abstract protected function preDraw();

    protected function getFontFile(string $fontName = "arial") {
        return $this->drawer->getFontFile($fontName);
    }

    /**
     * 绘制-生成资源对象
     * @return self
     */
    abstract protected function draw():self;

    /**
     * 绘制-生成文件
     * @param string $filePath
     * @return boolean
     */
    public function output(string $filePath = ""):bool {
        return $this->drawer->output($filePath);
    }
}
