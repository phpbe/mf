<?php
namespace Be\System\Session\Driver;

use Be\System\Response;

/**
 * memcache session
 */
class MemcacheImpl extends \SessionHandler
{

	private $expire = 1440; // session 超时时间

	/**
	 * @var \Memcache
	 */
	private $handler = null;
	private $options = null;

	/**
	 * 构造函数
	 *
	 * @param object $configSession session 配直参数
     * @throws \Exception
	 */
	public function __construct($configSession)
	{
		if (!extension_loaded('Memcache')) throw new \Exception('SESSION 初始化失败：服务器未安装 memcache 扩展！');

		if (isset($configSession->memcache)) {
			$this->options = $configSession->memcache;
		}
		$this->expire = $configSession->expire;
	}

	/**
	 * 初始化 session
	 *
	 * @param string $savePath 保存路径
	 * @param string $sessionId session id
	 * @return bool
     * @throws \Exception
	 */
	public function open($savePath, $sessionId) {
		$options = $this->options;
		if ($options === null) {
            throw new \Exception('SESSION 初始化失败：memcache 配置参数错误！');
		} else {
			$this->handler = new \Memcache;
			foreach ($options as $option) {
				if ($option['timeout'] > 0) {
					$this->handler->addServer($option['host'], $option['port'], $option['persistent'], $option['weight'], $option['timeout']);
				} else {
					$this->handler->addServer($option['host'], $option['port'], $option['persistent'], $option['weight']);
				}
			}
		}
		return true;
	}

	/**
	 * 关闭 session
	 *
	 * @return bool
	 */
	public function close() {
		return true;
	}

	/**
	 * 讯取 session 数据
	 *
	 * @param string $sessionId session id
	 * @return string
	 */
	public function read($sessionId) {
		return $this->handler->get('session:'.$sessionId);
	}

	/**
	 * 写入 session 数据
	 *
	 * @param string $sessionId session id
	 * @param string $sessionData 写入 session 的数据
	 * @return bool
	 */
	public function write($sessionId, $sessionData) {
		return $this->handler->set('session:'.$sessionId, $sessionData, 0 , $this->expire);
	}
	/**
	 * 销毁 session
	 *
	 * @param int $sessionId 要销毁的 session 的 session id
	 * @return bool
	 */
	public function destroy($sessionId) {
		return $this->handler->delete('session:'.$sessionId);
	}

	/**
	 * 垃圾回收
	 *
	 * @param int $maxLifetime 最大生存时间
	 * @return bool
	 */
	public function gc($maxLifetime) {
		return true;
	}

}
