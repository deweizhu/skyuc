<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{$lang.cp_home}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="styles/general.css" rel="stylesheet" type="text/css" />
{insert_scripts files="../skyuc_global.js,../skyuc_utils.js"}
<script language="JavaScript">
<!--
var noHelp   = "<p align='center' style='color: #666'>{$lang.no_help}</p>";
var helpLang = "{$help_lang}";
//-->
</script>

<style type="text/css">
body {
  background: #80BDCB;
}
#tabbar-div {
  background: #278296;
  padding-left: 10px;
  height: 21px;
  padding-top: 0px;
}
#tabbar-div p {
  margin: 1px 0 0 0;
}
.tab-front {
  background: #80BDCB;
  line-height: 20px;
  font-weight: bold;
  padding: 4px 15px 4px 18px;
  border-right: 2px solid #335b64;
  cursor: hand;
  cursor: pointer;
}
.tab-back {
  color: #F4FAFB;
  line-height: 20px;
  padding: 4px 15px 4px 18px;
  cursor: hand;
  cursor: pointer;
}
.tab-hover {
  color: #F4FAFB;
  line-height: 20px;
  padding: 4px 15px 4px 18px;
  cursor: hand;
  cursor: pointer;
  background: #2F9DB5;
}
#top-div
{
  padding: 3px 0 2px;
  background: #BBDDE5;
  margin: 5px;
  text-align: center;
}
#main-div {
  border: 1px solid #345C65;
  padding: 5px;
  margin: 5px;
  background: #FFF;
}
#menu-list {
  padding: 0;
  margin: 0;
}
#menu-list ul {
  padding: 0;
  margin: 0;
  list-style-type: none;
  color: #335B64;
}
#menu-list li {
  padding-left: 16px;
  line-height: 16px;
  cursor: hand;
  cursor: pointer;
}
#main-div a:visited, #menu-list a:link, #menu-list a:hover {
  color: #335B64
  text-decoration: none;
}
#menu-list a:active {
  color: #EB8A3D;
}
.explode {
  background: url(images/menu_minus.gif) no-repeat 0px 3px;
  font-weight: bold;
}
.collapse {
  background: url(images/menu_plus.gif) no-repeat 0px 3px;
  font-weight: bold;
}
.menu-item {
  background: url(images/menu_arrow.gif) no-repeat 0px 3px;
  font-weight: normal;
}
#help-title {
  font-size: 14px;
  color: #000080;
  margin: 5px 0;
  padding: 0px;
}
#help-content {
  margin: 0;
  padding: 0;
}
.tips {
  color: #CC0000;
}
.link {
  color: #000099;
}
</style>

</head>
<body>
<div id="tabbar-div">
<p><span style="float:right; padding: 3px 5px;" ><a href="javascript:toggleCollapse();"><img id="toggleImg" src="images/menu_minus.gif" width="9" height="9" border="0" alt="{$lang.collapse_all}" /></a></span>
  <span class="tab-front" id="menu-tab">{$lang.menu}</span><span class="tab-back" id="help-tab">{$lang.help}</span>
</p>
</div>
<div id="main-div">
<div id="menu-list">
<ul>
{foreach from=$menus item=menu}
{if $menu.action}
  <li class="explode"><a href="{$menu.action}" target="main-frame">{$menu.label}</a></li>
{else}
  <li class="explode">
    {$menu.label}
    {if $menu.children}
    <ul>
    {foreach from=$menu.children item=child}
      <li class="menu-item"><a href="{$child.action}" target="main-frame">{$child.label}</a></li>
    {/foreach}
    </ul>
    {/if}
  </li>
{/if}
{/foreach}
</ul>
</div>
<div id="help-div" style="display:none">
<h1 id="help-title"></h1>
<div id="help-content"></div>
</div>
</div>
<script language="JavaScript">
<!--
var collapse_all = "{$lang.collapse_all}";
var expand_all = "{$lang.expand_all}";
var collapse = true;

function toggleCollapse()
{
  var items = document.getElementsByTagName('LI');
  for (i = 0; i < items.length; i++)
  {
    if (collapse)
    {
      if (items[i].className == "explode")
      {
        toggleCollapseExpand(items[i], "collapse");
      }
    }
    else
    {
      if (items[i].className == "collapse")
      {
        toggleCollapseExpand(items[i], "explode");
      }
    }
  }

  collapse = !collapse;

  document.getElementById('toggleImg').src = collapse ? 'images/menu_plus.gif' : 'images/menu_plus.gif';
  document.getElementById('toggleImg').alt = collapse ? collapse_all : expand_all;
}

