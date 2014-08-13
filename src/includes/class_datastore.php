<?php
/**
 * SKYUC 数据库缓冲加速类
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

if (! class_exists ( 'Datastore' )) {
	exit ();
}

/**
 * 从 eAccelerator 获取和初始化数据存储 类
 *
 */
class Datastore_eAccelerator extends Datastore {
	/**
	 * 指示是否注册函数调用的结果应存储在内存中的值
	 *
	 * @var	boolean
	 */
	public $store_result = false;

	/**
	 * 从 eAccelerator 获取数据存储的内容
	 *
	 * @param	array	从数据存储获取的数组项目
	 *
	 * @return	void
	 */
	function fetch($items) {
		if (! function_exists ( 'eaccelerator_get' )) {
			trigger_error ( 'eAccelerator not installed', E_USER_ERROR );
		}

		if (! count ( $items = $this->prepare_itemarray ( $items ) )) {
			return;
		}

		$unfetched_items = array ();
		foreach ( $items as $item ) {
			$this->do_fetch ( $item, $unfetched_items );
		}

		$this->store_result = true;

		// 某些我们正在寻找的项目找不到，允许一次获取
		if (count ( $unfetched_items )) {
			if (! ($result = $this->do_db_fetch ( $this->prepare_itemlist ( $unfetched_items ) ))) {
				return false;
			}
		}

		$this->check_options ();
		return true;
	}

	/**
	 * 从共享内存获取数据和检测错误
	 *
	 * @param	string	数据存储项目标题
	 * @param	array	    失败并需要从数据库提取的数组项目的引用
	 *
	 * @return	boolean
	 */
	function do_fetch($title, &$unfetched_items) {
		$ptitle = $this->prefix . $title;

		if (($data = eaccelerator_get ( $ptitle )) === null) { // 看来它不存在，让抓取数据
			$unfetched_items [] = $title;
			return false;
		}
		$this->register ( $title, $data );
		return true;
	}

	/**
	 * 对从 datastore 表返回的数据进行反解析序列化并将其放入适当的地方
	 *
	 * @param	string	数据处理项目的名称
	 * @param	mixed		数据相关的名称
	 *
	 * @return	void
	 */
	function register($title, $data, $unserialize_detect = 2) {
		if ($this->store_result === true) {
			$this->build ( $title, $data );
		}
		parent::register ( $title, $data, $unserialize_detect );
	}

	/**
	 * 更新适当的缓存文件
	 *
	 * @param	string	数据存储项目标题
	 * @param	mixed		相关数据的名称
	 *
	 * @return	void
	 */
	function build($title, $data) {
		$ptitle = $this->prefix . $title;

		eaccelerator_rm ( $ptitle );
		eaccelerator_put ( $ptitle, $data );
	}
}

// #############################################################################
// Memcached


/**
 * 从一个 Memcache 服务器获取和初始化数据存储的类
 *
 */
class Datastore_Memcached extends Datastore {
	/**
	 * Memcache对象
	 *
	 * @var	Memcache
	 */
	public $memcache = null;

	/**
	 * 若要防止锁定在重新启动 memcached 时，我们要使用添加而不是设置
	 *
	 * @var	boolean
	 */
	public $memcache_set = true;

	/**
	 * 要验证的连接仍然是活动的
	 *
	 * @var	boolean
	 */
	public $memcache_connected = false;

	/**
	 * 指示是否注册函数调用的结果应存储在内存中的值
	 *
	 * @var	boolean
	 */
	public $store_result = false;

	/**
	 * 构造函数 - 建立数据库对象使用的数据存储查询
	 *
	 * @param	Registry	注册对象
	 * @param	Database	数据库对象
	 */
	function __construct(&$registry, &$dbobject) {
		parent::__construct ( $registry, $dbobject );

		if (! class_exists ( 'Memcache' )) {
			trigger_error ( 'Memcache is not installed', E_USER_ERROR );
		}

		$this->memcache = new Memcache ();
	}

	/**
	 * Memcache 连接
	 *
	 * @return	integer	当一个新的连接返回 1 , 如果一个已经存在的连接返回 2
	 */
	function connect() {
		if (! $this->memcache_connected) {
			if (is_array ( $this->registry->config ['Misc'] ['memcacheserver'] )) {
				if (method_exists ( $this->memcache, 'addServer' )) {
					foreach ( array_keys ( $this->registry->config ['Misc'] ['memcacheserver'] ) as $key ) {
						$this->memcache->addServer ( $this->registry->config ['Misc'] ['memcacheserver'] [$key], $this->registry->config ['Misc'] ['memcacheport'] [$key], $this->registry->config ['Misc'] ['memcachepersistent'] [$key], $this->registry->config ['Misc'] ['memcacheweight'] [$key], $this->registry->config ['Misc'] ['memcachetimeout'] [$key], $this->registry->config ['Misc'] ['memcacheretry_interval'] [$key] );
					}
				} else if (! $this->memcache->connect ( $this->registry->config ['Misc'] ['memcacheserver'] [1], $this->registry->config ['Misc'] ['memcacheport'] [1], $this->registry->config ['Misc'] ['memcachetimeout'] [1] )) {
					trigger_error ( 'Unable to connect to memcache server', E_USER_ERROR );
				}
			} else if (! $this->memcache->connect ( $this->registry->config ['Misc'] ['memcacheserver'], $this->registry->config ['Misc'] ['memcacheport'] )) {
				trigger_error ( 'Unable to connect to memcache server', E_USER_ERROR );
			}
			$this->memcache_connected = true;
			return 1;
		}
		return 2;
	}

