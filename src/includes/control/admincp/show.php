<?php
// #######################################################################
// ######################## show.php 私有函数    #########################
// #######################################################################


/**
 * 获得影片地区/语言的列表,返回下拉列表
 *
 * @param   string     $key   地区或语言
 * @param   string    $selected   影片信息中的值
 * @return  string
 */
function select_area_lang($key, $selected) {
	
	$arr = explode ( '|', $key );
	
	if (! is_array ( $arr )) {
		return '';
	}
	
	$lst = '';
	foreach ( $arr as $v ) {
		$lst .= "<option value='$v'";
		$lst .= ($selected == "$v") ? ' selected="true"' : '';
		$lst .= '>' . htmlspecialchars ( $v ) . '</option>';
	}
	
	return $lst;
}

/**
 * 获得影片主演/导演的可选列表,返回下拉列表
 *
 * @param   string     $key   主演或导演
 * @return  string
 */
function select_actor_director($key) {
	
	$arr = explode ( '|', $key );
	
	if (! is_array ( $arr )) {
		return '';
	}
	
	$lst = '';
	foreach ( $arr as $v ) {
		$lst .= "<option value='$v'";
		$lst .= ($selected == "$v") ? ' selected="true"' : '';
		$lst .= '>' . htmlspecialchars ( $v ) . '</option>';
	}
	
	return $lst;
}

/**
 * 保存某影片的扩展分类
 * @param   int     $show_id   影片编号
 * @param   array   $cat_list   分类编号数组
 * @return  void
 */
function handle_other_cat($show_id, $cat_list) {
	
	// 查询现有的扩展分类
	$sql = 'SELECT cat_id FROM ' . TABLE_PREFIX . 'show_cat' . ' WHERE show_id = ' . $show_id;
	$exist_list = array ();
	$res = $GLOBALS ['db']->query_read ( $sql );
	while ( $row = $GLOBALS ['db']->fetch_row ( $res ) ) {
		$exist_list [] = $row [0];
	}
	
	// 删除不再有的分类
	$delete_list = array_diff ( $exist_list, $cat_list );
	if ($delete_list) {
		$sql = 'DELETE FROM ' . TABLE_PREFIX . 'show_cat' . ' WHERE show_id = ' . $show_id . ' AND cat_id ' . db_create_in ( $delete_list );
		$GLOBALS ['db']->query_write ( $sql );
	}
	
	// 添加新加的分类
	$add_list = array_diff ( $cat_list, $exist_list, array (0 ) );
	foreach ( $add_list as $cat_id ) {
		// 插入记录
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'show_cat' . ' (show_id, cat_id) ' . "	VALUES ('" . $show_id . "', '" . $cat_id . "')";
		$GLOBALS ['db']->query_write ( $sql );
	}
}

/**
 * 从回收站删除多个影片
 * @param   mix     $show_id   影片id列表：可以逗号格开，也可以是数组
 * @return  void
 */
function delete_show($show_id) {
	
	if (empty ( $show_id )) {
		return;
	}
	
	require (DIR . '/includes/functions_ftp.php');
	require (DIR . '/includes/functions_log_error.php');
	
	// 取得有效影片id
	$sql = 'SELECT DISTINCT show_id FROM ' . TABLE_PREFIX . 'show' . ' WHERE show_id ' . db_create_in ( $show_id ) . ' AND is_show = 0';
	$show_id = array ();
	$res = $GLOBALS ['db']->query_read ( $sql );
	while ( $row = $GLOBALS ['db']->fetch_row ( $res ) ) {
		$show_id [] = $row [0];
	}
	
	if (empty ( $show_id )) {
		return;
	}
	
	// 删除影片图片文件
	$sql = 'SELECT thumb, image, source ' . ' FROM ' . TABLE_PREFIX . 'show' . ' WHERE show_id ' . db_create_in ( $show_id );
	$res = $GLOBALS ['db']->query_read ( $sql );
	while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
		if (! empty ( $row ['thumb'] )) {
			ftpdelete ( $row ['thumb'] );
			@unlink ( DIR . '/' . $row ['thumb'] );
		}
		if (! empty ( $row ['image'] )) {
			ftpdelete ( $row ['image'] );
			@unlink ( DIR . '/' . $row ['image'] );
		}
		if (! empty ( $row ['source'] )) {
			ftpdelete ( $row ['source'] );
			@unlink ( DIR . '/' . $row ['source'] );
		}
	}
	
	//退出FTP
	if ($ftp ['connid']) {
		@ftp_close ( $ftp ['connid'] );
	}
	$ftp = array ();
	
	// 删除影片
	$sql = 'DELETE FROM ' . TABLE_PREFIX . 'show' . ' WHERE show_id ' . db_create_in ( $show_id );
	$GLOBALS ['db']->query_write ( $sql );
	
	// 删除相关表记录
	$sql = 'DELETE FROM ' . TABLE_PREFIX . 'show_cat' . ' WHERE show_id ' . db_create_in ( $show_id );
	$GLOBALS ['db']->query_write ( $sql );
	
	// 清除缓存
	$GLOBALS ['skyuc']->secache->setModified ( array ('index.dwt', 'list.dwt', 'show.dwt' ) );
}

/**
 * 修改影片某字段值
 * @param   string  $show_id   影片编号，可以为多个，用 ',' 隔开
 * @param   string  $field      字段名
 * @param   int  		$value      字段值
 * @return  bool
 */
function update_show($show_id, $field, $value) {
	
	if ($show_id) {
		// 清除缓存
		$cachename = 'show_' . sprintf ( '%X', crc32 ( $show_id ) );
		$GLOBALS ['skyuc']->secache->setModified ( md5 ( $cachename ) );
		
		$sql = 'UPDATE ' . TABLE_PREFIX . 'show' . " SET $field = '" . $value . "'  WHERE show_id " . db_create_in ( $show_id );
		
		return $GLOBALS ['db']->query_write ( $sql );
	} else {
		return false;
	}
}

?>