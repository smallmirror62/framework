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
namespace Leaps\Http;

class UserAgent
{

	/**
	 * 系统平台
	 *
	 * @var array
	 */
	public $platforms = [
			//Windows
			'windows nt 10.0' => 'Windows 10',
			'windows nt 6.3' => 'Windows 8.1',
			'windows nt 6.2' => 'Windows 8',
			'windows nt 6.1' => 'Windows 7',
			'windows nt 6.0' => 'Windows Longhorn',
			'windows nt 5.2' => 'Windows 2003',
			'windows nt 5.0' => 'Windows 2000',
			'windows nt 5.1' => 'Windows XP',
			'windows nt 4.0' => 'Windows NT 4.0',
			'winnt4.0' => 'Windows NT 4.0',
			'winnt 4.0' => 'Windows NT',
			'winnt' => 'Windows NT',
			'windows 98' => 'Windows 98',
			'win98' => 'Windows 98',
			'windows 95' => 'Windows 95',
			'win95' => 'Windows 95',
			'windows phone' => 'Windows Phone',
			'windows' => 'Unknown Windows OS',
			//OSX
			'os x' => 'MacOS X',
			'ppc mac' => 'Power PC Mac',
			//Android
			'android' => 'Android',
			//LINUX
			'freebsd' => 'FreeBSD',
			'ubuntu' => 'Ubuntu',
			'debian' => 'Debian',
			'ppc' => 'Macintosh',
			'linux' => 'Linux',
			'openbsd' => 'OpenBSD',
			'gnu' => 'GNU/Linux',
			'meego'=>'MeeGo',
			'unix' => 'Unknown Unix OS'
	];

	/**
	 * 浏览器
	 *
	 * @var string
	 */
	public $browsers = [
			'Opera' => 'Opera',
			'MSIE' => 'IE',
			'rv:11.0' =>'IE',
			'Edge'=>'Edge',
			'Maxthon'=>'Maxthon',
			'QQBrowser'=>'QQBrowser',
			'UCWEB'=>'UCWEB',
			'Chrome' => 'Chrome',
			'Shiira' => 'Shiira',
			'Firefox' => 'Firefox',
			'Netscape' => 'Netscape',
			'MicroMessenger' => 'Wechat',
			'NokiaBrowser'=>'NokiaBrowser',
			'Nokia'=>'Nokia',
			'BlackBerry'=>'BlackBerry',
			'Safari' => 'Safari',
			'Mozilla' => 'Mozilla'
	];

	/**
	 * 手机
	 *
	 * @var array
	 */
	public $mobiles = [
			'mobileexplorer' => 'Mobile Explorer',
			'palmsource' => 'Palm',
			'palmscape' => 'Palmscape',
			'motorola' => "Motorola",
			'nokia' => "Nokia",
			'palm' => "Palm",
			'iphone' => "Apple iPhone",
			'ipad' => "Apple iPad",
			'ipod' => "Apple iPod Touch",
			'sony' => "Sony Ericsson",
			'ericsson' => "Sony Ericsson",
			'blackberry' => "BlackBerry",
			'cocoon' => "O2 Cocoon",
			'blazer' => "Treo",
			'lg' => "LG",
			'amoi' => "Amoi",
			'xda' => "XDA",
			'mda' => "MDA",
			'vario' => "Vario",
			'htc' => "HTC",
			'samsung' => "Samsung",
			'sharp' => "Sharp",
			'sie-' => "Siemens",
			'alcatel' => "Alcatel",
			'benq' => "BenQ",
			'ipaq' => "HP iPaq",
			'mot-' => "Motorola",
			'playstation portable' => "PlayStation Portable",
			'hiptop' => "Danger Hiptop",
			'nec-' => "NEC",
			'panasonic' => "Panasonic",
			'philips' => "Philips",
			'sagem' => "Sagem",
			'sanyo' => "Sanyo",
			'spv' => "SPV",
			'zte' => "ZTE",
			'sendo' => "Sendo",
			'nexus' => "Nexus",
			'xbox'=>'Xbox',
			'bb10'=>'BlackBerry',

			// Operating Systems
			'symbian' => "Symbian",
			'SymbianOS' => "SymbianOS",
			'elaine' => "Palm",
			'palm' => "Palm",
			'series60' => "Symbian S60",
			'windows ce' => "Windows CE",

			// Fallback
			'mobile' => "Generic Mobile",
			'wireless' => "Generic Mobile",
			'j2me' => "Generic Mobile",
			'midp' => "Generic Mobile",
			'cldc' => "Generic Mobile",
			'up.link' => "Generic Mobile",
			'up.browser' => "Generic Mobile",
			'smartphone' => "Generic Mobile",
			'cellphone' => "Generic Mobile"
	];

	/**
	 * 机器人
	 *
	 * @var array
	 */
	public $robots = [
			'googlebot' => 'Googlebot',
			'bingbot' => 'BingBot',
			'slurp' => 'Inktomi Slurp',
			'yahoo' => 'Yahoo',
			'askjeeves' => 'AskJeeves',
			'fastcrawler' => 'FastCrawler',
			'infoseek' => 'InfoSeek Robot 1.0',
			'lycos' => 'Lycos'
	];

	/**
	 * user-Agent字符串
	 *
	 * @var string
	 */
	private $userAgent;

	/**
	 * 是否是浏览器
	 *
	 * @var boolean
	 */
	private $isBrowser = false;

