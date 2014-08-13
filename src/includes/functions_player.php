<?php
/**
 * SKYUC! 播放器页面私有函数
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

/*------------------------------------------------------ */
//-- PRIVATE FUNCTION
/*------------------------------------------------------ */

/**
 * 获得指定网吧在线人数
 *
 * @access  public
 * @param   integer $netbarid 网吧ID
 * @return  array
 */
function online_count($netbarid)
{
    //利用session_id来解决LAN的问题
    @session_start();
    $online_log = DIR . '/data/caches/count_' . $netbarid . '.dat'; //保存人数的文件
    $timeout = 3600; //3600秒内没动作者,认为掉线
    $entries = @file($online_log);

    $temp = array();

    for ($i = 0; $i < count($entries); $i++) {
        $entry = explode(',', trim($entries [$i]));

        //防止第一次运行时,文件不存在报错Undefined offset: 1
        if (is_file($online_log)) {
            if (($entry [0] != session_id()) && ($entry [1] > TIMENOW)) {
                array_push($temp, $entry [0] . ',' . $entry [1] . "\n"); //取出其他浏览者的信息,并去掉超时者,保存进$temp
            }
        }
    }
    array_push($temp, session_id() . ',' . (TIMENOW + ($timeout)) . "\n"); //更新浏览者的时间
    $online_count = count($temp); //计算在线人数


    $entries = implode("", $temp);
    //写入文件
    $fp = fopen($online_log, 'w');
    flock($fp, LOCK_EX); //flock()   不能在NFS以及其他的一些网络文件系统中正常工作
    fputs($fp, $entries);
    flock($fp, LOCK_UN);
    fclose($fp);

    return $online_count;
}

/**
 * 点播记录入库
 * @param array $video 视频信息
 * @param int $show_point 当前地址扣点
 * return
 */
function insert_play_log(array $video)
{
    // 删除超过多少天的观看记录
    $time = TIMENOW - 86400 * $GLOBALS['skyuc']->options['logplayday'];
    $sql = 'DELETE FROM ' . TABLE_PREFIX . 'play_log' . " WHERE time <'" . $time . "'";
    $GLOBALS['db']->query_write($sql);

    //更新会员在线时长和点播影片总数量
    if ($GLOBALS['skyuc']->userinfo['user_id']) {
        $minute = round((TIMENOW - $GLOBALS['skyuc']->userinfo['lastactivity']) / 60);
        if ($video['look_id'] == 1) {
            $sql = 'UPDATE ' . TABLE_PREFIX . 'users' .
                " SET playcount=playcount+1, minute=minute+{$minute} WHERE user_id='" . $GLOBALS['skyuc']->userinfo['user_id'] . "'";
        } else {
            $sql = 'UPDATE ' . TABLE_PREFIX . 'users' .
                " SET minute=minute+{$minute} WHERE user_id='" . $GLOBALS['skyuc']->userinfo['user_id'] . "'";
        }
        $GLOBALS['db']->query_write($sql);
    }

    if (false !== last_play_time($video)) {
        //更新旧记录
        $sql = 'UPDATE ' . TABLE_PREFIX . 'play_log' . " SET counts=counts+1, player='" . $video['player'] .
            "', host='" . ALT_IP . "' WHERE mov_id ='" . $video['mov_id'] .
            "' AND url_id ='" . $video['look_id'] . "' AND player ='" . $video['player'] . "'";
        $GLOBALS['db']->query_write($sql);

        return;
    }
    //插入新记录
    if ($video['show_point'] > 0) {
        //每日点播和下载数量限制
        $playNum = get_user_play($GLOBALS['skyuc']->userinfo['user_id'], $video['player']);
        switch ($playNum) {
            case 1:
                top_close($GLOBALS['_LANG']['block_play']);
                exit();
                break;
            case 2:
                top_close($GLOBALS['_LANG']['block_down']);
                exit();
                break;
            default:
                break;
        }
    }
    $sql = 'INSERT INTO ' . TABLE_PREFIX . 'play_log' .
        ' (title, user_id, time, host, counts, player, mov_id, url_id) ' .
        " VALUES ('" . $GLOBALS['db']->escape_string($video['show_name']) . "','" .
        $GLOBALS['skyuc']->userinfo['user_id'] . "','" . TIMENOW . "','" . ALT_IP . "', '1', '" .
        $video['player'] . "','" . $video['mov_id'] . "','" . $video['look_id'] . "')";
    $GLOBALS['db']->query_write($sql);
    return;
}

/**
 * 最近点播时间距离现在有多久
 * @return bool|string
 */
