<?php

/**
 * SKYUC! 广告管理程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

$exc = new exchange ( TABLE_PREFIX . 'ad', $skyuc->db, 'ad_id', 'ad_name' );

/*------------------------------------------------------ */
//-- 广告列表页面
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list' || $skyuc->GPC ['act'] == '') {
	$skyuc->input->clean_gpc ( 'r', 'pid', TYPE_UINT );

	$smarty->assign ( 'ur_here', $_LANG ['ad_list'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['ads_add'], 'href' => 'ads.php?act=add' ) );
	$smarty->assign ( 'pid', $skyuc->GPC ['pid'] );
	$smarty->assign ( 'full_page', 1 );

	$ads_list = get_adslist ();

	$smarty->assign ( 'ads_list', $ads_list ['ads'] );
	$smarty->assign ( 'filter', $ads_list ['filter'] );
	$smarty->assign ( 'record_count', $ads_list ['record_count'] );
	$smarty->assign ( 'page_count', $ads_list ['page_count'] );

	$sort_flag = sort_flag ( $ads_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	assign_query_info ();
	$smarty->display ( 'ads_list.tpl' );
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$ads_list = get_adslist ();

	$smarty->assign ( 'ads_list', $ads_list ['ads'] );
	$smarty->assign ( 'filter', $ads_list ['filter'] );
	$smarty->assign ( 'record_count', $ads_list ['record_count'] );
	$smarty->assign ( 'page_count', $ads_list ['page_count'] );

	$sort_flag = sort_flag ( $ads_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	make_json_result ( $smarty->fetch ( 'ads_list.tpl' ), '', array ('filter' => $ads_list ['filter'], 'page_count' => $ads_list ['page_count'] ) );
}

/*------------------------------------------------------ */
//-- 添加新广告页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'add') {
	admin_priv ( 'ad_manage' );

	$end_date = skyuc_date ( $skyuc->options ['date_format'], strtotime ( '+1 month', TIMENOW ), TRUE, FALSE );
	$ads = array ();
	$ads ['start_date'] = skyuc_date ( $skyuc->options ['date_format'], TIMENOW, TRUE, FALSE );

	$smarty->assign ( 'ur_here', $_LANG ['ads_add'] );
	$smarty->assign ( 'action_link', array ('href' => 'ads.php?act=list', 'text' => $_LANG ['ad_list'] ) );
	$smarty->assign ( 'position_list', get_position_list () );

	$smarty->assign ( 'ads', $ads );
	$smarty->assign ( 'end_date', $end_date );
	$smarty->assign ( 'form_act', 'insert' );
	$smarty->assign ( 'action', 'add' );

	assign_query_info ();
	$smarty->display ( 'ads_info.tpl' );
}

