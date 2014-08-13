<?php

/**
 * SKYUC! 会员整合数据处理类
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * 这不是一个免费的软件,使用它必须向开发团队支付费用。
 * ============================================================================
 */

if (! defined ( 'SKYUC_AREA' )) {
	echo 'SKYUC_AREA must be defined to continue';
	exit ();
}

/* 模块的基本信息 */
if (isset ( $set_modules ) && $set_modules == TRUE) {
	$i = iif ( isset ( $modules ), count ( $modules ), 0 );

	//会员数据整合插件的代码必须和文件名保持一致
	$modules [$i] ['code'] = 'ucenter';

	// 被整合的第三方程序的名称
	$modules [$i] ['name'] = 'UCenter 1.6';

	// 被整合的第三方程序的版本
	$modules [$i] ['version'] = '1.6.0';

	// 插件的作者
	$modules [$i] ['author'] = 'SKYUC! R&D TEAM';

	// 插件作者的官方网站
	$modules [$i] ['website'] = 'http://www.skyuc.com';

	// 插件的初始的默认值
	$modules [$i] ['default'] ['db_host'] = 'localhost';
	$modules [$i] ['default'] ['db_user'] = 'root';
	$modules [$i] ['default'] ['prefix'] = 'uc_';
	$modules [$i] ['default'] ['cookie_prefix'] = 'xnW_';

	return;
}

require_once (DIR . '/includes/modules/integrates/integrate.php');
class ucenter extends integrate {

