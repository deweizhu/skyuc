<?php
/**
 * SKYUC! 下载类库，支持curl和fsockopen
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

define ( 'VURL_URL', 1 );
define ( 'VURL_TIMEOUT', 2 );
define ( 'VURL_POST', 4 );
define ( 'VURL_HEADER', 8 );
define ( 'VURL_POSTFIELDS', 16 );
define ( 'VURL_ENCODING', 32 );
define ( 'VURL_USERAGENT', 64 );
define ( 'VURL_RETURNTRANSFER', 128 );
define ( 'VURL_HTTPHEADER', 256 );
define ( 'VURL_REFERER', 512 );

define ( 'VURL_CLOSECONNECTION', 1024 );
define ( 'VURL_FOLLOWLOCATION', 2048 );
define ( 'VURL_MAXREDIRS', 4096 );
define ( 'VURL_NOBODY', 8192 );
define ( 'VURL_CUSTOMREQUEST', 16384 );
define ( 'VURL_MAXSIZE', 32768 );
define ( 'VURL_DIEONMAXSIZE', 65536 );
define ( 'VURL_VALIDSSLONLY', 131072 );

define ( 'VURL_ERROR_MAXSIZE', 1 );
define ( 'VURL_ERROR_SSL', 2 );
define ( 'VURL_ERROR_URL', 4 );
define ( 'VURL_ERROR_NOLIB', 8 );

define ( 'VURL_HANDLED', 1 );
define ( 'VURL_NEXT', 2 );

/**
 * SKYUC 远程网址类
 *
 * 此类处理发送并通过 cURL 和 fsockopen 从远程 URL将数据返回
 *
 */
class SKYUC_vURL {
	/**
	 * 注册表对象
	 *
	 * @public	string
	 */
	public $registry = null;

	/**
	 * 错误代码
	 *
	 * @public	int
	 */
	public $error = 0;

	/**
	 * 选项位阈
	 *
	 * @public	integer
	 */
	public $bitoptions = 0;

	/**
	 * 按键名排序的头部列表
	 *
	 * @public	array
	 */
	public $headerkey = array ();

	/**
	 * 选项数组
	 *
	 * @public	array
	 */
	public $options = array ();

	/**
	 * 传送对象类名数组
	 *
	 * @public	array
	 */
	public $classnames = array ('cURL', 'fsockopen' );

	/**
	 * 传送对象端口数组
	 *
	 * @public	array
	 */
	public $transports = array ();

	/**
	 * 用于存储结果的临时文件名
	 *
	 * @public	string
	 */
	public $tmpfile = null;

	/**
	 * 将该类重置为初始设置
	 *
	 */
	function reset() {
		$this->bitoptions = 0;
		$this->headerkey = array ();
		$this->error = 0;

		$this->options = array (VURL_TIMEOUT => 60, VURL_POSTFIELDS => '', VURL_ENCODING => '', VURL_REFERER => '', VURL_URL => '', VURL_HTTPHEADER => array (), VURL_MAXREDIRS => 5, VURL_USERAGENT => 'SKYUC via PHP', VURL_DIEONMAXSIZE => 1 );

		foreach ( array_keys ( $this->transports ) as $tname ) {
			$transport = & $this->transports [$tname];
			$transport->reset ();
		}

	}

	/**
	 * 构造函数
	 *
	 * @param	object	注册表对象
	 */
	function __construct(&$registry) {
		if (is_object ( $registry )) {
			$this->registry = & $registry;
		} else {
			trigger_error ( 'SKYUC_vURL::Registry object is not an object', E_USER_ERROR );
		}

		// 判断 CURL 和 fsockopen
		if ($this->registry->options ['curlorfsockopen'] == 'curl') {
			$this->classnames = array ('cURL' );
		} elseif ($this->registry->options ['curlorfsockopen'] == 'fsockopen') {
			$this->classnames = array ('fsockopen' );
		}

		/*	if (!function_exists('curl_init'))
		{
			$this->classnames = array('fsockopen');
		}

		if (!function_exists('fsockopen') || !ini_get('allow_url_fopen'))
		{
			$this->classnames = array('cURL');
		}
*/
		// 创建我们需要的对象
		foreach ( $this->classnames as $classname ) {
			$fullclass = 'SKYUC_vURL_' . $classname;
			if (class_exists ( $fullclass )) {
				$this->transports ["$classname"] = new $fullclass ( $this );
			}
		}
		$this->reset ();
	}