	/**
	 * Memcache 关闭
	 */
	function close() {
		if ($this->memcache_connected) {
			$this->memcache->close ();
			$this->memcache_connected = false;
		}
	}

	/**
	 * 从Memcache服务器提取数据存储的内容
	 *
	 * @param	array	从数据存储获取的数组阵列项目
	 *
	 * @return	void
	 */
	function fetch($items) {
		$this->connect ();

		$this->memcache_set = false;

		if (! count ( $items = $this->prepare_itemarray ( $items ) )) {
			return;
		}

		$unfetched_items = array ();
		foreach ( $items as $item ) {
			$this->do_fetch ( $item, $unfetched_items );
		}

		$this->store_result = true;

		//某些我们正在寻找的项目找不到，允许一次获取
		if (! empty ( $unfetched_items )) {
			if (! ($result = $this->do_db_fetch ( $this->prepare_itemlist ( $unfetched_items ) ))) {
				return false;
			}
		}

		$this->memcache_set = true;

		$this->check_options ();

		$this->close ();
		return true;

	}

	/**
	 * 从共享内存获取数据和检测错误
	 *
	 * @param	string	要处理的数据项目的名称
	 * @param	array	    失败并需要从数据库提取的数组项目的引用
	 *
	 * @return	boolean
	 */
	function do_fetch($title, &$unfetched_items) {
		$ptitle = $this->prefix . $title;

		if (($data = $this->memcache->get ( $ptitle )) === false) { // 看来它不存在，让抓取数据
			$unfetched_items [] = $title;
			return false;
		}
		$this->register ( $title, $data );
		return true;
	}

	/**
	 * 对从缓存返回的数据进行排序并将其放入适当的地方
	 *
	 * @param	string	要处理的数据项目的名称
	 * @param	mixed		与名称关联的数据
	 *
	 * @return	void
	 */
	function register($title, $data, $unserialize_detect = 2) {
		if ($this->store_result === true) {
			$this->build ( $title, $data );
		}
		parent::register ( $title, $data, $unserialize_detect );
	}

	/**
	 * 更新适当的缓存文件
	 *
	 * @param	string	要处理的数据项目的名称
	 * @param	mixed		与名称关联的数据
	 *
	 * @return	void
	 */
	function build($title, $data) {
		$ptitle = $this->prefix . $title;
		$check = $this->connect ();

		if ($this->memcache_set) {
			$this->memcache->set ( $ptitle, $data, MEMCACHE_COMPRESSED );
		} else {
			$this->memcache->add ( $ptitle, $data, MEMCACHE_COMPRESSED );
		}
		// 如果是一个新的连接，应该关闭它
		if ($check == 1) {
			$this->close ();
		}
	}
}

// #############################################################################
// APC


/**
 * 从 APC 获取和初始化数据存储的类
 *
 */
class Datastore_APC extends Datastore {
	/**
	 * 指示是否注册函数调用的结果应存储在内存中的值
	 *
	 * @var	boolean
	 */
	public $store_result = false;

	/**
	 * 从 APC 提取数据存储中的内容
	 *
	 * @param	array	从数据存储获取的数组阵列项目
	 *
	 * @return	void
	 */
	function fetch($items) {
		if (! function_exists ( 'apc_fetch' )) {
			trigger_error ( 'APC not installed', E_USER_ERROR );
		}

		if (! count ( $items = $this->prepare_itemarray ( $items ) )) {
			return;
		}

		$unfetched_items = array ();
		foreach ( $items as $item ) {
			$this->do_fetch ( $item, $unfetched_items );
		}

		$this->store_result = true;

		// 某些我们正在寻找的项目找不到，允许一次获取
		if (! empty ( $unfetched_items )) {
			if (! ($result = $this->do_db_fetch ( $this->prepare_itemlist ( $unfetched_items ) ))) {
				return false;
			}
		}

		$this->check_options ();
		return true;

	}

	/**
	 * 从共享内存取得数据和检测到错误
	 *
	 * @param	string	数据存储项的标题
	 * @param	array		失败并需要从数据库提取的数组项目的引用
	 *
	 * @return	boolean
	 */
	function do_fetch($title, &$unfetched_items) {
		$ptitle = $this->prefix . $title;

		if (($data = apc_fetch ( $ptitle )) === false) { // 看来它不存在，让获得的数据，锁定共享内存并把它放入
			$unfetched_items [] = $title;
			return false;
		}
		$this->register ( $title, $data );
		return true;
	}

	/**
	 * 对从缓存返回的数据进行排序并将其放入适当的地方
	 *
	 * @param	string	要处理的数据项目的名称
	 * @param	mixed		与名称关联的数据
	 *
	 * @return	void
	 */
	function register($title, $data, $unserialize_detect = 2) {
		if ($this->store_result === true) {
			$this->build ( $title, $data );
		}
		parent::register ( $title, $data, $unserialize_detect );
	}

