<?php
/**
 * SKYUC! 管理中心采集管理
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

/*------------------------------------------------------ */
//-- 采集节点列表
/*------------------------------------------------------ */

if ($skyuc->GPC ['act'] == 'list' || $skyuc->GPC ['act'] == '') {
	admin_priv ( 'collection' );

	// 模板赋值


	$smarty->assign ( 'ur_here', $_LANG ['collection_col'] );

	$action_link = array ('href' => 'col_main.php?act=add', 'text' => $_LANG ['collection_add'] );
	$smarty->assign ( 'action_link', $action_link );

	$smarty->assign ( 'lang', $_LANG );

	$col_list = get_col_list ();

	$smarty->assign ( 'col_list', $col_list ['col'] );
	$smarty->assign ( 'filter', $col_list ['filter'] );
	$smarty->assign ( 'record_count', $col_list ['record_count'] );
	$smarty->assign ( 'page_count', $col_list ['page_count'] );
	$smarty->assign ( 'full_page', 1 );

	// 排序标记
	$sort_flag = sort_flag ( $col_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	assign_query_info ();
	$smarty->display ( 'col_list.tpl' );
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {

	$col_list = get_col_list ();

	$smarty->assign ( 'col_list', $col_list ['col'] );
	$smarty->assign ( 'filter', $col_list ['filter'] );
	$smarty->assign ( 'record_count', $col_list ['record_count'] );
	$smarty->assign ( 'page_count', $col_list ['page_count'] );
	$smarty->assign ( 'lang', $_LANG );

	// 排序标记
	$sort_flag = sort_flag ( $col_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	make_json_result ( $smarty->fetch ( 'col_list.tpl' ), '', array ('filter' => $col_list ['filter'], 'page_count' => $col_list ['page_count'] ) );
} /*------------------------------------------------------ */
//-- 导入采集节点信息
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'importrule') {
	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['importrule'] );

	$action_link = array ('href' => 'col_main.php?act=list', 'text' => $_LANG ['collection_col'] );
	$smarty->assign ( 'action_link', $action_link );

	$smarty->assign ( 'lang', $_LANG );
	$smarty->assign ( 'importrule_desc', $_LANG ['importrule_desc'] );

	$smarty->display ( 'col_importrule.tpl' );
} /*------------------------------------------------------ */
//-- 执行导入采集节点信息
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'importrule_action') {
	//权限检查
	admin_priv ( 'collection' );

	$skyuc->input->clean_gpc ( 'p', 'importrule', TYPE_STR );

	//对Base64格式的规则进行解码
	if (preg_match ( '/^BASE64:/', $skyuc->GPC ['importrule'] )) {
		if (! preg_match ( '/:END$/', $skyuc->GPC ['importrule'] )) {
			sys_msg ( $_LANG ['import_error_base64rule'], 1, array (), false );
		}
		$notess = explode ( ':', $skyuc->GPC ['importrule'] );
		$skyuc->GPC ['importrule'] = base64_decode ( preg_replace ( "/[\r\n\t ]/", '', $notess [1] ) ) or die ( $_LANG ['import_error_str'] );
	} elseif (! preg_match ( '/<suc:([^>]+)>([\s\S]*?)<\/suc>/', $skyuc->GPC ['importrule'] )) {
		sys_msg ( $_LANG ['import_error_rule'], 1, array (), false );
	}

	$collection = new Collection ( $skyuc );
	$codeArray = $collection->parse_col_code ( $skyuc->GPC ['importrule'] );

	$codeArray ['savepic'] = isset ( $codeArray ['savepic'] ) ? $codeArray ['savepic'] : 0; //防止不选择时漏掉此项
	$itemconfig = $collection->generate_col_code ( $codeArray ); //由传递的数组生成规则代码


	$sql = 'INSERT INTO ' . TABLE_PREFIX . 'co_note' . ' (gathername,language,player,cat_id,server_id,lasttime,savetime,noteinfo) ' . " VALUES('" . $db->escape_string ( $codeArray ['notename'] ) . "', '" . $db->escape_string ( $codeArray ['language'] ) . "','" . $db->escape_string ( $codeArray ['player'] ) . "','" . $codeArray ['cat_id'] . "', '" . $codeArray ['server_id'] . "',  '0','" . TIMENOW . "', '" . $db->escape_string ( $itemconfig ) . "')";

	$db->query_write ( $sql );

	//提示页面
	$link [] = array ('text' => $_LANG ['back_conote_list'], 'href' => 'col_main.php?act=list' );
	sys_msg ( $_LANG ['addconote_ok'], 0, $link );
} /*------------------------------------------------------ */
//-- 导出采集节点信息
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'exportrule') {
	//权限检查
	admin_priv ( 'collection' );

	$skyuc->input->clean_array_gpc ( 'g', array ('extype' => TYPE_STR, 'nid' => TYPE_UINT ) );

	$smarty->assign ( 'ur_here', $_LANG ['exportrule'] );
	$action_link = array ('href' => 'col_main.php?act=list', 'text' => $_LANG ['collection_col'] );
	$smarty->assign ( 'action_link', $action_link );

	$collection = new Collection ( $skyuc );
	$col = $collection->get_col_info ( $skyuc->GPC ['nid'], 1 ); //取出节点信息
	$col ['noteinfo'] = str_replace ( '</suc>', "</suc>\r\n", $col ['noteinfo'] );

	if ($skyuc->GPC ['extype'] == 'base64' || $skyuc->GPC ['extype'] == '') {
		$noteconfig = 'BASE64:' . base64_encode ( $col ['noteinfo'] ) . ':END';
		$smarty->assign ( 'extype', 'text' );
	} elseif ($skyuc->GPC ['extype'] == 'text') {
		$noteconfig = $col ['noteinfo'];
		$smarty->assign ( 'extype', 'base64' );
	}

	$smarty->assign ( 'lang', $_LANG );
	$smarty->assign ( 'nid', $skyuc->GPC ['nid'] );
	$smarty->assign ( 'notename', $col ['gathername'] );
	$smarty->assign ( 'noteinfo', $noteconfig );
	$smarty->assign ( 'exportrule_desc', sprintf ( $_LANG ['exportrule_desc'], $col ['gathername'] ) );

	assign_query_info ();
	$smarty->display ( 'col_importrule.tpl' );
} /*------------------------------------------------------ */
//-- 导出数据
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'export') {
	// 权限检查
	admin_priv ( 'collection' );

	$skyuc->input->clean_gpc ( 'g', 'nid', TYPE_UINT );

	$total = $db->query_first ( 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'co_html' . ' WHERE nid=' . $skyuc->GPC ['nid'] );
	$note = $db->query_first ( 'SELECT gathername, cat_id FROM ' . TABLE_PREFIX . 'co_note' . ' WHERE nid=' . $skyuc->GPC ['nid'] );

	$smarty->assign ( 'ur_here', $_LANG ['exportdata'] );
	$action_link = array ('href' => 'col_main.php?act=list', 'text' => $_LANG ['collection_col'] );
	$smarty->assign ( 'action_link', $action_link );

	$smarty->assign ( 'nid', $skyuc->GPC ['nid'] );

	$smarty->assign ( 'lang', $_LANG );
	$smarty->assign ( 'cfg', $skyuc->options );
	$smarty->assign ( 'totalnum', $total ['total'] );
	$smarty->assign ( 'notename', $note ['gathername'] );
	$smarty->assign ( 'totalnote', sprintf ( $_LANG ['totalnote'], $total ['total'] ) );
	$smarty->assign ( 'cat_list', get_cat_list ( 0, $note ['cat_id'] ) );

	assign_query_info ();
	$smarty->display ( 'col_export.tpl' );

}

