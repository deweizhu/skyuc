<?php
/**
 * SKYUC! 管理中心FLASH轮播图片管理程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');
$uri = get_url ();
$allow_suffix = array ('gif', 'jpg', 'png', 'jpeg', 'bmp' );

/*------------------------------------------------------ */
//-- 轮播图片列表页面
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list') {
	$playerdb = get_flash_xml ();
	foreach ( $playerdb as $key => $val ) {
		if (strpos ( $val ['src'], 'http' ) === false) {
			$playerdb [$key] ['src'] = $uri . $val ['src'];
		}
	}
	assign_query_info ();
	$flash_dir = DIR . '/data/flashdata/';
	$smarty->assign ( 'uri', $uri );
	$smarty->assign ( 'ur_here', $_LANG ['flashplay'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['add_new'], 'href' => 'flashplay.php?act=add' ) );
	$smarty->assign ( 'flashtpls', get_flash_templates ( $flash_dir ) );
	$smarty->assign ( 'current_flashtpl', $skyuc->options ['flash_theme'] );
	$smarty->assign ( 'playerdb', $playerdb );
	$smarty->display ( 'flashplay_list.tpl' );
} /*------------------------------------------------------ */
//-- 删除轮播图片
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'del') {
	admin_priv ( 'flash_manage' );

	$id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );
	$flashdb = get_flash_xml ();
	if (isset ( $flashdb [$id] )) {
		$rt = $flashdb [$id];
	} else {
		$links [] = array ('text' => $_LANG ['go_url'], 'href' => 'flashplay.php?act=list' );
		sys_msg ( $_LANG ['id_error'], 0, $links );
	}

	if (strpos ( $rt ['src'], 'http' ) === false) {
		@unlink ( DIR . '/' . $rt ['src'] );
	}
	$temp = array ();
	foreach ( $flashdb as $key => $val ) {
		if ($key != $id) {
			$temp [] = $val;
		}
	}
	put_flash_xml ( $temp );
	set_flash_data ( $skyuc->options ['flash_theme'], $error_msg = '' );
	header ( "Location: flashplay.php?act=list\n" );
	exit ();
} /*------------------------------------------------------ */
//-- 添加轮播图片
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'add') {
	admin_priv ( 'flash_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('step' => TYPE_STR, 'ad_name' => TYPE_STR, 'ad_type' => TYPE_UINT, 'img_src' => TYPE_STR, 'img_url' => TYPE_STR, 'content' => TYPE_STR, 'ad_sort' => TYPE_UINT ) );

	$skyuc->input->clean_array_gpc ( 'g', array ('url' => TYPE_STR, 'src' => TYPE_STR ) );

	if (empty ( $skyuc->GPC ['step'] )) {
		$url = iif ( $skyuc->GPC_exists ['url'], $skyuc->GPC ['url'], 'http://' );
		$src = iif ( $skyuc->GPC_exists ['src'], $skyuc->GPC ['src'], '' );

		$rt = array ('act' => 'add', 'img_url' => $url, 'img_src' => $src );
		$width_height = get_width_height ();
		$smarty->assign ( 'width_height', sprintf ( $_LANG ['width_height'], $width_height ['width'], $width_height ['height'] ) );
		$smarty->assign ( 'action_link', array ('text' => $_LANG ['go_url'], 'href' => 'flashplay.php?act=list' ) );
		$smarty->assign ( 'rt', $rt );
		$smarty->assign ( 'current_flashtpl', $skyuc->options ['flash_theme'] );
		$smarty->display ( 'flashplay_add.tpl' );
	} elseif ($skyuc->GPC ['step'] == 2) {
		if (! empty ( $_FILES ['img_file_src'] ['name'] )) {
			if (! get_file_suffix ( $_FILES ['img_file_src'] ['name'], $allow_suffix )) {
				sys_msg ( $_LANG ['invalid_type'] );
			}
			$name = skyuc_date ( 'Ymd' );
			for($i = 0; $i < 6; $i ++) {
				$name .= chr ( mt_rand ( 97, 122 ) );
			}
			$name .= '.' . end ( explode ( '.', $_FILES ['img_file_src'] ['name'] ) );
			$target = DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/afficheimg/' . $name;
			if (move_upload_file ( $_FILES ['img_file_src'] ['tmp_name'], $target )) {
				$src = $skyuc->config ['Misc'] ['imagedir'] . '/afficheimg/' . $name;
			}
		} elseif (! empty ( $skyuc->GPC ['img_src'] )) {
			$src = $skyuc->GPC ['img_src'];
			if (strstr ( $src, 'http' ) && ! strstr ( $src, $_SERVER ['SERVER_NAME'] )) {
				$src = get_url_image ( $src );
			}
		} elseif ($skyuc->GPC ['ad_type'] == 1) {
			$links [] = array ('text' => $_LANG ['add_new'], 'href' => 'flashplay.php?act=add' );
			sys_msg ( $_LANG ['src_empty'], 0, $links );
		}

		if (empty ( $skyuc->GPC ['img_url'] ) and $skyuc->GPC ['ad_type'] == 1) {
			$links [] = array ('text' => $_LANG ['add_new'], 'href' => 'flashplay.php?act=add' );
			sys_msg ( $_LANG ['link_empty'], 0, $links );
		}
		// 获取flash播放器数据
		$flashdb = get_flash_xml ();
		// 插入新数据
		if ($skyuc->options ['flash_theme'] == 'dewei') {
			array_unshift ( $flashdb, array ('title' => $skyuc->GPC ['ad_name'], 'type' => $skyuc->GPC ['ad_type'], 'src' => $src, 'url' => $skyuc->GPC ['img_url'], 'text' => $skyuc->GPC ['content'], 'sort' => $skyuc->GPC ['ad_sort'] ) );
		} else {
			array_unshift ( $flashdb, array ('src' => $src, 'url' => $skyuc->GPC ['img_url'], 'text' => $skyuc->GPC ['content'], 'sort' => $skyuc->GPC ['ad_sort'] ) );
		}

		// 实现排序
		$flashdb_sort = array ();
		$_flashdb = array ();
		foreach ( $flashdb as $key => $value ) {
			$flashdb_sort [$key] = $value ['sort'];
		}
		asort ( $flashdb_sort, SORT_NUMERIC );
		foreach ( $flashdb_sort as $key => $value ) {
			$_flashdb [] = $flashdb [$key];
		}
		unset ( $flashdb, $flashdb_sort );

		put_flash_xml ( $_flashdb );
		set_flash_data ( $skyuc->options ['flash_theme'], $error_msg = '' );
		$links [] = array ('text' => $_LANG ['go_url'], 'href' => 'flashplay.php?act=list' );
		sys_msg ( $_LANG ['edit_ok'], 0, $links );
	}
} /*------------------------------------------------------ */
//-- 修改轮播图片
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit') {
	admin_priv ( 'flash_manage' );

	$skyuc->input->clean_array_gpc ( 'r', array ('id' => TYPE_UINT, 'step' => TYPE_UINT, 'ad_name' => TYPE_STR, 'ad_type' => TYPE_UINT, 'img_src' => TYPE_STR, 'img_url' => TYPE_STR, 'content' => TYPE_STR, 'ad_sort' => TYPE_UINT ) );

	$id = $skyuc->GPC ['id']; //取得id
	$flashdb = get_flash_xml (); //取得数据
	if (isset ( $flashdb [$id] )) {
		$rt = $flashdb [$id];
	} else {
		$links [] = array ('text' => $_LANG ['go_url'], 'href' => 'flashplay.php?act=list' );
		sys_msg ( $_LANG ['id_error'], 0, $links );
	}
	if (empty ( $skyuc->GPC ['step'] )) {
		$rt ['act'] = 'edit';
		$rt ['img_url'] = $rt ['url'];
		$rt ['img_src'] = $rt ['src'];

		$rt ['id'] = $id;
		$smarty->assign ( 'action_link', array ('text' => $_LANG ['go_url'], 'href' => 'flashplay.php?act=list' ) );
		$width_height = get_width_height ();
		$smarty->assign ( 'width_height', sprintf ( $_LANG ['width_height'], $width_height ['width'], $width_height ['height'] ) );
		$smarty->assign ( 'rt', $rt );
		$smarty->assign ( 'current_flashtpl', $skyuc->options ['flash_theme'] );
		$smarty->display ( 'flashplay_add.tpl' );
	} elseif ($skyuc->GPC ['step'] == 2) {
		if (empty ( $skyuc->GPC ['img_url'] )) {
			//若链接地址为空
			$links [] = array ('text' => $_LANG ['return_edit'], 'href' => 'flashplay.php?act=edit&id=' . $id );
			sys_msg ( $_LANG ['link_empty'], 0, $links );
		}
		if (! empty ( $_FILES ['img_file_src'] ['tmp_name'] )) {
			if (! get_file_suffix ( $_FILES ['img_file_src'] ['name'], $allow_suffix )) {
				sys_msg ( $_LANG ['invalid_type'] );
			}
			//有上传
			$name = skyuc_date ( 'Ymd' );
			for($i = 0; $i < 6; $i ++) {
				$name .= chr ( mt_rand ( 97, 122 ) );
			}
			$name .= '.' . end ( explode ( '.', $_FILES ['img_file_src'] ['name'] ) );
			$target = DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/afficheimg/' . $name;
			if (move_upload_file ( $_FILES ['img_file_src'] ['tmp_name'], $target )) {
				$src = $skyuc->config ['Misc'] ['imagedir'] . '/afficheimg/' . $name;
			}
		} else if (! empty ( $skyuc->GPC ['img_src'] )) {
			$src = $skyuc->GPC ['img_src'];
			if (strstr ( $src, 'http' ) && ! strstr ( $src, $_SERVER ['SERVER_NAME'] )) {
				$src = get_url_image ( $src );
			}
		} elseif ($skyuc->GPC ['ad_type'] == 1) {
			$links [] = array ('text' => $_LANG ['return_edit'], 'href' => 'flashplay.php?act=edit&id=' . $id );
			sys_msg ( $_LANG ['src_empty'], 0, $links );
		}

		if (strpos ( $rt ['src'], 'http' ) === false && $rt ['src'] != $src) {
			@unlink ( DIR . '/' . $rt ['src'] );
		}

		// 修改数据
		if ($skyuc->options ['flash_theme'] == 'dewei') {
			$flashdb [$id] = array ('title' => $skyuc->GPC ['ad_name'], 'type' => $skyuc->GPC ['ad_type'], 'src' => $src, 'url' => $skyuc->GPC ['img_url'], 'text' => $skyuc->GPC ['content'], 'sort' => $skyuc->GPC ['ad_sort'], 'status' => $skyuc->GPC ['ad_status'] );
		} else {
			$flashdb [$id] = array ('src' => $src, 'url' => $skyuc->GPC ['img_url'], 'text' => $skyuc->GPC ['content'], 'sort' => $skyuc->GPC ['ad_sort'], 'status' => $skyuc->GPC ['ad_status'] );
		}
		// 实现排序
		$flashdb_sort = array ();
		$_flashdb = array ();
		foreach ( $flashdb as $key => $value ) {
			$flashdb_sort [$key] = $value ['sort'];
		}
		asort ( $flashdb_sort, SORT_NUMERIC );
		foreach ( $flashdb_sort as $key => $value ) {
			$_flashdb [] = $flashdb [$key];
		}
		unset ( $flashdb, $flashdb_sort );

		put_flash_xml ( $_flashdb );
		set_flash_data ( $skyuc->options ['flash_theme'], $error_msg = '' );
		$links [] = array ('text' => $_LANG ['go_url'], 'href' => 'flashplay.php?act=list' );
		sys_msg ( $_LANG ['edit_ok'], 0, $links );
	}
} /*------------------------------------------------------ */
//-- 安装轮播图片样式
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'install') {
	check_authz_json ( 'flash_manage' );
	$flash_theme = $skyuc->input->clean_gpc ( 'g', 'flashtpl', TYPE_STR );
	if ($skyuc->options ['flash_theme'] != $flash_theme) {
		$sql = "UPDATE " . TABLE_PREFIX . 'setting' . " SET value = '$flash_theme' WHERE code = 'flash_theme'";
		if ($db->query_write ( $sql )) {
			clear_tpl_files ( 'index.dwt' ); //清除模板编译文件
			$skyuc->secache->setModified ( 'index.dwt' );
			build_options ();

			$error_msg = '';
			if (set_flash_data ( $flash_theme, $error_msg )) {
				make_json_error ( $error_msg );
			} else {
				make_json_result ( $flash_theme, $_LANG ['install_success'] );
			}
		} else {
			make_json_error ( $db->error () );
		}
	} else {
		make_json_result ( $flash_theme, $_LANG ['install_success'] );
	}
}

?>