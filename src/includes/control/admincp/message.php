<?php

// #######################################################################
// ######################## message.php 私有函数      ##########################
// #######################################################################


/**
 * 获取管理员留言列表
 *
 * @return void
 */
function get_message_list() {
	
	$GLOBALS ['skyuc']->input->clean_array_gpc ( 'r', array ('sort_by' => TYPE_STR, 'sort_order' => TYPE_STR, 'msg_type' => TYPE_UINT ) );
	
	// 查询条件
	$filter ['sort_by'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_by'] ), 'send_date', $GLOBALS ['skyuc']->GPC ['sort_by'] );
	$filter ['sort_order'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_order'] ), 'DESC' . $GLOBALS ['skyuc']->GPC ['sort_order'] );
	$filter ['msg_type'] = $GLOBALS ['skyuc']->GPC ['msg_type'];
	
	// 查询条件
	switch ($filter ['msg_type']) {
		case 2 :
			$where = " a.sender_id='" . $GLOBALS ['skyuc']->session->vars ['adminid'] . "' AND a.deleted='0'";
			break;
		case 3 :
			$where = " a.readed='0' AND a.receiver_id='" . $GLOBALS ['skyuc']->session->vars ['adminid'] . "' AND a.deleted='0'";
			break;
		case 4 :
			$where = " a.readed='1' AND a.receiver_id='" . $GLOBALS ['skyuc']->session->vars ['adminid'] . "' AND a.deleted='0'";
			break;
		default :
			$where = " a.receiver_id='" . $GLOBALS ['skyuc']->session->vars ['adminid'] . "' AND a.deleted='0'";
	}
	
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'admin_message' . ' AS a WHERE 1 AND ' . $where;
	$total = $GLOBALS ['db']->query_first_slave ( $sql );
	$filter ['record_count'] = $total ['total'];
	
	// 分页大小
	$filter = page_and_size ( $filter );
	
	$sql = 'SELECT a.message_id,a.sender_id,a.receiver_id,a.send_date,a.read_date,a.deleted,a.title,a.message,b.user_name' . ' FROM ' . TABLE_PREFIX . 'admin_message' . ' AS a,' . TABLE_PREFIX . 'admin' . ' AS b ' . ' WHERE a.sender_id=b.user_id AND ' . $where . ' ORDER BY ' . $filter ['sort_by'] . ' ' . $filter ['sort_order'];
	
	$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
	$array = array ();
	$res = $GLOBALS ['db']->query_first_slave ( $sql );
	while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
		$row ['send_date'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $row ['send_date'] );
		$row ['read_date'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $row ['read_date'] );
		$array [] = $row;
	}
	
	$arr = array ('item' => $array, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
	
	return $arr;
}
?>