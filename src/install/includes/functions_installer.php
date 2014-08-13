<?php
/**
 * SKYUC! 安装程序 之 模型
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
*/

if (!defined('SKYUC_AREA'))
{
    die('Hacking attempt');
}

/**
* 确定是否能够加载指定的 PHP 模块
*/
function can_load_dll($dll)
{
	return ((@ini_get('enable_dl') || strtolower(@ini_get('enable_dl')) == 'on') && (!@ini_get('safe_mode') || strtolower(@ini_get('safe_mode')) == 'off') && function_exists('dl') && @dl($dll . '.' . PHP_SHLIB_SUFFIX)) ? true : false;
}

/**
* 发现系统中Imagemagick路径
*/
function find_imagemagick()
{
		// Can we find Imagemagick anywhere on the system?
		$exe = (DIRECTORY_SEPARATOR == '\\') ? '.exe' : '';

		$magic_home = getenv('MAGICK_HOME');
		$img_imagick = '';
		if (empty($magic_home))
		{
			$locations = array('C:/WINDOWS/', 'C:/WINNT/', 'C:/WINDOWS/SYSTEM/', 'C:/WINNT/SYSTEM/', 'C:/WINDOWS/SYSTEM32/', 'C:/WINNT/SYSTEM32/', '/usr/bin/', '/usr/sbin/', '/usr/local/bin/', '/usr/local/sbin/', '/opt/', '/usr/imagemagick/', '/usr/bin/imagemagick/');
			$path_locations = str_replace('\\', '/', (explode(($exe) ? ';' : ':', getenv('PATH'))));

			$locations = array_merge($path_locations, $locations);
			foreach ($locations as $location)
			{
				// The path might not end properly, fudge it
				if (substr($location, -1, 1) !== '/')
				{
					$location .= '/';
				}

				if (@file_exists($location) && @is_readable($location . 'mogrify' . $exe) && @filesize($location . 'mogrify' . $exe) > 3000)
				{
					$img_imagick = str_replace('\\', '/', $location);
					continue;
				}
			}
		}
		else
		{
			$img_imagick = str_replace('\\', '/', $magic_home);
		}
		return $img_imagick;
}

/**
 * 检测服务器上是否存在指定的文件类型
 *
 * @access  public
 * @param   array     $file_types        文件路径数组，形如array('dwt'=>'', 'lbi'=>'', 'dat'=>'')
 * @return  string    全部可写返回空串，否则返回以逗号分隔的文件类型组成的消息串
 */
function file_types_exists($file_types)
{
    global $_LANG;

    $msg = '';
    foreach ($file_types as $file_type => $file_path)
    {
        if (!file_exists($file_path))
        {
            $msg .= $_LANG['cannt_support_' . $file_type] . ', ';
        }
    }

    $msg = preg_replace("/,\s*$/", '', $msg);

    return $msg;
}

/**
 * 获得系统的信息
 *
 * @access  public
 * @return  array     系统各项信息组成的数组
 */
function get_system_info()
{
    global $_LANG, $disabled;

    $system_info = array();

    // 检查系统基本参数
    $system_info[] = array($_LANG['php_os'], PHP_OS);
    $system_info[] = array($_LANG['php_ver'], PHP_VERSION);
    $system_info[] = array($_LANG['mysql_ver'], mysql_get_client_info());

    // 检查图片处理函数库
    $imagemagick = find_imagemagick();
    if ($imagemagick != '')
    {
    	 $system_info[] = array($_LANG['imagemagick_or_gd'], 'ImageMagick');
    }
		elseif (@extension_loaded('gd') || can_load_dll('gd'))
		{
			$system_info[] = array($_LANG['imagemagick_or_gd'], 'GD');
		}
		else
		{
			$system_info[] = array($_LANG['imagemagick_or_gd'],  $_LANG['not_support']);
			$disabled = 'disabled="true"';
		}

    // 检查系统是否支持以dwt,lib,dat为扩展名的文件
    $file_types = array(
            'dwt' => DIR . '/templates/default/index.dwt',
            'lbi' => DIR . '/templates/default/library/member.lbi',
            'dat' => DIR . '/includes/data/ipdata.dat'
        );

    $exists_info = file_types_exists($file_types);
    $exists_info = empty($exists_info) ? $_LANG['support_dld'] : $exists_info;
    $system_info[] = array($_LANG['does_support_dld'], $exists_info);

    // 服务器是否安全模式开启
    $safe_mode = ini_get('safe_mode') == '1' ? $_LANG['safe_mode_on'] : $_LANG['safe_mode_off'];
    $system_info[] = array($_LANG['safe_mode'], $safe_mode);

    return $system_info;
}

