{include file="pageheader.tpl"}
<div class="list-div" id="listDiv">

<table cellpadding="0" cellspacing="1">
  <tr>
		<th align="left">&nbsp;&nbsp;{$lang.delete_trash}</th>
		<th align="right"></th>
  </tr>

    <form name="theForm" action="col_url.php" method="get" target='stafrm'>
    <input type='hidden' name='totalnum' value='{$totalnum}'>
    <input type='hidden' name='startdd' value='0'>
	<input type='hidden' name='act' value='delete_trash_act'>

    <tr>
      <td height="30" colspan="2" bgcolor="#F8FBFB" align="center" style="padding:6px 0px 0px 0px">
      	<input name="b112" type="button" class="button primary submitButton"    value="{$lang.delete_trash}" onClick="document.theForm.submit();" style="width:100">
      	 </td>
    </tr>
  </form>

    <tr >
      <td colspan="2" id="mtd">
	  <div id='mdv' style='width:100%;height:350px;'>
	  <iframe name="stafrm" frameborder="0" id="stafrm" width="100%" height="100%" ></iframe>
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
