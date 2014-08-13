<?php

/**
 * SKYUC! 文章分类管理程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');
$exc = new exchange ( TABLE_PREFIX . 'article_cat', $skyuc->db, 'cat_id', 'cat_name' );

/*------------------------------------------------------ */
//-- 分类列表
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list' || $skyuc->GPC ['act'] == '') {
	$articlecat = article_cat_list ( 0, 0, false );

	foreach ( $articlecat as $key => $cat ) {
		$articlecat [$key] ['type_name'] = $_LANG ['type_name'] [$cat ['cat_type']];
	}
	$smarty->assign ( 'ur_here', $_LANG ['02_articlecat_list'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['articlecat_add'], 'href' => 'articlecat.php?act=add' ) );
	$smarty->assign ( 'full_page', 1 );
	$smarty->assign ( 'articlecat', $articlecat );

	assign_query_info ();
	$smarty->display ( 'articlecat_list.tpl' );
}

/*------------------------------------------------------ */
//-- 查询
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$articlecat = article_cat_list ( 0, 0, false );
	foreach ( $articlecat as $key => $cat ) {
		$articlecat [$key] ['type_name'] = $_LANG ['type_name'] [$cat ['cat_type']];
	}
	$smarty->assign ( 'articlecat', $articlecat );

	make_json_result ( $smarty->fetch ( 'articlecat_list.tpl' ) );
}

/*------------------------------------------------------ */
//-- 添加分类
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'add') {
	// 权限判断
	admin_priv ( 'article_cat' );

	$smarty->assign ( 'cat_select', article_cat_list ( 0 ) );
	$smarty->assign ( 'ur_here', $_LANG ['articlecat_add'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['02_articlecat_list'], 'href' => 'articlecat.php?act=list' ) );
	$smarty->assign ( 'form_action', 'insert' );

	assign_query_info ();
	$smarty->display ( 'articlecat_info.tpl' );
} elseif ($skyuc->GPC ['act'] == 'insert') {
	// 权限判断
	admin_priv ( 'article_cat' );

	$skyuc->input->clean_array_gpc ( 'p', array ('cat_name' => TYPE_STR, 'id' => TYPE_UINT, 'parent_id' => TYPE_UINT, 'show_in_nav' => TYPE_BOOL, 'cat_desc' => TYPE_STR, 'keywords' => TYPE_STR, 'sort_order' => TYPE_UINT ) );

	//检查分类名是否重复
	$is_only = $exc->is_only ( 'cat_name', $skyuc->GPC ['cat_name'] );

	if (! $is_only) {
		sys_msg ( sprintf ( $_LANG ['catname_exist'], $skyuc->GPC ['cat_name'] ), 1 );
	}

	$cat_type = 1;
	if ($skyuc->GPC ['parent_id'] > 0) {
		$sql = 'SELECT cat_type FROM ' . TABLE_PREFIX . 'article_cat' . ' WHERE cat_id = ' . $skyuc->GPC ['parent_id'];
		$row = $db->query_first ( $sql );
		$p_cat_type = $row ['cat_type'];
		if ($p_cat_type == 2 || $p_cat_type == 3 || $p_cat_type == 5) {
			sys_msg ( $_LANG ['not_allow_add'], 0 );
		} else if ($p_cat_type == 4) {
			$cat_type = 5;
		}
	}

	$sql = 'INSERT INTO ' . TABLE_PREFIX . 'article_cat' . '(cat_name, cat_type, cat_desc,keywords, parent_id, sort_order, show_in_nav) ' . " VALUES ('" . $db->escape_string ( $skyuc->GPC ['cat_name'] ) . "', '$cat_type',  '" . $db->escape_string ( $skyuc->GPC ['cat_desc'] ) . "','" . $db->escape_string ( $skyuc->GPC ['keywords'] ) . "', '" . $skyuc->GPC ['parent_id'] . "', '" . $skyuc->GPC ['sort_order'] . "', '" . $skyuc->GPC ['show_in_nav'] . "')";
	$db->query_write ( $sql );
	$newid = $db->insert_id ();

	if ($skyuc->GPC ['show_in_nav'] == 1) {
		$maxnum = $db->query_first ( 'SELECT max(vieworder) AS maxnum FROM ' . TABLE_PREFIX . 'nav' . " WHERE type = 'middle'" );
		$vieworder = $maxnum ['maxnum'] + 2;
		//显示在自定义导航栏中
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'nav' . ' (name,ctype,cid,ifshow,vieworder,opennew,url,type) ' . " VALUES('" . $db->escape_string ( $skyuc->GPC ['cat_name'] ) . "', 'a', '" . $newid . "','1','$vieworder','0', '" . build_uri ( 'article_cat', array ('acid' => $newid ) ) . "','middle'";
		$db->query_write ( $sql );
	}

	admin_log ( $skyuc->GPC ['cat_name'], 'add', 'articlecat' );

	$link [0] ['text'] = $_LANG ['continue_add'];
	$link [0] ['href'] = 'articlecat.php?act=add';

	$link [1] ['text'] = $_LANG ['back_list'];
	$link [1] ['href'] = 'articlecat.php?act=list';

	build_article_cat ();

	sys_msg ( $skyuc->GPC ['cat_name'] . $_LANG ['catadd_succed'], 0, $link );
}

