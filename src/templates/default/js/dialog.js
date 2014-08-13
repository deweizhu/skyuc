function dialog(){
	var theY = 0;
	(document.documentElement.clientHeight>=document.body.clientHeight&&document.documentElement.clientHeight)?theY = document.documentElement.clientHeight:theY = document.body.clientHeight;
	var title = '';
	var width = 400;
	var height = 170;
	var src = '../images/pic_haibao.jpg';
	var path = '../images/';
	var sFunc = '<input id="dialogOk" type="button" style="width:80px;height:34px;border:0;background:url(\'' + path + 'openbox1_btn2.gif\');line-height:22px;text-align:center;" value="确  认" onclick="new dialog().reset();" /> <input id="dialogCancel" type="button" style="width:80px;height:34px;border:0;background:url(\'' + path + 'openbox1_btn2.gif\');line-height:22px;text-align:center;" value="取  消" onclick="new dialog().reset();" />';
	var sClose = '<input type="image" id="dialogBoxClose" onclick="new parent.dialog().reset();" src="' + path + 'openbox4_x.gif" border="0" title="关闭" alt="关闭" width="28" height="25" align="absmiddle" />';
	var sBox = '\
		<div id="dialogBox" style="width:' + width + 'px;height:' + height + 'px;display:none;z-index:11;">\
			<table onselectstart="return false;" style="-moz-user-select:none;" width="100%" border="0" cellpadding="0" cellspacing="0">\
				<tr height="5" bgcolor="#FFA73E"><td colspan="4"></td></tr>\
				<tr height="25" bgcolor="#FF9518">\
					<td width="20" height="25"></td>\
					<td id="dialogBoxTitle" style="font-size:14px;font-weight:bold;color:#fff;cursor:move;" onmousedown="new dialog().moveStart(event, \'dialogBox\')">系统提示</td>\
					<td id="dialogClose" width="28">' + sClose + '</td>\
					<td width="10"></td>\
				</tr>\
			</table>\
			<div id="dialogBody" style="border:5px solid #FF9518; background-color:#fff;">\
				<div style="margin:10px;">\
					<div id="dialogMsg"></div>\
					<div id="dialogFunc" style="text-align:center;margin-top:10px;">' + sFunc + '</div>\
				</div>\
				<div style="clear:both;line-height:0px;font-size:0px;height:0px;"></div>\
			</div>\
		</div>\
		<div id="dialogBoxShadow" style="display:none;z-index:10;"></div>\
	';
	function $(_sId){return window.top.document.getElementById(_sId)}
	this.show = function(){$('dialogBody') ? function(){} : this.init();this.middle('dialogBox');this.shadow();}
	this.reset = function(){
		this.hideModule('select', '');
		$('dialogBox').style.display = 'none';
		$('dialogBoxShadow').style.display = 'none';
	}
	this.html = function(_sHtml){$("dialogBody").innerHTML = _sHtml;this.show();}
	this.init = function(){
		$('dialogCase') ? $('dialogCase').parentNode.removeChild($('dialogCase')) : function(){};
		var oDiv = document.createElement('span');
		oDiv.id = "dialogCase";
		oDiv.innerHTML = sBox;
		document.body.appendChild(oDiv);
	}
	this.button = function(_sId, _sFuc){
		if($(_sId)){
			$(_sId).style.display = '';
			if($(_sId).addEventListener){
				if($(_sId).act){$(_sId).removeEventListener('click', function(){eval($(_sId).act)}, false);}
				$(_sId).act = _sFuc;
				$(_sId).addEventListener('click', function(){eval(_sFuc)}, false);
			}else{
				if($(_sId).act){$(_sId).detachEvent('onclick', function(){eval($(_sId).act)});}
				$(_sId).act = _sFuc;
				$(_sId).attachEvent('onclick', function(){eval(_sFuc)});
			}
		}
	}
	this.shadow = function(){
		var oShadow = $('dialogBoxShadow');
		oShadow['style']['position'] = 'absolute';
		oShadow['style']['background']	= '#cccccc';
		oShadow['style']['display']	= '';
		oShadow['style']['opacity']	= '0.2';
		oShadow['style']['filter'] = 'alpha(opacity=20)';
		oShadow['style']['top'] = '0px';
		oShadow['style']['left'] = '0px';
		oShadow['style']['width'] = (window.top.document.documentElement.clientWidth)+'px';
		oShadow['style']['height'] = (theY)+'px';
	}
	this.open = function(_sUrl, _sMode){
		$('dialogBox') ? function(){} : this.init();
		this.shadow();
		if(!_sMode || _sMode == "no" || _sMode == "yes"){
			$('dialogBox').innerHTML = "<iframe id='dialogFrame' name='dialogFrame' width='100%' height='100%' frameborder='0' scrolling='" + _sMode + "' allowtransparency='true'></iframe>";
			$('dialogFrame').src = _sUrl;
		}
		this.middle('dialogBox');
	}
	this.showWindow = function(_sUrl, _iWidth, _iHeight, _sMode){
		var oWindow;
		var sLeft = (screen.width) ? (screen.width - _iWidth)/2 : 0;
		//var sTop = (screen.height) ? (screen.height - _iHeight)/2 : 0;
		var sTop = (screen.height) ? 800 : 0;
		if(window.showModalDialog && _sMode == "m"){
			oWindow = window.showModalDialog(_sUrl,"","dialogWidth:" + _iWidth + "px;dialogheight:" + _iHeight + 'px');
		} else {
			oWindow = window.open(_sUrl, '', 'height=' + _iHeight + ', width=' + _iWidth + ', top=' + sTop + ', left=' + sLeft + ', toolbar=no, menubar=no, scrollbars=' + _sMode + ', resizable=no,location=no, status=no');
		}
	}
	this.set = function(_oAttr, _sVal){
		var oDialog = $('dialogBox');
		if(_sVal != ''){
			switch(_oAttr){
				case 'title':
					$('dialogBoxTitle').innerHTML = _sVal;
					title = _sVal;
					break;
				case 'width':
					oDialog['style']['width'] = _sVal+'px';
					width = _sVal;
					break;
				case 'height':
					oDialog['style']['height'] = _sVal+'px';
					height = _sVal;
					break;
				case 'src':
					$('dialogBoxFace') ? $('dialogBoxFace').src = _sVal : function(){};
					src = _sVal;
					break;
				case 'b_ok':
					$('dialogOk') ? $('dialogOk').value = _sVal : function(){};
					break;
				case 'b_esc':
					$('dialogCancel') ? $('dialogCancel').value = _sVal : function(){};
					break;
			}
		}
	}
	this.hideModule = function(_sType, _sDisplay){
		var aIframe = parent.document.getElementsByTagName("iframe");aIframe=0;
		var aType = document.getElementsByTagName(_sType);
		var iChildObj, iChildLen;
		for (var i = 0; i < aType.length; i++){
			aType[i].style.display	= _sDisplay;
		}
		for (var j = 0; j < aIframe.length; j++){
			iChildObj = document.frames ? document.frames[j] : aIframe[j].contentWindow;
			iChildLen = iChildObj.document.body.getElementsByTagName(_sType).length;
			for (var k = 0; k < iChildLen; k++){
				iChildObj.document.body.getElementsByTagName(_sType)[k].style.display = _sDisplay;
			}
		}
	}
	this.middle = function(_sId){//确认位置
		$(_sId)['style']['display'] = '';
		$(_sId)['style']['position'] = 'absolute';
		$(_sId)['style']['left'] = (window.top.document.documentElement.clientWidth/2) - ($(_sId).offsetWidth/2)+'px';
		$(_sId)['style']['top'] = (window.top.document.documentElement.scrollTop + window.top.document.documentElement.clientHeight/2 - $(_sId).offsetHeight/2)+'px';
	}
	this.moveStart = function (event, _sId){
		var oObj = $(_sId);
		oObj.onmousemove = mousemove;
		oObj.ondragstart = mousemove;
		oObj.onmouseup = mouseup;
		oObj.setCapture ? oObj.setCapture() : function(){};
		oEvent = event ? event : (window.event ? window.event : null);//window.event ? window.event : event;
		var dragData = {x : oEvent.clientX, y : oEvent.clientY};
		var backData = {x : parseInt(oObj.style.top), y : parseInt(oObj.style.left)};
		function mousemove(event){
			var oEvent = event ? event : (window.event ? window.event : null);//window.event ? window.event : event;
			var iLeft = oEvent.clientX - dragData["x"] + parseInt(oObj.style.left);
			var iTop = oEvent.clientY - dragData["y"] + parseInt(oObj.style.top);
			oObj.style.left = iLeft+'px';
			oObj.style.top = iTop+'px';
			dragData = {x: oEvent.clientX, y: oEvent.clientY};
		}
		function mouseup(event){
			var oEvent = event ? event : (window.event ? window.event : null);//window.event ? window.event : event;
			oObj.onmousemove = null;
			oObj.onmouseup = null;
			if(oEvent.clientX < 1 || oEvent.clientY < 1 || oEvent.clientX > document.body.clientWidth || oEvent.clientY > document.body.clientHeight){
				oObj.style.left = backData.y+'px';
				oObj.style.top = backData.x+'px';
			}
			oObj.releaseCapture ? oObj.releaseCapture() : function(){};
		}
	}
	this.event = function(_sMsg, _sOk, _sCancel, _sClose){
		$('dialogFunc').innerHTML = sFunc;
		$('dialogClose').innerHTML = sClose;
		$('dialogMsg') ? $('dialogMsg').innerHTML = _sMsg : function(){};
		this.show();
		_sOk ? this.button('dialogOk', _sOk) | $('dialogOk').focus() : $('dialogOk').style.display = 'none';
		_sCancel ? this.button('dialogCancel', _sCancel) : $('dialogCancel').style.display = 'none';
		_sClose ? this.button('dialogBoxClose', _sClose) : function(){};
		//_sOk ? this.button('dialogOk', _sOk) : _sOk == "" ? function(){} : $('dialogOk').style.display = 'none';
		//_sCancel ? this.button('dialogCancel', _sCancel) : _sCancel == "" ? function(){} : $('dialogCancel').style.display = 'none';
	}
}
document.onkeydown = function(e){ 	
	e == null ? Key = event.keyCode : Key = e.which;
	switch (Key) {
		case 27:
		new dialog().reset();
		break;
	}
};
function openbox(w,h,_str){
	var E = new dialog();
	E.init();
	E.set('width',w);
	E.set('height',h);
	E.open(_str,'no');
}
function opened(Info){
	var w = new dialog();
	w.init();
	w.event(Info, 'closer();','','');
}
function opener(Info,ok,w,h){
	var ws = new dialog();
	ws.init();
	ws.set('width',w);
	ws.set('height',h);
	ws.event(Info, ok,'','');
}
function closer(){}