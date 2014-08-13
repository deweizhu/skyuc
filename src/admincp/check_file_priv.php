<?php

/**
 * SKYUC SHOP 系统文件检测
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

if ($skyuc->GPC ['act'] == 'check') {
	//要检查目录文件列表
	$upload_img_dir = array ();
	$folder = opendir ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] );
	while ( $dir = readdir ( $folder ) ) {
		if (is_dir ( DIR . '/' . $skyuc->config ['Misc'] ['imagedir'] . '/' . $dir ) && preg_match ( '/^[0-9]{6}$/', $dir )) {
			$upload_img_dir [] = $skyuc->config ['Misc'] ['imagedir'] . '/' . $dir;
		}
	}
	closedir ( $folder );

	$dir [] = $skyuc->config ['Misc'] ['admincpdir'] . '/templates';
	$dir_subdir ['data'] [] = 'data';
	$dir_subdir ['data'] [] = 'data/compiled';
	$dir_subdir ['data'] [] = 'data/images';
	$dir_subdir ['data'] [] = 'data/compiled/admincp';
	$dir_subdir ['data'] [] = 'data/caches';
	$dir_subdir ['data'] [] = 'data/sqldata';
	$dir_subdir ['data'] [] = 'data/flashdata';
	$dir_subdir ['upload'] [] = $skyuc->config ['Misc'] ['imagedir'];
	$dir_subdir ['upload'] [] = $skyuc->config ['Misc'] ['imagedir'] . '/afficheimg';
	$dir_subdir ['upload'] [] = $skyuc->config ['Misc'] ['imagedir'] . '/article';
	$dir_subdir ['upload'] [] = $skyuc->config ['Misc'] ['imagedir'] . '/posters';

	// 将上传图片目录加入检查范围
	foreach ( $upload_img_dir as $val ) {
		$dir_subdir ['upload'] [] = $val;
	}

	$list = array ();

	// 检查目录
	foreach ( $dir as $val ) {
		$mark = file_mode_info ( DIR . '/' . $val );
		$list [] = array ('item' => $val . $_LANG ['dir'], 'r' => $mark & 1, 'w' => $mark & 2, 'm' => $mark & 4 );
	}

	// 检查目录及子目录
	$keys = array_unique ( array_keys ( $dir_subdir ) );
	foreach ( $keys as $key ) {
		$err_msg = array ();
		$mark = check_file_in_array ( $dir_subdir [$key], $err_msg );
		$list [] = array ('item' => $key . $_LANG ['dir_subdir'], 'r' => $mark & 1, 'w' => $mark & 2, 'm' => $mark & 4, 'err_msg' => $err_msg );
	}

	// 检查当前模板可写性
	$tpl = 'templates/' . $skyuc->options ['themes'] . '/';
	$dwt = @opendir ( DIR . '/' . $tpl );
	$tpl_file = array (); //获取要检查的文件
	while ( $file = readdir ( $dwt ) ) {
		if (is_file ( DIR . '/' . $tpl . $file ) && strrpos ( $file, '.tpl' ) > 0) {
			$tpl_file [] = $tpl . $file;
		}
	}
	@closedir ( $dwt );

	//开始检查
	$err_msg = array ();
	$mark = check_file_in_array ( $tpl_file, $err_msg );
	$list [] = array ('item' => $tpl . $_LANG ['tpl_file'], 'r' => $mark & 1, 'w' => $mark & 2, 'm' => $mark & 4, 'err_msg' => $err_msg );

	//检查缓存目录和编译目录及image目录是否有执行rename()函数的权限
	$tpl_list = array ();
	$tpl_dirs [] = 'data/sqlcaches';
	$tpl_dirs [] = 'data/compiled';
	$tpl_dirs [] = 'data/compiled/admincp';

	//将上传图片目录加入检查范围
	foreach ( $upload_img_dir as $val ) {
		$tpl_dirs [] = $val;
	}

	foreach ( $tpl_dirs as $dir ) {
		$mask = file_mode_info ( DIR . '/' . $dir );

		if (($mask & 4) > 0) {
			//之前已经检查过修改权限，只有有修改权限才检查rename权限
			if (($mask & 8) < 1) {
				$tpl_list [] = $dir;
			}
		}
	}
	$tpl_msg = implode ( ', ', $tpl_list );

	$smarty->assign ( 'list', $list );
	$smarty->assign ( 'tpl_msg', $tpl_msg );

	$smarty->display ( 'file_priv.tpl' );
}

/**
 * 检查数组中目录权限
 *
 * @access  public
 * @param   array    $arr           要检查的文件列表数组
 * @param   array    $err_msg       错误信息回馈数组
 *
 * @return int       $mark          文件权限掩码
 */
function check_file_in_array($arr, &$err_msg) {
	$read = true;
	$writen = true;
	$modify = true;
	foreach ( $arr as $val ) {
		$mark = file_mode_info ( DIR . '/' . $val );
		if (($mark & 1) < 1) {
			$read = false;
			$err_msg ['r'] [] = $val;
		}
		if (($mark & 2) < 1) {
			$writen = false;
			$err_msg ['w'] [] = $val;

		}
		if (($mark & 4) < 1) {
			$modify = false;
			$err_msg ['m'] [] = $val;
		}
	}

	$mark = 0;
	if ($read) {
		$mark ^= 1;
	}
	if ($writen) {
		$mark ^= 2;
	}
	if ($modify) {
		$mark ^= 4;
	}

	return $mark;
}

?>
