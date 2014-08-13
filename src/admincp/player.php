<?php

/**
 * SKYUC! 管理中心播放器管理
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

require (dirname(__FILE__) . '/global.php');

$exc = new exchange (TABLE_PREFIX . 'player', $skyuc->db, 'id', 'player');

/*------------------------------------------------------ */
//-- 播放器列表
/*------------------------------------------------------ */
if ($skyuc->GPC ['act'] == 'list') {
    $smarty->assign('ur_here', $_LANG['player_manage']);
    $smarty->assign('action_link', array('text' => $_LANG ['player_add'], 'href' => 'player.php?act=add'));
    $smarty->assign('full_page', 1);

    $player_list = get_playerlist();

    $smarty->assign('player_list', $player_list['player']);
    $smarty->assign('filter', $player_list['filter']);
    $smarty->assign('record_count', $player_list['record_count']);
    $smarty->assign('page_count', $player_list['page_count']);

    assign_query_info();
    $smarty->display('player_list.tpl');
}

/*------------------------------------------------------ */
//-- 添加播放器
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'add') {
    /* 权限判断 */
    admin_priv('player_manage');

    $smarty->assign('ur_here', $_LANG ['player_add']);
    $smarty->assign('action_link', array('text' => $_LANG ['player_manage'], 'href' => 'player.php?act=list'));
    $smarty->assign('form_action', 'insert');

    assign_query_info();

    $sql = "SELECT *  " . "FROM " . TABLE_PREFIX . 'player' . " WHERE id='$id'";
    $player = $db->query_first_slave($sql);
    $player['user_rank'] = explode(',', $player['user_rank']);
    $player['sort_order'] = 0;
    $player['is_show'] = 1;

    $smarty->assign('player', $player);
    $smarty->assign('ranks', $skyuc->usergroup);
    $smarty->display('player_info.tpl');
} elseif ($skyuc->GPC ['act'] == 'insert') {

    admin_priv('player_manage');

    $skyuc->input->clean_array_gpc('p', array('is_show' => TYPE_UINT,
            'sort_order' => TYPE_UINT,
            'player_tag' => TYPE_STR,
            'user_rank' => TYPE_ARRAY_UINT,
            'player_code' => TYPE_STR,
            'player_title' => TYPE_STR)
    );

    $is_show = $skyuc->GPC ['is_show'];
    //检查播放器标识是否重复
    $is_only = $exc->is_only('tag', $skyuc->GPC ['player_tag']);

    if (!$is_only) {
        sys_msg(sprintf($_LANG ['playertag_exist'], $skyuc->GPC ['player_tag']), 1);
    }
    $user_rank = !empty($skyuc->GPC['user_rank']) ? implode ( ',', $skyuc->GPC['user_rank']) : '';

    //插入数据
    $sql = 'INSERT INTO ' . TABLE_PREFIX . 'player' . '(title, tag, player_code,is_show, sort_order, user_rank) ' .
        " VALUES ('" . $db->escape_string($skyuc->GPC['player_title']) . "', '" .
        $db->escape_string($skyuc->GPC['player_tag']) . "', '" .
        $db->escape_string($skyuc->GPC['player_code']) . "', '$is_show', '" .
        $skyuc->GPC['sort_order'] . "', '" . $user_rank . "')";
    $db->query_write($sql);

    admin_log($skyuc->GPC['player_title'], 'add', 'player');

    // 重建缓存
    build_players();

    $link[0]['text'] = $_LANG['continue_add'];
    $link[0]['href'] = 'player.php?act=add';

    $link[1]['text'] = $_LANG['back_list'];
    $link[1]['href'] = 'player.php?act=list';

    sys_msg($_LANG ['playeradd_succed'], 0, $link);
}

/*------------------------------------------------------ */
//-- 编辑播放器
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit') {
    // 权限判断
    admin_priv('player_manage');

    $id = $skyuc->input->clean_gpc('g', 'id', TYPE_UINT);

    $sql = "SELECT *  " . "FROM " . TABLE_PREFIX . 'player' . " WHERE id='$id'";
    $player = $db->query_first_slave($sql);
    $player['user_rank'] = explode(',', $player['user_rank']);


    $smarty->assign('ur_here', $_LANG['player_edit']);
    $smarty->assign('action_link', array('text' => $_LANG['player_manage'], 'href' => 'player.php?act=list'));
    $smarty->assign('player', $player);
    $smarty->assign('form_action', 'update');
    $smarty->assign('ranks', $skyuc->usergroup);

    assign_query_info();
    $smarty->display('player_info.tpl');
}
elseif ($skyuc->GPC ['act'] == 'update') {
    admin_priv('player_manage');

    $skyuc->input->clean_array_gpc('p', array('id' => TYPE_UINT,
            'is_show' => TYPE_UINT,
            'sort_order' => TYPE_UINT,
            'user_rank' => TYPE_ARRAY_UINT,
            'player_tag' => TYPE_STR,
            'old_playertag' => TYPE_STR,
            'player_code' => TYPE_STR,
            'player_title' => TYPE_STR)
    );

    if ($skyuc->GPC['player_tag'] !== $skyuc->GPC['old_playertag']) {
        //检查播放器标识是否相同
        $is_only = $exc->is_only('tag', $skyuc->GPC['player_tag'], $skyuc->GPC['id']);

        if (!$is_only) {
            sys_msg(sprintf($_LANG ['playertag_exist'], $skyuc->GPC['player_tag']), 1);
        }
    }

    $is_show = $skyuc->GPC['is_show'];
    $user_rank = !empty($skyuc->GPC['user_rank']) ? implode ( ',', $skyuc->GPC['user_rank']) : '';

    //处理更新列
    $param = "title = '" . $db->escape_string($skyuc->GPC['player_title']) .
        "',tag='" . $db->escape_string($skyuc->GPC['player_tag']) .
        "',player_code='" . $db->escape_string($skyuc->GPC['player_code']) .
        "',is_show='" . $is_show . "', sort_order='" . $skyuc->GPC['sort_order'] .
        "', user_rank='" . $user_rank . "'";

    if ($exc->edit($param, $skyuc->GPC['id'])) {
        // 重建缓存
        build_players();

        admin_log($skyuc->GPC['player_title'], 'edit', 'player');

        $link [0] ['text'] = $_LANG['back_list'];
        $link [0] ['href'] = 'player.php?act=list';

        sys_msg(sprintf($_LANG['playeredit_succed'], $skyuc->GPC['player_title']), 0, $link);
    } else {
        die ($db->error());
    }
}

