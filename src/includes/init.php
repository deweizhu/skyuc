<?php
/**
 * SKYUC! 前台公用文件
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

error_reporting ( E_ALL ^ E_NOTICE );

if (! defined ( 'SKYUC_AREA' ) and ! defined ( 'THIS_SCRIPT' )) {
	echo 'SKYUC_AREA and THIS_SCRIPT must be defined to continue';
	exit ();
}

if (isset ( $_REQUEST ['GLOBALS'] ) or isset ( $_FILES ['GLOBALS'] )) {
	die ( 'Request tainting attempted.' );
}
// Force PHP 5.3.0+ to take time zone information from OS
if (version_compare(phpversion(), '5.3.0', '>='))
{
	@date_default_timezone_set(date_default_timezone_get());
}
@ini_set ( 'pcre.backtrack_limit', - 1 );
// start the page generation timer
define ( 'TIMESTART', microtime ( true ) );
// 设置当前 UNIX TIMESTAMP
define ( 'TIMENOW', isset ( $_SERVER ['REQUEST_TIME'] ) ? $_SERVER ['REQUEST_TIME'] : time () );

// 定义 环境设置
define ( 'SAFEMODE', (@ini_get ( 'safe_mode' ) == 1 or strtolower ( @ini_get ( 'safe_mode' ) ) == 'on') ? true : false );
define ( 'PHP_SELF', isset ( $_SERVER ['PHP_SELF'] ) ? $_SERVER ['PHP_SELF'] : $_SERVER ['SCRIPT_NAME'] );

// 定义当前目录路径
if (! defined ( 'CWD' )) {
	define ( 'CWD', (($getcwd = getcwd ()) ? $getcwd : '.') );
}

/* 检查是否已安装 */
if (! is_file ( CWD . '/data/install.lock' ) && ! defined ( 'NO_CHECK_INSTALL' )) {
	header ( "Location: ./install/index.php\n" );
	exit ();
}

// #############################################################################
// fetch the core includes
require (CWD . '/includes/class_core.php');
set_error_handler ( 'skyuc_error_handler' );

// 初始化数据注册表
$skyuc = new Registry ();

// 分析配置 ini 文件
$skyuc->fetch_config ();

if (CWD == '.') {
	// getcwd() 失败，因此我们需要在 config.php 设置网站完整的绝对路径
	if (! empty ( $skyuc->config ['Misc'] ['sitepath'] )) {
		define ( 'DIR', $skyuc->config ['Misc'] ['sitepath'] );
	} else {
		trigger_error ( '<strong>Configuration</strong>: You must insert a value for <strong>sitepath</strong> in config.php', E_USER_ERROR );
	}
} else {
	define ( 'DIR', CWD );
}

if (! empty ( $skyuc->config ['Misc'] ['datastorepath'] )) {
	define ( 'DATASTORE', $skyuc->config ['Misc'] ['datastorepath'] );
} else {
	define ( 'DATASTORE', DIR . '/includes/datastore' );
}

if ($skyuc->debug) {
	restore_error_handler ();
}

// #############################################################################
// 加载数据库类
switch (strtolower ( $skyuc->config ['Database'] ['dbtype'] )) {
	// 加载标准 MySQL 类
	case 'mysql' :
	case '' :
		{
			if ($skyuc->debug and ($skyuc->input->clean_gpc ( 'r', 'explain', TYPE_UINT ) or (defined ( 'POST_EXPLAIN' ) and ! empty ( $_POST )))) {
				// load 'explain' database class
				require_once (DIR . '/includes/class_database_explain.php');
				$db = new Database_Explain ( $skyuc );
			} else {
				$db = new Database ( $skyuc );
			}
			break;
		}
	// 加载 MySQLi 类
	case 'mysqli' :
		{
			if ($skyuc->debug and ($skyuc->input->clean_gpc ( 'r', 'explain', TYPE_UINT ) or (defined ( 'POST_EXPLAIN' ) and ! empty ( $_POST )))) {
				// load 'explain' database class
				require_once (DIR . '/includes/class_database_explain.php');
				$db = new Database_MySQLi_Explain ( $skyuc );
			} else {
				$db = new Database_MySQLi ( $skyuc );
		}
		break;
}
	// 加载扩展、 非 MySQL 类
	default :
		{
			// 这并不是，尚未全面实施
			//	$db = 'Database_' . $skyuc->config['Database']['dbtype'];
			//	$db = new $db($skyuc);
			die ( 'Fatal error: Database class not found' );
		}
}
// get core functions
// 获取核心函数库
if (!empty($db->explain)) {
	$db->timer_start('Including Functions.php');
	require_once(DIR . '/includes/functions.php');
	$db->timer_stop(false);
}
else {
	require_once (DIR . '/includes/functions.php');
}
require (DIR . '/includes/class_error.php');
// get movie functions
if (!empty($db->explain)) {
	$db->timer_start('Including Functions_show.php');
	require (DIR . '/includes/functions_show.php');
	$db->timer_stop(false);
}
else {
	require (DIR . '/includes/functions_show.php');
}
// make database connection
// 建立数据库连接
$db->connect ( $skyuc->config ['Database'] ['dbname'], $skyuc->config ['MasterServer'] ['servername'], $skyuc->config ['MasterServer'] ['port'], $skyuc->config ['MasterServer'] ['username'], $skyuc->config ['MasterServer'] ['password'], $skyuc->config ['MasterServer'] ['usepconnect'], $skyuc->config ['SlaveServer'] ['servername'], $skyuc->config ['SlaveServer'] ['port'], $skyuc->config ['SlaveServer'] ['username'], $skyuc->config ['SlaveServer'] ['password'], $skyuc->config ['SlaveServer'] ['usepconnect'], $skyuc->config ['Database'] ['ini_file'], $skyuc->config ['Database'] ['charset'] );
if (! empty ( $skyuc->config ['Database'] ['force_sql_mode'] )) {
	$db->force_sql_mode ( '' );
}

