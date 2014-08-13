{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}

<div class="form-div">
<table width="100%">
    <tr>
      <td width="40%">
      <form name="theForm" method="POST" action="admin_logs.php">
      {$lang.view_ip}
      <select class="textCtrl"  name="ip">
      <option value='0'>{$lang.select_ip}</option>
      {html_options options=$ip_list selected=$ip}
      </select>
      <input type="submit" class="button primary submitButton" value="{$lang.comfrom}" />
      </form>
      </td>
      <td width="60%">
      <form name="Form2" action="admin_logs.php?act=batch_drop" method="POST">
      {$lang.drop_logs}
      <select class="textCtrl"  name="log_date">
        <option value='0'>{$lang.select_date}</option>
        <option value='1'>{$lang.week_date}</option>
        <option value='2'>{$lang.month_date}</option>
        <option value='3'>{$lang.three_month}</option>
        <option value='4'>{$lang.six_month}</option>
        <option value='5'>{$lang.a_yaer}</option>
      </select>
      <input name="drop_type_date" type="submit" class="button primary submitButton"  value="{$lang.comfrom}" />
      </form>
      </td>
    </tr>
</table>
</div>

<form method="POST" action="admin_logs.php?act=batch_drop" name="listForm">
<!-- start admin_logs list -->
<div class="list-div" id="listDiv">
{/if}

<table cellpadding="3" cellspacing="1">
  <tr>
    <th><input onclick='listTable.selectAll(this, "checkboxes")' type="checkbox">
    <a href="javascript:listTable.sort('log_id'); ">{$lang.log_id}</a>{$sort_log_id}</th>
    <th><a href="javascript:listTable.sort('user_id'); ">{$lang.user_id}</a>{$sort_user_id}</th>
    <th><a href="javascript:listTable.sort('log_time'); ">{$lang.log_time}</a>{$sort_log_time}</th>
    <th><a href="javascript:listTable.sort('ip_address'); ">{$lang.ip_address}</a>{$sort_ip_address}</th>
    <th>{$lang.log_info}</th>
  </tr>
  {foreach from=$log_list item=list}
  <tr>
    <td width="10%"><span><input name="checkboxes[]" type="checkbox" value="{$list.log_id}" />{$list.log_id}</span></td>
    <td width="15%" class="first-cell"><span>{$list.user_name|escape:html}</span></td>
    <td width="20%" align="center"><span>{$list.log_time}</span></td>
    <td width="15%" align="left"><span>{$list.ip_address}</span></td>
    <td width="40%" align="left"><span>{$list.log_info}</span></td>
  </tr>
  {/foreach}
  <tr>
    <td colspan="2"><input name="drop_type_id" type="submit" class="button primary submitButton"  id="btnSubmit" value="{$lang.drop_logs}" disabled="true" /></td>
    <td align="right" nowrap="true" colspan="10">{include file="page.tpl"}</td>
  </tr>
</table>

{if $full_page}
</div>
<!-- end ad_position list -->

<script type="text/javascript" language="JavaScript">
  listTable.recordCount = {$record_count};
  listTable.pageCount = {$page_count};

  {foreach from=$filter item=item key=key}
  listTable.filter.{$key} = '{$item}';
  {/foreach}
</script>
{include file="pagefooter.tpl"}
{/if}
