<?php

/**
 * SKYUC! 快钱插件
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

$payment_lang = DIR . '/languages/' . $GLOBALS['skyuc']->options ['lang'] . '/payment/kuaiqian.php';

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
	$modules [$i] ['desc'] = 'kq_desc';

	/* 是否支持实时开通 */
	$modules [$i] ['is_cod'] = '1';

	/* 作者 */
	$modules [$i] ['author'] = '99bill.com';

	/* 网址 */
	$modules [$i] ['website'] = 'http://www.99bill.com';

	/* 版本号 */
	$modules [$i] ['version'] = '1.0.1';

	/* 配置信息 */
	$modules [$i] ['config'] = array (array ('name' => 'kq_account', 'type' => 'text', 'value' => '' ), array ('name' => 'kq_key', 'type' => 'text', 'value' => '' ) );

	return;
}

/**
 * 类
 */
class kuaiqian {
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
		$data_merchant_id = $payment ['kq_account'];
		$data_order_id = $order ['log_id'];
		$data_amount = $order ['pay_amount'];
		$data_paymoney_type = '1';
		$data_body = '';
		$data_pname = ! empty ( $order ['user_name'] ) ? $order ['user_name'] : '';
		$data_return_url = return_url ( basename ( __FILE__, '.php' ) );

		$data_pay_key = $payment ['kq_key'];
		$data_pay_account = $payment ['kq_account'];

		$def_url = "merchant_id=" . $data_merchant_id . "&orderid=" . $data_order_id . "&amount=" . $data_amount . "&merchant_url=" . $data_return_url . "&merchant_key=" . $data_pay_key;
		$MD5KEY = strtoupper ( md5 ( $def_url ) );

		$def_url = '<br /><form style="text-align:center;" action="https://www.99bill.com/webapp/receiveMerchantInfoAction.do" method="post" target="_blank">';
		$def_url .= "<input type='hidden' name='merchant_id' value='" . $data_merchant_id . "'>\n";
		$def_url .= "<input type='hidden' name='orderid' value='" . $data_order_id . "'>\n";
		$def_url .= "<input type='hidden' name='amount' value='" . $data_amount . "'>\n";
		$def_url .= "<input type='hidden' name='commodity_info'  value='" . $data_body . "'>\n";
		$def_url .= "<input type='hidden' name='merchant_url'  value='" . $data_return_url . "'>\n";
		$def_url .= "<input type='hidden' name='pname'  value='" . $data_pname . "'>\n";
		$def_url .= "<input type='hidden' name='currency' value='" . $data_paymoney_type . "'>\n";
		$def_url .= "<input type='hidden' name='isSupportDES' value='2'>\n";
		$def_url .= "<input type='hidden' name='mac' value='" . $MD5KEY . "'>\n";
		$def_url .= "<input type='hidden' name='pid' value='bjcx13'>\n";
		$def_url .= "<input type='submit' value='" . $GLOBALS ['_LANG'] ['pay_button'] . "'>";
		$def_url .= "</form><br />";

		return $def_url;
	}

	/**
	 * 响应操作
	 */
	public function respond() {
		$payment = get_payment ( 'kuaiqian' );

		$merchant_id = $payment ['kq_account']; ///获取商户编号
		$merchant_key = $payment ['kq_key']; ///获取秘钥


		$order_sn = trim ( @$_REQUEST ['orderid'] );
		$amount = trim ( @$_REQUEST ['amount'] ); ///获取订单金额
		$dealdate = trim ( @$_REQUEST ['date'] ); ///获取交易日期
		$succeed = trim ( @$_REQUEST ['succeed'] ); ///获取交易结果,Y成功,N失败
		$mac = trim ( @$_REQUEST ['mac'] ); ///获取安全加密串
		$merchant_param = trim ( @$_REQUEST ['merchant_param'] ); ///获取商户私有参数


		//        $couponid       = trim(@$_REQUEST['couponid']);            ///获取优惠券编码
		//        $couponvalue    = trim(@$_REQUEST['couponvalue']);     ///获取优惠券面额


		///生成加密串,注意顺序
		$ScrtStr = "merchant_id=" . $merchant_id . "&orderid=" . $order_sn . "&amount=" . $amount . "&date=" . $dealdate . "&succeed=" . $succeed . "&merchant_key=" . $merchant_key;
		$mymac = md5 ( $ScrtStr );

		$v_result = false;

		if (strtoupper ( $mac ) == strtoupper ( $mymac )) {
			if ($succeed == 'Y') {
				///支付成功
				$v_result = true;

				order_paid ( $order_sn );
			}
		}

		return $v_result;
	}
}

?>
