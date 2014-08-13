<?php
/**
 * SKYUC! 影片详情文件
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
define('THIS_SCRIPT', 'show');
define('CSRF_PROTECTION', true);
define('CSRF_SKIP_LIST', '');
//define('SMARTY_CACHE',	true);
// 从缓存中获取指定数据
$specialtemplates = array('players', 'servers');
require (dirname(__FILE__) . '/global.php');
require (DIR . '/includes/control/show.php');
require (DIR . '/includes/functions_search.php');
/*------------------------------------------------------ */
//-- ID过滤
/*------------------------------------------------------ */
$mov_id = $skyuc->input->clean_gpc('g', 'id', TYPE_UINT);
/*------------------------------------------------------ */
//-- PROCESSOR
/*------------------------------------------------------ */
// 获得影片的信息
$show = get_show_info($mov_id);
if (empty($show)) {
    // 如果没有找到任何记录则跳回到首页
    header("Location: ./\n");
    exit();
}
/* meta */
$smarty->assign('keywords', htmlspecialchars($show['keywords']));
$smarty->assign('description', htmlspecialchars($show['description']));
// 载入网站信息
$catlist = array();
foreach (get_parent_cats($show['cat_id']) as $k => $v) {
    $catlist[] = $v['cat_id'];
}
assign_template('c', $catlist);

$children = get_children($show['cat_id']);
if (! empty($skyuc->template['show']['tree_detail'])) {
    $smarty->assign('categories', get_categories_tree()); // 影片分类树
    $smarty->assign('area_list',
    explode('|', $skyuc->options['show_area'])); // 地区分类树
    $smarty->assign('lang_list',
    explode('|', $skyuc->options['show_lang'])); // 语言分类树
}
if (! empty($skyuc->template['show']['top10_detail'])) {
    $smarty->assign('top_month',
    get_top_new_hot('top_detail', $children, 30)); // 月点播排行
}
if (! empty($skyuc->template['list']['new10_cate'])) {
    $smarty->assign('new_show', get_top_new_hot('new')); // 最近更新影片
}
if (! empty($skyuc->template['show']['related_director'])) {
    $smarty->assign('related_director',
    get_linked_show($show['director'], $mov_id)); // 相关导演的影片
}
if (! empty($skyuc->template['show']['related_actor'])) {
    $smarty->assign('related_actor',
    get_linked_show($show['actor'], $mov_id)); // 相关演员的影片
}
if (! empty($skyuc->template['show']['same_movie'])) {
    $smarty->assign('same_movie', get_same_movie($mov_id)); //看了本片的用户还看过
}
if (! empty($show['server_id'])) {
    $show['server_id'] = explode(',', $show['server_id']);
}
if (! empty($show['player'])) {
    $show['player'] = explode(',', $show['player']);
}
// 处理影片 数据地址
if (! empty($show['data'])) {
    // 地址分组
    $show['data'] = display_url_data($show['data'], $show['player'],
    $show['server_id'], true);
}
$show['title_style'] = add_style($show['title'], $show['title_style']);
$position = assign_ur_here($show['cat_id'], $show['title']);
$smarty->assign('page_title', $position['title']); // 页面标题
$smarty->assign('ur_here', $position['ur_here']); // 当前位置
$smarty->assign('show', $show);
$smarty->assign('id', $mov_id);
$smarty->assign('type', 0);
$smarty->assign('playerwidth', $skyuc->options['playerwidth']);
$smarty->assign('playerheight', $skyuc->options['playerheight']);
$smarty->assign('playlist', $show['data']); //地址列表
$smarty->assign('tags',
get_tags($mov_id, 0, $skyuc->options['related_tags'])); //影片标签
// 页面中的动态内容
assign_dynamic('show');
/* 记录浏览历史 */
if (! empty($_COOKIE[COOKIE_PREFIX . 'history'])) {
    $history = explode(',', $_COOKIE[COOKIE_PREFIX . 'history']);
    array_unshift($history, $mov_id);
    $history = array_unique($history);
    while (count($history) > $skyuc->options['history_number']) {
        array_pop($history);
    }
    skyuc_setcookie('history', implode(',', $history), TIMENOW + 86400 * 7);
} else {
    skyuc_setcookie('history', $mov_id, TIMENOW + 86400 * 7);
}
$smarty->display('show.dwt');
?>