<?php

class EzLineChart extends BaseChart
{
    /**
     * @var string 线段类型
     */
    private $lineType;

    /**
     * @var EzDrawerText $title
     */
    private $title;

    /**
     * 设置线段类型-直线
     * @return void
     */
    public function setStrateLineType() {
        $this->lineType = "";
    }

    /**
     * 向图表中追加一条折线
     * @param array  $lineData
     * @param string $color
     * @return void
     */
    public function addLine(array $lineData, $color) {

    }

    public function addTitle(string $title, $color = EzDrawerColorEnum::BLACK, $size = null, $position = EzChartEnum::MIDDLE) {
        $textObj = new EzDrawerText();
        $textObj->text = $title;
        if (is_null($size)) {
            $strLen = strlen($title);
            $min = min($this->drawer->getWidth(), $this->drawer->getHeight());
            $size = floor($min/$strLen);
        }
        $textObj->size = $size;
        $textObj->color = $color;
        list($x, $y) = $this->calcWithPositionDesc($position);
        $textObj->positionX = $x;
        $textObj->positionY = $y;
        $this->title = $textObj;
        return $this;
    }

    private function calcWithPositionDesc($position) {
        return [0, 0];
    }

    protected function preDraw()
    {
        // TODO: Implement preDraw() method.
    }

    public function draw():self {
        $this->preDraw();
        $this->drawer->fillFromName(EzDrawerColorEnum::WHITE);
        $this->drawer->writeText($this->title);
        return $this;
    }

    /*    public function output($filePath = null) {
            // 定义图片的宽度和高度
            $width = 600;
            $height = 400;
            $padding = 50;

            $antiAliasingScale = 2;

            // 获取最大值
            $maxValue = 0;
            foreach ($data as $row) {
                $maxValue = max($maxValue, max($row));
            }

            // 创建图片
            $image = imagecreatetruecolor($width * $antiAliasingScale, $height * $antiAliasingScale);

            // 设置颜色
            $backgroundColor = imagecolorallocate($image, 255, 255, 255);
            $lineColor = imagecolorallocate($image, 0, 0, 255);
            $axisColor = imagecolorallocate($image, 0, 0, 0);
            $textColor = imagecolorallocate($image, 0, 0, 0);
            // 加载字体文件
            $fontFile = $this->getFontFile("DejaVuSansMono_0");
            // 设置标题
            $titleFontSize = 18;
            $titleFontBox = imagettfbbox($titleFontSize * $antiAliasingScale, 0, $fontFile, $title);
            $titleX = $width * $antiAliasingScale / 2 - ($titleFontBox[2] - $titleFontBox[0]) / 2;
            imagettftext($image, $titleFontSize * $antiAliasingScale, 0, $titleX, 25 * $antiAliasingScale, $lineColor, $fontFile, $title);

            // 绘制背景色
            imagefill($image, 0, 0, $backgroundColor);

            // 绘制坐标轴
            imageline($image, $padding, $height - $padding, $width - $padding, $height - $padding, $axisColor);
            imageline($image, $padding, $padding, $padding, $height - $padding, $axisColor);

            // 自动生成 X 轴标签
            $xAxisLabels = [];
            for ($i = 0; $i < count($data); $i++) {
                $xAxisLabels[] = "X" . ($i + 1);
            }

            // 自动生成 Y 轴标签
            $yAxisLabels = [];
            $step = $maxValue / 5;
            for ($i = 0; $i <= 5; $i++) {
                $yAxisLabels[] = intval($i * $step);
            }

            // 绘制 X 轴标签
            for ($i = 0; $i < count($xAxisLabels); $i++) {
                $x = $padding + $i * ($width - 2 * $padding) / (count($xAxisLabels) - 1);
                imagettftext($image, 12, 0, $x - 5, $height - $padding + 25, $textColor, $fontFile, $xAxisLabels[$i]);
            }

            // 绘制 Y 轴标签
            for ($i = 0; $i < count($yAxisLabels); $i++) {
                $y = $height - $padding - $i * ($height - 2 * $padding) / (count($yAxisLabels) - 1);
                imagettftext($image, 12, 0, 10, $y + 5, $textColor, $fontFile, $yAxisLabels[$i]);
            }

            // 计算数据点间的间距
            $x_gap = ($width - 2 * $padding) / (count($data) - 1);
            $y_gap = ($height - 2 * $padding) / $maxValue;

            // 绘制折线图
            for ($i = 0; $i < count($data) - 1; $i++) {
                $x1 = $padding + $i * $x_gap;
                $x2 = $padding + ($i + 1) * $x_gap;

                for ($j = 0; $j < count($data[$i]); $j++) {
                    $y1 = $height - $padding - $data[$i][$j] * $y_gap;
                    $y2 = $height - $padding - $data[$i + 1][$j] * $y_gap;

                    // 设置线条宽度
                    imagesetthickness($image, 2);

                    // 画线
                    imageline($image, $x1, $y1, $x2, $y2, $lineColor);
                }
            }

            imagepng($image, $filePath);
            // 销毁图像释放内存
            imagedestroy($image);
        }*/
}
