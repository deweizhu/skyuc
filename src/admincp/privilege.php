<?php
/**
 * SKYUC! 管理员信息以及权限管理程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

/* act操作项的初始化 */
if (empty ( $skyuc->GPC ['act'] )) {
	$skyuc->GPC ['act'] = 'login';
}

/* 初始化 $exc 对象 */
$exc = new exchange ( TABLE_PREFIX . 'admin', $skyuc->db, 'user_id', 'user_name' );

/*------------------------------------------------------ */
//-- 退出登录
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'logout') {
	// 清除cookie
	skyuc_setcookie ( 'cpsession', '', false, true, true );
	skyuc_setcookie ( 'adminid', '', false, true, true );
	skyuc_setcookie ( 'admin_pass', '', false, true, true );
	$db->query_write ( "DELETE FROM " . TABLE_PREFIX . "cpsession WHERE adminid = " . $skyuc->session->vars ['adminid'] . " AND hash = '" . $db->escape_string ( $skyuc->GPC [COOKIE_PREFIX . 'cpsession'] ) . "'" );

	$skyuc->session->destroy_session ();
	if (! empty ( $skyuc->session->vars ['sessionurl_js'] )) {
		exec_header_redirect ( 'privilege.php?act=login' . $skyuc->session->vars ['sessionurl_js'] );
	} else {
		exec_header_redirect ( 'privilege.php?act=login' );
	}

	// $skyuc->GPC['act'] = 'login';
}

/*------------------------------------------------------ */
//-- 登陆界面
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'login') {

	if ((intval ( $skyuc->options ['humanverify'] ) & HV_ADMIN)) {
		require_once (DIR . '/includes/class_humanverify.php');
		$verification = & HumanVerify::fetch_library ( $skyuc );
		$human_verify = $verification->generate_token ();

		$smarty->assign ( 'humanverify', $human_verify );
	}

	$smarty->assign ( 'sessionhash', $skyuc->session->vars ['dbsessionhash'] );
	$smarty->assign ( 'scriptpath', $skyuc->scriptpath );
	$smarty->assign ( 'securitytoken', $skyuc->userinfo ['securitytoken'] );

	$smarty->display ( 'login.tpl' );
}

/*------------------------------------------------------ */
//-- 登陆验证
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'signin') {
	$skyuc->input->clean_array_gpc ( 'p', array ('username' => TYPE_STR, 'password' => TYPE_STR, 'humanverify' => TYPE_ARRAY_STR ) );

	if (! empty ( $skyuc->GPC ['humanverify'] )) {
		// 检查验证码是否正确
		require_once (DIR . '/includes/class_humanverify.php');
		$verify = & HumanVerify::fetch_library ( $skyuc );
		if (! $verify->verify_token ( $skyuc->GPC ['humanverify'] )) {
			sys_msg ( $_LANG ['captcha_error'], 1 );
		}
	}

	//检查密码是否正确
	$sql = 'SELECT user_id, user_name, password, action_list, last_time
 				FROM ' . TABLE_PREFIX . 'admin' . "
 			  WHERE user_name = '" . $db->escape_string ( $skyuc->GPC ['username'] ) . "' AND password = '" . md5 ( $skyuc->GPC ['password'] ) . "'";

	$row = $skyuc->db->query_first_slave ( $sql );
	if ($row) {
		// 登录成功
		define ( 'NOSHUTDOWNFUNC', 1 );
		set_admin_session ( $row ['user_id'], $row ['user_name'], $row ['action_list'], $row ['last_time'] );

		$cpsession = $skyuc->session->fetch_sessionhash ();
		//insert query
		$skyuc->db->query_write ( "INSERT INTO " . TABLE_PREFIX . "cpsession (adminid, hash, dateline) VALUES (" . $row ['user_id'] . ", '" . $skyuc->db->escape_string ( $cpsession ) . "', " . TIMENOW . ")" );
		skyuc_setcookie ( 'cpsession', $cpsession, false, true, true );

		// 更新最后登录时间和IP
		$skyuc->db->query ( 'UPDATE ' . TABLE_PREFIX . 'admin' . ' SET last_time=' . TIMENOW . ", last_ip='" . ALT_IP . "'" . ' WHERE user_id=' . $row ['user_id'] );
		// 保存COOKIE记录
		if (empty ( $skyuc->GPC [COOKIE_PREFIX . 'adminid'] )) {
			skyuc_setcookie ( 'adminid', $row ['user_id'], true, true, true );
			skyuc_setcookie ( 'admin_pass', md5 ( $row ['password'] . COOKIE_SALT ), false, true, true );
		}

		exec_header_redirect ( 'index.php' );
	} else {
		include_once (DIR . '/includes/functions_log_error.php');
		log_skyuc_error ( $skyuc->GPC ['username'], 'security' );

		sys_msg ( $_LANG ['login_faild'], 1 );
	}
}

