{include file="pageheader.tpl"}

<div class="list-div">
<form method="post" action="humanverify.php" name="theForm" >
<table cellpadding='3' cellspacing='1'>
<tr>
  <th colspan="2">{$lang.humanverify_setting}</th>
</tr>
<tr>
  <td width="60%" >
  <strong>{$lang.humanverify_turn_on}</strong><br />
  {$lang.turn_on_note}<br />
  <img src="../image.php?{$session.sessionurl}type=hv&amp;hash={$humanverify.hash}" alt="humanverify"  />
  </td>
  <td>
  <input type="checkbox" name="hv_register" value="1" {$hv.register} />{$lang.humanverify_register}<br />
  <input type="checkbox" name="hv_login" value="2" {$hv.login} />{$lang.humanverify_login}<br />
  <input type="checkbox" name="hv_comment" value="4"  {$hv.comment} />{$lang.humanverify_comment}<br />
  <input type="checkbox" name="hv_admin" value="8" {$hv.admin} />{$lang.humanverify_admin}<br />
  <input type="checkbox" name="hv_message" value="32" {$hv.message} />{$lang.humanverify_message}<br />
  </td>
</tr>

<tr>
  <td>
  <strong>{$lang.regimageoption}</strong><br />
  {$lang.regimageoption_note}
  </td>
  <td> <input type="checkbox" name="regimageoption[1]" value="1" {$rio.random_font} />{$lang.regimageoption_random_font}<br />
  <input type="checkbox" name="regimageoption[2]" value="2" {$rio.random_fontsize} />{$lang.regimageoption_random_fontsize}<br />
  <input type="checkbox" name="regimageoption[4]" value="4"  {$rio.random_slant} />{$lang.regimageoption_random_slant}<br />
  <input type="checkbox" name="regimageoption[8]" value="8" {$rio.random_color} />{$lang.regimageoption_random_color}<br />
  <input type="checkbox" name="regimageoption[16]" value="16" {$rio.random_shape} />{$lang.regimageoption_random_shape}<br /></td>
</tr>
<tr>
  <td colspan="2" align="center"><input type="hidden" name="act" value="save_config" />
  <input type="hidden" name="s" value="{$session.sessionhash}" />
  <input type="submit" class="button primary submitButton" value="{$lang.save_config}" /></td>
</tr>
</table>
</form>
</div>
{include file="pagefooter.tpl"}
