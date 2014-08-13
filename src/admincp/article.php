<?php

/**
 * SKYUC! 管理中心文章处理程序文件
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

/*初始化数据交换对象 */
$exc = new exchange ( TABLE_PREFIX . 'article', $skyuc->db, 'article_id', 'title' );
// 允许上传的文件类型
$allow_file_types = '|GIF|JPG|PNG|BMP|SWF|DOC|XLS|PPT|MID|WAV|ZIP|RAR|PDF|CHM|RM|TXT|';

//$specialtemplates = array('category');


//build_servers();
/*------------------------------------------------------ */
//-- 文章列表
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list') {
	// 取得过滤条件
	$filter = array ();
	$smarty->assign ( 'cat_select', article_cat_list ( 0 ) );
	$smarty->assign ( 'ur_here', $_LANG ['03_article_list'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['article_add'], 'href' => 'article.php?act=add' ) );
	$smarty->assign ( 'full_page', 1 );
	$smarty->assign ( 'filter', $filter );

	$article_list = get_articleslist ();

	$smarty->assign ( 'article_list', $article_list ['arr'] );
	$smarty->assign ( 'filter', $article_list ['filter'] );
	$smarty->assign ( 'record_count', $article_list ['record_count'] );
	$smarty->assign ( 'page_count', $article_list ['page_count'] );

	$sort_flag = sort_flag ( $article_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	assign_query_info ();
	$smarty->display ( 'article_list.tpl' );
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	check_authz_json ( 'article_manage' );

	$article_list = get_articleslist ();

	$smarty->assign ( 'article_list', $article_list ['arr'] );
	$smarty->assign ( 'filter', $article_list ['filter'] );
	$smarty->assign ( 'record_count', $article_list ['record_count'] );
	$smarty->assign ( 'page_count', $article_list ['page_count'] );

	$sort_flag = sort_flag ( $article_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	make_json_result ( $smarty->fetch ( 'article_list.tpl' ), '', array ('filter' => $article_list ['filter'], 'page_count' => $article_list ['page_count'] ) );
}

/*------------------------------------------------------ */
//-- 添加文章
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'add') {
	// 权限判断
	admin_priv ( 'article_manage' );

	// 创建 html editor
	create_html_editor ( 'article_content' );

	$skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	// 初始化
	$article = array ();
	$article ['is_open'] = 1;

	// 取得分类、服务器
	$smarty->assign ( 'show_cat_list', get_cat_list () );
	$smarty->assign ( 'server_list', get_server_list () );

	// 清理关联影片
	$sql = 'DELETE FROM ' . TABLE_PREFIX . 'show_article' . ' WHERE article_id = 0';
	$skyuc->db->query_write ( $sql );

	if ($skyuc->GPC_exists ['id']) {
		$smarty->assign ( 'cur_id', $skyuc->GPC ['id'] );
	}
	$smarty->assign ( 'article', $article );
	$smarty->assign ( 'cat_select', article_cat_list ( 0 ) );
	$smarty->assign ( 'ur_here', $_LANG ['article_add'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['03_article_list'], 'href' => 'article.php?act=list' ) );
	$smarty->assign ( 'form_action', 'insert' );

	assign_query_info ();
	$smarty->display ( 'article_info.tpl' );
}

