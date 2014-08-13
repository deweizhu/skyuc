function SkyucMenu(){
	this.pid  = null;
	this.obj  = null;
	this.w	  = null;
	this.h	  = null;
	this.t	  = 0;
	this.menu = null;
	this.init();
}

SkyucMenu.prototype = {

	init : function() {
		this.menu	= getSkyucBox();
		document.body.insertBefore(this.menu,document.body.firstChild);
	},
	close : function() {
		read.t = setTimeout("closep();",100);
	},

	setMenu : function(element,type){
		if (type) {
			var thisobj = this.menu;
		} else {
			var thisobj = getSkyucContainer();
		}
		if (typeof(element) == 'string') {
			thisobj.innerHTML = element;
		} else {
			while (thisobj.hasChildNodes()) {
				thisobj.removeChild(thisobj.firstChild);
			}
			thisobj.appendChild(element);
		}
	},

	move : function(e) {
		if (Browser.isIE) {
			document.body.onselectstart = function(){return false;}
		}
		var e  = Browser.isIE ? window.event : e;
		var o  = read.menu;
		var x  = e.clientX;
		var y  = e.clientY;
		read.w = e.clientX - parseInt(o.offsetLeft);
		read.h = e.clientY - parseInt(o.offsetTop);
		document.onmousemove = read.moving;
		document.onmouseup   = read.moved;
	},

	moving : function(e) {
		var e  = is_ie ? window.event : e;
		var x  = e.clientX;
		var y  = e.clientY;
		read.menu.style.left = x - read.w + 'px';
		read.menu.style.top  = y - read.h + 'px';
	},

	moved : function() {
		if (is_ie) {
			document.body.onselectstart = function(){return true;}
		}
		document.onmousemove = '';
		document.onmouseup   = '';
	},

	open : function(idName,object,type,pz) {
		clearTimeout(read.t);
		if (typeof type == "undefined") type = 1;
		if (typeof pz == "undefined") pz = 0;
		this.setMenu(getObj(idName).innerHTML,1);
		this.menu.className = getObj(idName).className;
		this.menupz(object,pz);
		if (type != 2) {
			getObj(object).onmouseout = function() {
				read.close();
				getObj(object).onmouseout = '';
			}
			read.menu.onmouseout = read.close;
			read.menu.onmouseover = function() {
				clearTimeout(read.t);
			}
		}
	},

	menupz : function(obj,pz) {
		read.menu.onmouseout = '';
		read.menu.style.display = '';
		read.menu.style.zIndex	= 3000;
		read.menu.style.left	= '-500px';
		read.menu.style.visibility = 'visible';
		if (typeof obj == 'string') {
			obj = getObj(obj);
		}
		if (obj == null) {
			read.menu.style.top  = (ietruebody().clientHeight - read.menu.offsetHeight)/3 + getTop() + 'px';
			read.menu.style.left = (ietruebody().clientWidth - read.menu.offsetWidth)/2 + 'px';
		} else {
			var top  = findPosY(obj);
			var left = findPosX(obj);
			var pz_h = Math.floor(pz/10);
			var pz_w = pz % 10;

			if (pz_h!=1 && (pz_h==2 || top < ietruebody().clientHeight/2)) {
				top += getTop() + obj.offsetHeight - 85;
			} else {
				top += getTop() - read.menu.offsetHeight - 60;
			}
			if (pz_w!=1 && (pz_w==2 || left > (ietruebody().clientWidth)*3/5)) {
				left -= read.menu.offsetWidth - obj.offsetWidth + getLeft();
			} else {
				left += getLeft();
			}
			read.menu.style.top  = top  + 'px';
			read.menu.style.left = left + 'px';
		}
	},

	InitMenu : function() {
		function setopen(a,b) {
			if (getObj(a)) {
				getObj(a).onmouseover = function(){read.open(b,a);}
			}
		}
		for (var i in openmenu)
			setopen(i,openmenu[i]);
	},

	IsShow : function() {
		return (read.menu.hasChildNodes() && read.menu.style.display != 'none') ? true : false;
	}
}
var read = new SkyucMenu();

