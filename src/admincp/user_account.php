<?php
/**
 * SKYUC! 会员帐目管理(包括预付款，余额)
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

/*------------------------------------------------------ */
//-- 会员余额记录列表
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list' || $skyuc->GPC ['act'] == '') {
	// 权限判断
	admin_priv ( 'surplus_manage' );

	$skyuc->input->clean_gpc ( 'r', 'id', TYPE_UINT );

	// 指定会员的ID为查询条件
	$user_id = $skyuc->GPC ['id'];

	// 获得支付方式列表
	$payment = array ();
	$sql = 'SELECT pay_id, pay_name FROM ' . TABLE_PREFIX . 'payment' . " WHERE enabled = 1 AND pay_code != 'cod' ORDER BY pay_id";
	$res = $db->query_read ( $sql );

	while ( $row = $db->fetch_array ( $res ) ) {
		$payment [$row ['pay_name']] = $row ['pay_name'];
	}

	$skyuc->input->clean_array_gpc ( 'r', array ('process_type' => TYPE_INT, 'is_paid' => TYPE_UINT ) );

	// 模板赋值
	if ($skyuc->GPC_exists ['process_type']) {
		$smarty->assign ( 'process_type_' . $skyuc->GPC ['process_type'], 'selected="selected"' );
	}
	if ($skyuc->GPC_exists ['is_paid']) {
		$smarty->assign ( 'is_paid_' . $skyuc->GPC ['is_paid'], 'selected="selected"' );
	}
	$smarty->assign ( 'ur_here', $_LANG ['05_user_account'] );
	$smarty->assign ( 'id', $user_id );
	$smarty->assign ( 'payment_list', $payment );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['surplus_add'], 'href' => 'user_account.php?act=add' ) );

	$list = account_list ();
	$smarty->assign ( 'list', $list ['list'] );
	$smarty->assign ( 'filter', $list ['filter'] );
	$smarty->assign ( 'record_count', $list ['record_count'] );
	$smarty->assign ( 'page_count', $list ['page_count'] );
	$smarty->assign ( 'full_page', 1 );

	assign_query_info ();
	$smarty->display ( 'user_account_list.tpl' );
}

/*------------------------------------------------------ */
//-- 添加/编辑会员余额页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'add' || $skyuc->GPC ['act'] == 'edit') {
	admin_priv ( 'surplus_manage' ); //权限判断


	$skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	$ur_here = ($skyuc->GPC ['act'] == 'add') ? $_LANG ['surplus_add'] : $_LANG ['surplus_edit'];
	$form_act = ($skyuc->GPC ['act'] == 'add') ? 'insert' : 'update';
	$id = $skyuc->GPC ['id'];

	// 获得支付方式列表
	$user_account = array ();
	$payment = array ();
	$sql = 'SELECT pay_id, pay_name FROM ' . TABLE_PREFIX . 'payment' . ' WHERE enabled = 1 ORDER BY pay_id';
	$res = $db->query_read ( $sql );

	while ( $row = $db->fetch_array ( $res ) ) {
		$payment [$row ['pay_name']] = $row ['pay_name'];
	}

	if ($skyuc->GPC ['act'] == 'edit') {
		// 取得余额信息
		$user_account = $db->query_first ( 'SELECT * FROM ' . TABLE_PREFIX . 'user_account' . ' WHERE id = ' . $id );

		// 如果是负数，去掉前面的符号
		$user_account ['amount'] = str_replace ( '-', '', $user_account ['amount'] );

		// 取得会员名称
		$sql = 'SELECT user_name FROM ' . TABLE_PREFIX . 'users' . ' WHERE user_id = ' . $user_account ['user_id'];
		$user = $db->query_first ( $sql );
		$user_name = $user ['user_name'];
	} else {
		$surplus_type = '';
		$user_name = '';
	}

	//模板赋值
	$smarty->assign ( 'ur_here', $ur_here );
	$smarty->assign ( 'form_act', $form_act );
	$smarty->assign ( 'payment_list', $payment );
	$smarty->assign ( 'action', $skyuc->GPC ['act'] );
	$smarty->assign ( 'user_surplus', $user_account );
	$smarty->assign ( 'user_name', $user_name );
	if ($skyuc->GPC ['act'] == 'add') {
		$href = 'user_account.php?act=list';
	} else {
		$href = 'user_account.php?act=list&' . list_link_postfix ();
	}
	$smarty->assign ( 'action_link', array ('href' => $href, 'text' => $_LANG ['05_user_account'] ) );

	assign_query_info ();
	$smarty->display ( 'user_account_info.tpl' );
}

