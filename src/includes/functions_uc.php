<?php
/**
 * SKYUC! UCenter 函数库
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

/**
 * 通过判断is_feed 向UCenter提交Feed
 *
 * @access public
 * @param  integer $value_id  $show_id
 * @param  interger $feed_type PLAY_SHOW or COMMENT_SHOW
 *
 * @return void
 */
function add_feed($id, $feed_type) {
	global $skyuc;
	$feed = array ();
	if ($feed_type == PLAY_SHOW) {
		if (empty ( $id )) {
			return;
		}
		$id = intval ( $id );
		$feed_res = $skyuc->db->query_all_slave ( 'SELECT show_id, title, actor, description, thumb FROM ' . TABLE_PREFIX . 'show' . ' WHERE show_id=' . $id );
		foreach ( $feed_res as $show_data ) {
			if (strpos ( $show_data ['thumb'], 'http' ) !== false) {
				$url = $show_data ['thumb'];
			} else {
				$url = get_url () . $show_data ['thumb'];
			}

			$link = get_url () . 'show.php?id=' . $show_data ['show_id'];

			$feed ['icon'] = 'show';
			$feed ['title_template'] = '<b>{username} ' . $skyuc->lang ['feed_user_play'] . ' {title}</b>';
			$feed ['title_data'] = array ('username' => $skyuc->userinfo ['user_name'], 'title' => $show_data ['title'] );
			$feed ['body_template'] = '{title} <br /> ' . $skyuc->lang ['feed_actor'] . '{actor}  <br />' . $skyuc->lang ['feed_detail'] . '{detail}';
			$feed ['body_data'] = array ('title' => $show_data ['title'], 'actor' => $show_data ['actor'], 'detail' => $show_data ['description'] );
			$feed ['images'] [] = array ('url' => $url, 'link' => $link );
			$feed ['user_name'] = $skyuc->userinfo ['user_name'];

			if (UC_CHARSET != 'UTF8') {
				$feed ['user_name'] = skyuc_iconv ( 'UTF8', UC_CHARSET, $feed ['user_name'] );
				$feed ['title_template'] = skyuc_iconv ( 'UTF8', UC_CHARSET, $feed ['title_template'] );

				skyuc_iconv_array ( 'UTF8', UC_CHARSET, $feed ['title_data'] );

				$feed ['body_template'] = skyuc_iconv ( 'UTF8', UC_CHARSET, $feed ['body_template'] );
				skyuc_iconv_array ( 'UTF8', UC_CHARSET, $feed ['body_data'] );
			}

			uc_call ( 'uc_feed_add', array ($feed ['icon'], $skyuc->userinfo ['user_id'], $feed ['user_name'], $feed ['title_template'], $feed ['title_data'], $feed ['body_template'], $feed ['body_data'], '', '', $feed ['images'] ) );
		}
	}

	return;
}

/**
 * 获得影片tag所关联的其他应用的列表
 *
 * @param   array       $attr
 *
 * @return  void
 */
function get_linked_tags($tag_data) {
	//取所有应用列表
	$app_list = uc_call ( 'uc_app_ls' );
	if ($app_list == '') {
		return '';
	}
	foreach ( $app_list as $app_key => $app_data ) {
		if ($app_data ['appid'] == UC_APPID) {
			unset ( $app_list [$app_key] );
			continue;
		}
		$get_tag_array [$app_data ['appid']] = '5';
		$app_array [$app_data ['appid']] ['name'] = $app_data ['name'];
		$app_array [$app_data ['appid']] ['type'] = $app_data ['type'];
		$app_array [$app_data ['appid']] ['url'] = $app_data ['url'];
		$app_array [$app_data ['appid']] ['tagtemplates'] = $app_data ['tagtemplates'];
	}

	$tag_rand_key = array_rand ( $tag_data );
	$get_tag_data = uc_call ( 'uc_tag_get', array ($tag_data [$tag_rand_key], $get_tag_array ) );
	foreach ( $get_tag_data as $appid => $tag_data_array ) {
		$templates = $app_array [$appid] ['tagtemplates'] ['template'];
		if (! empty ( $templates ) && ! empty ( $tag_data_array ['data'] )) {
			foreach ( $tag_data_array ['data'] as $tag_data ) {
				$show_data = $templates;
				foreach ( $tag_data as $tag_key => $data ) {
					$show_data = str_replace ( '{' . $tag_key . '}', $data, $show_data );
				}
				$app_array [$appid] ['data'] [] = $show_data;
			}
		}
	}

	return $app_array;
}

/**
 * 兑换积分
 *
 * @param  integer $uid 用户ID
 * @param  integer $fromcredits 原积分类型
 * @param  integer $tocredits 目标积分类型
 * @param  integer $toappid 目标在ucenter中应用程序ID
 * @param  integer $netamount 积分数额
 *
 * @return boolean
 */
function exchange_points($uid, $fromcredits, $tocredits, $toappid, $netamount) {
	$ucresult = uc_call ( 'uc_credit_exchange_request', array ($uid, $fromcredits, $tocredits, $toappid, $netamount ) );
	if (! $ucresult) {
		return false;
	} else {
		return true;
	}
}
?>