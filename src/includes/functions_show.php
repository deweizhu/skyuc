<?php

/**
 * SKYUC! 影片相关函数库
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

/**
 * 生成过滤条件：用于get_show_list和get_showlist
 * @param   object  $filter
 * @return  string
 */
function get_where_sql($filter)
{

    // 生成是否隐藏影片列表is_show==0是隐藏,1为显示
    $where = isset ($filter->is_show) && $filter->is_show == '0'
        ? ' WHERE is_show = 0 ' : ' WHERE is_show = 1 ';
    $where .= isset ($filter->cat_id) && $filter->cat_id > 0
        ? ' AND ' . get_children($filter->cat_id) : '';
    $where .= isset ($filter->server_id) && $filter->server_id > 0
        ? " AND server_id = '" . $filter->server_id . "'" : '';

    $where .= isset ($filter->keyword) && trim($filter->keyword) != ''
        ? " AND (title LIKE '%" . $GLOBALS ['db']->escape_string_like($filter->keyword) . "%' OR actor LIKE '%" . $GLOBALS ['db']->escape_string_like($filter->keyword) . "%' OR show_id LIKE '%" . $GLOBALS ['db']->escape_string_like($filter->keyword) . "%') "
        : '';

    return $where;
}

/**
 * 获得指定分类下的子分类的数组
 *
 * @access  public
 * @param   int     $cat_id     分类的ID
 * @param   int     $selected   当前选中分类的ID
 * @param   boolean $re_type    返回的类型: 值为真时返回下拉列表,否则返回数组
 * @param   int     $level      限定返回的级数。为0时返回所有级数
 * @param   int     $is_show_all 如果为true显示所有分类，如果为false隐藏不可见分类。
 * @return  mix
 */
function get_cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true)
{

    static $res = null;

    if (empty ($GLOBALS ['skyuc']->category) || !isset ($GLOBALS ['skyuc']->category)) {
        if (!defined('IN_CONTROL_PANEL')) {
            require_once (DIR . '/includes/functions_admin.php');
        }
        $GLOBALS ['skyuc']->category = build_category();
    }

    $res = &    $GLOBALS ['skyuc']->category;

    if (empty ($res) == true) {
        return $re_type ? '' : array();
    }

    $options = get_cat_options($cat_id, $res); // 获得指定分类下的子分类的数组


    $children_level = 29; //大于这个级别分类的将被删除
    if ($is_show_all == false) {
        foreach ($options as $key => $val) {
            if ($val ['level'] > $children_level) {
                unset ($options [$key]);
            } else {
                if ($val ['is_show'] == 0) {
                    unset ($options [$key]);
                    if ($children_level > $val ['level']) {
                        $children_level = $val ['level']; //标记一下，这样子分类也能删除
                    }
                } else {
                    $children_level = 29; //恢复初始值
                }
            }
        }
    }
    // 截取到指定的缩减级别
    if ($level > 0) {
        if ($cat_id == 0) {
            $end_level = $level;
        } else {
            $first_item = reset($options); // 获取第一个元素
            $end_level = $first_item ['level'] + $level;
        }

        // 保留level小于end_level的部分
        foreach ($options as $key => $val) {
            if ($val ['level'] >= $end_level) {
                unset ($options [$key]);
            }
        }
    }

    if ($re_type == true) {
        $select = '';
        foreach ($options as $var) {
            $select .= '<option value="' . $var ['cat_id'] . '" ';
            $select .= ($selected == $var ['cat_id']) ? "selected='ture'" : '';
            $select .= '>';
            if ($var ['level'] > 0) {
                $select .= str_repeat('&nbsp;', $var ['level'] * 4);
            }
            $select .= htmlspecialchars($var ['cat_name'], ENT_QUOTES) . '</option>';
        }

        return $select;
    } else {
        foreach ($options as $key => $value) {
            $options [$key] ['url'] = build_uri('category', array('cid' => $value ['cat_id']), $value ['cat_name']);
        }

        return $options;
    }

}