	/**
	 * PHP 5 的析构函数，这处理忘了删除或移动临时文件。
	 */
	function __destruct() {
		if (file_exists ( $this->tmpfile )) {
			@unlink ( $this->tmpfile );
		}
	}

	/**
	 * On/Off 选项
	 *
	 * @param		integer	定义的 VURL_* 常量其中一个
	 * @param		mixed		选项设置
	 *
	 */
	function set_option($option, $extra) {
		switch ($option) {
			case VURL_POST :
			case VURL_HEADER :
			case VURL_NOBODY :
			case VURL_FOLLOWLOCATION :
			case VURL_RETURNTRANSFER :
			case VURL_CLOSECONNECTION :
			case VURL_VALIDSSLONLY :
				if ($extra == 1 or $extra == true) {
					$this->bitoptions = $this->bitoptions | $option;
				} else {
					$this->bitoptions = $this->bitoptions & ~ $option;
				}
				break;
			case VURL_TIMEOUT :
				if ($extra == 1 or $extra == true) {
					$this->options [VURL_TIMEOUT] = intval ( $extra );
				} else {
					$this->options [VURL_TIMEOUT] = 60;
				}
				break;
			case VURL_POSTFIELDS :
				if ($extra == 1 or $extra == true) {
					$this->options [VURL_POSTFIELDS] = $extra;
				} else {
					$this->options [VURL_POSTFIELDS] = '';
				}
				break;
			case VURL_ENCODING :
			case VURL_REFERER :
			case VURL_USERAGENT :
			case VURL_URL :
			case VURL_CUSTOMREQUEST :
				$this->options ["$option"] = $extra;
				break;
			case VURL_HTTPHEADER :
				if (is_array ( $extra )) {
					$this->headerkey = array ();
					$this->options [VURL_HTTPHEADER] = $extra;
					foreach ( $extra as $line ) {
						list ( $header, $value ) = explode ( ': ', $line, 2 );
						$this->headerkey [strtolower ( $header )] = $value;
					}
				} else {
					$this->options [VURL_HTTPHEADER] = array ();
					$this->headerkey = array ();
				}
				break;
			case VURL_MAXSIZE :
			case VURL_MAXREDIRS :
			case VURL_DIEONMAXSIZE :
				$this->options ["$option"] = intval ( $extra );
				break;
		}
	}

	/**
	 * 执行 vURL 所有功能
	 *
	 * @return	mixed		失败返回 false , 成功返回 数组或字符串
	 */
	function exec() {
		$result = $this->exec2 ();

		if (is_array ( $result )) {
			if (empty ( $result ['body'] ) and file_exists ( $result ['body_file'] )) {
				$result ['body'] = file_get_contents ( $result ['body_file'] );
				@unlink ( $result ['body_file'] );
			}
			if (! ($this->bitoptions & VURL_HEADER)) {
				return $result ['body'];
			}
		}

		return $result;
	}

	/**
	 * 格式化响应(返回)的数组, 移除不需要的
	 *
	 * @param	array		响应包含 headers 和 body / body_file
	 *
	 * @return	mixed		true 或 array 取决于请求的响应
	 */
	function format_response($response) {
		if ($this->bitoptions & VURL_RETURNTRANSFER) {
			if ($this->bitoptions & VURL_HEADER) {
				$headers = $this->build_headers ( $response ['headers'] );

				if ($this->bitoptions & VURL_NOBODY) {
					return $headers;
				} else {
					return $response;
				}
			} else if ($this->bitoptions & VURL_NOBODY) {
				@unlink ( $response ['body_file'] );
				return true;
			} else {
				unset ( $response ['headers'] );
				return $response;
			}
		} else {
			@unlink ( $response ['body_file'] );
			return true;
		}
	}

