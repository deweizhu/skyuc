<?php
/**
 * SKYUC! 管理中心模版管理程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');
require_once (DIR . '/includes/functions_template.php');

/*------------------------------------------------------ */
//-- 模版列表
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list') {
	// 获得当前的模版的信息
	$curr_template = $skyuc->options ['themes'];
	$curr_style = $skyuc->options ['stylename'];

	// 获得可用的模版
	$available_templates = array ();
	$template_dir = @opendir ( DIR . '/templates/' );
	while ( $file = readdir ( $template_dir ) ) {
		if ($file != '.' && $file != '..' && is_dir ( DIR . '/templates/' . $file ) && $file != '.svn' && $file != 'index.htm') {
			$available_templates [] = get_template_info ( $file );
		}
	}
	@closedir ( $template_dir );

	/* 获得可用的模版的可选风格数组 */
	$templates_style = array ();
	if (count ( $available_templates ) > 0) {
		foreach ( $available_templates as $value ) {
			$templates_style [$value ['code']] = read_tpl_style ( $value ['code'], 2 );
		}
	}

	// 清除不需要的模板设置
	$available_code = array ();
	$sql = 'DELETE FROM ' . TABLE_PREFIX . 'template' . ' WHERE 1 ';
	foreach ( $available_templates as $tmp ) {
		$sql .= " AND theme <> '" . $tmp ['code'] . "' ";
		$available_code [] = $tmp ['code'];
	}
	$tmp_bak_dir = opendir ( DIR . '/templates/backup/library/' );
	while ( $file = readdir ( $tmp_bak_dir ) ) {
		if ($file != '.' && $file != '..' && $file != '.svn' && $file != 'index.htm' && is_file ( DIR . '/templates/backup/library/' . $file ) == true) {
			$code = substr ( $file, 0, strpos ( $file, '-' ) );
			if (! in_array ( $code, $available_code )) {
				@unlink ( DIR . '/templates/backup/library/' . $file );
			}
		}
	}

	$skyuc->db->query_write ( $sql );

	assign_query_info ();

	$smarty->assign ( 'ur_here', $_LANG ['02_template_list'] );
	$smarty->assign ( 'curr_tpl_style', $curr_style );
	$smarty->assign ( 'template_style', $templates_style );
	$smarty->assign ( 'curr_template', get_template_info ( $curr_template, $curr_style ) );
	$smarty->assign ( 'available_templates', $available_templates );
	$smarty->display ( 'templates_list.tpl' );
}

/*------------------------------------------------------ */
//-- 模板选择,安装模板
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'install') {
	check_authz_json ( 'template_manage' );

	$tpl_name = $skyuc->input->clean_gpc ( 'g', 'tpl_name', TYPE_STR );
	$tpl_fg = 0;
	$tpl_fg = $skyuc->input->clean_gpc ( 'g', 'tpl_fg', TYPE_STR );

	$sql = 'UPDATE ' . TABLE_PREFIX . 'setting' . " SET value = '" . $skyuc->db->escape_string ( $tpl_name ) . "' WHERE code = 'themes'";
	$step_one = $skyuc->db->query_write ( $sql );
	$sql = 'UPDATE ' . TABLE_PREFIX . 'setting' . " SET value = '" . $skyuc->db->escape_string ( $tpl_fg ) . "' WHERE code = 'stylename'";
	$step_two = $skyuc->db->query_write ( $sql );

	if ($step_one && $step_two) {
		$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', 'show.dwt', 'search.dwt', 'article.dwt', 'article_cat.dwt' ) );
		build_options (); //重建设置缓存
		//$skyuc->secache->setModified(array('index.dwt', 'list.dwt', 'show.dwt', 'search.dwt','article.dwt','article_cat.dwt'));
		clear_tpl_files ();
		// make_json_result(get_template_info($tpl_name), $_LANG['install_template_success']);
		make_json_result ( read_style_and_tpl ( $tpl_name, $tpl_fg ), $_LANG ['install_template_success'] );
	} else {
		make_json_error ( $db->error () );
	}
}

/*------------------------------------------------------ */
//-- 备份模版
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'backup') {
	check_authz_json ( 'template_manage' );
	require_once (DIR . '/includes/class_phpzip.php');

	$tpl = $skyuc->input->clean_gpc ( 'r', 'tpl_name', TYPE_STR );

	$filename = '../templates/backup/' . $tpl . '_' . skyuc_date ( 'Ymd', TIMENOW, FALSE, FALSE ) . '_' . random ( 9 ) . '.zip';

	$zip = new zipfile ();
	$done = $zip->addDir ( DIR . '/templates/' . $tpl . '/', DIR . $filename );
	unset ( $zip );
	if ($done) {
		make_json_result ( $filename );
	} else {
		make_json_error ( $_LANG ['backup_failed'] );
	}
}

