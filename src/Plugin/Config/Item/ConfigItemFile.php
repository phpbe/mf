<?php

namespace Be\Plugin\Config\Item;


use Be\System\Annotation\BeConfigItem;
use Be\System\Exception\PluginException;
use Be\System\Be;
use Be\Util\FileSystem\FileSize;

/**
 * 应用配置项 整型
 */
class ConfigItemFile extends ConfigItem
{

    public $path = ''; // 保存路径
    public $maxSizeInt = 0; // 最大尺寸（整型字节数）
    public $maxSize = ''; // 最大尺寸（字符类型）
    public $allowUploadFileTypes = []; // 允许上传的文件类型

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

        // 允许上传的文件类型
        if (isset($annotation->allowUploadFileTypes) && is_array($annotation->allowUploadFileTypes)) {
            $this->allowUploadFileTypes = $annotation->allowUploadFileTypes;
        } else {
            $this->allowUploadFileTypes = Be::getConfig('System.System')->allowUploadFileTypes;
        }

        if (!$this->description) {
            $this->description = '格式：'. implode(', ', $this->allowUploadFileTypes) .'，小于 ' . $this->maxSize;
        }

        if ($this->description) {
            if (!isset($this->ui['form-item']['help'])) {
                $this->ui['form-item']['help'] = htmlspecialchars($this->description);
            }
        }

        if (!isset($this->ui['upload']['accept'])) {
            $this->ui['upload']['accept'] = '.' . implode(', ', $this->allowUploadFileTypes);
        }

        if (!isset($this->ui['upload'][':show-upload-list'])) {
            $this->ui['upload'][':show-upload-list'] = 'false';
        }

        if (!isset($this->ui['upload'][':before-upload'])) {
            $this->ui['upload'][':beforeUpload'] = 'configItemFileBeforeUpload';
        }

        if (!isset($this->ui['upload']['@change'])) {
            $this->ui['upload']['@change'] = 'configItemFileChange';
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
        $this->ui['upload']['action'] = beUrl('System.Config.uploadFile', [
            'appName' => $this->appName,
            'configName' => $this->configName,
            'itemName' => $this->name
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

        $html .= '<a v-if="config.' . $this->name . '.fileName" :href="config.' . $this->name . '.url">{{config.' . $this->name . '.newValue}}</a>';
        $html .= '<div v-else>';
        $html .= '<el-button><el-icon type="upload" ></el-icon> 选择文件</el-button>';
        $html .= '</div>';

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
                    'loading' => false,
                    'fileName' => '',
                    'url' => '',
                    'newValue' => ''
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
            'configItemFileChange' => 'function (info) {
                  console.log(info);
                if (info.file.status === \'uploading\') {
                    this.'.$this->name.'.loading = true;
                    return;
                }
                
                if (info.file.status === \'done\') {
                    if (info.file.response) {
                        if (info.file.response.success) {
                            this.'.$this->name.'.url = info.file.response.url;
                            this.'.$this->name.'.newValue = info.file.response.newValue;
                            this.'.$this->name.'.loading = false;
                        } else {
                            this.$message.error(info.file.response.message);
                        }
                    }
                }
            }',
            'configItemFileBeforeUpload' => 'function(file) {
                if (file.size > '. $this->maxSizeInt.'){
                    this.$message.error(\''.$this->label.' 文件尺寸须小于 '.$this->maxSize.'！\');
                }
                return true;
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
