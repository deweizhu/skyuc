<?php
/**
 * SKYUC第三方程序会员数据整合插件管理程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');
/*------------------------------------------------------ */
//-- 会员数据整合插件列表
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list') {
	$modules = read_modules ( DIR . '/includes/modules/integrates' );
	for($i = 0; $i < count ( $modules ); $i ++) {
		$modules [$i] ['installed'] = ($modules [$i] ['code'] == $skyuc->options ['integrate_code']) ? 1 : 0;
	}

	$allow_set_points = $skyuc->options ['integrate_code'] == 'skyuc' ? 0 : 1;

	$smarty->assign ( 'allow_set_points', $allow_set_points );
	$smarty->assign ( 'ur_here', $_LANG ['07_user_integrate'] );
	$smarty->assign ( 'modules', $modules );

	assign_query_info ();
	$smarty->display ( 'integrates_list.tpl' );
}

/*------------------------------------------------------ */
//-- 安装会员数据整合插件
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'install') {
	admin_priv ( 'integrate_users', '' );

	$skyuc->input->clean_gpc ( 'g', 'code', TYPE_STR );

	// 增加ucenter设置时先检测uc_client与uc_client/data是否可写
	if ($skyuc->GPC ['code'] == 'ucenter') {
		$uc_client_dir = file_mode_info ( DIR . '/uc_client/data' );
		if ($uc_client_dir === false) {
			sys_msg ( $_LANG ['uc_client_not_exists'], 0 );
		}
		if ($uc_client_dir < 7) {
			sys_msg ( $_LANG ['uc_client_not_write'], 0 );
		}
	}
	if ($skyuc->GPC ['code'] == 'skyuc') {
		$sql = 'UPDATE ' . TABLE_PREFIX . 'setting' . " SET value = 'skyuc' WHERE code = 'integrate_code'";
		$db->query_write ( $sql );

		$sql = 'UPDATE ' . TABLE_PREFIX . 'setting' . " SET value = '' WHERE code = 'points_rule'";
		$db->query_write ( $sql );

		build_options ();

		$links [0] ['text'] = $_LANG ['go_back'];
		$links [0] ['href'] = 'integrate.php?act=list';
		sys_msg ( $_LANG ['update_success'], 0, $links );
	} else {
		$sql = 'UPDATE ' . TABLE_PREFIX . 'users' . " SET flag = 0, alias=''" . ' WHERE flag > 0';
		$db->query_read ( $sql ); //如果有标记，清空标记
		$set_modules = true;
		include_once (DIR . '/includes/modules/integrates/' . $skyuc->GPC ['code'] . '.php');
		$set_modules = false;

		//        if ($_GET['code'] == 'ucenter' && !empty($skyuc->options['integrate_config']))
		//        {
		//            $cfg = unserialize($skyuc->options['integrate_config']);
		//        }
		//        else
		//        {
		$cfg = $modules [0] ['default'];
		$cfg ['integrate_url'] = "http://";
		//        }


		assign_query_info ();

		$smarty->assign ( 'cfg', $cfg );
		$smarty->assign ( 'save', 0 );
		$smarty->assign ( 'set_list', get_charset_list () );
		$smarty->assign ( 'ur_here', $_LANG ['integrate_setup'] );
		$smarty->assign ( 'code', $skyuc->GPC ['code'] );
		$smarty->display ( 'integrates_setup.tpl' );
	}
}

if ($skyuc->GPC ['act'] == 'view_install_log') {

	$skyuc->input->clean_gpc ( 'g', 'code', TYPE_STR );
	if (empty ( $skyuc->GPC ['code'] ) || file_exists ( DIR . '/data/integrate_' . $skyuc->GPC ['code'] . '_log.php' )) {
		sys_msg ( $_LANG ['lost_intall_log'], 1 );
	}

	include (DIR . '/data/integrate_' . $skyuc->GPC ['code'] . '_log.php');
	if (isset ( $del_list ) || isset ( $rename_list ) || isset ( $ignore_list )) {
		if (isset ( $del_list )) {
			var_dump ( $del_list );
		}
		if (isset ( $rename_list )) {
			var_dump ( $rename_list );
		}
		if (isset ( $ignore_list )) {
			var_dump ( $ignore_list );
		}
	} else {
		sys_msg ( $_LANG ['empty_intall_log'], 1 );
	}
}

/*------------------------------------------------------ */
//-- 设置会员数据整合插件
/*------------------------------------------------------ */

if ($skyuc->GPC ['act'] == 'setup') {
	admin_priv ( 'integrate_users', '' );

	$skyuc->input->clean_gpc ( 'g', 'code', TYPE_STR );

	if ($skyuc->GPC ['code'] == 'skyuc') {
		sys_msg ( $_LANG ['need_not_setup'] );
	} else {
		$cfg = unserialize ( $skyuc->options ['integrate_config'] );
		assign_query_info ();

		$smarty->assign ( 'save', 1 );
		$smarty->assign ( 'set_list', get_charset_list () );
		$smarty->assign ( 'ur_here', $_LANG ['integrate_setup'] );
		$smarty->assign ( 'code', $skyuc->GPC ['code'] );
		$smarty->assign ( 'cfg', $cfg );
		$smarty->display ( 'integrates_setup.tpl' );
	}
}

