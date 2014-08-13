<?php
// #######################################################################
// ######################## ad_position.php 私有函数      ####################
// #######################################################################
/**
 * 获取广告位置列表
 *
 * @return array
 */
function ad_position_list() {
	
	$filter = array ();
	
	//记录总数以及页数
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'ad_position';
	$total = $GLOBALS ['db']->query_first_slave ( $sql );
	$filter ['record_count'] = $total ['total'];
	
	$filter = page_and_size ( $filter );
	
	// 查询数据
	$arr = array ();
	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'ad_position' . ' ORDER BY position_id DESC ';
	$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
	$res = $GLOBALS ['db']->query_read_slave ( $sql );
	while ( $rows = $GLOBALS ['db']->fetch_array ( $res ) ) {
		$position_desc = ! empty ( $rows ['position_desc'] ) ? sub_str ( $rows ['position_desc'], 50 ) : '';
		$rows ['position_desc'] = nl2br ( htmlspecialchars ( $position_desc ) );
		
		$arr [] = $rows;
	}
	
	return array ('position' => $arr, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
}

?>