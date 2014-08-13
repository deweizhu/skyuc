{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}

<form method="POST" action="" name="listForm">
<!-- start seeonline list -->
<div class="list-div" id="listDiv">
{/if}
<table cellpadding="3" cellspacing="1">
  <tr>
    <th><a href="javascript:listTable.sort('userid'); ">{$lang.user_name}</a>{$sort_user_name}</th>
    <th><a href="javascript:listTable.sort('sessionhash'); ">{$lang.sessionhash}</a>{$sort_sessionhash}</th>
    <th><a href="javascript:listTable.sort('lastactivity'); ">{$lang.lastactivity}</a>{$sort_lastactivity}</th>
    <th><a href="javascript:listTable.sort('host'); ">{$lang.host}</a>{$sort_host}</th>
	<th>{$lang.os}{$sort_os}</th>
	<th>{$lang.browser}{$sort_browser}</th>
	<th><a href="javascript:listTable.sort('location'); ">{$lang.location}</a>{$sort_location}</th>
	<th>{$lang.handler}</th>
  </tr>
  {foreach from =$online_list item=online}
  <tr>
    <td align="center">{$online.user_name}</td>
    <td align="center">{$online.sessionhash}</td>
    <td align="center" >{$online.lastactivity}</td>
    <td align="center">{$online.host}</td>
	<td align="center">{$online.os}</td>
	<td align="center">{$online.browser}</td>
	<td align="center"><a href="{$online.url}" target="_blank">{$online.location|truncate:30}</a></td>
	<td width="10%" align="center">{if $online.adminid eq 0}<a href="?act=write-off&sessionhash={$online.sessionhash}" title="{$lang.write-off}">{$lang.write-off}</a>{/if}
    </td>
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
<!-- end seeonline list -->
</form>
<script type="text/javascript" language="JavaScript">
listTable.recordCount = {$record_count};
listTable.pageCount = {$page_count};

{foreach from=$filter item=item key=key}
listTable.filter.{$key} = '{$item}';
{/foreach}

//-->
</script>

{include file="pagefooter.tpl"}
{/if}