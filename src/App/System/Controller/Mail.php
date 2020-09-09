<?php

namespace Be\App\System\Controller;

use Be\Plugin\Form\Item\FormItemInputTextArea;
use Be\System\Be;
use Be\System\Request;
use Be\System\Response;

/**
 * @BeMenuGroup("系统配置", icon="el-icon-setting")
 * @BePermissionGroup("系统配置")
 */
class Mail extends \Be\System\Controller
{

    /**
     * @BeMenu("发送邮件测试", icon="el-icon-fa fa-envelope-o")
     * @BePermission("发送邮件测试")
     */
    public function test()
    {
        if (Request::post()) {
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
                Response::success('发送邮件成功！', beUrl('System.Mail.test', ['toEmail' => $toEmail]));
            } catch (\Exception $e) {
                beSystemLog('发送测试邮件到 ' . $toEmail . ' -失败：' . $e->getMessage());
                Response::error('发送邮件失败：' . $e->getMessage(), beUrl('System.Mail.test', ['toEmail' => $toEmail]));
            }

        } else {
            Be::getPlugin('Form')->setting([
                'form' => [
                    'items' => [
                        [
                            'name' => 'toEmail',
                            'label' => '收件邮箱',
                            'required' => true,
                        ],
                        [
                            'name' => 'subject',
                            'label' => '标题',
                            'value' => '系统邮件测试',
                            'required' => true,
                        ],
                        [
                            'name' => 'body',
                            'label' => '内容',
                            'driver' => FormItemInputTextArea::class,
                            'value' => '这是一封测试邮件。',
                        ],
                    ],
                    'ui' => [
                        'style' => 'max-width: 800px;'
                    ]
                ],
                'theme' => 'Admin',
            ])->execute();

        }


        if (Request::isPost()) {


        } else {
            Response::setTitle('发送邮件测试');
            Response::display();
        }

    }


}