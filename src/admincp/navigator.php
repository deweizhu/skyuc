<?php

/**
 * SKYUC! 管理中心自定义导航栏程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

$exc = new exchange ( TABLE_PREFIX . 'nav', $skyuc->db, 'id', 'name' );

/*------------------------------------------------------ */
//-- 自定义导航栏列表
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list') {
	$smarty->assign ( 'ur_here', $_LANG ['navigator'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['add_new'], 'href' => 'navigator.php?act=add' ) );
	$smarty->assign ( 'full_page', 1 );

	$navdb = get_nav ();

	$smarty->assign ( 'navdb', $navdb ['navdb'] );
	$smarty->assign ( 'filter', $navdb ['filter'] );
	$smarty->assign ( 'record_count', $navdb ['record_count'] );
	$smarty->assign ( 'page_count', $navdb ['page_count'] );

	assign_query_info ();
	$smarty->display ( 'navigator.tpl' );
} /*------------------------------------------------------ */
//-- 自定义导航栏列表Ajax
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$navdb = get_nav ();
	$smarty->assign ( 'navdb', $navdb ['navdb'] );
	$smarty->assign ( 'filter', $navdb ['filter'] );
	$smarty->assign ( 'record_count', $navdb ['record_count'] );
	$smarty->assign ( 'page_count', $navdb ['page_count'] );

	$sort_flag = sort_flag ( $navdb ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	make_json_result ( $smarty->fetch ( 'navigator.tpl' ), '', array ('filter' => $navdb ['filter'], 'page_count' => $navdb ['page_count'] ) );
} /*------------------------------------------------------ */
//-- 自定义导航栏增加
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'add') {
	admin_priv ( 'navigator' );

	$skyuc->input->clean_array_gpc ( 'r', array ('step' => TYPE_UINT, 'item_name' => TYPE_STR, 'item_url' => TYPE_STR, 'item_ifshow' => TYPE_UINT, 'item_opennew' => TYPE_UINT, 'item_type' => TYPE_STR, 'item_vieworder' => TYPE_UINT ) );

	if (empty ( $skyuc->GPC ['step'] )) {
		$rt = array ('act' => 'add' );

		$sysmain = get_sysnav ();

		$smarty->assign ( 'action_link', array ('text' => $_LANG ['go_list'], 'href' => 'navigator.php?act=list' ) );
		$smarty->assign ( 'ur_here', $_LANG ['navigator'] );
		assign_query_info ();
		$smarty->assign ( 'sysmain', $sysmain );
		$smarty->assign ( 'rt', $rt );
		$smarty->display ( 'navigator_add.tpl' );
	} elseif ($skyuc->GPC ['step'] == 2) {
		$item_name = $skyuc->GPC ['item_name'];
		$item_url = $skyuc->GPC ['item_url'];
		$item_ifshow = $skyuc->GPC ['item_ifshow'];
		$item_opennew = $skyuc->GPC ['item_opennew'];
		$item_type = $skyuc->GPC ['item_type'];

		$vieworder = $db->query_first_slave ( "SELECT max(vieworder) AS total FROM " . TABLE_PREFIX . 'nav' . " WHERE type = '" . $item_type . "'" );

		$item_vieworder = iif ( $skyuc->GPC ['item_vieworder'] == 0, $vieworder ['total'] + 2, 0 );

		if ($item_ifshow == 1 && $item_type == 'middle') {
			//如果设置为在中部显示


			$arr = analyse_uri ( $item_url ); //分析URI
			if ($arr) {
				//如果为分类
				set_show_in_nav ( $arr ['type'], $arr ['id'], 1 ); //设置显示
				$sql = "INSERT INTO " . TABLE_PREFIX . 'nav' . ' (name,ctype,cid,ifshow,vieworder,opennew,url,type) VALUES ' . " ('" . $db->escape_string ( $item_name ) . "','" . $db->escape_string ( $arr ['type'] ) . "','" . $arr ['id'] . "','$item_ifshow','$item_vieworder','$item_opennew','" . $db->escape_string ( $item_url ) . "','" . $db->escape_string ( $item_type ) . "')";
			}
		}

		if (empty ( $sql )) {
			$sql = "INSERT INTO " . TABLE_PREFIX . 'nav' . ' (name,ifshow,vieworder,opennew,url,type) VALUES ' . " ('" . $db->escape_string ( $item_name ) . "','$item_ifshow','$item_vieworder','$item_opennew','" . $db->escape_string ( $item_url ) . "','" . $db->escape_string ( $item_type ) . "')";
		}
		$db->query_write ( $sql );
		$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', 'show.dwt' ) );
		$links [] = array ('text' => $_LANG ['navigator'], 'href' => 'navigator.php?act=list' );
		$links [] = array ('text' => $_LANG ['add_new'], 'href' => 'navigator.php?act=add' );
		sys_msg ( $_LANG ['edit_ok'], 0, $links );
	}
} /*------------------------------------------------------ */
//-- 自定义导航栏编辑
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit') {
	admin_priv ( 'navigator' );

	$skyuc->input->clean_array_gpc ( 'r', array ('id' => TYPE_UINT, 'step' => TYPE_UINT, 'item_name' => TYPE_STR, 'item_url' => TYPE_STR, 'item_ifshow' => TYPE_UINT, 'item_opennew' => TYPE_UINT, 'item_type' => TYPE_STR, 'item_vieworder' => TYPE_UINT ) );
	$id = $skyuc->GPC ['id'];
	if (empty ( $skyuc->GPC ['step'] )) {
		$rt = array ('act' => 'edit', 'id' => $id );
		$row = $db->query_first_slave ( 'SELECT * FROM ' . TABLE_PREFIX . 'nav' . " WHERE id='$id'" );
		$rt ['item_name'] = $row ['name'];
		$rt ['item_url'] = $row ['url'];
		$rt ['item_vieworder'] = $row ['vieworder'];
		$rt ['item_ifshow_' . $row ['ifshow']] = 'selected';
		$rt ['item_opennew_' . $row ['opennew']] = 'selected';
		$rt ['item_type_' . $row ['type']] = 'selected';

		$sysmain = get_sysnav ();

		$smarty->assign ( 'action_link', array ('text' => $_LANG ['go_list'], 'href' => 'navigator.php?act=list' ) );
		$smarty->assign ( 'ur_here', $_LANG ['navigator'] );
		assign_query_info ();
		$smarty->assign ( 'sysmain', $sysmain );
		$smarty->assign ( 'rt', $rt );
		$smarty->display ( 'navigator_add.tpl' );
	} elseif ($skyuc->GPC ['step'] == 2) {
		$item_name = $skyuc->GPC ['item_name'];
		$item_url = $skyuc->GPC ['item_url'];
		$item_ifshow = $skyuc->GPC ['item_ifshow'];
		$item_opennew = $skyuc->GPC ['item_opennew'];
		$item_type = $skyuc->GPC ['item_type'];
		$item_vieworder = $skyuc->GPC ['item_vieworder'];

		$row = $db->query_first_slave ( 'SELECT ctype,cid,ifshow,type FROM ' . TABLE_PREFIX . 'nav' . " WHERE id = '$id'" );
		$arr = analyse_uri ( $item_url );

		if ($arr) {
			//目标为分类
			if ($row ['ctype'] == $arr ['type'] && $row ['cid'] == $arr ['id']) {
				//没有修改分类
				if ($item_type != 'middle') {
					//位置不在中部
					set_show_in_nav ( $arr ['type'], $arr ['id'], 0 );
				}
			} else {
				//修改了分类
				if ($row ['ifshow'] == 1 && $row ['type'] == 'middle') {
					//原来在中部显示
					set_show_in_nav ( $row ['ctype'], $row ['cid'], 0 ); //设置成不显示
				} elseif ($row ['ifshow'] == 0 && $row ['type'] == 'middle') {
					//原来不显示
				}
			}

			//分类判断
			if ($item_ifshow != is_show_in_nav ( $arr ['type'], $arr ['id'] ) && $item_type == 'middle') {
				set_show_in_nav ( $arr ['type'], $arr ['id'], $item_ifshow );
			}
			$sql = 'UPDATE ' . TABLE_PREFIX . 'nav' . " SET name='" . $db->escape_string ( $item_name ) . "',ctype='" . $db->escape_string ( $arr ['type'] ) . "',cid='" . $arr ['id'] . "',ifshow='$item_ifshow',vieworder='$item_vieworder',opennew='$item_opennew',url='$item_url',type='" . $db->escape_string ( $item_type ) . "' WHERE id='$id'";
		} else {
			//目标不是分类
			if ($row ['ctype'] && $row ['cid']) {
				//原来是分类
				set_show_in_nav ( $row ['ctype'], $row ['cid'], 0 );
			}

			$sql = 'UPDATE ' . TABLE_PREFIX . 'nav' . " SET name='" . $db->escape_string ( $item_name ) . "',ctype='',cid='',ifshow='$item_ifshow',vieworder='$item_vieworder',opennew='$item_opennew',url='" . $db->escape_string ( $item_url ) . "',type='" . $db->escape_string ( $item_type ) . "' WHERE id='$id'";
		}

		$db->query_write ( $sql );
		$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', 'show.dwt' ) );
		$links [] = array ('text' => $_LANG ['navigator'], 'href' => 'navigator.php?act=list' );
		sys_msg ( $_LANG ['edit_ok'], 0, $links );
	}
} /*------------------------------------------------------ */
//-- 自定义导航栏删除
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'del') {
	admin_priv ( 'navigator' );
	$id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );
	$row = $db->query_first_slave ( 'SELECT ctype,cid,type FROM ' . TABLE_PREFIX . 'nav' . " WHERE id = '$id'" );

	if ($row ['type'] == 'middle' && $row ['ctype'] && $row ['cid']) {
		set_show_in_nav ( $row ['ctype'], $row ['cid'], 0 );
	}

	$sql = 'DELETE FROM ' . TABLE_PREFIX . 'nav' . " WHERE id='$id'";
	$db->query ( $sql );
	$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', 'show.dwt' ) );
	header ( "Location: navigator.php?act=list\n" );
	exit ();
}

