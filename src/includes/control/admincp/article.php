<?php
// #######################################################################
// ######################## article.php 私有函数      ########################
// #######################################################################


/**
 * 把影片删除关联
 *
 * @return void
 */
function drop_link_show($show_id, $article_id) {
	
	$sql = 'DELETE FROM ' . TABLE_PREFIX . 'show_article' . " WHERE show_id = '$show_id' AND article_id = '$article_id'";
	$GLOBALS ['db']->query_write ( $sql );
	create_result ( true, '', $show_id );
}

/**
 * 取得文章关联影片
 *
 * @return array
 */
function get_article_show($article_id) {
	
	$list = array ();
	$sql = 'SELECT m.show_id, m.title' . ' FROM ' . TABLE_PREFIX . 'show_article' . ' AS a' . ' LEFT JOIN ' . TABLE_PREFIX . 'show' . ' AS m ON m.show_id = a.show_id' . " WHERE a.article_id = '$article_id'";
	$list = $GLOBALS ['db']->query_all_slave ( $sql );
	
	return $list;
}

/**
 * 获得文章列表
 *
 * @return array
 */
function get_articleslist() {
	
	$GLOBALS ['skyuc']->input->clean_array_gpc ( 'p', array ('keyword' => TYPE_STR, 'cat_id' => TYPE_UINT, 'sort_by' => TYPE_STR, 'sort_order' => TYPE_STR ) );
	
	$result = get_filter ();
	if ($result === false) {
		$filter = array ();
		$filter ['keyword'] = $GLOBALS ['skyuc']->GPC ['keyword'];
		$filter ['cat_id'] = $GLOBALS ['skyuc']->GPC ['cat_id'];
		$filter ['sort_by'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_by'] ), 'a.article_id', $GLOBALS ['skyuc']->GPC ['sort_by'] );
		$filter ['sort_order'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_order'] ), 'DESC', $GLOBALS ['skyuc']->GPC ['sort_order'] );
		
		$where = '';
		if (! empty ( $filter ['keyword'] )) {
			$where = " AND a.title LIKE '%" . $GLOBALS ['db']->escape_string_like ( $filter ['keyword'] ) . "%'";
		}
		if ($filter ['cat_id']) {
			$where .= " AND a." . get_article_children ( $filter ['cat_id'] );
		}
		
		//文章总数
		$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'article' . ' AS a ' . 'LEFT JOIN ' . TABLE_PREFIX . 'article_cat' . ' AS ac ON ac.cat_id = a.cat_id ' . 'WHERE 1 ' . $where;
		$total = $GLOBALS ['db']->query_first_slave ( $sql );
		$filter ['record_count'] = $total ['total'];
		
		$filter = page_and_size ( $filter );
		
		// 获取文章数据
		$arr = array ();
		$sql = 'SELECT a.* , ac.cat_name ' . 'FROM ' . TABLE_PREFIX . 'article' . ' AS a ' . 'LEFT JOIN ' . TABLE_PREFIX . 'article_cat' . ' AS ac ON ac.cat_id = a.cat_id ' . 'WHERE 1 ' . $where . ' ORDER by ' . $filter ['sort_by'] . ' ' . $filter ['sort_order'];
		set_filter ( $filter, $sql );
	} else {
		$sql = $result ['sql'];
		$filter = $result ['filter'];
	}
	$arr = array ();
	$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
	$res = $GLOBALS ['db']->query_read_slave ( $sql );
	
	while ( $rows = $GLOBALS ['db']->fetch_array ( $res ) ) {
		$rows ['date'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $rows ['add_time'], false, false );
		$arr [] = $rows;
	}
	$filter ['keyword'] = stripslashes ( $filter ['keyword'] );
	return array ('arr' => $arr, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
}
?>