<?php
/**
 * SKYUC! 支付方式管理程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

$exc = new exchange ( TABLE_PREFIX . 'payment', $skyuc->db, 'pay_code', 'pay_name' );

/*------------------------------------------------------ */
//-- 支付方式列表 ?act=list
/*------------------------------------------------------ */

if ($skyuc->GPC ['act'] == 'list') {
	// 查询数据库中启用的支付方式
	$pay_list = array ();
	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'payment' . " WHERE enabled = '1' ORDER BY pay_order ASC";
	$res = $db->query_read ( $sql );
	while ( $row = $db->fetch_array ( $res ) ) {
		$pay_list [$row ['pay_code']] = $row;
	}

	// 取得插件文件中的支付方式
	$modules = read_modules ( DIR . '/includes/modules/payment' );
	$modules_count = count ( $modules );
	for($i = 0; $i < $modules_count; $i ++) {
		$code = $modules ["$i"] ['code'];

		// 如果数据库中有，取数据库中的名称和描述
		if (isset ( $pay_list [$code] )) {
			$modules ["$i"] ['name'] = $pay_list ["$code"] ['pay_name'];
			$modules ["$i"] ['pay_fee'] = $pay_list ["$code"] ['pay_fee'];
			$modules ["$i"] ['is_cod'] = $pay_list ["$code"] ['is_cod'];
			$modules ["$i"] ['desc'] = $pay_list ["$code"] ['pay_desc'];
			$modules ["$i"] ['pay_order'] = $pay_list ["$code"] ['pay_order'];
			$modules ["$i"] ['install'] = '1';
		} else {
			$modules ["$i"] ['name'] = $_LANG ["$code"];
			if (! isset ( $modules [$i] ['pay_fee'] )) {
				$modules [$i] ['pay_fee'] = 0;
			}
			$modules ["$i"] ['desc'] = $_LANG [$modules ["$i"] ['desc']];
			$modules ["$i"] ['install'] = '0';
		}
	}

	assign_query_info ();

	$smarty->assign ( 'ur_here', $_LANG ['payment_list'] );
	$smarty->assign ( 'modules', $modules );
	$smarty->display ( 'payment_list.tpl' );
}

/*------------------------------------------------------ */
//-- 安装支付方式 ?act=install&code=".$code."
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'install') {
	admin_priv ( 'payment' );

	$skyuc->input->clean_gpc ( 'g', 'code', TYPE_STR );

	// 取相应插件信息
	$set_modules = true;
	include_once (DIR . '/includes/modules/payment/' . $skyuc->GPC ['code'] . '.php');

	$data = $modules [0];
	/* 对支付费用判断。如果data['pay_fee']为false无支付费用，为空则说明以配送有关，其它可以修改 */
	if (isset ( $data ['pay_fee'] )) {
		$data ['pay_fee'] = trim ( $data ['pay_fee'] );
	} else {
		$data ['pay_fee'] = 0;
	}

	$pay ['pay_code'] = $data ['code'];
	$pay ['pay_name'] = $_LANG [$data ['code']];
	$pay ['pay_desc'] = $_LANG [$data ['desc']];
	$pay ['is_cod'] = $data ['is_cod'];
	$pay ['pay_fee'] = $data ['pay_fee'];
	$pay ['pay_config'] = array ();

	foreach ( $data ['config'] as $key => $value ) {
		$config_desc = (isset ( $_LANG [$value ['name'] . '_desc'] )) ? $_LANG [$value ['name'] . '_desc'] : '';
		$pay ['pay_config'] [$key] = $value + array ('label' => $_LANG [$value ['name']], 'value' => $value ['value'], 'desc' => $config_desc );

		if ($pay ['pay_config'] [$key] ['type'] == 'select' || $pay ['pay_config'] [$key] ['type'] == 'radiobox') {
			$pay ['pay_config'] [$key] ['range'] = $_LANG [$pay ['pay_config'] [$key] ['name'] . '_range'];
		}
	}

	assign_query_info ();

	$smarty->assign ( 'pay', $pay );
	$smarty->display ( 'payment_edit.tpl' );
}