/*------------------------------------------------------ */
//-- 管理员列表页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'list') {
	/* 模板赋值 */
	$smarty->assign ( 'ur_here', $_LANG ['03_admin_list'] );
	$smarty->assign ( 'action_link', array ('href' => 'privilege.php?act=add', 'text' => $_LANG ['02_admin_add'] ) );
	$smarty->assign ( 'full_page', 1 );
	$smarty->assign ( 'admin_list', get_admin_userlist () );

	/* 显示页面 */
	assign_query_info ();
	$smarty->display ( 'privilege_list.tpl' );
}

/*------------------------------------------------------ */
//-- 查询
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$smarty->assign ( 'admin_list', get_admin_userlist () );

	make_json_result ( $smarty->fetch ( 'privilege_list.tpl' ) );
}

/*------------------------------------------------------ */
//-- 添加管理员页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'add') {
	/* 检查权限 */
	admin_priv ( 'admin_manage' );

	/* 模板赋值 */
	$smarty->assign ( 'ur_here', $_LANG ['02_admin_add'] );
	$smarty->assign ( 'action_link', array ('href' => 'privilege.php?act=list', 'text' => $_LANG ['03_admin_list'] ) );
	$smarty->assign ( 'form_act', 'insert' );
	$smarty->assign ( 'action', 'add' );

	/* 显示页面 */
	assign_query_info ();
	$smarty->display ( 'privilege_info.tpl' );
}

/*------------------------------------------------------ */
//-- 添加管理员的处理
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'insert') {
	admin_priv ( 'admin_manage' );

	/* 判断管理员是否已经存在 */
	$skyuc->input->clean_array_gpc ( 'p', array ('user_name' => TYPE_STR, 'email' => TYPE_STR, 'password' => TYPE_STR ) );
	if ($skyuc->GPC_exists ['user_name']) {
		$is_only = $exc->is_only ( 'user_name', $skyuc->GPC ['user_name'] );

		if (! $is_only) {
			sys_msg ( sprintf ( $_LANG ['user_name_exist'], $skyuc->GPC ['user_name'] ), 1 );
		}
	}

	/* Email地址是否有重复 */
	if ($skyuc->GPC_exists ['email']) {
		$is_only = $exc->is_only ( 'email', $skyuc->GPC ['email'] );

		if (! $is_only) {
			sys_msg ( sprintf ( $_LANG ['email_exist'], $skyuc->GPC ['email'] ), 1 );
		}
	}

	/* 获取添加日期及密码 */
	$join_time = TIMENOW;
	$password = md5 ( $skyuc->GPC ['password'] );

	$sql = "INSERT INTO " . TABLE_PREFIX . 'admin' . " (user_name, email, password, join_time) " . "VALUES ('" . $skyuc->GPC ['user_name'] . "', '" . $skyuc->GPC ['email'] . "', '$password', '$join_time')";

	$skyuc->db->query_write ( $sql );
	/* 转入权限分配列表 */
	$new_id = $skyuc->db->insert_id ();

	/*添加链接*/
	$link [0] ['text'] = $_LANG ['go_allot_priv'];
	$link [0] ['href'] = 'privilege.php?act=allot&id=' . $new_id . '&user=' . $skyuc->GPC ['user_name'] . '';

	$link [1] ['text'] = $_LANG ['continue_add'];
	$link [1] ['href'] = 'privilege.php?act=add';

	sys_msg ( $_LANG ['add'] . "&nbsp;" . $skyuc->GPC ['user_name'] . "&nbsp;" . $_LANG ['action_succeed'], 0, $link );

	/* 记录管理员操作 */
	admin_log ( $skyuc->GPC ['user_name'], 'add', 'privilege' );
}

