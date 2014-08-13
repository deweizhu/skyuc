<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{$lang.app_name}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="styles/general.css" rel="stylesheet" type="text/css" />
<style type="text/css">
#header-div {
  background: #278296;
  border-bottom: 1px solid #FFF;
}

#logo-div {
  height: 57px;
  float: left;
}

#submenu-div {
  height: 57px;
}

#submenu-div ul {
  margin: 0;
  padding: 0;
  list-style-type: none;
}

#submenu-div li {
  float: right;
  padding: 0 10px;
  margin: 5px 0;
  border-left: 1px solid #FFF;
}

#submenu-div a:visited, #submenu-div a:link {
  color: #FFF;
  text-decoration: none;
}

#submenu-div a:hover {
  color: #F5C29A;
}

#loading-div {
  clear: right;
  text-align: right;
  display: block;
}

#menu-div {
  background: #80BDCB;
  font-weight: bold;
  padding-left: 30px;
  height: 24px;
}

#menu-div ul {
  margin: 0;
  padding: 0;
  list-style-type: none;
}

#menu-div li {
  float: left;
  padding: 0 20px;
  margin: 5px 0;
  border-right: 1px solid #192E32;
}

#menu-div a:visited, #menu-div a:link {
  text-decoration: none;
  color: #335B64;
}

#menu-div a:hover {
  color: #F5C29A;
}
</style>
<script type="text/javascript">
function modalDialog(url, name, width, height)
{
  if (width == undefined)
  {
    width = 400;
  }
  if (height == undefined)
  {
    height = 300;
  }

  x = (window.screen.width - width) / 2;
  y = (window.screen.height - height) / 2;
  try
  {
    window.showModalDialog(url, name, 'dialogWidth=' + (width) + 'px; dialogHeight=' + (height+5) + 'px; status=off');
  }
  catch (ex)
  {
    window.open(url, name, 'height='+height+', width='+width+', left='+x+', top='+y+', toolbar=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, modal=yes');
  }
}

function ShowToDoList()
{
  try
  {
    var mainFrame = window.top.frames['main-frame'];
    mainFrame.window.showTodoList(adminId);
  }
  catch (ex)
  {
  }
}

var adminId = "{$admin_id}";
</script>
</head>
<body>
<div id="header-div">
  <div id="logo-div"><img src="images/logo.gif"  alt="{$lang.app_name}" /></div>
  <div id="submenu-div">
    <ul>
      <li><a href="index.php?act=about_us" target="main-frame">{$lang.about}</a></li>
	   <li><a href="javascript:modalDialog('index.php?act=calculator', 'calculator', 340, 250)">{$lang.toggle_calculator}</a></li>
      <li><a href="../" target="_blank">{$lang.preview}</a></li>
      <li><a href="message.php?act=list" target="main-frame">
        {if $message_num > 0}
        {else}
        {$lang.view_message}
        {/if}
        </a>
      </li>
      <li><a href="message.php?act=send" target="main-frame">{$lang.send_msg}</a></li>
      <li><a href="privilege.php?act=modif" target="main-frame">{$lang.profile}</a></li>
      <li><a href="javascript:window.top.frames['main-frame'].document.location.reload()">{$lang.refresh}</a></li>
	  <li><a href="#"  onclick="ShowToDoList()">{$lang.todolist}</a></li>
    </ul>

    <div id="load-div" style="padding: 5px 10px 0 0; clear: right; text-align: right; color: #FF9900; display: none;"><img src="images/top_loader.gif" width="16" height="16" alt="{$lang.loading}" style="vertical-align: middle" /> {$lang.loading}</div>
  </div>
</div>
<div id="menu-div">
  <ul>
    <li><a href="index.php?act=main" target="main-frame">{$lang.admin_home}</a></li>
    {foreach from=$nav_list item=item key=key}
    <li><a href="{$key}" target="main-frame">{$item}</a></li>
    {/foreach}
    <li style="float: right; border-right: 0; "><a href="privilege.php?act=logout" target="_top">{$lang.signout}</a></li>
    <li style="float: right; "><a href="index.php?act=about_us" target="main-frame">{$lang.cache_mgr}</a></li>
  </ul>
  <br class="clear" />
</div>
</body>
</html>