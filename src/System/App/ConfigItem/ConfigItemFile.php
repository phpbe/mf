<?php

namespace Be\System\App\ConfigItem;


use Be\System\App\DataItem\DataItemFile;
use Be\System\Exception\ServiceException;
use Be\System\Be;
use Be\Util\Fso\File;

/**
 * 应用配置项 整型
 */
class ConfigItemFile extends DataItemFile
{
    use Driver;

    /**
     * 编辑
     *
     * @return string | array
     */
    public function getEditHtml()
    {
        $this->ui['upload']['action'] = url('System.Config.uploadImage', [
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

        $html .= '<a v-if="' . $this->name . '.fileName" :href="' . $this->name . '.url">{{' . $this->name . '.newValue}}</a>';
        $html .= '<div v-else>';
        $html .= '<a-button><a-icon type="upload" ></a-icon> 选择文件</a-button>';
        $html .= '</div>';

        $html .= '</a-upload>';
        $html .= '</a-form-item>';
        return $html;
    }



}
