<?php

namespace Be\Mf\App\System\Controller;

use Be\Mf\Plugin\Form\Item\FormItemInputTextArea;
use Be\Mf\Be;

/**
 * @BeMenuGroup("系统配置", icon="el-icon-setting")
 * @BePermissionGroup("系统配置")
 */
class Mail
{

    /**
     * @BeMenu("发送邮件测试", icon="el-icon-fa fa-envelope-o", ordering="20.1")
     * @BePermission("发送邮件测试", ordering="20.1")
     */
    public function test()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        if ($request->isAjax()) {

            $toEmail = $request->json('formData.toEmail', '');
            $subject = $request->json('formData.subject', '');
            $body = $request->json('formData.body', '', 'html');

            try {
                Be::getService('System.Mail')->send($toEmail, $subject, $body);

                beOpLog('发送测试邮件到 ' . $toEmail . ' -成功',  $request->json('formData'));
                $response->success('发送邮件成功！', beUrl('System.Mail.test', ['toEmail' => $toEmail]));
            } catch (\Exception $e) {
                beOpLog('发送测试邮件到 ' . $toEmail . ' -失败：' . $e->getMessage());
                $response->error('发送邮件失败：' . $e->getMessage(), beUrl('System.Mail.test', ['toEmail' => $toEmail]));
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
    }


}