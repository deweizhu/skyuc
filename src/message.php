<?php
/**
 * SKYUC! 留言板
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
define ( 'THIS_SCRIPT', 'message' );
define ( 'CSRF_PROTECTION', true );
define ( 'CSRF_SKIP_LIST', '' );
define ( 'SMARTY_CACHE', true );

require (dirname ( __FILE__ ) . '/global.php');
require (DIR . '/includes/control/message.php');

if (empty ( $skyuc->options ['message_board'] )) {
	show_message ( $_LANG ['message_board_close'] );
}

$skyuc->input->clean_gpc ( 'p', 'act', TYPE_STR );
$action = iif ( $skyuc->GPC_exists ['act'], $skyuc->GPC ['act'], 'default' );
if ($action == 'act_add_message') {
	// 载入语言文件
	require_once (DIR . '/languages/' . $skyuc->options ['lang'] . '/user.php');
	// 验证码防止灌水刷屏
	if (intval ( $skyuc->options ['humanverify'] ) & HV_MESSAGE) {
		// 检查验证码是否正确
		$skyuc->input->clean_gpc ( 'p', 'humanverify', TYPE_ARRAY_STR );

		require_once (DIR . '/includes/class_humanverify.php');
		$verify = & HumanVerify::fetch_library ( $skyuc );
		if (! $verify->verify_token ( $skyuc->GPC ['humanverify'] )) {
			show_message ( $_LANG ['invalid_captcha'] );
		}
	} else {
		// 没有验证码时，用时间来限制机器人发帖或恶意发评论
		if (! isset ( $_COOKIE [COOKIE_PREFIX . 'send_time'] )) {
			$send_time = 0;
		} else {
			$send_time = intval ( $_COOKIE [COOKIE_PREFIX . 'send_time'] );
		}
		if ((TIMENOW - $send_time) < 30) // 小于30秒禁止发评论
{
			show_message ( $_LANG ['cmt_spam_warning'] );
		}
	}
	$user_name = '';
	$skyuc->input->clean_array_gpc ( 'p', array ('anonymous' => TYPE_STR, 'user_name' => TYPE_STR, 'user_email' => TYPE_STR, 'msg_type' => TYPE_UINT, 'msg_title' => TYPE_STR, 'msg_content' => TYPE_STR ) );

	if (empty ( $skyuc->GPC ['anonymous'] ) && ! empty ( $skyuc->userinfo ['user_name'] )) {
		$user_name = $skyuc->userinfo ['user_name'];
	} elseif (! empty ( $skyuc->GPC ['anonymous'] ) && ! $skyuc->GPC_exists ['user_name']) {
		$user_name = $_LANG ['anonymous'];
	} elseif (empty ( $skyuc->GPC ['user_name'] )) {
		$user_name = $_LANG ['anonymous'];
	} else {
		$user_name = htmlspecialchars ( $skyuc->GPC ['user_name'] );
	}

	$message = array ('user_id' => $skyuc->session->vars ['userid'], 'user_name' => $user_name, 'user_email' => iif ( $skyuc->GPC_exists ['user_email'], htmlspecialchars ( $skyuc->GPC ['user_email'] ), '' ), 'msg_type' => $skyuc->GPC ['msg_type'], 'msg_title' => $skyuc->GPC ['msg_title'], 'msg_content' => $skyuc->GPC ['msg_content'], 'msg_area' => 1, 'upload' => array () );

	if (add_message ( $message )) {
		//新留言成功，把留言板的缓存清空
		$skyuc->secache->setModified ( 'message_board.dwt' );

		skyuc_setcookie ( 'send_time', TIMENOW + 30, TIMENOW + 3600 );

		show_message ( $_LANG ['add_message_success'], $_LANG ['message_list_lnk'], 'message.php' );
	} else {
		$err->show ( $_LANG ['message_list_lnk'], 'message.php' );
	}
} elseif ($action == 'default') {
    $page = $skyuc->input->clean_gpc ( 'r', 'page', TYPE_UNUM );
	$cache_id = sprintf ( '%X', crc32 ( $page ) );
	/*------------------------------------------------------ */
	//-- 判断是否存在缓存，如果存在则调用缓存，反之读取相应内容
	/*------------------------------------------------------ */
	if (! $smarty->is_cached ( 'message_board.dwt', $cache_id )) {
		assign_template ();
		$position = assign_ur_here ( 0, $_LANG ['message_board'] );
		$smarty->assign ( 'page_title', $position ['title'] ); // 页面标题
		$smarty->assign ( 'ur_here', $position ['ur_here'] ); // 当前位置


		if (! empty ( $skyuc->template ['message_board'] ['tree_cate'] )) {
			$smarty->assign ( 'categories', get_categories_tree () ); // 影片分类树
			$smarty->assign ( 'area_list', explode ( '|', $skyuc->options ['show_area'] ) ); // 地区分类树
			$smarty->assign ( 'lang_list', explode ( '|', $skyuc->options ['show_lang'] ) ); // 语言分类树
		}

		if (! empty ( $skyuc->template ['message_board'] ['top10_message'] )) {
			$smarty->assign ( 'top_month', get_top_new_hot ( 'top_cate', $children, 30 ) ); // 月点播排行
		}
		if (! empty ( $skyuc->template ['message_board'] ['new10_message'] )) {
			$smarty->assign ( 'new_show', get_top_new_hot ( 'new' ) ); // 最近更新影片
		}
		if (intval ( $skyuc->options ['humanverify'] ) & HV_MESSAGE) {
			$smarty->assign ( 'enabled_captcha', 1 );
		}

		// 获取留言的数量
		$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'feedback' . ' WHERE msg_area = 1';
		$total = $db->query_first_slave ( $sql );
		$record_count = $total ['total'];

		$skyuc->input->clean_gpc ( 'r', 'page', TYPE_UINT );

		$pagesize = get_library_number ( 'message_list', 'message_board' );
		$pager = get_pager ( 'message.php', array (), $record_count, $page, $pagesize );
		$msg_lists = get_msg_board_list ( $pagesize, $pager ['start'] );

		$smarty->assign ( 'msg_lists', $msg_lists );
		$smarty->assign ( 'pager', $pager );

	}
	$smarty->display ( 'message_board.dwt', $cache_id );
}

?>
