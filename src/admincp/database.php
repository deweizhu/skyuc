<?php
/**
 * SKYUC!
 * 数据库管理
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');
require_once (DIR . '/includes/class_sql_dump.php');

//@ini_set('memory_limit', '128M');


/*------------------------------------------------------ */
//-- 备分数据SQL
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'backup') {
	$tables = array ();
	$res = $db->query_read ( "SHOW TABLES LIKE '" . $db->escape_string_like ( TABLE_PREFIX ) . "%'" );
	while ( $row = $db->fetch_row ( $res ) ) {
		$tables [] = $row [0];
	}
	unset ( $row );

	/*    $allow_max_size = ini_size_to_bytes(@ini_get('upload_max_filesize')); // 单位为字节
    $allow_max_size = $allow_max_size / 1024; // 转换单位为 KB*/
	$allow_max_size = 2048;

	// 权限检查
	$path = DIR . '/data/sqldata';
	$mask = file_mode_info ( $path );
	if ($mask === false) {
		$warning = sprintf ( $_LANG ['dir_not_exist'], $path );
		$smarty->assign ( 'warning', $warning );
	} else if ($mask != 15) {
		$warning = sprintf ( $_LANG ['dir_priv'], $path ) . '<br/>';
		if (($mask & 1) < 1) {
			$warning .= $_LANG ['cannot_read'] . '&nbsp;&nbsp;';
		}
		if (($mask & 2) < 1) {
			$warning .= $_LANG ['cannot_write'] . '&nbsp;&nbsp;';
		}
		if (($mask & 4) < 1) {
			$warning .= $_LANG ['cannot_add'] . '&nbsp;&nbsp;';
		}
		if (($mask & 8) < 1) {
			$warning .= $_LANG ['cannot_modify'];
		}
		$smarty->assign ( 'warning', $warning );
	}

	assign_query_info ();
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['restore'], 'href' => 'database.php?act=restore' ) );
	$smarty->assign ( 'tables', $tables );
	$smarty->assign ( 'vol_size', $allow_max_size );
	$smarty->assign ( 'sql_name', SqlDump::get_random_name () );
	$smarty->assign ( 'ur_here', $_LANG ['01_db_manage'] );
	$smarty->display ( 'db_backup.tpl' );
}

/*------------------------------------------------------ */
//-- 备份恢复页面
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'restore') {
	// 权限判断
	admin_priv ( 'db_renew' );

	$list = array ();
	$path = DIR . '/data/sqldata/';

	// 检查目录
	$mask = file_mode_info ( $path );
	if ($mask === false) {
		$warning = sprintf ( $_LANG ['dir_not_exist'], $path );
		$smarty->assign ( 'warning', $warning );
	} elseif (($mask & 1) < 1) {
		$warning = $path . '&nbsp;&nbsp;' . $_LANG ['cannot_read'];
		$smarty->assign ( 'warning', $warning );
	} else {
		// 获取文件列表
		$real_list = array ();
		$folder = opendir ( $path );
		while ( $file = readdir ( $folder ) ) {
			if (strpos ( $file, '.sql' ) !== false || strpos ( $file, '.zip' ) !== false) {
				$real_list [] = $file;
			}
		}
		natsort ( $real_list );

		$match = array ();
		foreach ( $real_list as $file ) {
			if (preg_match ( '/-([0-9]+)\.sql$/', $file, $match )) {
				if ($match [1] == 1) {
					$mark = 1;
				} else {
					$mark = 2;
				}
			} elseif (preg_match ( '/([0-9a-zA-Z])+\.zip$/', $file, $match )) {
				$mark = 3;
			} else {
				$mark = 0;
			}

			$file_size = filesize ( $path . $file );
			$info = SqlDump::get_head ( $path . $file );
			$list [] = array ('name' => $file, 'ver' => $info ['skyuc_ver'], 'add_time' => $info ['date'], 'vol' => $info ['vol'], 'file_size' => num_bitunit ( $file_size ), 'mark' => $mark );
		}
	}

	assign_query_info ();
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['01_db_manage'], 'href' => 'database.php?act=backup' ) );
	$smarty->assign ( 'ur_here', $_LANG ['restore'] );
	$smarty->assign ( 'list', $list );
	$smarty->display ( 'db_restore.tpl' );
}

