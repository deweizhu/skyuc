<?php
/**
 * SKYUC PHP UCenter API
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
*/

define('UC_CLIENT_VERSION', '1.6.0'); //note Ucenter 版本标识
define('UC_CLIENT_RELEASE', '20110501');

define('API_DELETEUSER', 1);    //note 用户删除 API 接口开关
define('API_RENAMEUSER', 1);    //note 用户改名 API 接口开关
define('API_GETTAG', 1);        //note 获取标签 API 接口开关
define('API_SYNLOGIN', 1);      //note 同步登录 API 接口开关
define('API_SYNLOGOUT', 1);     //note 同步登出 API 接口开关
define('API_UPDATEPW', 1);      //note 更改用户密码 开关
define('API_UPDATEBADWORDS', 1);//note 更新关键字列表 开关
define('API_UPDATEHOSTS', 1);   //note 更新域名解析缓存 开关
define('API_UPDATEAPPS', 1);    //note 更新应用列表 开关
define('API_UPDATECLIENT', 1);  //note 更新客户端缓存 开关
define('API_UPDATECREDIT', 1);  //note 更新用户积分 开关
define('API_GETCREDITSETTINGS', 1);  //note 向 UCenter 提供积分设置 开关
define('API_GETCREDIT', 1);     //note 获取用户的某项积分 开关
define('API_UPDATECREDITSETTINGS', 1);  //note 更新应用积分设置 开关
define('API_ADDFEED', 1);
define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '1');


// ####################### 设置 PHP 环境 ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### 定义重要常量	 #######################
define('THIS_SCRIPT', 'uc');
define('SKYUC_AREA', 'IN_SKYUC');
define('CSRF_PROTECTION', true);
define('CSRF_SKIP_LIST', '');
define('LOCATION_BYPASS', 1);
define('SKIP_SMARTY', 1);
define('NOSHUTDOWNFUNC', true);
define('INGORE_VISIT_STATS', true);
define('SKIP_USERINFO', 1);


define('CWD', (($getcwd = getcwd()) ? substr($getcwd, 0, -3) : '..'));

require_once(CWD . '/includes/init.php');

// #############################################################################
// 获取 日期/时间 信息
fetch_time_data();

// 初始化会员数据整合类
$user = & init_users();

//数据验证
if(!defined('IN_UC'))
{
    @set_magic_quotes_runtime(0);
    defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

    $_DCACHE = $get = $post = array();

    $code = @$_GET['code'];
    parse_str(_authcode($code, 'DECODE', UC_KEY), $get);
    if(MAGIC_QUOTES_GPC)
    {
        $get = _stripslashes($get);
    }
    if(TIMENOW - $get['time'] > 3600)
    {
        exit('Authracation has expiried');
    }
    if(empty($get))
    {
        exit('Invalid Request');
    }
}
$action = $get['action'];
include(DIR . '/uc_client/lib/xml.class.php');
$post = xml_unserialize(file_get_contents('php://input'));

if(in_array($get['action'], array('test', 'deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcreditsettings', 'updatecreditsettings')))
{
    $uc_note = new uc_note();
    exit($uc_note->$get['action']($get, $post));
}
else
{
    exit(API_RETURN_FAILED);
}

$skyuc_url = str_replace('/api', '', get_url());

class uc_note
{
    public $db = '';
    public $tablepre = '';
    public $appdir = '';

    function _serialize($arr, $htmlon = 0)
    {
        if(!function_exists('xml_serialize'))
        {
            include(DIR . '/uc_client/lib/xml.class.php');
        }
        return xml_serialize($arr, $htmlon);
    }

    function uc_note()
    {
        $this->appdir = DIR;
        $this->db = $GLOBALS['db'];
    }

    function test($get, $post)
    {
        return API_RETURN_SUCCEED;
    }

    function deleteuser($get, $post)
    {
        $uids = $get['ids'];
        if(!API_DELETEUSER)
        {
            return API_RETURN_FORBIDDEN;
        }

        if (delete_user($uids))
        {
            return API_RETURN_SUCCEED;
        }
    }

    function renameuser($get, $post)
    {
        if (UC_CHARSET != 'UTF8')
        {
            $get['oldusername'] = skyuc_iconv(UC_CHARSET,'UTF8', $get['oldusername']);
            $get['newusername'] = skyuc_iconv(UC_CHARSET,'UTF8', $get['newusername']);
        }

        $uid = $get['uid'];
        $usernameold = $this->db->escape_string($get['oldusername']);
        $usernamenew = $this->db->escape_string($get['newusername']);
        if(!API_RENAMEUSER)
        {
            return API_RETURN_FORBIDDEN;
        }
        $this->db->query('UPDATE ' . TABLE_PREFIX. 'users' . " SET user_name='".$usernamenew ."' WHERE user_id='". $uid ."'");
        $this->db->query('UPDATE ' . TABLE_PREFIX. 'comment' . " SET user_name='". $usernamenew ."' WHERE user_name='". $usernameold."'");
        $this->db->query('UPDATE ' . TABLE_PREFIX. 'feedback' . " SET user_name='". $usernamenew ."' WHERE user_name='". $usernameold ."'");

        return API_RETURN_SUCCEED;
    }

