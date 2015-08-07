<?php
/**
 * SKYUC! 前台函数库
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */
if (!defined('SKYUC_AREA')) {
    echo 'SKYUC_AREA must be defined to continue';
    exit();
}
/**
 * 显示一个提示信息
 *
 * @access  public
 * @param   string  $content
 * @param   string  $links
 * @param   string  $hrefs
 * @param   string  $type               信息类型：warning, error, info
 * @param   string  $auto_redirect      是否自动跳转
 * @return  void
 */
function show_message($content, $links = '', $hrefs = '', $type = 'info',
                      $auto_redirect = true)
{
    assign_template();
    $msg['content'] = $content;
    if (is_array($links) && is_array($hrefs)) {
        if (!empty($links) && count($links) == count($hrefs)) {
            foreach ($links as $key => $val) {
                $msg['url_info'][$val] = $hrefs[$key];
            }
            $msg['back_url'] = $hrefs['0'];
        }
    } else {
        $link = iif(empty($links), $GLOBALS['_LANG']['back_up_page'], $links);
        $href = iif(empty($hrefs), 'javascript:history.back()', $hrefs);
        $msg['url_info'][$link] = $href;
        $msg['back_url'] = $href;
    }
    $msg['type'] = $type;
    $position = assign_ur_here(0, $GLOBALS['_LANG']['sys_msg']);
    $GLOBALS['smarty']->assign('page_title', $position['title']); // 页面标题
    $GLOBALS['smarty']->assign('ur_here', $position['ur_here']); // 当前位置
    if (is_null($GLOBALS['smarty']->get_template_vars('nav_list'))) {
        $GLOBALS['smarty']->assign('nav_list', get_navigator()); // 导航栏
    }
    $GLOBALS['smarty']->assign('auto_redirect', $auto_redirect);
    $GLOBALS['smarty']->assign('message', $msg);
    $GLOBALS['smarty']->display('message.dwt');
    exit();
}

/**
 * 获得查询次数以及查询时间
 *
 * @access  public
 * @return  string
 */
