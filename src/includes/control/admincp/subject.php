<?php
// #######################################################################
// ######################## subject.php 私有函数    #######################
// #######################################################################


/**
 * 获取专题列表
 *
 * @param
 *
 * @return  array()
 */
function get_subject_list() {
	
	// 过滤条件
	$GLOBALS ['skyuc']->input->clean_array_gpc ( 'r', array ('keyword' => TYPE_STR, 'sort_by' => TYPE_STR, 'sort_order' => TYPE_STR ) );
	$filter ['keyword'] = $GLOBALS ['skyuc']->GPC ['keyword'];
	$filter ['sort_by'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_by'] ), 'id', $GLOBALS ['skyuc']->GPC ['sort_by'] );
	$filter ['sort_order'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_order'] ), 'DESC', $GLOBALS ['skyuc']->GPC ['sort_order'] );
	
	// 关键字
	$where = 1;
	if (! empty ( $filter ['keyword'] )) {
		$where .= " AND title LIKE '%" . $GLOBALS ['db']->escape_string_like ( $filter ['keyword'] ) . "%'";
	}
	
	// 记录总数
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'subject';
	$total = $GLOBALS ['db']->query_first ( $sql );
	$filter ['record_count'] = $total ['total'];
	
	// 分页大小
	$filter = page_and_size ( $filter );
	$sql = 'SELECT id,	title,	thumb,	poster,	intro, add_time, recom, uselink, link FROM ' . TABLE_PREFIX . 'subject' . '  WHERE ' . $where . ' ORDER BY ' . $filter ['sort_by'] . ' ' . $filter ['sort_order'];
	$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
	$res = $GLOBALS ['db']->query_read ( $sql );
	$subject = array ();
	while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
		$row ['add_time'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $row ['add_time'] );
		$subject [] = $row;
	}
	
	$arr = array ('subject' => $subject, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
	
	return $arr;
}
?>