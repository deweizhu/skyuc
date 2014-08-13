<?php
/**
 * SKYUC!  影卡管理程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

$exc = new exchange ( TABLE_PREFIX . 'card', $skyuc->db, 'id', 'cardid' );

/*-----------------------------------------------------*/
//-- 影卡列表
/*-----------------------------------------------------*/
if ($skyuc->GPC ['act'] == 'list') {
	admin_priv ( 'card_manage' ); // 检查权限


	// 模板赋值
	$ur_here = $_LANG ['02_cardlist'];
	$smarty->assign ( 'ur_here', $ur_here );

	$action_link = array ('href' => 'card.php?act=add', 'text' => $_LANG ['01_cardadd'] );
	$smarty->assign ( 'action_link', $action_link );
	$smarty->assign ( 'lang', $_LANG );

	$card_list = get_card_list ();

	$smarty->assign ( 'card_list', $card_list ['card'] );
	$smarty->assign ( 'ranks', $skyuc->usergroup );
	$smarty->assign ( 'filter', $card_list ['filter'] );
	$smarty->assign ( 'record_count', $card_list ['record_count'] );
	$smarty->assign ( 'page_count', $card_list ['page_count'] );
	$smarty->assign ( 'full_page', 1 );

	// 排序标记
	$sort_flag = sort_flag ( $card_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	assign_query_info ();
	$smarty->display ( 'card_list.tpl' );
}
/*-----------------------------------------------------*/
//-- 影卡记录列表
/*-----------------------------------------------------*/
if ($skyuc->GPC ['act'] == 'log') {
	// 模板赋值
	$ur_here = $_LANG ['03_cardlog'];
	$smarty->assign ( 'ur_here', $ur_here );

	$action_link = array ('href' => 'card.php?act=list', 'text' => $_LANG ['02_cardlist'] );
	$smarty->assign ( 'action_link', $action_link );
	$smarty->assign ( 'lang', $_LANG );

	$card_list = get_cardlog_list ();

	$smarty->assign ( 'card_list', $card_list ['card'] );
	$smarty->assign ( 'ranks', $skyuc->usergroup );
	$smarty->assign ( 'filter', $card_list ['filter'] );
	$smarty->assign ( 'record_count', $card_list ['record_count'] );
	$smarty->assign ( 'page_count', $card_list ['page_count'] );
	$smarty->assign ( 'full_page', 1 );

	// 排序标记
	$sort_flag = sort_flag ( $card_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	assign_query_info ();
	$smarty->display ( 'cardlog_list.tpl' );
} /*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$skyuc->input->clean_gpc ( 'r', 'is_cardlist', TYPE_BOOL );

	if ($skyuc->GPC ['is_cardlist']) {
		$card_list = get_card_list ();
	} else {
		$card_list = get_cardlog_list ();
	}

	$smarty->assign ( 'card_list', $card_list ['card'] );
	$smarty->assign ( 'filter', $card_list ['filter'] );
	$smarty->assign ( 'record_count', $card_list ['record_count'] );
	$smarty->assign ( 'page_count', $card_list ['page_count'] );

	// 排序标记
	$sort_flag = sort_flag ( $card_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	$tpl = $skyuc->GPC ['is_cardlist'] ? 'card_list.tpl' : 'cardlog_list.tpl';
	make_json_result ( $smarty->fetch ( $tpl ), '', array ('filter' => $card_list ['filter'], 'page_count' => $card_list ['page_count'] ) );

} /*-----------------------------------------------------*/
//-- 添加影卡
/*-----------------------------------------------------*/
elseif ($skyuc->GPC ['act'] == 'add') {
	admin_priv ( 'card_manage' ); // 检查权限


	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['01_cardadd'] );
	$smarty->assign ( 'action_link', array ('href' => 'card.php?act=list', 'text' => $_LANG ['02_cardlist'] ) );

	$smarty->assign ( 'ranks', $skyuc->usergroup );
	$smarty->assign ( 'end_date', strtotime ( '+3 month', TIMENOW ) );
	$smarty->assign ( 'cardprefix', skyuc_date ( 'Ym', TIMENOW, false, false ) );

	$smarty->assign ( 'form_act', 'insert' );

	assign_query_info ();
	$smarty->display ( 'card_info.tpl' );
} /*------------------------------------------------------ */
//-- 插入影卡信息
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'insert') {
	// 检查权限
	admin_priv ( 'card_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('maxv' => TYPE_UINT, 'num' => TYPE_UINT, 'prefix' => TYPE_STR, 'rank_id' => TYPE_UINT, 'money' => TYPE_UINT, 'cardvalue' => TYPE_UINT, 'end_dateYear' => TYPE_UINT, 'end_dateMonth' => TYPE_UINT, 'end_dateDay' => TYPE_UINT ) );

	$end_date = skyuc_mktime ( 0, 0, 0, $skyuc->GPC ['end_dateMonth'], $skyuc->GPC ['end_dateDay'], $skyuc->GPC ['end_dateYear'] );

	for($i = 0; $i < $skyuc->GPC ['num']; $i ++) {
		// 生成一个随机数
		$cardlen = $skyuc->GPC ['maxv'] - strlen ( $skyuc->GPC ['prefix'] );
		$cardid = $skyuc->GPC ['prefix'] . random_card ( $cardlen );
		$cardpass = random_card ( $skyuc->GPC ['maxv'] );

		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'card' . ' (cardid, cardpass, rank_id, cardvalue, money, addtime, endtime) ' . " VALUES ('" . $cardid . "', '" . $cardpass . "', '" . $skyuc->GPC ['rank_id'] . "', '" . $skyuc->GPC ['cardvalue'] . "', '" . $skyuc->GPC ['money'] . "', '" . TIMENOW . "', '" . $end_date . "')";
		$skyuc->db->query_write ( $sql ); // 入库的操作
	}

	// 记录日志
	admin_log ( '', 'add', 'card' );

	$link [] = array ('text' => $_LANG ['continue_add_card'], 'href' => 'card.php?act=add' );
	$link [] = array ('text' => $_LANG ['back_card_list'], 'href' => 'card.php?act=list' );
	sys_msg ( $_LANG ['add_card_ok'], 0, $link );
} /*------------------------------------------------------ */
//-- 修改卡号
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_cardid') {
	check_authz_json ( 'card_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	if ($exc->edit ( "cardid = '" . $skyuc->db->escape_string ( $skyuc->GPC ['val'] ) . "'", $skyuc->GPC ['id'] )) {
		make_json_result ( $skyuc->GPC ['val'] );
	}
} /*------------------------------------------------------ */
//-- 修改密码
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_cardpass') {
	check_authz_json ( 'card_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	if ($exc->edit ( "cardpass = '" . $skyuc->db->escape_string ( $skyuc->GPC ['val'] ) . "'", $skyuc->GPC ['id'] )) {
		make_json_result ( $skyuc->GPC ['val'] );
	}
}

