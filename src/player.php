<?php
/**
 * SKYUC! 播放器页面
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */
// ####################### 设置 PHP 环境 ###########################
//error_reporting(E_ALL & ~E_NOTICE);
// #################### 定义重要常量	 #######################
define('NOSHUTDOWNFUNC', true);
define('THIS_SCRIPT', 'player');
define('CSRF_PROTECTION', true);
define('CSRF_SKIP_LIST', '');
// 从缓存中获取指定数据
$specialtemplates = array('players', 'servers');
require(dirname(__FILE__) . '/global.php');
require(DIR . '/includes/functions_player.php');
error_reporting(E_ALL);
/*------------------------------------------------------ */
//-- INPUT过滤
/*------------------------------------------------------ */
$skyuc->input->clean_array_gpc('g', array('mov_id' => TYPE_UINT, 'look_id' => TYPE_UINT, 'player' => TYPE_STR));
$uid = $skyuc->session->vars['userid'];
//视频信息
$video = array(
    'show_name', //本集名称
    'show_point', //扣点
    'skyuc_src', //播放地址
    'skyuc_nextsrc', //下集地址
    'playlist', //播放列表
    'skyuc_title', //片名
    'prevPageUrl', //上一页
    'nextPageUrl', //下一页
    'mov_id' => $skyuc->GPC['mov_id'], //影片ID
    'look_id' => $skyuc->GPC['look_id'], //观看集数
    'player' => $skyuc->GPC['player'] //播放器
);

// 如果type参数为空或ID参数为空,则关闭窗口
if ($video['mov_id'] === 0 || $video['look_id'] === 0) {
    top_close($_LANG['operation_error']);
}

read_film_info($video); //读取影片信息

$validated = 0; //0=禁止点播，1=免费点播，2=计时会员，3=计点会员，4=计点重复点播,5=IP段

