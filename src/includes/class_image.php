<?php
/**
 * SKYUC! 图像处理类
 *
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

if (! isset ( $GLOBALS ['skyuc']->db )) {
	exit ();
}

/**#@+
 * 定义全局图像类型
 */
define ( 'GIF', 1 );
define ( 'JPG', 2 );
define ( 'PNG', 3 );
/**#@-*/

/**#@+
 * 图像验证选项
 */
define ( 'ALLOW_RANDOM_FONT', 1 ); //随机字体
define ( 'ALLOW_RANDOM_SIZE', 2 ); //随机字体大小
define ( 'ALLOW_RANDOM_SLANT', 4 ); //随机倾斜
define ( 'ALLOW_RANDOM_COLOR', 8 ); //随机颜色
define ( 'ALLOW_RANDOM_SHAPE', 16 ); //随机形状
/**#@-*/

if (function_exists ( 'imagegif' )) {
	define ( 'IMAGEGIF', true );
} else {
	define ( 'IMAGEGIF', false );
}

if (function_exists ( 'imagejpeg' )) {
	define ( 'IMAGEJPEG', true );
} else {
	define ( 'IMAGEJPEG', false );
}

if (function_exists ( 'imagepng' )) {
	define ( 'IMAGEPNG', true );
} else {
	define ( 'IMAGEPNG', false );
}

if (($current_memory_limit = ini_size_to_bytes ( @ini_get ( 'memory_limit' ) )) < 128 * 1048576 and $current_memory_limit > 0) {
	@ini_set ( 'memory_limit', 128 * 1048576 );
}

/**
 * 抽象的图像类
 *
 */
class Image {
	/**
	 * 构造函数
	 * 不做任何事 *^_^*
	 *
	 * @return	void
	 */
	function __construct() {
	}

	/**
	 * 选择图像库
	 *
	 * @return	object
	 */
	function &fetch_library(&$registry, $type = 'image') {
		// 使用图像函数的缩略图的库
		if ($type == 'image') {
			$selectclass = 'Image_' . ($registry->options ['imagetype'] ? $registry->options ['imagetype'] : 'GD');
		} // 用于验证图像库
else {
			switch ($registry->options ['imagetype']) {
				case 'Magick' :
					$selectclass = 'Image_Magick';
					break;
				default :
					$selectclass = 'Image_GD';
			}
		}
		$object = new $selectclass ( $registry );
		return $object; // 必须返回一个定义的变量 & 函数定义为返回
	}
}

/**
 * 抽象的图像类
 *
 */
class Image_Abstract {
	/**
	 * 主数据注册表
	 *
	 * @public 	Registry
	 */
	public $registry = null;

	/**
	 * @public	array
	 */
	public $thumb_extensions = array ();

	/**
	 * @public	array
	 */
	public $info_extensions = array ();

	/**
	 * @ppublic	array
	 */
	public $must_convert_types = array ();

	/**
	 * @public	array
	 */
	public $resize_types = array ();

	/**
	 * @public	mixed
	 */
	public $imageinfo = null;

	/**
	 * @protected	array $extension_map
	 */
	protected $extension_map = array ('gif' => 'GIF', 'jpg' => 'JPEG', 'jpeg' => 'JPEG', 'jpe' => 'JPEG', 'png' => 'PNG', 'bmp' => 'BMP', 'tif' => 'TIFF', 'tiff' => 'TIFF', 'psd' => 'PSD', 'pdf' => 'PDF' );

	/**
	 * @protected	array	$regimageoption
	 */
	protected $regimageoption = array ('randomfont' => false, 'randomsize' => false, 'randomslant' => false, 'randomcolor' => false, 'randomshape' => false );

	/**
	 * 构造函数
	 * 不允许直接构造这个抽象类
	 * 设置注册表
	 *
	 * @return	void
	 */
	function __construct(&$registry) {
		if (! is_subclass_of ( $this, 'Image_Abstract' )) {
			trigger_error ( 'Direct Instantiation of Image_Abstract prohibited.', E_USER_ERROR );
			return NULL;
		}

		$this->registry = &$registry;
		$this->regimageoption ['randomfont'] = $this->registry->options ['regimageoption'] & ALLOW_RANDOM_FONT;
		$this->regimageoption ['randomsize'] = $this->registry->options ['regimageoption'] & ALLOW_RANDOM_SIZE;
		$this->regimageoption ['randomslant'] = $this->registry->options ['regimageoption'] & ALLOW_RANDOM_SLANT;
		$this->regimageoption ['randomcolor'] = $this->registry->options ['regimageoption'] & ALLOW_RANDOM_COLOR;
		$this->regimageoption ['randomshape'] = $this->registry->options ['regimageoption'] & ALLOW_RANDOM_SHAPE;
	}

	/**
	 * protected
	 * 从背景目录提取图像文件
	 *
	 * @return array
	 *
	 */
	protected function &fetch_regimage_backgrounds() {
		// 获取背景
		$backgrounds = array ();
		if ($handle = @opendir ( DIR . '/includes/data/backgrounds/' )) {
			while ( $filename = @readdir ( $handle ) ) {
				if (preg_match ( '#\.(gif|jpg|jpeg|jpe|png)$#i', $filename )) {
					$backgrounds [] = DIR . "/includes/data/backgrounds/$filename";
				}
			}
			@closedir ( $handle );
		}
		return $backgrounds;
	}

	/**
	 * protected
	 * 获取 True Type 字体从字体目录
	 *
	 * @return array
	 *
	 */
	protected function &fetch_regimage_fonts() {
		// 获取字体
		$fonts = array ();
		if ($handle = @opendir ( DIR . '/includes/data/fonts/' )) {
			while ( $filename = @ readdir ( $handle ) ) {
				if (preg_match ( '#\.ttf$#i', $filename )) {
					$fonts [] = DIR . "/includes/data/fonts/$filename";
				}
			}
			@closedir ( $handle );
		}
		return $fonts;
	}

	/**
	 *Public
	 *
	 *
	 * @param	string	$type		 $info_extensions 图像的类型
	 *
	 * @return	bool
	 */
	public function fetch_must_convert($type) {
		return ! empty ( $this->must_convert_types ["$type"] );
	}

	/**
	 * Public
	 * 如果提供扩展的检查可以由 fetch_image_info 使用
	 *
	 * @param	string	$extension 	文件的扩展名
	 *
	 * @return	bool
	 */
	public function is_valid_info_extension($extension) {
		return ! empty ( $this->info_extensions [strtolower ( $extension )] );
	}

	/**
	 * Public
	 * 如果提供扩展的检查可以调整到一个较小的固定图像，不用于 PSD、 PDF 等，便会失去原始格式
	 *
	 * @param	string	$type 	 $info_extensions 图像的类型
	 *
	 * @return	bool
	 */
	public function is_valid_resize_type($type) {
		return ! empty ( $this->resize_types ["$type"] );
	}

	/**
	 * Public
	 * 如果提供扩展的检查可以由 fetch_thumbnail 使用
	 *
	 * @param	string	$extension 	文件扩展名
	 *
	 * @return	bool
	 */
	public function is_valid_thumbnail_extension($extension) {
		return ! empty ( $this->thumb_extensions [strtolower ( $extension )] );
	}

	/**
	 * Public
	 * 如果提供扩展的检查可以由 fetch_thumbnail 使用
	 *
	 * @param	string	$extension 	文件扩展名
	 *
	 * @return	bool
	 */
	public function fetch_imagetype_from_extension($extension) {
		return $this->extension_map [strtolower ( $extension )];
	}

	/**
	 * protected
	 * 检查有可以被利用通过 IE 的 HTML 标记
	 *
	 * @param string	文件名
	 *
	 * @return bool
	 */
	protected function verify_image_file($filename) {
		// Verify that file is playing nice
		$fp = fopen ( $filename, 'rb' );
		if ($fp) {
			$header = fread ( $fp, 256 );
			fclose ( $fp );
			if (preg_match ( '#<html|<head|<body|<script|<pre|<plaintext|<table|<a href|<img|<title#si', $header )) {
				return false;
			}
		} else {
			return false;
		}

		return true;
	}

}

/**
 * ImageMagick 的图像类
 *
 *
 */
class Image_Magick extends Image_Abstract {

	/**
	 * @protected	string
	 */
	protected $convertpath = '/usr/local/bin/convert';

	/**
	 * @protected	string
	 */
	protected $identifypath = '/usr/local/bin/identify';

	/**
	 * @protected	integer
	 */
	protected $returnvalue = 0;

	/**
	 * @protected  string
	 */
	protected $identifyformat = '';

	/**
	 * @public	string
	 */
	public $convertoptions = array ('width' => '100', 'height' => '100', 'quality' => '75' );

	/**
	 * @public  string
	 *
	 */
	public $error = '';

	/**
	 * @public string
	 *
	 */
	public $thumbcolor = 'black';