/*------------------------------------------------------ */
//-- 新广告的处理
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'insert') {
	admin_priv ( 'ad_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'type' => TYPE_UINT, 'position_id' => TYPE_UINT, 'ad_name' => TYPE_STR, 'ad_link' => TYPE_STR, 'link_man' => TYPE_STR, 'link_email' => TYPE_STR, 'link_phone' => TYPE_STR, 'enabled' => TYPE_BOOL, 'start_dateYear' => TYPE_UINT, 'start_dateMonth' => TYPE_UINT, 'start_dateDay' => TYPE_UINT, 'end_dateYear' => TYPE_UINT, 'end_dateMonth' => TYPE_UINT, 'end_dateDay' => TYPE_UINT, 'media_type' => TYPE_UINT, 'img_url' => TYPE_STR, 'flash_url' => TYPE_STR, 'ad_code' => TYPE_STR, 'ad_text' => TYPE_STR ) );

	//初始化变量
	$id = $skyuc->GPC ['id'];
	$type = $skyuc->GPC ['type'];
	$ad_name = $skyuc->GPC ['ad_name'];

	// 获得广告的开始时期与结束日期
	$start_date = skyuc_mktime ( 0, 0, 0, $skyuc->GPC ['start_dateMonth'], $skyuc->GPC ['start_dateDay'], $skyuc->GPC ['start_dateYear'] );
	$end_date = skyuc_mktime ( 0, 0, 0, $skyuc->GPC ['end_dateMonth'], $skyuc->GPC ['end_dateDay'], $skyuc->GPC ['end_dateYear'] );

	// 查看广告名称是否有重复
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'ad' . " WHERE ad_name = '" . $skyuc->db->escape_string ( $ad_name ) . "'";
	$total = $skyuc->db->query_first_slave ( $sql );
	if ($total ['total'] > 0) {
		$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
		sys_msg ( $_LANG ['ad_name_exist'], 0, $link );
	}

	// 添加图片类型的广告
	if ($skyuc->GPC ['media_type'] == '0') {
		if ((isset ( $_FILES ['ad_img'] ['error'] ) && $_FILES ['ad_img'] ['error'] == 0) || (! isset ( $_FILES ['ad_img'] ['error'] ) && $_FILES ['ad_img'] ['tmp_name'] != 'none')) {
			$ad_code = basename ( upload_file ( $_FILES ['ad_img'], 'af' ) );
		}
		if (! empty ( $skyuc->GPC ['img_url'] )) {
			$ad_code = $skyuc->GPC ['img_url'];
		}
		if (((isset ( $_FILES ['ad_img'] ['error'] ) && $_FILES ['ad_img'] ['error'] > 0) || (! isset ( $_FILES ['ad_img'] ['error'] ) && $_FILES ['ad_img'] ['tmp_name'] == 'none')) && empty ( $skyuc->GPC ['img_url'] )) {
			$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
			sys_msg ( $_LANG ['js_languages'] ['ad_code_empty'], 0, $link );
		}
	}

	// 如果添加的广告是Flash广告
	elseif ($skyuc->GPC ['media_type'] == '1') {
		if ((isset ( $_FILES ['upfile_flash'] ['error'] ) && $_FILES ['upfile_flash'] ['error'] == 0) || (! isset ( $_FILES ['upfile_flash'] ['error'] ) && $_FILES ['upfile_flash'] ['tmp_name'] != 'none')) {
			// 检查文件类型
			if ($_FILES ['upfile_flash'] ['type'] != "application/x-shockwave-flash") {
				$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
				sys_msg ( $_LANG ['upfile_flash_type'], 0, $link );
			}

			// 生成文件名
			$urlstr = skyuc_date ( 'Ymd', TIMENOW, true, false );
			for($i = 0; $i < 6; $i ++) {
				$urlstr .= chr ( rand ( 97, 122 ) );
			}

			$source_file = $_FILES ['upfile_flash'] ['tmp_name'];
			$target = DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/afficheimg/';
			$file_name = $urlstr . '.swf';

			if (! move_upload_file ( $source_file, $target . $file_name )) {
				$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
				sys_msg ( $_LANG ['upfile_error'], 0, $link );
			} else {
				$ad_code = $file_name;
			}
		}
		if (! empty ( $skyuc->GPC ['flash_url'] )) {
			$ad_code = $skyuc->GPC ['flash_url'];
		}
		if (((isset ( $_FILES ['upfile_flash'] ['error'] ) && $_FILES ['upfile_flash'] ['error'] > 0) || (! isset ( $_FILES ['upfile_flash'] ['error'] ) && $_FILES ['upfile_flash'] ['tmp_name'] == 'none')) && empty ( $skyuc->GPC ['flash_url'] )) {
			$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
			sys_msg ( $_LANG ['js_languages'] ['ad_code_empty'], 0, $link );
		}
	} // 如果广告类型为代码广告
	elseif ($skyuc->GPC ['media_type'] == '2') {
		if (! empty ( $skyuc->GPC ['ad_code'] )) {
			$ad_code = $skyuc->GPC ['ad_code'];
		} else {
			$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
			sys_msg ( $_LANG ['js_languages'] ['ad_code_empty'], 0, $link );
		}
	}

	// 广告类型为文本广告
	elseif ($skyuc->GPC ['media_type'] == '3') {
		if (! empty ( $skyuc->GPC ['ad_text'] )) {
			$ad_code = $skyuc->GPC ['ad_text'];
		} else {
			$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
			sys_msg ( $_LANG ['js_languages'] ['ad_code_empty'], 0, $link );
		}
	}

	/* 插入数据 */
	$sql = 'INSERT INTO ' . TABLE_PREFIX . 'ad' . ' (position_id,media_type,ad_name,ad_link,ad_code,start_date,end_date,link_man,link_email,link_phone,click_count,enabled) ' . "  VALUES ('" . $skyuc->db->escape_string ( $skyuc->GPC ['position_id'] ) . "',
            '" . $skyuc->db->escape_string ( $skyuc->GPC ['media_type'] ) . "',
            '" . $skyuc->db->escape_string ( $ad_name ) . "',
            '" . $skyuc->db->escape_string ( $skyuc->GPC ['ad_link'] ) . "',
            '" . $skyuc->db->escape_string ( $ad_code ) . "',
            '$start_date',
            '$end_date',
            '" . $skyuc->db->escape_string ( $skyuc->GPC ['link_man'] ) . "',
            '" . $skyuc->db->escape_string ( $skyuc->GPC ['link_email'] ) . "',
            '" . $skyuc->db->escape_string ( $skyuc->GPC ['link_phone'] ) . "',
            '0',
            '" . $skyuc->db->escape_string ( $skyuc->GPC ['enabled'] ) . "')";
	$db->query_write ( $sql );
	// 记录管理员操作
	admin_log ( $skyuc->GPC ['ad_name'], 'add', 'ads' );

	// 提示信息
	$link [0] ['text'] = $_LANG ['back_ads_list'];
	$link [0] ['href'] = 'ads.php?act=list';

	$link [1] ['text'] = $_LANG ['continue_add_ad'];
	$link [1] ['href'] = 'ads.php?act=add';
	sys_msg ( $_LANG ['add'] . "&nbsp;" . $skyuc->GPC ['ad_name'] . "&nbsp;" . $_LANG ['attradd_succed'], 0, $link );

}

