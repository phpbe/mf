<?php
namespace Be\App\System\Config;

/**
 * @be-config-label 后台用户
 */
class AdminUser
{

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
     * @be-config-item-option {"path": "/System/AdminUser/DefaultAvatar/", "maxSize": "2M"}
     */
    public $defaultAvatarS = '0_s.png';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemImage
     * @be-config-item-label 用户默认中头像
     * @be-config-item-option {"path": "/System/AdminUser/DefaultAvatar/", "maxSize": "2M"}
     */
    public $defaultAvatarM = '0_m.png';

    /**
     * @be-config-item-driver \Be\System\App\ConfigItem\ConfigItemImage
     * @be-config-item-label 用户默认大头像
     * @be-config-item-option {"path": "/System/AdminUser/DefaultAvatar/", "maxSize": "2M"}
     */
    public $defaultAvatarL = '0_l.png';

}
