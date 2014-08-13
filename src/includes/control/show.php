<?php
/**
 * SKYUC! 前台show.php私有函数库
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */
/*------------------------------------------------------ */
//--	show.php影片详情页 PRIVATE FUNCTION
/*------------------------------------------------------ */
/**
 * 获得影片的详细信息
 *
 * @access  public
 * @param   integer     $mov_id
 * @return  void
 */
function get_show_info ($mov_id)
{
    $sql = 'SELECT show_id,	director, actor, title, title_alias, title_english, title_style,	status, image,	keywords,	description,	detail,	pubdate,	click_count,	click_month,	click_week,	cat_id,	area,	lang,	add_time,	points, moviepoint, userspoint,	runtime,	player,	server_id, cat_id, data  FROM ' .
     TABLE_PREFIX . 'show' . ' WHERE show_id = ' . $mov_id . ' AND is_show = 1';
    $key = md5($sql); //缓存名称：键
    //读缓存
    if ($data = get_file_cache($key)) {
        return $data;
    }
    $row = $GLOBALS['db']->query_first_slave($sql);
    if (! empty($row)) {
        // 格式化最后更新时间显示
        $row['add_time'] = skyuc_date(
        $GLOBALS['skyuc']->options['date_format'] . ' ' .
         $GLOBALS['skyuc']->options['time_format'], $row['add_time']);
        // 修正影片图片
        $row['image'] = get_image_path($row['image']);
        //演员搜索链接
        $row['actor'] = get_actor_array($row['actor']);
        $row['director'] = get_actor_array($row['director']);
        $moviepoint = ceil($row['moviepoint'] / $row['userspoint']);
        if ($moviepoint > 0 && $moviepoint < 10) {
            $row['moviepoint'] = $moviepoint;
        } else {
            $row['moviepoint'] = 10;
        }
        //评分人数
        $row['score_users'] = $row['userspoint'];
        put_file_cache($key, $row); //写缓存
        return $row;
    } else {
        return false;
    }
}
/**
 * 获得影片相关演员或导演的影片
 *
 * @access  public
 * @param		string			$actor
 * @param   integer     $mov_id
 * @param		string			$type
 * @return  array
 */
function get_linked_show ($actor, $mov_id = 1)
{
    $arr = array();
    $total = $GLOBALS['skyuc']->options['related_actor'];
    //print_r($actor);exit;
    if (empty($actor)) {
        return $arr;
    }
    foreach ($actor as $key => $val) {
        $arr["$val"] = array();
        //print_r($val);exit;
        $searchstring = fetch_index_text($val);
        $key = md5($searchstring); //缓存名称：键
        //读缓存
        if ($data = get_file_cache($key)) {
            $arr["$val"] = $data;
            continue;
        }
        if (count($arr) >= $total) {
            break; //要获取的演员/导演数量已达最大数
        }
        $sql = "SELECT `searchid` , MATCH (searchcore_text.title, searchcore_text.keywordtext) AGAINST ('" .
         $GLOBALS['db']->escape_string_like($searchstring) . "' ) AS score
				FROM " . TABLE_PREFIX .
         "searchcore_text AS searchcore_text
				WHERE MATCH (searchcore_text.title, searchcore_text.keywordtext)
				AGAINST ('" .
         $GLOBALS['db']->escape_string_like($searchstring) . "')";
        $sql = $GLOBALS['db']->query_limit($sql, $total);
        $searchcore_ids = $GLOBALS['db']->query_all($sql);
        if (empty($searchcore_ids)) {
            continue;
        }
        $show_ids = array();
        foreach ($searchcore_ids as $value) {
            $show_ids[] = $value['searchid'];
        }
        $where = ' show_id IN (' . implode(',', $show_ids) . ')';
        $sql = 'SELECT show_id, title FROM ' . TABLE_PREFIX . 'show' . ' WHERE ' .
         $where . '   ORDER BY show_id DESC';
        $res = $GLOBALS['db']->query_read_slave($sql);
        while ($row = $GLOBALS['db']->fetch_array($res)) {
            if ($mov_id == $row['show_id']) {
                continue;
            }
            $row['url'] = build_uri('show', array('mid' => $row['show_id']),
            $row['title']);
            $arr["$val"][] = $row;
        }
        if (! empty($arr["$val"])) {
            put_file_cache($key, $arr["$val"]); //写缓存
        } else {
            unset($arr["$val"]);
        }
    }
    return $arr;
}
/**
 * 添加影片名样式
 * @param   string     $title     影片名称
 * @param   string     $style          样式参数
 * @return  string
 */
function add_style ($title, $style)
{
    $arr = explode('+', $style);
    $font_color = ! empty($arr[0]) ? $arr[0] : '';
    $font_style = ! empty($arr[1]) ? $arr[1] : '';
    if ($font_color != '') {
        $title = '<font color=' . $font_color . '>' . $title . '</font>';
    }
    if ($font_style != '') {
        $title = '<' . $font_style . '>' . $title . '</' . $font_style . '>';
    }
    return $title;
}
/**
 * 看了本片的用户还看过
 *
 * @access  public
 * @param   integer     $mov_id 影片ID
 * @return  array
 */
function get_same_movie ($mov_id = 0)
{
    $mov_id = intval($mov_id);
    if ($mov_id === 0) {
        return ;
    }
    $sam_movie = array();
    //先取十个看过本片的用户ID
    $sql = 'SELECT user_id FROM ' . TABLE_PREFIX . 'play_log' .
     ' WHERE mov_id =' . $mov_id . '  ORDER BY time DESC';
    $sql = $GLOBALS['db']->query_limit($sql, 10);
    $key = md5($sql); //缓存名称：键
    //读缓存
    if ($data = get_file_cache($key)) {
        return $data;
    }
    $res = $GLOBALS['db']->query_read_slave($sql);
    while ($row = $GLOBALS['db']->fetch_array($res)) {
        //再取十个看过本片的用户还看过的其它影片ID
        $sql = 'SELECT mov_id, title FROM ' . TABLE_PREFIX . 'play_log' .
         ' WHERE  user_id=' . $row['user_id'] . ' ORDER BY time DESC';
        $sql = $GLOBALS['db']->query_limit($sql, 10);
        $result = $GLOBALS['db']->query_read_slave($sql);
        while ($rows = $GLOBALS['db']->fetch_array($result)) {
            if ($mov_id == $rows['mov_id'] or
             isset($sam_movie[$rows['mov_id']])) {
                continue;
            }
            $rows['url'] = build_uri('show',
            array('mid' => $rows['mov_id']), $rows['title']);
            //使用 影片ID作键名，防止影片ID重复
            $sam_movie[$rows['mov_id']] = $rows;
        }
        ksort($sam_movie);
        // 取完十个看过本片还看过其它影片
        if (count($sam_movie) >= 10) {
            break;
        }
    }
    ksort($sam_movie);
    //print_r($sam_movie);exit;
    put_file_cache($key, $sam_movie); //写缓存
    return $sam_movie;
}
?>