/*------------------------------------------------------ */
//-- 检查用户填写资料
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'check_config') {
	$skyuc->input->clean_array_gpc ( 'p', array ('code' => TYPE_STR, 'save' => TYPE_BOOL, 'cfg' => TYPE_ARRAY ) );

	$code = $skyuc->GPC ['code'];

	include_once (DIR . '/includes/modules/integrates/' . $code . '.php');
	$skyuc->GPC ['cfg'] ['quiet'] = 1;

	$cls_user = new $code ( $skyuc->GPC ['cfg'] );

	if ($cls_user->error) {
		// 出错提示
		if ($cls_user->error == 1) {
			sys_msg ( $_LANG ['error_db_msg'] );
		} elseif ($cls_user->error == 2) {
			sys_msg ( $_LANG ['error_table_exist'] );
		} elseif ($cls_user->error == 1049) {
			sys_msg ( $_LANG ['error_db_exist'] );
		} else {
			sys_msg ( $cls_user->db->error () );
		}
	}

	if ($cls_user->db->version () >= '4.1') {
		// 检测数据表字符集
		$sql = 'SHOW TABLE STATUS FROM ' . $cls_user->db_name . " LIKE '" . $cls_user->prefix . $cls_user->user_table . "'";
		$row = $cls_user->db->query_first ( $sql );
		if (isset ( $row ['Collation'] )) {
			$db_charset = trim ( substr ( $row ['Collation'], 0, strpos ( $row ['Collation'], '_' ) ) );

			if ($db_charset == 'latin1') {
				if (empty ( $skyuc->GPC ['cfg'] ['is_latin1'] )) {
					sys_msg ( $_LANG ['error_is_latin1'], null, null, false );
				}
			} else {
				$user_db_charset = $skyuc->GPC ['cfg'] ['db_charset'] == 'GB2312' ? 'GBK' : $skyuc->GPC ['cfg'] ['db_charset'];
				if (! empty ( $skyuc->GPC ['cfg'] ['is_latin1'] )) {
					sys_msg ( $_LANG ['error_not_latin1'], null, null, false );
				}
				if ($user_db_charset != strtoupper ( $db_charset )) {
					sys_msg ( sprintf ( $_LANG ['invalid_db_charset'], strtoupper ( $db_charset ), $user_db_charset ), null, null, false );
				}
			}
		}
	}
	// 中文检测
	$test_str = '测试中文字符';
	if ($skyuc->GPC ['cfg'] ['db_charset'] != 'UTF8') {
		$test_str = skyuc_iconv ( 'UTF8', $skyuc->GPC ['cfg'] ['db_charset'] );
	}

	$sql = 'SELECT ' . $cls_user->field_name . ' FROM ' . $cls_user->table ( $cls_user->user_table ) . ' WHERE ' . $cls_user->field_name . " = '$test_str'";
	$test = $cls_user->db->query_read ( $sql );

	if (! $test) {
		sys_msg ( $_LANG ['error_latin1'], null, null, false );
	}

	if (! empty ( $skyuc->GPC ['save'] )) {
		// 直接保存修改
		if (save_integrate_config ( $code, $skyuc->GPC ['cfg'] )) {
			sys_msg ( $_LANG ['save_ok'], 0, array (array ('text' => $_LANG ['07_user_integrate'], 'href' => 'integrate.php?act=list' ) ) );
		} else {
			sys_msg ( $_LANG ['save_error'], 0, array (array ('text' => $_LANG ['07_user_integrate'], 'href' => 'integrate.php?act=list' ) ) );
		}
	}

	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'users';
	$total = $db->query_first ( $sql );

	if ($total ['total'] == 0) {
		// 网站没有用户时，直接保存完成整合
		save_integrate_config ( $skyuc->GPC ['code'], $skyuc->GPC ['cfg'] );
		header ( "Location: integrate.php?act=complete\n" );
		exit ();
	}

	// 检测成功临时保存论坛配置参数
	build_adminutil_text ( 'cfg', $skyuc->GPC ['cfg'] );
	build_adminutil_text ( 'code', $code );

	$size = 100;

	$smarty->assign ( 'ur_here', $_LANG ['conflict_username_check'] );
	$smarty->assign ( 'domain', '@skyuc' );
	$smarty->assign ( 'lang_total', sprintf ( $_LANG ['site_user_total'], $total ['total'] ) );
	$smarty->assign ( 'size', $size );
	$smarty->display ( 'integrates_check.tpl' );
}
/*------------------------------------------------------ */
//-- 保存UCenter填写的资料
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'save_uc_config') {
	$skyuc->input->clean_array_gpc ( 'p', array ('code' => TYPE_STR, 'cfg' => TYPE_ARRAY ) );

	$code = $skyuc->GPC ['code'];

	$cfg = unserialize ( $skyuc->options ['integrate_config'] );

	include_once (DIR . '/includes/modules/integrates/' . $code . '.php');

	$skyuc->GPC ['cfg'] ['quiet'] = 1;
	$cls_user = new $code ( $skyuc->GPC ['cfg'] );

	if ($cls_user->error) {
		// 出错提示
		if ($cls_user->error == 1) {
			sys_msg ( $_LANG ['error_db_msg'] );
		} elseif ($cls_user->error == 2) {
			sys_msg ( $_LANG ['error_table_exist'] );
		} elseif ($cls_user->error == 1049) {
			sys_msg ( $_LANG ['error_db_exist'] );
		} else {
			sys_msg ( $cls_user->db->error () );
		}
	}

	//合并数组，保存原值
	$cfg = array_merge ( $cfg, $skyuc->GPC ['cfg'] );

	// 直接保存修改
	if (save_integrate_config ( $code, $cfg )) {
		sys_msg ( $_LANG ['save_ok'], 0, array (array ('text' => $_LANG ['07_user_integrate'], 'href' => 'integrate.php?act=list' ) ) );
	} else {
		sys_msg ( $_LANG ['save_error'], 0, array (array ('text' => $_LANG ['07_user_integrate'], 'href' => 'integrate.php?act=list' ) ) );
	}
}

