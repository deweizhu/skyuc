<?php
/**
 * SKYUC! 支付响应页面
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
define ( 'THIS_SCRIPT', 'responed' );
//define('CSRF_PROTECTION', false);
define ( 'NOSHUTDOWNFUNC', true );

require (dirname ( __FILE__ ) . '/global.php');

require (DIR . '/includes/functions_payment.php');

$skyuc->input->clean_array_gpc ( 'r', array ('code' => TYPE_STR, 'v_pmode' => TYPE_STR, 'v_pstring' => TYPE_STR, 'ext1' => TYPE_STR, 'ext2' => TYPE_STR ) );

// 支付方式代码
$pay_code = $skyuc->GPC ['code'];

//获取首信支付方式
if (empty ( $pay_code ) && ! empty ( $skyuc->GPC ['v_pmode'] ) && ! empty ( $skyuc->GPC ['v_pstring'] )) {
	$pay_code = 'cappay';
}
//获取快钱神州行支付方式
if (empty ( $pay_code ) && ($skyuc->GPC ['ext1'] == 'shenzhou') && ($skyuc->GPC ['ext2'] == 'skyuc')) {
	$pay_code = 'kuaiqianszx';
}
/* 参数是否为空 */
if (empty ( $pay_code )) {
	$msg = $_LANG ['pay_not_exist'];
} else {
	/* 检查code里面有没有问号 */
	if (strpos ( $pay_code, '?' ) !== false) {
		$arr1 = explode ( '?', $pay_code );
		$arr2 = explode ( '=', $arr1 [1] );

		$_REQUEST ['code'] = $arr1 [0];
		$_REQUEST [$arr2 [0]] = $arr2 [1];
		$_GET ['code'] = $arr1 [0];
		$_GET [$arr2 [0]] = $arr2 [1];
		$pay_code = $arr1 [0];
	}

	// 判断是否启用
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'payment' . " WHERE pay_code = '" . $db->escape_string ( $pay_code ) . "' AND enabled = 1";
	$total = $db->query_first_slave ( $sql );

	if ($total ['total'] == 0) {
		$msg = $_LANG ['pay_disabled'];
	} else {
		$plugin_file = DIR . '/includes/modules/payment/' . $pay_code . '.php';

		/*
         * 检查插件文件是否存在，如果存在则验证支付是否成功，否则则返回失败信息
         */
		if (is_file ( $plugin_file )) {
			/* 根据支付方式代码创建支付类的对象并调用其响应操作方法 */
			include_once ($plugin_file);

			$payment = new $pay_code ();
			$msg = iif ( $payment->respond (), $_LANG ['pay_success'], $_LANG ['pay_fail'] );
		} else {
			$msg = $_LANG ['pay_not_exist'];
		}
	}
}

assign_template ();
$position = assign_ur_here ();
$smarty->assign ( 'page_title', $position ['title'] ); // 页面标题
$smarty->assign ( 'ur_here', $position ['ur_here'] ); // 当前位置


$smarty->assign ( 'nav_list', get_navigator () );
$smarty->assign ( 'page_title', $position ['title'] ); // 页面标题
$smarty->assign ( 'ur_here', $position ['ur_here'] ); // 当前位置


$smarty->assign ( 'message', $msg );
$smarty->assign ( 'site_url', get_url () );

$smarty->display ( 'respond.dwt' );

?>