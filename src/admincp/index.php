<?php
/**
 * SKYUC! 管理中心首页文件
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

/*------------------------------------------------------ */
//-- 框架
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == '') {
	$smarty->display ( 'index.tpl' );
}
/*------------------------------------------------------ */
//-- 计算器
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'calculator') {
	$smarty->display ( 'calculator.tpl' );
}
/*------------------------------------------------------ */
//-- 顶部框架的内容
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'top') {
	// 获得管理员设置的菜单
	$nav_list = array ();
	$nav = $skyuc->db->query_first ( 'SELECT nav_list FROM ' . TABLE_PREFIX . 'admin' . " WHERE user_id = '" . $skyuc->session->vars ['adminid'] . "'" );
	if (! empty ( $nav )) {
		$arr = explode ( ',', $nav ['nav_list'] );

		foreach ( $arr as $val ) {
			$tmp = explode ( '|', $val );
			// 获得管理员ID
			$nav_list [$tmp [1]] = $tmp [0];
		}
	}

	// 获得管理员ID
	$smarty->assign ( 'nav_list', $nav_list );
	$smarty->assign ( 'admin_id', $skyuc->session->vars ['adminid'] );
	$smarty->display ( 'top.tpl' );
}
/*------------------------------------------------------ */
//-- 左边的框架
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'menu') {
	include (ADM . '/inc_menu.php');

	foreach ( $modules as $key => $value ) {
		ksort ( $modules [$key] );
	}
	ksort ( $modules );

	foreach ( $modules as $key => $val ) {
		$menus [$key] ['label'] = $_LANG [$key];
		if (is_array ( $val )) {
			foreach ( $val as $k => $v ) {
				if (isset ( $purview [$k] )) {
					if (! admin_priv ( $purview [$k], '', false )) {
						continue;
					}
				}
				if ($k == 'ucenter_setup' && $skyuc->options ['integrate_code'] != 'ucenter') {
					continue;
				}
				$menus [$key] ['children'] [$k] ['label'] = $_LANG [$k];
				$menus [$key] ['children'] [$k] ['action'] = $skyuc->session->vars ['sessionhashurl'] . $v;
			}
		} else {
			$menus [$key] ['action'] = $val;
		}
		// 如果children的子元素长度为0则删除该组
		if (! count ( $menus [$key] ['children'] )) {
			unset ( $menus [$key] );
		}
	}

	$smarty->assign ( 'menus', $menus );
	$smarty->assign ( 'no_help', $_LANG ['no_help'] );
	$smarty->assign ( 'help_lang', $skyuc->options ['lang'] );
	$smarty->display ( 'menu.tpl' );
}
/*------------------------------------------------------ */
//-- 主窗口，起始页
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'main') {

	/* 初始化创建目录 */
	if (! is_dir ( DIR . '/data/compiled' )) {
		@mkdir ( DIR . '/data/compiled', 0777 );
		@chmod ( DIR . '/data/compiled', 0777 );
	}
	if (! is_dir ( DIR . '/data/compiled/admincp' )) {
		@mkdir ( DIR . '/data/compiled/admincp', 0777 );
		@chmod ( DIR . '/data/compiled/admincp', 0777 );
	}
	if (! is_dir ( DIR . '/data/caches' )) {
		@mkdir ( DIR . '/data/caches', 0777 );
		@chmod ( DIR . '/data/caches', 0777 );
	}
	if (! is_dir ( DIR . '/data/sqldata' )) {
		@mkdir ( DIR . '/data/sqldata', 0777 );
		@chmod ( DIR . '/data/sqldata', 0777 );
	}
	if (! is_dir ( DIR . '/data/images' )) {
		@mkdir ( DIR . '/data/images', 0777 );
		@chmod ( DIR . '/data/images', 0777 );
	}
	if (! is_dir ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/posters' )) {
		@mkdir ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/posters', 0777 );
		@chmod ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/posters', 0777 );
	}
	if (! is_dir ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/feedbackimg' )) {
		@mkdir ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/feedbackimg', 0777 );
		@chmod ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/feedbackimg', 0777 );
	}
	if (! is_dir ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/article' )) {
		@mkdir ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/article', 0777 );
		@chmod ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/article', 0777 );
	}
	if (! is_dir ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/afficheimg' )) {
		@mkdir ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/afficheimg', 0777 );
		@chmod ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/afficheimg', 0777 );
	}

	// 系统信息
	$mysqlversion = $skyuc->db->version ();

	$sys_info ['os'] = PHP_OS;
	$sys_info ['web_server'] = $_SERVER ['SERVER_SOFTWARE'];
	$sys_info ['php_ver'] = PHP_VERSION;
	$sys_info ['mysql_ver'] = $mysqlversion;
	$sys_info ['zlib'] = iif ( function_exists ( 'gzclose' ), $_LANG ['yes'], $_LANG ['no'] );
	$sys_info ['safe_mode'] = iif ( ( boolean ) ini_get ( 'safe_mode' ), $_LANG ['yes'], $_LANG ['no'] );
	$sys_info ['safe_mode_gid'] = iif ( ( boolean ) ini_get ( 'safe_mode_gid' ), $_LANG ['yes'], $_LANG ['no'] );

	$timeoffset = iif ( $skyuc->options ['timezoneoffset'] >= 0, iif ( $skyuc->options ['timezoneoffset'] == 0, '', '+' . $skyuc->options ['timezoneoffset'] ), $skyuc->options ['timezoneoffset'] );
	$sys_info ['timezone'] = iif ( ! empty ( $skyuc->options ['timezoneoffset'] ), 'UTC ' . $timeoffset, $_LANG ['no_timezone'] );

	$sys_info ['socket'] = iif ( function_exists ( 'fsockopen' ), $_LANG ['yes'], $_LANG ['no'] );
	$sys_info ['register_globals'] = iif ( @ini_get ( 'register_globals' ), $_LANG ['yes'], $_LANG ['no'] );
	$sys_info ['magic_quotes_gpc'] = iif ( @ini_get ( 'magic_quotes_gpc' ), $_LANG ['yes'], $_LANG ['no'] );
	$sys_info ['post_max_size'] = @ini_get ( 'post_max_size' );
	$sys_info ['max_filesize'] = @ini_get ( 'upload_max_filesize' );
	$sys_info ['allow_url_fopen'] = iif ( @ini_get ( 'allow_url_fopen' ), $_LANG ['yes'], $_LANG ['no'] );
	$sys_info ['curl'] = iif ( function_exists ( 'curl_init' ), $_LANG ['yes'], $_LANG ['no'] );

	// IP库版本
	$ip_version = skyuc_geoip ( '255.255.255.0' );
	$sys_info ['ip_version'] = $ip_version;

	// 安装目录检查,若存在提示删除
	$warning_arr = array ();
	if (is_dir ( DIR . '/install' )) {
		$warning_arr [] = $_LANG ['remove_install'];
	}

	// 主机临时目录权限检查,无权限提示不能上传文件
	$open_basedir = @ini_get ( 'open_basedir' );
	if (! empty ( $open_basedir )) {
		// 如果 open_basedir 不为空，则检查是否包含了 upload_tmp_dir
		$open_basedir = str_replace ( array ("\\", "\\\\" ), array ('/', '/' ), $open_basedir );
		$upload_tmp_dir = ini_get ( 'upload_tmp_dir' );

		if (empty ( $upload_tmp_dir )) {
			if (DIRECTORY_SEPARATOR == '\\') {
				$upload_tmp_dir = getenv ( 'TEMP' ) ? getenv ( 'TEMP' ) : getenv ( 'TMP' );
				$upload_tmp_dir = str_replace ( array ("\\", "\\\\" ), array ('/', '/' ), $upload_tmp_dir );
			} else {
				$upload_tmp_dir = getenv ( 'TMPDIR' ) === false ? '/tmp' : getenv ( 'TMPDIR' );
			}
		}

		if (! stristr ( $open_basedir, $upload_tmp_dir )) {
			$warning [] = sprintf ( $_LANG ['temp_dir_cannt_read'], $upload_tmp_dir );
		}
	}

	// 验证你使用的优化程序和 SKYUC 是否兼容。
	if (($err = verify_optimizer_environment ()) !== true) {
		$warning [] = $err;
	}

	// data目录权限检查
	$result = file_mode_info ( DIR . '/data' );
	if ($result < 2) {
		$warning [] = sprintf ( $_LANG ['not_writable'], 'data', $_LANG ['data_cannt_write'] );
	}
	/* 上传图片目录权限检查 */
	$result = file_mode_info ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] );
	if ($result < 2) {
		$warning [] = sprintf ( $_LANG ['not_writable'], 'images', $_LANG ['images_cannt_write'] );
	}

	//如果管理员的最后登陆时间大于24小时则显示官方消息
	$last_check = fetch_adminutil_text ( $skyuc->session->vars ['adminid'] . '_last_check' );
	if (TIMENOW - $last_check > (3600 * 24)) {
		$smarty->assign('GetNewInfo', get_new_info());
	}

	assign_query_info ();
	$install_date = skyuc_date ( $skyuc->options ['date_format'], $skyuc->options ['install_date'], false, false );


	$smarty->assign ( 'sys_info', $sys_info );
	$smarty->assign ( 'warning_arr', $warning_arr );
	$smarty->assign ( 'skyuc_version', VERSION . ' release ' . RELEASE );
	$smarty->assign ( 'skyuc_lang', $skyuc->options ['lang'] );
	$smarty->assign ( 'install_date', $install_date );
	$smarty->display ( 'main.tpl' );
}

