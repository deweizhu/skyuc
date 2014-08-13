<?php
/**
 * SKYUC! 影片管理程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');
require (DIR . '/includes/functions_search.php');
$exc = new exchange ( TABLE_PREFIX . 'show', $skyuc->db, 'show_id', 'title' );

/*------------------------------------------------------ */
//-- 影片列表
/*------------------------------------------------------ */

if ($skyuc->GPC ['act'] == 'list') {

	$cat_id = $skyuc->input->clean_gpc ( 'r', 'cat_id', TYPE_UINT );

	// 模板赋值
	$ur_here = $_LANG ['01_show_list'];
	$smarty->assign ( 'ur_here', $ur_here );

	$action_link = array ('href' => 'show.php?act=add', 'text' => $_LANG ['02_show_add'] );
	$smarty->assign ( 'action_link', $action_link );

	$smarty->assign ( 'cat_list', get_cat_list ( 0, $cat_id ) );
	$smarty->assign ( 'server_list', get_server_list () );
	$smarty->assign ( 'player', get_player_list () );
	$smarty->assign ( 'intro_list', get_intro_list () );
	$smarty->assign ( 'lang', $_LANG );

	$show_list = get_show_list ( 1 );

	$smarty->assign ( 'show_list', $show_list ['show'] );
	$smarty->assign ( 'filter', $show_list ['filter'] );
	$smarty->assign ( 'record_count', $show_list ['record_count'] );
	$smarty->assign ( 'page_count', $show_list ['page_count'] );
	$smarty->assign ( 'full_page', 1 );

	// 排序标记
	$sort_flag = sort_flag ( $show_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	assign_query_info ();
	$smarty->display ( 'show_list.tpl' );
}

/*------------------------------------------------------ */
//-- 影片回收站列表
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'trash') {
	$cat_id = $skyuc->input->clean_gpc ( 'r', 'cat_id', TYPE_UINT );

	// 模板赋值
	$ur_here = $_LANG ['03_show_trash'];
	$smarty->assign ( 'ur_here', $ur_here );

	$action_link = array ('href' => 'show.php?act=list', 'text' => $_LANG ['01_show_list'] );
	$smarty->assign ( 'action_link', $action_link );

	$smarty->assign ( 'cat_list', get_cat_list ( 0, $cat_id ) );
	$smarty->assign ( 'server_list', get_server_list () );
	$smarty->assign ( 'player', get_player_list () );
	$smarty->assign ( 'intro_list', get_intro_list () );
	$smarty->assign ( 'lang', $_LANG );

	$show_list = get_show_list ( 0 );
	$smarty->assign ( 'show_list', $show_list ['show'] );
	$smarty->assign ( 'filter', $show_list ['filter'] );
	$smarty->assign ( 'record_count', $show_list ['record_count'] );
	$smarty->assign ( 'page_count', $show_list ['page_count'] );
	$smarty->assign ( 'full_page', 1 );

	// 排序标记
	$sort_flag = sort_flag ( $show_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	assign_query_info ();
	$smarty->display ( 'show_trash.tpl' );
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$skyuc->input->clean_array_gpc ( 'r', array ('cat_id' => TYPE_UINT, 'is_show' => TYPE_UINT ) );

	$cat_id = $skyuc->GPC ['cat_id'];
	$is_show = $skyuc->GPC ['is_show'];
	$show_list = get_show_list ( $is_show );

	$smarty->assign ( 'show_list', $show_list ['show'] );
	$smarty->assign ( 'filter', $show_list ['filter'] );
	$smarty->assign ( 'record_count', $show_list ['record_count'] );
	$smarty->assign ( 'page_count', $show_list ['page_count'] );
	$smarty->assign ( 'cat_list', get_cat_list ( 0, $cat_id ) );

	// 排序标记
	$sort_flag = sort_flag ( $show_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	$tpl = iif ( $is_show, 'show_list.tpl', 'show_trash.tpl' );

	make_json_result ( $smarty->fetch ( $tpl ), '', array ('filter' => $show_list ['filter'], 'page_count' => $show_list ['page_count'] ) );
}

/*------------------------------------------------------ */
//-- 添加新影片 编辑影片
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'add' || $skyuc->GPC ['act'] == 'edit' || $skyuc->GPC ['act'] == 'copy') {

	// 检查权限
	admin_priv ( 'show_manage' );

	$is_add = $skyuc->GPC ['act'] == 'add'; // 添加还是编辑的标识
	$is_copy = $skyuc->GPC ['act'] == 'copy'; //是否复制


	// 如果是安全模式，检查目录是否存在
	if (ini_get ( 'safe_mode' ) == 1 && ! is_dir ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/posters/' . skyuc_date ( 'Ym' ) )) {
		if (@! mkdir ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/posters/' . skyuc_date ( 'Ym' ), 0777 )) {
			$warning = sprintf ( $_LANG ['safe_mode_warning'], '../upload/posters/' . skyuc_date ( 'Ym' ) );
			$smarty->assign ( 'warning', $warning );
		}
	}

	// 如果目录存在但不可写，提示用户
	elseif (is_dir ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/posters/' . skyuc_date ( 'Ym' ) ) && file_mode_info ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/posters/' . skyuc_date ( 'Ym' ) ) < 2) {
		$warning = sprintf ( $_LANG ['not_writable_warning'], '../upload/posters/' . skyuc_date ( 'Ym' ) );
		$smarty->assign ( 'warning', $warning );
	}

	$skyuc->input->clean_gpc ( 'r', 'show_id', TYPE_UINT );

	// 取得影片信息
	if ($is_add) {

		// 默认值
		$last_choose = array (0, 0, 0, 0, 0, 0, 0, 1 );
		$skyuc->input->clean_gpc ( 'c', COOKIE_PREFIX . 'last_choose', TYPE_STR );

		if (! empty ( $skyuc->GPC [COOKIE_PREFIX . 'last_choose'] )) {
			$last_choose = explode ( '|', $skyuc->GPC [COOKIE_PREFIX . 'last_choose'] );
		}

		if (isset ( $_GET ['cat_id'] )) {
			$cat_id = $skyuc->input->clean_gpc ( 'g', 'cat_id', TYPE_UINT );
		} else {
			$cat_id = $last_choose [0];
		}

		$show = array ();
		$show ['server_id'] = $last_choose [7];
		$show ['other_cat'] = array (); // 扩展分类
		$show ['click_count'] = 1;
		$show ['keywords'] = '';
		$show ['description'] = '';
		$show ['detail'] = '';
		$show ['cat_id'] = $cat_id;
		$show ['area'] = $last_choose [1];
		$show ['lang'] = $last_choose [2];
		$show ['runtime'] = $last_choose [3];
		$show ['points'] = $last_choose [4];
		$show ['player'] = $last_choose [5];
		$show ['pubdate'] = $last_choose [6];
		$show ['data'] [0] = array ('url' => '', 'player' => $show ['player'], 'server' => $show ['server_id'] );
	} else {
		// 影片信息
		$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'show' . ' WHERE show_id = ' . $skyuc->GPC ['show_id'];
		$show = $db->query_first ( $sql );

		if (empty ( $show ) === true) {
			$show = array (); // 默认值
		}
		if (! empty ( $show ['actor'] )) {
			$show ['actor'] = trim ( $show ['actor'] );
		}
		if (! empty ( $show ['title'] )) {
			$show ['title'] = trim ( $show ['title'] );
		}
		if (! empty ( $show ['server_id'] )) {
			$show ['server_id'] = explode ( ',', $show ['server_id'] );
		}
		if (! empty ( $show ['player'] )) {
			$show ['player'] = explode ( ',', $show ['player'] );
		}
		// 处理影片 数据地址
		if (! empty ( $show ['data'] )) {
			// 地址分组
			$show ['data'] = display_url_data ( $show ['data'], $show ['player'], $show ['server_id'] );
		}

		// 如果是复制影片，处理
		if ($skyuc->GPC ['act'] == 'copy') {
			// 影片信息
			$show ['show_id'] = 0;
			$urls = array (); //复制时地址为空
			$show ['title'] = '';
			$show ['image'] = '';

	// 扩展分类不变
		}

		// 扩展分类
		$other_cat_list = array ();
		$show ['other_cat'] = array ();

		$sql = 'SELECT cat_id FROM ' . TABLE_PREFIX . 'show_cat' . ' WHERE show_id = ' . $skyuc->GPC ['show_id'];
		$res = $db->query_read ( $sql );
		while ( $row = $db->fetch_row ( $res ) ) {
			$show ['other_cat'] [] = $row [0];
		}
		foreach ( $show ['other_cat'] as $cat_id ) {
			$other_cat_list [$cat_id] = get_cat_list ( 0, $cat_id );
		}

		$smarty->assign ( 'other_cat_list', $other_cat_list );
	}

	// 拆分影片名称样式
	$title_style = explode ( '+', empty ( $show ['title_style'] ) ? '+' : $show ['title_style'] );

	// 创建 html editor
	create_html_editor ( 'detail', $show ['detail'] );

	// 模板赋值
	$smarty->assign ( 'ur_here', iif ( $is_add, $_LANG ['02_show_add'], iif ( $skyuc->GPC ['act'] == 'edit', $_LANG ['edit_show'], $_LANG ['copy_show'] ) ) );
	$smarty->assign ( 'action_link', array ('href' => 'show.php?act=list', 'text' => $_LANG ['01_show_list'] ) );
	$smarty->assign ( 'show', $show );
	$smarty->assign ( 'title_color', $title_style [0] );
	$smarty->assign ( 'title_style', $title_style [1] );
	$smarty->assign ( 'cat_list', get_cat_list ( 0, $show ['cat_id'] ) );
	$smarty->assign ( 'server', get_server_list ( 3 ) );

	$smarty->assign ( 'cfg', $skyuc->options );
	$smarty->assign ( 'form_act', iif ( $is_add, 'insert', iif ( $skyuc->GPC ['act'] == 'edit', 'update', 'insert' ) ) );

	//  影片地区
	$smarty->assign ( 'area_list', select_area_lang ( $skyuc->options ['show_area'], $show ['area'] ) );
	//  影片语言
	$smarty->assign ( 'lang_list', select_area_lang ( $skyuc->options ['show_lang'], $show ['lang'] ) );

	$smarty->assign ( 'actor_list', select_actor_director ( $skyuc->options ['show_actor'] ) );
	$smarty->assign ( 'director_list', select_actor_director ( $skyuc->options ['show_director'] ) );
	$smarty->assign ( 'status_list', select_actor_director ( $skyuc->options ['show_status'] ) );

	include (DIR . '/languages/' . $skyuc->options ['lang'] . '/common.php');

	//获取播放器列表
	$smarty->assign ( 'player', get_player_list () );

	assign_query_info ();
	$smarty->display ( 'show_info.tpl' );
}