/**
 * 获得数据库列表
 *
 * @access  public
 * @param   string      $db_host        主机
 * @param   string      $db_port        端口号
 * @param   string      $db_user        用户名
 * @param   string      $db_pass        密码
 * @return  mixed       成功返回数据库列表组成的数组，失败返回false
 */
function get_db_list($db_host, $db_port, $db_user, $db_pass)
{
    global $err, $_LANG;
    $databases = array();
    $filter_dbs = array('information_schema', 'mysql');
    $conn = @mysql_connect($db_host.':'.$db_port, $db_user, $db_pass);

    if ($conn === false)
    {
        $err->add($_LANG['connect_failed']);
        return false;
    }
    keep_right_conn($conn);

    $result = mysql_query('SHOW DATABASES', $conn);
    if ($result !== false)
    {
        while (($row = mysql_fetch_assoc($result)) !== false)
        {
            if (in_array($row['Database'], $filter_dbs))
            {
                continue;
            }
            $databases[] = $row['Database'];
        }
    }
    else
    {
        $err->add($_LANG['query_failed']);
        return false;
    }
    @mysql_close($conn);

    return $databases;
}

/**
 * 创建指定名字的数据库
 *
 * @access  public
 * @param   string      $db_host        主机
 * @param   string      $db_port        端口号
 * @param   string      $db_user        用户名
 * @param   string      $db_pass        密码
 * @param   string      $db_name        数据库名
 * @return  boolean     成功返回true，失败返回false
 */
function create_database($db_host, $db_port, $db_user, $db_pass, $db_name)
{
    global $err, $_LANG;
    $conn = @mysql_connect($db_host.':'.$db_port, $db_user, $db_pass);

    if ($conn === false)
    {
        $err->add($_LANG['connect_failed']);

        return false;
    }

    $mysql_version = mysql_get_server_info($conn);
    keep_right_conn($conn, $mysql_version);
    if (mysql_select_db($db_name, $conn) === false)
    {
        $sql = $mysql_version >= '4.1' ? "CREATE DATABASE $db_name DEFAULT CHARACTER SET utf8" : "CREATE DATABASE $db_name";
        if (mysql_query($sql, $conn) === false)
        {
            $err->add($_LANG['cannt_create_database']);
            return false;
        }
    }
    @mysql_close($conn);

    return true;
}

/**
 * 保证进行正确的数据库连接（如字符集设置）
 *
 * @access  public
 * @param   string      $conn                      数据库连接
 * @param   string      $mysql_version        mysql版本号
 * @return  void
 */
function keep_right_conn($conn, $mysql_version='')
{
    if ($mysql_version === '')
    {
        $mysql_version = mysql_get_server_info($conn);
    }

    if ($mysql_version >= '4.1')
    {
        mysql_query('SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary', $conn);

        if ($mysql_version > '5.0.1')
        {
            mysql_query("SET sql_mode=''", $conn);
        }
    }
}

/**
 * 创建配置文件
 *
 * @access  public
 * @param   string      $db_host        主机
 * @param   string      $db_port        端口号
 * @param   string      $db_user        用户名
 * @param   string      $db_pass        密码
 * @param   string      $db_name        数据库名
 * @param   string      $db_prefix     数据表前缀
 * @param		string			$database				数据库类型mysqli或mysql
 * @return  boolean     成功返回true，失败返回false
 */
