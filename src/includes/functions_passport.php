<?php
/**
 * SKYUC! 用户帐号相关函数库
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */
/**
 * 用户注册，登录函数
 *
 * @access  public
 * @param   string       $username          注册用户名
 * @param   string       $password          用户密码
 * @param   string       $email             注册email
 * @param   array        $other             注册的其他信息
 *
 * @return  bool         $bool
 */
function register ($username, $password, $email, $other = array())
{
    // 检查username
    if (empty($username)) {
        $GLOBALS['err']->add($GLOBALS['_LANG']['username_empty']);
    } else {
        if (preg_match(
        '/\'\/^\\s*$|^c:\\\\con\\\\con$|[%,\\*\\"\\s\\t\\<\\>\\&\'\\\\]/',
        $username)) {
            $GLOBALS['err']->add(
            sprintf($GLOBALS['_LANG']['username_invalid'],
            htmlspecialchars($username)));
        }
    }
    // 检查email
    if (empty($email)) {
        $GLOBALS['err']->add($GLOBALS['_LANG']['email_empty']);
    } else {
        if (! validate($email, 4)) {
            $GLOBALS['err']->add(
            sprintf($GLOBALS['_LANG']['email_invalid'],
            htmlspecialchars($email)));
        }
    }
    if ($GLOBALS['err']->error_no > 0) {
        return false;
    }
    // 检查是否和管理员重名
    if (admin_registered($username)) {
        $GLOBALS['err']->add(
        sprintf($GLOBALS['_LANG']['username_exist'], $username));
        return false;
    }
    if (! $GLOBALS['user']->add_user($username, $password, $email)) {
        if ($GLOBALS['user']->error == ERR_USERNAME_EXISTS) {
            $GLOBALS['err']->add(
            sprintf($GLOBALS['_LANG']['username_exist'], $username));
        } elseif ($GLOBALS['user']->error == ERR_EMAIL_EXISTS) {
            $GLOBALS['err']->add(
            sprintf($GLOBALS['_LANG']['email_exist'], $email));
        } else {
            $GLOBALS['err']->add('UNKNOWN ERROR!');
        }
        //注册失败
        return false;
    } else {
        //注册成功
        // 设置成登录状态
        $GLOBALS['user']->set_session($username);
        $GLOBALS['user']->set_cookie($username);
        $update_data = array();
        // 注册送积分
        if (intval($GLOBALS['skyuc']->options['user_rank']) > 0) {
            //取得会员等级类型
            /*     $sql = 'SELECT rank_type FROM '. TABLE_PREFIX. 'user_rank'." WHERE rank_id ='".$GLOBALS['skyuc']->options['user_rank']."'";
	        $user_type = $GLOBALS['db']->query_first_slave($sql);*/
            $rank_id = $GLOBALS['skyuc']->options['user_rank'];
            $user_type = $GLOBALS['skyuc']->usergroup["$rank_id"]['type'];
            if ($user_type == 1) {
                $update_data['unit_date'] = TIMENOW +
                 86400 * intval($GLOBALS['skyuc']->options['register_point']); //计时会员赠送天数
                $update_data['usertype'] = 1;
            } else {
                $update_data['user_point'] = $GLOBALS['skyuc']->options['register_point']; //计点会员赠送点数
                $update_data['usertype'] = 0;
            }
            $update_data['user_rank'] = $rank_id;
        }
        $update_data['reg_time'] = TIMENOW;
        $update_data['lastvisit'] = TIMENOW;
        $update_data['lastactivity'] = TIMENOW;
        $update_data['birthday'] = TIMENOW;
        //定义other合法的变量数组
        $other_key_array = array('msn', 'qq', 'phone', 'firstname',
        'referrer');
        if ($other) {
            foreach ($other as $key => $val) {
                //删除非法key值
                if (! in_array($key, $other_key_array)) {
                    unset($other[$key]);
                } else {
                    if ($key == 'firstname') {
                        $other[$key] = trim($val);
                    } else {
                        $other[$key] = htmlentities($val); //防止用户输入javascript代码
                    }
                }
            }
            $update_data = array_merge($update_data, $other);
        }
        $sql = fetch_query_sql($update_data, 'users',
        'WHERE user_id = ' . $GLOBALS['skyuc']->session->vars['userid']);
        $GLOBALS['db']->query_write($sql);
        // 更新用户信息
        update_user_info();
        return true;
    }
}
/**
 *
 *
 * @access  public
 * @param
 *
 * @return void
 */