	/**
	 * 将项目存储在一个文件中，如果可以直到所需的新 vURL 方法
	 *
	 * @return	mixed		成功返回 true 或 array 取决于请求的响应，失败返回 false
	 */
	function exec2() {
		if ($this->registry->options ['safeupload']) {
			$this->tmpfile = @tempnam ( $this->registry->options ['tmppath'] . '/', 'skyucupload' );
		} else {
			$this->tmpfile = @tempnam ( ini_get ( 'upload_tmp_dir' ), 'skyucupload' );
		}

		if (empty ( $this->options [VURL_URL] )) {
			trigger_error ( 'Must set URL with set_option(VURL_URL, $url)', E_USER_ERROR );
		}
		if ($this->options [VURL_REFERER]) {
			$this->options [VURL_HTTPHEADER] [] = 'Referer: ' . $this->options [VURL_USERAGENT];
		}
		if ($this->options [VURL_USERAGENT]) {
			$this->options [VURL_HTTPHEADER] [] = 'User-Agent: ' . $this->options [VURL_USERAGENT];
		}
		if ($this->bitoptions & VURL_CLOSECONNECTION) {
			$this->options [VURL_HTTPHEADER] [] = 'Connection: close';
		}

		foreach ( array_keys ( $this->transports ) as $tname ) {
			$transport = & $this->transports [$tname];
			/*		if (PHP_VERSION < 5)
			{
				$transport->vurl =& $this;
			}*/
			if (($result = $transport->exec ()) === VURL_HANDLED and ! $this->fetch_error ()) {
				return $this->format_response ( array ('headers' => $transport->response_header, 'body' => $transport->response_text, 'body_file' => $this->tmpfile ) );
			}

			if ($this->fetch_error ()) {
				return false;
			}

		}

		@unlink ( $this->tmpfile );
		$this->set_error ( VURL_ERROR_NOLIB );
		return false;
	}

	/**
	 * 建立头部数组
	 *
	 * @param		string	使用 "\r\n" 分割的头部字符串
	 *
	 * @return	array
	 */
	function build_headers($data) {
		$returnedheaders = explode ( "\r\n", $data );
		$headers = array ();
		foreach ( $returnedheaders as $line ) {
			list ( $header, $value ) = explode ( ': ', $line, 2 );
			if (preg_match ( '#^http/(1\.[012]) ([12345]\d\d) (.*)#i', $header, $httpmatches )) {
				$headers ['http-response'] ['version'] = $httpmatches [1];
				$headers ['http-response'] ['statuscode'] = $httpmatches [2];
				$headers ['http-response'] ['statustext'] = $httpmatches [3];
			} else if (! empty ( $header )) {
				$headers [strtolower ( $header )] = $value;
			}
		}

		return $headers;
	}

	/**
	 * 设置错误
	 *
	 * @param	integer	错误代码
	 *
	 */
	function set_error($errorcode) {
		$this->error = $errorcode;
	}

	/**
	 * 返回错误
	 *
	 * @return	integer
	 */
	function fetch_error() {
		return $this->error;
	}

	/**
	 * 获取 HTTP HEAD 请求
	 *
	 * @param	string	做 HTTP 请求的 URL
	 *
	 * @return	mixed	失败返回 False  成功返回数组或字符串
	 *
	 */
	function fetch_head($url) {
		$this->reset ();
		$this->set_option ( VURL_URL, $url );
		$this->set_option ( VURL_RETURNTRANSFER, true );
		$this->set_option ( VURL_HEADER, true );
		$this->set_option ( VURL_NOBODY, true );
		$this->set_option ( VURL_CUSTOMREQUEST, 'HEAD' );
		$this->set_option ( VURL_CLOSECONNECTION, 1 );
		return $this->exec ();
	}

	/**
	 * 做一个 HTTP 请求, 返回文档的正文
	 *
	 * @param	string	做 HTTP 请求的 URL
	 * @param	integer	获取最大大小
	 * @param	boolean	当我们达到最大的大小而停止吗？
	 * @param	boolean	并且获取标头?
	 *
	 * @return	mixed	失败返回 False , 成功返回数组字符串
	 *
	 */
	function fetch_body($url, $maxsize, $dieonmaxsize, $returnheaders) {
		$this->reset ();
		$this->set_option ( VURL_URL, $url );
		$this->set_option ( VURL_RETURNTRANSFER, true );
		$urlinfo = @parse_url ( $url );
		$this->set_option ( VURL_REFERER, $urlinfo ['scheme'] . '://' . $urlinfo ['host'] );
		if (intval ( $maxsize )) {
			$this->set_option ( VURL_MAXSIZE, $maxsize );
		}
		if ($returnheaders) {
			$this->set_option ( VURL_HEADER, true );
		}
		if (! $dieonmaxsize) {
			$this->set_option ( VURL_DIEONMAXSIZE, false );
		}
		return $this->exec ();
	}
}

