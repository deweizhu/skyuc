<?php
/**
 * SKYUC! 管理中心语言项编辑(前台语言项)
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

/*------------------------------------------------------ */
//-- 列表编辑 ?act=list
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list' || $skyuc->GPC ['act'] == '') {
	//从languages目录下获取语言项文件
	$lang_arr = array ();
	$lang_path = DIR . '/languages/' . $skyuc->options ['lang'];
	$lang_dir = opendir ( $lang_path );

	while ( $file = readdir ( $lang_dir ) ) {
		if (substr ( $file, - 3 ) == 'php') {
			$filename = substr ( $file, 0, - 4 );
			$lang_arr [$filename] = $file . ' - ' . @$_LANG ['language_files'] [$filename];
		}
	}

	ksort ( $lang_arr );
	@closedir ( $lang_dir );

	//获得需要操作的语言包文件
	$skyuc->input->clean_array_gpc ( 'p', array ('lang_file' => TYPE_STR, 'keyword' => TYPE_STR ) );

	$lang_file = $skyuc->GPC ['lang_file'];
	if ($lang_file == 'common') {
		$file_path = DIR . '/languages/' . $skyuc->options ['lang'] . '/common.php';
	} elseif ($lang_file == 'user') {
		$file_path = DIR . '/languages/' . $skyuc->options ['lang'] . '/user.php';
	} else {
		$file_path = DIR . '/languages/' . $skyuc->options ['lang'] . '/common.php';
	}

	$file_attr = '';
	if (file_mode_info ( $file_path ) < 7) {
		$file_attr = $lang_file . '.php：' . $_LANG ['file_attribute'];
	}

	//搜索的关键字
	$keyword = $skyuc->GPC ['keyword'];

	// 调用函数
	$language_arr = get_language_item_list ( $file_path, $keyword );

	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['edit_languages'] );
	$smarty->assign ( 'keyword', $keyword ); //关键字
	$smarty->assign ( 'action_link', array () );
	$smarty->assign ( 'file_attr', $file_attr ); //文件权限
	$smarty->assign ( 'lang_arr', $lang_arr ); //语言文件列表
	$smarty->assign ( 'file_path', $file_path ); //语言文件
	$smarty->assign ( 'lang_file', $lang_file ); //语言文件
	$smarty->assign ( 'language_arr', $language_arr ); //需要编辑的语言项列表


	assign_query_info ();
	$smarty->display ( 'language_list.tpl' );
}

/*------------------------------------------------------ */
//-- 编辑语言项
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit') {
	// 语言项的路径
	$skyuc->input->clean_array_gpc ( 'p', array ('file_path' => TYPE_STR, 'item' => TYPE_ARRAY_STR, 'item_id' => TYPE_ARRAY_STR, 'item_content' => TYPE_ARRAY_STR ) );

	$lang_file = $skyuc->GPC ['file_path'];

	// 替换前的语言项
	$src_items = $skyuc->GPC ['item'];

	// 修改过后的语言项
	$dst_items = array ();
	$item_count = count ( $skyuc->GPC ['item_id'] );

	for($i = 0; $i < $item_count; $i ++) {
		// 语言项内容如果为空，不修改
		if ($skyuc->GPC ['item_content'] [$i] == '') {
			unset ( $src_items [$i] );
		} else {
			$skyuc->GPC ['item_content'] [$i] = str_replace ( array ('\\\\n', '"', '\'' ), array ('\\n', '', '' ), $skyuc->GPC ['item_content'] [$i] );
			$dst_items [$i] = $skyuc->GPC ['item_id'] [$i] . ' = ' . '"' . $skyuc->GPC ['item_content'] [$i] . '";';
		}
	}

	// 调用函数编辑语言项
	$result = set_language_items ( $lang_file, $src_items, $dst_items );

	if ($result === false) {
		// 修改失败提示信息
		$link [] = array ('text' => $_LANG ['back_list'], 'href' => 'javascript:history.back(-1)' );
		sys_msg ( $_LANG ['edit_languages_false'], 0, $link );
	} else {
		// 记录管理员操作
		admin_log ( '', 'edit', 'languages' );

		// 清除缓存
		$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', 'show.dwt' ) );

		// 成功提示信息
		$link [] = array ('text' => $_LANG ['back_list'], 'href' => 'edit_languages.php?act=list' );
		sys_msg ( $_LANG ['edit_languages_success'], 0, $link );
	}
}

?>