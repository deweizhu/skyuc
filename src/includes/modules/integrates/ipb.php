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
	$i = (isset ( $modules )) ? count ( $modules ) : 0;

	// 会员数据整合插件的代码必须和文件名保持一致
	$modules [$i] ['code'] = 'ipb';

	// 被整合的第三方程序的名称
	$modules [$i] ['name'] = 'Invision Power Board';

	// 被整合的第三方程序的版本
	$modules [$i] ['version'] = '3.x';

	// 插件的作者
	$modules [$i] ['author'] = 'SKYUC! R&D TEAM';

	// 插件作者的官方网站
	$modules [$i] ['website'] = 'http://www.skyuc.com';

	// 插件的初始的默认值
	$modules [$i] ['default'] ['db_host'] = 'localhost';
	$modules [$i] ['default'] ['db_user'] = 'root';
	$modules [$i] ['default'] ['prefix'] = 'ipb_';
	//$modules[$i]['default']['cookie_prefix'] = 'xnW_';


	return;
}

require_once (DIR . '/includes/modules/integrates/integrate.php');
class ipb extends integrate {
	public $cookie_prefix = '';

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
			// 数据库连接出错
			return false;
		}
		//$this->cookie_prefix = $cfg['cookie_prefix'];
		$this->field_id = 'member_id';
		$this->field_name = 'name';
		$this->field_email = 'email';
		$this->field_gender = 'NULL';
		$this->field_bday = 'NULL';
		$this->field_pass = 'members_pass_hash';
		$this->field_reg_date = 'joined';
		$this->user_table = 'members';

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
		$row = $this->db->query_first ( 'SELECT conf_value FROM ' . $this->table ( 'core_sys_conf_settings' ) . " WHERE conf_key='cookie_id'" );
		$this->cookie_prefix = $row ['conf_value'];

		$row = $this->db->query_first ( 'SELECT conf_value FROM ' . $this->table ( 'core_sys_conf_settings' ) . " WHERE conf_key='cookie_path'" );
		$this->cookie_path = $row ['conf_value'];
		if (empty ( $this->cookie_path )) {
			$this->cookie_path = '/';
		}

		$row = $this->db->query_first ( 'SELECT conf_value FROM ' . $this->table ( 'core_sys_conf_settings' ) . " WHERE conf_key='cookie_domain'" );
		$this->cookie_domain = $row ['conf_value'];
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
		/*        // 将用户添加到整合方
        if ($this->check_user($username) > 0)
        {
            $this->error = ERR_USERNAME_EXISTS;

            return false;
        }
        // 检查email是否重复
        $sql = 'SELECT ' . $this->field_id . ' AS member_id'.
               ' FROM ' . $this->table($this->user_table).
               ' WHERE ' . $this->field_email . " = '". $this->db->escape_string($email) ."'";
        $row = $this->db->query_first($sql);
        if ($row['member_id'] > 0)
        {
            $this->error = ERR_EMAIL_EXISTS;

            return false;
        }
        */

		if ($this->charset != 'UTF8') {
			$post_username = skyuc_iconv ( 'UTF8', $this->charset, $username );
		} else {
			$post_username = $username;
		}

		$result = parent::add_user ( $username, $password, $email, $gender, 0, $reg_date, $md5password );

		if (! $result) {
			return false;
		}

		$post_username = $this->db->escape_string ( $post_username );
		// 生成随机串
		$salt = $this->generate_password_salt ( 5 );

		// 生成加密密码
		$members_pass_hash = $this->compile_password ( array ('password' => $password, 'salt' => $salt ) );

		// 规格化随机串
		$members_pass_salt = str_replace ( '\\', "\\\\", $salt );

		// 获得默认的用户组
		$grp = 1;

		// 生成自动登录密钥，存于COOKIE中
		$member_login_key = $this->generate_auto_log_in_key ();

		// 更新数据
		$sql = 'UPDATE ' . $this->table ( $this->user_table ) . ' SET member_group_id =1 ,' . "	ip_address ='" . ALT_IP . "', " . "	member_login_key ='" . $member_login_key . "', " . "	members_display_name ='" . $post_username . "', " . "  members_seo_name ='" . $post_username . "', " . "	members_l_display_name ='" . $post_username . "', " . "	members_l_username ='" . $post_username . "' " . " WHERE " . $this->field_name . "='" . $post_username . "'";
		$this->db->query_write ( $sql );

		return true;
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
			$sql = 'SELECT ' . $this->field_id . ' AS member_id' . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_name . "='" . $post_username . "'";

			$row = $this->db->query_first ( $sql );
			return $row ['member_id'];
		} else {

			$sql = 'SELECT member_id, member_login_key, members_pass_hash, members_pass_salt' . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_name . " = '" . $this->db->escape_string ( $post_username ) . "'";
			$row = $this->db->query_first ( $sql );

			if ($row ['members_pass_hash'] != $this->compile_password ( array ('password' => $password, 'salt' => $row ['members_pass_salt'] ) )) {
				return 0;
			} else {
				return $row ['member_id'];
			}
		}
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
			// 清除COOKIE
			$time = TIMENOW - 86400;
			setcookie ( $this->cookie_prefix . 'session_id', '', $time, $this->cookie_path, $this->cookie_domain );
			setcookie ( $this->cookie_prefix . 'member_id', '', $time, $this->cookie_path, $this->cookie_domain );
			setcookie ( $this->cookie_prefix . 'pass_hash', '', $time, $this->cookie_path, $this->cookie_domain );
		} else {
			// 保存 COOKIE
			$time = TIMENOW + 86400 * 7;
			if ($this->charset != 'UTF8') {
				$username = skyuc_iconv ( 'UTF8', $this->charset, $username );
			}
			$sql = 'SELECT ' . $this->field_id . ' AS user_id, member_login_key ' . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_name . " ='" . $this->db->escape_string ( $username ) . "'";
			$row = $this->db->query_first ( $sql );

			if ($row) {
				setcookie ( $this->cookie_prefix . 'member_id', $row ['user_id'], $time, $this->cookie_path, $this->cookie_domain );
				setcookie ( $this->cookie_prefix . 'pass_hash', $row ['member_login_key'], $time, $this->cookie_path, $this->cookie_domain );
			}
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
		if (empty ( $_COOKIE [$this->cookie_prefix . 'member_id'] ) || empty ( $_COOKIE [$this->cookie_prefix . 'pass_hash'] )) {
			return '';
		}

		$user_id = intval ( $_COOKIE [$this->cookie_prefix . 'member_id'] );
		$member_login_key = strval ( $_COOKIE [$this->cookie_prefix . 'pass_hash'] );

		$sql = 'SELECT ' . $this->field_name . ' AS user_name' . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_id . " = '" . $user_id . "' AND `member_login_key` = '" . $member_login_key . "'";

		$row = $this->db->query_first ( $sql );
		$username = $row ['user_name'];

		if ($username && ($this->charset != 'UTF8')) {
			$username = skyuc_iconv ( $this->charset, 'UTF8', $username );
		}

		return $username;
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
		if ((! empty ( $cfg ['password'] )) && empty ( $cfg ['md5password'] )) {
			$cfg ['md5password'] = md5 ( $cfg ['password'] );
		}

		if (! isset ( $cfg ['salt'] )) {
			$cfg ['salt'] = '';
		}

		return md5 ( md5 ( $cfg ['salt'] ) . $cfg ['md5password'] );
	}

	/**
	 * Generates a password salt
	 *
	 * Returns n length string of any char except backslash
	 *
	 * @access   private
	 * @param    integer Length of desired salt, 5 by default
	 * @return   string  n character random string
	 */
	public function generate_password_salt($len = 5) {
		$salt = '';

		//srand( (double)microtime() * 1000000 );
		// PHP 4.3 is now required ^ not needed


		for($i = 0; $i < $len; $i ++) {
			$num = mt_rand ( 33, 126 );

			if ($num == '92') {
				$num = 93;
			}

			$salt .= chr ( $num );
		}

		return $salt;
	}

	/**
	 * Generates a log in key
	 *
	 * @access   private
	 * @param    integer Length of desired random chars to MD5
	 * @return   string  MD5 hash of random characters
	 */
	public function generate_auto_log_in_key($len = 60) {
		$pass = $this->generate_password_salt ( 60 );

		return md5 ( $pass );
	}
}