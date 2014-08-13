<?php

/**
 * SKYUC! 管理中心服务器管理
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

$exc = new exchange ( TABLE_PREFIX . 'server', $skyuc->db, 'server_id', 'server_name' );

/*------------------------------------------------------ */
//-- 服务器列表
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list') {
	$smarty->assign ( 'ur_here', $_LANG ['05_server'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['07_server_add'], 'href' => 'server.php?act=add' ) );
	$smarty->assign ( 'full_page', 1 );

	$server_list = get_serverlist ();

	$smarty->assign ( 'server_list', $server_list ['server'] );
	$smarty->assign ( 'filter', $server_list ['filter'] );
	$smarty->assign ( 'record_count', $server_list ['record_count'] );
	$smarty->assign ( 'page_count', $server_list ['page_count'] );

	assign_query_info ();
	$smarty->display ( 'server_list.tpl' );
}

/*------------------------------------------------------ */
//-- 添加服务器
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'add') {
	// 权限判断
	admin_priv ( 'server_manage' );

	$smarty->assign ( 'ur_here', $_LANG ['07_server_add'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['05_server'], 'href' => 'server.php?act=list' ) );
	$smarty->assign ( 'form_action', 'insert' );
	$smarty->assign ( 'server', array ('sort_order' => 0, 'is_show' => 1 ) );

	assign_query_info ();
	$smarty->display ( 'server_info.tpl' );
} elseif ($skyuc->GPC ['act'] == 'insert') {
	//检查服务器名是否重复
	admin_priv ( 'server_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('is_show' => TYPE_BOOL, 'server_name' => TYPE_STR, 'server_desc' => TYPE_STR, 'server_url' => TYPE_STR, 'sort_order' => TYPE_UINT ) );

	$is_only = $exc->is_only ( 'server_name', $skyuc->GPC ['server_name'] );

	if (! $is_only) {
		sys_msg ( sprintf ( $_LANG ['servername_exist'], $skyuc->GPC ['server_name'] ), 1 );
	}

	// 插入数据
	$sql = 'INSERT INTO ' . TABLE_PREFIX . 'server' . ' (server_name, server_url, server_desc,  is_show, sort_order) ' . " VALUES ('" . $db->escape_string ( $skyuc->GPC ['server_name'] ) . "', '" . $db->escape_string ( $skyuc->GPC ['server_url'] ) . "', '" . $db->escape_string ( $skyuc->GPC ['server_desc'] ) . "', '" . $skyuc->GPC ['is_show'] . "', '" . $skyuc->GPC ['sort_order'] . "')";
	$db->query_write ( $sql );

	admin_log ( $skyuc->GPC ['server_name'], 'add', 'server' );

	build_servers (); //重建服务器缓存


	$link [0] ['text'] = $_LANG ['continue_add'];
	$link [0] ['href'] = 'server.php?act=add';

	$link [1] ['text'] = $_LANG ['back_list'];
	$link [1] ['href'] = 'server.php?act=list';

	sys_msg ( $_LANG ['serveradd_succed'], 0, $link );
}

/*------------------------------------------------------ */
//-- 编辑服务器
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit') {
	// 权限判断
	admin_priv ( 'server_manage' );

	$skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	$sql = 'SELECT *  FROM ' . TABLE_PREFIX . 'server' . ' WHERE server_id=' . $skyuc->GPC ['id'];
	$server = $db->query_first ( $sql );

	$smarty->assign ( 'ur_here', $_LANG ['server_edit'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['05_server'], 'href' => 'server.php?act=list' ) );
	$smarty->assign ( 'server', $server );
	$smarty->assign ( 'form_action', 'updata' );

	assign_query_info ();
	$smarty->display ( 'server_info.tpl' );
} elseif ($skyuc->GPC ['act'] == 'updata') {
	admin_priv ( 'server_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('is_show' => TYPE_BOOL, 'server_name' => TYPE_STR, 'old_servername' => TYPE_STR, 'server_desc' => TYPE_STR, 'server_url' => TYPE_STR, 'sort_order' => TYPE_UINT, 'id' => TYPE_UINT ) );

	if ($skyuc->GPC ['server_name'] != $_POST ['old_servername']) {
		//检查服务器名是否相同
		$is_only = $exc->is_only ( 'server_name', $skyuc->GPC ['server_name'], $skyuc->GPC ['id'] );

		if (! $is_only) {
			sys_msg ( sprintf ( $_LANG ['servername_exist'], $skyuc->GPC ['server_name'] ), 1 );
		}
	}

	// 处理更新
	$param = "server_name = '" . $db->escape_string ( $skyuc->GPC ['server_name'] ) . "',  server_url='" . $db->escape_string ( $skyuc->GPC ['server_url'] ) . "',server_desc='" . $db->escape_string ( $skyuc->GPC ['server_desc'] ) . "', is_show='" . $skyuc->GPC ['is_show'] . "', sort_order='" . $skyuc->GPC ['sort_order'] . "' ";

	if ($exc->edit ( $param, $skyuc->GPC ['id'] )) {
		// 重建缓存
		build_servers ();

		admin_log ( $skyuc->GPC ['server_name'], 'edit', 'server' );

		$link [0] ['text'] = $_LANG ['back_list'];
		$link [0] ['href'] = 'server.php?act=list';
		$note = vsprintf ( $_LANG ['serveredit_succed'], $skyuc->GPC ['server_name'] );
		sys_msg ( $note, 0, $link );
	} else {
		die ( $db->error () );
	}
}

/*------------------------------------------------------ */
//-- 编辑服务器名称
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_server_name') {
	check_authz_json ( 'server_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$id = $skyuc->GPC ['id'];
	$name = $skyuc->GPC ['val'];

	// 检查名称是否重复
	if ($exc->num ( 'server_name', $name, $id ) != 0) {
		make_json_error ( sprintf ( $_LANG ['servername_exist'], $name ) );
	} else {
		if ($exc->edit ( "server_name = '" . $db->escape_string ( $name ) . "'", $id )) {
			// 重建缓存
			build_servers ();
			admin_log ( $name, 'edit', 'server' );
			make_json_result ( $name );
		} else {
			make_json_result ( sprintf ( $_LANG ['serveredit_fail'], $name ) );
		}
	}
}

/*------------------------------------------------------ */
//-- 编辑排序序号
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_sort_order') {
	check_authz_json ( 'server_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	$id = $skyuc->GPC ['id'];
	$order = $skyuc->GPC ['val'];

	$name = $exc->get_name ( $id );

	if ($exc->edit ( "sort_order = '$order'", $id )) {
		// 重建缓存
		build_servers ();
		admin_log ( $name, 'edit', 'server' );

		make_json_result ( $order );
	} else {
		make_json_error ( sprintf ( $_LANG ['serveredit_fail'], $name ) );
	}
}

/*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'toggle_show') {
	check_authz_json ( 'server_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_BOOL ) );

	$id = $skyuc->GPC ['id'];
	$val = $skyuc->GPC ['val'];

	$exc->edit ( "is_show='$val'", $id );

	make_json_result ( $val );
}

/*------------------------------------------------------ */
//-- 删除服务器
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'remove') {
	check_authz_json ( 'server_manage' );

	$id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	// 删除ID服务器
	$exc->drop ( $id );

	// 更新影片的服务器编号
	$sql = 'UPDATE ' . TABLE_PREFIX . 'show' . ' SET server_id=0 WHERE server_id=' . $id;
	$db->query_write ( $sql );

	// 重建缓存
	build_servers ();

	$url = 'server.php?act=query&' . str_replace ( 'act=remove', '', $_SERVER ['QUERY_STRING'] );

	header ( "Location: $url\n" );
	exit ();
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$server_list = get_serverlist ();

	$smarty->assign ( 'server_list', $server_list ['server'] );
	$smarty->assign ( 'filter', $server_list ['filter'] );
	$smarty->assign ( 'record_count', $server_list ['record_count'] );
	$smarty->assign ( 'page_count', $server_list ['page_count'] );

	make_json_result ( $smarty->fetch ( 'server_list.tpl' ), '', array ('filter' => $server_list ['filter'], 'page_count' => $server_list ['page_count'] ) );
}

?>