	/**
	 * 更新适当的缓存文件
	 *
	 * @param	string	要处理的数据项目的名称
	 * @param	mixed		与名称关联的数据
	 *
	 * @return	void
	 */
	function build($title, $data) {
		$ptitle = $this->prefix . $title;

		apc_delete ( $ptitle );
		apc_store ( $ptitle, $data );
	}

}

// #############################################################################
// XCache


/**
 * 从 XCache 获取和初始化数据存储的类
 *
 */
class Datastore_XCache extends Datastore {
	/**
	 * 指示是否注册函数调用的结果应存储在内存中的值
	 *
	 * @var	boolean
	 */
	public $store_result = false;

	/**
	 * 从 XCache 提取数据存储中的内容
	 *
	 * @param	array	要从数据存储中获取的数组项目
	 *
	 * @return	void
	 */
	function fetch($items) {
		if (! function_exists ( 'xcache_get' )) {
			trigger_error ( 'Xcache not installed', E_USER_ERROR );
		}

		if (! ini_get ( 'xcache.var_size' )) {
			trigger_error ( 'Storing of variables is not enabled within XCache', E_USER_ERROR );
		}

		if (! count ( $items = $this->prepare_itemarray ( $items ) )) {
			return;
		}

		$unfetched_items = array ();
		foreach ( $items as $item ) {
			$this->do_fetch ( $item, $unfetched_items );
		}

		$this->store_result = true;

		// 某些我们正在寻找的项目找不到，允许一次获取
		if (sizeof ( $unfetched_items )) {
			if (! ($result = $this->do_db_fetch ( $this->prepare_itemlist ( $unfetched_items ) ))) {
				return false;
			}
		}

		$this->check_options ();
		return true;

	}

	/**
	 * 从共享内存取得数据和检测到错误
	 *
	 * @param	string	数据存储项的标题
	 * @param	array	失败并需要从数据库提取的数组项目的引用
	 *
	 * @return	boolean
	 */
	function do_fetch($title, &$unfetched_items) {
		$ptitle = $this->prefix . $title;

		if (! xcache_isset ( $ptitle )) { // 看来它不存在，让获得的数据，锁定共享内存并把它放入
			$unfetched_items [] = $title;
			return false;
		}
		$data = xcache_get ( $ptitle );
		$this->register ( $title, $data );
		return true;
	}

	/**
	 * 对从缓存返回的数据进行排序并将其放入适当的地方
	 *
	 * @param	string	要处理的数据项目的名称
	 * @param	mixed	     与名称关联的数据
	 *
	 * @return	void
	 */
	function register($title, $data, $unserialize_detect = 2) {
		if ($this->store_result === true) {
			$this->build ( $title, $data );
		}
		parent::register ( $title, $data, $unserialize_detect );
	}

	/**
	 * 更新适当的缓存文件
	 *
	 * @param	string	数据存储项的标题
	 * @param	mixed		与标题关联的数据
	 *
	 * @return	void
	 */
	function build($title, $data) {
		$ptitle = $this->prefix . $title;

		xcache_unset ( $ptitle );
		xcache_set ( $ptitle, $data );
	}

}
// #############################################################################
// Secache 文件缓存


/**
 * 从 Secache 获取和初始化数据存储的类
 *
 */
class Datastore_Filecache extends Datastore {
	/**
	 * 指示是否注册函数调用的结果应存储在内存中的值
	 *
	 * @var	boolean
	 */
	public $store_result = false;

	/**
	 * secache缓存类句柄
	 * @var object
	 */
	private $cache = null;

	/**
	 * 构造函数-建立用于数据存储查询的数据库对象
	 *
	 * @param	Registry	注册表对象
	 * @param	Database	数据库对象
	 */
	function __construct(&$registry, &$dbobject) {
		parent::__construct ( $registry, $dbobject );
		if (SAPI_NAME == 'isapi') {
			$this->cache = new secache_no_flock ();
		} else {
			$this->cache = new secache ();
		}
	}

	/**
	 * 从 APC 提取数据存储中的内容
	 *
	 * @param	array	从数据存储获取的数组阵列项目
	 *
	 * @return	void
	 */
	function fetch($items) {

		if (! count ( $items = $this->prepare_itemarray ( $items ) )) {
			return;
		}

		$unfetched_items = array ();
		foreach ( $items as $item ) {
			$this->do_fetch ( $item, $unfetched_items );
		}

		$this->store_result = true;

		// 某些我们正在寻找的项目找不到，允许一次获取
		if (! empty ( $unfetched_items )) {
			if (! ($result = $this->do_db_fetch ( $this->prepare_itemlist ( $unfetched_items ) ))) {
				return false;
			}
		}

		$this->check_options ();
		return true;

	}

