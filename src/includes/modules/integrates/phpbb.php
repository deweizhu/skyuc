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
	$modules [$i] ['code'] = 'phpbb';

	// 被整合的第三方程序的名称
	$modules [$i] ['name'] = 'phpBB';

	// 被整合的第三方程序的版本
	$modules [$i] ['version'] = '3.x';

	// 插件的作者
	$modules [$i] ['author'] = 'SKYUC! R&D TEAM';

	// 插件作者的官方网站
	$modules [$i] ['website'] = 'http://www.skyuc.com';

	// 插件的初始的默认值
	$modules [$i] ['default'] ['db_host'] = 'localhost';
	$modules [$i] ['default'] ['db_user'] = 'root';
	$modules [$i] ['default'] ['prefix'] = 'phpbb_';
	//$modules[$i]['default']['cookie_prefix'] = 'xn_';


	return;
}

require_once (DIR . '/includes/modules/integrates/integrate.php');
class phpbb extends integrate {
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
			/* 数据库连接出错 */
			return false;
		}
		//$this->cookie_prefix = $cfg['cookie_prefix'];
		$this->field_id = 'user_id';
		$this->field_name = 'username';
		$this->field_email = 'user_email';
		$this->field_gender = 'username_clean'; //由于PHPBB必须有username_clean字段值，因此用此字段代替。
		$this->field_bday = 'NULL';
		$this->field_pass = 'user_password';
		$this->field_reg_date = 'user_regdate';
		$this->user_table = 'users';

		// 检查数据表是否存在
		$sql = "SHOW TABLES LIKE '" . $this->prefix . "%'";
		$exist_tables = array ();
		$res = $this->db->query_read ( $sql );
		while ( $row = $this->db->fetch_row ( $res ) ) {
			$exist_tables [] = $row [0];
		}

		if (empty ( $exist_tables ) || (! in_array ( $this->prefix . $this->user_table, $exist_tables )) || (! in_array ( $this->prefix . 'config', $exist_tables ))) {
			$this->error = 2;
			// 缺少数据表
			return false;
		}

		$row = $this->db->query_first ( 'SELECT config_value FROM ' . $this->table ( 'config' ) . " WHERE config_name='cookie_name'" );
		$this->cookie_prefix = $row ['config_value'];

		$row = $this->db->query_first ( 'SELECT conf_value FROM ' . $this->table ( 'config' ) . " WHERE config_name='cookie_path'" );
		$this->cookie_path = $row ['config_value'];
		if (empty ( $this->cookie_path )) {
			$this->cookie_path = '/';
		}

		$row = $this->db->query_first ( 'SELECT conf_value FROM ' . $this->table ( 'config' ) . " WHERE config_name='cookie_domain'" );
		$this->cookie_domain = $row ['config_value'];
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
		//由于PHPBB必须有username_clean字段值，因此用此字段代替。
		$username_clean = fetch_try_to_ascii ( $username );

		if ($this->charset != 'UTF8') {
			$username_clean = skyuc_iconv ( 'UTF8', $this->charset, $username_clean );
		}
		$username_clean = $this->db->escape_string ( $username_clean );

		// 编译密码
		if (! empty ( $password )) {
			$md5password = $this->phpbb_hash ( $password );
		}

		$result = parent::add_user ( $username, $password, $email, $username_clean, 0, $reg_date, $md5password );

		if (! $result) {
			return false;
		}
		// 更新数据


		$sql = 'UPDATE ' . $this->table ( $this->user_table ) . ' SET ' . $this->field_reg_date . " = '" . TIMENOW . "' ";
		if (! empty ( $md5password )) {
			$sql .= ' , user_pass_convert = 1 ';
		}
		$sql .= " WHERE " . $this->field_name . "='" . $this->db->escape_string ( $username ) . "'";
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
			$sql = 'SELECT ' . $this->field_id . ' AS user_id ' . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_name . "='" . $this->db->escape_string ( $post_username ) . "'";
			$row = $this->db->query_first ( $sql );

			return $row ['user_id'];
		} else {
			$sql = 'SELECT ' . $this->field_id . ' AS user_id, ' . $this->field_pass . ' AS password ' . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_name . "='" . $this->db->escape_string ( $post_username ) . "'";
			$row = $this->db->query_first ( $sql );

			if (empty ( $row )) {
				return 0;
			}

			if (! $this->phpbb_check_hash ( $password, $row ['password'] )) {
				return 0;
			}

			return $row ['user_id'];

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
			$time = TIMENOW - 86400;
			setcookie ( $this->cookie_prefix . '_k', '', $time, $this->cookie_path, $this->cookie_domain );
			setcookie ( $this->cookie_prefix . '_u', '', $time, $this->cookie_path, $this->cookie_domain );
			setcookie ( $this->cookie_prefix . '_sid', '', $time, $this->cookie_path, $this->cookie_domain );
		} else {
			if ($this->charset != 'UTF8') {
				$username = skyuc_iconv ( 'UTF8', $this->charset, $username );
			}

			$sql = 'SELECT ' . $this->field_id . ' AS user_id, ' . $this->field_name . ' AS user_name, ' . $this->field_email . ' AS email ' . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_name . " = '" . $this->db->escape_string ( $username ) . "'";

			$row = $this->db->query_first ( $sql );

			$session_id = md5 ( $this->unique_id () );
			$key_id = $this->unique_id ( hexdec ( substr ( $this->session_id, 0, 8 ) ) );

			// 向整合对象的数据表里写入 cookie 值
			$this->db->query_write ( 'INSERT INTO ' . $this->table ( 'sessions_keys' ) . " (key_id, user_id, last_login) " . "VALUES ('" . md5 ( $key_id ) . "', '" . $row ['user_id'] . "', '" . TIMENOW . "')" );

			$sql = 'INSERT INTO ' . $this->table ( 'sessions' ) . ' (session_id, session_user_id, session_start, session_time, session_ip, session_autologin, session_admin) ' . " VALUES('" . $session_id . "', '" . $row ['user_id'] . "','" . TIMENOW . "','" . TIMENOW . "','" . $this->encode_ip ( ALT_IP ) . "',1, 0)";
			$this->db->query_write ( $sql );

			// 设置cookie
			$time = TIMENOW + 86400 * 7;
			setcookie ( $this->cookie_prefix . '_u', $row ['user_id'], $time, $this->cookie_path, $this->cookie_domain );
			setcookie ( $this->cookie_prefix . '_k', $key_id, $time, $this->cookie_path, $this->cookie_domain );
			setcookie ( $this->cookie_prefix . '_sid', $session_id, $time, $this->cookie_path, $this->cookie_domain );
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

		// 取得用户信息
		$user_id = ( int ) $_COOKIE [$this->cookie_prefix . '_u'];
		$key_id = $_COOKIE [$this->cookie_prefix . '_k'];
		$session_id = trim ( $_COOKIE [$this->cookie_prefix . '_sid'] );

		if (empty ( $user_id ) || empty ( $key_id ) || empty ( $session_id )) {
			return '';
		}

		$sql = 'SELECT ' . $this->field_name . ' FROM ' . $this->table ( 'sessions' ) . ' AS s ' . ' LEFT JOIN ' . $this->table ( $this->user_table ) . ' AS u ON s.session_user_id = u.user_id' . " WHERE session_id = '" . $this->db->escape_string ( $session_id ) . "' AND session_user_id = '" . $user_id . "'";

		$row = $this->db->query_first ( $sql );
		$username = $row ["$this->field_name"];

		if (empty ( $username )) {
			return '';
		} else {
			if ($this->charset != 'UTF8') {
				$username = skyuc_iconv ( $this->charset, 'UTF8', $username );
			}

			return $username;
		}
	}

	/**
	 * PHPBB 3.0生成随机数函数
	 */
	public function unique_id() {
		$dss_seeded = false;
		$row = $this->db->query_first ( 'SELECT config_value FROM ' . $this->table ( 'config' ) . " WHERE config_name = 'rand_seed'" );
		$config = $this->db->query_first ( 'SELECT config_value FROM ' . $this->table ( 'config' ) . " WHERE config_name = 'rand_seed_last_update'" );

		$val = $row ['config_value'] . microtime ();
		$val = md5 ( $val );
		$rand_seed = md5 ( $rand_seed . $val . 'a' );

		if ($dss_seeded !== true && ($config ['config_value'] < TIMENOW - rand ( 1, 10 ))) {
			$sql = 'UPDATE ' . $this->table ( 'config' ) . " SET config_value = '" . $rand_seed . "' WHERE config_name = 'rand_seed'";
			if (! $this->db->query_write ( $sql )) {
				die ( 'error' );
			}
			$sql = 'UPDATE ' . $this->table ( 'config' ) . " SET config_value ='" . TIMENOW . "' WHERE config_name = 'rand_seed_last_update = '";
			$this->db->query_write ( $sql );

			$dss_seeded = true;
		}

		return substr ( $val, 4, 16 );
	}

	public function encode_ip($dotquad_ip) {
		$ip_sep = explode ( '.', $dotquad_ip );

		return sprintf ( '%02x%02x%02x%02x', $ip_sep [0], $ip_sep [1], $ip_sep [2], $ip_sep [3] );
	}

	/**
	 *
	 * @version Version 0.1 / slightly modified for phpBB 3.0.x (using $H$ as hash type identifier)
	 *
	 * Portable PHP password hashing framework.
	 *
	 * Written by Solar Designer <solar at openwall.com> in 2004-2006 and placed in
	 * the public domain.
	 *
	 * There's absolutely no warranty.
	 *
	 * The homepage URL for this framework is:
	 *
	 * http://www.openwall.com/phpass/
	 *
	 * Please be sure to update the Version line if you edit this file in any way.
	 * It is suggested that you leave the main version number intact, but indicate
	 * your project name (after the slash) and add your own revision information.
	 *
	 * Please do not change the "private" password hashing method implemented in
	 * here, thereby making your hashes incompatible.  However, if you must, please
	 * change the hash type identifier (the "$P$") to something different.
	 *
	 * Obviously, since this code is in the public domain, the above are not
	 * requirements (there can be none), but merely suggestions.
	 *
	 *
	 * Hash the password
	 */
	function phpbb_hash($password) {
		$itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

		$random_state = $this->unique_id ();
		$random = '';
		$count = 6;

		if (($fh = @fopen ( '/dev/urandom', 'rb' ))) {
			$random = fread ( $fh, $count );
			fclose ( $fh );
		}

		if (strlen ( $random ) < $count) {
			$random = '';

			for($i = 0; $i < $count; $i += 16) {
				$random_state = md5 ( $this->unique_id () . $random_state );
				$random .= pack ( 'H*', md5 ( $random_state ) );
			}
			$random = substr ( $random, 0, $count );
		}

		$hash = $this->_hash_crypt_private ( $password, $this->_hash_gensalt_private ( $random, $itoa64 ), $itoa64 );

		if (strlen ( $hash ) == 34) {
			return $hash;
		}

		return md5 ( $password );
	}

	/**
	 * The crypt function/replacement
	 */
	function _hash_crypt_private($password, $setting, &$itoa64) {
		$output = '*';

		// Check for correct hash
		if (substr ( $setting, 0, 3 ) != '$H$') {
			return $output;
		}

		$count_log2 = strpos ( $itoa64, $setting [3] );

		if ($count_log2 < 7 || $count_log2 > 30) {
			return $output;
		}

		$count = 1 << $count_log2;
		$salt = substr ( $setting, 4, 8 );

		if (strlen ( $salt ) != 8) {
			return $output;
		}

		/**
		 * We're kind of forced to use MD5 here since it's the only
		 * cryptographic primitive available in all versions of PHP
		 * currently in use.  To implement our own low-level crypto
		 * in PHP would result in much worse performance and
		 * consequently in lower iteration counts and hashes that are
		 * quicker to crack (by non-PHP code).
		 */

		$hash = md5 ( $salt . $password, true );
		do {
			$hash = md5 ( $hash . $password, true );
		} while ( -- $count );

		$output = substr ( $setting, 0, 12 );
		$output .= $this->_hash_encode64 ( $hash, 16, $itoa64 );

		return $output;
	}
	/**
	 * Generate salt for hash generation
	 */
	function _hash_gensalt_private($input, &$itoa64, $iteration_count_log2 = 6) {
		if ($iteration_count_log2 < 4 || $iteration_count_log2 > 31) {
			$iteration_count_log2 = 8;
		}

		$output = '$H$';
		$output .= $itoa64 [min ( $iteration_count_log2 + ((PHP_VERSION >= 5) ? 5 : 3), 30 )];
		$output .= $this->_hash_encode64 ( $input, 6, $itoa64 );

		return $output;
	}

	/**
	 * Encode hash
	 */
	function _hash_encode64($input, $count, &$itoa64) {
		$output = '';
		$i = 0;

		do {
			$value = ord ( $input [$i ++] );
			$output .= $itoa64 [$value & 0x3f];

			if ($i < $count) {
				$value |= ord ( $input [$i] ) << 8;
			}

			$output .= $itoa64 [($value >> 6) & 0x3f];

			if ($i ++ >= $count) {
				break;
			}

			if ($i < $count) {
				$value |= ord ( $input [$i] ) << 16;
			}

			$output .= $itoa64 [($value >> 12) & 0x3f];

			if ($i ++ >= $count) {
				break;
			}

			$output .= $itoa64 [($value >> 18) & 0x3f];
		} while ( $i < $count );

		return $output;
	}

	/**
	 * Check for correct password
	 *
	 * @param string $password The password in plain text
	 * @param string $hash The stored password hash
	 *
	 * @return bool Returns true if the password is correct, false if not.
	 */
	function phpbb_check_hash($password, $hash) {
		$itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		if (strlen ( $hash ) == 34) {
			return ($this->_hash_crypt_private ( $password, $hash, $itoa64 ) === $hash) ? true : false;
		}

		return (md5 ( $password ) === $hash) ? true : false;
	}

}