	/**
	 * 构造函数
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function __construct($cfg) {
		parent::__construct ( array () );

		$this->user_table = 'users';
		$this->field_id = 'user_id';
		$this->field_name = 'user_name';
		$this->field_pass = 'password';
		$this->field_email = 'email';
		$this->field_gender = 'gender';
		$this->field_bday = 'birthday';
		$this->field_reg_date = 'reg_time';
		$this->need_sync = false;
		$this->is_skyuc = 1;

		/* 初始化UC需要常量 */
		if (! defined ( 'UC_CONNECT' ) && isset ( $cfg ['uc_id'] ) && isset ( $cfg ['db_host'] ) && isset ( $cfg ['db_user'] ) && isset ( $cfg ['db_name'] )) {
			if (strpos ( $cfg ['db_pre'], '`' . $cfg ['db_name'] . '`' ) === 0) {
				$db_pre = $cfg ['db_pre'];
			} else {
				$db_pre = '`' . $cfg ['db_name'] . '`.' . $cfg ['db_pre'];
			}

			$cfg ['uc_charset'] = str_replace ( '-', '', strtoupper ( $cfg ['uc_charset'] ) ); //编码utf-8转换为UTF8


			$this->charset = $cfg ['uc_charset'];

			define ( 'UC_CONNECT', isset ( $cfg ['uc_connect'] ) ? $cfg ['uc_connect'] : '' );
			define ( 'UC_DBHOST', isset ( $cfg ['db_host'] ) ? $cfg ['db_host'] : '' );
			define ( 'UC_DBUSER', isset ( $cfg ['db_user'] ) ? $cfg ['db_user'] : '' );
			define ( 'UC_DBPW', isset ( $cfg ['db_pass'] ) ? $cfg ['db_pass'] : '' );
			define ( 'UC_DBNAME', isset ( $cfg ['db_name'] ) ? $cfg ['db_name'] : '' );
			define ( 'UC_DBCHARSET', isset ( $cfg ['db_charset'] ) ? $cfg ['db_charset'] : '' );
			define ( 'UC_DBTABLEPRE', $db_pre );
			define ( 'UC_DBCONNECT', '0' );
			define ( 'UC_KEY', isset ( $cfg ['uc_key'] ) ? $cfg ['uc_key'] : '' );
			define ( 'UC_API', isset ( $cfg ['uc_url'] ) ? $cfg ['uc_url'] : '' );
			define ( 'UC_CHARSET', isset ( $cfg ['uc_charset'] ) ? $cfg ['uc_charset'] : '' );
			define ( 'UC_IP', isset ( $cfg ['uc_ip'] ) ? $cfg ['uc_ip'] : '' );
			define ( 'UC_APPID', isset ( $cfg ['uc_id'] ) ? $cfg ['uc_id'] : '' );
			define ( 'UC_PPP', '20' );
		}
	}

	/**
	 * 用户登录函数
	 *
	 * @access  public
	 * @param   string  $username
	 * @param   string  $password
	 *
	 * @return void
	 */
	public function login($username, $password) {
		if (UC_CHARSET != 'UTF8') {
			$username = skyuc_iconv ( 'UTF8', UC_CHARSET, $username );
		}
		list ( $uid, $uname, $pwd, $email, $repeat ) = uc_call ( 'uc_user_login', array ($username, $password ) );
		if (UC_CHARSET != 'UTF8') {
			$username = skyuc_iconv ( UC_CHARSET, 'UTF8', $uname );
		}
		if ($uid > 0) {
			//检查用户是否存在,不存在直接放入用户表
			$user_exist = $this->db->query_first_slave ( 'SELECT user_id FROM ' . TABLE_PREFIX . 'users' . " WHERE user_name='" . $this->db->escape_string ( $username ) . "'" );

			if (empty ( $user_exist ['user_id'] )) {
				$password = $this->compile_password ( array ('password' => $password ) );
				$this->db->query_write ( 'INSERT INTO ' . TABLE_PREFIX . 'users' . "(`user_id`, `email`, `user_name`, `password`, `reg_time`, `lastvisit`, `last_ip`) VALUES ('" . $uid . "', '" . $this->db->escape_string ( $email ) . "', '" . $this->db->escape_string ( $username ) . "', '" . $password . "', '" . TIMENOW . "', '" . TIMENOW . "', '" . ALT_IP . "')" );
			}

			$this->set_session ( $username );
			$this->set_cookie ( $username );
			$this->ucdata = uc_call ( 'uc_user_synlogin', array ($uid ) );
			return true;
		} elseif ($uid == - 1) {
			$this->error = ERR_INVALID_USERNAME;
			return false;
		} elseif ($uid == - 2) {
			$this->error = ERR_INVALID_PASSWORD;
			return false;
		} else {
			return false;
		}
	}

	/**
	 * 用户退出
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function logout() {
		$this->set_cookie (); //清除cookie
		$this->set_session (); //清除session
		$this->ucdata = uc_call ( 'uc_user_synlogout' ); //同步退出
		return true;
	}

	/**
	 * 添加用户
	 *
	 */
	public function add_user($username, $password, $email) {
		//检测用户名
		if ($this->check_user ( $username )) {
			$this->error = ERR_USERNAME_EXISTS;
			return false;
		}
		if (UC_CHARSET != 'UTF8') {
			$username = skyuc_iconv ( 'UTF8', UC_CHARSET, $username );
		}

		$uid = uc_call ( 'uc_user_register', array ($username, $password, $email ) );
		if ($uid <= 0) {
			if ($uid == - 1) {
				$this->error = ERR_INVALID_USERNAME;
				return false;
			} elseif ($uid == - 2) {
				$this->error = ERR_USERNAME_NOT_ALLOW;
				return false;
			} elseif ($uid == - 3) {
				$this->error = ERR_USERNAME_EXISTS;
				return false;
			} elseif ($uid == - 4) {
				$this->error = ERR_INVALID_EMAIL;
				return false;
			} elseif ($uid == - 5) {
				$this->error = ERR_EMAIL_NOT_ALLOW;
				return false;
			} elseif ($uid == - 6) {
				$this->error = ERR_EMAIL_EXISTS;
				return false;
			} else {
				return false;
			}
		} else {
			if (UC_CHARSET != 'UTF8') {
				$username = skyuc_iconv ( UC_CHARSET, 'UTF8', $username );
			}
			//注册成功，插入用户表
			$password = $this->compile_password ( array ('password' => $password ) );
			$this->db->query_write ( 'INSERT INTO ' . TABLE_PREFIX . 'users' . "(`user_id`, `email`, `user_name`, `password`, `reg_time`, `lastvisit`, `last_ip`) VALUES ('" . $uid . "', '" . $this->db->escape_string ( $email ) . "', '" . $this->db->escape_string ( $username ) . "', '" . $password . "', '" . TIMENOW . "', '" . TIMENOW . "', '" . ALT_IP . "')" );
			return true;
		}
	}

	/**
	 * 检查指定用户是否存在及密码是否正确
	 *
	 * @access  public
	 * @param   string  $username   用户名
	 *
	 * @return  int
	 */
	public function check_user($username, $password = null) {
		if (UC_CHARSET != 'UTF8') {
			$username = skyuc_iconv ( 'UTF8', UC_CHARSET, $username );
		}
		$userdata = uc_call ( 'uc_user_checkname', array ($username ) );
		if ($userdata == 1) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * 检测Email是否合法
	 *
	 * @access  public
	 * @param   string  $email   邮箱
	 *
	 * @return  blob
	 */
	public function check_email($email) {
		if (! empty ( $email )) {
			$email_exist = uc_call ( 'uc_user_checkemail', array ($email ) );
			if ($email_exist == 1) {
				return false;
			} else {
				$this->error = ERR_EMAIL_EXISTS;
				return true;
			}
		}
		return true;
	}

	/* 编辑用户信息 */
	public function edit_user($cfg, $forget_pwd = '0') {
		$set_str = '';
		$valarr = array ('email' => 'email', 'gender' => 'gender', 'bday' => 'birthday' );
		foreach ( $cfg as $key => $val ) {
			if ($key == 'username' || $key == 'password' || $key == 'old_password') {
				continue;
			}
			$set_str .= $valarr ["$key"] . '=' . "'" . iif ( is_numeric ( $val ), $val, $this->db->escape_string ( $val ) ) . "',";
		}
		$set_str = substr ( $set_str, 0, - 1 );
		if (! empty ( $set_str )) {
			$sql = 'UPDATE ' . TABLE_PREFIX . 'users' . ' SET ' . $set_str . "  WHERE user_name = '" . $this->db->escape_string ( $cfg ['username'] ) . "'";
			$this->db->query_write ( $sql );
			$flag = true;
		}

		if (UC_CHARSET != 'UTF8') {
			$cfg ['username'] = skyuc_iconv ( 'UTF8', UC_CHARSET, $cfg ['username'] );
		}

		if (! empty ( $cfg ['email'] )) {
			$ucresult = uc_call ( 'uc_user_edit', array ($cfg ['username'], '', '', $cfg ['email'], 1 ) );
			if ($ucresult > 0) {
				$flag = true;
			} elseif ($ucresult == - 4) {
				//echo 'Email 格式有误';
				$this->error = ERR_INVALID_EMAIL;

				return false;
			} elseif ($ucresult == - 5) {
				//echo 'Email 不允许注册';
				$this->error = ERR_INVALID_EMAIL;

				return false;
			} elseif ($ucresult == - 6) {
				//echo '该 Email 已经被注册';
				$this->error = ERR_EMAIL_EXISTS;

				return false;
			} elseif ($ucresult < 0) {
				return false;
			}
		}
		if (! empty ( $cfg ['old_password'] ) && ! empty ( $cfg ['password'] ) && $forget_pwd == 0) {
			$ucresult = uc_call ( 'uc_user_edit', array ($cfg ['username'], $cfg ['old_password'], $cfg ['password'], '' ) );
			if ($ucresult > 0) {
				return true;
			} else {
				$this->error = ERR_INVALID_PASSWORD;
				return false;
			}
		} elseif (! empty ( $cfg ['password'] ) && $forget_pwd == 1) {
			$ucresult = uc_call ( 'uc_user_edit', array ($cfg ['username'], '', $cfg ['password'], '', '1' ) );
			if ($ucresult > 0) {
				$flag = true;
			}
		}

		return true;
	}

	/**
	 * 获取指定用户的信息
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function get_profile_by_name($username) {
		if (UC_CHARSET != 'UTF8') {
			$username = skyuc_iconv ( UC_CHARSET, 'UTF8', $username );
		}
		$sql = 'SELECT user_id, user_name, email, gender, reg_time FROM ' . TABLE_PREFIX . 'users' . " WHERE user_name='" . $this->db->escape_string ( $username ) . "'";
		$row = $this->db->query_first_slave ( $sql );
		return $row;
	}

	/**
	 * 检查cookie是正确，返回用户名
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function check_cookie() {
		if (! empty ( $_COOKIE [COOKIE_PREFIX . 'username'] )) {
			return urldecode ( $_COOKIE [COOKIE_PREFIX . 'username'] );
		}
		return '';
	}

	/**
	 * 根据登录状态设置cookie
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function get_cookie() {
		$id = $this->check_cookie ();
		if ($id) {
			if ($this->need_sync) {
				$this->sync ( $id );
			}
			$this->set_session ( $id );

			return true;
		} else {
			return false;
		}
	}

	/**
	 * 设置cookie
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function set_cookie($username = '') {
		if (empty ( $username )) {
			// 摧毁cookie
			$time = TIMENOW - 86400;
			skyuc_setcookie ( 'username', '', $time );
			skyuc_setcookie ( 'userid', '', $time );
			skyuc_setcookie ( 'password', '', $time );
		} else {
			// 设置cookie
			$time = TIMENOW + 86400 * 7;
			skyuc_setcookie ( 'username', urlencode ( $username ), $time );

			$sql = 'SELECT user_id, password FROM ' . TABLE_PREFIX . 'users' . " WHERE user_name='" . $this->db->escape_string ( $username ) . "' ";
			$row = $this->db->query_first ( $sql );
			if (! empty ( $row )) {
				skyuc_setcookie ( 'userid', $row ['user_id'], $time );
				skyuc_setcookie ( 'password', $row ['password'], $time );
			}
		}
	}

	/**
	 * 设置指定用户SESSION
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function set_session($username = '') {
		global $skyuc;

		if (empty ( $username )) {
			$skyuc->session->destroy_session ();
		} else {
			$sql = 'SELECT user_id FROM ' . TABLE_PREFIX . 'users' . " WHERE user_name='" . $this->db->escape_string ( $username ) . "' ";
			$row = $skyuc->db->query_first ( $sql );

			if ($row) {
				$skyuc->session->set ( 'userid', $row ['user_id'] );
			}
		}
	}

	/**
	 * 获取指定用户的信息
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function get_profile_by_id($id) {
		$sql = 'SELECT user_id, user_name, email, gender, birthday, reg_time FROM ' . TABLE_PREFIX . 'users' . " WHERE user_id='" . $id . "'";
		$row = $this->db->query_first_slave ( $sql );

		return $row;
	}

	public function get_user_info($username) {
		return $this->get_profile_by_name ( $username );
	}

	/**
	 * 删除用户
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function remove_user($id) {

		if (is_array ( $id )) {
			$post_id = array ();
			foreach ( $id as $val ) {
				$post_id [] = $val;
			}
		} else {

			$post_id = $id;
		}

		// 如果需要同步或是SKYUC插件执行这部分代码


		$sql = 'SELECT user_id FROM ' . TABLE_PREFIX . 'users' . ' WHERE ';
		$sql .= iif ( is_array ( $post_id ), db_create_in ( $post_id, 'user_name' ), "user_name='" . $post_id . "' " );
		$col = array ();
		$res = $this->db->query_read_slave ( $sql );
		while ( $row = $this->db->fetch_row ( $res ) ) {
			$col [] = $row [0];
		}

		if ($col) {
			//删除用户
			$sql = 'DELETE FROM ' . TABLE_PREFIX . 'users' . ' WHERE ' . db_create_in ( $col, 'user_id' );
			$this->db->query_write ( $sql );
			// 删除用户订单
			$sql = 'SELECT order_id FROM ' . TABLE_PREFIX . 'order_info' . ' WHERE ' . db_create_in ( $col, 'user_id' );
			$col_order_id = array ();
			$res = $this->db->query_read ( $sql );
			while ( $row = $this->db->fetch_row ( $res ) ) {
				$col_order_id [] = $row [0];
			}
			if (! empty ( $col_order_id )) {
				$sql = 'DELETE FROM ' . TABLE_PREFIX . 'order_info' . ' WHERE ' . db_create_in ( $col_order_id, 'order_id' );
				$this->db->query_write ( $sql );
			}
			//删除用户留言
			$sql = 'DELETE FROM ' . TABLE_PREFIX . 'feedback' . ' WHERE ' . db_create_in ( $col, 'user_id' );
			$this->db->query_write ( $sql );

			//删除用户帐号金额
			$sql = 'DELETE FROM ' . TABLE_PREFIX . 'user_account' . ' WHERE ' . db_create_in ( $col, 'user_id' );
			$this->db->query_write ( $sql );

			//删除用户标记
			$sql = 'DELETE FROM ' . TABLE_PREFIX . 'tag' . ' WHERE ' . db_create_in ( $col, 'user_id' );
			$this->db->query_write ( $sql );
		}

		if (isset ( $this->skyuc ) && $this->skyuc) {
			// 如果是skyuc插件直接退出
			return;
		}

		$sql = 'DELETE FROM ' . TABLE_PREFIX . 'users' . ' WHERE ';
		if (is_array ( $post_id )) {
			$sql .= db_create_in ( $post_id, 'user_name' );
		} else {
			$sql .= "user_name='" . $post_id . "' ";
		}

		$this->db->query_write ( $sql );
	}

	/**
	 * 获取论坛有效积分及单位
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function get_points_name() {
		return 'ucenter';
	}
}

?>