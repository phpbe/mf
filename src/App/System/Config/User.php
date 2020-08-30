<?php
namespace Be\App\System\Config;

/**
 * @BeConfig("用户")
 */
class User
{

    /**
     * @BeConfigItem("锁定IP",
     *     driver="ConfigItemSwitch",
     *     description="启用锁定IP时，若用户IP变化，需重新登录。")
     */
    public $ipLock = 1;

    /**
     * @BeConfigItem("用户头像宽度",
     *     driver="ConfigItemInputNumberInt",
     *     description="单位：像素，修改后仅对此后上传的头像生效",
     *     ui="return [':min' => 1];")
     */
    public $avatarWidth = 96;

    /**
     * @BeConfigItem("用户头像高度",
     *     driver="ConfigItemInputNumberInt",
     *     description="单位：像素，修改后仅对此后上传的头像生效",
     *     ui="return [':min' => 1];")
     */
    public $avatarHeight = 96;


}
