<?php
namespace Be\App\System\Config;

/**
 * @be-config-label 邮件
 * @be-config-test beUrl('System.Mail.test')
 */
class Mail
{
    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemEmail
     * @be-config-item-label 发件人邮箱
     */
    public $fromMail = 'be@phpbe.com';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 发件人名称
     */
    public $fromName = 'BE';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 默认字符编码
     */
    public $charset = 'utf-8';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 默认字符编码
     */
    public $encoding = 'base64';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemBool
     * @be-config-item-label 是否启用SMTP
     */
    public $smtp = false;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label SMTP地址
     */
    public $smtpHost = '';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label SMTP端口号
     * @be-config-item-ui {"input-number":{":min":1}}
     */
    public $smtpPort = 25;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label SMTP用户名
     */
    public $smtpUser = '';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label SMTP密码
     */
    public $smtpPass = '';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label SMTP安全连接
     * @be-config-item-keyValues {"0":"不加密","ssl":"SSL","tls":"TLS"}
     */
    public $smtpSecure = '0';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label SMTP超时时间
     * @be-config-item-ui {"input-number":{":min":1}}
     */
    public $smtpTimeout = 10;
}