/*------------------------------------------------------ */
//-- 插入影片 更新影片
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'insert' || $skyuc->GPC ['act'] == 'update') {
	// 检查权限
	admin_priv ( 'show_manage' );

	$skyuc->input->clean_array_gpc ( 'f', array ('poster' => TYPE_FILE, 'thumb' => TYPE_FILE ) );
	$skyuc->input->clean_array_gpc ( 'p', array ('show_id' => TYPE_UINT, 'director' => TYPE_STR, 'actor' => TYPE_STR, 'title' => TYPE_STR, 'title_alias' => TYPE_STR, 'title_english' => TYPE_STR, 'title_color' => TYPE_STR, 'title_style' => TYPE_STR, 'title_nostyle' => TYPE_BOOL, 'status' => TYPE_STR, 'poster_url' => TYPE_STR, 'thumb_url' => TYPE_STR, 'keywords' => TYPE_STR, 'description' => TYPE_STR, 'detail' => TYPE_STR, 'area' => TYPE_STR, 'lang' => TYPE_STR, 'pubdate' => TYPE_STR, 'addtime' => TYPE_BOOL, 'runtime' => TYPE_UINT, 'click_count' => TYPE_UINT, 'points' => TYPE_UINT, 'cat_id' => TYPE_UINT, 'attribute' => TYPE_UINT, 'auto_thumb' => TYPE_BOOL, 'other_cat' => TYPE_ARRAY_UINT, 'player' => TYPE_ARRAY_STR, 'server_id' => TYPE_ARRAY_UINT, 'data' => TYPE_ARRAY_STR ) );

	$show_id = $skyuc->GPC ['show_id'];
	$title = $skyuc->GPC ['title'];

	// 检查是否选择了服务器
	if (empty ( $skyuc->GPC ['server_id'] )) {
		sys_msg ( $_LANG ['server_id_empty'], 1, array (), false );
	}
	// 检查片名是否重复
/*	if (! empty ( $title )) {
		$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'show' . " WHERE title = '" . $db->escape_string ( $title ) . "' AND cat_id = '" . $skyuc->GPC ['cat_id'] . "' AND show_id != '$show_id'";
		$total = $db->query_first ( $sql );
		if ($total ['total'] > 0) {
			sys_msg ( $_LANG ['title_exists'], 1, array (), false );
		}
	}*/

	//重要，upload类只接受$skyuc->GPC['upload']
	$skyuc->GPC ['upload'] = & $skyuc->GPC ['poster'];
	require_once (DIR . '/includes/class_upload.php');
	require_once (DIR . '/includes/class_image.php');
	require (DIR . '/includes/functions_ftp.php');
	require (DIR . '/includes/functions_log_error.php');

	$upload_dir = $skyuc->config ['Misc'] ['imagedir'] . '/posters/' . skyuc_date ( 'Ym' ) . '/image';
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

	$thumb_dir = $skyuc->config ['Misc'] ['imagedir'] . '/posters/' . skyuc_date ( 'Ym' ) . '/thumb';
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

	// 如果上传了海报，相应处理
	if (! empty ( $posterpath )) {
		if ($show_id > 0) {
			//如果有上传图片，删除原来的海报图
			$sql = 'SELECT thumb, image, source  FROM ' . TABLE_PREFIX . 'show' . " WHERE show_id = '$show_id'";
			$row = $db->query_first ( $sql );
			if ($row ['thumb'] != '' && pic_parse_url ( $row ['thumb'] )) {
				@unlink ( DIR . '/' . $row ['thumb'] );
			}
			if ($row ['image'] != '' && pic_parse_url ( $row ['image'] )) {
				@unlink ( DIR . '/' . $row ['image'] );
			}
			if ($row ['source'] != '' && pic_parse_url ( $row ['source'] )) {
				@unlink ( DIR . '/' . $row ['source'] );
			}
		}

		if (pic_parse_url ( $posterpath )) {
			//生成海报缩略图
			$imagepath = DIR . '/' . $posterpath;
			$image = & $upload->image;
			$posterimage = make_thumb ( $image, $imagepath, $skyuc->options ['image_width'], $skyuc->options ['image_height'], false );
			//出错返回
			if (is_array ( $posterimage )) {
				//删除上传的图片
				@unlink ( $imagepath );
				sys_msg ( $_LANG [$posterimage ['error']], 1, array (), false );
			}
			//保存原始图片
			$sourcepath = $skyuc->config ['Misc'] ['imagedir'] . '/posters/' . skyuc_date ( 'Ym' ) . '/source';
			$sourceimage = $sourcepath . '/' . basename ( $posterimage );
			if (! is_dir ( DIR . '/' . $sourcepath )) {
				make_dir ( DIR . '/' . $sourcepath );
			}
			copy ( $imagepath, DIR . '/' . $sourceimage );

		} else {
			$posterimage = $posterpath;
			$sourceimage = $posterimage;
		}

		//上传了缩略图
		if ($thumbpath != '' && pic_parse_url ( $thumbpath )) {
			if ($skyuc->options ['attachthumbs'] == 0) {
				//禁止生成缩略图时，用户上传了缩略图，需要删除海报原图
				@unlink ( $imagepath );
			}
			//生成缩略图
			$imagepath = DIR . '/' . $thumbpath;
			$image = & $upload_thumb->image;
			$thumbimage = make_thumb ( $image, $imagepath, $skyuc->options ['thumb_width'], $skyuc->options ['thumb_height'] );
		} elseif (pic_parse_url ( $posterpath ) && pic_parse_url ( $thumbpath ) && $skyuc->GPC_exists ['auto_thumb']) {
			// 未上传，如果选择自动生成，生成缩略图
			$imagepath = DIR . '/' . $posterpath;
			$image = & $upload_thumb->image;
			$thumbimage = make_thumb ( $image, $imagepath, $skyuc->options ['thumb_width'], $skyuc->options ['thumb_height'] );
		} else {
			//海报图使用远程URL ,因此缩略图也使用远程URL
			$thumbimage = iif ( $thumbpath == '' || pic_parse_url ( $thumbpath ), $posterimage, $thumbpath );
		}

	}

	// 处理影片数据
	$skyuc->GPC ['title_style'] = $skyuc->GPC ['title_color'] . '+' . $skyuc->GPC ['title_style'];
	$server_id = implode ( ',', $skyuc->GPC ['server_id'] );
	$player = implode ( ',', $skyuc->GPC ['player'] );
	$url_data = repair_url_data ( $skyuc->GPC ['data'] );

	//中文分词
	if (empty ( $skyuc->GPC ['keywords'] )) {
		$skyuc->GPC ['keywords'] = splitword ( $title, sub_str ( html2text ( $skyuc->GPC ['detail'] ), 250 ) );
	}
	if (empty ( $skyuc->GPC ['description'] )) {
		$skyuc->GPC ['description'] = sub_str ( html2text ( $skyuc->GPC ['detail'] ), 250 );
	}

	//禁用标题样式
	if ($skyuc->GPC ['title_nostyle']) {
		$skyuc->GPC ['title_style'] = '+';
	}

	// 插入还是更新的标识
	$is_insert = ($skyuc->GPC ['act'] == 'insert');

	//使用FTP上传图片
	if (pic_parse_url ( $posterimage )) {
		ftpupload ( $posterimage );
		ftpupload ( $sourceimage );
	}
	if (pic_parse_url ( $thumbimage )) {
		ftpupload ( $thumbimage );
	}

	// 添加新影片
	if ($is_insert) {
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'show' .
            ' (title,title_alias,title_english,title_style, actor, director, status, cat_id,server_id,
            attribute, image, thumb,source, keywords, description,detail,pubdate, area, lang, click_count,
            is_show, add_time, points, runtime, player, data) ' .
         " VALUES ('" .
            $db->escape_string ( $title ) . "',	'" .
            $db->escape_string ( $skyuc->GPC ['title_alias'] ) . "',	'" .
            $db->escape_string ( $skyuc->GPC ['title_english'] ) . "', '" .
            $db->escape_string ( $skyuc->GPC ['title_style'] ) . "','" .
            $db->escape_string ( $skyuc->GPC ['actor'] ) ."', '" .
            $db->escape_string ( $skyuc->GPC ['director'] ) . "', '" .
            $db->escape_string ( $skyuc->GPC ['status'] ) . "', '" .
            $skyuc->GPC ['cat_id'] . "', '" . $server_id . "',	'" .
            $skyuc->GPC ['attribute'] . "', '" .
            $db->escape_string ( $posterimage ) .  "', '" .
            $db->escape_string ( $thumbimage ) . "', '" .
            $db->escape_string ( $sourceimage ) . "','" .
            $db->escape_string ( $skyuc->GPC ['keywords'] ) . "','" .
            $db->escape_string ( $skyuc->GPC ['description'] ) ."','" .
            $db->escape_string ( $skyuc->GPC ['detail'] ) . "', '" .
            $db->escape_string ( $skyuc->GPC ['pubdate'] ) .  "', '" .
            $db->escape_string ( $skyuc->GPC ['area'] ) . "', '" .
            $db->escape_string ( $skyuc->GPC ['lang'] ) . "', '" .
            $skyuc->GPC ['click_count'] . "', '1' ,'" . TIMENOW . "', '" .
            $skyuc->GPC ['points'] . "','" . $skyuc->GPC ['runtime'] . "' ,'" .
            $db->escape_string ( $player ) . "' ,'" .
            $db->escape_string ( $url_data ) . "')";
		$db->query_write ( $sql );
		$show_id = $db->insert_id ();
		$param = array();
		$param['show_id'] = $show_id;
		$param['cat_id'] = $skyuc->GPC['cat_id'];
		$param['title'] = $title;
		$param['title_alias'] = $skyuc->GPC['title_alias'];
		$param['title_english'] = $skyuc->GPC['title_english'];
		$param['actor'] = $skyuc->GPC['actor'];
		$param['director'] = $skyuc->GPC['director'];
		$param['detail'] = $skyuc->GPC['detail'];

		add_search_index($param);

	} else { // 修改影片
		$sql = 'UPDATE ' . TABLE_PREFIX . 'show' .
            " SET title = '" . $db->escape_string ( $title ) . "', " . " title_alias = '" . $db->escape_string ( $skyuc->GPC ['title_alias'] ) . "'," . " title_english='" . $db->escape_string ( $skyuc->GPC ['title_english'] ) . "'," . " title_style = '" . $db->escape_string ( $skyuc->GPC ['title_style'] ) . "', " . " actor = '" . $db->escape_string ( $skyuc->GPC ['actor'] ) . "', " . " director = '" . $db->escape_string ( $skyuc->GPC ['director'] ) . "', " . " status = '" . $db->escape_string ( $skyuc->GPC ['status'] ) . "', " . " cat_id = '" . $skyuc->GPC ['cat_id'] . "', " . " server_id = '" . $server_id . "', " . " data = '" . $db->escape_string ( $url_data ) . "', ";

		// 如果有上传图片，需要更新数据库
		if (! empty ( $posterimage )) {
			if (! empty ( $row ['image'] ) && pic_parse_url ( $row ['image'] )) {
				skyuc_ftp_delete ( $ftp ['connid'], $row ['image'] );
				skyuc_ftp_delete ( $ftp ['connid'], $row ['source'] );
			}
			$sql .= "image = '" . $db->escape_string ( $posterimage ) . "', source = '" . $db->escape_string ( $sourceimage ) . "', ";
		}
		if (! empty ( $thumbimage )) {
			if (! empty ( $row ['thumb'] ) && pic_parse_url ( $row ['thumb'] )) {
				skyuc_ftp_delete ( $ftp ['connid'], $row ['thumb'] );
			}
			$sql .= "thumb = '" . $db->escape_string ( $thumbimage ) . "', ";
		}
		if ($skyuc->GPC ['addtime']) {
			$sql .= " add_time = '" . TIMENOW . "', ";
		}
		$sql .= " attribute = '" . $skyuc->GPC ['attribute'] . "', " . " keywords = '" . $db->escape_string ( $skyuc->GPC ['keywords'] ) . "', " . " description = '" . $db->escape_string ( $skyuc->GPC ['description'] ) . "', " . " detail = '" . $db->escape_string ( $skyuc->GPC ['detail'] ) . "', " . " pubdate = '" . $db->escape_string ( $skyuc->GPC ['pubdate'] ) . "', " . " click_count = '" . $skyuc->GPC ['click_count'] . "', " . " area = '" . $db->escape_string ( $skyuc->GPC ['area'] ) . "', " . " lang = '" . $db->escape_string ( $skyuc->GPC ['lang'] ) . "'," . " points = '" . $skyuc->GPC ['points'] . "', " . " player = '" . $db->escape_string ( $player ) . "', " . " runtime = '" . $skyuc->GPC ['runtime'] . "' " . " WHERE show_id = '$show_id'";

		$db->query_write ( $sql );

		$param = array();
		$param['show_id'] = $show_id;
		$param['cat_id'] = $skyuc->GPC['cat_id'];
		$param['title'] = $title;
		$param['title_alias'] = $skyuc->GPC['title_alias'];
		$param['title_english'] = $skyuc->GPC['title_english'];
		$param['actor'] = $skyuc->GPC['actor'];
		$param['director'] = $skyuc->GPC['director'];
		$param['detail'] = $skyuc->GPC['detail'];

		add_search_index($param, true);
	}

	// 处理扩展分类
	if ($skyuc->GPC_exists ['other_cat']) {
		$show_id = $show_id ? $show_id : $id; //影片ID为空用新插入的ID
		handle_other_cat ( $show_id, array_unique ( $skyuc->GPC ['other_cat'] ) );
	}

	//记住最后一次添加影片分类
	$last_choose = $skyuc->GPC ['cat_id'] . '|' . $skyuc->GPC ['area'] . '|' . $skyuc->GPC ['lang'] . '|' . $skyuc->GPC ['runtime'] . '|' . $skyuc->GPC ['points'] . '|' . $skyuc->GPC ['player'] [0] . '|' . $skyuc->GPC ['pubdate'] . '|' . $skyuc->GPC ['server'] [0];
	skyuc_setcookie ( 'last_choose', $last_choose, true );

	// 记录日志
	admin_log ( $title, $is_insert ? 'add' : 'edit', 'show' );

	$skyuc->topnewhots = array ();
	build_datastore ( 'topnewhots', serialize ( $skyuc->topnewhots ), 1 ); //设置新片和排行榜缓存过期


	// 清空缓存
	update_file_cache( md5($show_id) );

	//退出FTP
	if ($ftp ['connid']) {
		@ftp_close ( $ftp ['connid'] );
	}
	$ftp = array ();

	// 提示页面
	if ($is_insert) {
		$link [] = array ('text' => $_LANG ['continue_add_show'], 'href' => 'show.php?act=add' );
	}

	$link [] = array ('text' => $_LANG ['back_show_list'], 'href' => 'show.php?act=list' );
	sys_msg ( $is_insert ? $_LANG ['add_show_ok'] : $_LANG ['edit_show_ok'], 0, $link );
}