/*------------------------------------------------------ */
//-- 编辑管理员信息
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit') {
	/* 管理员demo不能编辑自己 */
	$admin_name = fetch_adminutil_text ( $skyuc->session->vars ['adminid'] . '_admin_name' );
	if ($admin_name == 'demo') {
		$link [] = array ('text' => $_LANG ['back_list'], 'href' => 'privilege.php?act=list' );
		sys_msg ( $_LANG ['edit_admininfo_cannot'], 0, $link );
	}

	$id = $skyuc->input->clean_gpc ( 'r', 'id', TYPE_UINT );

	/* 查看是否有权限编辑其他管理员的信息 */
	if ($skyuc->session->vars ['adminid'] !== $id) {
		admin_priv ( 'admin_manage' );
	}

	/* 获取管理员信息 */
	$sql = "SELECT user_id, user_name, email, password FROM " . TABLE_PREFIX . 'admin' . " WHERE user_id = '" . $id . "'";
	$user_info = $skyuc->db->query_first_slave ( $sql );

	/* 模板赋值 */
	$smarty->assign ( 'ur_here', $_LANG ['admin_edit'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['03_admin_list'], 'href' => 'privilege.php?act=list' ) );
	$smarty->assign ( 'user', $user_info );

	$smarty->assign ( 'form_act', 'update' );
	$smarty->assign ( 'action', 'edit' );

	assign_query_info ();
	$smarty->display ( 'privilege_info.tpl' );
}

/*------------------------------------------------------ */
//-- 更新管理员信息
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'update') {

	admin_priv ( 'admin_manage' );

	/* 变量初始化 */
	$admin_id = $skyuc->input->clean_gpc ( 'p', 'id', TYPE_UINT );
	$admin_name = $skyuc->input->clean_gpc ( 'p', 'user_name', TYPE_STR );
	$admin_email = $skyuc->input->clean_gpc ( 'p', 'email', TYPE_STR );

	$skyuc->input->clean_array_gpc ( 'p', array ('nav_list' => TYPE_ARRAY_STR, 'new_password' => TYPE_STR, 'pwd_confirm' => TYPE_STR, 'old_password' => TYPE_STR ) );

	$nav_list = iif ( ! empty ( $skyuc->GPC ['nav_list'] ), ", nav_list = '" . @join ( ",", $skyuc->GPC ['nav_list'] ) . "'", '' );
	$password = iif ( ! empty ( $skyuc->GPC ['new_password'] ), ", password = '" . md5 ( $skyuc->GPC ['new_password'] ) . "'", '' );

	/* 判断管理员是否已经存在 */
	if (! empty ( $admin_name )) {
		$is_only = $exc->num ( 'user_name', $admin_name, $admin_id );
		if ($is_only == 1) {
			sys_msg ( sprintf ( $_LANG ['user_name_exist'], $admin_name ), 1 );
		}
	}

	/* Email地址是否有重复 */
	if (! empty ( $admin_email )) {
		$is_only = $exc->num ( 'email', $admin_email, $admin_id );

		if ($is_only == 1) {
			sys_msg ( sprintf ( $_LANG ['email_exist'], $admin_email ), 1 );
		}
	}

	//如果要修改密码
	$pwd_modified = false;

	if (! empty ( $skyuc->GPC ['new_password'] )) {
		/* 查询旧密码并与输入的旧密码比较是否相同 */
		$sql = 'SELECT password FROM ' . TABLE_PREFIX . 'admin' . " WHERE user_id = '$admin_id'";
		$row = $skyuc->db->query_first_slave ( $sql );
		$old_password = $row ['password'];
		if ($old_password != (md5 ( $skyuc->GPC ['old_password'] ))) {
			$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
			sys_msg ( $_LANG ['pwd_error'], 0, $link );
		}

		/* 比较新密码和确认密码是否相同 */
		if ($skyuc->GPC ['new_password'] != $skyuc->GPC ['pwd_confirm']) {
			$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
			sys_msg ( $_LANG ['js_languages'] ['password_error'], 0, $link );
		} else {
			$pwd_modified = true;
		}
	}

	//更新管理员信息
	$sql = "UPDATE " . TABLE_PREFIX . 'admin' . " SET user_name = '$admin_name', email = '$admin_email' 	$password 	$nav_list " . ' WHERE user_id =' . $admin_id;

	$skyuc->db->query_write ( $sql );
	/* 记录管理员操作 */
	admin_log ( $admin_name, 'edit', 'privilege' );

	/* 如果修改了密码，则需要将session中该管理员的数据清空 */
	if ($pwd_modified) {
		$skyuc->db->query_write ( "DELETE FROM " . TABLE_PREFIX . "session WHERE adminid = '" . $skyuc->session->vars ['adminid'] . "'" );

		$msg = $_LANG ['edit_password_succeed'];
	} else {
		$msg = $_LANG ['edit_profile_succeed'];
	}

	/* 提示信息 */
	$link [] = array ('text' => $_LANG ['back_admin_list'], 'href' => 'privilege.php?act=list' );
	sys_msg ( $_LANG ['edit'] . "$msg<script>parent.document.getElementById('header-frame').contentWindow.document.location.reload();</script>", 0, $link );

}

