<?php
/**
 * SKYUC! 管理中心用户评论管理程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

/*------------------------------------------------------ */
//-- 获取没有回复的评论列表
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list' || $skyuc->GPC ['act'] == '') {
	$smarty->assign ( 'ur_here', $_LANG ['05_comment_manage'] );
	$smarty->assign ( 'full_page', 1 );

	$list = get_comment_list ();

	$smarty->assign ( 'comment_list', $list ['item'] );
	$smarty->assign ( 'filter', $list ['filter'] );
	$smarty->assign ( 'record_count', $list ['record_count'] );
	$smarty->assign ( 'page_count', $list ['page_count'] );

	$sort_flag = sort_flag ( $list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	assign_query_info ();
	$smarty->display ( 'comment_list.tpl' );
}

/*------------------------------------------------------ */
//-- 翻页、搜索、排序
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'query') {
	$list = get_comment_list ();

	$smarty->assign ( 'comment_list', $list ['item'] );
	$smarty->assign ( 'filter', $list ['filter'] );
	$smarty->assign ( 'record_count', $list ['record_count'] );
	$smarty->assign ( 'page_count', $list ['page_count'] );

	$sort_flag = sort_flag ( $list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	make_json_result ( $smarty->fetch ( 'comment_list.tpl' ), '', array ('filter' => $list ['filter'], 'page_count' => $list ['page_count'] ) );
}

/*------------------------------------------------------ */
//-- 回复用户评论(同时查看评论详情)
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'reply') {
	// 检查权限
	admin_priv ( 'comment_priv' );

	$comment_info = array ();
	$reply_info = array ();
	$id_value = array ();

	$skyuc->input->clean_gpc ( 'r', 'id', TYPE_UINT );

	// 获取评论详细信息并进行字符处理
	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'comment' . ' WHERE comment_id = ' . $skyuc->GPC ['id'];
	$comment_info = $db->query_first ( $sql );
	$comment_info ['content'] = nl2br ( htmlspecialchars ( $comment_info ['content'] ) );
	$comment_info ['add_time'] = skyuc_date ( $skyuc->options ['date_format'] . ' ' . $skyuc->options ['time_format'], $comment_info ['add_time'] );

	// 获得评论回复内容
	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'comment' . ' WHERE status = 2 and parent_id = ' . $skyuc->GPC ['id'];
	$reply_info = $db->query_first ( $sql );
	if (empty ( $reply_info )) {
		$reply_info ['content'] = '';
		$reply_info ['add_time'] = '';
	} else {
		$reply_info ['content'] = nl2br ( htmlspecialchars ( $reply_info ['content'] ) );
		$reply_info ['add_time'] = skyuc_date ( $skyuc->options ['date_format'] . ' ' . $skyuc->options ['time_format'], $reply_info ['add_time'] );
	}
	// 获取管理员的用户名和Email地址
	$sql = 'SELECT user_name, email FROM ' . TABLE_PREFIX . 'admin' . ' WHERE user_id = ' . $skyuc->session->vars ['adminid'];
	$admin_info = $db->query_first ( $sql );

	// 取得评论的对象(文章或者影片)
	if ($comment_info ['comment_type'] == 0) {
		$sql = 'SELECT title FROM ' . TABLE_PREFIX . 'show' . ' WHERE show_id = ' . $comment_info ['id_value'];
	} else {
		$sql = 'SELECT title FROM ' . TABLE_PREFIX . 'article' . ' WHERE article_id=' . $comment_info ['id_value'];
	}
	$title = $db->query_first ( $sql );
	$id_value = $title ['title'];

	// 模板赋值
	$smarty->assign ( 'msg', $comment_info ); //评论信息
	$smarty->assign ( 'admin_info', $admin_info ); //管理员信息
	$smarty->assign ( 'reply_info', $reply_info ); //回复的内容
	$smarty->assign ( 'id_value', $id_value ); //评论的对象


	$smarty->assign ( 'ur_here', $_LANG ['comment_info'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['05_comment_manage'], 'href' => 'comment_manage.php?act=list' ) );

	assign_query_info ();
	$smarty->display ( 'comment_info.tpl' );
}
/*------------------------------------------------------ */
//-- 处理 回复用户评论
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'action') {
	admin_priv ( 'comment_priv' );

	$skyuc->input->clean_array_gpc ( 'p', array ('comment_id' => TYPE_UINT, 'email' => TYPE_STR, 'user_name' => TYPE_STR, 'content' => TYPE_STR, 'comment_type' => TYPE_UINT, 'id_value' => TYPE_UINT ) );

	// 获得评论是否有回复
	$sql = 'SELECT comment_id, content, parent_id FROM ' . TABLE_PREFIX . 'comment' . ' WHERE status=2 and parent_id
	= ' . $skyuc->GPC ['comment_id'];
	$reply_info = $db->query_first ( $sql );
	if (! empty ( $reply_info ['content'] )) {
		// 更新回复的内容
		$sql = 'UPDATE ' . TABLE_PREFIX . 'comment' . ' SET ' . " email     = '" . $db->escape_string ( $skyuc->GPC
        ['email'] ) . "', " . " user_name = '" . $db->escape_string ( $skyuc->GPC ['user_name'] ) . "',
        " . " content   = '" . $db->escape_string ( $skyuc->GPC ['content'] ) . "', " . " add_time  =  '" . TIMENOW . "', " . " ip_address= '" . ALT_IP . "', " . ' status    = 2' . ' WHERE comment_id = ' . $reply_info ['comment_id'];
	} else {
		$admin_name = fetch_adminutil_text ( $skyuc->session->vars ['adminid'] . '_admin_name' );
		// 插入回复的评论内容
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'comment' . ' (comment_type, id_value, email, user_name , content, add_time, ip_address, status, parent_id) ' . " VALUES('" . $skyuc->GPC ['comment_type'] . "', '" . $skyuc->GPC ['id_value'] . "','" . $db->escape_string ( $skyuc->GPC ['email'] ) . "',
               '" . $db->escape_string ( $admin_name ) . "','" . $db->escape_string ( $skyuc->GPC ['content'] ) . "',
               '" . TIMENOW . "', '" . ALT_IP . "', '2', '" . $skyuc->GPC ['comment_id'] . "')";
	}
	$db->query_write ( $sql );

	// 更新当前的评论状态为已回复并且可以显示此条评论
	$sql = 'UPDATE ' . TABLE_PREFIX . 'comment' . ' SET status = 1 WHERE comment_id = ' . $skyuc->GPC ['comment_id'];
	$db->query_write ( $sql );

	// 记录管理员操作
	admin_log ( $_LANG ['reply'], 'edit', 'users_comment' );

	$url = 'comment_manage.php?act=reply&id=' . $skyuc->GPC ['comment_id'];
	header ( "Location: $url\n" );
	exit ();
}
/*------------------------------------------------------ */
//-- 更新评论的状态为显示或者禁止
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'check') {
	$skyuc->input->clean_array_gpc ( 'r', array ('check' => TYPE_STR, 'id' => TYPE_UINT ) );

	if ($skyuc->GPC ['check'] == 'allow') {
		// 允许评论显示
		$sql = 'UPDATE ' . TABLE_PREFIX . 'comment' . ' SET status = 1 WHERE comment_id = ' . $skyuc->GPC ['id'];
		$db->query_write ( $sql );
	} else {
		// 禁止评论显示
		$sql = 'UPDATE ' . TABLE_PREFIX . 'comment' . ' SET status = 0 WHERE comment_id = ' . $skyuc->GPC ['id'];
		$db->query_write ( $sql );
	}
	$url = 'comment_manage.php?act=reply&id=' . $skyuc->GPC ['id'];
	header ( "Location: $url\n" );
	exit ();
}

