<?php

namespace Be\System;

/**
 * Request
 */
class Request
{
    protected static $json = null;

    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function isGet()
    {
        return 'GET' == $_SERVER['REQUEST_METHOD'] ? true : false;
    }

    public static function isPost()
    {
        return 'POST' == $_SERVER['REQUEST_METHOD'] ? true : false;
    }

    public static function isAjax()
    {
        return (
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHTTPREQUEST' == strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'])) ||
            (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json') !== false)
        ) ? true : false;
    }


    /**
     * 获取当前请求的完整网址
     */
    public static function url()
    {
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
        $url .= isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT']));
        $url .= $_SERVER['REQUEST_URI'];
        return $url;
    }

    /**
     * 获取请求者的 IP 地址
     *
     * @param bool $detectProxy 是否检测代理服务器
     * @return string
     */
    public static function ip($detectProxy = true)
    {
        if ($detectProxy) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $pos = strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',');

                $ip = null;
                if (false !== $pos) {
                    $ip = substr($_SERVER['HTTP_X_FORWARDED_FOR'], 0, $pos);
                } else {
                    $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
                }

                if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * 获取 $_GET 数据
     * @param string|null $name 参数量
     * @param string|null $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public static function get($name = null, $default = null, $format = 'string')
    {
        return self::_request($_GET, $name, $default, $format);
    }

    /**
     * 获取 $_POST 数据
     * @param string|null $name 参数量
     * @param string|null $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public static function post($name = null, $default = null, $format = 'string')
    {
        return self::_request($_POST, $name, $default, $format);
    }

    /**
     * 获取 $_REQUEST 数据
     * @param string|null $name 参数量
     * @param string|null $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public static function request($name = null, $default = null, $format = 'string')
    {
        return self::_request($_REQUEST, $name, $default, $format);
    }

    /**
     * 获取 ajax 请求发送的 JSON 数据
     * @param string|null $name 参数量
     * @param string|null $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public static function json($name = null, $default = null, $format = 'string')
    {
        if (self::$json === null) {
            if (isset($_SERVER['CONTENT_TYPE']) && strpos(strtolower($_SERVER['CONTENT_TYPE']), 'application/json') !== false) {
                $json = file_get_contents('php://input');
                $json = json_decode($json, true);
                if ($json) {
                    self::$json = $json;
                }
            }
        }

        return self::_request(self::$json, $name, $default, $format);
    }

    /**
     * 获取 $_SERVER 数据
     * @param string|null $name 参数量
     * @param string|null $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public static function server($name = null, $default = null, $format = 'string')
    {
        return self::_request($_SERVER, $name, $default, $format);
    }

    /**
     * 获取 $_COOKIE 数据
     * @param string|null $name 参数量
     * @param string|null $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public static function cookie($name = null, $default = null, $format = 'string')
    {
        return self::_request($_COOKIE, $name, $default, $format);
    }

    /**
     * 获取上传的文件
     * @param string|null $name 参数量
     * @return array|null
     */
    public static function files($name = null)
    {
        if ($name === null) {
            return $_FILES;
        }

        if (!isset($_FILES[$name])) return null;

        return $_FILES[$name];
    }

    private static function _request($input, $name, $default, $format)
    {
        if ($name === null) {
            if ($format instanceof \Closure) {
                $input = self::formatByClosure($input, $format);
            } else {
                if ($format) {
                    $fnFormat = 'format' . ucfirst($format);
                    $input = self::$fnFormat($input);
                }
            }

            return $input;
        }

        $value = null;
        if (strpos($name, '.') === false) {
            if (!isset($input[$name])) return $default;
            $value = $input[$name];
        } else {
            $tmpInput = $input;
            $tmpValue = null;
            $names = explode('.', $name);
            foreach ($names as $x) {
                if (!isset($tmpInput[$x])) return $default;
                $tmpValue = $tmpInput[$x];
            }
            $value = $tmpValue;
        }

        if ($format instanceof \Closure) {
            return self::formatByClosure($value, $format);
        } else {
            if ($format) {
                $fnFormat = 'format' . ucfirst($format);
                return self::$fnFormat($value);
            } else {
                return $value;
            }
        }
    }

    private static function formatInt($value)
    {
        return is_array($value) ? array_map(array('Be\System\Request', 'formatInt'), $value) : intval($value);
    }

    private static function formatFloat($value)
    {
        return is_array($value) ? array_map(array('Be\System\Request', 'formatFloat'), $value) : floatval($value);
    }

    private static function formatBool($value)
    {
        return is_array($value) ? array_map(array('Be\System\Request', 'formatBool'), $value) : boolval($value);
    }

    private static function formatString($value)
    {
        return is_array($value) ? array_map(array('Be\System\Request', 'formatString'), $value) : htmlspecialchars($value);
    }

    // 过滤  脚本,样式，框架
    private static function formatHtml($value)
    {
        if (is_array($value)) {
            return array_map(array('System\Request', 'formatHtml'), $value);
        } else {
            $value = preg_replace("@<script(.*?)</script>@is", '', $value);
            $value = preg_replace("@<style(.*?)</style>@is", '', $value);
            $value = preg_replace("@<iframe(.*?)</iframe>@is", '', $value);

            return $value;
        }
    }

    /**
     * 格式化 IP
     * @param $value
     * @return array|string
     */
    private static function formatIp($value)
    {
        if (is_array($value)) {
            $returns = [];
            foreach ($value as $v) {
                $returns[] = self::formatIp($v);
            }
            return $returns;
        } else {
            if (filter_var($value, FILTER_VALIDATE_IP)) {
                return $value;
            } else {
                return 'invalid';
            }
        }
    }

    private static function formatByClosure($value, \Closure $closure)
    {
        if (is_array($value)) {
            $returns = [];
            foreach ($value as $v) {
                $returns[] = self::formatByClosure($v, $closure);
            }
            return $returns;
        } else {
            return $closure($value);
        }

    }

}