function create_config_file($db_host, $db_port, $db_user, $db_pass, $db_name, $db_prefix,$database,$email)
{
    global $err, $_LANG;


$content = <<<EOT
<?php
/**
* SKYUC! 数据库配置文件
* ============================================================================
* 版权所有 (C) 2012 天空网络，并保留所有权利。
* 网站地址: http://www.skyuc.com
* ----------------------------------------------------------------------------
* This is NOT a freeware, use is subject to license terms
* ============================================================================
*/

/*-------------------------------------------------------------------*\
| ***********   关于此文件中变量的注意事项              ************
+---------------------------------------------------------------------+
| 如果您尝试链接 MySQL 数据库时出现错误，请联系您的主机商，
| 因为我们无法告诉您数据库设置变量正确的值（每台主机都会有所不同）。
\*-------------------------------------------------------------------*/


// ****** 数据库类型 ******
// 在这里设置 SKYUC 使用的数据库服务器类型。
// 可选 mysql 和 mysqli。
// 如果您的网站数据库是主从数据库架构，您只需在您喜爱的数据库类型后添加
// _slave 后缀。即 mysql_slave 或 mysqli_slave。
\$config['Database']['dbtype']   = '{$database}';

// ****** 数据库名 ******
// 这是网站程序所使用的数据库名。
// 该数据库名的值请联系主机商。
\$config['Database']['dbname']   = '{$db_name}';

// ****** 数据表前缀 ******
//  数据库中使用的数据表的前缀。
\$config['Database']['tableprefix']   = '{$db_prefix}';

 // ****** 技术人员邮箱地址 ******
 // 如果发生数据库错误，错误信息将会发送到这个电子邮箱。
 // 留空则不发送错误信息到任何电子邮箱。
\$config['Database']['technicalemail']   = '{$email}';

// ****** 强制 SQL 模式 ******
// 应用于 MySQL 4.1+ 强制 sql_mode 变量 指定一个模式
// 将此处设置为“true”以禁用一些有可能与 SKYUC 不兼容的模式。
\$config['Database']['force_sql_mode']    = true;

// 数据库字符集(编码)
// 如果您已经设置了下一个选项（ini_file）, 并在 ini 文件中配置好了字符集，那么此选项不起作用。
\$config['Database']['charset']    = 'utf8';

// 可选的，PHP 可以从“ini_file”设置的文件中读取并设置连接参数。请在这里使用文件的绝对路径。
// 例如: \$config['Database']['ini_file'] = 'c:\program files\MySQL\MySQL Server 4.1\my.ini';
\$config['Database']['ini_file']    = '';




// ****** 主数据库服务器名与端口 ******
// 这是数据库的主机名或 IP 地址及端口。
// 如果您不确认这里填写什么，便不要管它。
\$config['MasterServer']['servername'] = '{$db_host}';

\$config['MasterServer']['port'] = '{$db_port}';
// ****** 主数据库用户名和密码 ******
// 这是连接和访问 MySQL 数据库时所需的用户名和密码。
// 它们的值必须从您的主机商处获得。
\$config['MasterServer']['username'] = '{$db_user}';
\$config['MasterServer']['password'] = '{$db_pass}';
// ****** 主数据库持久连接 ******
// 此选项设置连接 MySQL 数据库是否以持久方式。
// 对于小型网站，性能的差异可以忽略。
// 如果您不了解这个选项是干什么的，那么请关闭它。
// 0 = 关闭; 1 = 打开
\$config['MasterServer']['usepconnect'] = '1';




// ****** 从数据库服务器配置 ******
// 如果您运行了多个数据库后台服务器，您可以在这里填写从服务器的信息。
// 如果您不是 100% 确定在这里填写什么，那么请不要修改这里的默认配置。
\$config['SlaveServer']['servername'] = '';
\$config['SlaveServer']['port'] = '3600';
\$config['SlaveServer']['username'] = '';
\$config['SlaveServer']['password'] = '';
\$config['SlaveServer']['usepconnect'] = '0';




// ****** 管理面板的路径 ******
// 请注意如果您修改了这里的路径名，您必须同时手动修改,服务器上相应目录的目录名。
// 管理面板的路径
\$config['Misc']['admincpdir'] = 'admincp';
// 文件保存文件夹
\$config['Misc']['imagedir'] = 'upload';




// 网站程序所设置的 cookies 的前缀
// 请不要填写过长的前缀，并只能填写英文字母和数字
\$config['Misc']['cookieprefix'] = '{$db_prefix}';
// ******** 网站目录的绝对路径 ******
// 在某些系统中您可能需要输入网站目录的绝对路径，SKYUC 才能正常工作。
// 您可以忽略这个选项，除非 SKYUC 告诉您要填写它。
// 在这里不要在末尾填写斜杠！
// Unix 示例：
//   \$config['Misc']['sitepath'] = '/home/users/public_html';
// Win32 示例：
//   \$config['Misc']['sitepath'] = 'c:\program files\apache group\apache\htdocs';
\$config['Misc']['sitepath'] = '';
// ****** COOKIE 安全加密 ******
// 这个选项允许你加密COOKIE
// 您可以使用任何拉丁美洲和/或任何其他的字母数字符号。
// 留空为使用默认值。
// 注意：如果您改变这里，用户将需重新登陆网站。
\$config['Misc']['cookie_security_hash'] = '';




// ****** 缓存加速配置  ******
// 您在这里可以配置不同的方式来缓存 datastore 项目。
// Datastore_Filecache  - 使用缓存文件
// Datastore_eAccelerator - 使用 eAccelerator
// Datastore_APC - 使用 APC
// Datastore_XCache - 使用 XCache
// Datastore_Memcached - 使用一台 Memcache 服务器
// 同时需要指定缓存服务器的主机名或 IP，以及服务器所监听的端口
\$config['Datastore']['class'] = 'Datastore_Filecache';
//\$config['Datastore']['prefix'] = 'skyuc_'; //需要在一台服务器上运行多个 SKYUC 时请填写此项。

//第一台  Memcache 服务器，如有多台，复制下面代码并更改1为所需数字
/*\$config['Misc']['memcacheserver'][1]  = '127.0.0.1';
\$config['Misc']['memcacheport'][1]   = 11211;
\$config['Misc']['memcachepersistent'][1] = true;
\$config['Misc']['memcacheweight'][1]  = 1;
\$config['Misc']['memcachetimeout'][1]  = 1;
\$config['Misc']['memcacheretry_interval'][1] = 15;*/




// ****** 防CC攻击选项  ******
//*NIX 系统负载限制,当服务器的负载参数大于这个值时，自动开启CC防护模式 (建议值：5)
\$config['Misc']['db_loadavg'] = 5;

//CC攻击防护：0=关闭 ,1=预防遭受cc攻击(推荐),2=遭受CC攻击严重时使用
\$config['Misc']['db_cc'] = 0;

//页面缓存大小，最大为1G，最小15M，建议值大于100M
define('FILECACHE_SIZE','100M');

EOT;

    $fp = @fopen(DIR . '/data/config.php', 'wb+');
    if (!$fp)
    {
        $err->add($_LANG['open_config_file_failed']);
        return false;
    }
    if (!@fwrite($fp, trim($content)))
    {
        $err->add($_LANG['write_config_file_failed']);
        return false;
    }
    @fclose($fp);

    return true;
}


