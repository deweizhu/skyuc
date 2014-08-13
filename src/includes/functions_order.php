<?php
/**
 * SKYUC! 用户订单函数库
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

/**
 * 处理序列化的支付的配置参数
 * 返回一个以name为索引的数组
 *
 * @access  public
 * @param   string       $cfg
 * @return  void
 */
function unserialize_config($cfg) {
	if (is_string ( $cfg ) && ($arr = unserialize ( $cfg )) !== false) {
		$config = array ();

		foreach ( $arr as $key => $val ) {
			$config [$val ['name']] = $val ['value'];
		}

		return $config;
	} else {
		return false;
	}
}

/**
 * 取得已安装的支付方式列表
 * @return  array   已安装的支付方式列表
 */
function payment_list() {
	global $skyuc;

	$sql = 'SELECT pay_id, pay_name ' . 'FROM ' . TABLE_PREFIX . 'payment' . ' WHERE enabled = 1';

	return $skyuc->db->query_all_slave ( $sql );
}

/**
 * 取得支付方式信息
 * @param   int     $pay_id     支付方式id
 * @return  array   支付方式信息
 */
function payment_info($pay_id) {
	global $skyuc;

	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'payment' . ' WHERE pay_id = ' . $pay_id . ' AND enabled = 1';

	return $skyuc->db->query_first_slave ( $sql );
}

/**
 * 获得订单需要支付的支付费用
 *
 * @access  public
 * @param   integer $payment_id
 * @param   float   $order_amount
 * @return  float
 */
function pay_fee($payment_id, $order_amount, $codfee = 0) {
	$pay_fee = 0;
	$payment = payment_info ( $payment_id );
	$rate = empty ( $codfee ) ? $payment ['pay_fee'] : $codfee;

	if (strpos ( $rate, '%' ) !== false) {
		/* 支付费用是一个比例 */
		$val = floatval ( $rate ) / 100;
		$pay_fee = $val > 0 ? $order_amount * $val / (1 - $val) : 0;
	} else {
		$pay_fee = floatval ( $rate );
	}

	return round ( $pay_fee, 2 );

}

/**
 * 取得订单信息
 * @param   int     $order_id   订单id（如果order_id > 0 就按id查，否则按sn查）
 * @param   string  $order_sn   订单号
 * @return  array   订单信息
 */
function order_info($order_id, $order_sn = '') {
	global $skyuc;

	$order_id = intval ( $order_id );
	if ($order_id > 0) {
		$sql = 'SELECT *  FROM ' . TABLE_PREFIX . 'order_info' . ' WHERE order_id = ' . $order_id;
	} else {
		$sql = 'SELECT *   FROM ' . TABLE_PREFIX . 'order_info' . " WHERE order_sn = '" . $order_sn . "'";
	}
	$order = $skyuc->db->query_first ( $sql );

	// 格式化金额字段
	if ($order) {
		$order ['formated_order_amount'] = price_format ( abs ( $order ['order_amount'] ) );
		$order ['formated_pay_amount'] = price_format ( $order ['pay_amount'] );
	}

	return $order;
}

/**
 * 判断订单是否已完成
 * @param   array   $order  订单信息
 * @return  bool
 */
function order_finished($order) {
	return ($order ['pay_status'] == PS_PAYED) || ($order ['pay_status'] == PS_PAYING);
}

/**
 * 修改订单
 * @param   int     $order_id   订单id
 * @param   array   $order      key => value
 * @return  bool
 */
function update_order($order_id, $order) {
	global $skyuc;

	return $skyuc->db->query_write ( fetch_query_sql ( $order, 'order_info', 'WHERE order_id = ' . $order_id ) );
}

/**
 * 得到新订单号
 * @return  string
 */
function get_order_sn() {
	// 选择一个随机的方案
	mt_srand ( ( double ) microtime () * 1000000 );

	return skyuc_date ( 'Ymd' ) . str_pad ( mt_rand ( 1, 99999 ), 5, '0', STR_PAD_LEFT );
}

/**
 * 修改用户
 * @param   int     $user_id   订单id
 * @param   array   $user      key => value
 * @return  bool
 */
function update_user($user_id, $user) {
	global $skyuc;

	return $skyuc->db->query_write ( fetch_query_sql ( $user, 'users', 'WHERE user_id = ' . $user_id ) );
}

/**
 * 取得用户信息
 * @param   int     $user_id    用户id
 * @return  array   用户信息
 */
function get_user_info($user_id) {
	global $skyuc;

	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'users' . ' WHERE user_id = ' . $user_id;
	$user = $skyuc->db->query_first ( $sql );

	// 格式化帐户余额
	if (! empty ( $user )) {
		$user ['formated_user_money'] = price_format ( $user ['user_money'] );
	}
	return $user;
}

/**
 * 计算积分的价值（能抵多少钱）
 * @param   int     $integral   积分
 * @return  float   积分价值
 */
function value_of_integral($integral) {
	global $skyuc;
	$scale = floatval ( $skyuc->options ['integral_scale'] );

	return $scale > 0 ? round ( ($integral / 100) * $scale, 2 ) : 0;
}

/**
 * 计算指定的金额需要多少积分
 *
 * @access  public
 * @param   integer $value  金额
 * @return  void
 */
function integral_of_value($value) {
	global $skyuc;
	$scale = floatval ( $skyuc->options ['integral_scale'] );

	return $scale > 0 ? round ( $value / $scale * 100 ) : 0;
}
/**
 * 获取会员等级名称
 *
 * @access  public
 * @param   integer $rank_id  等级ＩＤ
 * @return  string
 */
