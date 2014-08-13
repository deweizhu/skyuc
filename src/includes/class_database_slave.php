<?php
/**
 * SKYUC 从数据库操作类
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

if (! class_exists ( 'Database' )) {
	exit ();
}

/**
 * 类用于处理主从数据库互动。从数据库用于一些读取操作，主数据库用于所有写操作和时间敏感的读操作。
 *
 */
class Database_Slave extends Database {
	/**
	 * 连接到指定数据库 服务器
	 *
	 * @param	string	数据库名称， 使用 select_db() 时用到
	 * @param	string	主 (写) 数据库服务器名称 - 应该是 'localhost' 或一个 IP 地址
	 * @param	integer	主服务器端口
	 * @param	string	连接到主服务器的用户名
	 * @param	string	主服务器上用户名的相关密码
	 * @param	boolean	是否使用持久连接到主服务器
	 * @param	string	(可选) 从(读)数据库服务器名称 - 应该是留空 或者设置 'localhost' 或 一个 IP 地址, 但是不能和主服务器名称相同。
	 * @param	integer	(可选) 从服务器端口
	 * @param	string	(可选) 连接到从服务器的用户名
	 * @param	string	(可选) 从服务器上用户名的相关密码
	 * @param	boolean	(可选) 是否使用持久连接到从服务器
	 * @param	string	(可选) 解析 MySQL 配置文件的设置选项
	 * @param	string	(可选) 连接字符 仅 MySQLi / PHP 5.1.0+ or 5.0.5+ / MySQL 4.1.13+ or MySQL 5.1.10+
	 *
	 * @return	none
	 */
	function connect($database, $w_servername, $w_port, $w_username, $w_password, $w_usepconnect = false, $r_servername = '', $r_port = 3306, $r_username = '', $r_password = '', $r_usepconnect = false, $configfile = '', $charset = '') {
		$this->database = $database;

		$w_port = $w_port ? $w_port : 3306;
		$r_port = $r_port ? $r_port : 3306;

		$this->connection_master = $this->db_connect ( $w_servername, $w_port, $w_username, $w_password, $w_usepconnect, $configfile, $charset );
		$this->multiserver = true;

		// 禁用错误并尝试连接到从服务器
		$this->reporterror = false;
		$this->connection_slave = $this->db_connect ( $r_servername, $r_port, $r_username, $r_password, $r_usepconnect, $configfile, $charset );
		$this->reporterror = true;

		if ($this->connection_slave === false) {
			$this->connection_slave = & $this->connection_master;
		}

		if ($this->connection_master) { // 当我们选择主服务器时从服务器将会自动选择
			$this->select_db ( $this->database );
		}
	}

	/**
	 * 选择要使用的数据库
	 *
	 * @param	string	位于数据库服务器上的数据库的名称
	 *
	 * @return	boolean
	 */
	function select_db($database = '') {
		$check_write = parent::select_db ( $database );
		$check_read = @$this->select_db_wrapper ( $this->database, $this->connection_slave );
		$this->connection_recent = & $this->connection_slave;

		return ($check_write and $check_read);
	}

	/**
	 * 通过'从'数据库连接执行一个数据读取SQL查询
	 *
	 * @param	string	将被执行SQL查询文本
	 * @param	boolean	是否要运行此查询缓冲 (true) 或不缓冲 (false)，默认是缓冲。
	 *
	 * @return	string
	 */
	function query_read_slave($sql, $buffered = true) {
		$this->sql = & $sql;
		return $this->execute_query ( $buffered, $this->connection_slave );
	}

	/**
	 * 执行一条数据读取SQL查询, 然后返回结果集的数据第一行数组
	 *
	 * @param	string	将被执行SQL查询文本
	 * @param	string	(NUM, ASSOC, BOTH)选其一
	 *
	 * @return	array
	 */
	function &query_first_slave($sql, $type = DBARRAY_ASSOC) {
		$this->sql = & $sql;
		$queryresult = $this->execute_query ( true, $this->connection_slave );
		$returnarray = $this->fetch_array ( $queryresult, $type );
		$this->free_result ( $queryresult );
		return $returnarray;
	}

	/**
	 * 执行一条数据读取SQL查询, 然后返回结果集的数据所有行数组
	 *
	 * @param	string	将被执行SQL查询文本
	 * @param	string	(NUM, ASSOC, BOTH)选其一
	 *
	 * @return	array
	 */
	function &query_all_slave($sql, $type = DBARRAY_ASSOC) {
		$this->sql = & $sql;
		$queryresult = $this->execute_query ( true, $this->connection_slave );
		$returnarray = array ();
		while ( $row = $this->fetch_array ( $queryresult, $type ) ) {
			$returnarray [] = $row;
		}
		$this->free_result ( $queryresult );
		return $returnarray;
	}

	/**
	 * 关闭到这两个读取的数据库服务器连接
	 *
	 * @return	integer
	 */
	function close() {
		$parent = parent::close ();
		return ($parent and @$this->functions ['close'] ( $this->connection_slave ));
	}
}

