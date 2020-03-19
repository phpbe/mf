<?php
namespace Be\App\System\Service;

use Be\System\Be;
use Be\System\RuntimeException;

class Watermark extends \Be\System\Service
{

    public function mark($image)
    {
        $libImage = Be::getLib('Image');
        $libImage->open($image);

        if (!$libImage->isImage()) {
            throw new RuntimeException('不是合法的图片！');
        }

        $width = $libImage->getWidth();
        $height = $libImage->getHeight();

        $configWatermark = Be::getConfig('System.Watermark');

        $x = 0;
        $y = 0;
        switch ($configWatermark->position) {
            case 'north':
                $x = $width / 2 + $configWatermark->offsetX;
                $y = $configWatermark->offsetY;
                break;
            case 'northEast':
                $x = $width + $configWatermark->offsetX;
                $y = $configWatermark->offsetY;
                break;
            case 'east':
                $x = $width + $configWatermark->offsetX;
                $y = $height / 2 + $configWatermark->offsetY;
                break;
            case 'southEast':
                $x = $width + $configWatermark->offsetX;
                $y = $height + $configWatermark->offsetY;
                break;
            case 'south':
                $x = $width / 2 + $configWatermark->offsetX;
                $y = $height + $configWatermark->offsetY;
                break;
            case 'southWest':
                $x = $configWatermark->offsetX;
                $y = $height + $configWatermark->offsetY;
                break;
            case 'west':
                $x = $configWatermark->offsetX;
                $y = $height / 2 + $configWatermark->offsetY;
                break;
            case 'northWest':
                $x = $configWatermark->offsetX;
                $y = $configWatermark->offsetY;
                break;
            case 'center':
                $x = $width / 2 + $configWatermark->offsetX;
                $y = $height / 2 + $configWatermark->offsetY;
                break;
        }

        $x = intval($x);
        $y = intval($y);

        if ($configWatermark->type == 'text') {
            $style = array();
            $style['fontSize'] = $configWatermark->textSize;
            $style['color'] = $configWatermark->textColor;

            // 添加文字水印
            $libImage->text($configWatermark->text, $x, $y, 0, $style);
        } else {

            $watermarkImage = Be::getRuntime()->getDataPath() . '/System/Watermark/' .  $configWatermark->image;
            if (!file_exists($watermarkImage)) {
                $watermarkImage = Be::getRuntime()->getRootPath() . Be::getProperty('App.System')->path . 'Template/System/Watermark/images/watermark.png';
            }

            // 添加图像水印
            $libImage->watermark($watermarkImage, $x, $y);
        }

        $libImage->save($image);

        return true;
    }


}