	/**
	 * 构造函数
	 * 设置 ImageMagick 转换和标识路径
	 *
	 * @return	void
	 */
	function __construct(&$registry) {
		parent::__construct ( $registry );

		$path = preg_replace ( '#[/\\\]+$#', '', $this->registry->options ['magickpath'] );

		$is_win = DIRECTORY_SEPARATOR == '\\';
		if ($is_win) {
			$this->identifypath = '"' . $path . '\identify.exe"';
			$this->convertpath = '"' . $path . '\convert.exe"';
		} else {
			$this->identifypath = "'" . $path . "/identify'";
			$this->convertpath = "'" . $path . "/convert'";
		}

		$this->must_convert_types = array ('PSD' => true, 'BMP' => true, 'TIFF' => true, 'PDF' => true );

		$this->resize_types = array ('GIF' => true, 'JPEG' => true, 'PNG' => true, 'BMP' => true, 'TIFF' => true );

		$this->thumb_extensions = array ('gif' => true, 'jpg' => true, 'jpe' => true, 'jpeg' => true, 'png' => true, 'psd' => true, 'pdf' => true, 'bmp' => true, 'tiff' => true, 'tif' => true );
		$this->info_extensions = & $this->thumb_extensions;

		if (preg_match ( '~^#([0-9A-F]{6})$~i', $this->registry->options ['thumbcolor'], $match )) {
			$this->thumbcolor = $match [0];
		}

		$this->version = $this->fetch_version ();
	}

	/**
	 * Private
	 * imagemagick 二进制文件的一般调用
	 *
	 * @param	string	要执行的命令 ,ImageMagick 二进制文件
	 * @param	string	args	ImageMagick 二进制文件的参数
	 *
	 * @return	mixed
	 */
	private function fetch_im_exec($command, $args, $needoutput = false, $dieongs = true) {
		if (! function_exists ( 'exec' )) {
			$this->error = array (fetch_error ( 'php_error_exec_disabled' ) );
			return false;
		}

		$imcommands = array ('identify' => $this->identifypath, 'convert' => $this->convertpath );

		$input = $imcommands ["$command"] . ' ' . $args . ' 2>&1';
		$is_win = DIRECTORY_SEPARATOR == '\\';
		if ($is_win and PHP_VERSION < '5.3.0') {
			$input = '"' . $input . '"';
		}
		$exec = @exec ( $input, $output, $this->returnvalue );

		if ($this->returnvalue or $exec === null) { // 遇到错误
			if (! empty ( $output )) { // 由 @exec 发出的命令失败
				if (strpos ( strtolower ( implode ( ' ', $output ) ), 'postscript delegate failed' ) !== false) {
					$output [] = fetch_error ( 'install_ghostscript_to_resize_pdf' );
				}
				$this->error = $output;
			} else if (! empty ( $php_errormsg )) { // @exec 失败，显示错误并删除路径显示
				$this->error = array (fetch_error ( 'php_error_x', str_replace ( $this->registry->options ['magickpath'] . '\\', '', $php_errormsg ) ) );
			} else if ($this->returnvalue == - 1) { // @exec 失败，但是没有告诉我们为什么 $php_errormsg
				$this->error = array (fetch_error ( 'php_error_unspecified_exec' ) );
			}
			return false;
		} else {
			$this->error = '';
			if (! empty ( $output )) { // $output 为返回文本组成的数组
				// 不能读取字体，失败的即时消息
				if (strpos ( strtolower ( implode ( ' ', $output ) ), 'unable to read font' ) !== false) {
					$this->error = $output;
					return false;
				}

				if (strpos ( strtolower ( implode ( ' ', $output ) ), 'postscript delegate failed' ) !== false) { // 当ghostscript没有安装时，exec(convert.exe)对.pdf 不返回 false
					$this->error = array (fetch_error ( 'install_ghostscript_to_resize_pdf' ) );
				}
				return $output;
			} else if (empty ( $output ) and $needoutput) { // $output 为空，我们希望返回一些东西
				return false;
			} else { // $output 为空，我们不希望返回任何东西
				return true;
			}
		}
	}

	/**
	 * Private
	 * 获取 Imagemagick 版本
	 *
	 * @return	mixed
	 */
	private function fetch_version() {
		if ($result = $this->fetch_im_exec ( 'convert', '-version', true ) and preg_match ( '#ImageMagick (\d+\.\d+\.\d+)#', $result [0], $matches )) {
			return $matches [1];
		}

		return false;
	}

	/**
	 * Private
	 * 图像标识
	 *
	 * @param	string	$filename 文件获取图像资料
	 *
	 * @return	mixed
	 */
	private function fetch_identify_info($filename) {
		$fp = @fopen ( $filename, 'rb' );
		if (($header = @fread ( $fp, 4 )) == '%PDF') { //这是一个 PDF，因此只有查看边框 0，以节省 许多处理时间
			$frame0 = '[0]';
		}
		@fclose ( $fp );

		$execute = (! empty ( $this->identifyformat ) ? "-format {$this->identifyformat} \"$filename\"" : "\"$filename\"") . $frame0;

		if ($result = $this->fetch_im_exec ( 'identify', $execute, true )) {
			if (empty ( $result ) or ! is_array ( $result )) {
				return false;
			}

			do {
				$last = array_pop ( $result );
			} while ( ! empty ( $result ) and $last == '' );

			$temp = explode ( '###', $last );

			if (count ( $temp ) < 6) {
				return false;
			}

			preg_match ( '#^(\d+)x(\d+)#', $temp [0], $matches );

			$imageinfo = array (2 => $temp [3], 'bits' => $temp [6], 'scenes' => $temp [4], 'animated' => ($temp [4] > 1), 'library' => 'IM' );

			if (version_compare ( $this->version, '6.2.6', '>=' )) {
				$imageinfo [0] = $matches [1];
				$imageinfo [1] = $matches [2];
			} else //ImageMagick v6.2.5 和更低版本，不支持最新优化
{
				$imageinfo [0] = $temp [1];
				$imageinfo [1] = $temp [2];
			}

			switch ($temp [5]) {
				case 'PseudoClassGray' :
				case 'PseudoClassGrayMatte' :
				case 'PseudoClassRGB' :
				case 'PseudoClassRGBMatte' :
					$imageinfo ['channels'] = 1;
					break;
				case 'DirectClassRGB' :
					$imageinfo ['channels'] = 3;
					break;
				case 'DirectClassCMYK' :
					$imageinfo ['channels'] = 4;
					break;
				default :
					$imageinfo ['channels'] = 1;
			}

			return $imageinfo;
		} else {
			return false;
		}
	}

	/**
	 * Private
	 * 设置转换的图像大小
	 *
	 * @param	width		新图像宽度
	 * @param	height	新图像高度
	 * @param	quality JPEG图像质量
	 * @param bool			包括图片尺寸和文件大小的缩略图
	 * @param bool			绘制缩略图周围的边框
	 *
	 * @return	void
	 */
	private function set_convert_options($width = 100, $height = 100, $quality = 75, $labelimage = false, $drawborder = false, $jpegconvert = false, $owidth = null, $oheight = null, $ofilesize = null) {
		$this->convertoptions ['width'] = $width;
		$this->convertoptions ['height'] = $height;
		$this->convertoptions ['quality'] = $quality;
		$this->convertoptions ['labelimage'] = $labelimage;
		$this->convertoptions ['drawborder'] = $drawborder;
		$this->convertoptions ['owidth'] = $owidth;
		$this->convertoptions ['oheight'] = $oheight;
		$this->convertoptions ['ofilesize'] = $ofilesize;
		$this->convertoptions ['jpegconvert'] = $jpegconvert;
	}

