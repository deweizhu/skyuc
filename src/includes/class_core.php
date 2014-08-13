<?php
/**
 * SKYUC 数据库基类
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */
if (! defined ( 'SKYUC_AREA' ) and ! defined ( 'THIS_SCRIPT' )) {
	echo 'SKYUC_AREA and THIS_SCRIPT must be defined to continue';
	exit ();
}

define ('APPNAME', 'SKYUC');
define ('VERSION', '3.4.2');
define ('RELEASE', '20121001');

/* 插件相关常数 */
define ( 'ERR_COPYFILE_FAILED', 1 );
define ( 'ERR_CREATETABLE_FAILED', 2 );
define ( 'ERR_DELETEFILE_FAILED', 3 );

/* 会员整合相关常数 */
define ( 'ERR_USERNAME_EXISTS', 1 ); // 用户名已经存在
define ( 'ERR_EMAIL_EXISTS', 2 ); // Email已经存在
define ( 'ERR_INVALID_USERID', 3 ); // 无效的user_id
define ( 'ERR_INVALID_USERNAME', 4 ); // 无效的用户名
define ( 'ERR_INVALID_PASSWORD', 5 ); // 密码错误
define ( 'ERR_INVALID_EMAIL', 6 ); // email错误
define ( 'ERR_USERNAME_NOT_ALLOW', 7 ); // 用户名不允许注册
define ( 'ERR_EMAIL_NOT_ALLOW', 8 ); // EMAIL不允许注册


/* 订单状态 */
define ( 'OS_UNCONFIRMED', 0 ); // 未确认
define ( 'OS_CONFIRMED', 1 ); // 已确认
define ( 'OS_CANCELED', 2 ); // 已取消
define ( 'OS_INVALID', 3 ); // 无效
define ( 'OS_RETURNED', 4 ); // 退货


/* 支付类型 */
define ( 'PAY_ORDER', 0 ); // 订单支付
define ( 'PAY_SURPLUS', 1 ); // 会员预付款


/* 支付状态 */
define ( 'PS_UNPAYED', 0 ); // 未付款
define ( 'PS_PAYING', 1 ); // 付款中
define ( 'PS_PAYED', 2 ); // 已付款


/* 帐户明细类型 */
define ( 'SURPLUS_SAVE', 0 ); // 为帐户冲值
define ( 'SURPLUS_RETURN', 1 ); // 从帐户提款


/* 评论状态 */
define ( 'COMMENT_UNCHECKED', 0 ); // 未审核
define ( 'COMMENT_CHECKED', 1 ); // 已审核或已回复(允许显示)
define ( 'COMMENT_REPLYED', 2 ); // 该评论的内容属于回复


/* 广告的类型 */
define ( 'IMG_AD', 0 ); // 图片广告
define ( 'FALSH_AD', 1 ); // flash广告
define ( 'CODE_AD', 2 ); // 代码广告
define ( 'TEXT_AD', 3 ); // 文字广告


/* 属性值的录入方式 */
define ( 'ATTR_INPUT', 0 ); // 录入
define ( 'ATTR_SELECT', 1 ); // 选择


/* 验证码 */
define ( 'HV_REGISTER', 1 ); //注册时使用验证码
define ( 'HV_LOGIN', 2 ); //登录时使用验证码
define ( 'HV_COMMENT', 4 ); //评论时使用验证码
define ( 'HV_ADMIN', 8 ); //后台登录时使用验证码
define ( 'HV_MESSAGE', 16 ); //留言时使用验证码


/* 用户中心留言类型 */
define ( 'M_MESSAGE', 0 ); // 留言
define ( 'M_COMPLAINT', 1 ); // 咨询
define ( 'M_ENQUIRY', 2 ); // 报错
define ( 'M_CUSTOME', 3 ); // 求片


/* 文章分类类型 */
define ( 'COMMON_CAT', 1 ); //普通分类
define ( 'SYSTEM_CAT', 2 ); //系统默认分类
define ( 'INFO_CAT', 3 ); //网站信息分类
define ( 'UPHELP_CAT', 4 ); //网站帮助分类分类
define ( 'HELP_CAT', 5 ); //网站帮助分类


/* 帐号变动类型 */
define ( 'ACT_SAVING', 0 ); // 帐户冲值
define ( 'ACT_DRAWING', 1 ); // 帐户提款
define ( 'ACT_ADJUSTING', 2 ); // 调节帐户
define ( 'ACT_OTHER', 99 ); // 其他类型


/* 密码加密方法 */
define ( 'PWD_MD5', 1 ); //md5加密方式
define ( 'PWD_PRE_SALT', 2 ); //前置验证串的加密方式
define ( 'PWD_SUF_SALT', 3 ); //后置验证串的加密方式


/* 加密方式 */
define ( 'ENCRYPT_ZC', 1 ); //zc(通常MD5)加密方式
define ( 'ENCRYPT_UC', 2 ); //uc加密方式
define ( 'ENCRYPT_PE', 3 ); //前置验证串的加密方式


/* 积分兑换 */
define ( 'TO_P', 0 ); //兑换到网站消费积分
define ( 'FROM_P', 1 ); //用网站消费积分兑换
define ( 'TO_R', 2 ); //兑换到网站等级积分
define ( 'FROM_R', 3 ); //用网站等级积分兑换


/* 支付宝商家账户 */
define ( 'ALIPAY_AUTH', '' );
define ( 'ALIPAY_ID', '' );

/* 添加feed事件到UC的TYPE*/
define ( 'PLAY_SHOW', 1 ); //点播影片
define ( 'COMMENT_SHOW', 2 ); //添加影片评论


define ( 'ROOT_PATH', substr ( dirname ( __FILE__ ), 0, - 8 ) );

/**#@+
 * 内联适度 javascript 选择器 应容易理解的位域值
 */
define ( 'POST_FLAG_INVISIBLE', 1 );
define ( 'POST_FLAG_DELETED', 2 );
define ( 'POST_FLAG_ATTACH', 4 );
define ( 'POST_FLAG_GUEST', 8 );
/**#@-*/

// #############################################################################
// MySQL 数据库类


/**#@+
 * 从数据库中的特定行返回的结果集类型。
 */
define ( 'DBARRAY_BOTH', 0 ); //二者兼有
define ( 'DBARRAY_ASSOC', 1 ); //关联数组
define ( 'DBARRAY_NUM', 2 ); //数字数组
/**#@-*/

/**
 * 数据库类接口
 *
 * 这个类也处理主从服务器之间的数据复制。
 *
 */
class Database {
	/**
	 * 数组函数名，一个简单的名称映射到RDBMS（关系型数据库管理系统）的具体功能名称
	 *
	 * @protected	array
	 */
	protected $functions = array ('connect' => 'mysql_connect', 'pconnect' => 'mysql_pconnect', 'select_db' => 'mysql_select_db', 'query' => 'mysql_query', 'query_unbuffered' => 'mysql_unbuffered_query', 'fetch_row' => 'mysql_fetch_row', 'fetch_array' => 'mysql_fetch_array', 'fetch_field' => 'mysql_fetch_field', 'free_result' => 'mysql_free_result', 'data_seek' => 'mysql_data_seek', 'error' => 'mysql_error', 'errno' => 'mysql_errno', 'affected_rows' => 'mysql_affected_rows', 'num_rows' => 'mysql_num_rows', 'num_fields' => 'mysql_num_fields', 'field_name' => 'mysql_field_name', 'insert_id' => 'mysql_insert_id', 'escape_string' => 'mysql_escape_string', 'real_escape_string' => 'mysql_real_escape_string', 'close' => 'mysql_close', 'client_encoding' => 'mysql_client_encoding', 'get_server_info' => 'mysql_get_server_info' );

	/**
	 * 数组常量用于fetch_array
	 *
	 * @private	array
	 */
	protected $fetchtypes = array (DBARRAY_NUM => MYSQL_NUM, DBARRAY_ASSOC => MYSQL_ASSOC, DBARRAY_BOTH => MYSQL_BOTH );

	/**
	 * 注册表对象
	 *
	 * @public	Registry
	 */
	public $registry = null;

	/**
	 * 系统全名
	 *
	 * @var	string
	 */
	public $appname = 'SKYUC';

	/**
	 * 系统简称
	 *
	 * @public	string
	 */
	public $appshortname = 'SKYUC';

	/**
	 * 数据库名称
	 *
	 * @public	string
	 */
	public $database = null;

	/**
	 * 链接变量，连接到主/写服务器。
	 *
	 * @public	string
	 */
	public $connection_master = null;

	/**
	 * 链接变量，连接到从/读服务器。
	 *
	 * @public	string
	 */
	public $connection_slave = null;

	/**
	 * 链接变量，最后使用的链接。
	 *
	 * @public	string
	 */
	public $connection_recent = null;

	/**
	 * 是否我们将使用不同的连接，读取和写入查询
	 *
	 * @public	boolean
	 */
	public $multiserver = false;

	/**
	 * 当脚本关闭时要执行的查询语句数组
	 *
	 * @var	array
	 */
	public $shutdownqueries = array ();

	/**
	 * 最近的 SQL 查询语句
	 *
	 * @public	string
	 */
	public $sql = '';

	/**
	 * 数据库错误时，是否显示并挂起
	 *
	 * @public	boolean
	 */
	public $reporterror = true;

	/**
	 * 最近数据库错误信息
	 *
	 * @public	string
	 */
	public $error = '';

	/**
	 * MYSQL版本
	 *
	 * @public	string
	 */
	public $version = '';

	/**
	 * 数据库错误信息数字代码
	 *
	 * @public	integer
	 */
	public $errno = '';

	/**
	 * SQL查询包大小
	 *
	 * @public	integer	主服务器 SQL 查询字符串最大包大小
	 */
	public $maxpacket = 0;

	/**
	 * 跟踪表的锁定状态，确定如果一个表锁定命令已发出
	 *
	 * @public	bool
	 */
	public $locked = false;

	/**
	 * 查询次数
	 *
	 * @public	integer	系统运行SQL查询数量
	 */
	public $queryCount = 0;
	public $queryTime = ''; //查询时间


	/**
	 * 构造函数， 如果 x_real_escape_string() 是可用的, 开启覆盖函数 x_escape_string()。
	 *
	 *
	 * @param	Registry object
	 */
	public function __construct(&$registry) {
		if (is_object ( $registry )) {
			$this->registry = & $registry;
		} else {
			trigger_error ( "Database::Registry object is not an object", E_USER_ERROR );
		}
	}

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
	 * @param	string	(可选) 连接字符编码  仅  MySQL / PHP 5.1.0+ or 5.0.5+ / MySQL 4.1.13+ or MySQL 5.1.10+
	 *
	 * @return	none
	 */
	public function connect($database, $w_servername, $w_port, $w_username, $w_password, $w_usepconnect = false, $r_servername = '', $r_port = 3306, $r_username = '', $r_password = '', $r_usepconnect = false, $configfile = '', $charset = '') {
		$this->database = $database;

		$w_port = $w_port ? $w_port : 3306;
		$r_port = $r_port ? $r_port : 3306;

		$this->connection_master = $this->db_connect ( $w_servername, $w_port, $w_username, $w_password, $w_usepconnect, $configfile, $charset );
		$this->multiserver = false;
		$this->connection_slave = & $this->connection_master;

		if ($this->connection_master) {
			$this->select_db ( $this->database );
		}
	}

	/**
	 * 初始化数据库链接
	 *
	 * 连接到主数据库服务器，如果指定了从服务器，也连接到从数据库服务器。
	 *
	 * @param	string	数据库名称 - 应该是 'localhost' 或一个 IP 地址
	 * @param	integer	数据库服务器端口 (通常 3306)
	 * @param	string	连接到数据库服务器的用户名
	 * @param	string	数据库服务器上用户名的密码
	 * @param	boolean	是否使用持久连接到服务器
	 * @param	string  不适用。仅 MySQLi 的配置文件。
	 * @param	string  强制连接字符编码设置 (防止检验错误)
	 *
	 * @return	boolean
	 */
	public function db_connect($servername, $port, $username, $password, $usepconnect, $configfile = '', $charset = '') {
		if (function_exists ( 'catch_db_error' )) {
			set_error_handler ( 'catch_db_error' );
		}

		// catch_db_error 将执行退出, 这里没有无限循环
		do {
			$link = $this->functions [$usepconnect ? 'pconnect' : 'connect'] ( "$servername:$port", $username, $password );
		} while ( $link == false and $this->reporterror );

		restore_error_handler ();

		$this->starttime = $_SERVER ['REQUEST_TIME'];

		if (! empty ( $charset )) {
			if (function_exists ( 'mysql_set_charset' )) {
				mysql_set_charset ( $charset );
			} else {
				$this->sql = "SET NAMES $charset";
				$this->execute_query ( true, $link );
			}
		}

		return $link;
	}
	/**
	 * 选择一个数据库使用
	 *
	 * @param	string	位于数据库服务器上的数据库名称
	 *
	 * @return	boolean
	 */
	protected function select_db($database = '') {
		if ($database != '') {
			$this->database = $database;
		}

		if ($check_write = @$this->select_db_wrapper ( $this->database, $this->connection_master )) {
			$this->connection_recent = & $this->connection_master;
			return true;
		} else {
			$this->connection_recent = & $this->connection_master;
			if (! file_exists ( DIR . '/install/index.php' )) {
				$this->halt ( 'Cannot use database ' . $this->database );
			}
			return false;
		}
	}

