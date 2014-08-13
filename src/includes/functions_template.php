<?php
/**
 * SKYUC! 模版相关公用函数库
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

// 模板页面文件
$template_files = array ('index.dwt', 'article.dwt', 'article_cat.dwt', 'show.dwt', 'list.dwt', 'user_center.dwt', 'user_passport.dwt', 'netbar.dwt', 'respond.dwt', 'search.dwt', 'message_board.dwt' );

// 每个页面允许包含的库项目
$page_libs = array ('article' => array ('/library/ur_here.lbi' => 0, '/library/member' => 0, '/library/history.lbi' => 0, '/library/new10_article.lbi' => 10, '/library/related_article.lbi' => 0, '/library/article_detail.lbi' => 0, '/library/search_form.lbi' => 0, '/library/comments.lbi' => 0, '/library/new10_article.lbi' => 10, '/library/top10_article.lbi' => 10, '/library/article_cate_tree.lbi' => 0 ), 'article_cat' => array ('/library/ur_here.lbi' => 0, '/library/member' => 0, '/library/history.lbi' => 0, '/library/article_cate_tree.lbi' => 0, '/library/article_cate.lbi' => 0, '/library/search_form.lbi' => 0, '/library/pages.lbi' => 0, '/library/new10_article.lbi' => 10, '/library/top10_article.lbi' => 10 ),

'list' => array ('/library/ur_here.lbi' => 0, '/library/top10_cate.lbi' => 10, '/library/new10_cate.lbi' => 10, '/library/show_list.lbi' => 0, '/library/pages.lbi' => 0, '/library/tree_cate.lbi' => 0, '/library/recom_cate.lbi' => 6, '/library/member.lbi' => 0, '/library/history.lbi' => 0, '/library/search_form.lbi' => 0, '/library/tag_cat.lbi' => 0 ),

'index' => array ('/library/new_articles.lbi' => 0, '/library/top10.lbi' => 10, '/library/new10.lbi' => 10, '/library/recom.lbi' => 10, '/library/vote.lbi' => 0, '/library/tree.lbi' => 0, '/library/subject.lbi' => 0, '/library/tag.lbi' => 0, '/library/member.lbi' => 0, '/library/search_form.lbi' => 0 ),

'show' => array ('/library/ur_here.lbi' => 0, '/library/history.lbi' => 0, '/library/top10_detail.lbi' => 10, '/library/new10_detail.lbi' => 10, '/library/related_actor.lbi' => 0, '/library/related_director.lbi' => 0, '/library/same_movie.lbi' => 0, '/library/member.lbi' => 0, '/library/comments.lbi' => 0, '/library/search_form.lbi' => 0, '/library/tree_detail.lbi' => 0, '/library/show_detail.lbi' => 0 ), 'search' => array ('/library/ur_here.lbi' => 0, '/library/top10_cate.lbi' => 10, '/library/new10_cate.lbi' => 10, '/library/show_list.lbi' => 0, '/library/pages.lbi' => 0, '/library/tree_cate.lbi' => 0, '/library/member.lbi' => 0, '/library/history.lbi' => 0, '/library/search_form.lbi' => 0 ), 'message_board' => array ('/library/ur_here.lbi' => 0, '/library/top10_cate.lbi' => 10, '/library/new10_cate.lbi' => 10, '/library/message_list.lbi' => 0, '/library/tree_cate.lbi' => 0, '/library/member.lbi' => 0, '/library/history.lbi' => 0, '/library/search_form.lbi' => 0 ) )

;

// 动态库项目
$dyna_libs = array ('cat_show', 'cat_hot', 'series', 'ad_position' );

/**
 * 获得模版的信息
 *
 * @access  private
 * @param   string      $template_name      模版名
 * @param   string      $template_style     模版风格名
 * @return  array
 */