// 开始执行备分数据
if ($skyuc->GPC ['act'] == 'dumpsql') {
	//权限判断
	admin_priv ( 'db_backup' );

	// 检查目录权限
	$path = DIR . '/data/sqldata';
	$mask = file_mode_info ( $path );
	if ($mask === false) {
		$warning = sprintf ( $_LANG ['dir_not_exist'], $path );
		sys_msg ( $warning, 1 );
	} elseif ($mask != 15) {
		$warning = sprintf ( $_LANG ['dir_priv'], $path );
		if (($mask & 1) < 1) {
			$warning .= $_LANG ['cannot_read'];
		}
		if (($mask & 2) < 1) {
			$warning .= $_LANG ['cannot_write'];
		}
		if (($mask & 4) < 1) {
			$warning .= $_LANG ['cannot_add'];
		}
		if (($mask & 8) < 1) {
			$warning .= $_LANG ['cannot_modify'];
		}
		sys_msg ( $warning, 1 );
	}

	@set_time_limit ( 0 );

	// 初始化
	$dump = new SqlDump ( $skyuc->db );
	$run_log = DIR . '/data/sqldata/run.log';

	// 初始化输入变量
	$skyuc->input->clean_array_gpc ( 'r', array ('sql_file_name' => TYPE_STR, 'vol_size' => TYPE_UINT, 'ext_insert' => TYPE_BOOL, 'usehex' => TYPE_BOOL, 'usezip' => TYPE_UINT, 'vol' => TYPE_UINT, 'from' => TYPE_UINT, 'type' => TYPE_STR, 'customtables' => TYPE_ARRAY_STR ) );

	if (empty ( $skyuc->GPC ['sql_file_name'] )) {
		$sql_file_name = $dump->get_random_name ();
	} else {
		$sql_file_name = str_replace ( "0xa", '', $skyuc->GPC ['sql_file_name'] ); // 过滤 0xa 非法字符
		$pos = strpos ( $sql_file_name, '.sql' );
		if ($pos !== false) {
			$sql_file_name = substr ( $sql_file_name, 0, $pos );
		}
	}

	$max_size = $skyuc->GPC ['vol_size'];
	$vol = iif ( $skyuc->GPC ['vol'] == 0, 1, $skyuc->GPC ['vol'] );
	$from = $skyuc->GPC ['from'];
	$usehex = $skyuc->GPC ['usehex'];
	$usezip = $skyuc->GPC ['usezip'];

	$dump->is_short = $skyuc->GPC ['ext_insert'];
	$dump->usehex = $skyuc->GPC ['usehex'];

	if ($usezip) {
		require_once (DIR . '/includes/class_phpzip.php');
	}

	// 变量验证
	$allow_max_size = intval ( @ini_get ( 'upload_max_filesize' ) ); //单位M
	if ($allow_max_size > 0 && $max_size > ($allow_max_size * 1024)) {
		$max_size = $allow_max_size * 1024; //单位K
	}

	if ($max_size > 0) {
		$dump->max_size = $max_size * 1024;
	}

	// 获取要备份数据列表
	$tables = array ();
	$temp = array ();
	switch ($skyuc->GPC ['type']) {
		case 'full' :
			$except = array (TABLE_PREFIX . 'session', TABLE_PREFIX . 'cpsession' );
			$res = $db->query_read ( "SHOW TABLES LIKE '" . $db->escape_string_like ( TABLE_PREFIX ) . "%'" );
			while ( $row = $db->fetch_row ( $res ) ) {
				$temp [] = $row [0];
			}
			unset ( $row );
			foreach ( $temp as $table ) {
				if (in_array ( $table, $except )) {
					continue;
				}
				$tables [$table] = - 1;
			}
			$dump->put_tables_list ( $run_log, $tables );
			break;

		case 'stand' :
			$temp = array ('account_log', 'ad', 'adsense', 'ad_position', 'admin_message', 'admin', 'admin_action', 'adminutil', 'article', 'article_cat', 'card', 'card_log', 'comment', 'category', 'datastore', 'setting', 'feedback', 'friend_link', 'co_note', 'co_media', 'nav', 'netbar', 'player', 'payment', 'pay_log', 'order_info', 'server', 'show', 'show_cat', 'show_article', 'template_mail', 'template', 'vote', 'vote_option', 'vote_log', 'users', 'user_account', 'user_rank', 'subject', 'tag' );
			foreach ( $temp as $table ) {
				$tables [TABLE_PREFIX . $table] = - 1;
			}
			$dump->put_tables_list ( $run_log, $tables );
			break;

		case 'min' :
			$temp = array ('adminutil', 'category', 'datastore', 'player', 'nav', 'server', 'show', 'show_cat', 'setting', 'subject', 'tag', 'template', 'template_mail' );
			foreach ( $temp as $table ) {
				$tables [TABLE_PREFIX . $table] = - 1;
			}
			$dump->put_tables_list ( $run_log, $tables );
			break;
		case 'custom' :
			foreach ( $skyuc->GPC ['customtables'] as $table ) {
				$tables [$table] = - 1;
			}
			$dump->put_tables_list ( $run_log, $tables );
			break;
	}

	//开始备份
	$tables = $dump->dump_table ( $run_log, $vol );

	if ($tables === false) {
		die ( $dump->errorMsg () );
	}

	$dumpfilename = sprintf ( $sql_file_name . "-%s" . '.sql', $vol );
	if (empty ( $tables )) {
		// 备份结束
		if ($vol > 1) {
			// 有多个文件
			if (! @file_put_contents ( DIR . '/data/sqldata/' . $dumpfilename, $dump->dump_sql )) {
				sys_msg ( sprintf ( $_LANG ['fail_write_file'], $dumpfilename ), 1, array (array ('text' => $_LANG ['01_db_manage'], 'href' => 'database.php?act=backup' ) ), false );
			}
			$list = array ();
			if ($usezip == 1) {
				$zip = new zipfile ();
				$zipfilename = DIR . '/data/sqldata/' . $sql_file_name . '.zip';
				$unlinks = '';
				for($i = 1; $i <= $vol; $i ++) {
					$filename = DIR . '/data/sqldata/' . sprintf ( $sql_file_name . "-%s" . '.sql', $i );
					$fp = fopen ( $filename, 'r' );
					$content = @fread ( $fp, filesize ( $filename ) );
					fclose ( $fp );
					$zip->addFile ( $content, basename ( $filename ) );
					$unlinks .= "@unlink('$filename');";

				}
				$fp = fopen ( $zipfilename, 'w' );
				if (@fwrite ( $fp, $zip->file () ) !== FALSE) {
					eval ( $unlinks );
				}
				unset ( $zip, $content );
				fclose ( $fp );

				$list [] = array ('name' => $sql_file_name . '.zip', 'href' => '../data/sqldata/' . $sql_file_name . '.zip' );
			} elseif ($usezip == 2) {

				for($i = 1; $i <= $vol; $i ++) {
					$filename = DIR . '/data/sqldata/' . sprintf ( $sql_file_name . "-%s" . '.sql', $i );
					$fp = fopen ( $filename, 'r' );
					$content = @fread ( $fp, filesize ( $filename ) );
					fclose ( $fp );
					$zip = new zipfile ();
					$zip->addFile ( $content, basename ( $filename ) );

					$zipfilename = DIR . '/data/sqldata/' . sprintf ( $sql_file_name . "-%s" . '.zip', $i );
					$fp = fopen ( $zipfilename, 'w' );
					if (@fwrite ( $fp, $zip->file () ) !== FALSE) {
						@unlink ( $filename );
					}
					unset ( $zip, $content );
					fclose ( $fp );

					$file_name = sprintf ( $sql_file_name . "-%s" . '.zip', $i );
					$list [] = array ('name' => $file_name, 'href' => '../data/sqldata/' . $file_name );
				}

			} else {
				for($i = 1; $i <= $vol; $i ++) {
					$file_name = sprintf ( $sql_file_name . '-' . $i . '.sql', $i );
					$list [] = array ('name' => $file_name, 'href' => '../data/sqldata/' . $file_name );
				}
			}

			$smarty->assign ( 'list', $list );
			$smarty->assign ( 'title', $_LANG ['backup_success'] );
			$smarty->display ( 'db_dump_msg.tpl' );
		} else {
			// 只有一个文件
			$dumpfilename = DIR . '/data/sqldata/' . $sql_file_name . '.sql';
			$filename = $sql_file_name . '.sql';
			if (! @file_put_contents ( $dumpfilename, $dump->dump_sql )) {
				sys_msg ( sprintf ( $_LANG ['fail_write_file'], $dumpfilename ), 1, array (array ('text' => $_LANG ['01_db_manage'], 'href' => 'database.php?act=backup' ) ), false );
			}
			;
			if ($usezip > 0) {
				$filename = $sql_file_name . '.zip';
				$fp = fopen ( $dumpfilename, 'r' );
				$content = @fread ( $fp, filesize ( $dumpfilename ) );
				fclose ( $fp );
				$zip = new zipfile ();
				$zip->addFile ( $content, basename ( $dumpfilename ) );
				$fp = fopen ( DIR . '/data/sqldata/' . $sql_file_name . '.zip', 'w' );
				if (@fwrite ( $fp, $zip->file () ) !== FALSE) {
					@unlink ( $dumpfilename );
				}
				fclose ( $fp );
				unset ( $zip, $content );
			}

			$list [] = array ('name' => $filename, 'href' => '../data/sqldata/' . $filename );

			$smarty->assign ( 'list', $list );
			$smarty->assign ( 'title', $_LANG ['backup_success'] );
			$smarty->display ( 'db_dump_msg.tpl' );
		}
	} else {
		// 下一个页面处理
		if (! @file_put_contents ( DIR . '/data/sqldata/' . $dumpfilename, $dump->dump_sql )) {
			sys_msg ( sprintf ( $_LANG ['fail_write_file'], $dumpfilename ), 1, array (array ('text' => $_LANG ['01_db_manage'], 'href' => 'database.php?act=backup' ) ), false );
		}

		$lnk = 'database.php?act=dumpsql&sql_file_name=' . $sql_file_name . '&vol_size=' . $max_size . '&vol=' . ($vol + 1) . '&usehex=' . $usehex . '&usezip= ' . $usezip;
		$smarty->assign ( 'title', sprintf ( $_LANG ['backup_title'], '#' . $vol ) );
		$smarty->assign ( 'auto_redirect', 1 );
		$smarty->assign ( 'auto_link', $lnk );
		$smarty->display ( 'db_dump_msg.tpl' );
	}
}