/*------------------------------------------------------ */
//-- 广告编辑页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit') {
	admin_priv ( 'ad_manage' );

	$skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	// 获取广告数据
	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'ad' . ' WHERE ad_id=' . $skyuc->GPC ['id'];
	$ads_arr = $skyuc->db->query_first_slave ( $sql );

	if ($ads_arr ['media_type'] == '0') {
		if (strpos ( $ads_arr ['ad_code'], 'http://' ) === false && strpos ( $ads_arr ['ad_code'], 'https://' ) === false) {
			$src = '../' . $skyuc->config ['Misc'] ['imagedir'] . '/afficheimg/' . $ads_arr ['ad_code'];
			$smarty->assign ( 'img_src', $src );
		} else {
			$src = $ads_arr ['ad_code'];
			$smarty->assign ( 'url_src', $src );
		}
	}
	if ($ads_arr ['media_type'] == '1') {
		if (strpos ( $ads_arr ['ad_code'], 'http://' ) === false && strpos ( $ads_arr ['ad_code'], 'https://' ) === false) {
			$src = '../' . $skyuc->config ['Misc'] ['imagedir'] . '/afficheimg/' . $ads_arr ['ad_code'];
			$smarty->assign ( 'flash_src', $src );
		} else {
			$src = $ads_arr ['ad_code'];
			$smarty->assign ( 'flash_url', $src );
		}
		$smarty->assign ( 'src', $src );
	}
	if ($ads_arr ['media_type'] == 0) {
		$smarty->assign ( 'media_type', $_LANG ['ad_img'] );
	} elseif ($ads_arr ['media_type'] == 1) {
		$smarty->assign ( 'media_type', $_LANG ['ad_flash'] );
	} elseif ($ads_arr ['media_type'] == 2) {
		$smarty->assign ( 'media_type', $_LANG ['ad_html'] );
	} elseif ($ads_arr ['media_type'] == 3) {
		$smarty->assign ( 'media_type', $_LANG ['ad_text'] );
	}

	$smarty->assign ( 'ur_here', $_LANG ['ads_edit'] );
	$smarty->assign ( 'action_link', array ('href' => 'ads.php?act=list', 'text' => $_LANG ['ad_list'] ) );
	$smarty->assign ( 'form_act', 'update' );
	$smarty->assign ( 'action', 'edit' );
	$smarty->assign ( 'position_list', get_position_list () );
	$smarty->assign ( 'ads', $ads_arr );
	$smarty->assign ( 'end_date', $ads_arr ['end_date'] );

	assign_query_info ();
	$smarty->display ( 'ads_info.tpl' );
}