function toggleCollapseExpand(obj, status)
{
  if (obj.tagName.toLowerCase() == 'li' && obj.className != 'menu-item')
  {
    for (i = 0; i < obj.childNodes.length; i++)
    {
      if (obj.childNodes[i].tagName == "UL")
      {
        if (status == null)
        {
          if (obj.childNodes[1].style.display != "none")
          {
            obj.childNodes[1].style.display = "none";
            obj.className = "collapse";
          }
          else
          {
            obj.childNodes[1].style.display = "block";
            obj.className = "explode";
          }
          break;
        }
        else
        {
          obj.className = status;
          obj.childNodes[1].style.display = (status == "explode") ? "block" : "none";
        }
      }
    }
  }
}
document.getElementById('menu-list').onclick=function(e)
{
  var obj = Utils.srcElement(e);
  toggleCollapseExpand(obj);
}

document.getElementById('tabbar-div').onmouseover=function(e)
{
  var obj = Utils.srcElement(e);

  if (obj.className == "tab-back")
  {
    obj.className = "tab-hover";
  }
}

document.getElementById('tabbar-div').onmouseout=function(e)
{
  var obj = Utils.srcElement(e);

  if (obj.className == "tab-hover")
  {
    obj.className = "tab-back";
  }
}

document.getElementById('tabbar-div').onclick=function(e)
{
  var obj = Utils.srcElement(e);

  var mnuTab = document.getElementById('menu-tab');
  var hlpTab = document.getElementById('help-tab');
  var mnuDiv = document.getElementById('menu-list');
  var hlpDiv = document.getElementById('help-div');

  if (obj.id == 'menu-tab')
  {
    mnuTab.className = 'tab-front';
    hlpTab.className = 'tab-back';
    mnuDiv.style.display = "block";
    hlpDiv.style.display = "none";
  }

  if (obj.id == 'help-tab')
  {
    mnuTab.className = 'tab-back';
    hlpTab.className = 'tab-front';
    mnuDiv.style.display = "none";
    hlpDiv.style.display = "block";

    loc = parent.frames['main-frame'].location.href;
    pos1 = loc.lastIndexOf("/");
    pos2 = loc.lastIndexOf("?");
    pos3 = loc.indexOf("act=");
    pos4 = loc.indexOf("&", pos3);

    filename = loc.substring(pos1 + 1, pos2 - 4);
    act = pos4 < 0 ? loc.substring(pos3 + 4) : loc.substring(pos3 + 4, pos4);
    loadHelp(filename, act);
  }
}

/**
 * 载入帮助内容
 */
function loadHelp(filename, act)
{
  var doc = createDocument();
  var path = 'help/' + helpLang + '/' + filename + ".xml";

  document.getElementById("help-title").innerHTML = '';
  document.getElementById("help-content").innerHTML = noHelp;
  try
  {
    doc.load(path);

    var items = doc.getElementsByTagName('section');

    for (i = 0; i < items.length; i++)
    {
      if (items[i].getAttribute("id") == act)
      {
        var title = items[i].getElementsByTagName("title");
        document.getElementById("help-title").innerHTML = (Browser.isIE) ? title[0].text : title[0].textContent;

        var content = items[i].getElementsByTagName("content");
        document.getElementById("help-content").innerHTML = (Browser.isIE) ? content[0].text : content[0].textContent;

        break;
      };
    }
  }
  catch (e)
  {
    alert(e.message);
  }
}

/**
 * 创建XML对象
 */
function createDocument()
{
  var xmlDoc;

  // create a DOM object
  if (window.ActiveXObject)
  {
    try
    {
      xmlDoc = new ActiveXObject("Msxml2.DOMDocument.6.0");
    }
    catch (e)
    {
      try
      {
        xmlDoc = new ActiveXObject("Msxml2.DOMDocument.5.0");
      }
      catch (e)
      {
        try
        {
          xmlDoc = new ActiveXObject("Msxml2.DOMDocument.4.0");
        }
        catch (e)
        {
          try
          {
            xmlDoc = new ActiveXObject("Msxml2.DOMDocument.3.0");
          }
          catch (e)
          {
            alert(e.message);
          }
        }
      }
    }
  }
  else
  {
    if (document.implementation && document.implementation.createDocument)
    {
      xmlDoc = document.implementation.createDocument("","doc",null);
    }
    else
    {
      alert("Create XML object is failed.");
    }
  }
  xmlDoc.async = false;

  return xmlDoc;
}

//-->
</script>
</body>
</html>
