<?php
/**
 * SKYUC! XML库
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */
if (! defined ( 'SKYUC_AREA' )) {
	echo 'SKYUC_AREA must be defined to continue';
	exit ();
}

error_reporting ( E_ALL & ~ E_NOTICE );

// 尝试加载XML扩展，如果我们没有已经加载的XML函数。
if (! function_exists ( 'xml_set_element_handler' )) {
	$extension_dir = ini_get ( 'extension_dir' );
	if (DIRECTORY_SEPARATOR == '\\') {
		$extension_file = 'php_xml.dll';
	} else {
		$extension_file = 'xml.so';
	}
	if ($extension_dir and file_exists ( $extension_dir . '/' . $extension_file )) {
		ini_set ( 'display_errors', true );
		dl ( $extension_file );
	}
}

if (! function_exists ( 'ini_size_to_bytes' ) or (($current_memory_limit = ini_size_to_bytes ( @ini_get ( 'memory_limit' ) )) < 128 * 1024 * 1024 and $current_memory_limit > 0)) {
	@ini_set ( 'memory_limit', 128 * 1048576 ); //128MB
}

/**
 * XML解析对象
 *
 * 这个类允许解析XML文档到一个数组
 *
 *
 */
class XML_Parser {
	/**
	 * 内部PHP的XML解析器
	 *
	 * @public	resource
	 */
	public $xml_parser;

	/**
	 * 错误号（0为没有错误）
	 *
	 * @public	integer
	 */
	public $error_no = 0;

	/**
	 * 正在处理中实际的XML数据
	 *
	 * @public	integer
	 */
	public $xmldata = '';

	/**
	 * 最后输出的数据
	 *
	 * @public	array
	 */
	public $parseddata = array ();

	/**
	 * 解析时使用的中间堆栈。
	 *
	 * @public	array
	 */
	public $stack = array ();

	/**
	 * 正在分析当前 CData
	 *
	 * @public	string
	 */
	public $cdata = '';

	/**
	 * 当前打开的标签数
	 *
	 * @public	integer
	 */
	public $tag_count = 0;

	/**
	 * 笨拙地 包含顶级元素， 因为此解析器不能返回它。现在所有的XML函数都假定不存在。
	 *
	 * @public	boolean
	 */
	public $include_first_tag = false;

	/**
	 * 从之前的 XML 对象的错误代码释放资源。
	 *
	 * @public integer
	 */
	public $error_code = 0;

	/**
	 * 从之前的 XML 对象的错误行号释放资源。
	 *
	 * @public integer
	 */
	public $error_line = 0;

	/**
	 * 构造函数
	 *
	 * @param	mixed	XML 数据或布尔值 false
	 * @param	string	要分析的 XML 文件的路径
	 */
	function __construct($xml, $path = '') {
		if ($xml !== false) {
			$this->xmldata = $xml;
		} else {
			if (empty ( $path )) {
				$this->error_no = 1;
			} else if (! ($this->xmldata = @file_get_contents ( $path ))) {
				$this->error_no = 2;
			}
		}
	}

	/**
	 * 解析XML文档到一个数组
	 *
	 * @param	string	输入的 XML 文件的编码
	 * @param	bool		清空解析后的 XML 数据
	 *
	 * @return	mixed	array or false on error
	 */
	function &parse($encoding = 'ISO-8859-1', $emptydata = true) {
		if (empty ( $this->xmldata ) or $this->error_no > 0) {
			$this->error_code = XML_ERROR_NO_ELEMENTS + (PHP_VERSION > '5.2.8' ? 0 : 1);
			return false;
		}

		if (! ($this->xml_parser = xml_parser_create ( $encoding ))) {
			return false;
		}

		xml_parser_set_option ( $this->xml_parser, XML_OPTION_SKIP_WHITE, 0 );
		xml_parser_set_option ( $this->xml_parser, XML_OPTION_CASE_FOLDING, 0 );
		xml_set_character_data_handler ( $this->xml_parser, array (&$this, 'handle_cdata' ) );
		xml_set_element_handler ( $this->xml_parser, array (&$this, 'handle_element_start' ), array (&$this, 'handle_element_end' ) );

		xml_parse ( $this->xml_parser, $this->xmldata, true );
		$err = xml_get_error_code ( $this->xml_parser );

		if ($emptydata) {
			$this->xmldata = '';
			$this->stack = array ();
			$this->cdata = '';
		}

		if ($err) {
			$this->error_code = @xml_get_error_code ( $this->xml_parser );
			$this->error_line = @xml_get_current_line_number ( $this->xml_parser );
			xml_parser_free ( $this->xml_parser );
			return false;
		}

		xml_parser_free ( $this->xml_parser );

		return $this->parseddata;
	}

