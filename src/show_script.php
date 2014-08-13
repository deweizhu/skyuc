<?php

/**
 * SKYUC! 生成站外JS调用文件
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
define ( 'THIS_SCRIPT', 'show_script' );
define ( 'CSRF_PROTECTION', true );
define ( 'CSRF_SKIP_LIST', '' );
define ( 'SMARTY_CACHE', true );
define ( 'SKIP_SESSIONCREATE', 1 );
define ( 'SKIP_USERINFO', 1 );
define ( 'SKIP_DEFAULTDATASTORE', 1 );

require (dirname ( __FILE__ ) . '/global.php');

$skyuc->input->clean_array_gpc ( 'g', array ('charset' => TYPE_STR, 'type' => TYPE_STR, 'sitename' => TYPE_STR, 'cat_id' => TYPE_UINT, 'server_id' => TYPE_UINT, 'intro_type' => TYPE_STR, 'show_num' => TYPE_UINT, 'arrange' => TYPE_STR, 'need_image' => TYPE_BOOL ) );

$charset = iif ( empty ( $skyuc->GPC ['charset'] ), 'utf-8', $skyuc->GPC ['charset'] );
if (strtolower ( $charset ) == 'gb2312') {
	$charset = 'gbk';
}
header ( 'content-type: application/x-javascript; charset=' . iif ( $charset == 'UTF8', 'utf-8', $charset ) );

/*------------------------------------------------------ */
//-- 判断是否存在缓存，如果存在则调用缓存，反之读取相应内容
/*------------------------------------------------------ */
/* 缓存编号 */
$cache_id = sprintf ( '%X', crc32 ( $_SERVER ['QUERY_STRING'] ) );

$tpl = DIR . '/data/show_script.tpl';
if (! $smarty->is_cached ( $tpl, $cache_id )) {

	/* 根据参数生成查询语句 */
	if (empty ( $skyuc->GPC ['type'] )) {
		$show_url = get_url () . 'affiche.php?ad_id=-1&amp;from=' . urlencode ( $skyuc->GPC ['sitename'] ) . '&amp;show_id=';

		$sql = 'SELECT show_id, title, thumb FROM ' . TABLE_PREFIX . 'show' . ' AS m WHERE is_show = 1';
		if (! empty ( $skyuc->GPC ['cat_id'] )) {
			$sql .= ' AND ' . get_children ( $skyuc->GPC ['cat_id'] );
		}
		if (! empty ( $skyuc->GPC ['server_id'] )) {
			$sql .= " AND server_id = '" . $skyuc->GPC ['server_id'] . "'";
		}
		if (! empty ( $skyuc->GPC ['intro_type'] )) {

			// 推荐类型is_best=1为强力推荐,is_hot=1为分类推荐
			switch ($skyuc->GPC ['intro_type']) {
				case 'is_vip' :
					$sql .= ' AND points>=1';
					break;
				case 'is_free' :
					$sql .= ' AND points=0';
					break;
				case 'is_best' :
					$sql .= ' AND attribute=1';
					break;
				case 'is_hot' :
					$sql .= ' AND attribute=2';
					break;
				case 'is_series' :
					$sql .= ' AND attribute=3';
					break;
				case 'is_done' :
					$sql .= ' AND attribute=4';
					break;
			}
		}
	}

	$sql .= '  ORDER BY show_id DESC ';
	$sql = $skyuc->db->query_limit ( $sql, iif ( $skyuc->GPC ['show_num'] > 0, $skyuc->GPC ['show_num'], 10 ) );
	$res = $db->query_read_slave ( $sql );

	$show_list = array ();
	while ( $show = $db->fetch_array ( $res ) ) {
		// 转换编码
		if ($charset != 'utf-8') {
			if ('utf-8' == 'gbk') {
				$tmp_show_name = htmlentities ( $show ['title'], ENT_QUOTES, 'gb2312' );
			} else {
				$tmp_show_name = htmlentities ( $show ['title'], ENT_QUOTES, 'utf-8' );
			}
			$show ['title'] = skyuc_iconv ( 'utf-8', $charset, $tmp_show_name );
		}
		$show_list [] = $show;
	}
	$smarty->assign ( 'show_list', $show_list );

	// 排列方式
	$arrange = empty ( $skyuc->GPC ['arrange'] ) || ! in_array ( $skyuc->GPC ['arrange'], array ('h', 'v' ) ) ? 'h' : $skyuc->GPC ['arrange'];
	$smarty->assign ( 'arrange', $arrange );

	// 是否需要图片
	$smarty->assign ( 'need_image', $skyuc->GPC ['need_image'] );

	// 图片大小
	$smarty->assign ( 'thumb_width', intval ( $skyuc->options ['thumb_width'] ) );
	$smarty->assign ( 'thumb_height', intval ( $skyuc->options ['thumb_height'] ) );

	// 网站根目录
	$smarty->assign ( 'url', get_url () );

	// 影片页面连接
	$smarty->assign ( 'show_url', $show_url );
}
$output = $smarty->fetch ( $tpl, $cache_id );
$output = str_replace ( array ("\r", "\n" ), '', $output );

echo "document.write('" . $output . "');";

?>