//使  $db 成为 $skyuc 的成员函数
$skyuc->db = $db;

if (! isset ( $specialtemplates ) || ! is_array ( $specialtemplates ) || is_null ( $specialtemplates )) {
	$specialtemplates = array ();
}

// #############################################################################
// 从数据存储中提取选项和其他数据
if (!empty($db->explain))
{
	$db->timer_start('Datastore Setup');
}
$datastore_class = (! empty ( $skyuc->config ['Datastore'] ['class'] )) ? $skyuc->config ['Datastore'] ['class'] : 'Datastore';

if ($datastore_class != 'Datastore') {
	require (DIR . '/includes/class_datastore.php');
}

$skyuc->datastore = new $datastore_class ( $skyuc, $db );
$skyuc->datastore->fetch ( $specialtemplates );
$skyuc->secache = new secache ();

if ($skyuc->options === null) {

	echo '<div>SKYUC 数据缓存错误，可能由如下原因导致:
		<ol>
 			' . (function_exists ( 'mmcache_get' ) ? '<li>Turck MMCache 安装在您的服务器中，首先尝试禁用 Turck MMCache 或将其替换为 eAccelerator</li>' : '') . '

		</ol>
	</div>';

	trigger_error ( 'SKYUC datastore 缓存失效或错误', E_USER_ERROR );
}
if (!empty($db->explain))
{
	$db->timer_stop(false);
}
if ($skyuc->options ['cookietimeout'] < 10 || $skyuc->options ['cookietimeout'] > 600) {
	// 值小于 10 ，防止出错
	$skyuc->options ['cookietimeout'] = 15;
}

// #############################################################################
/**
 * 如果允许关闭函数, 在退出时注册 exec_shut_down 运行。
 * 在 IIS CGI 开启 Gzip 模式时禁用关闭函数, 因为它不能正常工作。有时候, 除非我们破坏HTTP头部 content-length
 */
define ( 'SAPI_NAME', PHP_SAPI );
define ( 'NOSHUTDOWNFUNC', true );

// 获取引用(来源)页的 URL
$skyuc->url = & $skyuc->input->fetch_url ();
define ( 'REFERRER_PASSTHRU', $skyuc->url );

// #############################################################################
// 初始化 $show 显示变量-用于模板条件
$tpl = array ();

// #############################################################################
// 过滤 Cookie 值
$skyuc->input->clean_array_gpc ( 'c', array (COOKIE_PREFIX . 'userid' => TYPE_UINT, COOKIE_PREFIX . 'username' => TYPE_STR, COOKIE_PREFIX . 'password' => TYPE_STR, COOKIE_PREFIX . 'lastvisit' => TYPE_UINT, COOKIE_PREFIX . 'lastactivity' => TYPE_UINT, COOKIE_PREFIX . 'sessionhash' => TYPE_NOHTML ) );

// #############################################################################
// Setup session
// 设置 session
if (!empty($db->explain))
{
	$db->timer_start('Session Handling');
}
$skyuc->input->clean_array_gpc ( 'r', array ('s' => TYPE_NOHTML ) );

// 在模板中用来隐藏搜索引擎的东西的条件。
$tpl ['search_engine'] = ($skyuc->superglobal_size ['_COOKIE'] == 0 and is_spider ());
// 如果是蜘蛛的访问，那么默认为访客方式，并且不记录到日志中
if ($tpl ['search_engine']) {
	if (! defined ( 'SKIP_USERINFO' )) {
		define ( 'SKIP_USERINFO', 1 );
	}

}

