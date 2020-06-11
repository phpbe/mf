<?php
use Be\System\Be;


/**
 * 系统日志
 *
 * @param string $content 日志内容
 */
function beSystemLog($content) {
    Be::getService('System.beSystemLog')->addLog($content);
}

/**
 * 处理网址
 * 启用 SEF 时生成伪静态页， 为空时返回网站网址
 *
 * @param null | string $action 动作名
 * @param null | array $params
 * @return string 生成的网址
 * @throws \Exception
 */
function beUrl($action = null, $params = [])
{
    if ($action === null) {
        return Be::getRuntime()->getRootUrl();
    }

    $configSystem = Be::getConfig('System.System');
    if ($configSystem->urlRewrite) {
        $urlParams = '';
        if (count($params)) {
            foreach ($params as $key => $val) {
                $urlParams .= '/' . $key . '-' . $val;
            }
        }
        return Be::getRuntime()->getRootUrl() . '/' . $action . $urlParams . $configSystem->urlSuffix;
    } else {
        return Be::getRuntime()->getRootUrl() . '/?action=' . $action . (count($params) > 0 ? http_build_query($params) : '');
    }
}
