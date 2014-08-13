<?php
/**
 * SKYUC! AJAX 处理程序
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
define('THIS_SCRIPT', 'ajax');
define('CSRF_PROTECTION', true);
define('LOCATION_BYPASS', 1);
define('NOSHUTDOWNFUNC', 1);
define('INGORE_VISIT_STATS', true);
require (dirname(__FILE__) . '/global.php');
require (DIR . '/includes/class_json.php');
// #######################################################################
// ######################## 开始主脚本 ############################
// #######################################################################
$skyuc->input->clean_gpc('r', 'do', TYPE_STR);
// ###########################################################################
/*
 * SKYUC! AJAX 验证码图像
 */
if ($skyuc->GPC['do'] == 'imagereg') {
    define('SKIP_SMARTY', 1);
    define('NOCOOKIES', 1);
    $skyuc->input->clean_array_gpc('r',
    array('type' => TYPE_STR, 'hash' => TYPE_STR, 'i' => TYPE_STR));
    require (DIR . '/includes/class_humanverify.php');
    $verification = & HumanVerify::fetch_library($skyuc);
    if ($skyuc->GPC['hash'] !== '') {
        $verification->delete_token($skyuc->GPC['hash']);
    }
    $human_verify = $verification->generate_token();
    $json = new JSON();
    $result = array('error' => 0, 'message' => '', 'content' => '');
    $result['message'] = $human_verify['hash'];
    echo $json->encode($result);
    exit();
} /*
 * SKYUC! AJAX刷新会员在线时长
 */
elseif ($skyuc->GPC['do'] == 'refresh') {
    $skyuc->input->clean_array_gpc('r',
    array('user' => TYPE_UINT, 'mov_id' => TYPE_UINT, 'look_id' => TYPE_UINT,
    'host' => TYPE_STR));
    //增加观看时长一分钟
    $sql = 'UPDATE ' . TABLE_PREFIX . 'play_log' .
     " SET minute=minute+1 WHERE user_id='" . $skyuc->GPC['user'] .
     "' AND mov_id ='" . $skyuc->GPC['mov_id'] . "' AND url_id ='" .
     $skyuc->GPC['look_id'] . "'";
    $skyuc->db->query_write($sql);
    //会员在线时长增加一分钟，且会员最后活动时间为当前时间
    $sql = 'UPDATE ' . TABLE_PREFIX . 'users' .
     " SET minute=minute+1, lastactivity='" . TIMENOW . "' WHERE user_id='" .
     $skyuc->GPC['user'] . "'";
    $skyuc->db->query_write($sql);
} /*
 * SKYUC! AJAX搜索建议提示
 */
elseif ($skyuc->GPC['do'] == 'search') {
    $skyuc->input->clean_array_gpc('r', array('keywords' => TYPE_STR));
    if (! $skyuc->GPC_exists['keywords']) {
        exit();
    }
    require (DIR . '/includes/functions_search.php');
    include_once( DIR.'/includes/class_search.php');
    $nt = new normalizeText(4,false);

    $val = $skyuc->GPC['keywords'];
    $searchstring = sanitize_search_query($nt->parseQuery($val));
    // 查询影片
    $sql = "SELECT `searchid` , MATCH (searchcore_text.title, searchcore_text.keywordtext) AGAINST ('" .
     $searchstring . "' IN BOOLEAN MODE) AS score
				FROM " . TABLE_PREFIX .
     "searchcore_text AS searchcore_text
				WHERE MATCH (searchcore_text.title, searchcore_text.keywordtext)
				AGAINST ('" . $searchstring . "' IN BOOLEAN MODE)" . $categories;
    $sql = $db->query_limit($sql, 10);
    $searchcore_ids = $db->query_all_slave($sql);
    if (empty($searchcore_ids)) {
        echo '';
        exit();
    }
    $show_ids = array();
    foreach ($searchcore_ids as $value) {
        $show_ids[] = $value['searchid'];
    }
    $where = ' AND searchcore_text.show_id IN (' . implode(',', $show_ids) . ')';
    $sql = 'SELECT searchcore_text.show_id, searchcore_text.title FROM ' .
     TABLE_PREFIX . 'show' . ' AS searchcore_text ' .
     '  WHERE searchcore_text.is_show =1  ' . $where .
     '  ORDER BY click_count DESC';
    $res = $db->query_read_slave($sql);
    header('Content-Type: application/xml; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Fri, 14 Mar 1980 20:53:00 GMT');
    header('Last-Modified: ' . gmdate('r'));
    header('Pragma: no-cache');
    require_once (DIR . '/includes/class_xml.php');
    $xml = new XML_Builder($skyuc);
    $xml->add_group('root');
    $res = $db->query_read_slave($sql);
    if ($res !== false) {
        while ($row = $db->fetch_array($res)) {
            $xml->add_tag('text', $row['title']);
        }
        $xml->close_group();
        $xml->print_xml();
        $xml = null;
    }
} /*
 * SKYUC! AJAX网站调查处理
 */