/* 删除备份 */
if ($skyuc->GPC ['act'] == 'remove') {
	// 权限判断
	admin_priv ( 'db_backup' );

	$skyuc->input->clean_gpc ( 'p', 'file', TYPE_ARRAY_STR );

	if ($skyuc->GPC_exists ['file']) {
		$m_file = array (); //多卷文件
		$s_file = array (); //单卷文件


		$path = DIR . '/data/sqldata/';

		foreach ( $skyuc->GPC ['file'] as $file ) {
			if (preg_match ( '/-[0-9]+\.sql$/', $file )) {
				$m_file [] = substr ( $file, 0, strpos ( $file, '-' ) );
			} else {
				$s_file [] = $file;
			}
		}

		if ($m_file) {
			$m_file = array_unique ( $m_file );

			// 获取文件列表
			$real_file = array ();

			$folder = opendir ( $path );
			while ( $file = readdir ( $folder ) ) {
				if (preg_match ( '/-[0-9]+\.sql$/', $file ) && is_file ( $path . $file )) {
					$real_file [] = $file;
				}
			}

			foreach ( $real_file as $file ) {
				$short_file = substr ( $file, 0, strpos ( $file, '-' ) );
				if (in_array ( $short_file, $m_file )) {
					@unlink ( $path . $file );
				}
			}
		}

		if ($s_file) {
			foreach ( $s_file as $file ) {
				@unlink ( $path . $file );
			}
		}
	}

	sys_msg ( $_LANG ['remove_success'], 0, array (array ('text' => $_LANG ['restore'], 'href' => 'database.php?act=restore' ) ) );
}
/*------------------------------------------------------ */
//-- 从服务器上解压ZIP
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'importzip') {
	// 权限判断
	admin_priv ( 'db_renew' );

	$file_name = $skyuc->input->clean_gpc ( 'g', 'file_name', TYPE_STR );
	$path = DIR . '/data/sqldata/';

	if (! is_file ( $path . $file_name ) || ! preg_match ( '#\.zip$#iu', $file_name )) {
		sys_msg ( sprintf ( $_LANG ['no_zip_title'], $file_name ), 1, array (array ('text' => $_LANG ['restore'], 'href' => 'database.php?act=restore' ) ), false );
	}

	require_once DIR . '/includes/class_phpzip.php';
	$unzip = new SimpleUnzip ();
	$unzip->ReadFile ( $path . $file_name );
	if ($unzip->Count () == 0 || $unzip->GetError ( 0 ) != 0 || ! preg_match ( "/\.sql$/i", $importfile = $unzip->GetName ( 0 ) )) {
		sys_msg ( sprintf ( $_LANG ['no_zip_title'], $file_name ), 1, array (array ('text' => $_LANG ['restore'], 'href' => 'database.php?act=restore' ) ), false );

	}
	$sqlfilecount = 0;
	foreach ( $unzip->Entries as $entry ) {
		if (preg_match ( "/\.sql$/i", $entry->Name )) {
			$fp = fopen ( $path . $entry->Name, 'w' );
			fwrite ( $fp, $entry->Data );
			fclose ( $fp );
			$sqlfilecount ++;
		}
	}

	if ($sqlfilecount > 0) {
		sys_msg ( $_LANG ['unzip_success'], 0, array (array ('text' => $_LANG ['restore'], 'href' => 'database.php?act=restore' ) ) );

	}

}
/*------------------------------------------------------ */
//-- 从服务器上导入数据
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'import') {
	// 权限判断
	admin_priv ( 'db_renew' );

	$skyuc->input->clean_array_gpc ( 'g', array ('confirm' => TYPE_BOOL, 'file_name' => TYPE_STR ) );

	$is_confirm = $skyuc->GPC ['confirm'];
	$file_name = $skyuc->GPC ['file_name'];
	$path = DIR . '/data/sqldata/';

	@set_time_limit ( 0 );

	if (preg_match ( '/-[0-9]+\.sql$/', $file_name )) {
		//多卷处理
		if (! is_file ( $path . $file_name )) {
			$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', 'show.dwt' ) );
			build_options ();
			sys_msg ( $_LANG ['restore_success'], 0, array (array ('text' => $_LANG ['restore'], 'href' => 'database.php?act=restore' ) ) );

		}

		$info = SqlDump::get_head ( $path . $file_name );
		if ($is_confirm == 0 && $info ['vol'] == 1) {
			//提示用户要求确认
			sys_msg ( $_LANG ['confirm_import'], 1, array (array ('text' => $_LANG ['also_continue'], 'href' => 'database.php?act=import&confirm=1&file_name=' . $file_name ) ), false );
		}

		$short_name = substr ( $file_name, 0, strpos ( $file_name, '-' ) );

		// 获取文件列表
		$real_file = array ();
		$folder = opendir ( $path );
		while ( $file = readdir ( $folder ) ) {
			if (is_file ( $path . $file ) && preg_match ( '/-[0-9]+\.sql$/', $file )) {
				$real_file [] = $file;
			}
		}

		// 所有相同分卷数据列表
		$post_list = array ();
		foreach ( $real_file as $file ) {
			$tmp_name = substr ( $file, 0, strpos ( $file, '-' ) );
			if ($tmp_name == $short_name) {
				$post_list [] = $file;
			}
		}

		natsort ( $post_list );

		if ($info ['vol'] == 1) {
			if ($info ['skyuc_ver'] != VERSION) {
				sys_msg ( sprintf ( $_LANG ['version_error'], VERSION, $sql_info ['skyuc_ver'] ) );
			}
		}

		if (! sql_import ( $path . $file_name )) {
			sys_msg ( $_LANG ['sqlfile_error'], 1 );
		}

		$datafile_next = preg_replace ( "/-($info[vol])(\..+)$/", "-" . ($info ['vol'] + 1) . "\\2", $file_name );

		if (in_array ( $file_name, $post_list )) {

			$lnk = 'database.php?act=import&file_name=' . $datafile_next;
			$smarty->assign ( 'title', sprintf ( $_LANG ['restore_title'], '#' . $info ['vol'] ) );
			$smarty->assign ( 'auto_redirect', 1 );
			$smarty->assign ( 'auto_link', $lnk );
			$smarty->display ( 'db_dump_msg.tpl' );
		} else {
			$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', 'show.dwt' ) );
			build_options ();
			sys_msg ( $_LANG ['restore_success'], 0, array (array ('text' => $_LANG ['restore'], 'href' => 'database.php?act=restore' ) ) );
		}

	} else {
		// 单卷
		$info = SqlDump::get_head ( $path . $file_name );
		if ($info ['skyuc_ver'] != VERSION) {
			sys_msg ( sprintf ( $_LANG ['version_error'], VERSION, $sql_info ['skyuc_ver'] ) );
		}
		if (sql_import ( $path . $file_name )) {
			$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', 'show.dwt' ) );
			admin_log ( $_LANG ['backup_time'] . $info ['date'], 'restore', 'db_backup' );
			sys_msg ( $_LANG ['restore_success'], 0, array (array ('text' => $_LANG ['restore'], 'href' => 'database.php?act=restore' ) ) );
		} else {
			sys_msg ( $_LANG ['sqlfile_error'], 1 );
		}
	}
}

