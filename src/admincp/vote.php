<?php

/**
 * SKYUC!  调查管理程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

$exc = new exchange ( TABLE_PREFIX . 'vote', $skyuc->db, 'vote_id', 'vote_name' );
$exc_opn = new exchange ( TABLE_PREFIX . 'vote_option', $skyuc->db, 'option_id', 'option_name' );

/*------------------------------------------------------ */
//-- 投票列表页面
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list' || $skyuc->GPC ['act'] == '') {
	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['list_vote'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['add_vote'], 'href' => 'vote.php?act=add' ) );
	$smarty->assign ( 'full_page', 1 );

	$vote_list = get_votelist ();

	$smarty->assign ( 'list', $vote_list ['list'] );
	$smarty->assign ( 'filter', $vote_list ['filter'] );
	$smarty->assign ( 'record_count', $vote_list ['record_count'] );
	$smarty->assign ( 'page_count', $vote_list ['page_count'] );

	// 显示页面
	assign_query_info ();
	$smarty->display ( 'vote_list.tpl' );
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
	$vote_list = get_votelist ();

	$smarty->assign ( 'list', $vote_list ['list'] );
	$smarty->assign ( 'filter', $vote_list ['filter'] );
	$smarty->assign ( 'record_count', $vote_list ['record_count'] );
	$smarty->assign ( 'page_count', $vote_list ['page_count'] );

	make_json_result ( $smarty->fetch ( 'vote_list.tpl' ), '', array ('filter' => $vote_list ['filter'], 'page_count' => $vote_list ['page_count'] ) );
}

