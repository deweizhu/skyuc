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
	$modules [$i] ['code'] = 'phpwind';

	// 被整合的第三方程序的名称
	$modules [$i] ['name'] = 'PHPWind';

	// 被整合的第三方程序的版本
	$modules [$i] ['version'] = '6.32/7.x';

	// 插件的作者
	$modules [$i] ['author'] = 'SKYUC! R&D TEAM';

	// 插件作者的官方网站
	$modules [$i] ['website'] = 'http://www.skyuc.com';

	// 插件的初始的默认值
	$modules [$i] ['default'] ['db_host'] = 'localhost';
	$modules [$i] ['default'] ['db_user'] = 'root';
	$modules [$i] ['default'] ['prefix'] = 'pw_';

	return;
}

require_once (DIR . '/includes/modules/integrates/integrate.php');
class phpwind extends integrate {
	// 论坛加密密钥
	public $db_hash = '';

	/**
	 * 插件类初始化函数
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
		$this->field_id = 'uid';
		$this->field_name = 'username';
		$this->field_email = 'email';
		$this->field_gender = 'gender';
		$this->field_safecv = 'safecv';
		$this->field_bday = 'bday';
		$this->field_pass = 'password';
		$this->field_reg_date = 'regdate';
		$this->user_table = 'members';

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

		// 设置论坛的加密密钥
		$db_hash = $this->db->query_first ( "SELECT `db_value` FROM " . $this->table ( 'config' ) . " WHERE `db_name` = 'db_hash'" );
		$this->db_hash = $db_hash ['db_value'];

		$db_sitehash = $this->db->query_first ( "SELECT `db_value` FROM " . $this->table ( 'config' ) . " WHERE `db_name` = 'db_sitehash'" );
		$this->db_sitehash = $db_sitehash ['db_value'];
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
		$result = parent::add_user ( $username, $password, $email, $gender, $bday, $reg_date, $md5password );

		if (! $result) {
			return false;
		}

		if ($this->charset != 'UTF8') {
			$username = skyuc_iconv ( 'UTF8', $this->charset, $username );
		}

		/* 更新memberdata表 */
		$sql = 'INSERT INTO ' . $this->table ( 'memberdata' ) . ' (' . $this->field_id . ") " . ' SELECT ' . $this->field_id . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_name . "='" . $this->db->escape_string ( $username ) . "'";
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
		$cookie_name = substr ( md5 ( $this->db_sitehash ), 0, 5 ) . '_winduser';
		if (empty ( $username )) {
			$time = TIMENOW - 3600;
			setcookie ( $cookie_name, '', $time, $this->cookie_path, $this->cookie_domain );
		} else {
			if ($this->charset != 'UTF8') {
				$username = skyuc_iconv ( 'UTF8', $this->charset, $username );
			}

			$sql = 'SELECT ' . $this->field_id . ' AS user_id, ' . $this->field_pass . ' As password,' . $this->field_safecv . ' AS safecv' . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_name . "='" . $this->db->escape_string ( $username ) . "'";

			$row = $this->db->query_first ( $sql );

			$cookie_name = substr ( md5 ( $this->db_sitehash ), 0, 5 ) . '_winduser';
			$salt = md5 ( $_SERVER ['HTTP_USER_AGENT'] . $row ['password'] . $this->db_hash );

			$auto_login_key = $this->code_string ( $row ['user_id'] . "\t" . $salt . "\t" . $row ['safecv'], 'ENCODE' );

			setcookie ( $cookie_name, $auto_login_key, TIMENOW + 86400 * 7, $this->cookie_path, $this->cookie_domain );
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
		$cookie_name = substr ( md5 ( $this->db_sitehash ), 0, 5 ) . '_winduser';

		if (! isset ( $_COOKIE [$cookie_name] )) {
			return '';
		}

		$arr = addslashes_deep ( explode ( "\t", $this->code_string ( $_COOKIE [$cookie_name], 'DECODE' ) ) );
		if (count ( $arr ) != 3) {
			return false;
		}
		list ( $user_id, $salt_probe ) = $arr;

		$sql = 'SELECT ' . $this->field_id . ' AS user_id, ' . $this->field_name . ' As user_name, ' . $this->field_pass . ' AS password ' . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_id . " = '$user_id'";
		$row = $this->db->query_first ( $sql );

		if (! $row) {
			return '';
		}

		$salt = md5 ( $_SERVER ['HTTP_USER_AGENT'] . $row ['password'] . $this->db_hash );

		if ($salt != $salt_probe) {
			return '';
		}

		if ($this->charset != 'UTF8') {
			$row ['user_name'] = skyuc_iconv ( $this->charset, 'UTF8', $row ['user_name'] );
		}

		return $row ['user_name'];

	}

