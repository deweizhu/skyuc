<?php

/**
 * SKYUC! 记录管理员操作日志
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

/*------------------------------------------------------ */
//-- 获取所有日志列表
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list' || $skyuc->GPC ['act'] == '') {
	// 权限的判断
	admin_priv ( 'logs_manage' );

	$skyuc->input->clean_array_gpc ( 'r', array ('id' => TYPE_UINT, 'ip' => TYPE_STR, 'log_date' => TYPE_STR ) );

	$user_id = $skyuc->GPC ['id'];
	$admin_ip = $skyuc->GPC ['ip'];
	$log_date = $skyuc->GPC ['log_date'];

	// 查询IP地址列表
	$ip_list = array ();
	$res = $db->query_read_slave ( 'SELECT DISTINCT ip_address FROM ' . TABLE_PREFIX . 'admin_log' );
	while ( $row = $db->fetch_array ( $res ) ) {
		$ip_list [$row ['ip_address']] = $row ['ip_address'];
	}

	$smarty->assign ( 'ur_here', $_LANG ['04_admin_logs'] );
	$smarty->assign ( 'ip_list', $ip_list );
	$smarty->assign ( 'full_page', 1 );

	$log_list = get_admin_logs ();

	$smarty->assign ( 'log_list', $log_list ['list'] );
	$smarty->assign ( 'filter', $log_list ['filter'] );
	$smarty->assign ( 'record_count', $log_list ['record_count'] );
	$smarty->assign ( 'page_count', $log_list ['page_count'] );

	$sort_flag = sort_flag ( $log_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	assign_query_info ();
	$smarty->display ( 'admin_logs.tpl' );
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$log_list = get_admin_logs ();
	$smarty->assign ( 'log_list', $log_list ['list'] );
	$smarty->assign ( 'filter', $log_list ['filter'] );
	$smarty->assign ( 'record_count', $log_list ['record_count'] );
	$smarty->assign ( 'page_count', $log_list ['page_count'] );

	$sort_flag = sort_flag ( $log_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	make_json_result ( $smarty->fetch ( 'admin_logs.tpl' ), '', array ('filter' => $log_list ['filter'], 'page_count' => $log_list ['page_count'] ) );
}

/*------------------------------------------------------ */
//-- 批量删除日志记录
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'batch_drop') {

	admin_priv ( 'logs_drop' );

	$skyuc->input->clean_array_gpc ( 'p', array ('drop_type_date' => TYPE_STR, 'log_date' => TYPE_UINT, 'checkboxes' => TYPE_ARRAY_UINT ) );

	// 按日期删除日志
	if ($skyuc->GPC_exists ['drop_type_date']) {
		if ($skyuc->GPC ['log_date'] == 0) {
			header ( "Location: admin_logs.php?act=list\n" );
			exit ();
		} elseif ($skyuc->GPC ['log_date'] > 0) {
			$where = ' WHERE 1 ';
			switch ($skyuc->GPC ['log_date']) {
				case 1 :
					$a_week = strtotime ( '-1 week', TIMENOW );
					$where .= " AND log_time <= '" . $a_week . "'";
					break;
				case 2 :
					$a_month = strtotime ( '-1 month', TIMENOW );
					$where .= " AND log_time <= '" . $a_month . "'";
					break;
				case 3 :
					$three_month = strtotime ( '-3 month', TIMENOW );
					$where .= " AND log_time <= '" . $three_month . "'";
					break;
				case 4 :
					$half_year = strtotime ( '-6 month', TIMENOW );
					$where .= " AND log_time <= '" . $half_year . "'";
					break;
				case 5 :
					$a_year = strtotime ( '-1 year', TIMENOW );
					$where .= " AND log_time <= '" . $a_year . "'";
					break;
			}
			$sql = 'DELETE FROM ' . TABLE_PREFIX . 'admin_log' . $where;
			$res = $db->query_write ( $sql );
			if ($res) {
				admin_log ( '', 'remove', 'adminlog' );

				$link [] = array ('text' => $_LANG ['back_list'], 'href' => 'admin_logs.php?act=list' );
				sys_msg ( $_LANG ['drop_sueeccud'], 1, $link );
			}
		}
	} // 如果不是按日期来删除, 就按ID删除日志
	else {
		$count = 0;
		foreach ( $skyuc->GPC ['checkboxes'] as $key => $id ) {
			$sql = 'DELETE FROM ' . TABLE_PREFIX . 'admin_log' . " WHERE log_id = '$id'";
			$result = $db->query_write ( $sql );

			$count ++;
		}
		if ($result) {
			admin_log ( '', 'remove', 'adminlog' );

			$link [] = array ('text' => $_LANG ['back_list'], 'href' => 'admin_logs.php?act=list' );
			sys_msg ( sprintf ( $_LANG ['batch_drop_success'], $count ), 0, $link );
		}
	}
}

?>