	/**
	 * Private
	 * 转换一个图像
	 *
	 * @param	string	filename	要转换的图像文件
	 * @param	string	output		转换后的图像，写入的文件
	 * @param	string	extension	文件类型
	 * @param	boolean	thumbnail	生成在浏览器中显示的缩略图
	 * @param	boolean	sharpen		锐化输出
	 *
	 * @return	mixed
	 */
	private function fetch_converted_image($filename, $output, $imageinfo, $thumbnail = true, $sharpen = true) {
		$execute = '';

		if ($thumbnail) {
			// 如果这是一个PDF或PSD -- 允许GIF动画调整大小
			$execute .= (in_array ( $imageinfo [2], array ('PDF', 'PSD' ) )) ? " \"{$filename}\"[0] " : " \"$filename\"";
		} else {
			$execute .= " \"$filename\"";
		}

		if ($imageinfo ['scenes'] > 1 and version_compare ( $this->version, '6.2.6', '>=' )) {
			$execute .= ' -coalesce ';
		}

		if ($this->convertoptions ['width'] > 0 or $this->convertoptions ['height'] > 0) {
			if ($this->convertoptions ['width']) {
				$size = $this->convertoptions ['width'];
				if ($this->convertoptions ['height']) {
					$size .= 'x' . $this->convertoptions ['height'];
				}
			} else if ($this->convertoptions ['height']) {
				$size .= 'x' . $this->convertoptions ['height'];
			}
			$execute .= " -size $size ";
		}

		if ($thumbnail) {
			if ($size) { // 在这里使用 -thumbnail 参数 .. -看起来很糟糕的GIF动画
				$execute .= " -thumbnail \"$size>\" ";
			}
		}
		$execute .= ($sharpen and $imageinfo [2] == 'JPEG') ? " -sharpen 0x1 " : '';

		if ($imageinfo ['scenes'] > 1 and version_compare ( $this->version, '6.2.6', '>=' )) {
			$execute .= ' -layers optimize ';
		}

		// ### 转换一个 CMYK jpg 到 RGB ， IE/Firefox 不能显示 CMYK .. 转换是丑陋的，因为我们没有指定配置文件
		if ($this->imageinfo ['channels'] == 4 and $thumbnail) {
			$execute .= ' -colorspace RGB ';
		}

		if ($thumbnail) {
			$xratio = ($this->convertoptions ['width'] == 0 or $imageinfo [0] <= $this->convertoptions ['width']) ? 1 : $imageinfo [0] / $this->convertoptions ['width'];
			$yratio = ($this->convertoptions ['height'] == 0 or $imageinfo [1] <= $this->convertoptions ['height']) ? 1 : $imageinfo [1] / $this->convertoptions ['height'];

			if ($xratio > $yratio) {
				$new_width = round ( $imageinfo [0] / $xratio ) - 1;
				$new_height = round ( $imageinfo [1] / $xratio ) - 1;
			} else {
				$new_width = round ( $imageinfo [0] / $yratio ) - 1;
				$new_height = round ( $imageinfo [1] / $yratio ) - 1;
			}

			#			if ($imageinfo[0] <= $this->convertoptions['width'] AND $imageinfo[1] <= $this->convertoptions['height'])
			#			{
			#				$this->convertoptions['labelimage'] = false;
			#				$this->convertoptions['drawborder'] = false;
			#			}


			if ($this->convertoptions ['labelimage']) {
				if ($this->convertoptions ['owidth']) {
					$dimensions = "{$this->convertoptions['owidth']}x{$this->convertoptions['oheight']}";
				} else {
					$dimensions = "$imageinfo[0]x$imageinfo[1]";
				}
				if ($this->convertoptions ['ofilesize']) {
					$filesize = $this->convertoptions ['ofilesize'];
				} else {
					$filesize = @filesize ( $filename );
				}
				if ($filesize / 1024 < 1) {
					$filesize = 1024;
				}
				$sizestring = (! empty ( $filesize )) ? number_format ( $filesize / 1024, 0, '', '' ) . 'kb' : '';

				if (! $this->convertoptions ['jpegconvert'] or $imageinfo [2] == 'PSD' or $imageinfo [2] == 'PDF') {
					$type = $imageinfo [2];
				} else {
					$type = 'JPEG';
				}

				if (($new_width / strlen ( "$dimensions $sizestring $type" )) >= 6) {
					$finalstring = "$dimensions $sizestring $type";
				} else if (($new_width / strlen ( "$dimensions $sizestring" )) >= 6) {
					$finalstring = "$dimensions $sizestring";
				} else if (($new_width / strlen ( $dimensions )) >= 6) {
					$finalstring = $dimensions;
				} else if (($new_width / strlen ( $sizestring )) >= 6) {
					$finalstring = $sizestring;
				}

				if ($finalstring) { // confusing -flip statements added to workaround an issue with very wide yet short images. See http://www.imagemagick.org/discourse-server/viewtopic.php?t=10367
					$execute .= " -flip -background \"{$this->thumbcolor}\" -splice 0x15 -flip -gravity South -fill white  -pointsize 11 -annotate 0 \"$finalstring\" ";
				}
			}

			if ($this->convertoptions ['drawborder']) {
				$execute .= " -bordercolor \"{$this->thumbcolor}\" -compose Copy -border 1 ";
			}

			if (($imageinfo [2] == 'PNG' or $imageinfo [2] == 'PSD') and ! $this->convertoptions ['jpegconvert']) {
				$execute .= " -depth 8 -quality {$this->convertoptions['quality']} PNG:";
			} else if ($this->fetch_must_convert ( $imageinfo [2] ) or $imageinfo [2] == 'JPEG' or $this->convertoptions ['jpegconvert']) {
				$execute .= " -quality {$this->convertoptions['quality']} JPEG:";
			} else if ($imageinfo [2] == 'GIF') {
				$execute .= " -depth $imageinfo[bits] ";
			}
		}

		$execute .= "\"$output\"";

		if ($zak = $this->fetch_im_exec ( 'convert', $execute )) {
			return $zak;
		} else if ($sharpen and ! empty ( $this->error [0] ) and strpos ( $this->error [0], 'image smaller than radius' ) !== false) { // 尝试再次调整, 但是没有锐化
			$this->error = '';
			return $this->fetch_converted_image ( $filename, $output, $imageinfo, $thumbnail, false );
		} else {
			return false;
		}
	}

	/**
	 * Public
	 * 检索有关图像的信息
	 *
	 * @param	string	filename	文件的位置
	 * @param	string	extension	文件扩展名
	 *
	 * @return	array	[0]			int		width
	 * [1]			int		height
	 * [2]			string	type ('GIF', 'JPEG', 'PNG', 'PSD', 'BMP', 'TIFF',) (and so on)
	 * [scenes]	int		scenes
	 * [channels]	int		Number of channels (GREYSCALE = 1, RGB = 3, CMYK = 4)
	 * [bits]		int		Number of bits per pixel
	 * [library]	string	Library Identifier
	 */
	public function fetch_image_info($filename) {
		if (! $this->verify_image_file ( $filename )) {
			return false;
		}

		$this->identifyformat = '%g###%w###%h###%m###%n###%r###%z###';
		$this->imageinfo = $this->fetch_identify_info ( $filename );
		return $this->imageinfo;
	}

	/**
	 * Public
	 * 返回包含一个缩略图、 创建时间、 缩略图大小和任何错误的数组
	 *
	 * @param	string	filename	源文件的文件名
	 * @param	string	location	源文件的位置
	 * @param	int		newsize			新图像大小 (图像的最长边)
	 * @param	int		quality			Jpeg 质量
	 * @param bool		labelimage	缩略图，包含图像尺寸和大小
	 * @param bool		drawborder	绘制缩略图周围的边框
	 *
	 * @return	array
	 */
	public function fetch_thumbnail($filename, $location, $maxwidth = 100, $maxheight = 100, $quality = 75, $labelimage = false, $drawborder = false, $jpegconvert = false, $sharpen = true, $owidth = null, $oheight = null, $ofilesize = null) {
		$thumbnail = array ('filedata' => '', 'filesize' => 0, 'dateline' => 0, 'imageerror' => '' );

		if ($this->is_valid_thumbnail_extension ( file_extension ( $filename ) )) {
			if ($imageinfo = $this->fetch_image_info ( $location )) {
				$thumbnail ['source_width'] = $imageinfo [0];
				$thumbnail ['source_height'] = $imageinfo [1];

				if ($this->fetch_imagetype_from_extension ( file_extension ( $filename ) ) != $imageinfo [2]) {
					$thumbnail ['imageerror'] = 'thumbnail_notcorrectimage';
				} else if ($imageinfo [0] > $maxwidth or $imageinfo [1] > $maxheight or $this->fetch_must_convert ( $imageinfo [2] )) {
					if ($this->registry->options ['safeupload']) {
						$tmpname = $this->registry->options ['tmppath'] . '/' . md5 ( uniqid ( microtime () ) . $this->registry->userinfo ['userid'] );
					} else {
						if (! ($tmpname = @tempnam ( ini_get ( 'upload_tmp_dir' ), 'skyuc_thumb' ))) {
							$thumbnail ['imageerror'] = 'thumbnail_nogetimagesize';
							return $thumbnail;
						}
					}

					$this->set_convert_options ( $maxwidth, $maxheight, $quality, $labelimage, $drawborder, $jpegconvert, $owidth, $oheight, $ofilesize );
					if ($result = $this->fetch_converted_image ( $location, $tmpname, $imageinfo, true, $sharpen )) {
						if ($imageinfo = $this->fetch_image_info ( $tmpname )) {
							$thumbnail ['width'] = $imageinfo [0];
							$thumbnail ['height'] = $imageinfo [1];
						}
						$extension = strtolower ( file_extension ( $filename ) );
						if ($jpegconvert) {
							$thumbnail ['filename'] = preg_replace ( '#' . preg_quote ( file_extension ( $filename ), '#' ) . '$#', 'jpg', $filename );
						}
						$thumbnail ['filesize'] = filesize ( $tmpname );
						$thumbnail ['dateline'] = TIMENOW;
						$thumbnail ['filedata'] = file_get_contents ( $tmpname );
					} else {
						$thumbnail ['imageerror'] = 'thumbnail_nogetimagesize';
					}
					@unlink ( $tmpname );
				} else {
					if ($imageinfo [0] > 0 and $imageinfo [1] > 0) {
						$thumbnail ['filedata'] = @file_get_contents ( $location );
						$thumbnail ['width'] = $imageinfo [0];
						$thumbnail ['height'] = $imageinfo [1];
						$thumbnail ['imageerror'] = 'thumbnailalready';
					} else {
						$thumbnail ['filedata'] = '';
						$thumbnail ['imageerror'] = 'thumbnail_nogetimagesize';
					}
				}
			} else {
				$thumbnail ['filedata'] = '';
				$thumbnail ['imageerror'] = 'thumbnail_nogetimagesize';
			}
		}

		if (! empty ( $thumbnail ['filedata'] )) {
			$thumbnail ['filesize'] = strlen ( $thumbnail ['filedata'] );
			$thumbnail ['dateline'] = TIMENOW;
		}
		return $thumbnail;
	}