/*------------------------------------------------------ */
//-- 编辑个人资料
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'modif') {
	/* 管理员demo不能编辑自己  */
	$admin_name = fetch_adminutil_text ( $skyuc->session->vars ['adminid'] . '_admin_name' );
	if ($admin_name == 'demo') {
		$link [] = array ('text' => $_LANG ['back_admin_list'], 'href' => 'privilege.php?act=list' );
		sys_msg ( $_LANG ['edit_admininfo_cannot'], 0, $link );
	}

	include_once (ADM . '/inc_menu.php');
	foreach ( $modules as $key => $value ) {
		ksort ( $modules [$key] );
	}
	ksort ( $modules );

	foreach ( $modules as $key => $val ) {
		$menus [$key] ['label'] = $_LANG [$key];
		if (is_array ( $val )) {
			foreach ( $val as $k => $v ) {
				$menus [$key] ['children'] [$k] ['label'] = $_LANG [$k];
				$menus [$key] ['children'] [$k] ['action'] = $v;
			}
		} else {
			$menus [$key] ['action'] = $val;
		}
	}

	/* 获得当前管理员数据信息 */
	$sql = 'SELECT user_id, user_name, email, nav_list ' . 'FROM ' . TABLE_PREFIX . 'admin' . ' WHERE user_id = ' . $skyuc->session->vars ['adminid'];
	$user_info = $skyuc->db->query_first_slave ( $sql );

	/* 获取导航条 */
	$nav_arr = (trim ( $user_info ['nav_list'] ) == '') ? array () : explode ( ',', $user_info ['nav_list'] );
	$nav_lst = array ();
	foreach ( $nav_arr as $val ) {
		$arr = explode ( '|', $val );
		$nav_lst [$arr [1]] = $arr [0];
	}

	/* 模板赋值 */
	$smarty->assign ( 'lang', $_LANG );
	$smarty->assign ( 'ur_here', $_LANG ['modif_info'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['03_admin_list'], 'href' => 'privilege.php?act=list' ) );
	$smarty->assign ( 'user', $user_info );
	$smarty->assign ( 'menus', $modules );
	$smarty->assign ( 'nav_arr', $nav_lst );

	$smarty->assign ( 'form_act', 'update' );
	$smarty->assign ( 'action', 'modif' );

	/* 显示页面 */
	assign_query_info ();
	$smarty->display ( 'privilege_info.tpl' );
}

/*------------------------------------------------------ */
//-- 为管理员分配权限
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'allot') {
	require (DIR . '/languages/' . $skyuc->options ['lang'] . '/admincp/priv_action.php');

	admin_priv ( 'allot_priv' );

	$id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	// 获得该管理员的权限
	$priv_str = $skyuc->db->query_first ( 'SELECT action_list FROM ' . TABLE_PREFIX . 'admin' . ' WHERE user_id =' . $id . '' );

	// 如果被编辑的管理员拥有了all这个权限，将不能编辑
	if ($priv_str ['action_list'] == 'all') {
		$link [] = array ('text' => $_LANG ['back_admin_list'], 'href' => 'privilege.php?act=list' );
		sys_msg ( $_LANG ['edit_admininfo_cannot'], 0, $link );
	}

	//获取权限的分组数据
	$sql = 'SELECT action_id, parent_id, action_code FROM ' . TABLE_PREFIX . 'admin_action' . ' WHERE parent_id = 0';
	$res = $skyuc->db->query_read ( $sql );
	while ( $rows = $skyuc->db->fetch_array ( $res ) ) {
		$priv_arr [$rows ['action_id']] = $rows;
	}

	// 按权限组查询底级的权限名称
	$sql = 'SELECT action_id, parent_id, action_code FROM ' . TABLE_PREFIX . 'admin_action' . ' WHERE parent_id ' . db_create_in ( array_keys ( $priv_arr ) );
	$result = $skyuc->db->query_read ( $sql );
	while ( $priv = $skyuc->db->fetch_array ( $result ) ) {
		$priv_arr [$priv ["parent_id"]] ["priv"] [$priv ["action_code"]] = $priv;
	}

	// 将同一组的权限使用 "," 连接起来，供JS全选
	foreach ( $priv_arr as $action_id => $action_group ) {
		$priv_arr [$action_id] ['priv_list'] = join ( ',', @array_keys ( $action_group ['priv'] ) );

		foreach ( $action_group ['priv'] as $key => $val ) {
			$priv_arr [$action_id] ['priv'] [$key] ['cando'] = (strpos ( $priv_str ['action_list'], $val ['action_code'] ) !== false || $priv_str ['action_list'] == 'all') ? 1 : 0;
		}
	}

	// 赋值
	$smarty->assign ( 'lang', $_LANG );
	$smarty->assign ( 'ur_here', $_LANG ['allot_priv'] . ' [ ' . $skyuc->input->clean_gpc ( 'g', 'user', TYPE_STR ) . ' ] ' );
	$smarty->assign ( 'action_link', array ('href' => 'privilege.php?act=list', 'text' => $_LANG ['03_admin_list'] ) );
	$smarty->assign ( 'priv_arr', $priv_arr );
	$smarty->assign ( 'form_act', 'update_allot' );
	$smarty->assign ( 'user_id', $id );

	// 显示页面
	assign_query_info ();
	$smarty->display ( 'privilege_allot.tpl' );
}

