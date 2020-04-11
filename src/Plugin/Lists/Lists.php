<?php

namespace Be\Plugin\Lists;

use Be\System\Be;
use Be\System\Db\Tuple;
use Be\System\Plugin;
use Be\System\Request;
use Be\System\Response;
use Be\System\Cookie;

/**
 * 增删改查
 *
 * Class Curd
 * @package Be\Plugin
 */
class Lists extends Plugin
{


    protected $setting = null;


    /**
     * @param array $setting
     */
    public function execute($setting = [])
    {
        $this->setting = $setting;

        $runtime = Be::getRuntime();

        $appName = $runtime->getAppName();
        $controllerName = $runtime->getControllerName();
        $actionName = $runtime->getActionName();

        if (!isset($setting['page'])) {
            $setting['page'] = Request::post('page', 1, 'int');
        }

        if (!isset($setting['pageSize'])) {
            $pageSize = Request::post('pageSize', 0, 'int');
            $defaultPageSize = Be::getConfig('System.Admin')->pageSize;

            $cookiePageSizeKey = $appName . '.' . $controllerName . '.' . $actionName . '.pageSize';
            if (!$pageSize) {
                $cookiePageSize = Cookie::get($cookiePageSizeKey, 0, 'int');
                if ($cookiePageSize > 0) {
                    $pageSize = $cookiePageSize;
                } else {
                    $pageSize = $defaultPageSize;
                }
            }

            Cookie::set($cookiePageSizeKey, $pageSize, 86400 * 30);

            if ($pageSize <= 0) $pageSize = $defaultPageSize;
            if ($pageSize > 1000) $pageSize = 1000;

            $setting['pageSize'] = $pageSize;
        }

        if (!isset($setting['total'])) {
            $setting['total'] = 0;
            if (!isset($setting['pages'])) {
                $setting['pages'] = 1;
            }
        } else {
            if (!isset($setting['pages'])) {
                $pages = ceil($setting['total'] / $setting['pageSize']);
                if ($pages == 0) $pages = 1;
                $setting['pages'] = $pages;
            }
        }

        $searchDrivers = [];
        if (isset($setting['search']['items'])) {
            foreach ($setting['search']['items'] as $key => $search) {
                $driver = $search['driver'];
                $searchDriver = new $driver($key, $search);
                $searchDriver->buildWhere(Request::post());

                $searchDrivers[] = $searchDriver;
            }
        }




        $fieldDrivers = [];
        foreach ($rows as $row) {

            $tmpFieldDrivers = [];
            foreach ($fields as $item) {

                if (!isset($item['value']) && isset($item['name'])) {
                    $name = $item['name'];
                    if (isset($row->$name)) {
                        $item['value'] = $row->$name;
                    }
                }

                $driver = null;
                if (!isset($item['driver'])) {
                    $driver = FieldItemText::class;
                } else {
                    $driver = $item['driver'];
                }
                $fieldDriver = new $driver($item);
                $tmpFieldDrivers[] = $fieldDriver;
            }

            $fieldDrivers[] = $tmpFieldDrivers;
        }



        $toolbarDrivers = [];
        if (isset($setting['toolbar']['items'])) {
            foreach ($setting['toolbar']['items'] as &$toolbar) {
                if (isset($toolbar['task']) && $toolbar['task']) {
                    $toolbar['url'] = url($appName . '.' . $controllerName . '.' . $actionName, ['task' => $toolbar['task']]);
                }
            }
        }


        if (!isset($setting['orderBy'])) {
            $setting['orderBy'] = Request::post('orderBy', '');
        }


        Response::setTitle($setting['title']);
        Response::set('url', Request::url());
        Response::set('setting', $setting);
        Response::set('searchDrivers', $searchDrivers);
        Response::set('toolbarDrivers', $toolbarDrivers);
        Response::display('Plugin.Lists.lists');
        Response::createHistory();
    }


}

