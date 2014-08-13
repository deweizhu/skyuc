
(function($){
	$.fn.qvod = function(options){
		var isIE = true;
		var hc = false;
		var QvodIEFF;
		var FirstLoad = true;
		var _timer;
		var defaults = {
			/*播放器展示方式,默认为填充到div层里*/
			type: "default",
			/*播放器的宽度*/
			width: "650",
			/*播发器的高度*/
			height: "570", 
			/*自动播放 默认开启*/
			AutoPlay: "true", 
			/*自动全屏 默认关闭*/
			FullScreen: "false",
			/*播放器插入的位置，标签的id属性*/
			PlayerArea: "",	
			/*本集资源地址*/
			QvodUrl: "",
			/*下一集播放页地址*/
			NextWebPage: "",
			/*下一集资源地址，预缓冲时使用*/
			NextQvod: "",
			/*是否显示控制栏，0=不显示  1= 显示 默认参数是显示*/
			ShowControl: "1",
			/*缓冲广告 注：3.0.0.58及将来发布的客户端版本才支持*/
			AdUrl: "http://buffer-ad.qvod.com/"

	}/*配置参数结束*/
	
	/*参数的转换begin*/
	var options = $.extend(defaults, options);
	if(options.AutoPlay == "true"){ options.AutoPlay = "1"; }else{options.AutoPlay = "0";}
	
	/*参数的转换end*/

	this.each(function(){
		
		/*获取播放标题*/
		if(options.type == "default")
		{
			insert_player(options.QvodUrl);
		}
		/*第一次加载自动全屏*/
		if(FirstLoad)
		{
			/*站长设置的自动全屏*/
			if(options.FullScreen == "true" && $.cookie("qvodplayerautofull") == null){
				autofull();
			}
			/*用户上一集播放结束为全屏 则*/
			if($.cookie("qvodplayerautofull") == "true")
			{
				autofull();
			}
			/*延迟10s执行检查全屏*/
			setTimeout(function(){
				IsFull();
			},1000*10);		
			
			/*延迟20s执行检查未全屏*/
			setTimeout(function(){
				IsNotFull();
			},1000*20);	

			FirstLoad = false;
		}
		/*检查浏览器的兼容*/		
		CheckIsIe();
		/*预缓冲下一集*/
		setInterval(function(){
			PreBufferNext();
		},1000);
		

	});
	/*检测用户上一次是否全屏播放结束*/			
	function IsFull(){
		_timer = setInterval(function(){
					var pos = true;
					pos = QvodIEFF.Full;
					if(pos)
					{
						$.cookie("qvodplayerautofull", true);
					}
				},300);
	}
	/*检查用户正在播放时，不是全屏的状态*/
	function IsNotFull()
	{
		setInterval(function(){
			var _pos = true;
			_pos = QvodIEFF.Full;
			if(!_pos)
			{
				$.cookie("qvodplayerautofull", false);
			}
		},1000);
	}
	/*缓冲下一集*/
	function PreBufferNext()
	{
		var nexturl = options.NextQvod;
		var duration = QvodIEFF.Duration;
		if( duration > 0)
		{
			if(QvodIEFF.get_CurTaskProcess()==1000 && hc==false && typeof(nexturl)!="undefined") 
			{                
			   QvodIEFF.StartNextDown(nexturl);
			   hc = true;
			}
		}
	}
	/*自动全屏*/
	function autofull()
	{
		setTimeout(function(){
			QvodIEFF.Full = true;
		},500);
	}
	/*向指定的标签里插入播放器*/
	function insert_player(qvod_address)
	{
		if(options.PlayerArea == "")
		{
			alert("\u8bf7\u8bbe\u7f6e\u64ad\u653e\u5668\u63d2\u5165\u4f4d\u7f6e\u7684\u6807\u7b7e\u0049\u0044");
			return;
		}
		var html = '<iframe id="iframe_down" name="iframe_down" scrolling="no" frameborder="0" style="margin: 0; width: 100%; height: 100%; display: none;" src=""></iframe>'+
		'<div id="qvod2011"><div class="playerarea" style="width:650px; heigth:570px; margin:auto;">'+
		'<object classid="clsid:F3D0D36F-23F8-4682-A195-74C92B03D4AF" width="'+options.width+'" height="'+options.height+'" id="QvodPlayer" '+
			'name="QvodPlayer" onError="document.getElementById(\'QvodPlayer\').style.display=\'none\';'+
			'document.getElementById(\'iframe_down\').style.display=\'\';'+
			'document.getElementById(\'iframe_down\').src=\'http://error2.qvod.com/error4.htm\';'+
			'document.getElementById(\'qvod2011\').style.display=\'none\';">'+
			'<param name="URL" value="'+qvod_address+'" />'+
			'<param name="Showcontrol" value="'+options.ShowControl+'" />'+
			'<param name="NextWebPage" value="'+options.NextWebPage+'" />'+
			'<param name="Autoplay" value="'+options.AutoPlay+'" />'+
			'<param name="QvodAdUrl" value="'+options.AdUrl+'" />'+
			'<embed id="QvodPlayer2" name="QvodPlayer2" width="'+options.width+'" height="'+options.height+'" URL="'+qvod_address+'" '+
			'type="application/qvod-plugin" Autoplay="'+options.AutoPlay+'" QvodAdUrl="'+options.AdUrl+'" nextwebpage="'+options.NextWebPage+
			'" Showcontrol="'+options.ShowControl+'" /></object></div>';
		$("#"+options.PlayerArea).html(html);

	}
	
	/*兼容各种浏览器中的错误提示信息*/
	function CheckIsIe()
	{
		var checkIEorFirefox = {};
		 if(window.ActiveXObject){
			checkIEorFirefox.ie = "yes";
			QvodIEFF = document.getElementById("QvodPlayer");
		 }else{
			 isIE = false;
			checkIEorFirefox.firefox = "yes";
			QvodIEFF = document.getElementById("QvodPlayer2");

			var $E = function(){var c=$E.caller; while(c.caller)c=c.caller; return c.arguments[0]};
			__defineGetter__("event", $E);
		 }
		
        try{
			QvodIEFF.CallFunction("ab");			
		}catch(e){
			if(!isIE){
			QvodIEFF.style.display='none';
			document.getElementById('iframe_down').style.display='';			
			document.getElementById('qvod2011').style.display='none';
			document.getElementById('iframe_down').src='http://error2.qvod.com/error4.htm';
			}
			return;
		}
	}
	/*end*/

};
})(jQuery);

/*cookie插件*/
jQuery.cookie=function(name,value,options){if(typeof value!='undefined'){options=options||{};if(value===null){value='';options.expires=-1}var expires='';if(options.expires&&(typeof options.expires=='number'||options.expires.toUTCString)){var date;if(typeof options.expires=='number'){date=new Date();date.setTime(date.getTime()+(options.expires*24*60*60*1000))}else{date=options.expires}expires='; expires='+date.toUTCString()}var path=options.path?'; path='+options.path:'';var domain=options.domain?'; domain='+options.domain:'';var secure=options.secure?'; secure':'';document.cookie=[name,'=',encodeURIComponent(value),expires,path,domain,secure].join('')}else{var cookieValue=null;if(document.cookie&&document.cookie!=''){var cookies=document.cookie.split(';');for(var i=0;i<cookies.length;i++){var cookie=jQuery.trim(cookies[i]);if(cookie.substring(0,name.length+1)==(name+'=')){cookieValue=decodeURIComponent(cookie.substring(name.length+1));break}}}return cookieValue}};