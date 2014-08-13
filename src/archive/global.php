<?php
/**
 * SKYUC 存档入口
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
*/

// 标志我们在哪里
define('SKYUC_AREA', 'Archive');

// ###################### 开始初始化 #######################
chdir('./../');
define('CWD', (($getcwd = getcwd()) ? $getcwd : '.'));

require_once(CWD . '/includes/init.php');
require_once(CWD . '/includes/functions_main.php');
require(DIR . '/languages/' . $skyuc->options['lang'] . '/common.php');

// ###################### Start headers #######################
//exec_headers();

// ###################### 获取 日期/时间 信息 #######################
fetch_time_data();

// ###################### Start templates & styles #######################
if ((DIRECTORY_SEPARATOR == '\\' AND stristr($_SERVER['SERVER_SOFTWARE'], 'apache') === false) OR (strpos(SAPI_NAME, 'cgi') !== false AND @!ini_get('cgi.fix_pathinfo')))
{
	define('SLASH_METHOD', false);
}
else
{
	define('SLASH_METHOD', true);
}

if (SLASH_METHOD)
{
	$archive_info = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
}
else
{
	$archive_info = $_SERVER['QUERY_STRING'];
}

if ($skyuc->options['archiveenabled'] == 0)
{
	exec_header_redirect($skyuc->options['site_url'] . '/index.php');
}


// ###################### 检查封禁IP #######################################################
verify_ip_ban();


// #########################################################################################
// ###################### 存档 函数 ################################################

//列表分类，其正确的顺序和嵌套
function print_archive_cat_list($parentid = 0)
{
	global $skyuc;

	$output = '';

  if (!isset($skyuc->categories["$parentid"])) {
     $skyuc->categories["$parentid"] = get_categories_tree($parentid);
  }

	if (is_array($skyuc->categories["$parentid"]))
	{
		foreach($skyuc->categories["$parentid"] AS $x)
		{
			  $catid = $x['id'];
			  $catname = $x['name'];
				$cat_link ='<a '.iif($x['children'], 'id="parent"','').' href="' . $skyuc->options['site_url'] . '/archive/index.php' . (SLASH_METHOD ? '/' : '?') . "f-$catid.html\">";
				$output .= "\t<li>$cat_link$catname</a>" . "</li>\n";
				$output .= get_child($x['children']);

		}
		if (!empty($output))
		{
			$output = "\n<ul>\n" . $output . "</ul>\n";
		}
	}

	return $output;
}
function get_child($_from = array()) {
    global $skyuc;

    if (empty($_from)) {
    	return '';
    }
    $output = "\t<ul>\n";
		foreach($_from AS $catid => $cat)
		{
		    if ($cat['children']){
			 	 $cat_link ='<a id="parent"  href="' . $skyuc->options['site_url'] . '/archive/index.php' . (SLASH_METHOD ? '/' : '?') . "f-$catid.html\">";
		    }
		    else{
		     $cat_link ='<a href="' . $skyuc->options['site_url'] . '/archive/index.php' . (SLASH_METHOD ? '/' : '?') . "f-$catid.html\">";
		    }
		   $output .= "\t\t<li>$cat_link$cat[name]</a>" . "</li>\n";
		   $output .= get_child($cat['children']);
		}
		$output .=  "\t</ul>\n";

		return $output;
}

// 绘制存档页的导航
function print_archive_navigation($cat_id, $threadinfo='')
{
	global $skyuc, $querystring;

	$navarray = array('<a href="' . $skyuc->options['site_url'] . '/archive/index.php">' . $skyuc->options['site_name'] . '</a>');

	if (!empty($cat_id))
	{
	    $cat_arr = get_parent_cats($cat_id);

        // 循环分类
    if (! empty($cat_arr)) {
        krsort($cat_arr);
        foreach ($cat_arr as $val) {
          $navarray[] = "<a href=\"" . $skyuc->options['site_url'] . '/archive/index.php' . (SLASH_METHOD ? '/' : '?') . "f-$val[cat_id].html\">" . $val['cat_name'] . "</a>";
        }
    }
	}

	if (is_array($threadinfo))
	{
		$navarray[] =  $threadinfo['title'];
	}


	$return = '<div id="navbar">' . implode(' &gt; ', $navarray) . "</div>\n<hr />\n" ;


	return $return;
}

function print_archive_navbar($navbits = array())
{
	global $skyuc, $querystring;

	$navarray = array('<a href="' . $skyuc->options['site_url'] . '/index.php">' . $skyuc->options['site_title'] . '</a>');

	foreach ($navbits AS $url => $navbit)
	{
		if ($url)
		{
			$navarray[] = "<a href=\"" . htmlspecialchars_uni($url) . "\">$navbit</a>";
		}
		else
		{
			$navarray[] = $navbit;
		}
	}
	$return = '<div id="navbar">' . implode(' &gt; ', $navarray) . "</div>\n<hr />\n";


	return $return;
}

//绘制存档页网页的链接
function print_archive_page_navigation($total, $perpage, $link)
{
	global $p, $skyuc;

	$output = '';
	$perpage = iif($perpage, $perpage, 250);
	$numpages = ceil($total / $perpage);

	if ($numpages > 1)
	{
		$output .= "<div id=\"pagenumbers\"><b>页 :</b>\n";

		for ($i=1; $i <= $numpages; $i++)
		{
			if ($i == $p)
			{
				$output .= "[<b>$i</b>]\n";
			}
			else if ($i == 1)
			{
				$output .= '<a href="' . $skyuc->options['site_url'] . '/archive/index.php' . (SLASH_METHOD ? '/' : '?') . "$link.html\">$i</a>\n";
			}
			else
			{
				$output .= '<a href="' . $skyuc->options['site_url'] . '/archive/index.php' . (SLASH_METHOD ? '/' : '?') . "$link-p-$i.html\">$i</a>\n";
			}
		}

		$output .= "</div>\n<hr />\n";
	}

	return $output;
}
// #############################################################################
/**
* 返回一个包含指定影片 信息的数组, 如果不存在返回false
*
* @param	integer	(引用) 影片 ID
*
* @return	mixed
*/
function fetch_showinfo(&$threadid)
{
	   global $skyuc;

	   $threadid = intval($threadid);

    $sql = 'SELECT show_id,	director,	actor, title, title_alias, title_english,	status, image,	keywords,	description,	detail,	pubdate,cat_id,	area,	lang,	add_time, runtime, player, server_id, data  FROM ' . TABLE_PREFIX. 'show' . ' WHERE show_id = '. $threadid. ' AND is_show = 1';
    $row = $skyuc->db->query_first_slave($sql);
    if (!empty($row))
    {
      // 格式化最后更新时间显示
     	$row['add_time'] = skyuc_date($skyuc->options['date_format'].' '. $skyuc->options['time_format'],	$row['add_time']);
      // 修正影片图片
      $row['image'] = get_image_path($row['image']);
    }
    else
    {
        exec_header_redirect($skyuc->options['site_url'] . '/archive/index.php');
    }


	return $row;
}
// #############################################################################
/**
* 返回一个包含指定分类信息的数组, 如果不存在分类返回false
*
* @param	integer	(ref) 分类 ID
*
* @return	mixed
*/
function fetch_category_info(&$cat_id = 0)
{
	global $skyuc;

	$cat_id = intval($cat_id);
	foreach ($skyuc->category as $cat){
	    if ($cat['cat_id'] == $cat_id) {
	    	return $cat;
	    }
	}
	return false;


}

?>