/*------------------------------------------------------ */
//-- 添加文章
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'insert') {
	// 权限判断
	admin_priv ( 'article_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('title' => TYPE_STR, 'file_url' => TYPE_STR, 'article_content' => TYPE_STR, 'cat_id' => TYPE_UINT, 'title' => TYPE_STR, 'article_cat' => TYPE_UINT, 'article_type' => TYPE_BOOL, 'is_open' => TYPE_BOOL, 'author' => TYPE_STR, 'author_email' => TYPE_STR, 'keywords' => TYPE_STR, 'link_url' => TYPE_STR ) );

	// 检查是否重复
	$is_only = $exc->is_only ( 'title', $skyuc->GPC ['title'] );

	if (! $is_only) {
		sys_msg ( sprintf ( $_LANG ['title_exist'], $skyuc->GPC ['title'] ), 1 );
	}

	// 取得文件地址
	$file_url = '';
	if ((isset ( $_FILES ['file'] ['error'] ) && $_FILES ['file'] ['error'] == 0) || (! isset ( $_FILES ['file'] ['error'] ) && $_FILES ['file'] ['tmp_name'] != 'none')) {
		// 检查文件格式
		if (! check_file_type ( $_FILES ['file'] ['tmp_name'], $_FILES ['file'] ['name'], $allow_file_types )) {
			sys_msg ( $_LANG ['invalid_file'] );
		}

		// 复制文件
		$res = upload_file ( $_FILES ['file'], 'ar' );
		if ($res != false) {
			$file_url = $res;
		}
	}

	if ($file_url == '') {
		$file_url = $skyuc->GPC ['file_url'];
	}

	// 计算文章打开方式
	if ($file_url == '') {
		$open_type = 0;
	} else {
		$open_type = $skyuc->GPC ['article_content'] == '' ? 1 : 2;
	}

	//插入数据
	$sql = 'INSERT INTO ' . TABLE_PREFIX . 'article' . '(title, cat_id, article_type, is_open, author, author_email, keywords, content, add_time, file_url, open_type, link) ' . " VALUES ('" . $db->escape_string ( $skyuc->GPC ['title'] ) . "', '" . $skyuc->GPC ['article_cat'] . "', '" . $skyuc->GPC ['article_type'] . "', '" . $skyuc->GPC ['is_open'] . "', '" . $db->escape_string ( $skyuc->GPC ['author'] ) . "', '" . $db->escape_string ( $skyuc->GPC ['author_email'] ) . "', '" . $db->escape_string ( $skyuc->GPC ['keywords'] ) . "', '" . $db->escape_string ( $skyuc->GPC ['article_content'] ) . "', '" . TIMENOW . "', '" . $db->escape_string ( $file_url ) . "', '$open_type', '" . $db->escape_string ( $skyuc->GPC ['link_url'] ) . "')";
	$skyuc->db->query_write ( $sql );

	// 处理关联影片
	$article_id = $db->insert_id ();
	$sql = 'UPDATE ' . TABLE_PREFIX . 'show_article' . " SET article_id = '$article_id' WHERE article_id = 0";
	$skyuc->db->query_write ( $sql );

	$link [0] ['text'] = $_LANG ['continue_add'];
	$link [0] ['href'] = 'article.php?act=add';

	$link [1] ['text'] = $_LANG ['back_list'];
	$link [1] ['href'] = 'article.php?act=list';

	admin_log ( $skyuc->GPC ['title'], 'add', 'article' );

	$skyuc->secache->setModified ( array ('index.dwt', 'article.dwt', 'article_cat.dwt' ) );

	sys_msg ( $_LANG ['articleadd_succeed'], 0, $link );
}

/*------------------------------------------------------ */
//-- 编辑
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'edit') {
	// 权限判断
	admin_priv ( 'article_manage' );

	$skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	// 取文章数据
	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'article' . ' WHERE article_id=' . $skyuc->GPC ['id'];
	$article = $db->query_first_slave ( $sql );

	// 创建 html editor
	create_html_editor ( 'article_content', $article ['content'] );

	// 取得分类、服务器
	$smarty->assign ( 'show_cat_list', get_cat_list () );
	$smarty->assign ( 'server_list', get_server_list () );

	// 取得关联影片
	$show_list = get_article_show ( $skyuc->GPC ['id'] );
	$smarty->assign ( 'show_list', $show_list );

	$smarty->assign ( 'article', $article );
	$smarty->assign ( 'cat_select', article_cat_list ( 0, $article ['cat_id'] ) );
	$smarty->assign ( 'ur_here', $_LANG ['article_edit'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['03_article_list'], 'href' => 'article.php?act=list' ) );
	$smarty->assign ( 'form_action', 'update' );

	assign_query_info ();
	$smarty->display ( 'article_info.tpl' );
}