elseif ($skyuc->GPC['do'] == 'vote') {
    $skyuc->input->clean_array_gpc('r',
    array('vote' => TYPE_UINT, 'options' => TYPE_STR));
    if (! $skyuc->GPC_exists['vote'] || ! $skyuc->GPC_exists['options']) {
        header("Location: ./\n");
        exit();
    }
    $res = array('error' => 0, 'message' => '', 'content' => '');
    $vote_id = $skyuc->GPC['vote'];
    $options = $skyuc->GPC['options'];
    require (DIR . '/includes/control/ajax.php');
    if (vote_already_submited($vote_id)) {
        $res['error'] = 1;
        $res['message'] = $_LANG['vote_ip_same'];
    } else {
        save_vote($vote_id, $options);
        $vote = get_vote($vote_id);
        if (! empty($vote)) {
            $smarty->assign('vote_id', $vote['id']);
            $smarty->assign('vote', $vote['content']);
        }
        $str = $smarty->fetch('library/vote.lbi');
        $pattern = '/(?:<(\w+)[^>]*> .*?)?<div\s+id="SKYUC_VOTE">(.*)<\/div>(?:.*?<\/\1>)?/is';
        if (preg_match($pattern, $str, $match)) {
            $res['content'] = $match[2];
        }
        $res['message'] = $_LANG['vote_success'];
    }
    $json = new JSON();
    echo $json->encode($res);
} /**
 * SKYUC! AJAX留言：报错、求片
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */
elseif ($skyuc->GPC['do'] == 'adderror') {
    // 载入语言文件
    require_once (DIR . '/languages/' . $skyuc->options['lang'] .
     '/user.php');
    $skyuc->input->clean_array_gpc('r',
    array('type' => TYPE_UINT, 'title' => TYPE_STR, 'cont' => TYPE_STR));
    if (! $skyuc->GPC_exists['type'] ||
     empty($skyuc->session->vars['userid'])) {
        // 只有在没有留言类型以及没有登陆的情况下才跳转
        header("Location: ./\n");
        exit();
    }
    // 参数
    $title = $skyuc->GPC['title']; // 留言标题
    $type = $skyuc->GPC['type']; //留言类型，2为报错，3为求片
    $cont = $skyuc->GPC['cont']; // 留言内容
    require (DIR . '/includes/control/message.php');
    $message = array('user_id' => $skyuc->session->vars['userid'],
    'user_name' => $skyuc->userinfo['user_name'],
    'user_email' => $skyuc->userinfo['email'], 'msg_type' => $type,
    'msg_title' => $title, 'msg_content' => $cont, 'upload' => array());
    if (add_message($message)) {
        $content = sprintf($_LANG['msg_success_ajax'], $_LANG['type'][$type]);
    } else {
        $content = $_LANG['msg_fail_ajax'];
    }
    $smarty->assign('act', 'response');
    $smarty->assign('content', $content);
    $smarty->display('openbox.dwt');
} /**
 * SKYUC! AJAX添加影片评分
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */
elseif ($skyuc->GPC['do'] == 'addrate') {
    $skyuc->input->clean_array_gpc('r',
    array('mid' => TYPE_UINT, 'rate' => TYPE_UINT));
    if (! $skyuc->GPC_exists['mid'] && ! $skyuc->GPC_exists['rate']) {
        // 只有在没有提交评分以及没有影片编号的情况下才跳转
        header("Location: ./\n");
        exit();
    }
    // 参数
    $mid = $skyuc->GPC['mid']; // 影片编号
    $rate = $skyuc->GPC['rate']; // 用户评分
    if (isset($_COOKIE[COOKIE_PREFIX . 'MOV_ID']["$mid"])) {
        echo 'FALSE';
        die();
    }
    //更新评分
    $sql = 'UPDATE ' . TABLE_PREFIX . 'show' . ' SET ' .
     ' moviepoint= moviepoint+' . $rate . ', userspoint=userspoint+1 ' .
     ' WHERE show_id=' . $mid;
    $db->query_write($sql);
    // 设置cookie
    $time = TIMENOW + 86400;
    skyuc_setcookie("MOV_ID[$mid]", $mid, $time);
    // 获得评分
    $sql = 'SELECT moviepoint, userspoint FROM ' . TABLE_PREFIX . 'show' .
     ' WHERE show_id = ' . $mid;
    $row = $db->query_first($sql);
    if (! empty($row)) {
        $moviepoint = ceil($row['moviepoint'] / $row['userspoint']);
        if ($moviepoint > 0 && $moviepoint < 10) {
            echo $moviepoint;
        } else {
            echo 10;
        }
    }
} /*
 * SKYUC! 页面对话框
 */
