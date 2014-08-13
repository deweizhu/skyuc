<?php
/**
 * SKYUC! 前台RSS Feed 生成程序
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
define ( 'THIS_SCRIPT', 'rss' );
define ( 'SKYUC_AREA', 'IN_SKYUC' );
define ( 'CSRF_PROTECTION', true );
define ( 'LOCATION_BYPASS', 1 );
define ( 'NOSHUTDOWNFUNC', 1 );
define ( 'NOCOOKIES', 1 );
define ( 'INGORE_VISIT_STATS', true );
define ( 'SKIP_SMARTY', 1 );
define ( 'SKIP_SESSIONCREATE', 1 );
define ( 'SKIP_USERINFO', 1 );
define ( 'SKIP_DEFAULTDATASTORE', 1 );

require (dirname ( __FILE__ ) . '/includes/init.php');

@date_default_timezone_set ( 'Etc/GMT' . ($skyuc->options ['timezoneoffset'] > 0 ? '-' : '+') . (abs ( $skyuc->options ['timezoneoffset'] )) );

header ( 'Content-Type: application/xml; charset=utf-8' );
header ( 'Cache-Control: no-cache, must-revalidate' );
header ( 'Expires: Fri, 14 Mar 1980 20:53:00 GMT' );
header ( 'Last-Modified: ' . gmdate ( 'r' ) );
header ( 'Pragma: no-cache' );

$sql = 'SELECT c.cat_name, m.show_id, m.title, m.description, m.add_time ' . 'FROM ' . TABLE_PREFIX . 'category' . ' AS c, ' . TABLE_PREFIX . 'show' . ' AS m ' . 'WHERE c.cat_id = m.cat_id AND m.is_show = 1 ' . 'ORDER BY m.add_time DESC';

$sql = $skyuc->db->query_limit ( $sql, 100 );
$res = $skyuc->db->query_read_slave ( $sql );

if ($res !== false) {

	require_once (DIR . '/includes/class_xml.php');
	$xml = new XML_Builder ( $skyuc );
	$uri = get_url ();

	$xml->add_group ( 'rss', array ('version' => '2.0' ) );
	$xml->add_group ( 'channel' );
	$xml->add_tag ( 'title', $skyuc->options ['site_name'] );
	$xml->add_tag ( 'link', $skyuc->options ['site_url'] );
	$xml->add_tag ( 'description', $skyuc->options ['site_desc'] );
	$xml->add_tag ( 'docs', $uri . 'rss.php' );
	$xml->add_tag ( 'pubDate', skyuc_date ( 'r' ) );
	$xml->add_group ( 'image' );
	$xml->add_tag ( 'title', $skyuc->options ['site_name'] );
	$xml->add_tag ( 'url', $skyuc->options ['site_url'] );
	$xml->add_tag ( 'link', $skyuc->options ['site_url'] );
	$xml->add_tag ( 'description', $skyuc->options ['site_desc'] );
	$xml->close_group ();

	$xml->add_tag ( 'generator', APPNAME . ' ' . VERSION );
	while ( $row = $db->fetch_array ( $res ) ) {
		$item_url = build_uri ( 'show', array ('mid' => $row ['show_id'] ), $row ['title'] );
		$about = $uri . $item_url;
		$title = $row ['title'];
		$link = $uri . $item_url;
		$desc = $row ['description'];
		$category = $row ['cat_name'];

		$date = skyuc_date ( 'r', $row ['add_time'] );

		$xml->add_group ( 'item' );
		$xml->add_tag ( 'title', $row ['title'] );
		$xml->add_tag ( 'link', $uri . $item_url );
		$xml->add_tag ( 'description', $row ['description'] );
		$xml->add_tag ( 'category', $row ['cat_name'] );
		$xml->add_tag ( 'pubDate', skyuc_date ( 'r', $row ['add_time'] ) );
		$xml->add_tag ( 'guid', $uri . $item_url );
		$xml->close_group ();
	}
	$xml->close_group ();
	$xml->close_group ();

	$xml->print_xml ();
	$xml = null;
}

?>