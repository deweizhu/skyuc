<?php
// #######################################################################
// ######################## word_batch.php 私有函数    #################
// #######################################################################


/**
 * 关键词处理函数
 *
 * @access  public
 * @param   integer $page
 * @param   integer $page_size
 * @param   boolen  $keyword      是否生成关键词
 * @param   boolen  $description  是否生成描述看点
 * @param   boolen  $silent     是否执行能忽略错误
 *
 * @return void
 */
function process_word($page = 1, $page_size = 100, $keyword = 1, $description = 1, $silent = 1) {
	
	$sql = 'SELECT show_id, title, detail FROM ' . TABLE_PREFIX . 'show' . " AS m WHERE 1 " . $GLOBALS ['show_where'];
	$sql = $GLOBALS ['db']->query_limit ( $sql, $page_size, ($page - 1) * $page_size );
	$res = $GLOBALS ['db']->query_read ( $sql );
	while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
		// 详情描述看点
		if ($description) {
			$row ['description'] = sub_str ( html2text ( $row ['detail'] ), 250 );
			
			if (empty ( $row ['description'] )) {
				//出错返回
				$msg = sprintf ( $GLOBALS ['_LANG'] ['error_keywords'], $row ['show_id'], $row ['title'] ) . "\n";
				if ($silent) {
					$GLOBALS ['err_msg'] [] = $msg;
					continue;
				} else {
					make_json_error ( $msg );
				}
			
			}
		
		}
		
		// 关键词
		if ($keyword) {
			$row ['keywords'] = splitword ( $row ['title'], sub_str ( html2text ( $row ['detail'] ), 250 ) );
			
			if (empty ( $row ['keywords'] )) {
				//出错返回
				$msg = sprintf ( $GLOBALS ['_LANG'] ['error_description'], $row ['show_id'], $row ['title'] ) . "\n";
				if ($silent) {
					$GLOBALS ['err_msg'] [] = $msg;
					continue;
				} else {
					make_json_error ( $msg );
				}
			
			}
		}
		
		//更新数据库:start
		$sql = 'UPDATE ' . TABLE_PREFIX . 'show	SET ';
		if (! empty ( $row ['keywords'] )) {
			$sql .= " keywords = '" . $GLOBALS ['db']->escape_string ( $row ['keywords'] ) . "', ";
		
		}
		if (! empty ( $row ['description'] )) {
			$sql .= " description = '" . $GLOBALS ['db']->escape_string ( $row ['description'] ) . "'";
		}
		if (! empty ( $row ['keyword'] ) or ! empty ( $row ['description'] )) {
			$sql .= "  WHERE show_id = '" . $row ['show_id'] . "'";
			$GLOBALS ['db']->query_write ( $sql );
		}
	
		//更新数据库:end
	}

}

?>