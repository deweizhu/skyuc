<?php
/**
 * SKYUC! 系统环境检测函数库
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

$checking_dirs = array(
                    'admincp',
                    'upload/afficheimg',
                    'upload/article',
                    'upload/feedbackimg',
                    'upload/posters',
                    'data',
                    'data/caches',
                    'data/compiled',
                    'data/compiled/admincp',
                    'data/images',
                    'data/sqldata',
                    'templates',
                    'templates/backup',
                    'templates/backup/library',
                    'templates/default',
                    );

/**
 * 检查目录的读写权限
 *
 * @access  public
 * @param   array     $checking_dirs     目录列表
 * @return  array     检查后的消息数组，
 *    成功格式形如array('result' => 'OK', 'detail' => array(array($dir, $_LANG['can_write']), array(), ...))
 *    失败格式形如array('result' => 'ERROR', 'd etail' => array(array($dir, $_LANG['cannt_write']), array(), ...))
 */
function check_dirs_priv($checking_dirs)
{
    global $_LANG;
    $msgs = array('result' => 'OK', 'detail' => array());

    foreach ($checking_dirs AS $dir)
    {
        if (!file_exists(DIR.'/'  . $dir))
        {
            $msgs['result'] = 'ERROR';
            $msgs['detail'][] = array($dir, $_LANG['not_exists']);
            continue;
        }

        if (file_mode_info(DIR.'/' . $dir) < 2)
        {
            $msgs['result'] = 'ERROR';
            $msgs['detail'][] = array($dir, $_LANG['cannt_write']);
        }
        else
        {
            $msgs['detail'][] = array($dir, $_LANG['can_write']);
        }
    }

    return $msgs;
}

/**
 * 检查模板的读写权限
 *
 * @access  public
 * @param   array      $templates_root        模板文件类型所在的根路径数组，形如：array('dwt'=>'', 'lbi'=>'')
 * @return  array      检查后的消息数组，全部可写为空数组，否则是一个以不可写的文件路径组成的数组
 */
function check_templates_priv($templates_root)
{
    global $_LANG;

    $msgs = array();
    $filename = '';
    $filepath = '';

    foreach ($templates_root as $tpl_type => $tpl_root)
    {
        if (!file_exists($tpl_root))
        {
            $msgs[] = str_replace(ROOT_PATH, '', $tpl_root . ' ' . $_LANG['not_exists']);
            continue;
        }

        $tpl_handle = @opendir($tpl_root);
        while (($filename = @readdir($tpl_handle)) !== false)
        {
            $filepath = $tpl_root . $filename;
            if (is_file($filepath)
                    && strrpos($filename, '.' . $tpl_type) !== false
                    && file_mode_info($filepath) < 7)
            {
                $msgs[] = str_replace(ROOT_PATH, '', $filepath . ' ' . $_LANG['cannt_write']);
            }
        }
        @closedir($tpl_handle);
    }

    return $msgs;
}

/**
 *  检查特定目录是否有执行rename函数权限
 *
 * @access  public
 * @param   void
 *
 * @return void
 */
function check_rename_priv()
{
    // 获取要检查的目录 *
    $dir_list   = array();
    $dir_list[] = '/data/caches';
    $dir_list[] = '/data/compiled';
    $dir_list[] = '/data/compiled/admincp';

    /* 获取upload/posters目录下图片目录 */
    $folder = opendir(DIR . '/upload/posters');
    while ($dir = readdir($folder))
    {
        if (is_dir(DIR . '/upload/posters' . $dir) && preg_match('/^[0-9]{6}$/', $dir))
        {
            $dir_list[] = '/upload/posters' . $dir;
        }
    }
    closedir($folder);

    /* 检查目录是否有执行rename函数的权限 */
    $msgs = array();
    foreach ($dir_list AS $dir)
    {
        $mask = file_mode_info(DIR .$dir);
        if ((($mask & 2) > 0 ) && (($mask & 8) < 1))
        {
            /* 只有可写时才检查rename权限 */
            $msgs[] = $dir . ' ' . $GLOBALS['_LANG']['cannt_modify'];
        }
    }
    return $msgs;
}

?>