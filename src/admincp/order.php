<?php
/**
 * SKYUC! 订单管理
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');
require_once (DIR . '/includes/functions_order.php');

/*------------------------------------------------------ */
//-- 订单列表
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list') {
	// 检查权限
	admin_priv ( 'order_view' );

	// 取得过滤条件
	$filter = array ();

	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['02_order_list'] );

	$smarty->assign ( 'filter', $filter ); // 过滤条件
	$smarty->assign ( 'status_list', $_LANG ['ps'] ); // 订单状态
	$smarty->assign ( 'full_page', 1 );

	$order_list = get_order_list ();
	$smarty->assign ( 'order_list', $order_list ['orders'] );
	$smarty->assign ( 'filter', $order_list ['filter'] );
	$smarty->assign ( 'record_count', $order_list ['record_count'] );
	$smarty->assign ( 'page_count', $order_list ['page_count'] );
	$smarty->assign ( 'sort_order_time', '<img src="images/sort_desc.gif">' );

	assign_query_info ();
	$smarty->display ( 'order_list.tpl' );
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$order_list = get_order_list ();

	$smarty->assign ( 'order_list', $order_list ['orders'] );
	$smarty->assign ( 'filter', $order_list ['filter'] );
	$smarty->assign ( 'record_count', $order_list ['record_count'] );
	$smarty->assign ( 'page_count', $order_list ['page_count'] );
	$sort_flag = sort_flag ( $order_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	make_json_result ( $smarty->fetch ( 'order_list.tpl' ), '', array ('filter' => $order_list ['filter'], 'page_count' => $order_list ['page_count'] ) );
}

/*------------------------------------------------------ */
//-- 订单详情页面
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'info') {

	$skyuc->input->clean_array_gpc ( 'g', array ('order_id' => TYPE_UINT, 'order_sn' => TYPE_STR ) );
	// 根据订单id或订单号查询订单信息
	if ($skyuc->GPC_exists ['order_id']) {
		$order = order_info ( $skyuc->GPC ['order_id'] );
	} elseif ($skyuc->GPC_exists ['order_sn']) {
		$order = order_info ( 0, $skyuc->GPC ['order_sn'] );
	} else {
		// 如果参数不存在，退出
		die ( 'invalid parameter' );
	}

	// 如果订单不存在，退出
	if (empty ( $order )) {
		die ( 'order does not exist' );
	}

	// 根据订单是否完成检查权限
	if (order_finished ( $order )) {
		admin_priv ( 'order_view_finished' );
	} else {
		admin_priv ( 'order_view' );
	}

	// 取得用户名
	if ($order ['user_id'] > 0) {
		$user = get_user_info ( $order ['user_id'] );
		if (! empty ( $user )) {
			$order ['user_name'] = $user ['user_name'];
		}
	}
	//取得等级名称
	$rank_name = get_rank_name ( $order ['rank_id'] );

	// 格式化金额
	$order ['money_refund'] = $order ['order_amount'];
	$order ['formated_money_refund'] = price_format ( $order ['order_amount'] );
	$order ['formated_pay_amount'] = price_format ( $order ['pay_amount'] );
	$order ['formated_surplus'] = price_format ( $order ['surplus'] );

	// 其它处理
	$order ['order_time'] = skyuc_date ( $skyuc->options ['date_format'] . ' ' . $skyuc->options ['time_format'], $order ['order_time'] );
	$order ['pay_time'] = iif ( $order ['pay_time'] > 0, skyuc_date ( $skyuc->options ['date_format'] . ' ' . $skyuc->options ['time_format'], $order ['pay_time'] ), $_LANG ['ps'] [PS_UNPAYED] );
	$order ['status'] = $_LANG ['ps'] [$order ['pay_status']];
	$order ['order_buyinfo'] = iif ( ! empty ( $order ['usertype'] ), $rank_name . $order ['order_count'] . $_LANG ['look_day'], $rank_name . $order ['order_count'] . $_LANG ['look_count'] );

	// 取得上一个、下一个订单号
	$prev_id = $db->query_first ( 'SELECT MAX(order_id) AS prev_id FROM ' . TABLE_PREFIX . 'order_info' . ' WHERE order_id < ' . $order ['order_id'] );

	$next_id = $db->query_first ( 'SELECT MIN(order_id) AS next_id FROM ' . TABLE_PREFIX . 'order_info' . ' WHERE order_id > ' . $order ['order_id'] );

	$smarty->assign ( 'prev_id', $prev_id ['prev_id'] );
	$smarty->assign ( 'next_id', $next_id ['next_id'] );

	// 模板赋值
	$smarty->assign ( 'order', $order );
	$smarty->assign ( 'ur_here', $_LANG ['order_info'] );
	$smarty->assign ( 'action_link', array ('href' => 'order.php?act=list', 'text' => $_LANG ['02_order_list'] ) );

	// 显示模板
	assign_query_info ();
	$smarty->display ( 'order_info.tpl' );

}

