<?php
/**
 * SKYUC! 前台list.php私有函数库
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

/*------------------------------------------------------ */
//-- list.php影片列表页PRIVATE FUNCTION
/*------------------------------------------------------ */
/**
 * 获得分类的信息
 *
 * @param   integer $cat_id
 *
 * @return  void
 */
function get_cat_info($cat_id) {
	$sql = 'SELECT keywords, cat_desc, style, parent_id FROM ' . TABLE_PREFIX . 'category' . ' WHERE cat_id = ' . $cat_id;
	$key = md5 ( $sql ); //缓存名称：键
	//读缓存
	if ($data = get_file_cache ( $key )) {
		return $data;
	}
	$row = $GLOBALS ['db']->query_first_slave ( $sql );
	put_file_cache ( $key, $row ); //写缓存
	return $row;
}
/**
 * 获得分类下的影片总数
 *
 * @access  public
 * @param   string     $cat_id
 * @return  integer
 */
function get_cagtegory_show_count($children) {
	// 返回影片总数
	$where = ' m.is_show = 1 AND ( ' . $children . ' OR ' . get_extension_show ( $children ) . ')';
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'show' . ' AS m WHERE  ' . $where;
	$key = md5 ( $sql ); //缓存名称：键
	//读缓存
	if ($data = get_file_cache ( $key )) {
		return $data ['total'];
	}
	$total = $GLOBALS ['db']->query_first_slave ( $sql );
	put_file_cache ( $key, $total ); //写缓存
	return $total ['total'];
}
/**
 * 获得分类下的影片
 *
 * @access  public
 * @param   string  $children
 * @return  array
 */
function category_get_show($children, $size, $page, $sort, $order) {
	$where = ' m.is_show = 1 AND ( ' . $children . ' OR ' . get_extension_show ( $children ) . ')';

	// 获得影片列表
	$sql = 'SELECT m.show_id,	m.title,	m.thumb,	m.click_count,	m.actor,	m.director,	m.lang,	m.area,	m.runtime,	m.add_time,	m.description, m.status, m.pubdate  ' . ' FROM ' . TABLE_PREFIX . 'show' . ' AS m WHERE ' . $where . ' ORDER BY ' . $sort . ' ' . $order;
	$sql = $GLOBALS ['db']->query_limit ( $sql, $size, ($page - 1) * $size );

	$key = md5 ( $sql ); //缓存名称：键
	//读缓存
	if ($data = get_file_cache ( $key )) {
		return $data;
	}

	$res = $GLOBALS ['db']->query_read_slave ( $sql );
	$arr = array ();
	while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
		$row ['description'] = html2text ( $row ['description'] ); //去除影片看点中HTML代码
		// 修正影片图片
		$row ['thumb'] = get_image_path ( $row ['thumb'] );
		$row ['add_time'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $row ['add_time'] );
		//演员搜索链接
		$row ['actor'] = get_actor_array ( $row ['actor'] );

		$row ['url'] = build_uri ( 'show', array ('mid' => $row ['show_id'] ), $row ['title'] );
		$arr [] = $row;
	}
	put_file_cache ( $key, $arr ); //写缓存
	return $arr;
}

/**
 * 获得所有扩展分类属于指定分类的所有影片ID
 *
 * @access  public
 * @param   string $cat_id     分类查询字符串
 * @return  string
 */
function get_extension_show($cats) {
	static $extension_show_array = '';
	if ($extension_show_array !== '') {
		return db_create_in ( $extension_show_array, 'm.show_id' );
	} else {
		$sql = 'SELECT show_id FROM ' . TABLE_PREFIX . 'show_cat' . ' AS m WHERE ' . $cats;
		$key = md5 ( $sql ); //缓存名称：键
		//读缓存
		if ($data = get_file_cache ( $key )) {
			$extension_show_array = $data;
		} else {
			$res = $GLOBALS ['db']->query_read_slave ( $sql );
			while ( $row = $GLOBALS ['db']->fetch_row ( $res ) ) {
				$extension_show_array [] = $row [0];
			}
			if (empty ( $extension_show_array )) {
				$extension_show_array = array ('0'); //防止没有扩展分类时，重复读写缓存
			}
			put_file_cache ( $key, $extension_show_array ); //写缓存
		}
		return db_create_in ( $extension_show_array, 'm.show_id' );
	}
}