/**
 * 安装数据
 *
 * @access  public
 * @param   array         $sql_files        SQL文件路径组成的数组
 * @return  boolean       成功返回true，失败返回false
 */
function install_data($sql_files)
{
    global $err;
		/* --------------------start-------------------------------- */
    require_once(DIR . '/includes/class_core.php');

    // 初始化数据注册表
		$skyuc = new Registry();

		// 分析配置 ini 文件
		$skyuc->fetch_config();

		// #############################################################################
		// 加载数据库类
		switch (strtolower($skyuc->config['Database']['dbtype']))
		{
			// 加载标准 MySQL 类
			case 'mysql':
			case '':
			{
				$db = new Database($skyuc);
				break;
			}
			// 加载 MySQLi 类
			case 'mysqli':
			{
				$db = new Database_MySQLi($skyuc);
				break;
			}
		}


		// 获取核心函数库
		require_once(DIR . '/includes/functions.php');
		require_once(DIR . '/includes/class_sql_executor.php');

		// 建立数据库连接
		$db->connect(
			$skyuc->config['Database']['dbname'],
			$skyuc->config['MasterServer']['servername'],
			$skyuc->config['MasterServer']['port'],
			$skyuc->config['MasterServer']['username'],
			$skyuc->config['MasterServer']['password'],
			$skyuc->config['MasterServer']['usepconnect'],
			'',
			'',
			'',
			'',
			'',
			$skyuc->config['Database']['ini_file'],
			$skyuc->config['Database']['charset']
		);
		if (!empty($skyuc->config['Database']['force_sql_mode']))
		{
			$db->force_sql_mode('');
		}
		/* --------------------end-------------------------------- */
    $se = new sql_executor($db, $skyuc->config['Database']['charset'], 'skyuc_', TABLE_PREFIX);
    $result = $se->run_all($sql_files);
    if ($result === false)
    {
        $err->add($se->error);
        return false;
    }

    return true;
}