elseif ($skyuc->GPC['do'] == 'openbox') {
    // 载入语言文件
    require_once (DIR . '/languages/' . $skyuc->options['lang'] .
     '/user.php');
    $skyuc->input->clean_array_gpc('r',
    array('mid' => TYPE_UINT, 'type' => TYPE_UINT, 'title' => TYPE_STR,
    'titleshow' => TYPE_STR));
    $mid = $skyuc->GPC['mid'];
    $type = $skyuc->GPC['type'];
    $title = $skyuc->GPC['title'];
    $titleshow = $skyuc->GPC['titleshow'];
    if ($type == 0) {
        exit();
    }
    if (empty($skyuc->session->vars['userid'])) {
        $msg = $_LANG['login_please'];
        echo "<script>alert(\"$msg\");new parent.dialog().reset();</script>";
        exit();
    }
    $titleshow = is_utf8($titleshow) ? $titleshow : skyuc_iconv('GBK', 'UTF-8',
    $titleshow);
    if ($mid > 0) {
        $title = is_utf8($title) ? $title : skyuc_iconv('GBK', 'UTF-8', $title);
        $content = $title . '(ID:' . $mid . '):' . $_LANG['type'][$type];
    } else {
        $title = '';
        $content = '';
    }
    $smarty->assign('titleshow', $titleshow);
    $smarty->assign('title', $title);
    $smarty->assign('type', $type);
    $smarty->assign('content', $content);
    $smarty->display('openbox.dwt');
} /*
 * SKYUC! 提交用户评论
 */
