<?php
namespace Be\App\System\Config;

/**
 * @BeConfig("图片水印", test = "return beUrl('System.Watermark.test');")
 */
class Watermark
{
    /**
     * @BeConfigItem("是否启用", driver="ConfigItemSwitch")
     */
    public $watermark = true;

    /**
     * @BeConfigItem("类型",
     *     driver="ConfigItemSelect",
     *     keyValues = "return ['text' => '文字', 'image' => '图像'];"
     * )
     */
    public $type = 'image';

    /**
     * @BeConfigItem("水印位置",
     *     driver="ConfigItemSelect",
     *     keyValues = "return ['north'=>'上','south'=>'下','east'=>'左','west'=>'右','center'=>'中','northwest'=>'左上','southwest'=>'左下','northeast'=>'右上','southeast'=>'右下'];"
     * )
     */
    public $position = 'southeast';

    /**
     * @BeConfigItem("水平偏移像素值", driver="ConfigItemInputNumberInt")
     */
    public $offsetX = -70;

    /**
     * @BeConfigItem("垂直偏移像素值", driver="ConfigItemInputNumberInt")
     */
    public $offsetY = -70;

    /**
     * @BeConfigItem("图像水印文件", driver="ConfigItemFile", path="/System/Watermark/")
     */
    public $image = '0.png';

    /**
     * @BeConfigItem("文印文字", driver="ConfigItemInput")
     */
    public $text = 'BE';

    /**
     * @BeConfigItem("文印文字大小", driver="ConfigItemInputNumberInt", ui="return [':min' => 1];")
     */
    public $textSize = 20;

    /**
     * @BeConfigItem("文印文字颜色", driver="ConfigItemInputTextArea", valueType="array(int)")
     */
    public $textColor = [255, 255, 255];


}