/*------------------------------------------------------ */
//-- 添加新的投票页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'add') {
	// 权限检查
	admin_priv ( 'vote_priv' );

	// 日期初始化
	$vote_arr = array ();
	$vote_arr ['begin_date'] = skyuc_date ( $skyuc->options ['date_format'], TIMENOW, true, false );
	$vote_arr ['end_date'] = skyuc_date ( $skyuc->options ['date_format'], strtotime ( '+1 month' ), true, false );

	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['add_vote'] );
	$smarty->assign ( 'action_link', array ('href' => 'vote.php?act=list', 'text' => $_LANG ['list_vote'] ) );

	$smarty->assign ( 'action', 'add' );
	$smarty->assign ( 'form_act', 'insert' );
	$smarty->assign ( 'vote_arr', $vote_arr );

	// 显示页面
	assign_query_info ();
	$smarty->display ( 'vote_info.tpl' );
} elseif ($skyuc->GPC ['act'] == 'insert') {
	admin_priv ( 'vote_priv' );

	$skyuc->input->clean_array_gpc ( 'p', array ('begin_dateYear' => TYPE_UINT, 'begin_dateMonth' => TYPE_UINT, 'begin_dateDay' => TYPE_UINT, 'end_dateYear' => TYPE_UINT, 'end_dateMonth' => TYPE_UINT, 'end_dateDay' => TYPE_UINT, 'vote_name' => TYPE_STR, 'can_multi' => TYPE_BOOL ) );

	// 获得广告的开始时期与结束日期
	$begin_date = skyuc_mktime ( 0, 0, 0, $skyuc->GPC ['begin_dateMonth'], $skyuc->GPC ['begin_dateDay'], $skyuc->GPC ['begin_dateYear'] );
	$end_date = skyuc_mktime ( 0, 0, 0, $skyuc->GPC ['end_dateMonth'], $skyuc->GPC ['end_dateDay'], $skyuc->GPC ['end_dateYear'] );

	//查看广告名称是否有重复
	$sql = 'SELECT COUNT(*) AS total FROM  ' . TABLE_PREFIX . 'vote' . " WHERE vote_name='" . $skyuc->db->escape_string ( $skyuc->GPC ['vote_name'] ) . "'";
	$total = $skyuc->db->query_first_slave ( $sql );
	if ($total ['total'] == 0) {
		// 插入数据
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'vote' . ' (vote_name, begin_date, end_date, can_multi, vote_count) ' . " VALUES ('" . $skyuc->db->escape_string ( $skyuc->GPC ['vote_name'] ) . "', '$begin_date', '$end_date', '" . $skyuc->GPC ['can_multi'] . "', '0')";
		$skyuc->db->query_write ( $sql );

		$new_id = $skyuc->db->insert_id ();

		// 记录管理员操作
		admin_log ( $skyuc->GPC ['vote_name'], 'add', 'vote' );

		// 提示信息
		$link [0] ['text'] = $_LANG ['continue_add_option'];
		$link [0] ['href'] = 'vote.php?act=option&id=' . $new_id;

		$link [1] ['text'] = $_LANG ['continue_add_vote'];
		$link [1] ['href'] = 'vote.php?act=add';

		$link [2] ['text'] = $_LANG ['back_list'];
		$link [2] ['href'] = 'vote.php?act=list';

		sys_msg ( $_LANG ['add'] . "&nbsp;" . $_POST ['vote_name'] . "&nbsp;" . $_LANG ['attradd_succed'], 0, $link );

	} else {
		$link [] = array ('text' => $_LANG ['go_back'], 'href' => 'javascript:history.back(-1)' );
		sys_msg ( $_LANG ['vote_name_exist'], 0, $link );
	}
} /*------------------------------------------------------ */
//-- 在线调查编辑页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit') {
	admin_priv ( 'vote_priv' );

	$skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	// 获取数据
	$vote_arr = $skyuc->db->query_first_slave ( 'SELECT * FROM ' . TABLE_PREFIX . 'vote' . " WHERE vote_id='" . $skyuc->GPC ['id'] . "'" );

	/* 模板赋值 */
	$smarty->assign ( 'ur_here', $_LANG ['edit_vote'] );
	$smarty->assign ( 'action_link', array ('href' => 'vote.php?act=list', 'text' => $_LANG ['list_vote'] ) );
	$smarty->assign ( 'form_act', 'update' );
	$smarty->assign ( 'vote_arr', $vote_arr );

	assign_query_info ();
	$smarty->display ( 'vote_info.tpl' );
} elseif ($skyuc->GPC ['act'] == 'update') {
	$skyuc->input->clean_array_gpc ( 'p', array ('vote_name' => TYPE_STR, 'can_multi' => TYPE_BOOL, 'id' => TYPE_UINT, 'begin_dateYear' => TYPE_UINT, 'begin_dateMonth' => TYPE_UINT, 'begin_dateDay' => TYPE_UINT, 'end_dateYear' => TYPE_UINT, 'end_dateMonth' => TYPE_UINT, 'end_dateDay' => TYPE_UINT ) );

	// 获得广告的开始时期与结束日期
	$begin_date = skyuc_mktime ( 0, 0, 0, $skyuc->GPC ['begin_dateMonth'], $skyuc->GPC ['begin_dateDay'], $skyuc->GPC ['begin_dateYear'] );
	$end_date = skyuc_mktime ( 0, 0, 0, $skyuc->GPC ['end_dateMonth'], $skyuc->GPC ['end_dateDay'], $skyuc->GPC ['end_dateYear'] );

	// 更新信息
	$sql = 'UPDATE ' . TABLE_PREFIX . 'vote' . ' SET ' . "vote_name     = '" . $skyuc->db->escape_string ( $skyuc->GPC ['vote_name'] ) . "', " . "begin_date    = '$begin_date', " . "end_date      = '$end_date', " . "can_multi     = '" . $skyuc->db->escape_string ( $skyuc->GPC ['can_multi'] ) . "' " . 'WHERE vote_id = ' . $skyuc->GPC ['id'];
	$skyuc->db->query_write ( $sql );

	// 记录管理员操作
	admin_log ( $skyuc->GPC ['vote_name'], 'edit', 'vote' );

	// 提示信息
	$link [] = array ('text' => $_LANG ['back_list'], 'href' => 'vote.php?act=list' );
	sys_msg ( $_LANG ['edit'] . ' ' . $skyuc->GPC ['vote_name'] . ' ' . $_LANG ['attradd_succed'], 0, $link );
} /*------------------------------------------------------ */
//-- 调查选项列表页面
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'option') {

	$skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );

	// 模板赋值
	$smarty->assign ( 'ur_here', $_LANG ['list_vote_option'] );
	$smarty->assign ( 'action_link', array ('href' => 'vote.php?act=list', 'text' => $_LANG ['list_vote'] ) );
	$smarty->assign ( 'full_page', 1 );

	$smarty->assign ( 'id', $skyuc->GPC ['id'] );
	$smarty->assign ( 'option_arr', get_optionlist ( $skyuc->GPC ['id'] ) );

	// 显示页面
	assign_query_info ();
	$smarty->display ( 'vote_option.tpl' );
}

/*------------------------------------------------------ */
//-- 调查选项查询
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query_option') {
	$skyuc->input->clean_gpc ( 'g', 'vid', TYPE_UINT );

	$smarty->assign ( 'id', $skyuc->GPC ['vid'] );
	$smarty->assign ( 'option_arr', get_optionlist ( $skyuc->GPC ['vid'] ) );

	make_json_result ( $smarty->fetch ( 'vote_option.tpl' ) );
}