function insert_query_info()
{
    if ($GLOBALS['db']->queryTime == '') {
        $query_time = 0;
    }
    else {
        $query_time = number_format(
            microtime(true) - $GLOBALS['db']->queryTime, 6);
    }
    $gzip_enabled = iif(
        $GLOBALS['skyuc']->options['gzipoutput'] &&
            $GLOBALS['skyuc']->options['gziplevel'] > 0, 'On', 'Off');
    //在线人数
    $total = $GLOBALS['db']->query_first(
        'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'session');
    $online_count = $total['total'];
    $explain = ' ';
    if (!empty($GLOBALS['skyuc']->debug)) {
        $explain = " <a href=\"" .
            (htmlspecialchars($GLOBALS['skyuc']->scriptpath)) .
            (strpos($GLOBALS['skyuc']->scriptpath, '?') === false ? '?'
                : '&amp;') .
            "explain=1\" target=\"_blank\" title=\"Explain Queries\">(?)</a>";
    }
    return sprintf(
        'Processed in %f second(s), %d queries , %d Users Online , Gzip %s',
        $query_time, $GLOBALS['db']->queryCount, $online_count, $gzip_enabled) . ' ' .
        $explain;
}

/**
 * 调用浏览历史
 *
 * @access  public
 * @return  string
 */
function insert_history()
{
    $str = '';
    if (!empty($_COOKIE[COOKIE_PREFIX . 'history'])) {
        $where = db_create_in($_COOKIE[COOKIE_PREFIX . 'history'], 'show_id');
        $sql = 'SELECT show_id, title FROM ' . TABLE_PREFIX . 'show' . ' WHERE ' .
            $where . ' AND is_show = 1';
        $key = md5($sql); //缓存名称：键
        //读缓存
        if ($data = get_file_cache($key)) {
            $arr = $data;
        } else {
            $query = $GLOBALS['db']->query_read($sql);
            $arr = array();
            while ($row = $GLOBALS['db']->fetch_array($query)) {
                $arr[$row['show_id']] = $row;
            }
            put_file_cache($key, $arr); //写缓存
        }
        $tureorder = explode(',', $_COOKIE[COOKIE_PREFIX . 'history']);
        foreach ($tureorder as $key => $val) {
            $title = htmlspecialchars($arr[$val]['title']);
            if ($title) {
                $str .= '<li><a href="' .
                    build_uri('show', array('mid' => $val), $title) . '" title="' .
                    $title . '">' . $title . '</a></li>';
            }
        }
    }
    return $str;
}

/**
 * 获得指定用户、影片的所有标记
 *
 * @access  public
 * @param   integer $show_id    影片ID
 * @param   integer $user_id    用户ID
 * @param     integer    $num        数量
 * @param    boolean   $nocache    是否从缓存中获取
 * @return  array
 */
function get_tags($show_id = 0, $user_id = 0, $num = 0, $nocache = false)
{
    $where = '';
    if ($show_id > 0) {
        $where .= ' show_id = ' . $show_id;
    }
    if ($user_id > 0) {
        if ($show_id > 0) {
            $where .= ' AND ';
        }
        $where .= ' user_id = ' . $user_id;
    }
    if ($where > '') {
        $where = ' WHERE' . $where;
    }
    $sql = 'SELECT tag_id, user_id, tag_words, COUNT(tag_id) AS tag_count' .
        ' FROM ' . TABLE_PREFIX . 'tag' . $where . ' GROUP BY tag_words';
    $sql = $GLOBALS['db']->query_limit($sql, $num);
    $key = md5($sql); //缓存名称：键
    //读缓存
    if (($data = get_file_cache($key)) && $nocache === false) {
        return $data;
    } else {
        $arr = $GLOBALS['db']->query_all_slave($sql);
        put_file_cache($key, $arr); //写缓存
    }
    return $arr;
}

/**
 * 调用指定的广告位的广告
 *
 * @access  public
 * @param   integer $id     广告位ID
 * @param   integer $num    广告数量
 * @return  string
 */
function insert_ads($arr)
{
    static $static_res = NULL;
    if (!empty($arr['num']) && $arr['num'] != 1) {
        $sql = 'SELECT a.ad_id, a.position_id, a.media_type, a.ad_link, a.ad_code, a.ad_name, p.ad_width, ' .
            'p.ad_height, p.position_style, RAND() AS rnd ' . ' FROM ' .
            TABLE_PREFIX . 'ad' . ' AS a ' . ' LEFT JOIN ' . TABLE_PREFIX .
            'ad_position' . ' AS p ON a.position_id = p.position_id ' .
            ' WHERE enabled = 1 AND start_date <= ' . TIMENOW . ' AND end_date >= ' .
            TIMENOW . ' AND a.position_id = ' . $arr['id'] . ' ORDER BY rnd';
        $sql = $GLOBALS['db']->query_limit($sql, $arr['num']);
        $res = $GLOBALS['db']->query_all_slave($sql);
    } else {
        if ($static_res[$arr['id']] === NULL) {
            $sql = 'SELECT a.ad_id, a.position_id, a.media_type, a.ad_link, a.ad_code, a.ad_name, p.ad_width, ' .
                'p.ad_height, p.position_style, RAND() AS rnd ' . ' FROM ' .
                TABLE_PREFIX . 'ad' . ' AS a ' . ' LEFT JOIN ' . TABLE_PREFIX .
                'ad_position' . ' AS p ON a.position_id = p.position_id ' .
                ' WHERE enabled = 1 AND a.position_id = ' . $arr['id'] .
                ' AND start_date <= ' . TIMENOW . ' AND end_date >= ' . TIMENOW .
                ' ORDER BY rnd';
            $sql = $GLOBALS['db']->query_limit($sql, 1);
            $static_res[$arr['id']] = $GLOBALS['db']->query_all_slave($sql);
        }
        $res = $static_res[$arr['id']];
    }
    $ads = array();
    $position_style = '';
    foreach ($res as $row) {
        if ($row['position_id'] != $arr['id']) {
            continue;
        }
        $position_style = $row['position_style'];
        switch ($row['media_type']) {
            case 0: // 图片广告
                $src = iif(
                    (strpos($row['ad_code'], 'http://') === false &&
                        strpos($row['ad_code'], 'https://') === false),
                    $GLOBALS['skyuc']->config['Misc']['imagedir'] . '/afficheimg/' .
                        $row['ad_code'], $row['ad_code']);
                $ads[] = "<a href='affiche.php?ad_id=" . $row['ad_id'] .
                    "&amp;uri=" . urlencode($row['ad_link']) . "'
                target='_blank'><img src='" . $src . "' width='" .
                    $row['ad_width'] . "' height='" . $row['ad_height'] . "'
                border='0' /></a>";
                break;
            case 1: // Flash
                $src = iif(
                    (strpos($row['ad_code'], 'http://') === false &&
                        strpos($row['ad_code'], 'https://') === false),
                    $GLOBALS['skyuc']->config['Misc']['imagedir'] . '/afficheimg/' .
                        $row['ad_code'], $row['ad_code']);
                $ads[] = "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" " .
                    "codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0\"  " .
                    "width='" . $row['ad_width'] . "' height='" . $row['ad_height'] . "'>
                           <param name='movie' value='" . $src . "'>
                           <param name='quality' value='high'>
                           <embed src='" . $src . "' quality='high'
                           pluginspage='http://www.macromedia.com/go/getflashplayer'
                           type='application/x-shockwave-flash' width='" .
                    $row['ad_width'] . "'
                           height='" . $row['ad_height'] . "'></embed>
                         </object>";
                break;
            case 2: // CODE
                $ads[] = $row['ad_code'];
                // $ads[] = '<a href=' . '"' . 'affiche.php?ad_id=' . $row['ad_id'] . '&amp;uri=' .
                // urlencode($row["ad_link"]) . '"' . ' target="_blank">' . $row['ad_code'] . '</a>';
                break;
            case 3: // TEXT
                $ads[] = "<a href='affiche.php?ad_id=" .
                    $row['ad_id'] . '&amp;uri=' . urlencode($row['ad_link']) . "'
                target='_blank'>" . htmlspecialchars($row['ad_code']) .
                    '</a>';
                break;
        }
    }
    $position_style = 'str:' . $position_style;
    $need_cache = $GLOBALS['smarty']->caching;
    $GLOBALS['smarty']->caching = false;
    $GLOBALS['smarty']->assign('ads', $ads);
    $val = $GLOBALS['smarty']->fetch($position_style);
    $GLOBALS['smarty']->caching = $need_cache;
    return $val;
}

/**
 * 调用会员信息
 *
 * @access  public
 * @return  string
 */
function insert_member_info()
{
    $need_cache = $GLOBALS['smarty']->caching;
    $GLOBALS['smarty']->caching = false;
    $netbar = verify_ip_netbar();
    if ($netbar !== false) {
        $netbar['curip'] = ALT_IP;
        $netbar['addtime'] = skyuc_date(
            $GLOBALS['skyuc']->options['date_format'], $netbar['addtime'], FALSE,
            false);
        $netbar['endtime'] = skyuc_date(
            $GLOBALS['skyuc']->options['date_format'], $netbar['endtime'], FALSE,
            false);
        $GLOBALS['smarty']->assign('netbar', $netbar);
    } else {
        if ($GLOBALS['skyuc']->session->vars['userid'] > 0) {
            $GLOBALS['smarty']->assign('user_info', get_member_info());
        } else {
            if (!empty($GLOBALS['skyuc']->GPC[COOKIE_PREFIX . 'username'])) {
                $GLOBALS['smarty']->assign('skyuc_username',
                    $GLOBALS['skyuc']->GPC[COOKIE_PREFIX . 'username']);
            }
        }
        $pmopen = iif(
            $GLOBALS['skyuc']->options['integrate_code'] == 'ucenter', 1, 0);
        $GLOBALS['smarty']->assign('pmopen', $pmopen);
    }
    $output = $GLOBALS['smarty']->fetch('library/member_info.lbi');
    $GLOBALS['smarty']->caching = $need_cache;
    return $output;
}

/**
 * 网吧IP自动登陆
 *
 * @access  public
 * @param  $cip            客户端IP
 *
 * @return array
 */
function verify_ip_netbar()
{
    //判断是否启用网吧模块
    if ($GLOBALS['skyuc']->options['enable_netbar'] == 1) {
        // 将IP转为数字
        $ipnum = ip2num(ALT_IP);
        $sql = 'SELECT id,title,addtime,endtime,maxuser FROM ' . TABLE_PREFIX .
            'netbar' . ' WHERE  snum<=' . $ipnum . ' AND enum>=' . $ipnum .
            ' AND is_ok = 1 ORDER BY lasttime DESC';
        $netbar = $GLOBALS['db']->query_first_slave($sql);
        if (!empty($netbar)) {
            return $netbar;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * 创建分页信息
 *
 * @access  public
 * @param   string  $app            程序名称，如category
 * @param   string  $cat            分类ID
 * @param   string  $record_count   记录总数
 * @param   string  $size           每页记录数
 * @param   string  $sort           排序类型
 * @param   string  $order          排序顺序
 * @param   string  $page           当前页
 * @param   string  $keywords       查询关键字
 * @return  void
 */
function assign_pager($app, $cat, $record_count, $size, $sort, $order,
                      $page = 1, $keywords = '', $display_type = 'list')
{
    $sch = array('keywords' => $keywords, 'sort' => $sort, 'order' => $order,
        'cat' => $cat, 'display' => $display_type);
    $page = intval($page);
    if ($page < 1) {
        $page = 1;
    }
    $page_count = $record_count > 0 ? intval(ceil($record_count / $size)) : 1;
    $pager['page'] = $page;
    $pager['size'] = $size;
    $pager['sort'] = $sort;
    $pager['order'] = $order;
    $pager['record_count'] = $record_count;
    $pager['page_count'] = $page_count;
    $pager['display'] = $display_type;
    switch ($app) {
        case 'category':
            $uri_args = array('cid' => $cat, 'sort' => $sort,
                'order' => $order, 'display' => $display_type);
            break;
        case 'article_cat':
            $uri_args = array('acid' => $cat, 'sort' => $sort,
                'order' => $order);
            break;
        case 'search':
            $uri_args = array('cid' => $cat, 'sort' => $sort,
                'order' => $order);
            break;
    }
    // 分页样式
    $pager['styleid'] = isset($GLOBALS['skyuc']->options['page_style'])
        ? intval(
            $GLOBALS['skyuc']->options['page_style']) : 0;
    $page_prev = ($page > 1) ? $page - 1 : 1;
    $page_next = ($page < $page_count) ? $page + 1 : $page_count;
    if ($pager['styleid'] == 0) {
        $pager['page_first'] = build_uri($app, $uri_args, '', 1, $sch);
        $pager['page_prev'] = build_uri($app, $uri_args, '', $page_prev);
        $pager['page_next'] = build_uri($app, $uri_args, '', $page_next);
        $pager['page_last'] = build_uri($app, $uri_args, '', $page_count);
        $pager['array'] = array();
        for ($i = 1; $i <= $page_count; $i++) {
            $pager['array'][$i] = $i;
        }
    } else {
        $_pagenum = isset($GLOBALS['skyuc']->options['pagenavs']) ? intval(
            $GLOBALS['skyuc']->options['pagenavs']) : 10; // 显示的页码
        $_offset = 2; // 当前页偏移值
        $_from = $_to = 0; // 开始页, 结束页
        if ($_pagenum > $page_count) {
            $_from = 1;
            $_to = $page_count;
        } else {
            $_from = $page - $_offset;
            $_to = $_from + $_pagenum - 1;
            if ($_from < 1) {
                $_to = $page + 1 - $_from;
                $_from = 1;
                if ($_to - $_from < $_pagenum) {
                    $_to = $_pagenum;
                }
            } elseif ($_to > $page_count) {
                $_from = $page_count - $_pagenum + 1;
                $_to = $page_count;
            }
        }
        $pager['page_first'] = ($page - $_offset > 1 && $_pagenum < $page_count)
            ? build_uri(
                $app, $uri_args, '', 1) : '';
        $pager['page_prev'] = ($page > 1) ? build_uri($app, $uri_args, '',
            $page_prev) : '';
        $pager['page_next'] = ($page < $page_count) ? build_uri($app,
            $uri_args, '', $page_next)
            : '';
        $pager['page_last'] = ($_to < $page_count) ? build_uri($app, $uri_args,
            '', $page_count)
            : '';
        $pager['page_kbd'] = ($_pagenum < $page_count) ? true : false;
        $pager['page_number'] = array();
        for ($i = $_from; $i <= $_to; ++$i) {
            $pager['page_number'][$i] = build_uri($app, $uri_args, '', $i);
        }
    }
    $pager['search']['category'] = $cat;
    foreach ($sch as $key => $row) {
        $pager['search'][$key] = $row;
    }
    $GLOBALS['smarty']->assign('pager', $pager);
}

/**
 * 生成给pager.lbi赋值的数组
 *
 * @access  public
 * @param   string      $url        分页的链接地址(必须是带有参数的地址，若不是可以伪造一个无用参数)
 * @param   array       $param      链接参数 key为参数名，value为参数值
 * @param   int         $record     记录总数量
 * @param   int         $page       当前页数
 * @param   int         $size       每页大小
 *
 * @return  array       $pager
 */
function get_pager($url, $param, $record_count, $page = 1, $size = 10)
{
    $size = intval($size);
    if ($size < 1) {
        $size = 10;
    }
    $page = intval($page);
    if ($page < 1) {
        $page = 1;
    }
    $record_count = intval($record_count);
    $page_count = $record_count > 0 ? intval(ceil($record_count / $size)) : 1;
    if ($page > $page_count) {
        $page = $page_count;
    }
    // 分页样式
    $pager['styleid'] = isset($GLOBALS['skyuc']->options['page_style'])
        ? intval(
            $GLOBALS['skyuc']->options['page_style']) : 0;
    $page_prev = ($page > 1) ? $page - 1 : 1;
    $page_next = ($page < $page_count) ? $page + 1 : $page_count;
    // 将参数合成url字串
    $param_url = '?';
    foreach ($param as $key => $value) {
        $param_url .= $key . '=' . $value . '&';
    }
    $pager['url'] = $url;
    $pager['start'] = ($page - 1) * $size;
    $pager['page'] = $page;
    $pager['size'] = $size;
    $pager['record_count'] = $record_count;
    $pager['page_count'] = $page_count;
    if ($pager['styleid'] == 0) {
        $pager['page_first'] = $url . $param_url . 'page=1';
        $pager['page_prev'] = $url . $param_url . 'page=' . $page_prev;
        $pager['page_next'] = $url . $param_url . 'page=' . $page_next;
        $pager['page_last'] = $url . $param_url . 'page=' . $page_count;
        $pager['array'] = array();
        for ($i = 1; $i <= $page_count; $i++) {
            $pager['array'][$i] = $i;
        }
    } else {
        $_pagenum = isset($GLOBALS['skyuc']->options['pagenavs']) ? intval(
            $GLOBALS['skyuc']->options['pagenavs']) : 10; // 显示的页码
        $_offset = 2; // 当前页偏移值
        $_from = $_to = 0; // 开始页, 结束页
        if ($_pagenum > $page_count) {
            $_from = 1;
            $_to = $page_count;
        } else {
            $_from = $page - $_offset;
            $_to = $_from + $_pagenum - 1;
            if ($_from < 1) {
                $_to = $page + 1 - $_from;
                $_from = 1;
                if ($_to - $_from < $_pagenum) {
                    $_to = $_pagenum;
                }
            } elseif ($_to > $page_count) {
                $_from = $page_count - $_pagenum + 1;
                $_to = $page_count;
            }
        }
        $url_format = $url . $param_url . 'page=';
        $pager['page_first'] = ($page - $_offset > 1 && $_pagenum < $page_count)
            ? $url_format .
                1 : '';
        $pager['page_prev'] = ($page > 1) ? $url_format . $page_prev : '';
        $pager['page_next'] = ($page < $page_count) ? $url_format . $page_next
            : '';
        $pager['page_last'] = ($_to < $page_count) ? $url_format . $page_count
            : '';
        $pager['page_kbd'] = ($_pagenum < $page_count) ? true : false;
        $pager['page_number'] = array();
        for ($i = $_from; $i <= $_to; ++$i) {
            $pager['page_number'][$i] = $url_format . $i;
        }
    }
    $pager['search'] = $param;
    return $pager;
}

/**
 * 取得当前位置和页面标题
 *
 * @access  public
 * @param   integer     $cat    分类编号（只有影片及分类、文章及分类用到）
 * @param   string      $str    影片名、文章标题或其他附加的内容（无链接）
 * @return  array
 */
function assign_ur_here($cat = 0, $str = '')
{
    // 取得文件名
    $filename = substr(basename($_SERVER['PHP_SELF']), 0, -4);
    // 初始化“页面标题”和“当前位置”
    $page_title = $GLOBALS['skyuc']->options['site_title'];
    $ur_here = '<a href=".">' . $GLOBALS['skyuc']->lang['home'] . '</a>';
    // 根据文件名分别处理中间的部分
    if ($filename != 'index') {
        // 处理有分类的
        if (in_array($filename,
            array('list', 'show', 'article_cat', 'article'))
        ) {
            // 影片分类或影片
            if ('list' == $filename || 'show' == $filename) {
                if ($cat > 0) {
                    $cat_arr = get_parent_cats($cat);
                    $key = 'cid';
                    $type = 'category';
                } else {
                    $cat_arr = array();
                }
            } // 文章分类或文章
            else
                if ('article_cat' == $filename || 'article' == $filename) {
                    if ($cat > 0) {
                        $sql = 'SELECT cat_name FROM ' . TABLE_PREFIX .
                            'article_cat' . ' WHERE cat_id = ' . $cat;
                        $cate = $GLOBALS['db']->query_first_slave($sql);
                        $cat_arr[0]['cat_id'] = $cat;
                        $cat_arr[0]['cat_name'] = $cate['cat_name'];
                        $key = 'acid';
                        $type = 'article_cat';
                    } else {
                        $cat_arr = array();
                    }
                }
            // 循环分类
            if (!empty($cat_arr)) {
                krsort($cat_arr);
                foreach ($cat_arr as $val) {
                    $page_title = htmlspecialchars($val['cat_name']) . '_' .
                        $page_title;
                    $args = array($key => $val['cat_id']);
                    $ur_here .= ' <code>&gt;</code> <a href="' .
                        build_uri($type, $args, $val['cat_name']) . '">' .
                        htmlspecialchars($val['cat_name']) . '</a>';
                }
            }
        } // 处理无分类的
        else { // 其他的在这里补充
        }
    }
    // 处理最后一部分
    if (!empty($str)) {
        $page_title = $str . '_' . $page_title;
        $ur_here .= ' <code>&gt;</code> ' . $str;
    }
    // 返回值
    return array('title' => $page_title, 'ur_here' => $ur_here);
}

/**
 * 获得指定分类同级的所有分类以及该分类下的子分类
 *
 * @access  public
 * @param   integer     $cat_id     分类编号
 * @return  array
 */
function get_categories_tree($cat_id = 0)
{
    //检查是否存在缓存
    if (!isset($GLOBALS['skyuc']->categories)) {
        $GLOBALS['skyuc']->categories = array();
    } elseif (isset($GLOBALS['skyuc']->categories["$cat_id"]) &&
        !empty($GLOBALS['skyuc']->categories["$cat_id"])
    ) {
        if ($GLOBALS['skyuc']->categories['time'] >
            (TIMENOW - $GLOBALS['skyuc']->options['cache_time'] * 3600)
        )
            return $GLOBALS['skyuc']->categories["$cat_id"];
    }
    if ($cat_id > 0) {
        //对分类页，只显示它的子分类
        // $sql = 'SELECT parent_id FROM ' . TABLE_PREFIX . 'category' . ' WHERE cat_id = ' . $cat_id;
        // $parent = $GLOBALS['db']->query_first_slave($sql);
        $parent_id = $cat_id;
    } else {
        $parent_id = 0;
    }
    /*
     * 判断当前分类中是否全是底级分类，
     * 如果是取出底级分类上级分类，如果不是取当前分类及其下的子分类
     */
    $sql = 'SELECT count(*) AS total FROM ' . TABLE_PREFIX . 'category' .
        ' WHERE parent_id = ' . $cat_id . ' AND is_show = 1';
    $total = $GLOBALS['db']->query_first_slave($sql);
    $cat_arr = array();
    if ($total['total'] || $parent_id == 0) {
        // 获取当前分类及其子分类
        $sql = 'SELECT cat_id,cat_name ,parent_id,is_show ' . ' FROM ' .
            TABLE_PREFIX . 'category' . ' WHERE parent_id = ' . $parent_id .
            ' AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC';
        $res = $GLOBALS['db']->query_all_slave($sql);
        foreach ($res as $row) {
            if ($row['is_show']) {
                $cat_arr[$row['cat_id']]['id'] = $row['cat_id'];
                $cat_arr[$row['cat_id']]['name'] = $row['cat_name'];
                $cat_arr[$row['cat_id']]['url'] = build_uri('category',
                    array('cid' => $row['cat_id']), $row['cat_name']);
                if (isset($row['cat_id']) !== NULL) {
                    $cat_arr[$row['cat_id']]['children'] = get_child_tree(
                        $row['cat_id']);
                }
            }
        }
    }
    if (!empty($cat_arr)) {
        //重建缓存
        $GLOBALS['skyuc']->categories["$cat_id"] = &$cat_arr;
        $GLOBALS['skyuc']->categories['time'] = TIMENOW; //设置缓存时间
        build_datastore('categories',
            serialize($GLOBALS['skyuc']->categories), 1);
    }
    return $cat_arr;
}

function get_child_tree($tree_id = 0)
{
    $three_arr = array();
    $sql = 'SELECT count(*) AS total FROM ' . TABLE_PREFIX . 'category' .
        " WHERE parent_id = '$tree_id' AND is_show = 1 ";
    $total = $GLOBALS['db']->query_first_slave($sql);
    if ($total['total'] || $tree_id == 0) {
        $child_sql = 'SELECT cat_id, cat_name, parent_id, is_show ' . 'FROM ' .
            TABLE_PREFIX . 'category' .
            "	WHERE parent_id = '$tree_id' AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC";
        $res = $GLOBALS['db']->query_all_slave($child_sql);
        foreach ($res as $row) {
            if ($row['is_show'])
                $three_arr[$row['cat_id']]['id'] = $row['cat_id'];
            $three_arr[$row['cat_id']]['name'] = $row['cat_name'];
            $three_arr[$row['cat_id']]['url'] = build_uri('category',
                array('cid' => $row['cat_id']), $row['cat_name']);
            if (isset($row['cat_id']) !== NULL) {
                $children = get_child_tree($row['cat_id']);
                if (!empty($children)) {
                    $three_arr[$row['cat_id']]['children'] = $children;
                }
            }
        }
    }
    return $three_arr;
}

/**
 * 取得某模板某库设置的数量
 * @param   string      $template   模板名，如index
 * @param   string      $library    库名，如recommend_best
 * @param   int         $def_num    默认数量：如果没有设置模板，显示的数量
 * @return  int         数量
 */
function get_library_number($library, $template = null)
{
    if (empty($template)) {
        $template = basename(PHP_SELF);
        $template = substr($template, 0, strrpos($template, '.'));
    }
    static $lib_list = array();
    // 如果没有该模板的信息，取得该模板的信息
    if (!isset($lib_list[$template])) {
        $lib_list[$template] = array();
        $sql = 'SELECT library, number FROM ' . TABLE_PREFIX . 'template' .
            " WHERE theme = '" . $GLOBALS['skyuc']->options['themes'] . "'" .
            " AND filename = '" . $template . "' AND remarks='' ";
        $res = $GLOBALS['db']->query_read_slave($sql);
        while ($row = $GLOBALS['db']->fetch_array($res)) {
            $lib = basename(
                strtolower(
                    substr($row['library'], 0, strpos($row['library'], '.'))));
            $lib_list[$template][$lib] = $row['number'];
        }
    }
    $num = 0;
    if (isset($lib_list[$template][$library])) {
        $num = intval($lib_list[$template][$library]);
    } else {
        // 模板设置文件查找默认值
        $page_libs = array(); //在functions_template.php中定义
        include_once (DIR . '/includes/functions_template.php');
        static $static_page_libs = null;
        if ($static_page_libs == null) {
            $static_page_libs = $page_libs;
        }
        $lib = '/library/' . $library . '.lbi';
        $num = isset($static_page_libs[$template][$lib])
            ? $static_page_libs[$template][$lib] : 10;
    }
    return $num;
}

/*
 * 获得推荐影片
 *
 * @access  public
 * @param   string      $type       推荐类型，可以是  recom*, top*,new*, cat_hot
 * @param   string            $children        子分类,形如m.cat_id  IN ('29','11','10','14','13','12')
 * @param        int                    $top_time        按指定天数排行
 * @return  array
 */
function get_top_new_hot($type = '', $children = '', $top_time = '')
{
    if (!in_array($type,
        array('recom', 'recom_cate', 'top', 'top_cate', 'top_detail', 'cat_hot',
            'new', 'new_cate', 'new_detail', 'new_article'))
    ) {
        return array();
    }
    //检查是否存在缓存
    $key = $type . crc32($children) . $top_time;
    $key = iif(!empty($key), $key, 0);
    if (!isset($GLOBALS['skyuc']->topnewhots)) {
        $GLOBALS['skyuc']->topnewhots = array();
    } elseif (isset($GLOBALS['skyuc']->topnewhots["$key"]) &&
        !empty($GLOBALS['skyuc']->topnewhots["$key"])
    ) {
        return $GLOBALS['skyuc']->topnewhots["$key"];
    }
    //print_r($GLOBALS ['skyuc']->topnewhots);exit();
    switch ($type) {
        case 'recom':
            $where = ' attribute = 1';
            $sort = 'add_time';
            break;
        case 'recom_cate':
            $where = ' attribute = 2';
            $sort = 'add_time';
            break;
        case 'new':
        case 'new_cate':
        case 'new_article':
            $where = ' 1';
            $sort = 'add_time';
            break;
        case 'top':
        case 'top_cate':
        case 'top_detail':
        case 'cat_hot':
            $top_time = !empty($top_time) ? $top_time : 365;
            //当前时间减去指定天数unix时间
            $click_time = TIMENOW - 86400 * $top_time;
            if ($top_time == 7) {
                //当前时间减去天数
                $where = ' click_time >=' . $click_time;
                $sort = 'click_week';
            } elseif ($top_time == 30) {
                //当前时间减去天数
                $where = ' click_time >=' . $click_time;
                $sort = 'click_month';
            } else {
                $where = ' 1 ';
                $sort = 'click_count'; //按总点播次数排序
            }
            break;
    }
    /* 取得每一项的数量限制 */
    $type2lib = array('recom' => 'recom', 'recom_cate' => 'recom_cate',
        'new' => 'new10', 'new_article' => 'new10_article',
        'new_cate' => 'new10_cate', 'top' => 'top10',
        'top_detail' => 'top10_detail', 'top_cate' => 'top10_cate',
        'cat_hot' => 'cat_hot');
    $num = get_library_number($type2lib[$type]);
    if ($children) {
        $sql = 'SELECT m.show_id, m.director, m.actor, m.title, m.thumb, m.image, m.description, m.status, m.pubdate, m.click_count, m.cat_id, m.area, m.lang, m.points, m.runtime, m.add_time   FROM ' .
            TABLE_PREFIX . 'show' . ' AS m WHERE m.is_show=1 AND ' . $where .
            ' AND ' . $children . '  ORDER BY ' . $sort . ' DESC  ';
    } else {
        $sql = 'SELECT show_id, director, actor, title, thumb, image, description, status, pubdate, click_count, cat_id, area, lang, points, runtime, add_time  FROM ' .
            TABLE_PREFIX . 'show' . '  WHERE is_show=1 AND ' . $where .
            ' ORDER BY ' . $sort . ' DESC  ';
    }
    $sql = $GLOBALS['db']->query_limit($sql, $num);
    $res = $GLOBALS['db']->query_read_slave($sql);
    $arr = array();
    if ($res !== false) {
        while ($row = $GLOBALS['db']->fetch_array($res)) {
            $row['description'] = html2text($row['description']); //去除影片看点中HTML代码
            // 修正影片图片
            $row['image'] = get_image_path(
                $row['image']);
            $row['thumb'] = get_image_path($row['thumb']);
            $row['add_time'] = skyuc_date(
                $GLOBALS['skyuc']->options['date_format'] . ' ' .
                    $GLOBALS['skyuc']->options['time_format'], $row['add_time']);
            //演员搜索链接
            $row['actor'] = get_actor_array($row['actor']);
            $row['url'] = build_uri('show', array('mid' => $row['show_id']),
                $row['title']);
            $arr[] = $row;
        }
    }
    if (!empty($arr)) {
        //重建缓存
        $GLOBALS['skyuc']->topnewhots["$key"] = & $arr;
        //print_r($GLOBALS ['skyuc']->topnewhots ["$key"]);exit;
        build_datastore('topnewhots',
            serialize($GLOBALS['skyuc']->topnewhots), 1);
    }
    return $arr;
}

/**
 * 统计访问信息
 *
 * @access  public
 * @return  void
 */
function visit_stats()
{
    if (isset($GLOBALS['skyuc']->options['visit_stats']) &&
        $GLOBALS['skyuc']->options['visit_stats'] == 0
    ) {
        return;
    }
    //检查客户端是否存在访问统计的cookie
    $visit_times = (!empty($_COOKIE[COOKIE_PREFIX . 'visit_times'])) ? intval(
        $_COOKIE[COOKIE_PREFIX . 'visit_times']) + 1
        : 1;
    skyuc_setcookie('visit_times', $visit_times, TRUE);
    $browser = getbrowser();
    $os = get_os();
    $area = skyuc_geoip(ALT_IP);
    $keywords = '';
    // 语言
    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $pos = strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], ';');
        $lang = ($pos !== false) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0,
            $pos)
            : $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    } else {
        $lang = '';
    }
    // 来源
    if (!empty($_SERVER['HTTP_REFERER']) &&
        strlen($_SERVER['HTTP_REFERER']) > 9
    ) {
        $pos = strpos($_SERVER['HTTP_REFERER'], '/', 9);
        if ($pos !== false) {
            $domain = substr($_SERVER['HTTP_REFERER'], 0, $pos);
            $path = substr($_SERVER['HTTP_REFERER'], $pos);
            //来源关键字
            if (!empty($domain) && !empty($path)) {
                save_searchengine_keyword($domain, $path);
            }
        } else {
            $domain = $path = '';
        }
    } else {
        $domain = $path = '';
    }
    $sql = 'INSERT INTO ' . TABLE_PREFIX . 'stats' .
        ' (ip_address, visit_times, browser, system, language, area, referer_domain, referer_path, access_url, access_time) VALUES ' .
        " ('" . ALT_IP . "', '" . $visit_times . "', '" .
        $GLOBALS['db']->escape_string($browser) . "', '" .
        $GLOBALS['db']->escape_string($os) . "', '" .
        $GLOBALS['db']->escape_string($lang) . "', '" .
        $GLOBALS['db']->escape_string($area) . "',  '" .
        $GLOBALS['db']->escape_string($domain) . "', '" .
        $GLOBALS['db']->escape_string($path) . "', '" .
        $GLOBALS['db']->escape_string($_SERVER['PHP_SELF']) . "', '" . TIMENOW .
        "')";
    $GLOBALS['db']->query_write($sql);
}

