<?php
/**
 * SKYUC! 首页文件
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
define ( 'THIS_SCRIPT', 'index' );
define ( 'CSRF_PROTECTION', true );
define ( 'CSRF_SKIP_LIST', '' );
define ( 'SMARTY_CACHE', true );

// 从缓存中获取指定数据
$specialtemplates = array ('mailqueue' );

require (dirname ( __FILE__ ) . '/global.php');
require (DIR . '/includes/control/index.php');

/*------------------------------------------------------ */
//-- 判断是否存在缓存，如果存在则调用缓存，反之读取相应内容
/*------------------------------------------------------ */
/* 缓存编号 */
$cache_id = sprintf ( '%X', crc32 ( 'index' ) );

if (! $smarty->is_cached ( 'index.dwt', $cache_id )) {
	// 载入网站信息
	assign_template ();

	$position = assign_ur_here ();
	$smarty->assign ( 'page_title', $position ['title'] ); // 页面标题
	$smarty->assign ( 'ur_here', $position ['ur_here'] ); // 当前位置


	/* META 信息 */
	$smarty->assign ( 'keywords', htmlspecialchars ( $skyuc->options ['site_keywords'] ) );
	$smarty->assign ( 'description', htmlspecialchars ( $skyuc->options ['site_desc'] ) );
	$smarty->assign ( 'flash_theme', $skyuc->options ['flash_theme'] ); // Flash轮播图片模板


	$smarty->assign ( 'feed_url', iif ( $skyuc->options ['rewrite'], 'rss.xml', 'rss.php' ) ); // RSS URL


	if (! empty ( $skyuc->template ['index'] ['subject'] )) {
		$smarty->assign ( 'subject', index_get_new_subject () ); //最新专题
	}
	if (! empty ( $skyuc->template ['index'] ['recom'] )) {
		$smarty->assign ( 'recom_show', get_top_new_hot ( 'recom' ) ); // 强力推荐影片
	}
	if (! empty ( $skyuc->template ['index'] ['new10'] )) {
		$smarty->assign ( 'new_show', get_top_new_hot ( 'new' ) ); // 最近更新影片
	}
	if (! empty ( $skyuc->template ['index'] ['top10'] )) {
		$smarty->assign ( 'top_day', get_top_new_hot ( 'top', '', 1 ) ); // 日点播排行
		$smarty->assign ( 'top_week', get_top_new_hot ( 'top', '', 7 ) ); // 周点播排行
		$smarty->assign ( 'top_month', get_top_new_hot ( 'top', '', 30 ) ); // 月点播排行
	}
	if (! empty ( $skyuc->template ['index'] ['tree'] )) {
		$smarty->assign ( 'categories', get_categories_tree () ); // 影片分类树
		$smarty->assign ( 'area_list', explode ( '|', $skyuc->options ['show_area'] ) ); // 地区分类树
		$smarty->assign ( 'lang_list', explode ( '|', $skyuc->options ['show_lang'] ) ); // 语言分类树
	}

	if (! empty ( $skyuc->template ['index'] ['new_articles'] )) {
		$smarty->assign ( 'new_articles', index_get_new_articles () ); // 最新文章
	}

	if ($skyuc->options ['total_film'] == 1) {
		$smarty->assign ( 'film_total', index_get_film () ); //影片总数统计
	}

	$smarty->assign ( 'site_notice', $skyuc->options ['site_notice'] ); // 网站公告


	if (! empty ( $skyuc->template ['index'] ['tag'] )) {
		//标签汇总
		$tags = get_tags ( 0, 0, 45 );

		if (! empty ( $tags )) {
			require_once (DIR . '/includes/functions_users.php');
			color_tag ( $tags );
		}

		$smarty->assign ( 'tags', $tags );
	}

	// 调查
	//判断是否启用网站调查模块
	if ($skyuc->options ['enable_vote'] == 1) {
		require_once (DIR . '/includes/control/ajax.php');
		$vote = get_vote ();
	} else {
		$vote = '';
	}
	if (! empty ( $vote )) {
		$smarty->assign ( 'vote_id', $vote ['id'] );
		$smarty->assign ( 'vote', $vote ['content'] );
	}

	/* links */
	//判断是否启用友情链接模块
	if ($skyuc->options ['enable_links'] == 1) {
		$links = index_get_links ();
		$smarty->assign ( 'img_links', $links ['img'] );
		$smarty->assign ( 'txt_links', $links ['txt'] );
	}

	// 页面中的动态内容
	assign_dynamic ( 'index' );

}

$smarty->display ( 'index.dwt', $cache_id );
