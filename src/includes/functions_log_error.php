<?php

/**
 * 记录到一个文件的日志错误
 *
 * @param	string	要写入在日志中的错误消息
 * @param	string	错误类型。 php, database, security, 等.
 *
 * @return	boolean
 */
function log_skyuc_error($errstring, $type = 'database') {
	global $skyuc;
	
	switch ($type) {
		// 记录PHP E_USER_ERROR, E_USER_WARNING, E_WARNING 到文件
		case 'php' :
			if (! empty ( $skyuc->options ['errorlogphp'] )) {
				$errfile = DIR . '/data/' . $skyuc->options ['errorlogphp'];
				$errstring .= "\r\nDate: " . date ( 'l dS \o\f F Y h:i:s A' ) . "\r\n";
				$errstring .= "Username: {$skyuc->userinfo['username']}\r\n";
				$errstring .= 'IP Address: ' . IPADDRESS . "\r\n";
			}
			break;
		
		// 记录 数据库 错误到文件
		case 'database' :
			if (! empty ( $skyuc->options ['errorlogdatabase'] )) {
				$errstring = preg_replace ( "#(\r\n|\r|\n)#s", "\r\n", $errstring );
				$errfile = DIR . '/data/' . $skyuc->options ['errorlogdatabase'];
			}
			break;
		// 记录 FTP 错误到文件
		case 'ftp' :
			if (! empty ( $skyuc->options ['errorlogftp'] )) {
				$errstring = preg_replace ( "#(\r\n|\r|\n)#s", "\r\n", $errstring );
				$errfile = DIR . '/data/' . $skyuc->options ['errorlogftp'];
			}
			break;
		
		// 记录管理员登陆失败到文件
		case 'security' :
			if (! empty ( $skyuc->options ['errorlogsecurity'] )) {
				$errfile = DIR . '/data/' . $skyuc->options ['errorlogsecurity'];
				$username = $errstring;
				$errstring = 'Failed admin logon in ' . $skyuc->db->appname . ' ' . $skyuc->options ['skyuc_version'] . "\r\n\r\n";
				$errstring .= 'Date: ' . date ( 'l dS \o\f F Y h:i:s A' ) . "\r\n";
				$errstring .= 'Script: http://' . $_SERVER ['HTTP_HOST'] . unhtmlspecialchars ( $skyuc->scriptpath ) . "\r\n";
				$errstring .= 'Referer: ' . REFERRER . "\r\n";
				$errstring .= "Username: $username\r\n";
				$errstring .= 'IP Address: ' . IPADDRESS . "\r\n";
			
		//	$errstring .= 'Strikes: '.$GLOBALS['strikes']/5."\r\n";
			}
			break;
	}
	
	if (! isset ( $errfile )) {
		$errfile = DIR . '/data/' . $type;
		$skyuc->options ['errorlogmaxsize'] = 1048576;
	}
	
	// 如果不指定任何文件名，则退出本函数
	if (! ($errfile = trim ( $errfile )) or (defined ( 'DEBUG_MODE' ) and DEBUG_MODE == true)) {
		return false;
	}
	
	if ($skyuc->options ['errorlogmaxsize'] != 0 and $filesize = @filesize ( "$errfile.log" ) and $filesize >= $skyuc->options ['errorlogmaxsize']) {
		@copy ( "$errfile.log", $errfile . TIMENOW . '.log' );
		@unlink ( "$errfile.log" );
	}
	
	//将日志写入到适当的文件
	if ($fp = @fopen ( "$errfile.log", 'a+' )) {
		@fwrite ( $fp, "$errstring\r\n=====================================================\r\n\r\n" );
		@fclose ( $fp );
		return true;
	} else {
		return false;
	}
}

/**
 * 执行检查，请参阅是否发送一个错误的电子邮件以
 *
 * @param	mixed		识别，出现了错误的一致标识符
 * @param	string	错误类型。 php, database, security, 等.
 *
 * @return	boolean
 */
function verify_email_skyuc_error($error = '', $type = 'database') {
	return true;
}

?>