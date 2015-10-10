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
class Controller {

	/**
	 * 控制器ID
	 * @var string
	 */
	public $id;

	/**
	 * 该控制器所属模块
	 * @var Module $module
	 */
	public $module;

	/**
     * @param string $id 控制器ID
     * @param Module $module 该控制器所属模块
     * @param array $config 用来初始化对象属性的数组
     */
    public function __construct($id, $module, $config = [])
    {
        $this->id = $id;
        $this->module = $module;
        //parent::__construct($config);
    }

    /**
     * 默认操作
     * @var string
     */
    public $defaultAction = 'index';
}