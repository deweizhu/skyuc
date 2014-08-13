<?php
/**
 * SKYUC! 友情链接管理
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

$exc = new exchange ( TABLE_PREFIX . 'friend_link', $skyuc->db, 'link_id', 'link_name' );

// act操作项的初始化
if (empty ( $skyuc->GPC ['act'] )) {
	$skyuc->GPC ['act'] = 'list';
}

/*------------------------------------------------------ */
//-- 友情链接列表页面
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list') {
	/* 模板赋值 */
	$smarty->assign ( 'ur_here', $_LANG ['list_link'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['add_link'], 'href' => 'friend_link.php?act=add' ) );
	$smarty->assign ( 'full_page', 1 );

	/* 获取友情链接数据 */
	$links_list = get_links_list ();

	$smarty->assign ( 'links_list', $links_list ['list'] );
	$smarty->assign ( 'filter', $links_list ['filter'] );
	$smarty->assign ( 'record_count', $links_list ['record_count'] );
	$smarty->assign ( 'page_count', $links_list ['page_count'] );

	$sort_flag = sort_flag ( $links_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	assign_query_info ();
	$smarty->display ( 'link_list.tpl' );
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	/* 获取友情链接数据 */
	$links_list = get_links_list ();

	$smarty->assign ( 'links_list', $links_list ['list'] );
	$smarty->assign ( 'filter', $links_list ['filter'] );
	$smarty->assign ( 'record_count', $links_list ['record_count'] );
	$smarty->assign ( 'page_count', $links_list ['page_count'] );

	$sort_flag = sort_flag ( $links_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	make_json_result ( $smarty->fetch ( 'link_list.tpl' ), '', array ('filter' => $links_list ['filter'], 'page_count' => $links_list ['page_count'] ) );
}

/*------------------------------------------------------ */
//-- 添加新链接页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'add') {
	admin_priv ( 'friendlink' );

	$smarty->assign ( 'ur_here', $_LANG ['add_link'] );
	$smarty->assign ( 'action_link', array ('href' => 'friend_link.php?act=list', 'text' => $_LANG ['list_link'] ) );
	$smarty->assign ( 'action', 'add' );
	$smarty->assign ( 'form_act', 'insert' );

	assign_query_info ();
	$smarty->display ( 'link_info.tpl' );
}

/*------------------------------------------------------ */
//-- 处理添加的链接
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'insert') {
	//变量初始化
	$skyuc->input->clean_array_gpc ( 'p', array ('show_order' => TYPE_UINT, 'link_name' => TYPE_STR, 'url_logo' => TYPE_STR, 'link_url' => TYPE_STR ) );

	$show_order = $skyuc->GPC ['show_order'];
	$link_name = iif ( ! empty ( $skyuc->GPC ['link_name'] ), sub_str ( $skyuc->GPC ['link_name'], 250, false ), '' );

	// 查看链接名称是否有重复
	if ($exc->num ( 'link_name', $link_name ) == 0) {
		// 处理上传的LOGO图片
		if ((isset ( $_FILES ['link_img'] ['error'] ) && $_FILES ['link_img'] ['error'] == 0) || (! isset ( $_FILES ['link_img'] ['error'] ) && $_FILES ['link_img'] ['tmp_name'] != 'none')) {
			$img_up_info = @basename ( upload_link_img ( $_FILES ['link_img'] ) );
			$link_logo = $skyuc->config ['Misc'] ['imagedir'] . '/afficheimg/' . $img_up_info;
		}

		// 使用远程的LOGO图片
		if (! empty ( $skyuc->GPC ['url_logo'] )) {
			if (strpos ( $skyuc->GPC ['url_logo'], 'http://' ) === false && strpos ( $skyuc->GPC ['url_logo'], 'https://' ) === false) {
				$link_logo = 'http://' . $skyuc->GPC ['url_logo'];
			} else {
				$link_logo = $skyuc->GPC ['url_logo'];
			}
		}

		// 如果链接LOGO为空, LOGO为链接的名称
		if (((isset ( $_FILES ['upfile_flash'] ['error'] ) && $_FILES ['upfile_flash'] ['error'] > 0) || (! isset ( $_FILES ['upfile_flash'] ['error'] ) && $_FILES ['upfile_flash'] ['tmp_name'] == 'none')) && empty ( $skyuc->GPC ['url_logo'] )) {
			$link_logo = '';
		}

		// 如果友情链接的链接地址没有http://，补上
		if (strpos ( $skyuc->GPC ['link_url'], 'http://' ) === false && strpos ( $skyuc->GPC ['link_url'], 'https://' ) === false) {
			$link_url = 'http://' . $skyuc->GPC ['link_url'];
		} else {
			$link_url = $skyuc->GPC ['link_url'];
		}

		// 插入数据
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'friend_link  (link_name, link_url, link_logo, show_order) ' . "VALUES ('" . $db->escape_string ( $link_name ) . "', '" . $db->escape_string ( $link_url ) . "', '" . $db->escape_string ( $link_logo ) . "', '$show_order')";
		$db->query_write ( $sql );

		// 记录管理员操作
		admin_log ( $skyuc->GPC ['link_name'], 'add', 'friendlink' );

		// 清除缓存
		$skyuc->secache->setModified ( 'index.dwt' );

		// 提示信息
		$link [0] ['text'] = $_LANG ['continue_add'];
		$link [0] ['href'] = 'friend_link.php?act=add';

		$link [1] ['text'] = $_LANG ['back_list'];
		$link [1] ['href'] = 'friend_link.php?act=list';

		sys_msg ( $_LANG ['add'] . '&nbsp;' . $skyuc->GPC ['link_name'] . ' ' . $_LANG ['attradd_succed'], 0, $link );

	} else {
		$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
		sys_msg ( $_LANG ['link_name_exist'], 0, $link );
	}
}

