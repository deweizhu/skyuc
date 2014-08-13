<?php
/**
 * SKYUC! 影片搜索页
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

// ####################### 设置 PHP 环境 ###########################
error_reporting ( E_ALL & ~ E_NOTICE );

// #################### 定义重要常量 #######################
define ( 'THIS_SCRIPT', 'search' );
define ( 'CSRF_PROTECTION', true );
define ( 'CSRF_SKIP_LIST', '' );
define ( 'SMARTY_CACHE', true );

if (empty ( $_GET ['encode'] )) {
	$string = array_merge ( $_GET, $_POST );
	if (get_magic_quotes_gpc ()) {
		require (dirname ( __FILE__ ) . '/includes/functions.php');
		$string = stripslashes_deep ( $string );
	}
	$string ['search_encode_time'] = $_SERVER ['REQUEST_TIME'];
	$string = str_replace ( '+', '%2b', base64_encode ( serialize ( $string ) ) );

	header ( "Location: search.php?encode=$string\n" );

	exit ();
} else {
	$string = base64_decode ( trim ( $_GET ['encode'] ) );
	if ($string !== false) {
		$string = unserialize ( $string );
		if ($string !== false) {
			// 用户在重定向的情况下当作一次访问
			if (! empty ( $string ['search_encode_time'] )) {
				if (time () > $string ['search_encode_time'] + 2) {
					define ( 'INGORE_VISIT_STATS', true );
				}
			} else {
				define ( 'INGORE_VISIT_STATS', true );
			}
		} else {
			$string = array ();
		}
	} else {
		$string = array ();
	}
}

require (dirname ( __FILE__ ) . '/global.php');
require (DIR . '/includes/control/list.php');
require (DIR . '/includes/functions_search.php');

$_REQUEST = array_merge ( $_REQUEST, $string );
$skyuc->input->clean_array_gpc ( 'r', array ('keywords' => TYPE_STR, 'category' => TYPE_UINT, 'area' => TYPE_STR, 'lang' => TYPE_STR, 'detail' => TYPE_BOOL, 'act' => TYPE_STR, 'sort' => TYPE_STR, 'order' => TYPE_STR, 'page' => TYPE_UINT, 'intro' => TYPE_STR ) );

/*------------------------------------------------------ */
//-- 搜索结果
/*------------------------------------------------------ */
//按分类搜索
$category = $skyuc->GPC ['category'];
$categories = iif ( $category > 0, iif (empty ( $skyuc->GPC ['keywords']), ' AND ' . get_children( $category ), ' AND ' . get_contenttypeid( $category ) ), '' );

// 初始化搜索条件
$keywords = '';
if (! empty ( $skyuc->GPC ['area'] )) {
	//按地区查询
	$area = iif ( ! empty ( $skyuc->GPC ['area'] ), " m.area ='" . $db->escape_string ( $skyuc->GPC ['area'] ) . "'", '' );

	$keywords = 'AND ' . $area . '';
} elseif (! empty ( $skyuc->GPC ['lang'] )) {
	//按语言查询
	$lang = iif ( ! empty ( $skyuc->GPC ['lang'] ), " m.lang ='" . $db->escape_string ( $skyuc->GPC ['lang'] ) . "'", '' );

	$keywords = 'AND ' . $lang . '';
}

// 排序、显示方式以及类型
$default_display_type = iif ( $skyuc->options ['show_order_type'] == '0', 'list', iif ( $skyuc->options ['show_order_type'] == '1', 'grid', 'text' ) );
$default_sort_order_method = iif ( $skyuc->options ['sort_order_method'] == '0', 'DESC', 'ASC' );
$default_sort_order_type = '';

switch ($skyuc->options ['sort_order_type']) {
	case '1' :
		$default_sort_order_type = 'click_count';
		break;
	case '2' :
		$default_sort_order_type = 'pubdate';
		break;
	case '3' :
		$default_sort_order_type = 'add_time';
		break;
	case '0' :
	default :
		$default_sort_order_type = 'show_id';
		break;
}

$sort = iif ( $skyuc->GPC_exists ['sort'] && in_array ( strtolower ( $skyuc->GPC ['sort'] ), array ('show_id', 'click_count', 'pubdate', '' ) ), $skyuc->GPC ['sort'], $default_sort_order_type );
$order = iif ( $skyuc->GPC_exists ['order'] && in_array ( strtoupper ( $skyuc->GPC ['order'] ), array ('ASC', 'DESC' ) ), $skyuc->GPC ['order'], $default_sort_order_method );

