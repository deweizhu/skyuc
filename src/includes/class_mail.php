<?php
/**
 * SKYUC!  邮件发送类
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

define ( 'MAIL_INCLUDED', true );

// 试图加载 XML 扩展， 如果我们没有已加载的 XML 函数。它需要 utf8_encode()
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

/**
 * 标准邮件发送对象
 *
 * 此类从 SKYUC 使用 PHP mail () 函数发送电子邮件
 *
 *
 */
class Mail {
	/**
	 * 收件人电子邮件
	 *
	 * @protected	string
	 */
	protected $toemail = '';

	/**
	 * 主题
	 *
	 * @protected	string
	 */
	protected $subject = '';

	/**
	 * 信息
	 *
	 * @protected	string
	 */
	protected $message = '';

	/**
	 * 该邮件发送所有标头
	 *
	 * @protected	string
	 */
	protected $headers = '';

	/**
	 * 发件人电子邮件
	 *
	 * @protected	string
	 */
	protected $fromemail = '';

	/**
	 * 行分隔符
	 *
	 * @protected	string
	 */
	protected $delimiter = "\r\n";

	/**
	 * 我们需要的任何选项的注册表对象
	 *
	 * @protected	Registry
	 */
	protected $registry = null;

	/**
	 * 切换到启用/禁用调试。当启用时，警告没有抑制
	 *
	 * @protected	boolean
	 */
	protected $debug = false;

	/**
	 * 信息记录，如果已启用日志
	 *
	 * @protected	string
	 */
	protected $log = '';

	/**
	 * 构造函数
	 *
	 * @param	Registry	SKYUC 注册表对象
	 */
	function __construct(&$registry) {
		if (is_object ( $registry )) {
			$this->registry = & $registry;
		} else {
			trigger_error ( 'Registry object is not an object', E_USER_ERROR );
		}

		$sendmail_path = @ini_get ( 'sendmail_path' );
		if (! $sendmail_path or $this->registry->options ['use_smtp'] or defined ( 'FORCE_MAIL_CRLF' )) {
			// 没有 sendmail或启用use_smtp, 发送邮件使用 windows 回车换行符。
			//  use_smtp 部分 是为 MailQueue 扩展
			$this->delimiter = "\r\n";
		} else {
			$this->delimiter = "\n";
		}
	}