/**
 * 类用于处理主从数据库互动。从数据库用于一些读取操作，主数据库用于所有写操作和时间敏感的读操作。(MySQLi)
 *
 */
class Database_Slave_MySQLi extends Database_MySQLi {
	/**
	 * 连接到指定的数据库服务器
	 *
	 * @param	string	数据库名称， 使用 select_db() 时用到
	 * @param	string	主 (写) 数据库服务器名称 - 应该是 'localhost' 或一个 IP 地址
	 * @param	integer	主服务器端口
	 * @param	string	连接到主服务器的用户名
	 * @param	string	主服务器上用户名的相关密码
	 * @param	boolean	是否使用持久连接到主服务器
	 * @param	string	(可选) 从(读)数据库服务器名称 - 应该是留空 或者设置 'localhost' 或 一个 IP 地址, 但是不能和主服务器名称相同。
	 * @param	integer	(可选) 从服务器端口
	 * @param	string	(可选) 连接到从服务器的用户名
	 * @param	string	(可选) 从服务器上用户名的相关密码
	 * @param	boolean	(可选) 是否使用持久连接到从服务器
	 * @param	string	(可选) 解析 MySQL 配置文件的设置选项
	 * @param	string	(可选) 连接字符 仅 MySQLi / PHP 5.1.0+ or 5.0.5+ / MySQL 4.1.13+ or MySQL 5.1.10+
	 *
	 * @return	none
	 */
	function connect($database, $w_servername, $w_port, $w_username, $w_password, $w_usepconnect = false, $r_servername = '', $r_port = 3306, $r_username = '', $r_password = '', $r_usepconnect = false, $configfile = '', $charset = '') {
		$this->database = $database;

		$w_port = $w_port ? $w_port : 3306;
		$r_port = $r_port ? $r_port : 3306;

		$this->connection_master = $this->db_connect ( $w_servername, $w_port, $w_username, $w_password, $w_usepconnect, $configfile, $charset );
		$this->multiserver = true;

		// 禁用错误并尝试连接到从服务器
		$this->reporterror = false;
		$this->connection_slave = $this->db_connect ( $r_servername, $r_port, $r_username, $r_password, $r_usepconnect, $configfile, $charset );
		$this->reporterror = true;

		if ($this->connection_slave === false) {
			$this->connection_slave = & $this->connection_master;
		}

		if ($this->connection_master) { // 当我们选择主服务器时从服务器将会自动选择
			$this->select_db ( $this->database );
		}
	}

	/**
	 * 选择要使用的数据库
	 *
	 * @param	string	位于数据库服务器上的数据库的名称
	 *
	 * @return	boolean
	 */
	function select_db($database = '') {
		$check_write = parent::select_db ( $database );
		$check_read = @$this->select_db_wrapper ( $this->database, $this->connection_slave );
		$this->connection_recent = & $this->connection_slave;

		return ($check_write and $check_read);
	}

	/**
	 * 通过'从'数据库连接执行一个数据读取SQL查询
	 *
	 * @param	string	将被执行SQL查询文本
	 * @param	boolean	是否要运行此查询缓冲 (true) 或不缓冲 (false)，默认是缓冲。
	 *
	 * @return	string
	 */
	function query_read_slave($sql, $buffered = true) {
		$this->sql = & $sql;
		return $this->execute_query ( $buffered, $this->connection_slave );
	}

	/**
	 * 执行一条数据读取SQL查询, 然后返回结果集的数据第一行数组
	 *
	 * @param	string	将被执行SQL查询文本
	 * @param	string	(NUM, ASSOC, BOTH)选其一
	 *
	 * @return	array
	 */
	function &query_first_slave($sql, $type = DBARRAY_ASSOC) {
		$this->sql = & $sql;
		$queryresult = $this->execute_query ( true, $this->connection_slave );
		$returnarray = $this->fetch_array ( $queryresult, $type );
		$this->free_result ( $queryresult );
		return $returnarray;
	}

	/**
	 * 执行一条数据读取SQL查询, 然后返回结果集的数据所有行数组
	 *
	 * @param	string	将被执行SQL查询文本
	 * @param	string	(NUM, ASSOC, BOTH)选其一
	 *
	 * @return	array
	 */
	function &query_all_slave($sql, $type = DBARRAY_ASSOC) {
		$this->sql = & $sql;
		$queryresult = $this->execute_query ( true, $this->connection_slave );
		$returnarray = array ();
		while ( $row = $this->fetch_array ( $queryresult, $type ) ) {
			$returnarray [] = $row;
		}
		$this->free_result ( $queryresult );
		return $returnarray;
	}
	/**
	 * 关闭到这两个读取的数据库服务器连接
	 *
	 * @return	integer
	 */
	function close() {
		$parent = parent::close ();
		return ($parent and @$this->functions ['close'] ( $this->connection_slave ));
	}
}

?>
