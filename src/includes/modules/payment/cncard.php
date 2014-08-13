<?php

/**
 **
 * SKYUC! 云网支付插件
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

$payment_lang = DIR . '/languages/' . $GLOBALS['skyuc']->options ['lang'] . '/payment/cncard.php';

if (is_file ( $payment_lang )) {
	global $_LANG;

	include_once ($payment_lang);
}

/**
 * 模块信息
 */
if (isset ( $set_modules ) && $set_modules == TRUE) {
	$i = iif ( $modules, count ( $modules ), 0 );

	/* 代码 */
	$modules [$i] ['code'] = basename ( __FILE__, '.php' );

	/* 描述对应的语言项 */
	$modules [$i] ['desc'] = 'cncard_desc';

	/* 是否支持实时开通 */
	$modules [$i] ['is_cod'] = '1';

	/* 是否支持在线支付 */
	$modules [$i] ['is_online'] = '1';

	/* 作者 */
	$modules [$i] ['author'] = 'www.cncard.net';

	/* 网址 */
	$modules [$i] ['website'] = 'http://www.cncard.net/';

	/* 版本号 */
	$modules [$i] ['version'] = 'V1.1';

	/* 配置信息 */
	$modules [$i] ['config'] = array (array ('name' => 'c_mid', 'type' => 'text', 'value' => '' ), array ('name' => 'c_pass', 'type' => 'text', 'value' => '' ), array ('name' => 'c_memo1', 'type' => 'text', 'value' => 'skyuc' ), array ('name' => 'c_moneytype', 'type' => 'select', 'value' => '0' ), array ('name' => 'c_language', 'type' => 'select', 'value' => '0' ), array ('name' => 'c_paygate', 'type' => 'select', 'value' => '' ) );

	return;
}

class cncard {
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
		$c_mid = trim ( $payment ['c_mid'] ); //商户编号，在申请商户成功后即可获得，可以在申请商户成功的邮件中获取该编号
		$c_order = $order ['log_id']; //商户网站依照订单号规则生成的订单号，不能重复
		$c_name = ""; //商户订单中的收货人姓名
		$c_address = ""; //商户订单中的收货人地址
		$c_tel = ""; //商户订单中的收货人电话
		$c_post = ""; //商户订单中的收货人邮编
		$c_email = ""; //商户订单中的收货人Email
		$c_orderamount = $order ['pay_amount']; //商户订单总金额
		$c_ymd = substr ( $order ['order_sn'], 0, 8 ); //商户订单的产生日期，格式为"yyyymmdd"，如20050102
		$c_moneytype = $payment ['c_moneytype']; //支付币种，0为人民币
		$c_retflag = "1"; //商户订单支付成功后是否需要返回商户指定的文件，0：不用返回 1：需要返回
		$c_paygate = empty ( $payment ['c_paygate'] ) ? '' : trim ( $payment ['c_paygate'] ); //如果在商户网站选择银行则设置该值，具体值可参见《云网支付@网技术接口手册》附录一；如果来云网支付@网选择银行此项为空值。
		$c_returl = return_url ( basename ( __FILE__, '.php' ) ); //如果c_retflag为1时，该地址代表商户接收云网支付结果通知的页面，请提交完整文件名(对应范例文件：GetPayNotify.php)
		$c_memo1 = abs ( crc32 ( trim ( $payment ['c_memo1'] ) ) ); //商户需要在支付结果通知中转发的商户参数一
		$c_memo2 = "skyuc"; //商户需要在支付结果通知中转发的商户参数二
		$c_pass = trim ( $payment ['c_pass'] ); //支付密钥，请登录商户管理后台，在帐户信息-基本信息-安全信息中的支付密钥项
		$notifytype = "1"; //0普通通知方式/1服务器通知方式，空值为普通通知方式
		$c_language = trim ( $payment ['c_language'] ); //对启用了国际卡支付时，可使用该值定义消费者在银行支付时的页面语种，值为：0银行页面显示为中文/1银行页面显示为英文


		$srcStr = $c_mid . $c_order . $c_orderamount . $c_ymd . $c_moneytype . $c_retflag . $c_returl . $c_paygate . $c_memo1 . $c_memo2 . $notifytype . $c_language . $c_pass; //说明：如果您想指定支付方式(c_paygate)的值时，需要先让用户选择支付方式，然后再根据用户选择的结果在这里进行MD5加密，也就是说，此时，本页面应该拆分为两个页面，分为两个步骤完成。


		//--对订单信息进行MD5加密
		//商户对订单信息进行MD5签名后的字符串
		$c_signstr = md5 ( $srcStr );

