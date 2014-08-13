<?php
/**
 * SKYUC! 1topay支付系统插件
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

$payment_lang = DIR . '/languages/' . $GLOBALS['skyuc']->options ['lang'] . '/payment/onetopay.php';

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
	$modules [$i] ['desc'] = 'onetopay_desc';

	/* 是否支持货到付款 */
	$modules [$i] ['is_cod'] = '0';

	/* 是否支持在线支付 */
	$modules [$i] ['is_online'] = '1';

	/* 支付费用 */
	$modules [$i] ['pay_fee'] = '见壹支付后台商家自行设置，例:1%';

	/* 作者 */
	$modules [$i] ['author'] = '1topay.com';

	/* 网址 */
	$modules [$i] ['website'] = 'http://www.1topay.com';

	/* 版本号 */
	$modules [$i] ['version'] = '1.0.1';

	/* 配置信息 */
	$modules [$i] ['config'] = array (array ('name' => 'onetopay_account', 'type' => 'text', 'value' => '' ), array ('name' => 'onetopay_key', 'type' => 'text', 'value' => '' ), array ('name' => 'onetopay_sitecode', 'type' => 'text', 'value' => '' ) );

	return;
}

/**
 * 类
 */
class onetopay {
	/**
	 * 构造函数
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	function __construct() {
	}

	/**
	 * 生成支付代码
	 * @param   array   $order      订单信息
	 * @param   array   $payment    支付方式信息
	 */
	function get_code($order, $payment) {
		$p01_service = 'interface_pay';
		$p02_out_ordercode = $order ['order_sn']; //商户订单流水号
		$p03_payamount = $order ['pay_amount']; //支付金额
		$p04_sitecode = trim ( $payment ['onetopay_sitecode'] ); //商户网站身份ID
		$p05_subject = 'addmoney'; //产品名称
		$p06_body = 'savingmoney'; //产品描述
		$p07_price = $p03_payamount; //产品单价
		$p08_quantity = '1'; //购买数量
		$p09_notify_url = return_url ( basename ( __FILE__, '.php' ) ); //回调通知地址
		$p10_note = 'add'; //备注
		$p11_status = ''; //支付状态
		$p12_ordercode = ''; //壹支付平台的订单流水号


		$merchantcode = trim ( $payment ['onetopay_account'] ); //是商户在壹支付平台的商户身份ID
		$merchantkey = trim ( $payment ['onetopay_key'] ); //商户在壹支付平台设置的密钥，在接入方式->API接口 右边的设置API密钥,必须


		$sign = 'p01_service=' . $p01_service . '&p02_out_ordercode=' . $p02_out_ordercode . '&p03_payamount=' . $p03_payamount . '&p04_sitecode=' . $p04_sitecode . '&p05_subject=' . $p05_subject . '&p06_body=' . $p06_body . '&p07_price=' . $p07_price . '&p08_quantity=' . $p08_quantity . '&p09_notify_url=' . $p09_notify_url . '&p10_note=' . $p10_note . '&merchantcode=' . $merchantcode . '&merchantkey=' . $merchantkey;

		$MD5KEY = strtolower ( trim ( md5 ( $sign ) ) );

		$def_url = '<br /><form style="text-align:center;" method=post action="http://pay' . $merchantcode . '.1topay.com/gateway.aspx" target="_blank">';
		$def_url .= "<input type=HIDDEN name='p01_service' value='" . $p01_service . "'>";
		$def_url .= "<input type=HIDDEN name='p02_out_ordercode' value='" . $p02_out_ordercode . "'>";
		$def_url .= "<input type=HIDDEN name='p03_payamount' value='" . $p03_payamount . "'>";
		$def_url .= "<input type=HIDDEN name='p04_sitecode'  value='" . $p04_sitecode . "'>";
		$def_url .= "<input type=HIDDEN name='p05_subject'  value='" . $p05_subject . "'>";
		$def_url .= "<input type=HIDDEN name='p06_body'  value='" . $p06_body . "'>";
		$def_url .= "<input type=HIDDEN name='p07_price'  value='" . $p07_price . "'>";
		$def_url .= "<input type=HIDDEN name='p08_quantity'  value='" . $p08_quantity . "'>";
		$def_url .= "<input type=HIDDEN name='p09_notify_url'  value='" . $p09_notify_url . "'>";
		$def_url .= "<input type=HIDDEN name='p10_note' value='" . $p10_note . "'>";
		$def_url .= "<input type=HIDDEN name='sign' value='" . $MD5KEY . "'>";
		$def_url .= "<input type=submit value='" . $GLOBALS ['_LANG'] ['pay_button'] . "'>";
		$def_url .= "</form>";

		return $def_url;
	}

	/**
	 * 响应操作
	 */
	function respond() {
		$payment = get_payment ( basename ( __FILE__, '.php' ) );
		/*取返回参数*/
		$p01_service = $_GET ['p01_service'];
		$p02_out_ordercode = $_GET ['p02_out_ordercode'];
		$p03_payamount = $_GET ['p03_payamount'];
		$p04_sitecode = $_GET ['p04_sitecode'];
		$p05_subject = $_GET ['p05_subject'];
		$p06_body = $_GET ['p06_body'];
		$p07_price = $_GET ['p07_price'];
		$p08_quantity = $_GET ['p08_quantity'];
		$p09_notify_url = return_url ( basename ( __FILE__, '.php' ) ); //回调通知地址
		$p10_note = $_GET ['p10_note'];
		$p11_status = $_GET ['p11_status'];
		$p12_ordercode = $_GET ['p12_ordercode'];
		$sign = $_GET ['sign'];

		$merchantcode = trim ( $payment ['onetopay_account'] );
		$merchantkey = trim ( $payment ['onetopay_key'] ); //商户在壹支付平台的密钥


		$merchantMsg = "p01_service=" . $p01_service . "&p02_out_ordercode=" . $p02_out_ordercode . "&p03_payamount=" . $p03_payamount . "&p04_sitecode=" . $p04_sitecode . "&p05_subject=" . $p05_subject . "&p06_body=" . $p06_body . "&p07_price=" . $p07_price . "&p08_quantity=" . $p08_quantity . "&p10_note=" . $p10_note . "&p11_status=" . $p11_status . "&p12_ordercode=" . $p12_ordercode . "&merchantcode=" . $merchantcode . "&merchantkey=" . $merchantkey;

		//重新计算MD5
		$sign_md5 = strtolower ( trim ( md5 ( $merchantMsg ) ) );

		if ($sign_md5 != $sign) {
			return false;
		} else {
			// 改变订单状态
			$pay_order = get_order_id_by_sn ( $p02_out_ordercode );
			order_paid ( $pay_order );

			return true;
		}

	}
}

?>