{if $full_page}
{include file="pageheader.tpl"}
<SCRIPT LANGUAGE="JavaScript">
<!--
	 $(document).ready(function()
			 {
				$('#truncate').click(function(){
					$('#theForm').submit();
				});
			 });
//-->
</SCRIPT>
{insert_scripts files="skyuc_listtable.js"}
<div class="form-div">
  <form action="" method="post" id="theForm">
		<input type="hidden" name="s" value="{$session.sessionhash}" />
		<input type="hidden" name="act" value="truncate" />
  </form>
  <form action="javascript:searchLog()" name="searchForm">
    <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />
    {$lang.user_name}: <input type="text"  name="keyword" /> <input type="submit" class="button primary submitButton"  value="{$lang.button_search}" /> &nbsp;&nbsp;&nbsp;&nbsp;<input type="button" class="button"  id="truncate" value="{$lang.do_truncate}" >
  </form>
</div>
<form method="POST" action="" name="listForm">
<!-- start seelog list -->
<div class="list-div" id="listDiv">
{/if}
<table cellpadding="3" cellspacing="1">
  <tr>
    <th><a href="javascript:listTable.sort('title'); ">{$lang.title}</a>{$sort_title}</th>
    <th><a href="javascript:listTable.sort('user_name'); ">{$lang.user_name}</a>{$sort_user_name}</th>
    <th><a href="javascript:listTable.sort('url_id'); ">{$lang.look_id}</a>{$sort_look_id}</th>
    <th><a href="javascript:listTable.sort('player'); ">{$lang.looktype}</a>{$sort_looktype}</th>
    <th><a href="javascript:listTable.sort('time'); ">{$lang.look_time}</a>{$sort_looktime}</th>
	<th><a href="javascript:listTable.sort('minute'); ">{$lang.minute}</a>{$sort_minute}</th>
	<th><a href="javascript:listTable.sort('counts'); ">{$lang.counts}</a>{$sort_counts}</th>
    <th><a href="javascript:listTable.sort('host'); ">{$lang.userip}</a>{$sort_userip}</th>
  </tr>
  {foreach from =$log_list item=log}
  <tr>
    <td align="center">{$log.title|escape:html}</td>
    <td align="center">{$log.user_name}</td>
    <td align="center">{$log.lookid}</td>
    <td align="center">{$log.looktype}</td>
    <td align="center" >{$log.looktime}</td>
	<td align="center" >{$log.minute}</td>
	<td align="center" >{$log.counts}</td>
    <td align="center">{$log.host}</td>
  </tr>
  {foreachelse}
  <tr><td class="no-records" colspan="7">{$lang.no_records}</td></tr>
  {/foreach}
</table>

<table id="page-table" cellspacing="0">
<tr>
  <td>&nbsp;</td>
  <td align="right" nowrap="true">
  {include file="page.tpl"}
  </td>
</tr>
</table>
{if $full_page}
</div>
<!-- end seelog list -->
</form>
<script type="text/javascript" language="JavaScript">
listTable.recordCount = {$record_count};
listTable.pageCount = {$page_count};

{foreach from=$filter item=item key=key}
listTable.filter.{$key} = '{$item}';
{/foreach}

<!--
onload = function()
{
    document.forms['searchForm'].elements['keyword'].focus();
}

/**
 * 搜索用户
 */
function searchLog()
{
    var keyword = Utils.trim(document.forms['searchForm'].elements['keyword'].value);

    listTable.filter['keywords'] = Utils.trim(document.forms['searchForm'].elements['keyword'].value);
    listTable.filter['page'] = 1;
    listTable.loadList();
}
//-->
</script>

{include file="pagefooter.tpl"}
{/if}
