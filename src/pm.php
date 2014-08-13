<?php

/**
 * SKYUC! 短消息文件
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

// ####################### 设置 PHP 环境 ###########################
error_reporting ( E_ALL & ~ E_NOTICE );

// #################### 定义重要常量 #######################
define ( 'THIS_SCRIPT', 'pm' );
define ( 'CSRF_PROTECTION', true );
define ( 'CSRF_SKIP_LIST', '' );
define ( 'SMARTY_CACHE', true );

require (dirname ( __FILE__ ) . '/global.php');

if ($skyuc->session->vars ['userid'] == 0) {
	header ( 'Location:./' );
}

uc_call ( 'uc_pm_location', array ($skyuc->session->vars ['userid'] ) );
//$ucnewpm = uc_pm_checknew($skyuc->session->vars['userid']);
//setcookie('checkpm', '');


?>