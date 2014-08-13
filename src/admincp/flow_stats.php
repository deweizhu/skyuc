<?php
/**
 * SKYUC! 综合流量统计
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

if ($skyuc->GPC ['act'] == 'view' || $skyuc->GPC ['act'] == '') {
	admin_priv ( 'client_flow_stats' );

	//时间参数
	if ($_SERVER ['REQUEST_METHOD'] == 'POST') {
		$skyuc->input->clean_array_gpc ( 'p', array ('start_dateYear' => TYPE_UINT, 'start_dateMonth' => TYPE_UINT, 'start_dateDay' => TYPE_UINT, 'end_dateYear' => TYPE_UINT, 'end_dateMonth' => TYPE_UINT, 'end_dateDay' => TYPE_UINT, 'filter' => TYPE_ARRAY_STR ) );

		$start_date = skyuc_mktime ( 0, 0, 0, $skyuc->GPC ['start_dateMonth'], $skyuc->GPC ['start_dateDay'], $skyuc->GPC ['start_dateYear'] );
		$end_date = skyuc_mktime ( 0, 0, 0, $skyuc->GPC ['end_dateMonth'], $skyuc->GPC ['end_dateDay'], $skyuc->GPC ['end_dateYear'] );

		if ($start_date > $end_date) {
			$end_date = $start_date;
		} elseif (($end_date - $start_date) / 86400 > 30) {
			//如果选择日期范围大于30天，则更改日期范围为靠近结束日期的30天
			$start_date = strtotime ( '-1 month', $end_date );
		}
	} else {
		$start_date = strtotime ( '-1 day', TIMENOW );
		$end_date = TIMENOW;
	}

	/* ------------------------------------- */
	/* --综合流量
    /* ------------------------------------- */
	$max = 0;
	$general_xml = "<graph caption='" . $_LANG ['general_stats'] . "' shownames='1' showvalues='1' decimalPrecision='0' yaxisminvalue='0' yaxismaxvalue='%d' animation='1' outCnvBaseFontSize='12' baseFontSize='12' xaxisname='" . $_LANG ['date'] . "' yaxisname='" . $_LANG ['access_count'] . "' >";

	$sql = "SELECT FROM_UNIXTIME(access_time, '%m-%d') AS access_date, COUNT(*) AS access_count" . ' FROM ' . TABLE_PREFIX . 'stats' . ' WHERE access_time >= ' . $start_date . ' AND access_time <= ' . $end_date . ' GROUP BY access_date';
	$res = $db->query_read_slave ( $sql );

	$key = 0;
	while ( $val = $db->fetch_array ( $res ) ) {
		$general_xml .= "<set name='" . $val ['access_date'] . "' value='" . $val ['access_count'] . "' color='" . chart_color ( $key ) . "' />";
		if ($val ['access_count'] > $max) {
			$max = $val ['access_count'];
		}
		$key ++;
	}

	$general_xml .= '</graph>';
	$general_xml = sprintf ( $general_xml, $max );

	/* ------------------------------------- */
	/* --地域分布
    /* ------------------------------------- */
	$area_xml = '';
	$area_xml .= "<graph caption='" . $_LANG ['area_stats'] . "' shownames='1' showvalues='1' decimalPrecision='2' outCnvBaseFontSize='12' baseFontSize='12' pieYScale='45'  pieBorderAlpha='40' pieFillAlpha='70' pieSliceDepth='15' pieRadius='100'>";

	$sql = 'SELECT COUNT(*) AS access_count, area FROM ' . TABLE_PREFIX . 'stats' . ' WHERE access_time >=' . $start_date . ' AND access_time <= ' . $end_date . ' GROUP BY area ORDER BY access_count DESC ';
	$sql = $skyuc->db->query_limit ( $sql, 20 );
	$res = $db->query_read_slave ( $sql );

	$key = 0;
	while ( $val = $db->fetch_array ( $res ) ) {
		$area = empty ( $val ['area'] ) ? 'unknow' : $val ['area'];

		$area_xml .= "<set name='" . $area . "' value='" . $val ['access_count'] . "' color='" . chart_color ( $key ) . "' />";
		$key ++;
	}

	$area_xml .= '</graph>';

	/* ------------------------------------- */
	/* --来源网站
    /* ------------------------------------- */
	$from_xml = "<graph caption='" . $_LANG ['from_stats'] . "' shownames='1' showvalues='1' decimalPrecision='2' outCnvBaseFontSize='12' baseFontSize='12' pieYScale='45' pieBorderAlpha='40' pieFillAlpha='70' pieSliceDepth='15' pieRadius='100'>";

	$sql = 'SELECT COUNT(*) AS access_count, referer_domain FROM ' . TABLE_PREFIX . 'stats' . ' WHERE access_time >= ' . $start_date . ' AND access_time <= ' . $end_date . ' GROUP BY referer_domain ORDER BY access_count DESC';
	$sql = $skyuc->db->query_limit ( $sql, 20 );
	$res = $db->query_read_slave ( $sql );

	$key = 0;
	while ( $val = $db->fetch_array ( $res ) ) {
		$from = empty ( $val ['referer_domain'] ) ? $_LANG ['input_url'] : $val ['referer_domain'];

		$from_xml .= "<set name='" . str_replace ( array ('http://', 'https://' ), array ('', '' ), $from ) . "' value='" . $val ['access_count'] . "' color='" . chart_color ( $key ) . "' />";

		$key ++;
	}

	$from_xml .= '</graph>';

	// 模板赋值
	$filename = skyuc_date ( 'Ymd', $start_date, TRUE, FALSE ) . '_' . skyuc_date ( 'Ymd', $end_date, TRUE, FALSE );

	$smarty->assign ( 'action_link', array ('text' => $_LANG ['down_flow_stats'], 'href' => 'flow_stats.php?act=download&filename=' . $filename . '&start_date=' . $start_date . '&end_date=' . $end_date ) );

	$smarty->assign ( 'ur_here', $_LANG ['flow_stats'] );
	$smarty->assign ( 'general_data', $general_xml );
	$smarty->assign ( 'area_data', $area_xml );
	$smarty->assign ( 'from_data', $from_xml );

	//显示日期
	$smarty->assign ( 'start_date', $start_date );
	$smarty->assign ( 'end_date', $end_date );

	// 显示页面
	assign_query_info ();
	$smarty->display ( 'flow_stats.tpl' );
} // 报表下载
elseif ($skyuc->GPC ['act'] == 'download') {

	$skyuc->input->clean_array_gpc ( 'g', array ('start_date' => TYPE_UNIXTIME, 'end_date' => TYPE_UNIXTIME, 'filename' => TYPE_STR ) );
	$start_date = $skyuc->GPC ['start_date'];
	$end_date = $skyuc->GPC ['end_date'];

	$filename = $skyuc->GPC ['filename'];

	$sql = "SELECT FROM_UNIXTIME(access_time, '%m-%d') AS access_date, COUNT(*) AS access_count" . ' FROM ' . TABLE_PREFIX . 'stats' . ' WHERE access_time >= ' . $start_date . ' AND access_time <= ' . $end_date . ' GROUP BY access_date';
	$res = $db->query_read_slave ( $sql );

	header ( 'Content-type: application/vnd.ms-excel; charset=GB2312' );
	header ( 'Content-Disposition: attachment; filename=' . $filename . '.xls' );

	$data = $_LANG ['general_stats'] . "\t\n";
	$data .= $_LANG ['area'] . "\t";
	$data .= $_LANG ['access_count'] . "\t\n";

	while ( $val = $db->fetch_array ( $res ) ) {
		$data .= $val ['access_date'] . "\t";
		$data .= $val ['access_count'] . "\t\n";
	}

	$sql = 'SELECT COUNT(*) AS access_count, area FROM ' . TABLE_PREFIX . 'stats' . ' WHERE access_time >= ' . $start_date . ' AND access_time <= ' . $end_date . ' GROUP BY area ORDER BY access_count DESC';
	$sql = $skyuc->db->query_limit ( $sql, 20 );
	$res = $db->query_read_slave ( $sql );

	$data .= $_LANG ['area_stats'] . "\t\n";
	$data .= $_LANG ['area'] . "\t";
	$data .= $_LANG ['access_count'] . "\t\n";

	while ( $val = $db->fetch_array ( $res ) ) {
		$data .= $val ['area'] . "\t";
		$data .= $val ['access_count'] . "\t\n";
	}

	$sql = 'SELECT COUNT(*) AS access_count, referer_domain FROM ' . TABLE_PREFIX . 'stats' . ' WHERE access_time >= ' . $start_date . ' AND access_time <= ' . $end_date . ' GROUP BY referer_domain ORDER BY access_count DESC';
	$sql = $skyuc->db->query_limit ( $sql, 20 );
	$res = $db->query_read_slave ( $sql );

	$data .= "\n" . $_LANG ['from_stats'] . "\t\n";

	$data .= $_LANG ['url'] . "\t";
	$data .= $_LANG ['access_count'] . "\t\n";

	while ( $val = $db->fetch_array ( $res ) ) {
		$data .= ($val ['referer_domain'] == "" ? $_LANG ['input_url'] : $val ['referer_domain']) . "\t";
		$data .= $val ['access_count'] . "\t\n";
	}
	echo skyuc_iconv ( 'UTF8', 'GB2312', $data ) . "\t";
} // 清空统计信息
elseif ($skyuc->GPC ['act'] == 'truncate') {
	truncate_table ( 'stats' );

	$link [0] ['text'] = $_LANG ['back_list'];
	$link [0] ['href'] = 'flow_stats.php?act=view';

	sys_msg ( $_LANG ['truncate_succed'], 0, $link );
}
?>