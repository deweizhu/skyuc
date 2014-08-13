<?php
// #######################################################################
// ######################## navigator.php 私有函数      #####################
// #######################################################################


/*------------------------------------------------------ */
//-- 获取导航栏
/*------------------------------------------------------ */
function get_nav() {
	
	$GLOBALS ['skyuc']->input->clean_array_gpc ( 'r', array ('sort_by' => TYPE_STR, 'sort_order' => TYPE_UINT ) );
	
	$result = get_filter ();
	if ($result === false) {
		$filter ['sort_by'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_by'] ), 'type DESC, vieworder', 'type DESC, ' . $GLOBALS ['skyuc']->GPC ['sort_by'] );
		$filter ['sort_order'] = iif ( empty ( $GLOBALS ['skyuc']->GPC ['sort_order'] ), 'ASC', $GLOBALS ['skyuc']->GPC ['sort_order'] );
		
		$sql = "SELECT count(*) AS total FROM " . TABLE_PREFIX . 'nav';
		$total = $GLOBALS ['db']->query_first_slave ( $sql );
		$filter ['record_count'] = $total ['total'];
		
		//分页大小
		$filter = page_and_size ( $filter );
		
		// 查询
		$sql = 'SELECT id, name, ifshow, vieworder, opennew, url, type' . ' FROM ' . TABLE_PREFIX . 'nav' . ' ORDER by ' . $filter ['sort_by'] . ' ' . $filter ['sort_order'];
		$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
		set_filter ( $filter, $sql );
	} else {
		$sql = $result ['sql'];
		$filter = $result ['filter'];
	}
	
	$navdb = $GLOBALS ['db']->query_all_slave ( $sql );
	
	$type = "";
	$navdb2 = array ();
	foreach ( $navdb as $k => $v ) {
		if (! empty ( $type ) && $type != $v ['type']) {
			$navdb2 [] = array ();
		}
		$navdb2 [] = $v;
		$type = $v ['type'];
	}
	
	$arr = array ('navdb' => $navdb2, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
	
	return $arr;
}

/*------------------------------------------------------ */
//-- 排序相关
/*------------------------------------------------------ */
function sort_nav($a, $b) {
	return $a ['vieworder'] > $b ['vieworder'] ? 1 : - 1;
}

/*------------------------------------------------------ */
//-- 获得系统列表
/*------------------------------------------------------ */
function get_sysnav() {
	
	$sysmain = array (array ($GLOBALS ['_LANG'] ['user_center'], 'user.php' ), array ($GLOBALS ['_LANG'] ['netbar_center'], 'netbar.php' ), array ($GLOBALS ['_LANG'] ['message_board'], 'message.php' ) );
	
	$sysmain [] = array ('-', '-' );
	
	$catlist = array_merge ( get_cat_list ( 0, 0, false ), array ('-' ), article_cat_list ( 0, 0, false ) );
	foreach ( $catlist as $key => $val ) {
		$val ['view_name'] = $val ['cat_name'];
		for($i = 0; $i < $val ['level']; $i ++) {
			$val ['view_name'] = '&nbsp;&nbsp;&nbsp;&nbsp;' . $val ['view_name'];
		}
		$val ['url'] = str_replace ( '&amp;', '&', $val ['url'] );
		$val ['url'] = str_replace ( '&', '&amp;', $val ['url'] );
		$sysmain [] = array ($val ['cat_name'], $val ['url'], $val ['view_name'] );
	}
	return $sysmain;
}

/*------------------------------------------------------ */
//-- 根据URI对导航栏项目进行分析，确定其为影片分类还是文章分类
/*------------------------------------------------------ */
function analyse_uri($uri) {
	$uri = strtolower ( str_replace ( '&amp;', '&', $uri ) );
	$arr = explode ( '-', $uri );
	switch ($arr [0]) {
		case 'category' :
			return array ('type' => 'c', 'id' => $arr [1] );
			break;
		case 'article_cat' :
			return array ('type' => 'a', 'id' => $arr [1] );
			break;
		default :
			
			break;
	}
	
	list ( $fn, $pm ) = explode ( '?', $uri );
	
	if (strpos ( $uri, '&' ) === FALSE) {
		$arr = array ($pm );
	} else {
		$arr = explode ( '&', $pm );
	}
	switch ($fn) {
		case 'list.php' :
			//影片分类
			foreach ( $arr as $k => $v ) {
				list ( $key, $val ) = explode ( '=', $v );
				if ($key == 'id') {
					return array ('type' => 'c', 'id' => $val );
				}
			}
			break;
		case 'article_cat.php' :
			//文章分类
			foreach ( $arr as $k => $v ) {
				list ( $key, $val ) = explode ( '=', $v );
				if ($key == 'id') {
					return array ('type' => 'a', 'id' => $val );
				}
			}
			break;
		default :
			//未知
			return false;
			break;
	}

}

/*------------------------------------------------------ */
//-- 是否显示
/*------------------------------------------------------ */
function is_show_in_nav($type, $id) {
	
	if ($type == 'c') {
		$tablename = TABLE_PREFIX . 'category';
	} else {
		$tablename = TABLE_PREFIX . 'article_cat';
	}
	$nav = $GLOBALS ['db']->query_first_slave ( "SELECT show_in_nav FROM $tablename WHERE cat_id = '$id'" );
	return $nav ['show_in_nav'];
}

/*------------------------------------------------------ */
//-- 设置是否显示
/*------------------------------------------------------ */
function set_show_in_nav($type, $id, $val) {
	
	if ($type == 'c') {
		$tablename = TABLE_PREFIX . 'category';
	} else {
		$tablename = TABLE_PREFIX . 'article_cat';
	}
	$GLOBALS ['db']->query_write ( "UPDATE $tablename SET show_in_nav = '$val' WHERE cat_id = '$id'" );
	$GLOBALS ['skyuc']->secache->setModified ( array ('index.dwt', 'list.dwt' ) );
}
?>