/*------------------------------------------------------ */
//-- 批量操作
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'batch') {

	$skyuc->input->clean_array_gpc ( 'p', array ('checkboxes' => TYPE_ARRAY_UINT, 'type' => TYPE_STR, 'target_cat' => TYPE_UINT ) );
	// 取得要操作的影片编号
	$show_id = ! empty ( $skyuc->GPC ['checkboxes'] ) ? implode ( ',', $skyuc->GPC ['checkboxes'] ) : 0;

	if ($skyuc->GPC_exists ['type']) {

		switch ($skyuc->GPC ['type']) {
			case 'trash' :
				// 放入回收站
				admin_priv ( 'remove_back' );
				update_show ( $show_id, 'is_show', '0' );
				admin_log ( '', 'batch_trash', 'show' );
				break;
			case 'best' :
				// 设为首页推荐
				admin_priv ( 'show_manage' );
				update_show ( $show_id, 'attribute', '1' );
				break;
			case 'not_best' :
				// 取消首页推荐
				admin_priv ( 'show_manage' );
				update_show ( $show_id, 'attribute', '0' );
				break;
			case 'hot' :
				// 设为分类推荐
				admin_priv ( 'show_manage' );
				update_show ( $show_id, 'attribute', '2' );
				break;
			case 'not_hot' :
				// 取消分类推荐
				admin_priv ( 'show_manage' );
				update_show ( $show_id, 'attribute', '0' );
				break;
			case 'move_to' :
				//转移到分类
				admin_priv ( 'show_manage' );
				update_show ( $show_id, 'cat_id', $skyuc->GPC ['target_cat'] );
				break;
			case 'restore' :
				//还原
				admin_priv ( 'remove_back' );
				update_show ( $show_id, 'is_show', '1' );
				admin_log ( '', 'batch_restore', 'show' );
				break;
			case 'drop' :
				//删除
				admin_priv ( 'remove_back' );
				delete_show ( $show_id );
				admin_log ( '', 'batch_remove', 'show' );
				break;
			default :
				break;
		}

	}

	$skyuc->topnewhots = array ();
	build_datastore ( 'topnewhots', serialize ( $skyuc->topnewhots ), 1 ); //设置新片和排行榜缓存过期
	// 清除缓存
	$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', 'show.dwt' ) );

	$link [] = array ('href' => 'show.php?act=list', 'text' => $_LANG ['01_show_list'] );
	sys_msg ( $_LANG ['batch_handle_ok'], 0, $link );
}

