<?php
// #######################################################################
// ######################## user_online.php 私有函数      ################
// #######################################################################


/**
 * 获取在线用户列表
 *
 * @access  public
 * @param
 *
 * @return void
 */
function get_online_list() {
	
	$GLOBALS ['skyuc']->input->clean_array_gpc ( 'r', array ('sort_by' => TYPE_STR, 'sort_order' => TYPE_STR ) );
	
	// 过滤条件
	$filter ['sort_by'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_by'] ), 'lastactivity', $GLOBALS ['skyuc']->GPC ['sort_by'] );
	$filter ['sort_order'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_order'] ), 'DESC', $GLOBALS ['skyuc']->GPC ['sort_order'] );
	
	// 在线会员
	$total = $GLOBALS ['db']->query_first_slave ( 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'session' );
	$filter ['record_count'] = $total ['total'];
	
	// 分页大小
	$filter = page_and_size ( $filter );
	
	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'session' . ' ORDER by ' . $filter ['sort_by'] . ' ' . $filter ['sort_order'];
	$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
	$res = $GLOBALS ['db']->query_read_slave ( $sql );
	if ($res !== false) {
		$arr = array ();
		while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
			
			$row ['os'] = get_os ( $row ['useragent'] );
			$row ['browser'] = getbrowser ( $row ['useragent'] );
			$row ['lastactivity'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $row ['lastactivity'], true );
			//管理员
			if ($row ['adminid'] > 0) {
				$sql = 'SELECT  user_name FROM ' . TABLE_PREFIX . 'admin' . " WHERE user_id='" . $row ['adminid'] . "'";
				$rs = $GLOBALS ['db']->query_first_slave ( $sql );
				$row ['user_name'] = $rs ['user_name'] . $GLOBALS ['_LANG'] ['admin'];
			} //游客
elseif (empty ( $row ['userid'] )) {
				$row ['user_name'] = $GLOBALS ['_LANG'] ['anonymous'];
			} else //会员
{
				$sql = 'SELECT  user_name FROM ' . TABLE_PREFIX . 'users' . " WHERE user_id='" . $row ['userid'] . "'";
				$rs = $GLOBALS ['db']->query_first_slave ( $sql );
				$row ['user_name'] = $rs ['user_name'];
			}
			$row ['url'] = get_domain () . $row ['location'];
			$arr [] = $row;
		}
	}
	
	$arr = array ('online_list' => $arr, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
	
	return $arr;
}

?>