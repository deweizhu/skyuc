<?php
/**
 * SKYUC! 前台article_cat.php私有函数库
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

/**
 * 获得指定分类同级的所有分类以及该分类下的子分类
 *
 * @access  public
 * @param   integer     $cat_id     分类编号
 * @return  array
 */
function article_categories_tree($cat_id = 0) {

	if ($cat_id > 0) {
		$sql = 'SELECT parent_id FROM ' . TABLE_PREFIX . 'article_cat' . " WHERE cat_id = '$cat_id'";
		$row = $GLOBALS['db']->query_first_slave ( $sql );
		$parent_id = $row ['parent_id'];
	} else {
		$parent_id = 0;
	}

	/*   判断当前分类中全是是否是底级分类，
             如果是取出底级分类上级分类，
             如果不是取当前分类及其下的子分类*/

	$sql = 'SELECT count(*) AS total FROM ' . TABLE_PREFIX . 'article_cat' . " WHERE parent_id = '$parent_id'";
	$total = $GLOBALS['db']->query_first_slave ( $sql );
	if (! empty ( $total )) {
		/* 获取当前分类及其子分类 */
		$sql = 'SELECT a.cat_id, a.cat_name, a.sort_order AS parent_order, a.cat_id, ' . 'b.cat_id AS child_id, b.cat_name AS child_name, b.sort_order AS child_order ' . 'FROM ' . TABLE_PREFIX . 'article_cat' . ' AS a ' . 'LEFT JOIN ' . TABLE_PREFIX . 'article_cat' . ' AS b ON b.parent_id = a.cat_id ' . "WHERE a.parent_id = '$parent_id' AND a.cat_type=1 ORDER BY parent_order ASC, a.cat_id ASC, child_order ASC";
	} else {
		/* 获取当前分类及其父分类 */
		$sql = 'SELECT a.cat_id, a.cat_name, b.cat_id AS child_id, b.cat_name AS child_name, b.sort_order ' . 'FROM ' . TABLE_PREFIX . 'article_cat' . ' AS a ' . 'LEFT JOIN ' . TABLE_PREFIX . 'article_cat' . ' AS b ON b.parent_id = a.cat_id ' . "WHERE b.parent_id = '$parent_id' AND b.cat_type = 1 ORDER BY sort_order ASC";
	}
	$res = $GLOBALS['db']->query_all_slave ( $sql );

	$cat_arr = array ();
	foreach ( $res as $row ) {
		$cat_arr [$row ['cat_id']] ['id'] = $row ['cat_id'];
		$cat_arr [$row ['cat_id']] ['name'] = $row ['cat_name'];
		$cat_arr [$row ['cat_id']] ['url'] = build_uri ( 'article_cat', array ('acid' => $row ['cat_id'] ), $row ['cat_name'] );

		if ($row ['child_id'] != NULL) {
			$cat_arr [$row ['cat_id']] ['children'] [$row ['child_id']] ['id'] = $row ['child_id'];
			$cat_arr [$row ['cat_id']] ['children'] [$row ['child_id']] ['name'] = $row ['child_name'];
			$cat_arr [$row ['cat_id']] ['children'] [$row ['child_id']] ['url'] = build_uri ( 'article_cat', array ('acid' => $row ['child_id'] ), $row ['child_name'] );
		}
	}

	return $cat_arr;
}

/*------------------------------------------------------ */
//-- 文章内容 PRIVATE FUNCTION
/*------------------------------------------------------ */

/**
 * 获得指定的文章的详细信息
 *
 * @access  private
 * @param   integer     $article_id
 * @return  array
 */
function get_article_info($article_id) {


	// 获得文章的信息
	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'article' . ' WHERE is_open = 1 AND article_id = ' . $article_id . ' ORDER BY article_id';
	$row = $GLOBALS['db']->query_first_slave ( $sql );

	if (! empty ( $row )) {
		// 修正添加时间显示
		$row ['add_time'] = skyuc_date ( $GLOBALS['skyuc']->options ['date_format'] . ' ' . $GLOBALS['skyuc']->options ['time_format'], $row ['add_time'] );

		// 作者信息如果为空，则用网站名称替换
		if (empty ( $row ['author'] ) || $row ['author'] == '_SITEHELP') {
			$row ['author'] = $GLOBALS['skyuc']->options ['site_name'];
		}
	}

	return $row;
}

/**
 * 获得文章关联的影片
 *
 * @access  public
 * @param   integer $article_id
 * @return  array
 */
function article_related_show($article_id) {


	$NUM = $GLOBALS['skyuc']->options ['related_show'] > 0 ? $GLOBALS['skyuc']->options ['related_show'] : 5;
	$sql = 'SELECT sa.show_id,sa.article_id,m.show_id,m.title,m.thumb FROM ' . TABLE_PREFIX . 'show_article' . ' AS sa LEFT JOIN  ' . TABLE_PREFIX . 'show' . '  AS m ON m.show_id = sa.show_id WHERE sa.article_id =' . $article_id . ' AND m.is_show = 1 ';
	$sql = $GLOBALS['db']->query_limit ( $sql, $NUM );
	$res = $GLOBALS['db']->query_read_slave ( $sql );

	$arr = array ();
	while ( $row = $GLOBALS['db']->fetch_array ( $res ) ) {
		// 修正影片图片
		$row ['thumb'] = get_image_path ( $row ['thumb'] );
		$row ['url'] = build_uri ( 'show', array ('mid' => $row ['show_id'] ), $row ['title'] );
		$arr [] = $row;
	}

	return $arr;
}

/**
 * 获得文章分类下的文章列表
 *
 * @access  public
 * @param   integer     $cat_id
 * @param   integer     $page
 * @param   integer     $size
 *
 * @return  array
 */
function get_cat_articles($cat_id, $page = 1, $size = 20) {


	$sql = 'SELECT article_id, title, author, add_time, file_url, open_type' . ' FROM ' . TABLE_PREFIX . 'article' . ' WHERE is_open = 1 AND cat_id = ' . $cat_id . ' ORDER BY article_id DESC ';
	$sql = $GLOBALS['db']->query_limit ( $sql, $size, ($page - 1) * $size );
	$res = $GLOBALS['db']->query_read_slave ( $sql );

	$arr = array ();
	if ($res) {
		while ( $row = $GLOBALS['db']->fetch_array ( $res ) ) {
			$article_id = $row ['article_id'];

			$arr [$article_id] ['id'] = $article_id;
			$arr [$article_id] ['title'] = $row ['title'];
			$arr [$article_id] ['author'] = empty ( $row ['author'] ) || $row ['author'] == '_SITEHELP' ? $GLOBALS['skyuc']->options ['site_name'] : $row ['author'];
			$arr [$article_id] ['url'] = $row ['open_type'] != 1 ? build_uri ( 'article', array ('aid' => $article_id ), $row ['title'] ) : trim ( $row ['file_url'] );
			$arr [$article_id] ['add_time'] = skyuc_date ( $GLOBALS['skyuc']->options ['date_format'], $row ['add_time'] );
		}
	}

	return $arr;
}
?>