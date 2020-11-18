<?php
namespace Be\System\Traits;

/**
 * 缓存代理
 */
abstract class CacheProxy
{
    /**
     * 启动缓存代理
     *
     * @param int $expire 超时时间
     * @return \Be\System\CacheProxy | Mixed
     */
    public function withCache($expire = 600)
    {
        return new \Be\System\CacheProxy($this, $expire);
    }

}
