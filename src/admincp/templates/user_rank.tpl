{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}

<form method="post" action="" name="listForm">
<div class="list-div" id="listDiv">
{/if}

<table cellspacing='1' id="list-table">
  <tr>
    <th>{$lang.rank_name}</th>
    <th>{$lang.day_play}</th>
    <th>{$lang.day_down}</th>
    <th>{$lang.count}</th>
    <th>{$lang.money}</th>
	<th>{$lang.allow_hours}</th>
    <th>{$lang.handler}</th>
  </tr>
  {foreach from=$user_ranks item=rank}
  <tr>
    <td class="first-cell" ><span onclick="listTable.edit(this,'edit_name', {$rank.rank_id})">{$rank.rank_name}</span></td>
    <td align="right"><span>{$rank.day_play}</span></td>
    <td align="right"><span>{$rank.day_down}</span></td>
    <td align="right"><span>{$rank.count}</span></td>
    <td align="center"><span>{$rank.money}</span></td>
	<td align="center"><span>{$rank.allow_hours}</span></td>
	<td align="center">
	<a href="user_rank.php?act=edit&rank_id={$rank.rank_id}" title="{$lang.edit}"><img src="images/icon_edit.gif" width="16" height="16" border="0" /></a>&nbsp;&nbsp;
    <a href="javascript:;" onclick="listTable.remove({$rank.rank_id}, '{$lang.drop_confirm}')" title="{$lang.remove}"><img src="images/icon_drop.gif" border="0" height="16" width="16"></a></td>
  </tr>
  {/foreach}
  </table>

{if $full_page}
</div>
</form>
{include file="pagefooter.tpl"}
{/if}
