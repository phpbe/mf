<?php
namespace Be\System;

/**
 * 界面模块基类
 */
interface Ui
{
    
	public function head();    // 引入相关文件到 html 的 head 区域
    public function display();    // 输出
    
}