<?php

/**
 * SKYUC! 网银在线插件
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

$payment_lang = DIR . '/languages/' . $GLOBALS['skyuc']->options ['lang'] . '/payment/chinabank.php';

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
	$modules [$i] ['desc'] = 'chinabank_desc';

	/* 是否支持实时开通 */
	$modules [$i] ['is_cod'] = '1';

	/* 支付费用 */
	$modules [$i] ['pay_fee'] = '1%';

	/* 作者 */
	$modules [$i] ['author'] = 'chinabank.com.cn';

	/* 网址 */
	$modules [$i] ['website'] = 'http://www.chinabank.com.cn';

	/* 版本号 */
	$modules [$i] ['version'] = '1.0.1';

	/* 配置信息 */
	$modules [$i] ['config'] = array (array ('name' => 'chinabank_account', 'type' => 'text', 'value' => '' ), array ('name' => 'chinabank_key', 'type' => 'text', 'value' => '' ) );

	return;
}

/**
 * 类
 */
class chinabank {
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
		$data_vid = trim ( $payment ['chinabank_account'] );
		$data_orderid = $order ['log_id'];
		$data_vamount = $order ['pay_amount'];
		$data_vmoneytype = 'CNY';
		$data_vpaykey = trim ( $payment ['chinabank_key'] );
		$data_vreturnurl = return_url ( basename ( __FILE__, '.php' ) );

		$MD5KEY = $data_vamount . $data_vmoneytype . $data_orderid . $data_vid . $data_vreturnurl . $data_vpaykey;
		$MD5KEY = strtoupper ( md5 ( $MD5KEY ) );

		$def_url = '<br /><form style="text-align:center;" method=post action="https://pay3.chinabank.com.cn/PayGate" target="_blank">';
		$def_url .= "<input type=HIDDEN name='v_mid' value='" . $data_vid . "'>";
		$def_url .= "<input type=HIDDEN name='v_oid' value='" . $data_orderid . "'>";
		$def_url .= "<input type=HIDDEN name='v_amount' value='" . $data_vamount . "'>";
		$def_url .= "<input type=HIDDEN name='v_moneytype'  value='" . $data_vmoneytype . "'>";
		$def_url .= "<input type=HIDDEN name='v_url'  value='" . $data_vreturnurl . "'>";
		$def_url .= "<input type=HIDDEN name='v_md5info' value='" . $MD5KEY . "'>";
		$def_url .= "<input type=submit value='" . $GLOBALS ['_LANG'] ['pay_button'] . "'>";
		$def_url .= "</form>";

		return $def_url;
	}

	/**
	 * 响应操作
	 */
	public function respond() {
		$payment = get_payment ( basename ( __FILE__, '.php' ) );

		$v_oid = trim ( $_POST ['v_oid'] );
		$v_pmode = trim ( $_POST ['v_pmode'] );
		$v_pstatus = trim ( $_POST ['v_pstatus'] );
		$v_pstring = trim ( $_POST ['v_pstring'] );
		$v_amount = trim ( $_POST ['v_amount'] );
		$v_moneytype = trim ( $_POST ['v_moneytype'] );
		$remark1 = trim ( $_POST ['remark1'] );
		$remark2 = trim ( $_POST ['remark2'] );
		$v_md5str = trim ( $_POST ['v_md5str'] );

		/**
		 * 重新计算md5的值
		 */
		$key = $payment ['chinabank_key'];

		$md5string = strtoupper ( md5 ( $v_oid . $v_pstatus . $v_amount . $v_moneytype . $key ) );

		/* 检查秘钥是否正确 */
		if ($v_md5str == $md5string) {
			if ($v_pstatus == '20') {
				/* 改变订单状态 */
				order_paid ( $v_oid );

				return true;
			}
		} else {
			return false;
		}
	}
}

?>
