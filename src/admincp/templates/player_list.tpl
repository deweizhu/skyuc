{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}

<form method="post" action="" name="listForm">
<!-- start player list -->
<div class="list-div" id="listDiv">
{/if}

  <table cellpadding="3" cellspacing="1">
    <tr>
      <th>{$lang.record_id}</a></th>
      <th>{$lang.player_title}</th>
	  <th>{$lang.player_tag}</th>
      <th>{$lang.sort_order}</th>
      <th>{$lang.is_show}</th>
	  <th>{$lang.user_rank}</th>
      <th>{$lang.handler}</th>
    </tr>
    {foreach from=$player_list item=player}
    <tr>
	  <td>{$player.id}</td>
      <td class="first-cell">
        <span onclick="javascript:listTable.edit(this, 'edit_player_title', {$player.id})">{$player.title|escape:html}</span>
      </td>
	  <td> <span onclick="javascript:listTable.edit(this, 'edit_player_tag', {$player.id})">{$player.tag|escape:html}</span></td>
      <td align="right"><span onclick="javascript:listTable.edit(this, 'edit_sort_order', {$player.id})">{$player.sort_order}</span></td>
      <td align="center"><img src="images/{if $player.is_show}yes{else}no{/if}.gif" onclick="listTable.toggle(this, 'toggle_show', {$player.id})" /></td>
	   <td align="center"> {$player.user_rank}</td>
      <td align="center">
        <a href="player.php?{$session.sessionurl}act=edit&id={$player.id}" title="{$lang.edit}">{$lang.edit}</a> |
        <a href="javascript:;" onclick="listTable.remove({$player.id}, '{$lang.drop_confirm}')" title="{$lang.remove}">{$lang.remove}</a>
      </td>
    </tr>
    {foreachelse}
    <tr><td class="no-records" colspan="10">{$lang.no_records}</td></tr>
    {/foreach}
    <tr>
      <td align="right" nowrap="true" colspan="6">
      {include file="page.tpl"}
      </td>
    </tr>
  </table>

{if $full_page}
<!-- end player list -->
</div>
</form>

<script type="text/javascript" language="javascript">
  <!--
  listTable.recordCount = {$record_count};
  listTable.pageCount = {$page_count};

  {foreach from=$filter item=item key=key}
  listTable.filter.{$key} = '{$item}';
  {/foreach}
  //-->
</script>
{include file="pagefooter.tpl"}
{/if}