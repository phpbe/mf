<?php
use Be\System\Be;


/**
 * 后台操作日志
 *
 * @param string $content 日志内容
 */
function adminLog($content)
{
    Be::getService('System', 'AdminLog')->addLog($content);
}
