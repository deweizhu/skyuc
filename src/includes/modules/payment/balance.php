<?php

/**
 * SKYUC! 余额支付插件
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

if (! defined ( 'SKYUC_AREA' )) {
	echo 'SKYUC_AREA  must be defined to continue';
	exit ();
}

$payment_lang = DIR . '/languages/' . $GLOBALS['skyuc']->options ['lang'] . '/payment/balance.php';

if (is_file ( $payment_lang )) {
	global $_LANG;

	include_once ($payment_lang);
}

/* 模块的基本信息 */
if (isset ( $set_modules ) && $set_modules == TRUE) {
	$i = iif ( $modules, count ( $modules ), 0 );

	/* 代码 */
	$modules [$i] ['code'] = basename ( __FILE__, '.php' );

	/* 描述对应的语言项 */
	$modules [$i] ['desc'] = 'balance_desc';

	/* 是否支持实时开通 */
	$modules [$i] ['is_cod'] = '1';

	/* 支付费用 */
	$modules [$i] ['pay_fee'] = '0';

	/* 作者 */
	$modules [$i] ['author'] = 'SKYUC TEAM';

	/* 网址 */
	$modules [$i] ['website'] = 'http://www.skyuc.com';

	/* 版本号 */
	$modules [$i] ['version'] = '1.0.0';

	/* 配置信息 */
	$modules [$i] ['config'] = array ();

	return;
}

/**
 * 类
 */
class balance {
	/**
	 * 构造函数
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * 提交函数
	 */
	public function get_code() {
		return '';
	}

	/**
	 * 处理函数
	 */
	public function response() {
		return;
	}
}

?>