{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}

<!-- 影片搜索 -->
{include file="show_search.tpl"}
<!-- 影片列表 -->
<form method="post" action="" name="listForm" onsubmit="return confirmSubmit(this)">
  <!-- start film list -->
  <div class="list-div" id="listDiv">
{/if}

<table cellpadding="3" cellspacing="1">
  <tr>
    <th><input onclick='listTable.selectAll(this, "checkboxes")' type="checkbox" />
      <a href="javascript:listTable.sort('show_id'); ">{$lang.record_id}</a>{$sort_show_id}</th>
    <th><a href="javascript:listTable.sort('title'); ">{$lang.title}</a>{$sort_title}</th>
    <th><a href="javascript:listTable.sort('pubdate'); ">{$lang.pubdate}</a>{$sort_pubdate}</th>
    <th><a href="javascript:listTable.sort('click_count'); ">{$lang.click_count}</a>{$sort_click_count}</th>
	<th><a href="javascript:listTable.sort('player'); ">{$lang.player}</a>{$sort_player}</th>
	<th><a href="javascript:listTable.sort('points'); ">{$lang.points}</a>{$sort_points}</th>
    <th>{$lang.is_best}{$sort_is_best}</th>
    <th>{$lang.is_hot}{$sort_is_hot}</th>
	<th><a href="javascript:listTable.sort('add_time'); ">{$lang.add_time}</a>{$sort_add_time}</th>
    <th>{$lang.handler}</th>
  <tr>
  {foreach from=$show_list item=show}
  <tr>
    <td><input type="checkbox" name="checkboxes[]" value="{$show.show_id}" />{$show.show_id}</td>
    <td class="first-cell" style="{if $show.is_best || $show.is_hot}color:red;{/if}"><span onclick="listTable.edit(this, 'edit_title', {$show.show_id})">{$show.title|escape:html}</span> <span style="font-weight:normal;color:#f00;">{$show.status}</span></td>
    <td><span onclick="listTable.edit(this, 'edit_pubdate', '{$show.show_id}')">{$show.pubdate}</span></td>
    <td align="right"><span onclick="listTable.edit(this, 'edit_click_count', {$show.show_id})">{$show.click_count }</span></td>
	    <td align="right"><span onclick="listTable.edit(this, 'edit_player', {$show.show_id})">{$show.player}</span></td>
		  <td align="right"><span onclick="listTable.edit(this, 'edit_points', {$show.show_id})">{$show.points}</span></td>
    <td align="center"><img src="images/{if $show.attribute eq 1}yes{else}no{/if}.gif" onclick="listTable.toggle(this, 'toggle_best', {$show.show_id})" /></td>
    <td align="center"><img src="images/{if $show.attribute eq 2}yes{else}no{/if}.gif" onclick="listTable.toggle(this, 'toggle_hot', {$show.show_id})" /></td>
	<td align="center">{$show.add_time}</td>
    <td align="center">
      <a href="../show.php?id={$show.show_id}" target="_blank" title="{$lang.view}"><img src="images/icon_view.gif" width="16" height="16" border="0" /></a>
      <a href="show.php?act=edit&show_id={$show.show_id}" title="{$lang.edit}"><img src="images/icon_edit.gif" width="16" height="16" border="0" /></a>
      <a href="show.php?act=copy&show_id={$show.show_id}" title="{$lang.copy}"><img src="images/icon_copy.gif" width="16" height="16" border="0" /></a>
      <a href="javascript:;" onclick="listTable.remove({$show.show_id}, '{$lang.trash_show_confirm}')" title="{$lang.trash}"><img src="images/icon_trash.gif" width="16" height="16" border="0" /></a>
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
        <option value="trash">{$lang.trash}</option>
        <option value="best">{$lang.is_best}</option>
        <option value="not_best">{$lang.not_best}</option>
        <option value="hot">{$lang.is_hot}</option>
        <option value="not_hot">{$lang.not_hot}</option>
        <option value="move_to">{$lang.move_to}</option>
      </select>
      <select class="textCtrl"  name="target_cat" style="display:none" >
	  <option value="0">{$lang.select_please}</option>{$cat_list}</select>
      <input type="submit" class="button primary submitButton" value="{$lang.button_submit}" id="btnSubmit" name="btnSubmit"  disabled="true" />
    </td>
    <td align="right" nowrap="true">
    {include file="page.tpl"}
    </td>
  </tr>
</table>

{if $full_page}
</div>
</form>

<script type="text/javascript">
  listTable.recordCount = {$record_count};
  listTable.pageCount = {$page_count};

  {foreach from=$filter item=item key=key}
  listTable.filter.{$key} = '{$item}';
  {/foreach}


  onload = function()
  {
    document.forms['listForm'].reset();
  }

  /**
   * @param: bool ext 其他条件：用于转移分类
   */
  function confirmSubmit(frm, ext)
  {
      if (frm.elements['type'].value == 'trash')
      {
          return confirm(batch_trash_confirm);
      }
      else if (frm.elements['type'].value == 'not_on_sale')
      {
          return confirm(batch_no_on_sale);
      }
      else if (frm.elements['type'].value == 'move_to')
      {
          ext = (ext == undefined) ? true : ext;
          return ext && frm.elements['target_cat'].value != 0;
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

      // 切换分类列表的显示
      frm.elements['target_cat'].style.display = frm.elements['type'].value == 'move_to' ? '' : 'none';

      if (!document.getElementById('btnSubmit').disabled &&
          confirmSubmit(frm, false))
      {
          frm.submit();
      }
  }

</script>
{include file="pagefooter.tpl"}
{/if}
