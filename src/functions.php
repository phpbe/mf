<?php
/**
 * 系统日志
 *
 * @param string $content 日志内容
 * @param mixed $details 日志明细
 * @throws \Exception
 */
function beOpLog($content, $details = '')
{
    \Be\Mf\Be::getService('System.OpLog')->addLog($content, $details);
}

/**
 * 处理网址
 * 启用 SEF 时生成伪静态页， 为空时返回网站网址
 *
 * @param null | string $route 路径（应用名.控制器名.动作名）
 * @param null | array $params
 * @return string 生成的网址
 * @throws \Be\F\Runtime\RuntimeException
 */
function beUrl($route = null, $params = [])
{
    $request = \Be\Mf\Be::getRequest();
    if ($route === null) {
        if (count($params) > 0) {
            $route = $request->getRoute();
        } else {
            return $request->getRootUrl();
        }
    }

    $configSystem = \Be\Mf\Be::getConfig('System.System');
    if ($configSystem->urlRewrite) {
        $urlParams = '';
        if (count($params)) {
            foreach ($params as $key => $val) {
                $urlParams .= '/' . $key . '-' . $val;
            }
        }
        return $request->getRootUrl() . '/' . str_replace('.', '/', $route) . $urlParams . $configSystem->urlSuffix;
    } else {
        return $request->getRootUrl() . '/?route=' . $route . (count($params) > 0 ? '&' . http_build_query($params) : '');
    }
}