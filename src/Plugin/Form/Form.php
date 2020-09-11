<?php

namespace Be\Plugin\Form;

use Be\System\Plugin;
use Be\System\Request;
use Be\System\Response;

/**
 * 表单
 *
 * Class Form
 * @package Be\Plugin\Form
 */
class Form extends Plugin
{

    public function setting($setting = [])
    {
        if (!isset($setting['theme'])) {
            $setting['theme'] = 'Nude';
        }

        if (!isset($setting['form']['ui']['label-width'])) {
            $setting['form']['ui']['label-width'] = '150px';
        }

        if (!isset($setting['form']['ui']['size'])) {
            $setting['form']['ui']['size'] = 'mini';
        }

        if (!isset($setting['form']['action'])) {
            $setting['form']['action'] = Request::url();
        }

        return parent::setting($setting);
    }

    public function setValue($row)
    {
        if (is_object($row)) {
            $row = get_object_vars($row);
        }

        foreach ($this->setting['form']['items'] as &$item) {
            if (isset($item['value'])) {
                $value = $item['value'];
                if ($value instanceof \Closure) {
                    $item['value'] = $value($row);
                }
            } else {
                $name = $item['name'];
                if (isset($row[$name])) {
                    $item['value'] = (string) $row[$name];
                }
            }
        }
        unset($item);

        return $this;
    }


    public function display()
    {
        Response::set('setting', $this->setting);
        Response::display('Plugin.Form.display', $this->setting['theme']);
    }

}