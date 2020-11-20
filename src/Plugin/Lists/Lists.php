<?php

namespace Be\Plugin\Lists;

use Be\System\Be;
use Be\System\Plugin;
use Be\System\Request;
use Be\System\Response;

/**
 * 列表器
 *
 * Class Curd
 * @package Be\Plugin
 */
class Lists extends Plugin
{


    public function setting($setting = [])
    {
        if (!isset($setting['form']['action'])) {
            $setting['form']['action'] = Request::url();
        }

        if (!isset($setting['form']['actions'])) {
            $setting['form']['actions'] = [
                'submit' => true,
            ];
        }

        return parent::setting($setting);
    }


    public function display()
    {
        $pageSize = null;
        if (isset($this->setting['pageSize']) &&
            is_numeric($this->setting['pageSize']) &&
            $this->setting['pageSize'] > 0
        ) {
            $pageSize = $this->setting['pageSize'];
        } else {
            $pageSize = Be::getConfig('System.System')->pageSize;;
        }

        Response::setTitle($this->setting['title'] ?? '');
        Response::set('url', Request::url());
        Response::set('setting', $this->setting);
        Response::set('pageSize', $pageSize);

        $theme = null;
        if (isset($this->setting['theme'])) {
            $theme = $this->setting['theme'];
        }
        Response::display('Plugin.Lists.display', $theme);
        Response::createHistory();
    }


}

