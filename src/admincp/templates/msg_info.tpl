{include file="pageheader.tpl"}
<div class="main-div">
  <table width="98%">
    <tr>
      <td>{$msg.user_name}&nbsp;&nbsp;&lt;{$msg.user_email}&gt;:{$msg.msg_title|escape:"html"}</td>
    <td align="right"  nowrap="nowrap">{$msg.msg_time}</td>
    </tr>
    <tr>
      <td colspan="2"><p>{$msg.msg_content|escape:"html"}</p></td>
    </tr>
    {if $msg.message_img}
    <tr>
      <td>&nbsp;</td>
      <td align="right">
        <a href="../upload/feedbackimg/{$msg.message_img}" target="_bank" width="300" height="400">{$lang.view_upload_file}</a>
        <a href="user_msg.php?act=drop_file&id={$msg.msg_id}&file={$msg.message_img}">{$lang.drop}</a>
      </td>
    </tr>
    {/if}
  </table>
</div>

{if $msg.reply_id}
<div class="main-div">
  <table width="98%">
    <tr>
      <td>{$msg.reply_name} {$lang.from} {$msg.reply_time} {$lang.reply}:</td>
    </tr>
    <tr>
      <td><p>{$msg.reply_content|escape:"html"}</p></td>
    </tr>
  </table>
</div>
{/if}

<div class="main-div">
<form method="post" action="user_msg.php?act=action" name="theForm"  onsubmit="return validate()">
<table border="0" width="98%">
  <tr>
    <td>{$lang.email}:</td>
    <td><input name="user_email" id="user_email"  type="text" value="{$msg.reply_email}"></td>
  </tr>
  <tr>
    <td>{$lang.reply_content}:</td>
    <td rowspan="2"><textarea class="textCtrl" name="msg_content" cols="50" rows="4" wrap="VIRTUAL" id="msg_content">{$msg.reply_content}</textarea></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  {if $msg.reply_id}
  <tr>
    <td>&nbsp;</td>
    <td>{$lang.have_reply_content}</td>
  </tr>
  {/if}
  <tr>
    <td>&nbsp;</td>
    <td>
      <input type="hidden" name="msg_id" value="{$msg.msg_id}">
      <input type="hidden" name="parent_id" value="{$msg.reply_id}">
      <input name="Submit" value="{$lang.button_submit}" type="submit" class="button primary submitButton"  >
    </td>
  </tr>
</table>
</form>
</div>
{insert_scripts files="skyuc_validator.js"}
<script language="JavaScript">
<!--

document.forms['theForm'].elements['msg_content'].focus();

/**
 * 检查表单输入的数据
 */
function validate()
{
    validator = new Validator("theForm");
    validator.required("msg_content",  no_content);
    return validator.passed();
}
//-->

</script>
{include file="pagefooter.tpl"}
