<?php

/**
 * SKYUC! 管理中心菜单数组
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

$modules ['01_film'] ['02_show_add'] = 'show.php?act=add';
$modules ['01_film'] ['01_show_list'] = 'show.php?act=list';
$modules ['01_film'] ['03_show_trash'] = 'show.php?act=trash';
$modules ['01_film'] ['06_category_list'] = 'category.php?act=list';
$modules ['01_film'] ['05_server'] = 'server.php?act=list';
$modules ['01_film'] ['05_comment_manage'] = 'comment_manage.php?act=list';
$modules ['01_film'] ['tag_list'] = 'tag_manage.php?act=list';
$modules ['01_film'] ['08_batch_pic'] = 'picture_batch.php';
$modules ['01_film'] ['08_batch_word'] = 'word_batch.php';
$modules ['01_film'] ['09_subject'] = 'subject.php?act=list';
$modules ['01_film'] ['show_script'] = 'gen_show_script.php?act=setup';

$modules ['02_member'] ['02_user_list'] = 'users.php?act=list';
$modules ['02_member'] ['03_user_add'] = 'users.php?act=add';
$modules ['02_member'] ['04_user_msg'] = 'user_msg.php?act=list_all';
$modules ['02_member'] ['05_user_rank_list'] = 'user_rank.php?act=list';
$modules ['02_member'] ['06_user_log'] = 'user_log.php?act=list';
$modules ['02_member'] ['07_user_integrate'] = 'integrate.php?act=list';
$modules ['02_member'] ['user_online'] = 'user_online.php?act=list';
$modules ['02_member'] ['mail_batch'] = 'mail_batch.php?act=list';

$modules ['03_netbar'] ['02_netbar_add'] = 'netbar.php?act=add';
$modules ['03_netbar'] ['03_netbar_list'] = 'netbar.php?act=list';

$modules ['04_card'] ['02_cardlist'] = 'card.php?act=list';
$modules ['04_card'] ['03_cardlog'] = 'card.php?act=log';

$modules ['05_order'] ['02_order_list'] = 'order.php?act=list';
$modules ['05_order'] ['05_user_account'] = 'user_account.php?act=list';
$modules ['05_order'] ['payment_list'] = 'payment.php?act=list';

$modules ['06_collection'] ['collection_col'] = 'col_main.php?act=list';
$modules ['06_collection'] ['collection_down'] = 'col_url.php?act=list';
$modules ['06_collection'] ['collection_monitor'] = 'col_main.php?act=gather';
$modules ['06_collection'] ['collection_nodown'] = 'col_url.php?act=coall';

$modules ['07_article'] ['02_articlecat_list'] = 'articlecat.php?act=list';
$modules ['07_article'] ['03_article_list'] = 'article.php?act=list';
$modules ['07_article'] ['06_vote_list'] = 'vote.php?act=list';

$modules ['08_ads'] ['ad_position'] = 'ad_position.php?act=list';
$modules ['08_ads'] ['ad_list'] = 'ads.php?act=list';
$modules ['08_ads'] ['js_list'] = 'ads.php?act=listjs';

$modules ['09_template'] ['02_template_list'] = 'template.php?act=list';
$modules ['09_template'] ['03_template_setup'] = 'template.php?act=setup';
$modules ['09_template'] ['04_template_library'] = 'template.php?act=library';
$modules ['09_template'] ['04_template_mail'] = 'template.php?act=mail';
$modules ['09_template'] ['06_template_backup'] = 'template.php?act=backup_setting';
$modules ['09_template'] ['template_languages'] = 'edit_languages.php?act=list';

$modules ['10_system'] ['02_setting'] = 'setting.php?act=list_edit';
$modules ['10_system'] ['07_check_file_priv'] = 'check_file_priv.php?act=check';
$modules ['10_system'] ['sitemap'] = 'sitemap.php?act=google';
$modules ['10_system'] ['sitemap_baidu'] = 'sitemap.php?act=baidu';
$modules ['10_system'] ['07_friendlink_list'] = 'friend_link.php?act=list';
$modules ['10_system'] ['flow_stats'] = 'flow_stats.php?act=view';
$modules ['10_system'] ['flow_searchengine_stats'] = 'searchengine_stats.php?act=view';
$modules ['10_system'] ['flashplay'] = 'flashplay.php?act=list';
$modules ['10_system'] ['navigator'] = 'navigator.php?act=list';
$modules ['10_system'] ['captcha_manage'] = 'humanverify.php?act=main';
$modules ['10_system'] ['dbsearch_core'] = 'dbsearch.php?act=main';
$modules ['10_system'] ['player_manage'] = 'player.php?act=list';

$modules ['11_priv_admin'] ['02_admin_add'] = 'privilege.php?act=add';
$modules ['11_priv_admin'] ['03_admin_list'] = 'privilege.php?act=list';
$modules ['11_priv_admin'] ['04_admin_logs'] = 'admin_logs.php?act=list';

$modules ['12_data'] ['01_db_manage'] = 'database.php?act=backup';
$modules ['12_data'] ['02_db_restore'] = 'database.php?act=restore';
$modules ['12_data'] ['03_db_optimize'] = 'database.php?act=optimize';
$modules ['12_data'] ['04_sql_query'] = 'sql.php?act=main';
$modules ['12_data'] ['05_sql_replace'] = 'sql.php?act=replace';
?>