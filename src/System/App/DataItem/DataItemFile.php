<?php

namespace Be\System\App\DataItem;


use Be\System\Exception\ServiceException;
use Be\System\Be;
use Be\Util\Fso\File;

/**
 * 应用配置项 整型
 */
class DataItemFile extends Driver
{

    /**
     * 构造函数
     *
     * @param string $name 键名
     * @param mixed $value 值
     * @param array $params 注解参数
     * @throws ServiceException
     */
    public function __construct($name, $value, $params = array())
    {
        parent::__construct($name, $value, $params);

        if (!isset($this->option['path'])) {
            throw new ServiceException('参数' . $this->label . ' ('.$this->name.') 须指定保存路径（path）');
        }

        // 最大尺寸
        if (!isset($this->option['maxSize'])) {
            $this->option['maxSize'] = min(ini_get('upload_max_filesize'), ini_get('post_max_size'));
        }

        if (is_numeric($this->option['maxSize'])) {
            $this->option['maxSizeInt'] = $this->option['maxSize'];
        } else {
            $this->option['maxSizeInt'] = File::fileSizeString2Int($this->option['maxSize']);
        }
        $this->option['maxSize'] = File::fileSizeInt2String($this->option['maxSizeInt']);

        // 允许上传的文件类型
        if (!isset($this->option['allowUploadFileTypes']) || !is_array($this->option['allowUploadFileTypes'])) {
            $this->option['allowUploadFileTypes'] = Be::getConfig('System.System')->allowUploadFileTypes;
        }

        if (!$this->description) {
            $this->description = '格式：'. implode(', ', $this->option['allowUploadFileTypes']) .'，小于 ' . $this->option['maxSize'];
        }

        if ($this->description) {
            if (!isset($this->ui['form-item']['help'])) {
                $this->ui['form-item']['help'] = htmlspecialchars($this->description);
            }
        }

        if (!isset($this->ui['upload']['v-decorator'])) {
            $this->ui['upload']['v-decorator'] = '[\'' . $name . '\', {getValueFromEvent: function(e){ if(e.file.response) { return e.file.response.newValue} else {return \''.$value.'\';} }}]';
        }

        if (!isset($this->ui['upload']['accept'])) {
            $this->ui['upload']['accept'] = '.' . implode(',.', $this->option['allowUploadFileTypes']);
        }

        if (!isset($this->ui['upload'][':show-upload-list'])) {
            $this->ui['upload'][':show-upload-list'] = 'false';
        }

        if (!isset($this->ui['upload'][':before-upload'])) {
            $this->ui['upload'][':beforeUpload'] = $this->name . '_beforeUpload';
        }

        if (!isset($this->ui['upload']['@change'])) {
            $this->ui['upload']['@change'] = $this->name . '_handleChange';
        }

    }




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


    /**
     * 编辑
     *
     * @return false | array
     */
    public function getEditData()
    {
        return [
            'loading' => false,
            'newValue' => $this->value,
            'url' => Be::getRuntime()->getDataUrl() . $this->option['path'] . $this->value
        ];
    }


    /**
     * 编辑
     *
     * @return false | array
     */
    public function getEditMethods()
    {
        return [
            $this->name . '_handleChange: function (info) {

                if (info.file.status === \'uploading\') {
                    this.'.$this->name.'.loading = true;
                    return;
                }
                
                if (info.file.status === \'done\') {
                    var _this = this;
                    if (info.file.response) {
                        if (info.file.response.success) {
                            _this.'.$this->name.'.url = info.file.response.url;
                            _this.'.$this->name.'.newValue = info.file.response.newValue;
                            _this.'.$this->name.'.loading = false;
                        } else {
                            this.$message.error(info.file.response.message);
                        }
                    }
                }
            }',


            $this->name . '_beforeUpload: function(file) {
                if (file.size > '. $this->option['maxSizeInt'].'){
                    this.$message.error(\''.$this->label.' 文件尺寸须小于 '.$this->option['maxSize'].'！\');
                }
                return true;
            }'

        ];
    }


    /**
     * 提交处理
     *
     * @param $data
     * @throws ServiceException
     */
    public function submit($data)
    {
        if (isset($data[$this->name])) {
            $newValue = $data[$this->name];
            $newValue = htmlspecialchars_decode($newValue);

            if ($newValue != $this->value) {
                $lastPath = Be::getRuntime()->getDataPath() . $this->option['path'] . $this->value;
                @unlink($lastPath);
            }

            $this->newValue = $newValue;
        }
    }


}
