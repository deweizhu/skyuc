<?php
/**
 * SKYUC! 影片分类列表文件
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
define ( 'THIS_SCRIPT', 'list' );
define ( 'CSRF_PROTECTION', true );
define ( 'CSRF_SKIP_LIST', '' );
//define('SMARTY_CACHE',	true);


require (dirname ( __FILE__ ) . '/global.php');
require (DIR . '/includes/control/list.php');
/*------------------------------------------------------ */
//-- INPUT过滤
/*------------------------------------------------------ */
$skyuc->input->clean_array_gpc ( 'r', array ('id' => TYPE_UINT, 'category' => TYPE_UINT, 'page' => TYPE_UINT, 'sort' => TYPE_STR, 'order' => TYPE_STR, 'display' => TYPE_STR ) );

// 获得请求的分类 ID
if ($skyuc->GPC_exists ['id']) {
	$cat_id = $skyuc->GPC ['id'];
} elseif ($skyuc->GPC_exists ['category']) {
	$cat_id = $skyuc->GPC ['category'];
} else {
	// 如果分类ID为0，则返回首页
	header ( "Location: ./\n" );
	exit ();
}

// 初始化分页信息
$page = iif ( $skyuc->GPC_exists ['page'] && $skyuc->GPC ['page'] > 0, $skyuc->GPC ['page'], 1 );
$size = iif ( isset ( $skyuc->options ['page_size'] ) && intval ( $skyuc->options ['page_size'] ) > 0, intval ( $skyuc->options ['page_size'] ), 10 );

/* 排序方式以及类型 */
$default_display_type = iif ( $skyuc->options ['show_order_type'] == '0', 'list', iif ( $skyuc->options ['show_order_type'] == '1', 'grid', 'text' ) );
$default_sort_order_method = iif ( $skyuc->options ['sort_order_method'] == '0', 'DESC', 'ASC' );
$default_sort_order_type = '';

switch ($skyuc->options ['sort_order_type']) {
	case '0' :
		$default_sort_order_type = 'show_id';
		break;
	case '1' :
		$default_sort_order_type = 'click_count';
		break;
	case '2' :
		$default_sort_order_type = 'pubdate';
		break;
	case '3' :
		$default_sort_order_type = 'add_time';
		break;
	default :
		$default_sort_order_type = 'show_id';
		break;
}

$sort = iif ( $skyuc->GPC_exists ['sort'] && in_array ( strtolower ( $skyuc->GPC ['sort'] ), array ('show_id', 'click_count', 'pubdate', 'add_time' ) ), $skyuc->GPC ['sort'], $default_sort_order_type );
$order = iif ( $skyuc->GPC_exists ['order'] && in_array ( strtoupper ( $skyuc->GPC ['order'] ), array ('ASC', 'DESC' ) ), $skyuc->GPC ['order'], $default_sort_order_method );
$display = iif ( $skyuc->GPC_exists ['display'] && in_array ( strtolower ( $skyuc->GPC ['display'] ), array ('list', 'grid', 'text' ) ), $skyuc->GPC ['display'], $default_display_type );

/*------------------------------------------------------ */
//-- PROCESSOR
/*------------------------------------------------------ */

$children = get_children ( $cat_id );

$cat = get_cat_info ( $cat_id ); // 获得分类的相关信息


if (! empty ( $cat )) {
	$smarty->assign ( 'keywords', htmlspecialchars ( $cat ['keywords'] ) );
	$smarty->assign ( 'description', htmlspecialchars ( $cat ['cat_desc'] ) );
	$smarty->assign ( 'cat_style', htmlspecialchars ( $cat ['style'] ) );
} else {
	// 如果分类不存在则返回首页
	header ( "Location: ./\n" );
	exit ();
}

// 载入网站信息
assign_template ( 'c', array ($cat_id ) );

$position = assign_ur_here ( $cat_id );

$smarty->assign ( 'page_title', $position ['title'] ); // 页面标题
$smarty->assign ( 'ur_here', $position ['ur_here'] ); // 当前位置


if (! empty ( $skyuc->template ['list'] ['recom_cate'] )) {
	$smarty->assign ( 'recom_cate', get_top_new_hot ( 'recom_cate', $children ) ); // 分类推荐影片
}

if (! empty ( $skyuc->template ['list'] ['tree_cate'] )) {
	$smarty->assign ( 'categories', get_categories_tree () ); // 影片分类树
	$smarty->assign ( 'area_list', explode ( '|', $skyuc->options ['show_area'] ) ); // 地区分类树
	$smarty->assign ( 'lang_list', explode ( '|', $skyuc->options ['show_lang'] ) ); // 语言分类树
}
if (! empty ( $skyuc->template ['list'] ['top10_cate'] )) {
	$smarty->assign ( 'top_month', get_top_new_hot ( 'top_cate', $children, 30 ) ); // 月点播排行
}
if (! empty ( $skyuc->template ['list'] ['new10_cate'] )) {
	$smarty->assign ( 'new_show', get_top_new_hot ( 'new' ) ); // 最近更新影片
}

$smarty->assign ( 'category', $cat_id );

if (! empty ( $skyuc->template ['list'] ['tag_cate'] )) {
	//标签汇总
	$tags = get_tags ( 0, 0, 41 );

	if (! empty ( $tags )) {
		require_once (DIR . '/includes/functions_users.php');
		color_tag ( $tags );
	}

	$smarty->assign ( 'tags', $tags );
}

$count = get_cagtegory_show_count ( $children );
$max_page = iif ( $count > 0, ceil ( $count / $size ), 1 );
if ($page > $max_page) {
	$page = $max_page;
}

$show_list = category_get_show ( $children, $size, $page, $sort, $order );
if ($display == 'grid') {
	if (count ( $show_list ) % 2 != 0) {
		$show_list [] = array ();
	}
}

$smarty->assign ( 'show_list', $show_list );
$smarty->assign ( 'list', $cat_id );

assign_pager ( 'category', $cat_id, $count, $size, $sort, $order, $page, '', $display ); // 分页

// 页面中的动态内容
assign_dynamic ( 'list' );

$smarty->display ( 'list.dwt' );