	/**
	 * 启动发送一封电子邮件的进程 - 为发送邮件作充分准备
	 * 实际上调用 send() 发送
	 *
	 * @param	string	收件人电子邮件地址
	 * @param	string	邮件主题
	 * @param	string	邮件正文
	 * @param	int	 		0 普通邮件， 1 HTML邮件发送
	 * @param	string	可选 名称或电子邮件，在 'From' 标头中使用
	 * @param	string	用户自定义附加标头
	 * @param	string	发件人用户名
	 *
	 * @param	boolean	True 成功, false 失败
	 */
	function start($toemail, $subject, $message, $type = 0, $from = '', $uheaders = '', $username = '') {
		$toemail = $this->fetch_first_line ( $toemail );

		if (empty ( $toemail )) {
			return false;
		}

		$delimiter = & $this->delimiter;
		$skyuc = & $this->registry;

		$toemail = unhtmlspecialchars ( $toemail );
		$subject = $this->fetch_first_line ( $subject );
		$message = preg_replace ( "#(\r\n|\r|\n)#s", $delimiter, trim ( $message ) );

		// 仅仅设置标头编码
		$encoding = $skyuc->options ['mail_charset'];
		$unicode_decode = false;

		// 这些行 可能需要直接调用convert_int_to_utf8
		$message = unhtmlspecialchars ( $message, $unicode_decode );
		$subject = $this->encode_email_header ( unhtmlspecialchars ( $subject, $unicode_decode ), $encoding, false, false );

		$from = $this->fetch_first_line ( $from );
		if (empty ( $from )) {
			$mailfromname = $skyuc->options ['site_name'];

			if ($unicode_decode == true) {
				$mailfromname = utf8_encode ( $mailfromname );
			}
			$mailfromname = $this->encode_email_header ( unhtmlspecialchars ( $mailfromname, $unicode_decode ), $encoding );

			$headers .= 'From: ' . $mailfromname . '<' . $skyuc->options ['reply_email'] . '>' . $delimiter;
			$headers .= 'Auto-Submitted: auto-generated' . $delimiter;
			// Exchange (哦 Microsoft) 不尊重 auto-generated。
			if ($skyuc->options ['usebulkheader']) {
				$headers .= 'Precedence: bulk' . $delimiter;
			}

		} else {
			if ($username) {
				$mailfromname = $username . ' @ ' . $skyuc->options ['site_name'];
			} else {
				$mailfromname = $from;
			}

			if ($unicode_decode == true) {
				$mailfromname = utf8_encode ( $mailfromname );
			}
			$mailfromname = $this->encode_email_header ( unhtmlspecialchars ( $mailfromname, $unicode_decode ), $encoding );

			$headers .= 'From: ' . $mailfromname . ' <' . $from . '>' . $delimiter;
			$headers .= 'Sender: ' . $skyuc->options ['reply_email'] . $delimiter;
		}

		$fromemail = $skyuc->options ['reply_email'];
		$headers .= 'Return-Path: ' . $fromemail . $delimiter;

		$http_host = HTTP_HOST;
		if (! $http_host) {
			$http_host = substr ( md5 ( $message ), 12, 18 ) . '.skyuc_unknown.unknown';
		}
		$msgid = '<' . gmdate ( 'YmdHis' ) . '.' . substr ( md5 ( $message . microtime () ), 0, 12 ) . '@' . $http_host . '>';
		$headers .= 'Message-ID: ' . $msgid . $delimiter;

		$headers .= preg_replace ( "#(\r\n|\r|\n)#s", $delimiter, $uheaders );
		unset ( $uheaders );

		$headers .= 'MIME-Version: 1.0' . $delimiter;
		$headers .= iif ( $type == 0, 'Content-Type: text/plain', 'Content-Type: text/html' ) . iif ( $encoding, '; charset="' . $encoding . '"' ) . $delimiter;
		$headers .= 'Content-Transfer-Encoding: 8bit' . $delimiter;
		$headers .= 'X-Priority: 3' . $delimiter;
		$headers .= 'X-Mailer: Skyuc Mail via PHP' . $delimiter;
		$headers .= 'Date: ' . date ( 'r' ) . $delimiter;

		$this->toemail = $toemail;
		$this->subject = $subject;
		$this->message = $message;
		$this->headers = $headers;
		$this->fromemail = $fromemail;

		return true;
	}

	/**
	 * 设置所有必要的变量，用于发送一条消息。
	 *
	 * @param	string	收件人电子邮件
	 * @param	string	主题
	 * @param	string	信息
	 * @param	string	该邮件发送所有标头
	 * @param	string	发件人电子邮件
	 */
	function quick_set($toemail, $subject, $message, $headers, $fromemail) {
		$this->toemail = $toemail;
		$this->subject = $subject;
		$this->message = $message;
		$this->headers = $headers;
		$this->fromemail = $fromemail;
	}

	/**
	 * 实际发送信息
	 *
	 * @return	boolean	True 成功, false 失败
	 */
	function send() {
		if (! $this->toemail) {
			return false;
		}

		@ini_set ( 'sendmail_from', $this->fromemail );

		if (! SAFEMODE and $this->registry->options ['needfromemail']) {
			$result = @mail ( $this->toemail, $this->subject, $this->message, trim ( $this->headers ), '-f ' . $this->fromemail );
		} else {
			$result = @mail ( $this->toemail, $this->subject, $this->message, trim ( $this->headers ) );
		}

		$this->log_email ( $result );
		return $result;
	}

	/**
	 * 返回字符串的第一行 -- 以防止错误，发送电子邮件时
	 *
	 * @param	string	要裁剪的字符串
	 *
	 * @return	string
	 */
	function fetch_first_line($text) {
		$text = preg_replace ( "/(\r\n|\r|\n)/s", "\r\n", trim ( $text ) );
		$pos = strpos ( $text, "\r\n" );
		if ($pos !== false) {
			return substr ( $text, 0, $pos );
		}
		return $text;
	}

