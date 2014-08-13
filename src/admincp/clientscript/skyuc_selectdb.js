var region = new Object();

region.isAdmin = false;

region.loadRegions = function(parent,  target)
{
  Ajax.call(region.getFileName(), 'target=' + target + "&parent=" + parent , region.response, "GET", "JSON");
}


/* *
 * 载入指定的表下所有的列
 *
 * @country integer     表名称
 * @selName     string  列名称
 */
region.loadFields = function(parent,  selName)
{
  var objName = (typeof selName == "undefined") ? "selFields" : selName;
  region.loadRegions(parent,  objName);
}


/* *
 * 处理下拉列表改变的函数
 *
 * @obj     object  下拉列表
 * @selName string  目标列表框的名称
 */
region.changed = function(obj,  selName)
{
  var parent = obj.options[obj.selectedIndex].value;
  region.loadRegions(parent,  selName);
}

region.response = function(result, text_result)
{
  var sel = document.getElementById(result.target);

  sel.length = 1;
  sel.selectedIndex = 0;
  sel.style.display = (result.regions.length == 0 && ! region.isAdmin ) ? "none" : '';

  if (document.all)
  {
    sel.fireEvent("onchange");
  }
  else
  {
    var evt = document.createEvent("HTMLEvents");
    evt.initEvent('change', true, true);
    sel.dispatchEvent(evt);
  }

  if (result.regions)
  {
	var text = result.regions;
    var fileds = text.split(",");
	enterValue(fileds, sel);
  }
}

function enterValue(cell,place){
	clearPreValue(place);
	var selectedval = cell[0];
	for(i=0; i<cell.length; i++){
	    isselected = addOption(place, cell[i], cell[i]);
		if(isselected){
			place.options[i].selected = true;
			selectedval = cell[i];
		}
	}
	return selectedval;
}

function addOption(objSelectNow,txt,val){
	var objOption = document.createElement("option");
	objOption.text = txt;
	objOption.value = val;
	objSelectNow.options.add(objOption);
	return true;
}

function clearPreValue(pc){
	while(pc.hasChildNodes())
	pc.removeChild(pc.childNodes[0]);
}

region.getFileName = function()
{
  if (region.isAdmin)
  {
    return "./sql.php?act=getfields";
  }
  else
  {
    return '';
  }
}
