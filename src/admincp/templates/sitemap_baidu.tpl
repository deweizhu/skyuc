{include file="pageheader.tpl"}
<form method="POST" action="" name="theForm">
<div class="main-div">
<p style="padding: 0 10px">{$lang.sitemaps_baidu_note}</p>
</div>
<div class="main-div">
<table width="100%">
<tr>
    <td class="label">{$lang.content_changefreq}</td>
    <td><select class="textCtrl"  name="content_priority">
  {html_options values=$arr_changefreq output=$arr_changefreq selected=1}
  </select><select class="textCtrl" >
  <option>{$lang.priority.hourly}</option>
  </select></td>
</tr>
<tr>
    <td></td>
    <td>
	<input type="hidden" name="s" value="{$session.sessionhash}" />
	<input type="hidden" name="act" value="baidu" />
	<input type="submit" class="button primary submitButton" value="{$lang.button_submit}" /><input type="reset" class="button submitButton"  value="{$lang.button_reset}" /></td>
</tr>
</table>
</div>
</form>
<script type="text/javascript" language="JavaScript">
<!--
onload = function()
{
    document.forms['theForm'].elements['content_priority'].focus();
}
//-->
</script>

{include file="pagefooter.tpl"}
