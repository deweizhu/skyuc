<?php

/**
 * SKYUC! FTP函数
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */
$ftp = array ();

/**
 * FTP上传
 * @param	$source	上传的图片
 */
function ftpupload($source) {
	global $ftp, $skyuc;

	if ($skyuc->options ['ftp_on']) {
		if (! $ftp ['connid']) {
			if (! ($ftp ['connid'] = skyuc_ftp_connect ( $skyuc->options ['ftp_host'], $skyuc->options ['ftp_user'], mcryptcode ( $skyuc->options ['ftp_pass'], 'DECODE' ), $skyuc->options ['ftp_path'], $skyuc->options ['ftp_port'], $skyuc->options ['ftp_ssl'] ))) {
				return 0;
			}
		}

		$tmp = explode ( '/', $source ); //目录
		$file = array_pop ( $tmp ); //文件名
		foreach ( $tmp as $val ) {
			if ($val == '..' || $val == '.')
				continue;

			if (! skyuc_ftp_chdir ( $ftp ['connid'], $val )) {
				if (! skyuc_ftp_mkdir ( $ftp ['connid'], $val )) {
					log_skyuc_error ( 'Mkdir ' . $skyuc->options ['ftp_path'] . '/' . $val . ' error.', 'ftp' );
					return 0;
				}
				if (! function_exists ( 'ftp_chmod' ) || ! skyuc_ftp_chmod ( $ftp ['connid'], 0777, $val )) {
					skyuc_ftp_site ( $ftp ['connid'], "'CHMOD 0777 $val'" );
				}
				if (! skyuc_ftp_chdir ( $ftp ['connid'], $val )) {
					log_skyuc_error ( 'Chdir ' . $skyuc->options ['ftp_path'] . '/' . $val . ' error.', 'ftp' );
					return 0;
				}
			}
		}
		if (skyuc_ftp_put ( $ftp ['connid'], $file, $source, FTP_BINARY )) {
			$home = ($skyuc->options ['ftp_path'] == '.') ? '/' : $skyuc->options ['ftp_path'];
			skyuc_ftp_chdir ( $ftp ['connid'], $home );
			return 1;
		}
		log_skyuc_error ( "Upload '$source' error.", 'ftp' );
	}
	return 0;
}
/**
 * FTP 删除文件
 * @param $file
 */
function ftpdelete($file) {
	global $ftp, $skyuc;

	if ($skyuc->options ['ftp_on']) {
		if (! $ftp ['connid']) {
			if (! ($ftp ['connid'] = skyuc_ftp_connect ( $skyuc->options ['ftp_host'], $skyuc->options ['ftp_user'], mcryptcode ( $skyuc->options ['ftp_pass'], 'DECODE' ), $skyuc->options ['ftp_path'], $skyuc->options ['ftp_port'], $skyuc->options ['ftp_ssl'] ))) {
				return 0;
			}
		}
		if (skyuc_ftp_delete ( $ftp ['connid'], $file )) {
			return 1;
		}
	}
	return 0;
}
/**
 * FTP 链接
 * @param $ftphost
 * @param $ftpuser
 * @param $ftppass
 * @param $ftppath
 * @param $ftpport
 * @param $ftpssl
 * @param $silent
 */