$page = iif ( $skyuc->GPC ['page'] > 0, $skyuc->GPC ['page'], 1 );
$size = iif ( intval ( $skyuc->options ['page_size'] ) > 0, intval ( $skyuc->options ['page_size'] ), 10 );

if (! empty ( $skyuc->GPC ['intro'] )) {
	switch ($skyuc->GPC ['intro']) {
		case 'best' :
			$intro = ' AND m.attribute = 1';
			$ur_here = $_LANG ['is_best'];
			break;
		case 'hot' :
			$intro = ' AND m.attribute = 2';
			$ur_here = $_LANG ['is_hot'];
			break;
		case 'series' :
			$intro = ' AND m.attribute = 3';
			$ur_here = $_LANG ['is_series'];
			break;
		case 'done' :
			$intro = ' AND m.attribute = 4';
			$ur_here = $_LANG ['is_done'];
			break;
		default :
			$intro = '';
	}
} else {
	$intro = '';
}

/*------------------------------------------------------ */
//-- PROCESSOR
/*------------------------------------------------------ */

if (empty ( $ur_here )) {
	$ur_here = $_LANG ['search_show'];
}

// 获得符合条件的影片总数
if (! empty ( $skyuc->GPC ['keywords'] )) {
	$displaykeywords  = sanitize_search_query($skyuc->GPC ['keywords']);

	include_once( DIR.'/includes/class_search.php');
    $nt = new normalizeText(4,false);
    $searchstring = sanitize_search_query($nt->parseQuery($skyuc->GPC ['keywords']));

	$sql = "SELECT COUNT(*) AS total FROM ". TABLE_PREFIX . "searchcore_text AS searchcore_text
				WHERE MATCH (searchcore_text.title, searchcore_text.keywordtext)
				AGAINST ('" . $searchstring . "' IN BOOLEAN MODE) ".$categories;
} else {
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'show' . ' AS m ' . ' WHERE m.is_show = 1 '  . $categories . ' ' . $keywords. ' ' .$intro;
}

$key = md5 ( $sql ); //缓存名称：键
//读缓存
if ($data = get_file_cache ( $key )) {
	$total = $data;
} else {
	$total = $db->query_first_slave ( $sql );
	put_file_cache ( $key, $total ); //写缓存
}
$count = $total ['total'];

$max_page = iif ( $count > 0, ceil ( $count / $size ), 1 );
if ($page > $max_page) {
	$page = $max_page;
}

// 查询影片
if (! empty ( $skyuc->GPC ['keywords'] )) {
	$sql = "SELECT m.* , searchid ,  MATCH (searchcore_text.title, searchcore_text.keywordtext) AGAINST ('" .$searchstring . "'  IN BOOLEAN MODE) AS score
				FROM ". TABLE_PREFIX . 'searchcore_text AS searchcore_text  '.
			    'LEFT JOIN '. TABLE_PREFIX . 'show' . ' AS m '.
		        'ON searchcore_text.searchid = m.show_id '.
				'WHERE MATCH (searchcore_text.title, searchcore_text.keywordtext) '.
				"AGAINST ('" . $searchstring. "' IN BOOLEAN MODE)".$categories;
}
else{
    $sql = 'SELECT m.show_id, m.title, m.thumb, m.actor, m.director, m.pubdate,	m.click_count, m.area,	m.lang,	m.status, m.description,m.runtime, m.add_time  FROM ' . TABLE_PREFIX . 'show' . ' AS m ' .
		 '  WHERE m.is_show =1  ' . $keywords  . $categories . ' ' .$intro . '  ORDER BY  ' . $sort . ' ' . $order;
}
$sql = $db->query_limit ( $sql, $size, ($page - 1) * $size );
$key = md5 ( $sql ); //缓存名称：键
//读缓存
if ($data = get_file_cache ( $key )) {
	$show_list = $data;
} else {
	$res = $db->query_read_slave ( $sql );
	$show_list = array ();
	while ( $row = $db->fetch_array ( $res ) ) {
		$row ['description'] = html2text ( $row ['description'] ); //去除影片看点中HTML代码
		// 修正影片图片
		$row ['thumb'] = get_image_path ( $row ['thumb'] );
		$row ['add_time'] = skyuc_date ( $skyuc->options ['date_format'] . ' ' . $skyuc->options ['time_format'], $row ['add_time'] );
		//演员搜索链接
		$row ['actor'] = get_actor_array ( $row ['actor'] );
		$row ['url'] = build_uri ( 'show', array ('mid' => $row ['show_id'] ), $row ['title'] );
		$show_list [] = $row;

	}
	put_file_cache ( $key, $show_list ); //写缓存

	if ($skyuc->options ['seach_words'] == 1) {
			//保留本站用户搜索关键词
			save_searchengine_keyword ( '', '', 'SKYUC', $searchstring );
	}
}

