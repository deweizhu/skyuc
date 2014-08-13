<?php
// #######################################################################
// ######################## integrate.php 私有函数      #########################
// #######################################################################


/**
 * 返回冲突用户列表数据
 *
 * @access  public
 * @param
 *
 * @return void
 */
function conflict_userlist() {
	
	$GLOBALS ['skyuc']->input->clean_gpc ( 'r', 'flag', TYPE_UINT );
	
	$filter ['flag'] = $GLOBALS ['skyuc']->GPC ['flag'];
	$where = ' WHERE flag';
	if ($filter ['flag']) {
		$where .= '=' . $filter ['flag'];
	} else {
		$where .= '>' . 0;
	}
	
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'users' . $where;
	$total = $GLOBALS ['db']->query_first ( $sql );
	$filter ['record_count'] = $total ['total'];
	
	// 分页大小
	$filter = page_and_size ( $filter );
	$sql = 'SELECT user_id, user_name, email, reg_time, flag, alias ' . ' FROM ' . TABLE_PREFIX . 'users' . $where . ' ORDER BY user_id ASC';
	$sql = $GLOBALS ['db']->query_limit ( $sql, $filter ['page_size'], $filter ['start'] );
	$res = $GLOBALS ['db']->query_read ( $sql );
	$list = array ();
	while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
		$row ['reg_date'] = skyuc_date ( $GLOBALS ['skyuc']->options ['date_format'], $row ['reg_time'] );
		$list [] = $row;
	}
	
	$arr = array ('list' => $list, 'filter' => $filter, 'page_count' => $filter ['page_count'], 'record_count' => $filter ['record_count'] );
	
	return $arr;
}

/**
 * 保存整合配置
 *
 * @access  public
 * @param
 *
 * @return void
 */
function save_integrate_config($code, $cfg) {
	
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'setting' . " WHERE code = 'integrate_code'";
	$total = $GLOBALS ['db']->query_first_slave ( $sql );
	if ($total ['total'] == 0) {
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'setting' . ' (code, value) ' . "VALUES ('integrate_code', '" . $GLOBALS ['db']->escape_string ( $code ) . "')";
	} else {
		$sql = 'SELECT value FROM ' . TABLE_PREFIX . 'setting' . " WHERE code = 'integrate_code'";
		$row = $GLOBALS ['db']->query_first ( $sql );
		if ($code != $row ['value']) {
			// 有缺换整合插件，需要把积分设置也清除
			$sql = 'UPDATE ' . TABLE_PREFIX . 'setting' . " SET value = '' WHERE code = 'points_rule'";
			$GLOBALS ['db']->query_write ( $sql );
		}
		$sql = 'UPDATE ' . TABLE_PREFIX . 'setting' . " SET value = '" . $GLOBALS ['db']->escape_string ( $code ) . "' WHERE code = 'integrate_code'";
	}
	
	$GLOBALS ['db']->query_write ( $sql );
	
	// 当前的域名
	if (isset ( $_SERVER ['HTTP_X_FORWARDED_HOST'] )) {
		$cur_domain = $_SERVER ['HTTP_X_FORWARDED_HOST'];
	} elseif (isset ( $_SERVER ['HTTP_HOST'] )) {
		$cur_domain = $_SERVER ['HTTP_HOST'];
	} else {
		if (isset ( $_SERVER ['SERVER_NAME'] )) {
			$cur_domain = $_SERVER ['SERVER_NAME'];
		} elseif (isset ( $_SERVER ['SERVER_ADDR'] )) {
			$cur_domain = $_SERVER ['SERVER_ADDR'];
		}
	}
	
	// 整合对象的域名
	$int_domain = str_replace ( array ('http://', 'https://' ), array ('', '' ), $cfg ['integrate_url'] );
	if (strrpos ( $int_domain, '/' )) {
		$int_domain = substr ( $int_domain, 0, strrpos ( $int_domain, '/' ) );
	}
	
	if ($cur_domain != $int_domain) {
		$same_domain = true;
		$domain = '';
		
		// 域名不一样，检查是否在同一域下
		$cur_domain_arr = explode ( '.', $cur_domain );
		$int_domain_arr = explode ( '.', $int_domain );
		
		if (count ( $cur_domain_arr ) != count ( $int_domain_arr ) || $cur_domain_arr [0] == '' || $int_domain_arr [0] == '') {
			// 域名结构不相同
			$same_domain = false;
		} else {
			// 域名结构一致，检查除第一节以外的其他部分是否相同
			$count = count ( $cur_domain_arr );
			
			for($i = 1; $i < $count; $i ++) {
				if ($cur_domain_arr [$i] != $int_domain_arr [$i]) {
					$domain = '';
					$same_domain = false;
					break;
				} else {
					$domain .= ".$cur_domain_arr[$i]";
				}
			}
		}
		
		if ($same_domain == false) {
			// 不在同一域，设置提示信息
			$cfg ['cookie_domain'] = '';
			$cfg ['cookie_path'] = '/';
		} else {
			$cfg ['cookie_domain'] = $domain;
			$cfg ['cookie_path'] = '/';
		}
	} else {
		$cfg ['cookie_domain'] = '';
		$cfg ['cookie_path'] = '/';
	}
	
	$sql = 'SELECT COUNT(*) AS total FROM ' . TABLE_PREFIX . 'setting' . " WHERE code = 'integrate_config'";
	$total = $GLOBALS ['db']->query_first ( $sql );
	if ($total ['total'] == 0) {
		$sql = 'INSERT INTO ' . TABLE_PREFIX . 'setting' . ' (code, value) ' . "VALUES ('integrate_config', '" . serialize ( $cfg ) . "')";
	} else {
		$sql = 'UPDATE ' . TABLE_PREFIX . 'setting' . " SET value='" . serialize ( $cfg ) . "' " . "WHERE code='integrate_config'";
	}
	
	$GLOBALS ['db']->query_write ( $sql );
	
	build_options ();
	
	return true;
}
?>