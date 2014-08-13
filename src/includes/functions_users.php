<?php
/**
 * SKYUC! 用户相关函数库
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

/**
 * 修改个人资料（Email, 性别，生日，密保问题，密保答案)
 *
 * @access  public
 * @param   array       $profile       array_keys(user_id int, email string, gender int, birthday string, question string, answer string);
 *
 * @return  boolen      $bool
 */
function edit_profile($profile) {

	if (empty ( $profile ['user_id'] )) {
		$GLOBALS ['err']->add ( $GLOBALS ['_LANG'] ['not_login'] );

		return false;
	}
	$cfg = array ();
	$users = $GLOBALS ['db']->query_first_slave ( 'SELECT user_name FROM ' . TABLE_PREFIX . 'users' . ' WHERE user_id=' . $profile ['user_id'] );
	$cfg ['username'] = $users ['user_name'];

	if (isset ( $profile ['gender'] )) {
		$cfg ['gender'] = intval ( $profile ['gender'] );
	}
	if (! empty ( $profile ['email'] )) {
		if (! validate ( $profile ['email'], 4 )) {
			$GLOBALS ['err']->add ( sprintf ( $GLOBALS ['_LANG'] ['email_invalid'], $profile ['email'] ) );

			return false;
		}
		$cfg ['email'] = $profile ['email'];
	}
	if (! empty ( $profile ['birthday'] )) {
		$cfg ['bday'] = $profile ['birthday'];
	}

	if (! $GLOBALS ['user']->edit_user ( $cfg )) {
		if ($GLOBALS ['user']->error == ERR_EMAIL_EXISTS) {
			$GLOBALS ['err']->add ( sprintf ( $GLOBALS ['_LANG'] ['email_exist'], $profile ['email'] ) );
		} else {
			$GLOBALS ['err']->add ( 'DB ERROR!' );
		}

		return false;
	}

	// 过滤非法的键值
	$other_key_array = array ('msn', 'qq', 'phone', 'firstname' );
	foreach ( $profile ['other'] as $key => $val ) {
		//删除非法key值
		if (! in_array ( $key, $other_key_array )) {
			unset ( $profile ['other'] [$key] );
		} else {
			if ($key == 'firstname') {
				$profile ['other'] [$key] = trim ( $val );
			} else {
				$profile ['other'] [$key] = htmlentities ( $val ); //防止用户输入javascript代码
			}

		}
	}

	// 修改其他资料


	if (! empty ( $profile ['other'] )) {
		$sql = fetch_query_sql ( $profile ['other'], 'users', 'WHERE user_id = ' . $profile ['user_id'] );
		$GLOBALS ['db']->query_write ( $sql );
	}

	return true;
}

/**
 * 获取用户帐号信息
 *
 * @access  public
 * @param   int       $user_id        用户user_id
 *
 * @return void
 */
function get_profile($user_id) {

	$sql = 'SELECT user_id, user_name, gender, birthday , email, qq, phone,msn,firstname,last_ip,pay_point,user_money  FROM ' . TABLE_PREFIX . 'users' . ' WHERE user_id = ' . $user_id;
	$row = $GLOBALS ['db']->query_first ( $sql );

	//$row = $GLOBALS['user']->get_profile_by_name($row['user_name']); //获取用户帐号信息


	// 会员帐号信息
	$info = array ();
	$info ['email'] = $row ['email'];
	$info ['user_name'] = $row ['user_name'];
	$info ['pay_point'] = intval ( $row ['pay_point'] );
	$info ['user_money'] = price_format ( $row ['user_money'], false );
	$info ['gender'] = intval ( $row ['gender'] );
	$info ['birthday'] = intval ( $row ['birthday'] );
	$info ['last_ip'] = iif ( isset ( $row ['last_ip'] ), $row ['last_ip'], '0.0.0.0' );
	$info ['qq'] = $row ['qq'];
	$info ['phone'] = $row ['phone'];
	$info ['firstname'] = $row ['firstname'];
	$info ['msn'] = $row ['msn'];

	return $info;
}

/**
 * 获取指定用户的留言
 *
 * @access  public
 * @param   int     $user_id        用户ID
 * @param   int     $user_name      用户名
 * @param   int     $num            列表最大数量
 * @param   int     $start          列表其实位置
 * @return  array   $msg            留言及回复列表
 */