	/**
	 * 将编码为 RFC 2047 兼容的邮件标题。这允许非 ASCII 字符集通过 quoted-printable 编码的支持。
	 *
	 * @param	string	要编码的字符
	 * @param	string	字符编码
	 * @param	bool		是否强制编码为 quoted-printable 即使不是必须的
	 * @param	bool		是否引用字符串;仅适用于如果编码未完成
	 *
	 * @return	string	编码的标头
	 */
	function encode_email_header($text, $charset = 'utf-8', $force_encode = false, $quoted_string = true) {
		$text = trim ( $text );

		if (! $charset) {
			// 不知道如何编码，因此，我们不能继续
			return $text;
		}

		if ($force_encode == true) {
			$qp_encode = true;
		} else {
			$qp_encode = false;

			for($i = 0; $i < strlen ( $text ); $i ++) {
				if (ord ( $text {$i} ) > 127) {
					// 我们有非 ascii 字符
					$qp_encode = true;
					break;
				}
			}
		}

		if ($qp_encode == true) {
			// 请参阅RFC 2047 ; 不包含" _ "在这里, 包含编码空格
			$outtext = preg_replace ( '#([^a-zA-Z0-9!*+\-/ ])#e', "'=' . strtoupper(dechex(ord(str_replace('\\\"', '\"', '\\1'))))", $text );
			$outtext = str_replace ( ' ', '_', $outtext );
			$outtext = "=?$charset?q?$outtext?=";
			return $outtext;
		} else {
			if ($quoted_string) {
				$text = str_replace ( array ('"', '(', ')' ), array ('\"', '\(', '\)' ), $text );
				return "\"$text\"";
			} else {
				return preg_replace ( '#(\r\n|\n|\r)+#', ' ', $text );
			}
		}
	}

	/**
	 * 设置调试成员
	 *
	 * @param	boolean
	 */
	function set_debug($debug) {
		$this->debug = $debug;
	}

	/**
	 * 记录邮件到文件
	 *
	 */
	function log_email($status = true) {
		if (! empty ( $this->registry->options ['errorlogemail'] )) {
			$errfile = DIR . '/data/' . $this->registry->options ['errorlogemail'];
			if ($this->registry->options ['errorlogmaxsize'] != 0 and $filesize = @filesize ( "$errfile.log" ) and $filesize >= $this->registry->options ['errorlogmaxsize']) {
				@copy ( "$errfile.log", $errfile . TIMENOW . '.log' );
				@unlink ( "$errfile.log" );
			}

			$timenow = date ( 'r', TIMENOW );

			$fp = @fopen ( "$errfile.log", 'a+b' );

			if ($fp) {
				if ($status === true) {
					$output = "SUCCESS\r\n";
				} else {
					$output = "FAILED";
					if ($status !== false) {
						$output .= ": $status";
					}
					$output .= "\r\n";
				}
				if ($this->delimiter == "\n") {
					$append = "$timenow\r\nTo: " . $this->toemail . "\r\nSubject: " . $this->subject . "\r\n" . $this->headers . "\r\n\r\n" . $this->message . "\r\n=====================================================\r\n\r\n";
					@fwrite ( $fp, $output . $append );
				} else {
					$append = preg_replace ( "#(\r\n|\r|\n)#s", "\r\n", "$timenow\r\nTo: " . $this->toemail . "\r\nSubject: " . $this->subject . "\r\n" . $this->headers . "\r\n\r\n" . $this->message . "\r\n=====================================================\r\n\r\n" );

					@fwrite ( $fp, $output . $append );
				}
				fclose ( $fp );
			}
		}
	}
}

/**
 * SMTP 邮件发送类
 *
 * 此类发送电子邮件从 SKYUC 使用一个 SMTP 包装
 *
 *
 */
class SmtpMail extends Mail {
	/**
	 * SMTP 主机
	 *
	 * @property	string
	 */
	protected $smtpHost;

	/**
	 * SMTP 端口
	 *
	 * @protected	integer
	 */
	protected $smtpPort;

	/**
	 * SMTP  用户名
	 *
	 * @protected	string
	 */
	protected $smtpUser;

	/**
	 * SMTP 密码
	 *
	 * @protected	string
	 */
	protected $smtpPass;

	/**
	 * Raw SMTP socket
	 *
	 * @protected	resource
	 */
	protected $smtpSocket = null;

	/**
	 * 从 SMTP 服务器返回代码
	 *
	 * @protected	integer
	 */
	protected $smtpReturn = 0;

	/**
	 * 安全连接方法
	 *
	 * @protected	string
	 */
	protected $secure = '';

