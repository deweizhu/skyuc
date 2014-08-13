<?php
/**
 * SKYUC 全局入口
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

// 标志我们在哪里
define ( 'SKYUC_AREA', 'IN_SKYUC' );

define ( 'CWD', (($getcwd = getcwd ()) ? $getcwd : '.') );

// #############################################################################
// 开始初始化
require (CWD . '/includes/init.php');
require (DIR . '/includes/functions_main.php');
require (DIR . '/languages/' . $skyuc->options ['lang'] . '/common.php');
// 创建错误处理对象
$err = new skyuc_error ( 'message.dwt' );

//使 $_LANG 成为 $skyuc 的成员函数
$skyuc->lang = & $_LANG;

// 如果没有定义不使用Smarty则初始化Smarty
if (! defined ( 'SKIP_SMARTY' )) {
	if (empty ( $db->explain )) {
		header ( 'Cache-control: private' );
		header ( 'content-type: text/html; charset=utf-8' );
	}

	// 创建 Smarty 对象。
	require (DIR . '/includes/class_template.php');
	$smarty = new Template ();

	$smarty->cache_lifetime = $skyuc->options ['cache_time'] * 3600;
	$smarty->template_dir = DIR . '/templates/' . $skyuc->options ['themes'];
	$smarty->cache_dir = DIR . '/data/caches';
	$smarty->compile_dir = DIR . '/data/compiled';

	//定义了缓存
	if (defined ( 'SMARTY_CACHE' )) {
		$smarty->caching = TRUE;
	}

	$smarty->assign ( 'lang', $_LANG );

	$template_session = array ('sessionhash' => $skyuc->session->vars ['sessionhash'], 'sessionurl' => $skyuc->session->vars ['sessionurl'], 'sessionurl_q' => $skyuc->session->vars ['sessionurl_q'], 'sessionurl_js' => $skyuc->session->vars ['sessionurl_js'] );

	$smarty->assign ( 'session', $template_session );
	if (! empty ( $skyuc->options ['stylename'] )) {
		$smarty->assign ( 'skyuc_css_path', 'templates/' . $skyuc->options ['themes'] . '/style_' . $skyuc->options ['stylename'] . '.css' );
	} else {
		$smarty->assign ( 'skyuc_css_path', 'templates/' . $skyuc->options ['themes'] . '/style.css' );
	}
}

$skyuc->input->clean_array_gpc ( 'r', array ('id' => TYPE_UINT ) );

$skyuc->input->clean_array_gpc ( 'p', array ('is_ajax' => TYPE_BOOL ) );

// 网站关闭了，输出关闭的消息
if ($skyuc->options ['site_closed'] == 1 AND THIS_SCRIPT !='image') {
	header ( 'Content-type: text/html; charset=utf-8' );
	die ( '<div style="margin: 150px; text-align: center; font-size: 14px"><p>' . $_LANG ['site_closed'] . '</p><p>' . $skyuc->options ['close_comment'] . '</p></div>' );
}

// #############################################################################
//	防止CC攻击:开始
block_cc ();
// 防止CC攻击:结束


// ########################## 获取 日期/时间 信息 ###################################################
fetch_time_data ();

// ###################### 检查封禁IP #######################################################
verify_ip_ban ();

// ######################### 初始化会员整合信息 ###################################################
if (! defined ( 'SKIP_SESSIONCREATE' ) && ! defined ( 'SKIP_USERINFO' ) && SKYUC_AREA == 'IN_SKYUC') {
	// 初始化会员数据整合类
	$user = & init_users ();

	if (! defined ( 'INGORE_VISIT_STATS' )) {
		visit_stats ();
	}

	// 如果会员没有登录，但是存在有效COOKIE
	if ($skyuc->session->vars ['userid'] == 0) {
		if ($user->get_cookie ()) {
			if ($skyuc->session->vars ['userid'] > 0) {
				include_once (DIR . '/includes/functions_users.php');
				update_user_info ();
			}

		} else {
			$skyuc->session->set ( 'userid', 0 );
		}
	}
}

if (! empty ( $db->explain )) {
	$aftertime = microtime ( true ) - TIMESTART;
	echo "End call of global.php: $aftertime\n";
	echo "\n<hr />\n\n";
}
?>
