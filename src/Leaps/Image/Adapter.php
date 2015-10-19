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
namespace Leaps\Image;

abstract class Adapter
{
	protected $_image;
	protected $_file;
	protected $_realpath;
	protected $_width;
	protected $_height;
	protected $_type;
	protected $_mime;
	protected static $_checked = false;

	/**
	 * 将图像大小调整到给定大小
	 *
	 * @param int width
	 * @param int height
	 * @param int master
	 * @return Leaps\Image\Adapter
	 */
	public function resize($width = null, $height = null, $master = 7)
	{
		if ($master == Image::TENSILE) {
			if (! $width || ! $height) {
				throw new Exception ( "width and height must be specified" );
			}
		} else {
			if ($master == Image::AUTO) {
				if (! $width || ! $height) {
					throw new Exception ( "width and height must be specified" );
				}
				$master = ($this->_width / $width) > ($this->_height / $height) ? Image::WIDTH : Image::HEIGHT;
			}
			if ($master == Image::INVERSE) {
				if (! $width || ! $height) {
					throw new Exception ( "width and height must be specified" );
				}
				$master = ($this->_width / $width) > ($this->_height / $height) ? Image::HEIGHT : Image::WIDTH;
			}
			switch ($master) {
				case Image::WIDTH :
					if (! $width) {
						throw new Exception ( "width must be specified" );
					}
					$height = $this->_height * $width / $this->_width;
					break;

				case Image::HEIGHT :
					if (! $height) {
						throw new Exception ( "height must be specified" );
					}
					$width = $this->_width * $height / $this->_height;
					break;
				case Image::PRECISE :
					if (! $width || ! $height) {
						throw new Exception ( "width and height must be specified" );
					}
					$ratio = $this->_width / $this->_height;
					if (($width / $height) > $ratio) {
						$height = $this->_height * $width / $this->_width;
					} else {
						$width = $this->_width * $height / $this->_height;
					}
					break;

				case Image::NONE :

					if (! $width) {
						$width = ( int ) $this->_width;
					}

					if (! $height) {
						$width = ( int ) $this->_height;
					}
					break;
			}
		}

		$width = ( int ) max ( round ( $width ), 1 );
		$height = ( int ) max ( round ( $height ), 1 );
		$this->_resize ( $width, $height );
		return $this;
	}

	/**
	 * 裁剪图片到给定大小
	 *
	 * @param int width
	 * @param int height
	 * @param int offset_x
	 * @param int offset_y
	 * @return \Leaps\Image\Adapter
	 */
	public function crop($width, $height, $offset_x = null, $offset_y = null)
	{
		if (! $offset_x) {
			$offset_x = (($this->_width - $width) / 2);
		} else {
			if ($offset_x < 0) {
				$offset_x = $this->_width - $width + $offset_x;
			}
			if ($offset_x > $this->_width) {
				$offset_x = ( int ) $this->_width;
			}
		}
		if (! $offset_y) {
			$offset_y = (($this->_height - $height) / 2);
		} else {
			if ($offset_y < 0) {
				$offset_y = $this->_height - $height + $offset_y;
			}

			if ($offset_y > $this->_height) {
				$offset_y = ( int ) $this->_height;
			}
		}

		if ($width > ($this->_width - $offset_x)) {
			$width = $this->_width - $offset_x;
		}

		if ($height > ($this->_height - $offset_y)) {
			$height = $this->_height - $offset_y;
		}

		$this->_crop ( $width, $height, $offset_y, $offset_y );

		return $this;
	}

	/**
	 * 旋转指定图片
	 *
	 * @param int degrees
	 * @return Leaps\Image\Adapter
	 */
	public function rotate($degrees)
	{
		if ($degrees > 180) {
			$degrees %= 360;
			if ($degrees > 180) {
				$degrees -= 360;
			}
		} else {
			while ( $degrees < - 180 ) {
				$degrees += 360;
			}
		}
		$this->_rotate ( $degrees );
		return $this;
	}

	/**
	 * 水平或垂直旋转图片
	 *
	 * @param int direction
	 * @return \Leaps\Image\Adapter
	 */
	public function flip($direction)
	{
		if ($direction != Image::HORIZONTAL && $direction != Image::VERTICAL) {
			$direction = Image::HORIZONTAL;
		}
		$this->_flip ( $direction );
		return $this;
	}

	/**
	 * 锐化图片
	 *
	 * @param int amount
	 * @return \Leaps\Image\Adapter
	 */
	public function sharpen($amount)
	{
		if ($amount > 100) {
			$amount = 100;
		} else {
			if ($amount < 1) {
				$amount = 1;
			}
		}

		$this->_sharpen ( $amount );
		return $this;
	}

	/**
	 * 添加反射到如片
	 *
	 * @param int height
	 * @param int opacity
	 * @param boolean fade_in
	 * @return Leaps\Image\Adapter
	 */
	public function reflection($height, $opacity = 100, $fade_in = false)
	{
		if ($height <= 0 || $height > $this->_height) {
			$height = ( int ) $this->_height;
		}
		if ($opacity < 0) {
			$opacity = 0;
		} else {
			if ($opacity > 100) {
				$opacity = 100;
			}
		}
		$this->_reflection ( $height, $opacity, $fade_in );
		return $this;
	}

