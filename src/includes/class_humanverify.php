<?php
/**
 * SKYUC! 抽象真人验证类
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
 * 抽象真人验证类
 *
 */
class HumanVerify {
	/**
	 * 构造函数
	 * 不做任何事 :p
	 *
	 * @return	void
	 */
	function __construct() {
	}

	/**
	 * 一个仿真: 选择程序库
	 *
	 * @return	object
	 */
	function &fetch_library(&$registry, $library = '') {
		global $show;
		static $instance;

		if (! $instance) {
			if ($library) { //覆盖定义的系统选项
				$chosenlib = $library;
			} else {
				//$chosenlib = ($registry->options['hv_type'] ? $registry->options['hv_type'] : 'Disabled');
				$chosenlib = 'Image';
			}

			$selectclass = 'HumanVerify_' . $chosenlib;
			$chosenlib = strtolower ( $chosenlib );
			require_once (DIR . '/includes/class_humanverify_' . $chosenlib . '.php');
			$instance = new $selectclass ( $registry );
		}

		return $instance;
	}
}

/**
 * 抽象的真人验证类
 *
 *
 * @abstract
 */
class HumanVerify_Abstract {
	/**
	 * 主要数据注册表
	 *
	 * @public	Registry
	 */
	public $registry = null;

	/**
	 * 错误字符
	 *
	 * @public	string
	 */
	public $error = '';

	/**
	 * 最后产生的 hash
	 *
	 * @public	string
	 */
	public $hash = '';

	/**
	 * 构造函数
	 * Don't allow direct construction of this abstract class
	 * Sets registry
	 *
	 * @return	void
	 */
	function __construct(&$registry) {
		if (! is_subclass_of ( $this, 'HumanVerify_Abstract' )) {
			trigger_error ( 'Direct Instantiation of HumanVerify_Abstract prohibited.', E_USER_ERROR );
			return NULL;
		}

		$this->registry = & $registry;
	}

	/**
	 * 删除一个真人验证标记
	 *
	 * @param	string	要删除的哈希
	 * @param	string	相应的选项
	 * @param	integer	该标记是否已显示过
	 *
	 * @return	boolean	是否删除成功
	 *
	 */
	function delete_token($hash, $answer = NULL, $viewed = NULL) {
		$options = array ("hash = '" . $this->registry->db->escape_string ( $hash ) . "'" );

		if ($answer !== NULL) {
			$options [] = "answer = '" . $this->registry->db->escape_string ( $answer ) . "'";
		}
		if ($viewed !== NULL) {
			$options [] = "viewed = " . intval ( $viewed );
		}

		if ($this->hash == $hash) {
			$this->hash = '';
		}

		$this->registry->db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . 'humanverify WHERE ' . implode ( ' AND ', $options ) . '' );

		return $this->registry->db->affected_rows () ? true : false;
	}

	/**
	 * 生成一个随机的标记并将它存储在该数据库中
	 *
	 * @param	boolean	删除生成的上一个哈希
	 *
	 * @return	array	一个哈希与答案的数组
	 *
	 */
	function generate_token($deletehash = true) {
		$verify = array ('hash' => md5 ( uniqid ( skyuc_rand (), true ) ), 'answer' => $this->fetch_answer () );

		if ($deletehash and $this->hash) {
			$this->delete_token ( $this->hash );
		}
		$this->hash = $verify ['hash'];

		$this->registry->db->query_write ( '
			INSERT INTO ' . TABLE_PREFIX . "humanverify
				(hash, answer, dateline)
			VALUES
				('" . $this->registry->db->escape_string ( $verify ['hash'] ) . "', '" . $this->registry->db->escape_string ( $verify ['answer'] ) . "', " . TIMENOW . ")" );

		return $verify;
	}

	/**
	 * 验证真人验证是否是正确的条目
	 *
	 * @param	array	一个哈希与输入的答案的数组
	 *
	 * @return	boolean
	 *
	 */
	function verify_token($input) {
		return true;
	}

	/**
	 * 返回任何类中出现的错误
	 *
	 * @return	mixed
	 *
	 */
	function fetch_error() {
		return $this->error;
	}

	/**
	 * 产生预期的答案
	 *
	 * @return	mixed
	 *
	 */
	function fetch_answer() {
	}
}

?>
