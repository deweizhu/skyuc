<?php
/**
 * SKYUC! 生成显示影片的js代码
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

/*------------------------------------------------------ */
//-- 生成代码
/*------------------------------------------------------ */

if ($skyuc->GPC ['act'] == 'setup') {
	// 检查权限
	admin_priv ( 'show_manage' );

	// 编码
	$lang_list = array ('UTF8' => $_LANG ['charset'] ['utf8'], 'GB2312' => $_LANG ['charset'] ['zh-cn'], 'BIG5' => $_LANG ['charset'] ['zh-tw'] );

	// 参数赋值
	$ur_here = $_LANG ['show_script'];
	$smarty->assign ( 'ur_here', $ur_here );
	$smarty->assign ( 'cat_list', get_cat_list () );
	$smarty->assign ( 'server_list', get_server_list () );
	$smarty->assign ( 'intro_list', $_LANG ['intro'] );
	$smarty->assign ( 'url', get_url () );
	$smarty->assign ( 'lang_list', $lang_list );

	assign_query_info ();
	$smarty->display ( 'gen_show_script.tpl' );
}

?>