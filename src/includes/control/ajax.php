<?php
/**
 * SKYUC! 前台 AJAX 私有函数库
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
 * 检查是否已经提交过投票
 *
 * @access  private
 * @param   integer     $vote_id
 * @return  boolean
 */
function vote_already_submited ($vote_id)
{
    $sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'vote_log' .
     " WHERE ip_address = '" . ALT_IP . "' AND vote_id = '" . $vote_id . "' ";
    $total = $GLOBALS['db']->query_first($sql);
    return ($total['total'] > 0);
}
/**
 * 保存投票结果信息
 *
 * @access  public
 * @param   integer     $vote_id
 * @param   string      $option_id
 * @return  void
 */
function save_vote ($vote_id, $option_id)
{
    $sql = 'INSERT INTO ' . TABLE_PREFIX . 'vote_log' .
     ' (vote_id, ip_address, vote_time) ' . " VALUES ('" . $vote_id . "', '" .
     ALT_IP . "', " . TIMENOW . ")";
    $GLOBALS['db']->query_write($sql);
    // 更新投票主题的数量
    $sql = 'UPDATE ' . TABLE_PREFIX . 'vote' . ' SET ' .
     ' vote_count = vote_count + 1 ' . ' WHERE vote_id = ' . $vote_id;
    $GLOBALS['db']->query_write($sql);
    // 更新投票选项的数量
    $sql = 'UPDATE ' . TABLE_PREFIX . 'vote_option' . ' SET ' .
     ' option_count = option_count + 1 ' . ' WHERE ' .
     db_create_in($option_id, 'option_id');
    $GLOBALS['db']->query_write($sql);
}
/**
 * 调用调查内容
 *
 * @access  public
 * @param   integer $id   调查的编号
 * @return  array
 */
function get_vote ($id = '')
{
    // 随机取得一个调查的主题
    if (empty($id)) {
        $sql = 'SELECT vote_id, vote_name, can_multi, vote_count, RAND() AS rnd' .
         ' FROM ' . TABLE_PREFIX . 'vote' . ' WHERE begin_date <= ' . TIMENOW .
         ' AND end_date >= ' . TIMENOW . ' ORDER BY rnd';
    } else {
        $sql = 'SELECT vote_id, vote_name, can_multi, vote_count' . ' FROM ' .
         TABLE_PREFIX . 'vote' . ' WHERE vote_id = ' . $id;
    }
    $vote_arr = $GLOBALS['db']->query_first($sql);
    if ($vote_arr !== false && ! empty($vote_arr)) {
        // 通过调查的ID,查询调查选项
        $sql_option = 'SELECT v.*, o.option_id, o.vote_id, o.option_name, o.option_count ' .
         'FROM ' . TABLE_PREFIX . 'vote' . ' AS v, ' . TABLE_PREFIX .
         'vote_option' . ' AS o ' .
         ' WHERE o.vote_id = v.vote_id AND o.vote_id = ' . $vote_arr['vote_id'];
        $res = $GLOBALS['db']->query_all($sql_option);
        // 总票数
        $sql = 'SELECT SUM(option_count) AS all_option FROM ' .
         TABLE_PREFIX . 'vote_option' . ' WHERE vote_id = ' .
         $vote_arr['vote_id'] . ' GROUP BY vote_id';
        $option = $GLOBALS['db']->query_first($sql);
        $option_num = $option['all_option'];
        $arr = array();
        $count = 100;
        foreach ($res as $idx => $row) {
            if ($option_num > 0 && $idx == count($res) - 1) {
                $percent = $count;
            } else {
                $percent = ($row['vote_count'] > 0 && $option_num > 0) ? round(
                ($row['option_count'] / $option_num) * 100) : 0;
                $count -= $percent;
            }
            $arr[$row['vote_id']]['options'][$row['option_id']]['percent'] = $percent;
            $arr[$row['vote_id']]['vote_id'] = $row['vote_id'];
            $arr[$row['vote_id']]['vote_name'] = $row['vote_name'];
            $arr[$row['vote_id']]['can_multi'] = $row['can_multi'];
            $arr[$row['vote_id']]['vote_count'] = $row['vote_count'];
            $arr[$row['vote_id']]['options'][$row['option_id']]['option_id'] = $row['option_id'];
            $arr[$row['vote_id']]['options'][$row['option_id']]['option_name'] = $row['option_name'];
            $arr[$row['vote_id']]['options'][$row['option_id']]['option_count'] = $row['option_count'];
        }
        $vote_arr['vote_id'] = (! empty($vote_arr['vote_id'])) ? $vote_arr['vote_id'] : '';
        $vote = array('id' => $vote_arr['vote_id'], 'content' => $arr);
        return $vote;
    }
}
/**
 * 添加评论内容
 *
 * @access  public
 * @param   object  $cmt
 * @return  void
 */
function add_comment ($cmt)
{
    // 评论是否需要审核
    $status = 1 - $GLOBALS['skyuc']->options['comment_check'];
    $user_id = $GLOBALS['skyuc']->session->vars['userid'];
    $email = iif(empty($cmt->email) , $GLOBALS['skyuc']->userinfo['email'] ,
         trim($cmt->email));
    $user_name = iif(empty($cmt->username) ,
        $GLOBALS['skyuc']->userinfo['user_name'] , trim($cmt->username));
    $email = htmlspecialchars($email);
    $user_name = htmlspecialchars($user_name);
    $reid = intval($cmt->reid);
    // 保存评论内容
    $sql = 'INSERT INTO ' . TABLE_PREFIX . 'comment' .
     '(comment_type, id_value, email, user_name, content, add_time, ip_address, status, parent_id, user_id) ' .
     " VALUES (" . "'" . $cmt->type . "', '" . $cmt->id . "', '" .
     $GLOBALS['db']->escape_string($email) . "', '" .
     $GLOBALS['db']->escape_string($user_name) . "', " . "'" .
     $GLOBALS['db']->escape_string(trim($cmt->content)) . "', " . TIMENOW .
     ", '" . ALT_IP . "', '" . $status . "', '".$reid."', '" . $user_id . "')";
    return $GLOBALS['db']->query_write($sql);
}
?>