	/**
	 * Public
	 * 输出图像基于字符串
	 *
	 * @param	string	string	输出的字符串
	 * @param bool		moveabout	文本有背景
	 *
	 * @return	void
	 */
	public function print_image_from_string($string, $moveabout = true) {
		if ($this->registry->options ['safeupload']) {
			$tmpname = $this->registry->options ['tmppath'] . '/' . md5 ( uniqid ( microtime () ) . $this->registry->userinfo ['userid'] );
		} else {
			if (! ($tmpname = @tempnam ( ini_get ( 'upload_tmp_dir' ), 'skyuc' ))) {
				echo 'Could not create temporary file.';
				return false;
			}
		}

		// 没有背景图像的命令开始
		$execute = ' -size 201x61 xc:white ';

		$fonts = & $this->fetch_regimage_fonts ();
		if ($moveabout) {
			$backgrounds = & $this->fetch_regimage_backgrounds ();

			if (! empty ( $backgrounds )) {
				$index = mt_rand ( 0, count ( $backgrounds ) - 1 );
				$background = $backgrounds ["$index"];

				// 替换背景图像命令开始
				$execute = " \"$background\" -resize 201x61! -swirl " . mt_rand ( 10, 100 );

				// 随机旋转背景图像 180 度
				$execute .= (TIMENOW & 2) ? ' -rotate 180 ' : '';
			}

			// 随机上下移动的字母
			for($x = 0; $x < strlen ( $string ); $x ++) {
				if (! empty ( $fonts )) {
					$index = mt_rand ( 0, count ( $fonts ) - 1 );
					if ($this->regimageoption ['randomfont']) {
						$font = $fonts ["$index"];
					} else {
						if (! $font) {
							$font = $fonts ["$index"];
						}
					}
				} else {
					$font = 'Helvetica';
				}

				if ($this->regimageoption ['randomshape']) {
					// 笔划宽度, 1 或 2
					$strokewidth = mt_rand ( 1, 2 );
					// 选择一个随机的颜色
					$r = mt_rand ( 50, 200 );
					$b = mt_rand ( 50, 200 );
					$g = mt_rand ( 50, 200 );
					// 选择一个形状


					$x1 = mt_rand ( 0, 200 );
					$y1 = mt_rand ( 0, 60 );
					$x2 = mt_rand ( 0, 200 );
					$y2 = mt_rand ( 0, 60 );
					$start = mt_rand ( 0, 360 );
					$end = mt_rand ( 0, 360 );
					switch (mt_rand ( 1, 5 )) {
						case 1 :
							$shape = "\"roundrectangle $x1,$y1 $x2,$y2 $start,end\"";
							break;
						case 2 :
							$shape = "\"arc $x1,$y1 $x2,$y2 20,15\"";
							break;
						case 3 :
							$shape = "\"ellipse $x1,$y1 $x2,$y2 $start,$end\"";
							break;
						case 4 :
							$shape = "\"line $x1,$y1 $x2,$y2\"";
							break;
						case 5 :
							$x3 = mt_rand ( 0, 200 );
							$y3 = mt_rand ( 0, 60 );
							$x4 = mt_rand ( 0, 200 );
							$y4 = mt_rand ( 0, 60 );
							$shape = "\"polygon $x1,$y1 $x2,$y2 $x3,$y3 $x4,$y4\"";
							break;
					}
					// 之前或之后
					$place = mt_rand ( 1, 2 );

					$finalshape = " -flatten -stroke \"rgb($r,$b,$g)\" -strokewidth $strokewidth -fill none -draw $shape -stroke none ";

					if ($place == 1) {
						$execute .= $finalshape;
					}
				}

				$slant = (($x <= 1 or $x == 5) and $this->regimageoption ['randomslant']) ? true : false;
				$execute .= $this->annotate ( $string ["$x"], $font, $slant, true );

				if ($this->regimageoption ['randomshape'] and $place == 2) {
					$execute .= $finalshape;
				}
			}
		} else {
			if (! empty ( $fonts )) {
				$font = $fonts [0];
			} else {
				$font = 'Helvetica';
			}
			$execute .= $this->annotate ( "\"$string\"", $font, false, false );
		}

		// 漩涡文字, 划短横于内边框 1 像素 和输出 GIF
		$execute .= ' -flatten ';

		$execute .= ($moveabout and $this->regimageoption ['randomslant']) ? ' -swirl 20 ' : '';
		$execute .= " -stroke black -strokewidth 1 -fill none -draw \"rectangle 0,60 200,0\" -depth 8 PNG:\"$tmpname\"";

		if ($result = $this->fetch_im_exec ( 'convert', $execute )) {
			header ( 'Content-disposition: inline; filename=image.png' );
			header ( 'Content-transfer-encoding: binary' );
			header ( 'Content-Type: image/png' );
			if ($filesize = @filesize ( $tmpname )) { // 这是因为一个愚蠢的 Win32 CGI 东西. filesize 失败但是  readfile 工作
				header ( "Content-Length: $filesize" );
			}
			readfile ( $tmpname );
			@unlink ( $tmpname );
		} else {
			echo htmlspecialchars_uni ( $this->fetch_error () );
			@unlink ( $tmpname );
			return false;
		}
	}

	/**
	 * Private
	 * 返回一个字母位置命令
	 *
	 * @param	string	letter	字符位置
	 *
	 * @return	string
	 */
	private function annotate($letter, $font, $slant = false, $random = true) {
		// 起始位置
		static $r, $g, $b, $position = 10;

		// 字符倾斜
		static $slants = array ('0x0', # 正常
'0x30', # 向右斜
'20x20', # 向下斜
'315x315', # 向上斜
'45x45', '0x330' );

		// 不能同时使用倾斜和漩涡文字
		if ($slant) {
			$coord = mt_rand ( 1, count ( $slants ) - 1 );
			$coord = $slants ["$coord"];
		} else {
			$coord = $slants [0];
		}

		if ($random) {
			// Y 轴线位置, 随机从 32 至 48
			$y = mt_rand ( 32, 48 );

			if ($this->regimageoption ['randomcolor'] or empty ( $r )) {
				// 生成一个随机颜色..
				$r = mt_rand ( 50, 200 );
				$b = mt_rand ( 50, 200 );
				$g = mt_rand ( 50, 200 );
			}

			$pointsize = $this->regimageoption ['randomsize'] ? mt_rand ( 28, 36 ) : 32;
		} else {
			$y = 40;
			$pointsize = 32;
			$r = $b = $g = 0;
		}

		$output = " -font \"$font\" -pointsize $pointsize -fill \"rgb($r,$b,$g)\" -annotate $coord+$position+$y $letter ";
		$position += rand ( 25, 35 );

		return $output;

	}
	/**
	 * public
	 * 从 fetch_im_exec() 返回错误
	 *
	 * @return	string
	 */
	public function fetch_error() {
		if (! empty ( $this->error )) {
			return implode ( "\n", $this->error );
		} else {
			return false;
		}
	}
}

/**
 * GD 图像库的图像类
 *
 *
 */
class Image_GD extends Image_Abstract {
	/**
	 * @protected string
	 *
	 */
	protected $thumbcolor = array ('r' => 0, 'b' => 0, 'g' => 0 );

	/**
	 * 构造函数。 设置可调整大小的类型、 扩展名、 等。
	 *
	 * @return	void
	 */
	function __construct(&$registry) {
		parent::__construct ( $registry );

		$this->info_extensions = array ('gif' => true, 'jpg' => true, 'jpe' => true, 'jpeg' => true, 'png' => true, 'psd' => true, 'bmp' => true, 'tiff' => true, 'tif' => true );

		$this->thumb_extensions = array ('gif' => true, 'jpg' => true, 'jpe' => true, 'jpeg' => true, 'png' => true );

		$this->resize_types = array ('JPEG' => true, 'PNG' => true, 'GIF' => true );

		if (preg_match ( '~#?([0-9A-F]{2})([0-9A-F]{2})([0-9A-F]{2})~i', $this->registry->options ['thumbcolor'], $match )) {
			$this->thumbcolor = array ('r' => hexdec ( $match [1] ), 'g' => hexdec ( $match [2] ), 'b' => hexdec ( $match [3] ) );
		}
	}

