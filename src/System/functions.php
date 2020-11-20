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
 * @param null | string $route 路径（应用名.控制器名.动作名）
 * @param null | array $params
 * @return string 生成的网址
 * @throws \Be\System\Exception\RuntimeException
 */
function beUrl($route = null, $params = [])
{
    if ($route === null) {
        if (count($params) > 0) {
            $route = Be::getRuntime()->getRoute();
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
        return Be::getRuntime()->getRootUrl() . '/' . $route . $urlParams . $configSystem->urlSuffix;
    } else {
        return Be::getRuntime()->getRootUrl() . '/?route=' . $route . (count($params) > 0 ? '&' . http_build_query($params) : '');
    }
}

/**
 * 命令行方式访问指定方法
 *
 * @param null | string $route 路径（应用名.控制器名.动作名）
 * @param null | array $params
 */
function beExec($route = null, $params = [])
{
    $cmd = 'php ' . Be::getRuntime()->getRootPath() . '/be ' . $route;
    if ($params) {
        $cmd .= ' ' . implode(' ', $params);
    }

    if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
        pclose(popen("start /B " . $cmd, "r"));
    } else {
        exec($cmd . " > /dev/null &");
    }
}
