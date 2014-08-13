{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}

<form method="post" action="" name="listForm">
<!-- start server list -->
<div class="list-div" id="listDiv">
{/if}

  <table cellpadding="3" cellspacing="1">
    <tr>
      <th>{$lang.server_name}</th>
      <th>{$lang.server_url}</th>
      <th>{$lang.server_desc}</th>
      <th>{$lang.sort_order}</th>
      <th>{$lang.is_show}</th>
      <th>{$lang.handler}</th>
    </tr>
    {foreach from=$server_list item=server}
    <tr>
      <td class="first-cell">
        <span onclick="javascript:listTable.edit(this, 'edit_server_name', {$server.server_id})">{$server.server_name|escape:html}</span>
      </td>
      <td>{$server.server_url}</td>
      <td align="left">{$server.server_desc|truncate:36}</td>
      <td align="right"><span onclick="javascript:listTable.edit(this, 'edit_sort_order', {$server.server_id})">{$server.sort_order}</span></td>
      <td align="center"><img src="images/{if $server.is_show}yes{else}no{/if}.gif" onclick="listTable.toggle(this, 'toggle_show', {$server.server_id})" /></td>
      <td align="center">
        <a href="server.php?act=edit&id={$server.server_id}" title="{$lang.edit}">{$lang.edit}</a> |
        <a href="javascript:;" onclick="listTable.remove({$server.server_id}, '{$lang.drop_confirm}')" title="{$lang.remove}">{$lang.remove}</a>
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
<!-- end server list -->
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