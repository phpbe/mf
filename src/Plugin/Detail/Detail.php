<?php

namespace Be\Plugin\Detail;

use Be\System\Be;
use Be\System\Plugin;
use Be\System\Request;
use Be\System\Response;

/**
 * 明细
 *
 * Class Detail
 * @package Be\Plugin
 */
class Detail extends Plugin
{

    public function setting($setting = [])
    {
        if (!isset($setting['title'])) {
            $setting['title'] = '查看明细';
        }

        if (!isset($setting['theme'])) {
            $setting['theme'] = 'Nude';
        }

        if (!isset($setting['form']['ui']['label-width'])) {
            $setting['form']['ui']['label-width'] = '150px';
        }

        if (!isset($setting['form']['ui']['size'])) {
            $setting['form']['ui']['size'] = 'mini';
        }

        return parent::setting($setting);
    }


    public function display()
    {
        Response::setTitle($this->setting['title']);
        Response::set('setting', $this->setting);
        Response::display('Plugin.Detail.display', $this->setting['theme']);
    }

}