/*------------------------------------------------------ */
//-- 编辑支付方式 ?act=edit&code={$code}
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit') {
	admin_priv ( 'payment' );

	$skyuc->input->clean_gpc ( 'g', 'code', TYPE_STR );

	// 查询该支付方式内容
	if (! $skyuc->GPC_exists ['code']) {
		die ( 'invalid parameter' );
	}

	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'payment' . " WHERE pay_code = '" . $db->escape_string ( $skyuc->GPC ['code'] ) . "' AND enabled = '1'";
	$pay = $db->query_first ( $sql );
	if (empty ( $pay )) {
		$links [] = array ('text' => $_LANG ['back_list'], 'href' => 'payment.php?act=list' );
		sys_msg ( $_LANG ['payment_not_available'], 0, $links );
	}

	// 取相应插件信息
	$set_modules = true;
	include_once (DIR . '/includes/modules/payment/' . $skyuc->GPC ['code'] . '.php');
	$data = $modules [0];

	// 取得配置信息
	if (is_string ( $pay ['pay_config'] )) {
		$pay ['pay_config'] = unserialize ( $pay ['pay_config'] );
		foreach ( $pay ['pay_config'] as $key => $value ) {
			$pay ['pay_config'] [$key] ['label'] = $_LANG [$value ['name']];
			$pay ['pay_config'] [$key] ['desc'] = (isset ( $_LANG [$value ['name'] . '_desc'] )) ? $_LANG [$value ['name'] . '_desc'] : '';

			if ($pay ['pay_config'] [$key] ['type'] == 'select' || $pay ['pay_config'] [$key] ['type'] == 'radiobox') {
				$pay ['pay_config'] [$key] ['range'] = $_LANG [$pay ['pay_config'] [$key] ['name'] . '_range'];
			}
		}
	}

	/* 对支付费用判断。如果data['pay_fee']为false无支付费用，为空则说明以配送有关，其它可以修改 */
	if (isset ( $data ['pay_fee'] )) {
		if ($data ['pay_fee'] === false) {
			$pay ['pay_fee_ctl'] = - 1;
		} elseif (strlen ( $data ['pay_fee'] ) == 0) {
			$pay ['pay_fee_ctl'] = 0;
		} else {
			$pay ['pay_fee_ctl'] = 1;
		}
	} else {
		$pay ['pay_fee_ctl'] = 1;
	}

	assign_query_info ();

	$smarty->assign ( 'ur_here', $_LANG ['edit'] . $_LANG ['payment'] );
	$smarty->assign ( 'pay', $pay );
	$smarty->display ( 'payment_edit.tpl' );
}

/*------------------------------------------------------ */
//-- 提交支付方式 post
/*------------------------------------------------------ */
elseif (isset ( $_POST ['Submit'] )) {
	admin_priv ( 'payment' );

	$skyuc->input->clean_array_gpc ( 'p', array ('pay_name' => TYPE_STR, 'pay_code' => TYPE_STR, 'pay_desc' => TYPE_STR, 'pay_fee' => TYPE_NUM, 'pay_id' => TYPE_UINT, 'is_cod' => TYPE_BOOL, 'cfg_name' => TYPE_ARRAY_STR, 'cfg_value' => TYPE_ARRAY_STR, 'cfg_type' => TYPE_ARRAY_STR ) );

	// 检查输入
	if (empty ( $skyuc->GPC ['pay_name'] )) {
		sys_msg ( $_LANG ['payment_name'] . $_LANG ['empty'] );
	}

	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'payment' . " WHERE pay_name = '" . $db->escape_string ( $skyuc->GPC ['pay_name'] ) . "' AND pay_code <> '" . $db->escape_string ( $skyuc->GPC ['pay_code'] ) . "'";
	$total = $db->query_first ( $sql );
	if ($total ['total'] > 0) {
		sys_msg ( $_LANG ['payment_name'] . $_LANG ['repeat'], 1 );
	}

	// 取得配置信息
	$pay_config = array ();
	if ($skyuc->GPC_exists ['cfg_value'] && is_array ( $skyuc->GPC ['cfg_value'] )) {
		$value_count = count ( $skyuc->GPC ['cfg_value'] );
		for($i = 0; $i < $value_count; $i ++) {
			$pay_config [] = array ('name' => $skyuc->GPC ['cfg_name'] [$i], 'type' => $skyuc->GPC ['cfg_type'] [$i], 'value' => $skyuc->GPC ['cfg_value'] [$i] );
		}
	}
	$pay_config = serialize ( $pay_config );

	// 取得和验证支付手续费
	$pay_fee = $skyuc->GPC ['pay_fee'];

	// 检查是编辑还是安装
	$link [] = array ('text' => $_LANG ['back_list'], 'href' => 'payment.php?act=list' );
	if ($skyuc->GPC ['pay_id'] > 0) {
		// 编辑
		$sql = 'UPDATE ' . TABLE_PREFIX . 'payment' . "  SET pay_name = '" . $db->escape_string ( $skyuc->GPC ['pay_name'] ) . "'," . "   pay_desc = '" . $db->escape_string ( $skyuc->GPC ['pay_desc'] ) . "'," . "   pay_config = '$pay_config', " . "   pay_fee    =  '$pay_fee' " . "WHERE pay_code = '" . $db->escape_string ( $skyuc->GPC ['pay_code'] ) . "'";
		$db->query_write ( $sql );

		// 记录日志
		admin_log ( $skyuc->GPC ['pay_name'], 'edit', 'payment' );

		sys_msg ( $_LANG ['edit_ok'], 0, $link );
	} else {
		// 安装，检查该支付方式是否曾经安装过
		$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'payment' . " WHERE pay_code = '" . $db->escape_string ( $skyuc->GPC ['pay_code'] ) . "'";
		$total = $db->query_first ( $sql );
		if ($total ['total'] > 0) {
			// 该支付方式已经安装过, 将该支付方式的状态设置为 enable
			$sql = 'UPDATE ' . TABLE_PREFIX . 'payment' . "	SET pay_name = '" . $db->escape_string ( $skyuc->GPC ['pay_name'] ) . "'," . "   pay_desc = '" . $db->escape_string ( $skyuc->GPC ['pay_desc'] ) . "'," . "   pay_config = '$pay_config', " . "   pay_fee    =  '$pay_fee', " . "   enabled = '1' " . "WHERE pay_code = '" . $db->escape_string ( $skyuc->GPC ['pay_code'] ) . "'";
			$db->query_write ( $sql );
		} else {
			// 该支付方式没有安装过, 将该支付方式的信息添加到数据库
			$sql = 'INSERT INTO ' . TABLE_PREFIX . 'payment' . ' (pay_code, pay_name, pay_desc, pay_config, is_cod, pay_fee, enabled) ' . "VALUES ('" . $db->escape_string ( $skyuc->GPC ['pay_code'] ) . "', '" . $db->escape_string ( $skyuc->GPC ['pay_name'] ) . "', '" . $db->escape_string ( $skyuc->GPC ['pay_desc'] ) . "', '$pay_config', '" . $skyuc->GPC ['is_cod'] . "', '$pay_fee', 1)";
			$db->query_write ( $sql );
		}

		// 记录日志
		admin_log ( $skyuc->GPC ['pay_name'], 'install', 'payment' );

		sys_msg ( $_LANG ['install_ok'], 0, $link );
	}
}

