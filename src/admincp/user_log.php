<?php
/**
 * SKYUC! 点播记录
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
//-- 列出所有点播记录
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list') {
	$log_list = get_log_list ();

	$smarty->assign ( 'log_list', $log_list ['log_list'] );
	$smarty->assign ( 'filter', $log_list ['filter'] );
	$smarty->assign ( 'record_count', $log_list ['record_count'] );
	$smarty->assign ( 'page_count', $log_list ['page_count'] );
	$smarty->assign ( 'full_page', 1 );
	$smarty->assign ( 'sort_id', '<img src="images/sort_desc.gif">' );

	$smarty->assign ( 'action_link', array ('text' => $_LANG ['down_seelog'], 'href' => 'user_log.php?act=download&filename=' . skyuc_date ( $skyuc->options ['date_format'] ) ) );
	$smarty->assign ( 'ur_here', $_LANG ['06_user_log'] );
	$smarty->assign ( 'full_page', 1 );

	assign_query_info ();
	$smarty->display ( 'playlog_list.tpl' );
}

/*------------------------------------------------------ */
//-- ajax显示点播日志列表
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$log_list = get_log_list ();

	$smarty->assign ( 'log_list', $log_list ['log_list'] );
	$smarty->assign ( 'filter', $log_list ['filter'] );
	$smarty->assign ( 'record_count', $log_list ['record_count'] );
	$smarty->assign ( 'page_count', $log_list ['page_count'] );

	$sort_flag = sort_flag ( $log_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	make_json_result ( $smarty->fetch ( 'playlog_list.tpl' ), '', array ('filter' => $log_list ['filter'], 'page_count' => $log_list ['page_count'] ) );
} /*------------------------------------------------------ */
//-- 清空统计信息
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'truncate') {
	truncate_table ( 'play_log' );

	$link [0] ['text'] = $_LANG ['back_list'];
	$link [0] ['href'] = 'user_log.php?act=list';

	sys_msg ( $_LANG ['truncate_succed'], 0, $link );
} /*------------------------------------------------------ */
//-- 报表下载
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'download') {
	$skyuc->input->clean_gpc ( 'g', 'filename', TYPE_STR );

	header ( 'Content-type: application/vnd.ms-excel; charset=GB2312' );
	header ( 'Content-Disposition: attachment; filename=' . $skyuc->GPC ['filename'] . '.xls' );

	$sql = 'SELECT p.show_id, p.user_id, p.look_time, p.host, p.counts, p.looktype,	s.title AS title,	u.user_name AS user_name' . ' FROM ' . TABLE_PREFIX . 'play_log' . ' AS p ' . ' LEFT JOIN ' . TABLE_PREFIX . 'show' . ' AS s ON s.show_id=p.show_id ' . ' LEFT JOIN ' . TABLE_PREFIX . 'users' . ' AS u ON u.user_id =p.user_id ' . ' ORDER BY p.user_id, p.id DESC';
	$res = $skyuc->db->query_read_slave ( $sql );

	$data = $_LANG ['user_name'] . "\t";
	$data .= $_LANG ['title'] . "\t";
	$data .= $_LANG ['counts'] . "\t";
	$data .= $_LANG ['look_time'] . "\t";
	$data .= $_LANG ['looktype'] . "\t";
	$data .= $_LANG ['userip'] . "\t\n";

	while ( $val = $skyuc->db->fetch_array ( $res ) ) {
		$data .= $val ['user_name'] . "\t";
		$data .= $val ['title'] . "\t";
		$data .= $val ['counts'] . "\t";
		$data .= skyuc_date ( $skyuc->options ['date_format'] . ' ' . $skyuc->options ['time_format'], $val ['look_time'] ) . "\t";
		$data .= $val ['looktype'] > 0 ? $_LANG ['seelog_down'] : $_LANG ['seelog_play'] . "\t";
		$data .= $val ['host'] . "\t\n";
	}
	echo skyuc_iconv ( 'UTF8', 'GB2312', $data ) . "\t";
}
?>