/*------------------------------------------------------ */
//-- 设置模板的内容
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'setup') {
	admin_priv ( 'template_manage' );

	$skyuc->input->clean_array_gpc ( 'r', array ('template_file' => TYPE_STR ) );

	$template_theme = $skyuc->options ['themes'];
	$curr_template = iif ( empty ( $skyuc->GPC ['template_file'] ), 'index', $skyuc->GPC ['template_file'] );

	$temp_options = array ();
	$temp_regions = get_template_region ( $template_theme, $curr_template . '.dwt', false );
	$temp_libs = get_template_region ( $template_theme, $curr_template . '.dwt', true );

	$curr_libs = get_dwt_libs ( $curr_template, $page_libs [$curr_template] );

	// 获取数据库中数据，并跟模板中数据核对,并设置动态内容
	//固定内容
	foreach ( $curr_libs as $val => $number_enabled ) {
		$lib = basename ( strtolower ( substr ( $val, 0, strpos ( $val, '.' ) ) ) );
		if (! in_array ( $lib, $GLOBALS ['dyna_libs'] ) and strpos ( $val ['lib'], 'cat_show' ) === false and strpos ( $val ['lib'], 'cat_hot' ) === false and strpos ( $val ['lib'], 'series' ) === false) {
			//先排除动态内容
			$temp_options [$lib] = get_setted ( $val, $temp_libs );
			$temp_options [$lib] ['desc'] = $_LANG ['template_libs'] [$lib];
			$temp_options [$lib] ['library'] = $val;
			$temp_options [$lib] ['number_enabled'] = $number_enabled > 0 ? 1 : 0;
			$temp_options [$lib] ['number'] = $number_enabled;
		}
	}

	// 动态内容
	$cate_hot = array ();
	$cate_show = array ();
	$series = array ();
	$ad_positions = array ();
	$sql = 'SELECT region, library, sort_order, id, number, type FROM ' . TABLE_PREFIX . 'template' . "  WHERE theme='" . $db->escape_string ( $template_theme ) . "' AND filename='" . $db->escape_string ( $curr_template ) . "'  " . '  ORDER BY region, sort_order ASC ';

	$rc = $skyuc->db->query_read ( $sql );
	$db_dyna_libs = array ();
	while ( $row = $skyuc->db->fetch_array ( $rc ) ) {
		if ($row ['type'] > 0) {
			// 动态内容
			$db_dyna_libs [$row ['region']] [$row ['library']] [] = array ('id' => $row ['id'], 'library' => $row ['library'], 'number' => $row ['number'], 'type' => $row ['type'] );

		} else {
			// 固定内容
			$lib = basename ( strtolower ( substr ( $row ['library'], 0, strpos ( $row ['library'], '.' ) ) ) );
			if (isset ( $lib )) {
				$temp_options [$lib] ['number'] = $row ['number'];
			}
		}

	}

	foreach ( $temp_libs as $val ) {

		// 对动态内容赋值
		if ($val ['lib'] == 'cat_show' || strpos ( $val ['lib'], 'cat_show' ) !== false) {

			// 分类下的影片
			if (isset ( $db_dyna_libs [$val ['region']] [$val ['library']] ) && ($row = array_shift ( $db_dyna_libs [$val ['region']] [$val ['library']] ))) {
				$cate_show [] = array ('region' => $val ['region'], 'sort_order' => $val ['sort_order'], 'library' => $val ['library'], 'number' => $row ['number'], 'cats' => get_cat_list ( 0, $row ['id'] ) );
			} else {
				$cate_show [] = array ('region' => $val ['region'], 'sort_order' => $val ['sort_order'], 'library' => '/library/cat_show.lbi', 'number' => 0, 'cats' => get_cat_list ( 0 ) );
			}
		}
		// 分类点播排行榜
		if ($val ['lib'] == 'cat_hot' || strpos ( $val ['lib'], 'cat_hot' ) !== false) {
			if (isset ( $db_dyna_libs [$val ['region']] [$val ['library']] ) && ($row = array_shift ( $db_dyna_libs [$val ['region']] [$val ['library']] ))) {
				$cate_hot [] = array ('region' => $val ['region'], 'sort_order' => $val ['sort_order'], 'library' => $val ['library'], 'number' => $row ['number'], 'cats' => get_cat_list ( 0, $row ['id'] ) );
			} else {
				$cate_hot [] = array ('region' => $val ['region'], 'sort_order' => $val ['sort_order'], 'library' => '/library/cat_hot.lbi', 'number' => 0, 'cats' => get_cat_list ( 0 ) );
			}
		} // 连载影片
		elseif ($val ['lib'] == 'series' || strpos ( $val ['lib'], 'series' ) !== false) {
			if (isset ( $db_dyna_libs [$val ['region']] [$val ['library']] ) && ($row = array_shift ( $db_dyna_libs [$val ['region']] [$val ['library']] ))) {
				$series [] = array ('region' => $val ['region'], 'sort_order' => $val ['sort_order'], 'library' => $val ['library'], 'number' => $row ['number'], 'cat' => get_cat_list ( 0, $row ['id'] ) );
			} else {
				$series [] = array ('region' => $val ['region'], 'sort_order' => $val ['sort_order'], 'library' => '/library/series.lbi', 'number' => 0, 'cat' => get_cat_list ( 0 ) );
			}
		}

		// 广告位
		elseif ($val ['lib'] == 'ad_position') {
			if (isset ( $db_dyna_libs [$val ['region']] [$val ['library']] ) && ($row = array_shift ( $db_dyna_libs [$val ['region']] [$val ['library']] ))) {
				$ad_positions [] = array ('region' => $val ['region'], 'sort_order' => $val ['sort_order'], 'number' => $row ['number'], 'ad_pos' => $row ['id'] );
			} else {
				$ad_positions [] = array ('region' => $val ['region'], 'sort_order' => $val ['sort_order'], 'number' => 0, 'ad_pos' => 0 );
			}
		}
	}

	assign_query_info ();

	$smarty->assign ( 'ur_here', $_LANG ['03_template_setup'] );
	$smarty->assign ( 'curr_template_file', $curr_template );
	$smarty->assign ( 'temp_options', $temp_options );
	$smarty->assign ( 'temp_regions', $temp_regions );
	$smarty->assign ( 'cate_show', $cate_show );
	$smarty->assign ( 'cate_hot', $cate_hot );
	$smarty->assign ( 'series', $series );
	$smarty->assign ( 'ad_positions', $ad_positions );
	$smarty->assign ( 'arr_cates', get_cat_list ( 0, 0, true ) );
	$smarty->assign ( 'arr_ad_positions', get_position_list () );
	$smarty->display ( 'template_setup.tpl' );
}