function get_template_info($template_name, $template_style = '') {
	if (empty ( $template_style ) || $template_style == '') {
		$template_style = '';
	}

	$info = array ();
	$ext = array ('png', 'gif', 'jpg', 'jpeg' );

	$info ['code'] = $template_name;
	$info ['screenshot'] = '';
	$info ['stylename'] = $template_style;

	if ($template_style == '') {
		foreach ( $ext as $val ) {
			if (is_file ( DIR . '/templates/' . $template_name . "/images/screenshot.$val" )) {
				$info ['screenshot'] = '../templates/' . $template_name . "/images/screenshot.$val";

				break;
			}
		}
	} else {
		foreach ( $ext as $val ) {
			if (is_file ( DIR . '/templates/' . $template_name . "/images/screenshot_$template_style.$val" )) {
				$info ['screenshot'] = '../templates/' . $template_name . "/images/screenshot_$template_style.$val";

				break;
			}
		}
	}

	$css_path = DIR . '/templates/' . $template_name . '/style.css';
	if ($template_style != '') {
		$css_path = DIR . '../templates/' . $template_name . "/style_$template_style.css";
	}
	if (is_file ( $css_path ) && ! empty ( $template_name )) {
		$arr = array_slice ( file ( $css_path ), 0, 10 );

		$template_name = explode ( ': ', $arr [1] );
		$template_uri = explode ( ': ', $arr [2] );
		$template_desc = explode ( ': ', $arr [3] );
		$template_version = explode ( ': ', $arr [4] );
		$template_author = explode ( ': ', $arr [5] );
		$author_uri = explode ( ': ', $arr [6] );
		$logo_filename = explode ( ': ', $arr [7] );
		$template_type = explode ( ': ', $arr [8] );

		$info ['name'] = isset ( $template_name [1] ) ? trim ( $template_name [1] ) : '';
		$info ['uri'] = isset ( $template_uri [1] ) ? trim ( $template_uri [1] ) : '';
		$info ['desc'] = isset ( $template_desc [1] ) ? trim ( $template_desc [1] ) : '';
		$info ['version'] = isset ( $template_version [1] ) ? trim ( $template_version [1] ) : '';
		$info ['author'] = isset ( $template_author [1] ) ? trim ( $template_author [1] ) : '';
		$info ['author_uri'] = isset ( $author_uri [1] ) ? trim ( $author_uri [1] ) : '';
		$info ['logo'] = isset ( $logo_filename [1] ) ? trim ( $logo_filename [1] ) : '';
		$info ['type'] = isset ( $template_type [1] ) ? trim ( $template_type [1] ) : '';

	} else {
		$info ['name'] = '';
		$info ['uri'] = '';
		$info ['desc'] = '';
		$info ['version'] = '';
		$info ['author'] = '';
		$info ['author_uri'] = '';
		$info ['logo'] = '';
	}

	return $info;
}

/**
 * 获得模版文件中的编辑区域及其内容
 *
 * @access  public
 * @param   string  $tmp_name   模版名称
 * @param   string  $tmp_file   模版文件名称
 * @return  array
 */
