<?php

/**
 * SKYUC! 全文搜索管理程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

/*------------------------------------------------------ */
//-- 全文搜索设置
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'main') {

	assign_query_info ();

	$smarty->assign ( 'dbsearch_core', intval ( $skyuc->options ['dbsearch_core'] ) );
	$smarty->assign ( 'dbsearch_full', intval ( $skyuc->options ['dbsearch_full'] ) );
	$smarty->assign ( 'ur_here', $_LANG ['dbsearch_core'] );
	$smarty->display ( 'dbsearch.tpl' );
}

/*------------------------------------------------------ */
//-- 保存设置
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'save_config') {

	$skyuc->input->clean_array_gpc ( 'p', array ('dbsearch_core' => TYPE_UINT, 'dbsearch_full' => TYPE_BOOL ) );

	//$db->query_write ( 'UPDATE ' . TABLE_PREFIX . 'setting' . " SET value='" . $skyuc->GPC ['dbsearch_core'] . "' WHERE code='dbsearch_core'" );
	$db->query_write ( 'UPDATE ' . TABLE_PREFIX . 'setting' . " SET value='" . $skyuc->GPC ['dbsearch_full'] . "' WHERE code='dbsearch_full'" );

	build_options ();

	sys_msg ( $_LANG ['submit_succeed'], 0, array (array ('href' => 'dbsearch.php?act=main', 'text' => $_LANG ['dbsearch_core'] ) ) );
}

else if ($skyuc->GPC ['act'] == 'emptyindex') {

	$db->query_write ( "TRUNCATE TABLE " . TABLE_PREFIX . "searchcore_text" );

	sys_msg ( $_LANG ['submit_succeed'], 0, array (array ('href' => 'dbsearch.php?act=main', 'text' => $_LANG ['dbsearch_core'] ) ) );
} else if ($skyuc->GPC ['act'] == 'rebuild') {
	require (DIR . '/includes/functions_search.php');

	assign_query_info ();

	$skyuc->input->clean_array_gpc ( 'g', array ('startdd' => TYPE_UINT, 'perpage' => TYPE_UINT, 'curpage' => TYPE_UINT, 'totpage' => TYPE_UINT,'total' => TYPE_UINT ) );
	$perpage = iif ( $skyuc->GPC ['perpage'], $skyuc->GPC ['perpage'], 250 );
	$curpage = iif ( $skyuc->GPC ['curpage'], $skyuc->GPC ['curpage'], 0);
	$startdd = $skyuc->GPC ['startdd'];
	$totpage = $skyuc->GPC ['totpage'];

	if (! $skyuc->GPC ['total']) {
		$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'show WHERE is_show=1';
		$total = $db->query_first ( $sql );
		$skyuc->GPC ['total'] = $total ['total'];
		$totpage = ceil($total ['total']/$perpage);
	}

	$sql = 'SELECT show_id,cat_id, title, title_alias, title_english, actor, director, pubdate, detail FROM ' . TABLE_PREFIX . 'show WHERE is_show=1 ORDER BY show_id ASC';
	$sql = $db->query_limit ( $sql, $perpage, $startdd );
	$res = $db->query_read ( $sql );
	while ( $row = $db->fetch_array ( $res ) ) {
		//获取影片标签
		$sql = 'SELECT tag_words FROM ' . TABLE_PREFIX . 'tag' . ' WHERE  show_id='.$row['show_id'];
		$sql = $db->query_limit ( $sql, 30, 0 );
		$tagres = $db->query_read($sql) ;
		$tag = $row['pubdate'];
		while ( $tagrow = $db->fetch_array ( $tagres ) ) {
			$tag .= $tagrow['tag_words'];
		}

		$param = array();
		$param['show_id'] = $row['show_id'];
		$param['cat_id'] = $row['cat_id'];
		$param['title'] = $row['title'];
		$param['title_alias'] = $row['title_alias'];
		$param['title_english'] = $row['title_english'];
		$param['actor'] = $row['actor'];
		$param['director'] = $row['director'];
		$param['detail'] = $row['detail'];
		$param['tag'] = $tag ;

		add_search_index($param);
	}

	if ($startdd <= $skyuc->GPC ['total']) {
		$startdd += $perpage;
		$curpage++;

		$lnk = 'dbsearch.php?act=rebuild&startdd=' . $startdd . '&curpage=' . $curpage . '&total=' . $skyuc->GPC ['total'] . '&totpage=' . $totpage. '&perpage=' . $perpage;
		$smarty->assign ( 'title', sprintf ( $_LANG ['rebuild_title'],  $curpage, $totpage ) );
		$smarty->assign ( 'auto_redirect', 1 );
		$smarty->assign ( 'auto_link', $lnk );

		$smarty->display ( 'dbsearch_msg.tpl' );
	} else {
		sys_msg ( $_LANG ['submit_succeed'], 0, array (array ('href' => 'dbsearch.php?act=main', 'text' => $_LANG ['dbsearch_core'] ) ) );
	}
}

?>