/*------------------------------------------------------ */
//-- 更新管理员的权限
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'update_allot') {
	admin_priv ( 'admin_manage' );

	$id = $skyuc->input->clean_gpc ( 'p', 'id', TYPE_UINT );

	// 取得当前管理员用户名
	$admin = $skyuc->db->query_first ( 'SELECT user_name FROM ' . TABLE_PREFIX . 'admin' . " WHERE user_id = $id" );
	$admin_name = $admin ['user_name'];
	// 更新管理员的权限
	$act_list = @join ( ',', $skyuc->input->clean_gpc ( 'p', 'action_code', TYPE_ARRAY_STR ) );
	$sql = "UPDATE " . TABLE_PREFIX . 'admin' . " SET action_list = '$act_list' " . "WHERE user_id = $id";

	$skyuc->db->query_write ( $sql );
	// 动态更新管理员的SESSION
	if ($skyuc->session->vars ['adminid'] === $id) {
		build_adminutil_text ( $id . '_action_list', $act_list );
	}

	// 记录管理员操作
	admin_log ( $admin_name, 'edit', 'privilege' );

	// 提示信息
	$link [] = array ('text' => $_LANG ['back_admin_list'], 'href' => 'privilege.php?act=list' );
	sys_msg ( $_LANG ['edit'] . "&nbsp;" . $admin_name . "&nbsp;" . $_LANG ['action_succeed'], 0, $link );

}

/*------------------------------------------------------ */
//-- 删除一个管理员
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'remove') {
	check_authz_json ( 'admin_manage' );

	$id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	/* 获得管理员用户名 */
	$admin_name = $skyuc->db->query_first ( 'SELECT user_name FROM ' . TABLE_PREFIX . 'admin' . " WHERE user_id=$id" );

	/* demo这个管理员不允许删除 */
	if ($admin_name == 'demo') {
		make_json_error ( $_LANG ['edit_remove_cannot'] );
	}

	/* ID为1的不允许删除 */
	if ($id == 1) {
		make_json_error ( $_LANG ['remove_cannot'] );
	}

	if ($exc->drop ( $id )) {
		// 删除session中该管理员的记录
		$skyuc->db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . "session WHERE adminid =$id" );

		admin_log ( $admin_name, 'remove', 'privilege' );
	}

	$url = 'privilege.php?act=query&' . str_replace ( 'act=remove', '', $_SERVER ['QUERY_STRING'] );

	exec_header_redirect ( $url );
}

/* 获取管理员列表 */
function get_admin_userlist() {
	global $skyuc;

	$list = array ();
	$sql = 'SELECT user_id, user_name, email, join_time, last_time ' . 'FROM ' . TABLE_PREFIX . 'admin' . ' ORDER BY user_id DESC';

	$list = array ();
	$queryresult = $skyuc->db->query_read_slave ( $sql );
	while ( $row = $skyuc->db->fetch_array ( $queryresult ) ) {
		$row ['join_time'] = skyuc_date ( $skyuc->options ['date_format'] . ' ' . $skyuc->options ['time_format'], $row ['join_time'], false, false );
		$row ['last_time'] = skyuc_date ( $skyuc->options ['date_format'] . ' ' . $skyuc->options ['time_format'], $row ['last_time'], false, false );
		$list [] = $row;
	}
	$skyuc->db->free_result ( $queryresult );
	return $list;
}

?>