	/**
	 * 从共享内存取得数据和检测到错误
	 *
	 * @param	string	数据存储项的标题
	 * @param	array		失败并需要从数据库提取的数组项目的引用
	 *
	 * @return	boolean
	 */
	function do_fetch($title, &$unfetched_items) {
		$ptitle = $this->prefix . $title;
		$key = md5 ( $ptitle );
		if ($this->cache->fetch ( $key, $data ) === false) {
			$unfetched_items [] = $title;
			return false;
		}
		$this->register ( $title, $data );
		return true;
	}

	/**
	 * 对从缓存返回的数据进行排序并将其放入适当的地方
	 *
	 * @param	string	要处理的数据项目的名称
	 * @param	mixed		与名称关联的数据
	 *
	 * @return	void
	 */
	function register($title, $data, $unserialize_detect = 2) {
		if ($this->store_result === true) {
			$this->build ( $title, $data );
		}
		parent::register ( $title, $data, $unserialize_detect );
	}

	/**
	 * 更新适当的缓存文件
	 *
	 * @param	string	要处理的数据项目的名称
	 * @param	mixed		与名称关联的数据
	 *
	 * @return	void
	 */
	function build($title, $data) {
		$ptitle = $this->prefix . $title;
		$key = md5 ( $ptitle );

		$this->cache->store ( $key, $data );
	}

}

/**
 * secache
 *
 * 基于性能考虑,几点约束
 * 键需要自己做hash处理,前4位是16进制0-f,最长32位。
 * 值必须是字符串。如果要存对象，请自己serialize
 */
if (! defined ( 'FILECACHE_SIZE' )) {
	define ( 'FILECACHE_SIZE', '50M' );
}

class secache {

	public $idx_node_size = 40;
	public $data_base_pos = 262588; //40+20+24*16+16*16*16*16*4;
	public $schema_item_size = 24;
	public $header_padding = 20; //保留空间 放置php标记防止下载
	public $info_size = 20; //保留空间 4+16 maxsize|ver


	//40起 添加20字节保留区域
	public $idx_seq_pos = 40; //id 计数器节点地址
	public $dfile_cur_pos = 44; //id 计数器节点地址
	public $idx_free_pos = 48; //id 空闲链表入口地址


	public $idx_base_pos = 444; //40+20+24*16
	public $min_size = 10240; //10M最小值
	public $schema_struct = array ('size', 'free', 'lru_head', 'lru_tail', 'hits', 'miss' );
	public $ver = '$Rev: 3 $';
	public $name = 'Datastore_Filecache';

	function __construct() {
		$this->workat ( DIR . '/data/caches/cachedata' );
		$statfile = DIR . '/data/caches/cachedata.stat.php';
		if (is_file ( $statfile )) {
			$this->_stat_rs = fopen ( $statfile, 'rb+' );
			$contents = '';
			while ( ! feof ( $this->_stat_rs ) ) {
				$contents .= fread ( $this->_stat_rs, 4096 );
			}
			$this->_vary_list = unserialize ( $contents );
		} else {
			$this->_stat_rs = fopen ( $statfile, 'wb+' );
			$this->_vary_list = array ();
		}
	}

	function setModified($key, $unset = false) {
		if (is_array ( $key )) {
			foreach ( $key as $k ) {
				if ($unset) {
					unset ( $this->_vary_list [strtoupper ( $k )] );
				} else {
					$this->_vary_list [strtoupper ( $k )] = TIMENOW;
				}
			}
		} else {
			if ($unset) {
				unset ( $this->_vary_list [strtoupper ( $key )] );
			} else {
				$this->_vary_list [strtoupper ( $key )] = TIMENOW;
			}
		}
		fseek ( $this->_stat_rs, 0 );
		ftruncate ( $this->_stat_rs, 0 );
		return fputs ( $this->_stat_rs, serialize ( $this->_vary_list ) );
	}
	function getModified($key) {
		return isset ( $this->_vary_list [strtoupper ( $key )] ) ? $this->_vary_list [strtoupper ( $key )] : '';
	}

	function workat($file) {

		$this->_file = $file . '.php';
		$this->_bsize_list = array (512 => 10, 3 << 10 => 10, 8 << 10 => 10, 20 << 10 => 4, 30 << 10 => 2, 50 << 10 => 2, 80 << 10 => 2, 96 << 10 => 2, 128 << 10 => 2, 224 << 10 => 2, 256 << 10 => 2, 512 << 10 => 1, 1024 << 10 => 1 );

		$this->_node_struct = array ('next' => array (0, 'V' ), 'prev' => array (4, 'V' ), 'data' => array (8, 'V' ), 'size' => array (12, 'V' ), 'lru_right' => array (16, 'V' ), 'lru_left' => array (20, 'V' ), 'key' => array (24, 'H*' ) );

		if (! file_exists ( $this->_file )) {
			$this->create ();
		} else {
			$this->_rs = fopen ( $this->_file, 'rb+' ) or $this->trigger_error ( 'Can\'t open the cachefile: ' . realpath ( $this->_file ), E_USER_ERROR );
			$this->_seek ( $this->header_padding );
			$info = unpack ( 'V1max_size/a*ver', fread ( $this->_rs, $this->info_size ) );
			if ($info ['ver'] != $this->ver) {
				$this->_format ( true );
			} else {
				$this->max_size = $info ['max_size'];
			}
		}

		$this->idx_node_base = $this->data_base_pos + $this->max_size;
		$this->_block_size_list = array_keys ( $this->_bsize_list );
		sort ( $this->_block_size_list );
		return true;
	}