/*------------------------------------------------------ */
//-- 上传sql 文件
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'upload_sql') {
	// 权限判断
	admin_priv ( 'db_renew' );

	$sql_file = DIR . '/data/sqldata/upload_database_bak.sql';
	$mysql_ver_confirm = $skyuc->input->clean_gpc ( 'g', 'mysql_ver_confirm', TYPE_UINT );

	if (empty ( $mysql_ver_confirm )) {
		if (empty ( $_FILES ['sqlfile'] )) {
			sys_msg ( $_LANG ['empty_upload'], 1 );
		}

		$file = $_FILES ['sqlfile'];

		//检查上传是否成功
		if ((isset ( $file ['error'] ) && $file ['error'] > 0) || (! isset ( $file ['error'] ) && $file ['tmp_name'] == 'none')) {
			sys_msg ( $_LANG ['fail_upload'], 1 );
		}

		// 检查文件格式
		if ($file ['type'] == 'application/x-zip-compressed') {
			sys_msg ( $_LANG ['not_support_zip_format'], 1 );
		}

		if (! preg_match ( "/\.sql$/i", $file ['name'] )) {
			sys_msg ( $_LANG ['not_sql_file'], 1 );
		}

		//将文件移动到临时目录，避免权限问题
		@unlink ( $sql_file );
		if (! move_upload_file ( $file ['tmp_name'], $sql_file )) {
			sys_msg ( $_LANG ['fail_upload_move'], 1 );
		}
	}

	// 获取sql文件头部信息
	$sql_info = SqlDump::get_head ( $sql_file );

	//如果备份文件的SKYUC!系统与现有SKYUC!系统版本不同则拒绝执行
	if (empty ( $sql_info ['skyuc_ver'] )) {
		sys_msg ( $_LANG ['unrecognize_version'], 1 );
	} else {
		if ($sql_info ['skyuc_ver'] != VERSION) {
			sys_msg ( sprintf ( $_LANG ['version_error'], VERSION, $sql_info ['skyuc_ver'] ) );
		}
	}

	//检查数据库版本是否正确
	if (empty ( $mysql_ver_confirm )) {
		if (empty ( $sql_info ['mysql_ver'] )) {
			sys_msg ( $_LANG ['unrecognize_mysql_version'] );
		} else {
			$mysql_ver_arr = $db->version ();
			if ($sql_info ['mysql_ver'] != $mysql_ver_arr) {
				$lnk = array ();
				$lnk [] = array ('text' => $_LANG ['confirm_ver'], 'href' => 'database.php?act=upload_sql&mysql_ver_confirm=1' );
				$lnk [] = array ('text' => $_LANG ['unconfirm_ver'], 'href' => 'database.php?act=restore' );
				sys_msg ( sprintf ( $_LANG ['mysql_version_error'], $mysql_ver_arr, $sql_info ['mysql_ver'] ), 0, $lnk, false );
			}
		}
	}

	@set_time_limit ( 0 );

	if (sql_import ( $sql_file )) {
		$skyuc->secache->setModified ( array ('index.dwt', 'list.dwt', 'show.dwt', 'search.dwt', 'article.dwt', 'article_cat.dwt' ) );
		@unlink ( $sql_file );
		sys_msg ( $_LANG ['restore_success'], 0, array () );
	} else {
		@unlink ( $sql_file );
		sys_msg ( $_LANG ['sqlfile_error'], 1 );
	}
}

