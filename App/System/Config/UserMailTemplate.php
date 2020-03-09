<?php
namespace App\System\Config;

/**
 * @be-config-label 用户邮件模板
 */
class UserMailTemplate
{

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 注册成功激活邮件主题
     * @be-config-item-description 可用变量：{siteName}=网站名，{siteUrl}=网址，{username}=用户名，{password}=密码，{email}-邮箱,{name}-名字
     */
    public $registerMailActivationSubject = '激活您在{siteName}注册的账号：{username}';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemText
     * @be-config-item-label 注册成功激活邮件内容
     * @be-config-item-description 可用变量：{siteName}=网站名，{siteUrl}=网址，{username}=用户名，{password}=密码，{email}-邮箱,{name}-名字
     * @be-config-item-uiType textarea
     */
    public $registerMailActivationBody = '您好 {name}，<br /><br />感谢您在{siteName}注册账号。 在您正常使用前，需要激活您的账号。<br />请点击以下链接激活:<br /><a href="{activationUrl}" target="Blank">{activationUrl}</a><br /><br />激活后您将可以使用以下账号登陆{siteName}：<br /><br />用户名：{username}<br />密码：{password}';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 注册成功邮件主题
     * @be-config-item-description 可用变量：{siteName}=网站名，{siteUrl}=网址，{username}=用户名，{password}=密码，{email}-邮箱,{name}-名字
     */
    public $registerMailSubject = '您的账号创建成功';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemText
     * @be-config-item-label 注册成功邮件内容
     * @be-config-item-description 可用变量：{siteName}=网站名，{siteUrl}=网址，{username}=用户名，{password}=密码，{email}-邮箱,{name}-名字
     * @be-config-item-uiType textarea
     */
    public $registerMailBody = '您好 {name},<br /><br />您的账号已创建成功。您现在可以使用用户名{username}登陆{siteName}。';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 注册成功提醒管理员邮件主题
     * @be-config-item-description 可用变量：{siteName}=网站名，{siteUrl}=网址，{username}=用户名，{password}=密码，{email}-邮箱,{name}-名字
     */
    public $registerMailToAdminSubject = '一个新用户({username})在{siteName}注册';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemText
     * @be-config-item-label 注册成功提醒管理员邮件内容
     * @be-config-item-description 可用变量：{siteName}=网站名，{siteUrl}=网址，{username}=用户名，{password}=密码，{email}-邮箱,{name}-名字
     * @be-config-item-uiType textarea
     */
    public $registerMailToAdminBody = '您好管理员，<br /><br />一个新用户在{siteName}注册。<br />账号信息如下：<br /><br />名称：{name}<br />邮箱：{email}<br />用户名：{username}。';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 找回密码邮件主题
     * @be-config-item-description 可用变量：{siteName}=网站名，{siteUrl}=网址
     */
    public $forgotPasswordMailSubject = '找回您在{siteName}的密码';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemText
     * @be-config-item-label 找回密码邮件内容
     * @be-config-item-description 可用变量：{siteName}=网站名，{siteUrl}=网址，{resetPasswordUrl}=找回密码网址
     * @be-config-item-uiType textarea
     */
    public $forgotPasswordMailBody = '您好，<br /><br />您请求重设在{siteName}的账号密码。点击以下链接重置密码：<br /><br /><a href="{resetPasswordUrl}" target="Blank">{resetPasswordUrl}</a>';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 重设密码成功邮件主题
     * @be-config-item-description 可用变量：{siteName}=网站名，{siteUrl}=网址
     */
    public $forgotPasswordResetMailSubject = '重设您在{siteName}上的密码成功';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemText
     * @be-config-item-label 重设密码成功邮件内容
     * @be-config-item-description 可用变量：{siteName}=网站名，{siteUrl}=网址
     * @be-config-item-uiType textarea
     */
    public $forgotPasswordResetMailBody = '您好,<br /><br />您的密码重设成功。您现在可以使用您的新密码登陆<a href="{siteUrl}" target="Blank">{siteName}</a>。';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 管理员为用户创建账号成功邮件主题
     * @be-config-item-description 可用变量：{siteName}=网站名，{siteUrl}=网址
     */
    public $adminCreateAccountMailSubject = '{siteName}网站的管理员为您添加了一个账号';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemText
     * @be-config-item-label 管理员为用户创建账号成功邮件内容
     * @be-config-item-description 可用变量：{siteName}=网站名，{siteUrl}=网址，{username}=用户名，{password}=密码，{email}-邮箱,{name}-名字
     * @be-config-item-uiType textarea
     */
    public $adminCreateAccountMailBody = '<div style="padding:3px;font-size:12px;"><div style="padding:3px 5px;background-color:#f90;color:#fff;">您在 {siteName} 网站上的账号信息</div><div style="padding:20px;color:#666;">{siteName} 网站的管理员为您添加了一个账号， 账号信息如下：</div><ul><li>用户名：{username}</li><li>密码：{password}</li><li>邮箱：{email}</li><li>名字：{name}</li></ul><div style="padding:20px;color:#666;">请牢记您的账号信息，点击这里访问  <a href="{siteUrl}" target="Blank">{siteName}</a></div><div style="padding:3px;border-top:#ddd 1px solid;font-size:10px;color:#bbb;">本邮件由系统发送给 {email}，请勿直接回复。</div></div>';

}
