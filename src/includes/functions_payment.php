<?php
/**
 * SKYUC! 支付接口函数库
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */
/**
 * 取得返回信息地址
 * @param   string  $code   支付方式代码
 */
function return_url ($code)
{
    return get_url() . 'respond.php?code=' . $code;
}
/**
 * 取得某支付方式信息
 * @param  string  $code   支付方式代码
 */
function get_payment ($code)
{
    $sql = 'SELECT * FROM ' . TABLE_PREFIX . 'payment' . " WHERE pay_code = '" .
     $GLOBALS['db']->escape_string($code) . "' AND enabled = 1";
    $payment = $GLOBALS['db']->query_first_slave($sql);
    if ($payment) {
        $config_list = unserialize($payment['pay_config']);
        foreach ($config_list as $config) {
            $payment[$config['name']] = $config['value'];
        }
    }
    return $payment;
}
/**
 * 通过订单sn取得订单ID
 * @param  string  $order_sn   订单sn
 * @param  blob    $voucher    是否为会员充值
 */
function get_order_id_by_sn ($order_sn, $voucher = 'false')
{
    if ($voucher == 'true') {
        if (is_numeric($order_sn)) {
            $order_id = $GLOBALS['db']->query_first(
            'SELECT log_id FROM ' . TABLE_PREFIX . 'pay_log' .
             " WHERE order_id='" . $order_sn . "' AND order_type=1");
            return $order_id['log_id'];
        } else {
            return "";
        }
    } else {
        if (is_numeric($order_sn)) {
            $order_id = $GLOBALS['db']->query_first(
            'SELECT order_id FROM ' . TABLE_PREFIX . 'order_info' .
             " WHERE order_sn = '$order_sn'");
        }
        if (! empty($order_id)) {
            $pay_log_id = $GLOBALS['db']->query_first(
            "SELECT log_id FROM " . TABLE_PREFIX . 'pay_log' . " WHERE order_id=" .
             $order_id['order_id']);
            return $pay_log_id['log_id'];
        } else {
            return "";
        }
    }
}
/**
 * 检查支付的金额是否与订单相符
 *
 * @access  public
 * @param   string   $log_id      支付编号
 * @param   float    $money       支付接口返回的金额
 * @return  true
 */
function check_money ($log_id, $money)
{
    $sql = 'SELECT order_amount FROM ' . TABLE_PREFIX . 'pay_log' .
     " WHERE log_id = '$log_id'";
    $amount = $GLOBALS['db']->query_first($sql);
    if ($money == $amount['order_amount']) {
        return true;
    } else {
        return false;
    }
}
/**
 * 修改订单的支付状态
 *
 * @access  public
 * @param   string  $log_id     支付编号
 * @param   integer $pay_status 状态
 * @param   string  $note       备注
 * @return  void
 */
function order_paid ($log_id, $pay_status = PS_PAYED, $note = '')
{
    // 取得支付编号
    $log_id = intval($log_id);
    if ($log_id > 0) {
        // 取得要修改的支付记录信息
        $sql = 'SELECT * FROM ' . TABLE_PREFIX . 'pay_log' .
         " WHERE log_id = '$log_id'";
        $pay_log = $GLOBALS['db']->query_first($sql);
        if ($pay_log && $pay_log['is_paid'] == 0) {
            // 修改此次支付操作的状态为已付款
            $sql = 'UPDATE ' . TABLE_PREFIX . 'pay_log' .
             ' SET is_paid = 1 WHERE log_id = ' . $log_id;
            $GLOBALS['db']->query_write($sql);
            // 根据记录类型做相应处理
            if ($pay_log['order_type'] == PAY_ORDER) {
                // 取得订单信息
                $sql = 'SELECT * FROM ' . TABLE_PREFIX .
                 'order_info' . ' WHERE order_id = ' . $pay_log['order_id'];
                $order = $GLOBALS['db']->query_first($sql);
                // 修改订单状态为已付款
                $sql = 'UPDATE ' . TABLE_PREFIX . 'order_info' .
                 " SET pay_status ='" . PS_PAYED . "', pay_time = '" . TIMENOW .
                 "' " . ' WHERE order_id = ' . $order['order_id'];
                $GLOBALS['db']->query_write($sql);
                // 实时开通会员权限
                payment_finsh($order['order_count'],
                $order['usertype'], $order['rank_id'], $order['user_id'],
                $order['order_id'], $order['order_amount']);
            } elseif ($pay_log['order_type'] == PAY_SURPLUS) {
                // 会员预付款处理
                // 更新会员预付款的到款状态
                $sql = 'UPDATE ' . TABLE_PREFIX .
                 'user_account' . " SET paid_time = '" . TIMENOW .
                 "', is_paid = 1" . " WHERE id = '" . $pay_log['order_id'] . "' ";
                $GLOBALS['db']->query_write($sql);
                // 取得添加预付款的用户以及金额
                $sql = 'SELECT user_id, amount FROM ' .
                 TABLE_PREFIX . 'user_account' . ' WHERE id = ' .
                 $pay_log['order_id'];
                $arr = $GLOBALS['db']->query_first($sql);
                // 修改会员帐户金额
                $_LANG = array();
                include_once (DIR . '/languages/' . $GLOBALS['_LANG']['lang'] .
                 '/user.php');
                log_account_change($arr['user_id'], $arr['amount'], 0,
                $_LANG['surplus_type_0'], ACT_SAVING);
                $msg = '';
                $msg = '<tr><td><strong>' . $GLOBALS['_LANG']['buyer'] .
                 ':</strong></td><td>' . $arr['user_id'] .
                 '</td></tr><tr><td><strong>' . $GLOBALS['_LANG']['order_amount'] .
                 ':</strong></td><td>' . $arr['amount'] . '</td></tr>';
            }
            // 支付成功消息
            $GLOBALS['_LANG']['pay_success'] .= '<br /><table align="center">' .
             $msg . '</table>';
        }
    } else {
        $msg = '';
        // 禁止刷新支付成功页面
        $sql = 'SELECT pay_time, order_sn FROM ' . TABLE_PREFIX .
         'order_info' . " WHERE order_id = '" . $pay_log['order_id'] . "'";
        $row = $GLOBALS['db']->query_first($sql);
        if (TIMENOW > $row['pay_time']) {
            $msg = '<div>' . $GLOBALS['_LANG']['no_refresh'] . '</div>';
        }
        // 支付失败消息
        $GLOBALS['_LANG']['pay_success'] .= $msg;
    }
}
/**
 * 支付成功,实时开通会员操作
 *
 * @access  public
 * @param   string  $count     点数或天
 * @param   integer $usetype   会员类型
 * @param   integer $rank_id   会员等级
 * @param   integer $user_id   会员ID
 * @param   integer $order_id	 定单号
 * @param   float   $order_amount 定单金额
 * @return  bool
 */