/*------------------------------------------------------ */
//-- 优化页面
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'optimize') {
	// 初始化数据
	$db_ver_arr = $db->version ();
	$db_ver = $db_ver_arr;
	$ret = $db->query_read ( "SHOW TABLE STATUS LIKE '" . $db->escape_string_like ( TABLE_PREFIX ) . "%'" );

	$num = 0;
	$list = array ();
	while ( $row = $db->fetch_array ( $ret ) ) {
		if (strpos ( $row ['Name'], 'session' ) !== false) {
			$res ['Msg_text'] = 'Ignore';
			$row ['Data_free'] = 'Ignore';
		} else {
			$res = $db->query_first ( 'CHECK TABLE ' . $row ['Name'] );
			$num += $row ['Data_free'];
		}
		$type = $db_ver >= '4.1' ? $row ['Engine'] : $row ['Type'];
		$charset = $db_ver >= '4.1' ? $row ['Collation'] : 'N/A';
		$list [] = array ('table' => $row ['Name'], 'type' => $type, 'rec_num' => $row ['Rows'], 'rec_size' => sprintf ( " %.2f KB", $row ['Data_length'] / 1024 ), 'rec_index' => $row ['Index_length'], 'rec_chip' => $row ['Data_free'], 'status' => $res ['Msg_text'], 'charset' => $charset );
	}
	unset ( $ret );

	// 赋值
	assign_query_info ();
	$smarty->assign ( 'list', $list );
	$smarty->assign ( 'num', $num );
	$smarty->assign ( 'ur_here', $_LANG ['03_db_optimize'] );
	$smarty->display ( 'optimize.tpl' );
}

