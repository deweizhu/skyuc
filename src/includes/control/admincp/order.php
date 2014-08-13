<?php
// #######################################################################
// ######################## order.php 私有函数    ########################
// #######################################################################


/**
 * 获取订单列表信息
 *
 * @access  public
 * @param
 *
 * @return void
 */
function get_order_list() {
	
	$result = get_filter ();
	if ($result === false) {
		// 过滤信息
		

		$GLOBALS ['skyuc']->input->clean_array_gpc ( 'r', array ('order_sn' => TYPE_STR, 'pay_id' => TYPE_UINT, 'pay_status' => TYPE_INT, 'user_id' => TYPE_UINT, 'user_name' => TYPE_STR, 'sort_by' => TYPE_STR, 'sort_order' => TYPE_STR, 'page' => TYPE_UINT, 'page_size' => TYPE_UINT ) );
		
		$filter ['order_sn'] = $GLOBALS ['skyuc']->GPC ['order_sn'];
		$filter ['pay_id'] = $GLOBALS ['skyuc']->GPC ['pay_id'];
		$filter ['pay_status'] = iif ( $GLOBALS ['skyuc']->GPC_exists ['pay_status'], $GLOBALS ['skyuc']->GPC ['pay_status'], - 1 );
		$filter ['user_id'] = $GLOBALS ['skyuc']->GPC ['user_id'];
		$filter ['user_name'] = $GLOBALS ['skyuc']->GPC ['user_name'];
		
		$filter ['sort_by'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_by'] ), 'order_time', $GLOBALS ['skyuc']->GPC ['sort_by'] );
		$filter ['sort_order'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_order'] ), 'DESC', $GLOBALS ['skyuc']->GPC ['sort_order'] );
		
		$where = 'WHERE 1 ';
		if ($filter ['order_sn']) {
			$where .= " AND o.order_sn LIKE '%" . $GLOBALS ['db']->escape_string_like ( $filter ['order_sn'] ) . "%'";
		}
		
		if ($filter ['pay_id']) {
			$where .= " AND o.pay_id  = '" . $filter ['pay_id'] . "'";
		}
		
		if ($filter ['pay_status'] != - 1) {
			$where .= " AND o.pay_status = '" . $filter ['pay_status'] . "'";
		}
		if ($filter ['user_id']) {
			$where .= " AND o.user_id = '" . $filter ['user_id'] . "'";
		}
		if ($filter ['user_name']) {
			$where .= " AND u.user_name LIKE '%" . $GLOBALS ['db']->escape_string_like ( $filter ['user_name'] ) . "%'";
		}
		
		// 分页大小
		$filter ['page'] = iif ( $GLOBALS ['skyuc']->GPC ['page'] <= 0, 1, $GLOBALS ['skyuc']->GPC ['page'] );
		
		if ($GLOBALS ['skyuc']->GPC_exists ['page_size'] && $GLOBALS ['skyuc']->GPC ['page_size'] > 0) {
			$filter ['page_size'] = $GLOBALS ['skyuc']->GPC ['page_size'];
		} elseif (isset ( $_COOKIE ['SKYUC_page_size'] ) && intval ( $_COOKIE ['SKYUC_page_size'] ) > 0) {
			$filter ['page_size'] = intval ( $_COOKIE ['SKYUC_page_size'] );
		} else {
			$filter ['page_size'] = 15;
		}
		
		// 记录总数
		if ($filter ['user_name']) {
			$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'order_info' . ' AS o ,' . TABLE_PREFIX . 'users' . ' AS u ' . $where;
		} else {
			$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'order_info' . ' AS o ' . $where;
		}
		
		$total = $GLOBALS ['db']->query_first ( $sql );
		$record_count = $total ['total'];
		$page_count = iif ( $record_count > 0, ceil ( $record_count / $filter ['page_size'] ), 1 );
		
		// 查询
		$sql = 'SELECT o.order_id, o.order_sn, o.order_time,  o.order_amount,o.pay_amount, o.pay_status,o.pay_name, ' . "IFNULL(u.user_name, '" . $GLOBALS ['_LANG'] ['anonymous'] . "') AS buyer " . ' FROM ' . TABLE_PREFIX . 'order_info' . ' AS o ' . ' LEFT JOIN ' . TABLE_PREFIX . 'users' . ' AS u ON u.user_id=o.user_id ' . $where . ' ORDER BY ' . $filter ['sort_by'] . ' ' . $filter ['sort_order'];
		$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], ($filter ['page'] - 1) * $filter ['page_size'] );
		
		set_filter ( $filter, $sql );
	} else {
		$sql = $result ['sql'];
		$filter = $result ['filter'];
	}
	
	$res = $GLOBALS ['db']->query_read ( $sql );
	$order = array ();
	while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
		//格式化数据
		$row ['formated_order_amount'] = price_format ( $row ['order_amount'] );
		$row ['formated_pay_amount'] = price_format ( $row ['pay_amount'] );
		$row ['short_order_time'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $row ['order_time'] );
		if ($row ['pay_status'] == PS_UNPAYED || $row ['pay_status'] == PS_PAYING) {
			// 如果该订单为正在未付款或付款中则显示删除链接
			$row ['can_remove'] = 1;
		} else {
			$row ['can_remove'] = 0;
		}
		
		$order [] = $row;
	}
	
	$arr = array ('orders' => $order, 'filter' => $filter, 'page_count' => $page_count, 'record_count' => $record_count );
	
	return $arr;
}

?>