function get_message_list($user_id, $user_name, $num, $start) {
	// 获取留言数据
	$msg = array ();
	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'feedback' . ' WHERE parent_id = 0 AND user_id = ' . $user_id . " AND user_name = '" . $GLOBALS ['db']->escape_string ( $user_name ) . "' ORDER BY msg_time DESC ";
	$sql = $GLOBALS ['db']->query_limit ( $sql, $num, $start );
	$res = $GLOBALS ['db']->query_read_slave ( $sql );
	while ( $rows = $GLOBALS ['db']->fetch_array ( $res ) ) {
		// 取得留言的回复
		$reply = array ();
		$sql = 'SELECT user_name, user_email, msg_time, msg_content' . ' FROM ' . TABLE_PREFIX . 'feedback' . ' WHERE parent_id = ' . $rows ['msg_id'];
		$reply = $GLOBALS ['db']->query_first_slave ( $sql );

		if ($reply) {
			$msg [$rows ['msg_id']] ['re_user_name'] = $reply ['user_name'];
			$msg [$rows ['msg_id']] ['re_user_email'] = $reply ['user_email'];
			$msg [$rows ['msg_id']] ['re_msg_time'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $reply ['msg_time'] );
			$msg [$rows ['msg_id']] ['re_msg_content'] = nl2br ( htmlspecialchars ( $reply ['msg_content'] ) );
		}

		$msg [$rows ['msg_id']] ['msg_content'] = nl2br ( htmlspecialchars ( $rows ['msg_content'] ) );
		$msg [$rows ['msg_id']] ['msg_time'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $rows ['msg_time'] );
		$msg [$rows ['msg_id']] ['msg_type'] = $GLOBALS ['_LANG'] ['type'] [$rows ['msg_type']];
		$msg [$rows ['msg_id']] ['msg_title'] = nl2br ( htmlspecialchars ( $rows ['msg_title'] ) );
		$msg [$rows ['msg_id']] ['message_img'] = $rows ['message_img'];
	}

	return $msg;
}

/**
 * 插入会员账目明细
 *
 * @access  public
 * @param   array     $surplus  会员余额信息
 * @param   string    $amount   余额
 *
 * @return  int
 */
function insert_user_account($surplus, $amount) {
	$sql = 'INSERT INTO ' . TABLE_PREFIX . 'user_account' . ' (user_id, admin_user, amount, add_time, paid_time, admin_note, user_note, process_type, payment, is_paid)' . " VALUES ('" . $surplus ['user_id'] . "', '', '" . $amount . "', '" . TIMENOW . "', 0, '', '" . $GLOBALS ['db']->escape_string ( $surplus ['user_note'] ) . "', '" . $surplus ['process_type'] . "', '" . $GLOBALS ['db']->escape_string ( $surplus ['payment'] ) . "', 0)";
	$GLOBALS ['db']->query_write ( $sql );

	return $GLOBALS ['db']->insert_id ();
}

/**
 * 更新会员账目明细
 *
 * @access  public
 * @param   array     $surplus  会员余额信息
 *
 * @return  int
 */
function update_user_account($surplus) {

	$sql = 'UPDATE ' . TABLE_PREFIX . 'user_account' . ' SET ' . " amount     = '" . $surplus ['amount'] . "', " . " user_note  = '" . $GLOBALS ['db']->escape_string ( $surplus ['user_note'] ) . "', " . " payment    = '" . $GLOBALS ['db']->escape_string ( $surplus ['payment'] ) . "' " . ' WHERE id   = ' . $surplus ['rec_id'];
	$GLOBALS ['db']->query_write ( $sql );

	return $surplus ['rec_id'];
}

/**
 * 将支付LOG插入数据表
 *
 * @access  public
 * @param   integer     $id         订单编号
 * @param   float       $amount     订单金额
 * @param   integer     $type       支付类型
 * @param   integer     $is_paid    是否已支付
 *
 * @return  int
 */
function insert_pay_log($id, $amount, $type = PAY_SURPLUS, $is_paid = 0) {

	$sql = 'INSERT INTO ' . TABLE_PREFIX . 'pay_log' . ' (order_id, order_amount, order_type, is_paid)' . " VALUES  ('" . $id . "', '" . $amount . "', '" . $type . "', '" . $is_paid . "')";
	$GLOBALS ['db']->query_write ( $sql );

	return $GLOBALS ['db']->insert_id ();
}