/**
 * 过滤和排序所有分类，返回一个带有缩进级别的数组
 *
 * @access  private
 * @param   int     $cat_id     上级分类ID
 * @param   array   $arr        含有所有分类的数组
 * @param   int     $level      级别
 * @return  void
 */
function get_cat_options($spec_cat_id, $arr)
{
    static $cat_options = array();

    if (isset ($cat_options [$spec_cat_id])) {
        return $cat_options [$spec_cat_id];
    }

    if (!isset ($cat_options [0])) {
        $level = $last_cat_id = 0;
        $options = $cat_id_array = $level_array = array();
        while (!empty ($arr)) {
            foreach ($arr as $key => $value) {
                $cat_id = $value ['cat_id'];
                if ($level == 0 && $last_cat_id == 0) {
                    if ($value ['parent_id'] > 0) {
                        break;
                    }

                    $options [$cat_id] = $value;
                    $options [$cat_id] ['level'] = $level;
                    $options [$cat_id] ['id'] = $cat_id;
                    $options [$cat_id] ['name'] = $value ['cat_name'];
                    unset ($arr [$key]);

                    if ($value ['has_children'] == 0) {
                        continue;
                    }
                    $last_cat_id = $cat_id;
                    $cat_id_array = array($cat_id);
                    $level_array [$last_cat_id] = ++$level;
                    continue;
                }

                if ($value ['parent_id'] == $last_cat_id) {
                    $options [$cat_id] = $value;
                    $options [$cat_id] ['level'] = $level;
                    $options [$cat_id] ['id'] = $cat_id;
                    $options [$cat_id] ['name'] = $value ['cat_name'];
                    unset ($arr [$key]);

                    if ($value ['has_children'] > 0) {
                        if (end($cat_id_array) != $last_cat_id) {
                            $cat_id_array [] = $last_cat_id;
                        }
                        $last_cat_id = $cat_id;
                        $cat_id_array [] = $cat_id;
                        $level_array [$last_cat_id] = ++$level;
                    }
                } elseif ($value ['parent_id'] > $last_cat_id) {
                    break;
                }
            }

            $count = count($cat_id_array);
            if ($count > 1) {
                $last_cat_id = array_pop($cat_id_array);
            } elseif ($count == 1) {
                if ($last_cat_id != end($cat_id_array)) {
                    $last_cat_id = end($cat_id_array);
                } else {
                    $level = 0;
                    $last_cat_id = 0;
                    $cat_id_array = array();
                    continue;
                }
            }

            if ($last_cat_id && isset ($level_array [$last_cat_id])) {
                $level = $level_array [$last_cat_id];
            } else {
                $level = 0;
            }
        }
        $cat_options [0] = $options;
    } else {
        $options = $cat_options [0];
    }

    if (!$spec_cat_id) {
        return $options;
    } else {
        if (empty ($options [$spec_cat_id])) {
            return array();
        }

        $spec_cat_id_level = $options [$spec_cat_id] ['level'];

        foreach ($options as $key => $value) {
            if ($key != $spec_cat_id) {
                unset ($options [$key]);
            } else {
                break;
            }
        }

        $spec_cat_id_array = array();
        foreach ($options as $key => $value) {
            if (($spec_cat_id_level == $value ['level'] && $value ['cat_id'] != $spec_cat_id) || ($spec_cat_id_level > $value ['level'])) {
                break;
            } else {
                $spec_cat_id_array [$key] = $value;
            }
        }
        $cat_options [$spec_cat_id] = $spec_cat_id_array;

        return $spec_cat_id_array;
    }
}

/**
 * 重写 URL 地址
 *
 * @access  public
 * @param   string  $app    执行程序
 * @param   array   $params 参数数组
 * @param   string  $append 附加字串
 * @param   integer $page   页数
 * @return  void
 */
