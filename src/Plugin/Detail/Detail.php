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

        if (!isset($setting['field']['ui']['form']['label-width'])) {
            $setting['field']['ui']['form']['label-width'] = '150px';
        }

        if (!isset($setting['field']['ui']['form']['size'])) {
            $setting['field']['ui']['form']['size'] = 'mini';
        }

        return parent::setting($setting);
    }


    public function display()
    {
        Response::setTitle($this->setting['title']);
        Response::set('setting', $this->setting);
        Response::display('Plugin.detail.detail', $this->setting['theme']);
    }

}
