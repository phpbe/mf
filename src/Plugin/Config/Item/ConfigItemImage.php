<?php

namespace Be\Plugin\Config\Item;

use Be\System\Annotation\BeConfigItem;
use Be\System\Exception\PluginException;
use Be\System\Be;
use Be\Util\FileSystem\FileSize;

/**
 * 应用配置项 图像
 */
class ConfigItemImage extends ConfigItem
{

    public $path = ''; // 保存路径
    public $maxSizeInt = 0; // 最大尺寸（整型字节数）
    public $maxSize = ''; // 最大尺寸（字符类型）
    public $allowUploadImageTypes = []; // 允许上传的图像类型
    public $maxWidth = 0;
    public $maxHeight = 0;

    /**
     * 构造函数
     *
     * @param string $name 键名
     * @param mixed $value 值
     * @param BeConfigItem $annotation 注解参数
     * @throws PluginException
     */
    public function __construct($name, $value, $annotation)
    {
        parent::__construct($name, $value, $annotation);

        if (!isset($annotation->path)) {
            throw new PluginException('参数' . $this->label . ' ('.$this->name.') 须指定保存路径（path）');
        }
        $this->path = $annotation->path;

        if (isset($annotation->description)) {
            $this->description = $annotation->description;
        }

        $maxSize = null;
        if (isset($annotation->maxSize)) {
            $maxSize = $annotation->maxSize;
        } else {
            $maxSize = min(ini_get('upload_max_filesize'), ini_get('post_max_size'));
        }

        if (is_numeric($maxSize)) {
            $this->maxSizeInt = $maxSize;
            $this->maxSize = FileSize::int2String($maxSize);
        } else {
            $this->maxSizeInt = FileSize::string2Int($maxSize);
            $this->maxSize = $maxSize;
        }

        // 最大宽度
        if (isset($annotation->maxWidth) && is_numeric($annotation->maxWidth) && $annotation->maxWidth > 0) {
            $this->maxWidth = $annotation->maxWidth;
        }

        // 最大高度
        if (isset($annotation->maxHeight) && is_numeric($annotation->maxHeight) && $annotation->maxHeight > 0) {
            $this->maxHeight = $annotation->maxHeight;
        }

        // 允许上传的图像类型
        if (isset($annotation->allowUploadImageTypes) && is_array($annotation->allowUploadImageTypes)) {
            $this->allowUploadImageTypes = $annotation->allowUploadImageTypes;
        } else {
            $this->allowUploadImageTypes = Be::getConfig('System.System')->allowUploadImageTypes;
        }

        if (!$this->description) {
            $this->description = '格式：'. implode(', ', $this->allowUploadImageTypes) .'，小于 ' . $this->maxSize;
        }

        if (!isset($this->ui['upload']['accept'])) {
            $this->ui['upload']['accept'] = implode(',', $this->allowUploadImageTypes);
        }

        if (!isset($this->ui['upload'][':on-success'])) {
            $this->ui['upload'][':on-success'] = 'configItemImage_' . $this->name . '_onSuccess';
        }

        if (!isset($this->ui['upload'][':before-upload'])) {
            $this->ui['upload'][':before-upload'] = 'configItemImage_' . $this->name . '_beforeUpload';
        }

        if (!isset($this->ui['upload'][':on-error'])) {
            $this->ui['upload'][':on-error'] = 'configItemImage_onError';
        }

        if (!isset($this->ui['upload'][':file-list'])) {
            $this->ui['upload'][':file-list'] = 'config.'.$this->name.'.fileList';
        }

        if (!isset($this->ui['upload']['name'])) {
            $this->ui['upload']['name'] = $this->name;
        }

        if (!isset($this->ui['upload']['limit'])) {
            $this->ui['upload']['limit'] = 1;
        }

        $this->ui['upload']['v-model'] = 'formData.' . $this->name;
    }

    /**
     * 获取html内容
     *
     * @return string
     */
    public function getHtml()
    {
        if (!isset($this->ui['upload']['action'])) {
            $this->ui['upload']['action'] = beUrl('System.Config.uploadImage', [
                'appName' => $this->appName,
                'configName' => $this->configName,
                'itemName' => $this->name
            ]);;
        }

        $html = '<el-form-item';
        foreach ($this->ui['form-item'] as $k => $v) {
            if ($v === null) {
                $html .= ' ' . $k;
            } else {
                $html .= ' ' . $k . '="' . $v . '"';
            }
        }
        $html .= '>';

        $html .= '<img v-if="formData.' . $this->name . '" :src="config.' . $this->name . '.url" alt="'.$this->label.'" style="max-width:120px;" />';

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
        $html .= '<el-button size="small" type="primary"><i class="el-icon-upload2"></i> 选择图像文件</el-button>';
        $html .= '<div class="el-upload__tip" slot="tip">'.$this->description.'</div>';
        $html .= '</el-upload>';
        $html .= '</el-form-item>';
        return $html;
    }

    /**
     * 获取 vue data
     *
     * @return false | array
     */
    public function getVueData()
    {
        return [
            'config' => [
                $this->name => [
                    'url' => Be::getRuntime()->getDataUrl() . $this->path . $this->value,
                    'fileList' => []
                ]
            ]
        ];
    }


    /**
     * 获取 vue 方法
     *
     * @return false | array
     */
    public function getVueMethods()
    {
        return [
            'configItemImage_' . $this->name . '_beforeUpload' => 'function(file) {
                if (file.size > '. $this->maxSizeInt.'){
                    this.$message.error("'.$this->label.' 图像尺寸须小于 '.$this->maxSize.'！");
                    return false;
                }
                return true;
            }',
            'configItemImage_' . $this->name . '_onSuccess' => 'function (response, file, fileList) {
                if (response.success) {
                    this.$message.success(response.message);
                    this.config.'.$this->name.'.url = response.url;
                    this.formData.'.$this->name.' = response.newValue;
                } else {
                    this.$message.error(response.message);
                }
                this.config.'.$this->name.'.fileList = [];
            }',
            'configItemImage_onError' => 'function(){
                this.$message.error("上传失败，请重新上传");
            }',
        ];

    }


    /**
     * 提交处理
     *
     * @param $data
     * @throws PluginException
     */
    public function submit($data)
    {
        if (isset($data[$this->name])) {
            $newValue = $data[$this->name];
            $newValue = htmlspecialchars_decode($newValue);

            if ($newValue != $this->value) {
                $lastPath = Be::getRuntime()->getDataPath() . $this->path . $this->value;
                @unlink($lastPath);
            }

            $this->newValue = $newValue;
        }
    }


}
