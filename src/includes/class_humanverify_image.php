<?php
/**
 * SKYUC! 图像真人验证类
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

if (! isset ( $GLOBALS ['skyuc']->db )) {
	exit ();
}

/**
 * 图像验证真人验证类
 *
 *
 */
class HumanVerify_Image extends HumanVerify_Abstract {
	/**
	 * 构造函数
	 *
	 * @return	void
	 */
	function __construct(&$registry) {
		parent::__construct ( $registry );
	}

	/**
	 * 验证真人验证是否是正确的条目
	 *
	 * @param	array	Values given by user 'input' and 'hash'
	 *
	 * @return	bool
	 */
	function verify_token($input) {
		$input ['input'] = trim ( str_replace ( ' ', '', $input ['input'] ) );

		if ($this->delete_token ( $input ['hash'], $input ['input'] )) {
			return true;
		} else {
			$this->error = 'humanverify_image_wronganswer';
			return false;
		}
	}

	/**
	 * 产生预期的答案
	 *
	 * @return	string
	 */
	function fetch_answer() {
		return $this->fetch_answer_string ();
	}

	/**
	 * 生成一个图像验证随机字符串
	 *
	 * @param	int		结果长度
	 *
	 * @return	string
	 */
	function fetch_answer_string($length = 6) {
		$somechars = '234689ABCEFGHJMNPQRSTWY';
		$morechars = '234689ABCEFGHJKMNPQRSTWXYZabcdefghjkmnpstwxyz';

		for($x = 1; $x <= $length; $x ++) {
			$chars = ($x <= 2 or $x == $length) ? $morechars : $somechars;
			$number = skyuc_rand ( 1, strlen ( $chars ) );
			$word .= substr ( $chars, $number - 1, 1 );
		}

		return $word;
	}
}

?>
