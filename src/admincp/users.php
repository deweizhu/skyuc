<?php

/**
 * SKYUC! 会员管理程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

/*------------------------------------------------------ */
//-- 用户帐号列表
/*------------------------------------------------------ */

if ($skyuc->GPC ['act'] == 'list') {
	$intro_type = array ('is_count' => $_LANG ['is_count'], 'is_day' => $_LANG ['is_day'], 'is_validated' => $_LANG ['is_validated'], 'no_validated' => $_LANG ['no_validated'] );

	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['02_user_list'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['03_user_add'], 'href' => 'users.php?act=add' ) );
	$smarty->assign ( 'lang', $_LANG );

	$user_list = get_user_list ();

	$smarty->assign ( 'user_list', $user_list ['user_list'] );
	$smarty->assign ( 'intro_list', $intro_type );
	$smarty->assign ( 'filter', $user_list ['filter'] );
	$smarty->assign ( 'record_count', $user_list ['record_count'] );
	$smarty->assign ( 'page_count', $user_list ['page_count'] );
	$smarty->assign ( 'full_page', 1 );

	// 排序标记
	$sort_flag = sort_flag ( $user_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	assign_query_info ();
	$smarty->display ( 'users_list.tpl' );
}

/*------------------------------------------------------ */
//-- ajax返回用户列表
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$user_list = get_user_list ();

	$smarty->assign ( 'user_list', $user_list ['user_list'] );
	$smarty->assign ( 'filter', $user_list ['filter'] );
	$smarty->assign ( 'record_count', $user_list ['record_count'] );
	$smarty->assign ( 'page_count', $user_list ['page_count'] );

	$sort_flag = sort_flag ( $user_list ['filter'] );
	$smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );

	make_json_result ( $smarty->fetch ( 'users_list.tpl' ), '', array ('filter' => $user_list ['filter'], 'page_count' => $user_list ['page_count'] ) );
}

/*------------------------------------------------------ */
//-- 添加会员帐号
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'add') {
	// 检查权限
	admin_priv ( 'users_manage' );

	$user = array ('pay_point' => $skyuc->options ['register_points'], 'gender' => 0, 'birthday' => strtotime ( '-25 year', TIMENOW ) );

	$smarty->assign ( 'user', $user );
	$smarty->assign ( 'ur_here', $_LANG ['03_user_add'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['02_user_list'], 'href' => 'users.php?act=list' ) );
	$smarty->assign ( 'form_action', 'insert' );
	$smarty->assign ( 'ranks', $skyuc->usergroup );

	assign_query_info ();
	$smarty->display ( 'user_info.tpl' );
}

/*------------------------------------------------------ */
//-- 添加会员帐号
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'insert') {
	//检查权限
	admin_priv ( 'users_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('username' => TYPE_STR, 'password' => TYPE_STR, 'email' => TYPE_STR, 'gender' => TYPE_UINT, 'birthdayYear' => TYPE_UINT, 'birthdayMonth' => TYPE_UINT, 'birthdayDay' => TYPE_UINT, 'usertype' => TYPE_BOOL, 'user_rank' => TYPE_UINT, 'pay_point' => TYPE_UINT, 'other' => TYPE_ARRAY_STR ) );

	$username = $skyuc->GPC ['username'];
	$password = $skyuc->GPC ['password'];
	$email = $skyuc->GPC ['email'];
	$gender = $skyuc->GPC ['gender'];
	$gender = in_array ( $gender, array (0, 1, 2 ) ) ? $gender : 0;
	$birthday = skyuc_mktime ( 0, 0, 0, $skyuc->GPC ['birthdayMonth'], $skyuc->GPC ['birthdayDay'], $skyuc->GPC ['birthdayYear'] );

	$users = & init_users ();

	if (! $users->add_user ( $username, $password, $email, $gender, $birthday, TIMENOW )) {
		// 插入会员数据失败
		if ($users->error == ERR_INVALID_USERNAME) {
			$msg = $_LANG ['username_invalid'];
		} elseif ($users->error == ERR_USERNAME_NOT_ALLOW) {
			$msg = $_LANG ['username_not_allow'];
		} elseif ($users->error == ERR_USERNAME_EXISTS) {
			$msg = $_LANG ['username_exists'];
		} elseif ($users->error == ERR_INVALID_EMAIL) {
			$msg = $_LANG ['email_invalid'];
		} elseif ($users->error == ERR_EMAIL_NOT_ALLOW) {
			$msg = $_LANG ['email_not_allow'];
		} elseif ($users->error == ERR_EMAIL_EXISTS) {
			$msg = $_LANG ['email_exists'];
		} else {
			//die('Error:'.$users->error_msg());
		}
		sys_msg ( $msg, 1 );
	}

	// 更新会员的其它信息
	$sql = 'UPDATE ' . TABLE_PREFIX . 'users' . ' SET ' . 'usertype = ' . $skyuc->GPC ['usertype'] . ',' . 'user_rank	=	' . $skyuc->GPC ['user_rank'] . ',' . 'gender = ' . $gender . ',' . 'birthday = ' . $birthday . ',' . 'unit_date = ' . TIMENOW . ',' . 'pay_point = ' . $skyuc->GPC ['pay_point'] . ', ' . "qq = '" . $db->escape_string ( $skyuc->GPC ['other'] ['qq'] ) . "'," . "msn = '" . $db->escape_string ( $skyuc->GPC ['other'] ['msn'] ) . "'," . "phone = '" . $db->escape_string ( $skyuc->GPC ['other'] ['phone'] ) . "' " . " WHERE user_name ='" . $db->escape_string ( $username ) . "'";
	$skyuc->db->query_write ( $sql );

	// 记录管理员操作 */
	admin_log ( $username, 'add', 'users' );

	// 提示信息
	$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'users.php?act=list' );
	sys_msg ( sprintf ( $_LANG ['add_success'], htmlspecialchars ( $username ) ), 0, $link );

}