/*------------------------------------------------------ */
//-- 第一次保存UCenter安装的资料
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'save_uc_config_first') {
	$skyuc->input->clean_array_gpc ( 'p', array ('code' => TYPE_STR, 'uc_ip' => TYPE_STR, 'uc_url' => TYPE_STR, 'ucconfig' => TYPE_STR, 'save' => TYPE_BOOL ) );

	$code = $skyuc->GPC ['code'];

	list ( $appauthkey, $appid, $ucdbhost, $ucdbname, $ucdbuser, $ucdbpw, $ucdbcharset, $uctablepre, $uccharset, $ucapi, $ucip ) = explode ( '|', $skyuc->GPC ['ucconfig'] );
	$uc_ip = ! empty ( $ucip ) ? $ucip : $skyuc->GPC ['uc_ip'];
	$uc_url = ! empty ( $ucapi ) ? $ucapi : $skyuc->GPC ['uc_url'];
	$cfg = array ('uc_id' => $appid, 'uc_key' => $appauthkey, 'uc_url' => $uc_url, 'uc_ip' => $uc_ip, 'uc_connect' => 'mysql', 'uc_charset' => $uccharset, 'db_host' => $ucdbhost, 'db_user' => $ucdbuser, 'db_name' => $ucdbname, 'db_pass' => $ucdbpw, 'db_pre' => $uctablepre, 'db_charset' => $ucdbcharset );

	// 增加UC语言项
	$cfg ['uc_lang'] = $_LANG ['uc_lang'];

	// 检测成功临时保存论坛配置参数
	build_adminutil_text ( 'cfg', $cfg );
	build_adminutil_text ( 'code', $code );

	// 直接保存修改
	if (! empty ( $skyuc->GPC ['save'] )) {
		if (save_integrate_config ( $code, $cfg )) {
			sys_msg ( $_LANG ['save_ok'], 0, array (array ('text' => $_LANG ['07_user_integrate'], 'href' => 'integrate.php?act=list' ) ) );
		} else {
			sys_msg ( $_LANG ['save_error'], 0, array (array ('text' => $_LANG ['07_user_integrate'], 'href' => 'integrate.php?act=list' ) ) );
		}
	}

	$query = $db->query_read ( "SHOW TABLE STATUS LIKE '" . TABLE_PREFIX . 'users' . "'" );
	$data = $db->fetch_array ( $query );
	if ($data ["Auto_increment"]) {
		$maxuid = $data ["Auto_increment"] - 1;
	} else {
		$maxuid = 0;
	}

	// 保存完成整合
	save_integrate_config ( $code, $cfg );

	$smarty->assign ( 'ur_here', $_LANG ['ucenter_import_username'] );
	$smarty->assign ( 'user_startid_intro', sprintf ( $_LANG ['user_startid_intro'], $maxuid, $maxuid ) );
	$smarty->display ( 'integrates_uc_import.tpl' );
}
/*------------------------------------------------------ */
//-- 用户重名检查
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'check_user') {
	$skyuc->input->clean_array_gpc ( 'g', array ('start' => TYPE_UINT, 'size' => TYPE_UINT, 'method' => TYPE_UINT, 'domain' => TYPE_STR )

	 );

	$code = fetch_adminutil_text ( 'code' );
	include_once (DIR . '/includes/class_json.php');
	include_once (DIR . '/includes/modules/integrates/' . $code . '.php');
	$cfg = fetch_adminutil_text ( 'cfg' );
	$cls_user = new $code ( $cfg );
	$json = new JSON ();

	$start = $skyuc->GPC ['start'];
	$size = empty ( $skyuc->GPC ['size'] ) ? 100 : $skyuc->GPC ['size'];
	$method = empty ( $skyuc->GPC ['method'] ) ? 1 : $skyuc->GPC ['method'];
	$domain = empty ( $skyuc->GPC ['domain'] ) ? '@skyuc' : $skyuc->GPC ['domain'];
	if ($size < 2) {
		$size = 2;
	}

	build_adminutil_text ( 'domain', $domain );

	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'users';
	$total = $db->query_first ( $sql );

	$result = array ('error' => 0, 'message' => '', 'start' => 0, 'size' => $size, 'content' => '', 'method' => $method, 'domain' => $domain, 'is_end' => 0 );

	$sql = 'SELECT user_name FROM ' . TABLE_PREFIX . 'users';
	$sql = $skyuc->db->query_limit ( $sql, $size, $start );

	$user_list = array ();
	$res = $db->query_read ( $sql );
	while ( $row = $db->fetch_row ( $res ) ) {
		$user_list [] = $row;
	}

	$post_user_list = $cls_user->test_conflict ( $user_list );

	if ($post_user_list) {
		// 标记重名用户
		if ($method == 2) {
			$sql = 'UPDATE ' . TABLE_PREFIX . 'users' . " SET flag = '$method', alias = CONCAT(user_name, '" . $db->escape_string ( $domain ) . "') WHERE " . db_create_in ( $post_user_list, 'user_name' );
		} else {
			$sql = 'UPDATE ' . TABLE_PREFIX . 'users' . " SET flag = '$method' WHERE " . db_create_in ( $post_user_list, 'user_name' );
		}

		$db->query_write ( $sql );

		if ($method == 2) {
			// 需要改名,验证是否能成功改名
			$count = count ( $post_user_list );
			$test_user_list = array ();
			for($i = 0; $i < $count; $i ++) {
				$test_user_list [] = $post_user_list [$i] . $domain;
			}
			// 检查改名后用户是否和论坛用户有重名
			$error_user_list = $cls_user->test_conflict ( $test_user_list ); //检查
			if ($error_user_list) {
				$domain_len = 0 - strlen ( $domain );
				$count = count ( $error_user_list );
				for($i = 0; $i < $count; $i ++) {
					$error_user_list [$i] = substr ( $error_user_list [$i], 0, $domain_len );
				}
				// 将用户标记为改名失败
				$sql = 'UPDATE ' . TABLE_PREFIX . 'users' . " SET flag = '1' WHERE " . db_create_in ( $error_user_list, 'user_name' );
			}

			// 检查改名后用户是否与网站用户重名
			$sql = 'SELECT user_name FROM ' . TABLE_PREFIX . 'users' . ' WHERE ' . db_create_in ( $test_user_list, 'user_name' );
			$error_user_list = array ();
			$res = $db->query_read ( $sql );
			while ( $row = $db->fetch_row ( $res ) ) {
				$error_user_list [] = $row;
			}
			if ($error_user_list) {
				$domain_len = 0 - strlen ( $domain );
				$count = count ( $error_user_list );
				for($i = 0; $i < $count; $i ++) {
					$error_user_list [$i] = substr ( $error_user_list [$i], 0, $domain_len );

				}
				// 将用户标记为改名失败
				$sql = 'UPDATE ' . TABLE_PREFIX . 'users' . " SET flag = '1' WHERE " . db_create_in ( $error_user_list, 'user_name' );
			}
		}
	}

	if (($start + $size) < $total ['total']) {
		$result ['start'] = $start + $size;
		$result ['content'] = sprintf ( $_LANG ['notice'], $result ['start'], $total ['total'] );
	} else {
		$start = $total ['total'];
		$result ['content'] = $_LANG ['check_complete'];
		$result ['is_end'] = 1;

		// 查找有无重名用户,无重名用户则直接同步，有则查看重名用户
		$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'users' . ' WHERE flag > 0 ';
		$total = $db->query_first ( $sql );
		if ($total ['total'] > 0) {
			$result ['href'] = "integrate.php?act=modify";
		} else {
			$result ['href'] = "integrate.php?act=sync";
		}
	}
	die ( $json->encode ( $result ) );
}

