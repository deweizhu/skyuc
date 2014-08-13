<?php

/**
 * SKYUC! 会员整合插件的基类
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * 这不是一个免费的软件,使用它必须向开发团队支付费用。
 * ============================================================================
 */

class integrate {

	/*------------------------------------------------------ */
	//-- PUBLIC ATTRIBUTEs
	/*------------------------------------------------------ */

	// 整合对象使用的数据库主机
	public $db_host = '';

	// 整合对象使用的数据库名
	public $db_name = '';

	// 整合对象使用的数据库用户名
	public $db_user = '';

	// 整合对象使用的数据库密码
	public $db_pass = '';

	// 整合对象数据表前缀
	public $prefix = '';

	// 数据库所使用编码
	public $charset = '';

	// 整合对象使用的cookie的domain
	public $cookie_domain = '';

	// 整合对象使用的cookie的path
	public $cookie_path = '/';

	// 整合对象会员表名
	public $user_table = '';

	// 会员ID的字段名
	public $field_id = '';

	// 会员名称的字段名
	public $field_name = '';

	// 会员密码的字段名
	public $field_pass = '';

	// 会员密码的字段名
	public $field_email = '';

	// 会员性别
	public $field_gender = '';

	// 会员生日
	public $field_bday = '';

	// 注册日期的字段名
	public $field_reg_date = '';

	// 是否需要同步数据到网站
	public $need_sync = true;

	public $error = 0;

	public $db;

	/*------------------------------------------------------ */
	//-- PUBLIC METHODs
	/*------------------------------------------------------ */