class SKYUC_vURL_cURL {
	/**
	 * 持有 cURL 回调数据的字符串
	 *
	 * @public	string
	 */
	public $response_text = '';

	/**
	 * 持有 cURL 回调数据的字符串：头部
	 *
	 * @public	string
	 */
	public $response_header = '';

	/**
	 * cURL 句柄
	 *
	 * @public	esource
	 */
	public $ch = null;

	/**
	 * SKYUC_vURL 对象
	 *
	 * @public	object
	 */
	public $vurl = null;

	/**
	 * 到临时文件的文件指针
	 *
	 * @public	resource
	 */
	public $fp = null;

	/**
	 * 当前的响应的长度
	 *
	 * @public	integer
	 */
	public $response_length = 0;

	/**
	 * 当我们请求头部的私有变量
	 *
	 * @private	boolean
	 */
	private $__finished_headers = false;

	/**
	 * 如果当前结果达到最大的限制
	 *
	 * @public	integer
	 */
	public $max_limit_reached = false;

	/**
	 * 构造函数
	 *
	 * @param	object	引用 SKYUC_vURL 对象
	 */
	function __construct(&$vurl_registry) {
		if ($vurl_registry instanceof SKYUC_vURL) {
			$this->vurl = & $vurl_registry;
		} else {
			trigger_error ( 'Direct Instantiation of ' . __CLASS__ . ' prohibited.', E_USER_ERROR );
		}

	}

	/**
	 * 处理头部的回调函数
	 *
	 * @param	resource	cURL 对象
	 * @param	string		请求
	 *
	 * @return	integer		请求长度
	 */
	function curl_callback_header(&$ch, $string) {
		if (trim ( $string ) !== '') {
			$this->response_header .= $string;
		}
		return strlen ( $string );
	}

	/**
	 * 处理请求正文的回调函数
	 *
	 * @param	resource	cURL 对象
	 * @param	string		请求
	 *
	 * @return	integer		请求长度
	 */
	function curl_callback_response(&$ch, $response) {
		$chunk_length = strlen ( $response );

		/* 我们接受 headers + body */
		if ($this->vurl->bitoptions & VURL_HEADER) {
			if (! $this->__finished_headers) {
				if ($response === "\r\n") {
					$this->__finished_headers = true;
				}
				return $chunk_length;
			}
		}

		// 没有我们正在使用的文件指针，并且即将使用超过 100k
		if (! $this->fp and $this->response_length + $chunk_length >= 1024 * 100) {
			if ($this->fp = @fopen ( $this->vurl->tmpfile, 'wb' )) {
				fwrite ( $this->fp, $this->response_text );
				unset ( $this->response_text );
			}
		}

		if ($this->fp and $response) {
			fwrite ( $this->fp, $response );
		} else {
			$this->response_text .= $response;

		}

		$this->response_length += $chunk_length;

		if ($this->vurl->options [VURL_MAXSIZE] and $this->response_length > $this->vurl->options [VURL_MAXSIZE]) {
			$this->max_limit_reached = true;
			$this->vurl->set_error ( VURL_ERROR_MAXSIZE );
			return false;
		}

		return $chunk_length;
	}

	/**
	 * 清除所有以前的请求信息
	 */
	function reset() {
		$this->response_text = '';
		$this->response_header = '';
		$this->response_length = 0;
		$this->__finished_headers = false;
		$this->max_limit_reached = false;
	}

