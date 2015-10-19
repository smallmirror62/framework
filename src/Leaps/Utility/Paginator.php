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
namespace Leaps\Utility;

class Paginator
{

	/**
	 * 当前页面的结果
	 *
	 * @var array
	 */
	public $results;

	/**
	 * 当前页面
	 *
	 * @var int
	 */
	public $page;

	/**
	 * 结果集可用的最后一页
	 *
	 * @var int
	 */
	public $last;

	/**
	 * 总页数
	 *
	 * @var int
	 */
	public $total;

	/**
	 * 每页的项目数
	 *
	 * @var int
	 */
	public $perPage;

	/**
	 * 应该附加到连接查询字符串结尾的值
	 *
	 * @var array
	 */
	protected $appends;

	/**
	 * 编译的附属物，将附加到链接
	 *
	 * @var string
	 */
	protected $appendage;

	/**
	 * 要创建分页链接时使用的语言
	 *
	 * @var string
	 */
	protected $language;

	/**
	 * The "dots" element used in the pagination slider.
	 *
	 * @var string
	 */
	protected $dots = '<li class="dots disabled"><a href="#">...</a></li>';

	/**
	 * 创建一个新的页面实例
	 *
	 * @param array $results
	 * @param int $page
	 * @param int $total
	 * @param int $perPage
	 * @param int $last
	 * @return void
	 */
	protected function __construct($results, $page, $total, $perPage, $last)
	{
		$this->page = $page;
		$this->last = $last;
		$this->total = $total;
		$this->results = $results;
		$this->perPage = $perPage;
	}

	/**
	 * 创建一个新的页面实例
	 *
	 * @param array $results
	 * @param int $total
	 * @param int $per_page
	 * @return Paginator
	 */
	public static function make($results, $total, $perPage)
	{
		$page = static::page ( $total, $perPage );
		$last = ceil ( $total / $perPage );
		return new static ( $results, $page, $total, $perPage, $last );
	}

	/**
	 * Get the current page from the request query string.
	 *
	 * @param int $total
	 * @param int $per_page
	 * @return int
	 */
	public static function page($total, $per_page)
	{
		$page = Input::get ( 'page', 1 );
		if (is_numeric ( $page ) and $page > $last = ceil ( $total / $per_page )) {
			return ($last > 0) ? $last : 1;
		}
		return (static::valid ( $page )) ? $page : 1;
	}

	/**
	 * Determine if a given page number is a valid page.
	 *
	 * A valid page must be greater than or equal to one and a valid integer.
	 *
	 * @param int $page
	 * @return bool
	 */
	protected static function valid($page)
	{
		return $page >= 1 and filter_var ( $page, FILTER_VALIDATE_INT ) !== false;
	}

	/**
	 * Create the HTML pagination links.
	 *
	 * Typically, an intelligent, "sliding" window of links will be rendered based
	 * on the total number of pages, the current page, and the number of adjacent
	 * pages that should rendered. This creates a beautiful paginator similar to
	 * that of Google's.
	 *
	 * Example: 1 2 ... 23 24 25 [26] 27 28 29 ... 51 52
	 *
	 * If you wish to render only certain elements of the pagination control,
	 * explore some of the other public methods available on the instance.
	 *
	 * <code>
	 * // Render the pagination links
	 * echo $paginator->links();
	 *
	 * // Render the pagination links using a given window size
	 * echo $paginator->links(5);
	 * </code>
	 *
	 * @param int $adjacent
	 * @return string
	 */
	public function links($adjacent = 3)
	{
		if ($this->last <= 1)
			return '';
		if ($this->last < 7 + ($adjacent * 2)) {
			$links = $this->range ( 1, $this->last );
		} else {
			$links = $this->slider ( $adjacent );
		}
		$content = '<ul>' . $this->previous () . $links . $this->next () . '</ul>';
		return '<div class="pagination">' . $content . '</div>';
	}

	/**
	 * Build sliding list of HTML numeric page links.
	 *
	 * This method is very similar to the "links" method, only it does not
	 * render the "first" and "last" pagination links, but only the pages.
	 *
	 * <code>
	 * // Render the pagination slider
	 * echo $paginator->slider();
	 *
	 * // Render the pagination slider using a given window size
	 * echo $paginator->slider(5);
	 * </code>
	 *
	 * @param int $adjacent
	 * @return string
	 */
	public function slider($adjacent = 3)
	{
		$window = $adjacent * 2;
		if ($this->page <= $window) {
			return $this->range ( 1, $window + 2 ) . ' ' . $this->ending ();
		}		// Example: 1 2 ... 32 33 34 35 [36] 37
		elseif ($this->page >= $this->last - $window) {
			return $this->beginning () . ' ' . $this->range ( $this->last - $window - 2, $this->last );
		}
		// Example: 1 2 ... 23 24 25 [26] 27 28 29 ... 51 52
		$content = $this->range ( $this->page - $adjacent, $this->page + $adjacent );
		return $this->beginning () . ' ' . $content . ' ' . $this->ending ();
	}

