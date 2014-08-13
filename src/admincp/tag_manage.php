<?php

/**
 * SKYUC! 标签云管理
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

/*------------------------------------------------------ */
//-- 获取标签数据列表
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list' || $skyuc->GPC ['act'] == '') {
	// 权限判断
	admin_priv ( 'tag_manage' );

	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['tag_list'] );
	$smarty->assign ( 'action_link', array ('href' => 'tag_manage.php?act=add', 'text' => $_LANG ['add_tag'] ) );
	$smarty->assign ( 'full_page', 1 );

	$tag_list = get_tag_list ();
	$smarty->assign ( 'tag_list', $tag_list ['tags'] );
	$smarty->assign ( 'filter', $tag_list ['filter'] );
	$smarty->assign ( 'record_count', $tag_list ['record_count'] );
	$smarty->assign ( 'page_count', $tag_list ['page_count'] );

	$sort_flag = sort_flag ( $tag_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	assign_query_info ();
	$smarty->display ( 'tag_manage.tpl' );
}

/*------------------------------------------------------ */
//-- 添加 ,编辑
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'add' || $skyuc->GPC ['act'] == 'edit') {
	admin_priv ( 'tag_manage' );

	$is_add = $skyuc->GPC ['act'] == 'add';
	$smarty->assign ( 'insert_or_update', $is_add ? 'insert' : 'update' );

	if ($is_add) {
		$tag = array ('tag_id' => 0, 'tag_words' => '', 'show_id' => 0, 'title' => $_LANG ['pls_select_show'] );
		$smarty->assign ( 'ur_here', $_LANG ['add_tag'] );
	} else {
		$tag_id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );
		$tag = get_tag_info ( $tag_id );

		$smarty->assign ( 'ur_here', $_LANG ['tag_edit'] );
	}
	$smarty->assign ( 'tag', $tag );
	$smarty->assign ( 'action_link', array ('href' => 'tag_manage.php?act=list', 'text' => $_LANG ['tag_list'] ) );

	assign_query_info ();
	$smarty->display ( 'tag_edit.tpl' );
}

