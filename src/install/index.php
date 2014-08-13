<?php
/**
 * SKYUC! 安装程序 之 控制器
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
*/

define('SKYUC_AREA', 'INSTALL');

require_once(dirname(__FILE__) .'/includes/init.php');

/* 初始化语言变量 */
$installer_lang = isset($_REQUEST['lang']) ? trim($_REQUEST['lang']) : 'zh_cn';

if ($installer_lang != 'zh_cn' && $installer_lang != 'zh_tw')
{
    $installer_lang != 'zh_cn';
}

/* 加载安装程序所使用的语言包 */
$installer_lang_package_path = DIR . '/install/languages/' . $installer_lang . '.php';
if (is_file($installer_lang_package_path))
{
    include_once($installer_lang_package_path);
    $smarty->assign('lang', $_LANG);
}
else
{
    die('Can\'t find language package!');
}

/* 初始化流程控制变量 */
$step = isset($_REQUEST['step']) ? $_REQUEST['step'] : 'welcome';
if (is_file(DIR . '/data/install.lock'))
{
    $step = 'error';
    $err->add($_LANG['has_locked_installer']);

    if (isset($_REQUEST['IS_AJAX_REQUEST']) && $_REQUEST['IS_AJAX_REQUEST'] === 'yes')
    {
        die(implode(',', $err->get_all()));
    }
}