function last_play_time(array $video)
{
    $sql = 'SELECT time FROM ' . TABLE_PREFIX . 'play_log' .
        " WHERE user_id ='" . $GLOBALS['skyuc']->userinfo['user_id'] . "' AND mov_id='" . $video['mov_id'] .
        "' AND url_id='" . $video['look_id'] . "' AND player='" . $video['player'] . "'";
    $row = $GLOBALS['db']->query_first($sql);
    if (empty($row)) return false;
    // 相差多少小时
    $play_hours = (int)$GLOBALS['skyuc']->options['play_hours'];
    $play_hours = $play_hours > 0 ? $play_hours : 24;
    $remain_time = (int)$row['time'] + 3600 * $play_hours - TIMENOW;
    if ($remain_time > 1) {
        return sprintf("%d:%d", floor($remain_time / 3600), floor(($remain_time % 3600) / 60));
    } else {
        return false;
    }

}

/**
 * 获得HTML中的PHP标识
 * @param str $str
 *
 * return
 */
function html2php($str)
{
    preg_match("/\{php(.*)\/php\}/isU", $str, $matches);
    if (!empty ($matches [0])) {
        return $matches [0];
    } else {
        return '';
    }
}

/**
 * 判断用户等级权限
 *
 * @param    int $movid 影片ID
 * @param    string $player 当前播放器
 * return    bool
 */
function get_user_rank($movid, $player)
{

    if (empty ($GLOBALS['skyuc']->options['user_rank'])) {
        return true;
    }

    if (empty ($GLOBALS['skyuc']->userinfo['user_id'])) {
        //执行session关闭脚本
        exec_shut_down();
        //刷新当前页，重新加载$GLOBALS['skyuc']->userinfo
        echo "<script language='javascript'>window.location.reload();</script>";
    }

    $rank_id = $GLOBALS['skyuc']->userinfo['user_rank'];
    $allow_cate = $GLOBALS['skyuc']->usergroup["$rank_id"]['cate'];

    $row = $GLOBALS['db']->query_first_slave('SELECT cat_id FROM ' . TABLE_PREFIX . 'show' . ' WHERE show_id=' . $movid);
    $cat_id = $row['cat_id'];
    $parent = array($cat_id);
    for ($parent_id = $cat_id; $parent_id > 0; $parent_id = $row ['parent_id']) {
        if ($parent_id == 0) {
            break;
        }
        $row = $GLOBALS['db']->query_first_slave('SELECT parent_id FROM ' . TABLE_PREFIX . 'category' . ' WHERE cat_id=' . $parent_id . ' AND is_show=1');
        if ($row ['parent_id'] > 0) {
            $parent[] = $row['parent_id'];
        }
    }
    $parent_id = end($parent);
    if (strpos($allow_cate, $parent_id) === false) {
        return false;
    }

    //播放器权限检查
    $player_rank = $GLOBALS['skyuc']->players["$player"]['user_rank'];
    if ($player_rank and strpos($player_rank, $rank_id) === false) {
        return false;
    }
    return true;
}

/**
 * 判断用户今日点播数量
 *
 * @param    int $userid 用户ID
 * @param    string $player 当前播放器
 * return    bool
 */
function get_user_play($userid, $player)
{

    if ($GLOBALS['skyuc']->options['user_rank'] == 0) {
        return -1;
    }

    $rank_id = $GLOBALS['skyuc']->userinfo['user_rank'];
    $day_play = $GLOBALS['skyuc']->usergroup["$rank_id"]['play'];
    $day_down = $GLOBALS['skyuc']->usergroup["$rank_id"]['down'];

    $yesterday = strtotime('-1 day', TIMENOW);
    //下载播放器唯一标识
    $pdown = (string)$GLOBALS['skyuc']->options['player_down'];
    if ($pdown !== '' and preg_match('#^[0-9a-zA-Z,]{3,}$#', $pdown)) {
        if (strpos($pdown, ',') !== false) {
            $pdown = explode(',', $pdown);
            $pdown = implode("', '", $pdown);
        }
        $pdown = "'down', 'download','" . $pdown . "'";
    } else {
        $pdown = "'down', 'download'";
    }
    $to_play = $GLOBALS['db']->query_first('SELECT COUNT(id) AS total FROM ' . TABLE_PREFIX . 'play_log' .
        " WHERE user_id='" . $userid . "' AND time>'" . $yesterday . "' AND player not in (" . $pdown . ")");

    $to_down = $GLOBALS['db']->query_first('SELECT COUNT(id) AS total FROM ' . TABLE_PREFIX . 'play_log' .
        " WHERE user_id='" . $userid . "' AND time>'" . $yesterday . "' AND player in (" . $pdown . ")");

    //检查当前播放器是下载还是点播，再检查点播数量限制
    if (strpos($pdown, $player) === false) {
        if ($to_play['total'] >= $day_play) {
            return 1;
        }
    } else {
        if ($to_down['total'] >= $day_down) {
            return 2;
        }
    }
    return -1;
}

