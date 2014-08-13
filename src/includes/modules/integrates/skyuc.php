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

// 模块的基本信息
if (isset ( $set_modules ) && $set_modules == TRUE) {
	$i = (isset ( $modules )) ? count ( $modules ) : 0;

	// 会员数据整合插件的代码必须和文件名保持一致
	$modules [$i] ['code'] = 'skyuc';

	// 被整合的第三方程序的名称
	$modules [$i] ['name'] = 'SKYUC!';

	// 被整合的第三方程序的版本
	$modules [$i] ['version'] = '3.x';

	// 插件的作者
	$modules [$i] ['author'] = 'SKYUC! R&D TEAM';

	// 插件作者的官方网站
	$modules [$i] ['website'] = 'http://www.skyuc.com';

	return;
}

require_once (DIR . '/includes/modules/integrates/integrate.php');
class skyuc extends integrate {
	public $is_skyuc = 1;

	/**
	 *
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

	}

	/**
	 * 检查指定用户是否存在及密码是否正确(重载基类check_user函数，支持zc加密方法)
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
			$sql = 'SELECT user_id, password, salt' . ' FROM ' . $this->table ( $this->user_table ) . " WHERE user_name='" . $this->db->escape_string ( $post_username ) . "'";
			$row = $this->db->query_first ( $sql );

			if (empty ( $row )) {
				return 0;
			}

			//验证是否已登陆
			$user_id = $row ['user_id'];
			$sql = 'SELECT COUNT(*) AS total FROM ' . $this->table ( 'session' ) . " WHERE userid ='$user_id' AND host <>'" . ALT_IP . "' ";
			$total = $this->db->query_first ( $sql );

			if (empty ( $row ['salt'] )) {
				if ($row ['password'] != $this->compile_password ( array ('password' => $password ) )) {
					return 0;
				} elseif ($total ['total'] > 2) {
					return - 1;
				} else {
					return $row ['user_id'];
				}
			} else {
				// 如果salt存在，使用salt方式加密验证，验证通过洗白用户密码
				$encrypt_type = substr ( $row ['salt'], 0, 1 );
				$encrypt_salt = substr ( $row ['salt'], 1 );

				// 计算加密后密码
				$encrypt_password = '';
				switch ($encrypt_type) {
					case ENCRYPT_ZC :
						$encrypt_password = compile_password ( $encrypt_salt . $password );
						break;
					case ENCRYPT_UC :
						$encrypt_password = md5 ( md5 ( $password ) . $encrypt_salt );
						break;
					case ENCRYPT_PE :
						$encrypt_password = compile_password ( $encrypt_salt . $password, 16 );
						break;
					// 如果还有其他加密方式添加到这里
					default :
						$encrypt_password = '';

				}

				if ($row ['password'] != $encrypt_password) {
					return 0;
				} elseif (! empty ( $num )) {
					return - 1;
				}

				$sql = 'UPDATE ' . $this->table ( $this->user_table ) . " SET password = '" . $this->compile_password ( array ('password' => $password ) ) . "', salt=''" . ' WHERE user_id = ' . $row ['user_id'];
				$this->db->query_write ( $sql );

				return $row ['user_id'];
			}

		}
	}

}

?>