    function gettag($get, $post)
    {
        $name = $get['id'];
        if(!API_GETTAG)
        {
            return API_RETURN_FORBIDDEN;
        }
        $tags = fetch_tag($name);
        $return = array($name, $tags);
        include_once(DIR . '/uc_client/client.php');
        return uc_serialize($return, 1);
    }

    function synlogin($get, $post)
    {
        $uid = intval($get['uid']);
        $username = $get['username'];
        if(!API_SYNLOGIN)
        {
            return API_RETURN_FORBIDDEN;
        }
        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
        set_login($uid, $username);
    }

    function synlogout($get, $post)
    {
        if(!API_SYNLOGOUT)
        {
            return API_RETURN_FORBIDDEN;
        }

        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
        set_cookie();
        set_session();
    }

    function updatepw($get, $post)
    {
        if(!API_UPDATEPW)
        {
            return API_RETURN_FORBIDDEN;
        }
        if (UC_CHARSET != 'UTF8')
        {
            $get['username'] = skyuc_iconv(UC_CHARSET,'UTF8', $get['username']);
        }

        $username = $this->db->escape_string($get['username']);
        $newpw = md5(TIMENOW.rand(100000, 999999));
        $this->db->query_write('UPDATE ' . TABLE_PREFIX. 'users' . " SET password='". $newpw ."' WHERE user_name='". $username ."'");
        return API_RETURN_SUCCEED;
    }

    function updatebadwords($get, $post)
    {
        if(!API_UPDATEBADWORDS)
        {
            return API_RETURN_FORBIDDEN;
        }
        $cachefile = $this->appdir.'/uc_client/data/cache/badwords.php';
        $fp = fopen($cachefile, 'w');
        $data = array();
        if(is_array($post))
        {
            foreach($post as $k => $v)
            {
                if (UC_CHARSET != 'UTF8')
				        {
				            $v['findpattern'] = skyuc_iconv(UC_CHARSET,'UTF8', $v['findpattern']);
				            $v['replacement'] = skyuc_iconv(UC_CHARSET,'UTF8', $v['replacement']);
				        }
                $data['findpattern'][$k] = $v['findpattern'];
                $data['replace'][$k] = $v['replacement'];
            }
        }
        $s = "<?php\r\n";
        $s .= '$_CACHE[\'badwords\'] = '.var_export($data, TRUE).";\r\n";
        fwrite($fp, $s);
        fclose($fp);
        return API_RETURN_SUCCEED;
    }

    function updatehosts($get, $post)
    {
        if(!API_UPDATEHOSTS)
        {
            return API_RETURN_FORBIDDEN;
        }
        $cachefile = $this->appdir . '/uc_client/data/cache/hosts.php';
        $fp = fopen($cachefile, 'w');
        $s = "<?php\r\n";
        $s .= '$_CACHE[\'hosts\'] = '.var_export($post, TRUE).";\r\n";
        fwrite($fp, $s);
        fclose($fp);
        return API_RETURN_SUCCEED;
    }

    function updateapps($get, $post)
    {
        if(!API_UPDATEAPPS)
        {
            return API_RETURN_FORBIDDEN;
        }
        //$UC_API = $post['UC_API'];

        if (UC_CHARSET != 'UTF8')
        {
            skyuc_iconv_array(UC_CHARSET,'UTF8', $post);
        }
        $cachefile = $this->appdir . '/uc_client/data/cache/apps.php';
        $fp = fopen($cachefile, 'w');
        $s = "<?php\r\n";
        $s .= '$_CACHE[\'apps\'] = '.var_export($post, TRUE).";\r\n";
        fwrite($fp, $s);
        fclose($fp);

        return API_RETURN_SUCCEED;
    }

    function updateclient($get, $post)
    {
        if(!API_UPDATECLIENT)
        {
            return API_RETURN_FORBIDDEN;
        }
        if (UC_CHARSET != 'UTF8')
        {
            skyuc_iconv_array(UC_CHARSET,'UTF8', $post);
        }
        $cachefile = $this->appdir . '/uc_client/data/cache/settings.php';
        $fp = fopen($cachefile, 'w');
        $s = "<?php\r\n";
        $s .= '$_CACHE[\'settings\'] = '.var_export($post, TRUE).";\r\n";
        fwrite($fp, $s);
        fclose($fp);
        return API_RETURN_SUCCEED;
    }