/*------------------------------------------------------ */
//-- 编辑文章分类
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit') {
	// 权限判断
	admin_priv ( 'article_cat' );
	$skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	$sql = 'SELECT cat_id, cat_name, cat_type, cat_desc, show_in_nav, keywords, parent_id,sort_order FROM ' . TABLE_PREFIX . 'article_cat' . ' WHERE cat_id=' . $skyuc->GPC ['id'];
	$cat = $db->query_first ( $sql );

	if ($cat ['cat_type'] == 2 || $cat ['cat_type'] == 3 || $cat ['cat_type'] == 4) {
		$smarty->assign ( 'disabled', 1 );
	}
	$smarty->assign ( 'cat', $cat );
	$smarty->assign ( 'cat_select', article_cat_list ( 0, $cat ['parent_id'] ) );
	$smarty->assign ( 'ur_here', $_LANG ['articlecat_edit'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['02_articlecat_list'], 'href' => 'articlecat.php?act=list' ) );
	$smarty->assign ( 'form_action', 'update' );

	assign_query_info ();
	$smarty->display ( 'articlecat_info.tpl' );
} elseif ($skyuc->GPC ['act'] == 'update') {
	// 权限判断
	admin_priv ( 'article_cat' );

	$skyuc->input->clean_array_gpc ( 'p', array ('cat_name' => TYPE_STR, 'old_catname' => TYPE_STR, 'id' => TYPE_UINT, 'parent_id' => TYPE_UINT, 'show_in_nav' => TYPE_BOOL, 'cat_desc' => TYPE_STR, 'keywords' => TYPE_STR, 'sort_order' => TYPE_UINT ) );

	// 检查重名
	if ($skyuc->GPC ['cat_name'] != $skyuc->GPC ['old_catname']) {
		$is_only = $exc->is_only ( 'cat_name', $skyuc->GPC ['cat_name'], $skyuc->GPC ['id'] );

		if (! $is_only) {
			sys_msg ( sprintf ( $_LANG ['catname_exist'], $skyuc->GPC ['cat_name'] ), 1 );
		}
	}

	$row = $db->query_first ( 'SELECT cat_type, parent_id FROM ' . TABLE_PREFIX . 'article_cat' . ' WHERE cat_id=' . $skyuc->GPC ['id'] );
	$cat_type = $row ['cat_type'];
	if ($cat_type == 3 || $cat_type == 4) {
		$skyuc->GPC ['parent_id'] = $row ['parent_id'];
	}

	// 检查设定的分类的父分类是否合法
	$child_cat = article_cat_list ( $skyuc->GPC ['id'], 0, false );
	if (! empty ( $child_cat )) {
		foreach ( $child_cat as $child_data ) {
			$catid_array [] = $child_data ['cat_id'];
		}
	}
	if (in_array ( $skyuc->GPC ['parent_id'], $catid_array )) {
		sys_msg ( sprintf ( $_LANG ['parent_id_err'], $skyuc->GPC ['cat_name'] ), 1 );
	}

	if ($cat_type == 1 || $cat_type == 5) {
		if ($skyuc->GPC ['parent_id'] > 0) {
			$sql = 'SELECT cat_type FROM ' . TABLE_PREFIX . 'article_cat' . ' WHERE cat_id = ' . $skyuc->GPC ['parent_id'];
			$row = $db->query_first ( $sql );
			$p_cat_type = $row ['cat_type'];
			if ($p_cat_type == 4) {
				$cat_type = 5;
			} else {
				$cat_type = 1;
			}
		} else {
			$cat_type = 1;
		}
	}

	$dat = $db->query_first ( 'SELECT cat_name, show_in_nav FROM ' . TABLE_PREFIX . 'article_cat' . " WHERE cat_id = '" . $skyuc->GPC ['id'] . "'" );
	if ($exc->edit ( "cat_name = '" . $db->escape_string ( $skyuc->GPC ['cat_name'] ) . "', cat_desc ='" . $db->escape_string ( $skyuc->GPC ['cat_desc'] ) . "', keywords='" . $db->escape_string ( $skyuc->GPC ['keywords'] ) . "',parent_id = '" . $skyuc->GPC ['parent_id'] . "', cat_type='$cat_type', sort_order='" . $skyuc->GPC ['sort_order'] . "', show_in_nav = '" . $skyuc->GPC ['show_in_nav'] . "'", $skyuc->GPC ['id'] )) {
		if ($skyuc->GPC ['cat_name'] != $dat ['cat_name']) {
			//如果分类名称发生了改变
			$sql = 'UPDATE ' . TABLE_PREFIX . 'nav' . " SET name = '" . $db->escape_string ( $skyuc->GPC ['cat_name'] ) . "' WHERE ctype = 'a' AND cid = '" . $skyuc->GPC ['id'] . "' AND type = 'middle'";
			$db->query_write ( $sql );
		}
		if ($skyuc->GPC ['show_in_nav'] != $dat ['show_in_nav']) {
			if ($skyuc->GPC ['show_in_nav'] == 1) {
				//显示
				$nav_id = $db->query_first ( 'SELECT id FROM ' . TABLE_PREFIX . 'nav' . " WHERE ctype = 'a' AND cid = '" . $skyuc->GPC ['id'] . "' AND type = 'middle'" );
				$nid = $nav_id ['id'];
				if (empty ( $nid )) {
					$maxnum = $db->query_first ( "SELECT max(vieworder) AS maxnum FROM " . TABLE_PREFIX . 'nav' . " WHERE type = 'middle'" );
					$vieworder = $maxnum ['maxnum'] + 2;
					$uri = build_uri ( 'article_cat', array ('acid' => $skyuc->GPC ['id'] ) );
					//不存在
					$sql = 'INSERT INTO ' . TABLE_PREFIX . 'nav' . ' (name,ctype,cid,ifshow,vieworder,opennew,url,type) ' . "VALUES('" . $db->escape_string ( $skyuc->GPC ['cat_name'] ) . "', 'a', '" . $skyuc->GPC ['id'] . "','1','$vieworder','0', '" . $uri . "','middle')";
				} else {
					$sql = 'UPDATE ' . TABLE_PREFIX . 'nav' . " SET ifshow = 1 WHERE ctype = 'a' AND cid = '" . $skyuc->GPC ['id'] . "' AND type = 'middle'";
				}
				$db->query_write ( $sql );
			} else {
				//去除
				$db->query ( 'UPDATE ' . TABLE_PREFIX . 'nav' . " SET ifshow = 0 WHERE ctype = 'a' AND cid = '" . $skyuc->GPC ['id'] . "' AND type = 'middle'" );
			}
		}
		$link [0] ['text'] = $_LANG ['back_list'];
		$link [0] ['href'] = 'articlecat.php?act=list';
		$note = sprintf ( $_LANG ['catedit_succed'], $skyuc->GPC ['cat_name'] );
		admin_log ( $skyuc->GPC ['cat_name'], 'edit', 'articlecat' );

		build_article_cat ();

		sys_msg ( $note, 0, $link );

	} else {
		die ( $skyuc->db->error () );
	}
}