if ($skyuc->GPC ['act'] == 'run_optimize') {
	$skyuc->input->clean_gpc ( 'p', 'num', TYPE_UINT );
	$tables = array ();
	$res = $db->query_read ( "SHOW TABLES LIKE '" . $db->escape_string_like ( TABLE_PREFIX ) . "%'" );
	while ( $row = $db->fetch_row ( $res ) ) {
		$tables [] = $row [0];
	}
	unset ( $row );

	foreach ( $tables as $table ) {
		if ($row = $db->query_first ( 'OPTIMIZE TABLE ' . $table )) {
			//优化出错，尝试修复
			if ($row ['Msg_type'] == 'error' && strpos ( $row ['Msg_text'], 'repair' ) !== false) {
				$db->query_write ( 'REPAIR TABLE ' . $table );
			}
		}
	}

	sys_msg ( sprintf ( $_LANG ['optimize_ok'], $skyuc->GPC ['num'] ), 0, array (array ('text' => $_LANG ['go_back'], 'href' => 'database.php?act=optimize' ) ) );
}

/**
 * 导入SQL备分文件入库
 *
 * @access  public
 * @param
 *
 * @return void
 */
function sql_import($sql_file) {
	global $skyuc;

	$db_ver = $skyuc->db->version ();

	$sql_str = array_filter ( file ( $sql_file ), 'remove_comment' );
	$sql_str = str_replace ( "\r", '', implode ( '', $sql_str ) );

	$ret = explode ( ";\n", $sql_str );
	$ret_count = count ( $ret );

	// 执行sql语句
	for($i = 0; $i < $ret_count; $i ++) {
		$ret [$i] = trim ( $ret [$i], " \n;" ); //剔除多余信息
		if (! empty ( $ret [$i] )) {
			if ((strpos ( $ret [$i], 'CREATE TABLE' ) !== false) && (strpos ( $ret [$i], 'DEFAULT CHARSET=utf8' ) === false)) {
				/* 建表时缺 DEFAULT CHARSET=utf8 */
				$ret [$i] = $ret [$i] . ' DEFAULT CHARSET=utf8';
			}
			$skyuc->db->query_write ( $ret [$i] );
		}
	}

	return true;
}

/**
 * 将字节转成可阅读格式
 *
 * @access  public
 * @param
 *
 * @return void
 */
function num_bitunit($num) {
	$bitunit = array (' B', ' KB', ' MB', ' GB' );
	for($key = 0, $count = count ( $bitunit ); $key < $count; $key ++) {
		if ($num >= pow ( 2, 10 * $key ) - 1) // 1024B 会显示为 1KB
{
			$num_bitunit_str = (ceil ( $num / pow ( 2, 10 * $key ) * 100 ) / 100) . " $bitunit[$key]";
		}
	}

	return $num_bitunit_str;
}

/**
 * 删除SQL备分文件中的注释
 *
 * @access  public
 * @param
 * @return  void
 */
function remove_comment($var) {
	return (substr ( $var, 0, 2 ) != '--');
}

?>