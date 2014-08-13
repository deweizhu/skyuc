<?php

/**
 * SKYUC! 会员数据处理类
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

	// 会员数据整合插件的代码必须和文件名保持一致
	$modules [$i] ['code'] = 'vbb';

	// 被整合的第三方程序的名称
	$modules [$i] ['name'] = 'vBulletin';

	// 被整合的第三方程序的版本
	$modules [$i] ['version'] = '3.x';

	// 插件的作者
	$modules [$i] ['author'] = 'SKYUC! R&D TEAM';

	// 插件作者的官方网站
	$modules [$i] ['website'] = 'http://www.skyuc.com';

	// 插件的初始的默认值
	$modules [$i] ['default'] ['db_host'] = 'localhost';
	$modules [$i] ['default'] ['db_user'] = 'root';
	$modules [$i] ['default'] ['prefix'] = 'vbb_';
	$modules [$i] ['default'] ['cookie_salt'] = '';
	$modules [$i] ['default'] ['cookie_prefix'] = 'bb';

	return;
}

require_once (DIR . '/includes/modules/integrates/integrate.php');
class vbb extends integrate {
	public $cookie_salt = '';

	/**
	 *
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function __construct($cfg) {
		parent::__construct ( $cfg );
		if ($this->error) {
			/* 数据库连接出错 */
			return false;
		}

		$this->cookie_salt = $cfg ['cookie_salt'];
		$this->cookie_prefix = $cfg ['cookie_prefix'];
		$this->field_id = 'userid';
		$this->field_name = 'username';
		$this->field_email = 'email';
		$this->field_gender = 'NULL';
		$this->field_bday = 'birthday';
		$this->field_pass = 'password';
		$this->field_reg_date = 'joindate';
		$this->user_table = 'user';

		// 检查数据表是否存在
		$sql = "SHOW TABLES LIKE '" . $this->prefix . "%'";
		$exist_tables = array ();
		$res = $this->db->query_read ( $sql );
		while ( $row = $this->db->fetch_row ( $res ) ) {
			$exist_tables [] = $row [0];
		}

		if (empty ( $exist_tables ) || (! in_array ( $this->prefix . $this->user_table, $exist_tables ))) {
			$this->error = 2;
			// 缺少数据表
			return false;
		}

		$row = $this->db->query_first ( 'SELECT value FROM ' . $this->table ( 'setting' ) . " WHERE varname ='cookiepath'" );
		$this->cookie_path = $row ['value'];
		if (empty ( $this->cookie_path )) {
			$this->cookie_path = '/';
		}

		$row = $this->db->query_first ( 'SELECT value FROM ' . $this->table ( 'setting' ) . " WHERE varname ='cookiedomain'" );
		$this->cookie_domain = $row ['value'];
	}

	/**
	 * 添加新用户的函数
	 *
	 * @access      public
	 * @param       string      username    用户名
	 * @param       string      password    登录密码
	 * @param       string      email       邮件地址
	 * @param       string      bday        生日
	 * @param       string      gender      性别
	 * @return      int         返回最新的ID
	 */
	public function add_user($username, $password, $email, $gender = -1, $bday = 0, $reg_date = 0, $md5password = '') {
		//由于VBB表没有gender字段，故用-1跳过此字段
		$result = parent::add_user ( $username, $password, $email, - 1, $bday, $reg_date, $md5password );

		if (! $result) {
			return false;
		}
		// 会员自动升级发帖数
		$row = $this->db->query_first ( 'SELECT title FROM ' . $this->table ( 'usertitle' ) . ' ORDER BY minposts' );

		$user_title = $row ['title'];

		if ($this->charset != 'UTF8') {
			$username = skyuc_iconv ( 'UTF8', $this->charset, $username );
		}

		// 编译密码
		$salt = $this->db->escape_string ( $this->fetch_user_salt () );

		// 更新数据
		$sql = 'UPDATE ' . $this->table ( $this->user_table ) . ' SET ' . $this->field_pass . " = '" . $this->compile_password ( array ('type' => PWD_SUF_SALT, 'password' => $password, 'salt' => $salt ) ) . "', " . " salt = '$salt', " . " ipaddress = '" . ALT_IP . "', " . ' usergroupid = 2, ' . " usertitle = '" . $this->db->escape_string ( $user_title ) . "' " . " WHERE " . $this->field_name . "='" . $this->db->escape_string ( $username ) . "'";

		$this->db->query_write ( $sql );

		$sql = 'INSERT INTO ' . $this->table ( 'userfield' ) . ' (' . $this->field_id . ') ' . ' SELECT ' . $this->field_id . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_name . "='" . $this->db->escape_string ( $username ) . "'";
		$this->db->query_write ( $sql );

		$sql = 'INSERT INTO ' . $this->table ( 'usertextfield' ) . ' (' . $this->field_id . ') ' . ' SELECT ' . $this->field_id . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_name . "='" . $this->db->escape_string ( $username ) . "'";
		$this->db->query_write ( $sql );

		return true;
	}

	/**
	 * 设置论坛cookie
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function set_cookie($username = '') {
		parent::set_cookie ( $username );
		if (empty ( $username )) {
			$time = TIMENOW - 86400;
			setcookie ( $this->cookie_prefix . 'userid', '', $time, $this->cookie_path, $this->cookie_domain );
			setcookie ( $this->cookie_prefix . 'password', '', $time, $this->cookie_path, $this->cookie_domain );
			setcookie ( $this->cookie_prefix . 'sessionhash', '', $time, $this->cookie_path, $this->cookie_domain );

		} else {
			if ($this->charset != 'UTF8') {
				$username = skyuc_iconv ( 'UTF8', $this->charset, $username );
			}
			$sql = 'SELECT ' . $this->field_id . ' AS user_id, ' . $this->field_pass . ' As password ' . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_name . "='" . $this->db->escape_string ( $username ) . "'";

			$row = $this->db->query_first ( $sql );

			$time = TIMENOW + 86400 * 7;
			setcookie ( $this->cookie_prefix . 'userid', $row ['user_id'], $time, $this->cookie_path, $this->cookie_domain );
			setcookie ( $this->cookie_prefix . 'password', md5 ( $row ['password'] . $this->cookie_salt ), $time, $this->cookie_path, $this->cookie_domain );
		}
	}

	/**
	 * 检查cookie
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function check_cookie() {
		if (empty ( $_COOKIE [$this->cookie_prefix . 'userid'] ) || empty ( $_COOKIE [$this->cookie_prefix . 'password'] )) {
			return '';
		}

		$user_id = intval ( $_COOKIE [$this->cookie_prefix . 'userid'] );
		$bbpassword = strval ( $_COOKIE [$this->cookie_prefix . 'password'] );

		$row = $this->db->query_first ( 'SELECT ' . $this->field_name . ' AS user_name, ' . $this->field_pass . ' As password ' . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_id . "='" . $user_id . "'" );
		if (empty ( $row )) {
			return '';
		}

		if ($bbpassword != md5 ( $row ['password'] . $this->cookie_salt )) {
			return '';
		}

		if ($this->charset != 'UTF8') {
			$row ['user_name'] = skyuc_iconv ( $this->charset, 'UTF8', $row ['user_name'] );
		}

		return $row ['user_name'];

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

		if ($password === null) {
			$sql = 'SELECT ' . $this->field_id . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_name . "='" . $this->db->escape_string ( $post_username ) . "'";
			$row = $this->db->query_first ( $sql );

			return $row ["$this->field_id"];
		} else {
			$sql = 'SELECT ' . $this->field_id . ' AS user_id, ' . $this->field_pass . ' AS password, salt' . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_name . "='" . $this->db->escape_string ( $post_username ) . "'";
			$row = $this->db->query_first ( $sql );

			if (empty ( $row )) {
				return 0;
			}

			if ($row ['password'] != $this->compile_password ( array ('type' => PWD_SUF_SALT, 'password' => $password, 'salt' => $row ['salt'] ) )) {
				return 0;
			}

			return $row ['user_id'];

		}
	}

	/**
	 * 生成密码种子的函数
	 *
	 * @access      private
	 * @param       int     length        长度
	 * @return      string
	 */
	public function fetch_user_salt($length = 3) {
		$salt = '';
		for($i = 0; $i < $length; $i ++) {
			$salt .= chr ( mt_rand ( 32, 126 ) );
		}

		return $salt;
	}

}