/*------------------------------------------------------ */
//-- 编辑文章分类的排序
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_sort_order') {
	check_authz_json ( 'article_cat' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	$id = $skyuc->GPC ['id'];
	$order = $skyuc->GPC ['val'];

	// 检查输入的值是否合法
	if (! preg_match ( "/^[0-9]+$/", $order )) {
		make_json_error ( sprintf ( $_LANG ['enter_int'], $order ) );
	} else {
		if ($exc->edit ( "sort_order = '$order'", $id )) {
			build_article_cat ();
			make_json_result ( $order );
		} else {
			make_json_error ( $skyuc->db->error () );
		}
	}
}

/*------------------------------------------------------ */
//-- 删除文章分类
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'remove') {
	check_authz_json ( 'article_cat' );

	$id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	$sql = 'SELECT cat_type FROM ' . TABLE_PREFIX . 'article_cat' . " WHERE cat_id = '$id'";
	$row = $skyuc->db->query_first ( $sql );
	$cat_type = $row ['cat_type'];
	if ($cat_type == 2 || $cat_type == 3 || $cat_type == 4) {
		// 系统保留分类，不能删除
		make_json_error ( $_LANG ['not_allow_remove'] );
	}

	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'article_cat' . " WHERE parent_id = '$id'";
	$total = $skyuc->db->query_first ( $sql );
	if ($total ['total'] > 0) {
		//还有子分类，不能删除
		make_json_error ( $_LANG ['is_fullcat'] );
	}

	// 非空的分类不允许删除
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'article' . " WHERE cat_id = '$id'";
	$total = $skyuc->db->query_first ( $sql );
	if ($total ['total'] > 0) {
		make_json_error ( sprintf ( $_LANG ['not_emptycat'] ) );
	} else {
		$exc->drop ( $id );
		$skyuc->db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'nav' . " WHERE  ctype = 'a' AND cid = '$id' AND type = 'middle'" );

		build_article_cat ();

		admin_log ( '', 'remove', 'category' );
	}

	$url = 'articlecat.php?act=query&' . str_replace ( 'act=remove', '', $_SERVER ['QUERY_STRING'] );

	header ( "Location: $url\n" );
	exit ();
}
/*------------------------------------------------------ */
//-- 切换是否显示在导航栏
/*------------------------------------------------------ */

