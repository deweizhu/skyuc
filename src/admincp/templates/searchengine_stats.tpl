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
  <form action="" method="post" id="selectForm">
     <div id="group">
       {$lang.start_date}&nbsp;&nbsp;
		{html_select_date field_order="YMD" prefix="start_date" time=$start_date}&nbsp;&nbsp;
       {$lang.end_date}&nbsp;&nbsp;
       {html_select_date field_order="YMD" prefix="end_date" time=$end_date}&nbsp;&nbsp;&nbsp;&nbsp;<input type="hidden" name="s" value="{$session.sessionhash}" />
        <input type="submit" class="button primary submitButton" name="submit" value="{$lang.query}"  />&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="button" class="button"  id="truncate" value="{$lang.do_truncate}" >
       <br>{$lang.result_filter}&nbsp;
        {foreach from=$searchengines item=val key=sename}
        <label><input type="checkbox" value="{$sename}" name="filter[]" {if $val}checked{/if}>{$sename}</label>
        {/foreach}
  </form>
</div>

<div class="tab-div">
    <!-- tab bar -->
    <div id="tabbar-div">
      <p>
        <span class="tab-front" id="general-tab">{$lang.tab_keywords}</span>
      </p>
    </div>
    <!-- tab body -->
    <div id="tabbody-div">
        <!-- 关键字 -->
        <table width="90%" id="general-table">
          <tr><td align="center">
            <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
              codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"
              width="565" height="420" id="FCColumn2" align="middle">
              <PARAM NAME="FlashVars" value="&dataXML={$general_data}">
              <PARAM NAME=movie VALUE="images/charts/ScrollColumn2D.swf?chartWidth=650&chartHeight=400">
              <param NAME="quality" VALUE="high">
              <param NAME="bgcolor" VALUE="#FFFFFF">
              <embed src="images/charts/ScrollColumn2D.swf?chartWidth=650&chartHeight=400" FlashVars="&dataXML={$general_data}" quality="high" bgcolor="#FFFFFF"  width="650" height="400" name="FCColumn2" align="middle" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">
            </object>
          </td></tr>
        </table>
    </div>
</div>

{insert_scripts files="skyuc_tab.js"}
{include file="pagefooter.tpl"}
