{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}

<!-- 主题搜索 -->
<div class="form-div">
  <form action="javascript:searchShows()" name="searchForm">
    <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />
    <!-- 关键字 -->
    {$lang.keyword} <input type="text"   name="keyword"  />
    <input type="submit" class="button primary submitButton" value="{$lang.button_search}"  />
  </form>
</div>


<script language="JavaScript">
    function searchShows()
    {
        listTable.filter['keyword'] = Utils.trim(document.forms['searchForm'].elements['keyword'].value);
        listTable.filter['page'] = 1;

        listTable.loadList();
    }
</script>
<!-- 列表 -->
<form method="post" action="" name="listForm" onsubmit="return confirmSubmit(this)">
  <!-- start list -->
  <div class="list-div" id="listDiv">
{/if}

<table cellpadding="3" cellspacing="1">
  <tr>
    <th>
      <a href="javascript:listTable.sort('id'); ">{$lang.record_id}</a>{$sort_id}</th>
    <th>{$lang.title}</th>
	<th>{$lang.link}</th>
    <th>{$lang.uselink}</th>
	<th>{$lang.recom}</th>
	<th>{$lang.add_time}</th>
    <th>{$lang.handler}</th>
  <tr>
  {foreach from=$subject item=subject}
  <tr>
    <td align="center">{$subject.id}</td>
    <td class="first-cell" style="{if $subject.recom}color:red;{/if}"><span onclick="listTable.edit(this, 'edit_title', {$subject.id})">{$subject.title|escape:html}</span></td>
	<td><span title="{$subject.link|escape:html}">{$subject.link|truncate:40:true}</span></td>
    <td align="center"><img src="images/{if $subject.uselink}yes{else}no{/if}.gif" onclick="listTable.toggle(this, 'toggle_uselink', {$subject.id})" /></td>
	<td align="center"><img src="images/{if $subject.recom}yes{else}no{/if}.gif" onclick="listTable.toggle(this, 'toggle_recom', {$subject.id})" /></td>
	<td>{$subject.add_time}</td>
    <td align="center">
      <a href="../subject.php?id={$subject.id}" target="_blank" title="{$lang.view}"><img src="images/icon_view.gif" width="16" height="16" border="0" /></a>
      <a href="subject.php?act=edit&id={$subject.id}" title="{$lang.edit}"><img src="images/icon_edit.gif" width="16" height="16" border="0" /></a>
      <a href="javascript:;" onclick="listTable.remove({$subject.id}, '{$lang.drop_subject_confirm}', 'drop')" title="{$lang.drop}"><img src="images/icon_drop.gif" width="16" height="16" border="0" /></a>
    </td>
  </tr>
  {foreachelse}
  <tr><td class="no-records" colspan="10">{$lang.no_records}</td></tr>
  {/foreach}
</table>
<!-- end list -->

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