	/**
	 * 添加水印到图片
	 *
	 * @param Leaps\Image\Adapter watermark
	 * @param int offset_x
	 * @param int offset_y
	 * @param int opacity
	 * @return Leaps\Image\Adapter
	 */
	public function watermark($watermark, $offset_x = 0, $offset_y = 0, $opacity = 100)
	{
		$tmp = $this->_width - $watermark->getWidth ();
		if ($offset_x < 0) {
			$offset_x = 0;
		} else {
			if ($offset_x > $tmp) {
				$offset_x = $tmp;
			}
		}
		$tmp = $this->_height - $watermark->getHeight ();
		if ($offset_y < 0) {
			$offset_y = 0;
		} else {
			if ($offset_y > $tmp) {
				$offset_y = $tmp;
			}
		}
		if ($opacity < 0) {
			$opacity = 0;
		} else {
			if ($opacity > 100) {
				$opacity = 100;
			}
		}
		$this->_watermark ( $watermark, $offset_x, $offset_y, $opacity );
		return $this;
	}

	/**
	 * 添加文字到图片
	 *
	 * @param string text
	 * @param int offset_x
	 * @param int offset_y
	 * @param int opacity
	 * @param string color
	 * @param int size
	 * @param string fontfile
	 * @return Leaps\Image\Adapter
	 */
	public function text($text, $offset_x = 0, $offset_y = 0, $opacity = 100, $color = "000000", $size = 12, $fontfile = null)
	{
		if ($opacity < 0) {
			$opacity = 0;
		} else {
			if ($opacity > 100) {
				$opacity = 100;
			}
		}
		if (strlen ( $color ) > 1 && substr ( $color, 0, 1 ) === "#") {
			$color = substr ( $color, 1 );
		}
		if (strlen ( $color ) == 3) {
			$color = preg_replace ( "/./", "$0$0", $color );
		}
		$colors = array_map ( "hexdec", str_split ( $color, 2 ) );
		$this->_text ( $text, $offset_x, $offset_y, $opacity, $colors [0], $colors [1], $colors [2], $size, $fontfile );
		return $this;
	}

	/**
	 * 合并图像
	 *
	 * @param Leaps\Image\Adapter watermark
	 * @return Leaps\Image\Adapter
	 */
	public function mask($watermark)
	{
		$this->_mask ( $watermark );
		return $this;
	}

	/**
	 * 设置图像的背景颜色
	 *
	 * @param string color
	 * @param int opacity
	 * @return Leaps\Image\Adapter
	 */
	public function background($color, $opacity = 100)
	{
		if (strlen ( $color ) > 1 && substr ( $color, 0, 1 ) === "#") {
			$color = substr ( $color, 1 );
		}
		if (strlen ( $color ) == 3) {
			$color = preg_replace ( "/./", "$0$0", $color );
		}
		$colors = array_map ( "hexdec", str_split ( $color, 2 ) );
		$this->_background ( $colors [0], $colors [1], $colors [2], $opacity );
		return $this;
	}

	/**
	 * 模糊图像
	 *
	 * @param int radius
	 * @return Leaps\Image\Adapter
	 */
	public function blur($radius)
	{
		if ($radius < 1) {
			$radius = 1;
		} else {
			if ($radius > 100) {
				$radius = 100;
			}
		}

		$this->_blur ( $radius );
		return $this;
	}

	/**
	 * 像素化图像
	 *
	 * @param int amount
	 * @return Leaps\Image\Adapter
	 */
	public function pixelate($amount)
	{
		if ($amount < 2) {
			$amount = 2;
		}
		$this->_pixelate ( $amount );
		return $this;
	}

	/**
	 * 保存图像
	 *
	 * @param string file
	 * @param int quality
	 * @return Leaps\Image\Adapter
	 */
	public function save($file = null, $quality = 100)
	{
		if (! $file) {
			$file = ( string ) $this->_realpath;
		}

		if ($quality < 1) {
			$quality = 1;
		} else {
			if ($quality > 100) {
				$quality = 100;
			}
		}

		$this->_save ( $file, $quality );
		return $this;
	}

	/**
	 * 渲染图像并返回二进制字符串
	 *
	 * @param string ext
	 * @param int quality
	 * @return string
	 */
	public function render($ext = null, $quality = 100)
	{
		if (! $ext) {
			$ext = ( string ) pathinfo ( $this->_file, PATHINFO_EXTENSION );
		}

		if (empty ( $ext )) {
			$ext = "png";
		}

		if ($quality < 1) {
			$quality = 1;
		} else {
			if ($quality > 100) {
				$quality = 100;
			}
		}
		return $this->_render ( $ext, $quality );
	}
	public function getImage()
	{
		return $this->_image;
	}
	public function getRealpath()
	{
		return $this->_realpath;
	}
	public function getWidth()
	{
		return $this->_width;
	}
	public function getType()
	{
		return $this->_type;
	}
	public function getHeight()
	{
		return $this->_height;
	}
	public function getMime()
	{
		return $this->_mime;
	}
}