/*------------------------------------------------------ */
//-- 友情链接编辑页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit') {
	admin_priv ( 'friendlink' );

	$skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	// 取得友情链接数据
	$sql = 'SELECT link_id, link_name, link_url, link_logo, show_order ' . 'FROM ' . TABLE_PREFIX . 'friend_link' . ' WHERE link_id = ' . $skyuc->GPC ['id'];
	$link_arr = $db->query_first_slave ( $sql );

	// 标记为图片链接还是文字链接
	if (! empty ( $link_arr ['link_logo'] )) {
		$type = 'img';
		$link_logo = $link_arr ['link_logo'];
	} else {
		$type = 'chara';
		$link_logo = '';
	}

	$link_arr ['link_name'] = sub_str ( $link_arr ['link_name'], 250 ); // 截取字符串为250个字符避免出现非法字符的情况


	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['edit_link'] );
	$smarty->assign ( 'action_link', array ('href' => 'friend_link.php?act=list', 'text' => $_LANG ['list_link'] ) );
	$smarty->assign ( 'form_act', 'update' );
	$smarty->assign ( 'action', 'edit' );

	$smarty->assign ( 'type', $type );
	$smarty->assign ( 'link_logo', $link_logo );
	$smarty->assign ( 'link_arr', $link_arr );

	assign_query_info ();
	$smarty->display ( 'link_info.tpl' );
}

/*------------------------------------------------------ */
//-- 编辑链接的处理页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'update') {
	// 变量初始化
	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'show_order' => TYPE_UINT, 'link_name' => TYPE_STR, 'url_logo' => TYPE_STR, 'link_url' => TYPE_STR ) );

	$id = $skyuc->GPC ['id'];
	$show_order = $skyuc->GPC ['show_order'];
	$link_name = $skyuc->GPC ['link_name'];

	// 如果有图片LOGO要上传
	if ((isset ( $_FILES ['link_img'] ['error'] ) && $_FILES ['link_img'] ['error'] == 0) || (! isset ( $_FILES ['link_img'] ['error'] ) && $_FILES ['link_img'] ['tmp_name'] != 'none')) {
		$img_up_info = @basename ( upload_link_img ( $_FILES ['link_img'] ) );
		$link_logo = ", link_logo = " . '\'' . $skyuc->config ['Misc'] ['imagedir'] . '/afficheimg/' . $img_up_info . '\'';
	} elseif (! empty ( $skyuc->GPC ['url_logo'] )) {
		$link_logo = ", link_logo = '" . $db->escape_string ( $skyuc->GPC ['url_logo'] ) . "'";
	} else {
		// 如果是文字链接, LOGO为链接的名称
		$link_logo = ", link_logo = ''";
	}

	//如果要修改链接图片, 删除原来的图片
	if (! empty ( $img_up_info )) {
		//获取链子LOGO,并删除
		$old_logo = $db->query_first_slave ( 'SELECT link_logo FROM ' . TABLE_PREFIX . 'friend_link' . ' WHERE link_id = ' . $id );

		if ((strpos ( $old_logo ['link_logo'], 'http://' ) === false) && (strpos ( $old_logo ['link_logo'], 'https://' ) === false)) {
			$img_name = basename ( $old_logo ['link_logo'] );
			@unlink ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/afficheimg/' . $img_name );
		}
	}

	// 如果友情链接的链接地址没有http://，补上
	if (strpos ( $skyuc->GPC ['link_url'], 'http://' ) === false && strpos ( $skyuc->GPC ['link_url'], 'https://' ) === false) {
		$link_url = 'http://' . $skyuc->GPC ['link_url'];
	} else {
		$link_url = $skyuc->GPC ['link_url'];
	}

	// 更新信息
	$sql = "UPDATE " . TABLE_PREFIX . 'friend_link' . " SET " . " link_name = '" . $db->escape_string ( $link_name ) . "', " . " link_url = '" . $db->escape_string ( $link_url ) . "' " . $link_logo . ',' . " show_order = $show_order" . " WHERE link_id = '$id'";

	$db->query ( $sql );
	// 记录管理员操作
	admin_log ( $link_name, 'edit', 'friendlink' );

	// 清除缓存
	$skyuc->secache->setModified ( 'index.dwt' );

	// 提示信息
	$link [0] ['text'] = $_LANG ['back_list'];
	$link [0] ['href'] = 'friend_link.php?act=list';

	sys_msg ( $_LANG ['edit'] . "&nbsp;" . $link_name . "&nbsp;" . $_LANG ['attradd_succed'], 0, $link );
}