if ($skyuc->GPC ['act'] == 'import_user') {
	$skyuc->input->clean_gpc ( 'r', 'merge', TYPE_UINT );

	$cfg = fetch_adminutil_text ( 'cfg' );
	include_once (DIR . '/includes/class_json.php');
	$json = new JSON ();

	// 建立 ucenter 数据库连接
	$ucdb = new Database ( $skyuc );
	$ucdb->connect ( $cfg ['db_name'], $cfg ['db_host'], '', $cfg ['db_user'], $cfg ['db_pass'], 0, '', '', '', '', '', '', $cfg ['db_charset'] );

	$result = array ('error' => 0, 'message' => '' );
	$query = $db->query_read ( "SHOW TABLE STATUS LIKE '" . TABLE_PREFIX . 'users' . "'" );
	$data = $db->fetch_array ( $query );
	if ($data ['Auto_increment']) {
		$maxuid = $data ['Auto_increment'] - 1;
	} else {
		$maxuid = 0;
	}
	$merge_method = $skyuc->GPC ['merge'];
	$merge_uid = array ();
	$uc_uid = array ();
	$repeat_user = array ();

	$query = $db->query_read ( 'SELECT * FROM ' . TABLE_PREFIX . 'users' . ' ORDER BY `user_id` ASC' );
	while ( $data = $db->fetch_array ( $query ) ) {
		$salt = rand ( 100000, 999999 );
		$password = md5 ( $data ['password'] . $salt );
		$data ['username'] = addslashes ( $data ['user_name'] );
		$lastuid = $data ['user_id'] + $maxuid;
		$uc_userinfo = $ucdb->query_first ( 'SELECT uid, password, salt FROM ' . $cfg ['db_pre'] . "members WHERE username='" . $data ['username'] . "'" );
		if (! $uc_userinfo) {
			$ucdb->query_write ( 'INSERT LOW_PRIORITY INTO ' . $cfg ['db_pre'] . "members SET uid='$lastuid', username='" . $data ['username'] . "', password='$password', email='" . $data ['email'] . "', regip='" . $data ['last_ip'] . "', regdate='" . $data ['reg_time'] . "', salt='$salt'" );
			$ucdb->query_write ( 'INSERT LOW_PRIORITY INTO ' . $cfg ['db_pre'] . "memberfields SET uid='$lastuid', blacklist=''" );
		} else {
			if ($merge_method == 1) {
				if (md5 ( $data ['password'] . $uc_userinfo ['salt'] ) == $uc_userinfo ['password']) {
					$merge_uid [] = $data ['user_id'];
					$uc_uid [] = array ('user_id' => $data ['user_id'], 'uid' => $uc_userinfo ['uid'] );
					continue;
				}
			}
			$ucdb->query_write ( 'REPLACE INTO ' . $cfg ['db_pre'] . "mergemembers SET appid='" . UC_APPID . "', username='" . $data ['username'] . "'" );
			$repeat_user [] = $data;
		}
	}
	$ucdb->query_write ( 'ALTER TABLE ' . $cfg ['db_pre'] . 'members AUTO_INCREMENT=' . ($lastuid + 1) );

	//需要更新user_id的表
	$up_user_table = array ('comment', 'feedback', 'order_info', 'tag', 'users', 'user_account' );
	// 清空的表
	$truncate_user_table = array ('session', 'cpsession' );

	if (! empty ( $merge_uid )) {
		$merge_uid = implode ( ',', $merge_uid );
	} else {
		$merge_uid = 0;
	}
	// 更新SKYUC表
	foreach ( $up_user_table as $table ) {
		$db->query_write ( 'UPDATE ' . TABLE_PREFIX . $table . " SET user_id=user_id+ $maxuid ORDER BY user_id DESC" );
		foreach ( $uc_uid as $uid ) {
			$db->query_write ( 'UPDATE ' . TABLE_PREFIX . $table . " SET user_id='" . $uid ['uid'] . "' WHERE `user_id`='" . ($uid ['user_id'] + $maxuid) . "'" );
		}
	}
	foreach ( $truncate_user_table as $table ) {
		$db->query_write ( 'TRUNCATE TABLE ' . TABLE_PREFIX . $table );
	}
	// 保存重复的用户信息
	if (! empty ( $repeat_user )) {
		@file_put_contents ( DIR . '/data/repeat_user.php', $json->encode ( $repeat_user ) );
	}
	$result ['error'] = 0;
	$result ['message'] = $_LANG ['import_user_success'];
	die ( $json->encode ( $result ) );
}
/*------------------------------------------------------ */
//-- 重名用户处理
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'modify') {
	// 检查是否有改名失败的用户
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'users' . ' WHERE flag = 1';
	$total = $db->query_first ( $sql );
	if ($total ['total'] > 0) {
		$_REQUEST ['flag'] = 1;
		$smarty->assign ( 'default_flag', 1 );
	} else {
		$_REQUEST ['flag'] = 0;
		$smarty->assign ( 'default_flag', 0 );
	}

	// 显示重名用户及处理方法
	$flags = array (0 => $_LANG ['all_user'], 1 => $_LANG ['error_user'], 2 => $_LANG ['rename_user'], 3 => $_LANG ['delete_user'], 4 => $_LANG ['ignore_user'] );
	$smarty->assign ( 'flags', $flags );

	$arr = conflict_userlist ();

	$smarty->assign ( 'ur_here', $_LANG ['conflict_username_modify'] );
	$smarty->assign ( 'domain', '@skyuc' );
	$smarty->assign ( 'list', $arr ['list'] );
	$smarty->assign ( 'filter', $arr ['filter'] );
	$smarty->assign ( 'record_count', $arr ['record_count'] );
	$smarty->assign ( 'page_count', $arr ['page_count'] );
	$smarty->assign ( 'full_page', 1 );

	$smarty->display ( 'integrates_modify.tpl' );
}