	/**
	 * 简单封装 select_db(), 允许参数命令改变
	 *
	 * @param	string	数据库名称
	 * @param	integer	链接标识符
	 *
	 * @return	boolean
	 */
	protected function select_db_wrapper($database = '', $link = null) {
		return $this->functions ['select_db'] ( $database, $link );
	}

	/**
	 * 应用于 MySQL 4.1+ 强制 sql_mode 变量 指定一个模式，某些模式可能
	 * 不适合 SKYUC。
	 *
	 * @param	string	设置 sql_mode 模式变量
	 */
	public function force_sql_mode($mode) {
		$reset_errors = $this->reporterror;
		if ($reset_errors) {
			$this->hide_errors ();
		}

		$this->query_write ( "SET @@sql_mode = '" . $this->escape_string ( $mode ) . "'" );

		if ($reset_errors) {
			$this->show_errors ();
		}
	}

	/**
	 * 通过指定链接执行一个SQL查询
	 *
	 * @param	boolean	是否要运行此查询缓冲 (true) 或不缓冲 (false)， 默认是不缓冲。
	 * @param	string	连接服务器 ID
	 *
	 * @return	string
	 */
	protected function &execute_query($buffered = true, &$link) {
		$this->connection_recent = & $link;
		$this->queryCount ++;

		// 查询时间
		if ($this->queryTime == '') {
			$this->queryTime = microtime ( true );
		}

		if ($queryresult = $this->functions [$buffered ? 'query' : 'query_unbuffered'] ( $this->sql, $link )) {
			// 注销 $sql 降低内存 .. 这不是一个错误，因此不是必须的。
			$this->sql = '';

			return $queryresult;
		} else {
			$this->halt ();

			// 注销 $sql 降低内存 .. 错误已经抛出
			$this->sql = '';
		}
	}
	/**
	 * 通过'主'数据库连接执行一个数据写入SQL查询
	 * 执行 INSERT、 REPLACE、 UPDATE、 DROP、 ALTER 和其他数据修改查询。
	 *
	 * @param	string	将被执行SQL查询文本
	 * @param	boolean	是否要运行此查询缓冲 (true) 或不缓冲 (false)，默认是缓冲。
	 *
	 * @return	string
	 */
	public function query_write($sql, $buffered = true) {
		$this->sql = & $sql;
		return $this->execute_query ( $buffered, $this->connection_master );
	}

	/**
	 * 通过'主'数据库连接执行一个数据读取SQL查询
	 * 我们无法知道的'读'数据库是否最新的，因此慎重考虑
	 * 执行 SELECT 和 SHOW 操作。
	 *
	 * @param	string	将被执行SQL查询文本
	 * @param	boolean	是否要运行此查询缓冲 (true) 或不缓冲 (false)，默认是缓冲。
	 *
	 * @return	string
	 */
	public function query_read($sql, $buffered = true) {
		$this->sql = & $sql;
		return $this->execute_query ( $buffered, $this->connection_master );
	}

	/**
	 * 通过'从'数据库连接执行一个数据读取SQL查询
	 *
	 * @param	string	将被执行SQL查询文本
	 * @param	boolean	是否要运行此查询缓冲 (true) 或不缓冲 (false)，默认是缓冲。
	 *
	 * @return	string
	 */
	public function query_read_slave($sql, $buffered = true) {
		$this->sql = & $sql;
		return $this->execute_query ( $buffered, $this->connection_master );
	}

	/**
	 * 使用写连接，执行一条SQL查询语句。
	 *
	 *
	 * @param	string	将被执行SQL查询文本
	 * @param	boolean	是否要运行此查询缓冲 (true) 或不缓冲 (false)，默认是不缓冲。
	 *
	 * @return	string
	 */
	public function query($sql, $buffered = true) {
		$this->sql = & $sql;
		return $this->execute_query ( $buffered, $this->connection_master );
	}

	/**
	 * 建立限制(LIMIT)查询，在这里做一些验证。
	 *
	 *
	 * @param	string	将被执行SQL查询文本
	 * @param	integer	总数
	 * @param	integer	起始数（偏移量）
	 *
	 * @return	string
	 */
	public function query_limit($sql, $total, $offset = 0) {
		if (empty ( $sql )) {
			return '';
		}
		//永远不会使用负数偏移量
		$total = ($total < 0) ? 0 : $total;
		$offset = ($offset < 0) ? 0 : $offset;

		return $this->_query_limit ( $sql, $total, $offset );
	}

	/**
	 * 建立限制(LIMIT)查询
	 *
	 * @param	string	将被执行SQL查询文本
	 * @param	integer	总数
	 * @param	integer	起始数（偏移量）
	 */
	function _query_limit($sql, $total, $offset = 0) {
		// 如果我们不希望限制行数，将 $total 设为 0
		if ($total == 0) {
			// 值为-1 一直是一个错误
			$total = '18446744073709551615';
		}

		$sql .= "\n LIMIT " . ((! empty ( $offset )) ? $offset . ', ' . $total : $total);
		$this->sql = & $sql;

		return $sql;
	}
	/**
	 * 执行一条数据读取SQL查询, 然后返回结果集的数据第一行数组
	 *
	 * @param	string	将被执行SQL查询文本
	 * @param	string	 (NUM, ASSOC, BOTH)选其一
	 *
	 * @return	array
	 */
	public function &query_first($sql, $type = DBARRAY_ASSOC) {
		$this->sql = & $sql;
		$queryresult = $this->execute_query ( true, $this->connection_master );
		$returnarray = $this->fetch_array ( $queryresult, $type );
		$this->free_result ( $queryresult );
		return $returnarray;
	}

	/**
	 * 执行一条数据读取SQL查询, 然后返回结果集的数据所有行数组
	 *
	 * @param	string	将被执行SQL查询文本
	 * @param	string	 (NUM, ASSOC, BOTH)选其一
	 *
	 * @return	array
	 */
	public function &query_all($sql, $type = DBARRAY_ASSOC) {
		$this->sql = & $sql;
		$queryresult = $this->execute_query ( true, $this->connection_master );
		$returnarray = array ();

		while ( $row = $this->fetch_array ( $queryresult, $type ) ) {
			$returnarray [] = $row;
		}
		$this->free_result ( $queryresult );
		return $returnarray;
	}

	/**
	 * 执行一个 FOUND_ROWS 查询 来获取 SQL_CALC_FOUND_ROWS 结果
	 *
	 * @return	integer
	 */
	public function found_rows() {
		$this->sql = "SELECT FOUND_ROWS()";
		$queryresult = $this->execute_query ( true, $this->connection_recent );
		$returnarray = $this->fetch_array ( $queryresult, DBARRAY_NUM );
		$this->free_result ( $queryresult );

		return intval ( $returnarray [0] );
	}

	/**
	 * 执行一个数据读取SQL查询相对于从服务器, 然后返回结果集的数据第一行数组
	 *
	 * @param	string	将被执行SQL查询文本
	 * @param	string	(NUM, ASSOC, BOTH)选其一
	 *
	 * @return	array
	 */
	public function &query_first_slave($sql, $type = DBARRAY_ASSOC) {
		$returnarray = $this->query_first ( $sql, $type );
		return $returnarray;
	}

	/**
	 * 执行一个数据读取SQL查询相对于从服务器, 然后返回结果集的数据第一行数组
	 *
	 * @param	string	将被执行SQL查询文本
	 * @param	string	(NUM, ASSOC, BOTH)选其一
	 *
	 * @return	array
	 */
	public function &query_all_slave($sql, $type = DBARRAY_ASSOC) {
		$returnarray = $this->query_all ( $sql, $type );
		return $returnarray;
	}

	/**
	 * 执行 INSERT INTO 查询, 如果可能使用扩展插入
	 *
	 * @param	string	应插入数据的表名称
	 * @param	string	用逗号分隔的字段名称
	 * @param	array		数组 SQL 值 key => value
	 * @param	boolean	是否要运行此查询缓冲 (true) 或不缓冲 (false)，默认是不缓冲。
	 *
	 * @return	mixed
	 */
	public function &query_insert($table, $fields, &$values, $buffered = true) {
		return $this->insert_multiple ( "INSERT INTO $table $fields VALUES", $values, $buffered );
	}

	/**
	 * 执行一个  REPLACE INTO 查询, 如果可能使用扩展插入
	 *
	 * @param	string	应插入数据的表名称
	 * @param	string	用逗号分隔的字段名称
	 * @param	array		数组 SQL 值 key => value
	 * @param	boolean	是否要运行此查询缓冲 (true) 或不缓冲 (false)，默认是不缓冲。
	 *
	 * @return	mixed
	 */
	public function &query_replace($table, $fields, &$values, $buffered = true) {
		return $this->insert_multiple ( "REPLACE INTO $table $fields VALUES", $values, $buffered );
	}

	/**
	 * 执行一个 INSERT 或 REPLACE 查询多个值,  基于 $this->maxpacket 分割成可管理的大型查询块。
	 *
	 * @param	string	将为执行的SQL查询文本的第一段- 例如 "INSERT INTO table (field1, field2) VALUES"
	 * @param	mixed		插入值. 例如: (0 => "('value1', 'value2')", 1 => "('value3', 'value4')")
	 * @param	boolean	是否要运行此查询缓冲 (true) 或不缓冲 (false)，默认是不缓冲。
	 *
	 * @return	mixed
	 */
	protected function insert_multiple($sql, &$values, $buffered) {
		if ($this->maxpacket == 0) {
			// 必须做 读取 查询，在写链接这里！
			$vars = $this->query_write ( "SHOW VARIABLES LIKE 'max_allowed_packet'" );
			$var = $this->fetch_row ( $vars );
			$this->maxpacket = $var [1];
			$this->free_result ( $vars );
		}

		$i = 0;
		$num_values = count ( $values );
		$this->sql = $sql;

		while ( $i < $num_values ) {
			$sql_length = strlen ( $this->sql );
			$value_length = strlen ( "\r\n" . $values ["$i"] . "," );

			if (($sql_length + $value_length) < $this->maxpacket) {
				$this->sql .= "\r\n" . $values ["$i"] . ",";
				unset ( $values ["$i"] );
				$i ++;
			} else {
				$this->sql = (substr ( $this->sql, - 1 ) == ',') ? substr ( $this->sql, 0, - 1 ) : $this->sql;
				$this->execute_query ( $buffered, $this->connection_master );
				$this->sql = $sql;
			}
		}
		if ($this->sql != $sql) {
			$this->sql = (substr ( $this->sql, - 1 ) == ',') ? substr ( $this->sql, 0, - 1 ) : $this->sql;
			$this->execute_query ( $buffered, $this->connection_master );
		}

		if (count ( $values ) == 1) {
			return $this->insert_id ();
		} else {
			return true;
		}
	}

	/**
	 * 注册一个要执行的 SQL 查询 在 关闭时间。如果关闭函数被禁用, 这个查询立即执行。
	 *
	 * @param	string	将被执行SQL查询文本
	 * @param	mixed	(可选) 允许特别关闭查询 是标签
	 *
	 * @return	boolean
	 */
	public function shutdown_query($sql, $arraykey = -1) {
		if ($arraykey === - 1) {
			$this->shutdownqueries [] = $sql;
			return true;
		} else {
			$this->shutdownqueries ["$arraykey"] = $sql;
			return true;
		}
	}

	/**
	 * 返回的行数包含在查询结果集
	 *
	 * @param	string	我们正在处理的查询结果标识符
	 *
	 * @return	integer
	 */
	public function num_rows($queryresult) {
		return @$this->functions ['num_rows'] ( $queryresult );
	}

	/**
	 * 返回的列数包含在查询结果集
	 *
	 * @param	string	 我们正在处理的查询结果ID
	 *
	 * @return	integer
	 */
	public function num_fields($queryresult) {
		return @$this->functions ['num_fields'] ( $queryresult );
	}

	/**
	 * 返回的字段名称，从一个查询结果集中
	 *
	 * @param	string	我们正在处理的查询结果标识符
	 * @param	integer	该字段的数字偏移量。
	 *
	 * @return	string
	 */
	public function field_name($queryresult, $index) {
		return @$this->functions ['field_name'] ( $queryresult, $index );
	}

	/**
	 * 返回上一步 INSERT 操作产生的 ID
	 *
	 * @return	integer
	 */
	public function insert_id() {
		return @$this->functions ['insert_id'] ( $this->connection_master );
	}

	/**
	 * 返回当前连接的默认字符集名称。(暂时未用)
	 *
	 * @return	string
	 */
	protected function client_encoding() {
		return @$this->functions ['client_encoding'] ( $this->connection_master );
	}

