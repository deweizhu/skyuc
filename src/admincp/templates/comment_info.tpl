{include file="pageheader.tpl"}
<!-- comment content list -->
<div class="main-div">
  <table width="100%">
    <tr>
      <td>
      <a href="mailto:{$msg.email}"><b>{if $msg.user_name}{$msg.user_name}{else}{$lang.anonymous}{/if}</b></a>&nbsp;{$lang.from}
      &nbsp;{$msg.add_time}&nbsp;{$lang.to}&nbsp;<b>{$id_value}</b>&nbsp;{$lang.send_comment}
    </td>
    </tr>
    <tr>
      <td><hr color="#dadada" size="1"></td>
    </tr>
    <tr>
      <td>
	  <div>{$msg.content}</div>
        <div align="right"><b>{$lang.ip_address}</b>: {$msg.ip_address}</div>
		</td>
    </tr>
  <tr>
      <td align="center">
        {if $msg.status eq "0"}
        <input type="button" class="button"  onclick="location.href='comment_manage.php?act=check&check=allow&id={$msg.comment_id}'" value="{$lang.allow}"  />
        {else}
        <input type="button" class="button"  onclick="location.href='comment_manage.php?act=check&check=forbid&id={$msg.comment_id}'" value="{$lang.forbid}"  />
        {/if}
    </td>
    </tr>
  </table>
  </div>
{if $reply_info.content}
<!-- reply content list -->
<div class="main-div">
  <table width="100%">
    <tr>
      <td>
     {$lang.admin_user_name}&nbsp;<a href="mailto:{$msg.email}"><b>{$reply_info.user_name}</b></a>&nbsp;{$lang.from}
      &nbsp;{$reply_info.add_time}&nbsp;{$lang.reply}
    </td>
    </tr>
    <tr>
      <td><hr color="#dadada" size="1"></td>
    </tr>
    <tr>
      <td>
		<div>{$reply_info.content}</div>
        <div align="right"><b>{$lang.ip_address}</b>: {$reply_info.ip_address}</div>
		</td>
    </tr>
  </table>
</div>
{/if}
</div>
<div class="main-div">
<form method="post" action="comment_manage.php?act=action" name="theForm" onsubmit="return validate()">
<table border="0" align="center">
  <tr><th colspan="2">
  <strong>{$lang.reply_comment}</strong>
  </th></tr>
  <tr>
    <td>{$lang.user_name}:</td>
    <td><input name="user_name" type="text"  {if $reply_info.user_name eq ""}value="{$admin_info.user_name}"{else} value="{$reply_info.user_name}"{/if} size="30" readonly="true" /></td>
  </tr>
  <tr>
    <td>{$lang.email}:</td>
    <td><input name="email" type="text"  {if $reply_info.email eq ""}value="{$admin_info.email}"{else} value="{$reply_info.email}"{/if} size="30" readonly="true" /></td>
  </tr>
  <tr>
    <td>{$lang.reply_content}:</td>
    <td><textarea class="textCtrl" name="content" cols="50" rows="4" wrap="VIRTUAL"></textarea></td>
  </tr>
  {if $reply_info.content}
  <tr>
    <td>&nbsp;</td>
    <td>{$lang.have_reply_content}</td>
  </tr>
  {/if}
  <tr>
    <td>&nbsp;</td>
    <td>
      <input name="submit" type="submit" class="button primary submitButton"  value="{$lang.button_submit}" >
      <input type="reset" class="button submitButton"  value="{$lang.button_reset}" >
      <input type="hidden" name="comment_id" value="{$msg.comment_id}">
      <input type="hidden" name="comment_type" value="{$msg.comment_type}">
      <input type="hidden" name="id_value" value="{$msg.id_value}">
    </td>
  </tr>
</table>
</form>
</div>
{insert_scripts files="skyuc_validator.js"}

<script language="JavaScript">
<!--
document.forms['theForm'].elements['content'].focus();

/**
 * 检查表单输入的数据
 */
function validate()
{
    validator = new Validator("theForm");
    validator.required("content",  no_content);
    return validator.passed();
}

//-->
</script>

{include file="pagefooter.tpl"}