	/**
	 * 构造函数
	 *
	 * @param	Registry	SKYUC 注册表对象
	 */
	function __construct(&$registry) {
		if (is_object ( $registry )) {
			$this->registry = & $registry;
		} else {
			trigger_error ( 'Registry object is not an object', E_USER_ERROR );
		}

		$this->secure = $this->registry->options ['smtp_tls'];

		if ($this->registry->options ['smtp_tls'] == 1) {
			$this->secure = 'ssl';
		} //since ('ssl' == 0) is true in php, we need to check for legacy 0 values as well
//note that in the off change that somebody gets '0' into the system, this will
		//work just fine without conversion.
		else if ($this->registry->options ['smtp_tls'] === 0) {
			$this->secure = 'none';
		}

		$this->smtpHost = $this->registry->options ['smtp_host'];
		$this->smtpPort = (! empty ( $this->registry->options ['smtp_port'] ) ? intval ( $this->registry->options ['smtp_port'] ) : 25);
		$this->smtpUser = $this->registry->options ['smtp_user'];
		$this->smtpPass = (strlen ( $this->registry->options ['smtp_pass'] ) > 16 ? mcryptcode ( $this->registry->options ['smtp_pass'], 'DECODE' ) : $this->registry->options ['smtp_pass']);

		$this->delimiter = "\r\n";
	}

	/**
	 * 将指令发送到 SMTP 服务器
	 *
	 * @param	string	将发送到服务器消息
	 * @param	mixed	消息代码预期会返回或 false ,如果非预期
	 *
	 * @return	boolean	错误时返回 false
	 */
	function sendMessage($msg, $expectedResult = false) {
		if ($msg !== false and ! empty ( $msg )) {
			fputs ( $this->smtpSocket, $msg . "\r\n" );
		}
		if ($expectedResult !== false) {
			$result = '';
			while ( $line = @fgets ( $this->smtpSocket, 1024 ) ) {
				$result .= $line;
				if (preg_match ( '#^(\d{3}) #', $line, $matches )) {
					break;
				}
			}
			$this->smtpReturn = intval ( $matches [1] );
			return ($this->smtpReturn == $expectedResult);
		}
		return true;
	}

	/**
	 * 错误时触发 PHP 警告
	 *
	 * @param	string	要显示的错误消息
	 *
	 * @return	boolean	始终返回 false （错误）
	 */
	function errorMessage($msg) {
		if ($this->debug) {
			trigger_error ( $msg, E_USER_WARNING );
		}
		$this->log_email ( $msg );
		$GLOBALS ['err']->add ( $msg );
		return false;
	}

	function sendHello() {
		if (! $this->smtpSocket) {
			return false;
		}
		if (! $this->sendMessage ( 'EHLO ' . $this->smtpHost, 250 )) {
			if (! $this->sendMessage ( 'HELO ' . $this->smtpHost, 250 )) {
				return false;
			}
		}
		return true;
	}

	/**
	 * 尝试发送电子邮件基于参数传递到 start()/quick_set()
	 *
	 * @return	boolean	错误时返回 false
	 */
	function send() {
		if (! $this->toemail) {
			return false;
		}

		$this->smtpSocket = @fsockopen ( ($this->secure == 'ssl' ? 'ssl://' : 'tcp://') . $this->smtpHost, $this->smtpPort, $errno, $errstr, 30 );

		if ($this->smtpSocket) {
			if (! $this->sendMessage ( false, 220 )) {
				return $this->errorMessage ( $this->smtpReturn . ' Unexpected response when connecting to SMTP server' );
			}

			// 做初步握手
			if (! $this->sendHello ()) {
				return $this->errorMessage ( $this->smtpReturn . ' Unexpected response from SMTP server during handshake' );
			}

			if ($this->secure == 'tls' and function_exists ( 'stream_socket_enable_crypto' )) {
				if ($this->sendMessage ( 'STARTTLS', 220 )) {
					if (! stream_socket_enable_crypto ( $this->smtpSocket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT )) {
						return $this->errorMessage ( 'Unable to negotitate TLS handshake.' );
					}
				}

				//  TLS 后再说一遍你好
				$this->sendHello ();
			}

			if ($this->smtpUser and $this->smtpPass) {
				if ($this->sendMessage ( 'AUTH LOGIN', 334 )) {
					if (! $this->sendMessage ( base64_encode ( $this->smtpUser ), 334 ) or ! $this->sendMessage ( base64_encode ( $this->smtpPass ), 235 )) {
						return $this->errorMessage ( $this->smtpReturn . ' Authorization to the SMTP server failed' );
					}
				}
			}

			if (! $this->sendMessage ( 'MAIL FROM:<' . $this->fromemail . '>', 250 )) {
				return $this->errorMessage ( $this->smtpReturn . ' Unexpected response from SMTP server during FROM address transmission' );
			}

			// 我们可以有多个地址
			$addresses = explode ( ',', $this->toemail );
			foreach ( $addresses as $address ) {
				if (! $this->sendMessage ( 'RCPT TO:<' . trim ( $address ) . '>', 250 )) {
					return $this->errorMessage ( $this->smtpReturn . ' Unexpected response from SMTP server during TO address transmission' );
				}
			}
			if ($this->sendMessage ( 'DATA', 354 )) {
				$this->sendMessage ( 'Date: ' . gmdate ( 'r' ), false );
				$this->sendMessage ( 'To: ' . $this->toemail, false );
				$this->sendMessage ( trim ( $this->headers ), false ); // trim to prevent double \r\n
				$this->sendMessage ( 'Subject: ' . $this->subject, false );
				$this->sendMessage ( "\r\n", false ); // this makes a double \r\n
				// 在他们自己上捕捉到任何单一点
				$this->message = preg_replace ( '#^\.' . $this->delimiter . '#m', '..' . $this->delimiter, $this->message );
				$this->sendMessage ( $this->message, false );
			} else {
				return $this->errorMessage ( $this->smtpReturn . ' Unexpected response from SMTP server during data transmission' );
			}

			if (! $this->sendMessage ( '.', 250 )) {
				return $this->errorMessage ( $this->smtpReturn . ' Unexpected response from SMTP server when ending transmission' );
			}

			//不要检查 QUIT 返回一个有效的结果，如某些服务器只是杀死连接，例如 smtp.gmail.com
			$this->sendMessage ( 'QUIT', 221 );

			fclose ( $this->smtpSocket );
			$this->log_email ();
			return true;
		} else {
			return $this->errorMessage ( 'Unable to connect to SMTP server' );
		}
	}
}

