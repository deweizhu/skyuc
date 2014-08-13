<?php
/**
 * SKYUC! 管理中心起始页语言文件
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
*/

$_LANG['about'] = '关于';
$_LANG['toggle_calculator'] = '计算器';
$_LANG['todolist'] = '记事本';
$_LANG['preview'] = '查看网站';
$_LANG['menu'] = '菜单';
$_LANG['help'] = '帮助';
$_LANG['signout'] = '退出';
$_LANG['profile'] = '个人设置';
$_LANG['view_message'] = '管理员留言';
$_LANG['send_msg'] = '发送留言';
$_LANG['expand_all'] = '展开';
$_LANG['collapse_all'] = '闭合';
$_LANG['no_help'] = '暂时还没有该部分内容';

$_LANG['js_languages']['expand_all'] = '展开';
$_LANG['js_languages']['collapse_all'] = '闭合';

/*------------------------------------------------------ */
//-- 计算器
/*------------------------------------------------------ */

$_LANG['calculator'] = '计算器';
$_LANG['clear_calculator'] = '清除';
$_LANG['backspace'] = '退格';

/*------------------------------------------------------ */
//-- 起始页
/*------------------------------------------------------ */
$_LANG['pm_title'] = '留言标题';
$_LANG['pm_username'] = '留言者';
$_LANG['pm_time'] = '留言时间';

$_LANG['cache_name'] = '缓存系统名称';
$_LANG['cache_mgr'] = '缓存管理';
$_LANG['clear_cache'] = '清空缓存';
$_LANG['cache_desc'] = '注：只有在系统出现页面打不开、无法访问等严重故障时会需要用到此处清除缓存功能，其余情况下均无需使用此功能，系统会自动清除缓存。';

/* 优化程序 */
$_LANG['mmcache_not_supported'] = 'Turck MMCache 已经被 <a href="http://eaccelerator.net/" target="_blank">eAccelerator</a> 取代，不能正确支持 SKYUC。';
$_LANG['eaccelerator_too_old'] = '<a href="http://eaccelerator.net/" target="_blank">eAccelerator</a> for PHP 必须升级到 0.9.3 或更高版本。';
$_LANG['apc_too_old'] = '您的服务器正在运行 <a href="http://pecl.php.net/package/APC/" target="_blank">Alternative PHP Cache</a> (APC) 的一个版本，而这个版本不兼容此版本的 SKYUC。请升级到 APC  3.0.0 或更高版本。';


$_LANG['system_info'] = '系统信息';
$_LANG['os'] = '服务器操作系统：';
$_LANG['web_server'] = 'Web 服务器：';
$_LANG['php_version'] = 'PHP 版本：';
$_LANG['mysql_version'] = 'MySQL 版本：';
$_LANG['curl'] = 'CURL 支持：';
$_LANG['zlib'] = 'Zlib 支持：';
$_LANG['skyuc_version'] = 'SKYUC! 版本：';
$_LANG['install_date'] = '安装日期：';
$_LANG['allow_url_fopen'] = '是否允许打开远程连接：';
$_LANG['ip_version'] = 'ＩＰ库版本：';
$_LANG['post_max_size'] = '支持POST数据的大小：';
$_LANG['max_filesize'] = '支持上传文件的大小：';
$_LANG['safe_mode'] = '安全模式：';
$_LANG['safe_mode_gid'] = '安全模式GID：';
$_LANG['timezone'] = '时区设置：';
$_LANG['no_timezone'] = '无需设置';
$_LANG['socket'] = 'Socket 支持：';
$_LANG['register_globals'] = 'Register_Globals：';
$_LANG['magic_quotes_gpc'] = 'Magic_Quotes_GPC：';

$_LANG['remove_install'] = '您还没有删除 install 文件夹，出于安全的考虑，我们建议您删除 install 文件夹。';
$_LANG['temp_dir_cannt_read'] = '您的服务器设置了 open_base_dir 且没有包含 %s，您将无法上传文件。';
$_LANG['not_writable'] = '%s 目录不可写入，%s';
$_LANG['data_cannt_write'] = '您的系统将无法正常运行。';
$_LANG['cert_cannt_write'] = '您将无法上传 ICP 备案证书文件。';
$_LANG['images_cannt_write']= '您将无法上传任何电影图片。';
$_LANG['empty_upload_tmp_dir'] = '当前的上传临时目录为空，您可能无法上传文件，请检查 php.ini 中的设置。';
$_LANG['caches_cleared'] = '页面缓存已经清除成功。';

/*------------------------------------------------------ */
//-- 关于我们
/*------------------------------------------------------ */
$_LANG['team_member'] = 'SKYUC! 团队成员';
$_LANG['director'] = '项目策划';
$_LANG['programmer'] = '程序开发';
$_LANG['ui_designer'] = '界面设计';
$_LANG['documentation'] = '文档整理';
$_LANG['special_thanks'] = '特别感谢';
$_LANG['official_site'] = '官方网站';
$_LANG['site_url'] = '网站地址：';
$_LANG['support_center'] = '支持中心：';
$_LANG['support_forum'] = '支持论坛：';
?>
