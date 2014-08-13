<?php

/**
 * SKYUC! 邮件群发
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

// 权限检查
admin_priv ( 'mail_batch' );

if (empty ( $_GET ['is_ajax'] )) {
	assign_query_info ();

	$skyuc->input->clean_gpc ( 'g', 'uname', TYPE_STR );

	if ($skyuc->GPC ['uname']) {
		$smarty->assign ( 'username', $skyuc->GPC ['uname'] );
	}

	$smarty->assign ( 'ur_here', $_LANG ['mail_batch'] );
	$smarty->assign ( 'ranks', $skyuc->usergroup );

	$smarty->display ( 'mail_batch.tpl' );
} else {
	include_once (DIR . '/includes/class_json.php');
	$json = new JSON ();

	$skyuc->input->clean_array_gpc ( 'g', array ('user_rank' => TYPE_UINT, 'subject' => TYPE_STR, 'content' => TYPE_STR, 'page_size' => TYPE_UINT, 'page' => TYPE_UINT, 'total' => TYPE_UINT ) );
	$show_where = '';
	if ($skyuc->GPC ['user_rank'] > 0) {
		$show_where = ' AND user_rank = ' . $skyuc->GPC ['user_rank'];
	}
	//设置最长执行时间
	@set_time_limit ( 600 );

	if (isset ( $_GET ['start'] )) {
		$page_size = 500; // 默认500个/页


		$title = '';

		if (isset ( $_GET ['total_icon'] )) {

			$count = $db->query_first ( 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'users' . "  WHERE 1 " . $show_where );
			$title = sprintf ( $_LANG ['show_format'], $count ['total'], $page_size );
		}

		$result = array ('error' => 0, 'message' => '', 'content' => '', 'done' => 1, 'title' => $title, 'page_size' => $page_size, 'page' => 1, 'subject' => $skyuc->GPC ['subject'], 'content' => $skyuc->GPC ['content'], 'user_rank' => $skyuc->GPC ['user_rank'], 'total' => 1,

		'row' => array ('new_page' => sprintf ( $_LANG ['page_format'], 1 ), 'new_total' => sprintf ( $_LANG ['total_format'], ceil ( $count ['total'] / $page_size ) ), 'new_time' => $_LANG ['wait'], 'cur_id' => 'time_1' ) );

		die ( $json->encode ( $result ) );
	} else {
		$result = array ('error' => 0, 'message' => '', 'content' => '', 'done' => 2, 'show_id' => $show_id, 'server_id' => $server_id, 'cat_id' => $cat_id );
		$result ['user_rank'] = $skyuc->GPC ['user_rank'];
		$result ['subject'] = $skyuc->GPC ['subject'];
		$result ['content'] = $skyuc->GPC ['content'];
		$result ['page_size'] = iif ( $skyuc->GPC ['page_size'] == 0, 100, $skyuc->GPC ['page_size'] );
		$result ['page'] = iif ( $skyuc->GPC_exists ['page'], $skyuc->GPC ['page'], 1 );
		$result ['total'] = iif ( $skyuc->GPC_exists ['total'], $skyuc->GPC ['total'], 1 );

		/*------------------------------------------------------ */
		//-- 会员
		/*------------------------------------------------------ */
		$count = $db->query_first ( 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'users' . "   WHERE 1 " . $show_where );

		// 页数在许可范围内
		if ($result ['page'] <= ceil ( $count ['total'] / $result ['page_size'] )) {
			$start_time = time (); //开始执行时间


			//开始处理
			$sql = 'SELECT user_name, email FROM ' . TABLE_PREFIX . 'users' . " AS m WHERE 1 " . $show_where . " ORDER BY user_id ASC";
			$sql = $skyuc->db->query_limit ( $sql, $result ['page_size'], ($result ['page'] - 1) * $result ['page_size'] );
			$res = $skyuc->db->query_read ( $sql );
			skyuc_mail_start ();
			$result ['subject'] = str_replace ( array ('{$send_date}', '{$site_name}' ), array (skyuc_date ( $skyuc->options ['date_format'], TIMENOW ), $skyuc->options ['site_name'] ), $result ['content'] );
			while ( $row = $skyuc->db->fetch_array ( $res ) ) {
				$message = str_replace ( '{$user_name}', $row ['user_name'], $result ['subject'] );
				skyuc_mail ( $row ['email'], $result ['subject'], $message );
			}
			skyuc_mail_end ();
			$end_time = time (); //结束执行时间


			$result ['row'] ['pre_id'] = 'time_' . $result ['total'];
			$result ['row'] ['pre_time'] = iif ( ($end_time > $start_time), $end_time - $start_time, 1 );
			$result ['row'] ['pre_time'] = sprintf ( $_LANG ['time_format'], $result ['row'] ['pre_time'] );
			$result ['row'] ['cur_id'] = 'time_' . ($result ['total'] + 1);
			$result ['page'] ++; // 新行
			$result ['row'] ['new_page'] = sprintf ( $_LANG ['page_format'], $result ['page'] );
			$result ['row'] ['new_total'] = sprintf ( $_LANG ['total_format'], ceil ( $count ['total'] / $result ['page_size'] ) );
			$result ['row'] ['new_time'] = $_LANG ['wait'];
			$result ['total'] ++;
		} else {
			-- $result ['total'];
			-- $result ['page'];
			$result ['done'] = 0;
			$result ['message'] = $_LANG ['done'];

			die ( $json->encode ( $result ) );
		}

		die ( $json->encode ( $result ) );
	}
}
?>