/*------------------------------------------------------ */
//-- 提交模板内容设置
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'setting') {
	admin_priv ( 'template_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('template_file' => TYPE_STR, 'display' => TYPE_ARRAY_BOOL, 'map' => TYPE_ARRAY_STR ) );

	//由于下面数组是多维数组（且是混合类型），因此不能使用input->clean_array_gpc过滤
	$skyuc->GPC ['regions'] = $_POST ['regions'];
	$skyuc->GPC ['categories'] = $_POST ['categories'];
	$skyuc->GPC ['series_cat'] = $_POST ['series_cat'];
	$skyuc->GPC ['catehots'] = $_POST ['catehots'];
	$skyuc->GPC ['ad_position'] = $_POST ['ad_position'];
	$skyuc->GPC ['maps'] = $_POST ['maps'];
	$skyuc->GPC ['number'] = $_POST ['number'];
	$skyuc->GPC ['sort_order'] = $_POST ['sort_order'];

	$curr_template = $skyuc->options ['themes'];
	$template_file = $skyuc->GPC ['template_file'];
	$db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'template' . " WHERE remarks = '' AND filename = '" . $db->escape_string ( $template_file ) . "' AND theme = '$curr_template'" );

	//先处理固定内容
	foreach ( $skyuc->GPC ['regions'] as $key => $val ) {
		$number = intval ( $skyuc->GPC ['number'] [$key] );
		if (! in_array ( $key, $GLOBALS ['dyna_libs'] ) and $skyuc->GPC ['display'] [$key] == 1) {
			$sql = 'INSERT INTO ' . TABLE_PREFIX . 'template' . '(theme, filename, region, library, sort_order, number)' . ' VALUES ' . "('" . $curr_template . "', '" . $db->escape_string ( $template_file ) . "', '" . $db->escape_string ( $val ) . "', '" . $db->escape_string ( $skyuc->GPC ['map'] [$key] ) . "', '" . $db->escape_string ( $skyuc->GPC ['sort_order'] [$key] ) . "', '" . $db->escape_string ( $number ) . "')";
			$db->query_write ( $sql );
		}
	}

	// 分类的影片
	if (isset ( $skyuc->GPC ['regions'] ['cat_show'] )) {
		foreach ( $skyuc->GPC ['regions'] ['cat_show'] as $key => $val ) {
			if ($skyuc->GPC ['categories'] ['cat_show'] [$key] != '' && intval ( $skyuc->GPC ['categories'] ['cat_show'] [$key] ) > 0) {
				$sql = 'INSERT INTO ' . TABLE_PREFIX . 'template' . ' (' . 'theme, filename, region, library, sort_order, type, id, number' . ') VALUES (' . "'$curr_template', " . "'" . $db->escape_string ( $template_file ) . "', '" . $db->escape_string ( $val ) . "', '" . $db->escape_string ( $skyuc->GPC ['maps'] ['cat_show'] [$key] ) . "', " . "'" . $db->escape_string ( $skyuc->GPC ['sort_order'] ['cat_show'] [$key] ) . "', 1, '" . $db->escape_string ( $skyuc->GPC ['categories'] ['cat_show'] [$key] ) . "', '" . $db->escape_string ( $skyuc->GPC ['number'] ['cat_show'] [$key] ) . "'" . ")";
				$db->query_write ( $sql );
			}
		}

	}

	// 分类点播排行
	if (isset ( $skyuc->GPC ['regions'] ['cat_hot'] )) {
		foreach ( $skyuc->GPC ['regions'] ['cat_hot'] as $key => $val ) {
			if ($skyuc->GPC ['catehots'] ['cat_hot'] [$key] != '' && intval ( $skyuc->GPC ['catehots'] ['cat_hot'] [$key] ) > 0) {
				$sql = 'INSERT INTO ' . TABLE_PREFIX . 'template' . ' (' . 'theme, filename, region, library, sort_order, type, id, number' . ') VALUES (' . "'$curr_template', " . "'" . $db->escape_string ( $template_file ) . " ', '" . $db->escape_string ( $val ) . "', '/library/cat_hot.lbi', " . "'" . $db->escape_string ( $skyuc->GPC ['sort_order'] ['cat_hot'] [$key] ) . "', 2, '" . $db->escape_string ( $skyuc->GPC ['catehots'] ['cat_hot'] [$key] ) . "', '" . $db->escape_string ( $skyuc->GPC ['number'] ['cat_hot'] [$key] ) . "'" . ")";
				$db->query_write ( $sql );
			}
		}

	}

	// 连载影片
	if (isset ( $skyuc->GPC ['regions'] ['series'] )) {
		foreach ( $skyuc->GPC ['regions'] ['series'] as $key => $val ) {
			if ($skyuc->GPC ['series_cat'] ['series'] [$key] != '' && intval ( $skyuc->GPC ['series_cat'] ['series'] [$key] ) > 0) {
				$sql = 'INSERT INTO ' . TABLE_PREFIX . 'template' . ' (' . 'theme, filename, region, library, sort_order, type, id, number' . ') VALUES (' . "'$curr_template', " . "'" . $db->escape_string ( $template_file ) . "', '" . $db->escape_string ( $val ) . "', '/library/series.lbi', " . "'" . $db->escape_string ( $skyuc->GPC ['sort_order'] ['series'] [$key] ) . "', 3, '" . $db->escape_string ( $skyuc->GPC ['series_cat'] ['series'] [$key] ) . "', '" . $db->escape_string ( $skyuc->GPC ['number'] ['series'] [$key] ) . "'" . ")";
				$db->query_write ( $sql );
			}
		}

	}

	// 广告位
	if (isset ( $skyuc->GPC ['regions'] ['ad_position'] )) {
		foreach ( $skyuc->GPC ['regions'] ['ad_position'] as $key => $val ) {
			if ($skyuc->GPC ['ad_position'] [$key] != '' && intval ( $skyuc->GPC ['ad_position'] [$key] ) > 0) {
				$sql = 'INSERT INTO ' . TABLE_PREFIX . 'template' . ' (' . 'theme, filename, region, library, sort_order, type, id, number' . ') VALUES (' . "'$curr_template', " . "'" . $db->escape_string ( $template_file ) . "', '" . $db->escape_string ( $val ) . "', '/library/ad_position.lbi', " . "'" . $db->escape_string ( $skyuc->GPC ['sort_order'] ['ad_position'] [$key] ) . "', 4, '" . $skyuc->GPC ['ad_position'] [$key] . "', '" . $db->escape_string ( $skyuc->GPC ['number'] ['ad_position'] [$key] ) . "'" . ")";
				$db->query_write ( $sql );
			}
		}
	}

	// 对提交内容进行处理
	$post_regions = array ();
	foreach ( $skyuc->GPC ['regions'] as $key => $val ) {
		switch ($key) {

			case 'cat_show' :
				foreach ( $val as $k => $v ) {
					if (intval ( $skyuc->GPC ['categories'] ['cat_show'] [$k] ) > 0) {
						$post_regions [] = array ('region' => $v, 'type' => 1, 'number' => $skyuc->GPC ['number'] ['cat_show'] [$k], // 'library'    => '/library/' .$key. '.lbi',
						'library' => trim ( $skyuc->GPC ['maps'] ['cat_show'] [$k] ), 'sort_order' => $skyuc->GPC ['sort_order'] ['cat_show'] [$k], 'id' => $skyuc->GPC ['categories'] ['cat_show'] [$k] );
					}
				}
				break;

			case 'cat_hot' :
				foreach ( $val as $k => $v ) {
					if (intval ( $skyuc->GPC ['catehots'] ['cat_hot'] [$k] ) > 0) {
						$post_regions [] = array ('region' => $v, 'type' => 1, 'number' => $skyuc->GPC ['number'] ['cat_hot'] [$k], 'library' => trim ( $skyuc->GPC ['maps'] ['cat_hot'] [$k] ), //'library'    => '/library/' .$key. '.lbi',
						'sort_order' => $skyuc->GPC ['sort_order'] ['cat_hot'] [$k], 'id' => $skyuc->GPC ['catehots'] ['cat_hot'] [$k] );
					}
				}
				break;

			case 'series' :
				foreach ( $val as $k => $v ) {
					if (intval ( $skyuc->GPC ['series_cat'] ['series'] [$k] ) > 0) {
						$post_regions [] = array ('region' => $v, 'type' => 3, 'number' => $skyuc->GPC ['number'] ['series'] [$k], 'library' => trim ( $skyuc->GPC ['maps'] ['series'] [$k] ), //'library'    => '/library/' .$key. '.lbi',
						'sort_order' => $skyuc->GPC ['sort_order'] ['series'] [$k], 'id' => $skyuc->GPC ['series_cat'] ['series'] [$k] );
					}
				}
				break;
			case 'ad_position' :
				foreach ( $val as $k => $v ) {
					if (intval ( $skyuc->GPC ['ad_position'] [$k] ) > 0) {
						$post_regions [] = array ('region' => $v, 'type' => 4, 'number' => $skyuc->GPC ['number'] ['ad_position'] [$k], 'library' => '/library/' . $key . '.lbi', 'sort_order' => $skyuc->GPC ['sort_order'] ['ad_position'] [$k], 'id' => $skyuc->GPC ['ad_position'] [$k] );
					}
				}
				break;
			default :
				if (! empty ( $skyuc->GPC ['display'] [$key] )) {
					$post_regions [] = array ('region' => $val, 'type' => 0, 'number' => 0, 'library' => $skyuc->GPC ['map'] [$key], 'sort_order' => $skyuc->GPC ['sort_order'] [$key], 'id' => 0 );
				}

		}
	}

	//排序
	usort ( $post_regions, 'array_sort' );

	// 修改模板文件
	$template_file_dwt = DIR . '/templates/' . $curr_template . '/' . $template_file . '.dwt';
	$template_content = file_get_contents ( $template_file_dwt );
	$template_content = str_replace ( "\xEF\xBB\xBF", '', $template_content );
	$org_regions = get_template_region ( $curr_template, $template_file . '.dwt', false );

	$region_content = '';
	$pattern = '/(<!--\\s*TemplateBeginEditable\\sname="%s"\\s*-->)(.*?)(<!--\\s*TemplateEndEditable\\s*-->)/s';
	$replacement = "\\1\n%s\\3";
	//  $lib_template     = "\r\n\t<!-- #BeginLibraryItem \"%s\" -->\r\n\t%s\r\n\t <!-- #EndLibraryItem -->\r\n";
	$lib_template = "\r\n\t<!-- #BeginLibraryItem \"%s\" -->\r\n\t<!-- #EndLibraryItem -->\r\n"; //不获取库文件内容填充区域


	foreach ( $org_regions as $region ) {
		$region_content = ''; // 获取当前区域内容
		foreach ( $post_regions as $lib ) {
			if ($lib ['region'] == $region) {
				// 跳过区域名称为hidden的库文件操作
				//                if ($lib['region'] == 'hidden')
				//                {
				//                    continue;
				//                }
				if (! is_file ( DIR . '/templates/' . $curr_template . $lib ['library'] )) {
					continue;
				}
				//                $lib_content     = file_get_contents( DIR . '/templates/' . $curr_template . $lib['library']);
				//                $lib_content     = preg_replace('/<meta\\shttp-equiv=["|\']Content-Type["|\']\\scontent=["|\']text\/html;\\scharset=(.*?)["|\']>/i', '', $lib_content);
				//                $lib_content     = str_replace("\xEF\xBB\xBF", '', $lib_content);
				//                $region_content .= sprintf($lib_template, $lib['library'], $lib_content);
				$region_content .= sprintf ( $lib_template, $lib ['library'], '' ); //不获取库文件内容填充区域
			}
		}

		// 替换原来区域内容
		$template_content = preg_replace ( sprintf ( $pattern, $region ), sprintf ( $replacement, $region_content ), $template_content );
	}

	if (file_put_contents ( $template_file_dwt, $template_content )) {
		clear_tpl_files ( '.dwt.php' ); // 清除对应的编译文件
		build_template (); // 建立当前模板设置缓存


		$lnk [] = array ('text' => $_LANG ['go_back'], 'href' => 'template.php?act=setup&template_file=' . $template_file );
		sys_msg ( $_LANG ['setup_success'], 0, $lnk );
	} else {
		sys_msg ( sprintf ( $_LANG ['modify_dwt_failed'], 'templates/' . $curr_template . '/' . $template_file . '.dwt' ), 1, null, false );
	}
}

