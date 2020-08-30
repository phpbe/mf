<?php

namespace Be\Plugin\Form\Item;

use Be\System\Exception\PluginException;
use Be\System\Be;
use Be\Util\FileSystem\FileSize;

/**
 * 表单项 整型
 */
class FormItemFile extends FormItem
{

    protected $path = ''; // 保存路径
    protected $maxSizeInt = 0; // 最大尺寸（整型字节数）
    protected $maxSize = ''; // 最大尺寸（字符类型）
    protected $allowUploadFileTypes = []; // 允许上传的文件类型

    /**
     * 构造函数
     *
     * @param array $params 参数
     * @throws PluginException
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        if (!isset($params['path'])) {
            throw new PluginException('参数' . $this->label . ' ('.$this->name.') 须指定保存路径（path）');
        }
        $this->path = $params['path'];

        if (isset($params['description'])) {
            $this->description = $params['description'];
        }

        $maxSize = null;
        if (isset($params['maxSize'])) {
            $maxSize = $params['maxSize'];
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
        if (isset($params['allowUploadFileTypes']) && is_array($params['allowUploadFileTypes'])) {
            $this->allowUploadFileTypes = $params['allowUploadFileTypes'];
        } else {
            $this->allowUploadFileTypes = Be::getConfig('System.System')->allowUploadFileTypes;
        }

        if (!$this->description) {
            $this->description = '格式：'. implode(', ', $this->allowUploadFileTypes) .'，小于 ' . $this->maxSize;
        }

        if (!isset($this->ui['upload']['accept'])) {
            $this->ui['upload']['accept'] = implode(',', $this->allowUploadFileTypes);
        }

        if (!isset($this->ui['upload'][':on-success'])) {
            $this->ui['upload'][':on-success'] = 'formItemFile_' . $this->name . '_onSuccess';
        }

        if (!isset($this->ui['upload'][':before-upload'])) {
            $this->ui['upload'][':before-upload'] = 'formItemFile_' . $this->name . '_beforeUpload';
        }

        if (!isset($this->ui['upload'][':on-error'])) {
            $this->ui['upload'][':on-error'] = 'formItemFile_onError';
        }

        if (!isset($this->ui['upload'][':file-list'])) {
            $this->ui['upload'][':file-list'] = 'form.'.$this->name.'.fileList';
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
            $this->ui['upload']['action'] = beUrl('System.Plugin.uploadFile');;
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

        $html .= '<a v-if="formData.' . $this->name . '" :href="form.' . $this->name . '.url" target="_blank">{{formData.' . $this->name . '}}</a>';
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
        $html .= '<el-button size="small" type="primary"><i class="el-icon-upload2"></i> 选择文件</el-button>';
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
            'form' => [
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
            'formItemFile_' . $this->name . '_beforeUpload' => 'function(file) {
                if (file.size > '. $this->maxSizeInt.'){
                    this.$message.error("'.$this->label.' 文件尺寸须小于 '.$this->maxSize.'！");
                    return false;
                }
                return true;
            }',
            'formItemFile_' . $this->name . '_onSuccess' => 'function (response, file, fileList) {
                if (response.success) {
                    this.$message.success(response.message);
                    this.form.'.$this->name.'.url = response.url;
                    this.formData.'.$this->name.' = response.newValue;
                } else {
                    this.$message.error(response.message);
                }
                this.form.'.$this->name.'.fileList = [];
            }',
            'formItemFile_onError' => 'function(){
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
