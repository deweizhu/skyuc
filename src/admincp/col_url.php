<?php
/**
 * SKYUC! 管理中心采集网址内容管理
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');
require (DIR . '/languages/' . $skyuc->options ['lang'] . '/admincp/collection.php');
require (DIR . '/includes/class_collection.php');

set_time_limit ( 0 );
/*------------------------------------------------------ */
//-- 采集节点列表
/*------------------------------------------------------ */

if ($skyuc->GPC ['act'] == 'list') {
	$skyuc->input->clean_gpc ( 'r', 'nid', TYPE_UINT );

	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['collection_down'] );

	$action_link = array ('href' => 'col_main.php?act=list', 'text' => $_LANG ['collection_col'] );
	$smarty->assign ( 'action_link', $action_link );

	$smarty->assign ( 'lang', $_LANG );

	$col_list = col_url_list ( $skyuc->GPC ['nid'] );
	$full_page = empty ( $_GET ['full_page'] ) ? 1 : 0;

	$smarty->assign ( 'nid', $skyuc->GPC ['nid'] );
	$smarty->assign ( 'col_list', $col_list ['col'] );
	$smarty->assign ( 'filter', $col_list ['filter'] );
	$smarty->assign ( 'record_count', $col_list ['record_count'] );
	$smarty->assign ( 'page_count', $col_list ['page_count'] );
	$smarty->assign ( 'full_page', $full_page );

	// 排序标记
	$sort_flag = sort_flag ( $col_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	assign_query_info ();
	$smarty->display ( 'col_url.tpl' );
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$skyuc->input->clean_gpc ( 'r', 'nid', TYPE_UINT );

	$col_list = col_url_list ( $skyuc->GPC ['nid'] );
	$smarty->assign ( 'nid', $skyuc->GPC ['nid'] );

	$smarty->assign ( 'col_list', $col_list ['col'] );
	$smarty->assign ( 'filter', $col_list ['filter'] );
	$smarty->assign ( 'record_count', $col_list ['record_count'] );
	$smarty->assign ( 'page_count', $col_list ['page_count'] );
	$smarty->assign ( 'lang', $_LANG );

	// 排序标记
	$sort_flag = sort_flag ( $col_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	make_json_result ( $smarty->fetch ( 'col_url.tpl' ), '', array ('filter' => $col_list ['filter'], 'page_count' => $col_list ['page_count'] ) );
}

/*------------------------------------------------------ */
//-- 框架中显示的执行采集页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'getsource') {
	// 权限检查
	admin_priv ( 'collection' );

	$skyuc->input->clean_array_gpc ( 'g', array ('nid' => TYPE_UINT, 'islisten' => TYPE_INT, 'startdd' => TYPE_UINT, 'pagesize' => TYPE_UINT, 'threadnum' => TYPE_UINT, 'sptime' => TYPE_UINT, 'glstart' => TYPE_UINT, 'totalnum' => TYPE_UINT ) );

	//下载种子网址中未下载内容模式
	/*-----------------------------
	function Download_not_down() { }
	------------------------------*/
	if ($skyuc->GPC ['islisten'] == 0) {
		if ($skyuc->GPC ['totalnum'] > 0) {
			$totalnum = $skyuc->GPC ['totalnum'];
		} else {
			$total = $db->query_first ( 'SELECT COUNT(aid) AS total FROM ' . TABLE_PREFIX . 'co_html' . ' WHERE isdown !=1 AND nid=' . $skyuc->GPC ['nid'] );
			$totalnum = $total ['total'];
		}
		if ($totalnum <= 0) {
			ShowMsg ( $_LANG ['note_islisten_on'], 'javascript:;' );
			exit ();
		} else {
			$gurl = 'col_url.php?act=gather_start&islisten=0&nid=' . $skyuc->GPC ['nid'] . '&startdd=' . $skyuc->GPC ['startdd'] . '&pagesize=' . $skyuc->GPC ['pagesize'] . '&sptime=' . $skyuc->GPC ['sptime'] . '&threadnum=' . $skyuc->GPC ['threadnum'] . '&totalnum=' . $totalnum;
			ShowMsg ( $_LANG ['note_downloaded'], $gurl );
			exit ();
		}
	}

	//监控式采集（检测新内容）
	/*-----------------------------
	function Download_new() { }
	------------------------------*/
	elseif ($skyuc->GPC ['islisten'] == 1) {
		$collection = new Collection ( $skyuc );
		//针对专门节点
		if ($skyuc->GPC ['nid'] > 0) {
			$collection->codeArray = $collection->get_col_info ( $skyuc->GPC ['nid'] ); //取得节点信息
			$limitList = $collection->get_source_url ( 1, 0, 100 );

			if ($skyuc->GPC ['totalnum'] > 0) {
				$totalnum = $skyuc->GPC ['totalnum'];
			} else {
				$total = $db->query_first ( 'SELECT COUNT(aid) AS total FROM ' . TABLE_PREFIX . 'co_html' . ' WHERE   nid=' . $skyuc->GPC ['nid'] );
				$totalnum = $total ['total'];
			}
			if ($totalnum == 0) {
				ShowMsg ( $_LANG ['notfound_new'], 'javascript:;' );
				exit ();
			} else {
				$gourl = 'col_url.php?act=gather_start&islisten=1&nid=' . $skyuc->GPC ['nid'] . '&startdd=' . $skyuc->GPC ['startdd'] . '&pagesize=' . $skyuc->GPC ['pagesize'] . '&sptime=' . $skyuc->GPC ['sptime'] . '&threadnum=' . $skyuc->GPC ['threadnum'];
				ShowMsg ( $_LANG ['go_getsource_url'], $gourl . '&totalnum=' . $totalnum );
				exit ();
			}
		} //针对所有节点
		else {
			$curpos = (isset ( $_GET ['curpos'] ) ? intval ( $_GET ['curpos'] ) : 0);
			$sql = 'SELECT nid FROM ' . TABLE_PREFIX . 'co_note' . ' ORDER BY nid ASC ';
			$sql = $skyuc->db->query_limit ( $sql, 1, $curpos );
			$row = $db->query_first ( $sql );
			$nnid = $row ['nid'];

			if (! is_array ( $row )) {
				$gourl = 'col_url.php?act=gather_start&sptime=0&nid=0&threadnum=' . $skyuc->GPC ['threadnum'] . '&startdd=0&pagesize=5&totalnum=' . $totalnum;
				ShowMsg ( $_LANG ['finish_check'], $gourl );
				exit ();
			} else {
				$collection->codeArray = $collection->get_col_info ( $nnid ); //取得节点信息
				$limitList = $collection->get_source_url ( 1, 0, 100 );
				$curpos ++;

				$gourl = 'col_url.php?act=getsource&islisten=1&nid=0&pagesize=' . $skyuc->GPC ['pagesize'] . '&sptime=' . $skyuc->GPC ['sptime'] . '&threadnum=' . $skyuc->GPC ['threadnum'] . '&curpos=' . $curpos;
				ShowMsg ( sprintf ( $_LANG ['checked_col'], $nnid ), $gourl );
				exit ();
			}
		}
	}

	//重新下载所有内容模式
	/*-----------------------------
	function Download_all() { }
	------------------------------*/
	else {

		$collection = new Collection ( $skyuc );
		$collection->codeArray = $collection->get_col_info ( $skyuc->GPC ['nid'] ); //取得节点信息
		$limitList = $collection->get_source_url ( - 1, $skyuc->GPC ['glstart'], $skyuc->GPC ['pagesize'] );
		if ($limitList == 0) {
			if ($skyuc->GPC ['totalnum'] > 0) {
				$totalnum = $skyuc->GPC ['totalnum'];
			} else {
				$total = $db->query_first ( 'SELECT COUNT(aid) AS total FROM ' . TABLE_PREFIX . 'co_html' . ' WHERE nid=' . $skyuc->GPC ['nid'] );
				$totalnum = $total ['total'];
			}
			$gourl = 'col_url.php?act=gather_start&islisten=-1&nid=' . $skyuc->GPC ['nid'] . '&startdd=' . $skyuc->GPC ['startdd'] . '&pagesize=' . $skyuc->GPC ['pagesize'] . '&sptime=' . $skyuc->GPC ['sptime'] . '&threadnum=' . $skyuc->GPC ['threadnum'] . '&totalnum=' . $totalnum;

			ShowMsg ( $_LANG ['go_getsource_url'], $gourl );
			exit ();
		} elseif ($limitList > 0) {
			$gourl = 'col_url.php?act=getsource&islisten=-1&nid=' . $skyuc->GPC ['nid'] . '&startdd=' . $skyuc->GPC ['startdd'] . '&pagesize=' . $skyuc->GPC ['pagesize'] . '&sptime=' . $skyuc->GPC ['sptime'] . '&threadnum=' . $skyuc->GPC ['threadnum'];

			ShowMsg ( sprintf ( $_LANG ['limitlist'], $limitList ), $gourl . '&glstart=' . ($skyuc->GPC ['glstart'] + $skyuc->GPC ['pagesize']), 0, 100 );
			exit ();
		} else {
			//ADD BY ZDW 2008-11-29
			header ( 'Content-Type: text/html; charset=utf-8' );
			echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\r\n";
			echo $_LANG ['get_list_faild'];
		}
	}

} /*------------------------------------------------------ */
//-- 框架中显示的执行采集网页内容
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'gather_start') {
	// 权限检查
	admin_priv ( 'collection' );

	$skyuc->input->clean_array_gpc ( 'g', array ('nid' => TYPE_UINT, 'islisten' => TYPE_INT, 'startdd' => TYPE_UINT, 'pagesize' => TYPE_UINT, 'threadnum' => TYPE_UINT, //多线程模式初次设置
'sptime' => TYPE_UINT, 'glstart' => TYPE_UINT, 'totalnum' => TYPE_UINT, 'thethr' => TYPE_UINT, 'oldstart' => TYPE_UINT ) );

	if ($skyuc->GPC ['totalnum'] == 0) {
		ShowMsg ( $_LANG ['notfound_all'], 'javascript:;' );
		exit ();
	}
	if ($skyuc->GPC ['oldstart'] == 0) {
		$skyuc->GPC ['oldstart'] = $skyuc->GPC ['startdd'];
	}
	if ($skyuc->GPC ['threadnum'] > 0) {
		$step = ceil ( $skyuc->GPC ['totalnum'] / $skyuc->GPC ['threadnum'] );
		$j = 0;
		for($i = 1; $i <= $skyuc->GPC ['totalnum']; $i ++) {
			if ($i % $step == 0) {
				$j ++;
				$sdd = ($i - $step);
				$surl = 'col_url.php?act=gather_start&islisten=' . $skyuc->GPC ['islisten'] . '&thethr=' . $j . '&sptime=' . $skyuc->GPC ['sptime'] . '&nid=' . $skyuc->GPC ['nid'] . '&oldstart=' . $sdd . '&startdd=' . $sdd . '&totalnum=' . ($step * $j) . '&pagesize=' . $skyuc->GPC ['pagesize'];
				echo "<iframe scrolling='no' name='thredfrm" . $j . "' frameborder='0' width='100%' height='200' src='" . $surl . "'></iframe>\r\n";
			}
		}
		if ($skyuc->GPC ['totalnum'] % $skyuc->GPC ['threadnum'] != 0) {

			$sdd = $j * $step;
			$k = $j + 1;
			$surl = 'col_url.php?act=gather_start&islisten=' . $skyuc->GPC ['islisten'] . '&thethr=' . $k . '&sptime=' . $skyuc->GPC ['sptime'] . '&nid=' . $skyuc->GPC ['nid'] . '&oldstart=' . $sdd . '&startdd=' . $sdd . '&totalnum=' . $skyuc->GPC ['totalnum'] . '&pagesize=' . $skyuc->GPC ['pagesize'];
			echo "<iframe scrolling='no' name='thredfrm" . $j . "' frameborder='0' width='100%' height='200' src='" . $surl . "'></iframe>\r\n";
		}
		exit ();
	}

	$collection = new Collection ( $skyuc );
	//指点采集一个节点信息
	if ($skyuc->GPC ['nid'] > 0) {
		$collection->codeArray = $collection->get_col_info ( $skyuc->GPC ['nid'] ); //取得节点信息
		$sql = 'SELECT aid,nid,url,isdown,litpic FROM ' . TABLE_PREFIX . 'co_html' . ' WHERE nid=' . $skyuc->GPC ['nid'];
	} else { //没指定采集ID时下载所有内容
		$sql = 'SELECT aid,nid,url,isdown,litpic FROM ' . TABLE_PREFIX . 'co_html';
	}

	if ($skyuc->GPC ['totalnum'] > $skyuc->GPC ['startdd'] + $skyuc->GPC ['pagesize']) {
		$sql = $skyuc->db->query_limit ( $sql, $skyuc->GPC ['pagesize'], $skyuc->GPC ['startdd'] );
	} else {
		$sql = $skyuc->db->query_limit ( $sql, ($skyuc->GPC ['totalnum'] - $skyuc->GPC ['startdd']), $skyuc->GPC ['startdd'] );
	}
	if ($skyuc->GPC ['totalnum'] - $skyuc->GPC ['startdd'] < 1) {
		if ($skyuc->GPC ['nid'] == 0) {
			$db->query_write ( 'UPDATE ' . TABLE_PREFIX . 'co_note' . ' SET lasttime=' . TIMENOW );
		} else {
			$db->query_write ( 'UPDATE ' . TABLE_PREFIX . 'co_note' . ' SET lasttime=' . TIMENOW . ' WHERE nid=' . $skyuc->GPC ['nid'] );
		}
		ShowMsg ( $_LANG ['all_succeed'], 'javascript:;' );
		exit ();
	}

	$tjnum = $skyuc->GPC ['startdd'];
	$res = $db->query_read ( $sql );
	while ( $row = $db->fetch_array ( $res ) ) {
		if ($row ['isdown'] == 0) {
			if ($skyuc->GPC ['nid'] == 0) {
				$collection->codeArray = $collection->get_col_info ( $row ['nid'] ); //取得节点信息
			}
			$collection->gather_url ( $row ['aid'], $row ['nid'], $row ['url'] ); //采集网页并入库
		}

		$tjnum ++;
		if ($skyuc->GPC ['sptime'] > 0) {
			sleep ( $skyuc->GPC ['sptime'] );
		}
	}
	//进度条窗口
	if ($skyuc->GPC ['totalnum'] - $skyuc->GPC ['oldstart'] != 0) {
		$tjlen = ceil ( (($tjnum - $skyuc->GPC ['oldstart']) / ($skyuc->GPC ['totalnum'] - $skyuc->GPC ['oldstart'])) * 100 );
		$dvlen = $tjlen * 2;
		$tjsta = "<div style='width:200;height:15;border:1px solid #898989;text-align:left'><div style='width:$dvlen;height:15;background-color:#829D83'></div></div>";
		$tjsta .= "<br/>" . sprintf ( $_LANG ['progress'], $skyuc->GPC ['thethr'], $tjlen );
	}
	if ($tjnum < $skyuc->GPC ['totalnum']) {
		ShowMsg ( $tjsta, 'col_url.php?act=gather_start&islisten=' . $skyuc->GPC ['islisten'] . '&thethr=' . $skyuc->GPC ['thethr'] . '&sptime=' . $skyuc->GPC ['sptime'] . '&nid=' . $skyuc->GPC ['nid'] . '&oldstart=' . $skyuc->GPC ['oldstart'] . '&totalnum=' . $skyuc->GPC ['totalnum'] . '&startdd=' . ($skyuc->GPC ['startdd'] + $skyuc->GPC ['pagesize']) . '&pagesize=' . $skyuc->GPC ['pagesize'], '', 500 );
		exit ();
	} else {
		if ($skyuc->GPC ['nid'] == 0) {
			$db->query_write ( 'UPDATE ' . TABLE_PREFIX . 'co_note' . ' SET lasttime=' . TIMENOW );
		} else {
			$db->query_write ( 'UPDATE ' . TABLE_PREFIX . 'co_note' . ' SET lasttime=' . TIMENOW . ' WHERE nid=' . $skyuc->GPC ['nid'] );
		}
		ShowMsg ( $_LANG ['all_succeed'], 'javascript:;' );
		exit ();
	}
	/*

*/

} /*------------------------------------------------------ */
//-- 框架中显示的执行导出数据
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'export_action') {
	// 权限检查
	admin_priv ( 'collection' );

	$skyuc->input->clean_array_gpc ( 'g', array ('nid' => TYPE_UINT, 'pagesize' => TYPE_UINT, 'cat_id' => TYPE_UINT, 'pageno' => TYPE_UINT, 'startid' => TYPE_UINT, 'endid' => TYPE_UINT, 'totalnum' => TYPE_UINT, 'onlytitle' => TYPE_UINT ) );

	if ($skyuc->GPC ['pageno'] == 0) {
		$skyuc->GPC ['pageno'] = 1;
	}

	//导出数据的SQL操作
	$collection = new Collection ( $skyuc );
	$colinfo = $collection->get_col_info ( $skyuc->GPC ['nid'], 1 ); //取得节点信息


	//---------------------------------


	$totalpage = ceil ( $skyuc->GPC ['totalnum'] / $skyuc->GPC ['pagesize'] );
	$startdd = ($skyuc->GPC ['pageno'] - 1) * $skyuc->GPC ['pagesize'];
	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'co_html' . ' WHERE nid=' . $skyuc->GPC ['nid'] . ' ORDER BY aid ASC ';
	$sql = $skyuc->db->query_limit ( $sql, $skyuc->GPC ['pagesize'], $startdd );
	$res = $db->query_read ( $sql );
	while ( $row = $db->fetch_array ( $res ) ) {
		$filmInfo = $row ['result'];
		$filmArray = parse_film_code ( $filmInfo );
		if (empty ( $filmArray )) {
			continue;
		}
		$filmArray ['cat_id'] = $skyuc->GPC ['cat_id'];
		$filmArray ['server_id'] = $colinfo ['server_id'];
		$filmArray ['player'] = $colinfo ['player'];

		insert_film_info ( $filmArray, $skyuc->GPC ['onlytitle'] ); //插入影片信息


		$db->query ( 'UPDATE ' . TABLE_PREFIX . 'co_html' . ' SET isexport = 1 WHERE aid =' . $row ['aid'] );
	}
	//检测是否完成或后续操作
	//---------------------------------
	if ($totalpage < $skyuc->GPC ['pageno']) {
		build_category ();
		$skyuc->topnewhots = array ();
		build_datastore ( 'topnewhots', serialize ( $skyuc->topnewhots ), 1 ); //设置新片和排行榜缓存过期


		ShowMsg ( $_LANG ['export_succeed'], 'javascript:;' );
		exit ();
	} else {
		if ($totalpage > 0) {
            $percentage = ceil ($skyuc->GPC ['pageno'] / $totalpage * 100);
		} else {
			$percentage = '100';
		}

		$skyuc->GPC ['pageno'] ++;

		$gourl = 'col_url.php?act=export_action&nid=' . $skyuc->GPC ['nid'] . '&totalnum=' . $skyuc->GPC ['totalnum'] . '&pageno=' . $skyuc->GPC ['pageno'];
		$gourl .= '&cat_id=' . $skyuc->GPC ['cat_id'] . '&pagesize=' . $skyuc->GPC ['pagesize'];
		$gourl .= '&startid=' . $skyuc->GPC ['startid'] . '&endid=' . $skyuc->GPC ['endid'] . '&onlytitle=' . $skyuc->GPC ['onlytitle'];

		ShowMsg ( sprintf ( $_LANG ['export_progress'], $percentage ), $gourl, '', 100 );
		exit ();
	}

} /*------------------------------------------------------ */
//-- 查看已下载内容
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'view') {
	$skyuc->input->clean_gpc ( 'g', 'aid', TYPE_UINT );
	$res = $db->query_read ( 'SELECT * FROM ' . TABLE_PREFIX . 'co_html' . ' WHERE aid=' . $skyuc->GPC ['aid'] );
	$row = $db->fetch_array ( $res );
	$url = $row;
	if ($row ['isdown'] != 1) {
		$collection = new Collection ( $skyuc );
		$collection->codeArray = $collection->get_col_info ( $row ['nid'] ); //取得节点信息
		$url ['result'] = $collection->get_test_url ( $row ['url'], 0 ); //详情页信息
		$url ['url'] = $row ['url'] . $_LANG ['url_demo'];
	}

	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['collection_demo'] );

	$action_link = array ('href' => 'col_url.php?act=list', 'text' => $_LANG ['collection_down'] );
	$smarty->assign ( 'action_link', $action_link );

	$smarty->assign ( 'url', $url );
	$smarty->assign ( 'lang', $_LANG );

	assign_query_info ();
	$smarty->display ( 'col_view.tpl' );
} /*------------------------------------------------------ */
//-- 采集未下载内容
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'coall') {
	$total = $db->query_first ( 'SELECT COUNT(aid) AS total FROM ' . TABLE_PREFIX . 'co_html' );
	if ($total ['total'] == 0) {
		ShowMsg ( $_LANG ['notfound_down'], '-1' );
		exit ();
	}
	$gourl = 'col_url.php?act=gather_start&&sptime=0&nid=0&oldstart=0&totalnum=' . $total ['total'] . '&startdd=0&pagesize=5';

	$link [] = array ('text' => $_LANG ['confirm_yes'], 'href' => $gourl );
	$link [] = array ('text' => $_LANG ['confirm_no'], 'href' => 'col_url.php?act=list' );

	sys_msg ( $_LANG ['confirm_down'], 2, $link, false );

} /*------------------------------------------------------ */
//-- 测试规则
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'test_rule') {
	$skyuc->input->clean_gpc ( 'g', 'nid', TYPE_UINT );

	// 模板赋值
	$ur_here = $_LANG ['collection_test'];
	$smarty->assign ( 'ur_here', $ur_here );

	$action_link = array ('href' => 'col_main.php?act=list', 'text' => $_LANG ['collection_col'] );
	$smarty->assign ( 'action_link', $action_link );

	$smarty->assign ( 'lang', $_LANG );

	$collection = new Collection ( $skyuc );
	$collection->codeArray = $collection->get_col_info ( $skyuc->GPC ['nid'] ); //取得节点信息
	$test_list = $collection->TestList (); //列表页信息
	$dourl = $test_list [1];

	if (empty ( $dourl )) {
		$itemconfig = $_LANG ['no_test_url'];
	} else {
		$itemconfig = $collection->get_test_url ( $dourl, 1 ); //详情页信息
	}

	$gathername = $collection->codeArray ['notename'];
	$smarty->assign ( 'test_list', $test_list [0] );
	$smarty->assign ( 'test_art', $itemconfig );
	$smarty->assign ( 'gathername', $gathername );
	$smarty->assign ( 'full_page', 1 );

	assign_query_info ();
	$smarty->display ( 'col_test_rule.tpl' );
}