/*------------------------------------------------------ */
//-- 管理库项目
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'library') {
	admin_priv ( 'template_manage' );

	$curr_template = $skyuc->options ['themes'];
	$arr_library = array ();
	$library_path = DIR . '/templates/' . $curr_template . '/library';
	$library_dir = @opendir ( $library_path );
	$curr_library = '';

	while ( $file = @readdir ( $library_dir ) ) {
		if (substr ( $file, - 3 ) == 'lbi') {
			$filename = substr ( $file, 0, - 4 );
			$arr_library [$filename] = $file . ' - ' . @$_LANG ['template_libs'] [$filename];

			if ($curr_library == '') {
				$curr_library = $filename;
			}
		}
	}

	ksort ( $arr_library );

	@closedir ( $library_dir );

	$lib = load_library ( $curr_template, $curr_library );

	assign_query_info ();
	$smarty->assign ( 'ur_here', $_LANG ['04_template_library'] );
	$smarty->assign ( 'curr_library', $curr_library );
	$smarty->assign ( 'libraries', $arr_library );
	$smarty->assign ( 'library_html', $lib ['html'] );
	$smarty->display ( 'template_library.tpl' );
} /*------------------------------------------------------ */
//-- 载入指定库项目的内容
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'load_library') {
	$lib = $skyuc->input->clean_gpc ( 'g', 'lib', TYPE_STR );

	$library = load_library ( $skyuc->options ['themes'], $lib );
	$message = ($library ['mark'] & 7) ? '' : $_LANG ['library_not_written'];

	make_json_result ( $library ['html'], $message );
} /*------------------------------------------------------ */
//-- 更新库项目内容
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'update_library') {
	check_authz_json ( 'template_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('html' => TYPE_STR, 'lib' => TYPE_STR ) );

	$html = $skyuc->GPC ['html'];
	$lib_file = DIR . '/templates/' . $skyuc->options ['themes'] . '/library/' . $skyuc->GPC ['lib'] . '.lbi';
	$lib_file = str_replace ( "0xa", '', $lib_file ); // 过滤 0xa 非法字符


	$org_html = str_replace ( "\xEF\xBB\xBF", '', file_get_contents ( $lib_file ) );

	if (is_file ( $lib_file ) === true && file_put_contents ( $lib_file, $html )) {
		file_put_contents ( DIR . '/templates/backup/library/' . $skyuc->options ['themes'] . '-' . $skyuc->GPC ['lib'] . '.lbi', $org_html );

		make_json_result ( '', $_LANG ['update_lib_success'] );
	} else {
		make_json_error ( sprintf ( $_LANG ['update_lib_failed'], 'templates/' . $skyuc->options ['themes'] . '/library' ) );
	}
}

/*------------------------------------------------------ */
//-- 还原库项目
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'restore_library') {

	$lib_name = $skyuc->input->clean_gpc ( 'g', 'lib', TYPE_STR );
	$lib_file = DIR . '/templates/' . $skyuc->options ['themes'] . '/library/' . $lib_name . '.lbi';
	$lib_file = str_replace ( "0xa", '', $lib_file ); // 过滤 0xa 非法字符
	$lib_backup = DIR . '/templates/backup/library/' . $skyuc->options ['themes'] . '-' . $lib_name . '.lbi';
	$lib_backup = str_replace ( "0xa", '', $lib_backup ); // 过滤 0xa 非法字符


	if (file_exists ( $lib_backup ) && filemtime ( $lib_backup ) >= filemtime ( $lib_file )) {
		make_json_result ( str_replace ( "\xEF\xBB\xBF", '', file_get_contents ( $lib_backup ) ) );
	} else {
		make_json_result ( str_replace ( "\xEF\xBB\xBF", '', file_get_contents ( $lib_file ) ) );
	}
}

