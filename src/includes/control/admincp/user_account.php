<?php
// #######################################################################
// ######################## user_account.php 私有函数    #################
// #######################################################################


/*------------------------------------------------------ */
//-- 会员余额函数部分
/*------------------------------------------------------ */
/**
 * 查询会员余额的数量
 * @access  public
 * @param   int     $user_id        会员ID
 * @return  int
 */
function get_user_surplus($user_id) {
	
	$sql = 'SELECT SUM(user_money) AS total FROM ' . TABLE_PREFIX . 'account_log' . ' WHERE user_id = ' . $user_id;
	$row = $GLOBALS ['db']->query_first ( $sql );
	
	return $row ['total'];
}

/**
 * 更新会员账目明细
 *
 * @access  public
 * @param   int     $id          帐目ID
 * @param   string     $admin_note  管理员描述
 * @param   float     $amount      操作的金额
 * @param   int     $is_paid     是否已完成
 *
 * @return  int
 */
function update_user_account($id, $amount, $admin_note, $is_paid) {
	
	$admin_name = fetch_adminutil_text ( $GLOBALS ['skyuc']->session->vars ['adminid'] . '_admin_name' );
	$sql = 'UPDATE ' . TABLE_PREFIX . 'user_account' . ' SET ' . "admin_user  = '" . $GLOBALS ['db']->escape_string ( $admin_name ) . "', " . "amount      = '$amount', " . "paid_time   = '" . TIMENOW . "', " . "admin_note  = '" . $GLOBALS ['db']->escape_string ( $admin_note ) . "', " . "is_paid     = '$is_paid' WHERE id = '$id'";
	return $GLOBALS ['db']->query_write ( $sql );
}

/**
 *
 * 账户金额列表
 * @access  public
 * @param
 *
 * @return void
 */
function account_list() {
	
	$result = get_filter ();
	if ($result === false) {
		// 过滤列表
		$GLOBALS ['skyuc']->input->clean_array_gpc ( 'r', array ('id' => TYPE_UINT, 'keywords' => TYPE_STR, 'process_type' => TYPE_INT, 'payment' => TYPE_STR, 'is_paid' => TYPE_UINT, 'sort_by' => TYPE_STR, 'sort_order' => TYPE_STR ) );
		
		$filter ['user_id'] = $GLOBALS ['skyuc']->GPC ['id'];
		$filter ['keywords'] = $GLOBALS ['skyuc']->GPC ['keywords'];
		$filter ['process_type'] = iif ( $GLOBALS ['skyuc']->GPC_exists ['process_type'], $GLOBALS ['skyuc']->GPC ['process_type'], - 1 );
		$filter ['payment'] = $GLOBALS ['skyuc']->GPC ['payment'];
		$filter ['is_paid'] = iif ( $GLOBALS ['skyuc']->GPC_exists ['is_paid'], $GLOBALS ['skyuc']->GPC ['is_paid'], - 1 );
		$filter ['sort_by'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_by'] ), 'add_time', $GLOBALS ['skyuc']->GPC ['sort_by'] );
		$filter ['sort_order'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_order'] ), 'DESC', $GLOBALS ['skyuc']->GPC ['sort_order'] );
		
		$where = ' WHERE 1 ';
		if ($filter ['user_id'] > 0) {
			$where .= " AND ua.user_id = '" . $filter ['user_id'] . "' ";
		}
		if ($filter ['process_type'] != - 1) {
			$where .= " AND ua.process_type = '" . $filter ['process_type'] . "' ";
		} else {
			$where .= " AND ua.process_type " . db_create_in ( array (SURPLUS_SAVE, SURPLUS_RETURN ) );
		}
		if ($filter ['payment']) {
			$where .= " AND ua.payment = '" . $filter ['payment'] . "' ";
		}
		if ($filter ['is_paid'] != - 1) {
			$where .= " AND ua.is_paid = '" . $filter ['is_paid'] . "' ";
		}
		
		$where .= ' AND ua.user_id = u.user_id ';
		if ($filter ['keywords']) {
			$where .= " AND u.user_name LIKE '%" . $GLOBALS ['db']->escape_string_like ( $filter ['keywords'] ) . "%'";
		
		}
		
		$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'user_account' . ' AS ua, ' . TABLE_PREFIX . 'users' . ' AS u ' . $where;
		
		$total = $GLOBALS ['db']->query_first ( $sql );
		
		$filter ['record_count'] = $total ['total'];
		
		// 分页大小
		$filter = page_and_size ( $filter );
		
		// 查询数据
		$sql = 'SELECT ua.*, u.user_name FROM ' . TABLE_PREFIX . 'user_account' . ' AS ua, ' . TABLE_PREFIX . 'users' . ' AS u ' . $where . 'ORDER by ' . $filter ['sort_by'] . ' ' . $filter ['sort_order'];
		$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
		set_filter ( $filter, $sql );
	} else {
		$sql = $result ['sql'];
		$filter = $result ['filter'];
	}
	$list = array ();
	$res = $GLOBALS ['db']->query_read ( $sql );
	while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
		$row ['surplus_amount'] = price_format ( abs ( $row ['amount'] ), false );
		$row ['add_date'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $row ['add_time'] );
		$row ['process_type_name'] = $GLOBALS ['_LANG'] ['surplus_type_' . $row ['process_type']];
		
		$list [] = $row;
	}
	
	$arr = array ('list' => $list, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
	
	return $arr;
}

/**
 * 生成链接后缀
 */
function list_link_postfix() {
	return 'uselastfilter=1';
}
?>