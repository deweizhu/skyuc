{include file="pageheader.tpl"}
<div class="list-div" id="listDiv">

	<form name="theForm" action="col_url.php" method="get" target='stafrm'>
	<input type='hidden' name='act' value='export_action'>
	<input type='hidden' name='nid' value='{$nid}'>
    <input type="hidden" name="totalnum" value="{$totalnum}">
<table cellpadding="0" cellspacing="1" width="100%" align="center">
  <tr>
	<th>{$lang.exportdata}：</th>
	<th align="right" ></th>
  </tr>
    <tr>
      <td width="16%" align="center">{$lang.gathername}：</td>
      <td>
        {$notename}：{$totalnote}
      </td>
    </tr>
	  <tr>
      <td align="center">{$lang.cat_id}：</td>
      <td> <select class="textCtrl"  name="cat_id" >
			<option value="0">{$lang.select_please}</option>
			{$cat_list}
			</select>{$lang.require_field}
            </td>
    </tr>
     <tr>
      <td height="20" align="center">{$lang.export_pagesize}：</td>
      <td height="20"><input name="pagesize" type="text" id="pagesize" value="30" size="6">
      </td>
    </tr>
    <tr>
      <td height="20" align="center">{$lang.islisten}：</td>
      <td height="20">
      	<input name="onlytitle" type="radio" id="onlytitle" value="0" checked='checked'>
        {$lang.onlytitle}
		<input name="onlytitle" type="radio" id="onlytitle" value="1">
        {$lang.updateurl}
		<input name="onlytitle" type="radio" id="onlytitle" value="2">
        {$lang.updateall}
      </td>
    </tr>
    <tr>
      <td height="30" colspan="2" bgcolor="#F8FBFB" align="center" style="padding:6px 0px 0px 0px">
      	<input name="b112" type="button" class="button"    value="{$lang.button_submit}" onClick="document.theForm.submit();" style="width:100">&nbsp;&nbsp;
      	 <input type="button" class="button"  name="button" id="button" value="{$lang.button_back}"  onClick="location.href='col_main.php?act=list'" /> </td>
    </tr>
  </form>
    <tr>
      <td height="20" colspan="2">
		<table width="100%">
          <tr>
            <td width="74%">{$lang.progress_status}： </td>
            <td width="26%" align="right">
            	<script language='javascript'>
            	function ResizeDiv(obj,ty)
            	{
            		if(ty=="+") document.all[obj].style.pixelHeight += 50;
            		else if(document.all[obj].style.pixelHeight>80) document.all[obj].style.pixelHeight = document.all[obj].style.pixelHeight - 50;
            	}
            	</script>
            	[<a href='#' onClick="ResizeDiv('mdv','+');">{$lang.divmax}</a>] [<a href='#' onClick="ResizeDiv('mdv','-');">{$lang.divmin}</a>]
            	</td>
          </tr>
        </table></td>
    </tr>
    <tr >
      <td colspan="2" id="mtd">
	  <div id='mdv' style='width:100%;height:100;'>
	  <iframe name="stafrm" frameborder="0" id="stafrm" width="100%" height="100%"></iframe>
	  </div>
	  <script language="JavaScript">
	  document.all.mdv.style.pixelHeight = screen.height - 360;
	  </script>
	  </td>
    </tr>
</table>
</div>
{include file="pagefooter.tpl"}