	/**
	 * Generate the "previous" HTML link.
	 *
	 * <code>
	 * // Create the "previous" pagination element
	 * echo $paginator->previous();
	 *
	 * // Create the "previous" pagination element with custom text
	 * echo $paginator->previous('Go Back');
	 * </code>
	 *
	 * @param string $text
	 * @return string
	 */
	public function previous($text = null)
	{
		$disabled = function ($page)
		{
			return $page <= 1;
		};
		return $this->element ( __FUNCTION__, $this->page - 1, $text, $disabled );
	}

	/**
	 * Generate the "next" HTML link.
	 *
	 * <code>
	 * // Create the "next" pagination element
	 * echo $paginator->next();
	 *
	 * // Create the "next" pagination element with custom text
	 * echo $paginator->next('Skip Forwards');
	 * </code>
	 *
	 * @param string $text
	 * @return string
	 */
	public function next($text = null)
	{
		$disabled = function ($page, $last)
		{
			return $page >= $last;
		};
		return $this->element ( __FUNCTION__, $this->page + 1, $text, $disabled );
	}

	/**
	 * Create a chronological pagination element, such as a "previous" or "next" link.
	 *
	 * @param string $element
	 * @param int $page
	 * @param string $text
	 * @param Closure $disabled
	 * @return string
	 */
	protected function element($element, $page, $text, $disabled)
	{
		$class = "{$element}_page";

		if (is_null ( $text )) {
			$text = Lang::line ( "pagination.{$element}" )->get ( $this->language );
		}
		if ($disabled ( $this->page, $this->last )) {
			return '<li' . HTML::attributes ( array ('class' => "{$class} disabled" ) ) . '><a href="#">' . $text . '</a></li>';
		} else {
			return $this->link ( $page, $text, $class );
		}
	}

	/**
	 * Build the first two page links for a sliding page range.
	 *
	 * @return string
	 */
	protected function beginning()
	{
		return $this->range ( 1, 2 ) . ' ' . $this->dots;
	}

	/**
	 * Build the last two page links for a sliding page range.
	 *
	 * @return string
	 */
	protected function ending()
	{
		return $this->dots . ' ' . $this->range ( $this->last - 1, $this->last );
	}

	/**
	 * Build a range of numeric pagination links.
	 *
	 * For the current page, an HTML span element will be generated instead of a link.
	 *
	 * @param int $start
	 * @param int $end
	 * @return string
	 */
	protected function range($start, $end)
	{
		$pages = array ();
		for($page = $start; $page <= $end; $page ++) {
			if ($this->page == $page) {
				$pages [] = '<li class="active"><a href="#">' . $page . '</a></li>';
			} else {
				$pages [] = $this->link ( $page, $page, null );
			}
		}
		return implode ( ' ', $pages );
	}

	/**
	 * Create a HTML page link.
	 *
	 * @param int $page
	 * @param string $text
	 * @param string $class
	 * @return string
	 */
	protected function link($page, $text, $class)
	{
		$query = '?page=' . $page . $this->appendage ( $this->appends );
		return '<li' . HTML::attributes ( array ('class' => $class ) ) . '>' . HTML::link ( URI::current () . $query, $text, array (), Request::secure () ) . '</li>';
	}

	/**
	 * Create the "appendage" to be attached to every pagination link.
	 *
	 * @param array $appends
	 * @return string
	 */
	protected function appendage($appends)
	{
		if (! is_null ( $this->appendage ))
			return $this->appendage;
		if (count ( $appends ) <= 0) {
			return $this->appendage = '';
		}
		return $this->appendage = '&' . http_build_query ( $appends );
	}

	/**
	 * Set the items that should be appended to the link query strings.
	 *
	 * @param array $values
	 * @return Paginator
	 */
	public function appends($values)
	{
		$this->appends = $values;
		return $this;
	}

	/**
	 * Set the language that should be used when creating the pagination links.
	 *
	 * @param string $language
	 * @return Paginator
	 */
	public function speaks($language)
	{
		$this->language = $language;
		return $this;
	}
}