/**
 * 保存搜索引擎关键字
 *
 * @param        $domain        域名
 * @param        $path            路径
 * @param        $engine 指定搜索引擎
 * @param        $word            指定搜索关键词
 * @return  void
 */
function save_searchengine_keyword($domain, $path, $engine = '', $word = '')
{
    if ($engine != '' && $word != '') {
        $searchengine = 'SKYUC';
        $keywords = $word;
    } elseif (strpos($domain, 'google.com.tw') !== false &&
        preg_match('/q=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'GOOGLE TAIWAN';
        $keywords = urldecode($regs[1]); // google taiwan
    } elseif (strpos($domain, 'google.cn') !== false &&
        preg_match('/q=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'GOOGLE CHINA';
        $keywords = urldecode($regs[1]); // google china
    } elseif (strpos($domain, 'google.com') !== false &&
        preg_match('/q=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'GOOGLE';
        $keywords = urldecode($regs[1]); // google
    } elseif (strpos($domain, 'baidu.') !== false &&
        preg_match('/wd=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'BAIDU';
        $keywords = skyuc_iconv('GB2312', 'UTF8', urldecode($regs[1])); // baidu
    } elseif (strpos($domain, 'baidu.') !== false &&
        preg_match('/word=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'BAIDU';
        $keywords = skyuc_iconv('GB2312', 'UTF8', urldecode($regs[1])); // baidu
    } elseif (strpos($domain, '114.vnet.cn') !== false &&
        preg_match('/kw=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'CT114';
        $keywords = skyuc_iconv('GB2312', 'UTF8', urldecode($regs[1])); // ct114
    } elseif (strpos($domain, 'iask.com') !== false &&
        preg_match('/k=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'IASK';
        $keywords = skyuc_iconv('GB2312', 'UTF8', urldecode($regs[1])); // iask
    } elseif (strpos($domain, 'soso.com') !== false &&
        preg_match('/w=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'SOSO';
        $keywords = skyuc_iconv('GB2312', 'UTF8', urldecode($regs[1])); // soso
    } elseif (strpos($domain, 'sogou.com') !== false &&
        preg_match('/query=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'SOGOU';
        $keywords = skyuc_iconv('GB2312', 'UTF8', urldecode($regs[1])); // sogou
    } elseif (strpos($domain, 'so.163.com') !== false &&
        preg_match('/q=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'NETEASE';
        $keywords = skyuc_iconv('GB2312', 'UTF8', urldecode($regs[1])); // netease
    } elseif (strpos($domain, 'yodao.com') !== false &&
        preg_match('/q=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'YODAO';
        $keywords = urldecode($regs[1]); // yodao
    } elseif (strpos($domain, 'zhongsou.com') !== false &&
        preg_match('/word=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'ZHONGSOU';
        $keywords = skyuc_iconv('GB2312', 'UTF8', urldecode($regs[1])); // zhongsou
    } elseif (strpos($domain, 'search.tom.com') !== false &&
        preg_match('/w=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'TOM';
        $keywords = skyuc_iconv('GB2312', 'UTF8', urldecode($regs[1])); // tom
    } elseif (strpos($domain, 'bing.com') !== false &&
        preg_match('/q=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'BING';
        $keywords = urldecode($regs[1]); // BING
    } elseif (strpos($domain, 'tw.search.yahoo.com') !== false &&
        preg_match('/p=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'YAHOO TAIWAN';
        $keywords = urldecode($regs[1]); // yahoo taiwan
    } elseif (strpos($domain, 'cn.yahoo.') !== false &&
        preg_match('/p=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'YAHOO CHINA';
        $keywords = skyuc_iconv('GB2312', 'UTF8', urldecode($regs[1])); // yahoo china
    } elseif (strpos($domain, 'yahoo.') !== false &&
        preg_match('/p=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'YAHOO';
        $keywords = urldecode($regs[1]); // yahoo
    } elseif (strpos($domain, 'msn.com.tw') !== false &&
        preg_match('/q=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'MSN TAIWAN';
        $keywords = urldecode($regs[1]); // msn taiwan
    } elseif (strpos($domain, 'msn.com.cn') !== false &&
        preg_match('/q=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'MSN CHINA';
        $keywords = urldecode($regs[1]); // msn china
    } elseif (strpos($domain, 'msn.com') !== false &&
        preg_match('/q=([^&]*)/i', $path, $regs)
    ) {
        $searchengine = 'MSN';
        $keywords = urldecode($regs[1]); // msn
    }
    if (!empty($keywords)) {
        $arr = array();
        $arr[] = "('" . TIMENOW . "', '" .
            $GLOBALS['db']->escape_string($searchengine) . "', '" .
            $GLOBALS['db']->escape_string($keywords) .
            "', 1)  ON DUPLICATE KEY UPDATE count=count+1";
        $GLOBALS['db']->query_insert(TABLE_PREFIX . 'keywords',
            '(date,	searchengine,	keyword,	count)', $arr);
    }
}

/**
 * 调用评论信息
 *
 * @access  public
 * @return  string
 */
function insert_comments($arr)
{
    $need_cache = $GLOBALS['smarty']->caching;
    $GLOBALS['smarty']->caching = false;
    // 验证码相关设置
    if ((intval($GLOBALS['skyuc']->options['humanverify']) & HV_COMMENT)) {
        $GLOBALS['smarty']->assign('enabled_captcha', 1);
    }
    $GLOBALS['smarty']->assign('username',
        $GLOBALS['skyuc']->userinfo['user_name']);
    $GLOBALS['smarty']->assign('email', $GLOBALS['skyuc']->userinfo['email']);
    $GLOBALS['smarty']->assign('comment_type', $arr['type']);
    $GLOBALS['smarty']->assign('id', $arr['id']);
    $cmt = assign_comment($arr['id'], $arr['type']);
    $GLOBALS['smarty']->assign('comments', $cmt['comments']);
    $GLOBALS['smarty']->assign('pager', $cmt['pager']);
    $val = $GLOBALS['smarty']->fetch('library/comments_list.lbi');
    $GLOBALS['smarty']->caching = $need_cache;
    return $val;
}

/**
 * 查询评论内容
 *
 * @access  public
 * @params  integer     $id
 * @params  integer     $type
 * @params  integer     $page
 * @return  array
 */
function assign_comment($id, $type, $page = 1)
{
    // 取得评论列表
    $total = $GLOBALS['db']->query_first_slave(
        'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'comment' .
            " WHERE id_value = '$id' AND comment_type = '$type' AND
     	status > 0");
    $size = iif(!empty($GLOBALS['skyuc']->options['comments_number']),
        $GLOBALS['skyuc']->options['comments_number'], 5);
    $page_count = iif(($total['total'] > 0),
        intval(ceil($total['total'] / $size)), 1);
    $sql = 'SELECT * FROM ' . TABLE_PREFIX . 'comment' . " WHERE id_value = '" .
        $id . "' AND comment_type = '" . $type . "' AND status > 0 " .
        ' ORDER BY comment_id DESC';
    $sql = $GLOBALS['db']->query_limit($sql, $size, ($page - 1) * $size);
    $res = $GLOBALS['db']->query_read_slave($sql);
    $arr = array();
    $ids = '';
    while ($row = $GLOBALS['db']->fetch_array($res)) {
        $arr[$row['comment_id']]['id'] = $row['comment_id'];
        $arr[$row['comment_id']]['email'] = $row['email'];
        $arr[$row['comment_id']]['username'] = $row['user_name'];
        $arr[$row['comment_id']]['content'] = nl2br(
            htmlspecialchars(fetch_censored_text($row['content'])));
        $arr[$row['comment_id']]['add_time'] = skyuc_date(
            $GLOBALS['skyuc']->options['date_format'] . ' ' .
                $GLOBALS['skyuc']->options['time_format'], $row['add_time']);
        $ip = explode('.', $row['ip_address']);
        $ip[3] = '*';
        $arr[$row['comment_id']]['ip'] = implode('.', $ip);

        $arr[$row['comment_id']]['agree'] = $row['agree'];
        $arr[$row['comment_id']]['against'] = $row['against'];
        if ($row['status'] == 2) {
            $arr[$row['comment_id']]['isadmin'] = true;
        }
        if ($row['parent_id'] > 0) {
            $arr[$row['comment_id']]['reply'] = get_comment($row['parent_id'],
                $row['id_value'], $type);
            $arr[$row['comment_id']]['retotal'] = count($arr[$row['comment_id']]['reply']);
        }
    }

   // print_r($arr);exit();

    $pager['page'] = $page;
    $pager['size'] = $size;
    $pager['record_count'] = $total['total'];
    $pager['page_count'] = $page_count;
    $pager['page_first'] = 'javascript:gotoPage(1,' . $id . ', ' . $type . ')';
    $pager['page_prev'] = $page > 1 ? 'javascript:gotoPage(' . ($page - 1) .
        ', ' . $id . ', ' . $type . ')'
        : 'javascript:;';
    $pager['page_next'] = $page < $page_count ? 'javascript:gotoPage(' .
        ($page + 1) . ',' . $id . ',' . $type . ')'
        : 'javascript:;';
    $pager['page_last'] = $page < $page_count ? 'javascript:gotoPage(' .
        $page_count . ',' . $id . ',' . $type . ')'
        : 'javascript:;';
    $cmt = array('comments' => $arr, 'pager' => $pager);
    return $cmt;
}

/**
 * 获取回复评论
 * @access  public
 * @param   integer $pid    上级评论编号
 * @param   integer $id 影片编号
 * @param   integer $level 递归层次，默认29次，即29条评论
 * @param   array   $arr 临时储存回复评论数组
 * @return  array
 */
function get_comment($pid, $id, $type, $level = 0, $arr = array())
{
    $sql = 'SELECT * FROM ' . TABLE_PREFIX . 'comment' .
        " WHERE comment_id  = '" . $pid . "' AND status > 0";
    $sql = $GLOBALS['db']->query_limit($sql, 1);
    $res = $GLOBALS['db']->query_read_slave($sql);
    $row = $GLOBALS['db']->fetch_array($res);

    $ip = explode('.', $row['ip_address']);
    $ip[3] = '*';
    $row['ip'] = implode('.', $ip);
    $row['username'] = $row['user_name'];
    if ($row['status'] == 2) {
        $row['isadmin'] = true;
    }

    $arr[] = $row;
    if ($row['parent_id'] > 0 and  $level < 29) {
        $level++;
        return get_comment($row['parent_id'], $row['id_value'],
            $type, $level, $arr);
    } else {
        krsort($arr);
        sort($arr);
        return $arr;
    }
}

/**
 * 替换动态模块
 *
 * @access  public
 * @param   string       $matches    匹配内容
 *
 * @return string        结果
 */
function dyna_libs_replace($matches)
{
    $key = '/' . $matches[1];
    if ($row = array_shift($GLOBALS['libs'][$key])) {
        $str = '';
        switch ($row['type']) {
            case 1:
                // 分类的影片
                $str = '{assign var="cat_show" value=$cat_show_' .
                    $row['id'] . '}{assign var="show_cat" value=$show_cat_' .
                    $row['id'] . '}';
                break;
            case 2:
                // 分类的排行
                $str = '{assign var="cat_hot" value=$cat_hot_' .
                    $row['id'] . '}{assign var="show_cat" value=$show_cat_' .
                    $row['id'] . '}';
                break;
            case 3:
                // 连载影片
                $str = '{assign var="series" value=$series_' .
                    $row['id'] . '}{assign var="show_cat" value=$show_cat_' .
                    $row['id'] . '}';
                break;
            case 4:
                //广告位
                $str = '{assign var="ads_id" value=' .
                    $row['id'] . '}{assign var="ads_num" value=' . $row['number'] .
                    '}';
                break;
        }
        return $str . $matches[0];
    } else {
        return $matches[0];
    }
}

/**
 * 获取指定主题某个模板的主题的动态模块
 *
 * @access  public
 * @param   string       $theme    模板主题
 * @param   string       $tmp      模板名称
 *
 * @return array()
 */
function get_dyna_libs($theme, $tmp)
{
    $tmp_arr = explode('.', $tmp);
    $ext = end($tmp_arr);
    $tmp = basename($tmp, ".$ext");
    $sql = 'SELECT region, library, sort_order, id, number, type' . ' FROM ' .
        TABLE_PREFIX . 'template' . " WHERE theme = '" . $theme .
        "' AND filename = '" . $tmp . "' AND type > 0 AND remarks=''" .
        ' ORDER BY region, library, sort_order';
    $res = $GLOBALS['db']->query_all_slave($sql);
    $dyna_libs = array();
    foreach ($res as $row) {
        $dyna_libs[$row['region']][$row['library']][] = array(
            'id' => $row['id'], 'number' => $row['number'], 'type' => $row['type']);
    }
    return $dyna_libs;
}

/**
 * 获得指定页面的动态内容
 *
 * @access  public
 * @param   string  $tmp    模板名称
 * @return  void
 */
function assign_dynamic($tmp)
{
    $sql = 'SELECT id, number, type FROM ' . TABLE_PREFIX . 'template' .
        " WHERE filename = '" . $tmp .
        "' AND type > 0  AND remarks ='' AND theme = '" .
        $GLOBALS['skyuc']->options['themes'] . "'";
    $key = md5($sql); //缓存名称：键
    //读缓存
    if ($data = get_file_cache($key)) {
        $arr = $data;
    } else {
        $arr = $GLOBALS['db']->query_all_slave($sql);
        if (empty($arr)) {
            $arr = array('4'); //防止没有动态数据时，重复读写缓存
        }
        put_file_cache($key, $arr); //写缓存
    }
    foreach ($arr as $row) {
        if (!isset($row['type'])) continue;
        $type = (int)$row['type'];
        switch ($type) {
            case 1:
                // 分类下的影片
                $GLOBALS['smarty']->assign(
                    'show_cat_' . $row['id'],
                    assign_cat_show($row['id'], $row['number']));
                break;
            case 2:
                //分类的排行榜
                $children = get_children($row['id']); //底层分类ID
                $GLOBALS['smarty']->assign(
                    'cat_hot_' . $row['id'], get_top_new_hot('cat_hot', $children));
                //分类信息
                $cat_id = $row['id'];
                $sql = 'SELECT cat_name FROM ' . TABLE_PREFIX . 'category' .
                    ' WHERE cat_id = ' . $cat_id;
                $key = md5($sql); //缓存名称：键
                //读缓存
                if ($data = get_file_cache(
                    $key)
                ) {
                    $cate = $data;
                } else {
                    $cate = $GLOBALS['db']->query_first_slave($sql);
                    put_file_cache($key, $cate); //写缓存
                }
                $cat['name'] = $cate['cat_name'];
                $cat['url'] = build_uri('category', array('cid' => $cat_id),
                    $cat['name']);
                $cat['id'] = $cat_id;
                $GLOBALS['smarty']->assign('top_cat_' . $row['id'], $cat);
                break;
            case 3:
                // 连载影片
                $GLOBALS['smarty']->assign(
                    'show_cat_' . $row['id'],
                    assign_series($row['id'], $row['number']));
                break;
            default:
                break;
        }
    }
}

/**
 * 获得指定分类下的影片
 *
 * @access  public
 * @param   integer     $cat_id     分类ID
 * @param   integer     $num        数量
 * @return  array
 */
function assign_cat_show($cat_id, $num = 0)
{
    $children = get_children($cat_id); //底层分类ID
    $num = $num < 100 && $num > 0 ? $num : 10;
    $sql = 'SELECT m.show_id, m.director, m.actor, m.title, m.thumb, m.image, m.description, m.status, m.pubdate, m.click_count, m.cat_id, m.area, m.lang,  m.points, m.runtime, m.add_time FROM ' .
        TABLE_PREFIX . 'show' .
        ' AS m  WHERE m.is_show=1 AND (m.attribute is null OR m.attribute =0)  AND ' .
        $children . ' ORDER BY add_time DESC  ';
    $sql = $GLOBALS['db']->query_limit($sql, $num);
    $key = md5($sql); //缓存名称：键
    //读缓存
    if ($data = get_file_cache($key)) {
        $arr = $data;
    } else {
        $res = $GLOBALS['db']->query_read_slave($sql);
        $arr = array();
        if ($res !== false) {
            while ($row = $GLOBALS['db']->fetch_array($res)) {
                $row['description'] = html2text($row['description']); //去除影片看点中HTML代码
                // 修正影片图片
                $row['image'] = get_image_path(
                    $row['image']);
                $row['thumb'] = get_image_path($row['thumb']);
                //演员搜索链接
                $row['actor'] = get_actor_array($row['actor']);
                $row['add_time'] = skyuc_date(
                    $GLOBALS['skyuc']->options['date_format'] . ' ' .
                        $GLOBALS['skyuc']->options['time_format'], $row['add_time']);
                $row['url'] = build_uri('show',
                    array('mid' => $row['show_id']), $row['title']);
                $arr[] = $row;
            }
        }
        put_file_cache($key, $arr); //写缓存
    }
    $GLOBALS['smarty']->assign('cat_show_' . $cat_id, $arr);
    //分类信息
    $sql = 'SELECT cat_name FROM ' . TABLE_PREFIX . 'category' .
        ' WHERE cat_id = ' . $cat_id;
    $key = md5($sql); //缓存名称：键
    //读缓存
    if ($data = get_file_cache($key)) {
        $cat_name = $data;
    } else {
        $cat_name = $GLOBALS['db']->query_first_slave($sql);
        put_file_cache($key, $cat_name); //写缓存
    }
    $cat = array();
    $cat['name'] = $cat_name['cat_name'];
    $cat['url'] = build_uri('category', array('cid' => $cat_id), $cat['name']);
    $cat['id'] = $cat_id;
    $cat['sub'] = get_cat_list($cat_id, 0, False, 2);
    return $cat;
}

/**
 * 分配连载影片给smarty
 *
 * @access  public
 * @param   integer     $cat_id     分类的编号
 * @param   integer     $num    影片数量
 * @return  array
 */
function assign_series($cat_id, $num)
{
    $children = get_children($cat_id); //底层分类ID
    $num = $num < 100 && $num > 0 ? $num : 10;
    $sql = 'SELECT m.show_id, m.director, m.actor, m.title, m.thumb, m.image, m.description, m.status, m.pubdate, m.click_count, m.cat_id, m.area, m.lang,  m.points, m.runtime, m.add_time FROM ' .
        TABLE_PREFIX . 'show' .
        ' AS m  WHERE m.is_show=1 AND m.attribute >2   AND ' . $children .
        ' ORDER BY add_time DESC  ';
    $sql = $GLOBALS['db']->query_limit($sql, $num);
    $key = md5($sql); //缓存名称：键
    //读缓存
    if ($data = get_file_cache($key)) {
        $arr = $data;
    } else {
        $res = $GLOBALS['db']->query_read_slave($sql);
        $arr = array();
        if ($res !== false) {
            while ($row = $GLOBALS['db']->fetch_array($res)) {
                $row['description'] = html2text($row['description']); //去除影片看点中HTML代码
                // 修正影片图片
                $row['image'] = get_image_path(
                    $row['image']);
                $row['thumb'] = get_image_path($row['thumb']);
                //演员搜索链接
                $row['actor'] = get_actor_array($row['actor']);
                $row['add_time'] = skyuc_date(
                    $GLOBALS['skyuc']->options['date_format'] . ' ' .
                        $GLOBALS['skyuc']->options['time_format'], $row['add_time']);
                $row['url'] = build_uri('show',
                    array('mid' => $row['show_id']), $row['title']);
                $arr[] = $row;
            }
            put_file_cache($key, $arr); //写缓存
        }
    }
    $GLOBALS['smarty']->assign('series_' . $cat_id, $arr);
    //分类信息
    $sql = 'SELECT cat_name FROM ' . TABLE_PREFIX . 'category' .
        ' WHERE cat_id = ' . $cat_id;
    $key = md5($sql); //缓存名称：键
    //读缓存
    if ($data = get_file_cache($key)) {
        $cat_name = $data;
    } else {
        $cat_name = $GLOBALS['db']->query_first_slave($sql);
        put_file_cache($key, $cat_name); //写缓存
    }
    $cat = array();
    $cat['name'] = $cat_name['cat_name'];
    $cat['url'] = build_uri('category', array('cid' => $cat_id), $cat['name']);
    $cat['id'] = $cat_id;
    return $cat;
}

/**
 * 获取网站设置信息,用于前台显示
 *
 * @access  public
 * @return  string
 */
function assign_template($ctype = '', $catlist = array())
{
    $GLOBALS['smarty']->assign('image_width',
        $GLOBALS['skyuc']->options['image_width']);
    $GLOBALS['smarty']->assign('image_height',
        $GLOBALS['skyuc']->options['image_height']);
    $GLOBALS['smarty']->assign('thumb_width',
        $GLOBALS['skyuc']->options['thumb_width']);
    $GLOBALS['smarty']->assign('thumb_height',
        $GLOBALS['skyuc']->options['thumb_height']);
    $GLOBALS['smarty']->assign('integral_scale',
        $GLOBALS['skyuc']->options['integral_scale']);
    $GLOBALS['smarty']->assign('qq',
        explode(',', $GLOBALS['skyuc']->options['qq']));
    $GLOBALS['smarty']->assign('ww',
        explode(',', $GLOBALS['skyuc']->options['ww']));
    $GLOBALS['smarty']->assign('ym',
        explode(',', $GLOBALS['skyuc']->options['ym']));
    $GLOBALS['smarty']->assign('msn',
        explode(',', $GLOBALS['skyuc']->options['msn']));
    $GLOBALS['smarty']->assign('skype',
        explode(',', $GLOBALS['skyuc']->options['skype']));
    $GLOBALS['smarty']->assign('stats_code',
        $GLOBALS['skyuc']->options['stats_code']);
    $GLOBALS['smarty']->assign('copyright',
        htmlspecialchars($GLOBALS['skyuc']->options['copyright']));
    $GLOBALS['smarty']->assign('site_name',
        $GLOBALS['skyuc']->options['site_name']);
    $GLOBALS['smarty']->assign('site_url',
        $GLOBALS['skyuc']->options['site_url']);
    $GLOBALS['smarty']->assign('service_email',
        $GLOBALS['skyuc']->options['service_email']);
    $GLOBALS['smarty']->assign('service_phone',
        $GLOBALS['skyuc']->options['service_phone']);
    $GLOBALS['smarty']->assign('site_address',
        $GLOBALS['skyuc']->options['site_address']);
    $GLOBALS['smarty']->assign('licensed', license_info());
    $GLOBALS['smarty']->assign('skyuc_version',
        $GLOBALS['skyuc']->options['skyuc_version']);
    $GLOBALS['smarty']->assign('category_list', get_cat_list(0, 0, true, 2));
    $GLOBALS['smarty']->assign('catalog_list', get_cat_list(0, 0, false, 1));
    $GLOBALS['smarty']->assign('navigator_list',
        get_navigator($ctype, $catlist)); //自定义导航栏
    if (!empty($GLOBALS['skyuc']->options['search_keywords'])) {
        $searchkeywords = explode(' ',
            trim($GLOBALS['skyuc']->options['search_keywords']));
    } else {
        $searchkeywords = array();
    }
    $GLOBALS['smarty']->assign('searchkeywords', $searchkeywords);
}

/**
 * 取得自定义导航栏列表
 * @param   string      $type    位置，如top、bottom、middle
 * @return  array         列表
 */
function get_navigator($ctype = '', $catlist = array())
{
    $sql = 'SELECT * FROM ' . TABLE_PREFIX . 'nav' .
        ' WHERE ifshow = 1 ORDER BY type, vieworder';
    $key = md5($sql); //缓存名称：键
    //读缓存
    if ($data = get_file_cache($key)) {
        $navlist = $data;
    } else {
        $res = $GLOBALS['db']->query_read($sql);
        $cur_url = substr(strrchr($_SERVER['REQUEST_URI'], '/'), 1);
        $noindex = false;
        $active = 0;
        $navlist = array('top' => array(), 'middle' => array(), 'bottom' => array());
        while ($row = $GLOBALS['db']->fetch_array($res)) {
            $navlist[$row['type']][] = array('name' => $row['name'],
                'opennew' => $row['opennew'], 'url' => $row['url'],
                'ctype' => $row['ctype'], 'cid' => $row['cid']);
        }
        put_file_cache($key, $navlist); //写缓存
    }
    // 遍历自定义是否存在currentPage
    foreach ($navlist['middle'] as $k => $v) {
        if ($v['url'] == $cur_url) {
            $navlist['middle'][$k]['active'] = 1;
            $noindex = true;
            $active += 1;
        }
    }
    if (!empty($ctype) && $active < 1) {
        foreach ($catlist as $key => $val) {
            foreach ($navlist['middle'] as $k => $v) {
                if (!empty($v['ctype']) && $v['ctype'] == $ctype &&
                    $v['cid'] == $val
                ) {
                    $navlist['middle'][$k]['active'] = 1;
                    $noindex = true;
                }
            }
        }
    }
    if ($noindex == false) {
        $navlist['config']['index'] = 1;
    }
    return $navlist;
}

/**
 * 显示授权信息
 * @return  string
 */
function license_info()
{
    if ($GLOBALS['skyuc']->options['licensed'] > 0) {
        // 获取HOST
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        } elseif (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        }
        $license = '<a href="http://www.skyuc.com/license/?action=certificate&host=' .
            $host . '">Licensed</a>';
        return $license;
    } else {
        return '';
    }
}

/**
 * 获取用户信息：用于ajax显示在登陆框中
 *
 * @access  public
 * @param   int         $user_id            用户ID
 *
 * @return  array       $info
 */
function get_member_info($user_id = 0)
{
    if (empty($user_id)) {
        $user_id = $GLOBALS['skyuc']->session->vars['userid'];
    }
    $sql = 'SELECT user_name, usertype, user_point, pay_point, unit_date, reg_time	FROM ' .
        TABLE_PREFIX . 'users' . ' WHERE user_id = ' . $user_id;
    $row = $GLOBALS['db']->query_first($sql);
    $info = $row;
    if ($row['unit_date'] > 0) {
        $info['unit_date'] = skyuc_date(
            $GLOBALS['skyuc']->options['date_format'], $row['unit_date']);
    } else {
        $info['unit_date'] = skyuc_date(
            $GLOBALS['skyuc']->options['date_format'], $row['reg_time']);
    }
    $info['username'] = $row['user_name'];
    $info['integral'] = $row['pay_point'];
    if ($row['usertype'] == 1) {
        $info['usertype'] = $GLOBALS['skyuc']->lang['is_day'];
        $info['endlook'] = $info['unit_date'];
        $info['your_endlook'] = $GLOBALS['skyuc']->lang['your_endlook_d'];
    } else {
        $info['usertype'] = $GLOBALS['skyuc']->lang['is_count'];
        $info['endlook'] = $row['user_point'];
        $info['your_endlook'] = $GLOBALS['skyuc']->lang['your_endlook_p'];
    }
    return $info;
}

/**
 * 修正影片海报路径
 *
 * @param string $image 图片地址
 *
 * @return string   $url
 */
function get_image_path($image = '')
{
    if (empty($image)) {
        $url = './data/images/nopic.gif';
    } else {
        $url = iif(
            !empty($GLOBALS['skyuc']->options['image_host']) &&
                pic_parse_url($image),
            $GLOBALS['skyuc']->options['image_host'] . '' . $image, $image);
    }
    return $url;
}

/**
 * 影片主演生成演员搜索链接
 *
 * @param string $string 影片主演
 *
 * @return array
 */
function get_actor_array($string)
{
    if (is_array($string) || empty($string)) {
        return $string;
    }
    if (strpos($string, ',') !== false) {
        $separation = ',';
    }
    elseif (strpos($string, '，') !== false) {
        $separation = '，';
    }
    elseif (strpos($string, '|') !== false) {
        $separation = '|';
    }
    elseif (strpos($string, '、') !== false) {
        $separation = '、';
    }
    elseif (strpos($string, '/') !== false) {
        $separation = '/';
    }
    else {
        $separation = ' ';
    }
    //替换半角全角逗号，全角顿号以及竖线为换行符
    $string = str_replace($separation, "\n", trim($string));
    // $string = trim(preg_replace("/\s*(\s)\s*/u", "\r\n", trim($string))); //不匹配全角空格,演员名为二个字时中间有全角空格
    $Array = explode("\n", $string);
    return $Array;
}

/**
 * 防止CC攻击
 *
 * @return void
 */
function block_cc()
{
    if (isset($GLOBALS['skyuc']->config['Misc']['db_cc']) && isset($GLOBALS['skyuc']->config['Misc']['db_loadavg'])) {
        $c_agentip = 1;
        if (!isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !isset($_SERVER['HTTP_CLIENT_IP'])) {
            $c_agentip = 0;
        }
        if (!(DIRECTORY_SEPARATOR == '\\')) {
            if (!is_array($GLOBALS['skyuc']->loadcache) ||$GLOBALS['skyuc']->loadcache['lastcheck'] < (TIMENOW - 300)) {
                update_loadavg();
            }
            if ((int)$GLOBALS['skyuc']->config['Misc']['db_loadavg'] > 0 &&(int)$GLOBALS['skyuc']->loadcache['loadavg'] > 0 ) {
                (int)$GLOBALS['skyuc']->loadcache['loadavg'] > (int)$GLOBALS['skyuc']->config['Misc']['db_loadavg'] &&
                    $GLOBALS['skyuc']->config['Misc']['db_cc'] == 2;
            }
        }
        if ((!isset($_COOKIE) && !isset($_SERVER['HTTP_USER_AGENT'])) || ($GLOBALS['skyuc']->config['Misc']['db_cc'] == 2 && $c_agentip) ) {
            exit('Forbidden');
        }
    }
}

// #############################################################################
/**
 * 替换任何屏蔽的字词 ，在 $GLOBALS['skyuc']->options['censorwords'] 中替换为 $GLOBALS['skyuc']->options['censorchar']
 *
 * @param    string    要检查的字词
 *
 * @return    string
 */
function fetch_censored_text($text)
{
    static $censorwords;
    if (!$text) {
        // 返回 $text 而不是什么都没有, 因为这可能是 '' 或 0
        return $text;
    }
    if ($GLOBALS['skyuc']->options['enablecensor'] and
        !empty($GLOBALS['skyuc']->options['censorwords'])
    ) {
        if (empty($censorwords)) {
            $GLOBALS['skyuc']->options['censorwords'] = preg_quote(
                $GLOBALS['skyuc']->options['censorwords'], '#');
            $censorwords = preg_split('#[ \r\n\t]+#',
                $GLOBALS['skyuc']->options['censorwords'], -1, PREG_SPLIT_NO_EMPTY);
        }
        foreach ($censorwords as $censorword) {
            if (substr($censorword, 0, 2) == '\\{') {
                if (substr($censorword, -2, 2) == '\\}') {
                    // 防止错误的替换，如果  { 和 } 不匹配
                    $censorword = substr($censorword, 2,
                        -2);
                }
                // ASCII 字符搜索 0-47, 58-64, 91-96, 123-127
                $nonword_chars = '\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f';
                // 由 ASCII 字符分隔单词，在 A-Z, a-z 和 0-9 以外
                $text = preg_replace(
                    '#(?<=[' . $nonword_chars . ']|^)' . $censorword . '(?=[' .
                        $nonword_chars . ']|$)#si',
                    str_repeat($GLOBALS['skyuc']->options['censorchar'],
                        skyuc_strlen($censorword)), $text);
            } else {
                $text = preg_replace("#$censorword#si",
                    str_repeat($GLOBALS['skyuc']->options['censorchar'],
                        skyuc_strlen($censorword)), $text);
            }
        }
    }
    return $text;
}

// #############################################################################
/*	验证封禁IP
 *
 * 如果您输入了一个完整的 IP 地址 (242.21.11.7)，仅该 IP 地址会被封禁。
 * 如果您输入了一个不完整的 IP (243.21.11. 或 243.21.11)，任何以这部分开头的 IP 地址都会被封禁。
 * 例如，封禁 243.21.11 将会阻止 243.21.11.7 访问论坛。但是，243.21.115.7 仍然可以访问论坛。
 * 您也可以使用 * 作为通配符，使得封禁更为灵活。
 * 例如，如果您输入 243.21.11*，多个 IP 会被封禁，包括：243.21.11.7, 243.21.115.7, 243.21.119.225。
 * 每个 IP 地址封禁条件以空格或回车分隔。
 */
function verify_ip_ban()
{
    $user_ipaddress = IPADDRESS . '.';
    if ($GLOBALS['skyuc']->options['enablebanning'] == 1 and $GLOBALS['skyuc']->options['banip'] = trim(
        $GLOBALS['skyuc']->options['banip'])
    ) {
        $addresses = preg_split('#\s+#', $GLOBALS['skyuc']->options['banip'],
            -1, PREG_SPLIT_NO_EMPTY);
        foreach ($addresses as $banned_ip) {
            if (strpos($banned_ip, '*') === false and
                $banned_ip{strlen($banned_ip) - 1} != '.'
            ) {
                $banned_ip .= '.';
            }
            $banned_ip_regex = str_replace('\*', '(.*)',
                preg_quote($banned_ip, '#'));
            if (preg_match('#^' . $banned_ip_regex . '#U', $user_ipaddress)) {
                die('Your IP has been banned!');
            }
        }
    }
}

/**
 * 文件缓存读取
 * @param $key 缓存名称ID，32位字母和数字
 */
function get_file_cache($key)
{
    //return false;
    $data = '';
    if ($GLOBALS['skyuc']->secache->fetch($key, $data)) {
        $para = @unserialize($data);
        if ($para['expires'] > TIMENOW) {
            return $para['data']; //缓存时间未过期，返回缓存中数据
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * 文件缓存写入
 * @param $key 缓存名称ID，32位字母和数字
 * @param $data 要写入缓存的数据
 */
function put_file_cache($key, $data)
{
    //return false;
    if (!empty($data)) {
        $data = serialize(
            array('data' => $data,
                'expires' => TIMENOW + $GLOBALS['skyuc']->options['cache_time'] * 3600));
        if ($GLOBALS['skyuc']->secache->store($key, $data) === false) {
            trigger_error('can\'t write:' . $key);
        }
    }
}

?>