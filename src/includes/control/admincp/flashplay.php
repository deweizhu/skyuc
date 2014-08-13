<?php
// #######################################################################
// ######################## flashplay.php 私有函数    ####################
// #######################################################################


/**
 * 获取轮播图片XML列表
 *
 * @access  public
 *
 * @return array()
 */

function get_flash_xml() {
	$flashdb = array ();
	if (file_exists ( DIR . '/data/flash_data.xml' )) {
		// 兼容v3.0.5及以前版本
		if (! preg_match_all ( '/item_url="([^"]*)"\slink="([^"]+)"\stext="([^"]*)"\ssort="([^"]*)"\stitle="([^"]*)"\stype="([^"]*)"/', file_get_contents ( DIR . '/data/flash_data.xml' ), $t, PREG_SET_ORDER )) {
			preg_match_all ( '/item_url="([^"]*)"\slink="([^"]+)"\stext="([^"]*)"/', file_get_contents ( DIR . '/data/flash_data.xml' ), $t, PREG_SET_ORDER );
		}
		if (! empty ( $t )) {
			foreach ( $t as $key => $val ) {
				$val [4] = isset ( $val [4] ) ? $val [4] : 0;
				$val [5] = isset ( $val [5] ) ? $val [5] : '';
				$val [6] = isset ( $val [6] ) ? $val [6] : 1;
				$flashdb [] = array ('src' => $val [1], 'url' => $val [2], 'text' => $val [3], 'sort' => $val [4], 'title' => $val [5], 'type' => $val [6] );
			}
		}
	}

	return $flashdb;
}
/**
 * 写入轮播图片XML列表
 *
 * @access  public
 *
 * @return
 */
function put_flash_xml($flashdb) {
	if (! empty ( $flashdb )) {
		$xml = '<?xml version="1.0" encoding="utf-8"?><bcaster>';
		foreach ( $flashdb as $key => $val ) {
			$xml .= '<item item_url="' . $val ['src'] . '" link="' . $val ['url'] . '" text="' . $val ['text'] . '" sort="' . $val ['sort'] . '" title="' . $val ['title'] . '" type="' . $val ['type'] . '" />';
		}
		$xml .= '</bcaster>';
		file_put_contents ( DIR . '/data/flash_data.xml', $xml );
	} else {
		@unlink ( DIR . '/data/flash_data.xml' );
	}
}
/**
 * 获取图片地址
 *
 * @access  public
 *
 * @return
 */
function get_url_image($url) {


	$ext = strtolower ( end ( explode ( '.', $url ) ) );
	if ($ext != 'gif' && $ext != 'jpg' && $ext != 'png' && $ext != 'bmp' && $ext != 'jpeg') {
		return $url;
	}

	$name = date ( 'Ymd' );
	for($i = 0; $i < 6; $i ++) {
		$name .= chr ( mt_rand ( 97, 122 ) );
	}
	$name .= '.' . $ext;
	$target = DIR . '/' . $GLOBALS['skyuc']->config ['Misc'] ['imagedir'] . '/afficheimg/' . $name;

	$tmp_file = $GLOBALS['skyuc']->config ['Misc'] ['imagedir'] . '/afficheimg/' . $name;
	$body = fetch_body_request ( $url, '', 'img' );
	file_put_contents ( DIR . '/' . $tmp_file, $body );
	return $tmp_file;
}
/**
 * 获取图片尺寸大小
 *
 * @access  public
 *
 * @return
 */
function get_width_height() {


	$curr_template = $GLOBALS['skyuc']->options ['themes'];
	$path = DIR . '/templates/' . $curr_template . '/';
	$template_dir = @opendir ( $path );

	$width_height = array ();
	while ( $file = readdir ( $template_dir ) ) {
		if ($file == 'index.dwt') {
			$string = file_get_contents ( $path . $file );
			$pattern_width = '/var\s*swf_width\s*=\s*(\d+);/';
			$pattern_height = '/var\s*swf_height\s*=\s*(\d+);/';
			preg_match ( $pattern_width, $string, $width );
			preg_match ( $pattern_height, $string, $height );
			$width_height ['width'] = $width [1];
			$width_height ['height'] = $height [1];
			break;
		}
	}

	return $width_height;
}
/**
 * 获取FLASH模板
 *
 * @access  public
 *
 * @return
 */
function get_flash_templates($dir) {
	$flashtpls = array ();
	$template_dir = @opendir ( $dir );
	while ( $file = readdir ( $template_dir ) ) {
		if ($file != '.' && $file != '..' && is_dir ( $dir . $file ) && $file != '.svn' && $file != 'index.htm') {
			$flashtpls [] = get_flash_tpl_info ( $dir, $file );
		}
	}
	@closedir ( $template_dir );
	return $flashtpls;
}
/**
 * 获取FLASH模板信息
 *
 * @access  public
 *
 * @return
 */
function get_flash_tpl_info($dir, $file) {
	$info = array ();
	if (is_file ( $dir . $file . '/preview.jpg' )) {
		$info ['code'] = $file;
		$info ['screenshot'] = '../data/flashdata/' . $file . '/preview.jpg';
		$arr = array_slice ( file ( $dir . $file . '/cycle_image.js' ), 1, 2 );
		$info_name = explode ( ':', $arr [0] );
		$info_desc = explode ( ':', $arr [1] );
		$info ['name'] = isset ( $info_name [1] ) ? trim ( $info_name [1] ) : '';
		$info ['desc'] = isset ( $info_desc [1] ) ? trim ( $info_desc [1] ) : '';
	}
	return $info;
}
/**
 * 设置FLASH数据
 *
 * @access  public
 *
 * @return
 */
