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
     * @BeConfigItem("默认字符编码", driver="FormItemInput")
     */
    public $charset = 'utf-8';

    /**
     * @BeConfigItem("默认字符编码", driver="FormItemInput")
     */
    public $encoding = 'base64';

    /**
     * @BeConfigItem("是否启用SMTP", driver="FormItemSwitch")
     */
    public $smtp = 0;

    /**
     * @BeConfigItem("SMTP地址",
     *     driver="FormItemInput",
     *     ui="return ['form-item' => ['v-if' => 'formData.smtp']];")
     */
    public $smtpHost = '';

    /**
     * @BeConfigItem("SMTP端口号",
     *     driver="FormItemInputNumberInt",
     *     ui="return ['form-item' => ['v-if' => 'formData.smtp'], 'input-number' => [':min' => 1]];")
     */
    public $smtpPort = 25;

    /**
     * @BeConfigItem("SMTP用户名",
     *     driver="FormItemInput",
     *     ui="return ['form-item' => ['v-if' => 'formData.smtp']];")
     */
    public $smtpUser = '';

    /**
     * @BeConfigItem("SMTP密码",
     *     driver="FormItemInput",
     *     ui="return ['form-item' => ['v-if' => 'formData.smtp']];")
     */
    public $smtpPass = '';

    /**
     * @BeConfigItem("SMTP安全连接",
     *     driver="FormItemSelect",
     *     keyValues="return ['0' => '不加密','ssl' => 'SSL', 'tls' => 'TLS'];",
     *     ui="return ['form-item' => ['v-if' => 'formData.smtp']];")
     */
    public $smtpSecure = '0';

    /**
     * @BeConfigItem("SMTP超时时间",
     *     driver="FormItemInputNumberInt",
     *     ui="return ['form-item' => ['v-if' => 'formData.smtp'], 'input-number' => [':min' => 1]];")
     */
    public $smtpTimeout = 10;
}