function build_uri($app, $params, $append = '', $page = 0, $size = 0)
{

    static $rewrite = NULL;

    if ($rewrite === NULL) {
        $rewrite = intval($GLOBALS ['skyuc']->options ['rewrite']);
    }

    $args = array('cid' => 0, 'mid' => 0, 'bid' => 0, 'acid' => 0, 'aid' => 0, 'sid' => 0);
    extract(array_merge($args, $params));

    $uri = '';
    switch ($app) {
        case 'category' :
            if (empty ($cid)) {
                return false;
            } else {
                if ($rewrite) {
                    $uri = 'list-' . $cid;
                    if (!empty ($bid)) {
                        $uri .= '-s' . $bid;
                    }
                    if (!empty ($page)) {
                        $uri .= '-' . $page;
                    }
                    if (!empty ($sort)) {
                        $uri .= '-' . $sort;
                    }
                    if (!empty ($order)) {
                        $uri .= '-' . $order;
                    }
                    if (!empty ($display)) {
                        $uri .= '-' . $display;
                    }
                } else {
                    $uri = 'list.php?id=' . $cid;
                    if (!empty ($bid)) {
                        $uri .= '&amp;server=' . $bid;
                    }
                    if (!empty ($page)) {
                        $uri .= '&amp;page=' . $page;
                    }
                    if (!empty ($sort)) {
                        $uri .= '&amp;sort=' . $sort;
                    }
                    if (!empty ($order)) {
                        $uri .= '&amp;order=' . $order;
                    }
                    if (!empty ($display)) {
                        $uri .= '&amp;display=' . $display;
                    }
                }
            }
            break;
        case 'show' :
            if (empty ($mid)) {
                return false;
            } else {
                $uri = $rewrite ? 'show-' . $mid : 'show.php?id=' . $mid;
            }

            break;
        case 'article_cat' :
            if (empty ($acid)) {
                return false;
            } else {
                if ($rewrite) {
                    $uri = 'article_cat-' . $acid;
                    if (!empty ($page)) {
                        $uri .= '-' . $page;
                    }
                    if (!empty ($sort)) {
                        $uri .= '-' . $sort;
                    }
                    if (!empty ($order)) {
                        $uri .= '-' . $order;
                    }
                } else {
                    $uri = 'article_cat.php?id=' . $acid;
                    if (!empty ($page)) {
                        $uri .= '&amp;page=' . $page;
                    }
                    if (!empty ($sort)) {
                        $uri .= '&amp;sort=' . $sort;
                    }
                    if (!empty ($order)) {
                        $uri .= '&amp;order=' . $order;
                    }
                }

            }

            break;
        case 'article' :
            if (empty ($aid)) {
                return false;
            } else {
                $uri = $rewrite ? 'article-' . $aid : 'article.php?id=' . $aid;
            }

            break;
        case 'subject' :
            if (empty ($sid)) {
                return false;
            } else {
                $uri = $rewrite ? 'subject-' . $sid : 'subject.php?id=' . $sid;
            }

            break;
        case 'search' :
            break;
        default :
            return false;
            break;
    }

    if ($rewrite) {
        if ($rewrite == 2 && !empty ($append)) {
            $uri .= '-' . urlencode(preg_replace('/[\.|\/|\?|&|\+|\\\|\'|"|,]+/', '', $append));
        }

        $uri .= '.html';
    }

    return $uri;
}

/**
 * 取得影片列表：用于把影片添加到组合、关联类(目前用于文章关联影片和添加标签时搜索查询)
 * @param   object  $filters    过滤条件
 */
function get_show_article($filter)
{

    $where = get_where_sql($filter); // 取得过滤条件


    //取得数据
    $sql = 'SELECT show_id, title ' . 'FROM ' . TABLE_PREFIX . 'show' . ' AS m ' . $where;
    $sql = $GLOBALS ['db']->query_limit($sql, 50);
    $row = $GLOBALS ['db']->query_all_slave($sql);

    return $row;
}