// 处理 session 输入
$sessionhash = (! empty ( $skyuc->GPC ['s'] ) ? $skyuc->GPC ['s'] : $skyuc->GPC [COOKIE_PREFIX . 'sessionhash']); // 覆盖 cookie


//生成会话并设置环境
$skyuc->session = new Session ( $skyuc, $sessionhash, $skyuc->GPC [COOKIE_PREFIX . 'userid'], $skyuc->GPC [COOKIE_PREFIX . 'password'] );

// 在 URL 中隐藏 sessionid，如果是一个搜索引擎或我们拥有一个COOKIE
$skyuc->session->set_session_visibility ( $tpl ['search_engine'] or $skyuc->superglobal_size ['_COOKIE'] > 0 );
$skyuc->userinfo = & $skyuc->session->fetch_userinfo ();
$skyuc->session->do_lastvisit_update ( $skyuc->GPC [COOKIE_PREFIX . 'lastvisit'], $skyuc->GPC [COOKIE_PREFIX . 'lastactivity'] );

// CSRF 保护 之 POST 请求
if (strtoupper ( $_SERVER ['REQUEST_METHOD'] ) == 'POST' and ! defined ( 'CSRF_SKIP_PROTECTION' )) {
	if (empty ( $_POST ) and isset ( $_SERVER ['CONTENT_LENGTH'] ) and $_SERVER ['CONTENT_LENGTH'] > 0) {
		die ( 'The file(s) uploaded were too large to process.' );
	}

	if ($skyuc->userinfo ['userid'] > 0 and defined ( 'CSRF_PROTECTION' ) and CSRF_PROTECTION === true) {
		$skyuc->input->clean_array_gpc ( 'p', array ('securitytoken' => TYPE_STR ) );

		if (! in_array ( $_POST ['act'], $skyuc->csrf_skip_list )) {
			if (! verify_security_token ( $skyuc->GPC ['securitytoken'], $skyuc->userinfo ['securitytoken_raw'] )) {
				switch ($skyuc->GPC ['securitytoken']) {
					case '' :
						define ( 'CSRF_ERROR', 'missing' );
						break;
					case 'guest' :
						define ( 'CSRF_ERROR', 'guest' );
						break;
					case 'timeout' :
						define ( 'CSRF_ERROR', 'timeout' );
						break;
					default :
						define ( 'CSRF_ERROR', 'invalid' );
				}
			}
		}
	} else if (! defined ( 'CSRF_PROTECTION' ) and ! defined ( 'SKIP_REFERRER_CHECK' )) {
		if (HTTP_HOST and $_SERVER ['HTTP_REFERER']) {
			$http_host = preg_replace ( '#:80$#', '', HTTP_HOST );
			$referrer_parts = @parse_url ( $_SERVER ['HTTP_REFERER'] );
			$ref_port = intval ( $referrer_parts ['port'] );
			$ref_host = $referrer_parts ['host'] . ((! empty ( $ref_port ) and $ref_port != '80') ? ":$ref_port" : '');

			$allowed = preg_split ( '#\s+#', $skyuc->options ['allowedreferrers'], - 1, PREG_SPLIT_NO_EMPTY );
			$allowed [] = preg_replace ( '#^www\.#i', '', $http_host );
			$allowed [] = '.paypal.com';

			$pass_ref_check = false;
			foreach ( $allowed as $host ) {
				if (preg_match ( '#' . preg_quote ( $host, '#' ) . '$#siU', $ref_host )) {
					$pass_ref_check = true;
					break;
				}
			}
			unset ( $allowed );

			if ($pass_ref_check == false) {
				die ( 'In order to accept POST request originating from this domain, the admin must add this domain to the whitelist.' );
			}
		}
	}
}

// 防止 Google Web Accelerator(谷歌网络加速器)无视GET方法链接中JavaScript 的提示，预先读取敏感数据。
// Google Web Accelerator 对于游客是一件好事，但不适合所有人。
if ($skyuc->userinfo ['userid'] > 0 and isset ( $_SERVER ['HTTP_X_MOZ'] ) and strpos ( $_SERVER ['HTTP_X_MOZ'], 'prefetch' ) !== false) {
	if (SAPI_NAME == 'cgi' or SAPI_NAME == 'cgi-fcgi') {
		header ( 'Status: 403 Forbidden' );
	} else {
		header ( 'HTTP/1.1 403 Forbidden' );
	}
	die ( 'Prefetching is not allowed due to the various privacy issues that arise.' );
}

if (!empty($db->explain))
{
	$db->timer_stop(false);
}
?>