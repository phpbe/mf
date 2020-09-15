<?php

namespace Be\System;

/**
 * Response
 * @package System
 *
 * @method void setTitle(string $title) static 设置 title
 * @method void setMetaKeywords(string $metaKeywords)  static 设置 meta keywords
 * @method void setMetaDescription(string $metaDescription)  static 设置 meta description
 */
class Response
{
    private static $data = array(); // 暂存数据


    /**
     * 向客户机添加一个字符串值属性的响应头信息
     */
    public static function addHeader($name, $value)
    {
    }

    /**
     * 向客户机设置一个字符串值属性的响应头信息，已存在时覆盖
     */
    public static function setHeader($name, $value)
    {
    }

    /**
     * 判断是否含响应头信息
     */
    public static function hasHeader($name)
    {
    }


    /**
     * 设置响应码，比如：200,304,404等
     */
    public static function setStatus($status)
    {
    }

    /**
     * 设置设置响应头content-type的内容
     */
    public static function setContentType($contentType)
    {
        header('Content-type: ' . $contentType);
    }

    /**
     * 请求重定向
     *
     * @param string $url 跳转网址
     */
    public static function redirect($url)
    {
        header('location:' . $url);
        exit();
    }

    /**
     * 设置暂存数据
     * @param string $name 名称
     * @param mixed $value 值 (可以是数组或对象)
     */
    public static function set($name, $value)
    {
        self::$data[$name] = $value;
    }

    /**
     * 获取暂存数据
     *
     * @param string $name 名称
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        if (isset(self::$data[$name])) return self::$data[$name];
        return $default;
    }

    /**
     * 以 JSON 输出暂存数据
     */
    public static function ajax()
    {
        self::json();
    }

    /**
     * 以 JSON 输出暂存数据
     */
    public static function json()
    {
        header('Content-type: application/json');
        echo json_encode(self::$data);
        exit();
    }

    /**
     * 成功
     *
     * @param string $message 消息
     * @param string $redirectUrl 跳转网址
     * @param int $redirectTimeout 跳转超时时长
     */
    public static function success($message, $redirectUrl = null, $redirectTimeout = 3)
    {
        self::set('success', true);
        self::set('message', $message);

        if ($redirectUrl !== null) {
            self::set('redirectUrl', $redirectUrl);
            if ($redirectTimeout > 0) self::set('redirectTimeout', $redirectTimeout);
        }

        if (Request::isAjax()) {
            self::json();
        } else {
            self::display('App.System.System.success');
            exit;
        }
    }

    /**
     * 失败
     *
     * @param string $message 消息
     * @param string $redirectUrl 跳转网址
     * @param int $redirectTimeout 跳转超时时长
     */
    public static function error($message, $redirectUrl = null, $redirectTimeout = 3)
    {
        self::set('success', false);
        self::set('message', $message);

        if ($redirectUrl !== null) {
            self::set('redirectUrl', $redirectUrl);
            if ($redirectTimeout > 0) self::set('redirectTimeout', $redirectTimeout);
        }

        if (Request::isAjax()) {
            self::json();
        } else {
            self::display('App.System.System.error');
            exit;
        }
    }


    /**
     * 系统异常
     *
     * @param \Exception $e 错误码
     */
    public static function exception($e)
    {
        if (Request::isAjax()) {
            self::set('success', false);
            self::set('message', $e->getMessage());
            self::set('code', $e->getCode());
            self::json();
        } else {
            self::set('e', $e);
            self::display('App.System.System.exception');
            exit;
        }
    }


    /**
     * 记录历史节点
     *
     * @param string $historyKey 历史节点键名
     */
    public static function createHistory($historyKey = null)
    {
        if ($historyKey === null) {
            $runtime = Be::getRuntime();
            $historyKey = $runtime->getAppName() . '.' . $runtime->getControllerName();
        }

        Session::set('_history_url_'.$historyKey, Request::server('REQUEST_URI'));
        Session::set('_history_post_'.$historyKey, serialize(Request::post()));
    }

