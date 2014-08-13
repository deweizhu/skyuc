<?php

/**
 * SKYUC! 管理中心网站设置
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');
/*------------------------------------------------------ */
//-- 列表编辑 ?act=list_edit
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list_edit') {
	/* 检查权限 */
	admin_priv ( 'setting' );

	/* 取出全部数据：分组和变量 */
	$sql = "SELECT * FROM " . TABLE_PREFIX . 'setting' . " WHERE type<>'hidden' ORDER BY parent_id, sort_order, id";
	$item_list = $skyuc->db->query_all_slave ( $sql );

	/* 整理数据 */
	$group_list = array ();
	foreach ( $item_list as $key => $item ) {
		$pid = $item ['parent_id'];
		$item ['name'] = $_LANG ['cfg_name'] [$item ['code']];
		$item ['desc'] = isset ( $_LANG ['cfg_desc'] [$item ['code']] ) ? $_LANG ['cfg_desc'] [$item ['code']] : '';

		if ($pid == 0) {
			/* 分组 */
			if ($item ['type'] == 'group') {
				$group_list [$item ['id']] = $item;
			}
		} else {
			/* 变量 */
			if (isset ( $group_list [$pid] )) {
				if ($item ['site_range']) {
					$item ['site_options'] = explode ( ',', $item ['site_range'] );

					foreach ( $item ['site_options'] as $k => $v ) {
						$item ['display_options'] [$k] = $_LANG ['cfg_range'] [$item ['code']] [$v];
					}
				}
				$group_list [$pid] ['vars'] [] = $item;
			}
		}

	}

	/* 可选语言 */
	$dir = opendir ( DIR . '/languages/' );
	$lang_list = array ();
	while ( @$file = readdir ( $dir ) ) {
		if ($file != '.' && $file != '..' && $file != '.svn' && $file != '_svn' && is_dir ( DIR . '/languages/' . $file )) {
			$lang_list [] = $file;
		}
	}
	@closedir ( $dir );

	//会员等级
	$sql = 'SELECT rank_id, rank_name FROM ' . TABLE_PREFIX . 'user_rank';
	$sql = $skyuc->db->query_limit ( $sql, 30 );
	$user_rank = $skyuc->db->query_all_slave ( $sql );

	//会话 IP 八进制长度检查
	$ipcheck = array (0 => '255.255.255.255', 1 => '255.255.255.0', 2 => '255.255.0.0' );
	//Gzip压缩级别
	$gziplevel = array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9 );

	$smarty->assign ( 'ur_here', $_LANG ['02_setting'] );
	$smarty->assign ( 'group_list', $group_list );
	$smarty->assign ( 'cfg', $skyuc->options );
	$smarty->assign ( 'lang_list', $lang_list );
	$smarty->assign ( 'ipcheck', $ipcheck );
	$smarty->assign ( 'gziplevel', $gziplevel );
	$smarty->assign ( 'user_rank', $user_rank );

	assign_query_info ();
	$smarty->display ( 'setting.tpl' );
}