/*------------------------------------------------------ */
//-- 布局备份
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'backup_setting') {
	$sql = 'SELECT DISTINCT(remarks) FROM ' . TABLE_PREFIX . 'template' . " WHERE theme = '" . $skyuc->options ['themes'] . "' AND remarks <> ''";
	$res = $skyuc->db->query_read ( $sql );
	/*
    $col = array();
    while ($row = $db->fetch_row($res))
    {
        $col[] = $row[0];
    }

    $remarks = array();
    foreach ($col as $val)
    {
        $remarks[] = array('content'=>$val, 'url'=>urlencode($val));
    }
*/
	$remarks = array ();
	while ( $row = $skyuc->db->fetch_row ( $res ) ) {
		$remarks [] = array ('content' => $row [0], 'url' => urlencode ( $row [0] ) );
	}

	$sql = 'SELECT DISTINCT(filename) FROM ' . TABLE_PREFIX . 'template' . " WHERE theme = '" . $skyuc->options ['themes'] . "' AND remarks = ''";
	$res = $skyuc->db->query_read ( $sql );
	/*    $col = array();
    while ($row = $db->fetch_row($res))
    {
        $col[] = $row[0];
    }
    $files = array();
    foreach ($col as $val)
    {
        $files[$val] = $_LANG['template_files'][$val];
    }*/
	$files = array ();
	while ( $row = $db->fetch_row ( $res ) ) {
		$files [$row [0]] = $_LANG ['template_files'] [$row [0]];
	}

	assign_query_info ();
	$smarty->assign ( 'ur_here', $_LANG ['backup_setting'] );
	$smarty->assign ( 'list', $remarks );
	$smarty->assign ( 'files', $files );
	$smarty->display ( 'templates_backup.tpl' );
} /*------------------------------------------------------ */
//-- 执行布局备份
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'act_backup_setting') {

	$skyuc->input->clean_array_gpc ( 'p', array ('remarks' => TYPE_STR, 'files' => TYPE_ARRAY_STR )

	 );
	$remarks = iif ( empty ( $skyuc->GPC ['remarks'] ), skyuc_date ( $skyuc->options ['date_format'] . ' ' . $skyuc->options ['time_format'], TIMENOW, TRUE, FALSE ), $skyuc->GPC ['remarks'] );

	if (empty ( $skyuc->GPC ['files'] )) {
		$files = array ();
	} else {
		$files = $skyuc->GPC ['files'];
	}

	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'template' . " WHERE remarks='$remarks' AND theme = '" . $skyuc->options ['themes'] . "'";
	$total = $skyuc->db->query_first ( $sql );
	if ($total ['total'] > 0) {
		sys_msg ( sprintf ( $_LANG ['remarks_exist'], $remarks ), 1 );
	}

	$sql = 'INSERT INTO ' . TABLE_PREFIX . 'template' . ' (filename, region, library, sort_order, id, number, type, theme, remarks)' . " SELECT filename, region, library, sort_order, id, number, type, theme, '$remarks'" . ' FROM ' . TABLE_PREFIX . 'template' . " WHERE remarks = '' AND theme = '" . $skyuc->options ['themes'] . "'" . ' AND ' . db_create_in ( $files, 'filename' );

	$skyuc->db->query_write ( $sql );
	sys_msg ( $_LANG ['backup_template_ok'], 0, array (array ('text' => $_LANG ['backup_setting'], 'href' => 'template.php?act=backup_setting' ) ) );
}