/*------------------------------------------------------ */
//-- 删除订单
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'remove_order') {
	// 检查权限
	check_authz_json ( 'order_remove' );

	$order_id = $skyuc->input->clean_gpc ( 'r', 'id', TYPE_UINT );

	// 检查删除的订单
	$sql = 'SELECT order_sn  FROM ' . TABLE_PREFIX . 'order_info' . ' WHERE order_id = ' . $order_id;
	$order = $db->query_first ( $sql );
	if ($order_id == 'all') {
		$db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'order_info' . ' WHERE pay_status  = 0' );
	} else {
		$db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'order_info' . ' WHERE order_id = ' . $order_id );
	}

	// 记录日志
	admin_log ( $order ['order_sn'], 'remove', 'order' );

	if ($db->errno () == 0) {
		$url = 'order.php?act=query&' . str_replace ( 'act=remove_order', '', $_SERVER ['QUERY_STRING'] );

		header ( "Location: $url\n" );
		exit ();
	} else {
		make_json_error ( $db->errorMsg () );
	}
} /*------------------------------------------------------ */
//-- 确认订单
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'confirm_order') {
	// 检查权限
	check_authz_json ( 'order_remove' );

	$order_id = $skyuc->input->clean_gpc ( 'r', 'order_id', TYPE_UINT );

	// 查询订单信息
	$order = order_info ( $order_id );

	// 如果订单不存在或已付款，退出
	if (empty ( $order ) || ! empty ( $order ['pay_status'] )) {
		die ( 'order does not exist' );
	}

	// 标记订单为已付款
	update_order ( $order_id, array ('pay_status' => PS_PAYED, 'pay_time' => TIMENOW ) );

	// 实时开通会员权限
	include_once (DIR . '/includes/functions_payment.php');
	payment_finsh ( $order ['order_count'], $order ['usertype'], $order ['rank_id'], $order ['user_id'], $order ['order_id'] );

	// 记录日志
	admin_log ( addslashes ( $order ['order_sn'] ), 'edit', 'order' );

	if ($db->errno () == 0) {
		$url = 'order.php?act=list';

		header ( "Location: $url\n" );
		exit ();
	} else {
		make_json_error ( $db->errorMsg () );
	}

}

/*------------------------------------------------------ */
//-- 根据关键字和id搜索用户
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'search_users') {
	include_once (DIR . '/includes/class_json.php');
	$json = new JSON ();

	$skyuc->input->clean_gpc ( 'g', 'id_name', TYPE_STR );
	$id_name = $skyuc->GPC ['id_name'];

	$result = array ('error' => 0, 'message' => '', 'content' => '' );
	if ($id_name != '') {
		$sql = 'SELECT user_id, user_name FROM ' . TABLE_PREFIX . 'users' . " WHERE user_id LIKE '%" . $db->escape_string_like ( $id_name ) . "%'" . " OR user_name LIKE '%" . $db->escape_string_likee ( $id_name ) . "%'";
		$sql = $skyuc->db->query_limit ( $sql, 20 );
		$res = $db->query_read ( $sql );

		$result ['userlist'] = array ();
		while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
			$result ['userlist'] [] = array ('user_id' => $row ['user_id'], 'user_name' => $row ['user_name'] );
		}
	} else {
		$result ['error'] = 1;
		$result ['message'] = 'NO KEYWORDS!';
	}

	die ( $json->encode ( $result ) );
}

?>