if ($skyuc->GPC ['act'] == 'toggle_show_in_nav') {
	check_authz_json ( 'cat_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	$id = $skyuc->GPC ['id'];
	$val = $skyuc->GPC ['val'];

	if ($skyuc->db->query_write ( 'UPDATE	' . TABLE_PREFIX . 'article_cat' . ' SET show_in_nav=' . $db->escape_string ( $val ) . ' WHERE cat_id=' . $id ) != false) {
		if ($val == 1) {
			//显示
			$nav_id = $skyuc->db->query_first ( 'SELECT id FROM ' . TABLE_PREFIX . 'nav' . " WHERE ctype='a' AND cid='$id' AND type = 'middle'" );
			$nid = $nav_id ['id'];
			if (empty ( $nid )) {
				//不存在
				$maxnum = $skyuc->db->query_first ( 'SELECT max(vieworder) AS maxnum FROM ' . TABLE_PREFIX . 'nav' . " WHERE type = 'middle'" );
				$vieworder = $maxnum ['maxnum'] + 2;
				$row = $skyuc->db->query_first ( 'SELECT cat_name FROM ' . TABLE_PREFIX . 'article_cat' . " WHERE cat_id = '$id'" );
				$catname = $row ['cat_name'];
				$uri = build_uri ( 'article_cat', array ('acid' => $id ) );

				$sql = 'INSERT INTO ' . TABLE_PREFIX . 'nav' . ' (name,ctype,cid,ifshow,vieworder,opennew,url,type) ' . "VALUES('" . $db->escape_string ( $catname ) . "', 'a', '$id','1','$vieworder','0', '" . $uri . "','middle')";
			} else {
				$sql = 'UPDATE ' . TABLE_PREFIX . 'nav' . " SET ifshow = 1 WHERE ctype='a' AND cid='$id' AND type = 'middle'";
			}
			$skyuc->db->query_write ( $sql );
		} else {
			//去除
			$skyuc->db->query_write ( 'UPDATE ' . TABLE_PREFIX . 'nav' . " SET ifshow = 0 WHERE ctype='a' AND cid='$id' AND type = 'middle'" );
		}

		build_article_cat ();
		make_json_result ( $val );
	} else {
		make_json_error ( $skyuc->db->error () );
	}
}

?>
