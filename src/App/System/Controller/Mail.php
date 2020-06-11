<?php

namespace Be\App\System\Controller;

use Be\System\Be;
use Be\System\Request;
use Be\System\Response;

/**
 * @be-menu-group 设置
 * @be-permission-group 设置
 */
class Mail extends \Be\System\Controller
{


    /**
     * @be-menu 发送邮件测试
     * @be-menu-icon mail
     * @be-permission 发送邮件测试
     */
    public function test()
    {
        if (Request::isPost()) {
            $toEmail = Request::post('toEmail', '');
            $subject = Request::post('subject', '');
            $body = Request::post('body', '', 'html');

            try {
                $serviceMail = Be::getService('System.Mail');
                $serviceMail->subject($subject);
                $serviceMail->body($body);
                $serviceMail->to($toEmail);
                $serviceMail->send();

                beSystemLog('发送测试邮件到 ' . $toEmail . ' -成功');
                Response::success('发送邮件成功！', beUrl('System', 'Mail', 'test', ['toEmail' => $toEmail]));
            } catch (\Exception $e) {
                beSystemLog('发送测试邮件到 ' . $toEmail . ' -失败：' . $e->getMessage());
                Response::error('发送邮件失败：' . $e->getMessage(), beUrl('System', 'Mail', 'test', ['toEmail' => $toEmail]));
            }

        } else {
            Response::setTitle('发送邮件测试');
            Response::display();
        }

    }


}