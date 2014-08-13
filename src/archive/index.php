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

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE & ~8192);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('SESSION_BYPASS', 1);
define('THIS_SCRIPT', 'archive');

// ################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('forum');
$specialtemplates = array();

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
//require_once(DIR . '/includes/functions_bigthree.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

if (SLASH_METHOD AND strpos($archive_info , '/archive/index.php') === false)
{
	exec_header_redirect($skyuc->options['site_url'] . '/archive/index.php');
}

// parse query string
$f = 0;
$p = 0;
$t = 0;
$output = '';

$endbit = str_replace('.html', '', $archive_info);
if (SLASH_METHOD)
{
	$endbit = substr(strrchr($endbit, '/') , 1);
}
else if (strpos($endbit, '&') !== false)
{
	$endbit = substr(strrchr($endbit, '&') , 1);
}

if ($endbit != '' AND $endbit != 'index.php')
{
	$queryparts = explode('-', $endbit);
	foreach ($queryparts AS $querypart)
	{
		if ($lastpart != '')
		{
			// can be:
			// f: forumid
			// p: pagenumber
			// t: threadid
			$$lastpart = $querypart;
			$lastpart = '';
		}
		else
		{
			switch ($querypart)
			{
				case 'f':
				case 'p':
				case 't':
					$lastpart = $querypart;
					break;
				default:
					$lastpart = '';
			}
		}
	}
}
else
{
	$do = 'index';
}



// check to see if the person is using a PDA if so we'll sort in ASC
// force a redirect afterwards so we dont get problems with search engines
if ($t)
{
	$t = intval($t);
	$querystring = 't-' . $t . iif($p, '-p-' . intval($p)) . '.html';
}
else if ($f)
{
	$f = intval($f);
	$querystring = 'f-' . $f . iif($p, '-p-' . intval($p)) . '.html';
}


$title = $skyuc->options['site_title'];


if ($do == 'error')
{
}
else if ($t)
{
	$do = 'thread';

	$showinfo = fetch_showinfo($t);
	$catinfo = fetch_category_info($showinfo['cat_id']);

	$title = $showinfo['title'] .' - '.$skyuc->options['site_name'];

	$metatags = '<meta name="keywords" content="'. $showinfo['keywords']. "\" />
	<meta name=\"description\" content=\"".$showinfo['description']."\" />";

}
else if ($f)
{
	$do = 'list';


	$catinfo = fetch_category_info($f);

	$title = $catinfo['cat_name']. ($p > 1 ? ' - ' . construct_phrase($_LANG['page_x'], $p ): '') . ' - ' .$skyuc->options['site_name'];

	$p = intval($p);
	$metatags = "<meta name=\"keywords\" content=\"$catinfo[cat_desc], " . $skyuc->options['keywords'] . "\" />
	<meta name=\"description\" content=\" $catinfo[cat_desc] \" />	";

}
else
{
	$do = 'index';
	$metatags = "<meta name=\"keywords\" content=\"" . $skyuc->options['site_keywords'] . "\" />
	<meta name=\"description\" content=\"" . $skyuc->options['site_desc'] . "\" />";
}


$output .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" dir=\"ltr\" lang=\"zh-CN\">
<head>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
	$metatags
	<title>$title</title>
	<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $skyuc->options['site_url'] . "/archive/archive.css\" />
</head>
<body>
<div class=\"pagebody\">
";

// ********************************************************************************************
// 显示首页

if ($do == 'index')
{
	$output .= print_archive_navigation(array());

	$output .= "<p class=\"largefont\">$_LANG[view_full_version] : <a href=\"" . $skyuc->options['site_url'] . '/index.php">' . $skyuc->options['site_title'] . "</a></p>\n";

	$output .= "<div id=\"content\">\n";
	$output .= print_archive_cat_list();
	$output .= "</div>\n";

}


$globalignore = '';


// ********************************************************************************************
// 显示分类页

