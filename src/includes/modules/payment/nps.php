<?php

/**
 * SKYUC! NPS支付插件
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

/*
    对于使用NPS实时反馈接口的商户请注意：

    为了从根本上解决订单支付成功而商户收不到反馈信息的问题(简称掉单).
    我公司决定在信息反馈方面实行服务器端对服务器端的反馈方式.即客户支付过后.
    我们系统会对商户的网站进行两次支付信息的反馈(即对同一笔订单信息进行两次反馈).
    第一次是服务器端对服务器端的反馈.第二次是以页面的形式反馈.两次反馈的时延差在10秒之内.
    请商户那边做好对我们反馈信息的处理. 对我们系统反馈相同的订单信息您那边只
    做一次处理就可以了.以确保消费者的每一笔订单信息在您那边只得到一次相应的服务!!
*/

if (! defined ( 'SKYUC_AREA' )) {
	echo 'SKYUC_AREA  must be defined to continue';
	exit ();
}

$payment_lang = DIR . '/languages/' . $GLOBALS['skyuc']->options ['lang'] . '/payment/nps.php';

if (is_file ( $payment_lang )) {
	global $_LANG;

	include_once ($payment_lang);
}

/**
 * 模块信息
 */
if (isset ( $set_modules ) && $set_modules == TRUE) {
	$i = isset ( $modules ) ? count ( $modules ) : 0;

	/* 代码 */
	$modules [$i] ['code'] = basename ( __FILE__, '.php' );

	/* 描述对应的语言项 */
	$modules [$i] ['desc'] = 'nps_desc';

	/* 是否支持实时开通 */
	$modules [$i] ['is_cod'] = '1';

	/* 作者 */
	$modules [$i] ['author'] = 'nps.cn';

	/* 网址 */
	$modules [$i] ['website'] = 'http://www.nps.cn';

	/* 版本号 */
	$modules [$i] ['version'] = '4.0';

	/* 配置信息 */
	$modules [$i] ['config'] = array (array ('name' => 'nps_account', 'type' => 'text', 'value' => '' ), array ('name' => 'nps_key', 'type' => 'text', 'value' => '' ) );

	return;
}

class nps {
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
		$m_id = trim ( $payment ['nps_account'] );
		$m_orderid = $order ['log_id'];
		$m_oamount = $order ['pay_amount'];
		$m_ocurrency = '1';
		$m_url = return_url ( basename ( __FILE__, '.php' ) );
		$m_language = '1';
		$s_name = 'null';
		$s_addr = 'null';
		$s_postcode = 'null';
		$s_tel = 'null';
		$s_eml = 'null';
		$r_name = 'null';
		$r_addr = 'null';
		$r_postcode = 'null';
		$r_tel = 'null';
		$r_eml = 'null';
		$m_ocomment = '欢迎使用NPS在线支付';
		$modate = date ( 'y-m-d H:i:s', time () );
		$m_status = 0;

		//组织订单信息
		$m_info = $m_id . '|' . $m_orderid . '|' . $m_oamount . '|' . $m_ocurrency . '|' . $m_url . '|' . $m_language;
		$s_info = $s_name . '|' . $s_addr . '|' . $s_postcode . '|' . $s_tel . '|' . $s_eml;
		$r_info = $r_name . '|' . $r_addr . '|' . $r_postcode . '|' . $r_tel . '|' . $r_eml . '|' . $m_ocomment . '|' . $m_status . '|' . $modate;

		$OrderInfo = $m_info . '|' . $s_info . '|' . $r_info;

		//订单信息先转换成HEX，然后再加密
		$key = $payment ['nps_key']; //<--支付密钥--> 注:此处密钥必须与商家后台里的密钥一致


		$OrderInfo = $this->StrToHex ( $OrderInfo );
		$digest = strtoupper ( md5 ( $OrderInfo . $key ) );

		$def_url = "<form method=post action='https://payment.nps.cn/PHPReceiveMerchantAction.do' target='_blank'>";
		$def_url .= "<input type=HIDDEN name='OrderMessage' value='" . $OrderInfo . "'>";
		$def_url .= "<input type=HIDDEN name='digest' value='" . $digest . "'>";
		$def_url .= "<input type=HIDDEN name='M_ID' value='" . $m_id . "'>";
		$def_url .= "<input type=submit value='" . $GLOBALS ['_LANG'] ['pay_button'] . "'>";

		$def_url .= '</form>';

		return $def_url;
	}

	/**
	 * 响应操作
	 */

	public function respond() {
		$payment = get_payment ( basename ( __FILE__, '.php' ) );

		$m_id = $_POST ['m_id']; // 商家号
		$m_orderid = $_POST ['m_orderid']; // 商家订单号
		$m_oamount = $_POST ['m_oamount']; // 支付金额
		$m_ocurrency = $_POST ['m_ocurrency']; // 币种
		$m_language = $_POST ['m_language']; // 语言选择
		$s_name = $_POST ['s_name']; // 消费者姓名
		$s_addr = $_POST ['s_addr']; // 消费者住址
		$s_postcode = $_POST ['s_postcode']; // 邮政编码
		$s_tel = $_POST ['s_tel']; // 消费者联系电话
		$s_eml = $_POST ['s_eml']; // 消费者邮件地址
		$r_name = $_POST ['r_name']; // 消费者姓名
		$r_addr = $_POST ['r_addr']; // 收货人住址
		$r_postcode = $_POST ['r_postcode']; // 收货人邮政编码
		$r_tel = $_POST ['r_tel']; // 收货人联系电话
		$r_eml = $_POST ['r_eml']; // 收货人电子地址
		$m_ocomment = $_POST ['m_ocomment']; // 备注
		$State = $_POST ['m_status']; // 支付状态2成功,3失败
		$modate = $_POST ['modate']; // 返回日期
		$order_sn = $_POST ['m_orderid'];

		//接收组件的加密
		$OrderInfo = $_POST ['OrderMessage']; // 订单加密信息
		$signMsg = $_POST ['Digest']; // 密匙


		//接收新的md5加密认证
		$newmd5info = $_POST ['newmd5info'];

		//检查签名
		$key = $payment ['nps_key']; //<--支付密钥--> 注:此处密钥必须与商家后台里的密钥一致
		$digest = strtoupper ( md5 ( $OrderInfo . $key ) );

		//新的整合md5加密
		$newtext = $m_id . $m_orderid . $m_oamount . $key . $State;
		$newMd5digest = strtoupper ( md5 ( $newtext ) );

		if ($digest == $signMsg) {
			//解密
			//$decode = $DES->Descrypt($OrderInfo, $key);
			$OrderInfo = $this->HexToStr ( $OrderInfo );
			//md5密匙认证
			if ($newmd5info == $newMd5digest) {
				if ($State == 2) {
					//改变订单状态
					order_paid ( $m_orderid );

					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function StrToHex($string) {
		$hex = '';

		for($i = 0, $count = strlen ( $string ); $i < $count; $i ++) {
			$hex .= dechex ( ord ( $string [$i] ) );
		}

		return strtoupper ( $hex );
	}

	public function HexToStr($hex) {
		$string = '';

		for($i = 0, $count = strlen ( $hex ) - 1; $i < $count; $i += 2) {
			$string .= chr ( hexdec ( $hex [$i] . $hex [$i + 1] ) );
		}

		return $string;
	}
}

?>
