{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}
<div class="form-div">
  <form action="javascript:searchUser()" name="searchForm">
    <img src="images/icon_search.gif" width="25" height="22" border="0" alt="SEARCH" />
    {$lang.user_id} <input type="text"  name="keyword"  />
      <select class="textCtrl"  name="process_type">
        <option value="-1">{$lang.process_type}</option>
        <option value="0" {$process_type_0}>{$lang.surplus_type_0}</option>
        <option value="1" {$process_type_1}>{$lang.surplus_type_1}</option>
      </select>
      <select class="textCtrl"  name="payment">
      <option value="">{$lang.pay_mothed}</option>
      {html_options options=$payment_list}
      </select>
      <select class="textCtrl"  name="is_paid">
        <option value="-1">{$lang.status}</option>
        <option value="0" {$is_paid_0}>{$lang.unconfirm}</option>
        <option value="1" {$is_paid_1}>{$lang.confirm}</option>
        <option value="2">{$lang.cancel}</option>
      </select>
      <input type="submit" class="button primary submitButton" value="{$lang.button_search}"  />
  </form>
</div>

<form method="POST" action="" name="listForm">
<!-- start user_deposit list -->
<div class="list-div" id="listDiv">
{/if}
<table cellpadding="3" cellspacing="1">
  <tr>
    <th><a href="javascript:listTable.sort('user_name', 'DESC'); ">{$lang.user_id}</a>{$sort_user_name}</th>
    <th><a href="javascript:listTable.sort('add_time', 'DESC'); ">{$lang.add_date}</a>{$sort_add_time}</th>
    <th><a href="javascript:listTable.sort('process_type', 'DESC'); ">{$lang.process_type}</a>{$sort_process_type}</th>
    <th><a href="javascript:listTable.sort('amount', 'DESC'); ">{$lang.surplus_amount}</a>{$sort_amount}</th>
    <th><a href="javascript:listTable.sort('payment', 'DESC'); ">{$lang.pay_mothed}</a>{$sort_payment}</th>
    <th><a href="javascript:listTable.sort('is_paid', 'DESC'); ">{$lang.status}</a>{$sort_is_paid}</th>
    <th>{$lang.admin_user}</th>
    <th>{$lang.handler}</th>
  </tr>
  {foreach from=$list item=item}
  <tr>
    <td>{$item.user_name}</td>
    <td align="center">{$item.add_date}</td>
    <td align="center">{$item.process_type_name}</td>
    <td align="right">{$item.surplus_amount}</td>
    <td>{if $item.payment}{$item.payment}{else}N/A{/if}</td>
    <td align="center">{if $item.is_paid}{$lang.confirm}{else}{$lang.unconfirm}{/if}</td>
    <td align="center">{$item.admin_user}
    <td align="center">
    {if $item.is_paid}
    <a href="user_account.php?act=edit&id={$item.id}" title="{$lang.surplus}"><img src="images/icon_edit.gif" border="0" height="16" width="16" /></a>
    {else}
    <a href="user_account.php?act=check&id={$item.id}" title="{$lang.check}"><img src="images/icon_view.gif" border="0" height="16" width="16" /></a>
    <a href="javascript:;" onclick="listTable.remove({$item.id}, '{$lang.drop_confirm}')" title="{$lang.drop}" ><img src="images/icon_drop.gif" border="0" height="16" width="16" /></a>
    {/if}
    </td>
  </tr>
  {foreachelse}
  <tr>
    <td class="no-records" colspan="8">{$lang.no_records}</td>
  </tr>
  {/foreach}

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
<!-- end user_deposit list -->
</form>

<script type="text/javascript" language="JavaScript">
listTable.recordCount = {$record_count};
listTable.pageCount = {$page_count};
{foreach from=$filter item=item key=key}
listTable.filter.{$key} = '{$item}';
{/foreach}

<!--
/**
 * 搜索用户
 */
function searchUser()
{
    listTable.filter['keywords'] = Utils.trim(document.forms['searchForm'].elements['keyword'].value);
    listTable.filter['process_type'] = document.forms['searchForm'].elements['process_type'].value;
    listTable.filter['payment'] = Utils.trim(document.forms['searchForm'].elements['payment'].value);
    listTable.filter['is_paid'] = document.forms['searchForm'].elements['is_paid'].value;
    listTable.filter['page'] = 1;
    listTable.loadList();
}
//-->
</script>

{include file="pagefooter.tpl"}
{/if}
