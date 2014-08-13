<?php

/**
 * SKYUC! 贝宝插件
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
$payment_lang = DIR . '/languages/' . $GLOBALS['skyuc']->options ['lang'] . '/payment/paypal.php';

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
	$modules [$i] ['desc'] = 'paypal_desc';

	/* 是否支持实时开通 */
	$modules [$i] ['is_cod'] = '1';

	/* 是否支持在线支付 */
	$modules [$i] ['is_online'] = '1';
	/* 作者 */
	$modules [$i] ['author'] = 'paypal.com';

	/* 网址 */
	$modules [$i] ['website'] = 'http://www.paypal.com';

	/* 版本号 */
	$modules [$i] ['version'] = '1.0.0';

	/* 配置信息 */
	$modules [$i] ['config'] = array (array ('name' => 'paypal_account', 'type' => 'text', 'value' => '' ), array ('name' => 'paypal_currency', 'type' => 'select', 'value' => 'USD' ) );

	return;
}

/**
 * 类
 */
class paypal {
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
		$data_order_id = $order ['log_id'];
		$data_amount = $order ['pay_amount'];
		$data_return_url = return_url ( basename ( __FILE__, '.php' ) );
		$data_pay_account = $payment ['paypal_account'];
		$currency_code = $payment ['paypal_currency'];
		$data_notify_url = return_url ( basename ( __FILE__, '.php' ) );
		$cancel_return = get_url ();

		$def_url = '<br /><form style="text-align:center;" action="https://www.paypal.com/cgi-bin/webscr" method="post">' . // 不能省略
"<input type='hidden' name='cmd' value='_xclick'>" . // 不能省略
"<input type='hidden' name='business' value='$data_pay_account'>" . // 贝宝帐号
"<input type='hidden' name='item_name' value='$order[order_sn]'>" . // payment for
"<input type='hidden' name='amount' value='$data_amount'>" . // 订单金额
"<input type='hidden' name='currency_code' value='$currency_code'>" . // 货币
"<input type='hidden' name='return' value='$data_return_url'>" . // 付款后页面
"<input type='hidden' name='invoice' value='$data_order_id'>" . // 订单号
"<input type='hidden' name='charset' value='utf-8'>" . // 字符集
"<input type='hidden' name='no_shipping' value='1'>" . // 不要求客户提供收货地址
"<input type='hidden' name='no_note' value=''>" . // 付款说明
"<input type='hidden' name='notify_url' value='$data_notify_url'>" . "<input type='hidden' name='rm' value='2'>" . "<input type='hidden' name='cancel_return' value='$cancel_return'>" . "<input type='submit' value='" . $GLOBALS ['_LANG'] ['paypal_button'] . "'>" . // 按钮
"</form><br />";

		return $def_url;
	}

	/**
	 * 响应操作
	 */
	public function respond() {
		$payment = get_payment ( 'paypal' );
		$merchant_id = $payment ['paypal_account']; ///获取商户编号


		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		foreach ( $_POST as $key => $value ) {
			$value = urlencode ( stripslashes ( $value ) );
			$req .= "&$key=$value";
		}

		// post back to PayPal system to validate
		$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen ( $req ) . "\r\n\r\n";
		$fp = fsockopen ( 'www.paypal.com', 80, $errno, $errstr, 30 );

		// assign posted variables to local variables
		$item_name = $_POST ['item_name'];
		$item_number = $_POST ['item_number'];
		$payment_status = $_POST ['payment_status'];
		$payment_amount = $_POST ['mc_gross'];
		$payment_currency = $_POST ['mc_currency'];
		$txn_id = $_POST ['txn_id'];
		$receiver_email = $_POST ['receiver_email'];
		$payer_email = $_POST ['payer_email'];
		$order_sn = ( int ) $_POST ['invoice'];
		$memo = ! empty ( $_POST ['memo'] ) ? $_POST ['memo'] : '';
		$action_note = $txn_id . '（' . $GLOBALS ['_LANG'] ['paypal_txn_id'] . '）' . $memo;

		if (! $fp) {
			fclose ( $fp );

			return false;
		} else {
			fputs ( $fp, $header . $req );
			while ( ! feof ( $fp ) ) {
				$res = fgets ( $fp, 1024 );
				if (strcmp ( $res, 'VERIFIED' ) == 0) {
					// check the payment_status is Completed
					if ($payment_status != 'Completed' && $payment_status != 'Pending') {
						fclose ( $fp );

						return false;
					}

					// check that receiver_email is your Primary PayPal email
					if ($receiver_email != $merchant_id) {
						fclose ( $fp );

						return false;
					}

					// check that payment_amount/payment_currency are correct
					$sql = 'SELECT order_amount FROM ' . TABLE_PREFIX . 'order_info' . " WHERE order_sn = '$order_sn'";
					$order_info = $GLOBALS ['db']->query_first ( $sql );
					if ($order_info ['order_amount'] != $payment_amount) {
						fclose ( $fp );

						return false;
					}
					if ($payment ['paypal_currency'] != $payment_currency) {
						fclose ( $fp );

						return false;
					}

					// process payment
					order_paid ( $order_sn, PS_PAYED, $action_note );
					fclose ( $fp );

					return true;
				} elseif (strcmp ( $res, 'INVALID' ) == 0) {
					// log for manual investigation
					fclose ( $fp );

					return false;
				}
			}
		}
	}
}

?>
