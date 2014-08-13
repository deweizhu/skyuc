<?php

/**
 * SKYUC! 用户级错误处理类
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

if (! defined ( 'SKYUC_AREA' )) {
	echo 'SKYUC_AREA must be defined to continue';
	exit ();
}

class skyuc_error {
	public $_message = array ();
	public $_template = '';
	public $error_no = 0;

	/**
	 * 构造函数
	 *
	 * @access  public
	 * @param   string  $tpl
	 * @return  void
	 */
	public function __construct($tpl) {
		$this->_template = $tpl;
	}

	/**
	 * 添加一条错误信息
	 *
	 * @access  public
	 * @param   string  $msg
	 * @param   integer $errno
	 * @return  void
	 */
	public function add($msg, $errno = 1) {
		if (is_array ( $msg )) {
			$this->_message = array_merge ( $this->_message, $msg );
		} else {
			$this->_message [] = $msg;
		}

		$this->error_no = $errno;
	}

	/**
	 * 清空错误信息
	 *
	 * @access  public
	 * @return  void
	 */
	public function clean() {
		$this->_message = array ();
		$this->error_no = 0;
	}

	/**
	 * 返回所有的错误信息的数组
	 *
	 * @access  public
	 * @return  array
	 */
	public function get_all() {
		return $this->_message;
	}

	/**
	 * 返回最后一条错误信息
	 *
	 * @access  public
	 * @return  void
	 */
	public function last_message() {
		return array_slice ( $this->_message, - 1 );
	}

	/**
	 * 显示错误信息
	 *
	 * @access  public
	 * @param   string  $link
	 * @param   string  $href
	 * @return  void
	 */
	public function show($link = '', $href = '') {
		if ($this->error_no > 0) {
			$message = array ();

			$link = (empty ( $link )) ? $GLOBALS ['_LANG'] ['back_up_page'] : $link;
			$href = (empty ( $href )) ? 'javascript:history.back();' : $href;
			$message ['url_info'] [$link] = $href;
			$message ['back_url'] = $href;

			foreach ( $this->_message as $msg ) {
				$message ['content'] = '<div>' . htmlspecialchars ( $msg ) . '</div>';
			}

			if (isset ( $GLOBALS ['smarty'] )) {
				assign_template ();
				$GLOBALS ['smarty']->assign ( 'auto_redirect', true );
				$GLOBALS ['smarty']->assign ( 'message', $message );
				$GLOBALS ['smarty']->display ( $this->_template );
			} else {
				die ( $message ['content'] );
			}

			exit ();
		}
	}
}

?>