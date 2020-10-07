<?php
use Be\System\Be;


/**
 * 系统日志
 *
 * @param string $content 日志内容
 * @param mixed $details 日志明细
 * @throws \Exception
 */
function beSystemLog($content, $details = '')
{
    Be::getService('System.SystemLog')->addLog($content, $details);
}

/**
 * 处理网址
 * 启用 SEF 时生成伪静态页， 为空时返回网站网址
 *
 * @param null | string $pathway 路径（应用名.控制器名.动作名）
 * @param null | array $params
 * @return string 生成的网址
 * @throws \Be\System\Exception\RuntimeException
 */
function beUrl($pathway = null, $params = [])
{
    if ($pathway === null) {
        if (count($params) > 0) {
            $pathway = Be::getRuntime()->getPathway();
        } else {
            return Be::getRuntime()->getRootUrl();
        }
    }

    $configSystem = Be::getConfig('System.System');
    if ($configSystem->urlRewrite) {
        $urlParams = '';
        if (count($params)) {
            foreach ($params as $key => $val) {
                $urlParams .= '/' . $key . '-' . $val;
            }
        }
        return Be::getRuntime()->getRootUrl() . '/' . $pathway . $urlParams . $configSystem->urlSuffix;
    } else {
        return Be::getRuntime()->getRootUrl() . '/?pathway=' . $pathway . (count($params) > 0 ? '&' . http_build_query($params) : '');
    }
}