function logout ()
{
    /* todo */
}
/**
 * 用户进行密码找回操作时，发送一封确认邮件
 *
 * @access  public
 * @param   string  $uid          用户ID
 * @param   string  $user_name    用户帐号
 * @param   string  $email        用户Email
 * @param   string  $code         key
 *
 * @return  boolen  $result;
 */
function send_pwd_email ($uid, $user_name, $email, $code)
{
    if (empty($uid) || empty($user_name) || empty($email) || empty($code)) {
        header("Location: user.php?act=get_password\n");
        exit();
    }
    // 设置重置邮件模板所需要的内容信息
    $template = get_mail_template('send_password');
    $reset_email = get_url() . 'user.php?act=get_password&uid=' . $uid . '&code=' .
     $code;
    $GLOBALS['smarty']->assign('user_name', $user_name);
    $GLOBALS['smarty']->assign('reset_email', $reset_email);
    $GLOBALS['smarty']->assign('site_name',
    $GLOBALS['skyuc']->options['site_name']);
    $GLOBALS['smarty']->assign('send_date',
    skyuc_date($GLOBALS['skyuc']->options['date_format'], TIMENOW, true, false));
    $GLOBALS['smarty']->assign('sent_date',
    skyuc_date($GLOBALS['skyuc']->options['date_format'], TIMENOW, true, false));
    $content = $GLOBALS['smarty']->fetch(
    'str:' . $template['template_content']);
    // 发送确认重置密码的确认邮件
    if (false !==
     skyuc_mail($email, $template['template_subject'], $content, true,
    $template['is_html'])) {
        return true;
    } else {
        return false;
    }
}
/**
 * 发送激活验证邮件
 *
 * @access  public
 * @param   int     $user_id        用户ID
 *
 * @return boolen
 */
function send_regiter_hash ($user_id)
{
    // 设置验证邮件模板所需要的内容信息
    $template = get_mail_template('register_validate');
    $hash = register_hash('encode', $user_id);
    $validate_email = get_url() . 'user.php?act=validate_email&hash=' . $hash;
    $sql = 'SELECT user_name, email FROM ' . TABLE_PREFIX . 'users' .
     ' WHERE user_id = ' . $user_id;
    $row = $GLOBALS['db']->query_first_slave($sql);
    $GLOBALS['smarty']->assign('user_name', $row['user_name']);
    $GLOBALS['smarty']->assign('validate_email', $validate_email);
    $GLOBALS['smarty']->assign('site_name',
    $GLOBALS['skyuc']->options['site_name']);
    $GLOBALS['smarty']->assign('send_date',
    skyuc_date($GLOBALS['skyuc']->options['date_format'], TIMENOW, false, false));
    $GLOBALS['smarty']->assign('sent_date',
    skyuc_date($GLOBALS['skyuc']->options['date_format'], TIMENOW, false, false));
    $content = $GLOBALS['smarty']->fetch(
    'str:' . $template['template_content']);
    // 发送激活验证邮件
    if (false !==
     skyuc_mail($row['email'], $template['template_subject'], $content, true,
    $template['is_html'])) {
        return true;
    } else {
        return false;
    }
}
/**
 * 生成邮件验证hash
 *
 * @access  public
 * @param
 *
 * @return void
 */
