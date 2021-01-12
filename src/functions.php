<?php
use Be\Mf\Be;


/**
 * 系统日志
 *
 * @param string $content 日志内容
 * @param mixed $details 日志明细
 * @throws \Exception
 */
function beOpLog($content, $details = '')
{
    Be::getService('System.OpLog')->addLog($content, $details);
}