/*------------------------------------------------------ */
//-- 编辑用户帐号
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'edit') {
	// 检查权限
	admin_priv ( 'users_manage' );

	$skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	$users = & init_users ();
	$user = $users->get_user_info ( $skyuc->GPC ['id'] );

	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'users' . ' WHERE user_id=' . $skyuc->GPC ['id'];

	$row = $skyuc->db->query_first ( $sql );

	if ($row) {

		if ($row ['unit_date'] > 0) {
			$row ['unit_date'] = skyuc_date ( $skyuc->options ['date_format'], $row ['unit_date'], false, false );
		} else {
			$row ['unit_date'] = skyuc_date ( $skyuc->options ['date_format'], $row ['reg_time'], false, false );
		}
		$row ['birthday'] = skyuc_date ( $skyuc->options ['date_format'], $row ['birthday'], false, false );
		$row ['lastactivity'] = skyuc_date ( $skyuc->options ['date_format'] . ' ' . $skyuc->options ['time_format'], $row ['lastactivity'], true, false );
		$row ['lastvisit'] = skyuc_date ( $skyuc->options ['date_format'] . ' ' . $skyuc->options ['time_format'], $row ['lastvisit'], true, false );
		$row ['reg_time'] = skyuc_date ( $skyuc->options ['date_format'] . ' ' . $skyuc->options ['time_format'], $row ['reg_time'], true, false );
		$row ['user_money'] = price_format ( $row ['user_money'] );
		$row ['minute'] = iif ( $row ['minute'] > 60, sprintf ( $skyuc->lang ['format_hour'] . ' ' . $skyuc->lang ['format_minute'], floor ( $row ['minute'] / 60 ), $row ['minute'] % 60 ), sprintf ( $skyuc->lang ['format_minute'], $row ['minute'] ) );
	} else {
		$row = array ();
	}

	$smarty->assign ( 'ur_here', $_LANG ['users_edit'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['02_user_list'], 'href' => 'users.php?act=list' ) );
	$smarty->assign ( 'user', $row );
	$smarty->assign ( 'ranks', $skyuc->usergroup );
	$smarty->assign ( 'form_action', 'update' );

	assign_query_info ();

	$smarty->display ( 'user_info.tpl' );
}

