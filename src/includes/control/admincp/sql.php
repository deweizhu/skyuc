<?php
// #######################################################################
// ######################## sql.php 私有函数      ############################
// #######################################################################


/**
 * 执行SQL查询语句
 *
 * @access  public
 * @param
 *
 * @return void
 */
function assign_sql($sql) {
	
	$sql = stripslashes ( $sql );
	$GLOBALS ['smarty']->assign ( 'sql', $sql );
	
	// 解析查询项
	$sql = str_replace ( "\r", '', $sql );
	$query_items = explode ( ";\n", $sql );
	foreach ( $query_items as $key => $value ) {
		if (empty ( $value )) {
			unset ( $query_items [$key] );
		}
	}
	// 如果是多条语句，拆开来执行
	if (count ( $query_items ) > 1) {
		foreach ( $query_items as $key => $value ) {
			if ($GLOBALS['db']->query ( $value, 'SILENT' )) {
				$GLOBALS ['smarty']->assign ( 'type', 1 );
			} else {
				$GLOBALS ['smarty']->assign ( 'type', 0 );
				$GLOBALS ['smarty']->assign ( 'error', $GLOBALS['db']->error () );
				return;
			}
		}
		return; //退出函数
	}
	
	// 单独一条sql语句处理
	if (preg_match ( "/^(?:UPDATE|DELETE|TRUNCATE|ALERT|DROP|FLUSH|INSERT|REPLACE|SET|CREATE)\\s+/i", $sql )) {
		if ($GLOBALS['db']->query ( $sql, 'SILENT' )) {
			$GLOBALS ['smarty']->assign ( 'type', 1 );
		} else {
			$GLOBALS ['smarty']->assign ( 'type', 0 );
			$GLOBALS ['smarty']->assign ( 'error', $GLOBALS['db']->error () );
		}
	} else {
		$data = $GLOBALS['db']->query_all ( $sql );
		if (empty ( $data )) {
			$GLOBALS ['smarty']->assign ( 'type', 0 );
			$GLOBALS ['smarty']->assign ( 'error', $GLOBALS['db']->error () );
		} else {
			$result = '';
			if (is_array ( $data ) && isset ( $data [0] ) === true) {
				$result = "<table> \n <tr>";
				$keys = array_keys ( $data [0] );
				for($i = 0, $num = count ( $keys ); $i < $num; $i ++) {
					$result .= "<th>" . $keys [$i] . "</th>\n";
				}
				$result .= "</tr> \n";
				foreach ( $data as $data1 ) {
					$result .= "<tr>\n";
					foreach ( $data1 as $value ) {
						$result .= "<td>" . $value . "</td>";
					}
					$result .= "</tr>\n";
				}
				$result .= "</table>\n";
			} else {
				$result = "<center><h3>" . $GLOBALS ['_LANG'] ['no_data'] . "</h3></center>";
			}
			
			$GLOBALS ['smarty']->assign ( 'type', 2 );
			$GLOBALS ['smarty']->assign ( 'result', $result );
		}
	}
}

?>