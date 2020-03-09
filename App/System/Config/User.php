<?php
namespace App\System\Config;

/**
 * @be-config-label 用户
 */
class User
{
    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemBool
     * @be-config-item-label 是否开启注册功能
     */
    public $register = true;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemBool
     * @be-config-item-label 是否开启登陆验证码
     */
    public $captchaLogin = false;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemBool
     * @be-config-item-label 是否开启注册验证码
     */
    public $captchaRegister = true;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemBool
     * @be-config-item-label 是否发送激活链接验证用户邮箱
     */
    public $emailValid = false;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemBool
     * @be-config-item-label 注册成功后是否发送提示邮件
     */
    public $emailRegister = false;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemBool
     * @be-config-item-label 新用户注册后给管理员发送邮件
     */
    public $emailRegisterAdmin = '1024i@gmail.com';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 用户小头像宽度
     * @be-config-item-description 单位：像素，修改后仅对此后上传的头像生效
     * @be-config-item-ui {":min":1}
     */
    public $avatarSW = 32;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 用户小头像高度
     * @be-config-item-description 单位：像素，修改后仅对此后上传的头像生效
     * @be-config-item-ui {":min":1}
     */
    public $avatarSH = 32;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 用户中头像宽度
     * @be-config-item-description 单位：像素，修改后仅对此后上传的头像生效
     * @be-config-item-ui {":min":1}
     */
    public $avatarMW = 64;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 用户中头像高度
     * @be-config-item-description 单位：像素，修改后仅对此后上传的头像生效
     * @be-config-item-ui {":min":1}
     */
    public $avatarMH = 64;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 用户大头像宽度
     * @be-config-item-description 单位：像素，修改后仅对此后上传的头像生效
     * @be-config-item-ui {":min":1}
     */
    public $avatarLW = 96;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemInt
     * @be-config-item-label 用户大头像高度
     * @be-config-item-description 单位：像素，修改后仅对此后上传的头像生效
     * @be-config-item-ui {":min":1}
     */
    public $avatarLH = 96;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemImage
     * @be-config-item-label 用户默认小头像
     * @be-config-item-option {"path": "/System/User/DefaultAvatar/", "maxSize": "2M"}
     */
    public $defaultAvatarS = '0_s.png';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemImage
     * @be-config-item-label 用户默认中头像
     * @be-config-item-option {"path": "/System/User/DefaultAvatar/", "maxSize": "2M"}
     */
    public $defaultAvatarM = '0_m.png';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemImage
     * @be-config-item-label 用户默认大头像
     * @be-config-item-option {"path": "/System/User/DefaultAvatar/", "maxSize": "2M"}
     */
    public $defaultAvatarL = '0_l.png';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemBool
     * @be-config-item-label 是否开启QQ账号登录
     */
    public $connectQq = true;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label QQ APP ID
     */
    public $connectQqAppId = '';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label QQ APP KEY
     */
    public $connectQqAppKey = '';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemBool
     * @be-config-item-label 是否开启新浪微博登录
     */
    public $connectSina = true;

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 新浪微博 App Key
     */
    public $connectSinaAppKey = '1295333283';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemString
     * @be-config-item-label 新浪微博 App Secret
     */
    public $connectSinaAppSecret = '6ea122b52d501ba4433dc92d4fd1d806';

}