function skyuc_ftp_connect($ftphost, $ftpuser, $ftppass, $ftppath, $ftpport = 21, $ftpssl = 0, $silent = 0) {
	global $skyuc;
	@set_time_limit ( 0 );

	$ftphost = wipespecial ( $ftphost );
	$ftpport = intval ( $ftpport );
	$ftpssl = intval ( $ftpssl );
	$skyuc->options ['ftp_timeout'] = intval ( $skyuc->options ['ftp_timeout'] );

	$func = $ftpssl && function_exists ( 'ftp_ssl_connect' ) ? 'ftp_ssl_connect' : 'ftp_connect';
	if ($func == 'ftp_connect' && ! function_exists ( 'ftp_connect' )) {
		if ($silent) {
			return - 4;
		} else {
			log_skyuc_error ( "FTP not supported.", 'ftp' );
		}
	}
	if ($ftp_conn_id = @$func ( $ftphost, $ftpport, 20 )) {
		if ($skyuc->options ['ftp_timeout'] && function_exists ( 'ftp_set_option' )) {
			@ftp_set_option ( $ftp_conn_id, FTP_TIMEOUT_SEC, $skyuc->options ['ftp_timeout'] );
		}
		if (skyuc_ftp_login ( $ftp_conn_id, $ftpuser, $ftppass )) {
			if ($skyuc->options ['ftp_pasv']) {
				skyuc_ftp_pasv ( $ftp_conn_id, TRUE );
			}
			if (skyuc_ftp_chdir ( $ftp_conn_id, $ftppath )) {
				return $ftp_conn_id;
			} else {
				if ($silent) {
					return - 3;
				} else {
					log_skyuc_error ( "Chdir '$ftppath' error.", 'ftp' );
				}
			}
		} else {
			if ($silent) {
				return - 2;
			} else {
				log_skyuc_error ( '530 Not logged in.', 'ftp' );
			}
		}
	} else {
		if ($silent) {
			return - 1;
		} else {
			log_skyuc_error ( "Couldn't connect to $ftphost:$ftpport.", 'ftp' );
		}
	}
	skyuc_ftp_close ( $ftp_conn_id );
	return - 1;
}
/**
 * FTP 创建文件夹
 * @param $ftp_stream
 * @param $directory
 */
function skyuc_ftp_mkdir($ftp_stream, $directory) {
	$directory = wipespecial ( $directory );
	return @ftp_mkdir ( $ftp_stream, $directory );
}

function skyuc_ftp_rmdir($ftp_stream, $directory) {
	$directory = wipespecial ( $directory );
	return @ftp_rmdir ( $ftp_stream, $directory );
}

function skyuc_ftp_put($ftp_stream, $remote_file, $local_file, $mode, $startpos = 0) {
	$remote_file = wipespecial ( $remote_file );
	$local_file = wipespecial ( $local_file );
	$mode = intval ( $mode );
	$startpos = intval ( $startpos );
	return @ftp_put ( $ftp_stream, $remote_file, $local_file, $mode, $startpos );
}

function skyuc_ftp_size($ftp_stream, $remote_file) {
	$remote_file = wipespecial ( $remote_file );
	return @ftp_size ( $ftp_stream, $remote_file );
}

function skyuc_ftp_close($ftp_stream) {
	return @ftp_close ( $ftp_stream );
}

function skyuc_ftp_delete($ftp_stream, $path) {
	$path = wipespecial ( $path );
	return @ftp_delete ( $ftp_stream, $path );
}

function skyuc_ftp_get($ftp_stream, $local_file, $remote_file, $mode, $resumepos = 0) {
	$remote_file = wipespecial ( $remote_file );
	$local_file = wipespecial ( $local_file );
	$mode = intval ( $mode );
	$resumepos = intval ( $resumepos );
	return @ftp_get ( $ftp_stream, $local_file, $remote_file, $mode, $resumepos );
}

function skyuc_ftp_login($ftp_stream, $username, $password) {
	$username = wipespecial ( $username );
	$password = str_replace ( array ("\n", "\r" ), array ('', '' ), $password );
	return @ftp_login ( $ftp_stream, $username, $password );
}

function skyuc_ftp_pasv($ftp_stream, $pasv) {
	$pasv = intval ( $pasv );
	return @ftp_pasv ( $ftp_stream, $pasv );
}

function skyuc_ftp_chdir($ftp_stream, $directory) {
	$directory = wipespecial ( $directory );
	return @ftp_chdir ( $ftp_stream, $directory );
}

function skyuc_ftp_site($ftp_stream, $cmd) {
	$cmd = wipespecial ( $cmd );
	return @ftp_site ( $ftp_stream, $cmd );
}

function skyuc_ftp_chmod($ftp_stream, $mode, $filename) {
	$mode = intval ( $mode );
	$filename = wipespecial ( $filename );
	if (function_exists ( 'ftp_chmod' )) {
		return @ftp_chmod ( $ftp_stream, $mode, $filename );
	} else {
		return skyuc_ftp_site ( $ftp_stream, 'CHMOD ' . $mode . ' ' . $filename );
	}
}

function wipespecial($str) {
	return str_replace ( array ("\n", "\r", '..' ), array ('', '', '' ), $str );
}
?>