/*------------------------------------------------------ */
//-- 编辑排序
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_sort_order') {
	check_authz_json ( 'nav' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	$id = $skyuc->GPC ['id'];
	$val = $skyuc->GPC ['val'];

	// 检查输入的值是否合法
	if (! preg_match ( "/^[0-9]+$/", $val )) {
		make_json_error ( sprintf ( $_LANG ['enter_int'], $val ) );
	} else {
		if ($exc->edit ( "vieworder = '$val'", $id )) {
			$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', 'show.dwt' ) );
			make_json_result ( $val );
		} else {
			make_json_error ( $db->error () );
		}
	}
}

/*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */

if ($skyuc->GPC ['act'] == 'toggle_ifshow') {
	check_authz_json ( 'nav' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	$id = $skyuc->GPC ['id'];
	$val = $skyuc->GPC ['val'];

	$row = $db->query_first_slave ( 'SELECT type,ctype,cid FROM ' . TABLE_PREFIX . 'nav' . " WHERE id = '$id'" );

	if ($row ['type'] == 'middle' && $row ['ctype'] && $row ['cid']) {
		set_show_in_nav ( $row ['ctype'], $row ['cid'], $val );
	}

	$result = $db->query_write ( 'UPDATE ' . TABLE_PREFIX . 'nav' . " SET ifshow='" . $val . "' WHERE id='$id'" );
	if ($result != false) {
		$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', 'show.dwt' ) );
		make_json_result ( $val );
	} else {
		make_json_error ( $db->error () );
	}
}

/*------------------------------------------------------ */
//-- 切换是否新窗口
/*------------------------------------------------------ */

if ($skyuc->GPC ['act'] == 'toggle_opennew') {
	check_authz_json ( 'nav' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	$id = $skyuc->GPC ['id'];
	$val = $skyuc->GPC ['val'];

	$result = $db->query_write ( 'UPDATE ' . TABLE_PREFIX . 'nav' . " SET opennew='" . $val . "' WHERE id='$id'" );
	if ($result != false) {
		$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', 'show.dwt' ) );
		make_json_result ( $val );
	} else {
		make_json_error ( $db->error () );
	}
}

?>
