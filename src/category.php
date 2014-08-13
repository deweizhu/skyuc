<?php
/**
 * SKYUC! 分类页文件
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
define ( 'THIS_SCRIPT', 'category' );
define ( 'CSRF_PROTECTION', true );
define ( 'CSRF_SKIP_LIST', '' );
define ( 'SMARTY_CACHE', true );

// 从缓存中获取指定数据
$specialtemplates = array ('mailqueue' );

require (dirname ( __FILE__ ) . '/global.php');

/*------------------------------------------------------ */
//-- 判断是否存在缓存，如果存在则调用缓存，反之读取相应内容
/*------------------------------------------------------ */
/* 缓存编号 */
$cache_id = sprintf ( '%X', crc32 ( 'category' ) );

if (! $smarty->is_cached ( 'category.dwt', $cache_id )) {
	// 载入网站信息
	assign_template ();

	$position = assign_ur_here ();
	$smarty->assign ( 'page_title', $position ['title'] ); // 页面标题
	$smarty->assign ( 'ur_here', $position ['ur_here'] ); // 当前位置


	/* META 信息 */
	$smarty->assign ( 'keywords', htmlspecialchars ( $skyuc->options ['site_keywords'] ) );
	$smarty->assign ( 'description', htmlspecialchars ( $skyuc->options ['site_desc'] ) );

	$smarty->assign ( 'feed_url', iif ( $skyuc->options ['rewrite'], 'rss.xml', 'rss.php' ) ); // RSS URL


	$cat = get_categories_tree ();
	$smarty->assign ( 'categories', $cat ); // 影片分类树
	$smarty->assign ( 'catenum', ceil ( count ( $cat ) / 2 ) ); // 影片分类树


}

$smarty->display ( 'category.dwt', $cache_id );
?>
