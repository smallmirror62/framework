<?php
// +----------------------------------------------------------------------
// | Leaps Framework [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011-2014 Leaps Team (http://www.tintsoft.com)
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author XuTongle <xutongle@gmail.com>
// +----------------------------------------------------------------------
namespace Leaps\Cache;

use Leaps\Core\InvalidConfigException;

class MemCache extends Adapter
{
	public $useMemcached = false;
	public $persistentId;
	public $options;
	public $username;
	public $password;
	private $_cache = null;

	/**
	 * 缓存服务器连接实例
	 * @var array
	 */
	private $_servers = [ ];

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Di\Injectable::init()
	 */
	public function init()
	{
		parent::init ();
		$this->addServers ( $this->getMemcache (), $this->getServers () );
	}


	protected function addServers($cache, $servers)
	{
		if (empty ( $servers )) {
			$servers = [ new MemCacheServer ( [ 'host' => '127.0.0.1','port' => 11211 ] ) ];
		} else {
			foreach ( $servers as $server ) {
				if ($server->host === null) {
					throw new InvalidConfigException ( "The 'host' property must be specified for every memcache server." );
				}
			}
		}
		if ($this->useMemcached) {
			$this->addMemcachedServers ( $cache, $servers );
		} else {
			$this->addMemcacheServers ( $cache, $servers );
		}
	}

	/**
	 *
	 * @param \Memcached $cache
	 * @param array $servers
	 */
	protected function addMemcachedServers($cache, $servers)
	{
		$existingServers = [ ];
		if ($this->persistentId !== null) {
			foreach ( $cache->getServerList () as $s ) {
				$existingServers [$s ['host'] . ':' . $s ['port']] = true;
			}
		}
		foreach ( $servers as $server ) {
			if (empty ( $existingServers ) || ! isset ( $existingServers [$server->host . ':' . $server->port] )) {
				$cache->addServer ( $server->host, $server->port, $server->weight );
			}
		}
	}

	/**
	 *
	 * @param \Memcache $cache
	 * @param array $servers
	 */
	protected function addMemcacheServers($cache, $servers)
	{
		$class = new \ReflectionClass ( $cache );
		$paramCount = $class->getMethod ( 'addServer' )->getNumberOfParameters ();
		foreach ( $servers as $server ) {
			$timeout = ( int ) ($server->timeout / 1000) + (($server->timeout % 1000 > 0) ? 1 : 0);
			if ($paramCount === 9) {
				$cache->addServer ( $server->host, $server->port, $server->persistent, $server->weight, $timeout, $server->retryInterval, $server->status, $server->failureCallback, $server->timeout );
			} else {
				$cache->addServer ( $server->host, $server->port, $server->persistent, $server->weight, $timeout, $server->retryInterval, $server->status, $server->failureCallback );
			}
		}
	}

	/**
	 * 返回基础 memcache (或 memcached) 对象
	 *
	 * @return \Memcache|\Memcached the memcache (or memcached) object used by this cache component.
	 * @throws InvalidConfigException if memcache or memcached extension is not loaded
	 */
	public function getMemcache()
	{
		if ($this->_cache === null) {
			$extension = $this->useMemcached ? 'memcached' : 'memcache';
			if (! extension_loaded ( $extension )) {
				throw new InvalidConfigException ( "MemCache requires PHP $extension extension to be loaded." );
			}
			if ($this->useMemcached) {
				$this->_cache = $this->persistentId !== null ? new \Memcached ( $this->persistentId ) : new \Memcached ();
				if ($this->username !== null || $this->password !== null) {
					$this->_cache->setOption ( \Memcached::OPT_BINARY_PROTOCOL, true );
					$this->_cache->setSaslAuthData ( $this->username, $this->password );
				}
				if (! empty ( $this->options )) {
					$this->_cache->setOptions ( $this->options );
				}
			} else {
				$this->_cache = new \Memcache ();
			}
		}
		return $this->_cache;
	}

	/**
	 * 返回 memcache 或 memcached 服务器配置
	 *
	 * @return MemCacheServer[] list of memcache server configurations.
	 */
	public function getServers()
	{
		return $this->_servers;
	}

	/**
	 * 设置 memcache 或 memcached 服务器配置
	 * @param array $config list of memcache or memcached server configurations. Each element must be an array
	 *        with the following keys: host, port, persistent, weight, timeout, retryInterval, status.
	 * @see http://php.net/manual/en/memcache.addserver.php
	 * @see http://php.net/manual/en/memcached.addserver.php
	 */
	public function setServers($config)
	{
		foreach ( $config as $c ) {
			$this->_servers [] = new MemCacheServer ( $c );
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::getValue()
	 */
	protected function getValue($key)
	{
		return $this->_cache->get ( $key );
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::getValues()
	 */
	protected function getValues($keys)
	{
		return $this->useMemcached ? $this->_cache->getMulti ( $keys ) : $this->_cache->get ( $keys );
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::setValue()
	 */
	protected function setValue($key, $value, $duration)
	{
		$expire = $duration > 0 ? $duration + time () : 0;
		return $this->useMemcached ? $this->_cache->set ( $key, $value, $expire ) : $this->_cache->set ( $key, $value, 0, $expire );
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::setValues()
	 */
	protected function setValues($data, $duration)
	{
		if ($this->useMemcached) {
			$this->_cache->setMulti ( $data, $duration > 0 ? $duration + time () : 0 );

			return [ ];
		} else {
			return parent::setValues ( $data, $duration );
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::addValue()
	 */
	protected function addValue($key, $value, $duration)
	{
		$expire = $duration > 0 ? $duration + time () : 0;

		return $this->useMemcached ? $this->_cache->add ( $key, $value, $expire ) : $this->_cache->add ( $key, $value, 0, $expire );
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::deleteValue()
	 */
	protected function deleteValue($key)
	{
		return $this->_cache->delete ( $key, 0 );
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::flushValues()
	 */
	protected function flushValues()
	{
		return $this->_cache->flush ();
	}
}