	/**
	 * 如果可能执行提取文件动作
	 *
	 * @return	integer		返回两个常量之一, VURL_NEXT 或 VURL_HANDLED
	 */
	function exec() {
		$urlinfo = @parse_url ( $this->vurl->options [VURL_URL] );
		if (empty ( $urlinfo ['port'] )) {
			if ($urlinfo ['scheme'] == 'https') {
				$urlinfo ['port'] = 443;
			} else {
				$urlinfo ['port'] = 80;
			}
		}

		if (! function_exists ( 'curl_init' ) or ($this->ch = curl_init ()) === false) {
			return VURL_NEXT;
		}

		if ($urlinfo ['scheme'] == 'https') {
			// curl_version crashes if no zlib support in cURL (php <= 5.2.5)
			$curlinfo = curl_version ();
			if (empty ( $curlinfo ['ssl_version'] )) {
				curl_close ( $this->ch );
				return VURL_NEXT;
			}
		}

		curl_setopt ( $this->ch, CURLOPT_URL, $this->vurl->options [VURL_URL] );
		curl_setopt ( $this->ch, CURLOPT_TIMEOUT, $this->vurl->options [VURL_TIMEOUT] );
		if ($this->vurl->options [VURL_CUSTOMREQUEST]) {
			curl_setopt ( $this->ch, CURLOPT_CUSTOMREQUEST, $this->vurl->options [VURL_CUSTOMREQUEST] );
		} else if ($this->vurl->bitoptions & VURL_POST) {
			curl_setopt ( $this->ch, CURLOPT_POST, 1 );
			curl_setopt ( $this->ch, CURLOPT_POSTFIELDS, $this->vurl->options [VURL_POSTFIELDS] );
		} else {
			curl_setopt ( $this->ch, CURLOPT_POST, 0 );
		}
		curl_setopt ( $this->ch, CURLOPT_HEADER, ($this->vurl->bitoptions & VURL_HEADER) ? 1 : 0 );
		curl_setopt ( $this->ch, CURLOPT_HTTPHEADER, $this->vurl->options [VURL_HTTPHEADER] );
		curl_setopt ( $this->ch, CURLOPT_RETURNTRANSFER, ($this->vurl->bitoptions & VURL_RETURNTRANSFER) ? 1 : 0 );
		if ($this->vurl->bitoptions & VURL_NOBODY) {
			curl_setopt ( $this->ch, CURLOPT_NOBODY, 1 );
		}

		if ($this->vurl->bitoptions & VURL_FOLLOWLOCATION) {
			if (@curl_setopt ( $this->ch, CURLOPT_FOLLOWLOCATION, 1 ) === false) // disabled in safe_mode/open_basedir in PHP 5.1.6/4.4.4
{
				curl_close ( $this->ch );
				return VURL_NEXT;
			}
			curl_setopt ( $this->ch, CURLOPT_MAXREDIRS, $this->vurl->options [VURL_MAXREDIRS] );
		} else {
			curl_setopt ( $this->ch, CURLOPT_FOLLOWLOCATION, 0 );
		}

		if ($this->vurl->options [VURL_ENCODING]) {
			@curl_setopt ( $this->ch, CURLOPT_ENCODING, $this->vurl->options [VURL_ENCODING] ); // this will work on versions of cURL after 7.10, though was broken on PHP 4.3.6/Win32
		}

		$this->response_text = '';
		$this->response_header = '';

		curl_setopt ( $this->ch, CURLOPT_WRITEFUNCTION, array (&$this, 'curl_callback_response' ) );
		curl_setopt ( $this->ch, CURLOPT_HEADERFUNCTION, array (&$this, 'curl_callback_header' ) );

		if (! ($this->vurl->bitoptions & VURL_VALIDSSLONLY)) {
			curl_setopt ( $this->ch, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt ( $this->ch, CURLOPT_SSL_VERIFYHOST, 0 );
		}

		$result = curl_exec ( $this->ch );

		if ($urlinfo ['scheme'] == 'https' and $result === false and curl_errno ( $this->ch ) == '60') ## CURLE_SSL_CACERT problem with the CA cert (path? access rights?)
{
			curl_setopt ( $this->ch, CURLOPT_CAINFO, DIR . '/includes/data/ca-bundle.crt' );
			$result = curl_exec ( $this->ch );
		}

		curl_close ( $this->ch );
		if ($this->fp) {
			fclose ( $this->fp );
			$this->fp = null;
		}

		if ($result !== false or (! $this->vurl->options [VURL_DIEONMAXSIZE] and $this->max_limit_reached)) {
			return VURL_HANDLED;
		}
		return VURL_NEXT;
	}
}

class SKYUC_vURL_fsockopen {
	/**
	 * 持有 cURL 回调数据的字符串
	 *
	 * @public	string
	 */
	public $response_text = '';

	/**
	 * 持有 cURL 回调数据的字符串：头部
	 *
	 * @public	string
	 */
	public $response_header = '';

	/**
	 * SKYUC_vURL 对象
	 *
	 * @public	object
	 */
	public $vurl = null;

	/**
	 * 到临时文件的文件指针
	 *
	 * @public	resource
	 */
	public $fp = null;

	/**
	 * 当前的响应的长度
	 *
	 * @public	integer
	 */
	public $response_length = 0;

	/**
	 * 如果当前结果达到最大的限制
	 *
	 * @public	integer
	 */
	public $max_limit_reached = false;

