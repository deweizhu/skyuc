<?php
/**
 * SKYUC!  Perfect Money 插件
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */
if (! defined('SKYUC_AREA')) {
    echo 'SKYUC_AREA  must be defined to continue';
    exit();
}
$payment_lang = DIR . '/languages/' . $GLOBALS['skyuc']->options['lang'] .
    '/payment/perfectmoney.php';
if (is_file($payment_lang)) {
    global $_LANG;
    include_once ($payment_lang);
}
/*
 * 模块的基本信息
 */
if (isset($set_modules) && $set_modules == TRUE) {
    $i = iif($modules, count($modules), 0);
    // 代码
    $modules[$i]['code'] = basename(__FILE__, '.php');
    // 描述对应的语言项
    $modules[$i]['desc'] = 'perfectmoney_desc';
    // 是否支持实时开通
    $modules[$i]['is_cod'] = '1';
    // 作者
    $modules[$i]['author'] = 'perfectmoney.com';
    // 网址
    $modules[$i]['website'] = 'http://www.perfectmoney.com ';
    // 版本号
    $modules[$i]['version'] = '1.0';
    // 配置信息
    $modules[$i]['config'] = array(
        array('name' => 'payee_account', 'type' => 'text', 'value' => ''),
        array('name' => 'payee_name', 'type' => 'text', 'value' => ''),
        array('name' => 'payee_currency', 'type' => 'select', 'value' => 'USD'),
        array('name' => 'payee_passphrase', 'type' => 'text', 'value' => ''),
        array('name' => 'success_url', 'type' => 'text', 'value' => ''),
        array('name' => 'fail_url', 'type' => 'text', 'value' => ''));
    return;
}
/**
 * 类
 */
class perfectmoney
{
    /**
     * 构造函数
     *
     * @access  public
     * @param
     *
     * @return void
     */
    public function __construct ()
    {}
    /**
     * 生成支付代码
     * @param   array   $order      订单信息
     * @param   array   $payment    支付方式信息
     */
    public function get_code ($order, $payment)
    {
        $data_account = trim($payment['payee_account']);
        $data_currency = trim($payment['payee_currency']);
        $data_name = trim($payment['payee_name']);
        $data_ordersn = $order['order_sn'];
        $data_logid = $order['log_id'];
        $data_vamount = $order['pay_amount'];
        $data_vreturnurl = return_url(basename(__FILE__, '.php'));
        $fail_url = ! empty($payment['fail_url']) ? trim($payment['fail_url']) : $data_vreturnurl;
        $submit = $GLOBALS['_LANG']['pay_button'];
        if (!empty($payment['success_url'])) {
            $success_url = trim($payment['success_url']);
        } else {
            $success_url = $data_vreturnurl;
        }
        $def_url = <<< EOT
<br />
<form style="text-align:center;" method="post" action="https://perfectmoney.com/api/step1.asp" name="formpay" target="_blank">
<input type="hidden" name="PAYEE_ACCOUNT" value="{$data_account}">
<input type="hidden" name="PAYEE_NAME" value="{$data_name}">
<input type="hidden" name="PAYMENT_AMOUNT" value="{$data_vamount}">
<input type="hidden" name="PAYMENT_UNITS" value="{$data_currency}">
<input type="hidden" name="STATUS_URL" value="$data_vreturnurl">
<input type="hidden" name="PAYMENT_URL" value="{$success_url}">
<input type="hidden" name="NOPAYMENT_URL" value="{$fail_url}">
<input type="hidden" name="BAGGAGE_FIELDS" value="ORDER_NUM CUST_NUM">
<input type="hidden" name="ORDER_NUM" value="{$data_ordersn}">
<input type="hidden" name="CUST_NUM" value="{$data_logid}">
<input type="image" name="submit" alt="{$submit}" src="data/images/payment/perfectmoney.gif"/>
</form>
EOT;
        return $def_url;
    }
    /**
     * 响应操作
     */
    public function respond ()
    {
        if (!empty($_POST)) {
            foreach ($_POST as $key => $data) {
                $_GET[$key] = $data;
            }
        }
        $payment = get_payment ( basename ( __FILE__, '.php' ) );
        $ALTERNATE_PHRASE_HASH = strtoupper(md5(trim($payment['payee_passphrase'])));'';

        $string = $_GET['PAYMENT_ID'].':'.$_GET['PAYEE_ACCOUNT'].':'.
                $_GET['PAYMENT_AMOUNT'].':'.$_GET['PAYMENT_UNITS'].':'.
                $_GET['PAYMENT_BATCH_NUM'].':'.
                $_GET['PAYER_ACCOUNT'].':'.$ALTERNATE_PHRASE_HASH.':'.
                $_GET['TIMESTAMPGMT'];

        $hash=strtoupper(md5($string));

        if($hash==$_GET['V2_HASH']){ // proccessing payment if only hash is valid

            if($_GET['PAYEE_ACCOUNT'] == trim($payment['payee_account']) &&
                $_GET['PAYMENT_UNITS'] == trim($payment['payee_currency'])) {

                /* ...insert some code to proccess valid payments here... */
                $log_id = (int)$_GET['CUST_NUM'];
                // 检查支付的金额是否相符
                if (! check_money($log_id, trim($_GET['PAYMENT_AMOUNT']))) {
                    return false;
                }
                // 改变订单状态
                order_paid($log_id);

                // uncomment code below if you want to log successfull payments
                /* format = date("Y-m-d H:i")."; REASON: bad hash; POST: ".serialize($_POST)."; STRING: $string; HASH: $hash\n";
                file_put_contents('./data/perfect_bad.log', $format, FILE_APPEND);*/
                return true;

            }else{ // you can also save invalid payments for debug purposes

                // uncomment code below if you want to log requests with bad hash
                $format = date("Y-m-d H:i")."; REASON: bad hash; POST: ".serialize($_POST)."; STRING: $string; HASH: $hash\n";
                file_put_contents('./data/perfect_bad.log', $format, FILE_APPEND);

                return false;
            }
        }
        else{ // you can also save invalid payments for debug purposes

            // uncomment code below if you want to log requests with bad hash
            $format = date("Y-m-d H:i")."; REASON: bad hash; POST: ".serialize($_POST)."; STRING: $string; HASH: $hash\n";
            file_put_contents('./data/perfect_bad.log', $format, FILE_APPEND);
            return false;
        }
    }
}