/*------------------------------------------------------ */
//-- 修改影片名称
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_title') {
	check_authz_json ( 'show_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$show_id = $skyuc->GPC ['id'];
	$title = $skyuc->GPC ['val'];

	if ($exc->edit ( "title = '" . $db->escape_string ( $title ) . "'", $show_id )) {
		$skyuc->topnewhots = array ();
		build_datastore ( 'topnewhots', serialize ( $skyuc->topnewhots ), 1 ); //设置新片和排行榜缓存过期


		$cachename = 'show_' . sprintf ( '%X', crc32 ( $show_id ) );
		$skyuc->secache->setModified ( md5 ( $cachename ) );

		make_json_result ( $title );
	}
}

/*------------------------------------------------------ */
//-- 修改影片上映日期
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_pubdate') {
	check_authz_json ( 'show_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$show_id = $skyuc->GPC ['id'];
	$pubdate = $skyuc->GPC ['val'];

	if ($exc->edit ( "pubdate = '" . $db->escape_string ( $pubdate ) . "'", $show_id )) {
		$cachename = 'show_' . sprintf ( '%X', crc32 ( $show_id ) );
		$skyuc->secache->setModified ( md5 ( $cachename ) );
		make_json_result ( $pubdate );
	}
}

/*------------------------------------------------------ */
//-- 修改影片点播次数
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_click_count') {
	check_authz_json ( 'show_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	$show_id = $skyuc->GPC ['id'];
	$click_count = $skyuc->GPC ['val'];

	if ($exc->edit ( "click_count = '$click_count'", $show_id )) {
		$skyuc->topnewhots = array ();
		build_datastore ( 'topnewhots', serialize ( $skyuc->topnewhots ), 1 ); //设置新片和排行榜缓存过期
		$cachename = 'show_' . sprintf ( '%X', crc32 ( $show_id ) );
		$skyuc->secache->setModified ( md5 ( $cachename ) );
		make_json_result ( $click_count );
	}
}

