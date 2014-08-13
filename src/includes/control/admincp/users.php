<?php
// #######################################################################
// ######################## users.php 私有函数      ##########################
// #######################################################################


/**
 * 返回用户列表数据
 *
 * @access  public
 * @param
 *
 * @return Array
 */
function get_user_list() {
	
	$result = get_filter ();
	if ($result === false) {
		
		$GLOBALS ['skyuc']->input->clean_array_gpc ( 'r', array ('keywords' => TYPE_STR, 'intro_type' => TYPE_STR, 'sort_by' => TYPE_STR, 'sort_order' => TYPE_STR, 'no_day' => TYPE_UINT, 'no_count' => TYPE_UINT, 'no_money' => TYPE_UINT, 'no_point' => TYPE_UINT ) );
		
		// 过滤条件
		$filter ['keywords'] = $GLOBALS ['skyuc']->GPC ['keywords'];
		$filter ['intro_type'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['intro_type'] ), 'no', $GLOBALS ['skyuc']->GPC ['intro_type'] );
		$filter ['sort_by'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_by'] ), 'user_id', $GLOBALS ['skyuc']->GPC ['sort_by'] );
		$filter ['sort_order'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_order'] ), 'DESC', $GLOBALS ['skyuc']->GPC ['sort_order'] );
		$filter ['no_day'] = $GLOBALS ['skyuc']->GPC ['no_day'];
		$filter ['no_count'] = $GLOBALS ['skyuc']->GPC ['no_count'];
		$filter ['no_money'] = $GLOBALS ['skyuc']->GPC ['no_money'];
		$filter ['no_point'] = $GLOBALS ['skyuc']->GPC ['no_point'];
		
		$ex_where = ' WHERE 1 ';
		
		if ($filter ['keywords']) {
			$ex_where .= " AND user_name LIKE '%" . $GLOBALS ['db']->escape_string_like ( $filter ['keywords'] ) . "%'";
		}
		
		// 过滤usertype=1为通过包月会员
		switch ($filter ['intro_type']) {
			case 'is_day' :
				$ex_where .= ' AND usertype=1';
				break;
			case 'is_count' :
				$ex_where .= ' AND usertype=0';
				break;
			case 'is_validated' :
				$ex_where .= ' AND is_validated=1';
				break;
			case 'no_validated' :
				$ex_where .= ' AND is_validated=0';
				break;
			// 过滤器为空，关键字不为空，防止语句出错
			case 'no' :
				$ex_where = $ex_where;
				break;
		
		}
		
		if ($filter ['no_day']) {
			$ex_where .= ' AND unit_date < ' . TIMENOW;
		}
		if ($filter ['no_count']) {
			$ex_where .= ' AND user_point < 1';
		}
		if ($filter ['no_point']) {
			$ex_where .= ' AND pay_point < 1 ';
		}
		if ($filter ['no_money']) {
			$ex_where .= ' AND user_money < 1 ';
		}
		if ($ex_where == ' WHERE 1 ') {
			$extention = '';
		} else {
			$sql = 'SELECT user_id FROM ' . TABLE_PREFIX . 'users' . $ex_where;
			$res = $GLOBALS ['db']->query_read ( $sql );
			$ids = array ();
			while ( $row = $GLOBALS ['db']->fetch_row ( $res ) ) {
				$ids [] = $row [0];
			}
			unset ( $row );
			$extention = db_create_in ( $ids );
		}
		
		$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'users' . $ex_where;
		$total = $GLOBALS ['db']->query_first ( $sql );
		$filter ['record_count'] = $total ['total'];
		
		// 分页大小
		$filter = page_and_size ( $filter );
		$sql = 'SELECT user_id, user_name, email, reg_time, pay_point,	user_point,	unit_date,	usertype 	' . ' FROM ' . TABLE_PREFIX . 'users' . $ex_where . ' ORDER by ' . $filter ['sort_by'] . ' ' . $filter ['sort_order'];
		$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
		set_filter ( $filter, $sql );
	} else {
		$sql = $result ['sql'];
		$filter = $result ['filter'];
	}
	
	$user_list = array ();
	$result = $GLOBALS ['db']->query_read ( $sql );
	while ( $row = $GLOBALS ['db']->fetch_array ( $result ) ) {
		if ($row ['unit_date'] > 0) {
			$row ['unit_date'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'], $row ['unit_date'] );
		} else {
			$row ['unit_date'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'], $row ['reg_time'] );
		}
		
		$row ['reg_time'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $row ['reg_time'] );
		
		$user_list [] = $row;
	}
	
	$arr = array ('user_list' => $user_list, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
	
	return $arr;
}

?>