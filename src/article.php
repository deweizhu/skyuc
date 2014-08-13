<?php

/**
 * SKYUC! 文章内容
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
define ( 'THIS_SCRIPT', 'article' );
define ( 'CSRF_PROTECTION', true );
define ( 'CSRF_SKIP_LIST', '' );
define ( 'SMARTY_CACHE', true );

require (dirname ( __FILE__ ) . '/global.php');
require (DIR . '/includes/control/article.php');

/*------------------------------------------------------ */
//-- INPUT
/*------------------------------------------------------ */

$article_id = $skyuc->input->clean_gpc ( 'r', 'id', TYPE_UINT );

/*------------------------------------------------------ */
//-- PROCESSOR
/*------------------------------------------------------ */

$cache_id = sprintf ( '%X', crc32 ( $article_id ) );

if (! $smarty->is_cached ( 'article.dwt', $cache_id )) {
	// 文章详情
	$article = get_article_info ( $article_id );
	if (empty ( $article )) {
		header ( "Location: ./\n" );
		exit ();
	}

	$smarty->assign ( 'nav_list', get_navigator () ); // 导航栏


	if (! empty ( $skyuc->template ['article'] ['related_article'] )) {

		$smarty->assign ( 'related_article', article_related_show ( $article_id ) ); //关联影片
	}
	if (! empty ( $skyuc->template ['article'] ['new10_article'] )) {
		$smarty->assign ( 'new_show', get_top_new_hot ( 'new', '', 30 ) ); // 最近更新影片
	}
	if (! empty ( $skyuc->template ['article'] ['top10_article'] )) {
		$smarty->assign ( 'top_month', get_top_new_hot ( 'top', '', 30 ) ); // 热门影片
	}
	if (! empty ( $skyuc->template ['article'] ['article_cate_tree'] )) {
		$smarty->assign ( 'article_categories', article_categories_tree ( $cat_id ) ); //文章分类树
	}

	$smarty->assign ( 'id', $article_id );
	$smarty->assign ( 'type', '1' );

	$smarty->assign ( 'article', $article );

	$smarty->assign ( 'keywords', htmlspecialchars ( $article ['keywords'] ) );
	$smarty->assign ( 'descriptions', htmlspecialchars ( $article ['title'] ) );

	assign_template ();

	$position = assign_ur_here ( $article ['cat_id'], $article ['title'] );
	$smarty->assign ( 'page_title', $position ['title'] ); // 页面标题
	$smarty->assign ( 'ur_here', $position ['ur_here'] ); // 当前位置


}

$smarty->display ( 'article.dwt', $cache_id );

?>
