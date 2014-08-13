{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}

<!-- 影片搜索 -->
{include file="show_search.tpl"}

<!-- 影片列表 -->
<form method="post" action="" name="listForm">
  <!-- start show list -->
  <div class="list-div" id="listDiv">
{/if}
<table cellpadding="3" cellspacing="1">
  <tr>
    <th>
      <input onclick='listTable.selectAll(this, "checkboxes")' type="checkbox" />
      <a href="javascript:listTable.sort('show_id'); ">{$lang.record_id}</a>{$sort_show_id}
    </th>
    <th><a href="javascript:listTable.sort('title'); ">{$lang.title}</a>{$sort_title}</th>
    <th><a href="javascript:listTable.sort('pubdate'); ">{$lang.pubdate}</a>{$sort_pubdate}</th>
    <th><a href="javascript:listTable.sort('click_count'); ">{$lang.click_count}</a>{$sort_click_count}</th>
	<th><a href="javascript:listTable.sort('player'); ">{$lang.player}</a>{$sort_player}</th>
	<th><a href="javascript:listTable.sort('points'); ">{$lang.points}</a>{$sort_points}</th>
    <th>{$lang.handler}</th>
  <tr>
  {foreach from=$show_list item=show}
  <tr>
    <td><input type="checkbox" name="checkboxes[]" value="{$show.show_id}" />{$show.show_id}</td>
    <td>{$show.title|escape:html}</td>
    <td>{$show.pubdate}</td>
    <td align="right">{$show.click_count}</td>
	<td align="right">{$show.player}</td>
	<td align="right">{$show.points}</td>
    <td align="center">
	 <a href="show.php?act=edit&show_id={$show.show_id}" title="{$lang.edit}"><img src="images/icon_edit.gif" width="16" height="16" border="0" /></a>&nbsp;&nbsp;
      <a href="javascript:;" onclick="listTable.remove({$show.show_id}, '{$lang.restore_show_confirm}', 'restore_show')"  title="{$lang.restore}"><img src="images/icon_restore.gif" width="16" height="16" border="0" /></a>
      <a href="javascript:;" onclick="listTable.remove({$show.show_id}, '{$lang.drop_show_confirm}', 'drop_show')" title="{$lang.drop}"><img src="images/icon_drop.gif" width="16" height="16" border="0" /></a>

    </td>
  </tr>
  {foreachelse}
  <tr><td class="no-records" colspan="10">{$lang.no_records}</td></tr>
  {/foreach}
</table>
<!-- end show list -->

<!-- 分页 -->
<table id="page-table" cellspacing="0">
  <tr>
    <td>
      <input type="hidden" name="act" value="batch" />
      <select class="textCtrl"  name="type" id="selAction" onchange="changeAction()">
        <option value="">{$lang.select_please}</option>
        <option value="restore">{$lang.restore}</option>
        <option value="drop">{$lang.remove}</option>
      </select>
      <select class="textCtrl"  name="target_cat" style="display:none" onchange="checkIsLeaf(this)"><option value="0">{$lang.select_please}</caption>{$cat_list}</select>
      <input type="submit" class="button primary submitButton" value="{$lang.button_submit}" id="btnSubmit" name="btnSubmit"  disabled="true" />
    </td>
    <td align="right" nowrap="true">
    {include file="page.tpl"}
    </td>
  </tr>
</table>
</div>

{if $full_page}
</form>

<script language="JavaScript">
  listTable.recordCount = {$record_count};
  listTable.pageCount = {$page_count};

  {foreach from=$filter item=item key=key}
  listTable.filter.{$key} = '{$item}';
  {/foreach}


  onload = function()
  {
    document.forms['listForm'].reset();
  }

  function confirmSubmit(frm, ext)
  {
    if (frm.elements['type'].value == 'restore')
    {

      return confirm("{$lang.restore_show_confirm}");

    }
    else if (frm.elements['type'].value == 'drop')
    {

      return confirm("{$lang.batch_drop_confirm}");

    }
    else if (frm.elements['type'].value == '')
    {
        return false;
    }
    else
    {
        return true;
    }
  }

  function changeAction()
  {
      var frm = document.forms['listForm'];

      if (!document.getElementById('btnSubmit').disabled &&
          confirmSubmit(frm, false))
      {
          frm.submit();
      }
  }

</script>
{include file="pagefooter.tpl"}
{/if}
