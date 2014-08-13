<?php
/**
 * SKYUC! 等级管理
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

$exc = new exchange ( TABLE_PREFIX . 'user_rank', $skyuc->db, 'rank_id', 'rank_name' );
$exc_user = new exchange ( TABLE_PREFIX . 'users', $skyuc->db, 'user_rank', 'user_rank' );

/*------------------------------------------------------ */
//-- 会员等级列表
/*------------------------------------------------------ */

if ($skyuc->GPC ['act'] == 'list') {

	$smarty->assign ( 'ur_here', $_LANG ['05_user_rank_list'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['add_user_rank'], 'href' => 'user_rank.php?act=add' ) );
	$smarty->assign ( 'full_page', 1 );

	$smarty->assign ( 'user_ranks', get_rank_list () );

	assign_query_info ();
	$smarty->display ( 'user_rank.tpl' );
}

/*------------------------------------------------------ */
//-- 添加会员等级
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'add' || $skyuc->GPC ['act'] == 'edit') {
	admin_priv ( 'user_rank' );

	$ranks = array ();

	if ($skyuc->GPC ['act'] == 'add') {
		$rank['rank_id'] = 0;
		$rank['day_play'] = 50;
		$rank['day_down'] = 50;
		$rank['count'] = 30;
		$rank['money'] = 10;
		$rank['allow_cate'] = array ();
        $rank['allow_hours'] = '00:00-23:59';

		$form_action = 'insert';
	} else {
		$rank_id = $skyuc->input->clean_gpc( 'g', 'rank_id', TYPE_UINT );
		$ranks = get_rank_list ( $rank_id );
		$rank = $ranks[0];

		$form_action = 'update';
	}

	$cat_list = get_cat_list ( 0, 0, 0, 1 );

	$smarty->assign ( 'rank', $rank );
	$smarty->assign ( 'cat_list', $cat_list );
	$smarty->assign ( 'ur_here', $_LANG ['add_user_rank'] );
	$smarty->assign ( 'action_link', array ('text' => $_LANG ['05_user_rank_list'], 'href' => 'user_rank.php?act=list' ) );
	$smarty->assign ( 'ur_here', $_LANG ['add_user_rank'] );
	$smarty->assign ( 'form_action', $form_action );

	assign_query_info ();
	$smarty->display ( 'user_rank_info.tpl' );
}

/*------------------------------------------------------ */
//-- 增加或修改会员等级到数据库
/*------------------------------------------------------ */

elseif ($skyuc->GPC ['act'] == 'insert' || $skyuc->GPC ['act'] == 'update') {
	admin_priv ( 'user_rank' );

	$skyuc->input->clean_array_gpc ( 'p', array (
        'day_play' => TYPE_UINT,
        'day_down' => TYPE_UINT,
        'count' => TYPE_UINT,
        'money' => TYPE_UINT,
        'content' => TYPE_STR,
        'rank_name' => TYPE_STR,
        'allow_cate' => TYPE_ARRAY_UINT,
        'allow_hours' => TYPE_STR,
        'rank_type' => TYPE_UINT )
    );

	$skyuc->GPC['allow_cate'] = implode( ',', $skyuc->GPC ['allow_cate']);
    $skyuc->GPC['allow_hours'] = str_replace('.', ',', make_semiangle($skyuc->GPC['allow_hours']));

	if ($skyuc->GPC ['act'] == 'insert') {
		// 检查是否存在重名的会员等级
		if (! $exc->is_only ( 'rank_name', $skyuc->GPC ['rank_name'] )) {
			sys_msg ( sprintf ( $_LANG ['rank_name_exists'], $skyuc->GPC ['rank_name'] ), 1 );
		}
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'user_rank' .
            '(rank_name, rank_type, day_play, day_down, allow_cate, allow_hours, count, money, content) ' .
            " VALUES ('" . $db->escape_string ( $skyuc->GPC['rank_name'] ) . "', '" .
            $skyuc->GPC['rank_type'] . "', '" . $skyuc->GPC['day_play'] . "', " . "'" .
            $skyuc->GPC['day_down'] . "', '" . $skyuc->GPC['allow_cate'] . "', '" .
            $skyuc->GPC['allow_hours'] . "', '" . $skyuc->GPC['count'] . "', '" .
            $skyuc->GPC['money'] . "', '" . $db->escape_string ( $skyuc->GPC['content'] ) . "')";
		$db->query_write ( $sql );

		// 管理员日志
		admin_log ( $rank_name, 'add', 'user_rank' );
		build_usergroup ();

		$lnk [] = array ('text' => $_LANG ['back_list'], 'href' => 'user_rank.php?act=list' );
		$lnk [] = array ('text' => $_LANG ['add_continue'], 'href' => 'user_rank.php?act=add' );
		sys_msg ( $_LANG ['add_rank_success'], 0, $lnk );
	} else {
		$rank_id = $skyuc->input->clean_gpc ( 'p', 'id', TYPE_UINT );
		$sql = 'UPDATE ' . TABLE_PREFIX . 'user_rank' .
            " set rank_name='" . $db->escape_string ( $skyuc->GPC['rank_name'] ) .
            "', rank_type='" . $skyuc->GPC['rank_type'] .
            "', day_play='" . $skyuc->GPC['day_play'] .
            "', day_down='" . $skyuc->GPC['day_down'] .
            "', allow_cate='" . $skyuc->GPC['allow_cate'] .
            "', allow_hours='" . $skyuc->GPC['allow_hours'] .
            "', count='" . $skyuc->GPC['count'] .
            "', money='" . $skyuc->GPC['money'] .
            "', content='" . $db->escape_string ( $skyuc->GPC['content'] ) .
            "' WHERE rank_id='$rank_id'";
		$db->query_write ( $sql );

		// 管理员日志
		admin_log ( $skyuc->GPC ['rank_name'], 'edit', 'user_rank' );
		build_usergroup ();

		$lnk [] = array ('text' => $_LANG ['back_list'], 'href' => 'user_rank.php?act=list' );
		sys_msg ( $_LANG ['edit_rank_success'], 0, $lnk );

	}
}

/*------------------------------------------------------ */
//-- 删除会员等级
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'remove') {
	check_authz_json ( 'user_rank' );

	$rank_id = intval ( $_GET ['id'] );

	if ($exc->drop ( $rank_id )) {
		/* 更新会员表的等级字段 */
		$exc_user->edit ( "user_rank = 0", $rank_id );

		$rank_name = $exc->get_name ( $rank_id );
		admin_log ( addslashes ( $rank_name ), 'remove', 'user_rank' );
		build_usergroup ();
	}

	$url = 'user_rank.php?act=query&' . str_replace ( 'act=remove', '', $_SERVER ['QUERY_STRING'] );

	header ( "Location: $url\n" );
	exit ();

} /*
 *  编辑会员等级名称
 */
elseif ($skyuc->GPC ['act'] == 'edit_name') {
	$id = intval ( $_REQUEST ['id'] );
	$val = empty ( $_REQUEST ['val'] ) ? '' : trim ( $_REQUEST ['val'] );
	check_authz_json ( 'user_rank' );
	if ($exc->is_only ( 'rank_name', $val, $id )) {
		if ($exc->edit ( "rank_name = '$val'", $id )) {
			/* 管理员日志 */
			admin_log ( $val, 'edit', 'user_rank' );
			build_usergroup ();
			make_json_result ( stripcslashes ( $val ) );
		} else {
			make_json_error ( $db->error () );
		}
	} else {
		make_json_error ( sprintf ( $_LANG ['rank_name_exists'], htmlspecialchars ( $val ) ) );
	}
}

?>