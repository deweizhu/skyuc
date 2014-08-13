<?php
/**
 * SKYUC! 广告处理文件
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */
// ####################### 设置 PHP 环境 ###########################
error_reporting(E_ALL & ~ E_NOTICE);
// #################### 定义重要常量 #######################
define('THIS_SCRIPT', 'affiche');
define('CSRF_PROTECTION', true);
define('CSRF_SKIP_LIST', '');
define('LOCATION_BYPASS', 1);
define('SKIP_SMARTY', 1);
define('SKIP_DEFAULTDATASTORE', 1);
define('INGORE_VISIT_STATS', true);
require (dirname(__FILE__) . '/global.php');
$skyuc->input->clean_array_gpc('g',
array('ad_id' => TYPE_INT, 'id' => TYPE_UINT));
$ad_id = $skyuc->GPC['ad_id'];
$user_id = iif($skyuc->GPC['id'] == 0, $skyuc->session->vars['userid'],
$skyuc->GPC['id']);
// 没有用户ID和指定广告ID跳转地址
if ($user_id == 0 && $ad_id == 0) {
    header("Location: index.php\n");
    exit();
}
// 没有指定广告ID，但是有用户ID
if ($ad_id == 0 && $user_id > 0) {
    // 用户ID检查
    $sql = 'SELECT user_name FROM ' . TABLE_PREFIX . 'users' .
     ' WHERE user_id =' . $user_id;
    $row = $db->query_first_slave($sql);
    if (! empty($row['user_name'])) {
        skyuc_setcookie('referrer', $row['user_name'], TIMENOW + 86400);
        // 存在COOKIE
        if (TIMENOW >
         $skyuc->input->clean_gpc('c', COOKIE_PREFIX . 'affiche_ip', TYPE_STR)) {
            // 更新积分增加１-10
            $add_point = mt_rand(1, 10);
            $sql = 'UPDATE ' . TABLE_PREFIX . 'users' .
             ' SET pay_point = pay_point+(' . $add_point . ')  WHERE user_id =' .
             $user_id;
            $db->query_write($sql);
            //设置COOKIE 防止重复提交
            skyuc_setcookie('affiche_ip', TIMENOW + 86400);
        }
        header("Location: user.php?act=register\n");
        exit();
    } else {
        header("Location: index.php\n");
        exit();
    }
}
/// act 操作项的初始化
$skyuc->input->clean_array_gpc('g',
array('act' => TYPE_STR, 'charset' => TYPE_STR, 'type' => TYPE_UINT,
'from' => TYPE_STR, 'show_id' => TYPE_UINT, 'uri' => TYPE_STR));
if ($skyuc->GPC['act'] == 'js') {
    // 编码转换
    if (empty($skyuc->GPC['charset'])) {
        $skyuc->GPC['charset'] = 'UTF8';
    }
    header(
    'Content-type: application/x-javascript; charset=' . $skyuc->GPC['charset'] ==
     'UTF8' ? 'utf-8' : $skyuc->GPC['charset']);
    $url = get_url();
    $str = '';
    // 取得广告的信息
    $sql = 'SELECT ad.ad_id, ad.ad_name, ad.ad_link, ad.ad_code ' . ' FROM ' .
     TABLE_PREFIX . 'ad' . ' AS ad ' . ' LEFT JOIN ' . TABLE_PREFIX .
     'ad_position' . ' AS p ON ad.position_id = p.position_id ' .
     ' WHERE ad.ad_id = ' . $ad_id;
    $ad_info = $db->query_first_slave($sql);
    if (! empty($ad_info)) {
        // 转换编码
        if ($skyuc->GPC['charset'] != 'UTF8') {
            $ad_info['ad_name'] = skyuc_iconv('UTF8', $skyuc->GPC['charset'],
            $ad_info['ad_name']);
            $ad_info['ad_code'] = skyuc_iconv('UTF8', $skyuc->GPC['charset'],
            $ad_info['ad_code']);
        }
        // 初始化广告来源
        $skyuc->GPC['from'] = ! empty($skyuc->GPC['from']) ? urlencode(
        $skyuc->GPC['from']) : '';
        $str = '';
        switch ($skyuc->GPC['type']) {
            case 0:
                // 图片广告
                $src = (strpos($ad_info['ad_code'], 'http://') ===
                 false && strpos($ad_info['ad_code'], 'https://') === false) ? $url .
                 $skyuc->config['Misc']['imagedir'] . '/afficheimg/' .
                 $ad_info['ad_code'] : $ad_info['ad_code'];
                $str = '<a href="' . $url . 'affiche.php?ad_id=' .
                 $ad_info['ad_id'] . '&from=' . $skyuc->GPC['from'] . '&uri=' .
                 urlencode($ad_info['ad_link']) . '" target="_blank">' .
                 '<img src="' . $src . '" border="0" alt="' . $ad_info['ad_name'] .
                 '" /></a>';
                break;
            case 1:
                // Falsh广告
                $src = (strpos($ad_info['ad_code'], 'http://') ===
                 false && strpos($ad_info['ad_code'], 'https://') === false) ? $url .
                 $skyuc->config['Misc']['imagedir'] . '/afficheimg/' .
                 $ad_info['ad_code'] : $ad_info['ad_code'];
                $str = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" <param name="movie" value="' .
                 $src . '"><param name="quality" value="high"><embed src="' .
                 $src .
                 '" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed></object>';
                break;
            case 2:
                // 代码广告
                $str = $ad_info['ad_code'];
                break;
            case 3:
                // 文字广告
                $str = '<a href="' . $url . 'affiche.php?ad_id=' .
                 $ad_info['ad_id'] . '&from=' . $skyuc->GPC['from'] . '&uri=' .
                 urlencode($ad_info['ad_link']) . '" target="_blank">' .
                 nl2br(htmlspecialchars(addslashes($ad_info['ad_code']))) .
                 '</a>';
                break;
        }
    }
    echo "document.writeln('" . $str . "');";
} else {
    // 获取投放站点的名称
    $site_name = $db->escape_string($skyuc->GPC['from']);
    // 影片的ID
    $show_id = $skyuc->GPC['show_id'];
    // 如果是站外JS
    if ($ad_id == - 1) {
        // 查询是否有相同的投放站点
        $sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX .
         'adsense' . " WHERE from_ad = -1 AND referer = '" . $site_name . "'";
        $total = $db->query_first_slave($sql);
        if ($total['total'] > 0) {
            $sql = 'UPDATE ' . TABLE_PREFIX . 'adsense' .
             " SET clicks = clicks + 1 WHERE from_ad = '-1' AND referer = '" .
             $site_name . "'";
        } else {
            $sql = 'INSERT INTO ' . TABLE_PREFIX . 'adsense' .
             "(from_ad, referer, clicks) VALUES ('-1', '" . $site_name .
             "', '1')";
        }
        $db->query_write($sql);
        $uri = build_uri('show', array('mid' => $show_id));
        header("Location: $uri\n");
        exit();
    } else {
        // 更新站内广告的点击次数
        $db->query_write(
        'UPDATE ' . TABLE_PREFIX . 'ad' .
         ' SET click_count = click_count + 1 WHERE ad_id = ' . $ad_id);
        // 如果有当前广告的ID以及相同的站点存在,只更新点击次数
        $sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX .
         'adsense' . " WHERE from_ad = '" . $ad_id . "' AND referer = '" .
         $site_name . "'";
        $total = $db->query_first_slave($sql);
        if ($total['total'] > 0) {
            $sql = 'UPDATE ' . TABLE_PREFIX . 'adsense' .
             ' SET clicks = clicks + 1' . " WHERE from_ad = '" . $ad_id .
             "' AND referer = '" . $site_name . "'";
        } else {
            $sql = 'INSERT INTO ' . TABLE_PREFIX . 'adsense' .
             ' (from_ad, referer, clicks) ' . "VALUES ('" . $ad_id . "', '" .
             $site_name . "', '1')";
        }
        $db->query_write($sql);
        // 跳转到广告的链接页面
        if (! empty($skyuc->GPC['uri'])) {
            $uri = (strpos($skyuc->GPC['uri'], 'http://') === false &&
             strpos($skyuc->GPC['uri'], 'https://') === false) ? get_http() .
             urldecode($skyuc->GPC['uri']) : urldecode($skyuc->GPC['uri']);
        } else {
            $uri = get_url();
        }
        header("Location: $uri\n");
        exit();
    }
}
?>