/*------------------------------------------------------ */
//-- 添加/编辑会员余额的处理部分
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'insert' || $skyuc->GPC ['act'] == 'update') {
	//权限判断
	admin_priv ( 'surplus_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'is_paid' => TYPE_UINT, 'amount' => TYPE_NUM, 'process_type' => TYPE_UINT, 'user_id' => TYPE_STR, 'admin_note' => TYPE_STR, 'payment' => TYPE_STR ) );

	// 初始化变量
	$id = $skyuc->GPC ['id'];
	$is_paid = $skyuc->GPC ['is_paid'];
	$amount = floatval ( $skyuc->GPC ['amount'] );
	$process_type = $skyuc->GPC ['process_type'];
	$user_name = $skyuc->GPC ['user_id'];
	$admin_note = $skyuc->GPC ['admin_note'];
	$user_note = $skyuc->GPC ['user_note'];
	$payment = $skyuc->GPC ['payment'];

	$user = $db->query_first ( 'SELECT user_id FROM ' . TABLE_PREFIX . 'users' . "  WHERE user_name = '" . $db->escape_string ( $user_name ) . "'" );
	$user_id = $user ['user_id'];
	// 此会员是否存在
	if ($user_id == 0) {
		$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
		sys_msg ( $_LANG ['username_not_exist'], 0, $link );
	}

	// 退款，检查余额是否足够
	if ($process_type == 1) {
		$user_account = get_user_surplus ( $user_id );

		// 如果扣除的余额多于此会员拥有的余额，提示
		if ($amount > $user_account) {
			$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
			sys_msg ( $_LANG ['surplus_amount_error'], 0, $link );
		}
	}

	if ($skyuc->GPC ['act'] == 'insert') {
		// 入库的操作
		if ($process_type == 1) {
			$amount = (- 1) * $amount;
		}
		$admin_name = fetch_adminutil_text ( $skyuc->session->vars ['adminid'] . '_admin_name' );
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'user_account' . " VALUES ('', '$user_id', '" . $db->escape_string ( $admin_name ) . "', '$amount', '" . TIMENOW . "', '" . TIMENOW . "', '" . $db->escape_string ( $admin_note ) . "', '" . $db->escape_string ( $user_note ) . "', '$process_type', '" . $db->escape_string ( $payment ) . "', '$is_paid')";
		$skyuc->db->query_write ( $sql );
		$id = $db->insert_id ();
	} else {
		// 更新数据表
		$sql = 'UPDATE ' . TABLE_PREFIX . 'user_account' . ' SET ' . " admin_note   = '" . $db->escape_string ( $admin_note ) . "', " . " user_note    = '" . $db->escape_string ( $user_note ) . "', " . " payment      = '" . $db->escape_string ( $payment ) . "' " . ' WHERE id      = ' . $id;
		$skyuc->db->query_write ( $sql );
	}

	// 更新会员余额数量
	if ($is_paid == 1) {
		$change_desc = $amount > 0 ? $_LANG ['surplus_type_0'] : $_LANG ['surplus_type_1'];
		$change_type = $amount > 0 ? ACT_SAVING : ACT_DRAWING;
		log_account_change ( $user_id, $amount, 0, $change_desc, $change_type );
	}

	//如果是预付款并且未确认，向pay_log插入一条记录
	if ($process_type == 0 && $is_paid == 0) {
		include_once (DIR . '/includes/functions_order.php');

		// 取支付方式信息
		$payment_info = array ();
		$payment_info = $db->query_first ( 'SELECT * FROM ' . TABLE_PREFIX . 'payment' . " WHERE pay_name = '" . $db->escape_string ( $payment ) . "' AND enabled = '1'" );
		//计算支付手续费用
		$pay_fee = pay_fee ( $payment_info ['pay_id'], $amount, 0 );
		$total_fee = $pay_fee + $amount;

		// 插入 pay_log
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'pay_log' . " (order_id, order_amount, order_type, is_paid)" . " VALUES ('$id', '$total_fee', '" . PAY_SURPLUS . "', 0)";
		$skyuc->db->query_write ( $sql );
	}

	// 记录管理员操作
	if ($skyuc->GPC ['act'] == 'update') {
		admin_log ( $user_name, 'edit', 'user_surplus' );
	} else {
		admin_log ( $user_name, 'add', 'user_surplus' );
	}

	// 提示信息
	if ($skyuc->GPC ['act'] == 'insert') {
		$href = 'user_account.php?act=list';
	} else {
		$href = 'user_account.php?act=list&' . list_link_postfix ();
	}
	$link [0] ['text'] = $_LANG ['back_list'];
	$link [0] ['href'] = $href;

	$link [1] ['text'] = $_LANG ['continue_add'];
	$link [1] ['href'] = 'user_account.php?act=add';

	sys_msg ( $_LANG ['attradd_succed'], 0, $link );
}

/*------------------------------------------------------ */
//-- 审核会员余额页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'check') {
	// 检查权限
	admin_priv ( 'surplus_manage' );

	$id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	// 如果参数不合法，返回
	if ($id == 0) {
		header ( "Location: user_account.php?act=list\n" );
		exit ();
	}

	// 查询当前的预付款信息
	$account = array ();
	$account = $db->query_first ( 'SELECT * FROM ' . TABLE_PREFIX . 'user_account' . ' WHERE id = ' . $id );
	$account ['add_time'] = skyuc_date ( $skyuc->options ['date_format'] . ' ' . $skyuc->options ['time_format'], $account ['add_time'] );

	//余额类型:预付款，退款申请，购买商品，取消订单
	if ($account ['process_type'] == 0) {
		$process_type = $_LANG ['surplus_type_0'];
	} elseif ($account ['process_type'] == 1) {
		$process_type = $_LANG ['surplus_type_1'];
	} elseif ($account ['process_type'] == 2) {
		$process_type = $_LANG ['surplus_type_2'];
	} else {
		$process_type = $_LANG ['surplus_type_3'];
	}

	$sql = 'SELECT user_name FROM ' . TABLE_PREFIX . 'users' . ' WHERE user_id = ' . $account ['user_id'];
	$user = $db->query_first ( $sql );
	$user_name = $user ['user_name'];

	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['check'] );
	$smarty->assign ( 'surplus', $account );
	$smarty->assign ( 'process_type', $process_type );
	$smarty->assign ( 'user_name', $user_name );
	$smarty->assign ( 'id', $id );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['05_user_account'], 'href' => 'user_account.php?act=list' ) );

	assign_query_info ();
	$smarty->display ( 'user_account_check.tpl' );
}