function get_rank_name($rank_id) {
	global $skyuc;

	if ($rank_id > 0 && ! empty ( $skyuc->usergroup )) {
		//从缓存中取得等级名称
		$rank_name = $skyuc->usergroup [$rank_id] ['name'];
	} else {
		$rank_name = false;
	}
	return $rank_name;
}

/**
 * 获取用户指定范围的订单列表
 *
 * @access  public
 * @param   int         $user_id        用户ID号
 * @param   int         $num            列表最大数量
 * @param   int         $start          列表起始位置
 * @return  array       $order_list     订单列表
 */
function get_user_orders($user_id, $num = 10, $start = 0) {
	global $skyuc;

	// 取得订单列表
	$arr = array ();

	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'order_info' . ' WHERE user_id = ' . $user_id . ' ORDER BY order_time DESC';
	$sql = $skyuc->db->query_limit ( $sql, $num, $start );
	$res = $skyuc->db->query_read ( $sql );

	while ( $row = $skyuc->db->fetch_array ( $res ) ) {
		if ($row ['pay_status'] == PS_PAYED) {
			$row ['handler'] = "<a href=\"user.php?act=order_detail&order_id=" . $row ['order_id'] . '">' . $skyuc->lang ['view_order'] . '</a>';
		} else {
			$row ['handler'] = "<a href=\"user.php?act=cancel_order&order_id=" . $row ['order_id'] . '">' . $skyuc->lang ['cancel'] . '</a>';
		}

		//取得等级名称
		$rank_name = get_rank_name ( $row ['rank_id'] );

		$row ['pay_status'] = $skyuc->lang ['ps'] [$row ['pay_status']];
		$buyinfo = iif ( ! empty ( $row ['usertype'] ), $rank_name . $row ['order_count'] . $skyuc->lang ['look_day'], $rank_name . $row ['order_count'] . $skyuc->lang ['look_count'] );

		$arr [] = array ('order_id' => $row ['order_id'], 'order_sn' => $row ['order_sn'], 'order_time' => skyuc_date ( $skyuc->options ['date_format'] . ' ' . $skyuc->options ['time_format'], $row ['order_time'] ), 'pay_status' => $row ['pay_status'], 'order_amount' => price_format ( $row ['order_amount'] ), 'order_buyinfo' => $buyinfo, 'handler' => $row ['handler'] );
	}

	return $arr;
}

/**
 * 取消一个用户订单
 *
 * @access  public
 * @param   int         $order_id       订单ID
 * @param   int         $user_id        用户ID
 *
 * @return void
 */
function cancel_order($order_id, $user_id = 0) {
	global $skyuc, $err;

	//查询订单信息，检查状态
	$sql = 'SELECT user_id, order_sn , surplus , integral , pay_status FROM ' . TABLE_PREFIX . 'order_info' . ' WHERE order_id = ' . $order_id;
	$order = $skyuc->db->query_first_slave ( $sql );

	if ($order === false) {
		$err->add ( $skyuc->lang ['order_exist'] );
		return false;
	}

	// 如果用户ID大于0，检查订单是否属于该用户
	if ($user_id > 0 && $order ['user_id'] != $user_id) {
		$err->add ( $skyuc->lang ['no_priv'] );

		return false;
	}

	// 如果付款状态是“已付款”，不允许取消，要取消和管理员联系
	if ($order ['pay_status'] == PS_PAYED) {
		$err->add ( $skyuc->lang ['current_ps_not_cancel'] );

		return false;
	}

	// 将用户订单设置为取消
	$sql = 'DELETE FROM ' . TABLE_PREFIX . 'order_info' . '  WHERE order_id = ' . $order_id;
	if ($skyuc->db->query_write ( $sql )) {
		return true;
	} else {
		die ( $skyuc->db->errorMsg () );
	}

}

/**
 * 获取指定订单的详情
 *
 * @access  public
 * @param   int         $order_id       订单ID
 * @param   int         $user_id        用户ID
 *
 * @return   arr        $order          订单所有信息的数组
 */
function get_order_detail($order_id, $user_id = 0) {
	global $skyuc, $err;

	$order_id = intval ( $order_id );
	if ($order_id <= 0) {
		$err->add ( $skyuc->lang ['invalid_order_id'] );

		return false;
	}
	$order = order_info ( $order_id );

	//检查订单是否属于该用户
	if ($user_id > 0 && $user_id != $order ['user_id']) {
		$err->add ( $skyuc->lang ['no_priv'] );

		return false;
	}
	$order ['order_time'] = skyuc_date ( $skyuc->options ['date_format'] . ' ' . $skyuc->options ['time_format'], $order ['order_time'] );
	if ($order ['pay_time'] > 0 && $order ['pay_status'] == PS_PAYED) {
		$order ['pay_time'] = sprintf ( $skyuc->lang ['pay_time'], skyuc_date ( $skyuc->options ['date_format'] . ' ' . $skyuc->options ['time_format'], $order ['pay_time'] ) );
	} else {
		$order ['pay_time'] = '';
	}
	//取得等级名称
	$rank_name = get_rank_name ( $order ['rank_id'] );

	$order ['formated_surplus'] = price_format ( $order ['surplus'] );
	$order ['formated_integral'] = price_format ( value_of_integral ( $order ['integral'] ) );
	$order ['buyinfo'] = iif ( ! empty ( $order ['usertype'] ), $rank_name . $order ['order_count'] . $skyuc->lang ['look_day'], $rank_name . $order ['order_count'] . $skyuc->lang ['look_count'] );

	// 订单 支付状态语言项
	$order ['pay_status'] = $skyuc->lang ['ps'] [$order ['pay_status']];

	return $order;

}
?>