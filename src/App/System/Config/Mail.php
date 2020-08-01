<?php
namespace Be\App\System\Config;

/**
 * @BeConfig("邮件", test = "return beUrl('System.Mail.test');")
 */
class Mail
{
    /**
     * @BeConfigItem("发件人邮箱", driver="\Be\\Plugin\Config\Item\ConfigItemEmail")
     */
    public $fromMail = 'be@phpbe.com';

    /**
     * @BeConfigItem("发件人名称", driver="\Be\\Plugin\Config\Item\ConfigItemString")
     */
    public $fromName = 'BE';

    /**
     * @BeConfigItem("默认字符编码", driver="\Be\\Plugin\Config\Item\ConfigItemString")
     */
    public $charset = 'utf-8';

    /**
     * @BeConfigItem("默认字符编码", driver="\Be\\Plugin\Config\Item\ConfigItemString")
     */
    public $encoding = 'base64';

    /**
     * @BeConfigItem("是否启用SMTP", driver="\Be\\Plugin\Config\Item\ConfigItemBool")
     */
    public $smtp = false;

    /**
     * @BeConfigItem("SMTP地址", driver="\Be\\Plugin\Config\Item\ConfigItemString")
     */
    public $smtpHost = '';

    /**
     * @BeConfigItem("SMTP端口号",
     *     driver="\Be\\Plugin\Config\Item\ConfigItemInt",
     *     ui="return ['input-number' => [':min' => 1]];")
     */
    public $smtpPort = 25;

    /**
     * @BeConfigItem("SMTP用户名", driver="\Be\\Plugin\Config\Item\ConfigItemString")
     */
    public $smtpUser = '';

    /**
     * @BeConfigItem("SMTP密码", driver="\Be\\Plugin\Config\Item\ConfigItemString")
     */
    public $smtpPass = '';

    /**
     * @BeConfigItem("SMTP安全连接",
     *     driver="\Be\\Plugin\Config\Item\ConfigItemString",
     *     keyValues="return ['0' => '不加密','ssl' => 'SSL', 'tls' => 'TLS'];")
     */
    public $smtpSecure = '0';

    /**
     * @BeConfigItem("SMTP超时时间",
     *     driver="\Be\\Plugin\Config\Item\ConfigItemInt",
     *     ui="return ['input-number' => [':min' => 1]];")
     */
    public $smtpTimeout = 10;
}