	/**
	 * 关闭数据库连接。
	 *
	 * @return	integer
	 */
	public function close() {
		return @$this->functions ['close'] ( $this->connection_master );
	}

	/**
	 * 转义一个字符串，使其安全地插入一个SQL查询
	 *
	 * @param	string	该字符串被转义
	 *
	 * @return	string
	 */
	public function escape_string($string) {
		if ($this->functions ['escape_string'] == $this->functions ['real_escape_string']) {
			return $this->functions ['escape_string'] ( $string, $this->connection_master );
		} else {
			return $this->functions ['escape_string'] ( $string );
		}
	}
	/**
	 * 使用合适的转义字符，转义一个字符串， 在使用 LIKE 的条件时。
	 *
	 * @param	string	该字符串被转义
	 *
	 * @return	string
	 */
	public function escape_string_like($string) {
		return str_replace ( array ('%', '_' ), array ('\%', '\_' ), $this->escape_string ( $string ) );
	}

	/**
	 * 准备一段数据 并处理它（添加引号等），放进一个 SQL 查询。
	 *
	 * @param	mixed	要处理的数据
	 *
	 * @return	mixed	处理后的数据
	 */
	public function sql_prepare($value) {
		if (is_string ( $value )) {
			return "'" . $this->escape_string ( $value ) . "'";
		} else if (is_numeric ( $value ) and $value + 0 == $value) {
			return $value;
		} else if (is_bool ( $value )) {
			return $value ? 1 : 0;
		} else {
			return "'" . $this->escape_string ( $value ) . "'";
		}
	}

	/**
	 * 从结果集中取得一行，返回一个数组
	 *
	 * $type 值定义 关联数组，或数字数组，或二者兼有
	 *
	 * @param	string	我们正在处理的查询结果标识符
	 * @param	integer	选其一 DBARRAY_ASSOC / DBARRAY_NUM / DBARRAY_BOTH
	 *
	 * @return	array
	 */
	public function fetch_array($queryresult, $type = DBARRAY_ASSOC) {
		return @$this->functions ['fetch_array'] ( $queryresult, $this->fetchtypes ["$type"] );
	}

	/**
	 * 从结果集中取得一行，返回一个数字数组
	 *
	 * @param	string	我们正在处理的查询结果标识符
	 *
	 * @return	array
	 */
	public function fetch_row($queryresult) {
		return @$this->functions ['fetch_row'] ( $queryresult );
	}

	/**
	 * 从结果集中取得列信息并作为对象返回
	 *
	 * @param	string	我们正在处理的查询结果标识符
	 *
	 * @return	array
	 */
	public function fetch_field($queryresult) {
		return @$this->functions ['fetch_field'] ( $queryresult );
	}

	/**
	 * 移动内部结果的指针在一个查询结果集
	 *
	 * @param	string	我们正在处理的查询结果标识符
	 * @param	integer	想要设定的新的结果集指针的行数。  (取值范围应该从 0 到 总数 - 1)
	 *
	 * @return	boolean
	 */
	public function data_seek($queryresult, $index) {
		return @$this->functions ['data_seek'] ( $queryresult, $index );
	}

	/**
	 * 释放所有内存为指定查询结果
	 *
	 * @param	string	我们正在处理的查询结果标识符
	 *
	 * @return	boolean
	 */
	public function free_result($queryresult) {
		$this->sql = '';
		return @$this->functions ['free_result'] ( $queryresult );
	}

	/**
	 * 返回最近 insert/replace/update 查询影响的行数。
	 *
	 * @return	integer
	 */
	public function affected_rows() {
		$this->rows = $this->functions ['affected_rows'] ( $this->connection_recent );
		return $this->rows;
	}
	/**
	 * 锁定表
	 *
	 * @param	mixed		要锁定的表
	 * @param	string	执行的锁定类型
	 *
	 */
	public function lock_tables($tablelist) {
		if (! empty ( $tablelist ) and is_array ( $tablelist )) {
			// 如果我们知道可能会碰到困难，不要锁定表！ (pconnect = true)
			// MYSQLI 不支持持久连接。
			if (strtolower ( $this->registry->config ['Database'] ['dbtype'] ) != 'mysqli' and $this->registry->config ['MasterServer'] ['usepconnect']) {
				return;
			}

			$sql = '';
			foreach ( $tablelist as $name => $type ) {
				$sql .= (! empty ( $sql ) ? ', ' : '') . TABLE_PREFIX . $name . " " . $type;
			}

			$this->query_write ( "LOCK TABLES $sql" );
			$this->locked = true;

		}
	}

	/**
	 * 解锁表
	 *
	 */
	public function unlock_tables() {
		if ($this->locked) {
			$this->query_write ( "UNLOCK TABLES" );
		}
	}

	/**
	 * 取得 MYSQL 版本
	 *
	 * @return	string
	 */
	public function version() {
		if ($this->connection_recent === null) {
			$this->version = '';
		} else {
			$this->version = $this->functions ['get_server_info'] ( $this->connection_recent );
		}
		return $this->version;
	}

	/**
	 * 返回最后一次数据库操作，文本错误信息
	 *
	 * @return	string
	 */
	public function error() {
		if ($this->connection_recent === null) {
			$this->error = '';
		} else {
			$this->error = $this->functions ['error'] ( $this->connection_recent );
		}
		return $this->error;
	}

	/**
	 * 返回最后一次数据库操作，数字代号错误信息
	 *
	 * @return	integer
	 */
	public function errno() {
		if ($this->connection_recent === null) {
			$this->errno = 0;
		} else {
			$this->errno = $this->functions ['errno'] ( $this->connection_recent );
		}
		return $this->errno;
	}

	/**
	 * 打开数据库错误显示
	 */
	public function show_errors() {
		$this->reporterror = true;
	}

	/**
	 * 关闭数据库错误显示
	 */
	public function hide_errors() {
		$this->reporterror = false;
	}

	/**
	 * 停止执行整个系统，并显示一条错误信息
	 *
	 * @param	string	错误信息文本， 留空使用 $this->sql 作为错误文本。
	 *
	 * @return	integer
	 */
	public function halt($errortext = '') {
		global $skyuc;

		if ($this->connection_recent) {
			$this->error = $this->error ( $this->connection_recent );
			$this->errno = $this->errno ( $this->connection_recent );
		}

		if ($this->reporterror) {
			if ($errortext == '') {
				$this->sql = "Invalid SQL:\r\n" . rtrim ( $this->sql ) . ';';
				$errortext = & $this->sql;
			}

			if (! headers_sent ()) {
				if (SAPI_NAME == 'cgi' or SAPI_NAME == 'cgi-fcgi') {
					header ( 'Status: 503 Service Unavailable' );
				} else {
					header ( 'HTTP/1.1 503 Service Unavailable' );
				}
			}

			$options = & $skyuc->options;
			$technicalemail = & $skyuc->config ['Database'] ['technicalemail'];
			$userinfo = & $skyuc->userinfo;
			$requestdate = date ( 'l, F jS Y @ h:i:s A', TIMENOW );
			$date = date ( 'l, F jS Y @ h:i:s A' );
			$scriptpath = str_replace ( '&amp;', '&', $skyuc->scriptpath );
			$referer = REFERRER;
			$ipaddress = IPADDRESS;
			$classname = get_class ( $this );

			if ($this->connection_recent) {
				$this->hide_errors ();
				$mysqlversion = $this->version ();
				$this->show_errors ();
			}

			$display_db_error = (SKYUC_AREA == 'Upgrade' || SKYUC_AREA == 'Install' || SKYUC_AREA == 'AdminCP');

			// 隐藏 MySQL 版本
			if (! $display_db_error) {
				$mysqlversion = '';
			}

			eval ( '$message = "' . str_replace ( '"', '\"', file_get_contents ( DIR . '/includes/database_error_message.html' ) ) . '";' );

			// add a backtrace to the message
			if ($skyuc->debug) {
				$trace = debug_backtrace ();
				$trace_output = "\n";

				foreach ( $trace as $index => $trace_item ) {
					$param = (in_array ( $trace_item ['function'], array ('require', 'require_once', 'include', 'include_once' ) ) ? $trace_item ['args'] [0] : '');

					// remove path
					$param = str_replace ( DIR, '[path]', $param );
					$trace_item ['file'] = str_replace ( DIR, '[path]', $trace_item ['file'] );

					$trace_output .= "#$index $trace_item[class]$trace_item[type]$trace_item[function]($param) called in $trace_item[file] on line $trace_item[line]\n";
				}

				$message .= "\n\nStack Trace:\n$trace_output\n";
			}

			require_once (DIR . '/includes/functions_log_error.php');
			if (function_exists ( 'log_skyuc_error' )) {
				log_skyuc_error ( $message, 'database' );
			}

			if ($technicalemail != '' and ! $skyuc->options ['disableerroremail'] and verify_email_skyuc_error ( $this->errno, 'database' )) {
				//判断init.php中是否执行到define ( 'SAPI_NAME', PHP_SAPI ); 否则为数据库连接错误
				if (defined('SAPI_NAME')) {
					@skyuc_mail ( $technicalemail, $this->appshortname . ' Database Error!', $message, true, $technicalemail );
				} else {
					@mail ( $technicalemail, $this->appshortname . ' Database Error!', preg_replace ( "#(\r\n|\r|\n)#s", (@ini_get ( 'sendmail_path' ) === '') ? "\r\n" : "\n", $message ), "From: $technicalemail" );
				}
			}

			if ($display_db_error) {
				// 屏幕上显示错误消息
				$message = '<form><textarea rows="15" cols="70" wrap="off" id="message">' . htmlspecialchars_uni ( $message ) . '</textarea></form>';
			} else {
				// 屏幕上隐藏错误消息
				$message = "\r\n<!--\r\n" . htmlspecialchars_uni ( $message ) . "\r\n-->\r\n";
			}

			if ($skyuc->options ['site_url']) {
				$imagepath = $skyuc->options ['site_url'];
			} else {
				// 这可能无法工作,在存档中的太多斜杠
				$imagepath = (SKYUC_AREA == 'IN_SKYUC' ? '.' : '..');
			}

			eval ( '$message = "' . str_replace ( '"', '\"', file_get_contents ( DIR . '/includes/database_error_page.html' ) ) . '";' );

			// 这是必要的，IE浏览器没有显示漂亮的错误信息
			$message .= str_repeat ( ' ', 512 );
			die ( $message );
		} else if (! empty ( $errortext )) {
			$this->error = $errortext;
		}
	}

}

// #############################################################################
// MySQLi 数据库类


/**
 * MySQL 4.1+ 数据库类接口
 *
 * 这个类也处理主从服务器之间的数据复制
 *
 */
class Database_MySQLi extends Database {
	/**
	 * 数组函数名，一个简单的名称映射到RDBMS（关系型数据库管理系统）的具体功能名称
	 *
	 * @private	array
	 */
	protected $functions = array ('connect' => 'mysqli_real_connect', 'pconnect' => 'mysqli_real_connect', // mysqli 不支持持久连接 ！
'select_db' => 'mysqli_select_db', 'query' => 'mysqli_query', 'query_unbuffered' => 'mysqli_unbuffered_query', 'fetch_row' => 'mysqli_fetch_row', 'fetch_array' => 'mysqli_fetch_array', 'fetch_field' => 'mysqli_fetch_field', 'free_result' => 'mysqli_free_result', 'data_seek' => 'mysqli_data_seek', 'error' => 'mysqli_error', 'errno' => 'mysqli_errno', 'affected_rows' => 'mysqli_affected_rows', 'num_rows' => 'mysqli_num_rows', 'num_fields' => 'mysqli_num_fields', 'field_name' => 'mysqli_field_tell', 'insert_id' => 'mysqli_insert_id', 'escape_string' => 'mysqli_real_escape_string', 'real_escape_string' => 'mysqli_real_escape_string', 'close' => 'mysqli_close', 'client_encoding' => 'mysqli_client_encoding', 'get_server_info' => 'mysqli_get_server_info' );

	/**
	 * 数组常量用于fetch_array
	 *
	 * @var	array
	 */
	protected $fetchtypes = array (DBARRAY_NUM => MYSQLI_NUM, DBARRAY_ASSOC => MYSQLI_ASSOC, DBARRAY_BOTH => MYSQLI_BOTH );