function get_template_region($tmp_name, $tmp_file, $lib = true) {
	global $dyna_libs;

	$file = DIR . '/templates/' . $tmp_name . '/' . $tmp_file;

	// 将模版文件的内容读入内存
	$content = file_get_contents ( $file );

	// 获得所有编辑区域
	static $regions = array ();

	if (empty ( $regions )) {
		$matches = array ();
		$result = preg_match_all ( '/(<!--\\s*TemplateBeginEditable\\sname=")([^"]+)("\\s*-->)/', $content, $matches, PREG_SET_ORDER );

		if ($result && $result > 0) {
			foreach ( $matches as $key => $val ) {
				if ($val [2] != 'doctitle' && $val [2] != 'head') {
					$regions [] = $val [2];
				}
			}
		}

	}

	if (! $lib) {
		return $regions;
	}

	$libs = array ();
	// 遍历所有编辑区
	foreach ( $regions as $key => $val ) {
		$matches = array ();
		$pattern = '/(<!--\\s*TemplateBeginEditable\\sname="%s"\\s*-->)(.*?)(<!--\\s*TemplateEndEditable\\s*-->)/s';

		if (preg_match ( sprintf ( $pattern, $val ), $content, $matches )) {
			/* 找出该编辑区域内所有库项目 */
			$lib_matches = array ();

			$result = preg_match_all ( '/([\s|\S]{0,20})(<!--\\s#BeginLibraryItem\\s")([^"]+)("\\s-->)/', $matches [2], $lib_matches, PREG_SET_ORDER );
			$i = 0;
			if ($result && $result > 0) {
				foreach ( $lib_matches as $k => $v ) {
					$v [3] = strtolower ( $v [3] );
					$libs [] = array ('library' => $v [3], 'region' => $val, 'lib' => basename ( substr ( $v [3], 0, strpos ( $v [3], '.' ) ) ), 'sort_order' => $i );
					$i ++;
				}

			}
		}
	}

	return $libs;
}

/**
 * 获得指定库项目在模板中的设置内容
 *
 * @access  public
 * @param   string  $lib    库项目
 * @param   array   $libs    包含设定内容的数组
 * @return  void
 */
function get_setted($lib, &$arr) {
	$options = array ('region' => '', 'sort_order' => 0, 'display' => 0 );

	foreach ( $arr as $key => $val ) {
		if ($lib == $val ['library']) {
			$options ['region'] = $val ['region'];
			$options ['sort_order'] = $val ['sort_order'];
			$options ['display'] = 1;

			break;
		}
	}

	return $options;
}

/**
 * 自定义数组排序方式
 *
 * @access  public
 * @param   string  $a
 * @param   string  $b
 * @return  array
 */
function array_sort($a, $b) {
	$cmp = strcmp ( $a ['region'], $b ['region'] );

	if ($cmp == 0) {
		return ($a ['sort_order'] < $b ['sort_order']) ? - 1 : 1;
	} else {
		return ($cmp > 0) ? - 1 : 1;
	}
}

/**
 * 载入库项目内容
 *
 * @access  public
 * @param   string  $curr_template  模版名称
 * @param   string  $lib_name       库项目名称
 * @return  array
 */
function load_library($curr_template, $lib_name) {

	$lib_name = str_replace ( "0xa", '', $lib_name ); // 过滤 0xa 非法字符


	$lib_file = DIR . '/templates/' . $curr_template . '/library/' . $lib_name . '.lbi';
	$arr ['mark'] = file_mode_info ( $lib_file );
	$arr ['html'] = str_replace ( "\xEF\xBB\xBF", '', file_get_contents ( $lib_file ) ); //过滤单引号


	return $arr;
}

/**
 * 从相应模板xml文件中获得指定模板文件中的固定库文件
 *
 * @access  public
 * @param   string  $curr_template    当前模板文件名
 * @param   array   $curr_page_libs   缺少xml文件时的默认编辑区信息数组
 * @return  array   $edit_libs        返回可编辑的库文件数组
 */
function get_dwt_libs($curr_template, $curr_page_libs) {
	global $skyuc;
	$vals = array ();
	$edit_libs = array ();
	require_once (DIR . '/includes/class_xml.php');

	$xmlobj = new XML_Parser ( false, DIR . '/templates/' . $skyuc->options ['themes'] . '/library.xml' );
	$xml = $xmlobj->parse ();
	$libs = array ();
	foreach ( $xml ['dwt'] as $key => $value ) {
		if ($value ['file'] == $curr_template . '.dwt') {
			foreach ( $value ['lib'] as $k => $v ) {
				$libs [$v ['value']] = $v ['num'];
			}
		}

	}
	ksort ( $libs );

	if (empty ( $libs )) {
		$libs = $curr_page_libs;
	}

	return $libs;
}

