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

use Leaps\Kernel;

class FileCache extends Adapter
{
	/**
	 * 缓存前缀
	 *
	 * @var string
	 */
	public $keyPrefix = '';

	/**
	 * 缓存文件夹
	 *
	 * @var string
	 */
	public $cachePath = '@Runtime/cache';

	/**
	 * 缓存文件后缀
	 *
	 * @var string
	 */
	public $cacheFileSuffix = '.bin';

	/**
	 * 缓存文件夹保存深度
	 *
	 * @var integer
	 */
	public $directoryLevel = 1;

	/**
	 * 垃圾收集概率 默认10，表示0.001%的几率。
	 *
	 * @var integer
	 */
	public $gcProbability = 10;

	/**
	 * 缓存文件的权限
	 *
	 * @var integer
	 */
	public $fileMode;

	/**
	 * 缓存文件夹权限
	 *
	 * @var integer
	 */
	public $dirMode = 0775;

	/**
	 * 文本存储组件名称和实例
	 *
	 * @var \Leaps\Filesystem\Filesystem|string
	 */
	private $file = 'file';

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Leaps\Di\Injectable::init()
	 */
	public function init()
	{
		if (! is_object ( $this->file )) {
			$this->file = $this->_dependencyInjector->getShared ( $this->file );
		}
		$this->cachePath = Kernel::getAlias ( $this->cachePath );
		if (! is_dir ( $this->cachePath )) {
			$this->file->createDirectory ( $this->cachePath, $this->dirMode, true );
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::exists()
	 */
	public function exists($key)
	{
		$cacheFile = $this->getCacheFile ( $this->buildKey ( $key ) );
		return @filemtime ( $cacheFile ) > time ();
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::getValue()
	 */
	protected function getValue($key)
	{
		$cacheFile = $this->getCacheFile ( $key );
		if (@filemtime ( $cacheFile ) > time ()) {
			return @file_get_contents ( $cacheFile );
		} else {
			return false;
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::setValue()
	 */
	protected function setValue($key, $value, $duration)
	{
		$cacheFile = $this->getCacheFile ( $key );
		if ($this->directoryLevel > 0) {
			@$this->file->createDirectory ( dirname ( $cacheFile ), $this->dirMode, true );
		}
		if (@file_put_contents ( $cacheFile, $value, LOCK_EX ) !== false) {
			if ($this->fileMode !== null) {
				@chmod ( $cacheFile, $this->fileMode );
			}
			if ($duration <= 0) {
				$duration = 31536000; // 1 year
			}

			return @touch ( $cacheFile, $duration + time () );
		} else {
			return false;
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::addValue()
	 */
	protected function addValue($key, $value, $duration)
	{
		$cacheFile = $this->getCacheFile ( $key );
		if (@filemtime ( $cacheFile ) > time ()) {
			return false;
		}

		return $this->setValue ( $key, $value, $duration );
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::deleteValue()
	 */
	protected function deleteValue($key)
	{
		$cacheFile = $this->getCacheFile ( $key );
		return @unlink ( $cacheFile );
	}

	/**
	 * 通过Key获取缓存文件路径
	 *
	 * @param string $key 缓存Key
	 * @return string 缓存文件路径
	 */
	protected function getCacheFile($key)
	{
		if ($this->directoryLevel > 0) {
			$base = $this->cachePath;
			for($i = 0; $i < $this->directoryLevel; ++ $i) {
				if (($prefix = substr ( $key, $i + $i, 2 )) !== false) {
					$base .= DIRECTORY_SEPARATOR . $prefix;
				}
			}
			return $base . DIRECTORY_SEPARATOR . $key . $this->cacheFileSuffix;
		} else {
			return $this->cachePath . DIRECTORY_SEPARATOR . $key . $this->cacheFileSuffix;
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::flushValues()
	 */
	protected function flushValues()
	{
		$this->gc ( true, false );
		return true;
	}

	/**
	 * 删除已过期的缓存文件
	 *
	 * @param boolean $force whether to enforce the garbage collection regardless of [[gcProbability]].
	 *        Defaults to false, meaning the actual deletion happens with the probability as specified by [[gcProbability]].
	 * @param boolean $expiredOnly whether to removed expired cache files only.
	 *        If false, all cache files under [[cachePath]] will be removed.
	 */
	public function gc($force = false, $expiredOnly = true)
	{
		if ($force || mt_rand ( 0, 1000000 ) < $this->gcProbability) {
			$this->gcRecursive ( $this->cachePath, $expiredOnly );
		}
	}

	/**
	 * 递归删除过期的缓存
	 *
	 * @param string $path 缓存我呢见目录
	 * @param boolean $expiredOnly 是否只能删除已过期的缓存文件。
	 */
	protected function gcRecursive($path, $expiredOnly)
	{
		if (($handle = opendir ( $path )) !== false) {
			while ( ($file = readdir ( $handle )) !== false ) {
				if ($file [0] === '.') {
					continue;
				}
				$fullPath = $path . DIRECTORY_SEPARATOR . $file;
				if (is_dir ( $fullPath )) {
					$this->gcRecursive ( $fullPath, $expiredOnly );
					if (! $expiredOnly) {
						if (! @rmdir ( $fullPath )) {
							$error = error_get_last ();
							// Yii::warning ( "Unable to remove directory '{$fullPath}': {$error['message']}", __METHOD__ );
						}
					}
				} elseif (! $expiredOnly || $expiredOnly && @filemtime ( $fullPath ) < time ()) {
					if (! @unlink ( $fullPath )) {
						$error = error_get_last ();
						// Yii::warning ( "Unable to remove file '{$fullPath}': {$error['message']}", __METHOD__ );
					}
				}
			}
			closedir ( $handle );
		}
	}
}