/*------------------------------------------------------ */
//-- 添加或修改节点信息
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'add' || $skyuc->GPC ['act'] == 'edit') {
	admin_priv ( 'collection' );

	$is_add = ($skyuc->GPC ['act'] == 'add');
	$is_edit = ($skyuc->GPC ['act'] == 'edit');

	// 模板赋值
	if ($is_add) {

		$smarty->assign ( 'ur_here', $_LANG ['collection_add'] );

		$action_link = array ('href' => 'col_main.php?act=list', 'text' => $_LANG ['collection_col'] );
		$smarty->assign ( 'action_link', $action_link );
		$col = array ('language' => 'gb2312', 'varstart' => '1', 'varend' => '2', 'addv' => '1' );
	} else {
		$smarty->assign ( 'ur_here', $_LANG ['collection_edit'] );

		$action_link = array ('href' => 'col_main.php?act=list', 'text' => $_LANG ['collection_col'] );
		$smarty->assign ( 'action_link', $action_link );

		$nid = $skyuc->input->clean_gpc ( 'g', 'nid', TYPE_UINT );

		$collection = new Collection ( $skyuc );
		//取得节点信息
		$colinfo = $collection->get_col_info ( $nid, 1 );
		$col = $collection->parse_col_code ( $colinfo ['noteinfo'] );
		$col ['notename'] = $colinfo ['gathername'];

		$smarty->assign ( 'nid', $nid );
	}

	$smarty->assign ( 'lang', $_LANG );
	$smarty->assign ( 'cfg', $skyuc->options );
	$smarty->assign ( 'col', $col );
	$smarty->assign ( 'form_act', iif ( $is_add, 'insert', iif ( $is_edit, 'update', 'insert' ) ) );
	$smarty->assign ( 'cat_list', get_cat_list ( 0, $col ['cat_id'] ) );
	$smarty->assign ( 'server_list', get_server_list ( 2 ) );
	$smarty->assign ( 'player', get_player_list () );

	assign_query_info ();
	$smarty->display ( 'col_info.tpl' );

} /*------------------------------------------------------ */
//-- 执行添加或修改节点信息
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'insert' || $skyuc->GPC ['act'] == 'update') {
	admin_priv ( 'collection' );

	$skyuc->input->clean_array_gpc ( 'p', array ('nid' => TYPE_UINT, 'savepic' => TYPE_BOOL, 'notename' => TYPE_STR, 'language' => TYPE_STR, 'player' => TYPE_STR, 'cat_id' => TYPE_UINT, 'server_id' => TYPE_UINT ) );

	$codeArray = $_POST;
	$codeArray ['savepic'] = $skyuc->GPC ['savepic']; //防止不选择时漏掉此项


	$collection = new Collection ( $skyuc );
	$itemconfig = $collection->generate_col_code ( $codeArray ); //由传递的数组生成规则代码


	if ($skyuc->GPC ['act'] == 'insert') {

		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'co_note' . ' (gathername,language,player,cat_id,server_id,lasttime,savetime,noteinfo) ' . "	VALUES('" . $db->escape_string ( $skyuc->GPC ['notename'] ) . "', '" . $db->escape_string ( $skyuc->GPC ['language'] ) . "','" . $db->escape_string ( $skyuc->GPC ['player'] ) . "','" . $skyuc->GPC ['cat_id'] . "', '" . $skyuc->GPC ['server_id'] . "',  '0','" . TIMENOW . "', '" . $db->escape_string ( $itemconfig ) . "')";

		$db->query_write ( $sql );

		$link [] = array ('text' => $_LANG ['continue_add'], 'href' => 'col_main.php?act=add' );
		$link [] = array ('text' => $_LANG ['back_conote_list'], 'href' => 'col_main.php?act=list' );

		// 提示页面
		sys_msg ( $_LANG ['addconote_ok'], 0, $link );
	} else {
		$sql = 'UPDATE ' . TABLE_PREFIX . 'co_note' . ' SET ' . " gathername = '" . $db->escape_string ( $skyuc->GPC ['notename'] ) . "', " . " language = '" . $db->escape_string ( $skyuc->GPC ['language'] ) . "', " . " player = '" . $db->escape_string ( $skyuc->GPC ['player'] ) . "', " . " cat_id = '" . $skyuc->GPC ['cat_id'] . "', " . " server_id = '" . $skyuc->GPC ['server_id'] . "', " . " savetime = '" . TIMENOW . "', " . " noteinfo = '" . $db->escape_string ( $itemconfig ) . "' " . ' WHERE nid = ' . $skyuc->GPC ['nid'];
		$db->query_write ( $sql );

		$link [] = array ('text' => $_LANG ['back_conote_list'], 'href' => 'col_main.php?act=list' );
		// 提示页面
		sys_msg ( $_LANG ['editconote_ok'], 0, $link );
	}
} /*------------------------------------------------------ */
//-- 执行采集前页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'gather') {
	//	权限检查
	admin_priv ( 'collection' );

	$skyuc->input->clean_gpc ( 'g', 'nid', TYPE_UINT );
	if (! empty ( $skyuc->GPC ['nid'] )) {
		$collection = new Collection ( $skyuc );
		$colinfo = $collection->get_col_info ( $skyuc->GPC ['nid'], 1 ); //取得节点信息
		$col = $collection->parse_col_code ( $colinfo ['noteinfo'] );
		$total = $db->query_first ( 'SELECT COUNT(aid) AS total FROM ' . TABLE_PREFIX . 'co_html' . ' WHERE nid=' . $skyuc->GPC ['nid'] );
		$col ['seed'] = $total ['total'];

		if ($col ['seed'] == 0) {
			$col ['unum'] = $_LANG ['no_seed'];
		} else {
			$col ['unum'] = sprintf ( $_LANG ['seeds'], $col ['seed'] );
		}
		$ur_here = $_LANG ['collection_note'];
		$smarty->assign ( 'nid', $skyuc->GPC ['nid'] );
	} else {
		$ur_here = $_LANG ['collection_title'];
		$col = array ();
		$col ['notename'] = $_LANG ['all_node'];
		$col ['unum'] = $_LANG ['use_monitor'];
	}

	$smarty->assign ( 'ur_here', $ur_here );

	$action_link = array ('href' => 'col_main.php?act=list', 'text' => $_LANG ['collection_col'] );
	$smarty->assign ( 'action_link', $action_link );
	$smarty->assign ( 'col', $col );
	$smarty->assign ( 'lang', $_LANG );

	assign_query_info ();
	$smarty->display ( 'col_start.tpl' );

} /*------------------------------------------------------ */
//-- 复制一个节点
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'copy') {

	// 检查权限
	admin_priv ( 'collection' );

	// 取得要操作的编号
	$skyuc->input->clean_gpc ( 'g', 'nid', TYPE_UINT );

	if (! empty ( $skyuc->GPC ['nid'] )) {
		$collection = new Collection ( $skyuc );
		$row = $collection->get_col_info ( $skyuc->GPC ['nid'], 1 ); //取得节点信息
		if (! empty ( $row )) {
			$notename = $row ['gathername'] . ' Copy!';
			$sql = 'INSERT INTO ' . TABLE_PREFIX . 'co_note' . '(gathername,	language,	player,	cat_id,	server_id,	lasttime,	savetime,	noteinfo) ' . " VALUES('" . $db->escape_string ( $notename ) . "', '" . $db->escape_string ( $row ['language'] ) . "','" . $db->escape_string ( $row ['player'] ) . "','" . $row ['cat_id'] . "','" . $row ['server_id'] . "', '0','" . TIMENOW . "', '" . $db->escape_string ( $row ['noteinfo'] ) . "')";
			$db->query_write ( $sql );
		}
	}

	$link [] = array ('href' => 'col_main.php?act=list', 'text' => $_LANG ['collection_col'] );
	sys_msg ( $_LANG ['copy_succeed'], 0, $link );
} /*------------------------------------------------------ */
//-- 删除一个节点
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'delete') {

	// 检查权限
	admin_priv ( 'collection' );

	// 取得要操作的编号
	$skyuc->input->clean_gpc ( 'g', 'nid', TYPE_UINT );

	if ($skyuc->GPC ['nid'] != 0) {
		$db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'co_html' . ' WHERE nid =' . $skyuc->GPC ['nid'] );
		$db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'co_listen' . ' WHERE nid =' . $skyuc->GPC ['nid'] );
		$db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'co_note' . ' WHERE nid =' . $skyuc->GPC ['nid'] );
	}

	$link [] = array ('href' => 'col_main.php?act=list', 'text' => $_LANG ['collection_col'] );
	sys_msg ( $_LANG ['delete_succeed'], 0, $link );
}
?>
