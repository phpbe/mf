<?php

namespace Be\System\App\TupleItem;

use Be\System\App\DataItem\DataItemImage;
use Be\System\Service\ServiceException;
use Be\System\Be;
use Be\Util\Fso\File;

/**
 * 数据配置项 图像
 */
class TupleItemImage extends DataItemImage
{
    use Driver;


    /**
     * 编辑
     *
     * @return string | array
     */
    public function getEditHtml()
    {
        $this->ui['upload']['action'] = adminUrl('System', 'Config', 'uploadImage', [
            '_app' => $this->config['app'],
            '_config' => $this->config['name'],
            '_item' => $this->name
        ]);

        $html = '<a-form-item';
        foreach ($this->ui['form-item'] as $k => $v) {
            if ($v === null) {
                $html .= ' ' . $k;
            } else {
                $html .= ' ' . $k . '="' . $v . '"';
            }
        }
        $html .= '>';

        $html .= '<a-upload';
        if (isset($this->ui['upload'])) {
            foreach ($this->ui['upload'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';

        $html .= '<img v-if="' . $this->name . '.url" :src="' . $this->name . '.url" alt="'.$this->label.'" style="max-width:120px;" />';
        $html .= '<div v-else>';
        $html .= '<a-button><a-icon type="upload" ></a-icon> 选择图像文件</a-button>';
        $html .= '</div>';

        $html .= '</a-upload>';
        $html .= '</a-form-item>';
        return $html;
    }





}