/*------------------------------------------------------ */
//-- 广告编辑的处理
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'update') {
	admin_priv ( 'ad_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'type' => TYPE_UINT, 'img_url' => TYPE_STR, 'flash_url' => TYPE_STR, 'ad_code' => TYPE_STR, 'ad_text' => TYPE_STR, 'position_id' => TYPE_UINT, 'ad_name' => TYPE_STR, 'ad_link' => TYPE_STR, 'link_man' => TYPE_STR, 'link_email' => TYPE_STR, 'link_phone' => TYPE_STR, 'enabled' => TYPE_BOOL, 'start_dateYear' => TYPE_UINT, 'start_dateMonth' => TYPE_UINT, 'start_dateDay' => TYPE_UINT, 'end_dateYear' => TYPE_UINT, 'end_dateMonth' => TYPE_UINT, 'end_dateDay' => TYPE_UINT ) );

	// 初始化变量
	$id = $skyuc->GPC ['id'];
	$type = $skyuc->GPC ['type'];

	// 获得广告的开始时期与结束日期
	$start_date = skyuc_mktime ( 0, 0, 0, $skyuc->GPC ['start_dateMonth'], $skyuc->GPC ['start_dateDay'], $skyuc->GPC ['start_dateYear'] );
	$end_date = skyuc_mktime ( 0, 0, 0, $skyuc->GPC ['end_dateMonth'], $skyuc->GPC ['end_dateDay'], $skyuc->GPC ['end_dateYear'] );

	// 编辑图片类型的广告
	if ($skyuc->GPC ['type'] == '0') {
		if ((isset ( $_FILES ['ad_img'] ['error'] ) && $_FILES ['ad_img'] ['error'] == 0) || (! isset ( $_FILES ['ad_img'] ['error'] ) && $_FILES ['ad_img'] ['tmp_name'] != 'none')) {
			$img_up_info = basename ( upload_file ( $_FILES ['ad_img'], 'af' ) );

			$ad_code = "ad_code ='" . $db->escape_string ( $img_up_info ) . "', ";
		} else {
			$ad_code = '';
		}
		if (! empty ( $skyuc->GPC ['img_url'] )) {
			$ad_code = "ad_code ='" . $db->escape_string ( $skyuc->GPC ['img_url'] ) . "', ";
		}
	}

	// 如果是编辑Flash广告 */
	elseif ($skyuc->GPC ['type'] == '1') {
		if ((isset ( $_FILES ['upfile_flash'] ['error'] ) && $_FILES ['upfile_flash'] ['error'] == 0) || (! isset ( $_FILES ['upfile_flash'] ['error'] ) && $_FILES ['upfile_flash'] ['tmp_name'] != 'none')) {
			// 检查文件类型
			if ($_FILES ['upfile_flash'] ['type'] != "application/x-shockwave-flash") {
				$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
				sys_msg ( $_LANG ['upfile_flash_type'], 0, $link );
			}
			// 生成文件名
			$urlstr = skyuc_date ( 'Ymd', TIMENOW, TRUE, FALSE );
			for($i = 0; $i < 6; $i ++) {
				$urlstr .= chr ( rand ( 97, 122 ) );
			}

			$source_file = $_FILES ['upfile_flash'] ['tmp_name'];
			$target = DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/afficheimg/';
			$file_name = $urlstr . '.swf';
			if (! move_upload_file ( $source_file, $target . $file_name )) {
				$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
				sys_msg ( $_LANG ['upfile_error'], 0, $link );
			} else {
				$ad_code = "ad_code ='" . $db->escape_string ( $file_name ) . "', ";
			}
		} else {
			$ad_code = '';
		}
		if (! empty ( $skyuc->GPC ['flash_url'] )) {
			$ad_code = "ad_code ='" . $db->escape_string ( $skyuc->GPC ['flash_url'] ) . "', ";
		}
	}

	// 编辑代码类型的广告
	elseif ($skyuc->GPC ['type'] == '2') {
		$ad_code = "ad_code ='" . $db->escape_string ( $skyuc->GPC ['ad_code'] ) . "', ";
	}

	// 编辑文本类型的广告
	if ($skyuc->GPC ['type'] == '3') {
		$ad_code = "ad_code ='" . $db->escape_string ( $skyuc->GPC ['ad_text'] ) . "', ";
	}

	// 更新信息
	$sql = 'UPDATE ' . TABLE_PREFIX . 'ad' . ' SET ' . "position_id = '" . $skyuc->GPC ['position_id'] . "', " . "ad_name     = '" . $skyuc->db->escape_string ( $skyuc->GPC ['ad_name'] ) . "', " . "ad_link     = '" . $skyuc->db->escape_string ( $skyuc->GPC ['ad_link'] ) . "', " . $ad_code . "start_date  = '$start_date', " . "end_date    = '$end_date', " . "link_man    = '" . $skyuc->db->escape_string ( $skyuc->GPC ['link_man'] ) . "', " . "link_email  = '" . $skyuc->db->escape_string ( $skyuc->GPC ['link_email'] ) . "', " . "link_phone  = '" . $skyuc->db->escape_string ( $skyuc->GPC ['link_phone'] ) . "', " . "enabled     = '" . $skyuc->GPC ['enabled'] . "' " . "WHERE ad_id = '$id'";
	$skyuc->db->query_write ( $sql );

	// 记录管理员操作
	admin_log ( $skyuc->GPC ['ad_name'], 'edit', 'ads' );

	//提示信息
	$link [] = array ('text' => $_LANG ['back_ads_list'], 'href' => 'ads.php?act=list' );
	sys_msg ( $_LANG ['edit'] . ' ' . $skyuc->GPC ['ad_name'] . ' ' . $_LANG ['attradd_succed'], 0, $link );

}

/*------------------------------------------------------ */
//--生成广告的JS代码
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'add_js') {
	admin_priv ( 'ad_manage' );

	$skyuc->input->clean_array_gpc ( 'r', array ('type' => TYPE_STR, 'id' => TYPE_UINT ) );

	// 编码
	$lang_list = array ('UTF8' => $_LANG ['charset'] ['utf8'], 'GB2312' => $_LANG ['charset'] ['zh-cn'], 'BIG5' => $_LANG ['charset'] ['zh-tw'] );

	$js_code = "<script type=" . '"' . "text/javascript" . '"';
	$js_code .= ' src=' . '"' . get_url () . 'affiche.php?act=js&type=' . $skyuc->GPC ['type'] . '&ad_id=' . $skyuc->GPC ['id'] . '"' . '></script>';

	$site_url = get_url () . 'affiche.php?act=js&type=' . $skyuc->GPC ['type'] . '&ad_id=' . $skyuc->GPC ['id'];

	$smarty->assign ( 'ur_here', $_LANG ['add_js_code'] );
	$smarty->assign ( 'action_link', array ('href' => 'ads.php?act=list', 'text' => $_LANG ['ad_list'] ) );
	$smarty->assign ( 'url', $site_url );
	$smarty->assign ( 'js_code', $js_code );
	$smarty->assign ( 'lang_list', $lang_list );

	assign_query_info ();
	$smarty->display ( 'ads_js.tpl' );
}