	/**
	 * 初始化数据库链接
	 *
	 * 连接到主数据库服务器，如果指定了从服务器，也连接到从数据库服务器。
	 *
	 * @param	string  数据库名称 - 应该是 'localhost' 或一个 IP 地址
	 * @param	integer	数据库服务器端口 (通常 3306)
	 * @param	string  连接到数据库服务器的用户名
	 * @param	string  数据库服务器上用户名的密码
	 * @param	string  持久连接 - MySQLi 不支持
	 * @param	string  配置文件  (my.ini / my.cnf)
	 * @param	string  Mysqli 连接字符集 仅 PHP 5.1.0+ or 5.0.5+ / MySQL 4.1.13+ or MySQL 5.1.10+
	 *
	 * @return	object  Mysqli 资源
	 */
	public function db_connect($servername, $port, $username, $password, $usepconnect, $configfile = '', $charset = '') {
		if (function_exists ( 'catch_db_error' )) {
			set_error_handler ( 'catch_db_error' );
		}

		$link = mysqli_init ();
		# 设置选项连接选项
		if (! empty ( $configfile )) {
			mysqli_options ( $link, MYSQLI_READ_DEFAULT_FILE, $configfile );
		}

		// 这将执行最多 5 次, 见 catch_db_error()
		do {
			$connect = $this->functions ['connect'] ( $link, $servername, $username, $password, '', $port );
		} while ( $connect == false and $this->reporterror );

		restore_error_handler ();

		if (! empty ( $charset )) {
			if (function_exists ( 'mysqli_set_charset' )) {
				mysqli_set_charset ( $link, $charset );
			} else {
				$this->sql = "SET NAMES $charset";
				$this->execute_query ( true, $link );
			}
		}

		return (! $connect) ? false : $link;
	}

	/**
	 * 通过指定链接执行一个SQL查询
	 *
	 * @param	boolean	是否要运行此查询缓冲 (true) 或不缓冲 (false)， 默认是不缓冲。
	 * @param	string	连接服务器 ID
	 *
	 * @return	string
	 */
	protected function &execute_query($buffered = true, &$link) {
		$this->connection_recent = & $link;
		$this->queryCount ++;
		// 查询时间
		if ($this->queryTime == '') {
			$this->queryTime = microtime ( true );
		}

		if ($queryresult = mysqli_query ( $link, $this->sql, ($buffered ? MYSQLI_STORE_RESULT : MYSQLI_USE_RESULT) )) {
			// 注销 $sql 降低内存 .. 这不是一个错误，因此不是必须的。
			$this->sql = '';

			return $queryresult;
		} else {
			$this->halt ();

			// 注销 $sql 降低内存 .. 错误已经抛出
			$this->sql = '';
		}
	}

	/**
	 * 简单封装 select_db(), 允许参数命令改变
	 *
	 * @param	string	数据库名称
	 * @param	integer	链接标识符
	 *
	 * @return	boolean
	 */
	protected function select_db_wrapper($database = '', $link = null) {
		return $this->functions ['select_db'] ( $link, $database );
	}

	/**
	 * 转义一个字符串，使其安全地插入一个SQL查询
	 *
	 * @param	string	该字符串被转义
	 *
	 * @return	string
	 */
	public function escape_string($string) {
		return $this->functions ['real_escape_string'] ( $this->connection_master, $string );
	}

	/**
	 * 返回的字段名称，从一个查询结果集中
	 *
	 * @param	string	我们正在处理的查询结果标识符
	 * @param	integer	该字段的数字偏移量。
	 *
	 * @return	string
	 */
	public function field_name($queryresult, $index) {
		$field = @$this->functions ['fetch_field'] ( $queryresult );
		return $field->name;
	}
}

// #############################################################################
// 数据存储缓冲类


/**
 * 数据库存储缓冲获取和初始化类
 *
 */
class Datastore {
	/**
	 * fetch();总是加载的默认项
	 *
	 * @var	array
	 */
	protected $defaultitems = array ('options', 'loadcache', 'category', //'players',
//'servers',
	'article_cat', 'usergroup', 'template', 'categories', 'topnewhots' );

	/**
	 * 这个变量包含从数据存储返回的所有项目的列表
	 *
	 * @var    array
	 */
	static $registered = array ();

	/**
	 * 此变量应设置为是一个引用注册对象
	 *
	 * @var	Registry
	 */
	public $registry = null;

	/**
	 * 此变量应设置为是一个引用数据库对象
	 *
	 * @var	Database
	 */
	public $dbobject = null;

	/**
	 * 独特的前缀项目的标题，需要多个SKYUC在同一台服务器上使用相同的类，读/写内存
	 *
	 * @var	string
	 */
	public $prefix = '';

	/**
	 * 是否我们验证选项都正确加载。
	 *
	 * @var bool
	 */
	public $checked_options;

	/**
	 * 构造函数 - 建立了数据库对象使用的数据存储查询

	 * @param	Registry	注册对象
	 * @param	Database	数据库对象
	 */
	function __construct(&$registry, &$dbobject) {
		$this->registry = & $registry;
		$this->dbobject = & $dbobject;

		$this->prefix = & $this->registry->config ['Datastore'] ['prefix'];

		if (defined ( 'SKIP_DEFAULTDATASTORE' )) //定义了跳过默认缓冲
{
			$this->defaultitems = array ('options' );
		}

		if (! is_object ( $registry )) {
			trigger_error ( '<strong>Datastore</strong>: $this->registry is not an object', E_USER_ERROR );
		}
		if (! is_object ( $dbobject )) {
			trigger_error ( '<strong>Datastore</strong>: $this->dbobject is not an object!', E_USER_ERROR );
		}
	}

	/**
	 * 对从缓存返回的数据进行排序并将其放入适当的地方
	 *
	 * @param	string	要处理的数据项目的名称
	 * @param	mixed		与名称关联的数据
	 * @param	integer	如果数据需要反解析序列化 0 = 否, 1 = 是, 2 = 自动检测
	 *
	 * @return	boolean
	 */
	function register($title, $data, $unserialize_detect = 2) {
		// 指定 $data 是否应该是一个数组
		$try_unserialize = (($unserialize_detect == 2) and ($data [0] == 'a' and $data [1] == ':'));

		if ($try_unserialize or $unserialize_detect == 1) {
			// 反序列化返回一个错误,返回 false
			if (($data = unserialize ( $data )) === false) {
				return false;
			}
		}
		if (! empty ( $title )) {
			$this->registry->$title = $data;
		}

		// 确保项目不重新获取
		self::$registered [] = $title;

		return true;
	}

	/**
	 * 准备要取得的项的列表。
	 * 已获取的项将被跳过。
	 *
	 * @param array string $items				- 项目标题所需的数组
	 * @return array string						- 要获取的项的数组
	 */
	function prepare_itemarray($items) {
		if (is_array ( $items )) {
			$itemarray = $items;
		} else {
			$itemarray = explode ( ',', $items );

			foreach ( $itemarray as &$title ) {
				$title = trim ( $title );
			}
		}

		// 包含默认项目
		$itemarray = array_merge ( $itemarray, $this->defaultitems );

		// 删除已加载的东西
		$itemarray = array_diff ( $itemarray, DataStore::$registered );

		return $itemarray;
	}

	/**
	 * 准备到一个列表项的数组。
	 * 结果是一个逗号分隔, db escaped, quoted 列表，使用在SQL中
	 *
	 * @param array string $items				- 项目标题的数组
	 * @param bool $prepare_items				- 首先检查项目
	 *
	 * @return string							- 安全的 sql 逗号分隔的列表
	 */
	function prepare_itemlist($items, $prepare_items = false) {
		if (is_string ( $items ) or $prepare_items) {
			$items = $this->prepare_itemarray ( $items );
		}

		if (! count ( $items )) {
			return false;
		}

		foreach ( $items as &$item ) {
			$item = "'" . $this->dbobject->escape_string ( $item ) . "'";
		}

		return implode ( ',', $items );
	}
	/**
	 * 从数据库的数据存储获取的内容
	 *
	 * @param	array	从数据存储获取的数组项目
	 *
	 * @return	boolean
	 */
	function fetch($items) {
		if ($items = $this->prepare_itemlist ( $items, true )) {
			$result = $this->do_db_fetch ( $items );
			if (! $result) {
				return false;
			}
		}

		$this->check_options ();
		return true;
	}

	/**
	 * 执行从数据库实际获取的数据存储项目, 子类可以使用它
	 *
	 * @param	string	数据存储项目标题
	 *
	 * @return	bool	有效的查询？
	 */
	function do_db_fetch($itemlist) {
		$db = & $this->dbobject;

		$db->hide_errors ();
		$dataitems = $db->query_read ( "
			SELECT *
			FROM " . TABLE_PREFIX . "datastore
			WHERE title IN ($itemlist)
		" );
		$db->show_errors ();

		while ( $dataitem = $db->fetch_array ( $dataitems ) ) {
			$this->register ( $dataitem ['title'], $dataitem ['data'], (isset ( $dataitem ['unserialize'] ) ? $dataitem ['unserialize'] : 2) );
		}
		$db->free_result ( $dataitems );

		return (! $db->errno ());
	}

	/**
	 * 检查该项选项已走出正确的数据存储
	 */
	function check_options() {
		if ($this->checked_options) {
			return;
		}
		if (! isset ( $this->registry->options ['skyuc_version'] )) {
			// 致命错误-选项没有正确加载
			require_once (DIR . '/includes/functions.php');
			$this->register ( 'options', build_options (), 0 );
		}

		$this->checked_options = true;
	}
}

// #############################################################################
// 输入的处理程序类


/**#@+
 * 过滤输入的方法。 应主要容易理解。
 */
define ( 'TYPE_NOCLEAN', 0 ); // 不变


define ( 'TYPE_BOOL', 1 ); // 强制布尔值 boolean
define ( 'TYPE_INT', 2 ); // 强制整数 integer
define ( 'TYPE_UINT', 3 ); // 强制无符号整数 unsigned integer
define ( 'TYPE_NUM', 4 ); // 强制数字(淫点数) number
define ( 'TYPE_UNUM', 5 ); // 强制无符号数字(淫点数) unsigned number
define ( 'TYPE_UNIXTIME', 6 ); // 强制 Unix时间戳  unix datestamp (unsigned integer)
define ( 'TYPE_STR', 7 ); // 强制裁剪的字符串
define ( 'TYPE_NOTRIM', 8 ); // 强制字符串 - 不裁剪
define ( 'TYPE_NOHTML', 9 ); // 强制裁剪的字符串用 HTML 的安全
define ( 'TYPE_ARRAY', 10 ); // 强制数组
define ( 'TYPE_FILE', 11 ); // 强制文件
define ( 'TYPE_BINARY', 12 ); // 强制二进制字符串
define ( 'TYPE_NOHTMLCOND', 13 ); // 强制裁剪的字符串 用  HTML 安全, 如果确定为不安全


define ( 'TYPE_ARRAY_BOOL', 101 );
define ( 'TYPE_ARRAY_INT', 102 );
define ( 'TYPE_ARRAY_UINT', 103 );
define ( 'TYPE_ARRAY_NUM', 104 );
define ( 'TYPE_ARRAY_UNUM', 105 );
define ( 'TYPE_ARRAY_UNIXTIME', 106 );
define ( 'TYPE_ARRAY_STR', 107 );
define ( 'TYPE_ARRAY_NOTRIM', 108 );
define ( 'TYPE_ARRAY_NOHTML', 109 );
define ( 'TYPE_ARRAY_ARRAY', 110 );
define ( 'TYPE_ARRAY_FILE', 11 ); // "文件"的数组的行为方式不同于其它 <input> 数组。 TYPE_FILE 处理这两种类型。
define ( 'TYPE_ARRAY_BINARY', 112 );
define ( 'TYPE_ARRAY_NOHTMLCOND', 113 );

define ( 'TYPE_ARRAY_KEYS_INT', 202 );
define ( 'TYPE_ARRAY_KEYS_STR', 207 );

define ( 'TYPE_CONVERT_SINGLE', 100 ); // 从数组类型转换为单一类型 ，减去此值
define ( 'TYPE_CONVERT_KEYS', 200 ); // 从 array => keys 类型转换为单一类型，减去此值
/**#@-*/

/**
 * 类用于处理和过滤变量从 GET，POST 和 COOKIE 等
 *
 */
class Input_Cleaner {
	/**
	 * 长名称的短名称的翻译表
	 *
	 * @private    array
	 */
	private $shortvars = array ('cid' => 'cateid', 'mid' => 'movieid', 'uid' => 'userid', 'q' => 'query', 'pp' => 'perpage', 'page' => 'pagenumber', 'sort' => 'sortfield', 'order' => 'sortorder' );

	/**
	 * 短超全局变量名称为长超全局变量名称的翻译表
	 *
	 * @private     array
	 */
	private $superglobal_lookup = array ('g' => '_GET', 'p' => '_POST', 'r' => '_REQUEST', 'c' => '_COOKIE', 's' => '_SERVER', 'e' => '_ENV', 'f' => '_FILES' );

	/**
	 *系统状态。 当前页没有 sessionhash 的完整的 URL
	 *
	 * @public	string
	 */
	public $scriptpath = '';

	/**
	 * 重新加载 URL。 完整包括 sessionhash 当前页的 URL
	 *
	 * @public	string
	 */
	public $reloadurl = '';

	/**
	 * 系统状态。 为谁在线上页的完整 URL 用途
	 *
	 * @public	string
	 */
	public $wolpath = '';

	/**
	 * 系统状态。 引用的页的完整 URL
	 *
	 * @public	string
	 */
	public $url = '';

	/**
	 * 系统状态。 当前的访客的 IP 地址
	 *
	 * @public	string
	 */
	public $ipaddress = '';

	/**
	 * 系统状态。 试图查找当前访问者 （代理等） 的第二个的 IP
	 *
	 * @public	string
	 */
	public $alt_ip = '';