/*------------------------------------------------------ */
//-- 更新用户帐号
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'update') {
	// 检查权限
	admin_priv ( 'users_manage' );

	$skyuc->input->clean_array_gpc ( 'p', array ('old_username' => TYPE_STR, 'username' => TYPE_STR, 'password' => TYPE_STR, 'email' => TYPE_STR, 'gender' => TYPE_UINT, 'birthdayYear' => TYPE_UINT, 'birthdayMonth' => TYPE_UINT, 'birthdayDay' => TYPE_UINT, 'usertype' => TYPE_BOOL, 'user_rank' => TYPE_UINT, 'pay_point' => TYPE_UINT, 'user_point' => TYPE_UINT, 'unit_dateYear' => TYPE_UINT, 'unit_dateMonth' => TYPE_UINT, 'unit_dateDay' => TYPE_UINT, 'other' => TYPE_ARRAY_STR ) );

	$old_username = $skyuc->GPC ['old_username'];
	$username = $skyuc->GPC ['username'];
	$password = $skyuc->GPC ['password'];
	$email = $skyuc->GPC ['email'];
	$gender = $skyuc->GPC ['gender'];
	$gender = in_array ( $gender, array (0, 1, 2 ) ) ? $gender : 0;
	$birthday = skyuc_mktime ( 0, 0, 0, $skyuc->GPC ['birthdayMonth'], $skyuc->GPC ['birthdayDay'], $skyuc->GPC ['birthdayYear'] );

	$users = & init_users ();
	if (! $users->edit_user ( array ('username' => $username, 'password' => $password, 'email' => $email, 'gender' => $gender, 'bday' => $birthday ), 1 )) {
		if ($users->error == ERR_EMAIL_EXISTS) {
			$msg = $_LANG ['email_exists'];
		} else {
			$msg = $_LANG ['edit_user_failed'];
		}
		sys_msg ( $msg, 1 );
	}

	// 更新会员的其它信息
	$unit_date = skyuc_mktime ( 0, 0, 0, $skyuc->GPC ['unit_dateMonth'], $skyuc->GPC ['unit_dateDay'], $skyuc->GPC ['unit_dateYear'] );

	$sql = 'UPDATE ' . TABLE_PREFIX . 'users' . ' SET ' . 'usertype = ' . $skyuc->GPC ['usertype'] . ',' . 'user_rank	=	' . $skyuc->GPC ['user_rank'] . ',' . 'gender = ' . $gender . ',' . 'birthday = ' . $birthday . ',' . 'unit_date = ' . $unit_date . ',' . 'pay_point = ' . $skyuc->GPC ['pay_point'] . ', ' . 'user_point = ' . $skyuc->GPC ['user_point'] . ', ' . "qq = '" . $db->escape_string ( $skyuc->GPC ['other'] ['qq'] ) . "'," . "msn = '" . $db->escape_string ( $skyuc->GPC ['other'] ['msn'] ) . "'," . "phone = '" . $db->escape_string ( $skyuc->GPC ['other'] ['phone'] ) . "' " . " WHERE user_name ='" . $db->escape_string ( $old_username ) . "'";
	$skyuc->db->query_write ( $sql );

	// 记录管理员操作
	admin_log ( $username, 'edit', 'users' );

	// 提示信息
	$links [0] ['text'] = $_LANG ['goto_list'];
	$links [0] ['href'] = 'users.php?act=list';
	$links [1] ['text'] = $_LANG ['go_back'];
	$links [1] ['href'] = 'javascript:history.back()';

	sys_msg ( $_LANG ['update_success'], 0, $links );

}

/*------------------------------------------------------ */
//-- 批量删除会员帐号
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'batch_remove') {
	// 检查权限
	admin_priv ( 'users_drop' );

	$skyuc->input->clean_gpc ( 'p', 'checkboxes', TYPE_ARRAY_UINT );

	if ($skyuc->GPC_exists ['checkboxes']) {
		$sql = 'SELECT user_name FROM ' . TABLE_PREFIX . 'users' . ' WHERE user_id ' . db_create_in ( $skyuc->GPC ['checkboxes'] );
		$col = array ();
		$res = $db->query_read ( $sql );
		while ( $row = $db->fetch_row ( $res ) ) {
			$col [] = $row [0];
		}
		$count = count ( $col );

		//通过插件来删除用户
		$users = & init_users ();
		$users->remove_user ( $col );

		admin_log ( '', 'batch_remove', 'users' );

		$lnk [] = array ('text' => $_LANG ['go_back'], 'href' => 'users.php?act=list' );
		sys_msg ( sprintf ( $_LANG ['batch_remove_success'], $count ), 0, $lnk );
	} else {
		$lnk [] = array ('text' => $_LANG ['go_back'], 'href' => 'users.php?act=list' );
		sys_msg ( $_LANG ['no_select_user'], 0, $lnk );
	}
}

/*------------------------------------------------------ */
//-- 编辑email
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_email') {
	// 检查权限
	check_authz_json ( 'users_manage' );

	$skyuc->input->clean_array_gpc ( 'r', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$id = $skyuc->GPC ['id'];
	$email = $skyuc->GPC ['val'];

	$users = & init_users ();

	$sql = 'SELECT user_name FROM ' . TABLE_PREFIX . 'users' . ' WHERE user_id = ' . $id;
	$user = $db->query_first ( $sql );

	if (validate ( $email, 4 )) {
		if ($users->edit_user ( array ('username' => $db->escape_string ( $user ['user_name'] ), 'email' => $db->escape_string ( $email ) ) )) {
			admin_log ( $user ['user_name'], 'edit', 'users' );

			make_json_result ( $email );
		} else {
			$msg = iif ( ($users->error == ERR_EMAIL_EXISTS), $_LANG ['email_exists'], $_LANG ['edit_user_failed'] );
			make_json_error ( $msg );
		}
	} else {
		make_json_error ( $_LANG ['invalid_email'] );
	}
}

/*------------------------------------------------------ */
//-- 删除会员帐号
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'remove') {
	// 检查权限
	admin_priv ( 'users_drop' );

	$skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	$sql = 'SELECT user_name FROM ' . TABLE_PREFIX . 'users' . " WHERE user_id = '" . $skyuc->GPC ['id'] . "'";
	$user = $db->query_first ( $sql );
	// 通过插件来删除用户
	$users = & init_users ();
	$users->remove_user ( $user ['user_name'] ); //已经删除用户所有数据


	// 记录管理员操作
	admin_log ( $user ['user_name'], 'remove', 'users' );

	// 提示信息
	$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'users.php?act=list' );
	sys_msg ( sprintf ( $_LANG ['remove_success'], $user ['username'] ), 0, $link );
}

?>