function set_flash_data($tplname, &$msg) {
	$flashdata = get_flash_xml ();
	if (empty ( $flashdata )) {
		$flashdata [] = array ('src' => 'upload/afficheimg/banner.jpg', 'text' => 'SKYUC', 'url' => 'http://www.skyuc.com' );
	}
	switch ($tplname) {
		case 'uproll' :
			$msg = set_flash_uproll ( $tplname, $flashdata );
			break;
		case 'redfocus' :
		case 'pinkfocus' :
		case 'dynfocus' :
			$msg = set_flash_focus ( $tplname, $flashdata );
			break;
		case 'dewei' :
			$msg = set_flash_dewei ( $tplname, $flashdata );
			break;
		case 'default' :
		default :
			$msg = set_flash_default ( $tplname, $flashdata );
			break;
	}
	return $msg !== true;
}
/**
 * 设置FLASH默认数据
 *
 * @access  public
 *
 * @return
 */
function set_flash_uproll($tplname, $flashdata) {
	$data_file = DIR . '/data/flashdata/' . $tplname . '/data.xml';
	$xmldata = '<?xml version="1.0" encoding="utf-8"?><myMenu>';
	foreach ( $flashdata as $data ) {
		$xmldata .= '<myItem pic="' . $data ['src'] . '" url="' . $data ['url'] . '" />';
	}
	$xmldata .= '</myMenu>';
	file_put_contents ( $data_file, $xmldata );
	return true;
}
/**
 * 设置FLASH效果之三种聚焦
 *
 * @access  public
 *
 * @return
 */
function set_flash_focus($tplname, $flashdata) {
	$data_file = DIR . '/data/flashdata/' . $tplname . '/data.js';
	$jsdata = '';
	$jsdata2 = array ('url' => 'var pics=', 'txt' => 'var texts=', 'link' => 'var links=' );
	$count = 1;
	$join = '';
	foreach ( $flashdata as $data ) {
		$jsdata .= 'imgUrl' . $count . '="' . $data ['src'] . '";' . "\n";
		$jsdata .= 'imgtext' . $count . '="' . $data ['text'] . '";' . "\n";
		$jsdata .= 'imgLink' . $count . '=escape("' . $data ['url'] . '");' . "\n";
		if ($count != 1) {
			$join = '+"|"+';
		}
		$jsdata2 ['url'] .= $join . 'imgUrl' . $count;
		$jsdata2 ['txt'] .= $join . 'imgtext' . $count;
		$jsdata2 ['link'] .= $join . 'imgLink' . $count;
		++ $count;
	}
	file_put_contents ( $data_file, $jsdata . "\n" . $jsdata2 ['url'] . ";\n" . $jsdata2 ['link'] . ";\n" . $jsdata2 ['txt'] . ";" );
	return true;
}

/**
 * 设置FLASH效果之dewei
 *
 * @access  public
 *
 * @return
 */
function set_flash_dewei($tplname, $flashdata) {
	$data_file = DIR . '/data/flashdata/' . $tplname . '/data.xml';
	$xmldata = '<?xml version="1.0" encoding="utf-8"?><root>';
	$playlist = $titlelist = $newlist = '';

	foreach ( $flashdata as $data ) {
		if ($data ['type'] == 1) {
			//图片
			$playlist .= '<video><g_re_Title>' . $data ['title'] . '</g_re_Title><url>' . $data ['url'] . '</url><bpic>' . $data ['src'] . '</bpic><g_re_Str>' . $data ['text'] . '</g_re_Str><open>_self</open></video>';
		} elseif ($data ['type'] == 2) {
			//文字
			$titlelist .= '<title><g_re_Title>' . $data ['title'] . '</g_re_Title><url>' . $data ['url'] . '</url><g_re_Str>' . $data ['text'] . '</g_re_Str><open>_self</open></title>';
		} else {
			//链接
			$newlist .= '<new><g_re_Title>' . $data ['title'] . '</g_re_Title><url>' . $data ['url'] . '</url><open>_self</open></new>';
		}
	}
	$xmldata .= '<playlist>' . $playlist . '</playlist>' . '<titlelist>' . $titlelist . '</titlelist>' . '<newlist>' . $newlist . '</newlist>';
	$xmldata .= '</root>';
	file_put_contents ( $data_file, $xmldata );
	return true;
}
/**
 * 设置FLASH效果之默认
 *
 * @access  public
 *
 * @return
 */
function set_flash_default($tplname, $flashdata) {
	$data_file = DIR . '/data/flashdata/' . $tplname . '/data.xml';
	$xmldata = '<?xml version="1.0" encoding="utf-8"?><bcaster>';
	foreach ( $flashdata as $data ) {
		$xmldata .= '<item item_url="' . $data ['src'] . '" link="' . $data ['url'] . '" />';
	}
	$xmldata .= '</bcaster>';
	file_put_contents ( $data_file, $xmldata );
	return true;
}
/**
 * 获取文件后缀名,并判断是否合法
 *
 * @param string $file_name
 * @param array $allow_type
 * @return blob
 */
function get_file_suffix($file_name, $allow_type = array()) {
	$file_suffix = strtolower ( array_pop ( explode ( '.', $file_name ) ) );
	if (empty ( $allow_type )) {
		return $file_suffix;
	} else {
		if (in_array ( $file_suffix, $allow_type )) {
			return true;
		} else {
			return false;
		}
	}
}

?>