<?php
/**
 * SKYUC! 影片详情文件
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
define ( 'THIS_SCRIPT', 'subject' );
define ( 'CSRF_PROTECTION', true );
define ( 'CSRF_SKIP_LIST', '' );
define ( 'SMARTY_CACHE', true );

require (dirname ( __FILE__ ) . '/global.php');
/*------------------------------------------------------ */
//-- ID过滤
/*------------------------------------------------------ */
$skyuc->input->clean_gpc ( 'r', 'id', TYPE_UINT );
$id = $skyuc->GPC ['id'];
$cache_id = sprintf ( '%X', crc32 ( $id ) );
/*------------------------------------------------------ */
//-- 判断是否存在缓存，如果存在则调用缓存，反之读取相应内容
/*------------------------------------------------------ */
if (! $smarty->is_cached ( 'subject.dwt', $cache_id )) {

	//获得影片的信息
	$subject = get_subject_detail ( $id );

	if (empty ( $subject )) {
		// 如果没有找到任何记录则跳回到首页
		header ( "Location: ./\n" );
		exit ();
	} else {
		$smarty->assign ( 'page_title', $subject ['title'] );
		$smarty->assign ( 'detail', $subject ['detail'] );
	}
}

$smarty->display ( "subject.dwt", $cache_id );

/**
 * 获得指定的专题内容。
 *
 * @access  private
 * @return  array
 */
function get_subject_detail($id) {
	global $skyuc;

	$sql = 'SELECT id,title,detail FROM ' . TABLE_PREFIX . 'subject' . ' WHERE id=' . $id;
	$row = $skyuc->db->query_first_slave ( $sql );
	return $row;
}
?>