	/**
	 * 对主要注册表对象的引用
	 *
	 * @public	Registry
	 */
	public $registry = null;

	/**
	 * 保持跟踪已被过滤的变量
	 *
	 * @private	array
	 */
	private $cleaned_vars = array ();

	/**
	 * 构造函数
	 *
	 * 首先，对 GPC 反转魔术引号的影响。
	 * 第二，短变量名翻译成长变量名。 (u --> userid)
	 * 第三，处理 $_COOKIE[userid] 冲突。
	 *
	 * @param	Registry	Registry 对象的实例
	 */
	function __construct(&$registry) {
		$this->registry = & $registry;

		if (! is_array ( $GLOBALS )) {
			die ( '<strong>Fatal Error:</strong> Invalid URL.' );
		}

		// 获取当前页完整的 URL
		$registry->scriptpath = $this->fetch_scriptpath ();
		define ( 'SCRIPTPATH', $registry->scriptpath );
		define ( 'SCRIPTPATH_RAW', $this->fetch_scriptpath_raw () );

		$registry->script = $_SERVER ['SCRIPT_NAME'];
		define ( 'SCRIPT', $registry->script );

		// 用 POST[x] 覆写 GET[x] 、REQUEST[x] ，如果GET[x]存在 (超过服务器的 GPC 优先顺序)
		if ($_SERVER ['REQUEST_METHOD'] == 'POST') {
			foreach ( array_keys ( $_POST ) as $key ) {
				if (isset ( $_GET ["$key"] )) {
					$_GET ["$key"] = $_REQUEST ["$key"] = $_POST ["$key"];
				}
			}
		}

		// 处理会话绕过情况
		if (! defined ( 'SESSION_BYPASS' )) {
			define ( 'SESSION_BYPASS', ! empty ( $_REQUEST ['bypass'] ) );
		}

		// 如有必要反向魔术引号的影响
		if (function_exists ( 'get_magic_quotes_gpc' ) and get_magic_quotes_gpc ()) {
			$this->stripslashes_deep ( $_REQUEST ); // 由于某种原因需要 (至少 php5 - 未测试 php4)
			$this->stripslashes_deep ( $_GET );
			$this->stripslashes_deep ( $_POST );
			$this->stripslashes_deep ( $_COOKIE );

			if (is_array ( $_FILES )) {
				foreach ( $_FILES as $key => $val ) {
					$_FILES ["$key"] ['tmp_name'] = str_replace ( '\\', '\\\\', $val ['tmp_name'] );
				}
				$this->stripslashes_deep ( $_FILES );
			}
		}
		//set_magic_quotes_runtime 函数在PHP 5.3.0 中发出反对警告， 且在 php 6.0.0中移除。
		if (function_exists ( 'set_magic_quotes_runtime' )) {
			@set_magic_quotes_runtime ( 0 );
			@ini_set ( 'magic_quotes_sybase', 0 );
		}

		foreach ( array ('_GET', '_POST' ) as $arrayname ) {
			if (isset ( $GLOBALS ["$arrayname"] ['act'] )) {
				$GLOBALS ["$arrayname"] ['act'] = trim ( $GLOBALS ["$arrayname"] ['act'] );
			}

			$this->convert_shortvars ( $GLOBALS ["$arrayname"] );
		}

		// 设置 AJAX 标志，如果我们有一个 AJAX 提交
		if ($_SERVER ['REQUEST_METHOD'] == 'POST' and $_SERVER ['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
			$_POST ['is_ajax'] = $_REQUEST ['is_ajax'] = 1;
		}

		// 如有必要反向 register_globals 的影响
		if (@ini_get ( 'register_globals' ) or ! @ini_get ( 'gpc_order' )) {
			foreach ( $this->superglobal_lookup as $arrayname ) {
				$registry->superglobal_size ["$arrayname"] = count ( $GLOBALS ["$arrayname"] );

				foreach ( array_keys ( $GLOBALS ["$arrayname"] ) as $varname ) {
					// 确保我们没有销毁(unset)任何全局数组，像 _SERVER
					if (! in_array ( $varname, $this->superglobal_lookup )) {
						unset ( $GLOBALS ["$varname"] );
					}
				}
			}
		} else {
			foreach ( $this->superglobal_lookup as $arrayname ) {
				$registry->superglobal_size ["$arrayname"] = count ( $GLOBALS ["$arrayname"] );
			}
		}

		// 处理可能与_GET和_POST的数据冲突的Cookie，并建立我们自己的 _REQUEST 没有_COOKIE 输入
		foreach ( array_keys ( $_COOKIE ) as $varname ) {
			unset ( $_REQUEST ["$varname"] );
			if (isset ( $_POST ["$varname"] )) {
				$_REQUEST ["$varname"] = & $_POST ["$varname"];
			} else if (isset ( $_GET ["$varname"] )) {
				$_REQUEST ["$varname"] = & $_GET ["$varname"];
			}
		}

		// 获取客户端 IP 地址
		$registry->ipaddress = $this->fetch_ip ();
		define ( 'IPADDRESS', $registry->ipaddress );

		// 尝试获取 IP 地址从后面代理-有用，但不依赖于它...
		$registry->alt_ip = $this->fetch_alt_ip ();
		define ( 'ALT_IP', $registry->alt_ip );

		// 定义如果当前页面的访问是否通过SSL
		define ( 'REQ_PROTOCOL', (($_SERVER ['HTTPS'] == 'on' or $_SERVER ['HTTPS'] == '1') ? 'https' : 'http') );

		// 取当前页面的URL(用于POST请求)
		$registry->wolpath = $this->fetch_wolpath ();
		define ( 'WOLPATH', $registry->wolpath );
		// 定义会话常量:SESSION 服务器
		define ( 'SESSION_HOST', substr ( $registry->alt_ip, 0, 15 ) );

		// 定义一些有用的内容与相关的环境
		define ( 'USER_AGENT', $_SERVER ['HTTP_USER_AGENT'] );
		define ( 'REFERRER', $_SERVER ['HTTP_REFERER'] );

		if ($_SERVER ['HTTP_HOST'] or $_ENV ['HTTP_HOST']) {
			$http_host = ($_SERVER ['HTTP_HOST'] ? $_SERVER ['HTTP_HOST'] : $_ENV ['HTTP_HOST']);
		} else if ($_SERVER ['SERVER_NAME'] or $_ENV ['SERVER_NAME']) {
			$http_host = ($_SERVER ['SERVER_NAME'] ? $_SERVER ['SERVER_NAME'] : $_ENV ['SERVER_NAME']);
		}
		define ( 'HTTP_HOST', trim ( $http_host ) );
	}

	/**
	 * 使数组中的数据安全的使用
	 *
	 * @param	array	包含要过滤数据的源数组
	 * @param	array	我们要从源数组提取的数组变量名和类型array('name'=> type)
	 *
	 * @return	array
	 */
	function &clean_array(&$source, $variables) {
		$return = array ();

		foreach ( $variables as $varname => $vartype ) {
			$return ["$varname"] = & $this->clean ( $source ["$varname"], $vartype, isset ( $source ["$varname"] ) );
		}

		return $return;
	}

	/**
	 * 使 GPC 变量安全使用
	 *
	 * @param	string	 g, p, c, r 或 f其一 (对应于get, post, cookie, request 和 files)
	 * @param	array		我们要从源数组提取的数组变量名和类型 array('name'=> type)
	 *
	 * @return	array
	 */
	function clean_array_gpc($source, $variables) {
		$sg = & $GLOBALS [$this->superglobal_lookup ["$source"]];

		foreach ( $variables as $varname => $vartype ) {
			// 限制变量只能被“过滤”一次，除非其有不同的类型
			if (! isset ( $this->cleaned_vars ["$varname"] ) or $this->cleaned_vars ["$varname"] != $vartype) {
				$this->registry->GPC_exists ["$varname"] = isset ( $sg ["$varname"] );
				$this->registry->GPC ["$varname"] = & $this->clean ( $sg ["$varname"], $vartype, isset ( $sg ["$varname"] ) );
				$this->cleaned_vars ["$varname"] = $vartype;
			}
		}
	}

	/**
	 * 使单个 GPC 变量安全使用，并将其返回
	 *
	 * @param	array	包含要过滤数据的源数组
	 * @param	string	我们要过滤的变量的名称
	 * @param	integer	我们要过滤的变量的类型
	 *
	 * @return	mixed
	 */
	function &clean_gpc($source, $varname, $vartype = TYPE_NOCLEAN) {

		// 限制变量只能被“过滤”一次，除非其有不同的类型
		if (! isset ( $this->cleaned_vars ["$varname"] ) or $this->cleaned_vars ["$varname"] != $vartype) {
			$sg = & $GLOBALS [$this->superglobal_lookup ["$source"]];

			$this->registry->GPC_exists ["$varname"] = isset ( $sg ["$varname"] );
			$this->registry->GPC ["$varname"] = & $this->clean ( $sg ["$varname"], $vartype, isset ( $sg ["$varname"] ) );
			$this->cleaned_vars ["$varname"] = $vartype;
		}

		return $this->registry->GPC ["$varname"];
	}

	/**
	 * 使单个变量安全使用，并将其返回
	 *
	 * @param	mixed		该变量进行过滤
	 * @param	integer	我们要过滤的变量的类型
	 * @param	boolean	变量是否存在，默认存在
	 *
	 * @return	mixed		过滤后的值
	 */
	function &clean(&$var, $vartype = TYPE_NOCLEAN, $exists = true) {
		if ($exists) {
			if ($vartype < TYPE_CONVERT_SINGLE) {
				$this->do_clean ( $var, $vartype );
			} else if (is_array ( $var )) {
				if ($vartype >= TYPE_CONVERT_KEYS) {
					$var = array_keys ( $var );
					$vartype -= TYPE_CONVERT_KEYS;
				} else {
					$vartype -= TYPE_CONVERT_SINGLE;
				}

				foreach ( array_keys ( $var ) as $key ) {
					$this->do_clean ( $var ["$key"], $vartype );
				}
			} else {
				$var = array ();
			}
			return $var;
		} else {
			// 使用 $newvar 在这里以防止覆盖自动全局变量
			if ($vartype < TYPE_CONVERT_SINGLE) {
				switch ($vartype) {
					case TYPE_INT :
					case TYPE_UINT :
					case TYPE_NUM :
					case TYPE_UNUM :
					case TYPE_UNIXTIME :
						{
							$newvar = 0;
							break;
						}
					case TYPE_STR :
					case TYPE_NOHTML :
					case TYPE_NOTRIM :
					case TYPE_NOHTMLCOND :
						{
							$newvar = '';
							break;
						}
					case TYPE_BOOL :
						{
							$newvar = 0;
							break;
						}
					case TYPE_ARRAY :
					case TYPE_FILE :
						{
							$newvar = array ();
							break;
						}
					case TYPE_NOCLEAN :
						{
							$newvar = null;
							break;
						}
					default :
						{
							$newvar = null;
						}
				}
			} else {
				$newvar = array ();
			}

			return $newvar;
		}
	}

	/**
	 * 使一个变量安全的实际工作
	 *
	 * @param	mixed		我们要其使安全的数据
	 * @param	integer	数据的类型
	 *
	 * @return	mixed
	 */
	function &do_clean(&$data, $type) {
		static $booltypes = array ('1', 'yes', 'y', 'true', 'on' );

		switch ($type) {
			case TYPE_INT :
				$data = intval ( $data );
				break;
			case TYPE_UINT :
				$data = ($data = intval ( $data )) < 0 ? 0 : $data;
				break;
			case TYPE_NUM :
				$data = strval ( $data ) + 0;
				break;
			case TYPE_UNUM :
				$data = strval ( $data ) + 0;
				$data = ($data < 0) ? 0 : $data;
				break;
			case TYPE_BINARY :
				$data = strval ( $data );
				break;
			case TYPE_STR :
				$data = trim ( strval ( $data ) );
				break;
			case TYPE_NOTRIM :
				$data = strval ( $data );
				break;
			case TYPE_NOHTML :
				$data = htmlspecialchars_uni ( trim ( strval ( $data ) ) );
				break;
			case TYPE_BOOL :
				$data = in_array ( strtolower ( $data ), $booltypes ) ? 1 : 0;
				break;
			case TYPE_ARRAY :
				$data = (is_array ( $data )) ? $data : array ();
				break;
			case TYPE_NOHTMLCOND :
				{
					$data = trim ( strval ( $data ) );
					if (strcspn ( $data, '<>"' ) < strlen ( $data ) or (strpos ( $data, '&' ) !== false and ! preg_match ( '/&(#[0-9]+|amp|lt|gt|quot);/si', $data ))) {
						// 数据不可用 htmlspecialchars，因为它仍有字符或实体
						$data = htmlspecialchars_uni ( $data );
					}
					break;
				}
			case TYPE_FILE :
				{
					// 也许是多余的=^_^=
					if (is_array ( $data )) {
						if (is_array ( $data ['name'] )) {
							$files = count ( $data ['name'] );
							for($index = 0; $index < $files; $index ++) {
								$data ['name'] ["$index"] = trim ( strval ( $data ['name'] ["$index"] ) );
								$data ['type'] ["$index"] = trim ( strval ( $data ['type'] ["$index"] ) );
								$data ['tmp_name'] ["$index"] = trim ( strval ( $data ['tmp_name'] ["$index"] ) );
								$data ['error'] ["$index"] = intval ( $data ['error'] ["$index"] );
								$data ['size'] ["$index"] = intval ( $data ['size'] ["$index"] );
							}
						} else {
							$data ['name'] = trim ( strval ( $data ['name'] ) );
							$data ['type'] = trim ( strval ( $data ['type'] ) );
							$data ['tmp_name'] = trim ( strval ( $data ['tmp_name'] ) );
							$data ['error'] = intval ( $data ['error'] );
							$data ['size'] = intval ( $data ['size'] );
						}
					} else {
						$data = array ('name' => '', 'type' => '', 'tmp_name' => '', 'error' => 0, 'size' => 4 );// UPLOAD_ERR_NO_FILE

					}
					break;
				}
			case TYPE_UNIXTIME :
				{
					if (is_array ( $data )) {
						$data = $this->clean ( $data, TYPE_ARRAY_UINT );
						if ($data ['month'] and $data ['day'] and $data ['year']) {
							require_once (DIR . '/includes/functions_base.php');
							$data = skyuc_mktime ( $data ['hour'], $data ['minute'], $data ['second'], $data ['month'], $data ['day'], $data ['year'] );
						} else {
							$data = 0;
						}
					} else {
						$data = ($data = intval ( $data )) < 0 ? 0 : $data;
					}
					break;
				}
			// 空操作应当是在这里 deifned，因此我们还能捕捉下面的拼写错误
			case TYPE_NOCLEAN :
				{
					break;
				}

			default :
				{
					if ($this->registry->debug) {
						trigger_error ( 'Input_Cleaner::do_clean() Invalid data type specified', E_USER_WARNING );
					}
				}
		}

		// 二进制数据下剔除 NULL 字符
		switch ($type) {
			case TYPE_STR :
			case TYPE_NOTRIM :
			case TYPE_NOHTML :
			case TYPE_NOHTMLCOND :
				$data = str_replace ( chr ( 0 ), '', $data );
		}

		return $data;
	}

	/**
	 * 从字符串中移除 HTML 字符和潜在的不安全的脚本词
	 *
	 * @param	string	我们想要安全的变量
	 *
	 * @return	string
	 */
	function xss_clean($var) {
		static $preg_find = array ('#^javascript#i', '#^vbscript#i' ), $preg_replace = array ('java script', 'vb script' );

		return preg_replace ( $preg_find, $preg_replace, htmlspecialchars ( trim ( $var ) ) );
	}

	/**
	 * 对整个数组变量反转 magic_quotes 的影响
	 *
	 * @param	array	我们要处理的数组
	 */
	function stripslashes_deep(&$value, $depth = 0) {
		if (is_array ( $value )) {
			foreach ( $value as $key => $val ) {
				if (is_string ( $val )) {
					$value ["$key"] = stripslashes ( $val );
				} else if (is_array ( $val ) and $depth < 10) {
					$this->stripslashes_deep ( $value ["$key"], $depth + 1 );
				}
			}
		}
	}

	/**
	 * 转换 $_POST['uid'] 到 $_POST['userid'] 等.
	 *
	 * @param	array	该数组的名称
	 * @param	boolean	是否设置全局变量
	 */
	function convert_shortvars(&$array, $setglobals = true) {
		// 从长变量名提取短的变量名称
		foreach ( $this->shortvars as $shortname => $longname ) {
			if (isset ( $array ["$shortname"] ) and ! isset ( $array ["$longname"] )) {
				$array ["$longname"] = & $array ["$shortname"];
				if ($setglobals) {
					$GLOBALS ['_REQUEST'] ["$longname"] = & $array ["$shortname"];
				}

			}
		}
	}

	/**
	 * 从 URLs 剔除 s=xxx& 垃圾
	 *
	 * @param	string	要删除 session 东西的 URL 字符串
	 *
	 * @return	string
	 */
	function strip_sessionhash($string) {
		$string = preg_replace ( '/(s|sessionhash)=[a-z0-9]{32}?&?/', '', $string );
		return $string;
	}

	/**
	 * 获取 'scriptpath' 变量-即： 当前页的 URI
	 *
	 * @return	string
	 */
	function fetch_scriptpath() {
		if ($this->registry->scriptpath != '') {
			return $this->registry->scriptpath;
		} else {
			$scriptpath = $this->fetch_scriptpath_raw ();

			// 将来我们应该在这儿设置 $registry->script
			$quest_pos = strpos ( $scriptpath, '?' );
			if ($quest_pos !== false) {
				$script = urldecode ( substr ( $scriptpath, 0, $quest_pos ) );
				$scriptpath = $script . substr ( $scriptpath, $quest_pos );
			} else {
				$scriptpath = urldecode ( $scriptpath );
			}

			// 存储一个版本，包括 sessionhash
			$this->registry->reloadurl = $this->xss_clean ( $scriptpath );

			//$scriptpath = $this->strip_sessionhash($scriptpath);
			$scriptpath = $this->xss_clean ( $scriptpath );
			$this->registry->scriptpath = $scriptpath;

			return $scriptpath;
		}
	}

	/**
	 * Fetches the raw scriptpath.
	 *
	 * @return string
	 */
	function fetch_scriptpath_raw() {
		if ($_SERVER ['REQUEST_URI'] or $_ENV ['REQUEST_URI']) {
			$scriptpath = $_SERVER ['REQUEST_URI'] ? $_SERVER ['REQUEST_URI'] : $_ENV ['REQUEST_URI'];
		} else {
			if ($_SERVER ['PATH_INFO'] or $_ENV ['PATH_INFO']) {
				$scriptpath = $_SERVER ['PATH_INFO'] ? $_SERVER ['PATH_INFO'] : $_ENV ['PATH_INFO'];
			} else if ($_SERVER ['REDIRECT_URL'] or $_ENV ['REDIRECT_URL']) {
				$scriptpath = $_SERVER ['REDIRECT_URL'] ? $_SERVER ['REDIRECT_URL'] : $_ENV ['REDIRECT_URL'];
			} else {
				$scriptpath = $_SERVER ['PHP_SELF'] ? $_SERVER ['PHP_SELF'] : $_ENV ['PHP_SELF'];
			}

			if ($_SERVER ['QUERY_STRING'] or $_ENV ['QUERY_STRING']) {
				$scriptpath .= '?' . ($_SERVER ['QUERY_STRING'] ? $_SERVER ['QUERY_STRING'] : $_ENV ['QUERY_STRING']);
			}
		}

		return $this->strip_sessionhash ( $scriptpath );
	}

	/**
	 * 提取'basepath'变量，可作为<base> 。
	 *
	 * @return string
	 */
	function fetch_basepath($rel_modifier = false) {
		if ($this->registry->basepath != '') {
			return $this->registry->basepath;
		}

		if (0 and ($script_uri = max ( $_SERVER ['SCRIPT_URI'], $_ENV ['SCRIPT_URI'] ))) {
			$basepath = dirname ( $script_uri );
		} else {
			if ((! $_SERVER ['SERVER_NAME'] and ! $_SERVER ['HTTP_HOST']) or (! $_SERVER ['SCRIPT_NAME'] and ! $_SERVER ['PHP_SELF']) and ((! $_ENV ['SERVER_NAME'] and ! $_ENV ['HTTP_HOST']) or (! $_ENV ['SCRIPT_NAME'] and ! $_ENV ['PHP_SELF']))) {
				$basepath = $this->registry->options ['site_url'];
			} else {
				$port = $_SERVER ['SERVER_PORT'];

				$protocol = (443 == $port or (isset ( $_SERVER ['HTTPS'] ) and $_SERVER ['HTTPS'])) ? 'https://' : 'http://';
				$port = (! $port or 80 == $port or 443 == $port) ? '' : ":$port";

				if (! ($name = max ( array ($_SERVER ['HTTP_HOST'], $_ENV ['HTTP_HOST'] ) ))) {
					$name = max ( array ($_SERVER ['SERVER_NAME'], $_ENV ['SERVER_NAME'] ) );
				}

				$path = dirname ( max ( array ($_SERVER ['SCRIPT_PATH'], $_ENV ['SCRIPT_PATH'] ) ) );

				if (! $path) {
					if ($_SERVER ['SCRIPT_NAME']) {
						$path = dirname ( $_SERVER ['SCRIPT_NAME'] );
					} else {
						$path = dirname ( max ( array ($_SERVER ['PHP_SELF'], $_ENV ['PHP_SELF'] ) ) );

						if (defined ( 'FRIENDLY_URL' ) and FRIENDLY_URL == 2) {
							if (preg_match ( '#^(?:(.*)/)?.*?.php#u', $path, $matches ) and isset ( $matches [1] )) {
								$path = $matches [1];
							} else if (preg_match ( '#^(.*).php$#', $path, $matches )) {
								$path = '';
							}
						}
					}
				}

				$basepath = $protocol . $_SERVER ['SERVER_NAME'] . $port . $path . '/';
			}
		}

		$basepath = $this->xss_clean ( $basepath );

		if ($rel_modifier) {
			$basepath .= $rel_modifier;
		}

		$this->registry->base_path = $basepath;

		return $basepath;
	}

	/**
	 * 提取' wolpath '变量-即：与' scriptpath '相同 ，但是用于处理一个POST请求的方法
	 *
	 * @return	string
	 */
	function fetch_wolpath() {
		$wolpath = $this->fetch_scriptpath ();

		if ($_SERVER ['REQUEST_METHOD'] == 'POST') {
			// 如果我们来自 POST，以便 WOL 可以访问文件名后面的变量。
			$tackon = '';

			if (is_array ( $_POST )) {
				foreach ( $_POST as $varname => $value ) {
					switch ($varname) {
						case 'id' :
						case 'show_id' :
						case 'url_id' :
						case 'user_id' :
						case 'act' :
							{
								$tackon .= ($tackon == '' ? '' : '&amp;') . $varname . '=' . $value;
								break;
							}
					}
				}
			}
			if ($tackon != '') {
				$wolpath .= (strpos ( $wolpath, '?' ) !== false ? '&amp;' : '?') . "$tackon";
			}
		}

		return $wolpath;
	}

	/**
	 * 获取 URL 变量-通常历史记录中前一页的 URL
	 *
	 * @return	string
	 */
	function fetch_url() {
		$scriptpath = $this->fetch_scriptpath ();

		if (empty ( $_REQUEST ['url'] )) {
			$url = (! empty ( $_SERVER ['HTTP_REFERER'] ) ? $_SERVER ['HTTP_REFERER'] : '');
		} else {
			$temp_url = $_REQUEST ['url'];
			if (! empty ( $_SERVER ['HTTP_REFERER'] ) and $temp_url == $_SERVER ['HTTP_REFERER']) {
				$url = 'index.php';
			} else {
				$url = $temp_url;
			}
		}

		if ($url == $scriptpath or empty ( $url )) {
			$url = 'index.php';
		}

		// 如果 $url 设置为首页， 检查它对应选项
		//		if ($url == 'index.php' AND $this->registry->options['sitehome'] != 'index')
		//		{
		//			$url = $this->registry->options['sitehome'] . '.php';
		//		}


		$url = $this->xss_clean ( $url );

		return $url;
	}

	/**
	 * 获取当前的访客的 IP 地址
	 *
	 * @return	string
	 */
	function fetch_ip() {
		if ($this->registry->options ['xforwardip'])
			return $this->fetch_alt_ip ();
		else
			return $_SERVER ['REMOTE_ADDR'];
	}

	/**
	 * 获取当前访客尝试检测代理等的备用 IP 地址。
	 *
	 * @return	string
	 */
	function fetch_alt_ip() {
		$alt_ip = $_SERVER ['REMOTE_ADDR'];

		if (isset ( $_SERVER ['HTTP_CLIENT_IP'] )) {
			$alt_ip = $_SERVER ['HTTP_CLIENT_IP'];
		} else if (isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] ) and preg_match_all ( '#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER ['HTTP_X_FORWARDED_FOR'], $matches )) {
			// 确保我们不获得 由 RFC1918所定义的 一个内部 IP
			$ranges = array ('10.0.0.0/8' => array (ip2long ( '10.0.0.0' ), ip2long ( '10.255.255.255' ) ), '127.0.0.0/8' => array (ip2long ( '127.0.0.0' ), ip2long ( '127.255.255.255' ) ), '169.254.0.0/16' => array (ip2long ( '169.254.0.0' ), ip2long ( '169.254.255.255' ) ), '172.16.0.0/12' => array (ip2long ( '172.16.0.0' ), ip2long ( '172.31.255.255' ) ), '192.168.0.0/16' => array (ip2long ( '192.168.0.0' ), ip2long ( '192.168.255.255' ) ) );
			foreach ( $matches [0] as $ip ) {
				$ip_long = ip2long ( $ip );
				if ($ip_long === false or $ip_long == - 1) {
					continue;
				}

				$private_ip = false;
				foreach ( $ranges as $range ) {
					if ($ip_long >= $range [0] and $ip_long <= $range [1]) {
						$private_ip = true;
						break;
					}
				}

				if (! $private_ip) {
					$alt_ip = $ip;
					break;
				}
			}
		} else if (isset ( $_SERVER ['HTTP_FROM'] )) {
			$alt_ip = $_SERVER ['HTTP_FROM'];
		}

		return $alt_ip;
	}
}

// #############################################################################
// 数据注册表类


/**
 * 存储常用变量类
 *
 */
class Registry {
	// 一般对象
	/**
	 * 数据存储对象。
	 *
	 * @public	Datastore
	 */
	public $datastore;

