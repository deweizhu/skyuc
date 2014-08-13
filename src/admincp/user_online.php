<?php
/**
 * SKYUC! 用户在线状态
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

/*初始化数据交换对象 */
$exc = new exchange ( TABLE_PREFIX . 'seelog', $skyuc->db, 'id', 'title' );

/*------------------------------------------------------ */
//-- 列出所有留言
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list') {
	$online_list = get_online_list ();

	$smarty->assign ( 'online_list', $online_list ['online_list'] );
	$smarty->assign ( 'filter', $online_list ['filter'] );
	$smarty->assign ( 'record_count', $online_list ['record_count'] );
	$smarty->assign ( 'page_count', $online_list ['page_count'] );
	$smarty->assign ( 'full_page', 1 );
	$smarty->assign ( 'sort_id', '<img src="images/sort_desc.gif">' );

	$smarty->assign ( 'ur_here', $_LANG ['user_online'] );
	$smarty->assign ( 'full_page', 1 );

	assign_query_info ();
	$smarty->display ( 'online_list.tpl' );
}

/*------------------------------------------------------ */
//-- ajax显示点播日志列表
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$online_list = get_online_list ();

	$smarty->assign ( 'online_list', $online_list ['online_list'] );
	$smarty->assign ( 'filter', $online_list ['filter'] );
	$smarty->assign ( 'record_count', $online_list ['record_count'] );
	$smarty->assign ( 'page_count', $online_list ['page_count'] );

	$sort_flag = sort_flag ( $online_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	make_json_result ( $smarty->fetch ( 'online_list.tpl' ), '', array ('filter' => $online_list ['filter'], 'page_count' => $online_list ['page_count'] ) );
} /*------------------------------------------------------ */
//-- 踢出用户
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'write-off') {
	$sessionhash = $skyuc->input->clean_gpc ( 'g', 'sessionhash', TYPE_STR );
	$skyuc->db->query ( 'DELETE FROM ' . TABLE_PREFIX . 'session' . " WHERE adminid=0 AND sessionhash ='" . $db->escape_string ( $sessionhash ) . "'" );
	sys_msg ( $_LANG ['write-off_succeed'], 0, array (), true );
}
?>