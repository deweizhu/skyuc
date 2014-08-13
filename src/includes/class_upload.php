<?php
/**
 * SKYUC! 对上传文件的处理类
 *
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

if (! isset ( $GLOBALS ['db'] )) {
	exit ();
}

/**
 * 处理从 $_FILES POST 数据的抽象的类
 *
 */
class Upload_Abstract {

	/**
	 * 在上载或验证的过程中遇到的任何错误
	 *
	 * @public	array
	 */
	public $error = '';

	/**
	 * 主要注册表对象
	 *
	 * @public	Registry
	 */
	public $registry = null;

	/**
	 * 验证和调整大小图像对象
	 *
	 * public	Image
	 */
	public $image = null;

	/**
	 * 用于保存/删除操作的对象
	 *
	 * @public	DataManager
	 */
	public $upload = null;

	/**
	 * 我们正在上传的有关资料信息
	 *
	 * @public	array
	 */
	public $data = null;

	/**
	 * 上传的图像宽度和高度
	 *
	 * @public	array
	 */
	public $imginfo = array ();

	/**
	 * 文件的最大大小。 设置为零将不检查
	 *
	 * @public	int
	 */
	public $maxuploadsize = 0;

	/**
	 * 上载图像的最大像素宽度。 设置为零将不检查
	 *
	 * @public	int
	 */
	public $maxwidth = 0;

	/**
	 *上传图像的最大像素高度。 设置为零将不检查
	 *
	 * @public	int
	 */
	public $maxheight = 0;

	/**
	 * 是否显示错误消息，如果上传发出的空白或无效 (false = 多上载窗体）
	 *
	 * @public  bool
	 */
	public $emptyfile = true;

	/**
	 * 是否允许动画的 GIF 所上载
	 *
	 * @public boolean
	 */
	public $allowanimation = null;

	function __construct(&$registry) {
		$this->registry = & $registry;
	}

	/**
	 * 设置警告
	 *
	 * @param	string	Varname of error phrase
	 * @param	mixed	Value of 1st variable
	 * @param	mixed	Value of 2nd variable
	 * @param	mixed	Value of Nth variable
	 */
	function set_warning() {
		$args = func_get_args ();

		$this->error = call_user_func_array ( 'fetch_error', $args );
	}

	/**
	 * 设置错误状态并删除任何已上载的文件
	 *
	 * @param	string	Varname of error phrase
	 * @param	mixed	Value of 1st variable
	 * @param	mixed	Value of 2nd variable
	 * @param	mixed	Value of Nth variable
	 */
	function set_error() {
		$args = func_get_args ();

		$this->error = call_user_func_array ( 'fetch_error', $args );

		if (! empty ( $this->upload ['location'] )) {
			@unlink ( $this->upload ['location'] );
		}
	}

	/**
	 * 返回当前的错误
	 *
	 */
	function &fetch_error() {
		return $this->error;
	}

