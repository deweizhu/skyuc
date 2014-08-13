<?php

/**
 * 800pay 支付宝插件
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
$payment_lang = DIR . '/languages/' . $GLOBALS['skyuc']->options ['lang'] . '/payment/pay800.php';

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
	$modules [$i] ['desc'] = 'pay800_desc';

	/* 是否支持实时开通 */
	$modules [$i] ['is_cod'] = '1';

	/* 作者 */
	$modules [$i] ['author'] = '800-pay';

	/* 网址 */
	$modules [$i] ['website'] = 'http://www.800-pay.com';

	/* 版本号 */
	$modules [$i] ['version'] = '1.0.1';

	/* 配置信息,不同用户注意修改value */
	$modules [$i] ['config'] = array (array ('name' => 'pay800_account', 'type' => 'text', 'value' => '' ), array ('name' => 'pay800_key', 'type' => 'text', 'value' => '' ), array ('name' => 'pay800_currency', 'type' => 'select', 'value' => '' ), array ('name' => 'pay800_language', 'type' => 'select', 'value' => '' ) );

	return;
}

class pay800 {
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
		$data_M_ID = $payment ['pay800_account']; //商 家 号：
		$data_M_OrderID = $order ['log_id']; //订 单 号：
		$data_M_OAmount = $order ['pay_amount']; //订单金额：
		$data_M_OCurrency = $payment ['pay800_currency']; //币 种：
		$data_M_URL = return_url ( basename ( __FILE__, '.php' ) ); //返回地址：
		$data_M_Language = $payment ['pay800_language']; //语言选择：


		$data_T_TradeName = ''; //$order['order_sn'];              //商品名称：
		$data_T_Unit = ''; //$order['order_sn'];              //商品单位：
		$data_T_UnitPrice = ''; //$order['order_sn'];              //商品单价：
		$data_T_quantity = ''; //$order['order_sn'];              //商品数量：
		$data_T_carriage = ''; //$order['shipping_fee'];          //商品运费：


		$data_S_Name = ''; //$order['order_sn'];              //消费者姓名：
		$data_S_Address = ''; //$order['order_sn'];              //消费者住址：
		$data_S_PostCode = ''; //$order['order_sn'];              //消费者邮码：
		$data_S_Telephone = ''; //$order['order_sn'];              //消费者电话：
		$data_S_Email = ''; //$order['order_sn'];              //消费者邮件：


		$data_R_Name = ''; //$order['consignee'];             //收货人姓名：
		$data_R_Address = ''; //$order['address'];               //收货人住址：
		$data_R_PostCode = ''; //$order['zipcode'];               //收货人邮码：
		$data_R_Telephone = ''; //$order['tel'];                   //收货人电话：
		$data_R_Email = ''; //$order['email'];                 //收货人邮件：


		$data_M_OComment = ''; //$order['inv_content'];           //备 注
		$data_M_OState = '0'; //交易状态：
		$data_M_ODate = date ( 'Y-m-d H:i:s' ); //时间字段：


		$data_PrivateKey = $payment ['pay800_key'];

		//$data_R_Telephone2   = $order['mobile'];                    //收货人手机：


		if (empty ( $data_M_OComment )) {
			$data_M_OComment = 'From SKYUC! order ' . $payment ['pay800_account'];
		}

		$data_m_info = '' . $data_M_ID . '|' . $data_M_OrderID . '|' . $data_M_OAmount . '|' . $data_M_OCurrency . '|' . $data_M_URL . '|' . $data_M_Language . '';

		$data_t_info = '' . $data_T_TradeName . '|' . $data_T_Unit . '|' . $data_T_UnitPrice . '|' . $data_T_quantity . '|' . $data_T_carriage . '';

		$data_s_info = '' . $data_S_Name . '|' . $data_S_Address . '|' . $data_S_PostCode . '|' . $data_S_Telephone . '|' . $data_S_Email . '|' . $data_R_Name . '';

		$data_r_info = '' . $data_R_Address . '|' . $data_R_PostCode . '|' . $data_R_Telephone . '|' . $data_R_Email . '|' . $data_M_OComment . '|' . $data_M_OState . '|' . $data_M_ODate . '';

		$data_OrderInfo = $data_m_info . '|' . $data_t_info . '|' . $data_s_info . '|' . $data_r_info;
		$data_OrderMessage = $data_OrderInfo . $data_PrivateKey;
		$data_Digest = strtoupper ( trim ( md5 ( $data_OrderMessage ) ) );

		$def_url = "<form name='FORM' method='post' action='https://www.800-pay.com/PayAction/ReceivePayOrder.aspx'>" . "   <input type='hidden' name='OrderMessage' value='" . $data_OrderInfo . "'>" . "   <input type='hidden' name='Digest' value='" . $data_Digest . "'>" . "   <input type='hidden' name='m_id' value='" . $data_M_ID . "'>" . "   <input type='submit' name='s' value='" . $GLOBALS ['_LANG'] ['pay_button'] . "'>" . "</form>";

