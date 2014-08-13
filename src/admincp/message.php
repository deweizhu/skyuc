<?php
/**
 * SKYUC! 管理中心管理员留言程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

/*------------------------------------------------------ */
//-- 留言列表页面
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list') {
	$smarty->assign ( 'full_page', 1 );
	$smarty->assign ( 'ur_here', $_LANG ['msg_list'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['send_msg'], 'href' => 'message.php?act=send' ) );

	$list = get_message_list ();

	$smarty->assign ( 'message_list', $list ['item'] );
	$smarty->assign ( 'filter', $list ['filter'] );
	$smarty->assign ( 'record_count', $list ['record_count'] );
	$smarty->assign ( 'page_count', $list ['page_count'] );

	$sort_flag = sort_flag ( $list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	assign_query_info ();
	$smarty->display ( 'message_list.tpl' );
}

/*------------------------------------------------------ */
//-- 翻页、排序
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$list = get_message_list ();

	$smarty->assign ( 'message_list', $list ['item'] );
	$smarty->assign ( 'filter', $list ['filter'] );
	$smarty->assign ( 'record_count', $list ['record_count'] );
	$smarty->assign ( 'page_count', $list ['page_count'] );

	$sort_flag = sort_flag ( $list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	make_json_result ( $smarty->fetch ( 'message_list.tpl' ), '', array ('filter' => $list ['filter'], 'page_count' => $list ['page_count'] ) );
}

/*------------------------------------------------------ */
//-- 留言发送页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'send') {
	// 获取管理员列表
	$admin_list = $db->query_all_slave ( 'SELECT user_id, user_name FROM ' . TABLE_PREFIX . 'admin' );

	$smarty->assign ( 'ur_here', $_LANG ['send_msg'] );
	$smarty->assign ( 'action_link', array ('href' => 'message.php?act=list', 'text' => $_LANG ['msg_list'] ) );
	$smarty->assign ( 'action', 'add' );
	$smarty->assign ( 'form_act', 'insert' );
	$smarty->assign ( 'admin_list', $admin_list );

	assign_query_info ();
	$smarty->display ( 'message_info.tpl' );
}

