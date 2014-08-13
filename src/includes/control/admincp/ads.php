<?php
// #######################################################################
// ######################## ads.php 私有函数      ########################
// #######################################################################


/**
 * 获取广告数据列表
 *
 * @return array
 */
function get_adslist() {
	
	// 过滤查询
	$GLOBALS ['skyuc']->input->clean_array_gpc ( 'r', array ('pid' => TYPE_UINT, 'sort_by' => TYPE_STR, 'sort_order' => TYPE_STR ) );
	
	$pid = $GLOBALS ['skyuc']->GPC ['pid'];
	
	$filter = array ();
	$filter ['sort_by'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_by'] ), 'ad.ad_name', $GLOBALS ['skyuc']->GPC ['sort_by'] );
	$filter ['sort_order'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_order'] ), 'DESC', $GLOBALS ['skyuc']->GPC ['sort_order'] );
	
	$where = 'WHERE 1 ';
	if ($pid > 0) {
		$where .= " AND ad.position_id = '$pid' ";
	}
	
	// 获得总记录数据
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'ad' . ' AS ad ' . $where;
	$total = $GLOBALS ['db']->query_first ( $sql );
	$filter ['record_count'] = $total ['total'];
	
	$filter = page_and_size ( $filter );
	
	// 获得广告数据
	$arr = array ();
	$sql = 'SELECT ad.*, p.position_name ' . 'FROM ' . TABLE_PREFIX . 'ad' . ' AS ad ' . 'LEFT JOIN ' . TABLE_PREFIX . 'ad_position' . ' AS p ON p.position_id = ad.position_id ' . $where . ' ORDER by ' . $filter ['sort_by'] . ' ' . $filter ['sort_order'];
	$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
	$res = $GLOBALS ['db']->query_read_slave ( $sql );
	
	while ( $rows = $GLOBALS ['db']->fetch_array ( $res ) ) {
		// 广告类型的名称
		$rows ['type'] = ($rows ['media_type'] == 0) ? $GLOBALS ['_LANG'] ['ad_img'] : '';
		$rows ['type'] .= ($rows ['media_type'] == 1) ? $GLOBALS ['_LANG'] ['ad_flash'] : '';
		$rows ['type'] .= ($rows ['media_type'] == 2) ? $GLOBALS ['_LANG'] ['ad_html'] : '';
		$rows ['type'] .= ($rows ['media_type'] == 3) ? $GLOBALS ['_LANG'] ['ad_text'] : '';
		
		//广告时间
		$rows ['start_date'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'], $rows ['start_date'], false, false );
		$rows ['end_date'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'], $rows ['end_date'], false, false );
		
		$arr [] = $rows;
	}
	
	return array ('ads' => $arr, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
}
?>