/**
 * 取得服务器列表
 * @param  int  $type 类型，0和1为返回id => name，2返回id => name.online_url, 3返回array
 * @param     string    $select    限定服务器ID ，例 1,2,3以半角逗号分隔
 * @return array 服务器列表
 */
function get_server_list($type = null, $select = null)
{

    $res = array();

    if (empty ($GLOBALS ['skyuc']->servers) || !isset ($GLOBALS ['skyuc']->servers)) {
        if (!defined('IN_CONTROL_PANEL')) {
            require_once (DIR . '/includes/functions_admin.php');
        }
        $GLOBALS ['skyuc']->servers = build_servers();
    }

    if ($type == 3 && !empty ($select)) {
        $selArr = array();
        $selArr = explode(',', $select);
        foreach ($GLOBALS ['skyuc']->servers as $key => $val) {
            if (in_array($val ['id'], $selArr) === false) {
                unset ($GLOBALS ['skyuc']->servers [$key]);
            }
        }

    }

    $res = &    $GLOBALS ['skyuc']->servers;

    $server_list = array();

    //返回服务器 id=>name 和点播服务器地址
    if ($type == 2) {
        foreach ($res as $row) {
            $server_list [$row ['id']] = $row ['name'] . ' ' . $row ['online'];
        }
    } //返回服务器列表 id => name
    else {
        foreach ($res as $row) {
            $server_list [$row ['id']] = $row ['name'];
        }
    }
    return $server_list;
}

/**
 * 取得播放器列表返回名称
 * @return array 播放器列表 tag => name
 */
function get_player_list()
{

    if (empty ($GLOBALS ['skyuc']->players) || !isset ($GLOBALS ['skyuc']->players)) {
        if (!defined('IN_CONTROL_PANEL')) {
            require_once (DIR . '/includes/functions_admin.php');
        }
        $GLOBALS ['skyuc']->players = build_players();
    }

    $player_list = array();
    foreach ($GLOBALS ['skyuc']->players as $row) {
        $player_list [$row ['tag']] = $row ['title'];
    }

    return $player_list;
}

/**
 * 获得某个服务器下影片
 *
 * @access  public
 * @param   int     $cat
 * @return  array
 */
function get_servers($cat = 0, $app = 'server')
{

    $children = ($cat > 0) ? ' AND ' . get_children($cat) : '';

    $sql = "SELECT b.server_id, b.server_name, b.state, COUNT(m.show_id) AS show_num, IF(b.state > '', '1', '0') AS tag " . ' FROM ' . TABLE_PREFIX . 'server' . 'AS b, ' . TABLE_PREFIX . 'show' . ' AS m ' . ' WHERE m.server_id = b.server_id ' . $children . ' AND m.is_show = 1 ' . ' GROUP BY b.server_id HAVING show_num > 0 ORDER BY tag DESC, b.sort_order ASC';

    $row = $GLOBALS ['db']->query_all_slave($sql);

    foreach ($row as $key => $val) {
        $row [$key] ['url'] = build_uri($app, array('cid' => $cat, 'bid' => $val ['server_id']), $val ['server_name']);
    }

    return $row;
}

/**
 * 获得指定分类下的文章总数
 *
 * @param   integer     $cat_id
 *
 * @return  integer
 */
