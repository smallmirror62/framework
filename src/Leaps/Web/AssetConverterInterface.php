<?php

/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */
namespace Leaps\Web;

/**
 * 资源文件编译
 */
interface AssetConverterInterface
{

	/**
	 * 编译一个资源文件到CSS或JS文件
	 * @param string $asset the asset file path, relative to $basePath
	 * @param string $basePath the directory the $asset is relative to.
	 * @return string the converted asset file path, relative to $basePath.
	 */
	public function convert($asset, $basePath);
}
