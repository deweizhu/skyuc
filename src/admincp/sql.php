<?php

/**
 * SKYUC! SQL查询程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

$skyuc->input->clean_gpc ( 'p', 'sql', TYPE_STR );

/*------------------------------------------------------ */
//-- SQL查询页面
/*------------------------------------------------------ */

if ($skyuc->GPC ['act'] == 'main' || $skyuc->GPC ['act'] == '') {
	//检查权限：只有超级管理员（安装本系统的人）才可以执行此操作
	admin_priv ( 'all' );
	assign_query_info ();
	$smarty->assign ( 'type', - 1 );
	$smarty->assign ( 'ur_here', $_LANG ['04_sql_query'] );

	$smarty->display ( 'sql.tpl' );
}

elseif ($skyuc->GPC ['act'] == 'query') {
	admin_priv ( 'all' );
	assign_sql ( $skyuc->GPC ['sql'] );
	assign_query_info ();
	$smarty->assign ( 'ur_here', $_LANG ['04_sql_query'] );

	$smarty->display ( 'sql.tpl' );
} /*------------------------------------------------------ */
//-- 替换页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'replace') {
	admin_priv ( 'all' );

	$tablopt = '';

	$res = $db->query_read ( "SHOW TABLE STATUS LIKE '" . $db->escape_string_like ( TABLE_PREFIX ) . "%'" );
	while ( $tab = $db->fetch_array ( $res ) ) {
		$tablopt .= "<option value='" . $tab ['Name'] . "'>" . $tab ['Name'] . "</option>";
	}

	assign_query_info ();

	$smarty->assign ( 'ur_here', $_LANG ['05_sql_replace'] );
	$smarty->assign ( 'tablopt', $tablopt );
	$smarty->assign ( 'type', - 1 );
	$smarty->display ( 'sql_replace.tpl' );
} /*------------------------------------------------------ */
//-- 执行替换
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'run_replace') {
	admin_priv ( 'all' );

	$skyuc->input->clean_array_gpc ( 'r', array ('selTables' => TYPE_STR, 'selFields' => TYPE_STR, 'replace_mode' => TYPE_UINT, 'search' => TYPE_STR, 'replace' => TYPE_STR, 'addstr' => TYPE_STR, 'condition' => TYPE_STR ) );

	$fromtable = $skyuc->GPC ['selTables']; //替换表
	$fromfield = $skyuc->GPC ['selFields']; //替换字段
	$replace_mode = $skyuc->GPC ['replace_mode']; //替换类型
	$search = $skyuc->GPC ['search']; //搜索字符
	$replace = $skyuc->GPC ['replace']; //替换为
	$addstr = $skyuc->GPC ['addstr']; //追加字符
	$condition = $skyuc->GPC ['condition']; //替换条件


	if ($fromtable == '') {
		sys_msg ( $_LANG ['database_replace_table_invalid'] );
	}
	if ($fromfield == '') {
		sys_msg ( $_LANG ['database_replace_field_invalid'] );
	}

	$rst = $db->query_read ( 'SHOW COLUMNS FROM ' . $fromtable );
	while ( $p = $db->fetch_array ( $rst ) ) {
		if ($p ['Key'] == 'PRI') {
			$priid = $p ['Field'];
			break;
		}
	}
	if (! $priid) {
		sys_msg ( $_LANG ['database_replace_primary_invalid'] );
	}

	$condition = iif ( $condition, 'where ' . $condition, '' );
	$sql = 'SELECT ' . $fromfield . ',' . $priid . ' FROM ' . $fromtable . ' ' . $condition;
	if ($replace_mode == 1) {
		if ($search == '') {
			sys_msg ( $_LANG ['database_replace_content_invalid'] );
		}
		$result = $db->query ( $sql );
		while ( $r = $db->fetch_array ( $result ) ) {
			$r ["$fromfield"] = str_replace ( $search, $replace, $r ["$fromfield"] );
			$r ["$fromfield"] = $db->escape_string ( $r ["$fromfield"] );
			$db->query_write ( 'UPDATE ' . $fromtable . ' SET ' . $fromfield . "='" . $r ["$fromfield"] . "' WHERE " . $priid . "='" . $r ["$priid"] . "'" );
		}
		sys_msg ( $_LANG ['database_replace_succeed'] );
	} elseif ($replace_mode == 2) {
		if ($addstr == '') {
			sys_msg ( $_LANG ['database_replace_prefix_invalid'] );
		}

		$result = $db->query_read ( $sql );
		while ( $r = $db->fetch_array ( $result ) ) {
			$r ["$fromfield"] = $addstr . $r ["$fromfield"];
			$r ["$fromfield"] = $db->escape_string ( $r ["$fromfield"] );
			$db->query_write ( 'UPDATE ' . $fromtable . ' SET ' . $fromfield . "='" . $r ["$fromfield"] . "' WHERE " . $priid . "='" . $r ["$priid"] . "'" );
		}
		sys_msg ( $_LANG ['database_replace_succeed'] );
	} elseif ($replace_mode == 3) {
		if ($addstr == '') {
			sys_msg ( $_LANG ['database_replace_prefix_invalid'] );
		}
		$result = $db->query ( $sql );
		while ( $r = $db->fetch_array ( $result ) ) {
			$r ["$fromfield"] = $r ["$fromfield"] . $addstr;
			$r ["$fromfield"] = $db->escape_string ( $r ["$fromfield"] );
			$db->query_write ( 'UPDATE ' . $fromtable . ' SET ' . $fromfield . "='" . $r ["$fromfield"] . "' WHERE " . $priid . "='" . $r ["$priid"] . "'" );
		}
		sys_msg ( $_LANG ['database_replace_succeed'] );
	}

} /*------------------------------------------------------ */
//-- AJAX 列出指定表下所有列
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'getfields') {
	// 检查权限：只有超级管理员（安装本系统的人）才可以执行此操作
	check_authz_json ( 'all' );
	require (DIR . '/includes/class_json.php');

	$skyuc->input->clean_array_gpc ( 'r', array ('parent' => TYPE_STR, 'target' => TYPE_STR ) );

	header ( 'Content-type: text/html; charset=utf-8' );

	$parent = $skyuc->GPC ['parent'];
	$target = $skyuc->GPC ['target'];
	$tablename = $parent;

	$fields = '';
	$sql = 'SHOW COLUMNS FROM ' . $tablename;
	$result = $db->query_read ( $sql );
	while ( $fil = $db->fetch_array ( $result ) ) {
		$fields .= $fil ['Field'] . ',';
	}
	$fields = substr ( $fields, 0, - 1 );

	$arr ['regions'] = $fields;
	$arr ['target'] = $target;

	$json = new JSON ();
	echo $json->encode ( $arr );
}

?>
