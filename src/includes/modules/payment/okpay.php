<?php
/**
 * SKYUC! OKPAY插件
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
 '/payment/okpay.php';
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
    $modules[$i]['desc'] = 'okpay_desc';
    // 是否支持实时开通
    $modules[$i]['is_cod'] = '1';
    // 作者
    $modules[$i]['author'] = 'okpay.com';
    // 网址
    $modules[$i]['website'] = 'http://www.okpay.com';
    // 版本号
    $modules[$i]['version'] = '1.0';
    // 配置信息
    $modules[$i]['config'] = array(
    array('name' => 'okpay_account', 'type' => 'text', 'value' => ''),
    array('name' => 'okpay_currency', 'type' => 'text', 'value' => 'USD'),
    array('name' => 'success_url', 'type' => 'text', 'value' => ''),
    array('name' => 'fail_url', 'type' => 'text', 'value' => ''));
    return;
}
/**
 * 类
 */
class okpay
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
        $data_account = trim($payment['okpay_account']);
        $data_currency = trim($payment['okpay_currency']);
        $data_ordersn = $order['order_sn'];
        $data_logid = $order['log_id'];
        $data_vamount = $order['pay_amount'];
        $data_vreturnurl = return_url(basename(__FILE__, '.php'));
        $fail_url = ! empty($payment['fail_url']) ? trim($payment['fail_url']) : $data_vreturnurl;
        $submit = $GLOBALS['_LANG']['pay_button'];
        if (! empty($payment['success_url'])) {
            $success_url = trim($payment['success_url']);
            $ok_ipn = '<input type="hidden" name="ok_ipn" value="'.$data_vreturnurl.'" />';
        } else {
            $success_url = $data_vreturnurl;
            $ok_ipn = '';
        }
        $def_url = <<< EOT
<br />
<form style="text-align:center;" method="post" action="https://www.okpay.com/process.html" name="formpay" target="_blank">
<input type="hidden" name="ok_receiver" value="{$data_account}" />
<input type="hidden" name="ok_item_1_name" value="{$data_ordersn}" />
<input type="hidden" name="ok_currency" value="{$data_currency}" />
<input type="hidden" name="ok_item_1_type" value="service" />
<input type="hidden" name="ok_item_1_price" value="{$data_vamount}" />
<input type="hidden" name="ok_return_success" value="{$success_url}" />
<input type="hidden" name="ok_return_fail" value="{$fail_url}" />
<input type="hidden" name="ok_item_1_id" value="{$data_logid}" />
{$ok_ipn}
<input type="image" name="submit" alt="{$submit}" src="https://www.okpay.com/img/buttons/x05.gif"/>
</form>
EOT;
        return $def_url;
    }
    /**
     * 响应操作
     */
    public function respond ()
    {
        if (! empty($_POST)) {
            foreach ($_POST as $key => $data) {
                $_GET[$key] = $data;
            }
        }
        if (trim($_GET['ok_txn_status']) == 'completed' and trim($_GET['ok_payer_status']) == 'verified')
        {
            $order_sn = trim($_GET['ok_item_1_name']);
            $log_id = intval($_GET['ok_item_1_id']);
            // 检查支付的金额是否相符
            if (! check_money($log_id, trim($_GET['ok_item_1_amount']))) {
                return false;
            }
            // 改变订单状态
            order_paid($log_id);
            return true;
        } else {
            return false;
        }
    }
}
?>
