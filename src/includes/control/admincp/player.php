<?php
// #######################################################################
// ######################## player.php 私有函数      #####################
// #######################################################################


/**
 * 获取播放器列表
 *
 * @access  public
 * @return  array
 */
function get_playerlist()
{

    // 分页大小
    $filter = array();

    //记录总数以及页数
    $sql = "SELECT COUNT(*) AS total FROM " . TABLE_PREFIX . 'player';
    $total = $GLOBALS ['db']->query_first($sql);
    $filter ['record_count'] = $total ['total'];

    $filter = page_and_size($filter);

    // 查询记录
    $sql = "SELECT * FROM " . TABLE_PREFIX . 'player' . ' ORDER BY sort_order ASC ';
    $sql = $GLOBALS ['db']->query_limit($sql, $filter ['page_size'], $filter ['start']);
    $res = $GLOBALS ['db']->query_read($sql);

    $arr = array();
    while ($rows = $GLOBALS ['db']->fetch_array($res)) {
/*        foreach($GLOBALS['skyuc']->usergroup as $v) {
            if($v['id'] == $rows['user_rank']) {
                $rows['user_rank'] = $v['name'];
                break;
            }
            else{
                $rows['user_rank'] = $GLOBALS['_LANG']['not_rank'];
            }
        }*/
        if($rows['user_rank']) {
            if(strpos($rows['user_rank'], ',') === false) {
                $rows['user_rank'] = $GLOBALS['skyuc']->usergroup[$rows['user_rank']]['name'];
            }
            else {
                $urank = explode(',', $rows['user_rank']);
                $rows['user_rank'] = $GLOBALS['skyuc']->usergroup["$urank[0]"]['name'].' [...]';
            }

        }
        else {
            $rows['user_rank'] = $GLOBALS['_LANG']['not_rank'];
        }
        $arr [] = $rows;
    }

    return array('player' => $arr, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count']);
}

?>