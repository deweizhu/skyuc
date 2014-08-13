<?php
/**
 * SKYUC! 站点地图生成程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

/*------------------------------------------------------ */
//-- Google地图
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'google' || $skyuc->GPC ['act'] == '') {

	if ($_SERVER ['REQUEST_METHOD'] == 'GET') {
		/*------------------------------------------------------ */
		//-- 设置更新频率
		/*------------------------------------------------------ */
		assign_query_info ();
		$config = unserialize ( $skyuc->options ['sitemap'] );
		$smarty->assign ( 'config', $config );
		$smarty->assign ( 'ur_here', $_LANG ['sitemap'] );
		$smarty->assign ( 'arr_changefreq', array (1, 0.9, 0.8, 0.7, 0.6, 0.5, 0.4, 0.3, 0.2, 0.1 ) );
		$smarty->display ( 'sitemap.tpl' );
	} else {
		/*------------------------------------------------------ */
		//-- 生成站点地图
		/*------------------------------------------------------ */

		$skyuc->input->clean_array_gpc ( 'p', array ('homepage_changefreq' => TYPE_STR, 'homepage_priority' => TYPE_NUM, 'category_changefreq' => TYPE_STR, 'category_priority' => TYPE_NUM, 'content_changefreq' => TYPE_STR, 'content_priority' => TYPE_NUM ) );

		include_once (DIR . '/includes/class_sitemap.php');

		$domain = get_url ();
		$today = skyuc_date ( $skyuc->options ['date_format'], TIMENOW, FALSE, FALSE );

		$sm = new google_sitemap ();
		$smi = new google_sitemap_item ( $domain, $today, $skyuc->GPC ['homepage_changefreq'], $skyuc->GPC ['homepage_priority'] );
		$sm->add_item ( $smi );

		$config = array ('homepage_changefreq' => $skyuc->GPC ['homepage_changefreq'], 'homepage_priority' => $skyuc->GPC ['homepage_priority'], 'category_changefreq' => $skyuc->GPC ['category_changefreq'], 'category_priority' => $skyuc->GPC ['category_priority'], 'content_changefreq' => $skyuc->GPC ['content_changefreq'], 'content_priority' => $skyuc->GPC ['content_priority'] );
		$config = serialize ( $config );

		$db->query_write ( 'UPDATE ' . TABLE_PREFIX . 'setting' . " SET VALUE='$config' WHERE code='sitemap'" );
		// 影片分类
		$sql = "SELECT cat_id FROM " . TABLE_PREFIX . 'category' . " ORDER BY parent_id";
		$res = $db->query_read_slave ( $sql );

		while ( $row = $db->fetch_array ( $res ) ) {
			$smi = new google_sitemap_item ( $domain . build_uri ( 'category', array ('cid' => $row ['cat_id'] ) ), $today, $skyuc->GPC ['category_changefreq'], $skyuc->GPC ['category_priority'] );
			$sm->add_item ( $smi );
		}

		// 文章分类
		$sql = 'SELECT cat_id FROM ' . TABLE_PREFIX . 'article_cat' . ' WHERE cat_type=1';
		$res = $db->query_read_slave ( $sql );

		while ( $row = $db->fetch_array ( $res ) ) {
			$smi = new google_sitemap_item ( $domain . build_uri ( 'article_cat', array ('acid' => $row ['cat_id'] ) ), $today, $skyuc->GPC ['category_changefreq'], $skyuc->GPC ['category_priority'] );
			$sm->add_item ( $smi );
		}

		// 影片
		$sql = 'SELECT show_id FROM ' . TABLE_PREFIX . 'show' . ' WHERE is_show = 1';
		$res = $db->query_read_slave ( $sql );

		while ( $row = $db->fetch_array ( $res ) ) {
			$smi = new google_sitemap_item ( $domain . build_uri ( 'show', array ('mid' => $row ['show_id'] ) ), $today, $skyuc->GPC ['content_changefreq'], $skyuc->GPC ['content_priority'] );
			$sm->add_item ( $smi );
		}

		// 文章
		$sql = 'SELECT article_id FROM ' . TABLE_PREFIX . 'article' . ' WHERE is_open=1';
		$res = $db->query_read_slave ( $sql );

		while ( $row = $db->fetch_array ( $res ) ) {
			$smi = new google_sitemap_item ( $domain . build_uri ( 'article', array ('aid' => $row ['article_id'] ) ), $today, $skyuc->GPC ['content_changefreq'], $skyuc->GPC ['content_priority'] );
			$sm->add_item ( $smi );
		}
		$sm_file = 'sitemaps.xml';
		if ($sm->build ( $sm_file )) {
			sys_msg ( sprintf ( $_LANG ['generate_success'], get_url () . 'sitemaps.xml' ) );
		} else {
			$sm_file = 'data/sitemaps.xml';
			if ($sm->build ( $sm_file )) {
				sys_msg ( sprintf ( $_LANG ['generate_success'], get_url () . 'data/sitemaps.xml' ) );
			} else {
				sys_msg ( sprintf ( $_LANG ['generate_failed'] ) );
			}
		}

	}
} /*------------------------------------------------------ */
//-- Baidu 地图
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'baidu') {
	if ($_SERVER ['REQUEST_METHOD'] == 'GET') {
		/*------------------------------------------------------ */
		//-- 设置更新频率
		/*------------------------------------------------------ */
		assign_query_info ();
		$arr_changefreq = array (24, 23, 22, 21, 20, 19, 18, 17, 16, 15, 14, 13, 12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1, 0.9, 0.8, 0.7, 0.6, 0.5, 0.4, 0.3, 0.2, 0.1 );

		$smarty->assign ( 'ur_here', $_LANG ['sitemap_baidu'] );
		$smarty->assign ( 'lang', $_LANG );
		$smarty->assign ( 'arr_changefreq', $arr_changefreq );
		$smarty->display ( 'sitemap_baidu.tpl' );
	} else {
		/*------------------------------------------------------ */
		//-- 生成站点地图
		/*------------------------------------------------------ */
		include_once (DIR . '/includes/class_sitemap.php');

		$domain = get_url ();
		$updatePeri = $skyuc->input->clean_gpc ( 'p', 'content_priority', TYPE_NUM ) * 60;

		$sm = new baidu_sitemap ();
		$sm->add_header ( $updatePeri );

		//影片
		$sql = 'SELECT show_id,title,image,detail,runtime,add_time  FROM ' . TABLE_PREFIX . 'show' . ' WHERE is_show = 1';
		$res = $db->query_read_slave ( $sql );

		while ( $row = $db->fetch_array ( $res ) ) {
			$row ['title'] = skyuc_iconv ( 'UTF-8', 'GBK', $row ['title'] );
			$row ['image'] = $domain . $row ['image'];
			$row ['detail'] = skyuc_iconv ( 'UTF-8', 'GBK', sub_str ( $row ['detail'], 100 ) );
			$row ['longtime'] = $row ['longtime'] * 60;
			$row ['add_time'] = skyuc_date ( $skyuc->options ['date_format'] . ' ' . $skyuc->options ['time_format'], $row ['add_time'], FALSE, FALSE );

			$smi = new baidu_sitemap_item ( $domain . build_uri ( 'show', array ('mid' => $row ['show_id'] ) ), $row ['add_date'], $row ['title'], $row ['image'], $row ['detail'], $row ['runtime'] );
			$sm->add_item ( $smi );
		}
		$sm_file = 'sitemaps_baidu.xml';
		if ($sm->build ( $sm_file )) {
			sys_msg ( sprintf ( $_LANG ['generate_success'], $domain . 'sitemaps_baidu.xml' ) );
		} else {
			$sm_file = 'data/sitemaps_baidu.xml';
			if ($sm->build ( $sm_file )) {
				sys_msg ( sprintf ( $_LANG ['generate_success'], $domain . 'data/sitemaps_baidu.xml' ) );
			} else {
				sys_msg ( sprintf ( $_LANG ['generate_failed'] ) );
			}
		}

	}

}

?>