	/**
	 * 此函数接受文件通过 URL，或从 $_FILES、 验证它，并将其放在处理一个临时位置
	 *
	 * @param	mixed	有效选项是： （a）文件网址检索或 (b）一个指针到一个文件中的变量$_FILES数组
	 */
	function accept_upload(&$upload) {
		$this->error = '';

		if (! is_array ( $upload ) and strval ( $upload ) != '') {
			$this->upload ['extension'] = strtolower ( file_extension ( $upload ) );

			// 检查文件扩展名
			if (! $this->is_valid_extension ( $this->upload ['extension'] )) {
				$this->set_error ( 'upload_invalid_file' );
				return false;
			}

			$this->maxuploadsize = fetch_max_upload_size ();
			if (! $this->maxuploadsize) {
				$newmem = 20971520;
			}

			if (! preg_match ( '#^((http|ftp)s?):\/\/#i', $upload )) {
				$upload = 'http://' . $upload;
			}

			if (ini_get ( 'allow_url_fopen' ) == 0 and ! function_exists ( 'curl_init' )) {
				$this->set_error ( 'upload_fopen_disabled' );
				return false;
			} else if ($filesize = $this->fetch_remote_filesize ( $upload )) {
				if ($this->maxuploadsize and $filesize > $this->maxuploadsize) {
					$this->set_error ( 'upload_remoteimage_toolarge' );
					return false;
				} else {
					if (function_exists ( 'memory_get_usage' ) and $memory_limit = @ini_get ( 'memory_limit' ) and $memory_limit != - 1) {
						//确保我们有足够的内存来处理此文件
						$memorylimit = skyuc_number_format ( $memory_limit, 0, false, null, '' );
						$memoryusage = memory_get_usage ();
						$freemem = $memorylimit - $memoryusage;
						$newmemlimit = ! empty ( $newmem ) ? $freemem + $newmem : $freemem + $filesize;

						if (($current_memory_limit = ini_size_to_bytes ( @ini_get ( 'memory_limit' ) )) < $newmemlimit and $current_memory_limit > 0) {
							@ini_set ( 'memory_limit', $newmemlimit );
						}
					}

					require_once (DIR . '/includes/class_vurl.php');
					$vurl = new SKYUC_vURL ( $this->registry );
					$vurl->set_option ( VURL_URL, $upload );
					$vurl->set_option ( VURL_HEADER, true );
					$vurl->set_option ( VURL_MAXSIZE, $this->maxuploadsize );
					$vurl->set_option ( VURL_RETURNTRANSFER, true );
					if ($result = $vurl->exec2 ()) {

					} else {
						switch ($vurl->fetch_error ()) {
							case VURL_ERROR_MAXSIZE :
								$this->set_error ( 'upload_remoteimage_toolarge' );
								break;
							case VURL_ERROR_NOLIB : // 这个条件不可到达
								$this->set_error ( 'upload_fopen_disabled' );
								break;
							case VURL_ERROR_SSL :
							case VURL_URL_URL :
							default :
								$this->set_error ( 'retrieval_of_remote_file_failed' );
						}

						return false;
					}
					unset ( $vurl );
				}
			} else {
				$this->set_error ( 'upload_invalid_url' );
				return false;
			}

			// 写文件到临时文件夹...
			if ($this->registry->options ['safeupload']) {
				// ... 在安全模式情况下
				$this->upload ['location'] = $this->registry->options ['tmppath'] . '/skyucupload' . $this->userinfo ['userid'] . substr ( TIMENOW, - 4 );
			} else {
				// ... 在正常模式情况下
				$this->upload ['location'] = @tempnam ( ini_get ( 'upload_tmp_dir' ), 'skyucupload' );
			}

			$attachment_write_failed = true;
			if (! empty ( $result ['body'] )) {
				$fp = @fopen ( $this->upload ['location'], 'wb' );
				if ($fp and $this->upload ['location']) {
					@fwrite ( $fp, $result ['body'] );
					@fclose ( $fp );
					$attachment_write_failed = false;
				}
			} else if (file_exists ( $result ['body_file'] )) {
				if (@rename ( $result ['body_file'], $this->upload ['location'] ) or (copy ( $result ['body_file'], $this->upload ['location'] ) and unlink ( $result ['body_file'] ))) {
					$mask = 0777 & ~ umask ();
					@chmod ( $this->upload ['location'], $mask );

					$attachment_write_failed = false;
				}
			}

			if ($attachment_write_failed) {
				$this->set_error ( 'upload_writefile_failed' );
				return false;
			}

			$this->upload ['filesize'] = @filesize ( $this->upload ['location'] );
			$this->upload ['filename'] = basename ( $upload );
			$this->upload ['extension'] = strtolower ( file_extension ( $this->upload ['filename'] ) );
			$this->upload ['thumbnail'] = '';
			$this->upload ['filestuff'] = '';
			$this->upload ['url'] = true;
		} else {
			$this->upload ['filename'] = trim ( $upload ['name'] );
			$this->upload ['filesize'] = intval ( $upload ['size'] );
			$this->upload ['location'] = trim ( $upload ['tmp_name'] );
			$this->upload ['extension'] = strtolower ( file_extension ( $this->upload ['filename'] ) );
			$this->upload ['thumbnail'] = '';
			$this->upload ['filestuff'] = '';

			if ($this->upload ['error']) {
				// 遇到的 PHP 上载错误
				if (! ($maxupload = @ini_get ( 'upload_max_filesize' ))) {
					$maxupload = 10485760;
				}
				$maxattachsize = skyuc_number_format ( $maxupload, 1, true );

				switch ($this->upload ['error']) {
					case '1' : // UPLOAD_ERR_INI_SIZE
					case '2' : // UPLOAD_ERR_FORM_SIZE
						$this->set_error ( 'upload_file_exceeds_php_limit', $maxattachsize );
						break;
					case '3' : // UPLOAD_ERR_PARTIAL
						$this->set_error ( 'upload_file_partially_uploaded' );
						break;
					case '4' :
						$this->set_error ( 'upload_file_failed' );
						break;
					case '6' :
						$this->set_error ( 'missing_temporary_folder' );
						break;
					case '7' :
						$this->set_error ( 'upload_writefile_failed' );
						break;
					case '8' :
						$this->set_error ( 'upload_stopped_by_extension' );
						break;
					default :
						$this->set_error ( 'upload_invalid_file' );
				}

				return false;
			} else if ($this->upload ['error'] or $this->upload ['location'] == 'none' or $this->upload ['location'] == '' or $this->upload ['filename'] == '' or ! $this->upload ['filesize'] or ! is_uploaded_file ( $this->upload ['location'] )) {
				if ($this->emptyfile or $this->upload ['filename'] != '') {
					$this->set_error ( 'upload_file_failed' );
				}
				return false;
			}

			if ($this->registry->options ['safeupload']) {
				$temppath = $this->registry->options ['tmppath'] . '/' . $this->registry->session->fetch_sessionhash ();
				$moveresult = @move_uploaded_file ( $this->upload ['location'], $temppath );
				if (! $moveresult) {
					$this->set_error ( 'upload_unable_move' );
					return false;
				}
				$this->upload ['location'] = $temppath;
			}
		}

		$return_value = true;

		return $return_value;

	}