	/**
	 * Private
	 * 输出一个图像
	 *
	 * @param	object	filename		要转换的图像文件
	 * @param	int		output		Image file to write converted image to
	 * @param	bool		headers		生成图像头
	 * @param	int		quality		Jpeg 品质
	 *
	 * @return	void
	 */
	// ###################### Start print_image #######################
	private function print_image(&$image, $type = 'JPEG', $headers = true, $quality = 75) {
		// 确定输出什么图像类型
		switch ($type) {
			case 'GIF' :
				if (! IMAGEGIF) {
					if (IMAGEJPEG) {
						$type = 'JPEG';
					} else if (IMAGEPNG) {
						$type = 'PNG';
					} else // nothing!
{
						imagedestroy ( $image );
						return false;
					}
				}
				break;

			case 'PNG' :
				if (! IMAGEPNG) {
					if (IMAGEJPEG) {
						$type = 'JPEG';
					} else if (IMAGEGIF) {
						$type = 'GIF';
					} else // nothing!
{
						imagedestroy ( $image );
						return false;
					}
				}
				break;

			default : // JPEG
				if (! IMAGEJPEG) {
					if (IMAGEGIF) {
						$type = 'GIF';
					} else if (IMAGEPNG) {
						$type = 'PNG';
					} else // nothing!
{
						imagedestroy ( $image );
						return false;
					}
				} else {
					$type = 'JPEG';
				}
				break;
		}

		/* If you are calling print_image inside ob_start in order to capture the image
			remember any headers still get sent to the browser. Mozilla is not happy with this */

		switch ($type) {
			case 'GIF' :
				if ($headers) {
					header ( 'Content-transfer-encoding: binary' );
					header ( 'Content-disposition: inline; filename=image.gif' );
					header ( 'Content-type: image/gif' );
				}
				imagegif ( $image );
				imagedestroy ( $image );
				return 'gif';

			case 'PNG' :
				if ($headers) {
					header ( 'Content-transfer-encoding: binary' );
					header ( 'Content-disposition: inline; filename=image.png' );
					header ( 'Content-type: image/png' );
				}
				imagepng ( $image );
				imagedestroy ( $image );
				return 'png';

			case 'JPEG' :
				if ($headers) {
					header ( 'Content-transfer-encoding: binary' );
					header ( 'Content-disposition: inline; filename=image.jpg' );
					header ( 'Content-type: image/jpeg' );
				}
				imagejpeg ( $image, '', $quality );
				imagedestroy ( $image );
				return 'jpg';

			default :
				imagedestroy ( $image );
				return false;
		}
	}

	/**
	 * Private
	 * 锐化图像
	 *
	 * @param	object		finalimage
	 * @param	int			float
	 * @param	radius		float
	 * @param	threshold	float
	 *
	 * @return	void
	 */
	private function unsharpmask(&$finalimage, $amount = 50, $radius = 1, $threshold = 0) {
		// $finalimg 是一个已在 PHP 使用 imgcreatetruecolor 内创建图像。没有URL！ $img 必须是一个真彩色图像。
		// 尝试校准 Photoshop 的参数：
		if ($amount > 500) {
			$amount = 500;
		}
		$amount = $amount * 0.016;
		if ($radius > 50) {
			$radius = 50;
		}
		$radius = $radius * 2;
		if ($threshold > 255) {
			$threshold = 255;
		}

		$radius = abs ( round ( $radius ) ); // 仅整数有意义。
		if ($radius == 0) {
			return true;
		}

		$w = imagesx ( $finalimage );
		$h = imagesy ( $finalimage );
		$imgCanvas = imagecreatetruecolor ( $w, $h );
		$imgBlur = imagecreatetruecolor ( $w, $h );

		// 高斯模糊矩阵：
		//
		//	1	2	1
		//	2	4	2
		//	1	2	1
		//
		//////////////////////////////////////////////////


		if (function_exists ( 'imageconvolution' )) {
			$matrix = array (array (1, 2, 1 ), array (2, 4, 2 ), array (1, 2, 1 ) );
			imagecopy ( $imgBlur, $finalimage, 0, 0, 0, 0, $w, $h );
			imageconvolution ( $imgBlur, $matrix, 16, 0 );
		} else {
			// 周围移动一个像素图像的副本并将它们合并重量根据矩阵。高半径是只被重复相同的矩阵。
			for($i = 0; $i < $radius; $i ++) {
				imagecopy ( $imgBlur, $finalimage, 0, 0, 1, 0, $w - 1, $h ); // left
				imagecopymerge ( $imgBlur, $finalimage, 1, 0, 0, 0, $w, $h, 50 ); // right
				imagecopymerge ( $imgBlur, $finalimage, 0, 0, 0, 0, $w, $h, 50 ); // center
				imagecopy ( $imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h );

				imagecopymerge ( $imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 33.33333 ); // up
				imagecopymerge ( $imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 25 ); // down
			}
		}

		if ($threshold > 0) {
			// 计算模糊像素 和原始之间的差异，并设置该像素
			for($x = 0; $x < $w - 1; $x ++) // each row
{
				for($y = 0; $y < $h; $y ++) // each pixel
{
					$rgbOrig = ImageColorAt ( $finalimage, $x, $y );
					$rOrig = (($rgbOrig >> 16) & 0xFF);
					$gOrig = (($rgbOrig >> 8) & 0xFF);
					$bOrig = ($rgbOrig & 0xFF);

					$rgbBlur = ImageColorAt ( $imgBlur, $x, $y );

					$rBlur = (($rgbBlur >> 16) & 0xFF);
					$gBlur = (($rgbBlur >> 8) & 0xFF);
					$bBlur = ($rgbBlur & 0xFF);

					// 当隐蔽像素不同少于从原始阈值规定，他们将被设置为其原始值。
					$rNew = (abs ( $rOrig - $rBlur ) >= $threshold) ? max ( 0, min ( 255, ($amount * ($rOrig - $rBlur)) + $rOrig ) ) : $rOrig;

					$gNew = (abs ( $gOrig - $gBlur ) >= $threshold) ? max ( 0, min ( 255, ($amount * ($gOrig - $gBlur)) + $gOrig ) ) : $gOrig;

					$bNew = (abs ( $bOrig - $bBlur ) >= $threshold) ? max ( 0, min ( 255, ($amount * ($bOrig - $bBlur)) + $bOrig ) ) : $bOrig;

					if (($rOrig != $rNew) or ($gOrig != $gNew) or ($bOrig != $bNew)) {
						$pixCol = ImageColorAllocate ( $finalimage, $rNew, $gNew, $bNew );
						ImageSetPixel ( $finalimage, $x, $y, $pixCol );
					}
				}
			}
		} else {
			for($x = 0; $x < $w; $x ++) // each row
{
				for($y = 0; $y < $h; $y ++) // each pixel
{
					$rgbOrig = ImageColorAt ( $finalimage, $x, $y );
					$rOrig = (($rgbOrig >> 16) & 0xFF);
					$gOrig = (($rgbOrig >> 8) & 0xFF);
					$bOrig = ($rgbOrig & 0xFF);

					$rgbBlur = ImageColorAt ( $imgBlur, $x, $y );

					$rBlur = (($rgbBlur >> 16) & 0xFF);
					$gBlur = (($rgbBlur >> 8) & 0xFF);
					$bBlur = ($rgbBlur & 0xFF);

					$rNew = ($amount * ($rOrig - $rBlur)) + $rOrig;
					if ($rNew > 255) {
						$rNew = 255;
					} elseif ($rNew < 0) {
						$rNew = 0;
					}

					$gNew = ($amount * ($gOrig - $gBlur)) + $gOrig;
					if ($gNew > 255) {
						$gNew = 255;
					} elseif ($gNew < 0) {
						$gNew = 0;
					}

					$bNew = ($amount * ($bOrig - $bBlur)) + $bOrig;
					if ($bNew > 255) {
						$bNew = 255;
					} elseif ($bNew < 0) {
						$bNew = 0;
					}

					$rgbNew = ($rNew << 16) + ($gNew << 8) + $bNew;
					ImageSetPixel ( $finalimage, $x, $y, $rgbNew );
				}
			}
		}
		imagedestroy ( $imgCanvas );
		imagedestroy ( $imgBlur );

		return true;
	}

	/**
	 * Public
	 * 输出图像基于字符串
	 *
	 * @param	string	string	输出的字符串
	 * @param bool		moveabout	有关移动文本
	 *
	 * @return	void
	 */
	function print_image_from_string($string, $moveabout = true) {
		global $skyuc;
		$image_width = 201;
		$image_height = 61;

		$backgrounds = $this->fetch_regimage_backgrounds ();

		if ($moveabout) {
			$notdone = true;

			while ( $notdone and ! empty ( $backgrounds ) ) {
				$index = mt_rand ( 0, count ( $backgrounds ) - 1 );
				$background = $backgrounds ["$index"];
				switch (strtolower ( file_extension ( $background ) )) {
					case 'jpg' :
					case 'jpe' :
					case 'jpeg' :
						if (! function_exists ( 'imagecreatefromjpeg' ) or ! $image = @imagecreatefromjpeg ( $background )) {
							unset ( $backgrounds ["$index"] );
						} else {
							$notdone = false;
						}
						break;
					case 'gif' :
						if (! function_exists ( 'imagecreatefromgif' ) or ! $image = @imagecreatefromgif ( $background )) {
							unset ( $backgrounds ["$index"] );
						} else {
							$notdone = false;
						}
						break;
					case 'png' :
						if (! function_exists ( 'imagecreatefrompng' ) or ! $image = @imagecreatefrompng ( $background )) {
							unset ( $backgrounds ["$index"] );
						} else {
							$notdone = false;
						}
						break;
				}
				sort ( $backgrounds );
			}
		}

		if ($image) {
			// randomly flip
			if (TIMENOW & 2) {
				$image = & $this->flipimage ( $image );
			}
			$gotbackground = true;
		} else {
			$image = & $this->fetch_image_resource ( $image_width, $image_height );
		}

		if (function_exists ( 'imagettftext' ) and $fonts = $this->fetch_regimage_fonts ()) {
			if ($moveabout) {
				// Randomly move the letters up and down
				for($x = 0; $x < strlen ( $string ); $x ++) {
					$index = mt_rand ( 0, count ( $fonts ) - 1 );
					if ($this->regimageoption ['randomfont']) {
						$font = $fonts ["$index"];
					} else {
						if (empty ( $font )) {
							$font = $fonts ["$index"];
						}
					}
					$image = $this->annotatettf ( $image, $string ["$x"], $font );
				}
			} else {
				$image = $this->annotatettf ( $image, $string, $fonts [0], false );
			}
		}

		if ($moveabout) {
			$blur = .9;
			/*if (function_exists('imagefilter'))
			{
				#if (!@imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR))
				{
			#		$image =& $this->blur($image, $blur);
				}
			}
			else
			{
			#	$image =& $this->blur($image, $blur);
			}*/
		}

		$text_color = imagecolorallocate ( $image, 0, 0, 0 );

		// draw a border
		imageline ( $image, 0, 0, $image_width, 0, $text_color );
		imageline ( $image, 0, 0, 0, $image_height, $text_color );
		imageline ( $image, $image_width - 1, 0, $image_width - 1, $image_height, $text_color );
		imageline ( $image, 0, $image_height - 1, $image_width, $image_height - 1, $text_color );

		$this->print_image ( $image, 'JPEG', true, 100 );
	}

