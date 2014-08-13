<?php
// #######################################################################
// ######################## user_msg.php 私有函数      ###################
// #######################################################################


/**
 * 获取留言列表
 *
 * @access  public
 * @param
 *
 * @return void
 */
function get_msg_list() {
	
	$GLOBALS ['skyuc']->input->clean_array_gpc ( 'r', array ('keywords' => TYPE_STR, 'msg_type' => TYPE_UINT, 'sort_by' => TYPE_STR, 'sort_order' => TYPE_STR ) );
	
	// 过滤条件
	$filter ['keywords'] = $GLOBALS ['skyuc']->GPC ['keywords'];
	$filter ['msg_type'] = iif ( $GLOBALS ['skyuc']->GPC_exists ['msg_type'], $GLOBALS ['skyuc']->GPC ['msg_type'], - 1 );
	$filter ['sort_by'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_by'] ), 'msg_id', $GLOBALS ['skyuc']->GPC ['sort_by'] );
	$filter ['sort_order'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_order'] ), 'DESC', $GLOBALS ['skyuc']->GPC ['sort_order'] );
	
	$where = '';
	if ($filter ['keywords']) {
		$where .= " AND f.msg_title LIKE '%" . $GLOBALS ['db']->escape_string_like ( $filter ['keywords'] ) . "%' ";
	}
	if ($filter ['msg_type'] != - 1) {
		$where .= " AND f.msg_type = '" . $filter ['msg_type'] . "' ";
	}
	
	$sql = 'SELECT count(*) AS total FROM ' . TABLE_PREFIX . 'feedback' . ' AS f' . ' WHERE parent_id = 0 ' . $where;
	$total = $GLOBALS ['db']->query_first ( $sql );
	$filter ['record_count'] = $total ['total'];
	
	// 分页大小
	$filter = page_and_size ( $filter );
	
	$sql = 'SELECT f.msg_id, f.user_name, f.msg_title, f.msg_type, f.msg_time, COUNT(r.msg_id) AS reply ' . 'FROM ' . TABLE_PREFIX . 'feedback' . ' AS f ' . 'LEFT JOIN ' . TABLE_PREFIX . 'feedback' . ' AS r ON r.parent_id=f.msg_id ' . 'WHERE f.parent_id = 0  ' . $where . 'GROUP BY f.msg_id ' . 'ORDER by ' . $filter ['sort_by'] . ' ' . $filter ['sort_order'];
	$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
	$msg_list = array ();
	$res = $GLOBALS ['db']->query_read ( $sql );
	while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
		$row ['msg_time'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $row ['msg_time'] );
		$row ['msg_type'] = $GLOBALS ['_LANG'] ['type'] [$row ['msg_type']];
		$msg_list [] = $row;
	}
	
	$arr = array ('msg_list' => $msg_list, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
	
	return $arr;
}
/**
 * 获得留言的详细信息
 *
 * @param   integer $id
 *
 * @return  array
 */
function get_feedback_detail($id) {
	
	$sql = 'SELECT T1.*, T2.msg_id AS reply_id, T2.user_name  AS reply_name, u.email AS reply_email, ' . 'T2.msg_content AS reply_content , T2.msg_time AS reply_time, T2.user_name AS reply_name ' . 'FROM ' . TABLE_PREFIX . 'feedback' . ' AS T1 ' . 'LEFT JOIN ' . TABLE_PREFIX . 'admin' . ' AS u ON u.user_id=' . $GLOBALS ['skyuc']->session->vars ['adminid'] . ' ' . 'LEFT JOIN ' . TABLE_PREFIX . 'feedback' . ' AS T2 ON T2.parent_id=T1.msg_id ' . 'WHERE T1.msg_id = ' . $id;
	$msg = $GLOBALS ['db']->query_first ( $sql );
	if ($msg) {
		$msg ['msg_time'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $msg ['msg_time'] );
		$msg ['reply_time'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $msg ['reply_time'] );
	}
	
	return $msg;
}

?>