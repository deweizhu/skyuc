{include file="pageheader.tpl"}
<div class="list-div" id="listDiv">
    <form name='theForm2' action='col_url.php' target='stafrm'>
     <input type='hidden' name='full_page' value='1'>
     <input type='hidden' name='nid' value='{$nid}'>
	</form>
<table cellpadding="0" cellspacing="1">
  <tr>
		<th>{$lang.collection_note}：</th>
		<th align="right" ><input type="button" class="button"  name="b11" value="{$lang.viewdown}"   onClick="document.theForm2.submit();" style="width:90">

              <input type="button" class="button"  name="b12" value="{$lang.exportdown}"   style="width:90" onClick="location.href='col_main.php?act=export&nid={$nid}';"></th>
  </tr>
    <tr>
      <td width="108" >{$lang.gathername}：</td>
      <td width="377" >
        {$col.notename}
      </td>
    </tr>
    <tr>
      <td height="20" >{$lang.col_notes}：</td>
      <td height="20" >
       {$col.unum}
      </td>
    </tr>
    <form name="theForm" action="col_url.php" method="get" target='stafrm'>
    <input type='hidden' name='nid' value='{$nid}'>
    <input type='hidden' name='totalnum' value='{$col.seed}'>
    <input type='hidden' name='startdd' value='0'>
	<input type='hidden' name='act' value='getsource'>
    <tr>
      <td height="20" >{$lang.pagesize}：</td>
      <td height="20" >
      	<input name="pagesize" type="text" id="pagesize" value="5" size="3" >
        {$lang.threadnum}：
        <input name="threadnum" type="text" id="threadnum" value="1" size="3" >
         {$lang.sptime}：
        <input name="sptime" type="text" id="sptime" value="0" size="3" >
        {$lang.second}</td>
    </tr>
    <tr>
      <td height="20" >{$lang.islisten}：</td>
      <td height="20" >
		 <input name="islisten" type="radio" value="1"  {if $nid eq 0}checked='1';{/if}/>
        {$lang.islisten_no}
		{if $nid}
         <br />
        <input name="islisten" type="radio" value="-1" checked='1'; />
      	{$lang.islisten_re}
      	<br />
        <input name="islisten" type="radio" value="0" />
         {$lang.islisten_on}
        <br />
		{/if}
      	</td>
    </tr>
    <tr>
      <td height="30" colspan="2" bgcolor="#F8FBFB" align="center" style="padding:6px 0px 0px 0px">
      	<input name="b112" type="button" class="button primary submitButton"    value="{$lang.dostart}" onClick="document.theForm.submit();" style="width:100">
      	<input type="button" class="button"  name="b113" value="{$lang.viewnotes}"   onClick="document.theForm2.submit();" style="width:100">      </td>
    </tr>
  </form>
    <tr>
      <td height="20" colspan="2">
		<table width="100%">
          <tr>
            <td width="74%">{$lang.noteurl}： </td>
            <td width="26%" align="right">
            	<script language='javascript'>
            	function ResizeDiv(obj,ty)
            	{
            		if(ty=="+")
					{
						 document.getElementById(obj).style.pixelHeight += 50;
					}
            		else if(document.getElementById(obj).style.pixelHeight >80)
					{
						document.getElementById(obj).style.pixelHeight = document.getElementById(obj).style.pixelHeight - 50;
					}
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
	  <iframe name="stafrm" frameborder="0" id="stafrm" width="100%" height="100%"
	  {if $col.seed neq 0}src="col_url.php?act=list&nid={$nid}&full_page=1"{/if}></iframe>
	  </div>
	  <script language="JavaScript">
	  document.getElementById('mdv').style.pixelHeight = screen.height - 360;
	  </script>
	  </td>
    </tr>
</table>
</div>
<script language='javascript'>
	function SubmitNew()
	{
		document.theForm.totalnum.value = "0";
		document.theForm.submit();
	}
</script>

{include file="pagefooter.tpl"}
