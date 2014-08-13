<?php

/**
 * SKYUC! YeePay易宝神州行支付插件
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

$payment_lang = DIR . '/languages/' . $GLOBALS['skyuc']->options ['lang'] . '/payment/yeepayszx.php';

if (is_file ( $payment_lang )) {
	global $_LANG;

	include_once ($payment_lang);
}

/* 模块的基本信息 */
if (isset ( $set_modules ) && $set_modules == TRUE) {
	$i = isset ( $modules ) ? count ( $modules ) : 0;

	// 代码
	$modules [$i] ['code'] = basename ( __FILE__, '.php' );

	// 描述对应的语言项
	$modules [$i] ['desc'] = 'ypszx_desc';

	// 是否支持实时开通
	$modules [$i] ['is_cod'] = '1';

	// 是否支持在线支付
	$modules [$i] ['is_online'] = '1';

	// 作者
	$modules [$i] ['author'] = 'yeepay.com';

	// 网址
	$modules [$i] ['website'] = 'http://www.yeepay.com';

	// 版本号
	$modules [$i] ['version'] = '1.0.1';

	// 配置信息
	$modules [$i] ['config'] = array (array ('name' => 'yp_account', 'type' => 'text', 'value' => '' ), array ('name' => 'yp_key', 'type' => 'text', 'value' => '' ) );

	return;
}

/**
 * 类
 */
class yeepayszx {
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
	 * @param   array   $order  订单信息
	 * @param   array   $payment    支付方式信息
	 */
	public function get_code($order, $payment) {
		$data_merchant_id = $payment ['yp_account'];
		$data_order_id = $order ['log_id'];
		$data_amount = $order ['pay_amount'];
		$message_type = 'Buy';
		$data_cur = 'CNY';
		$product_id = '';
		$product_cat = '';
		$product_desc = '';
		$address_flag = '0';

		$data_return_url = return_url ( basename ( __FILE__, '.php' ) );

		$data_pay_key = $payment ['yp_key'];
		$data_pay_account = $payment ['yp_account'];
		$mct_properties = '';
		$frp_id = 'SZX';
		$need_response = '';
		$def_url = $message_type . $data_merchant_id . $data_order_id . $data_amount . $data_cur . $product_id . $data_return_url . $address_flag . $mct_properties . $frp_id . $need_response;
		$MD5KEY = $this->hmac ( $def_url, $data_pay_key );

		$def_url = "\n<form action='https://www.yeepay.com/app-merchant-proxy/node' method='post' target='_blank'>\n";
		$def_url .= "<input type='hidden' name='p0_Cmd' value='" . $message_type . "'>\n";
		$def_url .= "<input type='hidden' name='p1_MerId' value='" . $data_merchant_id . "'>\n";
		$def_url .= "<input type='hidden' name='p2_Order' value='" . $data_order_id . "'>\n";
		$def_url .= "<input type='hidden' name='p3_Amt' value='" . $data_amount . "'>\n";
		$def_url .= "<input type='hidden' name='p4_Cur' value='" . $data_cur . "'>\n";
		$def_url .= "<input type='hidden' name='p5_Pid' value='" . $product_id . "'>\n";
		//$def_url .= "<input type='hidden' name='p6_Pcat' value='".$product_cat."'>\n";
		//$def_url .= "<input type='hidden' name='p7_Pdesc' value='".$product_desc."'>\n";
		$def_url .= "<input type='hidden' name='p8_Url' value='" . $data_return_url . "'>\n";
		$def_url .= "<input type='hidden' name='p9_SAF' value='" . $address_flag . "'>\n";
		$def_url .= "<input type='hidden' name='pa_MP' value='" . $mct_properties . "'>\n";
		$def_url .= "<input type='hidden' name='pd_FrpId' value='" . $frp_id . "' >\n";
		$def_url .= "<input type='hidden' name='pr_NeedResponse'  value='" . $need_response . "' >\n";
		$def_url .= "<input type='hidden' name='hmac' value='" . $MD5KEY . "'>\n";
		$def_url .= "<input type='submit' value='" . $GLOBALS ['_LANG'] ['pay_button'] . "'>";
		$def_url .= "</form>\n";

		return $def_url;
	}

	/**
	 * 响应操作
	 */
	public function respond() {
		$payment = get_payment ( 'yeepay' );

		$merchant_id = $payment ['yp_account']; // 获取商户编号
		$merchant_key = $payment ['yp_key']; // 获取秘钥


		$message_type = trim ( $_REQUEST ['r0_Cmd'] );
		$succeed = trim ( $_REQUEST ['r1_Code'] ); // 获取交易结果,1成功,-1失败
		$trxId = trim ( $_REQUEST ['r2_TrxId'] );
		$amount = trim ( $_REQUEST ['r3_Amt'] ); // 获取订单金额
		$cur = trim ( $_REQUEST ['r4_Cur'] ); // 获取订单货币单位
		$product_id = trim ( $_REQUEST ['r5_Pid'] ); // 获取产品ID
		$orderid = trim ( $_REQUEST ['r6_Order'] ); // 获取订单ID
		$userId = trim ( $_REQUEST ['r7_Uid'] ); // 获取产品ID
		$merchant_param = trim ( $_REQUEST ['r8_MP'] ); // 获取商户私有参数
		$bType = trim ( $_REQUEST ['r9_BType'] ); // 获取订单ID


		$mac = trim ( $_REQUEST ['hmac'] ); // 获取安全加密串


		///生成加密串,注意顺序
		$ScrtStr = $merchant_id . $message_type . $succeed . $trxId . $amount . $cur . $product_id . $orderid . $userId . $merchant_param . $bType;

		$mymac = $this->hmac ( $ScrtStr, $merchant_key );

		$v_result = false;

		if (strtoupper ( $mac ) == strtoupper ( $mymac )) {
			if ($succeed == '1') {
				///支付成功
				$v_result = true;

				order_paid ( $orderid );
			}
		}

		return $v_result;
	}
	public function hmac($data, $key) {
		// RFC 2104 HMAC implementation for php.
		// Creates an md5 HMAC.
		// Eliminates the need to install mhash to compute a HMAC
		// Hacked by Lance Rushing(NOTE: Hacked means written)


		$key = skyuc_iconv ( 'GB2312', 'UTF8', $key );
		$data = skyuc_iconv ( 'GB2312', 'UTF8', $data );

		$b = 64; // byte length for md5
		if (strlen ( $key ) > $b) {
			$key = pack ( 'H*', md5 ( $key ) );
		}

		$key = str_pad ( $key, $b, chr ( 0x00 ) );
		$ipad = str_pad ( '', $b, chr ( 0x36 ) );
		$opad = str_pad ( '', $b, chr ( 0x5c ) );
		$k_ipad = $key ^ $ipad;
		$k_opad = $key ^ $opad;

		return md5 ( $k_opad . pack ( 'H*', md5 ( $k_ipad . $data ) ) );
	}
}

?>