	function create() {
		$this->_rs = fopen ( $this->_file, 'wb+' ) or $this->trigger_error ( 'Can\'t open the cachefile: ' . realpath ( $this->_file ), E_USER_ERROR );
		;
		fseek ( $this->_rs, 0 );
		fputs ( $this->_rs, '<' . '?php exit()?' . '>' );
		return $this->_format ();
	}

	function _puts($offset, $data) {
		if ($offset < $this->max_size * 1.5) {
			$this->_seek ( $offset );
			return fputs ( $this->_rs, $data );
		} else {
			$this->trigger_error ( 'Offset over quota:' . $offset, E_USER_ERROR );
		}
	}

	function _seek($offset) {
		return fseek ( $this->_rs, $offset );
	}

	function clear() {
		return $this->_format ( true );
	}

	function fetch($key, &$return) {

		if ($this->lock ( false )) {
			$locked = true;
		}

		if ($this->search ( $key, $offset )) {
			$info = $this->_get_node ( $offset );
			$schema_id = $this->_get_size_schema_id ( $info ['size'] );
			if ($schema_id === false) {
				if ($locked)
					$this->unlock ();
				return false;
			}

			$this->_seek ( $info ['data'] );
			$data = fread ( $this->_rs, $info ['size'] );
			$return = unserialize ( $data );

			if ($return === false) {
				if ($locked)
					$this->unlock ();
				return false;
			}

			if ($locked) {
				$this->_lru_push ( $schema_id, $info ['offset'] );
				$this->_set_schema ( $schema_id, 'hits', $this->_get_schema ( $schema_id, 'hits' ) + 1 );
				return $this->unlock ();
			} else {
				return true;
			}
		} else {
			if ($locked)
				$this->unlock ();
			return false;
		}
	}

	/**
	 * lock
	 * 如果flock不管用，请继承本类，并重载此方法
	 *
	 * @param mixed $is_block 是否阻塞
	 * @access public
	 * @return void
	 */
	function lock($is_block, $whatever = false) {
		return flock ( $this->_rs, $is_block ? LOCK_EX : LOCK_EX + LOCK_NB );
	}

	/**
	 * unlock
	 * 如果flock不管用，请继承本类，并重载此方法
	 *
	 * @access public
	 * @return void
	 */
	function unlock() {
		return flock ( $this->_rs, LOCK_UN );
	}

	function delete($key, $pos = false) {
		if ($pos || $this->search ( $key, $pos )) {
			if ($info = $this->_get_node ( $pos )) {
				//删除data区域
				if ($info ['prev']) {
					$this->_set_node ( $info ['prev'], 'next', $info ['next'] );
					$this->_set_node ( $info ['next'], 'prev', $info ['prev'] );
				} else { //改入口位置
					$this->_set_node ( $info ['next'], 'prev', 0 );
					$this->_set_node_root ( $key, $info ['next'] );
				}
				$this->_free_dspace ( $info ['size'], $info ['data'] );
				$this->_lru_delete ( $info );
				$this->_free_node ( $pos );
				return $info ['prev'];
			}
		}
		return false;
	}

	function store($key, $value) {

		if ($this->lock ( true )) {
			//save data
			$data = serialize ( $value );
			$size = strlen ( $data );

			//get list_idx
			$has_key = $this->search ( $key, $list_idx_offset );
			$schema_id = $this->_get_size_schema_id ( $size );
			if ($schema_id === false) {
				$this->unlock ();
				return false;
			}
			if ($has_key) {
				$hdseq = $list_idx_offset;

				$info = $this->_get_node ( $hdseq );
				if ($schema_id == $this->_get_size_schema_id ( $info ['size'] )) {
					$dataoffset = $info ['data'];
				} else {
					//破掉原有lru
					$this->_lru_delete ( $info );
					if (! ($dataoffset = $this->_dalloc ( $schema_id ))) {
						$this->unlock ();
						return false;
					}
					$this->_free_dspace ( $info ['size'], $info ['data'] );
					$this->_set_node ( $hdseq, 'lru_left', 0 );
					$this->_set_node ( $hdseq, 'lru_right', 0 );
				}

				$this->_set_node ( $hdseq, 'size', $size );
				$this->_set_node ( $hdseq, 'data', $dataoffset );
			} else {

				if (! ($dataoffset = $this->_dalloc ( $schema_id ))) {
					$this->unlock ();
					return false;
				}
				$hdseq = $this->_alloc_idx ( array ('next' => 0, 'prev' => $list_idx_offset, 'data' => $dataoffset, 'size' => $size, 'lru_right' => 0, 'lru_left' => 0, 'key' => $key ) );

				if ($list_idx_offset > 0) {
					$this->_set_node ( $list_idx_offset, 'next', $hdseq );
				} else {
					$this->_set_node_root ( $key, $hdseq );
				}
			}

			if ($dataoffset > $this->max_size) {
				$this->trigger_error ( 'alloc datasize:' . $dataoffset, E_USER_WARNING );
				return false;
			}
			$this->_puts ( $dataoffset, $data );

			$this->_set_schema ( $schema_id, 'miss', $this->_get_schema ( $schema_id, 'miss' ) + 1 );

			$this->_lru_push ( $schema_id, $hdseq );
			$this->unlock ();
			return true;
		} else {
			$this->trigger_error ( "Couldn't lock the file !", E_USER_WARNING );
			return false;
		}

	}

