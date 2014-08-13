<?php
/**
 * SKYUC! 安装程序语言文件
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
*/

/* 通用语言项 */
$_LANG['prev_step'] = '上一步：';
$_LANG['next_step'] = '下一步：';
$_LANG['copyright'] = '&copy; 2012 <a href="http://www.skyuc.com" target="_blank">天空网络</a>。保留所有权利。';

/* 欢迎页 */
$_LANG['welcome_title'] = '欢迎您选用SKYUC!(天空网络电影系统)！';
$_LANG['select_installer_lang'] = '界面语言：';
$_LANG['simplified_chinese'] = '简体中文';
$_LANG['traditional_chinese'] = '繁体中文';
$_LANG['agree_license'] = '我已仔细阅读，并同意上述条款中的所有内容';
$_LANG['check_system_environment'] = '检测系统环境';

/* 环境检测页 */
$_LANG['checking_title'] = 'SKYUC!安装程序 第2步/共3步 环境检测';
$_LANG['system_environment'] = '系统环境';
$_LANG['dir_priv_checking'] = '目录权限检测';
$_LANG['template_writable_checking'] = '模板可写性检查';
$_LANG['rename_priv_checking'] = '特定目录修改权限检查';
$_LANG['welcome_page'] = '欢迎页';
$_LANG['recheck'] = '重新检查';
$_LANG['config_system'] = '配置系统';
$_LANG['does_support_dld'] = '重要文件是否完整';
$_LANG['support_dld'] = '<font color="green">完整</font>';
$_LANG['support'] = '<font color="green">支持</font>';
$_LANG['not_support'] = '<font color="red">不支持</font>';
$_LANG['cannt_support_dwt'] = '<font color="red">缺少dwt文件</font>';
$_LANG['cannt_support_lbi'] = '<font color="red">缺少lib文件</font>';
$_LANG['cannt_support_dat'] = '<font color="red">缺少dat文件</font>';
$_LANG['php_os'] = '操作系统';
$_LANG['php_ver'] = 'PHP 版本';
$_LANG['mysql_ver'] = 'MySQL 版本';
$_LANG['imagemagick_or_gd'] = '图像处理库';
$_LANG['safe_mode'] = '服务器是否开启安全模式';
$_LANG['safe_mode_on'] = '<font color="red">开启(建议关闭)</font>';
$_LANG['safe_mode_off'] = '<font color="green">关闭</font>';
$_LANG['can_write'] = '<font color="green">可写</font>';
$_LANG['cannt_write'] = '<font color="red">不可写</font>';
$_LANG['not_exists'] = '<font color="red">不存在</font>';
$_LANG['cannt_modify'] = '<font color="red">不可修改</font>';
$_LANG['all_are_writable'] = '所有模板，全部可写';

/* 系统设置 */
$_LANG['setting_title'] = 'SKYUC!安装程序 第3步/共3步 配置系统';
$_LANG['db_account'] = '数据库帐号';
$_LANG['db_port'] = '端口号：';
$_LANG['db_host'] = '数据库主机：';
$_LANG['db_name'] = '数据库名：';
$_LANG['db_user'] = '用户名：';
$_LANG['db_pass'] = '密码：';
$_LANG['go'] = '搜';
$_LANG['db_list'] = '已有数据库';
$_LANG['db_prefix'] = '表前缀：';
$_LANG['cookie_prefix'] = 'COOKIE前缀：';
$_LANG['admin_account'] = '管理员帐号';
$_LANG['admin_name'] = '管理员姓名：';
$_LANG['admin_password'] = '登录密码：';
$_LANG['admin_password2'] = '密码确认：';
$_LANG['admin_email'] = '电子邮箱：';
$_LANG['mix_options'] = '杂项';
$_LANG['select_lang_package'] = '选择语言包：';
$_LANG['disable_captcha'] = '禁用验证码：';
$_LANG['captcha_notice'] = '选择此项，进入后台、发表评论无需验证';
$_LANG['database'] = '数据库类型：';
$_LANG['install_at_once'] = '立即安装';
$_LANG['default_friend_link'] = 'SKYUC! 官方网站';
$_LANG['monitor_title'] = '安装程序监视器';

/* 提示信息 */
$_LANG['has_locked_installer'] = '<strong>安装程序已经被锁定。</strong><br /><br />如果您确定要重新安装 SKYUC!，请删除data目录下的 install.lock。';
$_LANG['connect_failed'] = '连接 数据库失败，请检查您输入的 数据库帐号 是否正确。';
$_LANG['query_failed'] = '查询 数据库失败，请检查您输入的 数据库帐号 是否正确。';
$_LANG['select_db_failed'] = '选择 数据库失败，请检查您输入的 数据库名称 是否正确。';
$_LANG['cannt_find_db'] = '无';
$_LANG['cannt_create_database'] = '无法创建数据库';
$_LANG['password_empty_error'] = '密码不能为空';
$_LANG['passwords_not_eq'] = '密码不相同';
$_LANG['open_config_failed'] = '打开配置文件失败';
$_LANG['write_config_failed'] = '写入配置文件失败';
$_LANG['create_passport_failed'] = '创建管理员帐号失败';
$_LANG['cannt_mk_dir'] = '无法创建目录';
$_LANG['cannt_copy_file'] = '无法复制文件';
$_LANG['open_installlock_failed'] = '打开install.lock文件失败';
$_LANG['write_installlock_failed'] = '写入install.lock文件失败';

$_LANG['install_done_title'] = 'SKYUC! 安装程序 安装成功';
$_LANG['install_error_title'] = 'SKYUC! 安装程序 安装失败';
$_LANG['done'] = '恭喜您，SKYUC! 已经成功地安装完成。<br />基于安全的考虑，请在安装完成后删除 install 目录。';
$_LANG['go_to_view_my_skyuc'] = '前往 SKYUC! 首页';
$_LANG['go_to_view_control_panel'] = '前往 SKYUC! 后台管理中心';
$_LANG['open_config_file_failed'] = '无法写入 data/config.php，请检查该文件是否允许写入。';
$_LANG['write_config_file_failed'] = '写入配置文件出错';

/* 客户端JS语言项 */
$_LANG['js_languages']['success'] = '成功';
$_LANG['js_languages']['fail'] = '失败';
$_LANG['js_languages']['db_exists'] = '这是一个已经存在的数据库，确定要覆盖该数据库吗？';
$_LANG['js_languages']['total_num'] = '共 %s 个';
$_LANG['js_languages']['wait_please'] = '正在安装中，请稍候…………';
$_LANG['js_languages']['create_config_file'] = '创建配置文件............';
$_LANG['js_languages']['create_database'] = '创建数据库............';
$_LANG['js_languages']['install_data'] = '安装数据............';
$_LANG['js_languages']['create_admin_passport'] = '创建管理员帐号............';
$_LANG['js_languages']['do_others'] = '处理其它............';
$_LANG['js_languages']['display_detail'] = '显示细节';
$_LANG['js_languages']['hide_detail'] = '隐藏细节';
$_LANG['js_languages']['has_been_stopped'] = '安装进程已中止';

?>