/*------------------------------------------------------ */
//-- 删除模板设置备分
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'del_backup') {
	$skyuc->input->clean_gpc ( 'g', 'remarks', TYPE_STR );
	$remarks = $skyuc->GPC ['remarks'];
	if ($skyuc->GPC_exists ['remarks']) {
		$sql = 'DELETE FROM ' . TABLE_PREFIX . 'template' . " WHERE remarks='$remarks' AND theme = '" . $skyuc->options ['themes'] . "'";
		$skyuc->db->query_write ( $sql );
	}
	sys_msg ( $_LANG ['del_backup_ok'], 0, array (array ('text' => $_LANG ['backup_setting'], 'href' => 'template.php?act=backup_setting' ) ) );
}

/*------------------------------------------------------ */
//-- 还原模板备分
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'restore_backup') {
	$skyuc->input->clean_gpc ( 'g', 'remarks', TYPE_STR );
	$remarks = $skyuc->GPC ['remarks'];
	if ($skyuc->GPC_exists ['remarks']) {
		$sql = 'SELECT filename, region, library, sort_order ' . ' FROM ' . TABLE_PREFIX . 'template' . " WHERE remarks='" . $skyuc->db->escape_string ( $remarks ) . "' AND theme = '" . $skyuc->options ['themes'] . "'" . ' ORDER BY filename, region, sort_order';
		$arr = $skyuc->db->query_all ( $sql );
		if ($arr) {
			$data = array ();
			foreach ( $arr as $val ) {
				$lib_content = file_get_contents ( DIR . '/templates/' . $skyuc->options ['themes'] . $val ['library'] );
				//去除lib头部
				$lib_content = preg_replace ( '/<meta\\shttp-equiv=["|\']Content-Type["|\']\\scontent=["|\']text\/html;\\scharset=(.*?)["|\']>/i', '', $lib_content );
				//去除utf bom
				$lib_content = str_replace ( "\xEF\xBB\xBF", '', $lib_content );
				//加入dw 标识
				$lib_content = '<!-- #BeginLibraryItem "' . $val ['library'] . "\" -->\r\n" . $lib_content . "\r\n" . '<!-- #EndLibraryItem -->';
				if (isset ( $data [$val ['filename']] [$val ['region']] )) {
					$data [$val ['filename']] [$val ['region']] .= $lib_content;
				} else {
					$data [$val ['filename']] [$val ['region']] = $lib_content;
				}
			}

			foreach ( $data as $file => $regions ) {
				$pattern = '/(?:<!--\\s*TemplateBeginEditable\\sname="(' . implode ( '|', array_keys ( $regions ) ) . ')"\\s*-->)(?:.*?)(?:<!--\\s*TemplateEndEditable\\s*-->)/se';
				$temple_file = DIR . '/templates/' . $skyuc->options ['themes'] . '/' . $file . '.dwt';
				$template_content = file_get_contents ( $temple_file );
				$match = array ();
				$template_content = preg_replace ( $pattern, "'<!-- TemplateBeginEditable name=\"\\1\" -->\r\n' . \$regions['\\1'] . '\r\n<!-- TemplateEndEditable -->';", $template_content );
				file_put_contents ( $temple_file, $template_content );
			}

			// 文件修改成功后，恢复数据库
			$sql = 'DELETE FROM ' . TABLE_PREFIX . 'template' . " WHERE remarks = '' AND  theme = '" . $skyuc->options ['themes'] . "'" . " AND " . db_create_in ( array_keys ( $data ), 'filename' );
			$skyuc->db->query_write ( $sql );
			$sql = 'INSERT INTO ' . TABLE_PREFIX . 'template' . ' (filename, region, library, sort_order, id, number, type, theme, remarks)' . " SELECT filename, region, library, sort_order, id, number, type, theme, '' " . ' FROM ' . TABLE_PREFIX . 'template' . " WHERE remarks = '" . $skyuc->db->escape_string ( $remarks ) . "' AND theme = '" . $skyuc->options ['themes'] . "'";
			$skyuc->db->query_write ( $sql );
		}
	}
	sys_msg ( $_LANG ['restore_backup_ok'], 0, array (array ('text' => $_LANG ['backup_setting'], 'href' => 'template.php?act=backup_setting' ) ) );
}

