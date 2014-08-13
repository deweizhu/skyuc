<?php
/**
 * SKYUC! 网吧管理
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

// ####################### 设置 PHP 环境 ###########################
error_reporting ( E_ALL & ~ E_NOTICE );

// #################### 定义重要常量 #######################
define ( 'THIS_SCRIPT', 'netbar' );
define ( 'CSRF_PROTECTION', true );
define ( 'CSRF_SKIP_LIST', '' );
define ( 'SKIP_SESSIONCREATE', 1 );
define ( 'SKIP_USERINFO', 1 );
define ( 'SKIP_DEFAULTDATASTORE', 1 );

require (dirname ( __FILE__ ) . '/global.php');
require_once (DIR . '/includes/functions_users.php');

$skyuc->input->clean_gpc ( 'r', 'act', TYPE_STR );

$action = iif ( $skyuc->GPC_exists ['act'], $skyuc->GPC ['act'], 'login' );

assign_template ();

$position = assign_ur_here ( 0, $_LANG ['user_center'] );

$smarty->assign ( 'page_title', $position ['title'] ); // 页面标题
$smarty->assign ( 'ur_here', $position ['ur_here'] );
$smarty->assign ( 'nav_list', get_navigator () ); // 导航栏
$smarty->assign ( 'action', $action );
$smarty->assign ( 'lang', $_LANG );

/*------------------------------------------------------ */
//-- 用户登录界面
/*------------------------------------------------------ */
if ($action == 'login') {
	$smarty->display ( 'netbar.dwt' );
} /*------------------------------------------------------ */
//-- 处理网吧的登录
/*------------------------------------------------------ */
elseif ($action == 'act_login') {
	$skyuc->input->clean_array_gpc ( 'p', array ('netbar_name' => TYPE_STR, 'netbar_pwd' => TYPE_STR ) );

	$username = $skyuc->GPC ['netbar_name'];
	$password = $skyuc->GPC ['netbar_pwd'];

	$sql = 'SELECT username, is_ok FROM ' . TABLE_PREFIX . 'netbar' . " WHERE username ='" . $db->escape_string ( $username ) . "' AND userpass ='" . $db->escape_string ( $password ) . "'";
	$row = $db->query_first_slave ( $sql );
	if (! empty ( $row )) {
		if (empty ( $row ['is_ok'] )) {
			show_message ( $_LANG ['netbar_isok'], $_LANG ['netbar_lnk'], 'netbar.php', 'error' );
		}
		// 保存 COOKIE
		skyuc_setcookie ( 'netbar_name', $row ['username'], TIMENOW + 3600 );
		skyuc_setcookie ( 'netbar_chk', sign_client_string ( $row ['username'] ), TIMENOW + 3600 );

		//登陆成功
		header ( "Location: netbar.php?act=manage \n" );
		exit ();
	} else {
		show_message ( $_LANG ['netbar_failure'], $_LANG ['netbar_lnk'], 'netbar.php', 'error' );
	}
} /*------------------------------------------------------ */
//-- 网吧管理中心
/*------------------------------------------------------ */
elseif ($action == 'manage') {
	$skyuc->input->clean_array_gpc ( 'c', array (COOKIE_PREFIX . 'netbar_name' => TYPE_STR, COOKIE_PREFIX . 'netbar_chk' => TYPE_STR ) );

	$netbar_name = $skyuc->GPC [COOKIE_PREFIX . 'netbar_name'];
	$netbar_chk = $skyuc->GPC [COOKIE_PREFIX . 'netbar_chk'];

	if (empty ( $netbar_name ) || (verify_client_string ( $netbar_chk ) != $netbar_name)) {
		//未登陆处理
		header ( "Location: netbar.php?act=login \n" );
		exit ();
	}
	$sql = 'SELECT id,title,sip,eip,content,addtime,endtime,maxuser FROM ' . TABLE_PREFIX . 'netbar' . " WHERE username ='" . $db->escape_string ( $netbar_name ) . "'";
	$row = $db->query_first_slave ( $sql );
	if (! empty ( $row )) {

		$row ['addtime'] = skyuc_date ( $skyuc->options ['date_format'], $row ['addtime'], true, false );
		$row ['endtime'] = skyuc_date ( $skyuc->options ['date_format'], $row ['endtime'], true, false );
	}

	$smarty->assign ( 'netbar', $row );
	$smarty->assign ( 'ip', ALT_IP );
	$smarty->display ( 'netbar.dwt' );
} /*------------------------------------------------------ */
//-- 网吧 IP 、名称修改
/*------------------------------------------------------ */
elseif ($action === 'act_edit_ip') {
	$skyuc->input->clean_array_gpc ( 'c', array (COOKIE_PREFIX . 'netbar_name' => TYPE_STR, COOKIE_PREFIX . 'netbar_chk' => TYPE_STR ) );

	$netbar_name = $skyuc->GPC [COOKIE_PREFIX . 'netbar_name'];
	$netbar_chk = $skyuc->GPC [COOKIE_PREFIX . 'netbar_chk'];

	if (empty ( $netbar_name ) || (verify_client_string ( $netbar_chk ) != $netbar_name)) {
		//未登陆处理
		header ( "Location: netbar.php?act=login \n" );
		exit ();
	}

	$skyuc->input->clean_array_gpc ( 'p', array ('title' => TYPE_STR, 'sip' => TYPE_STR, 'eip' => TYPE_STR ) );

	$sql = 'UPDATE ' . TABLE_PREFIX . 'netbar' . ' SET ' . " title ='" . $db->escape_string ( $skyuc->GPC ['title'] ) . "', " . " sip ='" . $skyuc->GPC ['sip'] . "', " . " eip ='" . $skyuc->GPC ['eip'] . "', " . " snum ='" . ip2num ( $skyuc->GPC ['sip'] ) . "', " . " enum ='" . ip2num ( $skyuc->GPC ['eip'] ) . "', " . " lasttime ='" . TIMENOW . "' " . " WHERE title ='" . $db->escape_string ( $netbar_name ) . "'";
	$db->query_write ( $sql ); // 修改的操作


	show_message ( $_LANG ['edit_ip_success'] );

} /*------------------------------------------------------ */
//-- 密码修改
/*------------------------------------------------------ */
elseif ($action === 'act_edit_password') {
	$skyuc->input->clean_array_gpc ( 'c', array (COOKIE_PREFIX . 'netbar_name' => TYPE_STR, COOKIE_PREFIX . 'netbar_chk' => TYPE_STR ) );

	$netbar_name = $skyuc->GPC [COOKIE_PREFIX . 'netbar_name'];
	$netbar_chk = $skyuc->GPC [COOKIE_PREFIX . 'netbar_chk'];

	if (empty ( $netbar_name ) || (verify_client_string ( $netbar_chk ) != $netbar_name)) {
		//未登陆处理
		header ( "Location: netbar.php?act=login \n" );
		exit ();
	}

	$skyuc->input->clean_array_gpc ( 'p', array ('old_password' => TYPE_STR, 'new_password' => TYPE_STR ) );

	if (empty ( $skyuc->GPC ['old_password'] )) {
		show_message ( $_LANG ['netbar_js'] ['password_empty'] );
	}

	if (strlen ( $skyuc->GPC ['new_password'] ) < 6) {
		show_message ( $_LANG ['netbar_js'] ['password_shorter'] );
	}
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'netbar' . " WHERE userpass ='" . $db->escape_string ( $skyuc->GPC ['old_password'] ) . "' AND username='" . $db->escape_string ( $netbar_name ) . "'";
	$total = $db->query_first ( $sql );
	if ($total ['total'] == 0) {
		show_message ( $_LANG ['pass_failure'] );
	}

	$sql = 'UPDATE ' . TABLE_PREFIX . 'netbar' . " SET userpass ='" . $db->escape_string ( $skyuc->GPC ['new_password'] ) . "' WHERE username ='" . $db->escape_string ( $netbar_name ) . "'";
	$db->query_write ( $sql );

	// 清除 COOKIE
	skyuc_setcookie ( 'netbar_name', '', TIMENOW - 3600 );
	skyuc_setcookie ( 'netbar_chk', '', TIMENOW - 3600 );

	show_message ( $_LANG ['edit_password_success'], $_LANG ['netbar_lnk'], 'netbar.php?act=login', 'info' );
} /*------------------------------------------------------ */
//-- 退出网吧管理中心
/*------------------------------------------------------ */
elseif ($action == 'logout') {
	// 清除 COOKIE
	skyuc_setcookie ( 'netbar_name', '', TIMENOW - 3600 );
	skyuc_setcookie ( 'netbar_chk', '', TIMENOW - 3600 );

	show_message ( $_LANG ['logout_success'], $_LANG ['back_home_lnk'], 'index.php' );
}
?>

