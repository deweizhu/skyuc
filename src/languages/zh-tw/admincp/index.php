<?php
/**
 * SKYUC! 管理中心起始頁語言文件
 * ============================================================================
 * 版權所有 (C) 2012 天空網絡，並保留所有權利。
 * 網站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
*/

$_LANG['about'] = '關於';
$_LANG['toggle_calculator'] = '計算器';
$_LANG['todolist'] = '記事本';
$_LANG['preview'] = '查看網站';
$_LANG['menu'] = '菜單';
$_LANG['help'] = '幫助';
$_LANG['signout'] = '退出';
$_LANG['profile'] = '個人設置';
$_LANG['view_message'] = '管理員留言';
$_LANG['send_msg'] = '發送留言';
$_LANG['expand_all'] = '展開';
$_LANG['collapse_all'] = '閉合';
$_LANG['no_help'] = '暫時還沒有該部分內容';

$_LANG['js_languages']['expand_all'] = '展開';
$_LANG['js_languages']['collapse_all'] = '閉合';

/*------------------------------------------------------ */
//-- 計算器
/*------------------------------------------------------ */

$_LANG['calculator'] = '計算器';
$_LANG['clear_calculator'] = '清除';
$_LANG['backspace'] = '退格';

/*------------------------------------------------------ */
//-- 起始頁
/*------------------------------------------------------ */
$_LANG['pm_title'] = '留言標題';
$_LANG['pm_username'] = '留言者';
$_LANG['pm_time'] = '留言時間';

$_LANG['cache_name'] = '緩存系統名稱';
$_LANG['cache_mgr'] = '緩存管理';
$_LANG['clear_cache'] = '清空緩存';
$_LANG['cache_desc'] = '註：只有在系統出現頁面打不開、無法訪問等嚴重故障時會需要用到此處清除緩存功能，其餘情況下均無需使用此功能，系統會自動清除緩存。';

/* 優化程序 */
$_LANG['mmcache_not_supported'] = 'Turck MMCache 已經被 <a href="http://eaccelerator.net/" target="_blank">eAccelerator</a> 取代，不能正確支持 SKYUC。';
$_LANG['eaccelerator_too_old'] = '<a href="http://eaccelerator.net/" target="_blank">eAccelerator</a> for PHP 必須升級到 0.9.3 或更高版本。';
$_LANG['apc_too_old'] = '您的伺服器正在運行 <a href="http://pecl.php.net/package/APC/" target="_blank">Alternative PHP Cache</a> (APC) 的一個版本，而這個版本不兼容此版本的 SKYUC。請升級到 APC  3.0.0 或更高版本。';


$_LANG['system_info'] = '系統信息';
$_LANG['os'] = '伺服器操作系統：';
$_LANG['web_server'] = 'Web 伺服器：';
$_LANG['php_version'] = 'PHP 版本：';
$_LANG['mysql_version'] = 'MySQL 版本：';
$_LANG['curl'] = 'CURL 支持：';
$_LANG['zlib'] = 'Zlib 支持：';
$_LANG['skyuc_version'] = 'SKYUC! 版本：';
$_LANG['install_date'] = '安裝日期：';
$_LANG['allow_url_fopen'] = '是否允許打開遠程連接：';
$_LANG['ip_version'] = 'ＩＰ庫版本：';
$_LANG['post_max_size'] = '支持POST數據的大小：';
$_LANG['max_filesize'] = '支持上傳文件的大小：';
$_LANG['safe_mode'] = '安全模式：';
$_LANG['safe_mode_gid'] = '安全模式GID：';
$_LANG['timezone'] = '時區設置：';
$_LANG['no_timezone'] = '無需設置';
$_LANG['socket'] = 'Socket 支持：';
$_LANG['register_globals'] = 'Register_Globals：';
$_LANG['magic_quotes_gpc'] = 'Magic_Quotes_GPC：';

$_LANG['remove_install'] = '您還沒有刪除 install 文件夾，出於安全的考慮，我們建議您刪除 install 文件夾。';
$_LANG['temp_dir_cannt_read'] = '您的伺服器設置了 open_base_dir 且沒有包含 %s，您將無法上傳文件。';
$_LANG['not_writable'] = '%s 目錄不可寫入，%s';
$_LANG['data_cannt_write'] = '您的系統將無法正常運行。';
$_LANG['cert_cannt_write'] = '您將無法上傳 ICP 備案證書文件。';
$_LANG['images_cannt_write']= '您將無法上傳任何電影圖片。';
$_LANG['empty_upload_tmp_dir'] = '當前的上傳臨時目錄為空，您可能無法上傳文件，請檢查 php.ini 中的設置。';
$_LANG['caches_cleared'] = '頁面緩存已經清除成功。';

/*------------------------------------------------------ */
//-- 關於我們
/*------------------------------------------------------ */
$_LANG['team_member'] = 'SKYUC! 團隊成員';
$_LANG['director'] = '項目策劃';
$_LANG['programmer'] = '程序開發';
$_LANG['ui_designer'] = '界面設計';
$_LANG['documentation'] = '文檔整理';
$_LANG['special_thanks'] = '特別感謝';
$_LANG['official_site'] = '官方網站';
$_LANG['site_url'] = '網站地址：';
$_LANG['support_center'] = '支持中心：';
$_LANG['support_forum'] = '支持論壇：';
?>