/*------------------------------------------------------ */
//-- 更新
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'insert' || $skyuc->GPC ['act'] == 'update') {
	admin_priv ( 'tag_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('tag_name' => TYPE_STR, 'show_id' => TYPE_UINT, 'id' => TYPE_UINT ) );

	$is_insert = $skyuc->GPC ['act'] == 'insert';

	$tag_words = $skyuc->GPC ['tag_name'];
	$id = $skyuc->GPC ['id'];
	$show_id = $skyuc->GPC ['show_id'];

	if ($show_id <= 0) {
		sys_msg ( $_LANG ['pls_select_show'] );
	}

	if (! tag_is_only ( $tag_words, $id, $show_id )) {
		sys_msg ( sprintf ( $_LANG ['tagword_exist'], $tag_words ) );
	}

	    include(DIR. '/includes/functions_search.php');
	    $param = array();
		$param['show_id'] = $show_id;
	    $param['tag'] = $tag_words;
		add_search_index($param, false, true);

	if ($is_insert) {
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'tag' . '(tag_id, show_id, tag_words)' . " VALUES('$id', '$show_id', '" . $db->escape_string ( $tag_words ) . "')";
		$db->query_write ( $sql );

		admin_log ( $tag_words, 'add', 'tag' );

		// 清除缓存
		$cachename = 'show_' . sprintf ( '%X', crc32 ( $show_id ) );
		$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', md5 ( $cachename ) ) );

		$link [0] ['text'] = $_LANG ['back_list'];
		$link [0] ['href'] = 'tag_manage.php?act=list';

		sys_msg ( $_LANG ['tag_add_success'], 0, $link );
	} else {

		edit_tag ( $tag_words, $id, $show_id );

		//清除缓存
		$cachename = 'show_' . sprintf ( '%X', crc32 ( $show_id ) );
		$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', md5 ( $cachename ) ) );

		$link [0] ['text'] = $_LANG ['back_list'];
		$link [0] ['href'] = 'tag_manage.php?act=list';

		sys_msg ( $_LANG ['tag_edit_success'], 0, $link );
	}
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'query') {
	check_authz_json ( 'tag_manage' );

	$tag_list = get_tag_list ();
	$smarty->assign ( 'tag_list', $tag_list ['tags'] );
	$smarty->assign ( 'filter', $tag_list ['filter'] );
	$smarty->assign ( 'record_count', $tag_list ['record_count'] );
	$smarty->assign ( 'page_count', $tag_list ['page_count'] );

	$sort_flag = sort_flag ( $tag_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	make_json_result ( $smarty->fetch ( 'tag_manage.tpl' ), '', array ('filter' => $tag_list ['filter'], 'page_count' => $tag_list ['page_count'] ) );
}

/*------------------------------------------------------ */
//-- 搜索
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'search_show') {
	check_authz_json ( 'tag_manage' );

	$skyuc->input->clean_gpc ( 'g', 'JSON', TYPE_STR );

	include_once (DIR . '/includes/class_json.php');

	$json = new JSON ();
	$filter = $json->decode ( $skyuc->GPC ['JSON'] );
	$arr = get_show_article ( $filter );
	if (empty ( $arr )) {
		$arr [0] = array ('show_id' => 0, 'title' => '' );
	}

	make_json_result ( $arr );
}

/*------------------------------------------------------ */
//-- 批量删除标签
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'batch_drop') {
	admin_priv ( 'tag_manage' );

	$skyuc->input->clean_gpc ( 'p', 'checkboxes', TYPE_ARRAY_UINT );

	if ($skyuc->GPC_exists ['checkboxes']) {
		$count = 0;
		foreach ( $skyuc->GPC ['checkboxes'] as $key => $id ) {
			$sql = 'DELETE FROM ' . TABLE_PREFIX . 'tag' . ' WHERE tag_id=' . $id;
			$db->query_write ( $sql );

			$count ++;
		}

		admin_log ( $count, 'remove', 'tag' );

		$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', 'show.dwt' ) );

		$link [] = array ('text' => $_LANG ['back_list'], 'href' => 'tag_manage.php?act=list' );
		sys_msg ( sprintf ( $_LANG ['drop_success'], $count ), 0, $link );
	} else {
		$link [] = array ('text' => $_LANG ['back_list'], 'href' => 'tag_manage.php?act=list' );
		sys_msg ( $_LANG ['no_select_tag'], 0, $link );
	}
}

/*------------------------------------------------------ */
//-- 删除标签
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'remove') {
	check_authz_json ( 'tag_manage' );

	include_once (DIR . '/includes/class_json.php');
	$json = new JSON ();

	$skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	$id = $skyuc->GPC ['id'];

	// 获取删除的标签的名称
	$tag = $db->query_first ( 'SELECT tag_words FROM ' . TABLE_PREFIX . 'tag' . ' WHERE tag_id = ' . $id );
	$tag_name = $tag ['tag_words'];

	$sql = 'DELETE FROM ' . TABLE_PREFIX . 'tag' . ' WHERE tag_id = ' . $id;
	$result = $db->query_write ( $sql );
	if ($result) {
		// 管理员日志
		admin_log ( $tag_name, 'remove', 'tag' );

		$url = 'tag_manage.php?act=query&' . str_replace ( 'act=remove', '', $_SERVER ['QUERY_STRING'] );
		header ( "Location: $url\n" );
		exit ();
	} else {
		make_json_error ( $db->error () );
	}
}

/*------------------------------------------------------ */
//-- 编辑标签名称
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'edit_tag_name') {
	check_authz_json ( 'tag_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$name = $skyuc->GPC ['val'];
	$id = $skyuc->GPC ['id'];

	if (! tag_is_only ( $name, $id )) {
		make_json_error ( sprintf ( $_LANG ['tagword_exist'], $name ) );
	} else {
		edit_tag ( $name, $id );
		make_json_result ( $name );
	}
}

?>