/**
 * 创建管理员帐号
 *
 * @access  public
 * @param   string      $admin_name
 * @param   string      $admin_password
 * @param   string      $admin_password2
 * @param   string      $admin_email
 * @return  boolean     成功返回true，失败返回false
 */
function create_admin_passport($admin_name, $admin_password, $admin_password2, $admin_email)
{
    global $err, $_LANG;

    if ($admin_password === '')
    {
        $err->add($_LANG['password_empty_error']);
        return false;
    }

    if ($admin_password !== $admin_password2)
    {
        $err->add($_LANG['passwords_not_eq']);
        return false;
    }
		/* --------------------start-------------------------------- */
		require_once(DIR . '/includes/class_core.php');

    // 初始化数据注册表
		$skyuc = new Registry();

		// 分析配置 ini 文件
		$skyuc->fetch_config();

    // #############################################################################
		// 加载数据库类
		switch (strtolower($skyuc->config['Database']['dbtype']))
		{
			// 加载标准 MySQL 类
			case 'mysql':
			case '':
			{
				$db = new Database($skyuc);
				break;
			}
			// 加载 MySQLi 类
			case 'mysqli':
			{
				$db = new Database_MySQLi($skyuc);
				break;
			}
		}


		// 获取核心函数库
		require_once(DIR . '/includes/functions.php');
		require_once(DIR . '/includes/class_sql_executor.php');
		// 建立数据库连接
		$db->connect(
			$skyuc->config['Database']['dbname'],
			$skyuc->config['MasterServer']['servername'],
			$skyuc->config['MasterServer']['port'],
			$skyuc->config['MasterServer']['username'],
			$skyuc->config['MasterServer']['password'],
			$skyuc->config['MasterServer']['usepconnect'],
			'',
			'',
			'',
			'',
			'',
			$skyuc->config['Database']['ini_file'],
			$skyuc->config['Database']['charset']
		);
		if (!empty($skyuc->config['Database']['force_sql_mode']))
		{
			$db->force_sql_mode('');
		}
		/* --------------------end-------------------------------- */

    $sql = 'INSERT INTO '. TABLE_PREFIX.'admin'.
                ' (user_name, email, password, join_time, last_time, action_list, nav_list)'.
            ' VALUES '.
                "('". $db->escape_string($admin_name) ."', '". $db->escape_string($admin_email) ."', '".md5($admin_password). "', '". time() ."', '". time() ."', 'all', '')";
    if (!$db->query_write($sql))
    {
        $err->add($_LANG['create_passport_failed']);
        return false;
    }

    return true;
}



/**
 * 其它设置
 *
 * @access  public
 * @param   string      $system_lang            系统语言
 * @param   string      $disable_captcha        是否开启验证码
 * @return  boolean     成功返回true，失败返回false
 */
function do_others($system_lang, $captcha)
{
    global $err, $_LANG;

		// 替换zh_cn等为zh-cn
		$system_lang = str_replace('_','-',$system_lang);
		/* --------------------start-------------------------------- */
		require_once(DIR . '/includes/class_core.php');

    // 初始化数据注册表
		$skyuc = new Registry();

		// 分析配置 ini 文件
		$skyuc->fetch_config();

    // #############################################################################
		// 加载数据库类
		switch (strtolower($skyuc->config['Database']['dbtype']))
		{
			// 加载标准 MySQL 类
			case 'mysql':
			case '':
			{
				$db = new Database($skyuc);
				break;
			}
			// 加载 MySQLi 类
			case 'mysqli':
			{
				$db = new Database_MySQLi($skyuc);
				break;
			}
		}


		// 获取核心函数库
		require_once(DIR . '/includes/functions.php');
		require_once(DIR . '/includes/class_sql_executor.php');

		// 建立数据库连接
		$db->connect(
			$skyuc->config['Database']['dbname'],
			$skyuc->config['MasterServer']['servername'],
			$skyuc->config['MasterServer']['port'],
			$skyuc->config['MasterServer']['username'],
			$skyuc->config['MasterServer']['password'],
			$skyuc->config['MasterServer']['usepconnect'],
			'',
			'',
			'',
			'',
			'',
			$skyuc->config['Database']['ini_file'],
			$skyuc->config['Database']['charset']
		);
		if (!empty($skyuc->config['Database']['force_sql_mode']))
		{
			$db->force_sql_mode('');
		}
		/* --------------------end-------------------------------- */

    // 更新 SKYUC! 语言
    $sql = 'UPDATE '. TABLE_PREFIX ."setting SET value='" . $system_lang . "' WHERE code='lang'";
    if (!$db->query_write($sql))
    {
        $err->add($db->errno() .' '. $db->error());
        return false;
    }
    // 更新 ImageMagick 路径
    $imagemagick = find_imagemagick();
    if ($imagemagick != '')
    {
	    $sql = 'UPDATE '. TABLE_PREFIX ."setting SET value='" . $db->escape_string($imagemagick) . "' WHERE code='magickpath'";
	    if (!$db->query_write($sql))
	    {
	        $err->add($db->errno() .' '. $db->error());
	        return false;
	    }
    }

		// 处理验证码
    if ($captcha == 0)
    {
        $sql = 'UPDATE '. TABLE_PREFIX . "setting SET value = '20' WHERE code = 'humanverify'";
        if (!$db->query_write($sql))
        {
            $err->add($db->errno() .' '. $db->error());
            return false;
        }
    }

    return true;
}