	/**
	 * search
	 * 查找指定的key
	 * 如果找到节点则$pos=节点本身 返回true
	 * 否则 $pos=树的末端 返回false
	 *
	 * @param mixed $key
	 * @access public
	 * @return void
	 */
	function search($key, &$pos) {
		return $this->_get_pos_by_key ( $this->_get_node_root ( $key ), $key, $pos );
	}

	function _get_size_schema_id($size) {
		foreach ( $this->_block_size_list as $k => $block_size ) {
			if ($size <= $block_size) {
				return $k;
			}
		}
		return false;
	}

	function _parse_str_size($str_size, $default) {
		if (preg_match ( '/^([0-9]+)\s*([gmk]|)$/i', $str_size, $match )) {
			switch (strtolower ( $match [2] )) {
				case 'g' :
					if ($match [1] > 1) {
						$this->trigger_error ( 'Max cache size 1G', E_USER_ERROR );
					}
					$size = $match [1] << 30;
					break;
				case 'm' :
					$size = $match [1] << 20;
					break;
				case 'k' :
					$size = $match [1] << 10;
					break;
				default :
					$size = $match [1];
			}
			if ($size <= 0) {
				$this->trigger_error ( 'Error cache size ' . $this->max_size, E_USER_ERROR );
				return false;
			} elseif ($size < 10485760) {
				return 10485760;
			} else {
				return $size;
			}
		} else {
			return $default;
		}
	}

	function _format($truncate = false) {
		if ($this->lock ( true, true )) {

			if ($truncate) {
				$this->_seek ( 0 );
				ftruncate ( $this->_rs, $this->idx_node_base );
			}

			$this->max_size = $this->_parse_str_size ( FILECACHE_SIZE, 15728640 ); //default:15m
			$this->_puts ( $this->header_padding, pack ( 'V1a*', $this->max_size, $this->ver ) );

			ksort ( $this->_bsize_list );
			$ds_offset = $this->data_base_pos;
			$i = 0;
			foreach ( $this->_bsize_list as $size => $count ) {

				//将预分配的空间注册到free链表里
				$count *= min ( 3, floor ( $this->max_size / 10485760 ) );
				$next_free_node = 0;
				for($j = 0; $j < $count; $j ++) {
					$this->_puts ( $ds_offset, pack ( 'V', $next_free_node ) );
					$next_free_node = $ds_offset;
					$ds_offset += intval ( $size );
				}

				$code = pack ( str_repeat ( 'V1', count ( $this->schema_struct ) ), $size, $next_free_node, 0, 0, 0, 0 );

				$this->_puts ( 60 + $i * $this->schema_item_size, $code );
				$i ++;
			}
			$this->_set_dcur_pos ( $ds_offset );

			$this->_puts ( $this->idx_base_pos, str_repeat ( "\0", 262144 ) );
			$this->_puts ( $this->idx_seq_pos, pack ( 'V', 1 ) );
			$this->unlock ();
			return true;
		} else {
			$this->trigger_error ( "Couldn't lock the file !", E_USER_ERROR );
			return false;
		}
	}

	function _get_node_root($key) {
		$this->_seek ( hexdec ( substr ( $key, 0, 4 ) ) * 4 + $this->idx_base_pos );
		$a = fread ( $this->_rs, 4 );
		list ( , $offset ) = unpack ( 'V', $a );
		return $offset;
	}

	function _set_node_root($key, $value) {
		return $this->_puts ( hexdec ( substr ( $key, 0, 4 ) ) * 4 + $this->idx_base_pos, pack ( 'V', $value ) );
	}

	function _set_node($pos, $key, $value) {

		if (! $pos) {
			return false;
		}

		if (isset ( $this->_node_struct [$key] )) {
			return $this->_puts ( $pos * $this->idx_node_size + $this->idx_node_base + $this->_node_struct [$key] [0], pack ( $this->_node_struct [$key] [1], $value ) );
		} else {
			return false;
		}
	}

	function _get_pos_by_key($offset, $key, &$pos) {
		if (! $offset) {
			$pos = 0;
			return false;
		}

		$info = $this->_get_node ( $offset );

		if ($info ['key'] == $key) {
			$pos = $info ['offset'];
			return true;
		} elseif ($info ['next'] && $info ['next'] != $offset) {
			return $this->_get_pos_by_key ( $info ['next'], $key, $pos );
		} else {
			$pos = $offset;
			return false;
		}
	}

	function _lru_delete($info) {

		if ($info ['lru_right']) {
			$this->_set_node ( $info ['lru_right'], 'lru_left', $info ['lru_left'] );
		} else {
			$this->_set_schema ( $this->_get_size_schema_id ( $info ['size'] ), 'lru_tail', $info ['lru_left'] );
		}

		if ($info ['lru_left']) {
			$this->_set_node ( $info ['lru_left'], 'lru_right', $info ['lru_right'] );
		} else {
			$this->_set_schema ( $this->_get_size_schema_id ( $info ['size'] ), 'lru_head', $info ['lru_right'] );
		}

		return true;
	}