/*------------------------------------------------------ */
//-- 修改卡值
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_cardvalue') {
	check_authz_json ( 'card_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	if ($exc->edit ( "cardvalue = '" . $skyuc->GPC ['val'] . "'", $skyuc->GPC ['id'] )) {
		make_json_result ( $skyuc->GPC ['val'] );
	}
} /*------------------------------------------------------ */
//-- 修改面值
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_money') {
	check_authz_json ( 'card_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	if ($exc->edit ( "money = '" . $skyuc->GPC ['val'] . "'", $skyuc->GPC ['id'] )) {
		make_json_result ( $skyuc->GPC ['val'] );
	}

} /*------------------------------------------------------ */
//-- 修改有效日期
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_endtime') {
	check_authz_json ( 'card_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$val = strtotime ( $skyuc->GPC ['val'] );

	if ($exc->edit ( "endtime = '$val'", $skyuc->GPC ['id'] )) {
		make_json_result ( $skyuc->GPC ['val'] );
	}
} /*------------------------------------------------------ */
//-- 删除影卡
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'drop_card') {
	// 检查权限
	check_authz_json ( 'card_manage' );

	// 取得参数
	$id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );
	if ($id <= 0) {
		make_json_error ( 'invalid params' );
	}

	// 取得充值卡
	$sql = 'SELECT *  FROM ' . TABLE_PREFIX . 'card' . " WHERE id = '$id'";
	$card = $skyuc->db->query_first ( $sql );
	if (empty ( $card )) {
		make_json_error ( $_LANG ['card_not_exist'] );
	}

	// 删除充值卡
	$exc->drop ( $id );

	//记录日志
	admin_log ( $card ['cardid'], 'remove', 'card' );

	$url = 'card.php?act=query&' . str_replace ( 'act=drop_card', '', $_SERVER ['QUERY_STRING'] );

	header ( "Location: $url\n" );
	exit ();
} /*------------------------------------------------------ */
//-- 批量操作
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'batch') {
	// 检查权限
	admin_priv ( 'card_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('checkboxes' => TYPE_ARRAY_UINT, 'type' => TYPE_STR ) );

	// 取得要操作的影片编号
	$ids = iif ( ! empty ( $skyuc->GPC ['checkboxes'] ), join ( ',', $skyuc->GPC ['checkboxes'] ), 0 );

	// 删除影卡
	if ($skyuc->GPC ['type'] == 'drop') {
		// 取得有效影卡id
		$sql = 'SELECT DISTINCT id FROM ' . TABLE_PREFIX . 'card' . ' WHERE id ' . db_create_in ( $ids );

		$id = $skyuc->db->query_first ( $sql );
		if (! empty ( $id )) {
			// 删除影卡
			$sql = 'DELETE FROM ' . TABLE_PREFIX . 'card' . ' WHERE id ' . db_create_in ( $ids );
			$skyuc->db->query_write ( $sql );

			// 记录日志
			admin_log ( '', 'batch_remove', 'card' );

		}

	} elseif ($skyuc->GPC ['type'] == 'droplog') {

		// 取得有效影卡id
		$sql = 'SELECT DISTINCT id FROM ' . TABLE_PREFIX . 'card_log' . ' WHERE id ' . db_create_in ( $ids );
		$id = $skyuc->db->query_first ( $sql );
		if (! empty ( $id )) {
			// 删除影卡
			$sql = 'DELETE FROM ' . TABLE_PREFIX . 'card_log' . ' WHERE id ' . db_create_in ( $ids );
			$skyuc->db->query_write ( $sql );

			// 记录日志
			admin_log ( '', 'batch_remove', 'card_log' );

		}
	}

	if ($skyuc->GPC ['type'] == 'droplog') {
		$link [] = array ('href' => 'card.php?act=log', 'text' => $_LANG ['03_cardlog'] );
	} else {
		$link [] = array ('href' => 'card.php?act=list', 'text' => $_LANG ['02_cardlist'] );
	}

	sys_msg ( $_LANG ['batch_handle_ok'], 0, $link );
} /*-----------------------------------------------------*/
//-- 导出到EXCEL
/*-----------------------------------------------------*/
elseif ($skyuc->GPC ['act'] == 'doexport') {

	$rank_id = $skyuc->input->clean_gpc ( 'g', 'rid', TYPE_UINT );

	//header('Content-type: application/octet-stream');
	header ( 'Content-Type:   application/vnd.ms-excel' );
	header ( 'Content-Disposition:   attachment;   filename=excel_' . skyuc_date ( $skyuc->options ['date_format'], TIMENOW, false, false ) . '.csv' );
	header ( 'Pragma:   no-cache' );
	header ( 'Expires:   0' );

	$lab ['tab'] = array ('0' => $_LANG ['record_id'], '1' => $_LANG ['cardid'], '2' => $_LANG ['cardpass'], '3' => $_LANG ['cardvalue'], '4' => $_LANG ['money'], '5' => $_LANG ['endtime'], '6' => $_LANG ['rank_id'] . "\n" );

	$to_charset = iif ( $skyuc->options ['lang'] == 'zh-cn', 'GB2312', 'BIG5' );
	echo skyuc_iconv ( 'UTF8', $to_charset, join ( ',', $lab ['tab'] ) );

	$sql = 'SELECT c.*, u.rank_name AS rank_name FROM ' . TABLE_PREFIX . 'card' . ' AS c ' . ' left join ' . TABLE_PREFIX . 'user_rank' . ' AS u ON u.rank_id = c.rank_id' . ' WHERE c.rank_id=' . $rank_id;

	$arr = $skyuc->db->query_all_slave ( $sql );

	foreach ( $arr as $card ) {
		$list ['card'] = array ('1' => $card ['id'], '2' => 'NO.:' . $card ['cardid'], '3' => 'PWD:' . $card ['cardpass'], '4' => $card ['money'], '5' => $card ['cardvalue'], '6' => 'DATE:' . skyuc_date ( $skyuc->options ['date_format'], $card ['endtime'], false, false ), '7' => 'RANK:' . skyuc_iconv ( 'UTF8', $to_charset, $card ['rank_name'] ) . "\n" );
		echo join ( ',', $list ['card'] );
	}
	exit ();
}

?>