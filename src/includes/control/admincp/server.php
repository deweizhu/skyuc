<?php
// #######################################################################
// ######################## server.php 私有函数    #######################
// #######################################################################


/**
 * 获取服务器列表
 *
 * @access  public
 * @return  array
 */
function get_serverlist() {
	
	$filter = array ();
	
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'server';
	$total = $GLOBALS ['db']->query_first ( $sql );
	$filter ['record_count'] = $total ['total'];
	
	$filter = page_and_size ( $filter );
	
	// 查询记录
	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'server' . ' ORDER BY sort_order ASC ';
	$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
	$res = $GLOBALS ['db']->query_read ( $sql );
	
	$arr = array ();
	while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
		$row ['server_url'] = empty ( $row ['server_url'] ) ? 'N/A' : '<a href="' . $row ['server_url'] . '" target="_brank">' . $row ['server_url'] . '</a>';
		
		$arr [] = $row;
	}
	return array ('server' => $arr, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
}

?>