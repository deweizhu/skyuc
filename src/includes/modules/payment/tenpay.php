<?php

/**
 * SKYUC! 财付通插件
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

$payment_lang = DIR . '/languages/' . $GLOBALS['skyuc']->options ['lang'] . '/payment/tenpay.php';

if (is_file ( $payment_lang )) {
	global $_LANG;

	include_once ($payment_lang);
}

/* 模块的基本信息 */
if (isset ( $set_modules ) && $set_modules == TRUE) {
	$i = isset ( $modules ) ? count ( $modules ) : 0;

	/* 代码 */
	$modules [$i] ['code'] = basename ( __FILE__, '.php' );

	/* 描述对应的语言项 */
	$modules [$i] ['desc'] = 'tenpay_desc';

	/* 是否支持实时开通 */
	$modules [$i] ['is_cod'] = '1';

	/* 作者 */
	$modules [$i] ['author'] = 'tenpay.com';

	/* 网址 */
	$modules [$i] ['website'] = 'http://www.tenpay.com';

	/* 版本号 */
	$modules [$i] ['version'] = '1.0.0';

	/* 配置信息 */
	$modules [$i] ['config'] = array (array ('name' => 'tenpay_account', 'type' => 'text', 'value' => '' ), array ('name' => 'tenpay_key', 'type' => 'text', 'value' => '' ), array ('name' => 'magic_string', 'type' => 'text', 'value' => '' ) );

	return;
}

/**
 * 类
 */
class tenpay {
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
	 * @param   array    $order       订单信息
	 * @param   array    $payment     支付方式信息
	 */
	public function get_code($order, $payment) {
		$cmd_no = '1';

		/* 获得订单的流水号，补零到10位 */
		$bill_no = str_pad ( $order ['log_id'], 10, 0, STR_PAD_LEFT );

		/* 交易日期 */
		$today = date ( 'Ymd' );

		/* 将商户号+年月日+流水号 */
		$transaction_id = $payment ['tenpay_account'] . $today . $bill_no;

		/* 银行类型:支持纯网关和财付通 */
		$bank_type = '0';

		/* 订单描述，用订单号替代 */
		$desc = str_pad ( $order ['order_sn'], 13, 0, STR_PAD_LEFT );
		;

		/* 返回的路径 */
		$return_url = return_url ( 'tenpay' );

		/* 总金额 */
		$total_fee = floatval ( $order ['pay_amount'] ) * 100;

		/* 货币类型 */
		$fee_type = '1';

		/* 重写自定义签名 */
		$payment ['magic_string'] = abs ( crc32 ( $payment ['magic_string'] ) );

		/* 数字签名 */
		$sign_text = "cmdno=" . $cmd_no . "&date=" . $today . "&bargainor_id=" . $payment ['tenpay_account'] . "&transaction_id=" . $transaction_id . "&sp_billno=" . $bill_no . "&total_fee=" . $total_fee . "&fee_type=" . $fee_type . "&return_url=" . $return_url . "&attach=" . $payment ['magic_string'] . "&key=" . $payment ['tenpay_key'];
		$sign = strtoupper ( md5 ( $sign_text ) );

		/* 交易参数 */
		$parameter = array ('cmdno' => $cmd_no, // 业务代码, 财付通支付支付接口填  1
'date' => $today, // 商户日期：如20051212
'bank_type' => $bank_type, // 银行类型:支持纯网关和财付通
'desc' => $desc, // 交易的商品名称
'purchaser_id' => '', // 用户(买方)的财付通帐户,可以为空
'bargainor_id' => $payment ['tenpay_account'], // 商家的财付通商户号
'transaction_id' => $transaction_id, // 交易号(订单号)，由商户网站产生(建议顺序累加)
'sp_billno' => $bill_no, // 商户系统内部的定单号,最多10位
'total_fee' => $total_fee, // 订单金额
'fee_type' => $fee_type, // 现金支付币种
'return_url' => $return_url, // 接收财付通返回结果的URL
'attach' => $payment ['magic_string'], // 用户自定义签名
'sign' => $sign )// MD5签名
;

		$button = '<br /><form style="text-align:center;" action="http://portal.tenpay.com/cfbiportal/cgi-bin/cfbiin.cgi" target="_blank" style="margin:0px;padding:0px" >';

		foreach ( $parameter as $key => $val ) {
			$button .= "<input type='hidden' name='$key' value='$val' />";
		}

		$button .= '<input type="submit" value="' . $GLOBALS ['_LANG'] ['pay_button'] . '" /></form><br />';

		return $button;
	}

	/**
	 * 响应操作
	 */
	public function respond() {
		/*取返回参数*/
		$cmd_no = $_GET ['cmdno'];
		$pay_result = $_GET ['pay_result'];
		$pay_info = $_GET ['pay_info'];
		$bill_date = $_GET ['date'];
		$bargainor_id = $_GET ['bargainor_id'];
		$transaction_id = $_GET ['transaction_id'];
		$sp_billno = $_GET ['sp_billno'];
		$total_fee = $_GET ['total_fee'];
		$fee_type = $_GET ['fee_type'];
		$attach = $_GET ['attach'];
		$sign = $_GET ['sign'];

		$payment = get_payment ( 'tenpay' );
		//$order_sn   = $bill_date . str_pad(intval($sp_billno), 5, '0', STR_PAD_LEFT);
		$log_id = preg_replace ( '/0*([0-9]*)/', '\1', $sp_billno ); //取得支付的log_id


		/* 如果pay_result大于0则表示支付失败 */
		if ($pay_result > 0) {
			return false;
		}

		/* 检查支付的金额是否相符 */
		if (! check_money ( $log_id, $total_fee / 100 )) {
			return false;
		}

		/* 检查数字签名是否正确 */
		$sign_text = "cmdno=" . $cmd_no . "&pay_result=" . $pay_result . "&date=" . $bill_date . "&transaction_id=" . $transaction_id . "&sp_billno=" . $sp_billno . "&total_fee=" . $total_fee . "&fee_type=" . $fee_type . "&attach=" . $attach . "&key=" . $payment ['tenpay_key'];
		$sign_md5 = strtoupper ( md5 ( $sign_text ) );
		if ($sign_md5 != $sign) {
			return false;
		} else {
			/* 改变订单状态 */
			order_paid ( $log_id, 1 );

			return true;
		}
	}
}

?>