function closep() {
	read.menu.style.display = 'none';
	read.menu.className = '';
}
function findPosX(obj) {
	var curleft = 0;
	if (obj.offsetParent) {
		while (obj.offsetParent) {
			curleft += obj.offsetLeft
			obj = obj.offsetParent;
		}
	} else if (obj.x) {
		curleft += obj.x;
	}
	return curleft - getLeft();
}
function findPosY(obj) {
	var curtop = 0;
	if (obj.offsetParent) {
		while (obj.offsetParent) {
			curtop += obj.offsetTop
			obj = obj.offsetParent;
		}
	} else if (obj.y) {
		curtop += obj.y;
	}
	return curtop - getTop();
}
function in_array(str,a){
	for (var i=0; i<a.length; i++) {
		if(str == a[i])	return true;
	}
	return false;
}
function loadjs(path, code, id) {
	if (typeof id == 'undefined') id = '';
	if (id != '' && IsElement(id)) {
		return false;
	}
	var header = document.getElementsByTagName("head")[0];
	var s = document.createElement("script");
	if (id) s.id  = id;
	if (path) {
		s.src = path;
	} else if (code) {
		s.text = code;
	}
	header.appendChild(s);
	return true;
}
function getObj(id) {
	return document.getElementById(id);
}
function getTop() {
	return typeof window.pageYOffset != 'undefined' ? window.pageYOffset:ietruebody().scrollTop;
}
function getLeft() {
	return (typeof window.pageXOffset != 'undefined' ? window.pageXOffset:ietruebody().scrollLeft)
}
function ietruebody() {
	return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body;
}
function IsElement(id) {
	return document.getElementById(id) != null ? true : false;
}

function getSkyucBox(type){
	if (getObj('skyuc_box')) {
		return getObj('skyuc_box');
	}
	var skyuc_box	= elementBind('div','skyuc_box','','position:absolute');
	document.body.appendChild(skyuc_box);
	return skyuc_box;
}

function getSkyucContainer(){
	if (getObj('skyuc_box')) {
		var skyuc_box = getObj('skyuc_box');
	} else {
		var skyuc_box = getSkyucBox();
	}
	if (getObj('box_container')) {
		return getObj('box_container');
	}
	skyuc_box.innerHTML = '<div class="popout"><table  border="0" cellspacing="0" cellpadding="0"><tbody><tr><td class="bgcorner1"></td><td class="pobg1"></td><td class="bgcorner2"></td></tr><tr><td class="pobg4"></td><td><div class="popoutContent" id="box_container"></div></td><td class="pobg2"></td></tr><tr><td class="bgcorner4"></td><td class="pobg3"></td><td class="bgcorner3"></td></tr></tbody></table></div>';
	var popoutContent = getObj('box_container');
	return popoutContent;
}
function elementBind(type,id,stylename,csstext){
	var element = document.createElement(type);
	if (id) {
		element.id = id;
	}
	if (typeof(stylename) == 'string') {
		element.className = stylename;
	}
	if (typeof(csstext) == 'string') {
		element.style.cssText = csstext;
	}
	return element;
}

function addChild(parent,type,id,stylename,csstext){
	parent = objCheck(parent);
	var child = elementBind(type,id,stylename,csstext);
	parent.appendChild(child);
	return child;
}

function delElement(id){
	id = objCheck(id);
	id.parentNode.removeChild(id);
}

function opencode(menu,td)
{
	if (read.IsShow() && read.menu.firstChild.id == 'verifyimage') return;
	read.open(menu,td,2,0);
	getimage();
	document.onclick = function(e)
	{
		var o = Utils.srcElement(e);
		if (o == td)
		{
			return;
		}
		else if (o.id == 'verifyimage')
		{
			getimage();
		}
		else
		{
			closep();
			document.onclick = '';
		}
	}
}

/* *
 * 处理会员登录的反馈信息
 */
function signInResponse(result)
{
  toggleLoader(false);

  var done    = result.substr(0, 1);
  var content = result.substr(2);

  if (done == 1)
  {
    document.getElementById('member-zone').innerHTML = content;
  }
  else
  {
    alert(content);
  }
}

/* *
 * 评论的翻页函数
 */
function gotoPage(page, id, type)
{
  Ajax.call('ajax.php?do=comment&act=gotopage', 'page=' + page + '&id=' + id + '&type=' + type, gotoPageResponse, 'GET', 'JSON');
}

function gotoPageResponse(result)
{
  document.getElementById("SKYUC_COMMENT").innerHTML = result.content;
}

/* *
 *  返回属性列表
 */
function getAttr(cat_id)
{
  var tbodies = document.getElementsByTagName('tbody');
  for (i = 0; i < tbodies.length; i ++ )
  {
    if (tbodies[i].id.substr(0, 10) == 'show_type')tbodies[i].style.display = 'none';
  }

  var type_body = 'show_type_' + cat_id;
  try
  {
    document.getElementById(type_body).style.display = '';
  }
  catch (e)
  {
  }
}