/*------------------------------------------------------ */
//-- 提交   ?act=post
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'post') {
	/* 检查权限 */
	admin_priv ( 'setting' );

	/* 保存变量值 */
	$skyuc->input->clean_array_gpc ( 'p', array ('value' => TYPE_ARRAY_STR ) );
	$count = count ( $skyuc->GPC ['value'] );

	$arr = array ();
	$sql = 'SELECT id, value FROM ' . TABLE_PREFIX . 'setting' . " WHERE  parent_id !=0 AND type !='hidden'";
	$res = $db->query_read ( $sql );
	while ( $row = $db->fetch_array ( $res ) ) {
		$arr [$row ['id']] = $row ['value'];
	}
	foreach ( $skyuc->GPC ['value'] as $key => $val ) {
		if ($arr [$key] != $val) {
			switch ($key) {
				case 506 :
				case 618 :
					//FTP帐户密码 618, SMTP帐户密码 506 加密
					$val = mcryptcode ( $val, 'ENCODE' );
					break;
				case 102 :
					//网址URL以/结尾，删除/
					if (substr ( $val, - 1 ) == '/') {
						$val = substr ( $val, 0, - 1 );
					}
					break;
				case 213 :
					//会员默认等级
					$sql = 'ALTER TABLE ' . TABLE_PREFIX . "users CHANGE `user_rank` `user_rank` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT '" . ( int ) $val . "'";
					$skyuc->db->query_write ( $sql );
					break;
				default :
					break;
			}
			$sql = "UPDATE " . TABLE_PREFIX . 'setting' . " SET value = '" . $db->escape_string ( trim ( $val ) ) . "' WHERE id = '" . $key . "'";
			$skyuc->db->query_write ( $sql );
		}
	}

	/* 记录日志 */
	admin_log ( '', 'edit', 'setting' );

	// 清除缓存
	$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', 'show.dwt', 'search.dwt', 'article.dwt', 'article_cat.dwt' ) );

	$skyuc->options = build_options ();

	build_category ();

	/* 向官方服务器提交客户信息 */
	$spt = '<script type="text/javascript" src="http://api.skyuc.com/pack/record.php?';
	$spt .= 'url=' . urlencode ( get_url () );
	$spt .= '&site_name=' . urlencode ( $skyuc->options ['site_name'] );
	$spt .= '&site_title=' . urlencode ( $skyuc->options ['site_title'] );
	$spt .= '&site_desc=' . urlencode ( $skyuc->options ['site_desc'] );
	$spt .= '&site_keywords=' . urlencode ( $skyuc->options ['site_keywords'] );
	$spt .= '&address=' . urlencode ( $skyuc->options ['site_address'] );
	$spt .= '&qq=' . $skyuc->options ['qq'] . '&ww=' . $skyuc->options ['ww'] . '&ym=' . $skyuc->options ['ym'] . '&msn=' . $skyuc->options ['msn'];
	$spt .= '&email=' . $skyuc->options ['service_email'] . '&phone=' . $skyuc->options ['service_phone'];
	$spt .= '&version=' . $skyuc->options ['skyuc_version'] . '&language=' . $skyuc->options ['lang'];
	$spt .= '"></script>';

	sys_msg ( $_LANG ['save_success'] . $spt, 0, array (array ('href' => 'setting.php?act=list_edit', 'text' => $_LANG ['02_setting'] ) ) );
}

/*------------------------------------------------------ */
//-- 发送测试邮件
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'send_test_email') {
	/* 检查权限 */
	check_authz_json ( 'setting' );
	$skyuc->input->clean_array_gpc ( 'p', array ('use_smtp' => TYPE_BOOL, 'smtp_host' => TYPE_STR, 'smtp_port' => TYPE_STR, 'smtp_user' => TYPE_STR, 'smtp_pass' => TYPE_STR, 'reply_email' => TYPE_STR, 'mail_charset' => TYPE_STR, 'test_mail_address' => TYPE_STR ) );

	/* 更新配置 */
	$skyuc->options ['use_smtp'] = $skyuc->GPC ['use_smtp'];
	$skyuc->options ['smtp_host'] = $skyuc->GPC ['smtp_host'];
	$skyuc->options ['smtp_port'] = $skyuc->GPC ['smtp_port'];
	$skyuc->options ['smtp_user'] = $skyuc->GPC ['smtp_user'];
	$skyuc->options ['smtp_pass'] = $skyuc->GPC ['smtp_pass'];
	$skyuc->options ['smtp_mail'] = $skyuc->GPC ['reply_email'];
	$skyuc->options ['mail_charset'] = $skyuc->GPC ['mail_charset'];

	if (false !== skyuc_mail ( $skyuc->GPC ['test_mail_address'], $_LANG ['test_mail_title'], $_LANG ['cfg_name'] ['email_content'], true, 0 )) {
		make_json_result ( '', $_LANG ['sendemail_success'] . $skyuc->GPC ['test_mail_address'] );
	} else {
		make_json_error ( join ( "\n", $err->_message ) );
	}
}

?>