function get_article_count($cat_id)
{

    $count = $GLOBALS ['db']->query_first_slave('SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'article' . ' WHERE ' . get_article_children($cat_id) . ' AND is_open = 1');

    return $count ['total'];
}

/**
 * 获得指定分类下所有底层分类的ID
 *
 * @access  public
 * @param   integer     $cat        指定的分类ID
 * @return  string
 */
function get_children($cat = 0)
{
    return 'm.cat_id ' . db_create_in(array_unique(array_merge(array($cat), array_keys(get_cat_list($cat, 0, false)))));
}

/**
 * 获得指定文章分类下所有底层分类的ID
 *
 * @access  public
 * @param   integer     $cat        指定的分类ID
 *
 * @return void
 */
function get_article_children($cat = 0)
{
    return db_create_in(array_unique(array_merge(array($cat), array_keys(article_cat_list($cat, 0, false)))), 'cat_id');
}

/**
 * 获得指定分类下的子分类的数组
 *
 * @access  public
 * @param   int     $cat_id     分类的ID
 * @param   int     $selected   当前选中分类的ID
 * @param   boolean $re_type    返回的类型: 值为真时返回下拉列表,否则返回数组
 * @param   int     $level      限定返回的级数。为0时返回所有级数
 * @return  mix
 */
function article_cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0)
{

    static $res = NULL;

    if (empty ($GLOBALS ['skyuc']->article_cat) || !isset ($GLOBALS ['skyuc']->article_cat)) {
        if (!defined('IN_CONTROL_PANEL')) {
            require_once (DIR . '/includes/functions_admin.php');
        }
        $GLOBALS ['skyuc']->article_cat = build_article_cat();
    }

    $res = & $GLOBALS ['skyuc']->article_cat;

    if (empty ($res) == true) {
        return $re_type ? '' : array();
    }

    $options = article_cat_options($cat_id, $res); // 获得指定分类下的子分类的数组


    // 截取到指定的缩减级别
    if ($level > 0) {
        if ($cat_id == 0) {
            $end_level = $level;
        } else {
            $first_item = reset($options); // 获取第一个元素
            $end_level = $first_item ['level'] + $level;
        }

        /* 保留level小于end_level的部分 */
        foreach ($options as $key => $val) {
            if ($val ['level'] >= $end_level) {
                unset ($options [$key]);
            }
        }
    }

    $pre_key = 0;
    foreach ($options as $key => $value) {
        $options [$key] ['has_children'] = 1;
        if ($pre_key > 0) {
            if ($options [$pre_key] ['cat_id'] == $options [$key] ['parent_id']) {
                $options [$pre_key] ['has_children'] = 1;
            }
        }
        $pre_key = $key;
    }

    if ($re_type == true) {
        $select = '';
        foreach ($options as $var) {
            $select .= '<option value="' . $var ['cat_id'] . '" ';
            $select .= ' cat_type="' . $var ['cat_type'] . '" ';
            $select .= ($selected == $var ['cat_id']) ? "selected='ture'" : '';
            $select .= '>';
            if ($var ['level'] > 0) {
                $select .= str_repeat('&nbsp;', $var ['level'] * 4);
            }
            $select .= htmlspecialchars($var ['cat_name']) . '</option>';
        }

        return $select;
    } else {
        foreach ($options as $key => $value) {
            $options [$key] ['url'] = build_uri('article_cat', array('acid' => $value ['cat_id']), $value ['cat_name']);
        }
        return $options;
    }
}

/**
 * 过滤和排序所有文章分类，返回一个带有缩进级别的数组
 *
 * @access  private
 * @param   int     $cat_id     上级分类ID
 * @param   array   $arr        含有所有分类的数组
 * @param   int     $level      级别
 * @return  void
 */
