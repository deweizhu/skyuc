{include file="pageheader.tpl"}
<div class="list-div" id="listDiv">
{if $notename}
<table cellpadding="0" cellspacing="1">
        <tr>
          <th width="13%" height="24" align="center">{$lang.exportrule}：</th>
          <th width="87%" align="left">&nbsp;{$notename} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		  {if $extype eq 'text'}
		  <a href='col_main.php?act=exportrule&nid={$nid}&extype=text'>【<b>{$lang.export_text}</b>】</a>
		  {else}
		  <a href='col_main.php?act=exportrule&nid={$nid}&extype=base64'>【<b>{$lang.export_base64}</b>】</a>
		  {/if}</th>
        </tr>
        <tr>
          <td height="30" align="center">

		  </td>
          <td>{$exportrule_desc}</td>
        </tr>
        <tr>
          <td height="24" colspan="2" align="center">
         <textarea class="textCtrl" name="r2" style='width:99%;height:450px;word-wrap: break-word;word-break:break-all;' >{$noteinfo|escape:html}</textarea>
		  </td>
        </tr>
</table>
{else}
	<form name="theForm" action="" method="POST" >
		<input type='hidden' name='act' value='importrule_action'>
<table cellpadding="0" cellspacing="1">
        <tr>
          <th width="13%" height="24" align="center">{$lang.importrule}：</th>
          <th width="87%" align="left">&nbsp;</th>
        </tr>
        <tr>
          <td height="24" align="center"></td>
          <td >{$importrule_desc}</td>
        </tr>
        <tr>
          <td height="24" colspan="2" align="center">
         <textarea class="textCtrl" name="importrule" style='width:99%;height:450px;word-wrap: break-word;word-break:break-all;'></textarea>
		  </td>
        </tr>
	<tr>
      <td height="30" colspan="2" bgcolor="#F8FBFB" align="center" style="padding:6px 0px 0px 0px">
      	<input name="b112" type="button" class="button"   value="{$lang.button_submit}" onClick="document.theForm.submit();" style="width:100">&nbsp;&nbsp;
      	 <input type="button" class="button"  name="button" id="button" value="{$lang.button_back}"  onClick="location.href='col_main.php?act=list'" /> </td>
    </tr>
  </form>
</table>
{/if}
</div>
{include file="pagefooter.tpl"}
