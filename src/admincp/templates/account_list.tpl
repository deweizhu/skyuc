{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}

<div class="form-div">
<form method="post" action="account_log.php?act=list&user_id={$smarty.get.user_id}" name="searchForm">
  <select class="textCtrl"  name="account_type" onchange="document.forms['searchForm'].submit()">
    <option value="" {if $account_type eq ''}selected="selected"{/if}>{$lang.all_account}</option>
    <option value="user_money" {if $account_type eq 'user_money'}selected="selected"{/if}>{$lang.user_money}</option>
    <option value="pay_point" {if $account_type eq 'pay_point'}selected="selected"{/if}>{$lang.pay_point}</option>
  </select>
  <strong>{$lang.label_user_name}</strong>{$user.user_name}
  <strong>{$lang.label_user_money}</strong>{$user.formated_user_money}
  <strong>{$lang.label_pay_point}</strong>{$user.pay_point}
  </form>
</div>

<form method="post" action="" name="listForm">
<div class="list-div" id="listDiv">
{/if}

  <table cellpadding="3" cellspacing="1">
    <tr>
      <th width="20%">{$lang.change_time}</th>
      <th width="30%">{$lang.change_desc}</th>
      <th>{$lang.user_money}</th>
      <th>{$lang.pay_point}</th>
    </tr>
    {foreach from=$account_list item=account}
    <tr>
      <td>{$account.change_time}</td>
      <td>{$account.change_desc|escape:html}</td>
      <td align="right">
        {if $account.user_money gt 0}
          <span style="color:#0000FF">+{$account.user_money}</span>
        {elseif $account.user_money lt 0}
          <span style="color:#FF0000">{$account.user_money}</span>
        {else}
          {$account.user_money}
        {/if}
      </td>
      <td align="right">
        {if $account.pay_point gt 0}
          <span style="color:#0000FF">+{$account.pay_point}</span>
        {elseif $account.pay_point lt 0}
          <span style="color:#FF0000">{$account.pay_point}</span>
        {else}
          {$account.pay_point}
        {/if}
      </td>
    </tr>
    {foreachelse}
    <tr><td class="no-records" colspan="6">{$lang.no_records}</td></tr>
    {/foreach}
  </table>
<table id="page-table" cellspacing="0">
  <tr>
    <td align="right" nowrap="true">
    {include file="page.tpl"}
    </td>
  </tr>
</table>

{if $full_page}
</div>
</form>

<script type="text/javascript" language="javascript">
  <!--
  listTable.recordCount = {$record_count};
  listTable.pageCount = {$page_count};

  {foreach from=$filter item=item key=key}
  listTable.filter.{$key} = '{$item}';
  {/foreach}
  //-->
</script>
{include file="pagefooter.tpl"}
{/if}