/*------------------------------------------------------ */
//-- 处理留言的发送
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'insert') {

	$skyuc->input->clean_array_gpc ( 'p', array ('receiver_id' => TYPE_ARRAY_UINT, 'title' => TYPE_STR, 'message' => TYPE_STR ) );

	$rec_arr = $skyuc->GPC ['receiver_id'];

	// 向所有管理员发送留言
	if ($rec_arr [0] == 0) {
		// 获取管理员信息
		$result = $db->query ( 'SELECT user_id FROM ' . TABLE_PREFIX . 'admin' );
		while ( $rows = $db->fetch_array ( $result ) ) {
			if ($skyuc->session->vars ['adminid'] != $rows ['user_id']) {
				$sql = 'INSERT INTO ' . TABLE_PREFIX . 'admin_message' . ' (sender_id, receiver_id, send_date, read_date, readed, deleted, title, message) ' . "	VALUES ('" . $skyuc->session->vars ['adminid'] . "', '" . $rows ['user_id'] . "', '" . TIMENOW . "', '0', '0', '0', '" . $db->escape_string ( $skyuc->GPC ['title'] ) . "', '" . $db->escape_string ( $skyuc->GPC ['message'] ) . "')";
				$db->query_write ( $sql );
			}
		}

		// 添加链接
		$link [0] ['text'] = $_LANG ['back_list'];
		$link [0] ['href'] = 'message.php?act=list';

		$link [1] ['text'] = $_LANG ['continue_send_msg'];
		$link [1] ['href'] = 'message.php?act=send';

		sys_msg ( $_LANG ['send_msg'] . "&nbsp;" . $_LANG ['action_succeed'], 0, $link );

		// 记录管理员操作
		admin_log ( $_LANG ['send_msg'], 'add', 'admin_message' );
	} else {
		// 如果是发送给指定的管理员
		foreach ( $rec_arr as $key => $id ) {
			$sql = 'INSERT INTO ' . TABLE_PREFIX . 'admin_message' . ' (sender_id, receiver_id, send_date, read_date, readed, deleted, title, message) ' . "	VALUES ('" . $skyuc->session->vars ['adminid'] . "', '" . $id . "', '" . TIMENOW . "', '0', '0', '0', '" . $db->escape_string ( $skyuc->GPC ['title'] ) . "', '" . $db->escape_string ( $skyuc->GPC ['message'] ) . "')";
			$db->query_write ( $sql );
		}
		admin_log ( $_LANG ['send_msg'], 'add', 'admin_message' );

		$link [0] ['text'] = $_LANG ['back_list'];
		$link [0] ['href'] = 'message.php?act=list';
		$link [1] ['text'] = $_LANG ['continue_send_msg'];
		$link [1] ['href'] = 'message.php?act=send';

		sys_msg ( $_LANG ['send_msg'] . "&nbsp;" . $_LANG ['action_succeed'], 0, $link );
	}
} /*------------------------------------------------------ */
//-- 留言编辑页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit') {
	$id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	// 获取管理员列表
	$admin_list = $db->query_all_slave ( 'SELECT user_id, user_name FROM ' . TABLE_PREFIX . 'admin' );

	// 获得留言数据
	$sql = 'SELECT message_id, receiver_id, title, message' . '	FROM ' . TABLE_PREFIX . 'admin_message' . ' WHERE message_id=' . $id;
	$msg_arr = $db->query_first_slave ( $sql );

	$smarty->assign ( 'ur_here', $_LANG ['edit_msg'] );
	$smarty->assign ( 'action_link', array ('href' => 'message.php?act=list', 'text' => $_LANG ['msg_list'] ) );
	$smarty->assign ( 'form_act', 'update' );
	$smarty->assign ( 'admin_list', $admin_list );
	$smarty->assign ( 'msg_arr', $msg_arr );

	assign_query_info ();
	$smarty->display ( 'message_info.tpl' );
} elseif ($skyuc->GPC ['act'] == 'update') {
	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'title' => TYPE_STR, 'message' => TYPE_STR ) );

	// 获得留言数据
	$msg_arr = array ();
	$msg_arr = $db->query_first_slave ( 'SELECT * FROM ' . TABLE_PREFIX . 'admin_message' . ' WHERE message_id=' . $skyuc->GPC ['id'] );

	$sql = 'UPDATE ' . TABLE_PREFIX . 'admin_message' . ' SET ' . "	title = '" . $db->escape_string ( $skyuc->GPC ['title'] ) . "'," . "	message = '" . $db->escape_string ( $skyuc->GPC ['message'] ) . "'" . '	WHERE sender_id = ' . $msg_arr ['sender_id'] . ' AND send_date=' . $msg_arr ['send_date'] . "'";
	$db->query_write ( $sql );

	$link [0] ['text'] = $_LANG ['back_list'];
	$link [0] ['href'] = 'message.php?act=list';

	sys_msg ( $_LANG ['edit_msg'] . ' ' . $_LANG ['action_succeed'], 0, $link );

	// 记录管理员操作
	admin_log ( $_LANG ['edit_msg'], 'edit', 'admin_message' );
}

/*------------------------------------------------------ */
//-- 留言查看页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'view') {
	$msg_id = $skyuc->input->clean_gpc ( 'r', 'id', TYPE_UINT );

	// 获得管理员留言数据
	$msg_arr = array ();
	$sql = 'SELECT a.*, b.user_name ' . '	FROM ' . TABLE_PREFIX . 'admin_message' . ' AS a ' . '	LEFT JOIN ' . TABLE_PREFIX . 'admin' . ' AS b ON b.user_id = a.sender_id ' . '	WHERE a.message_id = ' . $msg_id;
	$msg_arr = $db->query_first_slave ( $sql );
	$msg_arr ['title'] = nl2br ( htmlspecialchars ( $msg_arr ['title'] ) );
	$msg_arr ['message'] = nl2br ( htmlspecialchars ( $msg_arr ['message'] ) );
	$msg_arr ['send_date'] = skyuc_date ( $skyuc->options ['date_format'] . ' ' . $skyuc->options ['time_format'], $msg_arr ['send_date'] );

	// 如果还未阅读
	if ($msg_arr ['readed'] == 0) {
		// 阅读日期为当前日期
		$msg_arr ['read_date'] = skyuc_date ( $skyuc->options ['date_format'] . ' ' . $skyuc->options ['time_format'], TIMENOW );

		// 更新阅读日期和阅读状态
		$sql = 'UPDATE ' . TABLE_PREFIX . 'admin_message' . ' SET ' . '	read_date = ' . TIMENOW . ', ' . '	readed = 1 ' . '	WHERE message_id = ' . $msg_id;
		$db->query_write ( $sql );
	}

	//模板赋值，显示
	$smarty->assign ( 'ur_here', $_LANG ['view_msg'] );
	$smarty->assign ( 'action_link', array ('href' => 'message.php?act=list', 'text' => $_LANG ['msg_list'] ) );
	$smarty->assign ( 'admin', fetch_adminutil_text ( $skyuc->session->vars ['adminid'] . '_admin_name' ) );
	$smarty->assign ( 'msg_arr', $msg_arr );

	assign_query_info ();
	$smarty->display ( 'message_view.tpl' );
}

