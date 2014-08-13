/* ObjectSwap - Bypasses the new ActiveX Activation requirement in Internet Explorer by swapping existing ActiveX objects on the page with the same objects. Can also be used for Flash version detection by adding the param:
<param name="flashVersion" value="8" /> to the object tag.

Author: Karina Steffens, www.neo-archaic.net
Created: April 2006
Changes and bug fixes: May 2006
Bug fixes: June 2006
Changes: October 2006 (Included Opera9 and excluded IE5.5)
*/

//Check if the browser is InternetExplorer, and if it supports the getElementById DOM method
var ie = (document.defaultCharset && document.getElementById && !window.home);
var opera9 = false;
if (ie){
	//Check for ie 5.5 and exclude it from the script
	var ver=navigator.appVersion.split("MSIE")
	ver=parseFloat(ver[1])
	ie = (ver >=6)
}else if (navigator.userAgent.indexOf("Opera")!=-1) {
	//Check for Opera9 and include it in the ObjectSwap
	var versionindex=navigator.userAgent.indexOf("Opera")+6
	if (parseInt(navigator.userAgent.charAt(versionindex))>=9)
	opera9 = true;
}
//Perform ObjectSwap if the browser is IE or Opera (if not just check flashVersion)
var oswap = (ie || opera9)

//Hide the object to prevent it from loading twice
if (oswap){
	document.write ("<style id='hideObject'> object{display:none;} </style>");
}

/*Replace all flash objects on the page with the same flash object, 
by rewriting the outerHTML values
This bypasses the new IE ActiveX object activation issue*/
objectSwap = function(){
	if (!document.getElementsByTagName){
		return;
	}
	//An array of ids for flash detection
	var stripQueue = [];
	//Get a list of all ActiveX objects
	var objects = document.getElementsByTagName('object');
	for (var i=0; i<objects.length; i++){			
		var o = objects[i];	
		var h = o.outerHTML;
		//The outer html omits the param tags, so we must retrieve and insert these separately
		var params = "";
		var hasFlash = true;
		for (var j = 0; j<o.childNodes.length; j++) {
			var p = o.childNodes[j];
			if (p.tagName == "PARAM"){
				//Check for version first - applies to all browsers
				//For this to work, a new param needs to be included in the object with the name "flashVersion" eg:
				//<param name="flashVersion" value="7" />
				if (p.name == "flashVersion"){
					hasFlash = detectFlash(p.value);
					if (!hasFlash){
						//Add the objects id to the list (create a new id if there's isn't one already)
						o.id = (o.id == "") ? ("stripFlash"+i) : o.id;
						stripQueue.push(o.id);
						break;
					}
				} 
				params += p.outerHTML;		       
			}
		}	
		if (!hasFlash){
			continue;
		}		
		//Only target internet explorer
		if (!oswap){
			continue;
		} 
		//Avoid specified objects, marked with a "noswap" classname
		if (o.className.toLowerCase().indexOf ("noswap") != -1){
			continue;
		}		
		//Get the tag and attributes part of the outer html of the object
		var tag = h.split(">")[0] + ">";			
		//Add up the various bits that comprise the object:
		//The tag with the attributes, the params and it's inner html
		var newObject = tag + params + o.innerHTML + " </OBJECT>";	
		//And rewrite the outer html of the tag 
		o.outerHTML = newObject;
	}
	//Strip flash objects
	if (stripQueue.length) {
		stripFlash(stripQueue)
	}
	//Make the objects visible again
	if (oswap){
		document.getElementById("hideObject").disabled = true;
	}
}

detectFlash = function(version){
	if(navigator.plugins && navigator.plugins.length){
		//Non-IE flash detection.
		var plugin = navigator.plugins["Shockwave Flash"];
		if (plugin == undefined){
			return false;
		}
		var ver = navigator.plugins["Shockwave Flash"].description.split(" ")[2];
		return (Number(ver) >= Number(version))
	} else if (ie && typeof (ActiveXObject) == "function"){
	//IE flash detection.
		try{
			var flash = new ActiveXObject("ShockwaveFlash.ShockwaveFlash." + version);
			return true;
		}
		catch(e){
			return false;
		}
	}
	//Catchall - skip detection
	return true;
}

//Loop through an array of ids to strip
//Replace the object by a div tag containing the same innerHTML.
//To display an alternative image, message for the user or a link to the flash installation page, place it inside the object tag.  
//For the usual object/embed pairs it needs to be enclosed in comments to hide from gecko based browsers.
stripFlash = function (stripQueue){
	if (!document.createElement){
		return;
	}
	for (var i=0; i<stripQueue.length; i++){
		var o = document.getElementById(stripQueue[i]);
		var newHTML = o.innerHTML;	
		//Strip the comments
		newHTML = newHTML.replace(/<!--\s/g, "");
		newHTML = newHTML.replace(/\s-->/g, "");
		//Neutralise the embed tag
		newHTML = newHTML.replace(/<embed/gi, "<span");		
		//Create a new div element with properties from the object
		var d = document.createElement("div");
		d.innerHTML = newHTML;
		d.className = o.className;
		d.id = o.id;
		//And swap the object with the new div
		o.parentNode.replaceChild(d, o);
	}
}

//Initiate the function without conflicting with the window.onload event of any preceding scripts
var tempFunc = window.onload;
window.onload = function(){
	if (typeof (tempFunc) == "function"){
		try{
			tempFunc();
		} catch(e){}
	}
	objectSwap();
}



