	/**
	 * Private
	 * 创建空白图像
	 *
	 * @param int width	图像宽度
	 * @param int height	图像高度
	 *
	 * @return resource
	 */
	private function &fetch_image_resource($width, $height) {
		$image = imagecreatetruecolor ( $width, $height );
		$background_color = imagecolorallocate ( $image, 255, 255, 255 ); //白色背景
		imagefill ( $image, 0, 0, $background_color ); // For GD2+


		return $image;
	}

	/**
	 * Private
	 * 返回一个字母位置命令
	 *
	 * @param	resource	image		图像注释
	 * @param	string	letter	字符位置
	 * @param boolean	random 	应用效果
	 *
	 * @return	string
	 */
	private function &annotategd($image, $letter, $random = true) {

		// 位置开始
		static $r, $g, $b, $xposition = 10;

		if ($random) {
			if ($this->regimageoption ['randomcolor'] or empty ( $r )) {
				// 产生一个随机的颜色..
				$r = mt_rand ( 50, 200 );
				$b = mt_rand ( 50, 200 );
				$g = mt_rand ( 50, 200 );
			}

			$yposition = mt_rand ( 0, 5 );

			$text_color = imagecolorallocate ( $image, $r, $g, $b );
			imagechar ( $image, 5, $xposition, $yposition, $letter, $text_color );
			$xposition += mt_rand ( 10, 25 );
		} else {
			$text_color = imagecolorallocate ( $image, 0, 0, 0 );
			$yposition = 2;
			imagechar ( $image, 5, $xposition, $yposition, $letter, $text_color );
			$xposition += 10;
		}

		return $image;
	}

	/**
	 * Private
	 * 返回一个字母位置命令
	 *
	 * @param	resource	image		图像注释
	 * @param	string	letter	字符位置
	 * @param string	font		字体注释 (路径)
	 * @param boolean	slant		斜字体向左或向右
	 * @param boolean	random 	应用效果
	 *
	 * @return	string
	 */
	private function annotatettf($image, $letter, $font, $random = true) {
		if ($random) {
			// 位置开始
			static $r, $g, $b, $position = 15;

			// Y 轴线位置, 随机数从 35 至 48
			$y = mt_rand ( 35, 48 );

			if ($this->regimageoption ['randomcolor'] or empty ( $r )) {
				// 产生一个随机颜色……
				$r = mt_rand ( 50, 200 );
				$b = mt_rand ( 50, 200 );
				$g = mt_rand ( 50, 200 );
			}

			if ($this->regimageoption ['randomshape']) {
				if (function_exists ( 'imageantialias' )) {
					imageantialias ( $image, true );
				}
				// 笔划宽度, 2 或 3
				imagesetthickness ( $image, mt_rand ( 2, 3 ) );
				// 选择一个随机的颜色
				$shapecolor = imagecolorallocate ( $image, mt_rand ( 50, 200 ), mt_rand ( 50, 200 ), mt_rand ( 50, 200 ) );

				// 选择一个形状
				$x1 = mt_rand ( 0, 200 );
				$y1 = mt_rand ( 0, 60 );
				$x2 = mt_rand ( 0, 200 );
				$y2 = mt_rand ( 0, 60 );
				$start = mt_rand ( 0, 360 );
				$end = mt_rand ( 0, 360 );
				switch (mt_rand ( 1, 4 )) {
					case 1 :
						imagearc ( $image, $x1, $y1, $x2, $y2, $start, $end, $shapecolor );
						break;
					case 2 :
						imageellipse ( $image, $x1, $y1, $x2, $y2, $shapecolor );
						break;
					case 3 :
						imageline ( $image, $x1, $y1, $x2, $y2, $shapecolor );
						break;
					case 4 :
						imagepolygon ( $image, array ($x1, $y1, $x2, $y2, mt_rand ( 0, 200 ), mt_rand ( 0, 60 ), mt_rand ( 0, 200 ), mt_rand ( 0, 60 ) ), 4, $shapecolor );
						break;
				}
			}

			//角
			$slant = $this->regimageoption ['randomslant'] ? mt_rand ( - 20, 60 ) : 0;
			$pointsize = $this->regimageoption ['randomsize'] ? mt_rand ( 20, 32 ) : 24;
			$text_color = imagecolorallocate ( $image, $r, $g, $b );
		} else {
			$position = 10;
			$y = 40;
			$slant = 0;
			$pointsize = 24;
			$text_color = imagecolorallocate ( $image, 0, 0, 0 );
		}

		if (! $result = @imagettftext ( $image, $pointsize, $slant, $position, $y, $text_color, $font, $letter )) {
			return false;
		} else {
			$position += rand ( 25, 35 );
			return $image;
		}
	}

	/**
	 * Private
	 * 水平镜像图像。 可以扩展到其他翻转但这是所有我们现在需要
	 *
	 * @param	image	image			要转换的图像文件
	 *
	 * @return	object	image
	 */
	private function &flipimage(&$image) {
		$width = imagesx ( $image );
		$height = imagesy ( $image );

		$output = imagecreatetruecolor ( $width, $height );

		for($x = 0; $x < $height; $x ++) {
			imagecopy ( $output, $image, 0, $height - $x - 1, 0, $x, $width, 1 );
		}

		return $output;
	}

	/**
	 * Private
	 * 对一个图像应用一 个 漩涡或旋转 滤镜
	 *
	 * @param	image	image			要转换的图像文件
	 * @param	float	output			旋转的度数
	 * @param	bool	randirection	随机方向旋转（顺时针/逆时针）
	 *
	 * @return	object	image
	 */
	private function &swirl(&$image, $degree = .005, $randirection = true) {
		$image_width = imagesx ( $image );
		$image_height = imagesy ( $image );

		$temp = imagecreatetruecolor ( $image_width, $image_height );

		if ($randirection) {
			$degree = (mt_rand ( 0, 1 ) == 1) ? $degree : $degree * - 1;
		}

		$middlex = floor ( $image_width / 2 );
		$middley = floor ( $image_height / 2 );

		for($x = 0; $x < $image_width; $x ++) {
			for($y = 0; $y < $image_height; $y ++) {
				$xx = $x - $middlex;
				$yy = $y - $middley;

				$theta = atan2 ( $yy, $xx );

				$radius = sqrt ( $xx * $xx + $yy * $yy );

				$radius -= 5;

				$newx = $middlex + ($radius * cos ( $theta + $degree * $radius ));
				$newy = $middley + ($radius * sin ( $theta + $degree * $radius ));

				if (($newx > 0 and $newx < $image_width) and ($newy > 0 and $newy < $image_height)) {
					$index = imagecolorat ( $image, $newx, $newy );
					$colors = imagecolorsforindex ( $image, $index );
					$color = imagecolorresolve ( $temp, $colors ['red'], $colors ['green'], $colors ['blue'] );
				} else {
					$color = imagecolorresolve ( $temp, 255, 255, 255 );
				}

				imagesetpixel ( $temp, $x, $y, $color );
			}
		}

		return $temp;
	}

	/**
	 * Private
	 * 对一个图像应用波浪滤镜
	 *
	 * @param	image	image			要转换的图像
	 * @param	int		wave			要应用波浪的数量
	 * @param	bool	randirection	随机方向波
	 *
	 * @return	image
	 */
	private function &wave(&$image, $wave = 10, $randirection = true) {
		$image_width = imagesx ( $image );
		$image_height = imagesy ( $image );

		$temp = imagecreatetruecolor ( $image_width, $image_height );

		if ($randirection) {
			$direction = (TIMENOW & 2) ? true : false;
		}

		$middlex = floor ( $image_width / 2 );
		$middley = floor ( $image_height / 2 );

		for($x = 0; $x < $image_width; $x ++) {
			for($y = 0; $y < $image_height; $y ++) {

				$xo = $wave * sin ( 2 * 3.1415 * $y / 128 );
				$yo = $wave * cos ( 2 * 3.1415 * $x / 128 );

				if ($direction) {
					$newx = $x - $xo;
					$newy = $y - $yo;
				} else {
					$newx = $x + $xo;
					$newy = $y + $yo;
				}

				if (($newx > 0 and $newx < $image_width) and ($newy > 0 and $newy < $image_height)) {
					$index = imagecolorat ( $image, $newx, $newy );
					$colors = imagecolorsforindex ( $image, $index );
					$color = imagecolorresolve ( $temp, $colors ['red'], $colors ['green'], $colors ['blue'] );
				} else {
					$color = imagecolorresolve ( $temp, 255, 255, 255 );
				}

				imagesetpixel ( $temp, $x, $y, $color );
			}
		}

		return $temp;
	}