if ($skyuc->GPC ['act'] == 'update') {
	// 权限判断
	admin_priv ( 'article_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'title' => TYPE_STR, 'old_title' => TYPE_STR, 'file_url' => TYPE_STR, 'article_content' => TYPE_STR, 'cat_id' => TYPE_UINT, 'title' => TYPE_STR, 'article_cat' => TYPE_UINT, 'article_type' => TYPE_BOOL, 'is_open' => TYPE_BOOL, 'author' => TYPE_STR, 'author_email' => TYPE_STR, 'keywords' => TYPE_STR, 'article_content' => TYPE_STR, 'link_url' => TYPE_STR ) );

	if ($skyuc->GPC ['title'] != $skyuc->GPC ['old_title']) {
		//检查文章名是否相同
		$is_only = $exc->is_only ( 'title', $skyuc->GPC ['title'], $skyuc->GPC ['id'], "cat_id = '" . $skyuc->GPC ['article_cat'] . "'" );

		if (! $is_only) {
			sys_msg ( sprintf ( $_LANG ['title_exist'], $skyuc->GPC ['title'] ), 1 );
		}
	}

	// 取得文件地址
	$file_url = '';
	if (empty ( $_FILES ['file'] ['error'] ) || (! isset ( $_FILES ['file'] ['error'] ) && $_FILES ['file'] ['tmp_name'] != 'none')) {
		// 检查文件格式
		if (! check_file_type ( $_FILES ['file'] ['tmp_name'], $_FILES ['file'] ['name'], $allow_file_types )) {
			sys_msg ( $_LANG ['invalid_file'] );
		}

		// 复制文件
		$res = upload_file ( $_FILES ['file'], 'ar' );
		if ($res != false) {
			$file_url = $res;
		}
	}

	if ($file_url == '') {
		$file_url = $skyuc->GPC ['file_url'];
	}

	// 计算文章打开方式
	if ($file_url == '') {
		$open_type = 0;
	} else {
		$open_type = $skyuc->GPC ['article_content'] == '' ? 1 : 2;
	}

	// 如果 file_url 跟以前不一样，且原来的文件是本地文件，删除原来的文件
	$sql = 'SELECT file_url FROM ' . TABLE_PREFIX . 'article' . ' WHERE article_id = ' . $skyuc->GPC ['id'];
	$old_file_url = $skyuc->db->query_first ( $sql );
	$old_url = $old_file_url ['file_url'];
	if ($old_url != '' && $old_url != $file_url && strpos ( $old_url, 'http://' ) === false && strpos ( $old_url, 'https://' ) === false) {
		@unlink ( DIR . '/' . $old_url );
	}

	if ($exc->edit ( "title='" . $skyuc->GPC ['title'] . "', cat_id='" . $skyuc->GPC ['article_cat'] . "', article_type='" . $skyuc->GPC ['article_type'] . "', is_open='" . $skyuc->GPC ['is_open'] . "', author='" . $skyuc->db->escape_string ( $skyuc->GPC ['author'] ) . "', author_email='" . $skyuc->db->escape_string ( $skyuc->GPC ['author_email'] ) . "', keywords ='" . $skyuc->db->escape_string ( $skyuc->GPC ['keywords'] ) . "', file_url ='" . $skyuc->db->escape_string ( $file_url ) . "', open_type='$open_type', content='" . $skyuc->db->escape_string ( $skyuc->GPC ['article_content'] ) . "', link='" . $skyuc->db->escape_string ( $skyuc->GPC ['link_url'] ) . "', add_time = '" . TIMENOW . "' ", $skyuc->GPC ['id'] )) {
		$link [0] ['text'] = $_LANG ['back_list'];
		$link [0] ['href'] = 'article.php?act=list';

		$note = sprintf ( $_LANG ['articleedit_succeed'], $skyuc->GPC ['title'] );
		admin_log ( $skyuc->GPC ['title'], 'edit', 'article' );

		$skyuc->secache->setModified ( array ('index.dwt', 'article.dwt', 'article_cat.dwt' ) );

		sys_msg ( $note, 0, $link );
	} else {
		die ( $skyuc->db->error () );
	}
}

/*------------------------------------------------------ */
//-- 编辑文章主题
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_title') {
	check_authz_json ( 'article_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$id = $skyuc->GPC ['id'];
	$title = $skyuc->GPC ['val'];

	// 检查文章标题是否重复
	if ($exc->num ( 'title', $title, $id ) != 0) {
		make_json_error ( sprintf ( $_LANG ['title_exist'], $title ) );
	} else {
		if ($exc->edit ( "title = '" . $skyuc->db->escape_string ( $title ) . "'", $id )) {
			$skyuc->secache->setModified ( array ('index.dwt', 'article.dwt', 'article_cat.dwt' ) );
			admin_log ( $title, 'edit', 'article' );
			make_json_result ( $title );
		} else {
			make_json_error ( $skyuc->db->error () );
		}
	}
}

/*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'toggle_show') {
	check_authz_json ( 'article_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	$id = $skyuc->GPC ['id'];
	$val = $skyuc->GPC ['val'];

	$exc->edit ( "is_open = '$val'", $id );
	$skyuc->secache->setModified ( array ('index.dwt', 'article.dwt', 'article_cat.dwt' ) );

	make_json_result ( $val );
}

/*------------------------------------------------------ */
//-- 切换文章重要性
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'toggle_type') {
	check_authz_json ( 'article_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_UINT ) );

	$id = $skyuc->GPC ['id'];
	$val = $skyuc->GPC ['val'];

	$exc->edit ( "article_type = '$val'", $id );
	$skyuc->secache->setModified ( array ('index.dwt', 'article.dwt', 'article_cat.dwt' ) );

	make_json_result ( $val );
}

/*------------------------------------------------------ */
//-- 批量删除文章
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'batch_remove') {
	admin_priv ( 'article_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('checkboxes' => TYPE_ARRAY_UINT ) );

	if (! $skyuc->GPC_exists ['checkboxes'] || ! is_array ( $skyuc->GPC ['checkboxes'] )) {
		sys_msg ( $_LANG ['no_select_article'], 1 );
	}

	// 删除原来的文件
	$sql = 'SELECT file_url FROM ' . TABLE_PREFIX . 'article' . ' WHERE article_id ' . db_create_in ( join ( ',', $skyuc->GPC ['checkboxes'] ) ) . " AND file_url <> ''";
	$res = $skyuc->db->query_read_slave ( $sql );
	while ( $row = $db->fetch_array ( $res ) ) {
		$old_url = $row ['file_url'];
		if (strpos ( $old_url, 'http://' ) === false && strpos ( $old_url, 'https://' ) === false) {
			@unlink ( DIR . '/' . $old_url );
		}
	}

	$count = 0;
	foreach ( $skyuc->GPC ['checkboxes'] as $key => $id ) {
		if ($exc->drop ( $id )) {
			$name = $exc->get_name ( $id );
			admin_log ( $name, 'remove', 'article' );

			$count ++;
		}
	}

	$lnk [] = array ('text' => $_LANG ['back_list'], 'href' => 'article.php?act=list' );
	sys_msg ( sprintf ( $_LANG ['batch_remove_succeed'], $count ), 0, $lnk );
}

/*------------------------------------------------------ */
//-- 删除文章主题
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'remove') {
	check_authz_json ( 'article_manage' );

	$skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	// 删除原来的文件
	$sql = 'SELECT file_url FROM ' . TABLE_PREFIX . 'article' . ' WHERE article_id = ' . $skyuc->GPC ['id'];
	$old_file_url = $db->query_first ( $sql );
	$old_url = $old_file_url ['file_url'];
	if ($old_url != '' && strpos ( $old_url, 'http://' ) === false && strpos ( $old_url, 'https://' ) === false) {
		@unlink ( DIR . '/' . $old_url );
	}

	$name = $exc->get_name ( $skyuc->GPC ['id'] );
	if ($exc->drop ( $skyuc->GPC ['id'] )) {
		admin_log ( $name, 'remove', 'article' );
		$skyuc->secache->setModified ( array ('index.dwt', 'article.dwt', 'article_cat.dwt' ) );
	}

	$url = 'article.php?act=query&' . str_replace ( 'act=remove', '', $_SERVER ['QUERY_STRING'] );

	header ( "Location: $url\n" );
	exit ();
}

