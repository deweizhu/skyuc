{include file="pageheader.tpl"}
<div class="main-div">
<form action="vote.php" method="post" name="theForm" enctype="multipart/form-data" onsubmit="return validate()">
<table width="100%" id="general-table">
  <tr>
    <td class="label">{$lang.vote_name}</td>
    <td>
      <input type='text' name='vote_name' value='{$vote_arr.vote_name}' size='40' />
    </td>
  </tr>
  <tr>
    <td class="label">{$lang.begin_date}</td>
    <td>
      {html_select_date prefix="begin_date" field_order="YMD" time=$vote_arr.begin_date}
    </td>
  </tr>
      <tr>
      <td class="label">{$lang.end_date}</td>
      <td>
        {html_select_date prefix="end_date" field_order="YMD" end_year="+1"  time=$vote_arr.end_date}
      </td>
    </tr>
  <tr>
    <td class="label">{$lang.can_multi}</td>
    <td>
      <input type="radio" name="can_multi" value="0"{if $vote_arr.can_multi eq 0} checked="true" {/if}/>{$lang.is_multi}
      &nbsp;&nbsp;&nbsp;&nbsp;
      <input type="radio" name="can_multi" value="1"{if $vote_arr.can_multi eq 1} checked="true" {/if}/>{$lang.no_multi}
    </td>
  </tr>
  <tr>
    <td class="label">&nbsp;</td>
    <td>
      <input type="submit" class="button primary submitButton" value="{$lang.button_submit}"  />
      <input type="reset" class="button submitButton"  value="{$lang.button_reset}"  />
    </td>
  </tr>
</table>
    <input type="hidden" name="act" value="{$form_act}" />
    <input type="hidden" name="id" value="{$vote_arr.vote_id}" />
</form>
</div>
{insert_scripts files="skyuc_validator.js"}

<script language="JavaScript">
<!--
document.forms['theForm'].elements['vote_name'].focus();
/**
 * 检查表单输入的数据
 */
function validate()
{
    validator = new Validator("theForm");
    validator.required("vote_name",      vote_name_empty);
    return validator.passed();
}
//-->
</script>

{include file="pagefooter.tpl"}
