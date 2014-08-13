{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}
<!-- 搜索 -->
<div class="form-div">
  <form action="javascript:searchNetbar()" name="searchForm">
    <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />
    <!-- 推荐 -->
    <select class="textCtrl"  name="intro_type"><option value="0">{$lang.intro_type}</option>{html_options options=$intro_list selected=$smarty.get.intro_type}</select>
    <!-- 关键字 -->
    {$lang.keyword} <input type="text"  name="keyword"  />
    <input type="submit" class="button primary submitButton" value="{$lang.button_search}"  />
  </form>
</div>


<script language="JavaScript">
    function searchNetbar()
    {   listTable.filter['intro_type'] = document.forms['searchForm'].elements['intro_type'].value;
        listTable.filter['keyword'] = Utils.trim(document.forms['searchForm'].elements['keyword'].value);
        listTable.filter['page'] = 1;

        listTable.loadList();
    }
</script>

<!-- 列表 -->
<form method="post" action="" name="listForm" onsubmit="return confirmSubmit(this)">
  <!-- start film list -->
  <div class="list-div" id="listDiv">
{/if}

<table cellpadding="3" cellspacing="1">
  <tr>
    <th>
      <a href="javascript:listTable.sort('id'); ">{$lang.record_id}</a>{$sort_id}</th>
    <th><a href="javascript:listTable.sort('title'); ">{$lang.title}</a>{$sort_title}</th>
    <th><a href="javascript:listTable.sort('sip'); ">{$lang.sip}</a>{$sort_sip}</th>
    <th><a href="javascript:listTable.sort('eip'); ">{$lang.eip}</a>{$sort_eip}</th>
	<th><a href="javascript:listTable.sort('maxuser'); ">{$lang.maxuser}</a>{$sort_maxuser}</th>
	<th><a href="javascript:listTable.sort('addtime'); ">{$lang.addtime}</a>{$sort_addtime}</th>
	<th><a href="javascript:listTable.sort('endtime'); ">{$lang.endtime}</a>{$sort_endtime}</th>
	<th>{$lang.online}</th>
	<th><a href="javascript:listTable.sort('is_ok'); ">{$lang.lab_intro}</a>{$sort_is_ok}</th>
    <th>{$lang.handler}</th>
  <tr>
  {foreach from=$netbar_list item=netbar}
  <tr>
    <td align="center">{$netbar.id}</td>
    <td class="first-cell"><span onclick="listTable.edit(this, 'edit_name', {$netbar.id})">{$netbar.title|escape:html}</span></td>
    <td align="right"><span onclick="listTable.edit(this, 'edit_sip', {$netbar.id})">{$netbar.sip}</span></td>
    <td align="right"><span onclick="listTable.edit(this, 'edit_eip', {$netbar.id})">{$netbar.eip}</span></td>
	<td align="right"><span onclick="listTable.edit(this, 'edit_maxuser', {$netbar.id})">{$netbar.maxuser}</span></td>
	<td align="right"><span onclick="listTable.edit(this, 'edit_addtime', {$netbar.id})">{$netbar.addtime}</span></td>
	<td align="right"><span onclick="listTable.edit(this, 'edit_endtime', {$netbar.id})">{$netbar.endtime}</span></td>
	<td align="center">{$netbar.online}</td>
	<td align="center"><img src="images/{if $netbar.is_ok}yes{else}no{/if}.gif" onclick="listTable.toggle(this, 'toggle_enabled', {$netbar.id})" /></td>
    <td align="center">
      <a href="netbar.php?act=edit&id={$netbar.id}" title="{$lang.edit}"><img src="images/icon_edit.gif" width="16" height="16" border="0" /></a>
      <a href="javascript:;" onclick="listTable.remove({$netbar.id}, '{$lang.drop_confirm}', 'drop_netbar')" title="{$lang.drop}"><img src="images/icon_drop.gif" width="16" height="16" border="0" /></a>
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

</script>
{include file="pagefooter.tpl"}
{/if}