/*------------------------------------------------------ */
//-- 将影片加入关联
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'add_link_show') {
	check_authz_json ( 'article_manage' );

	include_once (DIR . '/includes/class_json.php');
	$json = new JSON ();

	//传递的是JSON字符串，因此不过滤
	$add_ids = $json->decode ( $_GET ['add_ids'] );
	$args = $json->decode ( $_GET ['JSON'] );
	$article_id = $args [0];

	if ($article_id == 0) {
		$sql = 'SELECT MAX(article_id)+1 AS article_id FROM ' . TABLE_PREFIX . 'article';
		$article = $skyuc->db->query_first ( $sql );
		$article_id = $article ['article_id'];
	}

	foreach ( $add_ids as $key => $val ) {
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'show_article' . ' (show_id, article_id) ' . "VALUES ('$val', '$article_id')";
		$skyuc->db->query_write ( $sql );
	}

	//重新载入
	$arr = get_article_show ( $article_id );
	$opt = array ();

	foreach ( $arr as $key => $val ) {
		$opt [] = array ('value' => $val ['show_id'], 'text' => $val ['title'], 'data' => '' );
	}

	make_json_result ( $opt );
}

/*------------------------------------------------------ */
//-- 将影片删除关联
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'drop_link_show') {
	check_authz_json ( 'article_manage' );

	include_once (DIR . '/includes/class_json.php');
	$json = new JSON ();

	$drop_show = $json->decode ( $_GET ['drop_ids'] );
	$arguments = $json->decode ( $_GET ['JSON'] );
	$article_id = $arguments [0];

	if ($article_id == 0) {
		$sql = 'SELECT MAX(article_id)+1 AS article_id FROM ' . TABLE_PREFIX . 'article';
		$article = $skyuc->db->query_first ( $sql );
		$article_id = $article ['article_id'];
	}

	$sql = 'DELETE FROM ' . TABLE_PREFIX . 'show_article' . " WHERE article_id = '$article_id' AND show_id " . db_create_in ( $drop_show );
	$skyuc->db->query_write ( $sql );

	// 重新载入
	$arr = get_article_show ( $article_id );
	$opt = array ();

	foreach ( $arr as $key => $val ) {
		$opt [] = array ('value' => $val ['show_id'], 'text' => $val ['title'], 'data' => '' );
	}

	make_json_result ( $opt );
}

/*------------------------------------------------------ */
//-- 搜索影片
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'get_show_list') {
	include_once (DIR . '/includes/class_json.php');
	$json = new JSON ();

	$filters = $json->decode ( $_GET ['JSON'] );
	$arr = get_show_article ( $filters );
	$opt = array ();

	foreach ( $arr as $key => $val ) {
		$opt [] = array ('value' => $val ['show_id'], 'text' => $val ['title'], 'data' => '' );
	}

	make_json_result ( $opt );
}

?>
