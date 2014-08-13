<?php
/**
 * SKYUC! 公用函数库
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */
/**
 * @ignore
 */
define('COOKIE_SALT',
$GLOBALS['skyuc']->config['Misc']['cookie_security_hash']);
// #############################################################################
/**
 * 实际上是一个包装的三元运算符。
 *
 * @param	string	要计算的表达式
 * @param	mixed		如果表达式计算结果为 true，则返回此项
 * @param	mixed		如果表达式计算结果为 false，则返回此项
 *
 * @return	mixed		此函数的第二或第三参数
 */
function iif ($expression, $returntrue, $returnfalse = '')
{
    return ($expression ? $returntrue : $returnfalse);
}
// #############################################################################
/**
 * 将一个大小的简写字符串版本转换为字节， 8M = 8388608
 *
 * @param	string			从需要的 ini_get 值转换为字节
 *
 * @return	integer			扩展到字节的值
 */
function ini_size_to_bytes ($value)
{
    $value = trim($value);
    $retval = intval($value);
    switch (strtolower($value[strlen($value) - 1])) {
        case 'g':
            $retval *= 1024;
        case 'm':
            $retval *= 1024;
        case 'k':
            $retval *= 1024;
            break;
    }
    return $retval;
}
// #############################################################################
/**
 * 尝试对可能包含 HTML 实体的数据做一个基于字符的 strlen 。
 * 默认情况下，它会将只转换数字实体，但可以可选转换&quot;，&lt;，等。
 * 如果可用，使用多字节函数计数。
 *
 * @param	string	要衡量的字符串
 * @param	boolean	如果 true, 对字符串运行 unhtmlspecialchars 进行计数 &quot;等 为一个字符
 *
 * @return	integer	字符串长度
 */
function skyuc_strlen ($string, $unhtmlspecialchars = false)
{
    $string = preg_replace('#&\#([0-9]+);#', '_', $string);
    if ($unhtmlspecialchars) {
        // don't try to translate unicode entities ever, as we want them to count as 1 (above)
        $string = unhtmlspecialchars($string, false);
    }
    if (function_exists('mb_strlen') and
     $length = @mb_strlen($string, 'utf-8')) {
        return $length;
    } //  UTF-8 版本的 strlen
else {
        return strlen(utf8_decode($string));
         //return strlen($string);
    }
}
// #############################################################################
/**
 * 格式化一个数字 ，用户自己的小数和许多字符
 *
 * @param	mixed	要格式化的数字: integer / 8MB / 16 GB / 6.0 KB / 3M / 5K / 等
 * @param	integer	要显示的小数位数
 * @param	boolean	基于字节的数字的特殊情况
 *
 * @return	mixed	The formatted number
 */
function skyuc_number_format ($number, $decimals = 0, $bytesize = false,
$decimalsep = null, $thousandsep = null)
{
    $type = '';
    if (empty($number)) {
        return 0;
    } else
        if (preg_match('#^(\d+(?:\.\d+)?)(?>\s*)([mkg])b?$#i',
        trim($number), $matches)) {
            switch (strtolower($matches[2])) {
                case 'g':
                    $number = $matches[1] * 1073741824;
                    break;
                case 'm':
                    $number = $matches[1] * 1048576;
                    break;
                case 'k':
                    $number = $matches[1] * 1024;
                    break;
                default:
                    $number = $matches[1] * 1;
            }
        }
    if ($bytesize) {
        if ($number >= 1073741824) {
            $number = $number / 1073741824;
            $decimals = 2;
            $type = ' GB';
        } else
            if ($number >= 1048576) {
                $number = $number / 1048576;
                $decimals = 2;
                $type = ' MB';
            } else
                if ($number >= 1024) {
                    $number = $number / 1024;
                    $decimals = 1;
                    $type = ' KB';
                } else {
                    $decimals = 0;
                    $type = ' bytes';
                }
    }
    if ($decimalsep === null) {
        $decimalsep = '.';
    }
    if ($thousandsep === null) {
        $thousandsep = ',';
    }
    return str_replace('_', '&nbsp;',
    number_format($number, $decimals, $decimalsep, $thousandsep)) . $type;
}
// #############################################################################
/**
 * 生成一个更强的随机密码
 *
 * @param	integer	密码长度
 */
function fetch_random_password ($length = 8)
{
    $password_characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz';
    $total_password_characters = strlen($password_characters) - 1;
    $digit = skyuc_rand(0, $length - 1);
    $newpassword = '';
    for ($i = 0; $i < $length; $i ++) {
        if ($i == $digit) {
            $newpassword .= chr(skyuc_rand(48, 57));
            continue;
        }
        $newpassword .= $password_characters{skyuc_rand(0,
        $total_password_characters)};
    }
    return $newpassword;
}
// #############################################################################
/**
 * hash 获取者, 注意这可能在将来改变成从 a-f0-9 到 a-z0-9
 *
 * @param	integer	hash值长度, 最多 40 个字符限制
 */
function fetch_random_string ($length = 32)
{
    $hash = sha1(
    TIMENOW . SESSION_HOST . microtime() . uniqid(mt_rand(), true) .
     implode('', @fstat(fopen(__FILE__, 'r'))));
    return substr($hash, 0, $length);
}
// #############################################################################
/**
 * 随机数生成器
 *
 * @param	integer	所需的最小值
 * @param	integer	所需的最大值
 * @param	mixed		随机数发生器种子 (如果不指定将生成一个新的种子)
 */
function skyuc_rand ($min = 0, $max = 0, $seed = -1)
{
    mt_srand(crc32(microtime()));
    if ($max and $max <= mt_getrandmax()) {
        $number = mt_rand($min, $max);
    } else {
        $number = mt_rand();
    }
    // 补种，这样任何此函数的外部调用不会产生相同的数字
    mt_srand();
    return $number;
}
/**
 * 生成随机的字串
 *
 * @param	int	$length 长度
 * @param	int	$numeric	1为数字,0为字母
 * @return string
 */
function random ($length, $numeric = 0)
{
    if ($numeric) {
        $hash = sprintf('%0' . $length . 'd',
        mt_rand(0, pow(10, $length) - 1));
    } else {
        $hash = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i ++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
    }
    return $hash;
}
/**
 * 随机生成数字串(用于生成充值卡)
 * $length 生成数字串的长度
 */
function random_card ($length)
{
    $hash = '';
    for ($i = 0; $i < $length; $i ++) {
        $hash .= skyuc_rand(0, 9);
    }
    return $hash;
}
// #############################################################################
/**
 * 获取文件扩展名
 *
 * @param	string	文件名
 *
 * @return	string	扩展名
 */
function file_extension ($filename)
{
    return substr(strrchr($filename, '.'), 1);
}
// #############################################################################
/**
 * 读电子邮件队列信息，并且把队列中的电子邮件发送给收件人
 */
function exec_mail_queue ()
{
    if ($GLOBALS['skyuc']->mailqueue !== null and
     $GLOBALS['skyuc']->mailqueue > 0 and
     $GLOBALS['skyuc']->options['usemailqueue']) {
        // 等待发送的电子邮件队列不为空
        if (! class_exists('Mail')) {
            require_once (DIR . '/includes/class_mail.php');
        }
        $mail = & QueueMail::fetch_instance();
        $mail->exec_queue();
    }
}
// #############################################################################
/**
 * 开始添加电子邮件到邮件队列中
 */
function skyuc_mail_start ()
{
    if (! class_exists('Mail')) {
        require_once (DIR . '/includes/class_mail.php');
    }
    $mail = & QueueMail::fetch_instance();
    $mail->set_bulk(true);
}
// #############################################################################
/**
 * 启动发送电子邮件的进程 - 立即发送或将其添加到邮件队列。
 *
 * @param	string	收件人电子邮件地址
 * @param	string	邮件主题
 * @param	string	邮件正文
 * @param	boolean	如果 true,不使用电子邮件队列，并立即发送
 * @param	boolean	 		0 普通邮件， 1 HTML邮件发送
 * @param	string	可选 名称或EMAIL，在 'From' 标头中使用
 * @param	string	用户自定义附加标头
 * @param	string	发件人用户名
 */
function skyuc_mail ($toemail, $subject, $message, $notsubscription = false,
$type = 0, $from = '', $uheaders = '', $username = '')
{
    if (empty($toemail)) {
        return false;
    }
    if (defined('DISABLE_MAIL')) {
        //  在 config.php  定义 DISABLE_MAIL ，例：@hotmail.com 只允许hotmail邮箱通过
        if (is_string(DISABLE_MAIL) and
         strpos(DISABLE_MAIL, '@') !== false) {
            // DISABLE_MAIL 包含电子邮件地址的部分,只让匹配的电子邮件通过
            if (strpos($toemail, DISABLE_MAIL) === false) {
                return true; // 电子邮件地址不匹配
            }
        } else {
            return true; // 禁用所有电子邮件地址
        }
    }
    if (! class_exists('Mail')) {
        require_once (DIR . '/includes/class_mail.php');
    }
    // 如果邮件编码不是utf8，创建字符集转换对象，转换编码
    if ($GLOBALS['skyuc']->options['mail_charset'] != 'UTF8') {
        $toemail = skyuc_iconv('UTF8',
        $GLOBALS['skyuc']->options['mail_charset'], $toemail);
        $subject = skyuc_iconv('UTF8',
        $GLOBALS['skyuc']->options['mail_charset'], $subject);
        $message = skyuc_iconv('UTF8',
        $GLOBALS['skyuc']->options['mail_charset'], $message);
        $uheaders = skyuc_iconv('UTF8',
        $GLOBALS['skyuc']->options['mail_charset'], $uheaders);
        $username = skyuc_iconv('UTF8',
        $GLOBALS['skyuc']->options['mail_charset'], $username);
        $from = skyuc_iconv('UTF8',
        $GLOBALS['skyuc']->options['mail_charset'], $from);
        $GLOBALS['skyuc']->options['site_name'] = skyuc_iconv('UTF8',
        $GLOBALS['skyuc']->options['mail_charset'],
        $GLOBALS['skyuc']->options['site_name']);
    } else {
        $GLOBALS['skyuc']->options['mail_charset'] = 'UTF-8';
    }
    if ($GLOBALS['skyuc']->options['usemailqueue'] and ! $notsubscription) {
        $mail = QueueMail::fetch_instance();
    } else
        if ($GLOBALS['skyuc']->options['use_smtp']) {
            $mail = new SmtpMail($GLOBALS['skyuc']);
        } else {
            $mail = new Mail($GLOBALS['skyuc']);
        }
    if (! $mail->start($toemail, $subject, $message, $type, $from, $uheaders,
    $username)) {
        return false;
    }
    return $mail->send();
}
// #############################################################################
/**
 * 停止将邮件添加到邮件队列并插入 mailqueue 稍后发送的数据
 */
function skyuc_mail_end ()
{
    if (! class_exists('Mail')) {
        require_once (DIR . '/includes/class_mail.php');
    }
    $mail = & QueueMail::fetch_instance();
    $mail->set_bulk(false);
}
// #############################################################################
define('FETCH_USERINFO_RANK', 0x02);
//define('FETCH_USERINFO_LOCATION',   0x04);
/**
 * 获取包含指定用户 的数组信息，如果找不到用户返回false
 *
 * 选项参数值:
 * 1 - 没有什么可做...
 * 2 - 获取用户相关等级信息
 * //4 - 处理用户的在线位置
 *
 * @param	integer	(ref) 用户 ID
 * @param	integer	位域选项 （请参见说明）
 *
 * @return	array	所请求的用户信息
 */
