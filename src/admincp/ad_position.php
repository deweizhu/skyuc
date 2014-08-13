<?php

/**
 * SKYUC! 广告位置管理程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');
require_once (DIR . '/languages/' . $skyuc->options ['lang'] . '/admincp/ads.php');

$exc = new exchange ( TABLE_PREFIX . 'ad_position', $skyuc->db, 'position_id', 'position_name' );

/*------------------------------------------------------ */
//-- 广告位置列表
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list' || $skyuc->GPC ['act'] == '') {
	$smarty->assign ( 'ur_here', $_LANG ['ad_position'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['position_add'], 'href' => 'ad_position.php?act=add' ) );
	$smarty->assign ( 'full_page', 1 );

	$position_list = ad_position_list ();

	$smarty->assign ( 'position_list', $position_list ['position'] );
	$smarty->assign ( 'filter', $position_list ['filter'] );
	$smarty->assign ( 'record_count', $position_list ['record_count'] );
	$smarty->assign ( 'page_count', $position_list ['page_count'] );
	$smarty->assign ( 'lang', $_LANG );

	assign_query_info ();
	$smarty->display ( 'ad_position_list.tpl' );
}

/*------------------------------------------------------ */
//-- 添加广告位页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'add') {
	admin_priv ( 'ad_manage' );

	//模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['position_add'] );
	$smarty->assign ( 'form_act', 'insert' );

	$smarty->assign ( 'action_link', array ('href' => 'ad_position.php?act=list', 'text' => $_LANG ['ad_position'] ) );
	$smarty->assign ( 'posit_arr', array ('position_style' => '<table cellpadding="0" cellspacing="0">' . "\n" . '{foreach from=$ads item=ad}' . "\n" . '<tr><td>{$ad}</td></tr>' . "\n" . '{/foreach}' . "\n" . '</table>' ) );
	$smarty->assign ( 'lang', $_LANG );

	assign_query_info ();
	$smarty->display ( 'ad_position_info.tpl' );
} elseif ($skyuc->GPC ['act'] == 'insert') {
	admin_priv ( 'ad_manage' );

	// 过滤数据
	$skyuc->input->clean_array_gpc ( 'p', array ('position_name' => TYPE_STR, 'position_desc' => TYPE_NOHTML, 'ad_width' => TYPE_UINT, 'ad_height' => TYPE_UINT, 'position_style' => TYPE_STR )

	 );

	// 查看广告位是否有重复
	if ($exc->num ( 'position_name', $position_name ) == 0) {
		// 将广告位置的信息插入数据表
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'ad_position' . ' (position_name, ad_width, ad_height, position_desc, position_style) ' . "VALUES ('" . $skyuc->db->escape_string ( $skyuc->GPC ['position_name'] ) . "', '" . $skyuc->GPC ['ad_width'] . "', '" . $skyuc->GPC ['ad_height'] . "', '" . $skyuc->db->escape_string ( $skyuc->GPC ['position_desc'] ) . "', '" . $skyuc->db->escape_string ( $skyuc->GPC ['position_style'] ) . "')";

		$skyuc->db->query_write ( $sql );
		// 记录管理员操作
		admin_log ( $skyuc->GPC ['position_name'], 'add', 'ads_position' );

		// 提示信息
		$link [0] ['text'] = $_LANG ['continue_add_position'];
		$link [0] ['href'] = 'ad_position.php?act=add';

		$link [1] ['text'] = $_LANG ['back_position_list'];
		$link [1] ['href'] = 'ad_position.php?act=list';

		sys_msg ( $_LANG ['add'] . "&nbsp;" . $skyuc->GPC ['position_name'] . "&nbsp;" . $_LANG ['attradd_succed'], 0, $link );
	} else {
		$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
		sys_msg ( $_LANG ['posit_name_exist'], 0, $link );
	}
}