	/**
	 * 输入的过滤对象。
	 *
	 * @public	Input_Cleaner
	 */
	public $input;

	/**
	 * 数据库对象。
	 *
	 * @public	Database
	 */
	public $db;

	// user/session 相关
	/**
	 * 有关当前浏览用户信息的数组。 注册用户的情况下，这将是 fetch_userinfo() 的结果。 一位游客会稍有不同的条目。
	 *
	 * @public	array
	 */
	public $userinfo;

	/**
	 * Session 对象。
	 *
	 * @public Session
	 */
	public $session;

	/**
	 * 无需检查的动作数组
	 *
	 * @public array
	 */
	public $csrf_skip_list = array ();

	// 配置
	/**
	 * config.php 中的数据的数组。
	 *
	 * @public	array
	 */
	public $config;

	// GPC 输入
	/**
	 * 已被 input cleaner 过滤的数据的数组
	 *
	 * @public	array
	 */
	public $GPC = array ();

	/**
	 * 布尔值的数组。当过滤一个变量, 你经常会忘记确定它是否在用户的输入中已指定。
	 * 如果变量在过滤前存在,在此数组中的条目为 true
	 *
	 * @public	array
	 */
	public $GPC_exists = array ();

	/**
	 * 超全局数组的大小。
	 *
	 * @public	array
	 */
	public $superglobal_size = array ();

