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

$exc = new exchange ( TABLE_PREFIX . 'subject', $skyuc->db, 'id', 'title' );

/*------------------------------------------------------ */
//-- 专题列表
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list' || $skyuc->GPC ['act'] == '') {

	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['09_subject'] );
	$smarty->assign ( 'action_link', array ('href' => 'subject.php?act=add', 'text' => $_LANG ['subject_add'] ) );
	$smarty->assign ( 'full_page', 1 );
	$smarty->assign ( 'lang', $_LANG );

	$subject_list = get_subject_list ();

	$smarty->assign ( 'subject', $subject_list ['subject'] );
	$smarty->assign ( 'filter', $subject_list ['filter'] );
	$smarty->assign ( 'record_count', $subject_list ['record_count'] );
	$smarty->assign ( 'page_count', $subject_list ['page_count'] );

	assign_query_info ();
	$smarty->display ( 'subject_list.tpl' );
} /*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$subject_list = get_subject_list ();

	$smarty->assign ( 'subject', $subject_list ['subject'] );
	$smarty->assign ( 'filter', $subject_list ['filter'] );
	$smarty->assign ( 'record_count', $subject_list ['record_count'] );
	$smarty->assign ( 'page_count', $subject_list ['page_count'] );

	// 排序标记
	$sort_flag = sort_flag ( $subject_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	make_json_result ( $smarty->fetch ( 'subject_list.tpl' ), '', array ('filter' => $subject_list ['filter'], 'page_count' => $subject_list ['page_count'] ) );
}

/*------------------------------------------------------ */
//-- 添加、编辑专题
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'add' || $skyuc->GPC ['act'] == 'edit') {
	// 检查权限
	admin_priv ( 'subject_manage' );

	$is_add = $skyuc->GPC ['act'] == 'add'; // 添加还是编辑的标识


	// 如果是安全模式，检查目录是否存在
	if (ini_get ( 'safe_mode' ) == 1 && ! is_dir ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/subject/' . skyuc_date ( 'Ym' ) )) {
		if (@! mkdir ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/subject/' . skyuc_date ( 'Ym' ), 0777 )) {
			$warning = sprintf ( $_LANG ['safe_mode_warning'], '../' . $skyuc->config ['Misc'] ['imagedir'] . '/subject/' . skyuc_date ( 'Ym' ) );
			$smarty->assign ( 'warning', $warning );
		}
	}

	// 如果目录存在但不可写，提示用户
	elseif (is_dir ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/subject/' . skyuc_date ( 'Ym' ) ) && file_mode_info ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/subject/' . skyuc_date ( 'Ym' ) ) < 2) {
		$warning = sprintf ( $_LANG ['not_writable_warning'], '../upload/subject/' . skyuc_date ( 'Ym' ) );
		$smarty->assign ( 'warning', $warning );
	}

	// 取得影片信息
	if ($is_add) {
		$subject = array ('id' => '', 'title' => '', 'thumb' => '', 'poster' => '', 'intro' => '', 'recom' => 0, 'add_time' => '', 'detail' => '', 'link' => 'http://', 'uselink' => 0 );
	} else {
		$id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );
		// 影片信息
		$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'subject' . ' WHERE id = ' . $id;
		// 默认值
		$subject = array ();
		$subject = $skyuc->db->query_first ( $sql );
	}
	// 创建 html editor
	create_html_editor ( 'detail', $subject ['detail'] );

	// 模板赋值
	$smarty->assign ( 'ur_here', iif ( $is_add, $_LANG ['subject_add'], $_LANG ['subject_edit'] ) );
	$smarty->assign ( 'action_link', array ('href' => 'subject.php?act=list', 'text' => $_LANG ['09_subject'] ) );
	$smarty->assign ( 'subject', $subject );
	$smarty->assign ( 'form_act', iif ( $is_add, 'insert', 'update' ) );
	$smarty->assign ( 'lang', $_LANG );

	assign_query_info ();
	$smarty->display ( 'subject_info.tpl' );

} /*------------------------------------------------------ */
//-- 插入影片 更新影片
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'insert' || $skyuc->GPC ['act'] == 'update') {
	// 检查权限
	admin_priv ( 'subject_manage' );

	$skyuc->input->clean_array_gpc ( 'f', array ('poster' => TYPE_FILE, 'thumb' => TYPE_FILE ) );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'title' => TYPE_STR, 'poster_url' => TYPE_STR, 'thumb_url' => TYPE_STR, 'intro' => TYPE_STR, 'detail' => TYPE_STR, 'recom' => TYPE_BOOL, 'link' => TYPE_STR, 'uselink' => TYPE_BOOL, 'auto_thumb' => TYPE_BOOL ) );

	$id = $skyuc->GPC ['id'];
	$title = $skyuc->GPC ['title'];

	//重要，upload类只接受$skyuc->GPC['upload']
	$skyuc->GPC ['upload'] = & $skyuc->GPC ['poster'];

	// 检查名称是否重复
	if ($title) {
		$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'subject' . " WHERE title = '" . $skyuc->db->escape_string ( $title ) . "' AND id != '$id'";
		$total = $skyuc->db->query_first ( $sql );
		if ($total ['total'] > 0) {
			sys_msg ( $_LANG ['title_exists'], 1, array (), false );
		}
	}

	require_once (DIR . '/includes/class_upload.php');
	require_once (DIR . '/includes/class_image.php');

	$upload_dir = $skyuc->config ['Misc'] ['imagedir'] . '/subject/' . skyuc_date ( 'Ym' ) . '/image';
	$upload = new Upload_Image ( $skyuc );
	$upload->image = & Image::fetch_library ( $skyuc );
	$upload->path = $upload_dir;
	$upload->image->path = $upload_dir;

	// 初始化海报图片
	$posterpath = '';
	//上传海报
	if (! empty ( $skyuc->GPC ['poster'] ['tmp_name'] )) {
		if (! ($posterpath = $upload->process_upload ( $skyuc->GPC ['poster'] ))) {
			sys_msg ( $upload->fetch_error (), 1, array (), false );
		}
	} else {
		$posterpath = $skyuc->GPC ['poster_url'];
	}

	//重要，upload类只接受$skyuc->GPC['upload']
	$skyuc->GPC ['upload'] = & $skyuc->GPC ['thumb'];

	$thumb_dir = $skyuc->config ['Misc'] ['imagedir'] . '/subject/' . skyuc_date ( 'Ym' ) . '/thumb';
	$upload_thumb = new Upload_Image ( $skyuc );
	$upload_thumb->image = & Image::fetch_library ( $skyuc );
	$upload_thumb->path = $thumb_dir;
	$upload_thumb->image->path = $thumb_dir;

	// 初始化缩略图
	$thumbpath = '';
	//上传缩略图
	if (! empty ( $skyuc->GPC ['thumb'] ['tmp_name'] )) {
		if (! ($thumbpath = $upload_thumb->process_upload ( $skyuc->GPC ['thumb'] ))) {
			sys_msg ( $upload_thumb->fetch_error (), 1, array (), false );
		}
	} else {
		$thumbpath = $skyuc->GPC ['thumb_url'];
	}

	// 插入还是更新的标识
	$is_insert = ($skyuc->GPC ['act'] == 'insert');

	// 如果上传了海报，相应处理
	if (! empty ( $posterpath )) {
		if ($id > 0) {
			//如果有上传图片，删除原来的海报图
			$sql = 'SELECT thumb,  poster  FROM ' . TABLE_PREFIX . 'subject' . " WHERE id = '$id'";
			$row = $db->query_first ( $sql );
			if ($row ['thumb'] != '' && pic_parse_url ( $row ['thumb'] )) {
				@unlink ( DIR . '/' . $row ['thumb'] );
			}
			if ($row ['poster'] != '' && pic_parse_url ( $row ['poster'] )) {
				@unlink ( DIR . '/' . $row ['poster'] );
			}
		}

		if (pic_parse_url ( $posterpath )) {
			//生成海报缩略图
			$imagepath = DIR . '/' . $posterpath;
			$image = & $upload->image;
			$posterimage = make_thumb ( $image, $imagepath, 215, 170, false );
		} else {
			$posterimage = $posterpath;
		}

		//上传了缩略图
		if ($thumbpath != '' && pic_parse_url ( $thumbpath )) {
			//生成缩略图
			$imagepath = DIR . '/' . $thumbpath;
			$image = & $upload_thumb->image;
			$thumbimage = make_thumb ( $image, $imagepath, 215, 60 );
		} elseif (pic_parse_url ( $posterpath ) && pic_parse_url ( $thumbpath ) && $skyuc->GPC_exists ['auto_thumb']) {
			// 未上传，如果选择自动生成，生成缩略图
			$imagepath = DIR . '/' . $posterpath;
			$image = & $upload_thumb->image;
			$thumbimage = make_thumb ( $image, $imagepath, 215, 60 );
		} else {
			//海报图使用远程URL ,因此缩略图也使用远程URL
			$thumbimage = iif ( $thumbpath == '' || pic_parse_url ( $thumbpath ), $posterimage, $thumbpath );
		}

	}

	// 添加新主题
	if ($is_insert) {
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'subject' . '( title, thumb, poster, `intro`, `detail`, `add_time`, `recom`, `link`, `uselink`) ' . " VALUES ('" . $db->escape_string ( $title ) . "', '" . $db->escape_string ( $thumbimage ) . "', '" . $db->escape_string ( $posterimage ) . "', '" . $db->escape_string ( $skyuc->GPC ['intro'] ) . "', '" . $db->escape_string ( $skyuc->GPC ['detail'] ) . "', '" . TIMENOW . "', '" . $skyuc->GPC ['recom'] . "', '" . $db->escape_string ( $skyuc->GPC ['link'] ) . "', '" . $skyuc->GPC ['uselink'] . "')";

		if ($db->query_write ( $sql )) {
			$new_id = $db->insert_id ();

			admin_log ( $title, 'add', 'subject' ); // 记录管理员操作


			$skyuc->secache->setModified ( 'index.dwt' );

			//添加链接
			$link [0] ['text'] = $_LANG ['continue_add'];
			$link [0] ['href'] = 'subject.php?act=add';

			$link [1] ['text'] = $_LANG ['back_list'];
			$link [1] ['href'] = 'subject.php?act=list';

			sys_msg ( $_LANG ['add_subject_ok'], 0, $link );
		}
	} else {
		$sql = 'UPDATE ' . TABLE_PREFIX . 'subject' . "	SET `title` = '" . $db->escape_string ( $title ) . "', ";

		if (! empty ( $thumbimage )) {
			$sql .= " thumb = '" . $db->escape_string ( $thumbimage ) . "', ";
		}
		if (! empty ( $posterimage )) {
			$sql .= " poster = '" . $db->escape_string ( $posterimage ) . "', ";
		}

		$sql .= " intro = '" . $db->escape_string ( $skyuc->GPC ['intro'] ) . "', " . " detail = '" . $db->escape_string ( $skyuc->GPC ['detail'] ) . "', " . " add_time = '" . TIMENOW . "', " . " recom = '" . $skyuc->GPC ['recom'] . "', " . " link = '" . $db->escape_string ( $skyuc->GPC ['link'] ) . "', " . " uselink = '" . $skyuc->GPC ['uselink'] . "' " . '	 WHERE id = ' . $id;
		if ($db->query_write ( $sql )) {
			admin_log ( $title, 'edit', 'subject' ); // 记录管理员操作


			// 提示信息
			$link [] = array ('text' => $_LANG ['back_list'], 'href' => 'subject.php?act=list' );
			sys_msg ( $_LANG ['edit_subject_ok'], 0, $link );
		}
	}

} /*------------------------------------------------------ */
//-- 彻底删除
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'drop') {
	// 检查权限
	check_authz_json ( 'subject_manage' );

	$skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	if ($skyuc->GPC ['id'] <= 0) {
		make_json_error ( 'invalid params' );
	}

	// 取得影片信息
	$sql = 'SELECT id, title, poster, thumb ' . 'FROM ' . TABLE_PREFIX . 'subject' . ' WHERE id = ' . $skyuc->GPC ['id'];
	$row = $db->query_first ( $sql );
	if (empty ( $row )) {
		make_json_error ( $_LANG ['subject_not_exist'] );
	}

	// 删除海报图片和缩略图
	if (! empty ( $row ['thumb'] ) && strpos ( $row ['thumb'], 'http://' ) === false) {
		@unlink ( DIR . '/' . $row ['thumb'] );
	}
	if (! empty ( $row ['poster'] ) && strpos ( $row ['poster'], 'http://' ) === false) {
		@unlink ( DIR . '/' . $row ['poster'] );
	}

	// 删除影片
	$exc->drop ( $skyuc->GPC ['id'] );

	// 记录日志
	admin_log ( $row ['title'], 'remove', 'subject' );

	$skyuc->secache->setModified ( 'index.dwt' );
	$url = 'subject.php?act=query&' . str_replace ( 'act=drop', '', $_SERVER ['QUERY_STRING'] );

	header ( "Location: $url\n" );
	exit ();
} /*------------------------------------------------------ */
//-- 编辑名称
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_title') {
	check_authz_json ( 'subject_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$id = $skyuc->GPC ['id'];
	$val = $skyuc->GPC ['val'];

	// 检查名称是否重复
	if ($exc->num ( 'title', $val, $id ) != 0) {
		make_json_error ( sprintf ( $_LANG ['title_exist'], $val ) );
	} else {
		if ($exc->edit ( "title = '" . $db->escape_string ( $val ) . "'", $id )) {
			admin_log ( $val, 'edit', 'subject' );
			make_json_result ( $val );
		} else {
			make_json_result ( sprintf ( $_LANG ['title_edit_fail'], $val ) );
		}
	}
} /*------------------------------------------------------ */
//-- 切换是否推荐
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'toggle_recom') {
	check_authz_json ( 'subject_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	$id = $skyuc->GPC ['id'];
	$val = $skyuc->GPC ['val'];

	$exc->edit ( "recom='$val'", $id );

	make_json_result ( $val );
} /*------------------------------------------------------ */
//-- 切换是否使用二级域名
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'toggle_uselink') {
	check_authz_json ( 'subject_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	$id = $skyuc->GPC ['id'];
	$val = $skyuc->GPC ['val'];

	$exc->edit ( "uselink='$val'", $id );

	make_json_result ( $val );
} /*------------------------------------------------------ */
//-- 显示图片
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'show_image') {
	$skyuc->input->clean_gpc ( 'g', 'img_url', TYPE_STR );

	if (strpos ( $skyuc->GPC ['img_url'], 'http://' ) === 0) {
		$img_url = $skyuc->GPC ['img_url'];
	} else {
		$img_url = '../' . $skyuc->GPC ['img_url'];
	}

	$smarty->assign ( 'img_url', $img_url );
	$smarty->display ( 'show_image.tpl' );
}

?>