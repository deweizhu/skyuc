{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}
<!-- 订单搜索 -->
<SCRIPT LANGUAGE="JavaScript">
<!--
	 $(document).ready(function()
			 {
				$('#truncate').click(function(){
					return listTable.remove('all', remove_confirm_all, 'remove_order');
				});
			 });
//-->
</SCRIPT>
<div class="form-div">
  <form action="javascript:searchOrder()" name="searchForm">
    <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />
    {$lang.order_sn}&nbsp;<input name="order_sn" type="text"  id="order_sn" size="15">
    {$lang.username}&nbsp;<input name="username" type="text"  id="username" size="15">
    {$lang.all_status}
    <select class="textCtrl"  name="status" id="status">
      <option value="-1">{$lang.select_please}</option>
      {html_options options=$status_list}
    </select>
    <input type="submit" class="button primary submitButton" value="{$lang.button_search}"  />
 &nbsp;&nbsp;<input type="button" class="button"  id="truncate" value="{$lang.delete_all_order}" >
  </form>
</div>

<!-- 订单列表 -->
<form method="post" action="order.php?act=operate" name="listForm" onsubmit="return check()">
  <div class="list-div" id="listDiv">
{/if}

<table cellpadding="3" cellspacing="1">
  <tr>
    <th><a href="javascript:listTable.sort('order_sn', 'DESC'); ">{$lang.order_sn}</a>{$sort_order_sn}
    </th>
    <th><a href="javascript:listTable.sort('order_time', 'DESC'); ">{$lang.order_time}</a>{$sort_order_time}</th>
    <th><a href="javascript:listTable.sort('user_name', 'DESC'); ">{$lang.username}</a>{$sort_user_name}</th>
    <th><a href="javascript:listTable.sort('order_amount', 'DESC'); ">{$lang.order_amount}</a>{$sort_order_amount}</th>
	<th><a href="javascript:listTable.sort('pay_amount', 'DESC'); ">{$lang.pay_amount}</a>{$sort_pay_amount}</th>
	<th><a href="javascript:listTable.sort('pay_name', 'DESC'); ">{$lang.pay_name}</a>{$sort_pay_name}</th>
    <th>{$lang.all_status}</th>
    <th>{$lang.handler}</th>
  <tr>
  {foreach from=$order_list item=order}
  <tr>
    <td  nowrap="nowrap">{$order.order_sn}</td>
    <td>{$order.short_order_time}</td>
    <td align="left" >{$order.buyer}</a></td>
    <td align="right"  nowrap="nowrap">{$order.formated_order_amount}</td>
	<td align="right"  nowrap="nowrap">{$order.formated_pay_amount}</td>
	<td align="right"  nowrap="nowrap">{$order.pay_name}</td>
    <td align="center" nowrap="nowrap">{$lang.ps[$order.pay_status]}</td>
    <td align="center" nowrap="nowrap">
     <a href="order.php?act=info&order_id={$order.order_id}">{$lang.detail}</a>
     {if $order.can_remove}
     &nbsp;&nbsp;<a href="javascript:;" onclick="listTable.remove({$order.order_id}, remove_confirm, 'remove_order')">{$lang.remove}</a>

	 &nbsp;&nbsp; <a href="order.php?act=confirm_order&order_id={$order.order_id}">{$lang.op_confirm}</a>
     {/if}
    </td>
  </tr>
  {/foreach}
</table>

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
<script language="JavaScript">
listTable.recordCount = {$record_count};
listTable.pageCount = {$page_count};

{foreach from=$filter item=item key=key}
listTable.filter.{$key} = '{$item}';
{/foreach}


    /**
     * 搜索订单
     */
    function searchOrder()
    {
        listTable.filter['order_sn'] = Utils.trim(document.forms['searchForm'].elements['order_sn'].value);
        listTable.filter['user_name'] = Utils.trim(document.forms['searchForm'].elements['username'].value);
        listTable.filter['pay_status'] = document.forms['searchForm'].elements['status'].value;
        listTable.filter['page'] = 1;
        listTable.loadList();
    }
</script>


{include file="pagefooter.tpl"}
{/if}