/*------------------------------------------------------ */
//-- 修改影片播放器
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_player') {
	check_authz_json ( 'show_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$show_id = $skyuc->GPC ['id'];
	$player = $skyuc->GPC ['val'];

	if ($exc->edit ( "player = '" . $db->escape_string ( $player ) . "'", $show_id )) {
		$cachename = 'show_' . sprintf ( '%X', crc32 ( $show_id ) );
		$skyuc->secache->setModified ( md5 ( $cachename ) );
		make_json_result ( $player );
	}
}

/*------------------------------------------------------ */
//-- 修改影片观看所需点数
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_points') {
	check_authz_json ( 'show_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	$show_id = $skyuc->GPC ['id'];
	$points = $skyuc->GPC ['val'];

	if ($exc->edit ( "points = '$points'", $show_id )) {
		$cachename = 'show_' . sprintf ( '%X', crc32 ( $show_id ) );
		$skyuc->secache->setModified ( md5 ( $cachename ) );
		make_json_result ( $points );
	}
} /*------------------------------------------------------ */
//-- 修改强力推荐状态
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'toggle_best') {
	check_authz_json ( 'show_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_BOOL ) );

	$show_id = $skyuc->GPC ['id'];
	$val = iif ( $skyuc->GPC ['val'] == 1, 1, 0 );

	if ($exc->edit ( "attribute = '$val'", $show_id )) {
		$skyuc->topnewhots = array ();
		build_datastore ( 'topnewhots', serialize ( $skyuc->topnewhots ), 1 ); //设置新片和排行榜缓存过期
		$skyuc->secache->setModified ( 'index.dwt' );
		make_json_result ( $val );
	}
}

/*------------------------------------------------------ */
//-- 修改分类推荐
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'toggle_hot') {
	check_authz_json ( 'show_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_BOOL ) );

	$show_id = $skyuc->GPC ['id'];
	$val = iif ( $skyuc->GPC ['val'] == 1, 2, 0 );

	if ($exc->edit ( "attribute = '$val'", $show_id )) {
		$skyuc->topnewhots = array ();
		build_datastore ( 'topnewhots', serialize ( $skyuc->topnewhots ), 1 ); //设置新片和排行榜缓存过期
		$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt' ) );
		make_json_result ( $skyuc->GPC ['val'] );
	}
}

/*------------------------------------------------------ */
//-- 放入回收站
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'remove') {
	$show_id = $skyuc->input->clean_gpc ( 'r', 'id', TYPE_UINT );

	// 检查权限
	check_authz_json ( 'remove_back' );

	$exc->edit ( 'is_show = 0', $show_id );

	$skyuc->topnewhots = array ();
	build_datastore ( 'topnewhots', serialize ( $skyuc->topnewhots ), 1 ); //设置新片和排行榜缓存过期
	$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', 'show.dwt' ) );

	$title = $exc->get_name ( $show_id );

	admin_log ( $title, 'trash', 'show' ); // 记录日志


	$url = 'show.php?act=query&' . str_replace ( 'act=remove', '', $_SERVER ['QUERY_STRING'] );
	header ( "Location: $url\n" );
	exit ();
}

