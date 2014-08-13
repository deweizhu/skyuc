<?php
// #######################################################################
// ######################## category.php 私有函数      #######################
// #######################################################################


/**
 * 检查分类是否已经存在
 *
 * @param   string      $cat_name       分类名称
 * @param   integer     $parent_cat     上级分类
 * @param   integer     $exclude        排除的分类ID
 *
 * @return  boolean
 */
function cat_exists($cat_name, $parent_cat, $exclude = 0) {
	
	$sql = 'SELECT COUNT(*) AS count FROM ' . TABLE_PREFIX . 'category' . " WHERE parent_id = '$parent_cat' AND cat_name = '" . $GLOBALS ['db']->escape_string ( $cat_name ) . "' AND cat_id<>'$exclude'";
	$total = $GLOBALS ['db']->query_first ( $sql );
	return ($total ['count'] > 0) ? true : false;
}
?>