/*------------------------------------------------------ */
//-- 删除某一条评论
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'remove') {
	check_authz_json ( 'comment_priv' );

	$id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	$res = $db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'comment' . ' WHERE comment_id = ' . $id );
	if ($res) {
		$db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'comment' . ' WHERE parent_id = ' . $id );
	}

	admin_log ( '', 'remove', 'users_comment' );

	$url = 'comment_manage.php?act=query&' . str_replace ( 'act=remove', '', $_SERVER ['QUERY_STRING'] );

	header ( "Location: $url\n" );
	exit ();
}

/*------------------------------------------------------ */
//-- 批量删除用户评论
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'batch') {
	admin_priv ( 'comment_priv' );

	$skyuc->input->clean_array_gpc ( 'p', array ('checkboxes' => TYPE_ARRAY_UINT, 'sel_action' => TYPE_STR ) );

	$action = iif ( $skyuc->GPC_exists ['sel_action'], $skyuc->GPC ['sel_action'], 'deny' );

	if ($skyuc->GPC_exists ['checkboxes']) {
		switch ($action) {
			case 'remove' :

				$db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'comment' . ' WHERE ' . db_create_in ( $skyuc->GPC ['checkboxes'], 'comment_id' ) );
				$db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'comment' . ' WHERE ' . db_create_in ( $skyuc->GPC ['checkboxes'], 'parent_id' ) );

				break;
			case 'allow' :

				$db->query_write ( 'UPDATE ' . TABLE_PREFIX . 'comment' . ' SET status = 1  WHERE ' . db_create_in ( $skyuc->GPC ['checkboxes'], 'comment_id' ) );

				break;
			case 'deny' :

				$db->query_write ( 'UPDATE ' . TABLE_PREFIX . 'comment' . ' SET status = 0  WHERE ' . db_create_in ( $skyuc->GPC ['checkboxes'], 'comment_id' ) );

				break;
			default :
				break;
		}

		$action = ($action == 'remove') ? 'remove' : 'edit';
		admin_log ( '', $action, 'users_comment' );

		$link [] = array ('text' => $_LANG ['back_list'], 'href' => 'comment_manage.php?act=list' );
		sys_msg ( sprintf ( $_LANG ['batch_drop_success'], count ( $skyuc->GPC ['checkboxes'] ) ), 0, $link );
	} else {
		// 提示信息
		$link [] = array ('text' => $_LANG ['back_list'], 'href' => 'comment_manage.php?act=list' );
		sys_msg ( $_LANG ['no_select_comment'], 0, $link );
	}
}

?>