	/**
	 * 请求远程文件头部，检索其大小，排除正在下载中的文件
	 *
	 * @param	string	要检索大小的远程文件 URL
	 */
	function fetch_remote_filesize($url) {
		if (! preg_match ( '#^((http|ftp)s?):\/\/#i', $url, $check )) {
			$this->set_error ( 'upload_invalid_url' );
			return false;
		}

		require_once (DIR . '/includes/class_vurl.php');
		$vurl = new SKYUC_vURL ( $this->registry );
		$vurl->set_option ( VURL_URL, $url );
		$vurl->set_option ( VURL_HEADER, 1 );
		$vurl->set_option ( VURL_NOBODY, 1 );
		$vurl->set_option ( VURL_USERAGENT, 'SKYUC via PHP' );
		$vurl->set_option ( VURL_CUSTOMREQUEST, 'HEAD' );
		$vurl->set_option ( VURL_RETURNTRANSFER, 1 );
		$vurl->set_option ( VURL_CLOSECONNECTION, 1 );
		if ($result = $vurl->exec2 () and $length = intval ( $result ['content-length'] )) {
			return $length;
		} else {
			return false;
		}
	}

	/**
	 * 试图调整档案如果档案大小过大，在经过最初的最大尺寸调整后或文件已经在最大尺寸内，但仍然太大。
	 *
	 * @param	bool	该图像已经调整一次？
	 * @param	bool	尝试调整
	 */
	function fetch_best_resize(&$jpegconvert, $resize = true) {
		if (! $jpegconvert and $this->upload ['filesize'] > $this->maxuploadsize and $resize and $this->image->is_valid_resize_type ( $this->imginfo [2] )) {
			// 线性回归
			switch ($this->registry->options ['thumbquality']) {
				case 65 :
					// 没有锐化
					// $magicnumber = round(379.421 + .00348171 * $this->maxuploadsize);
					// 锐化
					$magicnumber = round ( 277.652 + .00428902 * $this->maxuploadsize );
					break;
				case 85 :
					// 没有锐化
					// $magicnumber = round(292.53 + .0027378 * $this-maxuploadsize);
					// 锐化
					$magicnumber = round ( 189.939 + .00352439 * $this->maxuploadsize );
					break;
				case 95 :
					// 没有锐化
					// $magicnumber = round(188.11 + .0022561 * $this->maxuploadsize);
					// 锐化
					$magicnumber = round ( 159.146 + .00234146 * $this->maxuploadsize );
					break;
				default : //75
					// 没有锐化
					// $magicnumber = round(328.415 + .00323415 * $this->maxuploadsize);
					// 锐化
					$magicnumber = round ( 228.201 + .00396951 * $this->maxuploadsize );
			}

			$xratio = ($this->imginfo [0] > $magicnumber) ? $magicnumber / $this->imginfo [0] : 1;
			$yratio = ($this->imginfo [1] > $magicnumber) ? $magicnumber / $this->imginfo [1] : 1;

			if ($xratio > $yratio and $xratio != 1) {
				$new_width = round ( $this->imginfo [0] * $xratio );
				$new_height = round ( $this->imginfo [1] * $xratio );
			} else {
				$new_width = round ( $this->imginfo [0] * $yratio );
				$new_height = round ( $this->imginfo [1] * $yratio );
			}
			if ($new_width == $this->imginfo [0] and $new_height == $this->imginfo [1]) { // 减去一个像素，以便要求大小不是图像大小相同
				$new_width --;
				$forceresize = false;
			} else {
				$forceresize = true;
			}

			$this->upload ['resized'] = $this->image->fetch_thumbnail ( $this->upload ['filename'], $this->upload ['location'], $new_width, $new_height, $this->registry->options ['thumbquality'], false, false, true, false );

			if (empty ( $this->upload ['resized'] ['filedata'] )) {
				if ($this->image->is_valid_thumbnail_extension ( file_extension ( $this->upload ['filename'] ) ) and ! empty ( $this->upload ['resized'] ['imageerror'] )) {
					if (($error = $this->image->fetch_error ()) !== false) {
						$this->set_error ( 'image_resize_failed_x', htmlspecialchars_uni ( $error ) );
						return false;
					} else {
						$this->set_error ( $this->upload ['resized'] ['imageerror'] );
						return false;
					}
				} else {
					$this->set_error ( 'upload_file_exceeds_limit', skyuc_number_format ( $this->upload ['filesize'], 1, true ), skyuc_number_format ( $this->maxuploadsize, 1, true ) );
					#$this->set_error('upload_exceeds_dimensions', $this->maxwidth, $this->maxheight, $this->imginfo[0], $this->imginfo[1]);
					return false;
				}
			} else {
				$jpegconvert = true;
			}
		}

		if (! $jpegconvert and $this->upload ['filesize'] > $this->maxuploadsize) {
			$this->set_error ( 'upload_file_exceeds_limit', skyuc_number_format ( $this->upload ['filesize'], 1, true ), skyuc_number_format ( $this->maxuploadsize, 1, true ) );
			return false;
		} else if ($jpegconvert and $this->upload ['resized'] ['filesize'] and ($this->upload ['resized'] ['filesize'] > $this->maxuploadsize or $forceresize)) {
			$ratio = $this->maxuploadsize / $this->upload ['resized'] ['filesize'];

			$newwidth = $this->upload ['resized'] ['width'] * sqrt ( $ratio );
			$newheight = $this->upload ['resized'] ['height'] * sqrt ( $ratio );

			if ($newwidth > $this->imginfo [0]) {
				$newwidth = $this->imginfo [0] - 1;
			}
			if ($newheight > $this->imginfo [1]) {
				$newheight = $this->imginfo [1] - 1;
			}

			$this->upload ['resized'] = $this->image->fetch_thumbnail ( $this->upload ['filename'], $this->upload ['location'], $newwidth, $newheight, $this->registry->options ['thumbquality'], false, false, true, false );
			if (empty ( $this->upload ['resized'] ['filedata'] )) {
				if (! empty ( $this->upload ['resized'] ['imageerror'] )) {
					if (($error = $this->image->fetch_error ()) !== false) {
						$this->set_error ( 'image_resize_failed_x', htmlspecialchars_uni ( $error ) );
						return false;
					} else {
						$this->set_error ( $this->upload ['resized'] ['imageerror'] );
						return false;
					}
				} else {
					$this->set_error ( 'upload_file_exceeds_limit', skyuc_number_format ( $this->upload ['filesize'], 1, true ), skyuc_number_format ( $this->maxuploadsize, 1, true ) );
					#$this->set_error('upload_exceeds_dimensions', $this->maxwidth, $this->maxheight, $this->imginfo[0], $this->imginfo[1]);
					return false;
				}
			} else {
				$jpegconvert = true;
			}
		}

		return true;
	}

}

