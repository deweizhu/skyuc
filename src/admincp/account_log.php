<?php
/**
 * SKYUC!	管理中心帐户变动记录
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');
include_once (DIR . '/includes/functions_order.php');

/*------------------------------------------------------ */
//-- 帐户变动列表
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list' || $skyuc->GPC ['act'] == '') {
	$skyuc->input->clean_array_gpc ( 'r', array ('user_id' => TYPE_UINT, 'account_type' => TYPE_STR ) );

	if ($skyuc->GPC ['user_id'] == 0) {
		sys_msg ( 'invalid param' );
	}
	$user = get_user_info ( $skyuc->GPC ['user_id'] );
	if (empty ( $user )) {
		sys_msg ( $_LANG ['user_not_exist'] );
	}
	$smarty->assign ( 'user', $user );

	if (empty ( $skyuc->GPC ['account_type'] ) || ! in_array ( $skyuc->GPC ['account_type'], array ('user_money', 'pay_point' ) )) {
		$account_type = '';
	} else {
		$account_type = $skyuc->GPC ['account_type'];
	}
	$smarty->assign ( 'account_type', $account_type );

	$smarty->assign ( 'ur_here', $_LANG ['account_list'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['add_account'], 'href' => 'account_log.php?act=add&user_id=' . $skyuc->GPC ['user_id'] ) );
	$smarty->assign ( 'full_page', 1 );

	$account_list = get_accountlist ( $skyuc->GPC ['user_id'], $account_type );
	$smarty->assign ( 'account_list', $account_list ['account'] );
	$smarty->assign ( 'filter', $account_list ['filter'] );
	$smarty->assign ( 'record_count', $account_list ['record_count'] );
	$smarty->assign ( 'page_count', $account_list ['page_count'] );

	assign_query_info ();
	$smarty->display ( 'account_list.tpl' );
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$skyuc->input->clean_array_gpc ( 'r', array ('user_id' => TYPE_UINT, 'account_type' => TYPE_STR ) );

	if ($skyuc->GPC ['user_id'] <= 0) {
		sys_msg ( 'invalid param' );
	}
	$user = get_user_info ( $user_id );
	if (empty ( $user )) {
		sys_msg ( $_LANG ['user_not_exist'] );
	}
	$smarty->assign ( 'user', $user );

	if (empty ( $skyuc->GPC ['account_type'] ) || ! in_array ( $skyuc->GPC ['account_type'], array ('user_money', 'pay_point' ) )) {
		$account_type = '';
	} else {
		$account_type = $skyuc->GPC ['account_type'];
	}
	$smarty->assign ( 'account_type', $account_type );

	$account_list = get_accountlist ( $skyuc->GPC ['user_id'], $account_type );
	$smarty->assign ( 'account_list', $account_list ['account'] );
	$smarty->assign ( 'filter', $account_list ['filter'] );
	$smarty->assign ( 'record_count', $account_list ['record_count'] );
	$smarty->assign ( 'page_count', $account_list ['page_count'] );

	make_json_result ( $smarty->fetch ( 'account_list.tpl' ), '', array ('filter' => $account_list ['filter'], 'page_count' => $account_list ['page_count'] ) );
}

/*------------------------------------------------------ */
//-- 调节帐户
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'add') {
	// 检查权限
	admin_priv ( 'account_manage' );

	// 检查参数
	$user_id = $skyuc->input->clean_gpc ( 'r', 'user_id', TYPE_UINT );
	if ($user_id <= 0) {
		sys_msg ( 'invalid param' );
	}
	$user = get_user_info ( $user_id );
	if (empty ( $user )) {
		sys_msg ( $_LANG ['user_not_exist'] );
	}
	$smarty->assign ( 'user', $user );

	// 显示模板
	$smarty->assign ( 'ur_here', $_LANG ['add_account'] );
	$smarty->assign ( 'action_link', array ('href' => 'account_log.php?act=list&user_id=' . $user_id, 'text' => $_LANG ['account_list'] ) );
	assign_query_info ();
	$smarty->display ( 'account_info.tpl' );
}

/*------------------------------------------------------ */
//-- 提交添加、编辑帐户变动
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'insert' || $skyuc->GPC ['act'] == 'update') {
	// 检查权限
	admin_priv ( 'account_manage' );

	// 检查参数
	$user_id = $skyuc->input->clean_gpc ( 'r', 'user_id', TYPE_UINT );
	if ($user_id <= 0) {
		sys_msg ( 'invalid param' );
	}
	$user = get_user_info ( $user_id );
	if (empty ( $user )) {
		sys_msg ( $_LANG ['user_not_exist'] );
	}

	$skyuc->input->clean_array_gpc ( 'p', array ('change_desc' => TYPE_STR, 'add_sub_user_money' => TYPE_NUM, 'user_money' => TYPE_NUM, 'add_sub_pay_point' => TYPE_NUM, 'pay_point' => TYPE_NUM ) );

	// 提交值
	$change_desc = sub_str ( $skyuc->GPC ['change_desc'], 255, false );
	$user_money = floatval ( $skyuc->GPC ['add_sub_user_money'] ) * abs ( floatval ( $skyuc->GPC ['user_money'] ) );
	$pay_point = floatval ( $skyuc->GPC ['add_sub_pay_point'] ) * abs ( floatval ( $skyuc->GPC ['pay_point'] ) );

	if ($user_money == 0 && $pay_point == 0) {
		sys_msg ( $_LANG ['no_account_change'] );
	}

	// 保存
	log_account_change ( $user_id, $user_money, $pay_point, $change_desc, ACT_ADJUSTING );

	// 提示信息
	$links = array (array ('href' => 'account_log.php?act=list&user_id=' . $user_id, 'text' => $_LANG ['account_list'] ) );
	sys_msg ( $_LANG ['log_account_change_ok'], 0, $links );
}

?>