if ($do == 'list')
{
	// list threads

	$output .= print_archive_navigation($f);

	$output .= "<p class=\"largefont\">$_LANG[view_full_version]: <a href=\"" . $skyuc->options['site_url'] . "/list.php?id=$catinfo[cat_id]\">$catinfo[cat_name]</a></p>\n<hr />\n";

	if ($catinfo['show_num'])
	{

		if (!$p)
		{
			$p = 1;
		}

		$output .= print_archive_page_navigation($catinfo['show_num'], $skyuc->options['archive_threadsperpage'], "f-$catinfo[cat_id]");

		$result = $db->query_read_slave("
			SELECT  show_id, title, status
			FROM " . TABLE_PREFIX . "show
			WHERE cat_id = '".$catinfo['cat_id']."'
				AND is_show = 1
			ORDER BY add_time DESC
			LIMIT " . ($p - 1) * $skyuc->options['archive_threadsperpage'] . ',' . $skyuc->options['archive_threadsperpage']
		);

		$start = ($p - 1) * $skyuc->options['archive_threadsperpage'] + 1;

		$output .= "<div id=\"content\">\n<ol start=\"$start\">\n";
		while ($show = $db->fetch_array($result))
		{
		  if ($skyuc->options['archive_threadtype'])
			{
				$output .= "\t<li> <a href=\"" . $skyuc->options['site_url'] . '/archive/index.php' . (SLASH_METHOD ? '/' : '?') . 't-'.$show['show_id'].".html\">".$show['title'].'</a>' . iif(!empty($show['status'])," <i>(".$show['status'].")</i>", '')."</li>\n";
			}
			else
			{
				$output .= "\t<li> <a href=\"" . $skyuc->options['site_url'] . "/show.php?id=".$show['show_id']."\">".$show['title']."</a>".iif(!empty($show['status'])," <i>(".$show['status'].")</i>", '')."</li>\n";
			}
		}
		$output .= "</ol>\n</div>\n";

	}
	else
	{
		$output .= "<div id=\"content\">\n";
		$output .= print_archive_cat_list($f);
		$output .= "</div>\n";
	}
}

// ********************************************************************************************
// 显示内容页

if ($do == 'thread')
{

	if (!$skyuc->options['archive_threadtype'])
	{
		exec_header_redirect($skyuc->options['site_url'] . "/show.php?" . $skyuc->session->vars['sessionurl_js'] . "id=$showinfo[show_id]");
	}

	$output .= print_archive_navigation($showinfo['cat_id'], $showinfo);

	$output .= "<p class=\"largefont\">$_LANG[view_full_version] : "
		. "<a href=\"" . $skyuc->options['site_url'] . "/show.php?id=$showinfo[show_id]\">$showinfo[title]</a></p>\n<hr />\n";

	$output .= "\n<div class=\"post\"><div class=\"posttop\"><div class=\"title\">$showinfo[title]   $showinfo[title_alias]   $showinfo[title_english]</div><div class=\"date\">$showinfo[add_time]</div></div>";
	$output .= "<div class=\"posttext\">".$_LANG['actor'].$showinfo['actor'].'<br />'.
	$_LANG['director']. $showinfo['director'].'<br />'.
	$_LANG['pubdate']. $showinfo['pubdate'].'<br />'.
	$_LANG['area']. $showinfo['area'].'<br />'.
	$_LANG['lang']. $showinfo['lang'].'<br />'.
	$_LANG['runtime']. $showinfo['runtime'].$_LANG['unit_fraction'].'<br />'.
	$_LANG['status']. $showinfo['status'].'<br />'.
	$_LANG['image'].iif($skyuc->options['archive_imagetype'], "<img id='image' src='". $showinfo['image']."'>", $showinfo['image'])."<br />".
	nl2br($showinfo['detail']) .'<br />';

	if(!empty($showinfo['data']) && $skyuc->options['archive_showdata'])
	{
	    $showinfo['player'] = explode(',',$showinfo['player']);
			$datainfo = '<skyuc_url>';
			$data = display_url_data($showinfo['data'], $showinfo['player'], $showinfo['server_id'], true);
			foreach ($data as $datarr)
			{
			    $datainfo.= "<div class=\"playtitle\">".$datarr['player_name'].'</div><p>';
			    foreach ($datarr['url'] as $url){
			        $datainfo.='<skyuc_title>'.$url['title'].'</skyuc_title>：<url> '.$url['src'].'</url><br />';
			    }
			    $datainfo .='</p>';
			}
			$output .=$datainfo.'</skyuc_url>';
	}
	$output .="</div></div><hr />\n\n";

}


// ********************************************************************************************
// display error
if ($do == 'error')
{
	$output .= print_archive_navigation(array());

	$output .= "<p class=\"largefont\">$_LANG[view_full_version]: <a href=\"" . $skyuc->options['site_url'] . '/index.php">' . $skyuc->options['site_title'] . "</a></p>\n";

	$output .= "<div id=\"content\">\n";
	$output .= $error_message;
	$output .= "</div>\n";
}


$output .= "
<div id=\"copyright\">".$skyuc->options['copyright']."</div>
</div>
</body>
</html>";

if (defined('NOSHUTDOWNFUNC'))
{
	exec_shut_down();
}

echo $output;
?>