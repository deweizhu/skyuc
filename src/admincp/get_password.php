<?php
/**
 * SKYUC! 找回管理员密码
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

/* 操作项的初始化 */
if (empty ( $_SERVER ['REQUEST_METHOD'] )) {
	$_SERVER ['REQUEST_METHOD'] = 'GET';
} else {
	$_SERVER ['REQUEST_METHOD'] = trim ( $_SERVER ['REQUEST_METHOD'] );
}

/*------------------------------------------------------ */
//-- 填写管理员帐号和email页面
/*------------------------------------------------------ */
if ($_SERVER ['REQUEST_METHOD'] == 'GET') {
	$skyuc->input->clean_array_gpc ( 'g', array ('act' => TYPE_STR, 'code' => TYPE_STR, 'uid' => TYPE_UINT ) );
	//验证从邮件地址过来的链接
	if (! empty ( $skyuc->GPC ['act'] ) && $skyuc->GPC ['act'] == 'reset_pwd') {
		$code = $skyuc->GPC ['code'];
		$adminid = $skyuc->GPC ['uid'];

		if ($adminid == 0 || empty ( $code )) {
			header ( "Location: privilege.php?act=login\n" );
			exit ();
		}

		/* 以用户的原密码，与code的值匹配 */
		$sql = 'SELECT password FROM ' . TABLE_PREFIX . 'admin' . " WHERE user_id = '$adminid'";
		$row = $db->query_first_slave ( $sql );
		$password = $row ['password'];

		if (md5 ( $adminid . $password ) != $code) {
			//此链接不合法
			$link [0] ['text'] = $_LANG ['back'];
			$link [0] ['href'] = 'privilege.php?act=login';

			sys_msg ( $_LANG ['code_param_error'], 0, $link );
		} else {
			$smarty->assign ( 'adminid', $adminid );
			$smarty->assign ( 'code', $code );
			$smarty->assign ( 'form_act', 'reset_pwd' );
		}
	} elseif (! empty ( $skyuc->GPC ['act'] ) && $skyuc->GPC ['act'] == 'forget_pwd') {
		$smarty->assign ( 'form_act', 'forget_pwd' );
	}

	$smarty->assign ( 'ur_here', $_LANG ['get_newpassword'] );

	assign_query_info ();
	$smarty->display ( 'get_pwd.tpl' );
}

/*------------------------------------------------------ */
//-- 验证管理员帐号和email, 发送邮件
/*------------------------------------------------------ */
else {
	$skyuc->input->clean_array_gpc ( 'p', array ('action' => TYPE_STR, 'user_name' => TYPE_STR, 'email' => TYPE_STR ) );

	// 发送找回密码确认邮件
	if (! empty ( $skyuc->GPC ['action'] ) && $skyuc->GPC ['action'] == 'get_pwd') {
		$adminname = $skyuc->GPC ['user_name'];
		$admin_email = $skyuc->GPC ['email'];

		if (empty ( $adminname ) || empty ( $admin_email )) {
			header ( "Location: privilege.php?act=login\n" );
			exit ();
		}

		// 管理员用户名和邮件地址是否匹配，并取得原密码
		$sql = 'SELECT user_id, password FROM ' . TABLE_PREFIX . 'admin' . " WHERE user_name = '" . $db->escape_string ( $adminname ) . "' AND email = '" . $db->escape_string ( $admin_email ) . "'";
		$admin_info = $db->query_first_slave ( $sql );

		if (! empty ( $admin_info )) {
			// 生成验证的code
			$admin_id = $admin_info ['user_id'];
			$code = md5 ( $admin_id . $admin_info ['password'] );

			// 设置重置邮件模板所需要的内容信息
			$template = get_mail_template ( 'send_password' );
			$reset_email = get_url () . $skyuc->config ['Misc'] ['admincpdir'] . '/get_password.php?act=reset_pwd&uid=' . $admin_id . '&code=' . $code;

			$smarty->assign ( 'user_name', $adminname );
			$smarty->assign ( 'reset_email', $reset_email );
			$smarty->assign ( 'site_name', $skyuc->options ['site_name'] );
			$smarty->assign ( 'send_date', skyuc_date ( $skyuc->options ['date_format'] ), TIMENOW, true, flase );
			$smarty->assign ( 'sent_date', skyuc_date ( $skyuc->options ['date_format'] ), TIMENOW, true, flase );

			$content = $smarty->fetch ( 'str:' . $template ['template_content'] );

			// 发送确认重置密码的确认邮件
			if (false !== skyuc_mail ( $admin_email, $template ['template_subject'], $content, true, $template ['is_html'] )) {
				//提示信息
				$link [0] ['text'] = $_LANG ['back'];
				$link [0] ['href'] = 'privilege.php?act=login';

				sys_msg ( $_LANG ['send_success'] . $admin_email, 0, $link );
			} else {
				sys_msg ( $_LANG ['send_mail_error'], 1 );
			}
		} else {
			// 提示信息
			sys_msg ( $_LANG ['email_username_error'], 1 );
		}
	} // 验证新密码，更新管理员密码
	elseif (! empty ( $skyuc->GPC ['action'] ) && $skyuc->GPC ['action'] == 'reset_pwd') {
		$skyuc->input->clean_array_gpc ( 'p', array ('password' => TYPE_STR, 'adminid' => TYPE_UINT, 'code' => TYPE_STR ) );

		$new_password = $skyuc->GPC ['password'];
		$adminid = $skyuc->GPC ['adminid'];
		$code = $skyuc->GPC ['code'];

		if (empty ( $new_password ) || empty ( $code ) || $adminid == 0) {
			header ( "Location: privilege.php?act=login\n" );
			exit ();
		}

		// 以用户的原密码，与code的值匹配
		$sql = 'SELECT password FROM ' . TABLE_PREFIX . 'admin' . " WHERE user_id = '$adminid'";
		$row = $db->query_first_slave ( $sql );
		$password = $row ['password'];

		if (md5 ( $adminid . $password ) != $code) {
			//此链接不合法
			$link [0] ['text'] = $_LANG ['back'];
			$link [0] ['href'] = 'privilege.php?act=login';

			sys_msg ( $_LANG ['code_param_error'], 0, $link );
		}

		//更新管理员的密码
		$sql = 'UPDATE ' . TABLE_PREFIX . 'admin' . " SET password = '" . md5 ( $new_password ) . "' " . "WHERE user_id = '$adminid'";
		$result = $db->query_write ( $sql );
		if ($result) {
			$link [0] ['text'] = $_LANG ['login_now'];
			$link [0] ['href'] = 'privilege.php?act=login';

			sys_msg ( $_LANG ['update_pwd_success'], 0, $link );
		} else {
			sys_msg ( $_LANG ['update_pwd_failed'], 1 );
		}
	}
}

?>