    function updatecredit($get, $post)
    {
    		global $skyuc;
        if(!API_UPDATECREDIT)
        {
            return API_RETURN_FORBIDDEN;
        }
        $cfg = unserialize($skyuc->options['integrate_config']);
        $credit = intval($get['credit']);
        $amount = intval($get['amount']);
        $uid = intval($get['uid']);
        $points = array(0 => 'pay_point');
        $sql = 'UPDATE ' .  TABLE_PREFIX. 'users' . ' SET '.$points["$credit"].' = '.$points["$credit"]. ' + '. $amount. ' WHERE user_id = '. $uid;
        $this->db->query_write($sql);
        if ($this->db->affected_rows() <= 0)
        {
            return API_RETURN_FAILED;
        }

        $sql = 'INSERT INTO ' . TABLE_PREFIX. 'account_log' . '(user_id, '. $points["$credit"]. ', change_time, change_desc, change_type)' .
         " VALUES ('".$uid ."', '". $amount."', '". TIMENOW ."', '" . $cfg['uc_lang']['exchange'] . "', '99')";
        $this->db->query_write($sql);
        return API_RETURN_SUCCEED;
    }

    function getcredit($get, $post)
    {
        if(!API_GETCREDIT)
        {
            return API_RETURN_FORBIDDEN;
        }

    }

    function getcreditsettings($get, $post)
    {
    		global $skyuc;
        if(!API_GETCREDITSETTINGS)
        {
            return API_RETURN_FORBIDDEN;
        }

        $cfg = unserialize($skyuc->options['integrate_config']);

        $credits = $cfg['uc_lang']['credits'];

        if (UC_CHARSET != 'UTF8')
        {
            $credits[0][0] = skyuc_iconv('UTF8', UC_CHARSET, $credits[0][0]);

        }
        include_once(DIR . '/uc_client/client.php');
        return uc_serialize($credits);
    }

    function updatecreditsettings($get, $post)
    {
        if(!API_UPDATECREDITSETTINGS)
        {
            return API_RETURN_FORBIDDEN;
        }

        $outextcredits = array();
        foreach($get['credit'] as $appid => $credititems)
        {
            if($appid == UC_APPID)
            {
                foreach($credititems as $value)
                {
                    if (UC_CHARSET != 'UTF8')
						        {
						            $value['appiddesc'] = skyuc_iconv(UC_CHARSET,	'UTF8', $value['appiddesc']);
						            $value['creditdesc'] = skyuc_iconv(UC_CHARSET,	'UTF8', $value['creditdesc']);
						            $value['title'] = skyuc_iconv(UC_CHARSET,	'UTF8', $value['title']);
						            $value['unit'] = skyuc_iconv(UC_CHARSET,	'UTF8', $value['unit']);
						            $value['ratio'] = skyuc_iconv(UC_CHARSET,	'UTF8', $value['ratio']);
						        }
                    $outextcredits[] = array(
                        'appiddesc' => $value['appiddesc'],
                        'creditdesc' => $value['creditdesc'],
                        'creditsrc' => $value['creditsrc'],
                        'title' => $value['title'],
                        'unit' => $value['unit'],
                        'ratio' => $value['ratio']
                    );
                }
            }
        }
        $this->db->query_write('UPDATE ' . TABLE_PREFIX. 'setting' . " SET value='". serialize($outextcredits) ."' WHERE code='points_rule'");

        build_options();

        return API_RETURN_SUCCEED;
    }
}

/**
 *  删除用户接口函数
 *
 * @access  public
 * @param   int $uids
 * @return  void
 */
function delete_user($uids = '')
{
		global $skyuc;
    if (empty($uids))
    {
        return;
    }
    else
    {
        $sql = 'DELETE FROM ' . TABLE_PREFIX. 'users' . ' WHERE user_id IN ('.$uids.')';
        $result = $skyuc->db->query_write($sql);
        return true;
    }
}

/**
 * 设置用户登陆
 *
 * @access  public
 * @param int $uid
 * @return void
 */