	/* 加密解密函数，自动登录密钥也是用该函数进行加密解密 */
	public function code_string($string, $action = 'ENCODE') {
		$key = substr ( md5 ( $_SERVER ['HTTP_USER_AGENT'] . $this->db_hash ), 8, 18 );
		$string = $action == 'ENCODE' ? $string : base64_decode ( $string );
		$keylen = strlen ( $key );
		$strlen = strlen ( $string );
		$code = '';
		for($i = 0; $i < $strlen; $i ++) {
			$k = $i % $keylen;
			$code .= $string [$i] ^ $key [$k];
		}

		$code = $action == 'DECODE' ? $code : base64_encode ( $code );

		return $code;
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
		static $ava_credits = NULL;
		if ($ava_credits === NULL) {

			$sql = 'SELECT db_value FROM ' . $this->table ( 'config' ) . " WHERE db_name='db_credits'";
			$row = $this->db->query_first ( $sql );
			$str = $row ['db_value'];
			if (empty ( $str )) {
				$change_arr = array ('credit' => 'db_credit', 'money' => 'db_money', 'rvrc' => 'db_rvrc' );
				foreach ( $change_arr as $key => $name ) {
					$sql = 'SELECT db_value FROM ' . $this->table ( 'config' ) . " WHERE db_name='" . $name . "unit'";
					$row = $this->db->query_first ( $sql );
					$ava_credits [$key] ['unit'] = $row ['db_value'];

					$sql = 'SELECT db_value FROM ' . $this->table ( 'config' ) . " WHERE db_name='" . $name . "name'";
					$row = $this->db->query_first ( $sql );
					$ava_credits [$key] ['title'] = $row ['db_value'];

					if ($this->charset != 'UTF8') {
						$ava_credits [$key] ['unit'] = skyuc_iconv ( $this->charset, 'UTF8', $ava_credits [$key] ['unit'] );
						$ava_credits [$key] ['title'] = skyuc_iconv ( $this->charset, 'UTF8', $ava_credits [$key] ['title'] );
					}
				}
			} else {
				list ( $ava_credits ['money'] ['title'], $ava_credits ['money'] ['unit'], $ava_credits ['rvrc'] ['title'], $ava_credits ['rvrc'] ['unit'], $ava_credits ['credit'] ['title'], $ava_credits ['credit'] ['unit'] ) = explode ( "\t", $str );

			}

		}

		return $ava_credits;
	}

	/**
	 * 获取用户积分
	 *
	 * @access  public
	 * @param
	 *
	 * @return array
	 */
	public function get_points($username) {
		$credits = $this->get_points_name ();
		$fileds = array_keys ( $credits );
		if ($fileds) {
			if ($this->charset != 'UTF8') {
				$username = skyuc_iconv ( 'UTF8', $this->charset, $username );
			}
			$sql = 'SELECT ud.' . $this->field_id . ', ' . implode ( ', ', $fileds ) . ' FROM ' . $this->table ( 'memberdata' ) . ' AS ud, ' . $this->table ( $this->user_table ) . ' AS u ' . ' WHERE u.' . $this->field_id . '= ud.' . $this->field_id . ' AND u.' . $this->field_name . "='" . $this->db->escape_string ( $username ) . "'";
			$row = $this->db->query_first ( $sql );
			if (isset ( $row ['rvrc'] )) {
				$row ['rvrc'] = floor ( $row ['rvrc'] / 10 );
			}
			return $row;
		} else {
			return false;
		}
	}

	/**
	 * 积分设置
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	public function set_points($username, $credits) {
		If ($this->charset != 'UTF8') {
			$username = skyuc_iconv ( 'UTF8', $this->charset, $username );
		}

		if (isset ( $credits ['rvrc'] )) {
			$credits ['rvrc'] = $credits ['rvrc'] * 10;
		}

		$sql = 'SELECT ' . $this->field_id . ' FROM ' . $this->table ( $this->user_table ) . ' WHERE ' . $this->field_name . "='" . $this->db->escape_string ( $username ) . "'";
		$row = $this->db->query_first ( $sql );
		$uid = $row ["$this->field_id"];

		$user_set = array_keys ( $credits );
		$points_set = array_keys ( $this->get_points_name () );

		$set = array_intersect ( $user_set, $points_set );

		if ($set) {
			$tmp = array ();
			foreach ( $set as $credit ) {
				$tmp [] = $credit . '=' . $credit . '+' . $credits [$credit];
			}
			$sql = 'UPDATE ' . $this->table ( 'memberdata' ) . ' SET ' . implode ( ', ', $tmp ) . ' WHERE ' . $this->field_id . ' = ' . $uid;
			$this->db->query_write ( $sql );
		}

		return true;
	}

}

?>