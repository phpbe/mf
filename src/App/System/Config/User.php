<?php
namespace Be\App\System\Config;

/**
 * @BeConfig("用户")
 */
class User
{

    /**
     * @BeConfigItem("用户小头像宽度",
     *     driver="ConfigItemInputNumberInt",
     *     description="单位：像素，修改后仅对此后上传的头像生效",
     *     ui="return [':min' => 1];")
     */
    public $avatarSW = 32;

    /**
     * @BeConfigItem("用户小头像高度",
     *     driver="ConfigItemInputNumberInt",
     *     description="单位：像素，修改后仅对此后上传的头像生效",
     *     ui="return [':min' => 1];"
     * )
     */
    public $avatarSH = 32;

    /**
     * @BeConfigItem("用户中头像宽度",
     *     driver="ConfigItemInputNumberInt",
     *     description="单位：像素，修改后仅对此后上传的头像生效",
     *     ui="return [':min' => 1];")
     */
    public $avatarMW = 64;

    /**
     * @BeConfigItem("用户中头像高度",
     *     driver="ConfigItemInputNumberInt",
     *     description="单位：像素，修改后仅对此后上传的头像生效",
     *     ui="return [':min' => 1];")
     */
    public $avatarMH = 64;

    /**
     * @BeConfigItem("用户大头像宽度",
     *     driver="ConfigItemInputNumberInt",
     *     description="单位：像素，修改后仅对此后上传的头像生效",
     *     ui="return [':min' => 1];")
     */
    public $avatarLW = 96;

    /**
     * @BeConfigItem("用户大头像高度",
     *     driver="ConfigItemInputNumberInt",
     *     description="单位：像素，修改后仅对此后上传的头像生效",
     *     ui="return [':min' => 1];")
     */
    public $avatarLH = 96;


}
