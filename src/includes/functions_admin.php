<?php
/**
 * SKYUC! 后台函数库
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */
if (!defined('SKYUC_AREA')) {
    echo 'SKYUC_AREA must be defined to continue';
    exit ();
}

// #############################################################################
/**
 * 将数据保存到数据库中的 adminutil 表
 *
 * @param    string    要保存 adminutil 记录的名称
 * @param    string    要保存到 adminutil 表数据
 *
 * @return    boolean
 */
function build_adminutil_text($title, $text = '')
{

    if ($text == '') {
        $GLOBALS ['db']->query_write('
			DELETE FROM ' . TABLE_PREFIX . "adminutil
			WHERE title = '" . $GLOBALS ['db']->escape_string($title) . "'
		");
    } else {
        // 对数组转换字符串表示
        $text = is_array($text) ? serialize($text) : $text;
        /*insert query*/
        $GLOBALS ['db']->query_write('
			REPLACE INTO ' . TABLE_PREFIX . "adminutil
			(title, text)
			VALUES
			('" . $GLOBALS ['db']->escape_string($title) . "', '" . $GLOBALS ['db']->escape_string($text) . "')
		");
    }

    return true;
}

// #############################################################################
/**
 * 从数据库中的 adminutil 表返回数据
 *
 * @param    string    要获取的 adminutil 记录的名称
 *
 * @return    string
 */
function fetch_adminutil_text($title)
{

    $text = $GLOBALS ['db']->query_first('SELECT text FROM ' . TABLE_PREFIX . "adminutil WHERE title = '$title'");
    if (($text ['text'] [0] == 'a' and $text ['text'] [1] == ':')) {
        $text ['text'] = unserialize($text ['text']);
    }
    return $text ['text'];
}

/**
 * 建立服务器数组缓存
 * @return array 服务器列表
 */
function build_servers()
{

    $GLOBALS ['skyuc']->servers = array();

    $sql = 'SELECT * FROM ' . TABLE_PREFIX . 'server' . ' WHERE is_show = 1 ORDER BY sort_order';
    $res = $GLOBALS ['db']->query_read($sql);
    while ($row = $GLOBALS ['db']->fetch_array($res)) {
        $GLOBALS ['skyuc']->servers ["$row[server_id]"] = array('id' => $row ['server_id'], 'name' => $row ['server_name'], 'url' => $row ['server_url'], 'desc' => $row ['server_desc'], 'show' => $row ['is_show']);
    }

    build_datastore('servers', serialize($GLOBALS ['skyuc']->servers), 1);

    return $GLOBALS ['skyuc']->servers;
}

/**
 * 建立会员等级缓存
 *
 * @return  array     rank_id=>rank_name
 */
function build_usergroup()
{

    $GLOBALS ['skyuc']->usergroup = array();

    $res = $GLOBALS ['db']->query_read('SELECT rank_id, rank_name, rank_type, day_play, day_down, allow_cate,
    allow_hours, count,money, content FROM ' . TABLE_PREFIX . 'user_rank' . ' ORDER BY rank_id');
    while ($row = $GLOBALS ['db']->fetch_array($res)) {
        $GLOBALS ['skyuc']->usergroup ["$row[rank_id]"] = array('id' => $row ['rank_id'],
            'name' => $row ['rank_name'],
            'type' => $row ['rank_type'],
            'play' => $row ['day_play'],
            'down' => $row ['day_down'],
            'cate' => $row ['allow_cate'],
            'hours' => $row ['allow_hours'],
            'count' => $row ['count'],
            'money' => $row ['money'],
            'content' => $row ['content']
        );
    }

    build_datastore('usergroup', serialize($GLOBALS ['skyuc']->usergroup), 1);

    return $GLOBALS ['skyuc']->usergroup;
}

/**
 * 建立播放器代码缓存
 *
 * @return  array
 */
function build_players()
{

    $GLOBALS ['skyuc']->players = array();

    $res = $GLOBALS ['db']->query_read('SELECT id, title, tag, user_rank, player_code  FROM ' . TABLE_PREFIX . 'player
    WHERE
    is_show=1 ORDER BY id, sort_order');
    while ($row = $GLOBALS ['db']->fetch_array($res)) {
        $GLOBALS ['skyuc']->players [$row ['tag']] = $row;
    }

    build_datastore('players', serialize($GLOBALS ['skyuc']->players), 1);

    return $GLOBALS ['skyuc']->players;
}

/**
 * 建立当前模板设置缓存
 *
 * @return  array
 */
function build_template()
{

    $GLOBALS ['skyuc']->template = array();

    $res = $GLOBALS ['db']->query_read('SELECT filename, library ,number  FROM ' . TABLE_PREFIX . 'template' . " WHERE theme='" . $GLOBALS ['skyuc']->options ['themes'] . "'" . ' ORDER BY filename');
    while ($row = $GLOBALS ['db']->fetch_array($res)) {
        $libname = basename($row ['library'], '.lbi');
        $GLOBALS ['skyuc']->template [$row ['filename']] ["$libname"] = $row;
    }

    build_datastore('template', serialize($GLOBALS ['skyuc']->template), 1);

    return $GLOBALS ['skyuc']->template;
}

/**
 * 建立文章分类数组缓存
 * @return array 服务器列表
 */
function build_article_cat()
{

    $GLOBALS ['skyuc']->article_cat = array();

    $sql = "SELECT c.*, COUNT(s.cat_id) AS has_children, COUNT(a.article_id) AS aricle_num " . ' FROM ' . TABLE_PREFIX . 'article_cat' . " AS c" . " LEFT JOIN " . TABLE_PREFIX . 'article_cat' . " AS s ON s.parent_id=c.cat_id" . " LEFT JOIN " . TABLE_PREFIX . 'article' . " AS a ON a.cat_id=c.cat_id" . " GROUP BY c.cat_id " . " ORDER BY parent_id, sort_order ASC";

    $res = $GLOBALS ['db']->query_read($sql);
    while ($row = $GLOBALS ['db']->fetch_array($res)) {
        $GLOBALS ['skyuc']->article_cat [] = $row;
    }

    build_datastore('article_cat', serialize($GLOBALS ['skyuc']->article_cat), 1);

    return $GLOBALS ['skyuc']->article_cat;
}

/**
 * 建立分类数组缓存
 *
 * @return array()
 */
function build_category()
{

    $GLOBALS ['skyuc']->category = array();

    //分类
    $sql = 'SELECT c.*, COUNT(s.cat_id) AS has_children ' . 'FROM ' . TABLE_PREFIX . 'category' . ' AS c ' . 'LEFT JOIN ' . TABLE_PREFIX . 'category' . ' AS s ON s.parent_id=c.cat_id ' . 'GROUP BY c.cat_id ' . 'ORDER BY parent_id, sort_order ASC';
    $res = $GLOBALS ['db']->query_all_slave($sql);

    //分类下影片总数统计
    $sql = 'SELECT c.cat_id as cat_id, COUNT(m.show_id) AS show_num ' . 'FROM ' . TABLE_PREFIX . 'category' . ' AS c ' . 'LEFT JOIN ' . TABLE_PREFIX . 'show' . ' AS m ON m.cat_id=c.cat_id ' . 'GROUP BY c.cat_id ';
    $res2 = $GLOBALS ['db']->query_all_slave($sql);

    $newres = array();
    foreach ($res2 as $k => $v) {
        $newres [$v ['cat_id']] = $v ['show_num'];
    }

    foreach ($res as $k => $v) {
        $res [$k] ['show_num'] = $newres [$v ['cat_id']];
    }

    $GLOBALS ['skyuc']->category = & $res;
    build_datastore('category', serialize($GLOBALS ['skyuc']->category), 1);
    $GLOBALS ['skyuc']->categories ['time'] = 0;
    build_datastore('categories', serialize($GLOBALS ['skyuc']->categories), 1); //设置前台分类缓存过期


    return $GLOBALS ['skyuc']->category;
}

/**
 * 记录管理员的操作内容
 *
 * @access  public
 * @param   string      $sn         数据的唯一值
 * @param   string      $action     操作的类型
 * @param   string      $content    操作的内容
 * @return  void
 */
function admin_log($sn = '', $action, $content)
{

    $log_info = $GLOBALS ['_LANG'] ['log_action'] [$action] . $GLOBALS ['_LANG'] ['log_action'] [$content] . ': ' . $sn;

    $sql = 'INSERT INTO ' . TABLE_PREFIX . 'admin_log' . ' (log_time, user_id, log_info, ip_address) ' . " VALUES ('" . TIMENOW . "', " . $GLOBALS ['skyuc']->session->vars ['adminid'] . ", '" . $GLOBALS ['db']->escape_string($log_info) . "', '" . ALT_IP . "')";
    $GLOBALS ['db']->query_write($sql);
}

/**
 * 设置管理员的session内容
 *
 * @access  public
 * @param   integer $user_id        管理员编号
 * @param   string  $username       管理员姓名
 * @param   string  $action_list    权限列表
 * @param   string  $last_time      最后登录时间
 * @return  void
 */
function set_admin_session($user_id, $username, $action_list, $last_time)
{
    $GLOBALS ['skyuc']->session->set('adminid', $user_id);
    $GLOBALS ['skyuc']->session->set('loggedin', 1);

    build_adminutil_text($user_id . '_admin_name', $username);
    build_adminutil_text($user_id . '_action_list', $action_list);
    build_adminutil_text($user_id . '_last_check', $last_time); // 用于保存最后一次检查订单的时间
}

/**
 * 检查管理员权限
 *
 * @access  public
 * @param   string  $authz
 * @return  boolean
 */
function check_authz($authz)
{

    $action_list = fetch_adminutil_text($GLOBALS ['skyuc']->session->vars ['adminid'] . '_action_list');

    return (preg_match('/,*' . $authz . ',*/', $action_list) || $action_list == 'all');
}

/**
 * 检查管理员权限，返回JSON格式数据
 *
 * @access  public
 * @param   string  $authz
 * @return  void
 */
function check_authz_json($authz)
{
    if (!check_authz($authz)) {
        make_json_error($GLOBALS ['_LANG'] ['priv_error']);
    }
}

/**
 * 清空表数据
 * @param   string  $table_name 表名称
 */
function truncate_table($table_name)
{

    $sql = 'TRUNCATE TABLE ' . TABLE_PREFIX . $table_name;

    return $GLOBALS ['db']->query_write($sql);
}

/**
 * 优化表数据
 * @param   string  $table_name 表名称
 */
function optimize_table($table_name)
{
    $sql = 'OPTIMIZE TABLE ' . TABLE_PREFIX . $table_name;

    return $GLOBALS ['db']->query_write($sql);
}

/**
 * 返回字符集列表数组
 *
 * @access  public
 * @param
 *
 * @return void
 */
function get_charset_list()
{
    return array('UTF8' => 'UTF-8', 'GB2312' => 'GB2312/GBK', 'BIG5' => 'BIG5');
}

/**
 * 创建一个JSON格式的数据
 *
 * @access  public
 * @param   string      $content
 * @param   integer     $error
 * @param   string      $message
 * @param   array       $append
 * @return  void
 */
function make_json_response($content = '', $error = "0", $message = '', $append = array())
{
    include_once (DIR . '/includes/class_json.php');

    $json = new JSON ();

    $res = array('error' => $error, 'message' => $message, 'content' => $content);

    if (!empty ($append)) {
        foreach ($append as $key => $val) {
            $res [$key] = $val;
        }
    }

    $val = $json->encode($res);
    exit ($val);
}

/**
 *
 *
 * @access  public
 * @param
 * @return  void
 */
function make_json_result($content, $message = '', $append = array())
{
    make_json_response($content, 0, $message, $append);
}

/**
 * 创建一个JSON格式的错误信息
 *
 * @access  public
 * @param   string  $msg
 * @return  void
 */
function make_json_error($msg)
{
    make_json_response('', 1, $msg);
}

/**
 * 判断管理员对某一个操作是否有权限。
 *
 * 根据当前对应的action_code，然后再和用户session里面的action_list做匹配，以此来决定是否可以继续执行。
 * @param     string    $priv_str    操作对应的priv_str
 * @param     string    $msg_type       返回的类型
 * @return true/false
 */
function admin_priv($priv_str, $msg_type = '')
{

    $action_list = fetch_adminutil_text($GLOBALS ['skyuc']->session->vars ['adminid'] . '_action_list');
    if ($action_list == 'all') {
        return true;
    }

    if (strpos(',' . $action_list . ',', ',' . $priv_str . ',') === false) {
        $link [] = array('text' => $GLOBALS ['_LANG'] ['go_back'], 'href' => 'javascript:history.back(-1)');

        sys_msg($GLOBALS ['_LANG'] ['priv_error'], 0, $link);
        return false;
    } else {
        return true;
    }
}

/**
 * 取得广告位置数组（用于生成下拉列表）
 *
 * @return  array       分类数组 position_id => position_name
 */
function get_position_list()
{

    $position_list = array();
    $sql = 'SELECT position_id, position_name, ad_width, ad_height ' . 'FROM ' . TABLE_PREFIX . 'ad_position';
    $res = $GLOBALS ['db']->query_read_slave($sql);

    while ($row = $GLOBALS ['db']->fetch_array($res)) {
        $position_list [$row ['position_id']] = $row ['position_name'] . ' [' . $row ['ad_width'] . 'x' . $row ['ad_height'] . ']';
    }

    return $position_list;
}

/**
 * 生成编辑器
 * @param   string  input_name  输入框名称
 * @param   string  input_value 输入框值
 */
function create_html_editor($input_name, $input_value = '')
{

    // 包含 html editor 类文件
    if (!class_exists('CKEditor')) {
        include (DIR . '/includes/editor/ckeditor.php');
    }
    $editor = new CKEditor ();
    $editor->basePath = '../includes/editor/';
    $editor->returnOutput = true;
    $editor->timestamp = 'Normal';

    $config = array();

    $config ['filebrowserBrowseUrl'] = '../includes/editor/ckfinder.html';
    $config ['filebrowserImageBrowseUrl'] = '../includes/editor/ckfinder.html?type=Images';
    $config ['filebrowserFlashBrowseUrl'] = '../includes/editor/ckfinder.html?type=Flash';
    $config ['filebrowserUploadUrl'] = '../includes/editor/core/connector/php/connector.php?command=QuickUpload&type=Files';
    $config ['filebrowserImageUploadUrl'] = '../includes/editor/core/connector/php/connector.php?command=QuickUpload&type=Images';
    $config ['filebrowserFlashUploadUrl'] = '../includes/editor/core/connector/php/connector.php?command=QuickUpload&type=Flash';

    $config ['toolbar'] = array(array('Source', '-'), array('Undo', 'Redo', '-', 'SelectAll', 'RemoveFormat'), array('Format', 'Font', 'FontSize'), array('Bold', 'Italic', 'Underline', 'Strike'), array('TextColor', 'BGColor'), array('Link', 'Unlink', 'Anchor'), array('Image', 'Table', 'HorizontalRule'), array('NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', 'Blockquote'), array('JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'), array('ShowBlocks'));

    $CKeditor = $editor->editor($input_name, $input_value, $config);
    $GLOBALS ['smarty']->assign('FCKeditor', $CKeditor);
}

/**
 * 分页的信息加入条件的数组
 *
 * @access  public
 * @return  array
 */
function page_and_size($filter)
{

    $GLOBALS ['skyuc']->input->clean_array_gpc('r', array('page_size' => TYPE_UINT, 'page' => TYPE_UINT));

    if ($GLOBALS ['skyuc']->GPC_exists ['page_size'] && $GLOBALS ['skyuc']->GPC ['page_size'] > 0) {
        $filter ['page_size'] = $GLOBALS ['skyuc']->GPC ['page_size'];
    } elseif (isset ($_COOKIE ['SKYUC_page_size']) && intval($_COOKIE ['SKYUC_page_size']) > 0) {
        $filter ['page_size'] = intval($_COOKIE ['SKYUC_page_size']);
    } else {
        $filter ['page_size'] = 15;
    }

    // 每页显示
    $filter ['page'] = iif($GLOBALS ['skyuc']->GPC ['page'] == 0, 1, $GLOBALS ['skyuc']->GPC ['page']);

    // page 总数
    $filter ['page_count'] = (!empty ($filter ['record_count']) && $filter ['record_count'] > 0)
        ? ceil($filter ['record_count'] / $filter ['page_size']) : 1;

    // 边界处理
    if ($filter ['page'] > $filter ['page_count']) {
        $filter ['page'] = $filter ['page_count'];
    }

    $filter ['start'] = ($filter ['page'] - 1) * $filter ['page_size'];

    return $filter;
}

/**
 * 获得所有模块的名称以及链接地址
 *
 * @access      public
 * @param       string      $directory      插件存放的目录
 * @return      array
 */
function read_modules($directory = '.')
{
    global $_LANG;

    $dir = @opendir($directory);
    $set_modules = true;
    $modules = array();

    while ($file = @readdir($dir)) {
        if (preg_match("/^.*?\.php$/", $file)) {
            include_once ($directory . '/' . $file);
        }
    }
    @closedir($dir);
    unset ($set_modules);

    foreach ($modules as $key => $value) {
        ksort($modules [$key]);
    }
    ksort($modules);

    return $modules;
}

/**
 * 系统提示信息
 *
 * @access      public
 * @param       string      msg_detail      消息内容
 * @param       int         msg_type        消息类型， 0消息，1错误，2询问
 * @param       array       links           可选的链接
 * @param       boolen      $auto_redirect  是否需要自动跳转
 * @return      void
 */
function sys_msg($msg_detail, $msg_type = 0, $links = array(), $auto_redirect = true)
{

    if (count($links) == 0) {
        $links [0] ['text'] = $GLOBALS ['_LANG'] ['go_back'];
        $links [0] ['href'] = 'javascript:history.go(-1)';
    }

    assign_query_info();

    $GLOBALS ['smarty']->assign('ur_here', $GLOBALS ['_LANG'] ['system_message']);
    $GLOBALS ['smarty']->assign('msg_detail', $msg_detail);
    $GLOBALS ['smarty']->assign('msg_type', $msg_type);
    $GLOBALS ['smarty']->assign('links', $links);
    $GLOBALS ['smarty']->assign('default_url', $links [0] ['href']);
    $GLOBALS ['smarty']->assign('auto_redirect', $auto_redirect);
    $GLOBALS ['smarty']->display('message.tpl');
    exit ();
}

/**
 * 消息提示
 *
 * @param                string        $msg    提示消息
 * @param                sring            $gourl    转向链接
 * @param                boolen        $onlymsg    消息类型:0=转向地址,1=消息
 * @param                int                $limittime 转向链接时间,单位毫秒,默认3秒
 * @return      void
 */

function ShowMsg($msg, $gourl, $onlymsg = 0, $limittime = 0)
{
    $htmlhead = "<html>\r\n<head>\r\n<title>SKYUC! 系统提示</title>\r\n<meta name=\"robots\" content=\"noindex, nofollow\">\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\r\n";
    $htmlhead .= "<base target='_self'/>\r\n</head>\r\n<body leftmargin='0' topmargin='0'>\r\n<center>\r\n<script>\r\n";
    $htmlfoot = "</script>\r\n</center>\r\n</body>\r\n</html>\r\n";

    if ($limittime == 0)
        $litime = 3000;
    else
        $litime = $limittime;

    if ($gourl == "-1") {
        if ($limittime == 0)
            $litime = 3000;
        $gourl = "javascript:history.go(-1);";
    }

    if ($gourl == '' || $onlymsg == 1) {
        $msg = "<script>alert(\"" . str_replace("\"", "“", $msg) . "\");</script>";
    } else {
        $func = "      var pgo=0;
      function JumpUrl(){
        if(pgo==0){ location='$gourl'; pgo=1; }
      }\r\n";
        $rmsg = $func;
        $rmsg .= "document.write(\"<br/><div style='width:450px;padding-top:4px;height:24;font-size:10pt;font-weight:bold;border-left:1px solid #BBDDE5;border-top:1px solid #BBDDE5;border-right:1px solid #BBDDE5;background-color:#80BDCB;'>" . $GLOBALS ['_LANG'] ['information'] . "</div>\");\r\n";
        $rmsg .= "document.write(\"<div style='width:450px;height:130;font-size:10pt;color:#192E32;border:1px solid #BBDDE5;background-color:#F4FaFb'><br/><br/>\");\r\n";
        $rmsg .= "document.write(\"" . str_replace("\"", "“", $msg) . "\");\r\n";
        $rmsg .= "document.write(\"";
        if ($onlymsg == 0) {
            if ($gourl != "javascript:;" && $gourl != "") {
                $rmsg .= "<br/><br/><a href='" . $gourl . "'>" . $GLOBALS ['_LANG'] ['pleaselink'] . "</a>";
            }
            $rmsg .= "<br/><br/></div>\");\r\n";
            if ($gourl != "javascript:;" && $gourl != "") {
                $rmsg .= "setTimeout('JumpUrl()',$litime);";
            }
        } else {
            $rmsg .= "<br/><br/></div>\");\r\n";
        }
        $msg = $htmlhead . $rmsg . $htmlfoot;
    }
    echo $msg;
}

/**
 * 获得查询时间和次数，并赋值给smarty
 *
 * @access  public
 * @return  void
 */
function assign_query_info()
{
    if ($GLOBALS ['db']->queryTime == '') {
        $query_time = 0;
    } else {
        $query_time = number_format(microtime(true) - $GLOBALS ['db']->queryTime, 6);
    }
    $GLOBALS ['smarty']->assign('query_info', sprintf('Processed in %f second(s), %d queries, ', $query_time, $GLOBALS ['db']->queryCount));

    $gzip_enabled = iif($GLOBALS ['skyuc']->options ['gzipoutput'] && $GLOBALS ['skyuc']->options ['gziplevel'] > 0, 'Gzip On', 'Gzip Off');
    $GLOBALS ['smarty']->assign('gzip_enabled', $gzip_enabled);
}

/**
 * 取得图表颜色
 *
 * @access  public
 * @param   integer $n  颜色顺序
 * @return  void
 */
function chart_color($n)
{
    /* 随机显示颜色代码 */
    $arr = array('33FF66', 'FF6600', '3399FF', '009966', 'CC3399', 'FFCC33', '6699CC', 'CC3366', '33FF66', 'FF6600', '3399FF');

    if ($n > 8) {
        $n = $n % 8;
    }

    return $arr [$n];
}

/**
 * 保存过滤条件
 * @param   array   $filter     过滤条件
 * @param   string  $sql        查询语句
 * @param   string  $param_str  参数字符串，由list函数的参数组成
 */
function set_filter($filter, $sql, $param_str = '')
{
    $filterfile = basename(PHP_SELF, '.php');
    if ($param_str) {
        $filterfile .= $param_str;
    }
    skyuc_setcookie('lastfilterfile', sprintf('%X', crc32($filterfile)), TIMENOW + 600);
    skyuc_setcookie('lastfilter', urlencode(serialize($filter)), TIMENOW + 600);
    skyuc_setcookie('lastfiltersql', urlencode($sql), TIMENOW + 600);
}

/**
 * 取得上次的过滤条件
 * @param   string  $param_str  参数字符串，由list函数的参数组成
 * @return  如果有，返回array('filter' => $filter, 'sql' => $sql)；否则返回false
 */
function get_filter($param_str = '')
{
    $filterfile = basename(PHP_SELF, '.php');
    if ($param_str) {
        $filterfile .= $param_str;
    }
    if (isset ($_GET ['uselastfilter']) && isset ($_COOKIE [COOKIE_PREFIX . 'lastfilterfile']) && $_COOKIE [COOKIE_PREFIX . 'lastfilterfile'] == sprintf('%X', crc32($filterfile))) {
        return array('filter' => unserialize(urldecode($_COOKIE [COOKIE_PREFIX . 'lastfilter'])), 'sql' => urldecode($_COOKIE [COOKIE_PREFIX . 'lastfiltersql']));
    } else {
        return false;
    }
}

/**
 * 验证你使用的优化程序和 SKYUC 是否兼容。
 * 优化程序的各种版本中的 bug，如 Turck MMCache 和 eAccelerator 不能用于 SKYUC 。
 *
 * @return    string|bool    如果没有错误返回true，否则返回一个字符串，表示所发生的错误
 */
function verify_optimizer_environment()
{

    // 失败，如果  eAccelerator 太旧 或 Turck MMCache 加载了
    if (extension_loaded('Turck MMCache')) {
        return $GLOBALS ['_LANG'] ['mmcache_not_supported'];
    } else if (extension_loaded('eAccelerator')) {
        // 首先，尝试使用 phpversion()...
        if ($eaccelerator_version = phpversion('eAccelerator')) {
            if (version_compare($eaccelerator_version, '0.9.3', '<') and (@ini_get('eaccelerator.enable') or @ini_get('eaccelerator.optimizer'))) {
                return $GLOBALS ['_LANG'] ['eaccelerator_too_old'];
            }
        } // phpversion() 失败, 使用 phpinfo 数据
        else if (function_exists('phpinfo') and function_exists('ob_start') and @ob_start()) {
            eval ('phpinfo();');
            $info = @ob_get_contents();
            @ob_end_clean();
            preg_match('#<tr class="h"><th>eAccelerator support</th><th>enabled</th></tr>(?:\s+)<tr><td class="e">Version </td><td class="v">(.*?)</td></tr>(?:\s+)<tr><td class="e">Caching Enabled </td><td class="v">(.*?)</td></tr>(?:\s+)<tr><td class="e">Optimizer Enabled </td><td class="v">(.*?)</td></tr>#si', $info, $hits);
            if (!empty ($hits [0])) {
                $version = trim($hits [1]);
                $caching = trim($hits [2]);
                $optimizer = trim($hits [3]);

                if (($caching === 'true' or $optimizer === 'true') and version_compare($version, '0.9.3', '<')) {
                    return $GLOBALS ['_LANG'] ['eaccelerator_too_old'];
                }
            }
        }
    } else if (extension_loaded('apc')) {
        //首先，尝试使用 phpversion()...
        if ($apc_version = phpversion('apc')) {
            if (version_compare($apc_version, '2.0.4', '<')) {
                return $GLOBALS ['_LANG'] ['apc_too_old'];
            }
        } // phpversion() 失败, 使用 phpinfo 数据
        else if (function_exists('phpinfo') and function_exists('ob_start') and @ob_start()) {
            eval ('phpinfo();');
            $info = @ob_get_contents();
            @ob_end_clean();
            preg_match('#<tr class="h"><th>APC support</th><th>enabled</th></tr>(?:\s+)<tr><td class="e">Version </td><td class="v">(.*?)</td></tr>#si', $info, $hits);
            if (!empty ($hits [0])) {
                $version = trim($hits [1]);

                if (version_compare($version, '2.0.4', '<')) {
                    return $GLOBALS ['_LANG'] ['apc_too_old'];
                }
            }
        }
    }

    return true;
}

/**
 * 中文分词
 *
 * @param        string        $title    标题
 * @param        string        $body    内容
 * @return    string 中文分词后的字符
 */
function splitword($title, $body)
{

    $keywords = '';
    $search = array(",", "/", "\\", ".", ";", ":", "\"", "!", "~", "`", "^", "(", ")", "?", "-", "\t", "\n", "'", "<", ">", "\r", "\r\n", "$", "&", "%", "#", "@", "+", "=", "{", "}", "[", "]", "：", "）", "（", "．", "。", "，", "！", "；", "“", "”", "‘", "’", "〔", "〕", "、", "—", "　", "《", "》", "－", "…", "【", "】", "|");
    $title = str_replace($search, '', $title);
    $body = str_replace($search, '', $body);

    if ($GLOBALS ['skyuc']->options ['enablehttpcws'] == 1 and $GLOBALS ['skyuc']->options ['httpcws'] != '') {
        $body = skyuc_iconv('utf-8', 'gbk', $title) . skyuc_iconv('utf-8', 'gbk', $body);
        $server = trim($GLOBALS ['skyuc']->options ['httpcws']) . urlencode($body);
        $allindexs = explode(' ', trim(fetch_body_request($server, 'gbk')));
        $allindexs = array_unique($allindexs);
        if (is_array($allindexs)) {
            foreach ($allindexs as $k) {
                if (strlen($keywords) >= 100) {
                    break;
                } else if (strlen($k) > 3) {
                    //多于一个UTF8汉字
                    $keywords .= $k . ' ';
                }
            }
        }
    } else {
        include_once (DIR . '/includes/class_splitword.php');
        $sp = new SplitWord ();
        $titleindexs = explode(' ', trim($sp->GetIndexText($title)));
        $allindexs = explode(' ', trim($sp->GetIndexText(html2text($body), 200)));
        $allindexs = array_unique($allindexs);
        if (is_array($allindexs) && is_array($titleindexs)) {
            foreach ($titleindexs as $k) {
                if (strlen($keywords) >= 30) {
                    break;
                } else {
                    $keywords .= $k . ',';
                }
            }
            foreach ($allindexs as $k) {
                if (strlen($keywords) >= 100) {
                    break;
                } else if (!in_array($k, $titleindexs) and strlen($k) > 3) {
                    $keywords .= $k . ' ';
                }
            }
        }
    }

    $keywords = addslashes($keywords);
    if ($keywords == '') {
        return '';
    }

    return trim($keywords);
}

/**
 * 上传文件
 *
 * @return string or false
 */
function upload_file($upload, $type)
{
    //上传文件夹
    switch ($type) {
        case 'af' :
            $dir = 'afficheimg';
            break;
        case 'ar' :
            $dir = 'article';
            break;
        case 'fe' :
            $dir = 'feedbackimg';
            break;
        case 'po' :
        default :
            $dir = 'posters';
            break;
    }

    $filename = random(9, 1) . substr($upload ['name'], strpos($upload ['name'], '.'));
    $path = DIR . '/' . $GLOBALS ['skyuc']->config ['Misc'] ['imagedir'] . '/' . $dir . '/' . $filename;

    if (move_upload_file($upload ['tmp_name'], $path)) {
        return $GLOBALS ['skyuc']->config ['Misc'] ['imagedir'] . '/' . $dir . '/' . $filename;
    } else {
        return false;
    }
}

/**
 * 更新文件缓存
 * @param $key 缓存名称ID，32位字母和数字
 * @param $data 要写入缓存的数据
 */
function update_file_cache($key, $data = '')
{
    $data = serialize(array('data' => $data, 'expires' => TIMENOW));
    if ($GLOBALS ['skyuc']->secache->store($key, $data) === false) {
        trigger_error('can\'t write:' . $key);
    }
    $GLOBALS ['skyuc']->secache->setModified('index.dwt');
}

?>