	// 单变量
	/**
	 * 当前的浏览用户的 IP 地址。
	 *
	 * @public	string
	 */
	public $ipaddress;

	/**
	 * 浏览用户的备用 IP。 这会尝试使用各种 HTTP 头来查找可能位于一个代理之后的用户的实际 IP。
	 *
	 * @public	string
	 */
	public $alt_ip;

	/**
	 * 在当前浏览的页面的 URL。
	 *
	 * @public	string
	 */
	public $scriptpath;

	/**
	 * 类似当前的页的 URL, 但扩展某些项目，并包括通过 POST 提交的数据。 用于谁在线上用途。
	 *
	 * @public	string
	 */
	public $wolpath;

	/**
	 * 当前页的 URL ，没有"?"后面的任何信息
	 *
	 * @public	string
	 */
	public $script;

	/**
	 * 网页来源 URL，一般用于 URL 重定向。
	 *
	 * @public	string
	 */
	public $url;

	/**#@+
	 * 数据存储中的特定条目的结果。
	 *
	 * @var	mixed	混合, 虽然大部分数组.
	 */
	public $options = null;
	public $usergroup = null;
	public $mailqueue = null;
	public $wol_spiders = null;
	public $loadcache = null;
	public $prefixcache = null;
	/**#@-*/

	/**#@+
	 * 杂项变量
	 *
	 * @public	mixed
	 */
	public $versionnumber;
	public $nozip;
	public $debug;
	public $noheader;
	public $shutdown;
	/**#@-*/

	/**
	 * 构造函数 - 初始化 nozip 系统,和调用 Input_Cleaner 类的实例化。
	 *
	 */
	public function __construct() {
		// 允许变量绕过gzip压缩
		$this->nozip = defined ( 'NOZIP' ) ? true : (@ini_get ( 'zlib.output_compression' ) ? true : false);
		// 变量控制HTTP头输出
		$this->noheader = defined ( 'NOHEADER' ) ? true : false;

		// 初始化该输入的处理程序
		$this->input = new Input_Cleaner ( $this );

		// 初始化关闭处理程序
		$this->shutdown = Shutdown::init ();

		$this->csrf_skip_list = (defined ( 'CSRF_SKIP_LIST' ) ? explode ( ',', CSRF_SKIP_LIST ) : array ());
	}

	/**
	 * 获取数据库/系统配置
	 */
	public function fetch_config() {
		// 解析配置文件
		$config = array ();
		include (CWD . '/data/config.php');

		if (count ( $config ) == 0) {
			if (file_exists ( CWD . '/data/config.php' )) {
				// config.php 存在，但是不能定义 $config
				die ( '<br /><br /><strong>Configuration</strong>: data/config.php exists, but is not in the 3.0+ format. Please convert your config file via the new config.php.new.' );
			} else {
				die ( '<br /><br /><strong>Configuration</strong>: data/config.php does not exist. Please fill out the data in config.php.new and rename it to config.php' );
			}
		}

		$this->config = & $config;
		// 如果一个配置存在精确的 HTTP 主机, 使用它。
		if (isset ( $this->config ["$_SERVER[HTTP_HOST]"] )) {
			$this->config ['MasterServer'] = $this->config ["$_SERVER[HTTP_HOST]"];
		}

		// 定义表和 Cookie 前缀常量
		define ( 'TABLE_PREFIX', trim ( $this->config ['Database'] ['tableprefix'] ) );
		define ( 'COOKIE_PREFIX', (empty ( $this->config ['Misc'] ['cookieprefix'] ) ? 'bb' : $this->config ['Misc'] ['cookieprefix']) );
		// set debug mode
		$this->debug = ! empty ( $this->config ['Misc'] ['debug'] );
		define ( 'DEBUG', $this->debug );
	}

	/**
	 * 对创建一个新定义的常量重复地使用每个标题/数据，组成一个数组的内容。
	 */
	public function array_define($array) {
		foreach ( $array as $title => $data ) {
			if (is_array ( $data )) {
				Registry::array_define ( $data );
			} else {
				define ( strtoupper ( $title ), $data );
			}
		}
	}
}

// #############################################################################
// session 会话管理类


/**
 * 处理 sessions 类
 *
 * 创建更新，并验证会话 ； 检索浏览用户的用户信息
 *
 */
class Session {
	/**
	 * 个别会话变量。 从过去到 $session 等效。
	 *
	 * @public	array
	 */
	public $vars = array ();

	/**
	 * 数据库中的变量 $var 成员中的列表。 包括它们的类型。
	 *
	 * @public	array
	 */
	private $db_fields = array ('sessionhash' => TYPE_STR, 'userid' => TYPE_INT, 'host' => TYPE_STR, 'adminid' => TYPE_INT, 'idhash' => TYPE_STR, 'lastactivity' => TYPE_INT, 'location' => TYPE_STR, 'loggedin' => TYPE_INT, 'useragent' => TYPE_STR, 'bypass' => TYPE_INT );

	/**
	 * 更改的数组。 用于防止多余的更新进行。
	 *
	 * @public	array
	 */
	public $changes = array ();

	/**
	 * 会话是创建的还是以前存在的
	 *
	 * @public	bool
	 */
	public $created = false;

	/**
	 * 一个  Registry 对象的引用 ,保持我们需要的各种数据。
	 *
	 * @public	Registry
	 */
	public $registry = null;

	/**
	 * 有关此会话所属的用户的信息。
	 *
	 * @public	array
	 */
	public $userinfo = null;

	/**
	 * Is the sessionhash to be passed through URLs?
	 *
	 * @public	boolean
	 */
	public $visible = true;

