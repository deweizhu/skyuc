<?php

/**
 * SKYUC! 验证码管理程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname ( __FILE__ ) . '/global.php');

/*------------------------------------------------------ */
//-- 验证码设置
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'main') {

	assign_query_info ();
	// 验证码选项
	$hv = intval ( $skyuc->options ['humanverify'] );
	$hv_check = array ();
	if ($hv & HV_REGISTER) {
		$hv_check ['register'] = 'checked="checked"';
	}
	if ($hv & HV_LOGIN) {
		$hv_check ['login'] = 'checked="checked"';
	}
	if ($hv & HV_COMMENT) {
		$hv_check ['comment'] = 'checked="checked"';
	}
	if ($hv & HV_ADMIN) {
		$hv_check ['admin'] = 'checked="checked"';
	}
	if ($hv & HV_MESSAGE) {
		$hv_check ['message'] = 'checked="checked"';
	}

	// 图像选项
	$rio = intval ( $skyuc->options ['regimageoption'] );
	$rio_check = array ();
	if ($rio & 1) {
		$rio_check ['random_font'] = 'checked="checked"';
	}
	if ($rio & 2) {
		$rio_check ['random_fontsize'] = 'checked="checked"';
	}
	if ($rio & 4) {
		$rio_check ['random_slant'] = 'checked="checked"';
	}
	if ($rio & 8) {
		$rio_check ['random_color'] = 'checked="checked"';
	}
	if ($rio & 16) {
		$rio_check ['random_shape'] = 'checked="checked"';
	}

	require_once (DIR . '/includes/class_humanverify.php');
	$verification = & HumanVerify::fetch_library ( $skyuc );
	$human_verify = $verification->generate_token ();

	$smarty->assign ( 'humanverify', $human_verify );

	$smarty->assign ( 'hv', $hv_check );
	$smarty->assign ( 'rio', $rio_check );
	$smarty->assign ( 'regimageoption', $skyuc->options ['regimageoption'] );
	$smarty->assign ( 'ur_here', $_LANG ['humanverify_manage'] );
	$smarty->display ( 'humanverify.tpl' );
}

/*------------------------------------------------------ */
//-- 保存设置
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'save_config') {
	$skyuc->input->clean_array_gpc ( 'p', array ('hv_register' => TYPE_UINT, 'hv_login' => TYPE_UINT, 'hv_comment' => TYPE_UINT, 'hv_admin' => TYPE_UINT, 'hv_message' => TYPE_UINT, 'hv_login_fail' => TYPE_UINT, 'regimageoption' => TYPE_ARRAY_UINT ) );

	$humanverify = 0;
	$humanverify = iif ( empty ( $skyuc->GPC ['hv_register'] ), $humanverify, $humanverify | HV_REGISTER );
	$humanverify = iif ( empty ( $skyuc->GPC ['hv_login'] ), $humanverify, $humanverify | HV_LOGIN );
	$humanverify = iif ( empty ( $skyuc->GPC ['hv_comment'] ), $humanverify, $humanverify | HV_COMMENT );
	$humanverify = iif ( empty ( $skyuc->GPC ['hv_admin'] ), $humanverify, $humanverify | HV_ADMIN );
	$humanverify = iif ( empty ( $skyuc->GPC ['hv_login_fail'] ), $humanverify, $humanverify | HV_LOGIN_FAIL );
	$humanverify = iif ( empty ( $skyuc->GPC ['hv_message'] ), $humanverify, $humanverify | HV_MESSAGE );

	$regimageoption = 0;
	$regimageoption = iif ( $skyuc->GPC ['regimageoption'] [1] == 0, $regimageoption, $regimageoption | 1 );
	$regimageoption = iif ( $skyuc->GPC ['regimageoption'] [2] == 0, $regimageoption, $regimageoption | 2 );
	$regimageoption = iif ( $skyuc->GPC ['regimageoption'] [4] == 0, $regimageoption, $regimageoption | 4 );
	$regimageoption = iif ( $skyuc->GPC ['regimageoption'] [8] == 0, $regimageoption, $regimageoption | 8 );
	$regimageoption = iif ( $skyuc->GPC ['regimageoption'] [16] == 0, $regimageoption, $regimageoption | 16 );

	$sql = 'UPDATE ' . TABLE_PREFIX . 'setting' . " SET value='$humanverify' WHERE code='humanverify'";
	$db->query_write ( $sql );
	$sql = 'UPDATE ' . TABLE_PREFIX . 'setting' . " SET value='$regimageoption' WHERE code='regimageoption'";
	$db->query_write ( $sql );

	build_options ();

	sys_msg ( $_LANG ['save_ok'], 0, array (array ('href' => 'humanverify.php?act=main', 'text' => $_LANG ['humanverify_manage'] ) ) );
}

?>