/**
 * SKYUC! 管理中心E-MAIL模版管理程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

/*------------------------------------------------------ */
//-- 邮件模版列表
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'mail') {

	// 获得所有邮件模板
	$sql = 'SELECT template_id, template_code FROM ' . TABLE_PREFIX . 'template_mail';
	$res = $db->query_read_slave ( $sql );
	$cur = null;

	while ( $row = $db->fetch_array ( $res ) ) {
		if ($cur == null) {
			$cur = $row ['template_id'];
		}

		$len = strlen ( $_LANG [$row ['template_code']] );
		$templates [$row ['template_id']] = $len < 18 ? $_LANG [$row ['template_code']] . str_repeat ( '&nbsp;', (18 - $len) / 2 ) . " [$row[template_code]]" : $_LANG [$row ['template_code']] . " [$row[template_code]]";
	}

	assign_query_info ();

	$smarty->assign ( 'ur_here', $_LANG ['04_template_mail'] );
	$smarty->assign ( 'templates', $templates );
	$smarty->assign ( 'template', load_template ( $cur ) );
	$smarty->display ( 'template_mail.tpl' );
}

/*------------------------------------------------------ */
//-- 载入指定邮件模版
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'loat_template') {
	$tpl = $skyuc->input->clean_gpc ( 'g', 'tpl', TYPE_UINT );

	make_json_result ( load_template ( $tpl ) );
}