/**
 * 读取模板风格列表
 *
 * @access  public
 * @param   string  $tpl_name       模版名称
 * @param   int     $flag           1，AJAX数据；2，Array
 * @return
 */
function read_tpl_style($tpl_name, $flag = 1) {
	if (empty ( $tpl_name ) && $flag == 1) {
		return 0;
	}

	global $skyuc;

	/* 获得可用的模版 */
	$temp = '';
	$start = 0;
	$available_templates = array ();
	$dir = DIR . '/templates/' . $tpl_name . '/';
	$tpl_style_dir = @opendir ( $dir );
	while ( $file = readdir ( $tpl_style_dir ) ) {
		if ($file != '.' && $file != '..' && is_file ( $dir . $file ) && $file != '.svn' && $file != 'index.htm') {
			if (preg_match ( "/^(style|style_)(.*)*/i", $file )) // 取模板风格缩略图
{
				$start = strpos ( $file, '.' );
				$temp = substr ( $file, 0, $start );
				$temp = explode ( '_', $temp );
				if (count ( $temp ) == 2) {
					$available_templates [] = $temp [1];
				}
			}
		}
	}
	@closedir ( $tpl_style_dir );

	if ($flag == 1) {
		$ec = '<table border="0" width="100%" cellpadding="0" cellspacing="0" class="colortable" onMouseOver="javascript:onSOver(0, this);" onMouseOut="onSOut(this);" onclick="javascript:setupTemplateFG(0);"  bgcolor="#FFFFFF"><tr><td>&nbsp;</td></tr></table>';
		if (count ( $available_templates ) > 0) {
			foreach ( $available_templates as $value ) {
				$tpl_info = get_template_info ( $tpl_name, $value );

				$ec .= '<table border="0" width="100%" cellpadding="0" cellspacing="0" class="colortable" onMouseOver="javascript:onSOver(\'' . $value . '\', this);" onMouseOut="onSOut(this);" onclick="javascript:setupTemplateFG(\'' . $value . '\');"  bgcolor="' . $tpl_info ['type'] . '"><tr><td>&nbsp;</td></tr></table>';

				unset ( $tpl_info );
			}
		} else {
			$ec = '0';
		}

		return $ec;
	} elseif ($flag == 2) {
		$templates_temp = array ('' );
		if (count ( $available_templates ) > 0) {
			foreach ( $available_templates as $value ) {
				$templates_temp [] = $value;
			}
		}

		return $templates_temp;
	}
}

/**
 * 读取当前风格信息与当前模板风格列表
 *
 * @access  public
 * @param   string  $tpl_name       模版名称
 * @param   string  $tpl_style 模版风格名
 * @return
 */
function read_style_and_tpl($tpl_name, $tpl_style) {
	$style_info = array ();
	$style_info = get_template_info ( $tpl_name, $tpl_style );

	$tpl_style_info = array ();
	$tpl_style_info = read_tpl_style ( $tpl_name, 2 );
	$tpl_style_list = '';
	if (count ( $tpl_style_info ) > 1) {
		foreach ( $tpl_style_info as $value ) {
			$tpl_style_list .= '<span style="cursor:pointer;" onMouseOver="javascript:onSOver(\'screenshot\', \'' . $value . '\', this);" onMouseOut="onSOut(\'screenshot\', this, \'' . $style_info ['screenshot'] . '\');" onclick="javascript:setupTemplateFG(\'' . $tpl_name . '\', \'' . $value . '\', \'\');" id="templateType_' . $value . '"><img src="../templates/' . $tpl_name . '/images/type' . $value . '_';

			if ($value == $tpl_style) {
				$tpl_style_list .= '1';
			} else {
				$tpl_style_list .= '0';
			}
			$tpl_style_list .= '.gif" border="0"></span>&nbsp;';
		}
	}
	$style_info ['tpl_style'] = $tpl_style_list;

	return $style_info;
}
?>