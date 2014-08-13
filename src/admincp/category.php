<?php

/**
 * SKYUC! 影片分类管理程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');
$exc = new exchange ( TABLE_PREFIX . 'category', $db, 'cat_id', 'cat_name' );

/*------------------------------------------------------ */
//-- 影片分类列表
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list' || $skyuc->GPC ['act'] == '') {
	// 获取分类列表
	$cat_list = get_cat_list ( 0, 0, false );

	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['06_category_list'] );
	$smarty->assign ( 'action_link', array ('href' => 'category.php?act=add', 'text' => $_LANG ['04_category_add'] ) );
	$smarty->assign ( 'full_page', 1 );

	$smarty->assign ( 'cat_info', $cat_list );

	assign_query_info ();
	$smarty->display ( 'category_list.tpl' );
} /*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$cat_list = get_cat_list ( 0, 0, false );
	$smarty->assign ( 'cat_info', $cat_list );

	make_json_result ( $smarty->fetch ( 'category_list.tpl' ) );
}
/*------------------------------------------------------ */
//-- 添加影片分类
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'add') {
	// 权限检查
	admin_priv ( 'cat_manage' );

	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['04_category_add'] );
	$smarty->assign ( 'action_link', array ('href' => 'category.php?act=list', 'text' => $_LANG ['06_category_list'] ) );

	$smarty->assign ( 'cat_select', get_cat_list ( 0, 0, true ) );
	$smarty->assign ( 'form_act', 'insert' );

	assign_query_info ();
	$smarty->display ( 'category_info.tpl' );
}

