<?php
/**
 * SKYUC! 会员中心
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */
// ####################### 设置 PHP 环境 ###########################
error_reporting(E_ALL & ~ E_NOTICE);
// #################### 定义重要常量 #######################
define('THIS_SCRIPT', 'user');
define('CSRF_PROTECTION', true);
define('CSRF_SKIP_LIST', '');
define('IN_USER_PANEL', true);
// 从缓存中获取指定数据
$specialtemplates = array('players');
require (dirname(__FILE__) . '/global.php');
//载入语言文件
require_once (DIR . '/languages/' . $skyuc->options['lang'] . '/user.php');
$skyuc->input->clean_gpc('r', 'act', TYPE_STR);
$action = iif($skyuc->GPC_exists['act'], $skyuc->GPC['act'], 'default');
// 不需要登录的操作或自己验证是否登录（如ajax处理）的act
$not_login_arr = array('login', 'act_login', 'register', 'act_register',
'act_edit_password', 'get_password', 'send_pwd_email', 'password', 'signin',
'return_to_cart', 'logout', 'is_registered', 'validate_email', 'check_email',
'send_hash_mail', 'add_tag');
// 显示页面的action列表
$ui_arr = array('register', 'login', 'profile', 'order_list', 'order_detail',
'message_list', 'get_password', 'reset_password', 'account_raply',
'account_deposit', 'account_log', 'act_account', 'pay', 'default', 'buyrank',
'buydone', 'card', 'play_log', 'get_integral', 'tag_list', 'comment_list',
'transform_points');
// 检查是否登陆
$user_id = $skyuc->session->vars['userid'];

