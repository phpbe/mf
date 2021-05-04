<?php

namespace Be\Mf\Plugin\Form\Item;

use Be\Mf\Be;
use Be\Mf\Plugin\PluginException;

/**
 * 表单项 百度 UEditor 编辑器
 */
class FormItemUEditor extends FormItem
{

    protected $js = []; // 需要引入的 JS 文件
    protected $css = []; // 需要引入的 CSS 文件
    protected $option = []; // 配置项

    /**
     * 构造函数
     *
     * @param array $params 参数
     * @param array $row 数据对象
     * @throws PluginException
     */
    public function __construct($params = [], $row = [])
    {
        parent::__construct($params, $row);

        if ($this->required) {
            if (!isset($this->ui['form-item'][':rules'])) {
                $this->ui['form-item'][':rules'] = '[{required: true, message: \'请输入' . $this->label . '\', trigger: \'blur\' }]';
            }
        }

        $this->js = [
            'lib/codemirror.js',
            'mode/javascript/javascript.js',
        ];

        $this->css = [
            'lib/codemirror.css',
        ];

        $this->option = [
            'theme' => 'default',
            'mode' => 'javascript',
            'lineNumbers' => true,
            'lineWrapping' => true,
        ];

        if (isset($params['js'])) {
            $js = $params['js'];
            if ($js instanceof \Closure) {
                $js = $js($row);
            }

            if (is_array($js)) {
                $this->js = array_merge($this->js, $js);
            }
        }

        if (isset($params['css'])) {
            $css = $params['css'];
            if ($css instanceof \Closure) {
                $css = $css($row);
            }

            if (is_array($css)) {
                $this->css = array_merge($this->css, $css);
            }
        }

        if (isset($params['option'])) {
            $option = $params['option'];
            if ($option instanceof \Closure) {
                $option = $option($row);
            }

            if (is_array($option)) {
                $this->option = array_merge($this->option, $option);
            }
        }

    }

    /**
     * 获取需要引入的 JS 文件
     *
     * @return false | array
     */
    public function getJs()
    {
        $baseUrl = Be::getProperty('Plugin.Form')->getUrl() . '/Template/codemirror-5.57.0/';
        $js = [];
        foreach ($this->js as $x) {
            $js[] = $baseUrl . $x;
        }

        return $js;
    }

    /**
     * 获取需要引入的 CSS 文件
     *
     * @return false | array
     */
    public function getCss()
    {
        $baseUrl = Be::getProperty('Plugin.Form')->getUrl() . '/Template/codemirror-5.57.0/';
        $css = [];
        foreach ($this->css as $x) {
            $css[] = $baseUrl . $x;
        }

        return $css;
    }

    /**
     * 获取html内容
     *
     * @return string
     */
    public function getHtml()
    {
        $html = '<el-form-item';
        foreach ($this->ui['form-item'] as $k => $v) {
            if ($v === null) {
                $html .= ' ' . $k;
            } else {
                $html .= ' ' . $k . '="' . $v . '"';
            }
        }
        $html .= '>';

        $html .= '<textarea ref="refFormItemCode_' . $this->name . '" v-model="formData.' . $this->name . '"></textarea>';

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
            'formItems' => [
                $this->name => [
                    'codeMirror' => false,
                ]
            ]
        ];
    }

    /**
     * 获取 vue 钩子
     *
     * @return false | array
     */
    public function getVueHooks()
    {
        $mountedCode = 'this.formItems.' . $this->name . '.codeMirror = CodeMirror.fromTextArea(this.$refs.refFormItemCode_' . $this->name . ',' . json_encode($this->option) . ');';

        $updatedCode = 'this.formItems.' . $this->name . '.codeMirror && this.formItems.' . $this->name . '.codeMirror.refresh();';
        $updatedCode .= 'this.formData.' . $this->name . ' = this.formItems.' . $this->name . '.codeMirror.getValue();';
        return [
            'mounted' => $mountedCode,
            'updated' => $updatedCode,
        ];
    }

}