/*------------------------------------------------------ */
//-- 更新会员余额的状态
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'action') {
	// 检查权限
	admin_priv ( 'surplus_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'is_paid' => TYPE_UINT, 'admin_note' => TYPE_STR ) );

	// 初始化
	$id = $skyuc->GPC ['id'];
	$is_paid = $skyuc->GPC ['is_paid'];
	$admin_note = $skyuc->GPC ['admin_note'];

	// 如果参数不合法，返回
	if ($id == 0 || empty ( $admin_note )) {
		header ( "Location: user_account.php?act=list\n" );
		exit ();
	}

	// 查询当前的预付款信息
	$account = array ();
	$account = $db->query_first ( 'SELECT * FROM ' . TABLE_PREFIX . 'user_account' . ' WHERE id = ' . $id );
	$amount = $account ['amount'];

	//如果状态为未确认
	if ($account ['is_paid'] == 0) {
		//如果是退款申请, 并且已完成,更新此条记录,扣除相应的余额
		if ($is_paid == '1' && $account ['process_type'] == '1') {
			$user_account = get_user_surplus ( $account ['user_id'] );
			$fmt_amount = str_replace ( '-', '', $amount );

			//如果扣除的余额多于此会员拥有的余额，提示
			if ($fmt_amount > $user_account) {
				$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
				sys_msg ( $_LANG ['surplus_amount_error'], 0, $link );
			}

			update_user_account ( $id, $amount, $admin_note, $is_paid );

			//更新会员余额数量
			log_account_change ( $account ['user_id'], $amount, 0, $_LANG ['surplus_type_1'], ACT_DRAWING );
		} elseif ($is_paid == '1' && $account ['process_type'] == '0') {
			//如果是预付款，并且已完成, 更新此条记录，增加相应的余额
			update_user_account ( $id, $amount, $admin_note, $is_paid );

			//更新会员余额数量
			log_account_change ( $account ['user_id'], $amount, 0, $_LANG ['surplus_type_0'], ACT_SAVING );

		} elseif ($is_paid == '0') {
			$admin_name = fetch_adminutil_text ( $skyuc->session->vars ['adminid'] . '_admin_name' );
			// 否则更新信息
			$sql = 'UPDATE ' . TABLE_PREFIX . 'user_account' . ' SET ' . "admin_user    = '" . $db->escape_string ( $admin_name ) . "', " . "admin_note    = '$admin_note', " . 'is_paid       = 0 WHERE id = ' . $id;
			$skyuc->db->query_write ( $sql );
		}

		// 记录管理员日志
		admin_log ( '(' . $_LANG ['check'] . ')' . $admin_note, 'edit', 'user_surplus' );

		// 提示信息
		$link [0] ['text'] = $_LANG ['back_list'];
		$link [0] ['href'] = 'user_account.php?act=list&' . list_link_postfix ();

		sys_msg ( $_LANG ['attradd_succed'], 0, $link );
	}
}

/*------------------------------------------------------ */
//-- ajax帐户信息列表
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$list = account_list ();

	$smarty->assign ( 'list', $list ['list'] );
	$smarty->assign ( 'filter', $list ['filter'] );
	$smarty->assign ( 'record_count', $list ['record_count'] );
	$smarty->assign ( 'page_count', $list ['page_count'] );

	$sort_flag = sort_flag ( $list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	make_json_result ( $smarty->fetch ( 'user_account_list.tpl' ), '', array ('filter' => $list ['filter'], 'page_count' => $list ['page_count'] ) );
} /*------------------------------------------------------ */
//-- ajax删除一条信息
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'remove') {
	// 检查权限
	check_authz_json ( 'surplus_manage' );

	$skyuc->input->clean_gpc ( 'r', 'id', TYPE_UINT );

	$id = $skyuc->GPC ['id'];
	$sql = 'SELECT u.user_name FROM ' . TABLE_PREFIX . 'users' . ' AS u, ' . TABLE_PREFIX . 'user_account' . ' AS ua ' . ' WHERE u.user_id = ua.user_id AND ua.id = ' . $id;
	$user = $db->query_first ( $sql );

	$sql = 'DELETE FROM ' . TABLE_PREFIX . 'user_account' . ' WHERE id = ' . $id;
	if ($db->query_write ( $sql )) {
		admin_log ( $user ['user_name'], 'remove', 'user_surplus' );
		$url = 'user_account.php?act=query&' . str_replace ( 'act=remove', '', $_SERVER ['QUERY_STRING'] );
		header ( "Location: $url\n" );
		exit ();
	} else {
		make_json_error ( $db->error () );
	}
}

?>
