<?php

namespace Be\System\App\DataItem;


use Be\System\Request;
use Be\System\Service\ServiceException;
use Be\System\Be;
use Be\Util\Fso\File;

/**
 * 应用配置项 图像
 */
class DataItemImage extends Driver
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
        if (!isset($this->option['allowUploadImageTypes']) || !is_array($this->option['allowUploadImageTypes'])) {
            $this->option['allowUploadImageTypes'] = Be::getConfig('System', 'System')->allowUploadImageTypes;
        }
        // 最大宽度
        if (!isset($this->option['maxWidth']) || !is_numeric($this->option['maxWidth']) || $this->option['maxWidth'] < 0) {
            $this->option['maxWidth'] = 0;
        }

        // 最大高度
        if (!isset($this->option['maxHeight']) || !is_numeric($this->option['maxHeight']) || $this->option['maxHeight'] < 0) {
            $this->option['maxHeight'] = 0;
        }

        if (!$this->description) {
            $this->description = '格式：'. implode(', ', $this->option['allowUploadImageTypes']) .'，小于 ' . $this->option['maxSize'];
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
            $this->ui['upload']['accept'] = '.' . implode(',.', $this->option['allowUploadImageTypes']);
        }

        if (!isset($this->ui['upload']['list-type'])) {
            $this->ui['upload']['list-type'] = 'picture-card';
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


    /**
     * 编辑
     *
     * @return false | array
     */
    public function getEditData()
    {
        return [
            'loading' => false,
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
                    
                    /*
                    if(window.FileReader) {  
                        var fr = new FileReader();  
                        fr.addEventListener(\'load\', function (event){
                            _this.'.$this->name.'.url = fr.result;
                            _this.'.$this->name.'.loading = false;
                        });
                        fr.readAsDataURL(info.file.originFileObj);
                    }
                    */
                    
                    if (info.file.response) {
                        if (info.file.response.success) {
                            _this.'.$this->name.'.url = info.file.response.url;
                            _this.'.$this->name.'.loading = false;
                        } else {
                            this.$message.error(info.file.response.message);
                        }
                    }
                }
            }',


            $this->name . '_beforeUpload: function(file) {
                if (file.size > '. $this->option['maxSizeInt'].'){
                    this.$message.error(\''.$this->label.' 图像尺寸须小于 '.$this->option['maxSize'].'！\');
                }
                return true;
            }'

        ];
    }

    /**
     * 提交处理
     *
     * @param $data
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
