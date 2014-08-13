<?php
/**
 * SKYUC! 管理中心全局入口
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

// 标志我们在哪里
define ( 'SKYUC_AREA', 'AdminCP' );
define ( 'IN_CONTROL_PANEL', true );

if (! isset ( $specialtemplates ) or ! is_array ( $specialtemplates )) {
	$specialtemplates = array ();
}
$specialtemplates [] = 'mailqueue';
$specialtemplates [] = 'players';
$specialtemplates [] = 'servers';

// ###################### Start functions #######################
chdir ( './../' );
define ( 'CWD', (($getcwd = getcwd ()) ? $getcwd : '.') );
define ( 'ADM', dirname ( __FILE__ ) );

require_once (CWD . '/includes/init.php');
require_once (DIR . '/includes/functions_admin.php');
require_once (DIR . '/includes/class_exchange.php');

// 创建错误处理对象
$err = new skyuc_error ( 'message.tpl' );

// 引入语言包
require (DIR . '/languages/' . $skyuc->options ['lang'] . '/admincp/common.php');
require (DIR . '/languages/' . $skyuc->options ['lang'] . '/admincp/log_action.php');
if (is_file ( DIR . '/languages/' . $skyuc->options ['lang'] . '/admincp/' . basename ( PHP_SELF ) )) {
	include (DIR . '/languages/' . $skyuc->options ['lang'] . '/admincp/' . basename ( PHP_SELF ));
}
if (is_file ( DIR . '/includes/control/admincp/' . basename ( PHP_SELF ) )) {
	include (DIR . '/includes/control/admincp/' . basename ( PHP_SELF ));
}

//使 $_LANG 成为 $skyuc 的成员函数
$skyuc->lang = & $_LANG;

// 清除文件状态缓存
clearstatcache ();

// 创建 Smarty 对象。
require (DIR . '/includes/class_template.php');
$smarty = new Template ();

$smarty->template_dir = DIR . '/' . $skyuc->config ['Misc'] ['admincpdir'] . '/templates';
$smarty->compile_dir = DIR . '/data/compiled/admincp';

$_LANG ['cp_home'] = APPNAME . ' ' . $_LANG ['cp_home'];
$_LANG ['copyright'] = '版权所有 &copy; 2012 <a href=\'http://www.skyuc.com\' target=\'_blank\'>天空网络</a>，并保留所有权利。';

if (! defined ( 'SKIP_SMARTY' )) {

	$smarty->assign ( 'lang', $_LANG );

	$template_session = array ('sessionhash' => $skyuc->session->vars ['sessionhash'], 'sessionurl' => $skyuc->session->vars ['sessionurl'], 'sessionurl_q' => $skyuc->session->vars ['sessionurl_q'], 'sessionurl_js' => $skyuc->session->vars ['sessionurl_js'] );

	$smarty->assign ( 'session', $template_session );
}

// ###################### Start headers (send no-cache) #######################
exec_nocache_headers ();

// #############################################################################
// 获取 日期/时间 信息
fetch_time_data ();

//初始化 action
$skyuc->input->clean_gpc ( 'r', 'act', TYPE_STR );

if (($skyuc->GPC ['act'] == 'login' || $skyuc->GPC ['act'] == 'logout' || $skyuc->GPC ['act'] == 'signin') && strpos ( $_SERVER ['PHP_SELF'], '/privilege.php' ) === false) {
	$skyuc->GPC ['act'] = '';
} elseif (($skyuc->GPC ['act'] == 'forget_pwd' || $skyuc->GPC ['act'] == 'reset_pwd' || $skyuc->GPC ['act'] == 'get_pwd') && strpos ( $_SERVER ['PHP_SELF'], '/get_password.php' ) === false) {
	$skyuc->GPC ['act'] = '';
} /*------------------------------------------------------ */
//-- 生成验证码
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'captcha') {
	include (DIR . '/includes/cls_captcha.php');
	$img = new captcha ( DIR . '/includes/data/captcha/' );
	$img->generate_image ();
	exit ();
}

// ############################################ Start Login Check ####################################
$cpsession = array ();

$skyuc->input->clean_array_gpc ( 'p', array ('adminhash' => TYPE_STR ) );

$skyuc->input->clean_array_gpc ( 'c', array (COOKIE_PREFIX . 'cpsession' => TYPE_STR ) );

if (! empty ( $skyuc->GPC [COOKIE_PREFIX . 'cpsession'] )) {
	$cpsession = $db->query_first ( "
		SELECT * FROM " . TABLE_PREFIX . "cpsession
		WHERE adminid = " . $skyuc->session->vars ['adminid'] . "
			AND hash = '" . $db->escape_string ( $skyuc->GPC [COOKIE_PREFIX . 'cpsession'] ) . "'
			AND dateline > " . iif ( $skyuc->options ['timeoutcontrolpanel'], intval ( TIMENOW - $skyuc->options ['cookietimeout'] * 60 ), intval ( TIMENOW - 3600 ) ) );

	if (! empty ( $cpsession )) {
		$db->query_write ( "
			UPDATE LOW_PRIORITY " . TABLE_PREFIX . "cpsession
			SET dateline = " . TIMENOW . "
			WHERE adminid = " . $skyuc->session->vars ['adminid'] . "
				AND hash = '" . $db->escape_string ( $skyuc->GPC [COOKIE_PREFIX . 'cpsession'] ) . "'
		" );
	}
}

//define('CP_SESSIONHASH', $cpsession['hash']);


$checkpwd = 0;
if ($skyuc->GPC ['act'] != 'login' && $skyuc->GPC ['act'] != 'signin' && $skyuc->GPC ['act'] != 'forget_pwd' && $skyuc->GPC ['act'] != 'reset_pwd') {
	$checkpwd = 1; //非登陆页面，需验证权限
}
// 验证管理员身份
if ($checkpwd and (($skyuc->options ['timeoutcontrolpanel'] and ! $skyuc->session->vars ['loggedin']) or empty ( $skyuc->GPC [COOKIE_PREFIX . 'cpsession'] ) or $skyuc->GPC [COOKIE_PREFIX . 'cpsession'] != $cpsession ['hash'] or empty ( $cpsession ))) {
	// #############################################################################
	// Put in some auto-repair ;)
	$check = array ();

	$spectemps = $skyuc->db->query_read ( 'SELECT title FROM ' . TABLE_PREFIX . 'datastore' );
	while ( $spectemp = $skyuc->db->fetch_array ( $spectemps ) ) {
		$check ["$spectemp[title]"] = true;
	}
	$skyuc->db->free_result ( $spectemps );

	if (! $check ['mailqueue']) {
		build_datastore ( 'mailqueue' );
	}
	if (! $check ['loadcache']) {
		update_loadavg ();
	}
	if (! $check ['servers']) {
		build_datastore ( 'servers', '', 1 );
		build_servers ();
	}

	// end auto-repair
	// #############################################################################
	if (! empty ( $_REQUEST ['is_ajax'] )) {
		make_json_error ( $_LANG ['priv_error'] );
	} else {
		exec_header_redirect ( 'privilege.php?act=login' );
	}
}

/* 管理员登录后可在任何页面使用 act=phpinfo 显示 phpinfo() 信息 */
if ($skyuc->GPC ['act'] == 'phpinfo' && function_exists ( 'phpinfo' )) {
	phpinfo ();
	exit ();
}
?>