/**
 * 验证会员组开放时间段,格式08:00-12:00,13:00-18:00
 * @return bool
 */
function verify_allow_hours()
{
    $rank_id = $GLOBALS['skyuc']->userinfo['user_rank'];
    $allow_hours = $GLOBALS['skyuc']->usergroup["$rank_id"]['hours'];
    if ($allow_hours != '' and $allow_hours != '00:00-23:59') {
        $hours = explode(',', $allow_hours);
        $hoursNow = skyuc_date('Hi');
        $return = false;
        foreach ($hours as $val) {
            if (strpos($val, '-') === false) {
                return false;
            }
            $hour = explode('-', $val);
            $start = str_replace(':', '', $hour[0]);
            $end = str_replace(':', '', $hour[1]);
            if ($hoursNow >= $start and $hoursNow <= $end) {
                $return = true;
                break;
            } else {
                $return = $allow_hours;
            }
        }
        return $return;
    } else {
        return true;
    }
}


/**
 * 读取影片信息
 *
 * @return    bool
 */
function read_film_info(array &$video)
{
    /*------------------------------------------------------ */
    //-- 读取影片ID，取得影片信息
    /*------------------------------------------------------ */
    $sql = 'SELECT server_id,points,title,player,data FROM ' . TABLE_PREFIX . 'show' .
        ' WHERE  is_show=1 AND show_id=' . $video['mov_id'];
    $row = $GLOBALS['db']->query_first_slave($sql);
    if (empty ($row)) {
        top_close($GLOBALS['_LANG'] ['operation_error']);
    } else {
        $serverArr = explode(',', $row ['server_id']);
        $playerArr = explode(',', $row ['player']);
        $video['show_point'] = (int)$row ['points'];
        $video['show_name'] = $row ['title'];

    }
    // 检查播放器是否正确
    if (!in_array($video['player'], $playerArr)) {
        top_close($GLOBALS['_LANG'] ['operation_error']);
    }

    /*------------------------------------------------------ */
    //-- 检查服务器ID，取得服务器地址
    /*------------------------------------------------------ */
    if (empty ($GLOBALS['skyuc']->servers)) {
        top_close($GLOBALS['_LANG'] ['server_error']);
    }
    foreach ($playerArr as $key => $val) {
        if ($val == $video['player']) {
            $array_key = $key;
            $server_id = $serverArr[$key];

            // 服务器维护中
            if (empty ($GLOBALS['skyuc']->servers [$server_id] ['show'])) {
                top_close($GLOBALS['_LANG'] ['server_repair']);
            }
            $server_url = $GLOBALS['skyuc']->servers [$server_id] ['url'];
        }
    }

    /*------------------------------------------------------ */
    //-- 检查地址ID，取得地址
    /*------------------------------------------------------ */
    if (!empty ($row ['data'])) {
        // 地址分组
        $data = display_url_data($row ['data'], '', '', true);
    }
    $data = $data[$array_key] ['url'];
    $video['playlist'] = $data;
    if (empty ($data)) {
        top_close($GLOBALS['_LANG'] ['operation_error']);
    } else {
        //地址信息数组从0开始，故观看集数要减1
        $cur_id = $video['look_id'] - 1;
        $video['skyuc_src'] = $server_url . $data[$cur_id]['src'];
        $video['skyuc_title'] = $data[$cur_id]['title'];
        if ($data[$cur_id]['point'] !== 'ignore') {
            $video['show_point'] = (int)$data[$cur_id]['point'];
        }
    }
    /*------------------------------------------------------ */
    //-- 下一页地址：自动播放下一集
    /*------------------------------------------------------ */
    $video['nextPageUrl'] = '';
    if (!empty ($video['skyuc_src']) && $video['look_id'] < count($data)) {
        $nextLookId = $video['look_id'] + 1;
        $video['skyuc_nextsrc'] = $server_url . $data [$video['look_id']] ['src'];
        $video['nextPageUrl'] = get_url() . 'player.php?mov_id=' . $video['mov_id'] .
            '&look_id=' . $nextLookId . '&player=' . $video['player'];
    }
    /*------------------------------------------------------ */
    //-- 上一页地址：
    /*------------------------------------------------------ */
    $video['prevPageUrl'] = '';
    if (!empty ($video['nextPageUrl']) && $video['look_id'] > 1) {
        $prevLookId = $video['look_id'] - 1;
        $video['prevPageUrl'] = get_url() . 'player.php?mov_id=' . $video['mov_id'] .
            '&look_id=' . $prevLookId . '&player=' . $video['player'];
    }
}