function set_login($user_id = '', $user_name = '')
{
		global $skyuc;

    if (empty($user_id))
    {
        return ;
    }
    else
    {
        $sql = 'SELECT user_name, email FROM ' . TABLE_PREFIX. 'users' . ' WHERE user_id='.$user_id;
        $row = $skyuc->db->query_first($sql);
        if ($row)
        {
            set_cookie($user_id, $row['user_name'], $row['email']);
            set_session($user_id, $row['user_name'], $row['email']);
            include_once(DIR . '/includes/functions_users.php');
            update_user_info();
        }
        else
        {
            include_once(DIR . '/uc_client/client.php');
            if($data = uc_get_user($user_name))
            {
                list($uid, $uname, $email) = $data;

                if (UC_CHARSET != 'UTF8')
				        {
				            $uname = skyuc_iconv(UC_CHARSET,'UTF8', $uname);
				        }
                $sql = 'REPLACE INTO ' . TABLE_PREFIX. 'users' .'(user_id, user_name, email) '.
                			 " VALUES('".$uid ."', '". $skyuc->db->escape_string($uname) ."', '". $skyuc->db->escape_string($email)."')";
                $skyuc->db->query($sql);
                set_login($uid);
            }
            else
            {
                return false;
            }
        }
    }
}

/**
 *  设置cookie
 *
 * @access  public
 * @param
 * @return void
 */
function set_cookie($user_id='', $user_name = '', $email = '')
{
    if (empty($user_id))
    {
            // 摧毁cookie
            $time = TIMENOW - 86400;
            skyuc_setcookie('username', '', $time);
            skyuc_setcookie('userid',  '', $time);
            skyuc_setcookie('password', '', $time);
    }
    else
    {
        // 设置cookie
        $time = TIMENOW + 86400 * 7;

        if (UC_CHARSET != 'UTF8')
        {
            $user_name = skyuc_iconv(UC_CHARSET,'UTF8', $user_name);
        }
        skyuc_setcookie('username', $user_name, $time);
        skyuc_setcookie('userid',   $user_id,   $time);
        skyuc_setcookie('email',    $email,     $time);
    }
}

/**
 *  设置指定用户SESSION
 *
 * @access  public
 * @param
 * @return void
 */
function set_session ($user_id = '', $user_name = '', $email = '')
{
		global $skyuc;
    if (empty($user_id))
    {
        $skyuc->session->destroy_session();
    }
    else
    {
				$skyuc->session->set('userid', $user_id);
    }
}

/**
 *  获取SKYUC的TAG数据
 *
 * @access  public
 * @param  string $tagname
 * @param   int    $num 获取的数量 默认取最新的100条
 * @return  array
 */
function fetch_tag($tagname, $num=100)
{
		global $skyuc;

    $rewrite = intval($skyuc->options['rewrite']) > 0;
    $sql = 'SELECT t.*, u.user_name, m.show_id, m.title, m.thumb	 FROM ' . TABLE_PREFIX. 'tag' . ' as t, ' . TABLE_PREFIX. 'users' .' as u, ' .
    TABLE_PREFIX. 'show' .' as m WHERE t.user_id = u.user_id AND m.show_id = t.show_id ORDER BY t.tag_id DESC ';
    $sql = $skyuc->db->query_limit($sql,	$num);
    $res = $skyuc->db->query_read_slave($sql);
    $tag_list = array();
    while ($row = $skyuc->db->fetch_array($res))
    {
        if (UC_CHARSET != 'UTF8')
        {
            $row['title'] = skyuc_iconv('UTF8', UC_CHARSET, $row['title']);
            $row['user_name'] = skyuc_iconv('UTF8', UC_CHARSET, $row['user_name']);
        }

    	  $row['film_name']  =  $row['title'];
        $row['uid']         = $row['user_id'];
        $row['username']    = $row['user_name'];
        $row['dateline']    = TIMENOW;
        $row['url']         = $GLOBALS['skyuc_url'] .  'show.php?id=' . $row['show_id'];
        $row['image']       = $GLOBALS['skyuc_url'] . $row['thumb'];
        $tag_list[] = $row;

    }
    return $tag_list;
}


/**
 *  uc自带函数2
 *
 * @access  public
 *
 * @return  string  $string
 */
function _authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    $ckey_length = 4;
    $key = md5($key ? $key : UC_KEY);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for($i = 0; $i <= 255; $i++)
    {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for($j = $i = 0; $i < 256; $i++)
    {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i++)
    {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if($operation == 'DECODE')
    {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16))
        {
            return substr($result, 26);
        }
        else
        {
            return '';
        }
    }
    else
    {
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}

/**
 *  uc自带函数3
 *
 * @access  public
 * @param   string  $string
 *
 * @return  string  $string
 */
function _stripslashes($string)
{
    if(is_array($string))
    {
        foreach($string as $key => $val)
        {
            $string[$key] = _stripslashes($val);
        }
    }
    else
    {
        $string = stripslashes($string);
    }
    return $string;
}

?>