class Upload_Image extends Upload_Abstract {
	/**
	 * 上传图片保存到的路径
	 *
	 * @public	string
	 */
	public $path = '';

	function is_valid_extension($extension) {
		return ! empty ( $this->image->info_extensions ["{$this->upload['extension']}"] );
	}

	function process_upload($uploadurl = '') {
		if ($uploadurl == '' or $uploadurl == 'http://www.') {
			$uploadstuff = & $this->registry->GPC ['upload'];
		} else {
			if (is_uploaded_file ( $this->registry->GPC ['upload'] ['tmp_name'] )) {
				$uploadstuff = & $this->registry->GPC ['upload'];
			} else {
				$uploadstuff = & $uploadurl;
			}
		}

		if ($this->accept_upload ( $uploadstuff )) {
			if ($this->image->is_valid_thumbnail_extension ( file_extension ( $this->upload ['filename'] ) )) {
				if ($this->imginfo = $this->image->fetch_image_info ( $this->upload ['location'] )) {
					if (! $this->image->fetch_must_convert ( $this->imginfo [2] )) {
						if (! $this->imginfo [2]) {
							$this->set_error ( 'upload_invalid_image' );
							return false;
						}

						if ($this->image->fetch_imagetype_from_extension ( $this->upload ['extension'] ) != $this->imginfo [2]) {
							$this->set_error ( 'upload_invalid_image_extension', $this->imginfo [2] );
							return false;
						}
					} else {
						$this->set_error ( 'upload_invalid_image' );
						return false;
					}
				} else {
					$this->set_error ( 'upload_imageinfo_failed_x', htmlspecialchars_uni ( $this->image->fetch_error () ) );
					return false;
				}
			} else {
				$this->set_error ( 'upload_invalid_image' );
				return false;
			}

			if (! $this->upload ['filestuff']) {
				if (! ($this->upload ['filestuff'] = file_get_contents ( $this->upload ['location'] ))) {
					$this->set_error ( 'upload_file_failed' );
					return false;
				}
			}
			@unlink ( $this->upload ['location'] );

			return $this->save_upload ();
		} else {
			return false;
		}
	}

	function save_upload() {
		if (! is_dir ( $this->path )) {
			make_dir ( $this->path );
		}
		if (! is_writable ( $this->path ) or ! ($fp = fopen ( $this->path . '/' . $this->upload ['filename'], 'wb' ))) {
			$this->set_error ( 'invalid_file_path_specified' );
			return false;
		}

		if (@fwrite ( $fp, $this->upload ['filestuff'] ) === false) {
			$this->set_error ( 'error_writing_x', $this->upload ['filename'] );
			return false;
		}

		@fclose ( $fp );
		return $this->path . '/' . $this->upload ['filename'];
	}
}
/*
 * 获取上传时遇到的错误消息
 */

function fetch_error() {
	global $skyuc;

	$args = func_get_args ();

	if (is_array ( $args [0] )) {
		$args = $args [0];
	}

	$args [0] = $skyuc->lang ["$args[0]"];
	if ($skyuc->GPC ['is_ajax']) {
		make_json_error ( $args [0] );
	}

	if (count ( $args ) > 1) {
		// call sprintf() on the first argument of this function
		return @call_user_func_array ( 'sprintf', $args );
	} else {
		return $args [0];
	}
}
?>