	/**
	 * 构造函数
	 *
	 * @param	object	引用一个 SKYUC_vURL 对象
	 */
	function __construct(&$vurl_registry) {
		if ($vurl_registry instanceof SKYUC_vURL) {
			$this->vurl = & $vurl_registry;
		} else {
			trigger_error ( 'Direct Instantiation of ' . __CLASS__ . ' prohibited.', E_USER_ERROR );
		}

	}

	/**
	 * 清除所有以前的请求信息
	 */
	function reset() {
		$this->response_text = '';
		$this->response_header = '';
		$this->response_length = 0;
		$this->max_limit_reached = false;
	}

	/**
	 * 解压响应正文, 如果它是  gzip 或 deflate
	 */
	function inflate_response($type) {
		if (! empty ( $this->response_text )) {
			switch ($type) {
				case 'gzip' :
					if ($this->response_text [0] == "\x1F" and $this->response_text [1] == "\x8b") {
						if ($inflated = @gzinflate ( substr ( $this->response_text, 10 ) )) {
							$this->response_text = $inflated;
						}
					}
					break;
				case 'deflate' :

					if ($this->response_text [0] == "\x78" and $this->response_text [1] == "\x9C" and $inflated = @gzinflate ( substr ( $this->response_text, 2 ) )) {
						$this->response_text = $inflated;
					} else if ($inflated = @gzinflate ( $this->response_text )) {
						$this->response_text = $inflated;
					}
					break;
			}
		} else {
			$compressed_file = $this->vurl->tmpfile;
			if ($gzfp = @gzopen ( $compressed_file, 'r' )) {
				if ($newfp = @fopen ( $this->vurl->tmpfile . 'u', 'w' )) {
					$this->vurl->tmpfile = $this->vurl->tmpfile . 'u';
					if (function_exists ( 'stream_copy_to_stream' )) {
						stream_copy_to_stream ( $gzfp, $newfp );
					} else {
						while ( ! gzeof ( $gzfp ) ) {
							fwrite ( $fp, gzread ( $gzfp, 20480 ) );
						}
					}

					fclose ( $newfp );
				}

				fclose ( $gzfp );
				@unlink ( $compressed_file );
			}
		}
	}

	/**
	 * 处理请求正文的回调函数
	 *
	 * @param	string		请求
	 *
	 * @return	integer		请求长度
	 */
	function callback_response($response) {
		$chunk_length = strlen ( $response );

		// 没有我们正在使用的文件指针，并且即将使用超过 100k
		if (! $this->fp and $this->response_length + $chunk_length >= 1024 * 100) {
			if ($this->fp = @fopen ( $this->vurl->tmpfile, 'wb' )) {
				fwrite ( $this->fp, $this->response_text );
				unset ( $this->response_text );
			}
		}

		if ($response) {
			if ($this->fp) {
				fwrite ( $this->fp, $response );
			} else {
				$this->response_text .= $response;

			}
		}

		$this->response_length += $chunk_length;

		if ($this->vurl->options [VURL_MAXSIZE] and $this->response_length > $this->vurl->options [VURL_MAXSIZE]) {
			$this->max_limit_reached = true;
			$this->vurl->set_error ( VURL_ERROR_MAXSIZE );
			return false;
		}

		return $chunk_length;
	}

