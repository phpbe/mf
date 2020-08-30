<?php

namespace Be\Plugin\Form\Item;

use Be\System\Annotation\BeConfigItem;
use Be\System\Exception\PluginException;
use Be\System\Be;
use Be\Util\FileSystem\FileSize;

/**
 * 表单项 头像
 */
class FormItemAvatar extends FormItem
{

    public $defaultValue = ''; // 当value为空时的默认值
    public $path = ''; // 保存路径
    public $maxSizeInt = 0; // 最大尺寸（整型字节数）
    public $maxSize = ''; // 最大尺寸（字符类型）
    public $allowUploadImageTypes = []; // 允许上传的图像类型
    public $maxWidth = 0;
    public $maxHeight = 0;

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

        $configSystem = Be::getConfig('System.System');
        $this->maxSize = $configSystem->uploadMaxSize;
        $this->maxSizeInt = FileSize::string2Int($this->maxSize);

        // 允许上传的图像类型
        $this->allowUploadImageTypes = Be::getConfig('System.System')->allowUploadImageTypes;

        // 最大宽度
        if (isset($params['maxWidth']) && is_numeric($params['maxWidth']) && $params['maxWidth'] > 0) {
            $this->maxWidth = $params['maxWidth'];
        }

        // 最大高度
        if (isset($params['maxHeight']) && is_numeric($params['maxHeight']) && $params['maxHeight'] > 0) {
            $this->maxHeight = $params['maxHeight'];
        }

        if (!$this->description) {
            $this->description = '格式：'. implode(', ', $this->allowUploadImageTypes) .'，小于 ' . $this->maxSize;
        }

        if ($this->required) {
            if (!isset($this->ui['form-item'][':rules'])) {
                $this->ui['form-item'][':rules'] = '[{required: true, message: \'请上传'.$this->label.'\', trigger: \'blur\' }]';
            }
        }

        if (!isset($this->ui['avatar']['shape'])) {
            $this->ui['avatar']['shape'] = 'square';
        }

        if (!isset($this->ui['avatar'][':src'])) {
            $this->ui['avatar'][':src'] = 'form.' . $this->name . '.url';
        }

        if (!isset($this->ui['upload']['accept'])) {
            $this->ui['upload']['accept'] = implode(',', $this->allowUploadImageTypes);
        }

        if (!isset($this->ui['upload'][':on-success'])) {
            $this->ui['upload'][':on-success'] = 'formItemImage_' . $this->name . '_onSuccess';
        }

        if (!isset($this->ui['upload'][':before-upload'])) {
            $this->ui['upload'][':before-upload'] = 'formItemImage_' . $this->name . '_beforeUpload';
        }

        if (!isset($this->ui['upload'][':on-error'])) {
            $this->ui['upload'][':on-error'] = 'formItemImage_onError';
        }

        if (!isset($this->ui['upload'][':file-list'])) {
            $this->ui['upload'][':file-list'] = 'form.'.$this->name.'.fileList';
        }

        if (!isset($this->ui['upload'][':data'])) {
            $this->ui['upload'][':data'] = 'form.'.$this->name.'.postData';
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
            $this->ui['upload']['action'] = beUrl('System.Plugin.uploadImage');
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

        $html .= '<el-avatar v-if="form.' . $this->name . '.url"';
        if (isset($this->ui['avatar'])) {
            foreach ($this->ui['avatar'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</el-avatar>';

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
        $html .= '<el-button size="mini" type="primary"><i class="el-icon-upload2"></i> 选择头像文件</el-button>';
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
        $url = '';
        if ($this->value) {
            $url = Be::getRuntime()->getDataUrl() . $this->path . $this->value;
        } else {
            if ($this->defaultValue) {
                $url = $this->defaultValue;
            }
        }

        return [
            'form' => [
                $this->name => [
                    'url' => $url,
                    'fileList' => [],
                    'postData' => [
                        'maxWidth' => $this->maxWidth,
                        'maxHeight' => $this->maxHeight,
                    ],
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
            'formItemImage_' . $this->name . '_beforeUpload' => 'function(file) {
                if (file.size > '. $this->maxSizeInt.'){
                    this.$message.error("'.$this->label.' 图像尺寸须小于 '.$this->maxSize.'！");
                    return false;
                }
                return true;
            }',
            'formItemImage_' . $this->name . '_onSuccess' => 'function (response, file, fileList) {
                if (response.success) {
                    this.form.'.$this->name.'.url = response.url;
                    this.formData.'.$this->name.' = response.newValue;
                } else {
                    this.$message.error(response.message);
                }
                this.form.'.$this->name.'.fileList = [];
            }',
            'formItemImage_onError' => 'function(){
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

            if ($newValue != $this->value && $this->value != '') {
                $lastPath = Be::getRuntime()->getDataPath() . $this->path . $this->value;
                if (file_exists($lastPath)) {
                    @unlink($lastPath);
                }
            }

            $pathDstDir = Be::getRuntime()->getDataPath() . $this->path;
            if (!file_exists($pathDstDir)) {
                mkdir($pathDstDir, 0755, true);
            }

            $pathSrc = Be::getRuntime()->getDataPath() . '/tmp/' . $newValue;
            $pathDst = $pathDstDir . $newValue;
            if (file_exists($pathSrc)) {
                @copy($pathSrc, $pathDst);
                @unlink($pathSrc);
            }

            $this->newValue = $newValue;
        }
    }

}
