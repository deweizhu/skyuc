<?php
/**
 * SKYUC! 前台message.php私有函数库
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

/**
 * 获取留言的详细信息
 *
 * @param   integer $num
 * @param   integer $start
 *
 * @return  array
 */
function get_msg_board_list($num, $start) {

	// 获取留言数据
	$msg = array ();
	$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'feedback' . ' WHERE msg_area=1 ORDER BY msg_time DESC';
	$sql = $GLOBALS['db']->query_limit ( $sql, $num, $start );
	$res = $GLOBALS['db']->query_read_slave ( $sql );

	while ( $rows = $GLOBALS['db']->fetch_array ( $res ) ) {
		$reply = array ();
		$sql = 'SELECT user_name, user_email, msg_time, msg_content' . ' FROM ' . TABLE_PREFIX . 'feedback' . ' WHERE parent_id = ' . $rows ['msg_id'];
		$reply = $GLOBALS['db']->query_first_slave ( $sql );

		if ($reply) {
			$msg [$rows ['msg_id']] ['re_user_name'] = $reply ['user_name'];
			$msg [$rows ['msg_id']] ['re_user_email'] = $reply ['user_email'];
			$msg [$rows ['msg_id']] ['re_msg_time'] = skyuc_date ( $GLOBALS['skyuc']->options ['date_format'] . ' ' . $GLOBALS['skyuc']->options ['time_format'], $reply ['msg_time'] );
			$msg [$rows ['msg_id']] ['re_msg_content'] = nl2br ( htmlspecialchars ( fetch_censored_text ( $reply ['msg_content'] ) ) );
		}

		$msg [$rows ['msg_id']] ['user_name'] = htmlspecialchars ( $rows ['user_name'] );
		$msg [$rows ['msg_id']] ['msg_content'] = nl2br ( htmlspecialchars ( fetch_censored_text ( $rows ['msg_content'] ) ) );
		$msg [$rows ['msg_id']] ['msg_time'] = skyuc_date ( $GLOBALS['skyuc']->options ['date_format'] . ' ' . $GLOBALS['skyuc']->options ['time_format'], $rows ['msg_time'] );
		$msg [$rows ['msg_id']] ['msg_type'] = $GLOBALS['_LANG'] ['message_type'] [$rows ['msg_type']];
		$msg [$rows ['msg_id']] ['msg_title'] = nl2br ( htmlspecialchars ( $rows ['msg_title'] ) );
		$msg [$rows ['msg_id']] ['message_img'] = $rows ['message_img'];
	}

	return $msg;
}

/**
 * 添加留言函数
 *
 * @access  public
 * @param   array       $message
 *
 * @return  boolen      $bool
 */
function add_message($message) {
	// 最大上传文件大小,单位字节
	$upload_size_limit = fetch_max_upload_size ();

	if ($_FILES ['message_img'] ['size'] > $upload_size_limit) {
		$GLOBALS['err']->add ( sprintf ( $GLOBALS['_LANG'] ['upload_file_limit'], $upload_size_limit / 1048576 ) );
		return false;
	}

	if ($message ['upload']) {
		$img_name = upload_file ( $_FILES ['message_img'], 'feedbackimg' );

		if ($img_name === false) {
			return false;
		}
	} else {
		$img_name = '';
	}

	if (empty ( $message ['msg_title'] )) {
		$GLOBALS['err']->add ( $GLOBALS['_LANG'] ['msg_title_empty'] );

		return false;
	}

	$message ['msg_area'] = isset ( $message ['msg_area'] ) ? intval ( $message ['msg_area'] ) : 0;
	$sql = 'INSERT INTO ' . TABLE_PREFIX . 'feedback' . ' (msg_id, parent_id, user_id, user_name, user_email, msg_title, msg_type, msg_content, msg_time, message_img,  msg_area)' . " VALUES (NULL, 0, '" . $message ['user_id'] . "', '" . $GLOBALS['db']->escape_string ( $message ['user_name'] ) . "', '" . $GLOBALS['db']->escape_string ( $message ['user_email'] ) . "', " . " '" . $GLOBALS['db']->escape_string ( $message ['msg_title'] ) . "', '" . $message ['msg_type'] . "', '" . $GLOBALS['db']->escape_string ( $message ['msg_content'] ) . "', '" . TIMENOW . "', '" . $GLOBALS['db']->escape_string ( $img_name ) . "',  '" . $message ['msg_area'] . "')";
	$GLOBALS['db']->query_write ( $sql );

	return true;
}

/**
 * 处理上传文件，并返回上传图片名(上传失败时返回图片名为空）
 *
 * @access  public
 * @param array     $upload     $_FILES 数组
 * @param array     $type       图片所属类别，即upload目录下的文件夹名
 *
 * @return string               上传图片名
 */
function upload_file($upload, $type) {

	if (! empty ( $upload ['tmp_name'] )) {
		$ftype = check_file_type ( $upload ['tmp_name'], $upload ['name'], '|jpg|jpeg|gif|doc|xls|txt|zip|ppt|pdf|rar|' );
		if (! empty ( $ftype )) {
			$name = skyuc_date ( 'Ymd', TIMENOW, TRUE, FALSE );
			for($i = 0; $i < 6; $i ++) {
				$name .= chr ( rand ( 97, 122 ) );
			}

			$name = $GLOBALS['skyuc']->userinfo ['userid'] . '_' . $name . '.' . $ftype;

			$target = DIR . '/' . $GLOBALS['skyuc']->config ['Misc'] ['imagedir'] . '/' . $type . '/' . $name;
			if (! move_upload_file ( $upload ['tmp_name'], $target )) {
				$GLOBALS['err']->add ( $GLOBALS['_LANG'] ['upload_file_error'], 1 );

				return false;
			} else {
				return $name;
			}
		} else {
			$GLOBALS['err']->add ( $GLOBALS['_LANG'] ['upload_file_type'], 1 );

			return false;
		}
	} else {
		$GLOBALS['err']->add ( $GLOBALS['_LANG'] ['upload_file_error'] );
		return false;
	}
}

?>