/**
 * 安装完成后的一些善后处理
 *
 * @access  public
 * @return  boolean     成功返回true，失败返回false
 */
function deal_aftermath()
{
    global $err, $_LANG;

    /* --------------------start-------------------------------- */
		require_once(DIR . '/includes/class_core.php');

    // 初始化数据注册表
		$skyuc = new Registry();

		// 分析配置 ini 文件
		$skyuc->fetch_config();

    // #############################################################################
		// 加载数据库类
		switch (strtolower($skyuc->config['Database']['dbtype']))
		{
			// 加载标准 MySQL 类
			case 'mysql':
			case '':
			{
				$db = new Database($skyuc);
				break;
			}
			// 加载 MySQLi 类
			case 'mysqli':
			{
				$db = new Database_MySQLi($skyuc);
				break;
			}
		}


		// 获取核心函数库
		require_once(DIR . '/includes/functions.php');
		require_once(DIR . '/includes/class_sql_executor.php');

		// 建立数据库连接
		$db->connect(
			$skyuc->config['Database']['dbname'],
			$skyuc->config['MasterServer']['servername'],
			$skyuc->config['MasterServer']['port'],
			$skyuc->config['MasterServer']['username'],
			$skyuc->config['MasterServer']['password'],
			$skyuc->config['MasterServer']['usepconnect'],
			'',
			'',
			'',
			'',
			'',
			$skyuc->config['Database']['ini_file'],
			$skyuc->config['Database']['charset']
		);
		if (!empty($skyuc->config['Database']['force_sql_mode']))
		{
			$db->force_sql_mode('');
		}
		/* --------------------end-------------------------------- */

    // 初始化友情链接
    $sql = 'INSERT INTO '. TABLE_PREFIX. 'friend_link '.
                ' (link_name, link_url, link_logo, show_order) '.
            ' VALUES '.
                "('".$_LANG['default_friend_link']."', 'http://www.skyuc.com/', 'http://www.skyuc.com/images/logo/skyuc_logo.png','0')";
    if (!$db->query_write($sql))
    {
        $err->add($db->errno() .' '. $db->error());
    }

    // 更新 SKYUC! 安装日期
    $sql = 'UPDATE '. TABLE_PREFIX . "setting SET value='" .time(). "' WHERE code='install_date'";
    if (!$db->query($sql))
    {
        $err->add($db->errno() .' '. $db->error());
    }

    // 更新 SKYUC! 版本
    $sql = 'UPDATE '. TABLE_PREFIX . "setting SET value='" .VERSION. "' WHERE code='skyuc_version'";
    if (!$db->query($sql))
    {
        $err->add($db->errno() .' '. $db->error());
        return false;
    }


    // 写入安装锁定文件
    $fp = @fopen(DIR . '/data/install.lock', 'wb+');
    if (!$fp)
    {
        $err->add($_LANG['open_installlock_failed']);
        return false;
    }
    if (!@fwrite($fp, "SKYUC! INSTALLED"))
    {
        $err->add($_LANG['write_installlock_failed']);
        return false;
    }
    @fclose($fp);

    return true;
}

?>