/**
 * 取得上次未支付的pay_lig_id
 *
 * @access  public
 * @param   array     $surplus_id  余额记录的ID
 * @param   array     $pay_type    支付的类型：预付款/订单支付
 *
 * @return  int
 */
function get_paylog_id($surplus_id, $pay_type = PAY_SURPLUS) {

	$sql = 'SELECT log_id FROM ' . TABLE_PREFIX . 'pay_log' . " WHERE order_id = '" . $surplus_id . "' AND order_type = '" . $pay_type . "' AND is_paid = 0";
	$row = $GLOBALS ['db']->query_first ( $sql );

	return $row ['log_id'];
}

/**
 * 根据ID获取当前余额操作信息
 *
 * @access  public
 * @param   int     $surplus_id  会员余额的ID
 *
 * @return  int
 */
function get_surplus_info($surplus_id) {

	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'user_account' . " WHERE id = '" . $surplus_id . "'";

	return $GLOBALS ['db']->query_first ( $sql );
}

/**
 * 取得已安装的支付方式(其中不包括线下支付的)
 * @param   bool    $include_balance    是否包含余额支付（冲值时不应包括）
 * @return  array   已安装的配送方式列表
 */
function get_online_payment_list($include_balance = true) {
	$sql = 'SELECT pay_id, pay_name, pay_fee, pay_desc ' . 'FROM ' . TABLE_PREFIX . 'payment' . ' WHERE enabled = 1 AND is_cod <> 2';
	if (! $include_balance) {
		$sql .= " AND pay_code <> 'balance' ";
	}
	$sql .= ' ORDER BY pay_order  ASC';

	$pay_list = $GLOBALS ['db']->query_all_slave ( $sql );
	foreach ($pay_list as $key => $value) {
	    $pay_list["$key"]['pay_fee'] = $value['pay_fee'] ? $value['pay_fee'].'%' : 0;
	}

	return $pay_list;
}

/**
 * 查询会员余额的操作记录
 *
 * @access  public
 * @param   int     $user_id    会员ID
 * @param   int     $num        每页显示数量
 * @param   int     $start      开始显示的条数
 * @return  array
 */
function get_account_log($user_id, $num, $start) {
	$account_log = array ();
	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'user_account' . ' WHERE user_id = ' . $user_id . ' ORDER BY add_time DESC';
	$sql = $GLOBALS ['db']->query_limit ( $sql, $num, $start );
	$res = $GLOBALS ['db']->query_read_slave ( $sql );

	if ($res) {
		while ( $rows = $GLOBALS ['db']->fetch_array ( $res ) ) {
			$rows ['add_time'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $rows ['add_time'] );
			$rows ['admin_note'] = nl2br ( htmlspecialchars ( $rows ['admin_note'] ) );
			$rows ['short_admin_note'] = iif ( ($rows ['admin_note'] != ''), sub_str ( $rows ['admin_note'], 15, true ), 'N/A' );
			$rows ['user_note'] = nl2br ( htmlspecialchars ( $rows ['user_note'] ) );
			$rows ['short_user_note'] = iif ( ($rows ['user_note'] != ''), sub_str ( $rows ['user_note'], 15, ture ), 'N/A' );
			$rows ['pay_status'] = iif ( ($rows ['is_paid'] == 0), $GLOBALS ['_LANG'] ['un_confirm'], $GLOBALS ['_LANG'] ['is_confirm'] );

			// 会员的操作类型： 预付款，退款申请，取消订单
			switch ($rows ['process_type']) {
				case 0 :
					$rows ['type'] = $GLOBALS ['_LANG'] ['surplus_type_0'];
					break;
				case 1 :
					$rows ['type'] = $GLOBALS ['_LANG'] ['surplus_type_1'];
					break;
				case 2 :
					$rows ['type'] = $GLOBALS ['_LANG'] ['surplus_type_2'];
					break;
				case 3 :
				default :
					$rows ['type'] = $GLOBALS ['_LANG'] ['surplus_type_3'];
					break;

			}

			// 支付方式的ID
			$sql = 'SELECT pay_id FROM ' . TABLE_PREFIX . 'payment' . " WHERE pay_name = '" . $GLOBALS ['db']->escape_string ( $rows ['payment'] ) . "' AND enabled = 1";
			$pid = $GLOBALS ['db']->query_first_slave ( $sql );

			// 如果是预付款而且还没有付款, 允许付款
			if (($rows ['is_paid'] == 0) && ($rows ['process_type'] == 0)) {
				$rows ['handle'] = '<a href="user.php?act=pay&id=' . $rows ['id'] . '&pid=' . $pid ['pay_id'] . '">' . $GLOBALS ['_LANG'] ['pay'] . '</a>';
			}

			$account_log [] = $rows;
		}

		return $account_log;
	} else {
		return false;
	}
}