		$def_url = '<form name="payForm1" action="https://www.cncard.net/purchase/getorder.asp" method="POST">' . "<input type=\"hidden\" name=\"c_mid\" value=\"$c_mid\" />" . "<input type=\"hidden\" name=\"c_order\" value=\"$c_order\" />" . "<input type=\"hidden\" name=\"c_name\" value=\"$c_name\" />" . "<input type=\"hidden\" name=\"c_address\" value=\"$c_address\" />" . "<input type=\"hidden\" name=\"c_tel\" value=\"$c_tel\" />" . "<input type=\"hidden\" name=\"c_post\" value=\"$c_post\" />" . "<input type=\"hidden\" name=\"c_email\" value=\"$c_email\" />" . "<input type=\"hidden\" name=\"c_orderamount\" value=\"$c_orderamount\" />" . "<input type=\"hidden\" name=\"c_ymd\" value=\"$c_ymd\" />" . "<input type=\"hidden\" name=\"c_moneytype\" value=\"$c_moneytype\" />" . "<input type=\"hidden\" name=\"c_retflag\" value=\"$c_retflag\" />" . "<input type=\"hidden\" name=\"c_paygate\" value=\"$c_paygate\" />" . "<input type=\"hidden\" name=\"c_returl\" value=\"$c_returl\" />" . "<input type=\"hidden\" name=\"c_memo1\" value=\"$c_memo1\" />" . "<input type=\"hidden\" name=\"c_memo2\" value=\"$c_memo2\" />" . "<input type=\"hidden\" name=\"c_language\" value=\"$c_language\" />" . "<input type=\"hidden\" name=\"notifytype\" value=\"$notifytype\" />" . "<input type=\"hidden\" name=\"c_signstr\" value=\"$c_signstr\" />" . "<input type=\"submit\" name=\"submit\" value=\"" . $GLOBALS ['_LANG'] ['cncard_button'] . "\" />" . "</form>";

		return $def_url;
	}

	/**
	 * 响应操作
	 */

	public function respond() {
		$payment = get_payment ( $_GET ['code'] );

		//--获取云网支付网关向商户发送的支付通知信息(以下简称为通知信息)
		$c_mid = $_REQUEST ['c_mid']; //商户编号，在申请商户成功后即可获得，可以在申请商户成功的邮件中获取该编号
		$c_order = $_REQUEST ['c_order']; //商户提供的订单号
		$c_orderamount = $_REQUEST ['c_orderamount']; //商户提供的订单总金额，以元为单位，小数点后保留两位，如：13.05
		$c_ymd = $_REQUEST ['c_ymd']; //商户传输过来的订单产生日期，格式为"yyyymmdd"，如20050102
		$c_transnum = $_REQUEST ['c_transnum']; //云网支付网关提供的该笔订单的交易流水号，供日后查询、核对使用；
		$c_succmark = $_REQUEST ['c_succmark']; //交易成功标志，Y-成功 N-失败
		$c_moneytype = $_REQUEST ['c_moneytype']; //支付币种，0为人民币
		$c_cause = $_REQUEST ['c_cause']; //如果订单支付失败，则该值代表失败原因
		$c_memo1 = $_REQUEST ['c_memo1']; //商户提供的需要在支付结果通知中转发的商户参数一
		$c_memo2 = $_REQUEST ['c_memo2']; //商户提供的需要在支付结果通知中转发的商户参数二
		$c_signstr = $_REQUEST ['c_signstr']; //云网支付网关对已上信息进行MD5加密后的字符串


		//--校验信息完整性---
		if ($c_mid == "" || $c_order == "" || $c_orderamount == "" || $c_ymd == "" || $c_moneytype == "" || $c_transnum == "" || $c_succmark == "" || $c_signstr == "") {
			//echo "支付信息有误!";


			return false;
		}

		//--将获得的通知信息拼成字符串，作为准备进行MD5加密的源串，需要注意的是，在拼串时，先后顺序不能改变
		//商户的支付密钥，登录商户管理后台(https://www.cncard.net/admin/)，在管理首页可找到该值
		$c_pass = trim ( $payment ['c_pass'] );

		$srcStr = $c_mid . $c_order . $c_orderamount . $c_ymd . $c_transnum . $c_succmark . $c_moneytype . $c_memo1 . $c_memo2 . $c_pass;

		//--对支付通知信息进行MD5加密
		$r_signstr = md5 ( $srcStr );

		//--校验商户网站对通知信息的MD5加密的结果和云网支付网关提供的MD5加密结果是否一致
		if ($r_signstr != $c_signstr) {
			//echo "签名验证失败";


			return false;
		}

		/* 检查支付的金额是否相符 */
		if (! check_money ( $c_order, $c_orderamount )) {
			//echo "订单金额不对";


			return false;
		}

		//--校验商户编号
		$MerchantID = trim ( $payment ['c_mid'] ); //商户自己的编号
		if ($MerchantID != $c_mid) {
			//echo "提交的商户编号有误";


			return false;
		}

		if ($c_memo1 != abs ( crc32 ( $payment ['c_memo1'] ) )) {
			//echo "个性签名不一致";


		//return false;
		}

		//      $r_orderamount = $row["订单金额"];  //商户从自己订单系统获取该值
		//      if($r_orderamount!=$c_orderamount){
		//          echo "支付金额有误";
		//          exit;
		//      }


		//--校验商户订单系统中记录的订单生成日期和云网支付网关通知信息中的订单生成日期是否一致
		//      $r_ymd = $row["订单生成日期"];      //商户从自己订单系统获取该值
		//      if($r_ymd!=$c_ymd){
		//          echo "订单时间有误";
		//          exit;
		//      }


		//--校验返回的支付结果的格式是否正确
		if ($c_succmark != 'Y' && $c_succmark != 'N') {
			//echo "参数提交有误";


			return false;
		}

		//--根据返回的支付结果，商户进行自己的发货等操作
		if ($c_succmark == 'Y') {
			//根据商户自己商务规则，进行发货等系列操作


			/* 改变订单状态 */
			order_paid ( $c_order );

			return true;
		} else {
			//echo $c_cause;


			return false;
		}
	}
}

?>