		return $def_url;
	}

	/**
	 * 响应操作
	 */
	public function respond() {
		$payment = get_payment ( 'pay800' );

		$data_PrivateKey = $payment ['pay800_key'];
		$get_PayResult = false;

		$rec_M_id = $_REQUEST ['M_ID'];
		$rec_OrderMessage = $_REQUEST ['OrderMessage'];
		$rec_Digest = $_REQUEST ['digest'];

		$data_OrderMessage = $rec_OrderMessage . $data_PrivateKey;
		$data_Digest = strtoupper ( trim ( md5 ( $data_OrderMessage ) ) );

		if ($rec_OrderMessage == '') {
			//echo '订单加密信息为空值';
			return $get_PayResult;
		}

		if ($rec_Digest == '') {
			//echo '认证签名为空值';
			return $get_PayResult;
		}

		if ($data_Digest == $rec_Digest) {
			$tempStr = $rec_OrderMessage;
			$V = explode ( '|', $tempStr );
			$num = count ( $V );
			if ($num !== 25) //返回时，多加了一个数据 m_serial，这里应该是25
{
				//echo 'error message = '. $tempStr .'<br /><br />';
				return $get_PayResult;
			}

			$data_m_id = $V [0];
			$data_m_orderid = $V [1];
			$data_m_oamount = $V [2];
			$data_m_ocurrency = $V [3];
			$data_m_url = $V [4];
			$data_m_language = $V [5];

			$data_T_TradeName = $V [6];
			$data_T_Unit = $V [7];
			$data_T_UnitPrice = $V [8];
			$data_T_quantity = $V [9];
			$data_T_carriage = $V [10];

			$data_s_name = $V [11];
			$data_s_addr = $V [12];
			$data_s_postcode = $V [13];
			$data_s_tel = $V [14];
			$data_s_eml = $V [15];

			$data_r_name = $V [16];
			$data_r_addr = $V [17];
			$data_r_postcode = $V [18];
			$data_r_tel = $V [19];
			$data_r_eml = $V [20];

			$data_m_ocomment = $V [21];
			$data_m_status = $V [22];
			$data_m_odate = $V [23];

			$data_m_serial = $V [24];

			/*
            if ($data_m_status == 2)
            {
                echo '验证成功!'    . '<br><br>';
                echo '商 家 号    ='        . $data_m_id        . '<br>';
                echo '支付订单    ='        . $data_m_orderid   . '<br>';
                echo '支付金额    ='        . $data_m_oamount   . '<br>';
                echo '币   种 　  ='        . $data_m_ocurrency . '<br>';
                echo '结果地址    ='        . $data_m_url       . '<br>';
                echo '语言选择    ='        . $data_m_language  . '<br>';

                echo '商品名称    ='        . $data_T_TradeName . '<br>';
                echo '商品单位    ='        . $data_T_Unit      . '<br>';
                echo '商品单价    ='        . $data_T_UnitPrice . '<br>';
                echo '商品数量    ='        . $data_T_quantity  . '<br>';
                echo '商品运费    ='        . $data_T_carriage  . '<br>';

                echo '消费者姓名     ='     . $data_s_name      . '<br>';
                echo '消费者住址  ='        . $data_s_addr      . '<br>';
                echo '消费者邮码     ='     . $data_s_postcode  . '<br>';
                echo '消费者电话     ='     . $data_s_tel       . '<br>';
                echo '消费者邮件     ='     . $data_s_eml       . '<br>';

                echo '收货姓名    ='        . $data_r_name      . '<br>';
                echo '收货住址    ='        . $data_r_addr      . '<br>';
                echo '收货编码    ='        . $data_r_postcode  . '<br>';
                echo '收货电话    ='        . $data_r_tel       . '<br>';
                echo '收货邮件    ='        . $data_r_eml       . '<br>';

                echo '备      注     ='     . $data_m_ocomment  . '<br>';
                echo '支付状态    ='        . $data_m_status    . '<br>';
                echo '支付日期    ='        . $data_m_odate     . '<br>';

                echo '系统参考号     ='     . $data_m_serial    . '<br>';

                echo '<br>返回的认证结果： ';
            }
            else
            {
                echo '支付失败!<br />';
            }
            */

			switch ($data_m_status) {
				case '0' :
					//echo '0.未支付';
					break;
				case '2' :
					//echo '2.支付成功';
					$get_PayResult = true;
					order_paid ( $data_m_orderid, PS_PAYED ); //修改订单状态
					break;
				case '3' :
					//echo '3.支付失败';
					break;
				default :
					//echo '支付状态 错误';
					break;
			}
		} else {
			//echo '失败，信息可能被篡改';
		}

		return $get_PayResult;
	}
}

?>
