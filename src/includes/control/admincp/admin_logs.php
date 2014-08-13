<?php
// #######################################################################
// ######################## admin_logs.php 私有函数      #####################
// #######################################################################


/**
 * 获取管理员日志
 *
 * @return	array
 */
function get_admin_logs() {
	
	$GLOBALS ['skyuc']->input->clean_array_gpc ( 'r', array ('id' => TYPE_UINT, 'ip' => TYPE_STR, 'sort_by' => TYPE_STR, 'sort_order' => TYPE_STR ) );
	
	$user_id = $GLOBALS ['skyuc']->GPC ['id'];
	$admin_ip = $GLOBALS ['skyuc']->GPC ['ip'];
	
	$filter = array ();
	$filter ['sort_by'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_by'] ), 'al.log_id', $GLOBALS ['skyuc']->GPC ['sort_by'] );
	$filter ['sort_order'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_order'] ), 'DESC', $GLOBALS ['skyuc']->GPC ['sort_order'] );
	
	//查询条件
	$where = ' WHERE 1 ';
	if (! empty ( $user_id )) {
		$where .= " AND al.user_id = '$user_id' ";
	} elseif (! empty ( $admin_ip )) {
		$where .= " AND al.ip_address = '$admin_ip' ";
	}
	
	//获得总记录数据
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'admin_log' . ' AS al ' . $where;
	$total = $GLOBALS ['db']->query_first ( $sql );
	$filter ['record_count'] = $total ['total'];
	
	$filter = page_and_size ( $filter );
	
	// 获取管理员日志记录
	$list = array ();
	$sql = 'SELECT al.*, u.user_name FROM ' . TABLE_PREFIX . 'admin_log' . ' AS al ' . 'LEFT JOIN ' . TABLE_PREFIX . 'admin' . ' AS u ON u.user_id = al.user_id ' . $where . ' ORDER by ' . $filter ['sort_by'] . ' ' . $filter ['sort_order'];
	$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
	$res = $GLOBALS ['db']->query_read_slave ( $sql );
	
	while ( $rows = $GLOBALS ['db']->fetch_array ( $res ) ) {
		$rows ['log_time'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $rows ['log_time'], FALSE, FALSE );
		
		$list [] = $rows;
	}
	
	return array ('list' => $list, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
}
?>