	/**
	 * 如果可能执行提取文件动作
	 *
	 * @return	integer		返回两个常量之一, VURL_NEXT 或 VURL_HANDLED
	 */
	function exec() {
		static $location_following_count = 0;

		$urlinfo = @parse_url ( $this->vurl->options [VURL_URL] );
		if (empty ( $urlinfo ['port'] )) {
			if ($urlinfo ['scheme'] == 'https') {
				$urlinfo ['port'] = 443;
			} else {
				$urlinfo ['port'] = 80;
			}
		}

		if (empty ( $urlinfo ['path'] )) {
			$urlinfo ['path'] = '/';
		}

		if ($urlinfo ['scheme'] == 'https') {
			if (! function_exists ( 'openssl_open' )) {
				$this->vurl->set_error ( VURL_ERROR_SSL );
				return VURL_NEXT;
			}
			$scheme = 'ssl://';
		}

		if ($request_resource = @fsockopen ( $scheme . $urlinfo ['host'], $urlinfo ['port'], $errno, $errstr, $this->vurl->options [VURL_TIMEOUT] )) {
			$headers = array ();
			if ($this->vurl->bitoptions & VURL_NOBODY) {
				$this->vurl->options [VURL_CUSTOMREQUEST] = 'HEAD';
			}
			if ($this->vurl->options [VURL_CUSTOMREQUEST]) {
				$headers [] = $this->vurl->options [VURL_CUSTOMREQUEST] . " $urlinfo[path]" . ($urlinfo ['query'] ? "?$urlinfo[query]" : '') . " HTTP/1.0";
			} else if ($this->vurl->bitoptions & VURL_POST) {
				$headers [] = "POST $urlinfo[path]" . ($urlinfo ['query'] ? "?$urlinfo[query]" : '') . " HTTP/1.0";
				if (empty ( $this->vurl->headerkey ['content-type'] )) {
					$headers [] = 'Content-Type: application/x-www-form-urlencoded';
				}
				if (empty ( $this->vurl->headerkey ['content-length'] )) {
					$headers [] = 'Content-Length: ' . strlen ( $this->vurl->options [VURL_POSTFIELDS] );
				}
			} else {
				$headers [] = "GET $urlinfo[path]" . ($urlinfo ['query'] ? "?$urlinfo[query]" : '') . " HTTP/1.0";
			}
			$headers [] = "Host: $urlinfo[host]";
			if (! empty ( $this->vurl->options [VURL_HTTPHEADER] )) {
				$headers = array_merge ( $headers, $this->vurl->options [VURL_HTTPHEADER] );
			}
			if ($this->vurl->options [VURL_ENCODING]) {
				$encodemethods = explode ( ',', $this->vurl->options [VURL_ENCODING] );
				$finalmethods = array ();
				foreach ( $encodemethods as $type ) {
					$type = strtolower ( trim ( $type ) );
					if ($type == 'gzip' and function_exists ( 'gzinflate' )) {
						$finalmethods [] = 'gzip';
					} else if ($type == 'deflate' and function_exists ( 'gzinflate' )) {
						$finalmethods [] = 'deflate';
					} else {
						$finalmethods [] = $type;
					}
				}

				if (! empty ( $finalmethods )) {
					$headers [] = "Accept-Encoding: " . implode ( ', ', $finalmethods );
				}
			}

			$output = implode ( "\r\n", $headers ) . "\r\n\r\n";
			if ($this->vurl->bitoptions & VURL_POST) {
				$output .= $this->vurl->options [VURL_POSTFIELDS];
			}

			$result = false;

			if (fputs ( $request_resource, $output, strlen ( $output ) )) {
				stream_set_timeout ( $request_resource, $this->vurl->options [VURL_TIMEOUT] );
				$in_header = true;
				$result = true;

				while ( ! feof ( $request_resource ) ) {
					$response = @fread ( $request_resource, 2048 );

					if ($in_header) {
						$header_end_position = strpos ( $response, "\r\n\r\n" );

						if ($header_end_position === false) {
							$this->response_header .= $response;
						} else {
							$this->response_header .= substr ( $response, 0, $header_end_position );
							$in_header = false;
							$response = substr ( $response, $header_end_position + 4 );
						}
					}

					if ($this->callback_response ( $response ) != strlen ( $response )) {
						$result = false;
						break;
					}
				}
				fclose ( $request_resource );
			}

			if ($this->fp) {
				fclose ( $this->fp );
				$this->fp = null;
			}

			if ($result !== false or (! $this->vurl->options [VURL_DIEONMAXSIZE] and $this->max_limit_reached)) {
				if ($this->vurl->bitoptions & VURL_FOLLOWLOCATION and preg_match ( "#\r\nLocation: (.*)\r\n#siU", $this->response_header, $location ) and $location_following_count < $this->vurl->options [VURL_MAXREDIRS]) {
					$location_following_count ++;
					$this->vurl->set_option ( VURL_URL, trim ( $location [1] ) );
					$this->reset ();
					return $this->exec ();
				}

				// 需要处理 gzip，如果它使用
				if (function_exists ( 'gzinflate' )) {
					if (stristr ( $this->response_header, "Content-encoding: gzip\r\n" ) !== false) {
						$this->inflate_response ( 'gzip' );
					} else if (stristr ( $this->response_header, "Content-encoding: deflate\r\n" ) !== false) {
						$this->inflate_response ( 'deflate' );
					}
				}

				return VURL_HANDLED;
			}
		}
		return VURL_NEXT;
	}

}

?>