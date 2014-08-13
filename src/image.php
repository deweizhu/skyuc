<?php
/**
 * SKYUC 图像显示
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

// ####################### 设置 PHP 环境 ###########################
error_reporting ( E_ALL & ~ E_NOTICE );

// #################### 定义重要常量	 #######################
define ( 'THIS_SCRIPT', 'image' );
define ( 'SKYUC_AREA', 'IN_SKYUC' );
define ( 'CSRF_PROTECTION', true );
define ( 'CSRF_SKIP_LIST', '' );
define ( 'LOCATION_BYPASS', 1 );
define ( 'SKIP_SMARTY', 1 );
define ( 'NOSHUTDOWNFUNC', true );
define ( 'NOCOOKIES', 1 );
define ( 'INGORE_VISIT_STATS', true );

if ((! empty ( $_SERVER ['HTTP_IF_MODIFIED_SINCE'] ) or ! empty ( $_SERVER ['HTTP_IF_NONE_MATCH'] )) and $_GET ['type'] != 'regcheck') {
	// 不检查修改日期，如 URL 包含要消除缓存的唯一项目
	if (PHP_SAPI == 'cgi' or PHP_SAPI == 'cgi-fcgi') {
		header ( 'Status: 304 Not Modified' );
	} else {
		header ( 'HTTP/1.1 304 Not Modified' );
	}
	exit ();
}

// ######################### REQUIRE BACK-END ############################
if ($_REQUEST ['type'] == 'dberror') // do not require back-end
{
	header ( 'Content-type: image/jpeg' );
	readfile ( './includes/database_error_image.jpg' );
	exit ();
} else if ($_REQUEST ['type'] == 'ieprompt') {
	header ( 'Content-type: image/jpeg' );
	readfile ( './includes/ieprompt.jpg' );
	exit ();
} else {
	define ( 'SKIP_SESSIONCREATE', 1 );
	define ( 'SKIP_USERINFO', 1 );
	define ( 'SKIP_DEFAULTDATASTORE', 1 );
	define ( 'CWD', (($getcwd = getcwd ()) ? $getcwd : '.') );
	require_once (CWD . '/includes/init.php');
}

$skyuc->input->clean_array_gpc ( 'r', array ('type' => TYPE_STR, 'hash' => TYPE_STR, 'i' => TYPE_STR ) );

// #######################################################################
// ######################## 开始主脚本 ############################
// #######################################################################
if ($skyuc->GPC ['type'] == 'hv') {
	require_once (DIR . '/includes/class_image.php');

	$imageinfo = array ();

	if ($skyuc->GPC ['hash'] == '' or $skyuc->GPC ['hash'] == 'test') {
		header ( 'Content-type: image/gif' );
		readfile ( DIR . '/data/images/clear.gif' );
		exit ();
	} else if (! ($imageinfo = $db->query_first ( 'SELECT answer FROM ' . TABLE_PREFIX . "humanverify WHERE hash = '" . $db->escape_string ( $skyuc->GPC ['hash'] ) . "' AND viewed = 0" ))) {
		header ( 'Content-type: image/gif' );
		readfile ( DIR . '/data/images/clear.gif' );
		exit ();
	} else {
		$db->query_write ( '
			UPDATE ' . TABLE_PREFIX . "humanverify
			SET viewed = 1
			WHERE hash = '" . $db->escape_string ( $skyuc->GPC ['hash'] ) . "' AND
				viewed = 0
		" );
		if ($db->affected_rows () == 0) { // image managed to get viewed by someone else between the $imageinfo query above and now
			header ( 'Content-type: image/gif' );
			readfile ( DIR . '/data/images/clear.gif' );
			exit ();
		}

	}
	$image = & Image::fetch_library ( $skyuc );
	$db->close ();
	$image->print_image_from_string ( $imageinfo ['answer'], true );
}

?>