// 未登录处理
if (empty($user_id)) {
    if (! in_array($action, $not_login_arr)) {
        if (in_array($action, $ui_arr)) {
            // 如果需要登录,并是显示页面的操作，记录当前操作，用于登录后跳转到相应操作
            if ($action == 'login') {
                if (isset($_REQUEST['back_act'])) {
                    $back_act = trim($_REQUEST['back_act']);
                }
            } else {
                if (! empty($_SERVER['QUERY_STRING'])) {
                    $back_act = 'user.php?' . $_SERVER['QUERY_STRING'];
                }
                $action = 'login';
            }
        } else {
            //未登录提交数据。非正常途径提交数据！
            die($_LANG['require_login']);
        }
    }
}
// 如果是显示页面，对页面进行相应赋值
if (in_array($action, $ui_arr)) {
    assign_template();
    $position = assign_ur_here(0, $_LANG['user_center']);
    $smarty->assign('page_title', $position['title']); // 页面标题
    $smarty->assign('ur_here', $position['ur_here']);
    // 是否显示积分兑换
    if (! empty($skyuc->options['points_rule']) &&
     unserialize($skyuc->options['points_rule'])) {
        $smarty->assign('show_transform_points', 1);
    }
    $smarty->assign('nav_list', get_navigator()); // 导航栏
    $smarty->assign('action', $action);
    $smarty->assign('lang', $_LANG);
}
//用户中心欢迎页
if ($action == 'default') {
    require_once (DIR . '/includes/functions_users.php');
    $smarty->assign('info', get_user_default($user_id));
    $smarty->assign('user_notice', $skyuc->options['user_notice']);
    $smarty->display('user_center.dwt');
}
/*------------------------------------------------------ */
//-- 显示会员注册界面
/*------------------------------------------------------ */
if ($action == 'register') {
    if ($skyuc->options['regusers_closed'] == 1) {
        header('Content-type: text/html; charset=utf-8');
        echo '<div style="margin: 150px; text-align: center; font-size: 14px"><p>' .
         $_LANG['regusers_closed'] . '</p><p>' .
         $skyuc->options['regusers_comment'] . '</p></div>';
        exit();
    }
    if (! isset($back_act) && defined(REFERRER)) {
        $back_act = iif(strpos(REFERRER, 'user.php'), './index.php', REFERRER);
    }
    //验证码相关设置
    if ((intval($skyuc->options['humanverify']) & HV_REGISTER)) {
        $smarty->assign('enabled_captcha', 1);
    }
    $smarty->assign('referrer',
    $skyuc->input->clean_gpc('c', COOKIE_PREFIX . 'referrer', TYPE_STR));
    $smarty->display('user_passport.dwt');
} /*------------------------------------------------------ */
//-- 注册会员的处理
/*------------------------------------------------------ */
elseif ($action == 'act_register') {
    require (DIR . '/includes/functions_passport.php');
    require_once (DIR . '/includes/functions_users.php');
    $skyuc->input->clean_array_gpc('p',
    array('username' => TYPE_STR, 'password' => TYPE_STR, 'email' => TYPE_STR,
    'humanverify' => TYPE_ARRAY_STR, 'other' => TYPE_ARRAY_STR));
    $username = $skyuc->GPC['username'];
    $password = $skyuc->GPC['password'];
    $email = $skyuc->GPC['email'];
    $other = iif($skyuc->GPC_exists['other'], $skyuc->GPC['other'], array());
    if (! empty($other['referrer'])) {
        $other['referrer'] = preg_replace("#[^a-zA-Z0-9_]#", '',
        $other['referrer']);
    }
    if (strlen($username) < 3) {
        show_message($_LANG['passport_js']['username_shorter']);
    }
    if (strlen($password) < 6) {
        show_message($_LANG['passport_js']['password_shorter']);
    }
    // 验证码检查
    if ((intval($skyuc->options['humanverify']) & HV_REGISTER)) {
        if (empty($skyuc->GPC['humanverify'])) {
            show_message($_LANG['invalid_captcha'], $_LANG['sign_up'],
            'user.php?act=register', 'error');
        }
        // 检查验证码
        require_once (DIR . '/includes/class_humanverify.php');
        $verify = & HumanVerify::fetch_library($skyuc);
        if (! $verify->verify_token($skyuc->GPC['humanverify'])) {
            show_message($_LANG['invalid_captcha'], $_LANG['sign_up'],
            'user.php?act=register', 'error');
        }
    }
    if (register($username, $password, $email, $other) === false) {
        $err->show($_LANG['sign_up'], 'user.php?act=register');
    } else {
        $ucdata = iif(empty($user->ucdata), '', $user->ucdata);
        //send_regiter_hash($skyuc->session->vars['userid']);
        show_message(
        sprintf($_LANG['register_success'], $username . $ucdata), 'reg_succeed',
        'user.php', 'info');
    }
} /*------------------------------------------------------ */
//-- ajax 发送验证邮件
/*------------------------------------------------------ */
elseif ($action == 'send_hash_mail') {
    require (DIR . '/includes/class_json.php');
    require (DIR . '/includes/functions_passport.php');
    $json = new JSON();
    $result = array('error' => 0, 'message' => '', 'content' => '');
    if ($user_id == 0) {
        // 用户没有登录
        $result['error'] = 1;
        $result['message'] = $_LANG['login_please'];
        die($json->encode($result));
    }
    if (send_regiter_hash($user_id)) {
        $result['message'] = $_LANG['validate_mail_ok'];
        die($json->encode($result));
    } else {
        $result['error'] = 1;
        $result['message'] = $GLOBALS['err']->last_message();
    }
    die($json->encode($result));
} /*------------------------------------------------------ */
//-- 验证用户注册邮件
/*------------------------------------------------------ */
elseif ($action == 'validate_email') {
    $hash = $skyuc->input->clean_gpc('g', 'hash', TYPE_STR);
    if ($hash) {
        require (DIR . '/includes/functions_passport.php');
        $id = register_hash('decode', $hash);
        if ($id > 0) {
            $sql = 'UPDATE ' . TABLE_PREFIX . 'users' .
             ' SET is_validated = 1 WHERE user_id=' . $id;
            $db->query_write($sql);
            $sql = 'SELECT user_name, email FROM ' . TABLE_PREFIX . 'users' .
             ' WHERE user_id = ' . $id;
            $row = $db->query_first_slave($sql);
            show_message(
            sprintf($_LANG['validate_ok'], $row['user_name'], $row['email']),
            $_LANG['profile_lnk'], 'user.php');
        }
    }
    show_message($_LANG['validate_fail']);
} /*------------------------------------------------------ */
//-- AJAX 验证用户注册用户名是否可以注册
/*------------------------------------------------------ */
elseif ($action == 'is_registered') {
    require (DIR . '/includes/functions_passport.php');
    $username = $skyuc->input->clean_gpc('g', 'username', TYPE_STR);
    if ($user->check_user($username) || admin_registered($username)) {
        echo 'false';
    } else {
        echo 'true';
    }
} /*------------------------------------------------------ */
//-- AJAX 验证用户邮箱地址是否被注册
/*------------------------------------------------------ */
elseif ($action == 'check_email') {
    $email = $skyuc->input->clean_gpc('g', 'email', TYPE_STR);
    if ($user->check_email($email)) {
        echo 'false';
    } else {
        echo 'true';
    }
} /*------------------------------------------------------ */
//-- 用户登录界面
/*------------------------------------------------------ */
elseif ($action == 'login') {
    if (empty($back_act) && defined(REFERRER)) {
        $back_act = iif(strpos(REFERRER, 'user.php'), './index.php', REFERRER);
    } else {
        $back_act = 'user.php';
    }
    if (intval($skyuc->options['humanverify']) & HV_LOGIN) {
        $smarty->assign('enabled_captcha', 1);
    }
    $smarty->assign('back_act', $back_act);
    $smarty->display('user_passport.dwt');
} /*------------------------------------------------------ */
//-- 处理会员的登录
/*------------------------------------------------------ */
elseif ($action == 'act_login') {
    require_once (DIR . '/includes/functions_users.php');
    $skyuc->input->clean_array_gpc('p',
    array('username' => TYPE_STR, 'password' => TYPE_STR,
    'back_act' => TYPE_STR, 'humanverify' => TYPE_ARRAY_STR));
    $username = $skyuc->GPC['username'];
    $password = $skyuc->GPC['password'];
    $back_act = $skyuc->GPC['back_act'];
    if (($skyuc->options['humanverify'] & HV_LOGIN)) {
        if (empty($skyuc->GPC['humanverify'])) {
            show_message($_LANG['invalid_captcha'], $_LANG['relogin_lnk'],
            'user.php', 'error');
        }
        // 检查验证码
        require_once (DIR . '/includes/class_humanverify.php');
        $verify = & HumanVerify::fetch_library($skyuc);
        if (! $verify->verify_token($skyuc->GPC['humanverify'])) {
            show_message($_LANG['invalid_captcha'], $_LANG['relogin_lnk'],
            'user.php', 'error');
        }
    }
    /*	require (DIR . '/includes/functions_passport.php');
	card_login ( $username, $password );*/
    if ($user->login($username, $password) > 0) {
        update_user_info();
        $ucdata = iif(isset($user->ucdata), $user->ucdata, '');
        show_message($_LANG['login_success'] . $ucdata,
        array($_LANG['back_up_page'], $_LANG['profile_lnk']),
        array($back_act, 'user.php'), 'info');
    } elseif ($user->login($username, $password) == 0) {
        show_message($_LANG['login_failure'], $_LANG['relogin_lnk'], 'user.php',
        'error');
    } else {
        show_message($_LANG['was_login_success'], $_LANG['relogin_lnk'],
        'user.php', 'error');
    }
} /*------------------------------------------------------ */
//-- 处理会员 AJAX 的登录请求
/*------------------------------------------------------ */
elseif ($action == 'signin') {
    require_once (DIR . '/includes/functions_users.php');
    require (DIR . '/includes/class_json.php');
    $json = new JSON();
    $skyuc->input->clean_array_gpc('p',
    array('username' => TYPE_STR, 'password' => TYPE_STR));
    $username = $skyuc->GPC['username'];
    $password = $skyuc->GPC['password'];
    /*	require (DIR . '/includes/functions_passport.php');
	card_login ( $username, $password );*/
    $result = array('error' => 0, 'content' => '');
    if ($user->login($username, $password) > 0) {
        update_user_info(); //更新用户信息
        $smarty->assign('user_info', get_member_info());
        $ucdata = iif(isset($user->ucdata), $user->ucdata, '');
        $result['ucdata'] = $ucdata;
        $result['content'] = $smarty->fetch('library/member_info.lbi');
    } elseif ($user->login($username, $password) == 0) {
        $result['error'] = 1;
        $result['content'] = $_LANG['login_failure'];
    } else {
        $result['error'] = 1;
        $result['content'] = $_LANG['was_login_success'];
    }
    //执行session关闭脚本
    exec_shut_down();
    die($json->encode($result));
} /*------------------------------------------------------ */
//-- 获取积分页面
/*------------------------------------------------------ */
elseif ($action == 'get_integral') {
    $smarty->assign('hosturl', get_url());
    $smarty->assign('userid', $user_id);
    $smarty->assign('user_notice', $skyuc->options['user_notice']);
    $smarty->display('user_center.dwt');
} /*------------------------------------------------------ */
//-- 退出会员中心
/*------------------------------------------------------ */
elseif ($action == 'logout') {
    if (! isset($back_act) && defined(REFERER)) {
        $back_act = iif(strpos(REFERER, 'user.php'), './index.php', REFERER);
    }
    $back_act = iif(empty($back_act), './index.php', $back_act);
    $user->logout();
    $ucdata = iif(isset($user->ucdata), $user->ucdata, '');
    show_message($_LANG['logout_success'] . $ucdata,
    array($_LANG['back_up_page'], $_LANG['back_home_lnk']),
    array($back_act, 'index.php'), 'info');
} /*------------------------------------------------------ */
//-- 个人资料页面
/*------------------------------------------------------ */
elseif ($action == 'profile') {
    require_once (DIR . '/includes/functions_users.php');
    $smarty->assign('profile', get_profile($user_id));
    $smarty->assign('user_notice', $skyuc->options['user_notice']);
    $smarty->display('user_center.dwt');
} /*------------------------------------------------------ */
//-- 修改个人资料的处理
/*------------------------------------------------------ */
elseif ($action == 'act_edit_profile') {
    require_once (DIR . '/includes/functions_users.php');
    $skyuc->input->clean_array_gpc('p',
    array('birthdayYear' => TYPE_UINT, 'birthdayMonth' => TYPE_UINT,
    'birthdayDay' => TYPE_UINT, 'email' => TYPE_STR, 'gender' => TYPE_UINT,
    'other' => TYPE_ARRAY_STR));
    $birthday = skyuc_mktime(0, 0, 0, $skyuc->GPC['birthdayMonth'],
    $skyuc->GPC['birthdayDay'], $skyuc->GPC['birthdayYear']);
    if (! empty($skyuc->GPC['other']['phone']) &&
     ! preg_match('/^[\d|\_|\-|\s]+$/', $skyuc->GPC['other']['phone'])) {
        show_message($_LANG['passport_js']['phone_invalid']);
    }
    if (! validate($skyuc->GPC['email'], 4)) {
        show_message($_LANG['msg_email_format']);
    }
    if (! empty($skyuc->GPC['other']['qq']) &&
     ! preg_match('/^\d+$/', $skyuc->GPC['other']['qq'])) {
        show_message($_LANG['passport_js']['qq_invalid']);
    }
    $profile = array('user_id' => $user_id, 'email' => $skyuc->GPC['email'],
    'gender' => $skyuc->GPC['gender'], 'birthday' => $birthday,
    'other' => iif($skyuc->GPC_exists['other'], $skyuc->GPC['other'], array()));
    if (edit_profile($profile)) {
        show_message($_LANG['edit_profile_success'], $_LANG['profile_lnk'],
        'user.php?act=profile', 'info');
    } else {
        show_message($_LANG['edit_profile_failed'], '', '', 'info');
    }
} /*------------------------------------------------------ */
//-- 观看记录页面
/*------------------------------------------------------ */
elseif ($action == 'play_log') {
    $skyuc->input->clean_gpc('r', 'page', TYPE_UINT);
    $page = iif($skyuc->GPC_exists['page'], $skyuc->GPC['page'], 1);
    $total = $db->query_first_slave(
    'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'play_log' .
     ' WHERE user_id = ' . $user_id);
    $pager = get_pager('user.php', array('act' => $action), $total['total'],
    $page);
    $sql = 'SELECT * FROM ' . TABLE_PREFIX . 'play_log' . ' WHERE user_id = ' .
     $user_id . ' ORDER BY id DESC';
    $sql = $skyuc->db->query_limit($sql, $pager['size'], $pager['start']);
    $res = $db->query_read_slave($sql);
    $play_log = array();
    while ($row = $db->fetch_array($res)) {
        $row['looktime'] = skyuc_date(
        $skyuc->options['date_format'] . ' ' . $skyuc->options['time_format'],
        $row['time']);
        $row['looktype'] = iif(! empty($row['player']),
        $skyuc->players["$row[player]"]['title'], 'N/A');
        $row['lookid'] = iif(! empty($row['url_id']),
        sprintf($_LANG['play_log_lookid'], $row['url_id']), 0);
        $play_log[] = $row;
    }
    $smarty->assign('pager', $pager);
    $smarty->assign('play_log', $play_log);
    $smarty->assign('user_notice', $skyuc->options['user_notice']);
    $smarty->display('user_center.dwt');
} /*------------------------------------------------------ */
//-- 充值卡页面
/*------------------------------------------------------ */
elseif ($action == 'card') {
    $username = $skyuc->userinfo['user_name'];
    $smarty->assign('username', $username);
    $smarty->assign('user_notice', $_CFG['user_notice']);
    $smarty->display('user_center.dwt');
} /*------------------------------------------------------ */
//-- 充值卡提交处理
/*------------------------------------------------------ */
elseif ($action == 'act_card_ok') {
    $skyuc->input->clean_array_gpc('p',
    array('carduser' => TYPE_STR, 'cardid' => TYPE_STR, 'cardpwd' => TYPE_STR));
    $sql = 'SELECT c.*, u.rank_type AS rank_type FROM ' . TABLE_PREFIX . 'card' .
     ' AS c' . ' LEFT JOIN ' . TABLE_PREFIX . 'user_rank' .
     ' AS u ON u.rank_id=c.rank_id' . " WHERE c.cardid ='" .
     $db->escape_string($skyuc->GPC['cardid']) . "' AND c.cardpass ='" .
     $db->escape_string($skyuc->GPC['cardpwd']) . "'";
    $row = $db->query_first_slave($sql);
    if (empty($row)) {
        show_message($_LANG['card_ok_failed'] . $_LANG['card_error'], '', '',
        'info');
    }
    //卡号过期检查
    if (TIMENOW > $row['endtime']) {
        show_message($_LANG['card_ok_failed'] . $_LANG['card_date_over'], '',
        '', 'info');
    }
    $rank_id = $row['rank_id']; //卡号等级
    $rank_type = $row['rank_type']; //卡号类型
    $cid = $row['id']; //卡编号
    //用户存在检查
    $sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'users' .
     " WHERE user_name = '" . $db->escape_string($skyuc->GPC['carduser']) . "'";
    $total = $db->query_first_slave($sql);
    if ($total['total'] == 0) {
        show_message($_LANG['card_ok_failed'] . $_LANG['user_error'], '', '',
        'info');
    } else {
        //充入帐户
        $sql = 'SELECT usertype, unit_date, user_point FROM ' .
         TABLE_PREFIX . 'users' . " WHERE user_name='" .
         $db->escape_string($skyuc->GPC['carduser']) . "'";
        $arr = $db->query_first_slave($sql);
        if (! empty($arr)) {
            $date = max($arr['unit_date'], TIMENOW);
            //计时会员添加天数
            $unit_date = 86400 * $row['cardvalue'] + $date;
            //计点会员添加点数
            $user_point = iif(! empty($arr['user_point']),
            $arr['user_point'] + $row['cardvalue'], $row['cardvalue']);
            //计时会员
            if ($rank_type == 1) {
                $sql = 'UPDATE ' . TABLE_PREFIX . 'users' .
                 " SET usertype=1, user_rank='" . $rank_id . "',unit_date='" .
                 $unit_date . "' WHERE user_name='" .
                 $db->escape_string($skyuc->GPC['carduser']) . "'";
            } //计点会员
elseif ($rank_type == 0) {
                $sql = 'UPDATE ' . TABLE_PREFIX . 'users' .
                 " SET usertype=0, user_rank='" . $rank_id . "',user_point='" .
                 $user_point . "' WHERE user_name='" .
                 $db->escape_string($skyuc->GPC['carduser']) . "'";
            }
            $db->query_write($sql);
            // 添加充值记录
            $sql = 'INSERT INTO ' . TABLE_PREFIX . 'card_log' .
             ' (cardid, cardpass, rank_id, cardvalue, money, addtime, username, userip) ' .
             " VALUES('" . $db->escape_string($skyuc->GPC['cardid']) . "', '" .
             $db->escape_string($skyuc->GPC['cardpwd']) . "', '" . $rank_id .
             "', '" . $row['cardvalue'] . "', '" . $row['money'] . "', '" .
             TIMENOW . "', '" . $db->escape_string($skyuc->GPC['carduser']) .
             "', '" . ALT_IP . "')";
            $db->query_write($sql);
            //删除影卡
            $sql = 'DELETE FROM ' . TABLE_PREFIX . 'card' .
             " WHERE id='" . $cid . "'";
            $db->query_write($sql);
            show_message(
            $_LANG['card_ok_success'] . $_LANG['carduser'] .
             $skyuc->GPC['carduser'], $_LANG['card_lnk'], 'user.php?act=card',
            'info');
        }
    }
} /*------------------------------------------------------ */
//-- 密码找回-->修改密码界面
/*------------------------------------------------------ */
elseif ($action == 'get_password') {
    $skyuc->input->clean_array_gpc('g',
    array('code' => TYPE_STR, 'uid' => TYPE_UINT));
    //从邮件处获得的act
    if ($skyuc->GPC_exists['code'] && $skyuc->GPC_exists['uid']) {
        $code = $skyuc->GPC['code'];
        $uid = $skyuc->GPC['uid'];
        // 判断链接的合法性
        $user_info = $user->get_profile_by_id($uid);
        if (empty($user_info) ||
         ($user_info &&
         md5($user_info['user_id'] . COOKIE_SALT . $user_info['password']) !=
         $code)) {
            show_message($_LANG['parm_error'], $_LANG['back_home_lnk'], './',
            'info');
        }
        $smarty->assign('uid', $uid);
        $smarty->assign('code', $code);
        $smarty->assign('user_notice', $skyuc->options['user_notice']);
        $smarty->assign('action', 'reset_password');
        $smarty->display('user_passport.dwt');
    } else {
        //显示用户名和email表单
        $smarty->display('user_passport.dwt');
    }
} /*------------------------------------------------------ */
//-- 发送密码修改确认邮件
/*------------------------------------------------------ */
elseif ($action == 'send_pwd_email') {
    require (DIR . '/includes/functions_passport.php');
    // 初始化会员用户名和邮件地址
    $skyuc->input->clean_array_gpc('p',
    array('user_name' => TYPE_STR, 'email' => TYPE_STR));
    $user_name = $skyuc->GPC['user_name'];
    $email = $skyuc->GPC['email'];
    //用户名和邮件地址是否匹配
    $user_info = $user->get_user_info($user_name);
    if ($user_info && $user_info['email'] == $email) {
        //生成code
        $code = md5(
        $user_info['user_id'] . COOKIE_SALT . $user_info['password']);
        //发送邮件的函数
        if (send_pwd_email($user_info['user_id'], $user_name, $email,
        $code)) {
            show_message($_LANG['send_success'] . $email,
            $_LANG['back_home_lnk'], './', 'info');
        } else {
            //发送邮件出错
            show_message($_LANG['fail_send_password'],
            $_LANG['back_page_up'], './', 'info');
        }
    } else {
        //用户名与邮件地址不匹配
        show_message($_LANG['username_no_email'],
        $_LANG['back_page_up'], '', 'info');
    }
} /*------------------------------------------------------ */
//-- 重置新密码
/*------------------------------------------------------ */
elseif ($action == 'reset_password') {
    //显示重置密码的表单
    $smarty->display('user_passport.dwt');
} /*------------------------------------------------------ */
//-- 修改会员密码
/*------------------------------------------------------ */
elseif ($action == 'act_edit_password') {
    require (DIR . '/includes/functions_passport.php');
    $skyuc->input->clean_array_gpc('p',
    array('old_password' => TYPE_STR, 'new_password' => TYPE_STR,
    'uid' => TYPE_UINT, 'code' => TYPE_STR));
    $old_password = $skyuc->GPC['old_password'];
    $new_password = $skyuc->GPC['new_password'];
    $user_id = iif($skyuc->GPC_exists['uid'], $skyuc->GPC['uid'], $user_id);
    $code = $skyuc->GPC['code'];
    if (strlen($new_password) < 6) {
        show_message($_LANG['passport_js']['password_shorter']);
    }
    $user_info = $user->get_profile_by_id($user_id); //论坛记录
    if (($user_info &&
     (! empty($code) &&
     md5($user_info['user_id'] . COOKIE_SALT . $user_info['password']) == $code)) || ($skyuc->session->vars['userid'] >
     0 && $skyuc->session->vars['userid'] == $user_id &&
     $user->check_user($skyuc->userinfo['user_name'], $old_password))) {
        if ($user->edit_user(
        array(
        'username' => empty($code) ? $skyuc->userinfo['user_name'] : $user_info['user_name'],
        'old_password' => $old_password, 'password' => $new_password))) {
            $user->logout();
            show_message($_LANG['edit_password_success'], $_LANG['relogin_lnk'],
            'user.php?act=login', 'info');
        } else {
            show_message($_LANG['edit_password_failure'],
            $_LANG['back_page_up'], '', 'info');
        }
    } else {
        show_message($_LANG['edit_password_failure'], $_LANG['back_page_up'],
        '', 'info');
    }
} /*------------------------------------------------------ */
//-- 查看会员购买
/*------------------------------------------------------ */
elseif ($action == 'buyrank') {
    require_once (DIR . '/includes/functions_users.php');
    require (DIR . '/includes/functions_order.php');
    // 获取价格列表
    $arr = array();
    foreach ($skyuc->usergroup as $row) {
        //格式化金额
        $row['b_money'] = price_format($row['money']);
        $row['b_count'] = iif($row['type'] != 0,
        $row['count'] . $_LANG['look_day'], $row['count'] . $_LANG['look_count']);
        $row['b_type'] = iif($row['type'] != 0, $_LANG['is_day'],
        $_LANG['is_count']);
        $arr[] = $row;
    }
    $user_info = get_user_info($user_id);
    // 如果使用余额，取得用户余额
    if ((! isset($skyuc->options['use_surplus']) ||
     $skyuc->options['use_surplus'] == 1) && $user_info['user_money'] > 0) {
        // 能使用余额
        $smarty->assign('allow_use_surplus', 1);
        $smarty->assign('your_surplus', $user_info['user_money']);
    }
    // 如果使用积分，取得用户可用积分及本订单最多可以使用的积分
    if ((! isset($skyuc->options['use_integral']) ||
     $skyuc->options['use_integral'] == 1) && $user_info['pay_point'] > 0) {
        // 能使用积分
        $smarty->assign('allow_use_integral', 1);
        $smarty->assign('your_integral', $user_info['pay_point']); // 用户积分
    }
    $account = get_surplus_info($user_id);
    $smarty->assign('payment', get_online_payment_list());
    $smarty->assign('order', $account);
    $smarty->assign('flow_no_payment', $_LANG['flow_no_payment']);
    $smarty->assign('buyrank', $arr);
    $smarty->assign('user_notice', $skyuc->options['user_notice']);
    $smarty->display('user_center.dwt');
} /*------------------------------------------------------ */
//-- 检查用户输入的余额
/*------------------------------------------------------ */
elseif ($action == 'check_surplus') {
    require (DIR . '/includes/class_json.php');
    require_once (DIR . '/includes/functions_users.php');
    $result = array('error' => '', 'content' => '');
    $surplus = floatval($_GET['surplus']);
    $user_info = get_user_info($user_id);
    $result['error'] = iif($user_info['user_money'] < $surplus,
    $_LANG['surplus_not_enough'], '');
    $json = new JSON();
    die($json->encode($result));
} /*------------------------------------------------------ */
//-- 检查用户输入的积分
/*------------------------------------------------------ */
elseif ($action == 'check_integral') {
    require (DIR . '/includes/class_json.php');
    require_once (DIR . '/includes/functions_users.php');
    $result = array('error' => '', 'content' => '');
    $points = floatval($_GET['points']);
    $user_info = get_user_info($user_id);
    $result['error'] = iif($user_info['pay_point'] < $points,
    $_LANG['integral_not_enough'], '');
    $json = new JSON();
    die($json->encode($result));
} /*------------------------------------------------------ */
//-- 完成所有订单操作，提交到数据库
/*------------------------------------------------------ */
elseif ($action == 'buydone') {
    require_once (DIR . '/includes/functions_users.php');
    require (DIR . '/includes/functions_order.php');
    require (DIR . '/includes/functions_payment.php');
    $skyuc->input->clean_array_gpc('p',
    array('buy_id' => TYPE_UINT, 'payment' => TYPE_UINT, 'surplus' => TYPE_UNUM,
    'points' => TYPE_UINT, 'multiple' => TYPE_UINT));
    // 取得提交的购买价格信息
    $buy_id = $skyuc->GPC['buy_id'];
    //购买倍数
    $multiple = iif($skyuc->GPC['multiple'] == 0, 1,
    $skyuc->GPC['multiple']);
    $order = array('pay_id' => $skyuc->GPC['payment'],
    'surplus' => floatval($skyuc->GPC['surplus']),
    'integral' => $skyuc->GPC['points'], 'order_amount' => 0, 'pay_amount' => 0,
    'user_id' => $user_id, 'pay_status' => PS_UNPAYED);
    $row = array();
    $row = $skyuc->usergroup["$buy_id"];
    $order['order_count'] = $row['count'] * $multiple; //购买点数或天数
    $order['usertype'] = $row['type']; //购买会员类型
    $order['rank_id'] = $row['id']; //购买会员等级
    $order['order_amount'] = $row['money'] * $multiple; //订单金额
    // 获取用户信息
    $user_info = get_user_info($user_id);
    // 用户IP和订单时间
    $order['order_time'] = TIMENOW;
    $order['user_ip'] = ALT_IP;
    // 支付方式
    if ($order['pay_id'] > 0) {
        $payment = payment_info($order['pay_id']);
        $order['pay_name'] = $payment['pay_name'];
    }
    $order['pay_amount'] = $order['order_amount'] +
     pay_fee($order['pay_id'], $order['order_amount']);
    // 插入订单表
    $error_no = 0;
    do {
        $order['order_sn'] = get_order_sn(); //获取新订单号
        $db->query_write(fetch_query_sql($order, 'order_info'));
        $error_no = $db->errno();
    } while ($error_no == 1062); //如果是订单号重复则重新提交数据
    $new_order_id = $db->insert_id();
    $order['order_id'] = $new_order_id;
    // 如果全部使用余额支付，检查余额是否足够
    if ($payment['pay_code'] == 'balance' && $order['order_amount'] > 0) {
        if ($order['order_amount'] > $user_info['user_money']) {
            show_message($_LANG['balance_not_enough']);
        } elseif ($order['user_id'] > 0) { //处理余额
            log_account_change($order['user_id'],
            $order['order_amount'] * (- 1), 0,
            sprintf($_LANG['pay_order'], $order['order_sn']));
            // 实时开通会员权限
            payment_finsh($order['order_count'], $order['usertype'],
            $order['rank_id'], $user_id, $order['order_id'],
            $order['order_amount']);
            $order['surplus'] = $order['order_amount']; //支付的余额
            // 订单状态设为已付款
            $order['pay_status'] = PS_PAYED;
        }
    }
    // 如果全部使用积分支付，检查积分是否足够
    if ($payment['pay_code'] == 'integral' && $order['order_amount'] > 0) {
        //订单的金额转成积分
        $order_ponits = integral_of_value($order['order_amount']);
        if ($order_ponits > $user_info['pay_point']) {
            show_message($_LANG['integral_not_enough']);
        } elseif ($order['user_id'] > 0 && $order_ponits > 0) { // 处理积分
            log_account_change($order['user_id'], 0,
            $order_ponits * (- 1),
            sprintf($_LANG['pay_order'], $order['order_sn']));
            // 实时开通会员权限
            payment_finsh($order['order_count'], $order['usertype'],
            $order['rank_id'], $user_id, $order['order_id'],
            $order['order_amount']);
            $order['integral'] = $order_ponits; //支付的积分
            // 订单状态设为已付款
            $order['pay_status'] = PS_PAYED;
        }
    }
    if ($payment['pay_code'] == 'integral' || $payment['pay_code'] == 'balance') {
        $sql = 'UPDATE ' . TABLE_PREFIX . 'order_info' . ' SET pay_time=' .
         TIMENOW . ', integral=' . $order['integral'] . ',	surplus=' .
         $order['surplus'] . ',	pay_status =' . $order['pay_status'] .
         ' WHERE order_id	=' . $order['order_id'] . '';
        $db->query_write($sql);
    }
    // 插入支付日志
    $order['log_id'] = insert_pay_log($new_order_id, $order['pay_amount'],
    PAY_ORDER);
    $order['amount_formated'] = price_format($order['pay_amount']); //格式化实际支付的金额
    // 取得支付信息，生成支付代码
    $payment = payment_info($order['pay_id']);
    require_once (DIR . '/includes/modules/payment/' . $payment['pay_code'] .
     '.php');
    $pay_obj = new $payment['pay_code']();
    $pay_online = $pay_obj->get_code($order,
    unserialize_config($payment['pay_config']));
    $order['pay_desc'] = $payment['pay_desc'];
    $smarty->assign('pay_online', $pay_online);
    // 订单信息
    $smarty->assign('order', $order);
    $smarty->assign('order_submit_back',
    sprintf($_LANG['order_submit_back'], $_LANG['back_home'],
    $_LANG['goto_user_center'])); // 返回提示
    $smarty->display('user_center.dwt');
} /*------------------------------------------------------ */
//-- 查看订单列表
/*------------------------------------------------------ */
elseif ($action == 'order_list') {
    require (DIR . '/includes/functions_order.php');
    $skyuc->input->clean_gpc('r', 'page', TYPE_UINT);
    $page = iif($skyuc->GPC_exists['page'], $skyuc->GPC['page'], 1);
    $total = $db->query_first_slave(
    'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'order_info' .
     ' WHERE user_id = ' . $user_id);
    $record_count = $total['total'];
    $pager = get_pager('user.php', array('act' => $action), $record_count,
    $page);
    $orders = get_user_orders($user_id, $pager['size'], $pager['start']);
    $smarty->assign('pager', $pager);
    $smarty->assign('orders', $orders);
    $smarty->assign('user_notice', $skyuc->options['user_notice']);
    $smarty->display('user_center.dwt');
} /*------------------------------------------------------ */
//-- 查看订单详情
/*------------------------------------------------------ */
elseif ($action == 'order_detail') {
    require (DIR . '/includes/functions_order.php');
    $order_id = $skyuc->input->clean_gpc('g', 'order_id', TYPE_UINT);
    // 订单详情
    $order = get_order_detail($order_id, $user_id);
    if ($order === false) {
        $err->show($_LANG['back_home_lnk'], './');
        exit();
    }
    $smarty->assign('user_notice', $skyuc->options['user_notice']);
    $smarty->assign('order', $order);
    $smarty->display('user_center.dwt');
} /*------------------------------------------------------ */
//--  取消订单
/*------------------------------------------------------ */
elseif ($action == 'cancel_order') {
    require (DIR . '/includes/functions_order.php');
    $order_id = $skyuc->input->clean_gpc('g', 'order_id', TYPE_UINT);
    if (cancel_order($order_id, $user_id)) {
        header("Location: user.php?act=order_list\n");
        exit();
    } else {
        $err->show($_LANG['order_list_lnk'], 'user.php?act=order_list');
    }
} /*------------------------------------------------------ */
//--  显示留言列表
/*------------------------------------------------------ */
elseif ($action == 'message_list') {
    require_once (DIR . '/includes/functions_users.php');
    $skyuc->input->clean_gpc('r', 'page', TYPE_UINT);
    $page = iif($skyuc->GPC_exists['page'], $skyuc->GPC['page'], 1);
    // 获取用户留言的数量
    $sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'feedback' .
     ' WHERE parent_id = 0 AND user_id = ' . $user_id;
    $total = $db->query_first_slave($sql);
    $record_count = $total['total'];
    $pager = get_pager('user.php', array('act' => $action), $record_count,
    $page, 5);
    $smarty->assign('message_list',
    get_message_list($user_id, $skyuc->userinfo['user_name'], $pager['size'],
    $pager['start']));
    $smarty->assign('pager', $pager);
    $smarty->assign('user_notice', $skyuc->options['user_notice']);
    $smarty->display('user_center.dwt');
} /*------------------------------------------------------ */
//--  添加我的留言
/*------------------------------------------------------ */
elseif ($action == 'act_add_message') {
    require_once (DIR . '/includes/functions_users.php');
    require (DIR . '/includes/control/message.php');
    $skyuc->input->clean_array_gpc('p',
    array('msg_type' => TYPE_UINT, 'msg_title' => TYPE_STR,
    'msg_content' => TYPE_STR));
    $message = array('user_id' => $user_id,
    'user_name' => $skyuc->userinfo['user_name'],
    'user_email' => $skyuc->userinfo['email'],
    'msg_type' => $skyuc->GPC['msg_type'],
    'msg_title' => $skyuc->GPC['msg_title'],
    'msg_content' => $skyuc->GPC['msg_content'],
    'upload' => (isset($_FILES['message_img']['error']) &&
     $_FILES['message_img']['error'] == 0) ||
     (! isset($_FILES['message_img']['error']) &&
     $_FILES['message_img']['tmp_name'] != 'none') ? $_FILES['message_img'] : array());
    if (add_message($message)) {
        if ($skyuc->options['send_admin'] == 1) {
            // 发送Email通知管理员
            $sql = 'SELECT email FROM ' . TABLE_PREFIX . 'admin' .
             " WHERE action_list = 'all'";
            $row = $db->query_first_slave($sql);
            $template = get_mail_template('admin_message'); //获取邮件模板
            $smarty->assign('send_date',
            skyuc_date($skyuc->options['date_format'], TIMENOW, true, false));
            $smarty->assign('sent_date',
            skyuc_date($skyuc->options['date_format'], TIMENOW, true, false));
            $content = $smarty->fetch('str:' . $template['template_content']);
            // 发送邮件
            skyuc_mail($row['email'], $template['template_subject'],
            $content, true, $template['is_html']);
        }
        show_message($_LANG['add_message_success'], $_LANG['message_list_lnk'],
        'user.php?act=message_list', 'info');
    } else {
        $err->show($_LANG['message_list_lnk'], 'user.php?act=message_list');
    }
} /*------------------------------------------------------ */
//--  AJAX 添加标签
/*------------------------------------------------------ */
elseif ($action == 'add_tag') {
    require (DIR . '/includes/class_json.php');
    require_once (DIR . '/includes/functions_users.php');
    $result = array('error' => 0, 'message' => '', 'content' => '');
    $skyuc->input->clean_array_gpc('p',
    array('id' => TYPE_UINT, 'tag' => TYPE_STR));
    $id = $skyuc->GPC['id'];
    $tag = $skyuc->GPC['tag'];
    if ($user_id == 0) {
        //用户没有登录
        $result['error'] = 1;
        $result['message'] = $_LANG['tag_anonymous'];
    } else {
        add_tag($id, $tag); // 添加tag
        // 重新获得该影片的所有标签
        $arr = get_tags($id, 0, $skyuc->options['related_tags'],
        true);
        foreach ($arr as $row) {
            $result['content'][] = array(
            'word' => htmlspecialchars($row['tag_words']),
            'count' => $row['tag_count']);
        }
    }
    $json = new JSON();
    echo $json->encode($result);
    exit();
} /*------------------------------------------------------ */
//--  标签云列表
/*------------------------------------------------------ */
elseif ($action == 'tag_list') {
    require_once (DIR . '/includes/functions_users.php');
    $show_id = $skyuc->input->clean_gpc('g', 'id', TYPE_UINT);
    $smarty->assign('tags', get_user_tags($user_id));
    $smarty->assign('tags_from', 'user');
    $smarty->assign('user_notice', $skyuc->options['user_notice']);
    $smarty->display('user_center.dwt');
} /*------------------------------------------------------ */
//-- 删除标签云的处理
/*------------------------------------------------------ */
elseif ($action == 'act_del_tag') {
    require_once (DIR . '/includes/functions_users.php');
    $tag_words = $skyuc->input->clean_gpc('g', 'tag_words', TYPE_STR);
    delete_tag($tag_words, $user_id);
    header("Location: user.php?act=tag_list\n");
    exit();
} /*------------------------------------------------------ */
//-- 显示评论列表
/*------------------------------------------------------ */
elseif ($action == 'comment_list') {
    require_once (DIR . '/includes/functions_users.php');
    $skyuc->input->clean_gpc('r', 'page', TYPE_UINT);
    $page = iif($skyuc->GPC_exists['page'], $skyuc->GPC['page'], 1);
    // 获取用户留言的数量
    $sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'comment' .
     ' WHERE parent_id = 0 AND user_name = ' . $user_id;
    $total = $db->query_first_slave($sql);
    $record_count = $total['total'];
    $pager = get_pager('user.php', array('act' => $action), $record_count,
    $page, 5);
    $smarty->assign('comment_list',
    get_comment_list($user_id, $pager['size'], $pager['start']));
    $smarty->assign('pager', $pager);
    $smarty->assign('user_notice', $skyuc->options['user_notice']);
    $smarty->display('user_center.dwt');
} /*------------------------------------------------------ */
//-- 删除评论
/*------------------------------------------------------ */
elseif ($action == 'del_cmt') {
    $id = $skyuc->input->clean_gpc('g', 'id', TYPE_UINT);
    if ($id > 0) {
        $sql = 'DELETE FROM ' . TABLE_PREFIX . 'comment' . ' WHERE comment_id = ' .
         $id . ' AND user_id = ' . $user_id;
        $db->query_write($sql);
    }
    header("Location: user.php?act=comment_list\n");
    exit();
} /*------------------------------------------------------ */
//-- 会员退款申请界面
/*------------------------------------------------------ */
elseif ($action == 'account_raply') {
    $smarty->assign('user_notice', $skyuc->options['user_notice']);
    $smarty->display('user_center.dwt');
} /*------------------------------------------------------ */
//-- 会员预付款界面
/*------------------------------------------------------ */
elseif ($action == 'account_deposit') {
    require_once (DIR . '/includes/functions_users.php');
    $surplus_id = $skyuc->input->clean_gpc('g', 'id', TYPE_UINT);
    $account = get_surplus_info($surplus_id);
    $smarty->assign('payment', get_online_payment_list(false));
    $smarty->assign('order', $account);
    $smarty->assign('user_notice', $skyuc->options['user_notice']);
    $smarty->display('user_center.dwt');
} /*------------------------------------------------------ */
//--  会员充值和提现申请记录
/*------------------------------------------------------ */
elseif ($action == 'account_log') {
    require_once (DIR . '/includes/functions_users.php');
    $skyuc->input->clean_gpc('r', 'page', TYPE_UINT);
    $page = iif($skyuc->GPC_exists['page'], $skyuc->GPC['page'], 1);
    // 获取记录条数
    $sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'user_account' .
     ' WHERE user_id = ' . $user_id . ' AND process_type ' .
     db_create_in(array(SURPLUS_SAVE, SURPLUS_RETURN));
    $total = $db->query_first_slave($sql);
    $record_count = $total['total'];
    //分页函数
    $pager = get_pager('user.php', array('act' => $action), $record_count,
    $page);
    //获取剩余余额
    $surplus_amount = get_user_surplus($user_id);
    if (empty($surplus_amount)) {
        $surplus_amount = 0;
    }
    //获取余额记录
    $account_log = get_account_log($user_id, $pager['size'],
    $pager['start']);
    //模板赋值
    $smarty->assign('surplus_amount', price_format($surplus_amount));
    $smarty->assign('account_log', $account_log);
    $smarty->assign('pager', $pager);
    $smarty->assign('user_notice', $skyuc->options['user_notice']);
    $smarty->display('user_center.dwt');
} /*------------------------------------------------------ */
//--  会员充值和提现申请记录
/*------------------------------------------------------ */
elseif ($action == 'act_account') {
    require_once (DIR . '/includes/functions_users.php');
    require (DIR . '/includes/functions_order.php');
    require (DIR . '/includes/functions_payment.php');
    $skyuc->input->clean_array_gpc('p',
    array('amount' => TYPE_UNUM, 'rec_id' => TYPE_UINT,
    'surplus_type' => TYPE_UINT, 'payment_id' => TYPE_UINT,
    'user_note' => TYPE_STR));
    $amount = iif($skyuc->GPC_exists['amount'], floatval($skyuc->GPC['amount']),
    0);
    if ($amount <= 0) {
        show_message($_LANG['amount_gt_zero']);
    }
    // 变量初始化
    $surplus = array('user_id' => $user_id,
    'rec_id' => $skyuc->GPC['rec_id'],
    'process_type' => $skyuc->GPC['surplus_type'],
    'payment_id' => $skyuc->GPC['payment_id'],
    'user_note' => $skyuc->GPC['user_note'], 'amount' => $amount);
    /*
     * 退款申请的处理
     */
    if ($surplus['process_type'] == 1) {
        // 判断是否有足够的余额的进行退款的操作
        $sur_amount = get_user_surplus($user_id);
        if ($amount > $sur_amount) {
            $content = $_LANG['surplus_amount_error'];
            show_message($content, $_LANG['back_page_up'], '', 'info');
        }
        //插入会员账目明细
        $amount = '-' . $amount;
        $surplus['payment'] = '';
        $surplus['rec_id'] = insert_user_account($surplus, $amount);
        // 如果成功提交
        if ($surplus['rec_id'] > 0) {
            $content = $_LANG['surplus_appl_submit'];
            show_message($content, $_LANG['back_account_log'],
            'user.php?act=account_log', 'info');
        } else {
            $content = $_LANG['process_false'];
            show_message($content, $_LANG['back_page_up'], '', 'info');
        }
    } /*
     * 如果是会员预付款，跳转到下一步，进行线上支付的操作
     */
else {
        if ($surplus['payment_id'] <= 0) {
            show_message($_LANG['select_payment_pls']);
        }
        //获取支付方式名称
        $payment_info = array();
        $payment_info = payment_info($surplus['payment_id']);
        $surplus['payment'] = $payment_info['pay_name'];
        if ($surplus['rec_id'] > 0) {
            //更新会员账目明细
            $surplus['rec_id'] = update_user_account($surplus);
        } else {
            //插入会员账目明细
            $surplus['rec_id'] = insert_user_account($surplus,
            $amount);
        }
        //取得支付信息，生成支付代码
        $payment = unserialize_config($payment_info['pay_config']);
        //生成伪订单号, 不足的时候补0
        $order = array();
        $order['order_sn'] = $surplus['rec_id'];
        $order['user_name'] = $skyuc->userinfo['user_name'];
        $order['surplus_amount'] = $amount;
        $order['order_amount'] = $amount;
        //计算支付手续费用
        $payment_info['pay_fee'] = pay_fee($surplus['payment_id'],
        $order['surplus_amount'], 0);
        //计算此次预付款需要支付的总金额
        $order['pay_amount'] = $amount + $payment_info['pay_fee'];
        //记录支付log
        $order['log_id'] = insert_pay_log($surplus['rec_id'],
        $order['pay_amount'], $type = PAY_SURPLUS, 0);
        // 调用相应的支付方式文件
        include_once (DIR . '/includes/modules/payment/' .
         $payment_info['pay_code'] . '.php');
        // 取得在线支付方式的支付按钮
        $pay_obj = new $payment_info['pay_code']();
        $payment_info['pay_button'] = $pay_obj->get_code($order, $payment);
        // 模板赋值
        $smarty->assign('payment', $payment_info);
        $smarty->assign('pay_fee',
        price_format($payment_info['pay_fee'], false));
        $smarty->assign('amount', price_format($amount, false));
        $smarty->assign('order', $order);
        $smarty->assign('user_notice', $skyuc->options['user_notice']);
        $smarty->display('user_center.dwt');
    }
} /*------------------------------------------------------ */
//--  删除预付款订单
/*------------------------------------------------------ */
elseif ($action == 'cancel') {
    require_once (DIR . '/includes/functions_users.php');
    $id = $skyuc->input->clean_gpc('g', 'id', TYPE_UINT);
    if ($id == 0 || $user_id == 0) {
        header("Location: user.php?act=account_log\n");
        exit();
    }
    $result = del_user_account($id, $user_id);
    if ($result) {
        header("Location: user.php?act=account_log\n");
        exit();
    }
} /*------------------------------------------------------ */
//--  会员通过帐目明细列表进行再付款的操作
/*------------------------------------------------------ */
elseif ($action == 'pay') {
    require_once (DIR . '/includes/functions_users.php');
    require (DIR . '/includes/functions_payment.php');
    require (DIR . '/includes/functions_order.php');
    //变量初始化
    $skyuc->input->clean_array_gpc('g',
    array('id' => TYPE_UINT, 'pid' => TYPE_UINT));
    $surplus_id = $skyuc->GPC['id'];
    $payment_id = $skyuc->GPC['pid'];
    if ($surplus_id == 0) {
        header("Location: user.php?act=account_log\n");
        exit();
    }
    //如果原来的支付方式已禁用或者已删除, 重新选择支付方式
    if ($payment_id == 0) {
        header(
        "Location: user.php?act=account_deposit&id=" . $surplus_id . "\n");
        exit();
    }
    //获取单条会员帐目信息
    $order = array();
    $order = get_surplus_info($surplus_id);
    //支付方式的信息
    $payment_info = array();
    $payment_info = payment_info($payment_id);
    /*
     * 如果当前支付方式没有被禁用，进行支付的操作
     */
    if (! empty($payment_info)) {
        //取得支付信息，生成支付代码
        $payment = unserialize_config($payment_info['pay_config']);
        //生成伪订单号
        $order['order_sn'] = $surplus_id;
        //获取需要支付的log_id
        $order['log_id'] = get_paylog_id($surplus_id,
        $pay_type = PAY_SURPLUS);
        $order['user_name'] = $skyuc->userinfo['user_name'];
        $order['surplus_amount'] = $order['amount'];
        //计算支付手续费用
        $payment_info['pay_fee'] = pay_fee($payment_id,
        $order['surplus_amount'], 0);
        //计算此次预付款需要支付的总金额
        $order['order_amount'] = $order['surplus_amount'] +
         $payment_info['pay_fee'];
        //如果支付费用改变了，也要相应的更改pay_log表的order_amount
        $order_amount = $db->query_first_slave(
        'SELECT order_amount FROM ' . TABLE_PREFIX . 'pay_log' .
         ' WHERE log_id = ' . $order['log_id']);
        if ($order_amount['order_amount'] != $order['order_amount']) {
            $db->query_write(
            'UPDATE ' . TABLE_PREFIX . 'pay_log' . ' SET order_amount = ' .
             $order['order_amount'] . ' WHERE log_id = ' . $order['log_id']);
        }
        // 调用相应的支付方式文件
        include_once (DIR . '/includes/modules/payment/' .
         $payment_info['pay_code'] . '.php');
        // 取得在线支付方式的支付按钮
        $pay_obj = new $payment_info['pay_code']();
        $payment_info['pay_button'] = $pay_obj->get_code($order, $payment);
        // 模板赋值
        $smarty->assign('payment', $payment_info);
        $smarty->assign('order', $order);
        $smarty->assign('pay_fee', price_format($payment_info['pay_fee']));
        $smarty->assign('amount', price_format($order['surplus_amount']));
        $smarty->assign('action', 'act_account');
        $smarty->assign('user_notice', $skyuc->options['user_notice']);
        $smarty->display('user_center.dwt');
    } /*
     * 重新选择支付方式
     */
else {
        $smarty->assign('payment', get_online_payment_list());
        $smarty->assign('order', $order);
        $smarty->assign('action', 'account_deposit');
        $smarty->assign('user_notice', $skyuc->options['user_notice']);
        $smarty->display('user_center.dwt');
    }
} /*------------------------------------------------------ */
//--  删除留言
/*------------------------------------------------------ */
elseif ($action == 'del_msg') {
    $id = $skyuc->input->clean_gpc('g', 'id', TYPE_UINT);
    if ($id > 0) {
        $sql = 'SELECT user_id, message_img FROM ' . TABLE_PREFIX . 'feedback' .
         ' WHERE msg_id = ' . $id;
        $row = $db->query_first_slave($sql);
        if ($row && $row['user_id'] == $user_id) {
            // 验证通过，删除留言，回复，及相应文件
            if ($row['message_img']) {
                @unlink(
                DIR . '/' . $skyuc->config['Misc']['imagedir'] . '/feedbackimg/' .
                 $row['message_img']);
            }
            $sql = 'DELETE FROM ' . TABLE_PREFIX . 'feedback' .
             ' WHERE msg_id = ' . $id . ' OR parent_id = ' . $id;
            $db->query_write($sql);
        }
    }
    header("Location: user.php?act=message_list\n");
    exit();
} /*------------------------------------------------------ */
//--  积分兑换页面
/*------------------------------------------------------ */
elseif ($action == 'transform_points') {
    $rule = array();
    if (! empty($skyuc->options['points_rule'])) {
        $rule = unserialize($skyuc->options['points_rule']);
    }
    $cfg = array();
    if (! empty($skyuc->options['integrate_config'])) {
        $cfg = unserialize($skyuc->options['integrate_config']);
        $_LANG['exchange_points'][0] = empty($cfg['uc_lang']['credits'][0][0]) ? $_LANG['exchange_points'][0] : $cfg['uc_lang']['credits'][0][0];
        // $_LANG['exchange_points'][1] = empty($cfg['uc_lang']['credits'][1][0])? $_LANG['exchange_points'][1] : $cfg['uc_lang']['credits'][1][0];
    }
    $sql = 'SELECT user_name, pay_point FROM ' . TABLE_PREFIX . 'users' .
     ' WHERE user_id=' . $user_id;
    $row = $db->query_first($sql);
    if ($skyuc->options['integrate_code'] == 'ucenter') {
        $exchange_type = 'ucenter';
        $to_credits_options = array();
        $out_exchange_allow = array();
        foreach ($rule as $credit) {
            $out_exchange_allow[$credit['appiddesc'] . '|' .
             $credit['creditdesc'] . '|' . $credit['creditsrc']] = $credit['ratio'];
            if (! array_key_exists(
            $credit['appiddesc'] . '|' . $credit['creditdesc'],
            $to_credits_options)) {
                $to_credits_options[$credit['appiddesc'] . '|' .
                 $credit['creditdesc']] = $credit['title'];
            }
        }
        $smarty->assign('selected_org', $rule[0]['creditsrc']);
        $smarty->assign('selected_dst',
        $rule[0]['appiddesc'] . '|' . $rule[0]['creditdesc']);
        $smarty->assign('descreditunit', $rule[0]['unit']);
        $smarty->assign('orgcredittitle',
        $_LANG['exchange_points'][$rule[0]['creditsrc']]);
        $smarty->assign('descredittitle', $rule[0]['title']);
        $smarty->assign('descreditamount', round((1 / $rule[0]['ratio']), 2));
        $smarty->assign('to_credits_options', $to_credits_options);
        $smarty->assign('user_notice', $skyuc->options['user_notice']);
        $smarty->assign('out_exchange_allow', $out_exchange_allow);
    } else {
        $exchange_type = 'other';
        $bbs_points_name = $user->get_points_name();
        $total_bbs_points = $user->get_points($row['user_name']);
        // 论坛积分
        $bbs_points = array();
        foreach ($bbs_points_name as $key => $val) {
            $bbs_points[$key] = array('title' => $_LANG['bbs'] . $val['title'],
            'value' => $total_bbs_points[$key]);
        }
        // 兑换规则
        $rule_list = array();
        foreach ($rule as $key => $val) {
            $rule_key = substr($key, 0, 1);
            $bbs_key = substr($key, 1);
            $rule_list[$key]['rate'] = $val;
            switch ($rule_key) {
                case TO_P:
                    $rule_list[$key]['from'] = $_LANG['bbs'] .
                     $bbs_points_name[$bbs_key]['title'];
                    $rule_list[$key]['to'] = $_LANG['pay_point'];
                    break;
                case FROM_P:
                    $rule_list[$key]['from'] = $_LANG['pay_point'];
                    $_LANG['bbs'] . $bbs_points_name[$bbs_key]['title'];
                    $rule_list[$key]['to'] = $_LANG['bbs'] .
                     $bbs_points_name[$bbs_key]['title'];
                    break;
            }
        }
        $smarty->assign('bbs_points', $bbs_points);
        $smarty->assign('rule_list', $rule_list);
    }
    $smarty->assign('site_points', array('pay_point' => $row['pay_point']));
    $smarty->assign('exchange_type', $exchange_type);
    $smarty->assign('action', $action);
    $smarty->assign('lang', $_LANG);
    $smarty->assign('user_notice', $skyuc->options['user_notice']);
    $smarty->display('user_center.dwt');
} /*------------------------------------------------------ */
//--  执行积分兑换
/*------------------------------------------------------ */
elseif ($action == 'act_transform_points') {
    $skyuc->input->clean_array_gpc('p',
    array('rule_index' => TYPE_STR, 'num' => TYPE_UINT));
    $rule_index = $skyuc->GPC['rule_index'];
    $num = $skyuc->GPC['num'];
    if ($num <= 0 || $num != floor($num)) {
        show_message($_LANG['invalid_points'], $_LANG['transform_points'],
        'user.php?act=transform_points');
    }
    $num = floor($num); //格式化为整数
    $bbs_key = substr($rule_index, 1);
    $rule_key = substr($rule_index, 0, 1);
    $max_num = 0;
    // 取出用户数据
    $sql = 'SELECT user_name, pay_point FROM ' . TABLE_PREFIX . 'users' .
     ' WHERE user_id=' . $user_id;
    $row = $db->query_first($sql);
    $bbs_points = $user->get_points($row['user_name']);
    $points_name = $user->get_points_name();
    $rule = array();
    if ($skyuc->options['points_rule']) {
        $rule = unserialize($skyuc->options['points_rule']);
    }
    list ($from, $to) = explode(':', $rule[$rule_index]);
    $max_points = 0;
    switch ($rule_key) {
        case TO_P:
            $max_points = $bbs_points[$bbs_key];
            break;
        case FROM_P:
            $max_points = $row['pay_point'];
            break;
    }
    // 检查积分是否超过最大值
    if ($max_points <= 0 || $num > $max_points) {
        show_message($_LANG['overflow_points'], $_LANG['transform_points'],
        'user.php?act=transform_points');
    }
    switch ($rule_key) {
        case TO_P:
            $result_points = floor($num * $to / $from);
            $user->set_points($skyuc->userinfo['user_name'],
            array($bbs_key => 0 - $num)); //调整论坛积分
            log_account_change($skyuc->userinfo['userid'], 0,
            $result_points, $_LANG['transform_points'], ACT_OTHER);
            show_message(
            sprintf($_LANG['to_pay_point'], $num,
            $points_name[$bbs_key]['title'], $result_points),
            $_LANG['transform_points'], 'user.php?act=transform_points');
        case FROM_P:
            $result_points = floor($num * $to / $from);
            log_account_change($skyuc->userinfo['userid'], 0, 0 - $num,
            $_LANG['transform_points'], ACT_OTHER); //调整网站积分
            $user->set_points($skyuc->userinfo['user_name'],
            array($bbs_key => $result_points)); //调整论坛积分
            show_message(
            sprintf($_LANG['from_pay_point'], $num, $result_points,
            $points_name[$bbs_key]['title']), $_LANG['transform_points'],
            'user.php?act=transform_points');
    }
} /*------------------------------------------------------ */
//--  执行 ucenter 兑换点数
/*------------------------------------------------------ */
elseif ($action == 'act_transform_ucenter_points') {
    $skyuc->input->clean_array_gpc('p',
    array('amount' => TYPE_UINT, 'fromcredits' => TYPE_UINT,
    'tocredits' => TYPE_STR));
    $rule = array();
    if ($skyuc->options['points_rule']) {
        $rule = unserialize($skyuc->options['points_rule']);
    }
    $site_points = array(0 => 'pay_point');
    $sql = 'SELECT user_name, pay_point FROM ' . TABLE_PREFIX . 'users' .
     ' WHERE user_id=' . $user_id;
    $row = $db->query_first($sql);
    $exchange_amount = $skyuc->GPC['amount'];
    $fromcredits = $skyuc->GPC['fromcredits'];
    $tocredits = $skyuc->GPC['tocredits'];
    $cfg = unserialize($skyuc->options['integrate_config']);
    if (! empty($cfg)) {
        $_LANG['exchange_points'][0] = empty($cfg['uc_lang']['credits'][0][0]) ? $_LANG['exchange_points'][0] : $cfg['uc_lang']['credits'][0][0];
        // $_LANG['exchange_points'][1] = empty($cfg['uc_lang']['credits'][1][0])? $_LANG['exchange_points'][1] : $cfg['uc_lang']['credits'][1][0];
    }
    list ($appiddesc, $creditdesc) = explode('|', $tocredits);
    $ratio = 0;
    if ($exchange_amount <= 0) {
        show_message($_LANG['invalid_points'], $_LANG['transform_points'],
        'user.php?act=transform_points');
    }
    if ($exchange_amount > $row[$site_points[$fromcredits]]) {
        show_message($_LANG['overflow_points'], $_LANG['transform_points'],
        'user.php?act=transform_points');
    }
    foreach ($rule as $credit) {
        if ($credit['appiddesc'] == $appiddesc &&
         $credit['creditdesc'] == $creditdesc &&
         $credit['creditsrc'] == $fromcredits) {
            $ratio = $credit['ratio'];
            break;
        }
    }
    if ($ratio == 0) {
        show_message($_LANG['exchange_deny'], $_LANG['transform_points'],
        'user.php?act=transform_points');
    }
    $netamount = floor($exchange_amount / $ratio);
    include_once (DIR . '/includes/functions_uc.php');
    $result = exchange_points($skyuc->userinfo['userid'], $fromcredits,
    $creditdesc, $appiddesc, $netamount);
    if ($result === true) {
        $sql = 'UPDATE ' . TABLE_PREFIX . 'users' .
         " SET {$site_points[$fromcredits]}={$site_points[$fromcredits]}-'$exchange_amount' WHERE user_id='{$skyuc->userinfo['userid']}'";
        $db->query_write($sql);
        $sql = 'INSERT INTO ' . TABLE_PREFIX . 'account_log' .
         "(user_id, {$site_points[$fromcredits]}, change_time, change_desc, change_type)" .
         " VALUES ('{$skyuc->userinfo['userid']}', '-$exchange_amount', '" .
         TIMENOW . "', '" . $cfg['uc_lang']['exchange'] . "', '98')";
        $db->query_write($sql);
        //重新获取用户缓存
        fetch_userinfo($user_id);
        show_message(
        sprintf($_LANG['exchange_success'], $exchange_amount,
        $_LANG['exchange_points'][$fromcredits], $netamount, $credit['title']),
        $_LANG['transform_points'], 'user.php?act=transform_points');
    } else {
        show_message($_LANG['exchange_error_1'], $_LANG['transform_points'],
        'user.php?act=transform_points');
    }
}
?>