/*------------------------------------------------------ */
//-- 编辑广告名称
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_ad_name') {
	check_authz_json ( 'ad_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$id = $skyuc->GPC ['id'];
	$ad_name = $skyuc->GPC ['val'];

	// 检查广告名称是否重复
	if ($exc->num ( 'ad_name', $ad_name, $id ) != 0) {
		make_json_error ( sprintf ( $_LANG ['ad_name_exist'], $ad_name ) );
	} else {
		if ($exc->edit ( "ad_name = '" . $skyuc->db->escape_string ( $ad_name ) . "'", $id )) {
			admin_log ( $ad_name, 'edit', 'ads' );
			make_json_result ( stripslashes ( $ad_name ) );
		} else {
			make_json_error ( $skyuc->db->error () );
		}
	}
}

/*------------------------------------------------------ */
//-- 删除广告位置
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'remove') {
	check_authz_json ( 'ad_manage' );

	$id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );
	$img = $exc->get_name ( $id, 'ad_code' );

	$exc->drop ( $id );

	if ((strpos ( $img, 'http://' ) === false) && (strpos ( $img, 'https://' ) === false)) {
		$img_name = basename ( $img );
		@unlink ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/afficheimg/' . $img_name );
	}

	admin_log ( '', 'remove', 'ads' );

	$url = 'ads.php?act=query&' . str_replace ( 'act=remove', '', $_SERVER ['QUERY_STRING'] );

	header ( "Location: $url\n" );
	exit ();
} /*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'toggle_enabled') {
	check_authz_json ( 'ad_manage' );
	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	$id = $skyuc->GPC ['id'];
	$val = $skyuc->GPC ['val'];

	$exc->edit ( "enabled='$val'", $id );

	make_json_result ( $val );
}

/**
 * SKYUC! 站外JS投放的统计程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

/*------------------------------------------------------ */
//-- 站外投放广告的统计
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'listjs') {

	admin_priv ( 'ad_manage' );

	// 获取广告数据
	$ads_stats = array ();
	$sql = 'SELECT a.ad_id, a.ad_name, b.* ' . 'FROM ' . TABLE_PREFIX . 'ad' . ' AS a, ' . TABLE_PREFIX . 'adsense' . ' AS b ' . 'WHERE b.from_ad = a.ad_id ORDER by a.ad_name DESC';
	$res = $db->query_first_slave ( $sql );
	while ( $rows = $db->fetch_array ( $res ) ) {
		$ads_stats [] = $rows;
	}
	$smarty->assign ( 'ads_stats', $ads_stats );

	// 站外JS投放商品的统计数据
	$show_stats = array ();
	$show_sql = 'SELECT from_ad, referer, clicks FROM ' . TABLE_PREFIX . 'adsense' . " WHERE from_ad = '-1' ORDER by referer DESC";
	$show_res = $db->query_read_slave ( $show_sql );
	while ( $rows2 = $db->fetch_array ( $show_res ) ) {
		$rows2 ['ad_name'] = $_LANG ['adsense_js_show'];
		$show_stats [] = $rows2;
	}
	$smarty->assign ( 'show_stats', $show_stats );

	// 赋值给模板
	$smarty->assign ( 'action_link', array ('href' => 'ads.php?act=list', 'text' => $_LANG ['ad_list'] ) );
	$smarty->assign ( 'ur_here', $_LANG ['adsense_js_stats'] );
	$smarty->assign ( 'lang', $_LANG );

	// 显示页面
	assign_query_info ();
	$smarty->display ( 'adsense.tpl' );

}

?>
