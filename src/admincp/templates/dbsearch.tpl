{include file="pageheader.tpl"}

<div class="list-div">
<form method="post" action="dbsearch.php" name="theForm" >
<table cellpadding='3' cellspacing='1'>
<tr>
  <th colspan="2">{$lang.dbsearch_setting}</th>
</tr>
<tr>
<td colspan="2"> {$lang.turn_on_note}<br /></td>
</tr>

<tr>
  <td width="20%" >
  <strong>{$lang.dbsearch_full}</strong>
  </td>

  <td>
  <input type="radio" name="dbsearch_full" value='0' {if $dbsearch_full eq 0}checked="checked"{/if}>{$lang.dbsearch_full_0}
  &nbsp;&nbsp;
  <input type="radio" name="dbsearch_full" value='1'  {if $dbsearch_full eq 1}checked="checked"{/if}>{$lang.dbsearch_full_1}

  </td>
</tr>
<tr>
  <td colspan="2" align="center"><input type="hidden" name="act" value="save_config" />
  <input type="hidden" name="s" value="{$session.sessionhash}" />
  <input type="submit" class="button primary submitButton" value="{$lang.save_config}" />

   <input class="button  submitButton" value="{$lang.rebuild_index}" onclick="location.href='dbsearch.php?act=rebuild';" >
    <input class="button  submitButton" value="{$lang.truncate_index}" onclick="location.href='dbsearch.php?act=emptyindex';" >
  </td>
</tr>
</table>
</form>
</div>
{include file="pagefooter.tpl"}