/*------------------------------------------------------ */
//-- 广告位编辑页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit') {
	admin_priv ( 'ad_manage' );

	$id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	// 获取广告位数据
	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'ad_position' . " WHERE position_id='$id'";
	$posit_arr = $skyuc->db->query_first_slave ( $sql );

	$smarty->assign ( 'ur_here', $_LANG ['position_edit'] );
	$smarty->assign ( 'action_link', array ('href' => 'ad_position.php?act=list', 'text' => $_LANG ['ad_position'] ) );
	$smarty->assign ( 'posit_arr', $posit_arr );
	$smarty->assign ( 'form_act', 'update' );
	$smarty->assign ( 'lang', $_LANG );

	assign_query_info ();
	$smarty->display ( 'ad_position_info.tpl' );
} elseif ($skyuc->GPC ['act'] == 'update') {
	admin_priv ( 'ad_manage' );

	// 过滤数据
	$skyuc->input->clean_array_gpc ( 'p', array ('position_name' => TYPE_STR, 'position_desc' => TYPE_NOHTML, 'ad_width' => TYPE_UINT, 'ad_height' => TYPE_UINT, 'position_style' => TYPE_STR, 'id' => TYPE_UINT )

	 );

	// 查看广告位是否与其它有重复
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'ad_position' . " WHERE position_name = '" . $skyuc->db->escape_string ( $skyuc->GPC ['position_name'] ) . "' AND position_id <> " . $skyuc->GPC ['id'];
	$total = $skyuc->db->query_first_slave ( $sql );
	if ($total ['total'] == 0) {
		$sql = 'UPDATE ' . TABLE_PREFIX . 'ad_position' . ' SET ' . "position_name    = '" . $skyuc->db->escape_string ( $skyuc->GPC ['position_name'] ) . "', " . "ad_width         = '" . $skyuc->GPC ['ad_width'] . "', " . "ad_height        = '" . $skyuc->GPC ['ad_height'] . "', " . "position_desc    = '" . $skyuc->db->escape_string ( $skyuc->GPC ['position_desc'] ) . "', " . "position_style   = '" . $skyuc->db->escape_string ( $skyuc->GPC ['position_style'] ) . "' " . 'WHERE position_id = ' . $skyuc->GPC ['id'];
		if ($skyuc->db->query_write ( $sql )) {
			// 记录管理员操作
			admin_log ( $skyuc->GPC ['position_name'], 'edit', 'ads_position' );

			// 提示信息
			$link [] = array ('text' => $_LANG ['back_position_list'], 'href' => 'ad_position.php?act=list' );
			sys_msg ( $_LANG ['edit'] . ' ' . stripslashes ( $position_name ) . ' ' . $_LANG ['attradd_succed'], 0, $link );
		}
	} else {
		$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
		sys_msg ( $_LANG ['posit_name_exist'], 0, $link );
	}
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$position_list = ad_position_list ();

	$smarty->assign ( 'position_list', $position_list ['position'] );
	$smarty->assign ( 'filter', $position_list ['filter'] );
	$smarty->assign ( 'record_count', $position_list ['record_count'] );
	$smarty->assign ( 'page_count', $position_list ['page_count'] );
	$smarty->assign ( 'lang', $_LANG );

	make_json_result ( $smarty->fetch ( 'ad_position_list.tpl' ), '', array ('filter' => $position_list ['filter'], 'page_count' => $position_list ['page_count'] ) );
}

/*------------------------------------------------------ */
//-- 编辑广告位置名称
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_position_name') {
	check_authz_json ( 'ad_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$id = $skyuc->GPC ['id'];
	$position_name = $skyuc->GPC ['val'];

	// 检查名称是否重复
	if ($exc->num ( 'position_name', $position_name, $id ) != 0) {
		make_json_error ( sprintf ( $_LANG ['posit_name_exist'], $position_name ) );
	} else {
		if ($exc->edit ( "position_name = '" . $skyuc->db->escape_string ( $position_name ) . "'", $id )) {
			admin_log ( $position_name, 'edit', 'ads_position' );
			make_json_result ( $position_name );
		} else {
			make_json_result ( sprintf ( $_LANG ['brandedit_fail'], $position_name ) );
		}
	}
}

/*------------------------------------------------------ */
//-- 编辑广告位宽高
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_ad_width') {
	check_authz_json ( 'ad_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	$id = $skyuc->GPC ['id'];
	$ad_width = $skyuc->GPC ['val'];

	// 宽度值必须是数字
	if (! preg_match ( "/^[\.0-9]+$/", $ad_width )) {
		make_json_error ( $_LANG ['width_number'] );
	}

	// 广告位宽度应在1-1024之间
	if ($ad_width > 1024 || $ad_width < 1) {
		make_json_error ( $_LANG ['width_value'] );
	}

	if ($exc->edit ( "ad_width = '$ad_width'", $id )) {
		admin_log ( $ad_width, 'edit', 'ads_position' );
		make_json_result ( stripslashes ( $ad_width ) );
	} else {
		make_json_error ( $db->error () );
	}
}

/*------------------------------------------------------ */
//-- 编辑广告位宽高
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_ad_height') {
	check_authz_json ( 'ad_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	$id = $skyuc->GPC ['id'];
	$ad_height = $skyuc->GPC ['val'];

	//高度值必须是数字
	if (! preg_match ( "/^[\.0-9]+$/", $ad_height )) {
		make_json_error ( $_LANG ['height_number'] );
	}

	// 广告位宽度应在1-1024之间
	if ($ad_height > 1024 || $ad_height < 1) {
		make_json_error ( $_LANG ['height_value'] );
	}

	if ($exc->edit ( "ad_height = '$ad_height'", $id )) {
		admin_log ( $ad_height, 'edit', 'ads_position' );
		make_json_result ( stripslashes ( $ad_height ) );
	} else {
		make_json_error ( $db->error () );
	}
}

/*------------------------------------------------------ */
//-- 删除广告位置
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'remove') {
	check_authz_json ( 'ad_manage' );

	$id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	//查询广告位下是否有广告存在
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'ad' . " WHERE position_id = '$id'";
	$total = $db->query_all_slave ( $sql );

	if ($total ['total'] > 0) {
		make_json_error ( $_LANG ['not_del_adposit'] );
	} else {
		$exc->drop ( $id );
		admin_log ( '', 'remove', 'ads_position' );
	}

	$url = 'ad_position.php?act=query&' . str_replace ( 'act=remove', '', $_SERVER ['QUERY_STRING'] );

	header ( "Location: $url\n" );
	exit ();
}
?>
