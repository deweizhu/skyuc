function setFavorite()
{
	sURL = 'http://www.skyuc.com/';
	sTitle = '天空网络';
	if(document.all){
		window.external.AddFavorite(sURL, sTitle);
	}else{
		window.sidebar.addPanel(sTitle, sURL, "");
	}
}
function setHome()
{
	if (document.all){
		document.body.style.behavior='url(#default#homepage)';
		document.body.setHomePage('http://www.skyuc.com');
	}else if(window.sidebar){
		if(window.netscape){
			try{
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			}catch (e){
				opened("该操作被浏览器拒绝，如果想启用该功能，请在地址栏内输入 about:config,然后将项 signed.applets.codebase_principal_support 值该为true");
			}
		}
		var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components. interfaces.nsIPrefBranch);
			prefs.setCharPref('browser.startup.homepage','http://www.skyuc.com');
	}
}

function rsstry(_sUrl)
{
    try {
        new ActiveXObject("SinaRss.RssObject");
        window.open(_sUrl, "_self");
    } 
    catch (e) {
        window.open(_sUrl);
    }
}



function getFunShowMore(firstId)
{
    var showId = firstId;
    return function(currShowId){
        document.getElementById(showId).style.display = "none";
        document.getElementById(currShowId).style.display = "";
        showId = currShowId;
    };
}

function CreateLabelChangeFun(firstId, changeMode, headerShowClass, headerHiddenClass) 
{
	headerShowClass = headerShowClass || '';
	headerHiddenClass = headerHiddenClass || '';
	var headerShowId = firstId;
	var divShowId = firstId + '_div';
	return function(currHeaderShowId) {
		if (currHeaderShowId == headerShowId)
			return;
		var currDivShowId = currHeaderShowId + '_div';
		if (changeMode == 0) {
			document.getElementById(currHeaderShowId).className = headerShowClass;
			document.getElementById(headerShowId).className = headerHiddenClass;

		} else if (changeMode == 1) {
			document.getElementById(headerShowId).src = document
					.getElementById(headerShowId).src.replace('s.jpg', '.jpg').replace('s.gif','.gif');
			if (document.getElementById(currHeaderShowId).src.indexOf('s.jpg') == -1&&document.getElementById(currHeaderShowId).src.indexOf('s.gif')==-1) {
				document.getElementById(currHeaderShowId).src = document
						.getElementById(currHeaderShowId).src.replace('.jpg',
						's.jpg').replace('.gif','s.gif');
			}
		}
		document.getElementById(divShowId).style.display = 'none';
		document.getElementById(currDivShowId).style.display = '';
		headerShowId = currHeaderShowId;
		divShowId = currDivShowId;
	}
}

function CreateFunLabelChange(IDHeader, changeMode, headerShowClass, headerHiddenClass)
{
    headerShowClass = headerShowClass || '';
    headerHiddenClass = headerHiddenClass || '';
    var headerShowId = IDHeader + '1';
    var divShowId = IDHeader + '1_div';
    return function(currHeaderShowId){
        if (currHeaderShowId == headerShowId) 
            return;
        var currDivShowId = currHeaderShowId + '_div';
        if (changeMode == 0) {
            document.getElementById(currHeaderShowId).className = headerShowClass;
            document.getElementById(headerShowId).className = headerHiddenClass;
        }
        else 
            if (changeMode == 1) {
                document.getElementById(headerShowId).src = document.getElementById(headerShowId).src.replace('s.jpg', '.jpg');
                if (document.getElementById(currHeaderShowId).src.indexOf('s.jpg') == -1) {
                    document.getElementById(currHeaderShowId).src = document.getElementById(currHeaderShowId).src.replace('.jpg', 's.jpg');
                }
            }
        document.getElementById(divShowId).style.display = 'none';
        document.getElementById(currDivShowId).style.display = '';
        headerShowId = currHeaderShowId;
        divShowId = currDivShowId;
    }
    
}