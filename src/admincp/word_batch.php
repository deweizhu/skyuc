<?php
/**
 * SKYUC! 图片批量处理程序
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */
require (dirname(__FILE__) . '/global.php');
// 权限检查
admin_priv('word_batch');
if (empty($_GET['is_ajax'])) {
    assign_query_info();
    $date = array('1', '7', '30', '90', '180', '365');
    $date_list = '';
    foreach ($date as $value) {
        $date_list .= '<option value="' . $value . '" >' .
         sprintf($_LANG['last_date'], $value) . '</option>';
    }
    $smarty->assign('ur_here', $_LANG['08_batch_word']);
    $smarty->assign('cat_list', get_cat_list(0, 0));
    $smarty->assign('date_list', $date_list);
    $smarty->display('word_batch.tpl');
} elseif (! empty($_GET['get_show'])) {
    $skyuc->input->clean_array_gpc('g',
    array('lastday' => TYPE_UINT, 'cat_id' => TYPE_UINT));

    include_once (DIR . '/includes/class_json.php');
    $json = new JSON();
    $lastday = $skyuc->GPC['lastday'];
    $cat_id = $skyuc->GPC['cat_id'];
    $show_where = '';
    if (! empty($cat_id)) {
        $show_where .= ' AND ' . get_children($cat_id);
    }
    if ($lastday > 0) {
        $show_where .= ' AND add_time > ' . (TIMENOW - $lastday * 86400);
    }
    $sql = 'SELECT show_id, title FROM ' . TABLE_PREFIX . 'show' .
     ' AS m  WHERE 1 ' . $show_where.' ORDER by show_id DESC ';
    $sql = $skyuc->db->query_limit($sql, 50);
    die($json->encode($db->query_all($sql)));
} else {
    include_once (DIR . '/includes/class_json.php');
    $json = new JSON();
    $skyuc->input->clean_array_gpc('g',
    array('show_id' => TYPE_STR, 'lastday' => TYPE_UINT,
    'cat_id' => TYPE_UINT, 'keyword' => TYPE_BOOL, 'description' => TYPE_BOOL,
    'silent' => TYPE_BOOL, 'page_size' => TYPE_UINT, 'page' => TYPE_UINT,
    'total' => TYPE_UINT));

    $show_id = $skyuc->GPC['show_id'];
    $lastday = $skyuc->GPC['lastday'];
    $cat_id = $skyuc->GPC['cat_id'];
    $show_where = '';
    if (empty($show_id)) {
        if (! empty($cat_id)) {
            $show_where .= ' AND ' . get_children($cat_id);
        }
        if ($lastday > 0) {
            $show_where .= ' AND add_time > ' . (TIMENOW - $lastday * 86400);
        }
    } else {
        $show_where .= ' AND show_id ' . db_create_in($show_id);
    }
    //设置最长执行时间
    @set_time_limit(600);
    if (isset($_GET['start'])) {
        $page_size = 50; // 默认50个/页
        $keyword = $skyuc->GPC['keyword'];
        $description = $skyuc->GPC['description'];
        $change = $skyuc->GPC['change'];
        $silent = $skyuc->GPC['silent'];
        $title = '';
        if (isset($_GET['total_icon'])) {
            $count = $db->query_first(
            'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'show' .
             "  AS m  WHERE 1 " . $show_where);
            $title = sprintf($_LANG['show_format'], $count['total'],
            $page_size);
        }
        $result = array('error' => 0, 'message' => '', 'content' => '',
        'done' => 1, 'title' => $title, 'page_size' => $page_size, 'page' => 1,
        'keyword' => $keyword, 'description' => $description, 'total' => 1,
        'silent' => $silent, 'show_id' => $show_id, 'lastday' => $lastday,
        'cat_id' => $cat_id,
        'row' => array('new_page' => sprintf($_LANG['page_format'], 1),
        'new_total' => sprintf($_LANG['total_format'],
        ceil($count['total'] / $page_size)), 'new_time' => $_LANG['wait'],
        'cur_id' => 'time_1'));
        die($json->encode($result));
    } else {
        $result = array('error' => 0, 'message' => '', 'content' => '',
        'done' => 2, 'show_id' => $show_id, 'lastday' => $lastday,
        'cat_id' => $cat_id);
        $result['keyword'] = $skyuc->GPC['keyword'];
        $result['description'] = $skyuc->GPC['description'];
        $result['page_size'] = iif($skyuc->GPC['page_size'] == 0, 100,
        $skyuc->GPC['page_size']);
        $result['page'] = iif($skyuc->GPC_exists['page'], $skyuc->GPC['page'],
        1);
        $result['total'] = iif($skyuc->GPC_exists['total'],
        $skyuc->GPC['total'], 1);
        $result['silent'] = $skyuc->GPC['silent'];
        if ($result['silent']) {
            $err_msg = array();
        }
        /*------------------------------------------------------ */
        //-- 影片
        /*------------------------------------------------------ */
        $count = $db->query_first(
        'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'show' .
         "  AS m WHERE 1 " . $show_where);
        // 页数在许可范围内
        if ($result['page'] <=
         ceil($count['total'] / $result['page_size'])) {
            $start_time = time(); //开始执行时间
            //开始处理
            process_word($result['page'],
            $result['page_size'], $result['keyword'], $result['description'],
            $result['silent']);
            $end_time = time(); //结束执行时间
            $result['row']['pre_id'] = 'time_' . $result['total'];
            $result['row']['pre_time'] = iif(($end_time > $start_time),
            $end_time - $start_time, 1);
            $result['row']['pre_time'] = sprintf($_LANG['time_format'],
            $result['row']['pre_time']);
            $result['row']['cur_id'] = 'time_' . ($result['total'] + 1);
            $result['page'] ++; // 新行
            $result['row']['new_page'] = sprintf(
            $_LANG['page_format'], $result['page']);
            $result['row']['new_total'] = sprintf($_LANG['total_format'],
            ceil($count['total'] / $result['page_size']));
            $result['row']['new_time'] = $_LANG['wait'];
            $result['total'] ++;
        } else {
            -- $result['total'];
            -- $result['page'];
            $result['done'] = 0;
            $result['message'] = $_LANG['done'];
            // 清除缓存
            $skyuc->secache->setModified('show.dwt');
            die($json->encode($result));
        }
        if ($result['silent'] && $err_msg) {
            $result['content'] = implode('<br />', $err_msg);
        }
        die($json->encode($result));
    }
}
?>