function article_cat_options($spec_cat_id, $arr)
{
    static $cat_options = array();

    if (isset ($cat_options [$spec_cat_id])) {
        return $cat_options [$spec_cat_id];
    }

    if (!isset ($cat_options [0])) {
        $level = $last_cat_id = 0;
        $options = $cat_id_array = $level_array = array();
        while (!empty ($arr)) {
            foreach ($arr as $key => $value) {
                $cat_id = $value ['cat_id'];
                if ($level == 0 && $last_cat_id == 0) {
                    if ($value ['parent_id'] > 0) {
                        break;
                    }

                    $options [$cat_id] = $value;
                    $options [$cat_id] ['level'] = $level;
                    $options [$cat_id] ['id'] = $cat_id;
                    $options [$cat_id] ['name'] = $value ['cat_name'];
                    unset ($arr [$key]);

                    if ($value ['has_children'] == 0) {
                        continue;
                    }
                    $last_cat_id = $cat_id;
                    $cat_id_array = array($cat_id);
                    $level_array [$last_cat_id] = ++$level;
                    continue;
                }

                if ($value ['parent_id'] == $last_cat_id) {
                    $options [$cat_id] = $value;
                    $options [$cat_id] ['level'] = $level;
                    $options [$cat_id] ['id'] = $cat_id;
                    $options [$cat_id] ['name'] = $value ['cat_name'];
                    unset ($arr [$key]);

                    if ($value ['has_children'] > 0) {
                        if (end($cat_id_array) != $last_cat_id) {
                            $cat_id_array [] = $last_cat_id;
                        }
                        $last_cat_id = $cat_id;
                        $cat_id_array [] = $cat_id;
                        $level_array [$last_cat_id] = ++$level;
                    }
                } elseif ($value ['parent_id'] > $last_cat_id) {
                    break;
                }
            }

            $count = count($cat_id_array);
            if ($count > 1) {
                $last_cat_id = array_pop($cat_id_array);
            } elseif ($count == 1) {
                if ($last_cat_id != end($cat_id_array)) {
                    $last_cat_id = end($cat_id_array);
                } else {
                    $level = 0;
                    $last_cat_id = 0;
                    $cat_id_array = array();
                    continue;
                }
            }

            if ($last_cat_id && isset ($level_array [$last_cat_id])) {
                $level = $level_array [$last_cat_id];
            } else {
                $level = 0;
            }
        }
        $cat_options [0] = $options;
    } else {
        $options = $cat_options [0];
    }

    if (!$spec_cat_id) {
        return $options;
    } else {
        if (empty ($options [$spec_cat_id])) {
            return array();
        }

        $spec_cat_id_level = $options [$spec_cat_id] ['level'];

        foreach ($options as $key => $value) {
            if ($key != $spec_cat_id) {
                unset ($options [$key]);
            } else {
                break;
            }
        }

        $spec_cat_id_array = array();
        foreach ($options as $key => $value) {
            if (($spec_cat_id_level == $value ['level'] && $value ['cat_id'] != $spec_cat_id) || ($spec_cat_id_level > $value ['level'])) {
                break;
            } else {
                $spec_cat_id_array [$key] = $value;
            }
        }
        $cat_options [$spec_cat_id] = $spec_cat_id_array;

        return $spec_cat_id_array;
    }
}

/**
 * 获得指定分类的所有上级分类
 *
 * @access  public
 * @param   integer $cat    分类编号
 * @return  array
 */
function get_parent_cats($cat)
{
    if ($cat == 0) {
        return array();
    }

    $sql = 'SELECT cat_id, cat_name, parent_id FROM ' . TABLE_PREFIX . 'category';
    $key = md5($sql); //缓存名称：键
    //读缓存
    if ($data = get_file_cache($key)) {
        $arr = $data;
    } else {
        $arr = $GLOBALS ['db']->query_all_slave($sql);
        put_file_cache($key, $arr); //写缓存
    }

    if (empty ($arr)) {
        return array();
    }

    $index = 0;
    $cats = array();

    while (1) {
        foreach ($arr as $row) {
            if ($cat == $row ['cat_id']) {
                $cat = $row ['parent_id'];

                $cats [$index] ['cat_id'] = $row ['cat_id'];
                $cats [$index] ['cat_name'] = $row ['cat_name'];

                $index++;
                break;
            }
        }

        if ($index == 0 || $cat == 0) {
            break;
        }
    }

    return $cats;
}

/**
 * 取得影片过滤器列表
 * @return  array   推荐类型列表
 */
function get_intro_list()
{
    global $_LANG;
    return array('is_best' => $_LANG ['is_best'], 'is_hot' => $_LANG ['is_hot'], 'is_series' => $_LANG ['is_series'], 'is_done' => $_LANG ['is_done'], 'is_vip' => $_LANG ['is_vip'], 'is_free' => $_LANG ['is_free']);
}