/**
 * 删除未确认的会员帐目信息
 *
 * @access  public
 * @param   int         $rec_id     会员余额记录的ID
 * @param   int         $user_id    会员的ID
 * @return  boolen
 */
function del_user_account($rec_id, $user_id) {
	$sql = 'DELETE FROM ' . TABLE_PREFIX . 'user_account' . ' WHERE is_paid = 0 AND id = ' . $rec_id . ' AND user_id = ' . $user_id;

	return $GLOBALS ['db']->query_write ( $sql );
}

/**
 * 查询会员余额的数量
 * @access  public
 * @param   int     $user_id        会员ID
 * @return  int
 */
function get_user_surplus($user_id) {

	$sql = 'SELECT SUM(amount) AS total FROM ' . TABLE_PREFIX . 'user_account' . ' WHERE is_paid = 1 AND user_id = ' . $user_id;
	$total = $GLOBALS ['db']->query_first_slave ( $sql );

	return $total ['total'];
}

/**
 * 获取用户中心默认页面所需的数据
 *
 * @access  public
 * @param   int         $user_id            用户ID
 *
 * @return  array       $info               默认页面所需资料数组
 */
function get_user_default($user_id) {

	$sql = 'SELECT user_name, pay_point, user_money,lastvisit,usertype,user_rank, user_point,unit_date,last_ip,reg_time,visit_count,playcount, minute, is_validated	FROM ' . TABLE_PREFIX . 'users' . ' WHERE user_id = ' . $user_id;
	$row = $GLOBALS ['db']->query_first ( $sql );
	$info = $row;

	if ($row ['unit_date'] > 0) {
		$info ['unit_date'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'], $row ['unit_date'] );
	} else {
		$info ['unit_date'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'], $row ['reg_time'] );
	}
	$info ['minute'] = iif ( $row ['minute'] > 60, sprintf ( $GLOBALS ['_LANG'] ['format_hour'] . ' ' . $GLOBALS ['_LANG'] ['format_minute'], floor ( $row ['minute'] / 60 ), $row ['minute'] % 60 ), sprintf ( $GLOBALS ['_LANG'] ['format_minute'], $row ['minute'] ) );

	$info ['username'] = $row ['user_name'];
	$info ['site_name'] = $GLOBALS ['skyuc']->options ['site_name'];
	$info ['integral'] = $row ['pay_point'];

	// 增加是否开启会员邮件验证开关
	$info ['is_validate'] = iif ( ($GLOBALS ['skyuc']->options ['member_email_validate'] && ! $row ['is_validated']), 0, 1 );
	$info ['reg_time'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $row ['reg_time'] );

	if ($row ['usertype'] == 1) {
		$info ['usertype'] = $GLOBALS ['_LANG'] ['is_day'];
		$info ['endlook'] = $info ['unit_date'];
		$info ['your_endlook'] = $GLOBALS ['_LANG'] ['your_endlook_d'];
	} else {
		$info ['usertype'] = $GLOBALS ['_LANG'] ['is_count'];
		$info ['endlook'] = $row ['user_point'];
		$info ['your_endlook'] = $GLOBALS ['_LANG'] ['your_endlook_p'];
	}
	$rank_name = $GLOBALS ['skyuc']->usergroup [$row ['user_rank']] ['name'];
	$info ['rank_name'] = iif ( ($rank_name != false), $rank_name, $GLOBALS ['_LANG'] ['no_rank'] );

	//如果用户是第一次登录。取当前登录时间。
	$lastvisit = iif ( $row ['lastvisit'] > 0, $row ['lastvisit'], TIMENOW );
	$info ['lastvisit'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $lastvisit );
	$info ['surplus'] = price_format ( $row ['user_money'] );

	$pre_month = skyuc_date ( 'Ymd', strtotime ( '-1 month' ) );
	$total = $GLOBALS ['db']->query_first_slave ( 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'order_info' . " WHERE user_id = '" . $user_id . "' AND order_sn > '" . $pre_month . "'" );
	$info ['order_count'] = $total ['total'];

	$info ['payed_order'] = $GLOBALS ['db']->query_all_slave ( 'SELECT order_id, order_sn FROM ' . TABLE_PREFIX . 'order_info' . " WHERE user_id = '" . $user_id . "' AND order_time  > '" . ($lastvisit - 86400 * 30) . "'" );

	return $info;
}

/**
 * 更新用户 COOKIE及登录时间、登录次数。
 *
 * @access  public
 * @return  void
 */
function update_user_info() {

	if (! $GLOBALS ['skyuc']->session->vars ['userid']) {
		return false;
	}

	// 更新登录时间，登录次数及登录ip
	$sql = 'UPDATE ' . TABLE_PREFIX . 'users' . ' SET ' . ' visit_count = visit_count + 1, ' . " last_ip = '" . ALT_IP . "'," . ' lastvisit = ' . TIMENOW . ',' . ' lastactivity = ' . TIMENOW . ' ' . " WHERE user_id = '" . $GLOBALS ['skyuc']->session->vars ['userid'] . "'";
	$GLOBALS ['db']->query_write ( $sql );

	// 查询会员信息
	$sql = 'SELECT user_id, user_name, password FROM ' . TABLE_PREFIX . 'users' . ' WHERE user_id = ' . $GLOBALS ['skyuc']->session->vars ['userid'];
	$row = $GLOBALS ['db']->query_first_slave ( $sql );

	// 更新Cookie
	$time = TIMENOW + 86400 * 30;
	if (! empty ( $row )) {
		skyuc_setcookie ( 'username', $row ['user_name'], $time );
		skyuc_setcookie ( 'userid', $row ['user_id'], $time );
		skyuc_setcookie ( 'password', $row ['password'], $time );
	}
}

/**
 * 获取用户的tags
 *
 * @access  public
 * @param   int         $user_id        用户ID
 *
 * @return array        $arr            tags列表
 */
function get_user_tags($user_id = 0) {
	if (empty ( $user_id )) {
		$GLOBALS ['error_no'] = 1;

		return false;
	}

	$tags = get_tags ( 0, $user_id );

	if (! empty ( $tags )) {
		color_tag ( $tags );
	}

	return $tags;
}

/**
 * 验证性的删除某个tag
 *
 * @access  public
 * @param   int         $tag_words      tag的ID
 * @param   int         $user_id        用户的ID
 *
 * @return  boolen      bool
 */
function delete_tag($tag_words, $user_id) {
	$sql = 'DELETE FROM ' . TABLE_PREFIX . 'tag' . " WHERE tag_words = '" . $GLOBALS ['db']->escape_string ( $tag_words ) . "' AND user_id = '" . $user_id . "'";

	return $GLOBALS ['db']->query_write ( $sql );
}

/**
 * 添加影片标签
 *
 * @access  public
 * @param   integer     $id
 * @param   string      $tag
 * @return  void
 */
function add_tag($id, $tag) {

	if (empty ( $tag )) {
		return;
	}

	$arr = explode ( ' ', $tag );

	foreach ( $arr as $val ) {
		// 检查是否重复
		$sql = 'SELECT COUNT(*) FROM ' . TABLE_PREFIX . 'tag' . " WHERE user_id = '" . $GLOBALS ['skyuc']->session->vars ['userid'] . "' AND show_id = '" . $id . "' AND tag_words = '" . $val . "'";
		$total = $GLOBALS ['db']->query_first_slave ( $sql );
		if ($total ['total'] == 0) {
			$sql = 'INSERT INTO ' . TABLE_PREFIX . 'tag' . ' (user_id, show_id, tag_words) ' . "VALUES ('" . $GLOBALS ['skyuc']->session->vars ['userid'] . "', '$id', '" . $val . "')";
			$GLOBALS ['db']->query_write ( $sql );

		    include(DIR. '/includes/functions_search.php');
    	    $param = array();
    		$param['show_id'] = $id;
    	    $param['tag'] = $val;
    		add_search_index($param, false, true);
		}
	}
}

/**
 * 标签着色
 *
 * @access   public
 * @param    array
 * @author   Xuan Yan
 *
 * @return   none
 */
function color_tag(&$tags) {
	$tagmark = array (array ('color' => '#666666', 'size' => '1.0em', 'ifbold' => 1 ), array ('color' => '#333333', 'size' => '1.1em', 'ifbold' => 0 ), array ('color' => '#006699', 'size' => '1.2em', 'ifbold' => 1 ), array ('color' => '#CC9900', 'size' => '1.3em', 'ifbold' => 0 ), array ('color' => '#666633', 'size' => '1.4em', 'ifbold' => 1 ), array ('color' => '#993300', 'size' => '1.5em', 'ifbold' => 0 ), array ('color' => '#669933', 'size' => '1.6em', 'ifbold' => 1 ), array ('color' => '#3366FF', 'size' => '1.7em', 'ifbold' => 0 ), array ('color' => '#197B30', 'size' => '1.8em', 'ifbold' => 1 ) );

	$maxlevel = count ( $tagmark );
	$tcount = $scount = array ();

	foreach ( $tags as $val ) {
		$tcount [] = $val ['tag_count']; // 获得tag个数数组
	}
	$tcount = array_unique ( $tcount ); // 去除相同个数的tag


	sort ( $tcount ); // 从小到大排序


	$tempcount = count ( $tcount ); // 真正的tag级数
	$per = $maxlevel >= $tempcount ? 1 : $maxlevel / ($tempcount - 1);

	foreach ( $tcount as $key => $val ) {
		$lvl = floor ( $per * $key );
		$scount [$val] = $lvl; // 计算不同个数的tag相对应的着色数组key
	}

	$rewrite = intval ( $GLOBALS ['skyuc']->options ['rewrite'] ) > 0;

	/* 遍历所有标签，根据引用次数设定字体大小 */
	foreach ( $tags as $key => $val ) {
		$lvl = $scount [$val ['tag_count']]; // 着色数组key


		$tags [$key] ['color'] = $tagmark [$lvl] ['color'];
		$tags [$key] ['size'] = $tagmark [$lvl] ['size'];
		$tags [$key] ['bold'] = $tagmark [$lvl] ['ifbold'];
		$tags [$key] ['url'] = $rewrite ? 'tag-' . urlencode ( $val ['tag_words'] ) . '.html' : 'search.php?keywords=' . urlencode ( $val ['tag_words'] );
	}
	shuffle ( $tags );
}

/**
 * 获取用户评论
 *
 * @access  public
 * @param   int     $user_id        用户id
 * @param   int     $page_size      列表最大数量
 * @param   int     $start          列表起始页
 * @return  array
 */
function get_comment_list($user_id, $page_size, $start) {
	$sql = 'SELECT c.*, m.title AS cmt_name, r.content AS reply_content, r.add_time AS reply_time ' . ' FROM ' . TABLE_PREFIX . 'comment' . ' AS c ' . ' LEFT JOIN ' . TABLE_PREFIX . 'comment' . ' AS r ' . ' ON r.parent_id = c.comment_id AND r.parent_id > 0 ' . ' LEFT JOIN ' . TABLE_PREFIX . 'show' . ' AS m ' . ' ON c.comment_type=0 AND c.id_value = m.show_id ' . ' WHERE c.user_id=' . $user_id;
	$sql = $GLOBALS ['db']->query_limit ( $sql, $page_size, $start );
	$res = $GLOBALS ['db']->query_read_slave ( $sql );

	$comments = array ();
	$to_article = array ();
	while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
		$row ['formated_add_time'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $row ['add_time'] );
		if ($row ['reply_time']) {
			$row ['formated_reply_time'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $row ['reply_time'] );
		}
		if ($row ['comment_type'] == 1) {
			$to_article [] = $row ['id_value'];
		}
		$comments [] = $row;
	}

	if ($to_article) {
		$sql = 'SELECT article_id , title FROM ' . TABLE_PREFIX . 'article' . ' WHERE ' . db_create_in ( $to_article, 'article_id' );
		$res = $GLOBALS ['db']->query_read_slave ( $sql );
		$to_cmt_name = array ();
		while ( $rows = $GLOBALS ['db']->fetch_array ( $res ) ) {
			$to_cmt_name [$rows ['article_id']] = $rows ['title'];
		}
		foreach ( $comments as $key => $val ) {
			if ($val ['comment_type'] == 1) {
				$comments [$key] ['cmt_name'] = isset ( $to_cmt_name [$val ['id_value']] ) ? $to_cmt_name [$val ['id_value']] : '';
			}
		}
	}

	return $comments;
}