/*------------------------------------------------------ */
//--留言回复页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'reply') {
	$msg_id = $skyuc->input->clean_gpc ( 'r', 'id', TYPE_UINT );

	// 获得留言数据
	$msg_val = array ();
	$sql = 'SELECT a.*, b.user_name ' . '	FROM ' . TABLE_PREFIX . 'admin_message' . ' AS a ' . '	LEFT JOIN ' . TABLE_PREFIX . 'admin' . ' AS b ON b.user_id = a.sender_id ' . '	WHERE a.message_id = ' . $msg_id;
	$msg_val = $db->query_first_slave ( $sql );

	$smarty->assign ( 'ur_here', $_LANG ['reply_msg'] );
	$smarty->assign ( 'action_link', array ('href' => 'message.php?act=list', 'text' => $_LANG ['msg_list'] ) );

	$smarty->assign ( 'action', 'reply' );
	$smarty->assign ( 'form_act', 're_msg' );
	$smarty->assign ( 'msg_val', $msg_val );

	assign_query_info ();
	$smarty->display ( 'message_info.tpl' );
}

/*------------------------------------------------------ */
//--留言回复的处理
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 're_msg') {
	$skyuc->input->clean_array_gpc ( 'p', array ('receiver_id' => TYPE_UINT, 'title' => TYPE_STR, 'message' => TYPE_STR ) );
	$sql = 'INSERT INTO ' . TABLE_PREFIX . 'admin_message' . ' (sender_id, receiver_id, send_date, read_date, readed, deleted, title, message) ' . "	VALUES ('" . $skyuc->session->vars ['adminid'] . "', '" . $skyuc->GPC ['receiver_id'] . "', '" . TIMENOW . "', '0', '0', '0', '" . $db->escape_string ( $skyuc->GPC ['title'] ) . "', '" . $db->escape_string ( $skyuc->GPC ['message'] ) . "')";
	$db->query_write ( $sql );

	$link [0] ['text'] = $_LANG ['back_list'];
	$link [0] ['href'] = 'message.php?act=list';

	sys_msg ( $_LANG ['send_msg'] . ' ' . $_LANG ['action_succeed'], 0, $link );

	// 记录管理员操作
	admin_log ( $_LANG ['send_msg'], 'add', 'admin_message' );
}

/*------------------------------------------------------ */
//-- 批量删除留言记录
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'drop_msg') {
	$skyuc->input->clean_gpc ( 'p', 'checkboxes', TYPE_ARRAY_UINT );
	if ($skyuc->GPC_exists ['checkboxes']) {
		$count = 0;
		foreach ( $skyuc->GPC ['checkboxes'] as $key => $id ) {
			$sql = 'UPDATE ' . TABLE_PREFIX . 'admin_message' . ' SET deleted = 1' . '	WHERE message_id = ' . $id;
			$db->query_write ( $sql );
			$count ++;
		}

		admin_log ( '', 'remove', 'admin_message' );
		$link [] = array ('text' => $_LANG ['back_list'], 'href' => 'message.php?act=list' );
		sys_msg ( sprintf ( $_LANG ['batch_drop_success'], $count ), 0, $link );
	} else {
		sys_msg ( $_LANG ['no_select_msg'], 1 );
	}
}

/*------------------------------------------------------ */
//-- 删除留言
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'remove') {
	$id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	$sql = 'UPDATE ' . TABLE_PREFIX . 'admin_message' . ' SET deleted=1 ' . ' WHERE message_id= ' . $id . ' AND receiver_id=' . $skyuc->session->vars ['adminid'];
	$db->query_write ( $sql );


	$url = 'message.php?act=query&' . str_replace ( 'act=remove', '', $_SERVER ['QUERY_STRING'] );

	header ( "Location: $url\n" );
	exit ();
}

?>