/*------------------------------------------------------ */
//-- 编辑播放器标识
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_player_tag') {
    check_authz_json('player_manage');

    $skyuc->input->clean_array_gpc('p', array('id' => TYPE_UINT, 'val' => TYPE_STR));

    $id = $skyuc->GPC ['id'];
    $name = $skyuc->GPC ['val'];

    //检查名称是否重复
    if ($exc->num('tag', $name, $id) != 0) {
        make_json_error(sprintf($_LANG ['playertag_exist'], $name));
    } else {
        if ($exc->edit("tag = '" . $db->escape_string($name) . "'", $id)) {
            admin_log($name, 'edit', 'player');
            make_json_result($name);
        } else {
            make_json_result(sprintf($_LANG ['playeredit_fail'], $name));
        }
    }
} /*------------------------------------------------------ */
//-- 编辑播放器标识
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_player_title') {
    check_authz_json('player_manage');

    $skyuc->input->clean_array_gpc('p', array('id' => TYPE_UINT, 'val' => TYPE_STR));

    $id = $skyuc->GPC ['id'];
    $name = $skyuc->GPC ['val'];

    // 检查名称是否重复
    if ($exc->num('title', $name, $id) != 0) {
        make_json_error(sprintf($_LANG ['playertag_exist'], $name));
    } else {
        if ($exc->edit("title = '" . $db->escape_string($name) . "'", $id)) {
            admin_log($name, 'edit', 'player');

            build_players();

            make_json_result($name);
        } else {
            make_json_result(sprintf($_LANG ['playeredit_fail'], $name));
        }
    }
} /*------------------------------------------------------ */
//-- 编辑排序序号
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'edit_sort_order') {
    check_authz_json('player_manage');

    $skyuc->input->clean_array_gpc('p', array('id' => TYPE_UINT, 'val' => TYPE_UINT));

    $id = $skyuc->GPC ['id'];
    $order = $skyuc->GPC ['val'];

    $name = $exc->get_name($id, 'title');
    // 由于此处 $skyuc->GPC['val'] 最无符号整数，因此无需转义字符
    if ($exc->edit("sort_order = '$order'", $id)) {
        admin_log($name, 'edit', 'player');

        make_json_result($order);
    } else {
        make_json_error(sprintf($_LANG ['playeredit_fail'], $name));
    }
}

/*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'toggle_show') {
    check_authz_json('player_manage');

    $skyuc->input->clean_array_gpc('p', array('id' => TYPE_UINT, 'val' => TYPE_UINT));

    $id = $skyuc->GPC ['id'];
    $val = $skyuc->GPC ['val'];

    // 由于此处 $skyuc->GPC['val'] 最无符号整数，因此无需转义字符
    $exc->edit("is_show='$val'", $id);

    build_players();

    make_json_result($val);
}

/*------------------------------------------------------ */
//-- 删除播放器
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'remove') {
    check_authz_json('player_manage');

    $id = $skyuc->input->clean_gpc('g', 'id', TYPE_UINT);
    //删除ID播放器
    $exc->drop($id);

    // 更新影片的播放器编号 为0
    $sql = 'UPDATE ' . TABLE_PREFIX . 'show' . " SET player=0 WHERE player='$id'";
    $db->query_write($sql);

    $url = 'player.php?act=query&' . str_replace('act=remove', '', $_SERVER ['QUERY_STRING']);

    build_players();

    header("Location: $url\n");
    exit ();
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($skyuc->GPC ['act'] == 'query') {
    $player_list = get_playerlist();

    $smarty->assign('player_list', $player_list ['player']);
    $smarty->assign('filter', $player_list ['filter']);
    $smarty->assign('record_count', $player_list ['record_count']);
    $smarty->assign('page_count', $player_list ['page_count']);

    make_json_result($smarty->fetch('player_list.tpl'), '', array('filter' => $player_list ['filter'], 'page_count' => $player_list ['page_count']));
}

?>
