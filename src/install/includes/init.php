<?php
@set_time_limit(360);

error_reporting(E_ALL ^ E_NOTICE);

// Force PHP 5.3.0+ to take time zone information from OS
if (version_compare(phpversion(), '5.3.0', '>='))
{
	@date_default_timezone_set(date_default_timezone_get());
}

// 清除所有和文件操作相关的状态信息
clearstatcache();

// 定义站点根
define('DIR', str_replace('/install/includes/init.php', '', str_replace('\\', '/', __FILE__)));
define('CWD', DIR);

require(DIR . '/includes/functions.php');

// 创建错误处理对象
require(DIR . '/includes/class_error.php');
$err = new skyuc_error('message.dwt');

// 初始化模板引擎
require(DIR . '/install/includes/class_template.php');
$smarty = new template(DIR . '/install/templates/');

require(DIR . '/install/includes/functions_installer.php');

// 发送HTTP头部，保证浏览器识别UTF8编码
header('Content-type: text/html; charset=utf-8');

?>