/*------------------------------------------------------ */
//-- 关于 SKYUC!
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'about_us') {
	assign_query_info ();

	$pagedata = cachemgr ();
	$pagedata ['curMBytes'] = round ( $pagedata ['curBytes'] / 1024 / 1024, 2 ) . ' M';
	$pagedata ['freeMBytes'] = round ( $pagedata ['freeBytes'] / 1024 / 1024, 2 ) . ' M';

	$smarty->assign ( 'pagedata', $pagedata );
	$smarty->display ( 'about_us.tpl' );
}

/*------------------------------------------------------ */
//-- 拖动的帧
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'drag') {

	$smarty->display ( 'drag.tpl' );
}

/*------------------------------------------------------ */
//-- Totolist操作
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'save_todolist') {
	$content = $skyuc->input->clean_gpc ( 'p', 'content', TYPE_STR );
	$sql = 'UPDATE ' . TABLE_PREFIX . 'admin' . " SET todolist='" . $content . "' WHERE user_id = " . $skyuc->session->vars ['adminid'];
	$skyuc->db->query_write ( $sql );
}

elseif ($skyuc->GPC ['act'] == 'get_todolist') {
	$sql = 'SELECT todolist FROM ' . TABLE_PREFIX . 'admin' . " WHERE user_id = " . $skyuc->session->vars ['adminid'];
	$content = $skyuc->db->query_first_slave ( $sql );
	echo $content ['todolist'];
}
/*------------------------------------------------------ */
//-- 清除缓存
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'clear_cache') {
	clear_tpl_files ();

	$skyuc->secache->_file = DIR . '/data/caches/cachedata.php';
	$skyuc->secache->create ();


	sys_msg ( $_LANG ['caches_cleared'] );
}

?>