	/**
	 * 会员数据整合插件类的构造函数
	 *
	 * @access      public
	 * @param       string  $db_host    数据库主机
	 * @param       string  $db_name    数据库名
	 * @param       string  $db_user    数据库用户名
	 * @param       string  $db_pass    数据库密码
	 * @return      void
	 */
	public function __construct($cfg) {
		global $skyuc;

		$this->charset = isset ( $cfg ['db_charset'] ) ? $cfg ['db_charset'] : 'UTF8';
		$this->prefix = isset ( $cfg ['prefix'] ) ? $cfg ['prefix'] : '';
		$this->db_name = isset ( $cfg ['db_name'] ) ? $cfg ['db_name'] : '';
		$this->cookie_domain = isset ( $cfg ['cookie_domain'] ) ? $cfg ['cookie_domain'] : '';
		$this->cookie_path = isset ( $cfg ['cookie_path'] ) ? $cfg ['cookie_path'] : '/';
		$this->need_sync = true;

		$quiet = empty ( $cfg ['quiet'] ) ? 0 : 1;

		// 初始化数据库
		if (empty ( $cfg ['db_host'] )) {
			$this->db_name = $skyuc->config ['Database'] ['dbname'];
			$this->prefix = $skyuc->config ['Database'] ['tableprefix'];
			$this->db = & $skyuc->db;
		} else {
			if (! empty ( $cfg ['is_latin1'] )) {
				$this->charset = 'latin1';
			}
			$this->db = new Database ( $skyuc );
			// 建立数据库连接
			$this->db->connect ( $cfg ['db_name'], $cfg ['db_host'], '', $cfg ['db_user'], $cfg ['db_pass'], $skyuc->config ['MasterServer'] ['usepconnect'], '', '', '', '', '', '', $this->charset );
			if (! empty ( $skyuc->config ['Database'] ['force_sql_mode'] )) {
				$this->db->force_sql_mode ( '' );
			}

		}

		if (! $this->db->connection_recent) {
			$this->error = 1;
		} else {
			$this->error = $this->db->errno ();
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
		if ($this->check_user ( $username, $password ) > 0) {
			if ($this->need_sync) {
				$this->sync ( $username, $password );
			}
			$this->set_session ( $username );
			$this->set_cookie ( $username );

			return 1;
		} elseif ($this->check_user ( $username, $password ) == 0) {
			return 0;
		} else {
			return - 1;
		}
	}

	/**
	 *
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function logout() {
		$this->set_cookie (); //清除cookie
		$this->set_session (); //清除session
	}

	/**
	 * 添加一个新用户
	 *
	 * @access  public
	 * @param
	 *
	 * @return int
	 */
	public function add_user($username, $password, $email, $gender = -1, $bday = 0, $reg_date = 0, $md5password = '') {
		// 将用户添加到整合方
		if ($this->check_user ( $username ) > 0) {
			$this->error = ERR_USERNAME_EXISTS;

			return false;
		}
		// 检查email是否重复
		$sql = 'SELECT ' . $this->field_id . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_email . " = '" . $this->db->escape_string ( $email ) . "'";
		$row = $this->db->query_first ( $sql );
		if ($row ["$this->field_id"] > 0) {
			$this->error = ERR_EMAIL_EXISTS;

			return false;
		}

		if ($this->charset != 'UTF8') {
			$post_username = skyuc_iconv ( 'UTF8', $this->charset, $username );
		} else {
			$post_username = $username;
		}

		if ($md5password) {
			$post_password = $this->compile_password ( array ('md5password' => $md5password ) );
		} else {
			$post_password = $this->compile_password ( array ('password' => $password ) );
		}

		$fields = array ($this->field_name, $this->field_email, $this->field_pass );
		$values = array ($this->db->escape_string ( $post_username ), $this->db->escape_string ( $email ), $this->db->escape_string ( $post_password ) );

		if (( int ) $gender > - 1) {
			$fields [] = $this->field_gender;
			$values [] = $gender;
		}
		if (! empty ( $bday )) {
			$fields [] = $this->field_bday;
			$values [] = $bday;
		}
		if ($reg_date) {
			$fields [] = $this->field_reg_date;
			$values [] = $reg_date;
		}

		$sql = 'INSERT INTO ' . $this->table ( $this->user_table ) . " (" . implode ( ',', $fields ) . ")" . " VALUES ('" . implode ( "', '", $values ) . "')";

		$this->db->query_write ( $sql );

		if ($this->need_sync) {
			$this->sync ( $username, $password );
		}

		return true;
	}

	/**
	 * 编辑用户信息($password, $email, $gender, $bday)
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function edit_user($cfg) {
		if (empty ( $cfg ['username'] )) {
			return false;
		} else {
			if ($this->charset != 'UTF8') {
				$cfg ['post_username'] = skyuc_iconv ( 'UTF8', $this->charset, $cfg ['username'] );
			} else {
				$cfg ['post_username'] = $cfg ['username'];
			}
		}

		$values = array ();
		if (! empty ( $cfg ['password'] ) && empty ( $cfg ['md5password'] )) {
			$cfg ['md5password'] = md5 ( $cfg ['password'] );
		}
		if ((! empty ( $cfg ['md5password'] )) && $this->field_pass != 'NULL') {
			$values [] = $this->field_pass . "='" . $this->compile_password ( array ('md5password' => $cfg ['md5password'] ) ) . "'";
		}

		if ((! empty ( $cfg ['email'] )) && $this->field_email != 'NULL') {
			//检查email是否重复
			$sql = 'SELECT ' . $this->field_id . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_email . " = '" . $this->db->escape_string ( $cfg ['email'] ) . "' " . ' AND ' . $this->field_name . " <> '" . $this->db->escape_string ( $cfg ['post_username'] ) . "'";
			$row = $this->db->query_first ( $sql );
			if ($row ["$this->field_id"] > 0) {
				$this->error = ERR_EMAIL_EXISTS;

				return false;
			}
			// 检查是否为新E-mail
			$sql = 'SELECT count(*) AS total' . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_email . " = '" . $this->db->escape_string ( $cfg ['email'] ) . "' ";
			$row = $this->db->query_first ( $sql );
			if ($row ['total'] == 0) {
				// 新的E-mail
				$sql = 'UPDATE ' . TABLE_PREFIX . 'users' . " SET is_validated = 0 WHERE user_name = '" . $this->db->escape_string ( $cfg ['post_username'] ) . "'";
				$this->db->query_write ( $sql );
			}
			$values [] = $this->field_email . "='" . $this->db->escape_string ( $cfg ['email'] ) . "'";
		}

		if (isset ( $cfg ['gender'] ) && $this->field_gender != 'NULL') {
			$values [] = $this->field_gender . "='" . $cfg ['gender'] . "'";
		}

		if ((! empty ( $cfg ['bday'] )) && $this->field_bday != 'NULL') {
			$values [] = $this->field_bday . "='" . $cfg ['bday'] . "'";
		}

		if ($values) {
			$sql = 'UPDATE ' . $this->table ( $this->user_table ) . ' SET ' . implode ( ', ', $values ) . ' WHERE ' . $this->field_name . "='" . $this->db->escape_string ( $cfg ['post_username'] ) . "'";

			$this->db->query_write ( $sql );

			if ($this->need_sync) {
				if (empty ( $cfg ['md5password'] )) {
					$this->sync ( $cfg ['username'] );
				} else {
					$this->sync ( $cfg ['username'], '', $cfg ['md5password'] );
				}
			}
		}

		return true;
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
		global $skyuc;
		if ($this->charset != 'UTF8') {
			if (is_array ( $id )) {
				$post_id = array ();
				foreach ( $id as $val ) {
					$post_id [] = skyuc_iconv ( 'UTF8', $this->charset, $val );
				}
			} else {
				$post_id = skyuc_iconv ( 'UTF8', $this->charset, $id );
			}
		} else {
			$post_id = $id;
		}

		if ($this->need_sync || (isset ( $this->is_skyuc ) && $this->is_skyuc)) {
			// 如果需要同步或是skyuc插件执行这部分代码
			$sql = 'SELECT user_id FROM ' . TABLE_PREFIX . 'users' . ' WHERE ';
			$sql .= (is_array ( $post_id )) ? db_create_in ( $post_id, 'user_name' ) : "user_name='" . $this->db->escape_string ( $post_id ) . "' ";
			$col = array ();
			$res = $skyuc->db->query_read ( $sql );
			while ( $row = $skyuc->db->fetch_row ( $res ) ) {
				$col [] = $row [0];
			}
			if (! empty ( $col )) {
				//删除用户
				$sql = 'DELETE FROM ' . TABLE_PREFIX . 'users' . ' WHERE ' . db_create_in ( $col, 'user_id' );
				$skyuc->db->query_write ( $sql );
				// 删除用户订单
				$sql = 'SELECT order_id FROM ' . TABLE_PREFIX . 'order_info' . ' WHERE ' . db_create_in ( $col, 'user_id' );
				$col_order_id = array ();
				$res = $skyuc->db->query_read ( $sql );
				while ( $row = $skyuc->db->fetch_row ( $res ) ) {
					$col_order_id [] = $row [0];
				}
				if (! empty ( $col_order_id )) {
					$sql = 'DELETE FROM ' . TABLE_PREFIX . 'order_info' . ' WHERE ' . db_create_in ( $col_order_id, 'order_id' );
					$skyuc->db->query_write ( $sql );
				}
				//删除用户留言
				$sql = 'DELETE FROM ' . TABLE_PREFIX . 'feedback' . ' WHERE ' . db_create_in ( $col, 'user_id' );
				$skyuc->db->query_write ( $sql );

				//删除用户帐号金额
				$sql = 'DELETE FROM ' . TABLE_PREFIX . 'user_account' . ' WHERE ' . db_create_in ( $col, 'user_id' );
				$skyuc->db->query_write ( $sql );

				//删除用户标记
				$sql = 'DELETE FROM ' . TABLE_PREFIX . 'tag' . ' WHERE ' . db_create_in ( $col, 'user_id' );
				$skyuc->db->query_write ( $sql );

			}
		}

		if (isset ( $this->skyuc ) && $this->skyuc) {
			//如果是skyuc插件直接退出
			return;
		}

		$sql = 'DELETE FROM ' . $this->table ( $this->user_table ) . ' WHERE ';
		if (is_array ( $post_id )) {
			$sql .= db_create_in ( $post_id, $this->field_name );
		} else {
			$sql .= $this->field_name . "='" . $post_id . "' ";
		}

		$this->db->query_write ( $sql );
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
		if ($this->charset != 'UTF8') {
			$post_username = skyuc_iconv ( 'UTF8', $this->charset, $username );
		} else {
			$post_username = $username;
		}

		$sql = 'SELECT ' . $this->field_id . ' AS user_id,' . $this->field_name . ' AS user_name,' . $this->field_email . ' AS email,' . $this->field_gender . ' AS gender,' . $this->field_bday . ' AS birthday,' . $this->field_reg_date . ' AS reg_time, ' . $this->field_pass . ' AS password ' . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_name . "='" . $this->db->escape_string ( $post_username ) . "'";
		;
		$row = $this->db->query_first ( $sql );
		if ($this->charset != 'UTF8') {
			$row ['user_name'] = $username;
		}

		return $row;
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
		$sql = 'SELECT ' . $this->field_id . ' AS user_id,' . $this->field_name . ' AS user_name,' . $this->field_email . ' AS email,' . $this->field_gender . ' AS gender,' . $this->field_bday . ' AS birthday,' . $this->field_reg_date . ' AS reg_time, ' . $this->field_pass . ' AS password ' . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_id . "='$id'";
		$row = $this->db->query_first ( $sql );

		if ($this->charset != 'UTF8') {
			$row ['user_name'] = skyuc_iconv ( $this->charset, 'UTF8', $row ['user_name'] );
		}

		return $row;
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
		$username = $this->check_cookie ();
		if ($username) {
			if ($this->need_sync) {
				$this->sync ( $username );
			}
			$this->set_session ( $username );

			return true;
		} else {
			return false;
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
		if ($this->charset != 'UTF8') {
			$post_username = skyuc_iconv ( 'UTF8', $this->charset, $username );
		} else {
			$post_username = $username;
		}

		// 如果没有定义密码则只检查用户名
		if ($password === null) {
			$sql = 'SELECT ' . $this->field_id . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_name . "='" . $post_username . "'";
			$row = $this->db->query_first ( $sql );
			return $row ["$this->field_id"];
		} else {
			$sql = 'SELECT ' . $this->field_id . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_name . "='" . $this->db->escape_string ( $post_username ) . "' AND " . $this->field_pass . " ='" . $this->compile_password ( array ('password' => $password ) ) . "'";
			$row = $this->db->query_first ( $sql );
			return $row ["$this->field_id"];
		}
	}

	/**
	 * 检查指定邮箱是否存在
	 *
	 * @access  public
	 * @param   string  $email   用户邮箱
	 *
	 * @return  boolean
	 */
	public function check_email($email) {
		if (! empty ( $email )) {
			// 检查email是否重复
			$sql = 'SELECT ' . $this->field_id . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_email . " = '" . $this->db->escape_string ( $email ) . "' ";
			$row = $this->db->query_first ( $sql );
			if ($row ["$this->field_id"] > 0) {
				$this->error = ERR_EMAIL_EXISTS;
				return true;
			}
			return false;
		}
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
			return $_COOKIE [COOKIE_PREFIX . 'username'];
		}
		return '';
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
		global $skyuc;

		if (empty ( $username )) {
			// 摧毁cookie
			$time = TIMENOW - 86400;
			skyuc_setcookie ( 'username', '', $time );
			skyuc_setcookie ( 'userid', '', $time );
			skyuc_setcookie ( 'password', '', $time );

		} else {
			// 设置cookie
			$time = TIMENOW + 86400 * 7;

			skyuc_setcookie ( 'username', $username, $time );

			$sql = 'SELECT user_id, password FROM ' . TABLE_PREFIX . 'users' . " WHERE user_name='" . $this->db->escape_string ( $username ) . "' ";
			$row = $skyuc->db->query_first ( $sql );
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
	 * 在给定的表名前加上数据库名以及前缀
	 *
	 * @access  private
	 * @param   string      $str    表名
	 *
	 * @return void
	 */
	public function table($str) {
		return '`' . $this->db_name . '`.`' . $this->prefix . $str . '`';
	}

	/**
	 * 编译密码函数
	 *
	 * @access  public
	 * @param   array   $cfg 包含参数为 $password, $md5password, $salt, $type
	 *
	 * @return void
	 */
	public function compile_password($cfg) {
		if (isset ( $cfg ['password'] )) {
			$cfg ['md5password'] = md5 ( $cfg ['password'] );
		}
		if (empty ( $cfg ['type'] )) {
			$cfg ['type'] = PWD_MD5;
		}

		switch ($cfg ['type']) {
			case PWD_MD5 :
				return $cfg ['md5password'];

			case PWD_PRE_SALT :
				if (empty ( $cfg ['salt'] )) {
					$cfg ['salt'] = '';
				}

				return md5 ( $cfg ['salt'] . $cfg ['md5password'] );

			case PWD_SUF_SALT :
				if (empty ( $cfg ['salt'] )) {
					$cfg ['salt'] = '';
				}

				return md5 ( $cfg ['md5password'] . $cfg ['salt'] );

			default :
				return '';
		}
	}

	/**
	 * 会员同步
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function sync($username, $password = '', $md5password = '') {
		global $skyuc;
		if ((! empty ( $password )) && empty ( $md5password )) {
			$md5password = md5 ( $password );
		}

		$main_profile = $this->get_profile_by_name ( $username );

		if (empty ( $main_profile )) {
			return false;
		}

		$sql = 'SELECT user_name, email, password, gender, birthday' . ' FROM ' . TABLE_PREFIX . 'users' . " WHERE user_name = '" . $this->db->escape_string ( $username ) . "'";
		$profile = $skyuc->db->query_first ( $sql );
		if (empty ( $profile )) {
			// 插入一条新记录
			if (empty ( $md5password )) {
				$sql = 'INSERT INTO ' . TABLE_PREFIX . 'users' . ' (user_name, email, gender, birthday, reg_time) ' . " VALUES('" . $this->db->escape_string ( $username ) . "', '" . $this->db->escape_string ( $main_profile ['email'] ) . "','" . $main_profile ['gender'] . "','" . $main_profile ['birthday'] . "','" . $main_profile ['reg_time'] . "')";
			} else {
				$sql = 'INSERT INTO ' . TABLE_PREFIX . 'users' . '(user_name, email, gender, birthday, reg_time, password)' . " VALUES('" . $this->db->escape_string ( $username ) . "', '" . $this->db->escape_string ( $main_profile ['email'] ) . "','" . $main_profile ['gender'] . "','" . $main_profile ['birthday'] . "','" . $main_profile ['reg_time'] . "', '$md5password')";

			}

			$skyuc->db->query_write ( $sql );

			return true;
		} else {
			$values = array ();
			if ($main_profile ['email'] != $profile ['email']) {
				$values [] = "email='" . $this->db->escape_string ( $main_profile ['email'] ) . "'";
			}
			if ($main_profile ['gender'] != $profile ['gender']) {
				$values [] = "gender='" . $main_profile ['gender'] . "'";
			}
			if ($main_profile ['birthday'] != $profile ['birthday']) {
				$values [] = "birthday='" . $main_profile ['birthday'] . "'";
			}
			if ((! empty ( $md5password )) && ($md5password != $profile ['password'])) {
				$values [] = "password='" . $md5password . "'";
			}

			if (empty ( $values )) {
				return true;
			} else {
				$sql = 'UPDATE ' . TABLE_PREFIX . 'users' . ' SET ' . implode ( ', ', $values ) . " WHERE user_name='" . $this->db->escape_string ( $username ) . "'";

				$skyuc->db->query_write ( $sql );

				return true;
			}
		}
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
		return array ();
	}

	/**
	 * 获取用户积分
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function get_points($username) {
		$credits = $this->get_points_name ();
		$fileds = array_keys ( $credits );
		if ($fileds) {
			if ($this->charset != 'UTF8') {
				$username = skyuc_iconv ( 'UTF8', $this->charset, $username );
			}
			$sql = 'SELECT ' . $this->field_id . ', ' . implode ( ', ', $fileds ) . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_name . "='" . $this->db->escape_string ( $username ) . "'";
			$row = $this->db->query_write ( $sql );
			return $row;
		} else {
			return false;
		}
	}

	/**
	 *设置用户积分
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function set_points($username, $credits) {
		$user_set = array_keys ( $credits );
		$points_set = array_keys ( $this->get_points_name () );

		$set = array_intersect ( $user_set, $points_set );

		if ($set) {
			if ($this->charset != 'UTF8') {
				$username = skyuc_iconv ( 'UTF8', $this->charset, $username );
			}
			$tmp = array ();
			foreach ( $set as $credit ) {
				$tmp [] = $credit . '=' . $credit . '+' . $credits ["$credit"];
			}
			$sql = 'UPDATE ' . $this->table ( $this->user_table ) . ' SET ' . implode ( ', ', $tmp ) . ' WHERE ' . $this->field_name . " = '" . $this->db->escape_string ( $username ) . "'";
			$this->db->query_write ( $sql );
		}

		return true;
	}

	public function get_user_info($username) {
		return $this->get_profile_by_name ( $username );
	}

	/**
	 * 检查有无重名用户，有则返回重名用户
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function test_conflict($user_list) {
		if (empty ( $user_list )) {
			return array ();
		}

		if ($this->charset != 'UTF8') {
			$count = count ( $user_list );
			for($i = 0; $i < $count; $i ++) {
				$user_list [$i] = skyuc_iconv ( 'UTF8', $this->charset, $user_list [$i] );
			}
		}

		$sql = 'SELECT ' . $this->field_name . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . db_create_in ( $user_list, $this->field_name );
		$user_list = array ();
		$res = $this->db->query_read ( $sql );
		while ( $row = $this->db->fetch_row ( $res ) ) {
			if ($user_list && ($this->charset != 'UTF8')) {
				$row [0] = skyuc_iconv ( 'UTF8', $this->charset, $row [0] );
			}
			$user_list [] = $row [0];
		}
		return $user_list;
	}
}