/*------------------------------------------------------ */
//-- ajax 用户列表查询
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'query') {
	$arr = conflict_userlist ();
	$smarty->assign ( 'list', $arr ['list'] );
	$smarty->assign ( 'filter', $arr ['filter'] );
	$smarty->assign ( 'record_count', $arr ['record_count'] );
	$smarty->assign ( 'page_count', $arr ['page_count'] );
	$smarty->assign ( 'full_page', 0 );
	make_json_result ( $smarty->fetch ( 'integrates_modify.tpl' ), '', array ('filter' => $arr ['filter'], 'page_count' => $arr ['page_count'] ) );
}

/*------------------------------------------------------ */
//-- 重名用户处理过程
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'act_modify') {
	$skyuc->input->clean_array_gpc ( 'p', array ('opt' => TYPE_ARRAY, 'alias' => TYPE_ARRAY )

	 );
	/* 先处理要改名的用户，改名用户要先检查是否有重名情况，有则标记出来 */
	$alias = array ();
	foreach ( $skyuc->GPC ['opt'] as $user_id => $val ) {
		if ($val = 2) {
			$alias [] = $skyuc->GPC ['alias'] [$user_id];
		}
	}
	if ($alias) {
		// 检查改名后用户名是否会重名
		$sql = 'SELECT user_name FROM ' . TABLE_PREFIX . 'users' . ' WHERE ' . db_create_in ( $alias, 'user_name' );
		$skyuc_error_list = array ();
		$res = $db->query_read ( $sql );
		while ( $row = $db->fetch_array ( $res ) ) {
			$skyuc_error_list [] = $row;
		}

		// 检查和网站是否有重名
		$code = fetch_adminutil_text ( 'code' );
		include_once (DIR . '/includes/modules/integrates/' . $code . '.php');
		$cfg = fetch_adminutil_text ( 'cfg' );
		$cls_user = new $code ( $cfg );

		$bbs_error_list = $cls_user->test_conflict ( $alias );

		$error_list = array_unique ( array_merge ( $skyuc_error_list, $bbs_error_list ) );

		if ($error_list) {
			// 将重名用户标记
			foreach ( $skyuc->GPC ['opt'] as $user_id => $val ) {
				if ($val = 2) {
					if (in_array ( $skyuc->GPC ['alias'] [$user_id], $error_list )) {
						// 重名用户，需要标记
						$sql = 'UPDATE ' . TABLE_PREFIX . 'users' . " SET flag = 1,  alias='' WHERE user_id = '" . $user_id;
					} else {
						// 用户名无重复，可以正常改名
						$sql = 'UPDATE ' . TABLE_PREFIX . 'users' . " SET flag = 2, alias = '" . $db->escape_string ( $skyuc->GPC ['alias'] ["$user_id"] ) . "'" . ' WHERE user_id = ' . $user_id;
					}
					$skyuc->db->query_write ( $sql );
				}
			}
		} else {
			// 处理没有重名的情况
			foreach ( $skyuc->GPC ['opt'] as $user_id => $val ) {
				$sql = 'UPDATE ' . TABLE_PREFIX . 'users' . " SET flag = 2, alias = '" . $db->escape_string ( $skyuc->GPC ['alias'] ["$user_id"] ) . "'" . ' WHERE user_id = ' . $user_id;
				$skyuc->db->query_write ( $sql );
			}
		}
	}

	// 处理删除和保留情况
	foreach ( $skyuc->GPC ['opt'] as $user_id => $val ) {
		if ($val == 3 || $val == 4) {
			$sql = 'UPDATE ' . TABLE_PREFIX . 'users' . " SET flag='" . $db->escape_string ( $val ) . "' WHERE user_id=" . $user_id;
			$skyuc->db->query_write ( $sql );
		}
	}

	// 跳转
	header ( "Location: integrate.php?act=modify" );
	exit ();
}

/*------------------------------------------------------ */
//-- 将网站数据同步到论坛
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'sync') {
	$size = 100;
	$row = $db->query_first_slave ( 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'users' );
	$total = $row ['total'];

	$row = $db->query_first_slave ( 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'users' . ' WHERE flag = 3' );
	$task_del = $row ['total'];

	$row = $db->query_first_slave ( 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'users' . ' WHERE flag = 2' );
	$task_rename = $row ['total'];

	$row = $db->query_first_slave ( 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'users' . ' WHERE flag = 4' );
	$task_ignore = $row ['total'];

	unset ( $row );

	$task_sync = $total - $task_del - $task_ignore;

	$task = array ('del' => array ('total' => $task_del, 'start' => 0 ), 'rename' => array ('total' => $task_rename, 'start' => 0 ), 'sync' => array ('total' => $task_sync, 'start' => 0 ) );
	build_adminutil_text ( 'task', $task );

	$tasks = array ();
	if ($task_del > 0) {
		$tasks [] = array ('task_name' => sprintf ( $_LANG ['task_del'], $task_del ), 'task_status' => '<span id="task_del">' . $_LANG ['task_uncomplete'] . '<span>' );
		$sql = 'SELECT user_name FROM ' . TABLE_PREFIX . 'users' . ' WHERE flag = 2';
		$del_list = array ();
		$res = $db->query_read ( $sql );
		while ( $row = $db->fetch_array ( $res ) ) {
			$del_list [] = $row;
		}
	}

	if ($task_rename > 0) {
		$tasks [] = array ('task_name' => sprintf ( $_LANG ['task_rename'], $task_rename ), 'task_status' => '<span id="task_rename">' . $_LANG ['task_uncomplete'] . '</span>' );
		$sql = 'SELECT user_name, alias FROM ' . TABLE_PREFIX . 'users' . ' WHERE flag = 3';
		$rename_list = $db->query_all ( $sql );
	}

	if ($task_ignore > 0) {
		$sql = 'SELECT user_name FROM ' . TABLE_PREFIX . 'users' . ' WHERE flag = 4';
		$ignore_list = array ();
		$res = $db->query_read ( $sql );
		while ( $row = $db->fetch_array ( $res ) ) {
			$ignore_list [] = $row;
		}
	}

	if ($task_sync > 0) {
		$tasks [] = array ('task_name' => sprintf ( $_LANG ['task_sync'], $task_sync ), 'task_status' => '<span id="task_sync">' . $_LANG ['task_uncomplete'] . '</span>' );
	}

	$tasks [] = array ('task_name' => $_LANG ['task_save'], 'task_status' => '<span id="task_save">' . $_LANG ['task_uncomplete'] . '</span>' );

	// 保存修改日志
	$fp = @fopen ( DIR . '/data/integrate_' . fetch_adminutil_text ( 'code' ) . '_log.php', 'wb' );
	$log = '';
	if (isset ( $del_list )) {
		$log .= '$del_list=' . var_export ( $del_list, true ) . ';';
	}
	if (isset ( $rename_list )) {
		$log .= '$rename_list=' . var_export ( $rename_list, true ) . ';';
	}
	if (isset ( $ignore_list )) {
		$log .= '$ignore_list=' . var_export ( $ignore_list, true ) . ';';
	}
	fwrite ( $fp, $log );
	fclose ( $fp );

	$smarty->assign ( 'tasks', $tasks );
	$smarty->assign ( 'ur_here', $_LANG ['user_sync'] );
	$smarty->assign ( 'size', $size );
	$smarty->display ( 'integrates_sync.tpl' );
}