/*------------------------------------------------------ */
//-- 添加新调查选项
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'new_option') {
	check_authz_json ( 'vote_priv' );

	$skyuc->input->clean_array_gpc ( 'p', array ('option_name' => TYPE_STR, 'id' => TYPE_UINT ) );

	if (! empty ( $skyuc->GPC ['option_name'] )) {
		// 查看调查标题是否有重复
		$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'vote_option' . " WHERE option_name = '" . $skyuc->db->escape_string ( $skyuc->GPC ['option_name'] ) . "' AND vote_id = " . $skyuc->GPC ['id'];
		$total = $skyuc->db->query_first_slave ( $sql );
		if ($total ['total'] != 0) {
			make_json_error ( $_LANG ['vote_option_exist'] );
		} else {
			$sql = 'INSERT INTO ' . TABLE_PREFIX . 'vote_option' . ' (vote_id, option_name, option_count) ' . "VALUES ('" . $skyuc->GPC ['id'] . "', '" . $skyuc->db->escape_string ( $skyuc->GPC ['option_name'] ) . "', 0)";
			$skyuc->db->query_write ( $sql );

			admin_log ( $skyuc->GPC ['option_name'], 'add', 'vote' );

			$url = 'vote.php?act=query_option&vid=' . $skyuc->GPC ['id'] . '&' . str_replace ( 'act=new_option', '', $_SERVER ['QUERY_STRING'] );
			header ( "Location: $url\n" );
			exit ();
		}
	} else {
		make_json_error ( $_LANG ['js_languages'] ['option_name_empty'] );
	}

}

/*------------------------------------------------------ */
//-- 编辑调查主题
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_vote_name') {
	check_authz_json ( 'vote_priv' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$id = $skyuc->GPC ['id'];
	$vote_name = $skyuc->GPC ['val'];

	// 检查名称是否重复
	if ($exc->num ( 'vote_name', $vote_name, $id ) != 0) {
		make_json_error ( sprintf ( $_LANG ['vote_name_exist'], $vote_name ) );
	} else {
		if ($exc->edit ( "vote_name = '" . $skyuc->db->escape_string ( $vote_name ) . "'", $id )) {
			admin_log ( $vote_name, 'edit', 'vote' );
			make_json_result ( $vote_name );
		}
	}
}

/*------------------------------------------------------ */
//-- 编辑调查选项
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_option_name') {
	check_authz_json ( 'vote_priv' );

	$skyuc->input->clean_array_gpc ( 'p', array ('id' => TYPE_UINT, 'val' => TYPE_STR ) );

	$id = $skyuc->GPC ['id'];
	$option_name = $skyuc->GPC ['val'];

	// 检查名称是否重复
	$vote = $skyuc->db->query_first_slave ( 'SELECT vote_id FROM ' . TABLE_PREFIX . 'vote_option' . " WHERE option_id='$id'" );
	$vote_id = $vote ['vote_id'];

	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'vote_option' . " WHERE option_name = '" . $skyuc->db->escape_string ( $option_name ) . "' AND vote_id = '$vote_id' AND option_id <> $id";
	$total = $skyuc->db->query_first_slave ( $sql );
	if ($total ['total'] != 0) {
		make_json_error ( sprintf ( $_LANG ['vote_option_exist'], $option_name ) );
	} else {
		if ($exc_opn->edit ( "option_name = '" . $skyuc->db->escape_string ( $option_name ) . "'", $id )) {
			admin_log ( $option_name, 'edit', 'vote' );
			make_json_result ( $option_name );
		}
	}
}

/*------------------------------------------------------ */
//-- 删除在线调查主题
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'remove') {
	check_authz_json ( 'vote_priv' );

	$id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );
	if ($exc->drop ( $id )) {
		// 同时删除调查选项
		$skyuc->db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'vote_option' . " WHERE vote_id = '$id'" );

		admin_log ( '', 'remove', 'ads_position' );
	}

	$url = 'vote.php?act=query&' . str_replace ( 'act=remove', '', $_SERVER ['QUERY_STRING'] );

	header ( "Location: $url\n" );
	exit ();
}

/*------------------------------------------------------ */
//-- 删除在线调查选项
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'remove_option') {
	check_authz_json ( 'vote_priv' );

	$id = $skyuc->input->clean_gpc ( 'g', 'id', TYPE_UINT );
	$vote = $skyuc->db->query_first_slave ( 'SELECT vote_id FROM ' . TABLE_PREFIX . 'vote_option' . " WHERE option_id='$id'" );
	$vote_id = $vote ['vote_id'];
	if ($exc_opn->drop ( $id )) {
		admin_log ( '', 'remove', 'vote' );
	}

	$url = 'vote.php?act=query_option&vid=' . $vote_id . '&' . str_replace ( 'act=remove_option', '', $_SERVER ['QUERY_STRING'] );

	header ( "Location: $url\n" );
	exit ();
}

?>