/*------------------------------------------------------ */
//-- 影片分类添加时的处理
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'insert') {
	//权限检查
	admin_priv ( 'cat_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('cat_id' => TYPE_UINT, 'parent_id' => TYPE_UINT, 'sort_order' => TYPE_UINT, 'show_in_nav' => TYPE_BOOL, 'is_show' => TYPE_BOOL, 'keywords' => TYPE_STR, 'cat_desc' => TYPE_STR, 'cat_name' => TYPE_STR, 'style' => TYPE_STR ) );

	// 同级别下不能有重复的分类名称
	if (cat_exists ( $skyuc->GPC ['cat_name'], $skyuc->GPC ['parent_id'] )) {
		$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
		sys_msg ( $_LANG ['catname_exist'], 0, $link );
	}

	// 入库的操作
	$sql = 'INSERT INTO ' . TABLE_PREFIX . 'category' . ' (cat_name,  keywords,  style,  cat_desc,  parent_id,  sort_order,  is_show,  show_in_nav) ' . " VALUES('" . $db->escape_string ( $skyuc->GPC ['cat_name'] ) . "',  '" . $db->escape_string ( $skyuc->GPC ['keywords'] ) . "',  '" . $db->escape_string ( $skyuc->GPC ['style'] ) . "',  '" . $db->escape_string ( $skyuc->GPC ['cat_desc'] ) . "',  '" . $skyuc->GPC ['parent_id'] . "', '" . $skyuc->GPC ['sort_order'] . "', '" . $skyuc->GPC ['is_show'] . "', '" . $skyuc->GPC ['show_in_nav'] . "' )";
	if ($db->query_write ( $sql ) !== false) {
		$cat_id = $db->insert_id ();
		if ($cat ['show_in_nav'] == 1) {
			$nav = $db->query_first ( 'SELECT max(vieworder) AS max FROM ' . TABLE_PREFIX . 'nav' . " WHERE type = 'middle'" );
			$vieworder = $nav ['max'] + 2;

			//显示在自定义导航栏中
			$sql = 'INSERT INTO ' . TABLE_PREFIX . 'nav' . ' (name,ctype,cid,ifshow,vieworder,opennew,url,type)' . " VALUES('" . $db->escape_string ( $skyuc->GPC ['cat_name'] ) . "', 	'c', 	'" . $db->insert_id () . "',	'1',	'$vieworder',	'0', '" . build_uri ( 'category', array ('cid' => $cat_id ) ) . "',	'middle')";
			$db->query_write ( $sql );
		}

		admin_log ( $skyuc->GPC ['cat_name'], 'add', 'category' ); // 记录管理员操作


		build_category (); // 重建分类缓存


		//添加链接
		$link [0] ['text'] = $_LANG ['continue_add'];
		$link [0] ['href'] = 'category.php?act=add';

		$link [1] ['text'] = $_LANG ['back_list'];
		$link [1] ['href'] = 'category.php?act=list';

		sys_msg ( $_LANG ['catadd_succed'], 0, $link );
	}
}

/*------------------------------------------------------ */
//-- 编辑影片分类信息
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'edit') {
	// 权限检查
	admin_priv ( 'cat_manage' );

	$skyuc->input->clean_gpc ( 'g', 'cat_id', TYPE_UINT );

	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'category' . ' WHERE cat_id=' . $skyuc->GPC ['cat_id'];
	$cat_info = $skyuc->db->query_first ( $sql ); // 查询分类信息数据


	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['category_edit'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['06_category_list'], 'href' => 'category.php?act=list' ) );

	$smarty->assign ( 'cat_info', $cat_info );
	$smarty->assign ( 'form_act', 'update' );
	$smarty->assign ( 'cat_select', get_cat_list ( 0, $cat_info ['parent_id'], true ) );

	assign_query_info ();
	$smarty->display ( 'category_info.tpl' );
}
/*------------------------------------------------------ */
//-- 编辑影片分类信息
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'update') {
	// 权限检查
	admin_priv ( 'cat_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('cat_id' => TYPE_UINT, 'parent_id' => TYPE_UINT, 'sort_order' => TYPE_UINT, 'show_in_nav' => TYPE_BOOL, 'is_show' => TYPE_BOOL, 'keywords' => TYPE_STR, 'cat_desc' => TYPE_STR, 'cat_name' => TYPE_STR, 'style' => TYPE_STR, 'old_cat_name' => TYPE_STR ) );

	// 判断分类名是否重复
	if ($skyuc->GPC ['cat_name'] != $skyuc->GPC ['old_cat_name']) {
		if (cat_exists ( $skyuc->GPC ['cat_name'], $skyuc->GPC ['parent_id'] )) {
			$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
			sys_msg ( $_LANG ['catname_exist'], 0, $link );
		}
	}

	// 判断上级目录是否合法
	$children = array_keys ( get_cat_list ( $skyuc->GPC ['cat_id'], 0, false ) ); // 获得当前分类的所有下级分类
	if (in_array ( $skyuc->GPC ['parent_id'], $children )) {
		// 选定的父类是当前分类或当前分类的下级分类
		$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
		sys_msg ( $_LANG ["is_leaf_error"], 0, $link );
	}

	$dat = $db->query_first ( 'SELECT cat_name, show_in_nav FROM ' . TABLE_PREFIX . 'category' . " WHERE cat_id = '" . $skyuc->GPC ['cat_id'] . "'" );

	$sql = 'UPDATE ' . TABLE_PREFIX . 'category' . ' SET ' . ' parent_id = ' . $skyuc->GPC ['parent_id'] . ', ' . ' sort_order = ' . $skyuc->GPC ['sort_order'] . ', ' . ' show_in_nav = ' . $skyuc->GPC ['show_in_nav'] . ', ' . '	is_show = ' . $skyuc->GPC ['is_show'] . ', ' . " keywords = '" . $db->escape_string ( $skyuc->GPC ['keywords'] ) . "', " . " cat_desc = '" . $db->escape_string ( $skyuc->GPC ['cat_desc'] ) . "', " . " cat_name = '" . $db->escape_string ( $skyuc->GPC ['cat_name'] ) . "', " . " style = '" . $db->escape_string ( $skyuc->GPC ['style'] ) . "' " . ' WHERE cat_id=' . $skyuc->GPC ['cat_id'];
	if ($db->query_write ( $sql )) {
		if ($skyuc->GPC ['cat_name'] != $dat ['cat_name']) {
			//如果分类名称发生了改变
			$sql = 'UPDATE ' . TABLE_PREFIX . 'nav' . " SET name = '" . $db->escape_string ( $skyuc->GPC ['cat_name'] ) . "' WHERE ctype = 'c' AND cid = '" . $skyuc->GPC ['cat_id'] . "' AND type = 'middle'";
			$db->query_write ( $sql );
		}

		if ($skyuc->GPC ['show_in_nav'] != $dat ['show_in_nav']) {
			//是否显示于导航栏发生了变化
			if ($cat ['show_in_nav'] == 1) {
				//显示
				$nav = $db->query_first ( 'SELECT id FROM ' . TABLE_PREFIX . 'nav' . " WHERE ctype = 'c' AND cid = '" . $skyuc->GPC ['cat_id'] . "' AND type = 'middle'" );
				$nid = $nav ['id'];
				if (empty ( $nid )) {
					//不存在
					$max = $db->query_first ( 'SELECT max(vieworder) AS max FROM ' . TABLE_PREFIX . 'nav' . " WHERE type = 'middle'" );
					$vieworder = $max ['max'] + 2;
					$uri = build_uri ( 'category', array ('cid' => $skyuc->GPC ['cat_id'] ) );

					$sql = 'INSERT INTO ' . TABLE_PREFIX . 'nav' . ' (name,ctype,cid,ifshow,vieworder,opennew,url,type) ' . " VALUES('" . $db->escape_string ( $skyuc->GPC ['cat_name'] ) . "', 	'c', 	'" . $skyuc->GPC ['cat_id'] . "',	'1',	'$vieworder',	'0', 	'" . $db->escape_string ( $uri ) . "',	'middle'";
				} else {
					$sql = 'UPDATE ' . TABLE_PREFIX . 'nav' . " SET ifshow = 1 WHERE ctype = 'c' AND cid = '" . $skyuc->GPC ['cat_id'] . "' AND type = 'middle'";
				}
				$db->query_write ( $sql );
			} else {
				//去除
				$db->query_write ( 'UPDATE ' . TABLE_PREFIX . 'nav' . " SET ifshow = 0 WHERE ctype = 'c' AND cid = '" . $skyuc->GPC ['cat_id'] . "' AND type = 'middle'" );
			}
		}

		build_category (); // 重建分类缓存


		admin_log ( $skyuc->GPC ['cat_name'], 'edit', 'category' ); // 记录管理员操作


		// 提示信息
		$link [] = array ('text' => $_LANG ['back_list'], 'href' => 'category.php?act=list' );
		sys_msg ( $_LANG ['catedit_succed'], 0, $link );
	}
}

/*------------------------------------------------------ */
//-- 批量转移影片分类页面
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'move') {
	// 权限检查
	admin_priv ( 'cat_drop' );

	$cat_id = $skyuc->input->clean_gpc ( 'g', 'cat_id', TYPE_UINT );

	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['move_show'] );
	$smarty->assign ( 'action_link', array ('href' => 'category.php?act=list', 'text' => $_LANG ['06_category_list'] ) );

	$smarty->assign ( 'cat_select', get_cat_list ( 0, $cat_id, true ) );
	$smarty->assign ( 'form_act', 'move_cat' );

	assign_query_info ();
	$smarty->display ( 'category_move.tpl' );
}