	/**
	 * Private
	 * 对一个图像应用模糊滤镜
	 *
	 * @param	image	image			要转换的图像
	 * @param	int		radius			模糊半径
	 *
	 * @return	image
	 */
	private function &blur(&$image, $radius = .5) {
		$radius = ($radius > 50) ? 100 : abs ( round ( $radius * 2 ) );

		if ($radius == 0) {
			return $image;
		}

		$w = imagesx ( $image );
		$h = imagesy ( $image );

		$imgCanvas = imagecreatetruecolor ( $w, $h );
		$imgBlur = imagecreatetruecolor ( $w, $h );
		imagecopy ( $imgCanvas, $image, 0, 0, 0, 0, $w, $h );

		// 高斯模糊矩阵：
		//
		//	1	2	1
		//	2	4	2
		//	1	2	1
		//
		//////////////////////////////////////////////////


		// Move copies of the image around one pixel at the time and merge them with weight
		// according to the matrix. The same matrix is simply repeated for higher radii.
		for($i = 0; $i < $radius; $i ++) {
			imagecopy ( $imgBlur, $imgCanvas, 0, 0, 1, 1, $w - 1, $h - 1 ); // up left
			imagecopymerge ( $imgBlur, $imgCanvas, 1, 1, 0, 0, $w, $h, 50 ); // down right
			imagecopymerge ( $imgBlur, $imgCanvas, 0, 1, 1, 0, $w - 1, $h, 33.33333 ); // down left
			imagecopymerge ( $imgBlur, $imgCanvas, 1, 0, 0, 1, $w, $h - 1, 25 ); // up right
			imagecopymerge ( $imgBlur, $imgCanvas, 0, 0, 1, 0, $w - 1, $h, 33.33333 ); // left
			imagecopymerge ( $imgBlur, $imgCanvas, 1, 0, 0, 0, $w, $h, 25 ); // right
			imagecopymerge ( $imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 20 ); // up
			imagecopymerge ( $imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 16.666667 ); // down
			imagecopymerge ( $imgBlur, $imgCanvas, 0, 0, 0, 0, $w, $h, 50 ); // center
			imagecopy ( $imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h );
		}
		imagedestroy ( $imgBlur );
		return $imgCanvas;
	}

	/**
	 * Public
	 * 检索有关图像的信息
	 *
	 * @param	string	filename	文件的位置
	 * @param	string	extension	文件扩展名
	 *
	 * @return	array	[0]			int		width
	 * [1]			int		height
	 * [2]			string	type ('GIF', 'JPEG', 'PNG', 'PSD', 'BMP', 'TIFF',) (and so on)
	 * [scenes]	int		scenes
	 * [channels]	int		Number of channels (GREYSCALE = 1, RGB = 3, CMYK = 4)
	 * [bits]		int		Number of bits per pixel
	 * [library]	string	Library Identifier
	 */
	function fetch_image_info($filename) {
		static $types = array (1 => 'GIF', 2 => 'JPEG', 3 => 'PNG', 4 => 'SWF', 5 => 'PSD', 6 => 'BMP', 7 => 'TIFF', 8 => 'TIFF', 9 => 'JPC', 10 => 'JP2', 11 => 'JPX', 12 => 'JB2', 13 => 'SWC', 14 => 'IFF', 15 => 'WBMP', 16 => 'XBM' );

		if (! $this->verify_image_file ( $filename )) {
			return false;
		}

		// 如果可以，请使用 PHP 的 getimagesize
		if ($imageinfo = getimagesize ( $filename )) {
			$this->imageinfo = array (0 => $imageinfo [0], 1 => $imageinfo [1], 2 => $types ["$imageinfo[2]"], 'channels' => $imageinfo ['channels'], 'bits' => $imageinfo ['bits'], 'scenes' => 1, 'library' => 'GD', 'animated' => false );

			if ($this->imageinfo [2] == 'GIF') { // 获取场景
				$data = file_get_contents ( $filename );

				// 寻找一个全局彩色表字符和图像分隔符字符
				$this->imageinfo ['scenes'] = count ( preg_split ( '#\x00[\x00-\xFF]\x00\x2C#', $data ) ) - 1;

				$this->imageinfo ['animated'] = (strpos ( $data, 'NETSCAPE2.0' ) !== false);
				unset ( $data );
			}

			return $this->imageinfo;
		} // getimagesize 对一些 jpegs 无效，但我们可以尝试创建一个图像找到尺寸
else if (function_exists ( 'imagecreatefromjpeg' ) and $img = @imagecreatefromjpeg ( $filename )) {
			$this->imageinfo = array (0 => imagesx ( $img ), 1 => imagesy ( $img ), 2 => 'JPEG', 'channels' => 3, 'bits' => 8, 'library' => 'GD' );
			imagedestroy ( $img );

			return $this->imageinfo;
		} else {
			return false;
		}
	}