function register_hash ($operation, $key)
{
    if ($operation == 'encode') {
        $user_id = intval($key);
        $sql = 'SELECT reg_time  FROM ' . TABLE_PREFIX . 'users' .
         ' WHERE user_id = ' . $user_id;
        $users = $GLOBALS['db']->query_first_slave($sql);
        $reg_time = $users['reg_time'];
        $hash = substr(md5($user_id . COOKIE_SALT . $reg_time), 16, 4);
        return base64_encode($user_id . ',' . $hash);
    } else {
        $hash = base64_decode(trim($key));
        $row = explode(',', $hash);
        if (count($row) != 2) {
            return 0;
        }
        $user_id = intval($row[0]);
        $salt = trim($row[1]);
        if ($user_id <= 0 || strlen($salt) != 4) {
            return 0;
        }
        $sql = 'SELECT reg_time  FROM ' . TABLE_PREFIX . 'users' .
         ' WHERE user_id = ' . $user_id;
        $users = $GLOBALS['db']->query_first_slave($sql);
        $reg_time = $users['reg_time'];
        $pre_salt = substr(md5($user_id . COOKIE_SALT . $reg_time), 16, 4);
        if ($pre_salt == $salt) {
            return $user_id;
        } else {
            return 0;
        }
    }
}
/**
 * 判断超级管理员用户名是否存在
 * @param   string      $adminname 超级管理员用户名
 * @return  boolean
 */
function admin_registered ($adminname)
{
    $total = $GLOBALS['db']->query_first_slave(
    'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'admin' .
     " WHERE user_name = '" . $GLOBALS['db']->escape_string($adminname) . "'");
    return $total['total'];
}
/**
 * 充值卡登录
 * @param   string      $username 充值卡号码
 * @param   string      $password 充值卡密码
 * @return  boolean
 */
/*function card_login($username, $password) {
	$sql = "SELECT * FROM " . TABLE_PREFIX . 'card' . " WHERE cardid='" . $GLOBALS['db']->escape_string ( $username ) . "' and cardpass='" . $GLOBALS['db']->escape_string ( $password ) . "' and endtime >= " . TIMENOW;
	$row = $GLOBALS['db']->query_first ( $sql );
	if (! empty ( $row )) {
		register ( $row ['cardid'], $row ['cardpass'], $row ['cardid'] . '@123.com' );

		$unit_date = TIMENOW + 86400 * $row ['cardvalue'];
		$user_point = $row ['cardvalue'];
		$rank_id = $row ['rank_id'];
		$user_type = $GLOBALS['skyuc']->usergroup ["$rank_id"] ['type'];
		if ($user_type == 1) {
			$sql = 'UPDATE ' . TABLE_PREFIX . 'users' . " SET usertype=1, user_rank='" . $rank_id . "',unit_date='" . $unit_date . "' WHERE user_name='" . $GLOBALS['db']->escape_string ( $row ['cardid'] ) . "'";
		} elseif ($user_type == 0) {
			$sql = 'UPDATE ' . TABLE_PREFIX . 'users' . " SET usertype=0, user_rank='" . $rank_id . "',user_point='" . $user_point . "' WHERE user_name='" . $GLOBALS['db']->escape_string ( $row ['cardid'] ) . "'";
		}
		$GLOBALS['db']->query_write ( $sql );
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'card_log' . ' (cardid, cardpass, rank_id, cardvalue, money, addtime, username, userip) ' . " VALUES('" . $GLOBALS['db']->escape_string ( $row ['cardid'] ) . "', '" . $GLOBALS['db']->escape_string ( $row ['cardpass'] ) . "', '" . $rank_id . "', '" . $row ['cardvalue'] . "', '" . $row ['money'] . "', '" . TIMENOW . "', '" . $GLOBALS['db']->escape_string ( $row ['cardid'] ) . "', '" . ALT_IP . "')";
		$GLOBALS['db']->query_write ( $sql );
		$sql = 'DELETE FROM ' . TABLE_PREFIX . 'card' . " WHERE id='" . $row ['id'] . "'";
		$GLOBALS['db']->query_write ( $sql );
	}
}*/
?>