function fetch_userinfo (&$userid, $option = 0)
{
    global $usercache;
    if ($userid == $GLOBALS['skyuc']->userinfo['userid'] and $option != 0 and
     isset($usercache["$userid"])) {
        // 清除缓存， 如果需要更新用户信息
        unset($usercache["$userid"]);
    }
    $userid = intval($userid);
    // 如果它存在，则返回缓存的结果
    if (isset($usercache["$userid"])) {
        return $usercache["$userid"];
    }
    // 没有缓存可用-查询用户
    $users = $GLOBALS['db']->query_first_slave(
    '
		SELECT ' .
     iif(($option & FETCH_USERINFO_RANK), ' rank.*, ') . '
		user.*	' .
     '
		FROM ' .
     TABLE_PREFIX . 'users  AS user ' .
     iif(($option & FETCH_USERINFO_RANK),
    ' LEFT JOIN ' . TABLE_PREFIX .
     'user_rank AS rank ON (user.user_rank = rank.rank_id)') . "
		WHERE user.user_id = {$userid}
	");
    if (! $users) {
        return false;
    }
    $users['userid'] = $users['user_id'];
    //使用户变量是安全的，通过URL链接
    $users['urlusername'] = urlencode(
    unhtmlspecialchars($users['user_name']));
    $users['securitytoken_raw'] = sha1(
    $users['user_id'] . sha1($users['salt']) . sha1(COOKIE_SALT));
    $users['securitytoken'] = TIMENOW . '-' .
     sha1(TIMENOW . $users['securitytoken_raw']);
    $users['logouthash'] = & $users['securitytoken'];
    $usercache["$userid"] = $users;
    return $usercache["$userid"];
}
// #############################################################################
/**
 * 返回指定字符串的 Gzip 压缩版本
 *
 * @param	string	要压缩的文本
 * @param	integer	Gzip压缩等级 (1-10)
 *
 * @return	string
 */
function fetch_gzipped_text ($text, $level = 1)
{
    $returntext = $text;
    $encoding = null;
    if (function_exists('crc32') and function_exists('gzcompress') and
     ! $GLOBALS['skyuc']->nozip) {
        if (strpos(' ' . $_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false) {
            $encoding = 'x-gzip';
        }
        if (strpos(' ' . $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
            $encoding = 'gzip';
        }
        if ($encoding) {
            $GLOBALS['skyuc']->donegzip = true;
            header('Content-Encoding: ' . $encoding);
            if (function_exists('gzencode')) {
                $returntext = gzencode($text, $level);
            } else {
                $size = strlen($text);
                $crc = crc32($text);
                $returntext = "\x1f\x8b\x08\x00\x00\x00\x00\x00\x00\xff";
                $returntext .= substr(gzcompress($text, $level), 2, - 4);
                $returntext .= pack('V', $crc);
                $returntext .= pack('V', $size);
            }
        }
    }
    return $returntext;
}
// #############################################################################
/**
 * 设置基于 SKYUC 环境设置的 Cookie
 *
 * @param	string	Cookie 名称
 * @param	mixed		在 Cookie 中存储的值
 * @param	mixed	如果为 true，将不设置一个 cookie 的有效期(默认为一年)，否则应为多少秒
 * @param	boolean	允许安全 Cookie (SSL)
 * @param	boolean	在支持的浏览器中设置Cookie的'httponly'
 */
function skyuc_setcookie ($name, $value = '', $permanent = true, $allowsecure = true,
$httponly = false)
{
    if (defined('NOCOOKIES')) {
        return;
    }
    if ($permanent === true) {
        $expire = TIMENOW + 86400 * 365; //一年
    } else {
        $expire = (int) $permanent;
    }
    // IE for Mac 不支持 httponly
    $httponly = (($httponly and
     (is_browser('ie') and is_browser('mac'))) ? false : $httponly);
    // SSL 检查
    $secure = ((REQ_PROTOCOL === 'https' and $allowsecure) ? true : false);
    $name = COOKIE_PREFIX . $name;
    $filename = 'N/A';
    $linenum = 0;
    if (! headers_sent($filename, $linenum)) { // 考虑显示一条错误信息，如果他们不使用上述变量发送?
        if ($value === '' or $value === false) {
            // 这将尝试 销毁 在每个路径上的目录的 Cookie。
            // 例, 文件路径 = /test/skyuc/，将会销毁: /, /test, /test/, /test/skyuc, /test/skyuc/
            // 当更改 cookie 路径时，这应该有希望防止 Cookie 冲突。
            if ($_SERVER['PATH_INFO'] or
             $_ENV['PATH_INFO']) {
                $scriptpath = $_SERVER['PATH_INFO'] ? $_SERVER['PATH_INFO'] : $_ENV['PATH_INFO'];
            } else
                if ($_SERVER['REDIRECT_URL'] or $_ENV['REDIRECT_URL']) {
                    $scriptpath = $_SERVER['REDIRECT_URL'] ? $_SERVER['REDIRECT_URL'] : $_ENV['REDIRECT_URL'];
                } else {
                    $scriptpath = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_ENV['PHP_SELF'];
                }
            $scriptpath = preg_replace(
            array('#/[^/]+\.php$#i',
            '#/(' .
             preg_quote($GLOBALS['skyuc']->config['Misc']['admincpdir'], '#') .
             ')(/|$)#i'), '', $scriptpath);
            $dirarray = explode('/', preg_replace('#/+$#', '', $scriptpath));
            $alldirs = '';
            $havepath = false;
            if (! defined('SKIP_AGGRESSIVE_LOGOUT')) {
                // 发送多条 header 信息引起了与少数的服务器问题尤其是与 IIS。
                // 定义 SKIP_AGGRESSIVE_LOGOUT 减少返回的 Cookie 标头的数量。
                foreach ($dirarray as $thisdir) {
                    $alldirs .= "$thisdir";
                    if ($alldirs == $GLOBALS['skyuc']->options['cookiepath'] or
                     "$alldirs/" == $GLOBALS['skyuc']->options['cookiepath']) {
                        $havepath = true;
                    }
                    if (! empty($thisdir)) {
                        // 尝试销毁没有 / 结尾
                        exec_setcookie($name, $value,
                        $expire, $alldirs,
                        $GLOBALS['skyuc']->options['cookiedomain'], $secure,
                        $httponly);
                    }
                    $alldirs .= "/";
                    exec_setcookie($name, $value, $expire, $alldirs,
                    $GLOBALS['skyuc']->options['cookiedomain'], $secure,
                    $httponly);
                }
            }
            if ($havepath == false) {
                exec_setcookie($name, $value, $expire,
                $GLOBALS['skyuc']->options['cookiepath'],
                $GLOBALS['skyuc']->options['cookiedomain'], $secure, $httponly);
            }
        } else {
            exec_setcookie($name, $value, $expire,
            $GLOBALS['skyuc']->options['cookiepath'],
            $GLOBALS['skyuc']->options['cookiedomain'], $secure, $httponly);
        }
    } else
        if (empty($GLOBALS['skyuc']->db->explain)) {
            trigger_error('can\'t set cookies.' . $filename . $linenum,
            E_USER_WARNING);
        }
}
// #############################################################################
/**
 * 调用 PHP的 setcookie() 或者发送 原生标头(如果 'httponly' 为true)
 * 应该仅能通过 skyuc_setcookie() 调用。
 *
 * @param	string	名称
 * @param	string	值
 * @param	int		过期
 * @param	string	路径
 * @param	string	域名
 * @param	boolean	安全
 * @param	boolean	HTTP-only cookie
 *
 * @return	boolean	 成功返回 True
 */
function exec_setcookie ($name, $value, $expires, $path = '', $domain = '',
$secure = false, $httponly = false)
{
    if ($httponly and $value) {
        // Cookie 名称和值不能包含任何列出的字符
        foreach (array(",", ";", " ", "\t", "\r", "\n", "\013", "\014") as $bad_char) {
            if (strpos($name, $bad_char) !== false or
             strpos($value, $bad_char) !== false) {
                return false;
            }
        }
        // name and value
        $cookie = "Set-Cookie: $name=" . urlencode($value);
        // expiry
        $cookie .= ($expires > 0 ? '; expires=' .
         gmdate('D, d-M-Y H:i:s', $expires) . ' GMT' : '');
        // path
        $cookie .= ($path ? "; path=$path" : '');
        // domain
        $cookie .= ($domain ? "; domain=$domain" : '');
        // secure
        $cookie .= ($secure ? '; secure' : '');
        // httponly
        $cookie .= ($httponly ? '; HttpOnly' : '');
        header($cookie, false);
        return true;
    } else {
        return setcookie($name, $value, $expires, $path, $domain, $secure);
    }
}
// #############################################################################
/**
 * 标记一个我们要发送给客户端的字符串，但是不改变它们
 *
 * @param	string	要标记的字符串
 *
 * @return	string	字符串紧随其后的MD5哈希
 */
function sign_client_string ($string, $extra_entropy = '')
{
    if (preg_match('#[\x00-\x1F\x80-\xFF]#s', $string)) {
        $string = base64_encode($string);
        $prefix = 'B64:';
    } else {
        $prefix = '';
    }
    return $prefix . sha1($string . sha1(COOKIE_SALT) . $extra_entropy) .
     $string;
}
// #############################################################################
/**
 * 验证一个客户端字符串 ，返回没有哈希的字符串原型
 *
 * @param	string	要验证的客户端字符串
 *
 * @return	string|boolean	没有哈希的字符串 ，失败时返回 false
 */
function verify_client_string ($string, $extra_entropy = '')
{
    if (substr($string, 0, 4) == 'B64:') {
        $firstpart = substr($string, 4, 40);
        $return = substr($string, 44);
        $decode = true;
    } else {
        $firstpart = substr($string, 0, 40);
        $return = substr($string, 40);
        $decode = false;
    }
    if (sha1($return . sha1(COOKIE_SALT) . $extra_entropy) === $firstpart) {
        return ($decode ? base64_decode($return) : $return);
    }
    return false;
}
// #############################################################################
/**
 * 验证一个安全标识的有效性
 *
 * @param	string	从 REQUEST 数据的安全标识
 * @param	string	在哈希值中使用的安全标识
 *
 * @return	boolean	在正确的 TTL（Time to Live生存时间）内,如果哈希值匹配，返回 True
 */
function verify_security_token ($request_token, $user_token)
{
    // 这是为了向下兼容 ，以后的 标识有 TIMENOW 前缀
    if (strpos($request_token, '-') === false) {
        return ($request_token === $user_token);
    }
    list($time, $token) = explode('-', $request_token);
    if ($token !== sha1($time . $user_token)) {
        return false;
    }
    // 一个标识仅在三小时内有效
    if ($time <= TIMENOW - 10800) {
        $GLOBALS['skyuc']->GPC['securitytoken'] = 'timeout';
        return false;
    }
    return true;
}
// #############################################################################
/**
 * 暂停执行和重定向到指定的 URL
 *
 * @param	string	目标 URL
 */
function exec_header_redirect ($url)
{
    $url = create_full_url($url);
    $url = str_replace('&amp;', '&', $url); // 防止可能发生奇怪的东西
    if (strpos($url, "\r\n") !== false) {
        trigger_error(
        "Header may not contain more than a single header, new line detected.",
        E_USER_ERROR);
    }
    header("Location: $url", 0, 302);
    if ($GLOBALS['skyuc']->options['addheaders'] and
     (SAPI_NAME == 'cgi' or SAPI_NAME == 'cgi-fcgi')) {
        header('Status: 302 Found');
    }
    if (defined('NOSHUTDOWNFUNC')) {
        exec_shut_down();
    }
    exit();
}
// #############################################################################
/**
 * 转换一个相对URL为绝对URL。不以'/'开头的URL假定为在 SKYUC 主目录下。
 *
 * @param	string	相对URL
 *
 * @param	string	绝对URL
 */
function create_full_url ($url)
{
    // 遵守执行 HTTP 1.1
    if (! preg_match('#^[a-z]+(?<!about|javascript|vbscript|data)://#i',
    $url)) {
        // 确保我们得到众多服务器设置正确的值
        if ($_SERVER['HTTP_HOST'] or $_ENV['HTTP_HOST']) {
            $http_host = ($_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_ENV['HTTP_HOST']);
        } else
            if ($_SERVER['SERVER_NAME'] or $_ENV['SERVER_NAME']) {
                $http_host = ($_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : $_ENV['SERVER_NAME']);
            }
        // if we don't have this, then this isn't going to work correctly,
        // so let's assume that we're going to be OK with just a relative URL
        if ($http_host = trim($http_host)) {
            $method = REQ_PROTOCOL . '://';
            if ($url{0} != '/') {
                if (($dirpath = dirname(SCRIPT . 'i')) != '/') {
                    if ($dirpath == '\\') {
                        $dirpath = '/';
                    } else {
                        $dirpath .= '/';
                    }
                }
            } else {
                $dirpath = '';
            }
            $dirpath .= $url;
            $url = $method . $http_host . $dirpath;
        }
    }
    return $url;
}
// #############################################################################
/**
 * 设置各种时间和日期相关的变量
 *
 * 设置 $timediff, $datenow, $timenow, $copyrightyear
 */
function fetch_time_data ()
{
    global $timediff, $datenow, $timenow, $copyrightyear;
    // 默认时区
    $GLOBALS['skyuc']->userinfo['tzoffset'] = $GLOBALS['skyuc']->options['timezoneoffset'];
    if (substr($GLOBALS['skyuc']->userinfo['tzoffset'], 0, 1) != '-') {
        // 时区数为正时，添加  + 符号
        $GLOBALS['skyuc']->userinfo['tzoffset'] = '+' .
         $GLOBALS['skyuc']->userinfo['tzoffset'];
    }
    //  gmdate bug 的一些东西
    $GLOBALS['skyuc']->options['hourdiff'] = (date('Z', TIMENOW) / 3600 -
     $GLOBALS['skyuc']->userinfo['tzoffset']) * 3600;
    if ($GLOBALS['skyuc']->userinfo['tzoffset']) {
        if ($GLOBALS['skyuc']->userinfo['tzoffset'] > 0 and
         strpos($GLOBALS['skyuc']->userinfo['tzoffset'], '+') === false) {
            $GLOBALS['skyuc']->userinfo['tzoffset'] = '+' .
             $GLOBALS['skyuc']->userinfo['tzoffset'];
        }
        if (abs($GLOBALS['skyuc']->userinfo['tzoffset']) == 1) {
            $timediff = ' ' . $GLOBALS['skyuc']->userinfo['tzoffset'] . ' hour';
        } else {
            $timediff = ' ' . $GLOBALS['skyuc']->userinfo['tzoffset'] . ' hours';
        }
    } else {
        $timediff = '';
    }
    $datenow = skyuc_date($GLOBALS['skyuc']->options['date_format'], TIMENOW);
    $timenow = skyuc_date($GLOBALS['skyuc']->options['time_format'], TIMENOW);
    $copyrightyear = skyuc_date('Y', TIMENOW, false, false);
}
// #############################################################################
/**
 * 格式化 UNIX 时间戳为人易读的字符串
 *
 * 注意： 如果 skyuc_date() 调用了一个 $GLOBALS['skyuc']->options[] 之外的日期格式,
 * 设置 $locale 为 false， 除非你在 skyuc_date() 调用中动态设置  date() 和 strftime() 的格式
 *
 * @param	string	日期格式字符串（与PHP的 date()函数语法相同）
 * @param	integer	Unix 时间戳
 * @param	boolean	如果为 true, 尝试显示像 "Yesterday, 12pm" 替代完整的日期字符串
 * @param	boolean	如果为 true, 使用 strftime() 生成指定日期
 * @param	boolean	如果为 true, 不调整用户的调整时间 .. (认为 gmdate 替换 date!)
 * @param	boolean	如果为 true, 使用 gmstrftime() 、 gmdate() 替换 strftime() 和 date()
 *
 * @return	string	格式化的日期字符串
 */
function skyuc_date ($format, $timestamp = TIMENOW, $doyestoday = false, $locale = false,
$adjust = true, $gmdate = false)
{
    $hourdiff = $GLOBALS['skyuc']->options['hourdiff'];
    if ($locale) {
        if ($gmdate) {
            $datefunc = 'gmstrftime';
        } else {
            $datefunc = 'strftime';
        }
    } else {
        if ($gmdate) {
            $datefunc = 'gmdate';
        } else {
            $datefunc = 'date';
        }
    }
    if (! $adjust) {
        $hourdiff = 0;
    }
    $timestamp_adjusted = max(0, $timestamp - $hourdiff);
    //if ($format == $GLOBALS['skyuc']->options['date_format'] AND $doyestoday AND $GLOBALS['skyuc']->options['yestoday'])
    if ($doyestoday and $GLOBALS['skyuc']->options['yestoday']) {
        if ($GLOBALS['skyuc']->options['yestoday'] == 1) {
            if (! defined('TODAYDATE')) {
                define('TODAYDATE',
                skyuc_date('n-j-Y', TIMENOW, false, false));
                define('YESTDATE',
                skyuc_date('n-j-Y', TIMENOW - 86400, false, false));
                define('TOMDATE',
                skyuc_date('n-j-Y', TIMENOW + 86400, false, false));
            }
            $datetest = @date('n-j-Y', $timestamp - $hourdiff);
            if ($datetest == TODAYDATE) {
                $returndate = $GLOBALS['_LANG']['today'];
            } else
                if ($datetest == YESTDATE) {
                    $returndate = $GLOBALS['_LANG']['yesterday'];
                } else {
                    $returndate = $datefunc($format, $timestamp_adjusted);
                }
        } else {
            $timediff = TIMENOW - $timestamp;
            if ($timediff >= 0) {
                if ($timediff < 120) {
                    $returndate = $GLOBALS['_LANG']['1_minute_ago'];
                } else
                    if ($timediff < 3600) {
                        $returndate = sprintf(
                        $GLOBALS['_LANG']['x_minutes_ago'],
                        intval($timediff / 60));
                    } else
                        if ($timediff < 7200) {
                            $returndate = $GLOBALS['_LANG']['1_hour_ago'];
                        } else
                            if ($timediff < 86400) {
                                $returndate = sprintf(
                                $GLOBALS['_LANG']['x_hours_ago'],
                                intval($timediff / 3600));
                            } else
                                if ($timediff < 172800) {
                                    $returndate = $GLOBALS['_LANG']['1_day_ago'];
                                } else
                                    if ($timediff < 604800) {
                                        $returndate = sprintf(
                                        $GLOBALS['_LANG']['x_days_ago'],
                                        intval($timediff / 86400));
                                    } else
                                        if ($timediff < 1209600) {
                                            $returndate = $GLOBALS['_LANG']['1_week_ago'];
                                        } else
                                            if ($timediff < 3024000) {
                                                $returndate = sprintf(
                                                $GLOBALS['_LANG']['x_weeks_ago'],
                                                intval($timediff / 604900));
                                            } else {
                                                $returndate = $datefunc(
                                                $format, $timestamp_adjusted);
                                            }
            } else {
                $returndate = $datefunc($format, $timestamp_adjusted);
            }
        }
    } else {
        $returndate = $datefunc($format, $timestamp_adjusted);
    }
    return $returndate;
}
// #############################################################################
/**
 * 返回一个字符串，HTML实体转换回原来的字符
 *
 * @param	string	要将解析的字符串
 * @param	boolean	将HTML 实体转换回 Unicode 字符吗？
 *
 * @return	string
 */
function unhtmlspecialchars ($text, $doUniCode = false)
{
    if ($doUniCode) {
        $text = preg_replace('/&#([0-9]+);/esiU', "convert_int_to_utf8('\\1')",
        $text);
    }
    return str_replace(array('&lt;', '&gt;', '&quot;', '&amp;'),
    array('<', '>', '"', '&'), $text);
}
// #############################################################################
/**
 * 转换一个整数到一个UTF-8字符的字符串
 *
 * @param	integer	要转换的整数
 *
 * @return	string
 */
function convert_int_to_utf8 ($intval)
{
    $intval = intval($intval);
    switch ($intval) {
        // 1 byte, 7 bits
        case 0:
            return chr(0);
        case ($intval & 0x7F):
            return chr($intval);
        // 2 bytes, 11 bits
        case ($intval & 0x7FF):
            return chr(0xC0 | (($intval >> 6) & 0x1F)) .
             chr(0x80 | ($intval & 0x3F));
        // 3 bytes, 16 bits
        case ($intval & 0xFFFF):
            return chr(0xE0 | (($intval >> 12) & 0x0F)) .
             chr(0x80 | (($intval >> 6) & 0x3F)) .
             chr(0x80 | ($intval & 0x3F));
        // 4 bytes, 21 bits
        case ($intval & 0x1FFFFF):
            return chr(0xF0 | ($intval >> 18)) .
             chr(0x80 | (($intval >> 12) & 0x3F)) .
             chr(0x80 | (($intval >> 6) & 0x3F)) .
             chr(0x80 | ($intval & 0x3F));
    }
}
// #############################################################################
/**
 * 发送不缓存 HTTP 标头
 *
 * @param	boolean	如果为 true，发送 content-type 标头
 */
function exec_nocache_headers ($sendcontent = true)
{
    static $sentheaders;
    if (! $sentheaders) {
        @header("Expires: 0"); // Date in the past
        #@header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
        #@header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
        @header(
        "Cache-Control: private, post-check=0, pre-check=0, max-age=0", false);
        @header("Pragma: no-cache"); // HTTP/1.0
        if ($sendcontent) {
            @header('Content-Type: text/html;charset=utf-8');
        }
    }
    $sentheaders = true;
}
// #############################################################################
/**
 * 从数据库读取网站设置，然后将值保存到数据存储
 *
 * 从设置表中读取内容后, 本函数重新生成 $GLOBALS['skyuc']->options 数组，
 * 然后序列化数组并保存结果到 数据存储项目'options'
 *
 * @return	array	 $GLOBALS['skyuc']->options 数组
 */
function build_options ()
{
    $GLOBALS['skyuc']->options = array();
    $res = $GLOBALS['db']->query_read(
    'SELECT code, value  FROM ' . TABLE_PREFIX . 'setting	WHERE parent_id > 0');
    while ($set = $GLOBALS['db']->fetch_array($res)) {
        $GLOBALS['skyuc']->options[$set['code']] = $set['value'];
    }
    if (! isset($GLOBALS['skyuc']->options['skyuc_version'])) {
        // 如果没有版本号则默认为3.0
        $GLOBALS['skyuc']->options['skyuc_version'] = '3.0';
    }
    //限定语言项
    if (empty($GLOBALS['skyuc']->options['lang'])) {
        $GLOBALS['skyuc']->options['lang'] = 'zh-cn'; // 默认语言为简体中文
    }
    if (empty($GLOBALS['skyuc']->options['integrate_code'])) {
        $GLOBALS['skyuc']->options['integrate_code'] = 'skyuc'; // 默认的会员整合插件为 skyuc
    }
    if (substr($GLOBALS['skyuc']->options['cookiepath'], - 1, 1) != '/') {
        $GLOBALS['skyuc']->options['cookiepath'] .= '/';
        $GLOBALS['db']->query_write(
        "
			UPDATE " . TABLE_PREFIX .
         "setting
			SET value = '" .
         $GLOBALS['db']->escape_string(
        $GLOBALS['skyuc']->options['cookiepath']) . "'
			WHERE code = 'cookiepath'
		");
    }
    build_datastore('options', serialize($GLOBALS['skyuc']->options), 1);
    return $GLOBALS['skyuc']->options;
}
// #############################################################################
/**
 * 将指定的数据保存到数据存储
 *
 * @param	string	将要保存的数据存储项的名称
 * @param	mixed		要保存的数据
 * @param        	整型 1 或 0 ，此值是否自动 反解析序列串 检索
 */
function build_datastore ($title = '', $data = '', $unserialize = 0)
{
    if ($title != '') {
        /*insert query*/
        $GLOBALS['db']->query_write(
        "
			REPLACE INTO " . TABLE_PREFIX .
         "datastore
				(title, data, unserialize)
			VALUES
				('" .
         $GLOBALS['db']->escape_string(trim($title)) . "', '" .
         $GLOBALS['db']->escape_string(trim($data)) . "', " .
         intval($unserialize) . ")
		");
        if (method_exists($GLOBALS['skyuc']->datastore, 'build')) {
            $GLOBALS['skyuc']->datastore->build($title, $data);
        }
    }
}
// #############################################################################
/**
 * 更新 Linux/Unix	 负载均衡 数据存储
 */
function update_loadavg ()
{
    if (! isset($GLOBALS['skyuc']->loadcache)) {
        $GLOBALS['skyuc']->loadcache = array();
    }
    if (function_exists('exec') and $stats = @exec('uptime 2>&1') and
     trim($stats) != '' and
     preg_match('#: ([\d.,]+),?\s+([\d.,]+),?\s+([\d.,]+)$#', $stats, $regs)) {
        $GLOBALS['skyuc']->loadcache['loadavg'] = $regs[2];
    } else
        if (@file_exists('/proc/loadavg') and
         $filestuff = @file_get_contents('/proc/loadavg')) {
            list($loadavg) = explode(' ', $filestuff);
            $GLOBALS['skyuc']->loadcache['loadavg'] = $loadavg;
        } else {
            $GLOBALS['skyuc']->loadcache['loadavg'] = 0;
        }
    $GLOBALS['skyuc']->loadcache['lastcheck'] = TIMENOW;
    build_datastore('loadcache', serialize($GLOBALS['skyuc']->loadcache), 1);
}
// #############################################################################
/**
 * 系统退出后执行一般清理，如运行关闭查询
 */
function exec_shut_down ()
{
    if (SKYUC_AREA == 'Install' or SKYUC_AREA == 'Upgrade') {
        return;
    }
    $GLOBALS['db']->unlock_tables();
    if (is_object($GLOBALS['skyuc']->session)) {
        if (! defined('LOCATION_BYPASS')) {
            //$GLOBALS['skyuc']->session->set('inthread', $threadinfo['threadid']);
        }
        if ($GLOBALS['skyuc']->session->vars['loggedin'] == 1 and
         ! $GLOBALS['skyuc']->session->created) {
            // 如果 loggedin = 1, 在登陆后更改值为2
            $GLOBALS['skyuc']->session->set('loggedin', 2);
        }
        $GLOBALS['skyuc']->session->save();
    }
    // 随机对 sessions 表进行删除操作
    if (mt_rand(0, 2) == 2 or (time() % 2) == 0) {
        $timeout = intval(
        TIMENOW - $GLOBALS['skyuc']->options['cookietimeout'] * 60);
        $timeout_cp = iif($GLOBALS['skyuc']->options['timeoutcontrolpanel'],
        $timeout, TIMENOW - 3600);
        $GLOBALS['db']->query_write(
        'DELETE FROM ' . TABLE_PREFIX .
         'session WHERE adminid=0 AND lastactivity < ' . $timeout);
        $GLOBALS['db']->query_write(
        'DELETE FROM ' . TABLE_PREFIX .
         'session WHERE adminid=1 AND lastactivity < ' . $timeout_cp);
        $GLOBALS['db']->query_write(
        'DELETE FROM ' . TABLE_PREFIX . 'cpsession WHERE dateline < ' .
         $timeout_cp);
        // 一小时后图像验证码过期
        $GLOBALS['db']->query_write(
        'DELETE FROM ' . TABLE_PREFIX . 'humanverify WHERE dateline < ' .
         (TIMENOW - 3600));
    }
    if (is_array($GLOBALS['db']->shutdownqueries)) {
        $GLOBALS['db']->hide_errors();
        foreach ($GLOBALS['db']->shutdownqueries as $name => $query) {
            if (! empty($query)) {
                $GLOBALS['db']->query_write($query);
            }
        }
        $GLOBALS['db']->show_errors();
    }
    exec_mail_queue();
    // 请确保关闭数据库连接
    // 如果 NOSHUTDOWNFUNC 是定义的，那么此函数应该始终是在输出数据前最后一个被调用的
    if (defined('NOSHUTDOWNFUNC')) {
        $GLOBALS['db']->close();
    }
    $GLOBALS['db']->shutdownqueries = array();
     // 再见 ！
}
/**
 * 截取UTF-8编码下字符串的函数
 *
 * @param   string      $str        被截取的字符串
 * @param   int         $length     截取的长度
 * @param   bool        $append     是否附加省略号
 *
 * @return  string
 */
function sub_str ($str, $length = 0, $append = false)
{
    $str = trim($str);
    $strlength = strlen($str);
    if ($length == 0 || $length >= $strlength) {
        return $str;
    } elseif ($length < 0) {
        $length = $strlength + $length;
        if ($length < 0) {
            $length = $strlength;
        }
    }
    if (function_exists('mb_substr')) {
        $newstr = mb_substr($str, 0, $length, 'UTF-8');
    } elseif (function_exists('iconv_substr')) {
        $newstr = iconv_substr($str, 0, $length, 'UTF-8');
    } else {
        $newstr = substr($str, 0, $length);
    }
    if ($append && $str != $newstr) {
        $newstr .= '...';
    }
    return $newstr;
}
/**
 * 将IP转为数字
 *
 * @access  public
 * @param   string	$ip IPV4从0.0.0.0到255.255.255.255
 * @return  int			返回值范围-1到4294967294
 */
function ip2num ($cip)
{
    // 判断IP是否合法
    $cip = trim($cip);
    $gip = is_ip($cip) ? $cip : '0.0.0.0';
    $ip = explode('.', $gip);
    //$ipNum = $ip[0]*256*256*256 + $ip[1]*256*256 + $ip[2]*256 + $ip[3]-1;
    $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3] - 1;
    return $ipNum;
}
/**
 * 判断IP是否合法
 *
 * @access  public
 * @param   string	$ip IPV4从0.0.0.0到255.255.255.255
 * @return  bool
 */
function is_ip ($ip)
{
    $ip = explode('.', $ip);
    if (count($ip) != 4 && count($ip) != 6) {
        return false;
    }
    foreach ($ip as $ip_addr) {
        if (! is_numeric($ip_addr)) {
            return false;
        }
        if ($ip_addr < 0 || $ip_addr > 255) {
            return false;
        }
    }
    return true;
}
/**
 * 获取邮件模板
 *
 * @access  public
 * @param:  $tpl_name[string]       模板代码
 *
 * @return array
 */
function get_mail_template ($tpl_name)
{
    $sql = 'SELECT template_subject, is_html, template_content FROM ' .
     TABLE_PREFIX . 'template_mail' . " WHERE template_code = '$tpl_name'";
    return $GLOBALS['db']->query_first_slave($sql);
}
/**
 * 格式化价格
 *
 * @access  public
 * @param   float   $price  价格
 * @return  string
 */
function price_format ($price)
{
    switch ($GLOBALS['skyuc']->options['price_format']) {
        case 0:
            $price = number_format($price, 2, '.', '');
            break;
        case 1: // 保留不为 0 的尾数
            $price = preg_replace('/(.*)(\\.)([0-9]*?)0+$/',
            '\1\2\3', number_format($price, 2, '.', ''));
            if (substr($price, - 1) == '.') {
                $price = substr($price, 0, - 1);
            }
            break;
        case 2: // 不四舍五入，保留1位
            $price = substr(number_format($price, 2, '.', ''), 0,
            - 1);
            break;
        case 3: // 直接取整
            $price = intval($price);
            break;
        case 4: // 四舍五入，保留 1 位
            $price = number_format($price, 1, '.', '');
            break;
        case 5: // 先四舍五入，不保留小数
            $price = round($price);
            break;
    }
    return sprintf($GLOBALS['skyuc']->options['currency_format'], $price);
}
/**
 * 创建像这样的查询: "IN('a','b')";
 *
 * @access   public
 * @param    mix      $item_list      列表数组或字符串
 * @param    string   $field_name     字段名称
 *
 * @return   void
 */
function db_create_in ($item_list, $field_name = '')
{
    if (empty($item_list)) {
        return $field_name . " IN ('') ";
    } else {
        if (! is_array($item_list)) {
            $item_list = explode(',', $item_list);
        }
        $item_list = array_unique($item_list);
        $item_list_tmp = '';
        foreach ($item_list as $item) {
            if ($item !== '') {
                $item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
            }
        }
        if (empty($item_list_tmp)) {
            return $field_name . " IN ('') ";
        } else {
            return $field_name . ' IN (' . $item_list_tmp . ') ';
        }
    }
}
/**
 * 根据过滤条件获得排序的标记
 *
 * @access  public
 * @param   array   $filter
 * @return  array
 */
function sort_flag ($filter)
{
    $flag['tag'] = 'sort_' . preg_replace('/^.*\./', '', $filter['sort_by']);
    $flag['img'] = '<img src="images/' .
     ($filter['sort_order'] == "DESC" ? 'sort_desc.gif' : 'sort_asc.gif') .
     '"/>';
    return $flag;
}
/*
 * 错误提示信息
 */
function error_message ($msg)
{
    echo "<SCRIPT>alert(\"$msg\");history.go(-1)</SCRIPT>";
    exit();
}
/*
 * 关闭窗口
 */
function top_close ($msg)
{
    echo "<script>alert(\"$msg\");top.close();</script>";
    exit();
}
/*
 * 提示信息
 */
function window_message ($msg)
{
    echo "<script>alert(\"$msg\");</script>";
}
/*
 * 提示信息转到连接
 */
function go_message ($msg, $url = 'index.php')
{
    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
    echo "<script>alert(\"$msg\");parent.window.location.href='$url'</script>";
    exit();
}
/**
 * 清除指定后缀的模板缓存或编译文件
 *
 * @access  public
 * @param  string     $ext       文件后缀
 *
 * @return int        返回清除的文件个数
 */
function clear_tpl_files ($ext = '')
{
    $dirs = array();
    $dirs[] = DIR . '/data/compiled/';
    $dirs[] = DIR . '/data/compiled/admincp/';
    $str_len = strlen($ext);
    $count = 0;
    foreach ($dirs as $dir) {
        $folder = @opendir($dir);
        if ($folder == false) {
            continue;
        }
        while ($file = readdir($folder)) {
            if ($file == '.' || $file == '..' || $file == 'index.htm' ||
             $file == 'index.html') {
                continue;
            }
            if (is_file($dir . $file)) {
                // 如果有后缀判断后缀是否匹配
                if ($str_len > 0) {
                    if (strpos($file, $ext) !== false) {
                        if (@unlink($dir . $file)) {
                            $count ++;
                        }
                    }
                } else {
                    if (@unlink($dir . $file)) {
                        $count ++;
                    }
                }
            }
        }
        closedir($folder);
    }
    return $count;
}
/**
 * 初始化会员数据整合类
 *
 * @access  public
 * @return  object
 */
function &init_users ()
{
    $set_modules = false;
    static $cls = null;
    if ($cls != null) {
        return $cls;
    }
    include_once (DIR . '/includes/modules/integrates/' .
     $GLOBALS['skyuc']->options['integrate_code'] . '.php');
    $cfg = unserialize($GLOBALS['skyuc']->options['integrate_config']);
    $cls = new $GLOBALS['skyuc']->options['integrate_code']($cfg);
    return $cls;
}
//获得HTML里的文本
function html2text ($str)
{
    $str = preg_replace(
    "/<sty(.*)\\/style>|<scr(.*)\\/script>|<!--(.*)-->/isU", '', $str);
    $str = str_replace(array('<br />', '<br>', '<br/>'), "\n", $str);
    $str = strip_tags($str);
    return $str;
}
/**
 * 记录帐户变动
 * @param   int     $user_id        用户id
 * @param   float   $user_money     可用余额变动
 * @param   int     $pay_point     消费积分变动
 * @param   string  $change_desc    变动说明
 * @param   int     $change_type    变动类型：参见常量文件
 * @return  void
 */
function log_account_change ($user_id, $user_money = 0, $pay_point = 0,
$change_desc = '', $change_type = ACT_OTHER)
{
    // 插入帐户变动记录
    $account_log = array();
    $account_log[] = '(' . $user_id . ', ' . $user_money . ',' . $pay_point .
     ', ' . TIMENOW . ", '" . $GLOBALS['db']->escape_string($change_desc) .
     "', '" . $change_type . "')";
    $GLOBALS['db']->query_insert(TABLE_PREFIX . 'account_log',
    '(user_id, user_money, pay_point, change_time, change_desc, change_type)',
    $account_log);
    // 更新用户信息
    $sql = 'UPDATE ' . TABLE_PREFIX . 'users' .
     ' SET user_money = user_money + (' . $user_money .
     '), pay_point = pay_point + (' . $pay_point . ') WHERE user_id = ' .
     $user_id;
    $GLOBALS['db']->query_write($sql);
}
/**
 * 检查图片是否为远程地址
 *
 * @param string $url 网址
 *
 * @return boolean 远程图片返回 FALSE, 本地图片返回 TRUE
 */
function pic_parse_url ($url)
{
    /*
 	  $parse_url = @parse_url($url);
    return (empty($parse_url['scheme']) && empty($parse_url['host']));
    */
    // 两者均可
    return (strpos($url, 'http://') === false);
}
/**
 * 调用UCenter的函数
 *
 * @param   string  $func
 * @param   array   $params
 *
 * @return  mixed
 */
function uc_call ($func, $params = null)
{
    restore_error_handler();
    if (! function_exists($func)) {
        include_once (DIR . '/uc_client/client.php');
    }
    $res = call_user_func_array($func, $params);
    set_error_handler('exception_handler');
    return $res;
}
/**
 * 调用使用UCenter插件时的函数
 *
 * @param   string  $func
 * @param   array   $params
 *
 * @return  mixed
 */
function user_uc_call ($func, $params = null)
{
    if (isset($GLOBALS['skyuc']->options['integrate_code']) &&
     $GLOBALS['skyuc']->options['integrate_code'] == 'ucenter') {
        restore_error_handler();
        if (! function_exists($func)) {
            include_once (DIR . '/includes/functions_uc.php');
        }
        $res = call_user_func_array($func, $params);
        set_error_handler('exception_handler');
        return $res;
    } else {
        return;
    }
}
/**
 * error_handle回调函数
 *
 * @return
 */
function exception_handler ($errno, $errstr, $errfile, $errline)
{
    return;
}
// #############################################################################
/**
 * 浏览器检测系统 - 返回正在访问的浏览器是否为一个指定的。
 *
 * @param	string	浏览器名称 (opera, ie, mozilla, firebord, firefox... 等. - 参见 $is = array)
 * @param	float		true 结果,最低可接受的版本 （可选）
 *
 * @return	boolean
 */
function is_browser ($browser, $version = 0)
{
    static $is;
    if (! is_array($is)) {
        $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $is = array('opera' => 0, 'ie' => 0, 'mozilla' => 0, 'firebird' => 0,
        'firefox' => 0, 'camino' => 0, 'konqueror' => 0, 'safari' => 0,
        'webkit' => 0, 'webtv' => 0, 'netscape' => 0, 'mac' => 0);
        // 检测 opera
        # Opera/7.11 (Windows NT 5.1; U) [en]
        # Mozilla/4.0 (compatible; MSIE 6.0; MSIE 5.5; Windows NT 5.0) Opera 7.02 Bork-edition [en]
        # Mozilla/4.0 (compatible; MSIE 6.0; MSIE 5.5; Windows NT 4.0) Opera 7.0 [en]
        # Mozilla/4.0 (compatible; MSIE 5.0; Windows 2000) Opera 6.0 [en]
        # Mozilla/4.0 (compatible; MSIE 5.0; Mac_PowerPC) Opera 5.0 [en]
        if (strpos($useragent,
        'opera') !== false) {
            preg_match('#opera(/| )([0-9\.]+)#', $useragent, $regs);
            $is['opera'] = $regs[2];
        }
        // 检测 internet explorer
        # Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; Q312461)
        # Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.0.3705)
        # Mozilla/4.0 (compatible; MSIE 5.22; Mac_PowerPC)
        # Mozilla/4.0 (compatible; MSIE 5.0; Mac_PowerPC; e504460WanadooNL)
        if (strpos($useragent, 'msie ') !==
         false and ! $is['opera']) {
            preg_match('#msie ([0-9\.]+)#', $useragent, $regs);
            $is['ie'] = $regs[1];
        }
        // 检测 macintosh
        if (strpos($useragent, 'mac') !== false) {
            $is['mac'] = 1;
        }
        // 检测 safari
        # Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en-us) AppleWebKit/74 (KHTML, like Gecko) Safari/74
        # Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/51 (like Gecko) Safari/51
        # Mozilla/5.0 (Windows; U; Windows NT 6.0; en) AppleWebKit/522.11.3 (KHTML, like Gecko) Version/3.0 Safari/522.11.3
        # Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1C28 Safari/419.3
        # Mozilla/5.0 (iPod; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/3A100a Safari/419.3
        if (strpos($useragent,
        'applewebkit') !== false) {
            preg_match('#applewebkit/([0-9\.]+)#', $useragent, $regs);
            $is['webkit'] = $regs[1];
            if (strpos($useragent, 'safari') !== false) {
                preg_match('#safari/([0-9\.]+)#', $useragent, $regs);
                $is['safari'] = $regs[1];
            }
        }
        // 检测 konqueror
        # Mozilla/5.0 (compatible; Konqueror/3.1; Linux; X11; i686)
        # Mozilla/5.0 (compatible; Konqueror/3.1; Linux 2.4.19-32mdkenterprise; X11; i686; ar, en_US)
        # Mozilla/5.0 (compatible; Konqueror/2.1.1; X11)
        if (strpos($useragent, 'konqueror') !==
         false) {
            preg_match('#konqueror/([0-9\.-]+)#', $useragent, $regs);
            $is['konqueror'] = $regs[1];
        }
        // 检测 mozilla
        # Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.4b) Gecko/20030504 Mozilla
        # Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.2a) Gecko/20020910
        # Mozilla/5.0 (X11; U; Linux 2.4.3-20mdk i586; en-US; rv:0.9.1) Gecko/20010611
        if (strpos($useragent, 'gecko') !==
         false and ! $is['safari'] and ! $is['konqueror']) {
            // See bug #26926, this is for Gecko based products without a build
            $is['mozilla'] = 20090105;
            if (preg_match('#gecko/(\d+)#', $useragent, $regs)) {
                $is['mozilla'] = $regs[1];
            }
            // 检测 firebird / firefox
            # Mozilla/5.0 (Windows; U; WinNT4.0; en-US; rv:1.3a) Gecko/20021207 Phoenix/0.5
            # Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.4b) Gecko/20030516 Mozilla Firebird/0.6
            # Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.4a) Gecko/20030423 Firebird Browser/0.6
            # Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.6) Gecko/20040206 Firefox/0.8
            if (strpos(
            $useragent, 'firefox') !== false or
             strpos($useragent, 'firebird') !== false or
             strpos($useragent, 'phoenix') !== false) {
                preg_match(
                '#(phoenix|firebird|firefox)( browser)?/([0-9\.]+)#',
                $useragent, $regs);
                $is['firebird'] = $regs[3];
                if ($regs[1] == 'firefox') {
                    $is['firefox'] = $regs[3];
                }
            }
            // 检测 camino
            # Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en-US; rv:1.0.1) Gecko/20021104 Chimera/0.6
            if (strpos($useragent, 'chimera') !== false or
             strpos($useragent, 'camino') !== false) {
                preg_match('#(chimera|camino)/([0-9\.]+)#', $useragent, $regs);
                $is['camino'] = $regs[2];
            }
        }
        // 检测 web tv
        if (strpos($useragent, 'webtv') !== false) {
            preg_match('#webtv/([0-9\.]+)#', $useragent, $regs);
            $is['webtv'] = $regs[1];
        }
        // 检测 pre-gecko netscape
        if (preg_match('#mozilla/([1-4]{1})\.([0-9]{2}|[1-8]{1})#',
        $useragent, $regs)) {
            $is['netscape'] = "$regs[1].$regs[2]";
        }
    }
    // 过滤传入的浏览器名称
    $browser = strtolower($browser);
    if (substr($browser, 0, 3) == 'is_') {
        $browser = substr($browser, 3);
    }
    // 返回检测到浏览器的版本号，如果浏览器和 $browser 相同
    if ($is["$browser"]) {
        // $version 已经指定 - 仅返回版本号，如果检测的版本号 >= 指定的 $version
        if ($version) {
            if ($is["$browser"] >= $version) {
                return $is["$browser"];
            }
        } else {
            return $is["$browser"];
        }
    }
    // 不指定浏览器，或版本号太低
    return 0;
}
/**
 * 判断是否为搜索引擎蜘蛛
 *
 * @access  public
 * @return  string
 */
function is_spider ()
{
    static $spider = NULL;
    if ($spider !== NULL) {
        return $spider;
    }
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        $spider = '';
        return '';
    }
    $searchengine_bot = array('googlebot', 'mediapartners-google',
    'baiduspider+', 'msnbot', 'yodaobot', 'yahoo! slurp;',
    'yahoo! slurp china;', 'iaskspider', 'sogou web spider',
    'sogou push spider');
    $searchengine_name = array('GOOGLE', 'GOOGLE ADSENSE', 'BAIDU', 'MSN',
    'YODAO', 'YAHOO', 'Yahoo China', 'IASK', 'SOGOU', 'SOGOU');
    $spider = strtolower($_SERVER['HTTP_USER_AGENT']);

    foreach ($searchengine_bot as $key => $value) {
        if (strpos($spider, $value) !== false) {
            $spider = $searchengine_name["$key"];
            if (isset($GLOBALS['skyuc']->options['visit_stats']) &&
             $GLOBALS['skyuc']->options['visit_stats'] == 1) {
                    $arr = array();
                    $arr[] = "('" . TIMENOW . "', '" .
                     $GLOBALS['db']->escape_string($spider) .
                     "', 1)  ON DUPLICATE KEY UPDATE count=count+1";
                    $GLOBALS['db']->query_insert(TABLE_PREFIX . 'searchengine',
                    '(date,	searchengine,	count)', $arr);
            }
            return $spider;
        }
    }
    $spider = '';
    return '';
}
/**
 * 获得客户端的操作系统
 *
 * @access  private
 * @return  void
 */
function get_os ($agent = '')
{
    if (! isset($_SERVER['HTTP_USER_AGENT']) && $agent == '') {
        return 'Unknown';
    }
    $agent = ($agent == '') ? strtolower($_SERVER['HTTP_USER_AGENT']) : strtolower(
    $agent);
    $os = '';
    if (strpos($agent, 'win') !== false) {
        if (strpos($agent, 'nt 5.1') !== false) {
            $os = 'Windows XP';
        } elseif (strpos($agent, 'nt 5.2') !== false) {
            $os = 'Windows 2003';
        } elseif (strpos($agent, 'nt 5.0') !== false) {
            $os = 'Windows 2000';
        } elseif (strpos($agent, 'nt 6.0') !== false) {
            $os = 'Windows Vista';
        } elseif (strpos($agent, 'nt') !== false) {
            $os = 'Windows NT';
        } elseif (strpos($agent, 'win 9x') !== false &&
         strpos($agent, '4.90') !== false) {
            $os = 'Windows ME';
        } elseif (strpos($agent, '98') !== false) {
            $os = 'Windows 98';
        } elseif (strpos($agent, '95') !== false) {
            $os = 'Windows 95';
        } elseif (strpos($agent, '32') !== false) {
            $os = 'Windows 32';
        } elseif (strpos($agent, 'ce') !== false) {
            $os = 'Windows CE';
        }
    } elseif (strpos($agent, 'linux') !== false) {
        $os = 'Linux';
    } elseif (strpos($agent, 'unix') !== false) {
        $os = 'Unix';
    } elseif (strpos($agent, 'sun') !== false &&
     strpos($agent, 'os') !== false) {
        $os = 'SunOS';
    } elseif (strpos($agent, 'ibm') !== false &&
     strpos($agent, 'os') !== false) {
        $os = 'IBM OS/2';
    } elseif (strpos($agent, 'mac') !== false &&
     strpos($agent, 'pc') !== false) {
        $os = 'Macintosh';
    } elseif (strpos($agent, 'powerpc') !== false) {
        $os = 'PowerPC';
    } elseif (strpos($agent, 'aix') !== false) {
        $os = 'AIX';
    } elseif (strpos($agent, 'hpux') !== false) {
        $os = 'HPUX';
    } elseif (strpos($agent, 'netbsd') !== false) {
        $os = 'NetBSD';
    } elseif (strpos($agent, 'bsd') !== false) {
        $os = 'BSD';
    } elseif (strpos($agent, 'osf1') !== false) {
        $os = 'OSF1';
    } elseif (strpos($agent, 'irix') !== false) {
        $os = 'IRIX';
    } elseif (strpos($agent, 'freebsd') !== false) {
        $os = 'FreeBSD';
    } elseif (strpos($agent, 'teleport') !== false) {
        $os = 'teleport';
    } elseif (strpos($agent, 'flashget') !== false) {
        $os = 'flashget';
    } elseif (strpos($agent, 'webzip') !== false) {
        $os = 'webzip';
    } elseif (strpos($agent, 'offline') !== false) {
        $os = 'offline';
    } elseif (strpos($agent, 'soso') !== false) {
        $os = 'Sosospider';
    } elseif (strpos($agent, 'google') !== false) {
        $os = 'Googlebot';
    } elseif (strpos($agent, 'baidu') !== false) {
        $os = 'Baiduspider';
    } elseif (strpos($agent, 'yahoo') !== false) {
        $os = 'Yahoo! Slurp';
    } elseif (strpos($agent, 'sogou') !== false) {
        $os = 'Sogou web spider';
    } else {
        $os = 'Unknown';
    }
    return $os;
}
/**
 * 获得浏览器名称和版本
 *
 * @access  public
 * @return  string
 */
function getbrowser ($agent = '')
{
    global $_SERVER;
    if (! isset($_SERVER['HTTP_USER_AGENT']) && $agent == '') {
        return 'Unknow browser';
    }
    $agent = ($agent == '') ? strtolower($_SERVER['HTTP_USER_AGENT']) : strtolower(
    $agent);
    $browser = '';
    $browser_ver = '';
    if (preg_match('/MSIE\s([^\s|;]+)/i', $agent, $regs)) {
        $browser = 'Internet Explorer';
        $browser_ver = $regs[1];
    } elseif (preg_match('/FireFox\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'FireFox';
        $browser_ver = $regs[1];
    } elseif (preg_match('/Maxthon/i', $agent, $regs)) {
        $browser = '(Internet Explorer ' . $browser_ver . ') Maxthon';
        $browser_ver = '';
    } elseif (preg_match('/Opera[\s|\/]([^\s]+)/i', $agent, $regs)) {
        $browser = 'Opera';
        $browser_ver = $regs[1];
    } elseif (preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $regs)) {
        $browser = 'OmniWeb';
        $browser_ver = $regs[2];
    } elseif (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Netscape';
        $browser_ver = $regs[2];
    } elseif (preg_match('/safari\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Safari';
        $browser_ver = $regs[1];
    } elseif (preg_match('/NetCaptor\s([^\s|;]+)/i', $agent, $regs)) {
        $browser = '(Internet Explorer ' . $browser_ver . ') NetCaptor';
        $browser_ver = $regs[1];
    } elseif (preg_match('/Lynx\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Lynx';
        $browser_ver = $regs[1];
    } elseif (preg_match('/([^\s]+)soso([^\s]+)/i', $agent, $regs)) {
        $browser = 'Sosospider';
        $browser_ver = '';
    } elseif (preg_match('/([^\s]+)google([^\s]+)/i', $agent, $regs)) {
        $browser = 'Googlebot';
        $browser_ver = '';
    } elseif (preg_match('/([^\s]+)baidu([^\s]+)/i', $agent, $regs)) {
        $browser = 'Baiduspider';
        $browser_ver = '';
    } elseif (preg_match('/([^\s]+)yahoo([^\s]+)/i', $agent, $regs)) {
        $browser = 'Yahoo! Slurp';
        $browser_ver = '';
    } elseif (preg_match('/([^\s]+)sogou([^\s]+)/i', $agent, $regs)) {
        $browser = 'Sogou web spider';
        $browser_ver = '';
    } elseif (preg_match('/chrome\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Chrome';
        $browser_ver = $regs[1];
    }
    if (! empty($browser)) {
        return addslashes($browser . ' ' . $browser_ver);
    } else {
        return 'Unknow browser';
    }
}
// #############################################################################
/**
 * 返回用于 UPDATE 或 INSERT 查询字符串，在负载的字段的大查询中使用...
 *
 * @param	array	数组(字段名 = 值)  - array('userid' => 21, 'username' => 'John Doe')
 * @param	string	数据应保存的表的名称
 * @param	string	要在查询字符串中添加 SQL 条件 		- WHERE id=1
 * @param	array		应忽略addslashes从 $queryvalues 数组的字段名称的数组
 *
 * @return	string	UPDATE 或 INSERT 查询语句
 */
function fetch_query_sql ($queryvalues, $table, $condition = '', $exclusions = '')
{
    if (empty($exclusions)) {
        $exclusions = array();
    }
    $numfields = count($queryvalues);
    $i = 1;
    if (! empty($condition)) {
        $querystring = "\n### UPDATE QUERY GENERATED BY fetch_query_sql() ###\n";
        foreach ($queryvalues as $fieldname => $value) {
            if (! preg_match('#^\w+$#', $fieldname)) {
                continue;
            }
            $querystring .= "\t`$fieldname` = " .
             iif(is_numeric($value) or in_array($fieldname, $exclusions),
            "'$value'", "'" . $GLOBALS['db']->escape_string($value) . "'") .
             iif($i ++ == $numfields, "\n", ",\n");
        }
        return "UPDATE " . TABLE_PREFIX . "$table SET\n$querystring$condition";
    } else {
        #$fieldlist = $table . 'id, ';
        #$valuelist = 'NULL, ';
        $fieldlist = '';
        $valuelist = '';
        foreach ($queryvalues as $fieldname => $value) {
            if (! preg_match('#^\w+$#', $fieldname)) {
                continue;
            }
            $endbit = iif($i ++ == $numfields, '', ', ');
            $fieldlist .= "`" . $fieldname . "`" . $endbit;
            $valuelist .= iif(
            is_numeric($value) or in_array($fieldname, $exclusions), "'$value'",
            "'" . $GLOBALS['db']->escape_string($value) . "'") . $endbit;
        }
        return "\n### INSERT QUERY GENERATED BY fetch_query_sql() ###\nINSERT INTO " .
         TABLE_PREFIX . "$table\n\t($fieldlist)\nVALUES\n\t($valuelist)";
    }
}
// #############################################################################
/**
 * 构建短语
 *
 * 本函数是实际上就是一个 sprintf 包装， 但使得更容易识别的短语代码，不会出错，
 * 如果没有额外的参数。第一个参数是文本短语（不限数量），以下参数是变量，将为这一词语解析。
 *
 * @param	string	文本的短语
 * @param	mixed	首先要插入的变量
 * @param	mixed	第N个变量插入
 *
 * @return	string	词组的解析
 */
function construct_phrase ()
{
    $args = func_get_args();
    $numargs = sizeof($args);
    if ($numargs == 2 and is_array($args[1])) {
        $args = $args[1];
        $numargs = sizeof($args);
    }
    // 如果我们只有一个参数，只返回参数
    if ($numargs < 2) {
        return $args[0];
    } else {
        // 对此函数的第一个参数调用 sprintf()
        $phrase = @call_user_func_array('sprintf', $args);
        if ($phrase !== false) {
            return $phrase;
        } else {
            // 如果失败，添加一些额外的参数，用于调试
            for ($i = $numargs; $i < 10; $i ++) {
                $args["$i"] = "[ARG:$i UNDEFINED]";
            }
            if ($phrase = @call_user_func_array('sprintf', $args)) {
                return $phrase;
            } // 如果它仍不起作用，只返回 没有解析的 文本
else {
                return $args[0];
            }
        }
    }
}
/**
 * 获取远程文件请求头部信息
 *
 * @param		$url			远程地址
 * @param		$maxsize	原始编码
 * @return	binary
 */
function fetch_head_request ($url)
{
    require_once (DIR . '/includes/class_vurl.php');
    $vurl = new SKYUC_vURL($GLOBALS['skyuc']);
    return $vurl->fetch_head($url);
}
/**
 * 获取远程文件(使用下载类库)
 *
 * @param		$url			远程地址
 * @param		$maxsize	原始编码
 * @param		$type     远程文件类型，默认为空不限制，'img'等于图片
 * @return	binary
 */
function fetch_body_request ($url, $charset = '', $type = '')
{
    require_once (DIR . '/includes/class_vurl.php');
    $vurl = new SKYUC_vURL($GLOBALS['skyuc']);
    $maxsize = 512000; // 1024* 500 = 500KB
    $dieonmaxsize = false;
    $returnheaders = false;
    if (empty($url)) {
        return '';
    }
    $body = $vurl->fetch_body($url, $maxsize, $dieonmaxsize, $returnheaders);
    if ($type == 'img') {
        //下载图片
        if (validate($url, 7)) {
            return $body;
        }
    } else {
        //普通文件，不限制格式
        if (! empty($charset) &&
         in_array(strtolower($charset), array('gb2312', 'gbk', 'big5'))) {
            $body = skyuc_iconv($charset, 'utf-8', $body);
        }
        return $body;
    }
}
/**
 * 获取允许上传文件大小
 *
 * @return	int	单位 (字节) byte
 */
function fetch_max_upload_size ()
{
    if ($temp = @ini_get('upload_max_filesize')) {
        if (preg_match('#^\s*(\d+(?:\.\d+)?)\s*(?:([mkg])b?)?\s*$#i', $temp,
        $matches)) {
            switch (strtolower($matches[2])) {
                case 'g':
                    return $matches[1] * 1073741824;
                case 'm':
                    return $matches[1] * 1048576;
                case 'k':
                    return $matches[1] * 1024;
                default: // no g, m, k, gb, mb, kb
                    return $matches[1] * 1;
            }
        } else {
            return $temp;
        }
    } else {
        return 10485760; // 约 10 MB :)
    }
}
/**
 * 文件或目录权限检查函数
 *
 * @access          public
 * @param           string  $file_path   文件路径
 * @param           bool    $rename_prv  是否在检查修改权限时检查执行rename()函数的权限
 *
 * @return          int     返回值的取值范围为{0 <= x <= 15}，每个值表示的含义可由四位二进制数组合推出。
 * 返回值在二进制计数法中，四位由高到低分别代表
 * 可执行rename()函数权限、可对文件追加内容权限、可写入文件权限、可读取文件权限。
 */
function file_mode_info ($file_path)
{
    error_reporting(0);
    /* 如果不存在，则不可读、不可写、不可改 */
    if (! file_exists($file_path)) {
        return false;
    }
    $mark = 0;
    if (DIRECTORY_SEPARATOR == '\\') {
        /* 测试文件 */
        $test_file = $file_path . '/cf_test.txt';
        /* 如果是目录 */
        if (is_dir($file_path)) {
            /* 检查目录是否可读 */
            $dir = opendir($file_path);
            if ($dir === false) {
                return $mark; //如果目录打开失败，直接返回目录不可修改、不可写、不可读
            }
            if (readdir($dir) !== false) {
                $mark ^= 1; //目录可读 001，目录不可读 000
            }
            closedir($dir);
            /* 检查目录是否可写 */
            $fp = fopen($test_file, 'wb');
            if ($fp === false) {
                return $mark; //如果目录中的文件创建失败，返回不可写。
            }
            if (fwrite($fp, 'directory access testing.') !== false) {
                $mark ^= 2; //目录可写可读011，目录可写不可读 010
            }
            fclose($fp);
            unlink($test_file);
            /* 检查目录是否可修改 */
            $fp = fopen($test_file, 'ab+');
            if ($fp === false) {
                return $mark;
            }
            if (fwrite($fp, "modify test.\r\n") !== false) {
                $mark ^= 4;
            }
            fclose($fp);
            /* 检查目录下是否有执行rename()函数的权限 */
            if (rename($test_file, $test_file) !== false) {
                $mark ^= 8;
            }
            unlink($test_file);
        } /* 如果是文件 */
elseif (is_file($file_path)) {
            /* 以读方式打开 */
            $fp = fopen($file_path, 'rb');
            if ($fp) {
                $mark ^= 1; //可读 001
            }
            fclose($fp);
            /* 试着修改文件 */
            $fp = fopen($file_path, 'ab+');
            if ($fp && fwrite($fp, '') !== false) {
                $mark ^= 6; //可修改可写可读 111，不可修改可写可读011...
            }
            fclose($fp);
            /* 检查目录下是否有执行rename()函数的权限 */
            if (rename($test_file, $test_file) !== false) {
                $mark ^= 8;
            }
        }
    } else {
        if (is_readable($file_path)) {
            $mark ^= 1;
        }
        if (is_writable($file_path)) {
            $mark ^= 14;
        }
    }
    return $mark;
}
/**
 * 检查目标文件夹是否存在，如果不存在则自动创建该目录
 *
 * @access      public
 * @param       string      folder     目录路径。不能使用相对于网站根目录的URL
 *
 * @return      bool
 */
function make_dir ($folder)
{
    $reval = false;
    if (! file_exists($folder)) {
        /* 如果目录不存在则尝试创建该目录 */
        @umask(0);
        /* 将目录路径拆分成数组 */
        preg_match_all('/([^\/]*)\/?/i', $folder, $atmp);
        /* 如果第一个字符为/则当作物理路径处理 */
        $base = ($atmp[0][0] == '/') ? '/' : '';
        /* 遍历包含路径信息的数组 */
        foreach ($atmp[1] as $val) {
            if ('' != $val) {
                $base .= $val;
                if ('..' == $val || '.' == $val) {
                    /* 如果目录为.或者..则直接补/继续下一个循环 */
                    $base .= '/';
                    continue;
                }
            } else {
                continue;
            }
            $base .= '/';
            if (! file_exists($base)) {
                /* 尝试创建目录，如果创建失败则继续循环 */
                if (@mkdir(rtrim($base, '/'), 0777)) {
                    @chmod($base, 0777);
                    $reval = true;
                }
            }
        }
    } else {
        /* 路径已经存在。返回该路径是不是一个目录 */
        $reval = is_dir($folder);
    }
    clearstatcache();
    return $reval;
}
/**
 * 多功能验证函数
 *
 * @access      public
 * @param       string      $l1   要验证的信息
 * @param				int					$l2		验证类型
 *
 * @return      bool
 */
function validate ($l1, $l2)
{
    // $l1:str, $l2:类型
    switch ($l2) {
        case '0': // 数字，字母，逗号，杠，下划线，[，]
            $l3 = '^[a-zA-Z0-9\,\/\-\_\[\]]+$';
            break;
        case '1': // 字母
            $l3 = '^[A-Za-z]+$';
            break;
        case '2': // 匹配数字
            $l3 = '^\d+$';
            break;
        case '3': // 字母，数字，下划线，杠
            $l3 = '^[A-Za-z0-9\_\-]+$';
            break;
        case '4': // Email
            $l3 = '^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$';
            break;
        case '5': // url
            $l3 = '^(http|https|ftp):(\/\/|\\\\)(([\w\/\\\+\-~`@:%])+\.)+([\w\/\\\.\=\?\+\-~`@\':!%#]|(&amp;)|&)+';
            break;
        case '6': // IP
            $l3 = '^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$';
            break;
        case '7': // 图片连接 http://www.example.com/xxx.jpg
            $l4 = 'png|gif|jpg|jpeg|bmp';
            $l3 = '^(http|https|ftp):(\/\/|\\\\)(([\w\/\\\+\-~`@:%])+\.)+([\w\/\\\.\=\?\+\-~`@\':!%#]|(&amp;)|&)+\.(' .
             $l4 . ')$';
            break;
        case '8': // 日期格式
            $l3 = '^((((1[6-9]|[2-9]\d)\d{2})-(0?[13578]|1[02])-(0?[1-9]|[12]\d|3[01]))|(((1[6-9]|[2-9]\d)\d{2})-(0?[13456789]|1[012])-(0?[1-9]|[12]\d|30))|(((1[6-9]|[2-9]\d)\d{2})-0?2-(0?[1-9]|1\d|2[0-8]))|(((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))-0?2-29-))(| (20|21|22|23|[0-1]?\d):[0-5]?\d:[0-5]?\d)$';
            break;
        default: // 自定义正则
            $l3 = $l2;
            break;
    }
    return preg_match("/{$l3}/i", $l1);
}
// #############################################################################
/**
 * 转义 Javascript的字符串中的引号
 *
 * @param	string	要准备的 Javascript 字符串
 * @param	string	引号 的类型(单或双引号）
 *
 * @return	string
 */
function addslashes_js ($text, $quotetype = "'")
{
    if ($quotetype == "'") {
        // 单引号
        $replaced = str_replace(array('\\', '\'', "\n", "\r"),
        array('\\\\', "\\'", "\\n", "\\r"), $text);
    } else {
        // 双引号
        $replaced = str_replace(array('\\', '"', "\n", "\r"),
        array('\\\\', "\\\"", "\\n", "\\r"), $text);
    }
    $replaced = preg_replace('#(-(?=-))#', "-$quotetype + $quotetype",
    $replaced);
    $replaced = preg_replace('#</script#i',
    "<\\/scr$quotetype + {$quotetype}ipt", $replaced);
    return $replaced;
}
/**
 * 递归方式的对变量中的特殊字符进行转义
 *
 * @access  public
 * @param   mix     $value
 *
 * @return  mix
 */
function addslashes_deep ($value)
{
    if (empty($value)) {
        return $value;
    } else {
        return is_array($value) ? array_map('addslashes_deep', $value) : addslashes(
        $value);
    }
}
/**
 * 将对象成员变量或者数组的特殊字符进行转义
 *
 * @access   public
 * @param    mix        $obj      对象或者数组
 * @author   Xuan Yan
 *
 * @return   mix                  对象或者数组
 */
function addslashes_deep_obj ($obj)
{
    if (is_object($obj) == true) {
        foreach ($obj as $key => $val) {
            $obj->$key = addslashes_deep($val);
        }
    } else {
        $obj = addslashes_deep($obj);
    }
    return $obj;
}
/**
 * 递归方式的对变量中的特殊字符去除转义
 *
 * @access  public
 * @param   mix     $value
 *
 * @return  mix
 */
function stripslashes_deep ($value)
{
    if (empty($value)) {
        return $value;
    } else {
        return is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes(
        $value);
    }
}
/**
 * 将一个字串中含有全角的数字字符、字母、空格或'%+-()'字符转换为相应半角字符
 *
 * @access  public
 * @param   string       $str         待转换字串
 *
 * @return  string       $str         处理后字串
 */
function make_semiangle ($str)
{
    $arr = array('０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',
    '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9', 'Ａ' => 'A',
    'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E', 'Ｆ' => 'F', 'Ｇ' => 'G',
    'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J', 'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M',
    'Ｎ' => 'N', 'Ｏ' => 'O', 'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S',
    'Ｔ' => 'T', 'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y',
    'Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd', 'ｅ' => 'e',
    'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i', 'ｊ' => 'j', 'ｋ' => 'k',
    'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n', 'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q',
    'ｒ' => 'r', 'ｓ' => 's', 'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w',
    'ｘ' => 'x', 'ｙ' => 'y', 'ｚ' => 'z', '（' => '(', '）' => ')', '〔' => '[',
    '〕' => ']', '【' => '[', '】' => ']', '〖' => '[', '〗' => ']', '“' => '[',
    '”' => ']', '‘' => '[', '’' => ']', '｛' => '{', '｝' => '}', '《' => '<',
    '》' => '>', '％' => '%', '＋' => '+', '—' => '-', '－' => '-', '～' => '-',
    '：' => ':', '。' => '.', '、' => ',', '，' => '.', '、' => '.', '；' => ',',
    '？' => '?', '！' => '!', '…' => '-', '‖' => '|', '”' => '"', '’' => '`',
    '‘' => '`', '｜' => '|', '〃' => '"', '　' => ' ');
    return strtr($str, $arr);
}
/**
 * 检查文件类型
 *
 * @access      public
 * @param       string      filename            文件名
 * @param       string      realname            真实文件名
 * @param       string      limit_ext_types     允许的文件类型
 * @return      string
 */
function check_file_type ($filename, $realname = '', $limit_ext_types = '')
{
    if ($realname) {
        $extname = strtolower(
        substr($realname, strrpos($realname, '.') + 1));
    } else {
        $extname = strtolower(
        substr($filename, strrpos($filename, '.') + 1));
    }
    if ($limit_ext_types &&
     stristr($limit_ext_types, '|' . $extname . '|') === false) {
        return '';
    }
    $str = $format = '';
    $file = @fopen($filename, 'rb');
    if ($file) {
        $str = @fread($file, 0x400); // 读取前 1024 个字节
        @fclose($file);
    } else {
        if (stristr($filename, DIR) === false) {
            if ($extname == 'jpg' || $extname == 'jpeg' || $extname == 'gif' ||
             $extname == 'png' || $extname == 'doc' || $extname == 'xls' ||
             $extname == 'txt' || $extname == 'zip' || $extname == 'rar' ||
             $extname == 'ppt' || $extname == 'pdf' || $extname == 'rm' ||
             $extname == 'mid' || $extname == 'wav' || $extname == 'bmp' ||
             $extname == 'swf' || $extname == 'chm' || $extname == 'sql' ||
             $extname == 'cert') {
                $format = $extname;
            }
        } else {
            return '';
        }
    }
    if ($format == '' && strlen($str) >= 2) {
        if (substr($str, 0, 4) == 'MThd' && $extname != 'txt') {
            $format = 'mid';
        } elseif (substr($str, 0, 4) == 'RIFF' && $extname == 'wav') {
            $format = 'wav';
        } elseif (substr($str, 0, 3) == "\xFF\xD8\xFF") {
            $format = 'jpg';
        } elseif (substr($str, 0, 4) == 'GIF8' && $extname != 'txt') {
            $format = 'gif';
        } elseif (substr($str, 0, 8) == "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A") {
            $format = 'png';
        } elseif (substr($str, 0, 2) == 'BM' && $extname != 'txt') {
            $format = 'bmp';
        } elseif ((substr($str, 0, 3) == 'CWS' ||
         substr($str, 0, 3) == 'FWS') && $extname != 'txt') {
            $format = 'swf';
        } elseif (substr($str, 0, 4) == "\xD0\xCF\x11\xE0") { // D0CF11E == DOCFILE == Microsoft Office Document
            if (substr($str, 0x200, 4) == "\xEC\xA5\xC1\x00" ||
             $extname == 'doc') {
                $format = 'doc';
            } elseif (substr($str, 0x200, 2) == "\x09\x08" || $extname == 'xls') {
                $format = 'xls';
            } elseif (substr($str, 0x200, 4) == "\xFD\xFF\xFF\xFF" ||
             $extname == 'ppt') {
                $format = 'ppt';
            }
        } elseif (substr($str, 0, 4) == "PK\x03\x04") {
            $format = 'zip';
        } elseif (substr($str, 0, 4) == 'Rar!' && $extname != 'txt') {
            $format = 'rar';
        } elseif (substr($str, 0, 4) == "\x25PDF") {
            $format = 'pdf';
        } elseif (substr($str, 0, 3) == "\x30\x82\x0A") {
            $format = 'cert';
        } elseif (substr($str, 0, 4) == 'ITSF' && $extname != 'txt') {
            $format = 'chm';
        } elseif (substr($str, 0, 4) == "\x2ERMF") {
            $format = 'rm';
        } elseif ($extname == 'sql') {
            $format = 'sql';
        } elseif ($extname == 'txt') {
            $format = 'txt';
        }
    }
    if ($limit_ext_types &&
     stristr($limit_ext_types, '|' . $format . '|') === false) {
        $format = '';
    }
    return $format;
}
//PHP判断字符编码类型是否为UTF－8型
// Returns true if $string is valid UTF-8 and false otherwise.
function is_utf8 ($string)
{
    // From http://w3.org/International/questions/qa-forms-utf-8.html
    return preg_match(
    '%^(?:
	[\x09\x0A\x0D\x20-\x7E] # ASCII
	| [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte
	| \xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs
	| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
	| \xED[\x80-\x9F][\x80-\xBF] # excluding surrogates
	| \xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
	| [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
	| \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
	)*$%xs', $string);
} // function is_utf8
/**
 * 字符编码转换
 *
 * @access  public
 * @return  object
 */
function skyuc_iconv ($source_lang, $target_lang, $source_string = '')
{
    static $chs = NULL;
    /* 如果字符串为空或者字符串不需要转换，直接返回 */
    if ($source_lang == $target_lang || $source_string == '' ||
     preg_match("/[\x80-\xFF]+/", $source_string) == 0) {
        return $source_string;
    }
    if ($chs === NULL) {
        require_once (DIR . '/includes/class_iconv.php');
        $chs = new Chinese(DIR);
    }
    return $chs->Convert($source_lang, $target_lang, $source_string);
}
/**
 * 字符编码转换，支持数组
 *
 * @access  public
 * @param   mix     $value
 *
 * @return  mix
 */
function skyuc_iconv_array ($source_lang, $target_lang, &$source_string = '')
{
    if (empty($source_string)) {
        return $source_string;
    } else {
        if (is_array($source_string)) {
            foreach ($source_string as $k => $v) {
                $source_string[$k] = skyuc_iconv($source_lang, $target_lang,
                $v);
            }
        } else {
            skyuc_iconv($source_lang, $target_lang, $source_string);
        }
    }
}
/**
 * IP地址转换
 *
 * @access  public
 * @return  object
 */
function skyuc_geoip ($ip)
{
    static $fp = NULL, $offset = array(), $index = NULL;
    $ip = gethostbyname($ip);
    $ipdot = explode('.', $ip);
    $ip = pack('N', ip2long($ip));
    $ipdot[0] = (int) $ipdot[0];
    $ipdot[1] = (int) $ipdot[1];
    if ($ipdot[0] == 10 || $ipdot[0] == 127 ||
     ($ipdot[0] == 192 && $ipdot[1] == 168) ||
     ($ipdot[0] == 172 && ($ipdot[1] >= 16 && $ipdot[1] <= 31))) {
        return 'LAN';
    }
    if ($fp === NULL) {
        $fp = fopen(DIR . '/includes/data/ipdata.dat', 'rb');
        if ($fp === false) {
            return 'Invalid IP data file';
        }
        $offset = unpack('Nlen', fread($fp, 4));
        if ($offset['len'] < 4) {
            return 'Invalid IP data file';
        }
        $index = fread($fp, $offset['len'] - 4);
    }
    $length = $offset['len'] - 1028;
    $start = unpack('Vlen',
    $index[$ipdot[0] * 4] . $index[$ipdot[0] * 4 + 1] .
     $index[$ipdot[0] * 4 + 2] . $index[$ipdot[0] * 4 + 3]);
    for ($start = $start['len'] * 8 + 1024; $start < $length; $start += 8) {
        if ($index{$start} . $index{$start + 1} . $index{$start + 2} .
         $index{$start + 3} >= $ip) {
            $index_offset = unpack('Vlen',
            $index{$start + 4} . $index{$start + 5} . $index{$start + 6} . "\x0");
            $index_length = unpack('Clen', $index{$start + 7});
            break;
        }
    }
    fseek($fp, $offset['len'] + $index_offset['len'] - 1024);
    $area = fread($fp, $index_length['len']);
    fclose($fp);
    $fp = NULL;
    return $area;
}
/**
 * 将上传文件转移到指定位置
 *
 * @param string $file_name
 * @param string $target_name
 * @return blob
 */
function move_upload_file ($file_name, $target_name = '')
{
    if (function_exists('move_uploaded_file')) {
        if (move_uploaded_file($file_name, $target_name)) {
            @chmod($target_name, 0755);
            return true;
        } else
            if (copy($file_name, $target_name)) {
                @chmod($target_name, 0755);
                return true;
            }
    } elseif (copy($file_name, $target_name)) {
        @chmod($target_name, 0755);
        return true;
    }
    return false;
}
/**
 * 取得当前的域名(带http://)
 *
 * @access  public
 *
 * @return  string      当前的域名
 */
function get_domain ()
{
    /* 协议 */
    $protocol = get_http();
    /* 域名或IP地址 */
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    } elseif (isset($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
    } else {
        /* 端口 */
        if (isset($_SERVER['SERVER_PORT'])) {
            $port = ':' . $_SERVER['SERVER_PORT'];
            if ((':80' == $port && 'http://' == $protocol) ||
             (':443' == $port && 'https://' == $protocol)) {
                $port = '';
            }
        } else {
            $port = '';
        }
        if (isset($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'] . $port;
        } elseif (isset($_SERVER['SERVER_ADDR'])) {
            $host = $_SERVER['SERVER_ADDR'] . $port;
        }
    }
    return $protocol . $host;
}
/**
 * 获得当前环境的 URL 地址
 *
 * @access  public
 *
 * @return  string
 */
function get_url ()
{
    $curr = strpos($_SERVER['PHP_SELF'],
    $GLOBALS['skyuc']->config['Misc']['admincpdir']) !== false ? preg_replace(
    '/(.*)(' . $GLOBALS['skyuc']->config['Misc']['admincpdir'] . ')(\/?)(.)*/i',
    '\1', dirname($_SERVER['PHP_SELF'])) : dirname($_SERVER['PHP_SELF']);
    $root = str_replace('\\', '/', $curr);
    if (substr($root, - 1) != '/') {
        $root .= '/';
    }
    return get_domain() . $root;
}
/**
 * 获得当前环境的 HTTP 协议方式
 *
 * @access  public
 *
 * @return  void
 */
function get_http ()
{
    return (isset($_SERVER['HTTPS']) &&
     (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';
}
// ###################### 开始 skyuc_mktime #######################
function skyuc_mktime ($hours = 0, $minutes = 0, $seconds = 0, $month = 0, $day = 0,
$year = 0)
{
    // some stuff for the gmdate bug
    $GLOBALS['skyuc']->options['hourdiff'] = (date('Z', TIMENOW) / 3600 -
     $GLOBALS['skyuc']->userinfo['tzoffset']) * 3600;
    return mktime(intval($hours), intval($minutes), intval($seconds),
    intval($month), intval($day), intval($year)) +
     $GLOBALS['skyuc']->options['hourdiff'];
}
// ###################### 开始 skyuc_gmdate #####################
function skyuc_gmdate ($format, $timestamp, $doyestoday = false, $locale = true)
{
    return skyuc_date($format, $timestamp, $doyestoday, $locale, false, true);
}
/**
 * 尝试转换一个字符到最接近非扩展 ascii
 *
 * @param string $chr							- 要转换的字符
 * @returns string								- 转换后的字符
 */
function fetch_try_to_ascii ($chr)
{
    $conv = array('À' => 'a', 'Á' => 'a', 'Â' => 'a', 'Ã' => 'a', 'Ä' => 'a',
    'Å' => 'a', 'Æ' => 'e', 'Ç' => 'c', 'È' => 'e', 'É' => 'e', 'Ê' => 'e',
    'Ë' => 'e', 'Ì' => 'i', 'Í' => 'i', 'Î' => 'i', 'Ï' => 'i', 'Ð' => 'd',
    'Ñ' => 'n', 'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ö' => 'o',
    'Ø' => 'o', 'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'Ý' => 'y',
    'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
    'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i',
    'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o',
    'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u',
    'ü' => 'u', 'ý' => 'y', 'ÿ' => 'y');
    return (isset($conv[$chr]) ? $conv[$chr] : $chr);
}
/**
 * 普通密码MD5加密;
 *
 * @access  public
 * @param   string      $pass       需要编译的原始密码
 * @param		int					$len			MD5位长,为空是32位
 *
 * @return  string
 */
function compile_password ($pass, $len = '')
{
    if (empty($len) || $len != '16') {
        return md5($pass);
    } else {
        return substr(md5($pass), 8, - 8);
    }
}
/**
 * 对javascript中escape的URL解码
 *
 * @access  public
 * @param   string     需要解码的字符串
 *
 * @return  string
 */
function phpUnescape ($escstr)
{
    preg_match_all("/%u[0-9A-Za-z]{4}|%.{2}|[0-9a-zA-Z.+-_]+/", $escstr,
    $matches);
    $ar = &$matches[0];
    $c = "";
    foreach ($ar as $val) {
        if (substr($val, 0, 1) != "%") {
            $c .= $val;
        } elseif (substr($val, 1, 1) != "u") {
            $x = hexdec(substr($val, 1, 2));
            $c .= chr($x);
        } else {
            $val = intval(substr($val, 2), 16);
            if ($val < 0x7F) // 0000-007F
{
                $c .= chr($val);
            } elseif ($val < 0x800) // 0080-0800
{
                $c .= chr(0xC0 | ($val / 64));
                $c .= chr(0x80 | ($val % 64));
            } else // 0800-FFFF
{
                $c .= chr(0xE0 | (($val / 64) / 64));
                $c .= chr(0x80 | (($val / 64) % 64));
                $c .= chr(0x80 | ($val % 64));
            }
        }
    }
    return $c;
}
/**
 * 加密解密函数
 *
 * @access  public
 *
 * @return  string  $string
 */
function mcryptcode ($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    $ckey_length = 4;
    $key = md5($key ? $key : 'DEWEI');
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0,
    $ckey_length) : substr(md5(microtime()), - $ckey_length)) : '';
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(
    substr($string, $ckey_length)) : sprintf('%010d',
    $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) .
     $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for ($i = 0; $i <= 255; $i ++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for ($j = $i = 0; $i < 256; $i ++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $string_length; $i ++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(
        ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 ||
         substr($result, 0, 10) - time() > 0) &&
         substr($result, 10, 16) ==
         substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}
?>
