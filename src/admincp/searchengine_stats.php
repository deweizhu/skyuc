<?php

/**
 * SKYUC! 搜索引擎关键字统计
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
	/* --关键字统计
    /* ------------------------------------- */
	$max = 0;
	$general_xml = "<chart caption='" . $_LANG ['tab_keywords'] . "' shownames='1' showvalues='0' decimals='0' numberPrefix='' outCnvBaseFontSize='12' baseFontSize='12'>";
	$sql = 'SELECT keyword, count, searchengine ' . ' FROM ' . TABLE_PREFIX . 'keywords' . ' WHERE date >= ' . $start_date . ' AND date <= ' . $end_date;
	if ($skyuc->GPC_exists ['filter']) {
		$sql .= ' AND ' . db_create_in ( $skyuc->GPC ['filter'], 'searchengine' );
	}
	$res = $db->query_read_slave ( $sql );
	$search = array ();
	$searchengine = array ();
	$keyword = array ();

	while ( $val = $db->fetch_array ( $res ) ) {
		$keyword [$val ['keyword']] = 1;
		$searchengine [$val ['searchengine']] [$val ['keyword']] = $val ['count'];
	}

	$general_xml .= "<categories>";
	foreach ( $keyword as $key => $val ) {
		$key = str_replace ( '&', '＆', $key );
		$key = str_replace ( '>', '＞', $key );
		$key = str_replace ( '<', '＜', $key );
		$general_xml .= "<category label='" . str_replace ( '\'', '', $key ) . "' />";
	}
	$general_xml .= "</categories>\n";

	$i = 0;

	foreach ( $searchengine as $key => $val ) {
		$general_xml .= "<dataset seriesName='" . $key . "' color='" . chart_color ( $i ) . "' showValues='0'>";
		foreach ( $keyword as $k => $v ) {
			$count = 0;
			if (! empty ( $searchengine [$key] [$k] )) {
				$count = $searchengine [$key] [$k];
			}
			$general_xml .= "<set value='$count' />";
		}
		$general_xml .= "</dataset>";
		$i ++;
	}

	$general_xml .= '</chart>';

	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['flow_searchengine_stats'] );
	$smarty->assign ( 'general_data', $general_xml );

	$searchengines = array ('BAIDU' => true, 'GOOGLE' => false, 'YAHOO' => false, 'BING' => false, 'CT114' => false, 'SOSO' => false, 'SKYUC' => true );

	if ($skyuc->GPC_exists ['filter']) {
		foreach ( $skyuc->GPC ['filter'] as $v ) {
			$searchengines [$v] = true;
		}
	}
	$smarty->assign ( 'searchengines', $searchengines );

	// 显示日期
	$smarty->assign ( 'start_date', $start_date );
	$smarty->assign ( 'end_date', $end_date );

	$filename = skyuc_date ( 'Ymd', $start_date, TRUE, FALSE ) . '_' . skyuc_date ( 'Ymd', $end_date, TRUE, FALSE );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['down_search_stats'], 'href' => 'searchengine_stats.php?act=download&start_date=' . $start_date . '&end_date=' . $end_date . '&filename=' . $filename ) );

	$smarty->assign ( 'lang', $_LANG );
	// 显示页面
	assign_query_info ();
	$smarty->display ( 'searchengine_stats.tpl' );
} elseif ($skyuc->GPC ['act'] == 'download') {
	$skyuc->input->clean_array_gpc ( 'g', array ('start_date' => TYPE_UNIXTIME, 'end_date' => TYPE_UNIXTIME, 'filename' => TYPE_STR ) );
	$start_date = $skyuc->GPC ['start_date'];
	$end_date = $skyuc->GPC ['end_date'];

	$filename = $skyuc->GPC ['filename'];
	$sql = 'SELECT keyword, count,searchengine ' . ' FROM ' . TABLE_PREFIX . 'keywords' . ' WHERE date >= ' . $start_date . ' AND date <= ' . $end_date;
	$res = $db->query_read_slave ( $sql );

	$searchengine = array ();
	$keyword = array ();

	while ( $val = $db->fetch_array ( $res ) ) {
		$keyword [$val ['keyword']] = 1;
		$searchengine [$val ['searchengine']] [$val ['keyword']] = $val ['count'];
	}
	header ( 'Content-type: application/vnd.ms-excel; charset=GB2312' );
	header ( 'Content-Disposition: attachment; filename=' . $filename . '.xls' );
	$data = "\t";
	foreach ( $searchengine as $k => $v ) {
		$data .= "$k\t";
	}
	foreach ( $keyword as $kw => $val ) {
		$data .= "\n$kw\t";
		foreach ( $searchengine as $k => $v ) {
			if (isset ( $searchengine [$k] [$kw] )) {
				$data .= $searchengine [$k] [$kw] . "\t";
			} else {
				$data .= '0' . "\t";
			}
		}
	}
	echo skyuc_iconv ( 'UTF8', 'GB2312', $data ) . "\t";
} /* 清空统计信息 */
elseif ($skyuc->GPC ['act'] == 'truncate') {

	truncate_table ( 'keywords' );
	truncate_table ( 'searchengine' );

	$link [0] ['text'] = $_LANG ['back_list'];
	$link [0] ['href'] = 'searchengine_stats.php?act=view';

	sys_msg ( $_LANG ['truncate_succed'], 0, $link );
}
?>