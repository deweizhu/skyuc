{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}

<form method="post" action="" name="listForm">

<div class="list-div" id="listDiv">
{/if}

<table cellpadding="3" cellspacing="1">
  <tr>
    <th><a href="javascript:listTable.sort('link_name'); ">{$lang.link_name}</a>{$sort_link_name}</th>
    <th><a href="javascript:listTable.sort('link_url'); ">{$lang.link_url}</a>{$sort_link_url}</th>
    <th><a href="javascript:listTable.sort('link_logo'); ">{$lang.link_logo}</a>{$sort_link_logo}</th>
    <th><a href="javascript:listTable.sort('show_order'); ">{$lang.show_order}</a>{$sort_show_order}</th>
    <th>{$lang.handler}</th>
  </tr>
  <tr>
  {foreach from=$links_list item=link}
  <tr>
    <td class="first-cell"><span onclick="listTable.edit(this, 'edit_link_name', {$link.link_id})">{$link.link_name|escape:html}</span></td>
    <td align="left"><span><a href="{$link.link_url}" target="_blank">{$link.link_url|escape:html}</a></span></td>
    <td align="center"><span>{$link.link_logo}</span></td>
    <td align="right"><span onclick="listTable.edit(this, 'edit_show_order', {$link.link_id})">{$link.show_order}</span></td>
    <td align="center"><span>
    <a href="friend_link.php?{$session.sessionurl}act=edit&id={$link.link_id}" title="{$lang.edit}"><img src="images/icon_edit.gif" border="0" height="16" width="16" /></a>&nbsp;
    <a href="javascript:;" onclick="listTable.remove({$link.link_id}, '{$lang.drop_confirm}')" title="{$lang.remove}"><img src="images/icon_drop.gif" border="0" height="16" width="16" /></a></span></td>
  </tr>
  {foreachelse}
    <tr><td class="no-records" colspan="10">{$lang.no_links}</td></tr>
  {/foreach}
  <tr>
    <td align="right" nowrap="true" colspan="10">{include file="page.tpl"}</td>
  </tr>
</table>

{if $full_page}
</div>

</form>

<script type="text/javascript" language="JavaScript">
  listTable.recordCount = {$record_count};
  listTable.pageCount = {$page_count};

  {foreach from=$filter item=item key=key}
  listTable.filter.{$key} = '{$item}';
  {/foreach}
</script>
{include file="pagefooter.tpl"}
{/if}