/*------------------------------------------------------ */
//-- 完成任务
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'task') {
	$skyuc->input->clean_gpc ( 'g', 'size', TYPE_UINT );

	if ($skyuc->GPC ['size'] == 0) {
		$size = 100;
	} else {
		$size = $skyuc->GPC ['size'];
	}

	include_once (DIR . '/includes/class_json.php');
	$json = new JSON ();
	$result = array ('message' => '', 'error' => 0, 'content' => '', 'id' => '', 'end' => 0, 'size' => $size );

	$task = fetch_adminutil_text ( 'task' );

	if ($task ['del'] ['start'] < $task ['del'] ['total']) {
		// 执行操作
		$sql = 'SELECT user_name FROM ' . TABLE_PREFIX . 'users' . ' WHERE flag = 3 ';
		$sql = $skyuc->db->query_limit ( $sql, $result ['size'], $task ['del'] ['start'] );
		// 查找要删除用户
		$arr = array ();
		$res = $db->query_read ( $sql );
		while ( $row = $db->fetch_array ( $res ) ) {
			$arr [] = $row;
		}

		$skyuc->db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'users' . ' WHERE ' . db_create_in ( $arr, 'user_name' ) );

		// 保存设置
		$result ['id'] = 'task_del';
		if ($task ['del'] ['start'] + $result ['size'] >= $task ['del'] ['total']) {
			$task ['del'] ['start'] = $task ['del'] ['total'];
			$result ['content'] = $_LANG ['task_complete'];
		} else {
			$task ['del'] ['start'] += $result ['size'];
			$result ['content'] = sprintf ( $_LANG ['task_run'], $task ['del'] ['start'], $task ['del'] ['total'] );
		}

		build_adminutil_text ( 'task', $task );

		die ( $json->encode ( $result ) );
	} else if ($task ['rename'] ['start'] < $task ['rename'] ['total']) {
		/* 查找要改名用户 */
		$sql = "SELECT user_name FROM " . TABLE_PREFIX . 'users' . " WHERE flag = 2";
		$sql = $skyuc->db->query_limit ( $sql, $result ['size'], $task ['del'] ['start'] );
		$res = $skyuc->db->query_slave ( $sql );
		$arr = array ();
		while ( $row = $db->fetch_row ( $res ) ) {
			$arr [] = $row [0];
		}

		$skyuc->db->query_write ( 'UPDATE ' . TABLE_PREFIX . 'users' . " SET user_name=alias, alias='' WHERE " . db_create_in ( $arr, 'user_name' ) );

		/* 保存设置 */
		$result ['id'] = 'task_rename';
		if ($task ['rename'] ['start'] + $result ['size'] >= $task ['rename'] ['total']) {
			$task ['rename'] ['start'] = $task ['rename'] ['total'];
			$result ['content'] = $_LANG ['task_complete'];
		} else {
			$task ['rename'] ['start'] += $result ['size'];
			$result ['content'] = sprintf ( $_LANG ['task_run'], $task ['rename'] ['start'], $task ['rename'] ['total'] );
		}

		// 保存设置
		build_adminutil_text ( 'task', $task );
		die ( $json->encode ( $result ) );
	} else if ($task ['sync'] ['start'] < $task ['sync'] ['total']) {
		$code = fetch_adminutil_text ( 'code' );
		include_once (DIR . '/includes/modules/integrates/' . $code . '.php');
		$cfg = fetch_adminutil_text ( 'cfg' );
		$cls_user = new $code ( $cfg );
		$cls_user->need_sync = false;

		$sql = 'SELECT user_name, password, email, gender, birthday, reg_time ' . 'FROM ' . TABLE_PREFIX . 'users';
		$sql = $skyuc->db->query_limit ( $sql, $result ['size'], $task ['del'] ['start'] );
		$res = $db->query_read ( $sql );
		while ( $user = $db->fetch_array ( $res ) ) {
			@$cls_user->add_user ( $user ['user_name'], '', $user ['email'], $user ['gender'], $user ['birthday'], $user ['reg_time'], $user ['password'] );
		}

		// 保存设置
		$result ['id'] = 'task_sync';
		if ($task ['start'] + $result ['size'] >= $task ['sync'] ['total']) {
			$task ['sync'] ['start'] = $task ['sync'] ['total'];
			$result ['content'] = $_LANG ['task_complete'];
		} else {
			$task ['sync'] ['start'] += $result ['size'];
			$result ['content'] = sprintf ( $_LANG ['task_run'], $task ['sync'] ['start'], $task ['sync'] ['total'] );
		}

		build_adminutil_text ( 'task', $task );
		die ( $json->encode ( $result ) );
	} else {
		// 记录合并用户
		$code = fetch_adminutil_text ( 'code' );
		$cfg = fetch_adminutil_text ( 'cfg' );

		// 插入code到config表
		$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'setting' . " WHERE code = 'integrate_code'";
		$total = $db->query_first ( $sql );
		if ($total ['total'] == 0) {
			$sql = 'INSERT INTO ' . TABLE_PREFIX . 'setting' . ' (code, value) ' . "VALUES ('integrate_code', '" . $code . "')";
		} else {
			$sql = 'UPDATE ' . TABLE_PREFIX . 'setting' . " SET value = '" . $code . "' WHERE code = 'integrate_code'";
		}
		$db->query_write ( $sql );

		build_options ();

		// 序列化设置信息，并保存到数据库
		save_integrate_config ( $code, $cfg );

		$result ['content'] = $_LANG ['task_complete'];
		$result ['id'] = 'task_save';
		$result ['end'] = 1;

		// 清理临时信息
		build_adminutil_text ( 'cfg' );
		build_adminutil_text ( 'code' );
		build_adminutil_text ( 'task' );
		build_adminutil_text ( 'domain' );

		$sql = 'UPDATE ' . TABLE_PREFIX . 'users' . " set flag = 0, alias = '' WHERE flag > 0";
		$db->query_write ( $sql );
		die ( $json->encode ( $result ) );
	}
}
/*------------------------------------------------------ */
//-- 保存UCenter设置
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'setup_ucenter') {
	include_once (DIR . '/includes/class_json.php');
	include_once (DIR . '/includes/class_transport.php');

	$json = new JSON ();
	$result = array ('error' => 0, 'message' => '' );

	$skyuc->input->clean_array_gpc ( 'p', array ('ucapi' => TYPE_STR, 'ucip' => TYPE_STR, 'ucfounderpw' => TYPE_STR ) );

	$app_type = 'OTHER';
	$app_name = $skyuc->options ['site_name'];
	$app_url = get_url ();
	$app_charset = 'UTF-8';
	$app_dbcharset = 'utf8';
	$ucapi = $skyuc->GPC ['ucapi'];
	$ucip = $skyuc->GPC ['ucip'];
	$dns_error = false;
	if (! $ucip) {
		$temp = @parse_url ( $ucapi );
		$ucip = gethostbyname ( $temp ['host'] );
		if (ip2long ( $ucip ) == - 1 || ip2long ( $ucip ) === FALSE) {
			$ucip = '';
			$dns_error = true;
		}
	}
	if ($dns_error) {
		$result ['error'] = 2;
		$result ['message'] = '';
		die ( $json->encode ( $result ) );
	}

	// 初获取整合的ucenter编码
	$cfg = unserialize ( $skyuc->options ['integrate_config'] );
	if (! defined ( UC_CHARSET ))
		define ( 'UC_CHARSET', isset ( $cfg ['uc_charset'] ) ? $cfg ['uc_charset'] : '' );

	if (strtoupper ( UC_CHARSET ) != 'UTF8' || strtoupper ( UC_CHARSET ) != 'UTF-8') {
		$app_name = skyuc_iconv ( 'UTF8', UC_CHARSET, $app_name );
		$_LANG ['tagtemplates_filmname'] = skyuc_iconv ( 'UTF8', UC_CHARSET, $_LANG ['tagtemplates_filmname'] );
		$_LANG ['tagtemplates_uid'] = skyuc_iconv ( 'UTF8', UC_CHARSET, $_LANG ['tagtemplates_uid'] );
		$_LANG ['tagtemplates_username'] = skyuc_iconv ( 'UTF8', UC_CHARSET, $_LANG ['tagtemplates_username'] );
		$_LANG ['tagtemplates_dateline'] = skyuc_iconv ( 'UTF8', UC_CHARSET, $_LANG ['tagtemplates_dateline'] );
		$_LANG ['tagtemplates_url'] = skyuc_iconv ( 'UTF8', UC_CHARSET, $_LANG ['tagtemplates_url'] );
		$_LANG ['tagtemplates_image'] = skyuc_iconv ( 'UTF8', UC_CHARSET, $_LANG ['tagtemplates_image'] );
	}

	$ucfounderpw = $skyuc->GPC ['ucfounderpw'];
	$app_tagtemplates = 'apptagtemplates[template]=' . urlencode ( '<a href="{url}" target="_blank">{film_name}</a>' ) . '&' . 'apptagtemplates[fields][film_name]=' . urlencode ( $_LANG ['tagtemplates_filmname'] ) . '&' . 'apptagtemplates[fields][uid]=' . urlencode ( $_LANG ['tagtemplates_uid'] ) . '&' . 'apptagtemplates[fields][username]=' . urlencode ( $_LANG ['tagtemplates_username'] ) . '&' . 'apptagtemplates[fields][dateline]=' . urlencode ( $_LANG ['tagtemplates_dateline'] ) . '&' . 'apptagtemplates[fields][url]=' . urlencode ( $_LANG ['tagtemplates_url'] ) . '&' . 'apptagtemplates[fields][image]=' . urlencode ( $_LANG ['tagtemplates_image'] );

	$postdata = "release=20110501&m=app&a=add&ucfounder=&ucfounderpw=" . urlencode ( $ucfounderpw ) . "&apptype=" . urlencode ( $app_type ) . "&appname=" . urlencode ( $app_name ) . "&appurl=" . urlencode ( $app_url ) . "&appip=&appcharset=" . $app_charset . '&appdbcharset=' . $app_dbcharset . '&apptagtemplates=' . $app_tagtemplates;
	$transport = new transport ();
	$ucconfig = $transport->request ( $ucapi . '/index.php', $postdata );
	$ucconfig = $ucconfig ['body'];
	if (empty ( $ucconfig )) {
		//ucenter 验证失败
		$result ['error'] = 1;
		$result ['message'] = $_LANG ['uc_msg_verify_failur'];
	} elseif ($ucconfig == '-1') {
		//管理员密码无效
		$result ['error'] = 1;
		$result ['message'] = $_LANG ['uc_msg_password_wrong'];
	} else {
		list ( $appauthkey, $appid ) = explode ( '|', $ucconfig );
		if (empty ( $appauthkey ) || empty ( $appid )) {
			//ucenter 安装数据错误
			$result ['error'] = 1;
			$result ['message'] = $_LANG ['uc_msg_data_error'];
		} else {
			$result ['error'] = 0;
			$result ['message'] = $ucconfig;
		}
	}

	die ( $json->encode ( $result ) );
}