//显示所有分类
function showCatalog(obj)
{
  var pos = getCoordinate(obj);
  var div = document.getElementById('SKYUC_CATALOG');

  if (div && div.style.display != 'block')
  {
    div.style.display = 'block';
    div.style.left = pos.x + "px";
    div.style.top = (pos.y + obj.offsetHeight - 1) + "px";
  }
}
//获取坐标
function getCoordinate(obj)
{
  var pos =
  {
    "x" : 0, "y" : 0
  }

  pos.x = document.body.offsetLeft;
  pos.y = document.body.offsetTop;

  do
  {
    pos.x += obj.offsetLeft;
    pos.y += obj.offsetTop;

    obj = obj.offsetParent;
  }
  while (obj.tagName.toUpperCase() != 'BODY')

  return pos;
}
//隐藏所有分类
function hideCatalog(obj)
{
  var div = document.getElementById('SKYUC_CATALOG');

  if (div && div.style.display != 'none') div.style.display = "none";
}

//发送邮件验证
function sendHashMail()
{
  Ajax.call('user.php?act=send_hash_mail', '', sendHashMailResponse, 'GET', 'JSON')
}

//发送邮件验证返回消息
function sendHashMailResponse(result)
{
  alert(result.message);
}

/* 弹出播放窗口 */
function OpenPlay(mov_id,look_id,width,height,player)
{
	var xposition = (screen.width - width)/2;
	var yposition = (screen.height - height)/2;
	var htmurl = "player.php?mov_id="+mov_id+"&look_id="+look_id+"&player="+player+"";
	window.open(htmurl,'SKYUC','toolbar=no,Directories=no,location=no,Status=no,,menubar=no,resizable=1,scrollbars=no,width='+width+',height='+height+',left='+xposition+',top='+yposition)
}


/* 分类页显示方式 */
function display_mode(str)
{
    document.getElementById('display').value = str;
	setTimeout(doSubmit, 0);
	function doSubmit() {document.forms['listform'].submit();}
}


function hash(string, length)
{
  var length = length ? length : 32;
  var start = 0;
  var i = 0;
  var result = '';
  filllen = length - string.length % length;
  for(i = 0; i < filllen; i++)
  {
    string += "0";
  }
  while(start < string.length)
  {
    result = stringxor(result, string.substr(start, length));
    start += length;
  }
  return result;
}

function stringxor(s1, s2)
{
  var s = '';
  var hash = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  var max = Math.max(s1.length, s2.length);
  for(var i=0; i<max; i++)
  {
    var k = s1.charCodeAt(i) ^ s2.charCodeAt(i);
    s += hash.charAt(k % 52);
  }
  return s;
}

//　AJAX会员同步登陆
function hash(string, length)
{
  var length = length ? length : 32;
  var start = 0;
  var i = 0;
  var result = '';
  filllen = length - string.length % length;
  for(i = 0; i < filllen; i++)
  {
    string += "0";
  }
  while(start < string.length)
  {
    result = stringxor(result, string.substr(start, length));
    start += length;
  }
  return result;
}

function stringxor(s1, s2)
{
  var s = '';
  var hash = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  var max = Math.max(s1.length, s2.length);
  for(var i=0; i<max; i++)
  {
    var k = s1.charCodeAt(i) ^ s2.charCodeAt(i);
    s += hash.charAt(k % 52);
  }
  return s;
}

var evalscripts = new Array();
function evalscript(s)
{
  if(s.indexOf('<script') == -1) return s;
  var p = /<script[^\>]*?src=\"([^\>]*?)\"[^\>]*?(reload=\"1\")?(?:charset=\"([\w\-]+?)\")?><\/script>/ig;
  var arr = new Array();
  while(arr = p.exec(s)) appendscript(arr[1], '', arr[2], arr[3]);
  return s;
}


function appendscript(src, text, reload, charset)
{
  var id = hash(src + text);
  if(!reload && in_array(id, evalscripts)) return;
  if(reload && document.getElementById(id))
  {
    document.getElementById(id).parentNode.removeChild(document.getElementById(id));
  }
  evalscripts.push(id);
  var scriptNode = document.createElement("script");
  scriptNode.type = "text/javascript";
  scriptNode.id = id;
  //scriptNode.charset = charset;
  try
  {
    if(src)
    {
      scriptNode.src = src;
    }
    else if(text)
    {
      scriptNode.text = text;
    }
    document.getElementById('append_parent').appendChild(scriptNode);
  }
  catch(e)
  {}
}

function in_array(needle, haystack)
{
  if(typeof needle == 'string' || typeof needle == 'number')
  {
    for(var i in haystack)
    {
      if(haystack[i] == needle)
      {
        return true;
      }
    }
  }
  return false;
}



