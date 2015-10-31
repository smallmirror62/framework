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
namespace Leaps\Core;

use Leaps;

class View extends Base
{

	/**
	 * 默认的模板文件后缀
	 * @var string
	 */
	public $defaultExtension = 'php';

	/**
	 * 风格对象实例或配置
	 * @var Theme|array|string
	 */
	public $theme;

	/**
	 * 已加载的视图文件列表
	 * @var array the view files currently being rendered. There may be multiple view files being
	 *      rendered at a moment because one view may be rendered within another.
	 */
	private $_viewFiles = [ ];

	/**
	 * 初始化
	 */
	public function init()
	{
		parent::init ();
		if (is_array ( $this->theme )) {
			if (! isset ( $this->theme ['className'] )) {
				$this->theme ['className'] = 'Leaps\Core\Theme';
			}
			$this->theme = Leaps::createObject ( $this->theme );
		} elseif (is_string ( $this->theme )) {
			$this->theme = Leaps::createObject ( $this->theme );
		}
	}

	/**
	 * 渲染视图
	 *
	 * @param string $view 视图名称
	 * @param array $params 参数
	 * @param string $context 渲染结果
	 */
	public function render($view, $params = [], $context = null)
	{
		$viewFile = $this->findViewFile ( $view, $context );
		return $this->renderFile ( $viewFile, $params, $context );
	}

	/**
	 * 根据视图名称查找视图文件
	 *
	 * @param string $view 视图的文件名或路径别名。
	 * @param object $context 被分配到视图的上下文
	 * @return string 视图文件路径。注意该文件可能不存在。
	 * @throws InvalidCallException if a relative view name is given while there is no active context to
	 *         determine the corresponding view file.
	 */
	protected function findViewFile($view, $context = null)
	{
		if (strncmp ( $view, '@', 1 ) === 0) {
			// e.g. "@app/views/main"
			$file = Leaps::getAlias ( $view );
		} elseif (strncmp ( $view, '//', 2 ) === 0) {
			// e.g. "//layouts/main"
			$file = Leaps::$app->getViewPath () . DIRECTORY_SEPARATOR . ltrim ( $view, '/' );
		} elseif (strncmp ( $view, '/', 1 ) === 0) {
			// e.g. "/site/index"
			if (Leaps::$app->controller !== null) {
				$file = Leaps::$app->controller->module->getViewPath () . DIRECTORY_SEPARATOR . ltrim ( $view, '/' );
			} else {
				throw new InvalidCallException ( "Unable to locate view file for view '$view': no active controller." );
			}
		} elseif ($context instanceof ViewContextInterface) {
			$file = $context->getViewPath () . DIRECTORY_SEPARATOR . $view;
		} elseif (($currentViewFile = $this->getViewFile ()) !== false) {
			$file = dirname ( $currentViewFile ) . DIRECTORY_SEPARATOR . $view;
		} else {
			throw new InvalidCallException ( "Unable to resolve view file for view '$view': no active view context." );
		}
		if (pathinfo ( $file, PATHINFO_EXTENSION ) !== '') {
			return $file;
		}
		$path = $file . '.' . $this->defaultExtension;
		if ($this->defaultExtension !== 'php' && ! is_file ( $path )) {
			$path = $file . '.php';
		}

		return $path;
	}

	/**
	 * 渲染视图文件
	 *
	 * If [[theme]] is enabled (not null), it will try to render the themed version of the view file as long
	 * as it is available.
	 *
	 * The method will call [[FileHelper::localize()]] to localize the view file.
	 *
	 * If [[renderers|renderer]] is enabled (not null), the method will use it to render the view file.
	 * Otherwise, it will simply include the view file as a normal PHP file, capture its output and
	 * return it as a string.
	 *
	 * @param string $viewFile the view file. This can be either an absolute file path or an alias of it.
	 * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
	 * @param object $context the context that the view should use for rendering the view. If null,
	 *        existing [[context]] will be used.
	 * @return string the rendering result
	 * @throws InvalidParamException if the view file does not exist
	 */
	public function renderFile($viewFile, $params = [], $context = null)
	{
		$viewFile = Leaps::getAlias ( $viewFile );
		if ($this->theme !== null) {
			$viewFile = $this->theme->applyTo ( $viewFile );
		}
		if (is_file ( $viewFile )) {
			$viewFile = FileHelper::localize ( $viewFile );
		} else {
			throw new InvalidParamException ( "The view file does not exist: $viewFile" );
		}

		$oldContext = $this->context;
		if ($context !== null) {
			$this->context = $context;
		}
		$output = '';
		$this->_viewFiles [] = $viewFile;

		if ($this->beforeRender ( $viewFile, $params )) {
			Leaps::trace ( "Rendering view file: $viewFile", __METHOD__ );
			$ext = pathinfo ( $viewFile, PATHINFO_EXTENSION );
			if (isset ( $this->renderers [$ext] )) {
				if (is_array ( $this->renderers [$ext] ) || is_string ( $this->renderers [$ext] )) {
					$this->renderers [$ext] = Leaps::createObject ( $this->renderers [$ext] );
				}
				/* @var $renderer ViewRenderer */
				$renderer = $this->renderers [$ext];
				$output = $renderer->render ( $this, $viewFile, $params );
			} else {
				$output = $this->renderPhpFile ( $viewFile, $params );
			}
			$this->afterRender ( $viewFile, $params, $output );
		}

		array_pop ( $this->_viewFiles );
		$this->context = $oldContext;

		return $output;
	}

	/**
	 * 目前正在使用的视图文件。
	 *
	 * @return string|boolean
	 */
	public function getViewFile()
	{
		return end ( $this->_viewFiles );
	}

	/**
	 * 返回一个视图文件作为PHP脚本
	 *
	 * @param string $_file_ 文件路径
	 * @param array $_params_ 二维数组的配置
	 * @return string 渲染结果
	 */
	public function renderPhpFile($_file_, $_params_ = [])
	{
		ob_start ();
		ob_implicit_flush ( false );
		extract ( $_params_, EXTR_OVERWRITE );
		require ($_file_);
		return ob_get_clean ();
	}
}