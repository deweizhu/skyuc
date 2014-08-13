<?php

/**
 * SKYUC! 影片分類管理語言文件
 * ============================================================================
 * 版權所有 (C) 2012 天空網絡，並保留所有權利。
 * 網站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
*/
/* 影片分類字段信息 */
$_LANG['cat_id'] = '編號';
$_LANG['cat_name'] = '分類名稱';
$_LANG['keywords'] = '關鍵字';
$_LANG['cat_desc'] = '分類描述';
$_LANG['show_number'] = '影片數量';
$_LANG['parent_id'] = '上級分類';
$_LANG['sort_order'] = '排序';
$_LANG['delete_info'] = '刪除選中';
$_LANG['category_edit'] = '編輯影片分類';
$_LANG['add_show'] = '添加影片';
$_LANG['move_show'] = '轉移影片';
$_LANG['cat_top'] = '設為頂級分類';
$_LANG['cat_style'] = '分類的樣式表文件';
$_LANG['is_show'] = '啟用';
$_LANG['show_in_nav'] = '是否顯示在導航欄';

$_LANG['nav'] = '導航欄';

$_LANG['back_list'] = '返回分類列表';
$_LANG['continue_add'] = '繼續添加分類';

$_LANG['notice_style'] = '您可以為每一個影片分類指定一個樣式表文件。例如文件存放在 templates 目錄下則輸入：templates/default/style.css';

/* 操作提示信息 */
$_LANG['catname_empty'] = '分類名稱不能為空!';
$_LANG['catname_exist'] = '已存在相同的分類名稱!';
$_LANG["parent_isleaf"] = '所選分類不能是末級分類!';
$_LANG["cat_isleaf"] = '不是末級分類或者此分類下還存在有影片,您不能刪除!';
$_LANG["cat_noleaf"] = '底下還有其它子分類,不能修改為末級分類!';
$_LANG["is_leaf_error"] = '所選擇的上級分類不能是當前分類的下級分類!';
$_LANG["cat_leaf_same"] = '所選擇的上級分類不能和當前分類相同!';
$_LANG["cat_is_show"] = '當前分類下有影片存在,您不能將它改為非末級!';

$_LANG['catadd_succed'] = '新影片分類添加成功!';
$_LANG['catedit_succed'] = '影片分類編輯成功!';
$_LANG['catdrop_succed'] = '影片分類刪除成功!';
$_LANG['catremove_succed'] = '影片分類轉移成功!';
$_LANG['move_cat_success'] = '轉移影片分類已成功完成!';

$_LANG['cat_move_desc'] = '什麼是轉移影片分類?';
$_LANG['select_source_cat'] = '選擇要轉移的分類';
$_LANG['select_target_cat'] = '選擇目標分類';
$_LANG['source_cat'] = '從此分類';
$_LANG['target_cat'] = '轉移到';
$_LANG['start_move_cat'] = '開始轉移';
$_LANG['cat_move_notic'] = '在添加影片或者在影片管理中,如果需要對影片的分類進行變更,那麼你可以通過此功能,正確管理你的影片分類<br />轉移影片分類必須是在末級分類之間進行。';

$_LANG['cat_move_empty'] = '你沒有正確選擇影片分類!';

/*JS 語言項*/
$_LANG['js_languages']['catname_empty'] = '分類名稱不能為空!';
$_LANG['js_languages']['is_leafcat'] = '您選定的分類是一個末級分類。\r\n新分類的上級分類不能是一個末級分類';
$_LANG['js_languages']['not_leafcat'] = '您選定的分類不是一個末級分類。\r\n影片的分類轉移只能在末級分類之間才可以操作。';

?>