// 显示整合成功信息
if ($skyuc->GPC ['act'] == 'complete') {
	// 重建缓存
	build_options ();

	sys_msg ( $_LANG ['sync_ok'], 0, array (array ('text' => $_LANG ['07_user_integrate'], 'href' => 'integrate.php?act=list' ) ) );
}

if ($skyuc->GPC ['act'] == 'points_set') {
	$skyuc->input->clean_gpc ( 'g', 'rule_index', TYPE_STR );
	$rule_index = $skyuc->GPC ['rule_index'];

	$user = &init_users ();
	$points = $user->get_points_name (); //获取网站可用积分


	if (empty ( $points )) {
		sys_msg ( $_LANG ['no_points'], 0, array (array ('text' => $_LANG ['07_user_integrate'], 'href' => 'integrate.php?act=list' ) ) );
	} elseif ($points == 'ucenter') {
		sys_msg ( $_LANG ['uc_points'], 0, array (array ('text' => $_LANG ['uc_set_credits'], 'href' => UC_API, 'target' => '_blank' ) ), false );
	}

	$rule = array (); //取得一样规则
	if ($skyuc->options ['points_rule']) {
		$rule = unserialize ( $skyuc->options ['points_rule'] );
	}

	$points_key = array_keys ( $points );
	$count = count ( $points_key );

	$select_rule = array ();
	$exist_rule = array ();
	for($i = 0; $i < $count; $i ++) {
		if (! isset ( $rule [TO_P . $points_key [$i]] )) {
			$select_rule [TO_P . $points_key [$i]] = $_LANG ['bbs'] . $points [$points_key [$i]] ['title'] . '->' . $_LANG ['site_pay_point'];
		} else {
			$exist_rule [TO_P . $points_key [$i]] = $_LANG ['bbs'] . $points [$points_key [$i]] ['title'] . '->' . $_LANG ['site_pay_point'];
		}
	}

	for($i = 0; $i < $count; $i ++) {
		if (! isset ( $rule [FROM_P . $points_key [$i]] )) {
			$select_rule [FROM_P . $points_key [$i]] = $_LANG ['site_pay_point'] . '->' . $_LANG ['bbs'] . $points [$points_key [$i]] ['title'];
		} else {
			$exist_rule [FROM_P . $points_key [$i]] = $_LANG ['site_pay_point'] . '->' . $_LANG ['bbs'] . $points [$points_key [$i]] ['title'];
		}
	}

	// 判断是否还能添加新规则
	if (($rule_index && isset ( $rule [$rule_index] )) || empty ( $select_rule )) {
		$allow_add = 0;
	} else {
		$allow_add = 1;
	}

	if ($rule_index && isset ( $rule [$rule_index] )) {
		list ( $from_val, $to_val ) = explode ( ':', $rule [$rule_index] );

		$select_rule [$rule_index] = $exist_rule [$rule_index];
		$smarty->assign ( 'from_val', $from_val );
		$smarty->assign ( 'to_val', $to_val );
	}

	$smarty->assign ( 'rule_index', $rule_index );
	$smarty->assign ( 'allow_add', $allow_add );
	$smarty->assign ( 'select_rule', $select_rule );
	$smarty->assign ( 'exist_rule', $exist_rule );
	$smarty->assign ( 'rule_list', $rule );
	$smarty->assign ( 'full_page', 1 );
	$smarty->assign ( 'points', $points );
	$smarty->display ( 'integrates_points.tpl' );
}