	/**
	 * 是否是机器人
	 *
	 * @var boolean
	 */
	private $isRobot = false;

	/**
	 * 是否是移动设备
	 *
	 * @var boolean
	 */
	private $isMobile = false;

	/**
	 * 解析出的操作系统平台
	 *
	 * @var string
	 */
	private $platformName = '';

	/**
	 * 解析出的浏览器名称
	 *
	 * @var string
	 */
	private $browserName = '';

	/**
	 * 解析出的浏览器版本
	 *
	 * @var string
	 */
	private $browserVersion = '';

	/**
	 * 解析出的移动设备名称
	 *
	 * @var string
	 */
	private $mobileName = '';

	/**
	 * 解析出的机器人名称
	 *
	 * @var string
	 */
	private $robotName = '';

	/**
	 * 构造方法
	 *
	 * @return void
	 */
	public function __construct($userAgent = '')
	{
		if ($userAgent != '') {
			$this->userAgent = $userAgent;
		} else if (isset ( $_SERVER ['HTTP_USER_AGENT'] )) {
			$this->userAgent = trim ( $_SERVER ['HTTP_USER_AGENT'] );
		}
		if (! is_null ( $this->userAgent )) {
			$this->compileData ();
		}
	}

	/**
	 * 获取User Agent字符串
	 *
	 * @return string
	 */
	public function getString()
	{
		return $this->userAgent;
	}

	/**
	 * 获取操作系统平台
	 *
	 * @access public
	 * @return string
	 */
	public function getPlatform()
	{
		return $this->platformName;
	}

	/**
	 * 获取浏览器
	 *
	 * @access public
	 * @return string
	 */
	public function getBrowser()
	{
		return $this->browserName;
	}

	/**
	 * 获取浏览器版本
	 *
	 * @return string
	 */
	public function getBrowserVersion()
	{
		return $this->browserVersion;
	}

	/**
	 * 获取机器人名称
	 *
	 * @return string
	 */
	public function getRobot()
	{
		return $this->robotName;
	}

	/**
	 * 获取移动设备名称
	 *
	 * @return string
	 */
	public function getMobile()
	{
		return $this->mobileName;
	}

	/**
	 * 是否是浏览器
	 *
	 * @access public
	 * @return bool
	 */
	public function isBrowser($key = null)
	{
		if (! $this->isBrowser) {
			return false;
		}
		if ($key === null) {
			return true;
		}
		return array_key_exists ( $key, $this->browsers ) and $this->browserName === $this->browsers [$key];
	}

	/**
	 * 是否是机器人
	 *
	 * @return bool
	 */
	public function isRobot($key = null)
	{
		if (! $this->isRobot) {
			return false;
		}

		if ($key === null) {
			return true;
		}
		return array_key_exists ( $key, $this->robots ) and $this->robotName === $this->robots [$key];
	}

	/**
	 * 是否是移动设备
	 *
	 * @return bool
	 */
	public function isMobile($key = null)
	{
		if (! $this->isMobile) {
			return false;
		}
		if ($key === null) {
			return true;
		}
		return array_key_exists ( $key, $this->mobiles ) and $this->mobileName === $this->mobiles [$key];
	}

	/**
	 * 编译 User Agent
	 *
	 * @return bool
	 */
	private function compileData()
	{
		$this->setPlatform ();
		foreach ( [
				'setRobot',
				'setBrowser',
				'setMobile'
		] as $function ) {
			if ($this->$function () === true) {
				break;
			}
		}
	}

	/**
	 * 设置系统平台
	 *
	 * @return mixed
	 */
	private function setPlatform()
	{
		if (is_array ( $this->platforms ) and count ( $this->platforms ) > 0) {
			foreach ( $this->platforms as $key => $val ) {
				if (preg_match ( "|" . preg_quote ( $key ) . "|i", $this->userAgent )) {
					$this->platformName = $val;
					return true;
				}
			}
		}
		$this->platformName = 'Unknown Platform';
	}

	/**
	 * 设置浏览器
	 *
	 * @return bool
	 */
	private function setBrowser()
	{
		if (is_array ( $this->browsers ) and count ( $this->browsers ) > 0) {
			foreach ( $this->browsers as $key => $val ) {
				if (preg_match ( "|" . preg_quote ( $key ) . ".*?([0-9\\.]+)|i", $this->userAgent, $match )) {
					$this->isBrowser = true;
					$this->browserVersion = $match [1];
					$this->browserName = $val;
					$this->setMobile ();
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * 设置机器人
	 *
	 * @return bool
	 */
	private function setRobot()
	{
		if (is_array ( $this->robots ) and count ( $this->robots ) > 0) {
			foreach ( $this->robots as $key => $val ) {
				if (preg_match ( "|" . preg_quote ( $key ) . "|i", $this->userAgent )) {
					$this->isRobot = true;
					$this->robotName = $val;
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * 设置移动设备
	 *
	 * @return bool
	 */
	private function setMobile()
	{
		if (is_array ( $this->mobiles ) and count ( $this->mobiles ) > 0) {
			foreach ( $this->mobiles as $key => $val ) {
				if (false !== (strpos ( strtolower ( $this->userAgent ), $key ))) {
					$this->isMobile = true;
					$this->mobileName = $val;
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * 获取字符串
	 */
	public function __toString(){
		return $this->userAgent;
	}
}