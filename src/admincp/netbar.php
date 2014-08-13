<?php
/**
 * SKYUC!  网吧管理程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

$exc = new exchange ( TABLE_PREFIX . 'netbar', $skyuc->db, 'id', 'title' );

/*------------------------------------------------------ */
//-- 网吧列表
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list') {
	$intro_type = array ('is_ok' => $_LANG ['is_ok'], 'is_no' => $_LANG ['is_no'] );

	// 模板赋值
	$ur_here = $_LANG ['03_netbar_list'];
	$smarty->assign ( 'ur_here', $ur_here );

	$action_link = array ('href' => 'netbar.php?act=add', 'text' => $_LANG ['02_netbar_add'] );
	$smarty->assign ( 'action_link', $action_link );
	$smarty->assign ( 'lang', $_LANG );

	$netbar_list = get_netbar_list ();

	$smarty->assign ( 'netbar_list', $netbar_list ['netbar'] );
	$smarty->assign ( 'intro_list', $intro_type );
	$smarty->assign ( 'filter', $netbar_list ['filter'] );
	$smarty->assign ( 'record_count', $netbar_list ['record_count'] );
	$smarty->assign ( 'page_count', $netbar_list ['page_count'] );
	$smarty->assign ( 'full_page', 1 );

	// 排序标记
	$sort_flag = sort_flag ( $netbar_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	// 显示网吧列表页面
	assign_query_info ();
	$smarty->display ( 'netbar_list.tpl' );
} /*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {

	$netbar_list = get_netbar_list ();

	$smarty->assign ( 'netbar_list', $netbar_list ['netbar'] );
	$smarty->assign ( 'filter', $netbar_list ['filter'] );
	$smarty->assign ( 'record_count', $netbar_list ['record_count'] );
	$smarty->assign ( 'page_count', $netbar_list ['page_count'] );

	// 排序标记
	$sort_flag = sort_flag ( $netbar_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	make_json_result ( $smarty->fetch ( 'netbar_list.tpl' ), '', array ('filter' => $netbar_list ['filter'], 'page_count' => $netbar_list ['page_count'] ) );

} /*------------------------------------------------------ */
//-- 添加、修改网吧
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'add' || $skyuc->GPC ['act'] == 'edit') {
	admin_priv ( 'netbar_manage' ); // 检查权限
	$is_add = ($skyuc->GPC ['act'] == 'add'); // 添加还是编辑的标识


	// 取得影片信息
	if ($is_add) {
		// 默认值
		$netbar = array ();

	} else {
		$skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

		// 网吧信息
		$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'netbar' . ' WHERE id = ' . $skyuc->GPC ['id'];
		$netbar = $db->query_first ( $sql );

		$netbar ['endtime'] = skyuc_date ( $skyuc->options ['date_format'], $netbar ['endtime'], true, false );
		$netbar ['addtime'] = skyuc_date ( $skyuc->options ['date_format'], $netbar ['addtime'], true, false );

		if (empty ( $netbar ) === true) {
			// 默认值
			$netbar = array ();
		}
		if (! empty ( $netbar ['title'] )) {
			$netbar ['title'] = trim ( $netbar ['title'] );
		}

	}
	// 模板赋值
	$smarty->assign ( 'ur_here', $is_add ? $_LANG ['02_netbar_add'] : $_LANG ['edit_netbar'] );
	$smarty->assign ( 'action_link', array ('href' => 'netbar.php?act=list', 'text' => $_LANG ['03_netbar_list'] ) );
	$smarty->assign ( 'netbar', $netbar );
	$smarty->assign ( 'today', strtotime ( '+1 month', TIMENOW ) );
	$smarty->assign ( 'form_act', $is_add ? 'insert' : 'update' );

	assign_query_info ();
	$smarty->display ( 'netbar_info.tpl' );
} /*------------------------------------------------------ */
//-- 插入、更新网吧信息
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'insert' || $skyuc->GPC ['act'] == 'update') {
	// 检查权限
	admin_priv ( 'netbar_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'title' => TYPE_STR, 'username' => TYPE_STR, 'userpass' => TYPE_STR, 'sip' => TYPE_STR, 'eip' => TYPE_STR, 'maxuser' => TYPE_UINT, 'is_ok' => TYPE_BOOL, 'content' => TYPE_STR, 'add_dateYear' => TYPE_UINT, 'add_dateMonth' => TYPE_UINT, 'add_dateDay' => TYPE_UINT, 'end_dateYear' => TYPE_UINT, 'end_dateMonth' => TYPE_UINT, 'end_dateDay' => TYPE_UINT )

	 );

	// 验证IP是否有效
	if (is_ip ( $skyuc->GPC ['sip'] ) == false || is_ip ( $skyuc->GPC ['eip'] ) == false) {
		sys_msg ( $_LANG ['ip_not_valid'], 1, array (), false );
	}

	// 入库前操作
	$skyuc->GPC ['snum'] = ip2num ( $skyuc->GPC ['sip'] );
	$skyuc->GPC ['enum'] = ip2num ( $skyuc->GPC ['eip'] );
	$skyuc->GPC ['endtime'] = skyuc_mktime ( 0, 0, 0, $skyuc->GPC ['end_dateMonth'], $skyuc->GPC ['end_dateDay'], $skyuc->GPC ['end_dateYear'] );

	// 插入还是更新的标识
	$is_insert = ($skyuc->GPC ['act'] == 'insert');
	// 添加新网吧
	if ($is_insert) {
		// 检查登陆名称是否重复
		if ($netbar ['username']) {
			$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'netbar' . " WHERE username = '" . $db->escape_string ( $skyuc->GPC ['username'] ) . "' ";
			$total = $db->query_first ( $sql );
			if ($total ['total'] > 0) {
				sys_msg ( $_LANG ['netbar_name_exists'], 1, array (), false );
			}
		}
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'netbar' . ' (title, snum, enum,  sip,  eip, content, username, userpass, addtime, endtime, lasttime, maxuser , is_ok) ' . " VALUES ('" . $db->escape_string ( $skyuc->GPC ['title'] ) . "', '" . $skyuc->GPC ['snum'] . "', '" . $skyuc->GPC ['enum'] . "', '" . $db->escape_string ( $skyuc->GPC ['sip'] ) . "', '" . $db->escape_string ( $skyuc->GPC ['eip'] ) . "', '" . $db->escape_string ( $skyuc->GPC ['content'] ) . "', '" . $db->escape_string ( $skyuc->GPC ['username'] ) . "', '" . $db->escape_string ( $skyuc->GPC ['userpass'] ) . "', '" . TIMENOW . "', '" . $skyuc->GPC ['endtime'] . "', '" . TIMENOW . "', '" . $skyuc->GPC ['maxuser'] . "','" . $skyuc->GPC ['is_ok'] . "')";
		$skyuc->db->query_write ( $sql ); // 入库的操作
	} else {
		$skyuc->GPC ['addtime'] = skyuc_mktime ( 0, 0, 0, $skyuc->GPC ['add_dateMonth'], $skyuc->GPC ['add_dateDay'], $skyuc->GPC ['add_dateYear'] );

		$sql = 'UPDATE ' . TABLE_PREFIX . 'netbar' . ' SET ' . "title = '" . $db->escape_string ( $skyuc->GPC ['title'] ) . "'," . 'snum =' . $skyuc->GPC ['snum'] . ', ' . 'enum =' . $skyuc->GPC ['enum'] . ', ' . "sip ='" . $db->escape_string ( $skyuc->GPC ['sip'] ) . "'," . "eip ='" . $db->escape_string ( $skyuc->GPC ['eip'] ) . "'," . "content ='" . $db->escape_string ( $skyuc->GPC ['content'] ) . "'," . "username ='" . $db->escape_string ( $skyuc->GPC ['username'] ) . "'," . "userpass ='" . $db->escape_string ( $skyuc->GPC ['userpass'] ) . "'," . 'addtime = ' . $skyuc->GPC ['addtime'] . ', ' . 'endtime = ' . $skyuc->GPC ['endtime'] . ', ' . 'lasttime =' . TIMENOW . ', ' . 'is_ok = ' . $skyuc->GPC ['is_ok'] . ' WHERE id =' . $skyuc->GPC ['id'];

		$skyuc->db->query_write ( $sql ); // 修改的操作


	}

	// 记录日志
	if ($is_insert) {
		admin_log ( $skyuc->GPC ['title'], 'add', 'netbar' );
	} else {
		admin_log ( $skyuc->GPC ['title'], 'edit', 'netbar' );
	}

	// 提示页面
	if ($is_insert) {
		$link [] = array ('text' => $_LANG ['continue_add_netbar'], 'href' => 'netbar.php?act=add' );
	}
	$link [] = array ('text' => $_LANG ['back_netbar_list'], 'href' => 'netbar.php?act=list' );
	sys_msg ( $is_insert ? $_LANG ['add_netbar_ok'] : $_LANG ['edit_netbar_ok'], 0, $link );
} /*------------------------------------------------------ */
//-- 修改网吧名称
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_name') {
	check_authz_json ( 'netbar_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$id = $skyuc->GPC ['id'];
	$val = $skyuc->GPC ['val'];

	if ($exc->edit ( "title = '" . $db->escape_string ( $val ) . "'", $id )) {
		make_json_result ( $val );
	}
} /*------------------------------------------------------ */
//-- 修改起始IP
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_sip') {
	check_authz_json ( 'netbar_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$id = $skyuc->GPC ['id'];
	$val = $skyuc->GPC ['val'];
	$snum = ip2num ( $val );

	if ($exc->edit ( "sip = '" . $db->escape_string ( $val ) . "',snum = '$snum'", $id )) {
		make_json_result ( $val );
	}
} /*------------------------------------------------------ */
//-- 修改终止IP
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_eip') {
	check_authz_json ( 'netbar_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$id = $skyuc->GPC ['id'];
	$val = $skyuc->GPC ['val'];
	$enum = ip2num ( $val );

	if ($exc->edit ( "eip = '" . $db->escape_string ( $val ) . "',enum = '$enum'", $id )) {
		make_json_result ( $val );
	}
} /*------------------------------------------------------ */
//-- 修改电脑台数
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_maxuser') {
	check_authz_json ( 'netbar_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	$id = $skyuc->GPC ['id'];
	$val = $skyuc->GPC ['val'];

	if ($exc->edit ( "maxuser = '$val'", $id )) {
		make_json_result ( $val );
	}
} /*------------------------------------------------------ */
//-- 修改添加时间
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_addtime') {
	check_authz_json ( 'netbar_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$id = $skyuc->GPC ['id'];
	$val = strtotime ( $skyuc->GPC ['val'] );

	if ($exc->edit ( "addtime = '$val'", $id )) {
		make_json_result ( $skyuc->GPC ['val'] );
	}
} /*------------------------------------------------------ */
//-- 修改终止时间
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_endtime') {
	check_authz_json ( 'netbar_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$id = $skyuc->GPC ['id'];
	$val = strtotime ( $skyuc->GPC ['val'] );

	if ($exc->edit ( "endtime = '$val'", $id )) {
		make_json_result ( $skyuc->GPC ['val'] );
	}
} /*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'toggle_enabled') {
	// 检查权限
	check_authz_json ( 'netbar_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	$id = $skyuc->GPC ['id'];
	$val = $skyuc->GPC ['val'];

	$exc->edit ( "is_ok='$val'", $id );

	make_json_result ( $val );
}

/*------------------------------------------------------ */
//-- 删除网吧信息
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'drop_netbar') {
	// 检查权限
	check_authz_json ( 'netbar_manage' );

	// 取得参数
	$id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );
	if ($id <= 0) {
		make_json_error ( 'invalid params' );
	}

	// 取得网吧信息
	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'netbar' . " WHERE id = '$id'";
	$netbar = $db->query_first ( $sql );
	if (empty ( $netbar )) {
		make_json_error ( $_LANG ['netbar_not_exist'] );
	}

	// 删除网吧信息
	$exc->drop ( $id );

	// 记录日志
	admin_log ( $netbar ['title'], 'remove', 'netbar' );

	$url = 'netbar.php?act=query&' . str_replace ( 'act=drop_netbar', '', $_SERVER ['QUERY_STRING'] );

	header ( "Location: $url\n" );
	exit ();
}

?>