<?php
/**
 * SKYUC! 会员留言
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

//初始化数据交换对象
$exc = new exchange ( TABLE_PREFIX . 'feedback', $skyuc->db, 'msg_id', 'msg_title' );

/*------------------------------------------------------ */
//-- 列出所有留言
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list_all') {
	$msg_list = get_msg_list ();

	$smarty->assign ( 'msg_list', $msg_list ['msg_list'] );
	$smarty->assign ( 'filter', $msg_list ['filter'] );
	$smarty->assign ( 'record_count', $msg_list ['record_count'] );
	$smarty->assign ( 'page_count', $msg_list ['page_count'] );
	$smarty->assign ( 'full_page', 1 );
	$smarty->assign ( 'sort_msg_id', '<img src="images/sort_desc.gif">' );

	$smarty->assign ( 'ur_here', $_LANG ['list_all'] );
	$smarty->assign ( 'full_page', 1 );

	assign_query_info ();
	$smarty->display ( 'msg_list.tpl' );
}

/*------------------------------------------------------ */
//-- ajax显示留言列表
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$msg_list = get_msg_list ();

	$smarty->assign ( 'msg_list', $msg_list ['msg_list'] );
	$smarty->assign ( 'filter', $msg_list ['filter'] );
	$smarty->assign ( 'record_count', $msg_list ['record_count'] );
	$smarty->assign ( 'page_count', $msg_list ['page_count'] );

	$sort_flag = sort_flag ( $msg_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	make_json_result ( $smarty->fetch ( 'msg_list.tpl' ), '', array ('filter' => $msg_list ['filter'], 'page_count' => $msg_list ['page_count'] ) );
} /*------------------------------------------------------ */
//-- ajax 删除留言
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'remove') {
	// 检查权限
	check_authz_json ( 'feedback_priv' );

	$msg_id = $skyuc->input->clean_gpc ( 'r', 'id', TYPE_UINT );

	$msg_title = $exc->get_name ( $msg_id );
	$img = $exc->get_name ( $msg_id, 'message_img' );
	if ($exc->drop ( $msg_id )) {
		// 删除图片
		if (! empty ( $img )) {
			@unlink ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/feedbackimg/' . $img );
		}
		$sql = 'DELETE FROM ' . TABLE_PREFIX . 'feedback' . ' WHERE parent_id = ' . $msg_id;
		$skyuc->db->query_write ( $sql );

		$skyuc->secache->setModified ( 'message_board.dwt' );
		admin_log ( $msg_title, 'remove', 'message' );
		$url = 'user_msg.php?act=query&' . str_replace ( 'act=remove', '', $_SERVER ['QUERY_STRING'] );
		header ( "Location: $url\n" );
		exit ();
	} else {
		make_json_error ( $skyuc->db->error () );
	}
} /*------------------------------------------------------ */
//-- 回复留言
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'view') {
	// 权限判断
	admin_priv ( 'feedback_priv' );

	$skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	$smarty->assign ( 'msg', get_feedback_detail ( $skyuc->GPC ['id'] ) );
	$smarty->assign ( 'ur_here', $_LANG ['reply'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['04_user_msg'], 'href' => 'user_msg.php?act=list_all' ) );

	assign_query_info ();
	$smarty->display ( 'msg_info.tpl' );
} elseif ($skyuc->GPC ['act'] == 'action') {
	// 权限判断
	admin_priv ( 'feedback_priv' );

	$skyuc->input->clean_array_gpc ( 'r', array ('parent_id' => TYPE_UINT, 'msg_id' => TYPE_UINT, 'user_email' => TYPE_STR, 'msg_content' => TYPE_STR ) );

	$skyuc->secache->setModified ( 'message_board.dwt' );
	if (empty ( $skyuc->GPC ['parent_id'] )) {
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'feedback' . ' (msg_title, msg_time, user_id, user_name ,user_email, parent_id, msg_content) ' . " VALUES('reply', '" . TIMENOW . "', '" . $skyuc->session->vars ['adminid'] . "', '" . $skyuc->session->vars ['admin_name'] . "', '" . $db->escape_string ( $skyuc->GPC ['user_email'] ) . "', '" . $skyuc->GPC ['msg_id'] . "', '" . $db->escape_string ( $skyuc->GPC ['msg_content'] ) . "')";
		$skyuc->db->query_write ( $sql );
		header ( "Location: ?act=view&id=" . $skyuc->GPC ['msg_id'] . "&reply=1\n" );
		exit ();

	} else {
		$sql = 'UPDATE ' . TABLE_PREFIX . 'feedback' . " SET user_email = '" . $db->escape_string ( $skyuc->GPC ['user_email'] ) . "', msg_content='" . $db->escape_string ( $skyuc->GPC ['msg_content'] ) . "', msg_time = '" . TIMENOW . "' WHERE msg_id = '" . $skyuc->GPC ['parent_id'] . "'";
		$skyuc->db->query_write ( $sql );
		header ( "Location: ?act=view&id=" . $skyuc->GPC ['msg_id'] . "&reply=1\n" );
		exit ();
	}
}

/*------------------------------------------------------ */
//-- 删除会员上传的文件
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'drop_file') {
	// 权限判断
	admin_priv ( 'feedback_priv' );

	$skyuc->input->clean_array_gpc ( 'g', array ('file' => TYPE_STR, 'id' => TYPE_UINT ) );

	// 删除上传的文件
	@unlink ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/feedbackimg/' . $skyuc->GPC ['file'] );

	// 更新数据库
	$skyuc->db->query_write ( 'UPDATE ' . TABLE_PREFIX . 'feedback' . " SET message_img = '' WHERE msg_id = '" . $skyuc->GPC ['id'] . "'" );

	header ( "Location: user_msg.php?act=view&id=" . $skyuc->GPC ['id'] . "\n" );
	exit ();
}

?>