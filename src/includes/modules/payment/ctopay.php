<?php

/**
 * SKYUC! Ctopay 支付插件
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

$payment_lang = DIR . '/languages/' . $GLOBALS['skyuc']->options ['lang'] . '/payment/ctopay.php';

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
	$modules [$i] ['desc'] = 'ctopay_desc';

	/* 是否支持实时开通 */
	$modules [$i] ['is_cod'] = '1';

	/* 作者 */
	$modules [$i] ['author'] = 'ctopay.com';

	/* 网址 */
	$modules [$i] ['website'] = 'http://www.ctopay.com';

	/* 版本号 */
	$modules [$i] ['version'] = '1.0.0';

	/* 配置信息 */
	$modules [$i] ['config'] = array (array ('name' => 'MerNo', 'type' => 'text', 'value' => '' ), array ('name' => 'MD5key', 'type' => 'text', 'value' => '' ), array ('name' => 'Currency', 'type' => 'select', 'value' => '' ), array ('name' => 'Language', 'type' => 'select', 'value' => '' ) );

	return;
}

/**
 * 类
 */
class ctopay {

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
	 * 生成支付代码
	 * @param   array   $order      订单信息
	 * @param   array   $payment    支付方式信息
	 */
	public function get_code($order, $payment) {
		$MD5key = $payment ['MD5key']; //MD5私钥
		$MerNo = $payment ['MerNo']; //商户号
		$BillNo = $order ['log_id']; //订单号
		$Currency = $payment ['Currency']; //币种
		$Amount = $order ['pay_amount']; //金额
		$DispAmount = 0; //外币金额
		$Language = $payment ['Language']; //语言
		$ReturnURL = return_url ( basename ( __FILE__, '.php' ) ); //返回地址
		$Remark = "From SKYUC! order sn : " . $order ['order_sn']; //备注


		$md5src = $MerNo . $BillNo . $Currency . $Amount . $Language . $ReturnURL . $MD5key; //校验源字符串
		$MD5info = strtoupper ( md5 ( $md5src ) ); //MD5检验结果


		$button = '<form action="http://219.133.36.139/payment/Interface" method="post">' . "  <input type='hidden' name='MerNo' value='" . $MerNo . "'>" . "  <input type='hidden' name='Currency' value='" . $Currency . "'>" . "  <input type='hidden' name='BillNo' value='" . $BillNo . "'>" . "  <input type='hidden' name='Amount' value='" . $Amount . "'>" . "  <input type='hidden' name='DispAmount' value='" . $DispAmount . "'>" . "  <input type='hidden' name='ReturnURL' value='" . $ReturnURL . "'>" . "  <input type='hidden' name='Language' value='" . $Language . "'>" . "  <input type='hidden' name='MD5info' value='" . $MD5info . "'>" . "  <input type='hidden' name='Remark' value='" . $Remark . "'>" . "  <input type='submit' name='b1' value='" . $GLOBALS ['_LANG'] ['pay_button'] . "'>" . "</form>";

		return $button;
	}

	/**
	 * 响应操作
	 */
	public function respond() {
		$payment = get_payment ( $_GET ['code'] );

		$BillNo = $_REQUEST ["BillNo"]; //订单号
		$Currency = $_REQUEST ["Currency"]; //币种
		$BankID = $_REQUEST ["BankID"]; //银行ID号
		$Amount = $_REQUEST ["Amount"]; //金额
		$Succeed = $_REQUEST ["Succeed"]; //支付状态
		$TradeNo = $_REQUEST ["TradeNo"]; //支付平台流水号
		$Result = $_REQUEST ["Result"]; //支付结果
		$MD5info = $_REQUEST ["MD5info"]; //取得的MD5校验信息
		$Remark = $_REQUEST ["Remark"]; //备注
		//$Drawee = $_REQUEST["Drawee"];   //支付人名称


		$MD5key = $payment ['MD5key']; //MD5私钥
		$md5src = $BillNo . $Currency . $Amount . $Succeed . $MD5key; //校验源字符串
		$md5sign = strtoupper ( md5 ( $md5src ) ); //MD5检验结果


		/* 验证 */
		if ($MD5info != $md5sign) {
			return false;
		}

		if ($Succeed == 1) {
			/* 改变订单状态 */
			order_paid ( $BillNo, PS_PAYED );

			return true;
		} else {
			return false;
		}
	}
}

?>