	/**
	 * 处理编码问题，以及 XML 解析到一个数组
	 *
	 * @return	boolean	Success
	 */
	function parse_xml() {
		// 在这里，我们应该做从输入到输出的转换。
		if (preg_match ( '#(<?xml.*encoding=[\'"])(.*?)([\'"].*?>)#m', $this->xmldata, $match )) {
			$in_encoding = strtoupper ( $match [2] );
			if ($in_encoding == 'ISO-8859-1') {
				// 浏览器处理像这样的编码, 所以我们要用 iconv 做好
				$in_encoding = 'WINDOWS-1252';
			}

			if ($in_encoding != 'UTF-8') {
				// 当在 PHP5 中尝试输出一个不支持编码,这是有必要的
				$this->xmldata = str_replace ( $match [0], "$match[1]ISO-8859-1$match[3]", $this->xmldata );
			}
		} else {
			$in_encoding = 'UTF-8';

			if (strpos ( $this->xmldata, '<?xml' ) === false) {
				// 这是必要的，如果没有标记，因为在 PHP5 中不知道是什么字符集，因此指定字符集。
				$this->xmldata = '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n" . $this->xmldata;
			} else {
				// XML标记没有一个编码，这是不好的
				$this->xmldata = preg_replace ( '#(<?xml.*)(\?>)#', '\\1 encoding="ISO-8859-1" \\2', $this->xmldata );
			}

			$in_encoding = 'ISO-8859-1';
		}

		$orig_string = $this->xmldata;

		$target_encoding = 'UTF-8';
		$xml_encoding = 'UTF-8';
		$iconv_passed = false;

		if (strtoupper ( $in_encoding ) !== strtoupper ( $target_encoding )) {
			// 现在我们需要处理这些未知的字符集 ！
			if (function_exists ( 'iconv' ) and $encoded_data = iconv ( $in_encoding, $target_encoding . '//TRANSLIT', $this->xmldata )) {
				$iconv_passed = true;
				$this->xmldata = & $encoded_data;
			}

			if (! $iconv_passed and function_exists ( 'mb_convert_encoding' ) and $encoded_data = @mb_convert_encoding ( $this->xmldata, $target_encoding, $in_encoding )) {
				$this->xmldata = & $encoded_data;
			}
		}

		if ($this->parse ( $xml_encoding )) {
			return true;
		} else if ($iconv_passed and $this->xmldata = iconv ( $in_encoding, $target_encoding . '//IGNORE', $orig_string )) {
			// 因为 iconv 出于某种原因可能会中断了字符串，不过，当出现故障时 //TRANSLIT，//IGNORE 仍可以工作。
			if ($this->parse ( $xml_encoding )) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * XML解析器回调。处理CDATA值。
	 *
	 * @param	resource	Parser that called this
	 * @param	string		The CDATA
	 */
	function handle_cdata(&$parser, $data) {
		$this->cdata .= $data;
	}

	/**
	 * XML解析器回调。处理打开标签。
	 *
	 * @param	resource	Parser that called this
	 * @param	string		The name of the tag opened
	 * @param	array		The tag's attributes
	 */
	function handle_element_start(&$parser, $name, $attribs) {
		$this->cdata = '';

		foreach ( $attribs as $key => $val ) {
			if (preg_match ( '#&[a-z]+;#i', $val )) {
				$attribs ["$key"] = unhtmlspecialchars ( $val );
			}
		}

		array_unshift ( $this->stack, array ('name' => $name, 'attribs' => $attribs, 'tag_count' => ++ $this->tag_count ) );
	}

	/**
	 * XML解析器回调。处理关闭标签。
	 *
	 * @param	resource	Parser that called this
	 * @param	string		The name of the tag closed
	 */
	function handle_element_end(&$parser, $name) {
		$tag = array_shift ( $this->stack );
		if ($tag ['name'] != $name) {
			//没有理由应该发生的事 - 这一定意味着无效的XML
			return;
		}

		$output = $tag ['attribs'];

		if (trim ( $this->cdata ) !== '' or $tag ['tag_count'] == $this->tag_count) {
			if (sizeof ( $output ) == 0) {
				$output = $this->unescape_cdata ( $this->cdata );
			} else {
				$this->add_node ( $output, 'value', $this->unescape_cdata ( $this->cdata ) );
			}
		}

		if (isset ( $this->stack [0] )) {
			$this->add_node ( $this->stack [0] ['attribs'], $name, $output );
		} else {
			// 弹出的第一个元素，这应该完成分析
			if ($this->include_first_tag) {
				$this->parseddata = array ($name => $output );
			} else {
				$this->parseddata = $output;
			}
		}

		$this->cdata = '';
	}

	/**
	 * 返回解析器的错误字符串
	 *
	 * @return	mixed error message
	 */
	function error_string() {
		if ($errorstring = @xml_error_string ( $this->error_code () )) {
			return $errorstring;
		} else {
			return 'unknown';
		}
	}

	/**
	 * 返回解析器错误的行号
	 *
	 * @return	int error line number
	 */
	function error_line() {
		if ($this->error_line) {
			return $this->error_line;
		} else {
			return 0;
		}
	}

	/**
	 * 返回解析器错误代码
	 *
	 * @return	int error line code
	 */
	function error_code() {
		if ($this->error_code) {
			return $this->error_code;
		} else {
			return 0;
		}
	}

	/**
	 * 当唯一条目时添加适当的逻辑节点，多个值可以添加到数组中。
	 *
	 * @param	array		引用被添加到的数组节点
	 * @param	string	Name of node
	 * @param	string	Value of node
	 *
	 */
	function add_node(&$children, $name, $value) {
		if (! is_array ( $children ) or ! in_array ( $name, array_keys ( $children ) )) { // 不是数组或目前无法设置
			$children [$name] = $value;
		} else if (is_array ( $children [$name] ) and isset ( $children [$name] [0] )) { // 存在相同的标签，且是一个数组
			$children [$name] [] = $value;
		} else { // 存在相同的标签，但它还不是一个数组
			$children [$name] = array ($children [$name] );
			$children [$name] [] = $value;
		}
	}

	/**
	 * 转义CDATA
	 *
	 * @param	string	XML to have any of our custom CDATAs to be made into CDATA
	 *
	 */
	function unescape_cdata($xml) {
		static $find, $replace;

		if (! is_array ( $find )) {
			$find = array ('�![CDATA[', ']]�', "\r\n", "\n" );
			$replace = array ('<![CDATA[', ']]>', "\n", "\r\n" );
		}

		return str_replace ( $find, $replace, $xml );
	}
}

// #############################################################################
// xml builder


class XML_Builder {
	public $registry = null;
	public $charset = 'windows-1252';
	public $content_type = 'text/xml';
	public $open_tags = array ();
	public $tabs = "";

	function __construct(&$registry, $content_type = null, $charset = 'utf-8') {
		if (is_object ( $registry )) {
			$this->registry = & $registry;
		} else {
			trigger_error ( "XML_Builder::Registry object is not an object", E_USER_ERROR );
		}

		if ($content_type) {
			$this->content_type = $content_type;
		}

		$this->charset = (strtolower ( $charset ) == 'iso-8859-1') ? 'windows-1252' : $charset;
	}

	/**
	 * 读取内容类型标头 $this->content_type
	 */
	function fetch_content_type_header() {
		return 'Content-Type: ' . $this->content_type . ($this->charset == '' ? '' : '; charset=' . $this->charset);
	}

	/**
	 * 读取内容长度标头
	 */
	function fetch_content_length_header() {
		return 'Content-Length: ' . $this->fetch_xml_content_length ();
	}

	/**
	 * 发送内容类型标头 $this->content_type
	 */
	function send_content_type_header() {
		@header ( 'Content-Type: ' . $this->content_type . ($this->charset == '' ? '' : '; charset=' . $this->charset) );
	}

	/**
	 * 发送内容长度标头
	 */
	function send_content_length_header() {
		@header ( 'Content-Length: ' . $this->fetch_xml_content_length () );
	}

	/**
	 * Returns the <?xml tag complete with $this->charset character set defined
	 *
	 * @return	string	<?xml tag
	 */
	function fetch_xml_tag() {
		return '<?xml version="1.0" encoding="' . $this->charset . '"?>' . "\n";
	}

	/**
	 *
	 * @return	integer	Length of document
	 */
	function fetch_xml_content_length() {
		return strlen ( $this->doc ) + strlen ( $this->fetch_xml_tag () );
	}

	function add_group($tag, $attr = array()) {
		$this->open_tags [] = $tag;
		$this->doc .= $this->tabs . $this->build_tag ( $tag, $attr ) . "\n";
		$this->tabs .= "\t";
	}

	function close_group() {
		$tag = array_pop ( $this->open_tags );
		$this->tabs = substr ( $this->tabs, 0, - 1 );
		$this->doc .= $this->tabs . "</$tag>\n";
	}

	function add_tag($tag, $content = '', $attr = array(), $cdata = false, $htmlspecialchars = false) {
		$this->doc .= $this->tabs . $this->build_tag ( $tag, $attr, ($content === '') );
		if ($content !== '') {
			if ($htmlspecialchars) {
				$this->doc .= htmlspecialchars_uni ( $content );
			} else if ($cdata or preg_match ( '/[\<\>\&\'\"\[\]]/', $content )) {
				$this->doc .= '<![CDATA[' . $this->escape_cdata ( $content ) . ']]>';
			} else {
				$this->doc .= $content;
			}
			$this->doc .= "</$tag>\n";
		}
	}

	function build_tag($tag, $attr, $closing = false) {
		$tmp = "<$tag";
		if (! empty ( $attr )) {
			foreach ( $attr as $attr_name => $attr_key ) {
				if (strpos ( $attr_key, '"' ) !== false) {
					$attr_key = htmlspecialchars_uni ( $attr_key );
				}
				$tmp .= " $attr_name=\"$attr_key\"";
			}
		}
		$tmp .= ($closing ? " />\n" : '>');
		return $tmp;
	}

	function escape_cdata($xml) {
		// 带无效字符的 XML 1.0： 00-08，11-12 和 14-31
		// 找不到任何使用这些字符的字符集。
		$xml = preg_replace ( '#[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]#', '', $xml );

		return str_replace ( array ('<![CDATA[', ']]>' ), array ('�![CDATA[', ']]�' ), $xml );
	}

	function output() {
		if (! empty ( $this->open_tags )) {
			trigger_error ( "There are still open tags within the document", E_USER_ERROR );
			return false;
		}

		return $this->doc;
	}

	/**
	 * 打印出排队的XML，然后退出。
	 *
	 * @param	boolean	是否要完全关闭（会话的更新等），如果不使用关机功能只关闭数据库
	 */
	function print_xml($full_shutdown = false) {
		global $skyuc;

		//运行所有注册的关闭功能
		//$skyuc->shutdown->shutdown();
		if (defined ( 'NOSHUTDOWNFUNC' )) {
			if ($full_shutdown) {
				exec_shut_down ();
			} else {
				$this->registry->db->close ();
			}
		}

		$this->send_content_type_header ();

		if (strpos ( $_SERVER ['SERVER_SOFTWARE'], 'Microsoft-IIS' ) !== false) {
			//此行导致 mod_gzip/deflate 问题，但某些 IIS 设置所需要
			$this->send_content_length_header ();
		}

		echo $this->fetch_xml ();
		exit ();
	}

	/**
	 * 提取排队的XML
	 *
	 * @return string
	 */
	function fetch_xml() {
		return $this->fetch_xml_tag () . $this->output ();
	}
}

// #############################################################################


class AJAX_XML_Builder extends XML_Builder {
	function escape_cdata($xml) {
		$xml = preg_replace ( '#[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]#', '', $xml );

		return str_replace ( array ('<![CDATA[', ']]>' ), array ('<=!=[=C=D=A=T=A=[', ']=]=>' ), $xml );
	}
}