function payment_finsh ($count = 0, $usertype = 0, $rank_id = 0, $user_id = '',
$order_id = 0, $order_amount = 0)
{
    if (empty($order_id) || empty($rank_id)) {
        die('System error!');
    }
    $sql = 'SELECT usertype, unit_date, user_point, user_name, referrer FROM ' .
     TABLE_PREFIX . 'users' . ' WHERE user_id=' . $user_id;
    $res = $GLOBALS['db']->query_read_slave($sql);
    if ($res !== false) {
        $row = $GLOBALS['db']->fetch_array($res);
        $date = max($row['unit_date'], TIMENOW);
        $unit_date = 86400 * $count + $date; //计时会员添加天数
        $look_count = ! empty($row['user_point']) ? $row['user_point'] +
         $count : $count; //计点会员添加点数
        //计时会员
        if ($usertype == 1) {
            $sql = 'UPDATE ' . TABLE_PREFIX . 'users' .
             " SET usertype=1, user_rank='" . $rank_id . "' , unit_date='" .
             $unit_date . "' WHERE user_id='" . $user_id . "'";
            $GLOBALS['db']->query_write($sql);
        } elseif ($usertype == 0) {
            //计点会员
            $sql = 'UPDATE ' . TABLE_PREFIX . 'users' .
             " SET usertype=0, user_rank='" . $rank_id . "' , user_point='" .
             $look_count . "' WHERE user_id='" . $user_id . "'";
            $GLOBALS['db']->query($sql);
        }
        //重新获取用户缓存
        fetch_userinfo($user_id);
        if (intval($order_amount) > 0) {
            // 用户推荐注册佣金
            $rate = iif(
            $GLOBALS['skyuc']->options['commission'],
            $GLOBALS['skyuc']->options['commission'], '10%');
            if (strpos($rate, '%') !== false) {
                // 支付费用和佣金的一个比例
                $val = floatval($rate) / 100;
                $user_money = iif($val > 0, $order_amount * $val, 0);
            } else {
                $user_money = floatval($rate);
            }
            $user_money = round($user_money, 2);
            $change_desc = sprintf($GLOBALS['_LANG']['referrer'],
            $row['user_name'], price_format($user_money));
            $sql = 'SELECT user_id FROM ' . TABLE_PREFIX . 'users' .
             " WHERE user_name='" . $row['referrer'] . "'";
            $referrer = $GLOBALS['db']->query_first($sql);
            if (! empty($referrer['user_id'])) {
                //充值提现记录，显示给用户查看佣金
                $sql = 'INSERT INTO ' . TABLE_PREFIX .
                 'user_account' .
                 ' (user_id, admin_user, amount, add_time, paid_time, admin_note, user_note, process_type, payment, is_paid)' .
                 " VALUES ('" . $referrer['user_id'] . "', 'system', '" .
                 $user_money . "', '" . TIMENOW . "', " . TIMENOW . ",'" .
                 $GLOBALS['db']->escape_string($change_desc) . "', '',  '0', '" .
                 $GLOBALS['db']->escape_string($GLOBALS['_LANG']['commission']) .
                 "', 1)";
                $GLOBALS['db']->query_write($sql);
                //调节帐户，仅管理员可见
                log_account_change($referrer['user_id'],
                $user_money, 0, $change_desc, ACT_ADJUSTING);
            }
        }
    }
}
?>