/*------------------------------------------------------ */
//-- 编辑链接名称
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_link_name') {
	check_authz_json ( 'friendlink' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$id = $skyuc->GPC ['id'];
	$link_name = $skyuc->GPC ['val'];

	//检查链接名称是否重复
	if ($exc->num ( "link_name", $link_name, $id ) != 0) {
		make_json_error ( sprintf ( $_LANG ['link_name_exist'], $link_name ) );
	} else {
		if ($exc->edit ( "link_name = '" . $db->escape_string ( $link_name ) . "'", $id )) {
			admin_log ( $link_name, 'edit', 'friendlink' );
			$skyuc->secache->setModified ( 'index.dwt' );
			make_json_result ( $link_name );
		} else {
			make_json_error ( $db->error () );
		}
	}
}

/*------------------------------------------------------ */
//-- 删除友情链接
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'remove') {
	check_authz_json ( 'friendlink' );

	$id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	// 获取链子LOGO,并删除
	$link_logo = $exc->get_name ( $id, 'link_logo' );

	if ((strpos ( $link_logo, 'http://' ) === false) && (strpos ( $link_logo, 'https://' ) === false)) {
		$img_name = basename ( $link_logo );
		@unlink ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/afficheimg/' . $img_name );
	}

	$exc->drop ( $id );
	$skyuc->secache->setModified ( 'index.dwt' );
	admin_log ( '', 'remove', 'friendlink' );

	$url = 'friend_link.php?act=query&' . str_replace ( 'act=remove', '', $_SERVER ['QUERY_STRING'] );

	header ( "Location: $url\n" );
	exit ();
}

/*------------------------------------------------------ */
//-- 编辑排序
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_show_order') {
	check_authz_json ( 'friendlink' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$id = $skyuc->GPC ['id'];
	$order = $skyuc->GPC ['val'];

	// 检查输入的值是否合法
	if (! preg_match ( "/^[0-9]+$/", $order )) {
		make_json_error ( sprintf ( $_LANG ['enter_int'], $order ) );
	} else {
		if ($exc->edit ( "show_order = '$order'", $id )) {
			$skyuc->secache->setModified ( 'index.dwt' );
			make_json_result ( stripslashes ( $order ) );
		}
	}
}

/* 获取友情链接数据列表 */
function get_links_list() {
	global $skyuc;

	$skyuc->input->clean_array_gpc ( 'r', array ('sort_by' => TYPE_STR, 'sort_order' => TYPE_STR ) );

	$filter = array ();
	$filter ['sort_by'] = iif ( empty ( $skyuc->GPC ['sort_by'] ), 'link_id', $skyuc->GPC ['sort_by'] );
	$filter ['sort_order'] = iif ( empty ( $skyuc->GPC ['sort_order'] ), 'DESC', $skyuc->GPC ['sort_order'] );

	// 获得总记录数据
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'friend_link';
	$total = $skyuc->db->query_first ( $sql );
	$filter ['record_count'] = $total ['total'];

	$filter = page_and_size ( $filter );

	// 获取数据
	$sql = 'SELECT link_id, link_name, link_url, link_logo, show_order ' . ' FROM ' . TABLE_PREFIX . 'friend_link' . ' ORDER by ' . $filter ['sort_by'] . ' ' . $filter ['sort_order'];
	$sql = $skyuc->db->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
	$res = $skyuc->db->query_read_slave ( $sql );

	$list = array ();
	while ( $rows = $skyuc->db->fetch_array ( $res ) ) {
		if (empty ( $rows ['link_logo'] )) {
			$rows ['link_logo'] = '';
		} else {
			if ((strpos ( $rows ['link_logo'], 'http://' ) === false) && (strpos ( $rows ['link_logo'], 'https://' ) === false)) {
				$rows ['link_logo'] = "<img src='" . '../' . $rows ['link_logo'] . "' width=88 height=31 />";
			} else {
				$rows ['link_logo'] = "<img src='" . $rows ['link_logo'] . "' width=88 height=31 />";
			}
		}

		$list [] = $rows;
	}

	return array ('list' => $list, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
}

// 上传友情链接LOGO
function upload_link_img($upload) {
	global $skyuc;

	$filename = random ( 9, 1 ) . substr ( $upload ['name'], strpos ( $upload ['name'], '.' ) );
	$path = DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/afficheimg/' . $filename;

	if (move_upload_file ( $upload ['tmp_name'], $path )) {
		return $skyuc->config ['Misc'] ['imagedir'] . '/afficheimg/' . $filename;
	} else {
		return false;
	}
}
?>