elseif ($skyuc->GPC['do'] == 'comment') {
    require (DIR . '/includes/control/ajax.php');
    if (! isset($_REQUEST['cmt']) && ! isset($_REQUEST['act'])) {
        header("Location: ./\n");
        exit();
    }
    $json = new JSON();
    $result = array('error' => 0, 'message' => '', 'content' => '');
    if (empty($_REQUEST['act'])) {
        //默认为添加评论内容
        $cmt = $json->decode(
        $skyuc->db->escape_string($_REQUEST['cmt']));
        $cmt->page = 1;
        if (empty($cmt) || ! isset($cmt->type) || ! isset($cmt->id)) {
            $result['error'] = 1;
            $result['message'] = $_LANG['invalid_comments'];
        } else {
            if ((intval($skyuc->options['humanverify']) & HV_COMMENT)) {
                // 检查验证码是否正确
                $humanverify = array();
                $humanverify['input'] = $cmt->input;
                $humanverify['hash'] = $cmt->hash;
                require_once (DIR . '/includes/class_humanverify.php');
                $verify = & HumanVerify::fetch_library($skyuc);
                if (! $verify->verify_token($humanverify)) {
                    $result['error'] = 1;
                    $result['message'] = $_LANG['invalid_captcha'];
                } else {
                    // 无错误就保存留言
                    if (empty($result['error'])) {
                        add_comment($cmt);
                    }
                }
            } else {
                // 没有验证码时，用时间来限制机器人发帖或恶意发评论
                if (! isset($_COOKIE[COOKIE_PREFIX . 'send_time'])) {
                    $send_time = 0;
                } else {
                    $send_time = intval($_COOKIE[COOKIE_PREFIX . 'send_time']);
                }
                // 小于30秒禁止发评论
                if ((TIMENOW - $send_time) < 30) {
                    $result['error'] = 1;
                    $result['message'] = $_LANG['cmt_spam_warning'];
                } else {
                    // 无错误就保存留言
                    if (empty($result['error'])) {
                        add_comment($cmt);
                    }
                }
                skyuc_setcookie('send_time', TIMENOW + 30, TIMENOW + 3600);
            }
        }
    } else {
        /*
		     * act 参数不为空
		     * 默认为评论内容列表
		     * 根据 _GET 创建一个静态对象
		     */
        $cmt = new stdClass();
        $cmt->id = ! empty($_GET['id']) ? intval($_GET['id']) : 0;
        $cmt->type = ! empty($_GET['type']) ? intval($_GET['type']) : 0;
        $cmt->page = ! empty($_GET['page']) ? intval($_GET['page']) : 1;
    }
    if (empty($result['error'])) {
        $comments = assign_comment($cmt->id, $cmt->type, $cmt->page);
        $smarty->assign('comment_type', $cmt->type);
        $smarty->assign('id', $cmt->id);
        $smarty->assign('username', $_SESSION['user_name']);
        $smarty->assign('email', $_SESSION['email']);
        $smarty->assign('comments', $comments['comments']);
        $smarty->assign('pager', $comments['pager']);
        // 验证码相关设置
        if ((intval($skyuc->options['humanverify']) & HV_COMMENT)) {
            $smarty->assign('enabled_captcha', 1);
        }
        $result['message'] = $skyuc->options['comment_check'] ? $_LANG['cmt_submit_wait'] : $_LANG['cmt_submit_done'];
        $result['content'] = $smarty->fetch('library/comments_list.lbi');
    }
    echo $json->encode($result);
} /*
 * SKYUC! 用户评论顶踩
 */
elseif ($skyuc->GPC['do'] == 'cmtidea') {
    $skyuc->input->clean_array_gpc('p',
    array('cmtid' => TYPE_UINT, 'id' => TYPE_UINT));
    if ($skyuc->GPC['cmtid'] === 0 OR
    $skyuc->GPC['cmtid'] === intval($_COOKIE[COOKIE_PREFIX . 'cmtid'])) {
        exit();
    }
    skyuc_setcookie('cmtid', $skyuc->GPC['cmtid'], TIMENOW + 3600);
    $json = new JSON();
    if ($skyuc->GPC['id'] === 3) {
       $sql = 'UPDATE ' . TABLE_PREFIX . 'comment' .
     		" SET against=against+1 " .
            " WHERE comment_id='" . $skyuc->GPC['cmtid'] . "'";
       $db->query_write($sql);
       $sql = 'SELECT against FROM ' . TABLE_PREFIX . 'comment' .
            " WHERE comment_id='" . $skyuc->GPC['cmtid'] . "'";
       $row = $db->query_first_slave($sql);
       $result = $_LANG['against'].'[-'. $row['against'] .']';
    }elseif ($skyuc->GPC['id'] === 2) {
        $sql = 'UPDATE ' . TABLE_PREFIX . 'comment' .
     		" SET agree=agree+1 " .
            " WHERE comment_id='" . $skyuc->GPC['cmtid'] . "'";
       $db->query_write($sql);
       $sql = 'SELECT agree FROM ' . TABLE_PREFIX . 'comment' .
            " WHERE comment_id='" . $skyuc->GPC['cmtid'] . "'";
       $row = $db->query_first_slave($sql);
       $result = $_LANG['agree'].'[+'. $row['agree'] .']';
    }
    echo $json->encode($result);
}