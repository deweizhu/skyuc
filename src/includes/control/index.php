<?php
/**
 * SKYUC! 前台index.php私有函数库
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

/*------------------------------------------------------ */
//-- index.php首页PRIVATE FUNCTIONS
/*------------------------------------------------------ */

/**
 * 获得所有的友情链接
 *
 * @access  private
 * @return  array
 */
function index_get_links() {

	$sql = 'SELECT link_logo, link_name, link_url FROM ' . TABLE_PREFIX . 'friend_link' . ' ORDER BY show_order';
	$res = $GLOBALS ['db']->query_read_slave ( $sql );
	$links ['img'] = $links ['txt'] = array ();
	while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
		if (! empty ( $row ['link_logo'] )) {
			$links ['img'] [] = array ('name' => $row ['link_name'], 'url' => $row ['link_url'], 'logo' => $row ['link_logo'] );
		} else {
			$links ['txt'] [] = array ('name' => $row ['link_name'], 'url' => $row ['link_url'] );
		}
	}
	return $links;
}
/**
 * 获得最新的文章列表。
 *
 * @access  private
 * @return  array
 */
function index_get_new_articles() {

	$sql = 'SELECT a.article_id, a.title, ac.cat_name, a.add_time, a.file_url, a.open_type, ac.cat_id ' . ' FROM ' . TABLE_PREFIX . 'article' . ' AS a, ' . TABLE_PREFIX . 'article_cat' . ' AS ac' . ' WHERE a.is_open = 1 AND a.cat_id = ac.cat_id AND ac.cat_type = 1' . ' ORDER BY a.article_type DESC, a.add_time DESC ';
	$sql = $GLOBALS ['db']->query_limit ( $sql, $GLOBALS ['skyuc']->options ['article_number'] );
	$res = $GLOBALS ['db']->query_read_slave ( $sql );
	$arr = array ();
	while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
		$row ['id'] = $row ['article_id'];
		$row ['add_time'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'], $row ['add_time'] );
		$row ['url'] = iif ( $row ['open_type'] != 1, build_uri ( 'article', array ('aid' => $row ['article_id'] ), $row ['title'] ), trim ( $row ['file_url'] ) );
		$row ['cat_url'] = build_uri ( 'article_cat', array ('acid' => $row ['cat_id'] ) );
		$arr [] = $row;

	}

	return $arr;
}
/**
 * 获得最新的专题。
 *
 * @access  private
 * @return  array
 */
function index_get_new_subject($num = 5) {

	$sql = 'SELECT id,title,link,thumb,poster,intro,uselink,add_time,recom  FROM ' . TABLE_PREFIX . 'subject' . ' ORDER BY add_time DESC ';
	$sql = $GLOBALS ['db']->query_limit ( $sql, $num );
	$res = $GLOBALS ['db']->query_read_slave ( $sql );
	$arr = array ();
	while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
		$row ['add_time'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $row ['add_time'] );
		$row ['link'] = iif ( $row ['uselink'] != 1, build_uri ( 'subject', array ('sid' => $row ['id'] ), $row ['title'] ), trim ( $row ['link'] ) );
		$arr [] = $row;
	}

	return $arr;
}

/**
 * 影片总数统计
 *
 * @access  private
 * @return  array
 */
function index_get_film() {

	//时间为今天凌晨0:00
	$yesterday = skyuc_mktime ( 0, 0, 0, date ( 'm' ), date ( 'd' ), date ( 'Y' ) );

	$arr = array ();

	$film = $GLOBALS ['db']->query_first_slave ( 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'show' );
	$arr ['films'] = $film ['total'];

	$today = $GLOBALS ['db']->query_first_slave ( 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'show' . ' WHERE add_time>=' . $yesterday );
	$arr ['today'] = $today ['total'];

	return $arr;
}
?>