// 整合ucenter后pm.php弹出窗口
var pmwinposition = new Array();

var userAgent = navigator.userAgent.toLowerCase();
var is_opera = userAgent.indexOf('opera') != -1 && opera.version();
var is_moz = (navigator.product == 'Gecko') && userAgent.substr(userAgent.indexOf('firefox') + 8, 3);
var is_ie = (userAgent.indexOf('msie') != -1 && !is_opera) && userAgent.substr(userAgent.indexOf('msie') + 5, 3);
function pmwin(action, param)
{
  var objs = document.getElementsByTagName("OBJECT");
  if(action == 'open')
  {
    for(i = 0;i < objs.length; i ++)
    {
      if(objs[i].style.visibility != 'hidden')
      {
        objs[i].setAttribute("oldvisibility", objs[i].style.visibility);
        objs[i].style.visibility = 'hidden';
      }
    }
    var clientWidth = document.body.clientWidth;
    var clientHeight = document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight;
    var scrollTop = document.body.scrollTop ? document.body.scrollTop : document.documentElement.scrollTop;
    var pmwidth = 800;
    var pmheight = clientHeight * 0.9;
    if(!document.getElementById('pmlayer'))
    {
      div = document.createElement('div');div.id = 'pmlayer';
      div.style.width = pmwidth + 'px';
      div.style.height = pmheight + 'px';
      div.style.left = ((clientWidth - pmwidth) / 2) + 'px';
      div.style.position = 'absolute';
      div.style.zIndex = '999';
      document.getElementById('append_parent').appendChild(div);
      document.getElementById('pmlayer').innerHTML = '<div style="width: 800px; background: #666666; margin: 5px auto; text-align: left">' +
        '<div style="width: 800px; height: ' + pmheight + 'px; padding: 1px; background: #FFFFFF; border: 1px solid #7597B8; position: relative; left: -6px; top: -3px">' +
        '<div onmousedown="pmwindrag(event, 1)" onmousemove="pmwindrag(event, 2)" onmouseup="pmwindrag(event, 3)" style="cursor: move; position: relative; left: 0px; top: 0px; width: 800px; height: 30px; margin-bottom: -30px;"></div>' +
        '<a href="###" onclick="pmwin(\'close\')"><img style="position: absolute; right: 20px; top: 15px" src="data/images/close.gif" title="关闭" /></a>' +
        '<iframe id="pmframe" name="pmframe" style="width:' + pmwidth + 'px;height:100%" allowTransparency="true" frameborder="0"></iframe></div></div>';
    }
    document.getElementById('pmlayer').style.display = '';
    document.getElementById('pmlayer').style.top = ((clientHeight - pmheight) / 2 + scrollTop) + 'px';
    if(!param)
    {
        pmframe.location = 'pm.php';
    }
    else
    {
        pmframe.location = 'pm.php?' + param;
    }
  }
  else if(action == 'close')
  {
    for(i = 0;i < objs.length; i ++)
    {
      if(objs[i].attributes['oldvisibility'])
      {
        objs[i].style.visibility = objs[i].attributes['oldvisibility'].nodeValue;
        objs[i].removeAttribute('oldvisibility');
      }
    }
    hiddenobj = new Array();
    document.getElementById('pmlayer').style.display = 'none';
  }
}

var pmwindragstart = new Array();
function pmwindrag(e, op)
{
  if(op == 1)
  {
    pmwindragstart = is_ie ? [event.clientX, event.clientY] : [e.clientX, e.clientY];
    pmwindragstart[2] = parseInt(document.getElementById('pmlayer').style.left);
    pmwindragstart[3] = parseInt(document.getElementById('pmlayer').style.top);
    doane(e);
  }
  else if(op == 2 && pmwindragstart[0])
  {
    var pmwindragnow = is_ie ? [event.clientX, event.clientY] : [e.clientX, e.clientY];
    document.getElementById('pmlayer').style.left = (pmwindragstart[2] + pmwindragnow[0] - pmwindragstart[0]) + 'px';
    document.getElementById('pmlayer').style.top = (pmwindragstart[3] + pmwindragnow[1] - pmwindragstart[1]) + 'px';
    doane(e);
  }
  else if(op == 3)
  {
    pmwindragstart = [];
    doane(e);
  }
}

function doane(event)
{
  e = event ? event : window.event;
  if(is_ie)
  {
    e.returnValue = false;
    e.cancelBubble = true;
  }
  else if(e)
  {
    e.stopPropagation();
    e.preventDefault();
  }
}
