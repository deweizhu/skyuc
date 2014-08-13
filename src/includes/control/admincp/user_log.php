<?php
// #######################################################################
// ######################## user_log.php 私有函数      ###################
// #######################################################################


/**
 * 获取点播日志列表
 *
 * @access  public
 * @param
 *
 * @return void
 */
function get_log_list()
{

    $GLOBALS['skyuc']->input->clean_array_gpc('r',
        array(
            'keywords' => TYPE_STR,
            'sort_by' => TYPE_STR,
            'sort_order' => TYPE_STR
        )
    );
    //过滤条件
    $filter['keywords'] = $GLOBALS ['skyuc']->GPC ['keywords'];
    $filter['sort_by'] = iif(empty ($GLOBALS ['skyuc']->GPC ['sort_by']),
        'id', $GLOBALS ['skyuc']->GPC ['sort_by']);
    $filter['sort_order'] = iif(empty ($GLOBALS ['skyuc']->GPC ['sort_order']),
        'DESC', $GLOBALS ['skyuc']->GPC ['sort_order']);

    $select = ', u.user_name AS user_name ';


    if ($filter['keywords']) {
        $where = ' LEFT JOIN ' . TABLE_PREFIX . 'users AS u' .
            ' ON u.user_id = p.user_id' .
            " WHERE u.user_name  LIKE '%" .
            $GLOBALS ['db']->escape_string_like($filter ['keywords']) .
            "%' ";
    }
    else {
        $where = ' LEFT JOIN ' . TABLE_PREFIX . 'users AS u' .
            ' ON u.user_id = p.user_id ';
    }

    $sql = 'SELECT COUNT(*) AS total' . $select . '  FROM ' . TABLE_PREFIX .
        'play_log AS p' . $where;

    $total = $GLOBALS ['db']->query_first($sql);
    $filter['record_count'] = $total ['total'];

    // 分页大小
    $filter = page_and_size($filter);

    $sql = 'SELECT p.* ' . $select . ' FROM ' . TABLE_PREFIX . 'play_log AS p' .
        $where . ' ORDER by ' . $filter ['sort_by'] . ' ' .
        $filter ['sort_order'];
    $sql = $GLOBALS ['db']->query_limit($sql, $filter['page_size'],
        $filter['start']);
    $res = $GLOBALS ['db']->query_read($sql);
    if ($res !== false) {
        $log_list = array();
        while ($row = $GLOBALS ['db']->fetch_array($res)) {
            $row ['user_name'] = iif(!empty ($row ['user_name']),
                $row ['user_name'], $GLOBALS ['_LANG'] ['anonymous']);
            $row ['looktime'] = skyuc_date(
                $GLOBALS ['skyuc']->options ['date_format'] . ' ' .
                    $GLOBALS ['skyuc']->options ['time_format'], $row ['time'],
                true);
            $row ['looktype'] = iif(!empty ($row ['player']),
                $GLOBALS ['skyuc']->players ["$row[player]"]['title'], 'N/A');
            $row ['lookid'] = iif(!empty ($row ['url_id']),
                sprintf($GLOBALS ['_LANG']['seelog_lookid'], $row ['url_id']),
                0);
            $log_list [] = $row;
        }
    }

    $arr = array('log_list' => $log_list,
        'filter' => $filter,
        'page_count' => $filter ['page_count'],
        'record_count' => $filter ['record_count']
    );

    return $arr;
}

?>