	function _lru_push($schema_id, $offset) {
		$lru_head = $this->_get_schema ( $schema_id, 'lru_head' );
		$lru_tail = $this->_get_schema ( $schema_id, 'lru_tail' );

		if ((! $offset) || ($lru_head == $offset))
			return;

		$info = $this->_get_node ( $offset );

		$this->_set_node ( $info ['lru_right'], 'lru_left', $info ['lru_left'] );
		$this->_set_node ( $info ['lru_left'], 'lru_right', $info ['lru_right'] );

		$this->_set_node ( $offset, 'lru_right', $lru_head );
		$this->_set_node ( $offset, 'lru_left', 0 );

		$this->_set_node ( $lru_head, 'lru_left', $offset );
		$this->_set_schema ( $schema_id, 'lru_head', $offset );

		if ($lru_tail == 0) {
			$this->_set_schema ( $schema_id, 'lru_tail', $offset );
		} elseif ($lru_tail == $offset && $info ['lru_left']) {
			$this->_set_schema ( $schema_id, 'lru_tail', $info ['lru_left'] );
		}
		return true;
	}

	function _get_node($offset) {
		$this->_seek ( $offset * $this->idx_node_size + $this->idx_node_base );
		$info = unpack ( 'V1next/V1prev/V1data/V1size/V1lru_right/V1lru_left/H*key', fread ( $this->_rs, $this->idx_node_size ) );
		$info ['offset'] = $offset;
		return $info;
	}

	function _lru_pop($schema_id) {
		if ($node = $this->_get_schema ( $schema_id, 'lru_tail' )) {
			$info = $this->_get_node ( $node );
			if (! $info ['data']) {
				return false;
			}
			$this->delete ( $info ['key'], $info ['offset'] );
			if (! $this->_get_schema ( $schema_id, 'free' )) {
				$this->trigger_error ( 'pop lru,But nothing free...', E_USER_ERROR );
			}
			return $info;
		} else {
			return false;
		}
	}

	function _dalloc($schema_id, $lru_freed = false) {

		if ($free = $this->_get_schema ( $schema_id, 'free' )) { //如果lru里有链表
			$this->_seek ( $free );
			list ( , $next ) = unpack ( 'V', fread ( $this->_rs, 4 ) );
			$this->_set_schema ( $schema_id, 'free', $next );
			return $free;
		} elseif ($lru_freed) {
			$this->trigger_error ( 'Bat lru poped freesize', E_USER_ERROR );
			return false;
		} else {
			$ds_offset = $this->_get_dcur_pos ();
			$size = $this->_get_schema ( $schema_id, 'size' );

			if ($size + $ds_offset > $this->max_size) {
				if ($info = $this->_lru_pop ( $schema_id )) {
					return $this->_dalloc ( $schema_id, $info );
				} else {
					$this->trigger_error ( 'Can\'t alloc dataspace', E_USER_ERROR );
					return false;
				}
			} else {
				$this->_set_dcur_pos ( $ds_offset + $size );
				return $ds_offset;
			}
		}
	}

	function _get_dcur_pos() {
		$this->_seek ( $this->dfile_cur_pos );
		list ( , $ds_offset ) = unpack ( 'V', fread ( $this->_rs, 4 ) );
		return $ds_offset;
	}
	function _set_dcur_pos($pos) {
		return $this->_puts ( $this->dfile_cur_pos, pack ( 'V', $pos ) );
	}

	function _free_dspace($size, $pos) {

		if ($pos > $this->max_size) {
			$this->trigger_error ( 'free dspace over quota:' . $pos, E_USER_ERROR );
			return false;
		}

		$schema_id = $this->_get_size_schema_id ( $size );
		if ($free = $this->_get_schema ( $schema_id, 'free' )) {
			$this->_puts ( $free, pack ( 'V1', $pos ) );
		} else {
			$this->_set_schema ( $schema_id, 'free', $pos );
		}
		$this->_puts ( $pos, pack ( 'V1', 0 ) );
	}

	function _dfollow($pos, &$c) {
		$c ++;
		$this->_seek ( $pos );
		list ( , $next ) = unpack ( 'V1', fread ( $this->_rs, 4 ) );
		if ($next) {
			return $this->_dfollow ( $next, $c );
		} else {
			return $pos;
		}
	}

	function _free_node($pos) {
		$this->_seek ( $this->idx_free_pos );
		list ( , $prev_free_node ) = unpack ( 'V', fread ( $this->_rs, 4 ) );
		$this->_puts ( $pos * $this->idx_node_size + $this->idx_node_base, pack ( 'V', $prev_free_node ) . str_repeat ( "\0", $this->idx_node_size - 4 ) );
		return $this->_puts ( $this->idx_free_pos, pack ( 'V', $pos ) );
	}

