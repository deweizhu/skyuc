<?php

/**
 * SKYUC! 影片分类管理语言文件
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
*/
/* 影片分类字段信息 */
$_LANG['cat_id'] = '编号';
$_LANG['cat_name'] = '分类名称';
$_LANG['keywords'] = '关键字';
$_LANG['cat_desc'] = '分类描述';
$_LANG['show_number'] = '影片数量';
$_LANG['parent_id'] = '上级分类';
$_LANG['sort_order'] = '排序';
$_LANG['delete_info'] = '删除选中';
$_LANG['category_edit'] = '编辑影片分类';
$_LANG['add_show'] = '添加影片';
$_LANG['move_show'] = '转移影片';
$_LANG['cat_top'] = '设为顶级分类';
$_LANG['cat_style'] = '分类的样式表文件';
$_LANG['is_show'] = '启用';
$_LANG['show_in_nav'] = '是否显示在导航栏';

$_LANG['nav'] = '导航栏';

$_LANG['back_list'] = '返回分类列表';
$_LANG['continue_add'] = '继续添加分类';

$_LANG['notice_style'] = '您可以为每一个影片分类指定一个样式表文件。例如文件存放在 templates 目录下则输入：templates/default/style.css';

/* 操作提示信息 */
$_LANG['catname_empty'] = '分类名称不能为空!';
$_LANG['catname_exist'] = '已存在相同的分类名称!';
$_LANG["parent_isleaf"] = '所选分类不能是末级分类!';
$_LANG["cat_isleaf"] = '不是末级分类或者此分类下还存在有影片,您不能删除!';
$_LANG["cat_noleaf"] = '底下还有其它子分类,不能修改为末级分类!';
$_LANG["is_leaf_error"] = '所选择的上级分类不能是当前分类的下级分类!';
$_LANG["cat_leaf_same"] = '所选择的上级分类不能和当前分类相同!';
$_LANG["cat_is_show"] = '当前分类下有影片存在,您不能将它改为非末级!';

$_LANG['catadd_succed'] = '新影片分类添加成功!';
$_LANG['catedit_succed'] = '影片分类编辑成功!';
$_LANG['catdrop_succed'] = '影片分类删除成功!';
$_LANG['catremove_succed'] = '影片分类转移成功!';
$_LANG['move_cat_success'] = '转移影片分类已成功完成!';

$_LANG['cat_move_desc'] = '什么是转移影片分类?';
$_LANG['select_source_cat'] = '选择要转移的分类';
$_LANG['select_target_cat'] = '选择目标分类';
$_LANG['source_cat'] = '从此分类';
$_LANG['target_cat'] = '转移到';
$_LANG['start_move_cat'] = '开始转移';
$_LANG['cat_move_notic'] = '在添加影片或者在影片管理中,如果需要对影片的分类进行变更,那么你可以通过此功能,正确管理你的影片分类<br />转移影片分类必须是在末级分类之间进行。';

$_LANG['cat_move_empty'] = '你没有正确选择影片分类!';

/*JS 语言项*/
$_LANG['js_languages']['catname_empty'] = '分类名称不能为空!';
$_LANG['js_languages']['is_leafcat'] = '您选定的分类是一个末级分类。\r\n新分类的上级分类不能是一个末级分类';
$_LANG['js_languages']['not_leafcat'] = '您选定的分类不是一个末级分类。\r\n影片的分类转移只能在末级分类之间才可以操作。';

?>