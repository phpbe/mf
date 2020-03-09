<?php
use Be\System\Be;

/**
 * 处理网址
 * 启用 SEF 时生成伪静态页， 为空时返回网站网址
 *
 * @param null | string $action 动作名
 * @param null | array $params
 * @return string 生成的网址
 * @throws \Exception
 */
function url($action = null, $params = [])
{
    if ($action === null) {
        return Be::getRuntime()->getRootUrl();
    }

    $configSystem = Be::getConfig('System.System');
    if ($configSystem->sef) {
        $urlParams = '';
        if (count($params)) {
            foreach ($params as $key => $val) {
                $urlParams .= '/' . $key . '-' . $val;
            }
        }
        return Be::getRuntime()->getRootUrl() . '/' . $action . $urlParams . $configSystem->sefSuffix;
    } else {
        return Be::getRuntime()->getRootUrl() . '/?action=' . $action . (count($params) > 0 ? http_build_query($params) : '');
    }
}


/**
 * 处理后台网址
 * 启用 SEF 时生成伪静态页， 为空时返回网站网址
 *
 * @param null | string $action 动作名
 * @param null | array $params
 * @return string 生成的网址
 * @throws \Exception
 */
function adminUrl($action = null, $params = [])
{
    $runtime = Be::getRuntime();

    if ($action === null) {
        return $runtime->getAdminUrl();
    }

    $configSystem = Be::getConfig('System.System');
    if ($configSystem->sef) {

        $urlParams = '';
        if (count($params)) {
            foreach ($params as $key => $val) {
                $urlParams .= '/' . $key . '-' . $val;
            }
        }

        return $runtime->getAdminUrl() . '/' . $action . $urlParams . $configSystem->sefSuffix;
    } else {
        return $runtime->getRootUrl() . '/index.php/' . $runtime->getAdminDir() . '/?action=' . $action . (count($params) > 0 ? http_build_query($params) : '');
    }

}