/*------------------------------------------------------ */
//-- 处理批量转移影片分类的处理程序
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'move_cat') {
	// 权限检查
	admin_priv ( 'cat_drop' );

	$skyuc->input->clean_array_gpc ( 'p', array ('cat_id' => TYPE_UINT, 'target_cat_id' => TYPE_UINT ) );

	$cat_id = $skyuc->GPC ['cat_id'];
	$target_cat_id = $skyuc->GPC ['target_cat_id'];

	// 影片分类不允许为空
	if ($cat_id == 0 || $target_cat_id == 0) {
		$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'category.php?act=move' );
		sys_msg ( $_LANG ['cat_move_empty'], 0, $link );
	}

	// 更新影片分类
	$sql = 'UPDATE ' . TABLE_PREFIX . 'show' . " SET cat_id = '$target_cat_id' " . " WHERE cat_id = '$cat_id'";
	if ($db->query_write ( $sql )) {
		// 清除缓存
		$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', 'show.dwt' ) );

		// 提示信息
		$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'category.php?act=list' );
		sys_msg ( $_LANG ['move_cat_success'], 0, $link );
	}
}

/*------------------------------------------------------ */
//-- 编辑排序序号
/*------------------------------------------------------ */

if ($skyuc->GPC ['act'] == 'edit_sort_order') {
	check_authz_json ( 'cat_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	$id = $skyuc->GPC ['id'];
	$val = $skyuc->GPC ['val'];

	$sql = 'UPDATE ' . TABLE_PREFIX . 'category' . '  SET sort_order =' . $val . ' WHERE cat_id=' . $id;
	if ($skyuc->db->query_write ( $sql ) != false) {

		make_json_result ( $val );
	} else {
		make_json_error ( $db->error () );
	}
}

/*------------------------------------------------------ */
//-- 切换是否显示在导航栏
/*------------------------------------------------------ */

if ($skyuc->GPC ['act'] == 'toggle_show_in_nav') {
	check_authz_json ( 'cat_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_BOOL ) );

	$id = $skyuc->GPC ['id'];
	$val = $skyuc->GPC ['val'];

	$sql = 'UPDATE ' . TABLE_PREFIX . 'category' . ' SET show_in_nav =' . $val . ' WHERE cat_id=' . $id;
	if ($skyuc->db->query_write ( $sql ) !== false) {
		if ($val) {
			//显示
			$nav = $db->query_first ( 'SELECT max(vieworder) AS max FROM ' . TABLE_PREFIX . 'nav' . " WHERE type = 'middle'" );
			$vieworder = $nav ['max'] + 2;
			$cate = $db->query_first ( "SELECT cat_name FROM " . TABLE_PREFIX . 'category' . " WHERE cat_id = '$id'" );
			$catname = $cate ['cat_name'];

			//显示在自定义导航栏中
			$skyuc->options ['rewrite'] = 0;
			$uri = build_uri ( 'category', array ('cid' => $id ) );

			$nid = $db->query_first ( 'SELECT id FROM ' . TABLE_PREFIX . 'nav' . " WHERE ctype = 'c' AND cid = '" . $id . "' AND type = 'middle'" );
			if (empty ( $nid )) {
				//不存在
				$sql = 'INSERT INTO ' . TABLE_PREFIX . 'nav' . ' (name,ctype,cid,ifshow,vieworder,opennew,url,type) ' . " VALUES('" . $db->escape_string ( $catname ) . "', 	'c', 	'$id',	'1',	'$vieworder',		'0', '" . $db->escape_string ( $uri ) . "',	'middle')";
				$db->query_write ( $sql );
			} else {
				$sql = 'UPDATE ' . TABLE_PREFIX . 'nav' . " SET ifshow = 1 WHERE ctype = 'c' AND cid = '" . $id . "' AND type = 'middle'";
				$db->query_write ( $sql );
			}

		} else {
			//去除
			$db->query_write ( 'UPDATE ' . TABLE_PREFIX . 'nav' . " SET ifshow = 0 WHERE ctype = 'c' AND cid = '" . $id . "' AND type = 'middle'" );
		}

		build_category (); // 重建缓存
		make_json_result ( $val );
	} else {
		make_json_error ( $db->error () );
	}
}
/*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */

if ($skyuc->GPC ['act'] == 'toggle_is_show') {
	check_authz_json ( 'cat_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	$id = $skyuc->GPC ['id'];
	$val = $skyuc->GPC ['val'];

	$sql = 'update ' . TABLE_PREFIX . 'category' . ' SET is_show =' . $val . ' WHERE cat_id=' . $id;
	if ($skyuc->db->query_write ( $sql ) != false) {
		build_category (); // 重建缓存
		make_json_result ( $val );
	} else {
		make_json_error ( $db->error () );
	}
} /*------------------------------------------------------ */
//-- 添加影片时AJAX添加分类
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'add_category') {
	check_authz_json ( 'cat_manage' );

	$skyuc->input->clean_array_gpc ( 'r', array ('parent_id' => TYPE_UINT, 'cat' => TYPE_STR )

	 );
	$parent_id = $skyuc->GPC ['parent_id'];
	$category = $skyuc->GPC ['cat'];

	if (cat_exists ( $category, $parent_id )) {
		make_json_error ( $_LANG ['catname_exist'] );
	} else {
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'category' . '(cat_name, parent_id, is_show)' . "VALUES ( '" . $db->escape_string ( $category ) . "', '$parent_id', 1)";

		$db->query_write ( $sql );
		$category_id = $db->insert_id ();

		$arr = array ('parent_id' => $parent_id, 'id' => $category_id, 'cat' => $category );

		build_category (); // 重建缓存


		make_json_result ( $arr );
	}
}

/*------------------------------------------------------ */
//-- 删除影片分类
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'remove') {
	check_authz_json ( 'cat_manage' );

	// 初始化分类ID并取得分类名称
	$cat_id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	$cate = $db->query_first ( 'SELECT cat_name FROM ' . TABLE_PREFIX . 'category' . " WHERE cat_id='$cat_id'" );
	$cat_name = $cate ['cat_name'];

	// 当前分类下是否有子分类
	$cat_count = $db->query_first ( 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'category' . " WHERE parent_id='$cat_id'" );

	// 当前分类下是否存在影片
	$show_count = $db->query_first ( 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'show' . " WHERE cat_id='$cat_id'" );

	// 如果不存在下级子分类和影片，则删除之
	if ($cat_count ['total'] == 0 && $show_count ['total'] == 0) {
		// 删除分类
		$sql = 'DELETE FROM ' . TABLE_PREFIX . 'category' . " WHERE cat_id = '$cat_id'";
		if ($db->query_write ( $sql )) {
			$db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'nav' . " WHERE ctype = 'c' AND cid = '" . $cat_id . "' AND type = 'middle'" );

			build_category (); // 重建缓存


			admin_log ( $cat_name, 'remove', 'category' );
		}
	} else {
		make_json_error ( $cat_name . ' ' . $_LANG ['cat_isleaf'] );
	}

	$url = 'category.php?act=query&' . str_replace ( 'act=remove', '', $_SERVER ['QUERY_STRING'] );

	header ( "Location: $url\n" );
	exit ();
}

?>
