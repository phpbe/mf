<?php
namespace Be\App\System\Config;

/**
 * @BeConfig("邮件", test = "return beUrl('System.Mail.test');")
 */
class Mail
{
    /**
     * @BeConfigItem("发件人邮箱", driver="FormItemInput")
     */
    public $fromMail = 'be@phpbe.com';

    /**
     * @BeConfigItem("发件人名称", driver="FormItemInput")
     */
    public $fromName = 'BE';

    /**
     * @BeConfigItem("是否启用SMTP", driver="FormItemSwitch")
     */
    public $smtp = 0;

    /**
     * @BeConfigItem("SMTP地址",
     *     driver="FormItemInput",
     *     ui="return ['form-item' => ['v-show' => 'formData.smtp==1']];")
     */
    public $smtpHost = '';

    /**
     * @BeConfigItem("SMTP端口号",
     *     driver="FormItemInputNumberInt",
     *     ui="return ['form-item' => ['v-show' => 'formData.smtp==1'], ':min' => 1];")
     */
    public $smtpPort = 25;

    /**
     * @BeConfigItem("SMTP用户名",
     *     driver="FormItemInput",
     *     ui="return ['form-item' => ['v-show' => 'formData.smtp==1']];")
     */
    public $smtpUser = '';

    /**
     * @BeConfigItem("SMTP密码",
     *     driver="FormItemInput",
     *     ui="return ['form-item' => ['v-show' => 'formData.smtp==1']];")
     */
    public $smtpPass = '';

    /**
     * @BeConfigItem("SMTP安全连接",
     *     driver="FormItemSelect",
     *     keyValues="return ['0' => '不加密','ssl' => 'SSL', 'tls' => 'TLS'];",
     *     ui="return ['form-item' => ['v-show' => 'formData.smtp==1']];")
     */
    public $smtpSecure = '0';

    /**
     * @BeConfigItem("SMTP超时时间",
     *     driver="FormItemInputNumberInt",
     *     ui="return ['form-item' => ['v-show' => 'formData.smtp==1'],':min' => 1];")
     */
    public $smtpTimeout = 10;
}
