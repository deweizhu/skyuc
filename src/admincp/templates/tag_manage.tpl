{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}

<form method="POST" action="tag_manage.php?act=batch_drop" name="listForm">
<!-- start tag list -->
<div class="list-div" id="listDiv">
{/if}

<table cellpadding="3" cellspacing="1">
  <tr>
    <th>
      <input onclick='listTable.selectAll(this, "checkboxes")' type="checkbox">
      <a href="javascript:listTable.sort('tag_id'); ">{$lang.record_id}</a>{$sort_tag_id}
    </th>
    <th><a href="javascript:listTable.sort('tag_words'); ">{$lang.tag_words}</a>{$sort_tag_words}</th>
    <th><a href="javascript:listTable.sort('user_name'); ">{$lang.user_id}</a>{$sort_user_name}</th>
    <th><a href="javascript:listTable.sort('title'); ">{$lang.show_id}</a>{$sort_title}</th>
    <th>{$lang.handler}</th>
  </tr>
  {foreach from=$tag_list item=tag}
  <tr>
    <td><span><input name="checkboxes[]" type="checkbox" value="{$tag.tag_id}">{$tag.tag_id}</span></td>
    <td class="first-cell"><span onclick="javascript:listTable.edit(this, 'edit_tag_name', {$tag.tag_id})">{$tag.tag_words}</span></td>
    <td align="left"><span>{$tag.user_name}</span></td>
    <td align="left"><span><a href="../show.php?id={$tag.show_id}" target="_blank">{$tag.title}</a></span></td>
    <td align="center">
      <span>
      <a href="tag_manage.php?act=edit&amp;id={$tag.tag_id}" title="{$lang.edit}"><img src="images/icon_edit.gif" border="0" height="16" width="16" /></a>
      <a href="javascript:;" onclick="listTable.remove({$tag.tag_id}, '{$lang.drop_confirm}')" title="{$lang.remove}"><img src="images/icon_drop.gif" border="0" height="16" width="16"></a></span>
    </td>
  </tr>
  {foreachelse}
    <tr><td class="no-records" colspan="10">{$lang.no_records}</td></tr>
  {/foreach}
  <tr>
    <td colspan="2"><input type="submit" class="button primary submitButton"  id="btnSubmit" value="{$lang.drop_tags}" disabled="true" /></td>
    <td align="right" nowrap="true" colspan="3">{include file="page.tpl"}</td>
  </tr>
</table>

{if $full_page}
</div>
<!-- end tag list -->
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
