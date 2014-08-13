<?php

/**
 * SKYUC! 易付通插件
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

$payment_lang = DIR . '/languages/' . $GLOBALS['skyuc']->options ['lang'] . '/payment/xpay.php';
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
	$modules [$i] ['desc'] = 'xpay_desc';

	/* 是否支持实时开通 */
	$modules [$i] ['is_cod'] = '1';

	/* 作者 */
	$modules [$i] ['author'] = 'xpay.cn';

	/* 网址 */
	$modules [$i] ['website'] = 'http://www.xpay.cn';

	/* 版本号 */
	$modules [$i] ['version'] = '2.0.0';

	/* 配置信息 */
	$modules [$i] ['config'] = array (array ('name' => 'xpay_tid', 'type' => 'text', 'value' => '' ), array ('name' => 'xpay_key', 'type' => 'text', 'value' => '' ) );

	return;
}

/**
 * 类
 */
class xpay {
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
		$data_return_url = return_url ( 'xpay' );
		;
		$data_tid = $payment ['xpay_tid'];
		$data_key = md5 ( "$payment[xpay_key]:$data_amount,$data_order_id,$data_tid,bank,,sell,,2.0" );

		include_once (DIR . 'includes/iconv/cls_iconv.php');
		$iconv = new Chinese ( DIR );

		$def_url = '<br /><form style="text-align:center;" method=post action="http://pay.xpay.cn/pay.aspx">';
		$def_url .= "<input type=hidden name=tid value='$data_tid'>"; // 商户交易号
		$def_url .= "<input type=hidden name=bid value='$data_order_id'>"; // 订单号
		$def_url .= "<input type=hidden name=prc value='$data_amount'>"; // 订单总金额
		$def_url .= "<input type=hidden name=card value='bank'>"; // 默认支付方式
		$def_url .= "<input type=hidden name=scard value=''>"; // 支持支付种类
		$def_url .= "<input type=hidden name=actioncode value='sell'>"; // 交易码
		$def_url .= "<input type=hidden name=actionParameter value=''>"; // 业务代码参数
		$def_url .= "<input type=hidden name=ver value='2.0'>"; // 版本号
		$def_url .= "<input type=hidden name=md value='$data_key'>"; // 订单MD5校验码
		$def_url .= "<input type=hidden name=url value='$data_return_url'>"; // 支付交易完成后返回到该url，支付结果以get方式发送
		$def_url .= "<input type='hidden' name='pdt' value='$data_order_id'>"; // 产品名称或交易说明
		$def_url .= "<input type='hidden' name='type' value=''>"; // 产品类型或交易分类
		$def_url .= "<input type='hidden' name='username' value=''>"; // 消费购买用户名
		$def_url .= "<input type='hidden' name='lang' value='gb2312'>"; // 语言
		$def_url .= "<input type='hidden' name='remark1' value=''>"; // 备注字段
		$def_url .= "<input type='hidden' name='disableemail' value=''>"; // 隐藏交易邮箱
		$def_url .= "<input type='hidden' name='disablealert' value=''>"; // 隐藏弹窗提示
		$def_url .= "<input type='hidden' name='sitename' value=''>"; // 商户网站名称
		$def_url .= "<input type='hidden' name='siteurl' value=''>"; // 商户网站域名
		$def_url .= "<input type=submit value='" . $GLOBALS ['_LANG'] ['xpay_button'] . "'>";
		$def_url .= "</form>";

		return $def_url;
	}

	/**
	 * 响应操作
	 */
	public function respond() {
		/*取返回参数*/
		$tid = $_REQUEST ["tid"]; // 商户唯一交易号
		$bid = $_REQUEST ["bid"]; // 商户网站订单号
		$sid = $_REQUEST ["sid"]; // 易付通交易成功 流水号
		$prc = $_REQUEST ["prc"]; // 支付的金额
		$actionCode = $_REQUEST ["actioncode"]; // 交易码
		$actionParameter = $_REQUEST ["actionparameter"]; // 业务代码
		$card = $_REQUEST ["card"]; // 支付方式
		$success = $_REQUEST ["success"]; // 成功标志，
		$bankcode = $_REQUEST ["bankcode"]; // 支付银行
		$remark1 = $_REQUEST ["remark1"]; // 备注信息
		$username = $_REQUEST ["username"]; // 商户网站支付用户
		$md = $_REQUEST ["md"]; // 32位md5加密数据


		$payment = get_payment ( 'xpay' );
		if ($success == 'false') {
			return false;
		}
		// 验证数据是否正确
		$ymd = md5 ( $payment ['xpay_key'] . ":" . $bid . "," . $sid . "," . $prc . "," . $actionCode . "," . $actionParameter . "," . $tid . "," . $card . "," . $success ); // 本地进行数据加密
		if ($md != $ymd) {
			return false;
		} else {
			order_paid ( $bid, PS_PAYED );

			return true;
		}
	}
}

?>
