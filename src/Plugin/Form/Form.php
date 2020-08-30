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

    public function display()
    {
        Response::set('setting', $this->setting);
        Response::display('Plugin.Form.display', $this->setting['theme']);
    }

}