if ($skyuc->GPC ['act'] == 'edit_points') {

	$skyuc->input->clean_gpc ( 'r', 'rule_index', TYPE_STR );
	$skyuc->input->clean_array_gpc ( 'p', array ('from_val' => TYPE_UINT, 'to_val' => TYPE_UINT, 'old_rule_index' => TYPE_STR ) );

	$rule_index = $skyuc->GPC ['rule_index'];

	$rule = array (); //取得一样规则
	if ($skyuc->options ['points_rule']) {
		$rule = unserialize ( $skyuc->options ['points_rule'] );
	}

	if ($skyuc->GPC_exists ['from_val'] && $skyuc->GPC_exists ['to_val']) {
		// 添加rule
		$from_val = $skyuc->GPC ['from_val'];
		$to_val = ($skyuc->GPC ['to_val'] == 0) ? 1 : $skyuc->GPC ['to_val'];
		$old_rule_index = $skyuc->GPC ['old_rule_index'];

		if (empty ( $old_rule_index ) || $old_rule_index == $rule_index) {
			$rule [$rule_index] = $from_val . ':' . $to_val;
		} else {
			$tmp_rule = array ();
			foreach ( $rule as $key => $val ) {
				if ($key == $old_rule_index) {
					$tmp_rule [$rule_index] = $from_val . ':' . $to_val;
				} else {
					$tmp_rule [$key] = $val;
				}
			}

			$rule = $tmp_rule;
		}

	} else {
		// 删除rule
		unset ( $rule [$rule_index] );
	}

	$sql = 'UPDATE ' . TABLE_PREFIX . 'setting' . " SET value ='" . serialize ( $rule ) . "' WHERE code='points_rule'";

	$db->query_write ( $sql );

	build_options ();

	header ( "Location: integrate.php?act=points_set\n" );
	exit ();
}

if ($skyuc->GPC ['act'] == 'save_points') {
	$keys = array_keys ( $_POST );
	$cfg = array ();
	foreach ( $keys as $key ) {
		if (is_array ( $_POST [$key] )) {
			$cfg [$key] ['bbs_points'] = empty ( $_POST [$key] ['bbs_points'] ) ? 0 : intval ( $_POST [$key] ['bbs_points'] );
			$cfg [$key] ['fee_points'] = empty ( $_POST [$key] ['fee_points'] ) ? 0 : intval ( $_POST [$key] ['fee_points'] );
			$cfg [$key] ['pay_point'] = empty ( $_POST [$key] ['pay_point'] ) ? 0 : intval ( $_POST [$key] ['pay_point'] );

	//$cfg[$key]['rank_points'] = empty($_POST[$key]['rank_points']) ? 0 : intval($_POST[$key]['rank_points']);
		}
	}

	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'setting' . " WHERE code='points_set'";
	$total = $db->query_first ( $sql );
	if ($total ['total'] == 0) {
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'setting' . " (parent_id, type, code, value) VALUES (6, 'hidden', 'points_set', '" . serialize ( $cfg ) . "'";
	} else {
		$sql = 'UPDATE ' . TABLE_PREFIX . 'setting' . " SET value ='" . serialize ( $cfg ) . "' WHERE code='points_set'";
	}
	$db->query_write ( $sql );

	build_options ();

	sys_msg ( $_LANG ['save_ok'], 0, array (array ('text' => $_LANG ['07_user_integrate'], 'href' => 'integrate.php?act=list' ) ) );
}

?>