$smarty->assign ( 'show_list', $show_list );
$smarty->assign ( 'category', $category );
$smarty->assign ( 'keywords', $displaykeywords);
$smarty->assign ( 'search_keywords', htmlspecialchars ( $displaykeywords) );

// 分页 链接
$url_format = 'search.php?category=' . $category . '&amp;keywords=' . urlencode ( $displaykeywords );
if (! empty ( $skyuc->GPC ['area'] )) {
	$url_format .= '&amp;area=' . urlencode ( $skyuc->GPC ['area'] );
} elseif (! empty ( $skyuc->GPC ['lang'] )) {
	$url_format .= '&amp;lang=' . urlencode ( $skyuc->GPC ['lang'] );
}

if (! empty ( $skyuc->GPC ['intro'] )) {
	$url_format .= '&amp;intro=' . $skyuc->GPC ['intro'];
}

$url_format .= '&amp;order=' . $order . '&amp;page=';

$pager = array ('page' => $page, 'size' => $size, 'sort' => $sort, 'order' => $order, 'record_count' => $count, 'page_count' => $max_page, 'page_first' => $url_format . '1', 'page_prev' => $page > 1 ? $url_format . ($page - 1) : 'javascript:;', 'page_next' => $page < $max_page ? $url_format . ($page + 1) : 'javascript:;', 'page_last' => $url_format . $max_page, 'array' => array () );

for($i = 1; $i <= $max_page; $i ++) {
	$pager ['array'] [$i] = $i;
}

$pager ['search'] = array ('keywords' => urlencode ( $skyuc->GPC ['keywords'] ), 'category' => $category, 'sort' => $sort, 'order' => $order, 'intro' => $skyuc->GPC ['intro'], 'detail' => $skyuc->GPC ['detail'] );

iif ( ! empty ( $skyuc->GPC ['area'] ), $pager ['search'] ['area'] = urlencode ( $skyuc->GPC ['area'] ), '' );
iif ( ! empty ( $skyuc->GPC ['lang'] ), $pager ['search'] ['lang'] = urlencode ( $skyuc->GPC ['lang'] ), '' );

$pager = get_pager ( 'search.php', $pager ['search'], $count, $page, $size );
$pager ['display'] = $default_display_type;

$smarty->assign ( 'url_format', $url_format );
$smarty->assign ( 'pager', $pager );

assign_template ();
$position = assign_ur_here ( 0, $ur_here );
$smarty->assign ( 'page_title', $position ['title'] ); // 页面标题
$smarty->assign ( 'ur_here', $position ['ur_here'] ); // 当前位置


$smarty->assign ( 'search_keywords', $displaykeywords );

if (! empty ( $skyuc->template ['search'] ['tree_cate'] )) {
	$smarty->assign ( 'categories', get_categories_tree () ); // 影片分类树
	$smarty->assign ( 'area_list', explode ( '|', $skyuc->options ['show_area'] ) ); // 地区分类树
	$smarty->assign ( 'lang_list', explode ( '|', $skyuc->options ['show_lang'] ) ); // 语言分类树
}
if (! empty ( $skyuc->template ['search'] ['top10_cate'] )) {
	$smarty->assign ( 'top_month', get_top_new_hot ( 'top_cate', $children, 30 ) ); // 月点播排行
}
if (! empty ( $skyuc->template ['search'] ['new10_cate'] )) {
	$smarty->assign ( 'new_show', get_top_new_hot ( 'new' ) ); // 最近更新影片
}

if (! empty ( $skyuc->template ['search'] ['tag_cate'] )) {
	//标签汇总
	$tags = get_tags ( 0, 0, 41 );

	if (! empty ( $tags )) {
		require_once (DIR . '/includes/functions_users.php');
		color_tag ( $tags );
	}

	$smarty->assign ( 'tags', $tags );
}

$smarty->display ( 'search.dwt' );