/**
 * 邮件排队类。 此类应作为一个单独通过 fetch_instance() 访问 ！
 * 此类并不实际发送电子邮件，但相当队列，稍后在批处理中发送。
 *
 *
 */
class QueueMail extends Mail {
	/**
	 * 插入到邮件队列SQL
	 *
	 * @protected	string
	 */
	protected $mailsql = '';

	/**
	 * 邮件被插入到队列的数目
	 *
	 * @protected	string
	 */
	protected $mailcounter = '';

	/**
	 * 是否要做批量插入数据库。 永远不会直接设置此选项 ！
	 *
	 * @protected	boolean
	 */
	protected $bulk = false;

	/**
	 * 插入队列的而不是将其发送的消息。
	 *
	 * @return	string	成功返回 True, 失败返回false
	 */
	function send() {
		if (! $this->toemail) {
			return false;
		}

		$skyuc = & $this->registry;

		$data = "
			(" . TIMENOW . ",
			'" . $skyuc->db->escape_string ( $this->toemail ) . "',
			'" . $skyuc->db->escape_string ( $this->fromemail ) . "',
			'" . $skyuc->db->escape_string ( $this->subject ) . "',
			'" . $skyuc->db->escape_string ( $this->message ) . "',
			'" . $skyuc->db->escape_string ( $this->headers ) . "')
		";

		if ($this->bulk) {
			if (! empty ( $this->mailsql )) {
				$this->mailsql .= ',';
			}

			$this->mailsql .= $data;
			$this->mailcounter ++;

			// 当前插入超过 0.5 MB、 将其插入和重新开始
			if (strlen ( $this->mailsql ) > 524288) {
				$this->set_bulk ( false );
				$this->set_bulk ( true );
			}
		} else {
			/*insert query*/
			$skyuc->db->query_write ( "
				INSERT INTO " . TABLE_PREFIX . "mailqueue
					(dateline, toemail, fromemail, subject, message, header)
				VALUES
				" . $data );

			$skyuc->db->query_write ( "
				UPDATE " . TABLE_PREFIX . "datastore SET
					data = data + 1
				WHERE title = 'mailqueue'
			" );

			// 如果我们使用备用的数据存储，我们需要给它一个整型值。 这可能不是原子。
			if (method_exists ( $skyuc->datastore, 'build' )) {
				$mailqueue_db = $skyuc->db->query_first ( "
					SELECT data
					FROM " . TABLE_PREFIX . "datastore
					WHERE title = 'mailqueue'
				" );
				$skyuc->datastore->build ( 'mailqueue', intval ( $mailqueue_db ['data'] ) );
			}
		}

		return true;
	}

	/**
	 * 设置批量选项。 如果禁用该选项，这也刷新缓存到数据库。
	 *
	 * @param	boolean
	 */
	function set_bulk($bulk) {
		if ($bulk) {
			$this->bulk = true;
			$this->mailcounter = 0;
			$this->mailsql = '';
		} else if ($this->mailcounter and $this->mailsql) {
			// 关闭批量发送，所以保存所有邮件
			$skyuc = & $this->registry;

			/*insert query*/
			$skyuc->db->query_write ( "
				INSERT INTO " . TABLE_PREFIX . "mailqueue
				(dateline, toemail, fromemail, subject, message, header)
				VALUES
				" . $this->mailsql );
			$skyuc->db->query_write ( "
				UPDATE " . TABLE_PREFIX . "datastore
				SET data = data + " . intval ( $this->mailcounter ) . "
				WHERE title = 'mailqueue'
			" );

			//如果我们使用备用的数据存储，我们需要给它一个整型值。 这可能不是原子。
			if (method_exists ( $skyuc->datastore, 'build' )) {
				$mailqueue_db = $skyuc->db->query_first ( "
					SELECT data
					FROM " . TABLE_PREFIX . "datastore
					WHERE title = 'mailqueue'
				" );
				$skyuc->datastore->build ( 'mailqueue', intval ( $mailqueue_db ['data'] ) );
			}
		}

		$this->bulk = true;
		$this->mailsql = '';
		$this->mailcounter = 0;
	}

	/**
	 * 一个仿真器， 如果它不存在，请获取该实例。
	 * 如果使用此函数，请务必接受一个引用！
	 *
	 * @return	QueueMail	实例的引用
	 */
	function &fetch_instance() {
		static $instance = null;

		if ($instance === null) {
			global $skyuc;
			$instance = new QueueMail ( $skyuc );
		}

		return $instance;
	}

	/**
	 * 此类唯一的实际发送一封电子邮件部分。将从队列发送邮件。
	 */
	function exec_queue() {
		$skyuc = & $this->registry;

		if ($skyuc->options ['usemailqueue'] == 2) {
			// 锁定 mailqueue 表，以便只有一个进程可以发送的电子邮件的批处理，然后删除它们
			$skyuc->db->lock_tables ( array ('mailqueue' => 'WRITE' ) );
		}

		$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'mailqueue  ORDER BY mailqueueid ';
		$sql = $skyuc->db->query_limit ( $sql, intval ( $skyuc->options ['emailsendnum'] ) );
		$emails = $skyuc->db->query_read ( $sql );

		$mailqueueids = '';
		$newmail = 0;
		$emailarray = array ();
		while ( $email = $skyuc->db->fetch_array ( $emails ) ) {
			// 计算即将发送的邮件数
			$mailqueueids .= ',' . $email ['mailqueueid'];
			$newmail ++;
			$emailarray [] = $email;
		}
		if (! empty ( $mailqueueids )) {
			// 删除邮件队列-停止重复发送
			$skyuc->db->query_write ( "
				DELETE FROM " . TABLE_PREFIX . "mailqueue
				WHERE mailqueueid IN (0 $mailqueueids)
			" );

			if ($skyuc->options ['usemailqueue'] == 2) {
				$skyuc->db->unlock_tables ();
			}

			if ($skyuc->options ['use_smtp']) {
				$prototype = new SmtpMail ( $skyuc );
			} else {
				$prototype = new Mail ( $skyuc );
			}

			foreach ( $emailarray as $index => $email ) {
				// 发送这些邮件
				$mail = clone ($prototype); // 避免开销
				$mail->quick_set ( $email ['toemail'], $email ['subject'], $email ['message'], $email ['header'], $email ['fromemail'] );
				$mail->send ();
			}

			$newmail = 'data - ' . intval ( $newmail );
		} else {
			if ($skyuc->options ['usemailqueue'] == 2) {
				$skyuc->db->unlock_tables ();
			}

			$newmail = 0;
		}

		// 更新邮件剩余数
		$skyuc->db->query_write ( "
			UPDATE " . TABLE_PREFIX . "datastore SET
				data = " . $newmail . ",
				data = IF(data < 0, 0, data)
			WHERE title = 'mailqueue'
		" );

		// 如果我们使用备用的数据存储，我们需要给它一个整型值。 这可能不是原子。
		if (method_exists ( $skyuc->datastore, 'build' )) {
			$mailqueue_db = $skyuc->db->query_first ( "
				SELECT data
				FROM " . TABLE_PREFIX . "datastore
				WHERE title = 'mailqueue'
			" );
			$skyuc->datastore->build ( 'mailqueue', intval ( $mailqueue_db ['data'] ) );
		}
	}
}

?>