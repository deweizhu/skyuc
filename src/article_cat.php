<?php
/**
 * SKYUC! 文章分类
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
define ( 'THIS_SCRIPT', 'article_cat' );
define ( 'CSRF_PROTECTION', true );
define ( 'CSRF_SKIP_LIST', '' );
define ( 'SMARTY_CACHE', true );

require (dirname ( __FILE__ ) . '/global.php');
require (DIR . '/includes/control/article.php');
/*------------------------------------------------------ */
//-- INPUT
/*------------------------------------------------------ */
$skyuc->input->clean_array_gpc ( 'r', array ('id' => TYPE_UINT, 'category' => TYPE_UINT, 'page' => TYPE_UINT ) );
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

/*------------------------------------------------------ */
//-- PROCESSOR
/*------------------------------------------------------ */

/* 获得页面的缓存ID */
$cache_id = sprintf ( '%X', crc32 ( $cat_id . '-' . $page ) );

if (! $smarty->is_cached ( 'article_cat.dwt', $cache_id )) {
	// 如果页面没有被缓存则重新获得页面的内容
	$smarty->assign ( 'nav_list', get_navigator () ); // 导航栏


	assign_template ();

	$position = assign_ur_here ( $cat_id );
	$smarty->assign ( 'page_title', $position ['title'] ); // 页面标题
	$smarty->assign ( 'ur_here', $position ['ur_here'] ); // 当前位置


	if (! empty ( $skyuc->template ['article_cat'] ['new10_article'] )) {
		$smarty->assign ( 'new_show', get_top_new_hot ( 'new', '', 30 ) ); // 最近更新影片
	}
	if (! empty ( $skyuc->template ['article_cat'] ['top10_article'] )) {
		$smarty->assign ( 'top_month', get_top_new_hot ( 'top', '', 30 ) ); // 热门影片
	}
	if (! empty ( $skyuc->template ['article_cat'] ['article_cate_tree'] )) {
		$smarty->assign ( 'article_categories', article_categories_tree ( $cat_id ) ); //文章分类树
	}
	/* Meta */
	$meta = $db->query_first_slave ( 'SELECT keywords, cat_desc FROM ' . TABLE_PREFIX . 'article_cat' . ' WHERE cat_id = ' . $cat_id );

	if ($meta === false || empty ( $meta )) {
		// 如果没有找到任何记录则返回首页
		header ( "Location: ./\n" );
		exit ();
	}

	$smarty->assign ( 'keywords', htmlspecialchars ( $meta ['keywords'] ) );
	$smarty->assign ( 'description', htmlspecialchars ( $meta ['cat_desc'] ) );

	$size = iif ( intval ( $skyuc->options ['page_size'] ) > 0, intval ( $skyuc->options ['page_size'] ), 20 );

	// 获得文章总数
	$total = $db->query_first_slave ( 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'article' . ' WHERE cat_id = ' . $cat_id . ' AND is_open = 1' );
	$count = $total ['total'];
	$max_page = ($count > 0) ? ceil ( $count / $size ) : 1;
	if ($page > $max_page) {
		$page = $max_page;
	}
	$pager ['search'] ['id'] = $cat_id;
	/* 获得文章列表 */
	//    if (isset($_GET['keywords']))
	//    {
	//        $keywords = addslashes(urldecode(trim($_GET['keywords'])));
	//        $pager['search']['keywords'] = $keywords;
	//        $search_url = $_SERVER['REQUEST_URI'];
	//
	//        $smarty->assign('search_value',     $keywords);
	//        $smarty->assign('search_url',       $search_url);
	//        $count  = get_article_count($cat_id, $keywords);
	//        $pages  = ($count > 0) ? ceil($count / $size) : 1;
	//        if ($page > $pages)
	//        {
	//            $page = $pages;
	//        }
	//    }


	// 获得文章列表
	$smarty->assign ( 'artciles_list', get_cat_articles ( $cat_id, $page, $size ) );

	// 分页
	// assign_pager('article_cat', $cat_id, $count, $size, '', '', $page);
	$pager = get_pager ( 'article_cat.php', $pager ['search'], $count, $page, $size );
	$smarty->assign ( 'pager', $pager );

	assign_dynamic ( 'article_cat' );

}

$smarty->display ( 'article_cat.dwt', $cache_id );

?>