/*------------------------------------------------------ */
//-- 还原回收站中的影片
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'restore_show') {
	$show_id = $skyuc->input->clean_gpc ( 'r', 'id', TYPE_UINT );

	check_authz_json ( 'remove_back' ); // 检查权限


	$exc->edit ( 'is_show = 1, add_time =' . TIMENOW, $show_id );

	$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt' ) );

	$title = $exc->get_name ( $show_id );

	admin_log ( $title, 'restore', 'show' ); // 记录日志


	$url = 'show.php?act=query&' . str_replace ( 'act=restore_show', '', $_SERVER ['QUERY_STRING'] );

	header ( "Location: $url\n" );
	exit ();
}

/*------------------------------------------------------ */
//-- 彻底删除影片
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'drop_show') {
	// 检查权限
	check_authz_json ( 'remove_back' );

	// 取得参数
	$show_id = $skyuc->input->clean_gpc ( 'r', 'id', TYPE_UINT );
	if ($show_id <= 0) {
		make_json_error ( 'invalid params' );
	}

	// 取得影片信息
	$sql = 'SELECT show_id, title, image,thumb,source, is_show  FROM ' . TABLE_PREFIX . 'show' . ' WHERE show_id = ' . $show_id;
	$show = $db->query_first ( $sql );
	if (empty ( $show )) {
		make_json_error ( $_LANG ['show_not_exist'] );
	}

	if ($show ['is_show'] != 0) {
		make_json_error ( $_LANG ['show_not_in_recycle_bin'] );
	}

	require (DIR . '/includes/functions_ftp.php');
	require (DIR . '/includes/functions_log_error.php');

	// 删除海报图片和缩略图 *
	if (! empty ( $show ['thumb'] ) && pic_parse_url ( $row ['thumb'] )) {
		ftpdelete ( $show ['thumb'] );
		@unlink ( DIR . '/' . $show ['thumb'] );
	}
	if (! empty ( $show ['image'] ) && pic_parse_url ( $row ['image'] )) {
		ftpdelete ( $show ['image'] );
		@unlink ( DIR . '/' . $show ['image'] );
	}
	if (! empty ( $show ['source'] ) && pic_parse_url ( $row ['source'] )) {
		ftpdelete ( $show ['source'] );
		@unlink ( DIR . '/' . $show ['source'] );
	}
	//退出FTP
	if ($ftp ['connid']) {
		@ftp_close ( $ftp ['connid'] );
	}
	$ftp = array ();

	// 删除影片
	$exc->drop ( $show_id );

	// 删除相关表记录
	$sql = 'DELETE FROM ' . TABLE_PREFIX . 'show_cat' . " WHERE show_id = '$show_id'";
	$db->query_write ( $sql );

	// 记录日志
	admin_log ( $show ['title'], 'remove', 'show' );

	$skyuc->topnewhots = array ();
	build_datastore ( 'topnewhots', serialize ( $skyuc->topnewhots ), 1 ); //设置新片和排行榜缓存过期
	$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', 'show.dwt' ) );

	$url = 'show.php?act=query&' . str_replace ( 'act=drop_show', '', $_SERVER ['QUERY_STRING'] );

	header ( "Location: $url\n" );

	exit ();
}

/*------------------------------------------------------ */
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
