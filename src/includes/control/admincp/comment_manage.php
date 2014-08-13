<?php
// #######################################################################
// ######################## comment_manage.php 私有函数    ###############
// #######################################################################


/**
 * 获取评论列表
 * @access  public
 * @return  array
 */
function get_comment_list() {
	
	$GLOBALS ['skyuc']->input->clean_array_gpc ( 'r', array ('keywords' => TYPE_STR, 'sort_by' => TYPE_STR, 'sort_order' => TYPE_STR ) );
	
	// 查询条件
	$filter ['keywords'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['keywords'] ), 0, $GLOBALS ['skyuc']->GPC ['keywords'] );
	$filter ['sort_by'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_by'] ), 'add_time', $GLOBALS ['skyuc']->GPC ['sort_by'] );
	$filter ['sort_order'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_order'] ), 'DESC', $GLOBALS ['skyuc']->GPC ['sort_order'] );
	
	$where = (! empty ( $filter ['keywords'] )) ? " AND content LIKE '%" . $GLOBALS ['db']->escape_string_like ( $filter ['keywords'] ) . "%' " : '';
	
	$sql = 'SELECT count(*) AS total FROM ' . TABLE_PREFIX . 'comment' . ' WHERE parent_id = 0 ' . $where;
	$total = $GLOBALS ['db']->query_first ( $sql );
	$filter ['record_count'] = $total ['total'];
	
	// 分页大小
	$filter = page_and_size ( $filter );
	
	// 获取评论数据
	$arr = array ();
	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'comment' . ' WHERE status < 2 ' . $where . ' ORDER BY ' . $filter
    ['sort_by'] . ' ' . $filter ['sort_order'];
	$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
	$res = $GLOBALS ['db']->query_read ( $sql );
	
	while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
		$sql = ($row ['comment_type'] == 0) ? 'SELECT title FROM ' . TABLE_PREFIX . 'show' . ' WHERE show_id=' . $row ['id_value'] : 'SELECT title FROM ' . TABLE_PREFIX . 'article' . ' WHERE article_id=' . $row ['id_value'];
		$title = $GLOBALS ['db']->query_first ( $sql );
		
		$row ['title'] = $title ['title'];
		$row ['is_reply'] = empty ( $row ['status'] ) ? $GLOBALS ['_LANG'] ['hidden'] : $GLOBALS ['_LANG'] ['display'];
		
		$row ['add_time'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $row ['add_time'], true );
		
		$arr [] = $row;
	}
	
	$arr = array ('item' => $arr, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
	
	return $arr;
}

?>