/**
 * 获得影片列表
 *
 * @access  public
 * @params  integer $is_show=1影片列表，0回收站
 * @return  array
 */
function get_show_list($is_show)
{

    $GLOBALS ['skyuc']->input->clean_array_gpc('r', array('cat_id' => TYPE_UINT, 'server_id' => TYPE_UINT, 'player' => TYPE_STR, 'intro_type' => TYPE_STR, 'keyword' => TYPE_STR, 'sort_by' => TYPE_STR, 'sort_order' => TYPE_STR));

    //过滤条件
    $filter ['cat_id'] = $GLOBALS ['skyuc']->GPC ['cat_id'];
    $filter ['intro_type'] = $GLOBALS ['skyuc']->GPC ['intro_type'];
    $filter ['server_id'] = $GLOBALS ['skyuc']->GPC ['server_id'];
    $filter ['player'] = $GLOBALS ['skyuc']->GPC ['player'];
    $filter ['keyword'] = $GLOBALS ['skyuc']->GPC ['keyword'];
    $filter ['sort_by'] = iif(empty ($GLOBALS ['skyuc']->GPC ['sort_by']), 'show_id', $GLOBALS ['skyuc']->GPC ['sort_by']);
    $filter ['sort_order'] = iif(empty ($GLOBALS ['skyuc']->GPC ['sort_order']), 'DESC', $GLOBALS ['skyuc']->GPC ['sort_order']);
    $filter ['is_show'] = iif($is_show, '1', '0');

    $where = '';
    $categories = iif($filter ['cat_id'] > 0, iif(empty ($filter ['keyword']), ' AND ' . get_children($filter ['cat_id']), ' AND ' . get_contenttypeid($filter ['cat_id'])), '');
    // 推荐类型is_best=1为强力推荐,is_series=1为分类推荐
    switch ($filter ['intro_type']) {
        case 'is_vip' :
            $where .= ' AND points>=1';
            break;
        case 'is_free' :
            $where .= ' AND points=0';
            break;
        case 'is_best' :
            $where .= " AND attribute=1";
            break;
        case 'is_hot' :
            $where .= ' AND attribute=2';
            break;
        case 'is_series' :
            $where .= ' AND attribute=3';
            break;
        case 'is_done' :
            $where .= ' AND attribute=4';
            break;
    }

    // 服务器
    if ($filter ['server_id']) {
        $where .= " AND server_id LIKE '%" . $filter ['server_id'] . "%'";
    }
    // 播放器
    if ($filter ['player']) {
        $where .= " AND player LIKE '%" . $filter ['player'] . "%'";
    }
    // 关键字
    if (!empty ($filter ['keyword'])) {
        $displaykeywords = sanitize_search_query($filter ['keyword']);

        include_once(DIR . '/includes/class_search.php');
        $nt = new normalizeText(4, false);
        $searchstring = sanitize_search_query($nt->parseQuery($filter ['keyword']));

        // 记录总数
        $sql = "SELECT COUNT(*) AS total FROM " . TABLE_PREFIX . "searchcore_text AS searchcore_text
					WHERE MATCH (searchcore_text.title, searchcore_text.keywordtext)
					AGAINST ('" . $searchstring . "' IN BOOLEAN MODE) " . $categories;
    } else {
        // 记录总数
        $sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'show' . ' AS m  WHERE m.is_show=' . $is_show . '  ' . $where . '  ' . $categories;
    }

    $total = $GLOBALS ['db']->query_first_slave($sql);
    $filter ['record_count'] = $total ['total'];

    // 分页大小
    $filter = page_and_size($filter);
    if (!empty ($filter ['keyword'])) {
        $sql = "SELECT m.* , searchid , MATCH (searchcore_text.title, searchcore_text.keywordtext) AGAINST ('" . $searchstring . "' IN BOOLEAN MODE) AS score
				FROM " . TABLE_PREFIX . 'searchcore_text AS searchcore_text ' .
            'LEFT JOIN ' . TABLE_PREFIX . 'show' . ' AS m ' .
            'ON searchcore_text.searchid = m.show_id ' .
            "WHERE MATCH (searchcore_text.title, searchcore_text.keywordtext)
				AGAINST ('" . $searchstring . "' IN BOOLEAN MODE)" . $categories;
    } else {
        $sql = 'SELECT m.*  FROM ' . TABLE_PREFIX . 'show' . ' AS m  ' . ' WHERE m.is_show=' . $is_show . '  ' . $where . '  ' . $categories . ' ORDER BY ' . $filter ['sort_by'] . ' ' . $filter ['sort_order'];
    }

    $sql = $GLOBALS ['db']->query_limit($sql, $filter ['page_size'], $filter ['start']);
    $res = $GLOBALS ['db']->query_read_slave($sql);
    $arr = array();
    while ($row = $GLOBALS ['db']->fetch_array($res)) {
        $row ['add_time'] = skyuc_date($GLOBALS ['skyuc']->options ['date_format'] . ' ' . $GLOBALS ['skyuc']->options ['time_format'], $row ['add_time'], true);
        $arr [] = $row;
    }

    $filter ['keyword'] = stripslashes($filter ['keyword']);
    $arr = array('show' => $arr, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count']);

    return $arr;
}

/**
 * 显示影片 地址字符串
 *
 * @param  string    $data                地址字符串
 * @param  array    $player     播放器数组
 * @param  array  $server             服务器数组
 * @param     boolen $exurl            true分割地址为数组，false不分割
 *
 * @return array
 */

function display_url_data($data, $player = '', $server = '', $exurl = false)
{

    if (empty ($data)) {
        return '';
    }

    // 地址分组
    $data = explode('###', $data);
    array_pop($data);
    $urlArray = array();
    $arr = array();
    foreach ($data as $key => $value) {

        if (!$exurl) {
            $arr ['url'] = str_replace('$', "\r\n", $value);
        } else {
            $urlarr = explode('$', $value);
            array_pop($urlarr);

            $urls = array();
            $urldata = array();
            foreach ($urlarr as $k => $v) {
                $url = explode('@@', $v);
                $urls ['title'] = $url [0];
                $urls ['src'] = $url [1];
                $urls ['point'] = isset ($url[2]) ? $url[2] : 'ignore';
                $urldata [] = $urls;
            }
            $arr['url'] = $urldata;
        }
        if ($player && $server) {
            $arr['player'] = $player[$key];
            $arr['server'] = $server[$key];
            if (!empty ($GLOBALS ['skyuc']->players)) {
                $arr ['player_name'] = $GLOBALS ['skyuc']->players ["$player[$key]"] ['title'];
            }
        }
        $urlArray[] = $arr;
    }
    return $urlArray;
}

/**
 * 影片地址数组转换成字符串
 *
 * @param  array    $urlArray    地址数组
 * @return string
 */

function repair_url_data($urlArray)
{
    global $_LANG;

    if (!is_array($urlArray) || empty ($urlArray)) {
        return '';
    }
    $urlstring = '';
    foreach ($urlArray as $value) {
        //地址信息转换为数组
        $urldata = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", trim($value)));
        $urlarr = explode("\r\n", $urldata);
        foreach ($urlarr as $k => $v) {
            $v = str_replace("\\", '/', $v);

            $k++;
            if ($k <= 9)
                $k = '0' . $k;
            if (preg_match("#@@#i", $v)) {
                $url = explode('@@', $v);
                if (trim($url [0]) == '@@') {
                    $urlstring .= $_LANG ['show_txt_pre'] . $k . $_LANG ['show_txt_ext'] . $v . '$';
                } else {
                    $urlstring .= $v . "$";
                }
            } else {
                $urlstring .= $_LANG ['show_txt_pre'] . $k . $_LANG ['show_txt_ext'] . '@@' . $v . '$';
            }
        }
        $urlstring .= '###';

    }
    return $urlstring;
}

?>