switch ($step)
{
case 'welcome' :
    $smarty->assign('installer_lang', $installer_lang);
    $smarty->display('welcome.php');

    break;

case 'check' :
    include_once(DIR . '/install/includes/functions_env_checker.php');

    $dir_checking = check_dirs_priv($checking_dirs);

    $templates_root = array(
        'dwt' => DIR . '/templates/default/',
        'lbi' => DIR . '/templates/default/library/');
    $template_checking = check_templates_priv($templates_root);

    $rename_priv = check_rename_priv();

    $disabled = '';
    if ($dir_checking['result'] === 'ERROR'
            || !empty($template_checking)
            || !empty($rename_priv)
            || !function_exists('mysql_connect'))
    {
        $disabled = 'disabled="true"';
    }

    $has_unwritable_tpl = 'yes';
    if (empty($template_checking))
    {
        $template_checking = $_LANG['all_are_writable'];
        $has_unwritable_tpl = 'no';
    }

    $smarty->assign('installer_lang', 	$installer_lang);
    $smarty->assign('system_info', 			get_system_info());
    $smarty->assign('dir_checking', 		$dir_checking['detail']);
    $smarty->assign('has_unwritable_tpl', 	$has_unwritable_tpl);
    $smarty->assign('template_checking', 		$template_checking);
    $smarty->assign('rename_priv', 					$rename_priv);
    $smarty->assign('disabled', 							$disabled);
    $smarty->display('checking.php');

    break;

case 'setting_ui' :

    $prefix = 'skyuc_';


    if (find_imagemagick() == '' && (!@extension_loaded('gd') || can_load_dll('gd') === false))
    {
        $checked = 'checked="checked"';
        $disabled = 'disabled="true"';
    }
    else
    {
        $checked = '';
        $disabled = '';
    }

		$mysqli_checked = 'checked="checked"';

    $smarty->assign('installer_lang', 	$installer_lang);
    $smarty->assign('checked', 					$checked);
    $smarty->assign('disabled', 				$disabled);
		$smarty->assign('mysqli_checked', 	$mysqli_checked);
		$smarty->assign('mysql_checked', 		$mysql_checked);
    $smarty->display('setting.php');

    break;

case 'get_db_list' :
    $db_host    = isset($_POST['db_host']) ? trim($_POST['db_host']) : '';
    $db_port    = isset($_POST['db_port']) ? trim($_POST['db_port']) : '';
    $db_user    = isset($_POST['db_user']) ? trim($_POST['db_user']) : '';
    $db_pass    = isset($_POST['db_pass']) ? trim($_POST['db_pass']) : '';


    include_once(DIR . '/includes/class_json.php');
    $json = new JSON();

    $databases  = get_db_list($db_host, $db_port, $db_user, $db_pass);
    if ($databases === false)
    {
        echo $json->encode(implode(',', $err->get_all()));
    }
    else
    {
        $result = array('msg'=> 'OK', 'list'=>implode(',', $databases));
        echo $json->encode($result);
    }

    break;

case 'create_config_file' :
    $db_host    = isset($_POST['db_host'])      ?   trim($_POST['db_host']) : 'localhost';
    $db_port    = isset($_POST['db_port'])      ?   trim($_POST['db_port']) : '3306';
    $db_user    = isset($_POST['db_user'])      ?   trim($_POST['db_user']) : 'root';
    $db_pass    = isset($_POST['db_pass'])      ?   trim($_POST['db_pass']) : '';
    $db_name    = isset($_POST['db_name'])      ?   trim($_POST['db_name']) : '';
    $db_prefix  = isset($_POST['db_prefix'])    ?   trim($_POST['db_prefix']) : 'skyuc_';
		$database   = isset($_POST['db_database'])  ?   trim($_POST['db_database']) : 'mysql';
		$email		= isset($_POST['admin_email'])	?		trim($_POST['admin_email']) : 'admin@yourdomain.com';

    $result = create_config_file($db_host, $db_port, $db_user, $db_pass, $db_name, $db_prefix,$database,$email);
    if ($result === false)
    {
        echo implode(',', $err->get_all());
    }
    else
    {
        echo 'OK';
    }

    break;

case 'create_database' :
    $db_host    = isset($_POST['db_host'])      ?   trim($_POST['db_host']) : '';
    $db_port    = isset($_POST['db_port'])      ?   trim($_POST['db_port']) : '';
    $db_user    = isset($_POST['db_user'])      ?   trim($_POST['db_user']) : '';
    $db_pass    = isset($_POST['db_pass'])      ?   trim($_POST['db_pass']) : '';
    $db_name    = isset($_POST['db_name'])      ?   trim($_POST['db_name']) : '';

    $result = create_database($db_host, $db_port, $db_user, $db_pass, $db_name);
    if ($result === false)
    {
        echo implode(',', $err->get_all());
    }
    else
    {
        echo 'OK';
    }

    break;

case 'install_base_data' :
    $system_lang = isset($_POST['system_lang']) ? $_POST['system_lang'] : 'zh_cn';

    if (is_file(DIR . '/install/data/data_' . $system_lang . '.sql'))
    {
        $data_path = DIR . '/install/data/data_' . $system_lang . '.sql';
    }
    else
    {
        $data_path = DIR . '/install/data/data_zh_cn.sql';
    }

    $sql_files = array(
        DIR . '/install/data/structure.sql',
        $data_path
    );

    $result = install_data($sql_files);

    if ($result === false)
    {
        echo implode(',', $err->get_all());
    }
    else
    {
        echo 'OK';
    }

    break;

case 'create_admin_passport' :
    $admin_name         = isset($_POST['admin_name'])       ? trim($_POST['admin_name']) : '';
    $admin_password     = isset($_POST['admin_password'])   ? trim($_POST['admin_password']) : '';
    $admin_password2    = isset($_POST['admin_password2'])  ? trim($_POST['admin_password2']) : '';
    $admin_email        = isset($_POST['admin_email'])      ? trim($_POST['admin_email']) : '';

    $result = create_admin_passport($admin_name, $admin_password,$admin_password2, $admin_email);
    if ($result === false)
    {
        echo implode(',', $err->get_all());
    }
    else
    {
        echo 'OK';
    }

    break;

case 'do_others' :
    $system_lang = isset($_POST['system_lang'])     ? $_POST['system_lang'] : 'zh_cn';
    $captcha = isset($_POST['disable_captcha'])     ? intval($_POST['disable_captcha']) : '0';
    $result = do_others($system_lang, $captcha);
    if ($result === false)
    {
        echo implode(',', $err->get_all());
    }
    else
    {
        echo 'OK';
    }

    break;

case 'done' :
    $result = deal_aftermath();
    if ($result === false)
    {
        $err_msg = implode(',', $err->get_all());
        $smarty->assign('err_msg', $err_msg);
        $smarty->display('error.php');
    }
    else
    {
        $smarty->display('done.php');
    }

    break;

case 'error' :
    $err_msg = implode(',', $err->get_all());
    $smarty->assign('err_msg', $err_msg);
    $smarty->display('error.php');

    break;

default :
    die('Error, unknown step!');
}

?>