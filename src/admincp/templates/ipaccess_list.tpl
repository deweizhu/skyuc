{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}
<!-- 搜索 -->
<div class="form-div">
  <form action="javascript:searchipaccess()" name="searchForm">
    <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />

    <!-- 关键字 -->
    {$lang.keyword} <input type="text"   name="keyword" size="15" />
    <input type="submit" class="button primary submitButton" value="{$lang.button_search}"  />
  </form>
</div>


<script language="JavaScript">
    function searchipaccess()
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
    <th><a href="javascript:listTable.sort('sip'); ">{$lang.sip}</a>{$sort_sip}</th>
    <th><a href="javascript:listTable.sort('eip'); ">{$lang.eip}</a>{$sort_eip}</th>
	<th><a href="javascript:listTable.sort('content'); ">{$lang.content}</a>{$sort_content}</th>
    <th>{$lang.handler}</th>
  <tr>
  {foreach from=$ipaccess_list item=ipaccess}
  <tr>
    <td align="center">{$ipaccess.id}</td>
    <td align="right"><span onclick="listTable.edit(this, 'edit_sip', {$ipaccess.id})">{$ipaccess.sip}</span></td>
    <td align="right"><span onclick="listTable.edit(this, 'edit_eip', {$ipaccess.id})">{$ipaccess.eip}</span></td>
	<td align="right"><span onclick="listTable.edit(this, 'edit_content', {$ipaccess.id})">{$ipaccess.content}</span></td>
    <td align="center">
      <a href="ipaccess.php?act=edit&id={$ipaccess.id}" title="{$lang.edit}"><img src="images/icon_edit.gif" width="16" height="16" border="0" /></a>
      <a href="javascript:;" onclick="listTable.remove({$ipaccess.id}, '{$lang.drop_confirm}', 'drop_ipaccess')">{$lang.drop}</a>
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
