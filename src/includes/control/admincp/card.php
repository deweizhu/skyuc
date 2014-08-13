<?php
// #######################################################################
// ######################## card.php 私有函数      ##########################
// #######################################################################


/**
 * 影卡列表
 *
 * @access public
 * @return array
 */
function get_card_list() {
	
	$GLOBALS ['skyuc']->input->clean_array_gpc ( 'r', array ('intro_type' => TYPE_UINT, 'keyword' => TYPE_STR, 'sort_by' => TYPE_STR, 'sort_order' => TYPE_STR ) );
	
	// 过滤条件
	$filter ['intro_type'] = $GLOBALS ['skyuc']->GPC ['intro_type'];
	$filter ['keyword'] = $GLOBALS ['skyuc']->GPC ['keyword'];
	$filter ['sort_by'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_by'] ), 'id', $GLOBALS ['skyuc']->GPC ['sort_by'] );
	$filter ['sort_order'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_order'] ), 'DESC', $GLOBALS ['skyuc']->GPC ['sort_order'] );
	$filter ['is_cardlist'] = 1;
	
	// 过滤卡等级
	if ($filter ['intro_type'] > 0) {
		$where = " WHERE c.rank_id='" . $filter ['intro_type'] . "'";
	} else {
		$where = ' WHERE 1';
	}
	
	// 关键字
	if (! empty ( $filter ['keyword'] )) {
		$where .= " AND cardid LIKE '%" . $GLOBALS ['db']->escape_string_like ( $filter ['keyword'] ) . "%'";
	}
	
	// 记录总数
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'card' . ' AS c  ' . $where;
	$total = $GLOBALS ['db']->query_first ( $sql );
	$filter ['record_count'] = $total ['total'];
	
	// 分页大小
	$filter = page_and_size ( $filter );
	
	$sql = 'SELECT c.*, u.rank_name AS rank_name  FROM ' . TABLE_PREFIX . 'card' . ' AS c ' . ' LEFT JOIN ' . TABLE_PREFIX . 'user_rank' . ' AS u ON u.rank_id=c.rank_id' . ' ' . $where . ' ORDER BY ' . $filter ['sort_by'] . ' ' . $filter ['sort_order'];
	$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
	$row = $GLOBALS ['db']->query_all_slave ( $sql );
	foreach ( $row as $key => $value ) {
		$row [$key] ['addtime'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'], $row [$key] ['addtime'], true, false );
		$row [$key] ['endtime'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'], $row [$key] ['endtime'], true, false );
	}
	
	$arr = array ('card' => $row, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
	
	return $arr;

}

/**
 * 已充值影卡记录列表
 *
 * @access public
 * @return array
 */
function get_cardlog_list() {
	
	$GLOBALS ['skyuc']->input->clean_array_gpc ( 'r', array ('intro_type' => TYPE_UINT, 'keyword' => TYPE_STR, 'sort_by' => TYPE_STR, 'sort_order' => TYPE_STR ) );
	
	// 过滤条件
	$filter ['intro_type'] = $GLOBALS ['skyuc']->GPC ['intro_type'];
	$filter ['keyword'] = $GLOBALS ['skyuc']->GPC ['keyword'];
	$filter ['sort_by'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_by'] ), 'id', $GLOBALS ['skyuc']->GPC ['sort_by'] );
	$filter ['sort_order'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_order'] ), 'DESC', $GLOBALS ['skyuc']->GPC ['sort_order'] );
	$filter ['is_cardlist'] = 0;
	
	// 过滤卡等级
	if ($filter ['intro_type'] > 0) {
		$where = " WHERE c.rank_id='" . $filter ['intro_type'] . "'";
	} else {
		$where = ' WHERE 1';
	}
	
	// 关键字
	if (! empty ( $filter ['keyword'] )) {
		$where .= " AND cardid LIKE '%" . $GLOBALS ['db']->escape_string_like ( $filter ['keyword'] ) . "%'";
	}
	
	// 记录总数
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'card_log' . ' AS c  ' . $where;
	$total = $GLOBALS ['db']->query_first ( $sql );
	$filter ['record_count'] = $total ['total'];
	
	// 分页大小
	$filter = page_and_size ( $filter );
	
	$sql = 'SELECT c.*, u.rank_name AS rank_name  FROM ' . TABLE_PREFIX . 'card_log' . ' AS c ' . ' LEFT JOIN ' . TABLE_PREFIX . 'user_rank' . ' AS u ON u.rank_id=c.rank_id' . ' ' . $where . ' ORDER BY ' . $filter ['sort_by'] . ' ' . $filter ['sort_order'];
	$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
	$row = $GLOBALS ['db']->query_all_slave ( $sql );
	foreach ( $row as $key => $value ) {
		$row [$key] ['addtime'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'], $row [$key] ['addtime'], true, false );
	}
	
	$arr = array ('card' => $row, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
	
	return $arr;

}

?>