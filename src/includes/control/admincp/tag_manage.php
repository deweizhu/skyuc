<?php
// #######################################################################
// ######################## tag_manage.php 私有函数    ###################
// #######################################################################


/**
 * 判断同一影片的标签是否唯一
 *
 * @param $name  标签名
 * @param $id  标签id
 * @return bool
 */
function tag_is_only($name, $tag_id, $show_id = '') {
	
	if (empty ( $show_id )) {
		$sql = 'SELECT show_id FROM ' . TABLE_PREFIX . 'tag' . ' WHERE tag_id = ' . $tag_id;
		$row = $GLOBALS ['db']->query_first ( $sql );
		$show_id = $row ['show_id'];
	}
	
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'tag' . " WHERE tag_words = '" . $GLOBALS ['db']->escape_string ( $name ) . "'" . ' AND show_id = ' . $show_id . ' AND tag_id != ' . $tag_id;
	$total = $GLOBALS ['db']->query_first ( $sql );
	if ($total ['total'] > 0) {
		return false;
	} else {
		return true;
	}
}

/**
 * 更新标签
 *
 * @param  $name
 * @param  $id
 * @return void
 */
function edit_tag($name, $id, $show_id = '') {
	
	$sql = 'UPDATE ' . TABLE_PREFIX . 'tag' . " SET tag_words = '" . $GLOBALS ['db']->escape_string ( $name ) . "'";
	if (! empty ( $show_id )) {
		$sql .= ', show_id = ' . $show_id;
	}
	$sql .= ' WHERE tag_id = ' . $id;
	
	$GLOBALS ['db']->query_write ( $sql );
	
	admin_log ( $name, 'edit', 'tag' );
}

/**
 * 获取标签数据列表
 * @access  public
 * @return  array
 */
function get_tag_list() {
	
	$GLOBALS ['skyuc']->input->clean_array_gpc ( 'r', array ('sort_by' => TYPE_STR, 'sort_order' => TYPE_STR ) );
	
	$filter ['sort_by'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_by'] ), 't.tag_id', $GLOBALS ['skyuc']->GPC ['sort_by'] );
	$filter ['sort_order'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_order'] ), 'DESC', $GLOBALS ['skyuc']->GPC ['sort_order'] );
	
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'tag';
	$total = $GLOBALS ['db']->query_first ( $sql );
	$filter ['record_count'] = $total ['total'];
	
	$filter = page_and_size ( $filter );
	
	$sql = 'SELECT t.tag_id, u.user_name, t.show_id, m.title, t.tag_words ' . ' FROM ' . TABLE_PREFIX . 'tag' . ' AS t ' . ' LEFT JOIN ' . TABLE_PREFIX . 'users' . ' AS u ON u.user_id=t.user_id ' . ' LEFT JOIN ' . TABLE_PREFIX . 'show' . ' AS m ON m.show_id=t.show_id ' . ' ORDER by ' . $filter ['sort_by'] . ' ' . $filter ['sort_order'];
	$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
	$res = $GLOBALS ['db']->query_read ( $sql );
	$tag = array ();
	while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
		$row ['tag_words'] = htmlspecialchars ( $row ['tag_words'] );
		$tag [] = $row;
	}
	
	$arr = array ('tags' => $tag, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
	
	return $arr;
}

/**
 * 取得标签的信息
 * return array
 */

function get_tag_info($tag_id) {
	
	$sql = 'SELECT t.tag_id, t.tag_words, t.show_id, m.title FROM ' . TABLE_PREFIX . 'tag' . ' AS t' . ' LEFT JOIN ' . TABLE_PREFIX . 'show' . ' AS m ON t.show_id=m.show_id' . " WHERE tag_id = '$tag_id'";
	$row = $GLOBALS ['db']->query_first ( $sql );
	
	return $row;
}

?>