/*------------------------------------------------------ */
//-- 批量删除网址或历史内容（监控内容）
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'clear') {

	// 检查权限
	admin_priv ( 'collection' );

	$skyuc->input->clean_array_gpc ( 'g', array ('nid' => TYPE_UINT, 'ids' => TYPE_STR, 'clshash' => TYPE_BOOL ) );

	// 取得要操作的编号
	$nid = $skyuc->GPC ['nid'];
	$ids = $skyuc->GPC ['ids'];
	$clshash = $skyuc->GPC ['clshash'];

	if (empty ( $ids )) {
		// 清空一个节点的内容
		if (! empty ( $nid )) {
			$db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'co_html' . ' WHERE nid =' . $nid );
		}
		$link [] = array ('href' => 'col_main.php?act=list', 'text' => $_LANG ['collection_col'] );
		sys_msg ( $_LANG ['delete_url_succeed'], 0, $link );
	} else {
		// 删除历史内容（监控内容）
		if ($clshash) {
			$sql = 'SELECT nid,url FROM ' . TABLE_PREFIX . 'co_html' . ' WHERE ' . db_create_in ( $ids, 'aid' );
			$res = $db->query_read ( $sql );
			while ( $row = $db->fetch_array ( $res ) ) {
				$nhash = md5 ( $row ['url'] );
				$nid = $row ['nid'];
				$db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'co_listen' . ' WHERE nid =' . $nid . " AND hash='" . $nhash . "' " );
			}
		}

		// 删除网址
		$db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'co_html' . ' WHERE ' . db_create_in ( $ids, 'aid' ) );

		$link [] = array ('href' => 'col_url.php?act=list', 'text' => $_LANG ['collection_down'] );
		sys_msg ( $_LANG ['delete_url_history_succeed'], 0, $link );
	}
} /*------------------------------------------------------ */
//-- 仅清空内容
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'clearct') {
	// 权限检查
	admin_priv ( 'collection' );

	$ids = $skyuc->input->clean_gpc ( 'g', 'ids', TYPE_STR );
	if (! empty ( $ids )) {
		$db->query_write ( 'UPDATE ' . TABLE_PREFIX . 'co_html' . " SET isdown=0, result='' WHERE " . db_create_in ( $ids, 'aid' ) );
	}
	sys_msg ( $_LANG ['delete_all_succeed'], 0, $link );

} /*------------------------------------------------------ */
//-- 清空所有信息
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'clearall') {
	// 权限检查
	admin_priv ( 'collection' );

	truncate_table ( 'co_html' );
	//truncate_table('co_listen'); //不清空监控内容


	$link [0] ['text'] = $_LANG ['collection_down'];
	$link [0] ['href'] = 'col_url.php?act=list';

	sys_msg ( $_LANG ['truncate_succed'], 0, $link );
}
/*------------------------------------------------------ */
//-- 删除垃圾图片
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'delete_trash') {

	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['delete_trash'] );

	$action_link = array ('href' => 'col_url.php?act=list', 'text' => $_LANG ['collection_down'] );
	$smarty->assign ( 'action_link', $action_link );

	$smarty->assign ( 'lang', $_LANG );

	$totalnum = $db->query_first ( 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'co_media' );

	$smarty->assign ( 'totalnum', $totalnum ['total'] );

	// 排序标记
	$sort_flag = sort_flag ( $col_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	assign_query_info ();
	$smarty->display ( 'trash_batch.tpl' );
} elseif ($skyuc->GPC ['act'] == 'delete_trash_act') {
	// 权限检查
	admin_priv ( 'collection' );

	$skyuc->input->clean_array_gpc ( 'g', array ('pageno' => TYPE_UINT, 'startdd' => TYPE_UINT, 'totalnum' => TYPE_UINT ) );
	if ($skyuc->GPC ['pageno'] === 0) {
		$skyuc->GPC ['pageno'] = 1;
	}
	if ($skyuc->GPC ['totalnum'] === 0) {
		$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'co_media';
		$total = $db->query_first ( $sql );
		$skyuc->GPC ['totalnum'] = $total ['total'];
	}
	$totalpage = ceil ( $skyuc->GPC ['totalnum'] / 100 );
	$startdd = ($totalpage - $skyuc->GPC ['pageno']) * 100;
	if ($totalpage < $skyuc->GPC ['pageno']) {
		ShowMsg ( $_LANG ['delete_trash_succed'], 'javascript:;' );
		exit ();
	} else {
		if ($totalpage > 0) {
			//$percentage = substr ( ($skyuc->GPC ['pageno'] / $totalpage * 100), 0, 2 );
            $percentage = floor($skyuc->GPC ['pageno'] / $totalpage * 100);
		} else {
			$percentage = '100';
		}

		$skyuc->GPC ['pageno'] ++;
		$sql = 'SELECT tofile FROM ' . TABLE_PREFIX . 'co_media ORDER BY nid DESC';
		$sql = $db->query_limit ( $sql, 100, $startdd );
		$res = $db->query_read ( $sql );
		while ( $row = $db->fetch_array ( $res ) ) {
			if (! is_file ( DIR . '/' . $row ['tofile'] )) {
				//不存在图片，删除记录
				$db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'co_media' . " WHERE tofile='" . $db->escape_string ( $row ['tofile'] ) . "'" );

				// @unlink(DIR .'/'. $row['tofile']);
				@unlink ( DIR . '/' . str_replace ( '/source/', '/image/', $row ['tofile'] ) );
				@unlink ( DIR . '/' . str_replace ( '/source/', '/thumb/', $row ['tofile'] ) );
			} else {
				//存在图片，但是没有影片使用这张图片，删除图片和记录
				$sql = 'SELECT source FROM ' . TABLE_PREFIX . 'show' . " WHERE source = '" . $db->escape_string ( $row ['tofile'] ) . "'";
				$rows = $db->query_first ( $sql );
				if (empty ( $rows ['source'] )) {
					@unlink ( DIR . '/' . $row ['tofile'] );
					@unlink ( DIR . '/' . str_replace ( '/source/', '/image/', $row ['tofile'] ) );
					@unlink ( DIR . '/' . str_replace ( '/source/', '/thumb/', $row ['tofile'] ) );
					$db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'co_media' . " WHERE tofile='" . $db->escape_string ( $row ['tofile'] ) . "'" );
				}

			}

		}
		$gourl = 'col_url.php?act=delete_trash_act&startdd=' . $startdd . '&totalnum=' . $skyuc->GPC ['totalnum'] . '&pageno=' . $skyuc->GPC ['pageno'];

		ShowMsg ( sprintf ( $_LANG ['trash_progress'], $percentage ), $gourl, '', 100 );
		exit ();
	}

}
?>
