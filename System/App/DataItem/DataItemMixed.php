<?php

namespace Be\System\App\DataItem;


use Be\System\Service\ServiceException;

/**
 * 应用配置项 哈希 键值对
 */
class DataItemMixed extends Driver
{

    /**
     * 构造函数
     *
     * @param string $name 键名
     * @param mixed $value 值
     * @param array $params 注解参数
     */
    public function __construct($name, $value, $params = array())
    {
        parent::__construct($name, $value, $params);

        $this->value = json_encode($this->value);

        if (!isset($this->ui['textarea'][':autosize'])) {
            $this->ui['textarea'][':autosize'] = '{minRows:3,maxRows:10}';
        }

        if (!isset($this->ui['textarea']['v-decorator'])) {
            $this->ui['textarea']['v-decorator'] = '[\''.$name.'\']';
        }
    }

    /*
    function isJsonString(str) {
        try {
            if (typeof JSON.parse(str) == "object") {
                return true;
            }
        } catch(e) {
        }
        return false;
    */

    /**
     * 编辑
     *
     * @return string | array
     */
    public function getEditHtml()
    {
        $html = '<a-form-item';
        foreach ($this->ui['form-item'] as $k => $v) {
            if ($v === null) {
                $html .= ' '.$k;
            } else {
                $html .= ' '.$k.'="' . $v . '"';
            }
        }
        $html .= '>';
        $html .= '<a-textarea';
        //$html .= ' v-model="formData.' . $this->name . '"';
        if (isset($this->ui['textarea'])) {
            foreach ($this->ui['textarea'] as $k => $v) {
                if ($v === null) {
                    $html .= ' ' . $k;
                } else {
                    $html .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        $html .= '>';
        $html .= '</a-textarea>';
        $html .= '</a-form-item>';
        return $html;
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

            $newValue =  $data[$this->name];
            $newValue =  htmlspecialchars_decode($newValue);

            $newValue = json_decode($newValue, true);
            if (NULL === $newValue) {
                throw new ServiceException('参数 ' . $this->label . ' (' . $this->name . ') 数据格式非有效的 JSON！');
            }

            $this->newValue = $newValue;
        }
    }


}