/*------------------------------------------------------ */
//-- 卸载支付方式 ?act=uninstall&code={$code}
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'uninstall') {
	admin_priv ( 'payment' );

	$skyuc->input->clean_gpc ( 'r', 'code', TYPE_STR );

	// 把 enabled 设为 0
	$sql = 'UPDATE ' . TABLE_PREFIX . 'payment' . "	SET enabled = '0' " . "	WHERE pay_code = '" . $db->escape_string ( $skyuc->GPC ['code'] ) . "'";
	$db->query_write ( $sql );

	// 记录日志
	admin_log ( $skyuc->GPC ['code'], 'uninstall', 'payment' );

	$link [] = array ('text' => $_LANG ['back_list'], 'href' => 'payment.php?act=list' );
	sys_msg ( $_LANG ['uninstall_ok'], 0, $link );
}

/*------------------------------------------------------ */
//-- 修改支付方式名称
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'edit_name') {
	// 检查权限
	check_authz_json ( 'payment' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_STR, 'val' => TYPE_STR ) );

	$code = $db->escape_string ( $skyuc->GPC ['id'] ); //特殊情况：ID为字符串，不是数字，需要转义
	$name = $skyuc->GPC ['val'];

	// 检查名称是否为空
	if (empty ( $name )) {
		make_json_error ( $_LANG ['name_is_null'] );
	}

	// 检查名称是否重复
	if (! $exc->is_only ( 'pay_name', $name, $code )) {
		make_json_error ( $_LANG ['name_exists'] );
	}

	// 更新支付方式名称
	$exc->edit ( "pay_name = '" . $db->escape_string ( $name ) . "'", $code );
	make_json_result ( $name );
}

/*------------------------------------------------------ */
//-- 修改支付方式描述
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'edit_desc') {
	// 检查权限
	check_authz_json ( 'payment' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_STR, 'val' => TYPE_STR ) );

	$code = $db->escape_string ( $skyuc->GPC ['id'] ); //特殊情况：ID为字符串，不是数字，需要转义
	$desc = $skyuc->GPC ['val'];

	// 更新描述
	$exc->edit ( "pay_desc = '" . $db->escape_string ( $desc ) . "'", $code );
	make_json_result ( $desc );
}

/*------------------------------------------------------ */
//-- 修改支付方式排序
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'edit_order') {
	// 检查权限
	check_authz_json ( 'payment' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_STR, 'val' => TYPE_UINT ) );

	$code = $db->escape_string ( $skyuc->GPC ['id'] ); //特殊情况：ID为字符串，不是数字，需要转义
	$order = $skyuc->GPC ['val'];

	/* 更新排序 */
	$exc->edit ( "pay_order = '$order'", $code );
	make_json_result ( $order );
}

/*------------------------------------------------------ */
//-- 修改支付方式费用
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'edit_pay_fee') {
	// 检查权限
	check_authz_json ( 'payment' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_STR, 'val' => TYPE_STR ) );

	$code = $db->escape_string ( $skyuc->GPC ['id'] ); //特殊情况：ID为字符串，不是数字，需要转义
	$pay_fee = $skyuc->GPC ['val'];

	if (empty ( $pay_fee )) {
		$pay_fee = 0;
	} else {
		$pay_fee = make_semiangle ( $pay_fee ); //全角转半角
		if (strpos ( $pay_fee, '%' ) === false) {
			$pay_fee = floatval ( $pay_fee );
		} else {
			$pay_fee = floatval ( $pay_fee ) . '%';
		}
	}

	// 更新支付费用
	$exc->edit ( "pay_fee = '" . $db->escape_string ( $pay_fee ) . "'", $code );
	make_json_result ( $pay_fee );
}

?>
