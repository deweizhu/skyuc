{include file="pageheader.tpl"}
<form method="POST" action="" name="theForm">
<div class="main-div">
<p style="padding: 0 10px">{$lang.sitemaps_note}</p>
</div>
<div class="main-div">
<table width="100%">
<tr>
    <td class="label">{$lang.homepage_changefreq}</td>
    <td><select class="textCtrl"  name="homepage_priority">
  {html_options values=$arr_changefreq output=$arr_changefreq  selected=$config.homepage_priority}
  </select><select class="textCtrl"  name="homepage_changefreq">
  {html_options options=$lang.priority selected=$config.homepage_changefreq}
  </select></td>
</tr>
<tr>
    <td class="label">{$lang.category_changefreq}</td>
    <td><select class="textCtrl"  name="category_priority">
  {html_options values=$arr_changefreq output=$arr_changefreq selected=$config.category_priority}
  </select><select class="textCtrl"  name="category_changefreq">
  {html_options options=$lang.priority selected=$config.category_changefreq}
  </select></td>
</tr>
<tr>
    <td class="label">{$lang.content_changefreq}</td>
    <td><select class="textCtrl"  name="content_priority">
  {html_options values=$arr_changefreq output=$arr_changefreq selected=$config.content_priority}
  </select><select class="textCtrl"  name="content_changefreq">
  {html_options options=$lang.priority selected=$config.content_changefreq}
  </select></td>
</tr>
<tr>
    <td></td>
    <td><input type="hidden" name="s" value="{$session.sessionhash}" />
	<input type="hidden" name="act" value="google" />
	<input type="submit" class="button primary submitButton" value="{$lang.button_submit}" />
	<input type="reset" class="button submitButton"  value="{$lang.button_reset}"  /></td>
</tr>
</table>
</div>
</form>
<script type="text/javascript" language="JavaScript">
<!--
onload = function()
{
    document.forms['theForm'].elements['homepage_changefreq'].focus();
}
//-->
</script>

{include file="pagefooter.tpl"}
