<?php

namespace Be\Mf\Plugin\Detail;

use Be\Mf\Be;
use Be\Mf\Plugin\Driver;

/**
 * 明细
 *
 * Class Detail
 * @package Be\Mf\Plugin\Detail
 */
class Detail extends Driver
{

    public function setting($setting = [])
    {
        if (!isset($setting['title'])) {
            $setting['title'] = '查看明细';
        }

        if (!isset($setting['theme'])) {
            $setting['theme'] = 'Nude';
        }

        if (!isset($setting['form']['actions'])) {
            $setting['form']['actions'] = [
                'cancel' => true,
            ];
        }

        return parent::setting($setting);
    }


    public function setValue($row)
    {
        if (is_object($row)) {
            $row = get_object_vars($row);
        }

        foreach ($this->setting['form']['items'] as &$item) {
            $itemName = $item['name'];
            $itemValue = '';
            if (isset($item['value'])) {
                $value = $item['value'];
                if ($value instanceof \Closure) {
                    $itemValue = $value($row);
                } else {
                    $itemValue = $value;
                }
            } else {
                if (isset($row[$itemName])) {
                    $itemValue = $row[$itemName];
                }
            }

            $item['value'] = $itemValue;
        }
        unset($item);

        return $this;
    }


    public function display()
    {
        $response = Be::getResponse();

        $response->set('title', $this->setting['title']);
        $response->set('setting', $this->setting);
        $response->display('Plugin.Detail.display', $this->setting['theme']);
    }

}

