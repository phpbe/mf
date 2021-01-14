<?php

namespace Be\Mf\Plugin\Lists;

use Be\Mf\Be;
use Be\Mf\Plugin\Driver;

/**
 * 列表器
 *
 * Class Curd
 * @package Be\Mf\Plugin
 */
class Lists extends Driver
{


    public function setting($setting = [])
    {
        if (!isset($setting['form']['action'])) {
            $setting['form']['action'] = Be::getRequest()->getUrl();
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
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pageSize = null;
        if (isset($this->setting['pageSize']) &&
            is_numeric($this->setting['pageSize']) &&
            $this->setting['pageSize'] > 0
        ) {
            $pageSize = $this->setting['pageSize'];
        } else {
            $pageSize = Be::getConfig('System.System')->pageSize;;
        }

        $response->set('title', $this->setting['title'] ?? '');
        $response->set('url', $request->getUrl());
        $response->set('setting', $this->setting);
        $response->set('pageSize', $pageSize);

        $theme = null;
        if (isset($this->setting['theme'])) {
            $theme = $this->setting['theme'];
        }
        $response->display('Plugin.Lists.display', $theme);
        $response->createHistory();
    }


}

