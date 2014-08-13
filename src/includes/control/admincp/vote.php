<?php
// #######################################################################
// ######################## vote.php 私有函数      ###########################
// #######################################################################


/**
 * 获取在线调查数据列表
 *
 * @return array
 */
function get_votelist() {
	
	$filter = array ();
	
	// 记录总数以及页数
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'vote';
	$total = $GLOBALS ['db']->query_first ( $sql );
	$filter ['record_count'] = $total ['total'];
	
	$filter = page_and_size ( $filter );
	
	// 查询数据
	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'vote' . ' ORDER BY vote_id DESC ';
	$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
	$res = $GLOBALS ['db']->query_read_slave ( $sql );
	
	$list = array ();
	while ( $rows = $GLOBALS ['db']->fetch_array ( $res ) ) {
		$rows ['begin_date'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'], $rows ['begin_date'], true, false );
		$rows ['end_date'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'], $rows ['end_date'], true, false );
		$list [] = $rows;
	}
	
	return array ('list' => $list, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
}
/**
 * 获取调查选项列表
 *
 * @return array
 */
function get_optionlist($id) {
	
	$list = array ();
	$sql = 'SELECT option_id, vote_id, option_name, option_count' . ' FROM ' . TABLE_PREFIX . 'vote_option' . " WHERE vote_id = '$id' ORDER BY option_id DESC";
	$res = $GLOBALS ['db']->query ( $sql );
	while ( $rows = $GLOBALS ['db']->fetch_array ( $res ) ) {
		$list [] = $rows;
	}
	
	return $list;
}

?>