    /**
     * 成功
     *
     * @param string $message 消息
     * @param string $historyKey 历史节点键名
     * @param int $redirectTimeout 跳转超时时长
     */
    public static function successAndBack($message, $historyKey = null, $redirectTimeout = 3)
    {
        if ($historyKey === null) {
            $runtime = Be::getRuntime();
            $historyKey = $runtime->getAppName() . '.' . $runtime->getControllerName();
        }

        self::set('success', true);
        self::set('message', $message);
        self::set('historyKey', $historyKey);

        $historyUrl = null;
        if (Session::has('_history_url_'.$historyKey)) {
            $historyUrl = Session::get('_history_url_'.$historyKey);
        }
        if (!$historyUrl) $historyUrl = Request::server('HTTP_REFERER');
        if (!$historyUrl) $historyUrl = './';

        $historyPost = null;
        if (Session::has('_history_post_'.$historyKey)) {
            $historyPost = Session::get('_history_post_'.$historyKey);
            if ($historyPost) $historyPost = unserialize($historyPost);
        }

        self::set('historyUrl', $historyUrl);
        self::set('historyPost', $historyPost);
        self::set('redirectTimeout', $redirectTimeout);
        self::display('App.System.System.successAndBack');
        exit;
    }

    /**
     * 失败
     *
     * @param string $message 消息
     * @param string $historyKey 历史节点键名
     * @param int $redirectTimeout 跳转超时时长
     */
    public static function errorAndBack($message, $historyKey = null, $redirectTimeout = 3)
    {
        if ($historyKey === null) {
            $runtime = Be::getRuntime();
            $historyKey = $runtime->getAppName() . '.' . $runtime->getControllerName();
        }

        self::set('success', false);
        self::set('message', $message);
        self::set('historyKey', $historyKey);

        $historyUrl = null;
        if (Session::has('_history_url_'.$historyKey)) {
            $historyUrl = Session::get('_history_url_'.$historyKey);
        }
        if (!$historyUrl) $historyUrl = Request::server('HTTP_REFERER');
        if (!$historyUrl) $historyUrl = './';

        $historyPost = null;
        if (Session::has('_history_post_'.$historyKey)) {
            $historyPost = Session::get('_history_post_'.$historyKey);
            if ($historyPost) $historyPost = unserialize($historyPost);
        }

        self::set('historyUrl', $historyUrl);
        self::set('historyPost', $historyPost);
        self::set('redirectTimeout', $redirectTimeout);
        self::display('App.System.System.errorAndBack');
        exit;
    }

    /**
     * 显示模板
     *
     * @param string $template 模板名
     * @param string $theme 主题名
     */
    public static function display($template = null, $theme = null)
    {
        $templateInstance = null;
        if ($template === null) {
            $runtime = Be::getRuntime();
            $app = $runtime->getAppName();
            $controller = $runtime->getControllerName();
            $action = $runtime->getActionName();
            $template = 'App.' . $app . '.' . $controller . '.' . $action;

            $templateInstance = Be::getTemplate($template, $theme);
        } else {
            $templateInstance = Be::getTemplate($template, $theme);
        }

        foreach (self::$data as $key => $val) {
            $templateInstance->$key = $val;
        }

        $templateInstance->display();
    }

    /**
     * 获取模板内容
     *
     * @param string $template 模板名
     * @param string $theme 主题名
     * @return  string
     */
    public static function fetch($template, $theme = null)
    {
        ob_start();
        ob_clean();
        self::display($template, $theme);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /**
     * 结束输出
     *
     * @param string $string 输出内空
     * @return  string
     */
    public static function end($string = null)
    {
        if ($string === null) {
            exit;
        } else {
            exit('<!DOCTYPE html><html><head><meta charset="utf-8" /></head><body><div style="padding:10px;text-align:center;">' . $string . '</div></body></html>');
        }
    }

    /*
     * 封装 setXxx 方法
     */
    public static function __callStatic($fn, $args)
    {
        if (substr($fn, 0, 3) == 'set' && count($args) == 1) {
            self::$data[lcfirst(substr($fn, 3))] = $args[0];
        }
    }

}

