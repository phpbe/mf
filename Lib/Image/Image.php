<?php
namespace Be\Lib\Image;

/**
 * 图像处理库
 *
 * @package Be\Lib\Image
 * @author liu12 <i@liu12.com>
 */
class Image
{
    private $handler = null;
    private $imagick = false;
    private $gd = false;

    // 构造函数
    public function __construct()
    {
        if (class_exists('Imagick')) {
            $this->handler = new \Be\Lib\Image\Driver\ImagickImpl();
            $this->imagick = true;
        } else {
            $this->handler = new \Be\Lib\Image\Driver\GdImpl();
            $this->gd = true;
        }
    }

    // 析构函数
    public function __destruct()
    {
        $this->handler = null;
    }

    // 检测当前是否为 imagick 处理器
    public function isImagick()
    {
        return $this->imagick;
    }

    // 检测当前是否为  GD 处理器
    public function isGD()
    {
        return $this->gd;
    }

    // 获取处理器
    public function getHandler()
    {
        return $this->handler;
    }

    public function __call($fn, $args)
    {
        return call_user_func_array(array($this->handler, $fn), $args);
    }

}
