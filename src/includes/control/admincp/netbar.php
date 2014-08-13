<?php
// #######################################################################
// ######################## netbar.php 私有函数      #########################
// #######################################################################


/**
 * 获得网吧列表,返回数组
 *
 * @access  public
 * @return  array
 */
function get_netbar_list() {
	
	$GLOBALS ['skyuc']->input->clean_array_gpc ( 'r', array ('intro_type' => TYPE_STR, 'keyword' => TYPE_STR, 'sort_by' => TYPE_STR, 'sort_order' => TYPE_STR ) );
	
	// 过滤条件
	$filter ['intro_type'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['intro_type'] ), 'no', $GLOBALS ['skyuc']->GPC ['intro_type'] );
	$filter ['keyword'] = $GLOBALS ['skyuc']->GPC ['keyword'];
	$filter ['sort_by'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_by'] ), 'id', $GLOBALS ['skyuc']->GPC ['sort_by'] );
	$filter ['sort_order'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_order'] ), 'DESC', $GLOBALS ['skyuc']->GPC ['sort_order'] );
	
	// 过滤is_ok=1为通过审核
	switch ($filter ['intro_type']) {
		case 'is_ok' :
			$where = ' WHERE is_ok=1';
			break;
		case 'is_no' :
			$where = ' WHERE is_ok=0';
			break;
		// 过滤器为空，关键字不为空，防止语句出错
		case 'no' :
			$where = ' WHERE id<>0';
			break;
	
	}
	
	// 关键字
	if (! empty ( $filter ['keyword'] )) {
		$where .= " AND title LIKE '%" . $GLOBALS ['db']->escape_string ( $filter ['keyword'] ) . "%'";
	}
	// 记录总数
	$sql = 'SELECT COUNT(*)	AS total FROM ' . TABLE_PREFIX . 'netbar' . '  ' . $where;
	$total = $GLOBALS ['db']->query_first ( $sql );
	$filter ['record_count'] = $total ['total'];
	
	// 分页大小
	$filter = page_and_size ( $filter );
	
	include (DIR . '/includes/functions_player.php');
	
	$sql = 'SELECT *  FROM ' . TABLE_PREFIX . 'netbar' . '   ' . $where . ' ORDER BY ' . $filter ['sort_by'] . ' ' . $filter ['sort_order'];
	$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
	$row = $GLOBALS ['db']->query_all_slave ( $sql );
	
	foreach ( $row as $key => $value ) {
		$row [$key] ['endtime'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'], $row [$key] ['endtime'], false, false );
		$row [$key] ['addtime'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'], $row [$key] ['addtime'], false, false );
		$row [$key] ['online'] = online_count ( $row [$key] ['id'] ) - 1; //当前在线人数，不包括管理员
	}
	
	$arr = array ('netbar' => $row, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
	
	return $arr;

}

?>