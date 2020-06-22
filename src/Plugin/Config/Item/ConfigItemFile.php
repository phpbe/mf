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
        $this->ui['upload']['action'] = beUrl('System.Config.uploadImage', [
            '_app' => $this->config['app'],
            '_config' => $this->config['name'],
            '_item' => $this->name
        ]);

        $html = '<el-form-item';
        foreach ($this->ui['form-item'] as $k => $v) {
            if ($v === null) {
                $html .= ' ' . $k;
            } else {
                $html .= ' ' . $k . '="' . $v . '"';
            }
        }
        $html .= '>';

        $html .= '<el-upload';
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
        $html .= '<el-button><el-icon type="upload" ></el-icon> 选择文件</el-button>';
        $html .= '</div>';

        $html .= '</el-upload>';
        $html .= '</el-form-item>';
        return $html;
    }



}
