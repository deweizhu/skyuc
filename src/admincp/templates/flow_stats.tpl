{include file="pageheader.tpl"}
<SCRIPT LANGUAGE="JavaScript">
<!--
	 $(document).ready(function()
			 {
				$('#truncate').click(function(){
					$('#theForm').submit();
				});
			 });
//-->
</SCRIPT>
<div class="form-div">
  <form action="" method="post" id="theForm">
		<input type="hidden" name="s" value="{$session.sessionhash}" />
		<input type="hidden" name="act" value="truncate" />
  </form>
  <form action="" method="post">
    {$lang.start_date}&nbsp;{html_select_date field_order="YMD" prefix="start_date" time=$start_date}&nbsp;&nbsp;
    {$lang.end_date}&nbsp;{html_select_date field_order="YMD" prefix="end_date" time=$end_date}&nbsp;&nbsp;
	<input type="hidden" name="s" value="{$session.sessionhash}" />
    <input type="submit" class="button primary submitButton" name="submit" value="{$lang.access_query}" /> &nbsp;&nbsp;&nbsp;&nbsp;<input type="button" class="button"  id="truncate" value="{$lang.do_truncate}" >
  </form>
</div>

<div class="tab-div">
    <!-- tab bar -->
    <div id="tabbar-div">
      <p>
        <span class="tab-front" id="general-tab">{$lang.tab_general}</span><span
        class="tab-back" id="area-tab">{$lang.tab_area}</span><span
        class="tab-back" id="from-tab">{$lang.tab_from}</span>
      </p>
    </div>

    <!-- tab body -->
    <div id="tabbody-div">
        <!-- 综合流量 -->
        <table width="90%" id="general-table">
          <tr><td align="center">
            <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
              codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"
              width="565" height="420" id="FCColumn2" align="middle">
              <PARAM NAME="FlashVars" value="&dataXML={$general_data}">
              <PARAM NAME=movie VALUE="images/charts/line.swf?chartWidth=650&chartHeight=400">
              <param NAME="quality" VALUE="high">
              <param NAME="bgcolor" VALUE="#FFFFFF">
              <embed src="images/charts/line.swf?chartWidth=650&chartHeight=400" FlashVars="&dataXML={$general_data}" quality="high" bgcolor="#FFFFFF"  width="650" height="400" name="FCColumn2" align="middle" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">
            </object>
          </td></tr>
        </table>
        <!-- 地区分布 -->
        <table width="90%" id="area-table" style="display:none">
          <tr><td align="center">
            <OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"  codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH="650" HEIGHT="400" id="General" ALIGN="middle">
              <PARAM NAME="FlashVars" value="&dataXML={$area_data}">
              <PARAM NAME=movie VALUE="images/charts/pie3d.swf?chartWidth=650&chartHeight=400">
              <PARAM NAME=quality VALUE=high>
              <PARAM NAME=bgcolor VALUE=#FFFFFF>
              <EMBED src="images/charts/pie3d.swf?chartWidth=650&chartHeight=400" FlashVars="&dataXML={$area_data}" quality=high bgcolor=#FFFFFF WIDTH="650" HEIGHT="400" NAME="General" ALIGN="middle" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
              </OBJECT>
          </td></tr>
        </table>
        <!-- 来源网站 -->
        <table width="90%" id="from-table" style="display:none">
          <tr><td align="center">
          <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
           codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"
           width="565" height="420" align="middle">
              <PARAM NAME="FlashVars" value="&dataXML={$from_data}">
              <PARAM NAME=movie VALUE="images/charts/pie3d.swf?chartWidth=650&chartHeight=400">
              <param name=quality value="high">
              <param name=bgcolor value="#FFFFFF">
              <embed src="images/charts/pie3d.swf?chartWidth=650&chartHeight=400" FlashVars="&dataXML={$from_data}" quality="high" bgcolor="#FFFFFF"  width="650" height="400" align="middle"
           type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">
              </embed>
            </object>
          </td></tr>
        </table>


    </div>
</div>
{insert_scripts files="skyuc_tab.js"}
{include file="pagefooter.tpl"}