if ($video['show_point'] === 0 && $skyuc->options['no_login'] == 1) {
    $validated = 1;
} else {
    // 取得网吧信息
    $netbar = verify_ip_netbar();
    // 未登录处理
    if ($uid == 0 && empty($netbar)) {
        top_close($_LANG['nologin_empty']);
    } elseif (!empty($uid)) {
        //频道和播放器点播权限检查
        if (!get_user_rank($video['mov_id'], $video['player'])) {
            top_close($_LANG['block_cate']);
        }
    }
    /*------------------------------------------------------ */
    //-- 网吧验证
    /*------------------------------------------------------ */
    if (!empty($netbar)) {
        $online_count = online_count($netbar['id']);
        $max = abs($netbar['maxuser']);
        if ((TIMENOW - 86400) > $netbar['endtime']) {
            $smarty->assign('action', 'netbar_date'); //网吧时间到期
            $smarty->display('player.dwt');
            exit();
        } elseif ($online_count > $max && !empty($max)) {
            $bar['online'] = $online_count;
            $bar['max'] = $max;
            $smarty->assign('action', 'netbar_max'); //超过最大人数限制
            $smarty->assign('bar', $bar);
            $smarty->display('player.dwt');
            exit();
        }
        $validated = 5;
    } /*------------------------------------------------------ */
    //-- 会员验证
    /*------------------------------------------------------ */
    elseif (!empty($uid)) {
        if (empty($skyuc->userinfo['user_rank'])) {
            top_close($_LANG['nologin_empty']);
        }
        //验证会员组开放时间段
        $allowHours = verify_allow_hours();

        if ($allowHours !== true) {
            top_close(sprintf($_LANG['block_hours'], $allowHours));
        }

        //计时会员有效期验证
        if ($skyuc->userinfo['usertype'] == 1) {
            if ($skyuc->userinfo['unit_date'] > TIMENOW || $video['show_point'] === 0) {
                $validated = 2;
            } else {
                $smarty->assign('msg', $_LANG['over_date']);
                $smarty->assign('action', 'user');
                $smarty->display('player.dwt');
                exit();
            }
        } //计点会员点数验证
        elseif ($skyuc->userinfo['usertype'] == 0) {

            if (($time = last_play_time($video)) !== false && strpos($time, ':') !== false) {
                // 提示余下多少小时点播不扣点
                $validated = 4;
                $hours = explode(':', $time);
                $message = sprintf($_LANG['message_nopoints'], $hours[0], $hours[1]);
                window_message($message);
            } elseif ($skyuc->userinfo['user_point'] > $video['show_point']) {
                // 提示此次点播扣除点数
                $validated = 3;
                $message = sprintf($_LANG['message_points'], $video['show_point']);
                window_message($message);
                // 减去会员点数
                $sql = 'UPDATE ' . TABLE_PREFIX . 'users' .
                    ' SET user_point = user_point -' . $video['show_point'] .
                    ' WHERE user_id=' . $uid;
                $db->query_write($sql);
                // 重建用户点数缓存
                $skyuc->userinfo['user_point'] -= $video['show_point'];
            } else {
                $smarty->assign('msg', $_LANG['over_count']);
                $smarty->assign('action', 'user');
                $smarty->display('player.dwt');
                exit();
            }
        }
    }
}
/*------------------------------------------------------ */
//-- 播放页面
/*------------------------------------------------------ */
if ($validated > 0) {
    //记录日志：免费点播、会员点播
    if ($validated < 5) {
        insert_play_log($video);
        //会员点播推送feed到uc
        if ($validated > 1) {
            user_uc_call('add_feed', array($video['mov_id'], PLAY_SHOW));
        }
    }
    // 更新点击次数
    $db->query_write(
        'UPDATE ' . TABLE_PREFIX . 'show' .
        " SET click_count = click_count + 1, click_month = click_month + 1, click_week = click_week + 1, click_time = '" .
        TIMENOW . "' WHERE show_id = '$mov_id'");
    //现在时间是12:00~12:10，可能是中午或午夜
    if (skyuc_date('hi') < '1210' and skyuc_date('hi') > '1200') {
        if (skyuc_date('w') == 1) {
            // 今天是周一，清空上周记录
            $skyuc->db->query_write(
                'UPDATE ' . TABLE_PREFIX . 'show' . ' SET click_week =0 ');
        } elseif (skyuc_date('j') == 1) {
            //今天是本月1号， 清空上个月记录
            $skyuc->db->query_write(
                'UPDATE ' . TABLE_PREFIX . 'show' . ' SET click_month =0');
        }
    }
    if (!isset($skyuc->players[$video['player']])) {
        top_close($skyuc->lang['operation_error']);
    }
    $player_code = $skyuc->players[$video['player']]['player_code'];
    //取得播放器代码中的PHP
    $player_phpcode = html2php($player_code);
    //去除播放器代码中的PHP
    $player_code = str_replace($player_phpcode, '', $player_code);
    //播放器中PHP代码修正
    $player_phpcode = str_replace(array('{php}', '{/php}'), '',
        $player_phpcode);
    if ($player_phpcode) {
        eval($player_phpcode);
    }
    $video['skyuc_title'] = iif(!empty($video['skyuc_title']), $video['skyuc_title'], $video['look_id']);
    //替换播放器代码
    $player_code = str_replace(
        array('{$skyuc_src}', '{$skyuc_name}', '{$skyuc_title}', '{$skyuc_nextpage}', '{$skyuc_prevpage}', '{$skyuc_nextsrc}'),
        array($video['skyuc_src'], $video['skyuc_name'], $video['skyuc_title'], $video['nextPageUrl'], $video['prevPageUrl'], $video['skyuc_nextsrc']), $player_code);
    //实时统计会员在线时长
    if ($uid) {
        $player_code .= '<script type="text/javascript" language="javascript">$(function(){setInterval(function(){$("#refresh").load("ajax.php?do=refresh&user=' .
            $uid . '&mov_id=' . $video['mov_id'] . '&look_id=' . $video['look_id'] .
            '&rnd="+(Math.ceil(Math.random()*1000))+" #refresh");},60000);});</script><div id="refresh" style="display:none"></div>';
    }
    $smarty->assign('player_code', $player_code);
    $smarty->assign('skyuc_name', $video['show_name']);
    $smarty->assign('skyuc_title', $video['skyuc_title']);
    $smarty->assign('action', 'player');
    //播放列表
    $smarty->assign('playlist', $video['playlist']);
    $smarty->assign('playing', $video['look_id']);
    $smarty->assign('player', $video['player']);
    $smarty->assign('mov_id', $video['mov_id']);
    $smarty->assign('id', $video['mov_id']);
    $smarty->assign('type', 0);
    $smarty->display('player.dwt');
}