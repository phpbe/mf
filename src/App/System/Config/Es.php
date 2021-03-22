<?php

namespace Be\Mf\App\System\Config;

/**
 * @BeConfig("Es")
 */
class Es
{

    /**
     * @BeConfigItem("ES服务器", driver="FormItemCode", language="json", valueType = "array(string)")
     */
    public $hosts = ['172.24.0.130:9200'];

}
