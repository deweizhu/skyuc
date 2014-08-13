<?php
// #######################################################################
// ######################## account_log.php 私有函数    ##################
// #######################################################################


/**
 * 取得帐户明细
 * @param   int     $user_id    用户id
 * @param   string  $account_type   帐户类型：空表示所有帐户，user_money表示可用资金，
 * pay_point表示消费积分
 * @return  array
 */
function get_accountlist($user_id, $account_type = '') {
	
	// 检查参数
	$where = ' WHERE user_id = ' . $user_id;
	if (in_array ( $account_type, array ('user_money', 'pay_point' ) )) {
		$where .= ' AND ' . $account_type . ' <> 0 ';
	}
	
	// 初始化分页参数
	$filter = array ('user_id' => $user_id, 'account_type' => $account_type );
	
	// 查询记录总数，计算分页数
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'account_log' . $where;
	$total = $GLOBALS ['db']->query_first ( $sql );
	$filter ['record_count'] = $total ['total'];
	$filter = page_and_size ( $filter );
	
	// 查询记录
	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'account_log' . $where . ' ORDER BY log_id DESC';
	$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
	$res = $GLOBALS ['db']->query_read ( $sql );
	
	$arr = array ();
	while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
		$row ['change_time'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $row ['change_time'] );
		$arr [] = $row;
	}
	
	return array ('account' => $arr, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
}
?>