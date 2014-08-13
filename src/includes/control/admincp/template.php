<?php
// #######################################################################
// ######################## template.php 私有函数      ###################
// #######################################################################


/**
 * 加载指定的模板内容
 *
 * @access  public
 * @param   string  $temp   邮件模板的ID
 * @return  array
 */
function load_template($temp_id) {
	$sql = 'SELECT template_subject, template_content, is_html ' . 'FROM ' . TABLE_PREFIX . 'template_mail' . " WHERE template_id='$temp_id'";
	return $GLOBALS ['db']->query_first ( $sql );
}
?>