	/**
	 * Public
	 * 返回包含一个缩略图、 创建时间、 缩略图大小和任何错误的数组
	 *
	 * @param	string	filename	源文件的文件名($_FILES['userfile']['name'] 客户端机器文件的原名称)
	 * @param	string	location	源文件的位置($_FILES['userfile']['tmp_name'] 文件被上传后在服务端储存的临时文件名。)
	 * @param	int		newsize			新图像大小 (图像的最长边)
	 * @param	int		quality			Jpeg 质量
	 * @param bool		labelimage	缩略图，包含图像尺寸和大小
	 * @param bool		drawborder	绘制缩略图周围的边框
	 *
	 * @return	array
	 */
	function fetch_thumbnail($filename, $location, $maxwidth = 100, $maxheight = 100, $quality = 75, $labelimage = false, $drawborder = false, $jpegconvert = false, $sharpen = true, $owidth = null, $oheight = null, $ofilesize = null) {
		$thumbnail = array ('filedata' => '', 'filesize' => 0, 'dateline' => 0, 'imageerror' => '' );

		if ($validfile = $this->is_valid_thumbnail_extension ( file_extension ( $filename ) ) and $imageinfo = $this->fetch_image_info ( $location )) {
			$new_width = $width = $imageinfo [0];
			$new_height = $height = $imageinfo [1];

			if ($this->fetch_imagetype_from_extension ( file_extension ( $filename ) ) != $imageinfo [2]) {
				$thumbnail ['imageerror'] = 'thumbnail_notcorrectimage';
			} else if ($width > $maxwidth or $height > $maxheight) {
				$memoryok = true;
				if (function_exists ( 'memory_get_usage' ) and $memory_limit = @ini_get ( 'memory_limit' ) and $memory_limit != - 1) {
					$memorylimit = skyuc_number_format ( $memory_limit, 0, false, null, '' );
					$memoryusage = memory_get_usage ();
					$freemem = $memorylimit - $memoryusage;
					$checkmem = true;
					$tmemory = $width * $height * ($imageinfo [2] == 'JPEG' ? 5 : 2) + 7372.8 + sqrt ( sqrt ( $width * $height ) );
					$tmemory += 166000; // 蒙混因子, 对象开销等。


					if ($freemem > 0 and $tmemory > $freemem and $tmemory <= ($memorylimit * 3)) { // 尝试增加内存，不超过三次
						if (($current_memory_limit = ini_size_to_bytes ( @ini_get ( 'memory_limit' ) )) < $memorylimit + $tmemory and $current_memory_limit > 0) {
							@ini_set ( 'memory_limit', $memorylimit + $tmemory );
						}

						$memory_limit = @ini_get ( 'memory_limit' );
						$memorylimit = skyuc_number_format ( $memory_limit, 0, false, null, '' );
						$memoryusage = memory_get_usage ();
						$freemem = $memorylimit - $memoryusage;
					}
				}

				switch ($imageinfo [2]) {
					case 'GIF' :
						if (function_exists ( 'imagecreatefromgif' )) {
							if ($checkmem) {
								if ($freemem > 0 and $tmemory > $freemem) {
									$thumbnail ['imageerror'] = 'thumbnail_notenoughmemory';
									$memoryok = false;
								}
							}
							if ($memoryok and ! $image = @imagecreatefromgif ( $location )) {
								$thumbnail ['imageerror'] = 'thumbnail_nocreateimage';
							}
						} else {
							$thumbnail ['imageerror'] = 'thumbnail_nosupport';
						}
						break;
					case 'JPEG' :
						if (function_exists ( 'imagecreatefromjpeg' )) {
							if ($checkmem) {
								if ($freemem > 0 and $tmemory > $freemem) {
									$thumbnail ['imageerror'] = 'thumbnail_notenoughmemory';
									$memoryok = false;
								}
							}

							if ($memoryok and ! $image = @imagecreatefromjpeg ( $location )) {
								$thumbnail ['imageerror'] = 'thumbnail_nocreateimage';
							}
						} else {
							$thumbnail ['imageerror'] = 'thumbnail_nosupport';
						}
						break;
					case 'PNG' :
						if (function_exists ( 'imagecreatefrompng' )) {
							if ($checkmem) {
								if ($freemem > 0 and $tmemory > $freemem) {
									$thumbnail ['imageerror'] = 'thumbnail_notenoughmemory';
									$memoryok = false;
								}
							}
							if ($memoryok and ! $image = @imagecreatefrompng ( $location )) {
								$thumbnail ['imagerror'] = 'thumbnail_nocreateimage';
							}
						} else {
							$thumbnail ['imageerror'] = 'thumbnail_nosupport';
						}
						break;
				}

				if ($image) {
					$xratio = ($maxwidth == 0) ? 1 : $width / $maxwidth;
					$yratio = ($maxheight == 0) ? 1 : $height / $maxheight;
					if ($xratio > $yratio) {
						$new_width = round ( $width / $xratio );
						$new_height = round ( $height / $xratio );
					} else {
						$new_width = round ( $width / $yratio );
						$new_height = round ( $height / $yratio );
					}

					if ($drawborder) {
						$create_width = $new_width + 2;
						$create_height = $new_height + 2;
						$dest_x_start = 1;
						$dest_y_start = 1;
					} else {
						$create_width = $new_width;
						$create_height = $new_height;
						$dest_x_start = 0;
						$dest_y_start = 0;
					}

					if ($labelimage) {
						$font = 2;
						$labelboxheight = ($drawborder) ? 13 : 14;

						if ($ofilesize) {
							$filesize = $ofilesize;
						} else {
							$filesize = @filesize ( $location );
						}

						if ($filesize / 1024 < 1) {
							$filesize = 1024;
						}
						if ($owidth) {
							$dimensions = $owidth . 'x' . $oheight;
						} else {
							$dimensions = (! empty ( $width ) and ! empty ( $height )) ? "{$width}x{$height}" : '';
						}

						$sizestring = (! empty ( $filesize )) ? number_format ( $filesize / 1024, 0, '', '' ) . 'kb' : '';

						if (($string_length = (strlen ( $string = "$dimensions $sizestring $imageinfo[2]" ) * imagefontwidth ( $font ))) < $new_width) {
							$finalstring = $string;
							$finalwidth = $string_length;
						} else if (($string_length = (strlen ( $string = "$dimensions $sizestring" ) * imagefontwidth ( $font ))) < $new_width) {
							$finalstring = $string;
							$finalwidth = $string_length;
						} else if (($string_length = (strlen ( $string = $dimensions ) * imagefontwidth ( $font ))) < $new_width) {
							$finalstring = $string;
							$finalwidth = $string_length;
						} else if (($string_length = (strlen ( $string = $sizestring ) * imagefontwidth ( $font ))) < $new_width) {
							$finalstring = $string;
							$finalwidth = $string_length;
						}

						if (! empty ( $finalstring )) {
							$create_height += $labelboxheight;
							if ($drawborder) {
								$label_x_start = ($new_width - ($finalwidth)) / 2 + 2;
								$label_y_start = ($labelboxheight - imagefontheight ( $font )) / 2 + $new_height + 1;
							} else {
								$label_x_start = ($new_width - ($finalwidth)) / 2 + 1;
								$label_y_start = ($labelboxheight - imagefontheight ( $font )) / 2 + $new_height;
							}
						}
					}
					if (! ($finalimage = @imagecreatetruecolor ( $create_width, $create_height ))) {
						$thumbnail ['imageerror'] = 'thumbnail_nocreateimage';
						imagedestroy ( $image );
						return $thumbnail;
					}

					$bgcolor = imagecolorallocate ( $finalimage, 255, 255, 255 );
					imagefill ( $finalimage, 0, 0, $bgcolor );
					//@imagecopyresampled($finalimage, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_width, $dst_height, $srcWidth, $srcHeight);
					@imagecopyresampled ( $finalimage, $image, $dest_x_start, $dest_y_start, 0, 0, $new_width, $new_height, $width, $height );
					imagedestroy ( $image );
					if ($sharpen and $this->imageinfo [2] != 'GIF') {
						$this->unsharpmask ( $finalimage );
					}

					if ($labelimage and ! empty ( $finalstring )) {
						$bgcolor = imagecolorallocate ( $finalimage, $this->thumbcolor ['r'], $this->thumbcolor ['g'], $this->thumbcolor ['b'] );
						$recstart = ($drawborder) ? $create_height - $labelboxheight - 1 : $create_height - $labelboxheight;
						imagefilledrectangle ( $finalimage, 0, $recstart, $create_width, $create_height, $bgcolor );
						$textcolor = imagecolorallocate ( $finalimage, 255, 255, 255 );
						imagestring ( $finalimage, $font, $label_x_start, $label_y_start, $finalstring, $textcolor );
					}

					if ($drawborder) {
						$bordercolor = imagecolorallocate ( $finalimage, $this->thumbcolor ['r'], $this->thumbcolor ['g'], $this->thumbcolor ['b'] );
						imageline ( $finalimage, 0, 0, $create_width, 0, $bordercolor );
						imageline ( $finalimage, 0, 0, 0, $create_height, $bordercolor );
						imageline ( $finalimage, $create_width - 1, 0, $create_width - 1, $create_height, $bordercolor );
						imageline ( $finalimage, 0, $create_height - 1, $create_width, $create_height - 1, $bordercolor );
					}

					ob_start ();
					$new_extension = $this->print_image ( $finalimage, $jpegconvert ? 'JPEG' : $imageinfo [2], false, $quality );
					$thumbnail ['filedata'] = ob_get_contents ();
					ob_end_clean ();
					$thumbnail ['width'] = $new_width;
					$thumbnail ['height'] = $new_height;
					$extension = file_extension ( $filename );
					if ($new_extension != $extension) {
						$thumbnail ['filename'] = preg_replace ( '#' . preg_quote ( $extension, '#' ) . '$#', $new_extension, $filename );
					}
				}
			} else {
				if ($imageinfo [0] == 0 and $imageinfo [1] == 0) // getimagesize() 失败
{
					$thumbnail ['filedata'] = '';
					$thumbnail ['imageerror'] = 'thumbnail_nogetimagesize';
				} else {
					$thumbnail ['filedata'] = @file_get_contents ( $location );
					$thumbnail ['width'] = $imageinfo [0];
					$thumbnail ['height'] = $imageinfo [1];
					$thumbnail ['imageerror'] = 'thumbnailalready';
				}
			}
		} else if (! $validfile) {
			$thumbnail ['filedata'] = '';
			$thumbnail ['imageerror'] = 'thumbnail_nosupport';
		}

		if (! empty ( $thumbnail ['filedata'] )) {
			$thumbnail ['filesize'] = strlen ( $thumbnail ['filedata'] );
			$thumbnail ['dateline'] = TIMENOW;
		}
		return $thumbnail;
	}

	/**
	 * Public
	 * 从图形库返回错误
	 *
	 * @return	mixed
	 */
	function fetch_error() {
		return false;
	}
}

/**
 *
 * 生成缩略图
 *
 * @param	object	$image					图像处理类句柄
 * @param	string	$imagepath			原始图片
 * @param	int			$thumb_width 		目标缩略图宽度
 * @param	int			$thumb_height		目标缩略图高度
 * @param  bool		$unlink					是否删除原始图片
 *
 * @return	string or array   错误时返回数组，成功时返回新图片路径
 */
function make_thumb($image, $imagepath, $thumb_width = 0, $thumb_height = 0, $unlink = TRUE) {
	global $skyuc;

	if (! is_dir ( $image->path )) {
		make_dir ( $image->path );
	}
	if (empty ( $image->filename )) {
		//新生成图片使用新名称
		$imageinfo = $image->fetch_image_info ( $imagepath );
		$saveimage = $image->path . '/' . TIMENOW . '_' . random ( 3, 0 ) . '.' . ($imageinfo [2] == 'JPEG' ? 'jpg' : strtolower ( $imageinfo [2] ));
	} else {
		//新生成图片使用旧名称
		$saveimage = $image->path . '/' . $image->filename;
	}

	//禁止生成缩略图
	if ($skyuc->options ['attachthumbs'] == 0) {
		copy ( $imagepath, DIR . '/' . $saveimage );

	} else {
		$labelimage = ($skyuc->options ['attachthumbs'] == 3 or $skyuc->options ['attachthumbs'] == 4);
		$drawborder = ($skyuc->options ['attachthumbs'] == 2 or $skyuc->options ['attachthumbs'] == 4);
		$thumb_width = $thumb_width == 0 ? 100 : $thumb_width;
		$thumb_height = $thumb_height == 0 ? 100 : $thumb_height;
		$thumbnail = $image->fetch_thumbnail ( $saveimage, $imagepath, $thumb_width, $thumb_height, $skyuc->options ['thumbquality'], $labelimage, $drawborder );

		//图片处理发生错误，返回错误
		if ($thumbnail ['imageerror'] != '') {
			$error = array ('error' => $thumbnail ['imageerror'], 'image' => $imagepath );
			return $error;
		}

		@file_put_contents ( DIR . '/' . $saveimage, $thumbnail ['filedata'] );
	}

	if ($unlink) {
		@unlink ( $imagepath );
	}

	return $saveimage;
}
?>