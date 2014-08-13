{include file="pageheader.tpl"}
<div class="main-div">
<form method="post" action="server.php" name="theForm" enctype="multipart/form-data" onsubmit="return validate()">
<table >
  <tr>
    <td class="label">{$lang.server_name}</td>
    <td><input type="text"   name="server_name" maxlength="60" value="{$server.server_name}" />{$lang.require_field}</td>
  </tr>
  <tr>
    <td class="label">{$lang.server_url}</td>
    <td><input type="text"   name="server_url" maxlength="255" size="40" value="{$server.server_url}" /></td>
  </tr>
  <tr>
    <td class="label">{$lang.server_desc}</td>
    <td><textarea class="textCtrl"  name="server_desc" cols="60" rows="4"  >{$server.server_desc}</textarea></td>
  </tr>
  <tr>
    <td class="label">{$lang.sort_order}</td>
    <td><input type="text"   name="sort_order" maxlength="40" size="15" value="{$server.sort_order}" /></td>
  </tr>
  <tr>
    <td class="label">{$lang.is_show}</td>
    <td><input type="radio" name="is_show" value="1" {if $server.is_show eq 1}checked="checked"{/if} /> {$lang.yes}
        <input type="radio" name="is_show" value="0" {if $server.is_show eq 0}checked="checked"{/if} /> {$lang.no}
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center"><br />
      <input type="submit" class="button primary submitButton"  value="{$lang.button_submit}" />
      <input type="reset" class="button submitButton"   value="{$lang.button_reset}" />
      <input type="hidden" name="act" value="{$form_action}" />
      <input type="hidden" name="old_servername" value="{$server.server_name}" />
      <input type="hidden" name="id" value="{$server.server_id}" />
      <input type="hidden" name="old_serverlogo" value="{$server.server_logo}">
    </td>
  </tr>
</table>
</form>
</div>
{insert_scripts files="skyuc_validator.js"}

<script language="JavaScript">
<!--
document.forms['theForm'].elements['server_name'].focus();
/**
 * 检查表单输入的数据
 */
function validate()
{
    validator = new Validator("theForm");
    validator.required("server_name",  no_servername);
    validator.isNumber("sort_order", require_num, true);
    return validator.passed();
}
//-->
</script>

{include file="pagefooter.tpl"}