	function _alloc_idx($data) {
		$this->_seek ( $this->idx_free_pos );
		list ( , $list_pos ) = unpack ( 'V', fread ( $this->_rs, 4 ) );
		if ($list_pos) {

			$this->_seek ( $list_pos * $this->idx_node_size + $this->idx_node_base );
			list ( , $prev_free_node ) = unpack ( 'V', fread ( $this->_rs, 4 ) );
			$this->_puts ( $this->idx_free_pos, pack ( 'V', $prev_free_node ) );

		} else {
			$this->_seek ( $this->idx_seq_pos );
			list ( , $list_pos ) = unpack ( 'V', fread ( $this->_rs, 4 ) );
			$this->_puts ( $this->idx_seq_pos, pack ( 'V', $list_pos + 1 ) );
		}
		return $this->_create_node ( $list_pos, $data );
	}

	function _create_node($pos, $data) {
		$this->_puts ( $pos * $this->idx_node_size + $this->idx_node_base, pack ( 'V1V1V1V1V1V1H*', $data ['next'], $data ['prev'], $data ['data'], $data ['size'], $data ['lru_right'], $data ['lru_left'], $data ['key'] ) );
		return $pos;
	}

	function _set_schema($schema_id, $key, $value) {
		$info = array_flip ( $this->schema_struct );
		return $this->_puts ( 60 + $schema_id * $this->schema_item_size + $info [$key] * 4, pack ( 'V', $value ) );
	}

	function _get_schema($id, $key) {
		$info = array_flip ( $this->schema_struct );

		$this->_seek ( 60 + $id * $this->schema_item_size );
		unpack ( 'V1' . implode ( '/V1', $this->schema_struct ), fread ( $this->_rs, $this->schema_item_size ) );

		$this->_seek ( 60 + $id * $this->schema_item_size + $info [$key] * 4 );
		list ( , $value ) = unpack ( 'V', fread ( $this->_rs, 4 ) );
		return $value;
	}

	function _all_schemas() {
		$schema = array ();
		for($i = 0; $i < 16; $i ++) {
			$this->_seek ( 60 + $i * $this->schema_item_size );
			$info = unpack ( 'V1' . implode ( '/V1', $this->schema_struct ), fread ( $this->_rs, $this->schema_item_size ) );
			if ($info ['size']) {
				$info ['id'] = $i;
				$schema [$i] = $info;
			} else {
				return $schema;
			}
		}
	}

	function schemaStatus() {
		$return = array ();
		foreach ( $this->_all_schemas () as $k => $schemaItem ) {
			if ($schemaItem ['free']) {
				$this->_dfollow ( $schemaItem ['free'], $schemaItem ['freecount'] );
			}
			$return [] = $schemaItem;
		}
		return $return;
	}

	function status(&$curBytes, &$totalBytes) {
		$totalBytes = $curBytes = 0;
		$hits = $miss = 0;

		$schemaStatus = $this->schemaStatus ();
		$totalBytes = $this->max_size;
		$freeBytes = $this->max_size - $this->_get_dcur_pos ();

		foreach ( $schemaStatus as $schema ) {
			$freeBytes += $schema ['freecount'] * $schema ['size'];
			$miss += $schema ['miss'];
			$hits += $schema ['hits'];
		}
		$curBytes = $totalBytes - $freeBytes;

		$return [] = array ('name' => '缓存命中', 'value' => $hits );
		$return [] = array ('name' => '缓存未命中', 'value' => $miss );
		return $return;
	}

	public function trigger_error($errstr, $errno) {
		if ($errno == E_USER_ERROR) {
			if (! $this->_in_fatal_error) {
				$this->_in_fatal_error = true;
				$this->_format ( true );
			}
			header ( "HTTP/1.1 500 Internal Server Error", true, 500 );
			if (function_exists ( "debug_print_backtrace" )) {
				echo "<h1>" . $errstr . "</h1><hr />";
				echo "<pre>";
				debug_print_backtrace ();
				echo "</pre>";
				exit ();
			} else {
				trigger_error ( $errstr, $errno );
			}
		} else {
			trigger_error ( $errstr, $errno );
		}
	}

}

class secache_no_flock extends secache {

	function secache_no_flock() {
		parent::__construct ();
		$this->__support_usleep = version_compare ( PHP_VERSION, 5, '>=' ) ? 20 : 1;
	}

	function lock($is_block, $whatever = false) {

		ignore_user_abort ( 1 );
		$lockfile = $this->_file . '.lck';

		if (file_exists ( $lockfile )) {
			if (time () - filemtime ( $lockfile ) > 5) {
				unlink ( $lockfile );
			} elseif (! $is_block) {
				return false;
			}
		}

		$lock_ex = @fopen ( $lockfile, 'x' );
		for($i = 0; ($lock_ex === false) && ($whatever || $i < 20); $i ++) {
			clearstatcache ();
			if ($this->__support_usleep == 1) {
				usleep ( rand ( 9, 999 ) );
			} else {
				sleep ( 1 );
			}
			$lock_ex = @fopen ( $lockfile, 'x' );
		}

		return ($lock_ex !== false);
	}

	function unlock() {
		ignore_user_abort ( 0 );
		return unlink ( $this->_file . '.lck' );
	}
}
?>