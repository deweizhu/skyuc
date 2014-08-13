<?php
/**
 * SKYUC! 安裝程式語言文件
 * ============================================================================
 * 版權所有 (C) 2012 天空網絡，並保留所有權利。
 * 網站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
*/

/* 通用語言項 */
$_LANG['prev_step'] = '上一步：';
$_LANG['next_step'] = '下一步：';
$_LANG['copyright'] = '&copy; 2012 <a href="http://www.skyuc.com" target="_blank">天空網絡</a>。保留所有權利。';

/* 歡迎頁 */
$_LANG['welcome_title'] = '歡迎您選用SKYUC!(天空網絡電影系統)！';
$_LANG['select_installer_lang'] = '界面語言：';
$_LANG['simplified_chinese'] = '簡體中文';
$_LANG['traditional_chinese'] = '繁體中文';
$_LANG['agree_license'] = '我已仔細閱讀，並同意上述條款中的所有內容';
$_LANG['check_system_environment'] = '檢測系統環境';

/* 環境檢測頁 */
$_LANG['checking_title'] = 'SKYUC!安裝程式 第2步/共3步 環境檢測';
$_LANG['system_environment'] = '系統環境';
$_LANG['dir_priv_checking'] = '目錄權限檢測';
$_LANG['template_writable_checking'] = '模板可寫性檢查';
$_LANG['rename_priv_checking'] = '特定目錄修改權限檢查';
$_LANG['welcome_page'] = '歡迎頁';
$_LANG['recheck'] = '重新檢查';
$_LANG['config_system'] = '配置系統';
$_LANG['does_support_mysql'] = '是否支持MySQL';
$_LANG['support'] = '支持';
$_LANG['does_support_dld'] = '重要文件是否完整';
$_LANG['support_dld'] = '完整';
$_LANG['support'] = '支持';
$_LANG['not_support'] = '<font color="red">不支持</font>';
$_LANG['cannt_support_dwt'] = '<font color="red">缺少dwt文件</font>';
$_LANG['cannt_support_lbi'] = '<font color="red">缺少lib文件</font>';
$_LANG['cannt_support_dat'] = '<font color="red">缺少dat文件</font>';
$_LANG['php_os'] = '操作系統';
$_LANG['php_ver'] = 'PHP 版本';
$_LANG['mysql_ver'] = 'MySQL 版本';
$_LANG['imagemagick_or_gd'] = '圖像處理庫';
$_LANG['safe_mode'] = '伺服器是否開啟安全模式';
$_LANG['safe_mode_on'] = '開啟';
$_LANG['safe_mode_off'] = '關閉';
$_LANG['can_write'] = '可寫';
$_LANG['cannt_write'] = '<font color="red">不可寫</font>';
$_LANG['not_exists'] = '<font color="red">不存在</font>';
$_LANG['cannt_modify'] = '<font color="red">不可修改</font>';
$_LANG['all_are_writable'] = '所有模板，全部可寫';

/* 系統設置 */
$_LANG['setting_title'] = 'SKYUC!安裝程式 第3步/共3步 配置系統';
$_LANG['db_account'] = '數據庫帳號';
$_LANG['db_port'] = '端口號：';
$_LANG['db_host'] = '數據庫主機：';
$_LANG['db_name'] = '數據庫名：';
$_LANG['db_user'] = '用戶名：';
$_LANG['db_pass'] = '密碼：';
$_LANG['go'] = '搜';
$_LANG['db_list'] = '已有數據庫';
$_LANG['db_prefix'] = '表前綴：';
$_LANG['admin_account'] = '管理員帳號';
$_LANG['admin_name'] = '管理員姓名：';
$_LANG['admin_password'] = '登錄密碼：';
$_LANG['admin_password2'] = '密碼確認：';
$_LANG['admin_email'] = '電子郵箱：';
$_LANG['mix_options'] = '雜項';
$_LANG['select_lang_package'] = '選擇語言包：';
$_LANG['disable_captcha'] = '禁用驗證碼：';
$_LANG['captcha_notice'] = '選擇此項，進入後台、發表評論無需驗證';
$_LANG['database'] = '數據庫類型：';
$_LANG['install_at_once'] = '立即安裝';
$_LANG['default_friend_link'] = 'SKYUC! 官方網站';
$_LANG['monitor_title'] = '安裝程式監視器';

/* 提示信息 */
$_LANG['has_locked_installer'] = '<strong>安裝程式已經被鎖定。</strong><br /><br />如果您確定要重新安裝 SKYUC!，請刪除data目錄下的 install.lock。';
$_LANG['connect_failed'] = '連接 數據庫失敗，請檢查您輸入的 數據庫帳號 是否正確。';
$_LANG['query_failed'] = '查詢 數據庫失敗，請檢查您輸入的 數據庫帳號 是否正確。';
$_LANG['select_db_failed'] = '選擇 數據庫失敗，請檢查您輸入的 數據庫名稱 是否正確。';
$_LANG['cannt_find_db'] = '無';
$_LANG['cannt_create_database'] = '無法創建數據庫';
$_LANG['password_empty_error'] = '密碼不能為空';
$_LANG['passwords_not_eq'] = '密碼不相同';
$_LANG['open_config_failed'] = '打開配置文件失敗';
$_LANG['write_config_failed'] = '寫入配置文件失敗';
$_LANG['create_passport_failed'] = '創建管理員帳號失敗';
$_LANG['cannt_mk_dir'] = '無法創建目錄';
$_LANG['cannt_copy_file'] = '無法復制文件';
$_LANG['open_installlock_failed'] = '打開install.lock文件失敗';
$_LANG['write_installlock_failed'] = '寫入install.lock文件失敗';

$_LANG['install_done_title'] = 'SKYUC! 安裝程式 安裝成功';
$_LANG['install_error_title'] = 'SKYUC! 安裝程式 安裝失敗';
$_LANG['done'] = '恭喜您，SKYUC! 已經成功地安裝完成。<br />基於安全的考慮，請在安裝完成後刪除 install 目錄。';
$_LANG['go_to_view_my_skyuc'] = '前往 SKYUC! 首頁';
$_LANG['go_to_view_control_panel'] = '前往 SKYUC! 後台管理中心';
$_LANG['open_config_file_failed'] = '無法寫入 data/config.php，請檢查該文件是否允許寫入。';
$_LANG['write_config_file_failed'] = '寫入配置文件出錯';

/* 客戶端JS語言項 */
$_LANG['js_languages']['success'] = '成功';
$_LANG['js_languages']['fail'] = '失敗';
$_LANG['js_languages']['db_exists'] = '這是一個已經存在的數據庫，確定要覆蓋該數據庫嗎？';
$_LANG['js_languages']['total_num'] = '共 %s 個';
$_LANG['js_languages']['wait_please'] = '正在安裝中，請稍候…………';
$_LANG['js_languages']['create_config_file'] = '創建配置文件............';
$_LANG['js_languages']['create_database'] = '創建數據庫............';
$_LANG['js_languages']['install_data'] = '安裝數據............';
$_LANG['js_languages']['create_admin_passport'] = '創建管理員帳號............';
$_LANG['js_languages']['do_others'] = '處理其它............';
$_LANG['js_languages']['display_detail'] = '顯示細節';
$_LANG['js_languages']['hide_detail'] = '隱藏細節';
$_LANG['js_languages']['has_been_stopped'] = '安裝進程已中止';

?>