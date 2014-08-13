{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}

<div class="form-div">
  <form method="post" action="javascript:searchMessage()" name="theForm">
  {$lang.select_msg_type}:
  <select class="textCtrl"  name="msg_type" onchange="javascript:searchMessage()">
    <option value="1" {if $msg_type eq 1} selected="selected" {/if}>{$lang.all_msg}</option>
    <option value="2" {if $msg_type eq 2} selected="selected" {/if}>{$lang.all_send_msg}</option>
    <option value="3" {if $msg_type eq 3} selected="selected" {/if}>{$lang.no_read_msg}</option>
    <option value="4" {if $msg_type eq 4} selected="selected" {/if}>{$lang.is_read_msg}</option>
  </select>
  <input type="submit" class="button primary submitButton" value="{$lang.button_submit}"  />
  </form>
</div>

<!-- start admin_message list -->
<form method="POST" action="message.php?act=drop_msg" name="listForm">
<div class="list-div" id="listDiv">
{/if}

  <table cellpadding="3" cellspacing="1">
    <tr>
      <th>
        <input onclick='listTable.selectAll(this, "checkboxes")' type="checkbox" />
        <a href="javascript:listTable.sort('message_id'); ">{$lang.record_id}</a>{$sort_message_id}
      </th>
      <th><a href="javascript:listTable.sort('title'); ">{$lang.title}</a>{$sort_title}</th>
      <th><a href="javascript:listTable.sort('sender_id'); ">{$lang.sender_id}</a>{$sort_sender_id}</th>
      <th><a href="javascript:listTable.sort('send_date'); ">{$lang.send_date}</a>{$sort_send_date}</th>
      <th><a href="javascript:listTable.sort('read_date'); ">{$lang.read_date}</a>{$sort_read_date}</th>
      <th>{$lang.handler}</th>
    </tr>
    {foreach from=$message_list item=msg}
    <tr>
      <td><input type="checkbox" name="checkboxes[]" value="{$msg.message_id}" />{$msg.message_id}</td>
      <td class="first-cell">{$msg.title|escape:html|truncate:35}</td>
      <td>{$msg.user_name|escape:html}</td>
      <td align="right">{$msg.send_date}</td>
      <td align="right">{$msg.read_date}</td>
      <td align="center">
        <a href="message.php?act=view&id={$msg.message_id}" title="{$lang.view_msg}"><img src="images/icon_view.gif" border="0" /></a>
         <a href="javascript:;" onclick="listTable.remove({$msg.message_id}, '{$lang.drop_confirm}')"><img src="images/icon_drop.gif" border="0" /></a>
      </td>
    </tr>
    {foreachelse}
    <tr><td class="no-records" colspan="10">{$lang.no_records}</td></tr>
    {/foreach}
  </table>

  <table cellpadding="4" cellspacing="0">
    <tr>
      <td><input type="submit" class="button primary submitButton" name="drop" id="btnSubmit" value="{$lang.drop}"  disabled="true" /></td>
      <td align="right">{include file="page.tpl"}</td>
    </tr>
  </table>

{if $full_page}
</div>
</form>
<script type="text/javascript" language="JavaScript">
<!--
  listTable.recordCount = {$record_count};
  listTable.pageCount = {$page_count};

  {foreach from=$filter item=item key=key}
  listTable.filter.{$key} = '{$item}';
  {/foreach}


  /**
   * 查询留言
   */
  function searchMessage()
  {
    listTable.filter.msg_type = document.forms['theForm'].elements['msg_type'].value;
    listTable.filter.page = 1;
    listTable.loadList();
  }

//-->
</script>

{include file="pagefooter.tpl"}
{/if}
