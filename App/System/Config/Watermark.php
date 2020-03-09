<?php
namespace App\System\Config;

/**
 * @be-config-label 图片水印
 * @be-config-test adminUrl('System', 'Watermark', 'test')
 */
class Watermark
{
    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemBool
     * @be-config-item-label 是否启用
     */
    public $watermark = true;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 类型
     * @be-config-item-keyValues {"text":"文字","image":"图像"}
     */
    public $type = 'image';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 水印位置
     * @be-config-item-keyValues {"north":"上","northeast":"右上","east":"左","southeast":"右下","south":"下","southwest":"下左","west":"右","northwest":"左上","center":"中"}
     */
    public $position = 'southeast';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 水平偏移像素值
     */
    public $offsetX = -70;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 垂直偏移像素值
     */
    public $offsetY = -70;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemImage
     * @be-config-item-label 图像水印文件
     * @be-config-item-path /System/Watermark/
     */
    public $image = '0.png';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 文印文字
     */
    public $text = 'BE';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 文印文字大小
     * @be-config-item-ui {":min":1}
     */
    public $textSize = 20;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemArrayInt
     * @be-config-item-label 文印文字颜色
     */
    public $textColor = [255, 255, 255];


}
