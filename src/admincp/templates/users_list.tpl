{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}

<div class="form-div">
  <form action="javascript:searchUser()" name="searchForm">
    <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />
    &nbsp;{$lang.label_user_type}  <!-- 过滤器 -->
    <select class="textCtrl"  name="intro_type"><option value="0">{$lang.intro_type}</option>{html_options options=$intro_list selected=$smarty.get.intro_type}</select>
	<!-- 关键字 -->
	 &nbsp;<INPUT TYPE="checkbox" NAME="no_day" VALUE="1">{$lang.no_day} <INPUT TYPE="checkbox" NAME="no_count" VALUE="1"> {$lang.no_count}<INPUT TYPE="checkbox" NAME="no_point" VALUE="1">{$lang.no_point} <INPUT TYPE="checkbox" NAME="no_money" VALUE="1">{$lang.no_money}
    &nbsp;{$lang.label_user_name} &nbsp;<input type="text"  name="keyword" /> <input type="submit" class="button primary submitButton" value="{$lang.button_search}"  />
  </form>
</div>

<form method="POST" action="" name="listForm">

<!-- start users list -->
<div class="list-div" id="listDiv">
{/if}
<!--用户列表部分-->
<table cellpadding="3" cellspacing="1">
  <tr>
    <th>
      <input onclick='listTable.selectAll(this, "checkboxes")' type="checkbox">
      <a href="javascript:listTable.sort('user_id'); ">{$lang.record_id}</a>{$sort_user_id}
    </th>
    <th><a href="javascript:listTable.sort('user_name'); ">{$lang.username}</a>{$sort_user_name}</th>
    <th><a href="javascript:listTable.sort('email'); ">{$lang.email}</a>{$sort_email}</th>
    <th><a href="javascript:listTable.sort('reg_time'); ">{$lang.reg_date}</a>{$sort_reg_time}</th>
    <th><a href="javascript:listTable.sort('usertype'); ">{$lang.label_user_type}</a>{$sort_usertype}</th>
    <th><a href="javascript:listTable.sort('user_point'); ">{$lang.label_user_point}</a>{$sort_user_point}</th>
    <th><a href="javascript:listTable.sort('unit_date'); ">{$lang.label_unit_date}</a>{$sort_unit_date}</th>
    <th><a href="javascript:listTable.sort('pay_point'); ">{$lang.label_pay_point}</a>{$sort_pay_point}</th>
    <th>{$lang.handler}</th>
  <tr>
  {foreach from=$user_list item=user}
  <tr>
    <td><input type="checkbox" name="checkboxes[]" value="{$user.user_id}" />{$user.user_id}</td>
    <td class="first-cell">{$user.user_name|escape}</td>
    <td><span onclick="listTable.edit(this, 'edit_email', {$user.user_id})">{$user.email}</span></td>
    <td align="center">{$user.reg_time}</td>
    <td align="center">{if $user.usertype}{$lang.is_day}{else}{$lang.is_count}{/if}</td>
    <td>{$user.user_point}</td>
		<td align="center">{$user.unit_date}</td>
		<td>{$user.pay_point}</td>
    <td align="center">
      <a href="users.php?act=edit&id={$user.user_id}" title="{$lang.edit}"><img src="images/icon_edit.gif" border="0" height="16" width="16" /></a>
      <a href="order.php?act=list&user_id={$user.user_id}" title="{$lang.view_order}"><img src="images/icon_view.gif" border="0" height="16" width="16" /></a>
      <a href="account_log.php?act=list&user_id={$user.user_id}" title="{$lang.view_deposit}"><img src="images/icon_account.gif" border="0" height="16" width="16" /></a>
      <a href="javascript:confirm_redirect('{$lang.remove_confirm}', 'users.php?act=remove&id={$user.user_id}')" title="{$lang.remove}"><img src="images/icon_drop.gif" border="0" height="16" width="16" /></a>
    </td>
  </tr>
  {foreachelse}
  <tr><td class="no-records" colspan="10">{$lang.no_records}</td></tr>
  {/foreach}
  <tr>
      <td colspan="2">
      <input type="hidden" name="act" value="batch_remove" />
      <input type="submit" class="button primary submitButton"  id="btnSubmit" value="{$lang.button_remove}" disabled="true"  /></td>
      <td align="right" nowrap="true" colspan="8">
      {include file="page.tpl"}
      </td>
  </tr>
</table>

{if $full_page}
</div>
<!-- end users list -->
</form>
<script type="text/javascript" language="JavaScript">
<!--
listTable.recordCount = {$record_count};
listTable.pageCount = {$page_count};

{foreach from=$filter item=item key=key}
listTable.filter.{$key} = '{$item}';
{/foreach}


onload = function()
{
    document.forms['searchForm'].elements['keyword'].focus();
}

/**
 * 搜索用户
 */
function searchUser()
{
    listTable.filter['keywords'] = Utils.trim(document.forms['searchForm'].elements['keyword'].value);
	if (document.forms['searchForm'].elements['no_day'].checked){
	listTable.filter['no_day'] = Utils.trim(document.forms['searchForm'].elements['no_day'].value);
	}
	if (document.forms['searchForm'].elements['no_count'].checked){
    listTable.filter['no_count']=Utils.trim(document.forms['searchForm'].elements['no_count'].value);
	}
	if (document.forms['searchForm'].elements['no_point'].checked){
	listTable.filter['no_point']=Utils.trim(document.forms['searchForm'].elements['no_point'].value);
	}
	if (document.forms['searchForm'].elements['no_money'].checked){
	listTable.filter['no_money']=Utils.trim(document.forms['searchForm'].elements['no_money'].value);}
    listTable.filter['intro_type'] = document.forms['searchForm'].elements['intro_type'].value;
    listTable.filter['page'] = 1;
    listTable.loadList();
}
//-->
</script>

{include file="pagefooter.tpl"}
{/if}