/*------------------------------------------------------ */
//-- 保存邮件模板内容
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'save_template') {
	check_authz_json ( 'template_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('subject' => TYPE_STR, 'content' => TYPE_STR, 'is_html' => TYPE_BOOL, 'tpl' => TYPE_UINT ) );
	if (empty ( $skyuc->GPC ['subject'] )) {
		make_json_error ( $_LANG ['subject_empty'] );
	} else {
		$subject = $skyuc->GPC ['subject'];
	}

	if (empty ( $skyuc->GPC ['content'] )) {
		make_json_result ( $_LANG ['content_empty'] );
	} else {
		$content = $skyuc->GPC ['content'];
	}

	$type = $skyuc->GPC ['is_html'];
	$tpl_id = $skyuc->GPC ['tpl'];

	$sql = 'UPDATE ' . TABLE_PREFIX . 'template_mail' . ' SET ' . "template_subject = '" . $db->escape_string ( $subject ) . "', " . "template_content = '" . $db->escape_string ( $content ) . "', " . "is_html = '$type', " . 'last_modify = ' . TIMENOW . "  WHERE template_id='$tpl_id'";
	if ($db->query_write ( $sql )) {
		make_json_result ( '', $_LANG ['update_success'] );
	} else {
		make_json_error ( $_LANG ['update_failed'] . "\n" . $db->error () );
	}
}

?>