	/**
	 * 构造函数。 尝试获取一个会话匹配参数，但如果不能,将创建一个。
	 *
	 * @param	Registry	对注册表对象的引用
	 * @param	string		预先指定的 sessionhash
	 * @param	integer		用户 ID (在通过一个 Cookie)
	 * @param	string		密码, 必须得到 cookie 格式: md5(md5(md5(password) . salt) . 'abcd1234')
	 */
	public function __construct(&$registry, $sessionhash = '', $userid = 0, $password = '') {
		$userid = intval ( $userid );

		$this->registry = & $registry;
		$db = & $this->registry->db;
		$gotsession = false;

		if (! defined ( 'SESSION_IDHASH' )) {
			define ( 'SESSION_IDHASH', md5 ( $_SERVER ['HTTP_USER_AGENT'] . $this->fetch_substr_ip ( $registry->alt_ip ) ) ); // this should *never* change during a session
		}

		// sessionhash 指定, 如果它已经存在
		if ($sessionhash and ! defined ( 'SKIP_SESSIONCREATE' )) {
			$session = $db->query_first_slave ( "
				SELECT *
				FROM " . TABLE_PREFIX . "session
				WHERE sessionhash = '" . $db->escape_string ( $sessionhash ) . "'
					AND lastactivity > " . (TIMENOW - $registry->options ['cookietimeout'] * 60) . "
					AND idhash = '" . $this->registry->db->escape_string ( SESSION_IDHASH ) . "'
			" );
			if ($session and $this->fetch_substr_ip ( $session ['host'] ) == $this->fetch_substr_ip ( SESSION_HOST )) {
				$gotsession = true;
				$this->vars = & $session;
				$this->created = false;

				// 发现一个 session - 获取用户信息
				if ($session ['userid'] != 0) {
					$useroptions = (defined ( 'GET_USER_RANK' ) ? FETCH_USERINFO_RANK : 0);
					$userinfo = fetch_userinfo ( $session ['userid'], $useroptions );
					$this->userinfo = & $userinfo;
				}
			}
		}

		// 或者也许我们可以使用 Cookie
		if (($gotsession == false or empty ( $session ['userid'] )) and $userid and $password and ! defined ( 'SKIP_SESSIONCREATE' )) {
			$useroptions = (defined ( 'GET_USER_RANK' ) ? FETCH_USERINFO_RANK : 0);
			$userinfo = fetch_userinfo ( $userid, $useroptions );

			if (md5 ( $userinfo ['password'] . COOKIE_SALT ) == $password) {
				$gotsession = true;

				// 组合有效
				if (! empty ( $session ['sessionhash'] )) {
					// 旧会话仍存在 , 杀死它。
					$db->shutdown_query ( "
						DELETE FROM " . TABLE_PREFIX . "session
						WHERE sessionhash = '" . $this->registry->db->escape_string ( $session ['sessionhash'] ) . "'
					" );
				}

				$this->vars = $this->fetch_session ( $userinfo ['userid'] );
				$this->created = true;

				$this->userinfo = & $userinfo;
			}
		}

		// 在此时我们是一位客人，所以让尝试 * 查找 ＊ 会话
		// 可以防止这种检查从正在运行的没有密码的 userid 传入
		if ($gotsession == false and $userid == 0 and ! defined ( 'SKIP_SESSIONCREATE' )) {
			if ($session = $db->query_first_slave ( 'SELECT * FROM ' . TABLE_PREFIX . 'session' . " WHERE userid = 0 AND host = '" . $this->registry->db->escape_string ( SESSION_HOST ) . "' AND idhash = '" . $this->registry->db->escape_string ( SESSION_IDHASH ) . "'" )) {
				$gotsession = true;

				$this->vars = & $session;
				$this->created = false;
			}
		}

		// 有没有什么工作，创建一个新的会话
		if ($gotsession == false) {
			$gotsession = true;

			$this->vars = $this->fetch_session ( 0 );
			$this->created = true;
		}

		$this->vars ['dbsessionhash'] = $this->vars ['sessionhash'];

		if ($this->created == false) {
			$this->set ( 'useragent', USER_AGENT );
			$this->set ( 'lastactivity', TIMENOW );
			if (! defined ( 'LOCATION_BYPASS' )) {
				$this->set ( 'location', WOLPATH );
			}
			$this->set ( 'bypass', SESSION_BYPASS );
		}
	}

	/**
	 * 通过插入或更新现有保存到数据库的会话。
	 */
	public function save() {
		if (defined ( 'SKIP_SESSIONCREATE' )) {
			return;
		}

		$cleaned = $this->build_query_array ();

		//  sessionhash 可能无效, 从 "dbsessionhash" 读取
		$cleaned ['sessionhash'] = "'" . $this->registry->db->escape_string ( $this->vars ['dbsessionhash'] ) . "'";

		if ($this->created == true) {
			/*insert 查询*/
			$this->registry->db->query_write ( "
				INSERT IGNORE INTO " . TABLE_PREFIX . "session
					(" . implode ( ', ', array_keys ( $cleaned ) ) . ")
				VALUES
					(" . implode ( ', ', $cleaned ) . ")
			" );
		} else {
			// update 查询


			unset ( $this->changes ['sessionhash'] ); // 此 sessionhash 不可更新
			$update = array ();
			foreach ( $cleaned as $key => $value ) {
				if (! empty ( $this->changes ["$key"] )) {
					$update [] = "$key = $value";
				}
			}

			if (count ( $update ) > 0) {
				// 注意： $cleaned['sessionhash'] 在上面需要时已被转义 !
				$this->registry->db->query_write ( "
					UPDATE " . TABLE_PREFIX . "session
					SET " . implode ( ', ', $update ) . "
					WHERE sessionhash = $cleaned[sessionhash]
				" );
			}
		}

		$this->changes = array ();

	}

	/**
	 * 创建一个 可用于建立插入/更新该会话查询 的数组
	 *
	 * @return	array	列名称的数组 => 准备的值
	 */
	private function build_query_array() {
		$return = array ();
		foreach ( $this->db_fields as $fieldname => $cleantype ) {
			switch ($cleantype) {
				case TYPE_INT :
					$cleaned = intval ( $this->vars ["$fieldname"] );
					break;
				case TYPE_STR :
				default :
					$cleaned = "'" . $this->registry->db->escape_string ( $this->vars ["$fieldname"] ) . "'";
			}
			$return ["$fieldname"] = $cleaned;
		}

		return $return;
	}

	/**
	 * 设置会话变量，并更新更改列表。
	 *
	 * @param	string	要更新的会话变量的名称
	 * @param	string	要更新它的值
	 */
	public function set($key, $value) {
		if (! isset ( $this->vars ["$key"] ) or $this->vars ["$key"] != $value) {
			$this->vars ["$key"] = $value;
			$this->changes ["$key"] = true;
		}
	}

	/**
	 * 设置会话可见性 （会话信息是否在 URL 中显示）。 true,更新放入  $vars 成员变量中。
	 *
	 * @param	bool	会话元素是否可见。
	 */
	public function set_session_visibility($invisible) {
		$this->visible = ! $invisible;

		if ($invisible) {
			$this->vars ['sessionhash'] = '';
			$this->vars ['sessionurl'] = '';
			$this->vars ['sessionurl_q'] = '';
			$this->vars ['sessionurl_js'] = '';
		} else {
			$this->vars ['sessionurl'] = 's=' . $this->vars ['dbsessionhash'] . '&amp;';
			$this->vars ['sessionurl_q'] = '?s=' . $this->vars ['dbsessionhash'];
			$this->vars ['sessionurl_js'] = 's=' . $this->vars ['dbsessionhash'] . '&';
		}
	}

	/**
	 * 获取一个的有效 sessionhash 值,不一定是与此会话相关的。
	 *
	 * @return	string	32 个字符 sessionhash
	 */
	public function fetch_sessionhash() {
		return md5 ( uniqid ( microtime (), true ) );
	}

	/**
	 * 返回一个移除指定八位字节数目的 IP 地址,用于 生成 IP 范围，例：192.168.1
	 *
	 * @param	string	IP 地址
	 *
	 * @return	string	截断的IP地址
	 */
	public function fetch_substr_ip($ip, $length = null) {
		if ($length === null or $length > 3) {
			$length = $this->registry->options ['ipcheck'];
		}
		return implode ( '.', array_slice ( explode ( '.', $ip ), 0, 4 - $length ) );
	}

	/**
	 * 获取一个默认会话。 使用时创建一个新的会话。
	 *
	 * @param	integer	应为该会话的用户 ID
	 * @param	integer	应为该会话的管理员 ID
	 *
	 * @return	array	会话变量的数组
	 */
	public function fetch_session($userid = 0, $adminid = 0) {
		$sessionhash = $this->fetch_sessionhash ();
		if (! defined ( 'SKIP_SESSIONCREATE' )) {
			skyuc_setcookie ( 'sessionhash', $sessionhash, false, false, true );
		}

		return array ('sessionhash' => $sessionhash, 'dbsessionhash' => $sessionhash, 'userid' => intval ( $userid ), 'host' => SESSION_HOST, 'adminid' => intval ( $adminid ), 'idhash' => SESSION_IDHASH, 'lastactivity' => TIMENOW, 'location' => defined ( 'LOCATION_BYPASS' ) ? '' : WOLPATH, 'loggedin' => intval ( $userid ) ? 1 : 0, 'useragent' => USER_AGENT, 'bypass' => SESSION_BYPASS );

	}

	/**
	 * 返回适用于此会话的所有者的用户信息。
	 *
	 * @return	array	用户信息的数组。
	 */
	public function &fetch_userinfo() {
		if ($this->userinfo) {
			//用户信息已存在
			return $this->userinfo;
		} else if ($this->vars ['userid'] and ! defined ( 'SKIP_USERINFO' )) {
			//用户已登录
			$useroptions = (defined ( 'IN_USER_PANEL' ) ? FETCH_USERINFO_RANK : 0);
			$this->userinfo = fetch_userinfo ( $this->vars ['userid'], $useroptions );
			return $this->userinfo;
		} else {
			// 设置游客
			$this->userinfo = array ('userid' => 0, 'user_rank' => 0, 'user_name' => (! empty ( $_REQUEST ['username'] ) ? htmlspecialchars_uni ( $_REQUEST ['username'] ) : ''), 'password' => '', 'email' => '', 'lastactivity' => $this->vars ['lastactivity'], 'timezoneoffset' => $this->registry->options ['timezoneoffset'], 'securitytoken' => 'guest', 'securitytoken_raw' => 'guest' );

			return $this->userinfo;
		}
	}

	/**
	 * 更新游客和注册用户的最后访问和更新活动时间（两者不同） 。
	 * 只在某段时间已失效时，最后一次访问被设置为上次活动时间 (之前它已更新）。
	 * 最后活动已设为指定时间。
	 *
	 * @param	integer	最后访问时间的时间戳 (只适用于游客)
	 * @param	integer	最后活动时间 的时间戳(只适用于游客)
	 */
	public function do_lastvisit_update($lastvisit = 0, $lastactivity = 0) {
		// 更新最后一次访问/活动东西
		if ($this->vars ['userid'] == 0) {
			// 游客 -- 通过 Cookie 模拟 注册用户 最后访问/活动
			if ($lastvisit) {
				// 我们以前来过这里
				$this->userinfo ['lastvisit'] = intval ( $lastvisit );
				$this->userinfo ['lastactivity'] = ($lastvisit ? intval ( $lastvisit ) : TIMENOW);

				// 这里的模拟
				if (TIMENOW - $this->userinfo ['lastactivity'] > ($this->registry->options ['cookietimeout'] * 60)) {
					$this->userinfo ['lastvisit'] = $this->userinfo ['lastactivity'];

					skyuc_setcookie ( 'lastvisit', $this->userinfo ['lastactivity'] );
				}
			} else {
				// 第一次访问 ！
				$this->userinfo ['lastactivity'] = TIMENOW;
				$this->userinfo ['lastvisit'] = TIMENOW;

				skyuc_setcookie ( 'lastvisit', TIMENOW );
			}
			skyuc_setcookie ( 'lastactivity', $lastactivity );
		} else {
			// 注册的用户
			if (! SESSION_BYPASS) {
				if (TIMENOW - $this->userinfo ['lastactivity'] > ($this->registry->options ['cookietimeout'] * 60)) {
					// 如果会话 '过期' 或者需要更新会话
					$this->registry->db->shutdown_query ( '
						UPDATE ' . TABLE_PREFIX . 'users
						SET
							lastvisit = lastactivity,
							lastactivity = ' . TIMENOW . '
						WHERE user_id = ' . $this->userinfo ['userid'] . '
					', 'lastvisit' );

					$this->userinfo ['lastvisit'] = $this->userinfo ['lastactivity'];
				} else {
					// 如果该行被删除，表示将取代计划作业，您将需要改变所有的'在线'状态指示器，
					// 因为它们使用$userinfo['lastactivity']以确定如果用户在线将依靠这实时更新状态。
					$this->registry->db->shutdown_query ( "
						UPDATE " . TABLE_PREFIX . "user
						SET lastactivity = " . TIMENOW . "
						WHERE userid = " . $this->userinfo ['userid'] . "
					", 'lastvisit' );
				}
			}
		}
	}

	public function destroy_session() {

		skyuc_setcookie ( 'sessionhash', '', false, false, true );

		$this->registry->db->query_write ( 'DELETE FROM ' . TABLE_PREFIX . "session WHERE sessionhash = '" . $this->registry->db->escape_string ( $this->vars ['dbsessionhash'] ) . "'" );

	}

}

/**
 * 关闭处理类
 *
 */
class Shutdown {
	private $shutdown = array ();

	/**
	 * 构造函数，为空
	 */
	function __construct() {
	}

	/**
	 * 使用此函数实例化此类
	 *
	 * @return	Shutdown
	 */
	static function &init() {
		static $instance;

		if (! $instance) {
			$instance = new Shutdown ();
			// PHP4没有__destruct析构函数，所以要注册一个关闭函数
			if (PHP_VERSION < '5.0.5') {
				register_shutdown_function ( array (&$instance, '__destruct' ) );
			}
		}

		return $instance;
	}

	/**
	 * 添加要在关闭时执行的函数
	 *
	 * @param	string	关闭时执行函数的名称
	 */
	function add($function) {
		$obj = & Shutdown::init ();
		if (function_exists ( $function ) and ! in_array ( $function, $obj->shutdown )) {
			$obj->shutdown [] = $function;
		}
	}

	// 仅在一个对象被销毁时调用, 因此 $this 是适当的
	function __destruct() {
		if (! empty ( $this->shutdown )) {
			foreach ( $this->shutdown as $key => $funcname ) {
				$funcname ();
				unset ( $this->shutdown [$key] );
			}
		}
	}
}

// #############################################################################
// 杂项函数库


// #############################################################################
/**
 * 捕捉数据库连接错误信息
 *
 * @param	integer	错误号
 * @param	string	PHP 的错误文本字符串
 * @param	strig		包含错误的文件
 * @param	integer	在包含错误的文件中的行
 */
function catch_db_error($errno, $errstr, $errfile, $errline) {
	global $db;
	static $failures;

	if (strstr ( $errstr, 'Lost connection' ) and $failures < 5) {
		$failures ++;
		return;
	}

	if (is_object ( $db )) {
		$db->halt ( "$errstr\r\n$errfile on line $errline" );
	} else {
		skyuc_error_handler ( $errno, $errstr, $errfile, $errline );
	}
}

// #############################################################################
/**
 * 在任何错误显示中移除全部路径
 *
 * @param	integer	错误号
 * @param	string	PHP 的错误文本字符串
 * @param	strig		包含错误的文件
 * @param	integer	在包含错误的文件中的行
 */
function skyuc_error_handler($errno, $errstr, $errfile, $errline) {
	global $skyuc;

	switch ($errno) {
		case E_WARNING :
		case E_USER_WARNING:
			/* 由于我们抑制，不记录有效警告有关的虚假错误报告,但仍出现在日志中
			require_once(DIR . '/includes/functions_log_error.php');
			$message = "Warning: $errstr in $errfile on line $errline";
			log_skyuc_error($message, 'php');
			*/

			if (! error_reporting () or ! ini_get ( 'display_errors' )) {
				return;
			}
			$errfile = str_replace ( DIR, '[path]', $errfile );
			$errstr = str_replace ( DIR, '[path]', $errstr );
			echo "<br /><strong>Warning</strong>: $errstr in <strong>$errfile</strong> on line <strong>$errline</strong><br />";
			break;

		case E_USER_ERROR :
			require_once (DIR . '/includes/functions_log_error.php');
			$message = "Fatal error: $errstr in $errfile on line $errline";
			log_skyuc_error ( $message, 'php' );

			if (! headers_sent ()) {
				if (SAPI_NAME == 'cgi' or SAPI_NAME == 'cgi-fcgi') {
					header ( 'Status: 500 Internal Server Error' );
				} else {
					header ( 'HTTP/1.1 500 Internal Server Error' );
				}
			}

			if (error_reporting () or ini_get ( 'display_errors' )) {
				$errfile = str_replace ( DIR, '[path]', $errfile );
				$errstr = str_replace ( DIR, '[path]', $errstr );
				echo "<br /><strong>Fatal error:</strong> $errstr in <strong>$errfile</strong> on line <strong>$errline</strong><br />";
				if (function_exists ( 'debug_print_backtrace' )) {
					// 这是必要的， IE 不显示漂亮的错误消息
					echo str_repeat ( ' ', 512 );
					debug_print_backtrace ();
				}
			}
			exit ();
			break;
	}
}

// #############################################################################
/**
 * htmlspecialchars() 的 Unicode 安全版本,将特殊字符转成 HTML 格式。
 *
 * @param	string	进行 html 安全的文本
 * @param  boolen	是否显示实体，即&gt;、&gt;等不转换为<、> （true不转换，false转换）
 *
 * @return	string
 */
function htmlspecialchars_uni($text, $entities = true) {
	return str_replace ( // 替换特殊 HTML 字符
array ('<', '>', '"' ), array ('&lt;', '&gt;', '&quot;' ), // 转换为所有非 Unicode 实体
preg_replace ( '/&(?!' . ($entities ? '#[0-9]+|shy' : '(#[0-9]+|[a-z]+)') . ';)/si', '&amp;', $text ) );
}
