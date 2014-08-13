{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}
<div class="form-div">
  <form action="javascript:searchMsg()" name="searchForm">
    <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />
    {$lang.msg_type}:
    <select class="textCtrl"  name="msg_type">
      <option value="-1">{$lang.select_please}</option>
      <option value="0">{$lang.type[0]}</option>
      <option value="1">{$lang.type[1]}</option>
      <option value="2">{$lang.type[2]}</option>
      <option value="3">{$lang.type[3]}</option>
    </select>
    {$lang.msg_title}: <input type="text"   name="keyword" /> <input type="submit" class="button primary submitButton"  value="{$lang.button_search}" />
  </form>
</div>
<form method="POST" action="" name="listForm">

<div class="list-div" id="listDiv">
{/if}
<table cellpadding="3" cellspacing="1">
  <tr>
    <th><a href="javascript:listTable.sort('msg_id'); ">{$lang.msg_id}</a>{$sort_msg_id}</th>
    <th><a href="javascript:listTable.sort('user_name'); ">{$lang.user_name}</a>{$sort_user_name}</th>
    <th><a href="javascript:listTable.sort('msg_title'); ">{$lang.msg_title}</a>{$sort_msg_title}</th>
    <th><a href="javascript:listTable.sort('msg_type'); ">{$lang.msg_type}</a>{$sort_msg_type}</th>
    <th><a href="javascript:listTable.sort('msg_time'); ">{$lang.msg_time}</a>{$sort_msg_time}</th>
    <th><a href="javascript:listTable.sort('reply'); ">{$lang.reply}</a>{$sort_reply}</th>
    <th>{$lang.handler}</th>
  </tr>
  {foreach from =$msg_list item=msg}
  <tr>
    <td align="center">{$msg.msg_id}</td>
    <td align="center">{$msg.user_name}</td>
    <td align="left">{$msg.msg_title|truncate:40|escape:html}</td>
    <td align="center">{$msg.msg_type}</td>
    <td align="center"  nowrap="nowrap">{$msg.msg_time}</td>
    <td align="center">{if $msg.reply eq 0}{$lang.unreplyed}{else}{$lang.replyed}{/if}</td>
    <td align="center">
      <a href="user_msg.php?act=view&id={$msg.msg_id}&reply={$msg.reply}" title="{$lang.view}"><img src="images/icon_view.gif" border="0" height="16" width="16" /></a>&nbsp;&nbsp;
      <a href="javascript:;" onclick="listTable.remove({$msg.msg_id}, '{$lang.drop_confirm}')"  title="{$lang.remove}"><img src="images/icon_drop.gif" border="0" height="16" width="16"></a>
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
 * 搜索标题
 */
function searchMsg()
{
    var keyword = Utils.trim(document.forms['searchForm'].elements['keyword'].value);
    var msgType = document.forms['searchForm'].elements['msg_type'].value;

    listTable.filter['keywords'] = Utils.trim(document.forms['searchForm'].elements['keyword'].value);
    listTable.filter['msg_type'] = document.forms['searchForm'].elements['msg_type'].value;
    listTable.filter['page'] = 1;
    listTable.loadList();
}
//-->
</script>

{include file="pagefooter.tpl"}
{/if}
