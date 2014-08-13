<?php
// #######################################################################
// ######################## edit_languages.php 私有函数      #############
// #######################################################################
/*------------------------------------------------------ */
//-- 语言项的操作函数
/*------------------------------------------------------ */

/**
 * 获得语言项列表
 * @access  public
 * @exception           如果语言项中包含换行符，将发生异常。
 * @param   string      $file_path   存放语言项列表的文件的绝对路径
 * @param   string      $keyword    搜索时指定的关键字
 * @return  array       正确返回语言项列表，错误返回false
 */
function get_language_item_list($file_path, $keyword) {
	if (empty ( $keyword )) {
		return array ();
	}
	
	/* 获取文件内容 */
	$line_array = file ( $file_path );
	if (! $line_array) {
		return false;
	} else {
		/* 防止用户输入敏感字符造成正则引擎失败 */
		$keyword = preg_quote ( $keyword, '/' );
		
		$matches = array ();
        $pattern    = '/\\[[\'|"](.*?)'.$keyword.'(.*?)[\'|"]\\]\\s|=\\s?[\'|"](.*?)'.$keyword.'(.*?)[\'|"];/';
        $regx       = '/(?P<item>(?P<item_id>\\$_LANG\\[[\'|"].*[\'|"]\\])\\s?=\\s?[\'|"](?P<item_content>.*)[\'|"];)/';
		
		foreach ( $line_array as $lang ) {
			if (preg_match ( $pattern, $lang )) {
				$out = array ();
				
				if (preg_match ( $regx, $lang, $out )) {
					$matches [] = $out;
				}
			}
		}
		
		return $matches;
	}
}

/**
 * 设置语言项
 * @access  public
 * @param   string      $file_path     存放语言项列表的文件的绝对路径
 * @param   array       $src_items     替换前的语言项
 * @param   array       $dst_items     替换后的语言项
 * @return  void        成功就把结果写入文件，失败返回false
 */
function set_language_items($file_path, $src_items, $dst_items) {
	/* 检查文件是否可写（修改） */
	if (file_mode_info ( $file_path ) < 2) {
		return false;
	}
	
	/* 获取文件内容 */
	$line_array = file ( $file_path );
	if (! $line_array) {
		return false;
	} else {
		$file_content = implode ( '', $line_array );
	}
	
	$snum = count ( $src_items );
	$dnum = count ( $dst_items );
	if ($snum != $dnum) {
		return false;
	}
	/* 对索引进行排序，防止错位替换 */
	ksort ( $src_items );
	ksort ( $dst_items );
	for($i = 0; $i < $snum; $i ++) {
		$file_content = str_replace ( $src_items [$i], $dst_items [$i], $file_content );
	
	}
	
	/* 写入修改后的语言项 */
	$f = fopen ( $file_path, "w" );
	if (! $f) {
		return false;
	}
	if (! fwrite ( $f, $file_content )) {
		return false;
	} else {
		return true;
	}
}
?>