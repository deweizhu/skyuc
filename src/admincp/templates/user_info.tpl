{include file="pageheader.tpl"}
<div class="main-div">
<form method="post" action="users.php" name="theForm" onsubmit="return validate()">
<table >
  <tr>
    <td class="label">{$lang.username}：</td>
    <td><input type="text"  name="username" maxlength="60" value="{$user.user_name}" />{$lang.require_field}</td>
  </tr>
  <tr>
    <td class="label">{$lang.email}：</td>
    <td><input type="text"  name="email" maxlength="60" size="40" value="{$user.email}" />{$lang.require_field}</td>
  </tr>
  {if $form_action eq "insert"}
  <tr>
    <td class="label">{$lang.password}：</td>
    <td><input type="password"  name="password" maxlength="30"  />{$lang.require_field}</td>
  </tr>
 {elseif  $form_action eq "update"}
  <tr>
    <td class="label">{$lang.newpass}:</td>
    <td><input type="password"  name="password" maxlength="30"  /></td>
  </tr>
  <tr>
    <td class="label">{$lang.confirm_password}:</td>
    <td><input type="password"  name="confirm_password" maxlength="30"  /></td>
  </tr>
  {/if}
    <tr>
    <td class="label">{$lang.user_rank}:</td>
    <td> <select class="textCtrl"  name="user_rank">
			  <option value="0">{$lang.not_rank}</option>
			  {foreach from=$ranks item=rank}
				<option value="{$rank.id}" {if $user.user_rank eq $rank.id} selected="selected" {/if}>{$rank.name}</option>
              {/foreach}
		</select>
	</td>
  </tr>
  <tr>
    <td class="label">{$lang.label_gender}:</td>
    <td>{html_radios name="gender" options=$lang.gender checked=$user.gender}</td>
  </tr>
  <tr>
    <td class="label">{$lang.birthday}：</td>
    <td>{html_select_date field_order="YMD" prefix="birthday" time=$user.birthday start_year="-50" end_year="-10"}</td>
  </tr>
  <tr>
    <td class="label">{$lang.label_pay_point}：<a href="javascript:showNotice('noticePayPoints');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a> </td>
    <td><input type="text"  name="pay_point" maxlength="10"  value="{$user.pay_point}" />{$lang.require_field}<br /><span class="notice-span" id="noticePayPoints">{$lang.notice_pay_point}</span></td>
  </tr>
   <tr>
      <td class="label">{$lang.label_user_type}：</td>
       <td><input type="radio" name="usertype" value="1" {if $user.usertype eq 1} checked="checked" {/if} />{$lang.is_day}
			<input type="radio" name="usertype" value="0" {if $user.usertype neq 1} checked="checked" {/if} />
			{$lang.is_count}
		</td>
  </tr>
  {if $form_action eq "update"}
  <tr>
    <td class="label">{$lang.label_user_money}：</td>
    <td>{$user.user_money}  <a href="user_account.php?act=list&id={$user.user_id}">[ {$lang.view_detail_account} ]</a>
    </td>
  </tr>
   <tr>
    <td class="label">{$lang.label_unit_date}：<a href="javascript:showNotice('noticeLookdate');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a> </td>
    <td>{html_select_date field_order="YMD" prefix="unit_date" time=$user.unit_date start_year="-2" end_year="+10"}<br /><span class="notice-span" id="noticeLookdate">{$lang.notice_unit_date}</span></td>
  </tr>
   <tr>
    <td class="label">{$lang.label_user_point}：<a href="javascript:showNotice('noticeLookPoints');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a> </td>
    <td><input type="text"  name="user_point" maxlength="20"  value="{$user.user_point}" /><br /><span class="notice-span" id="noticeLookPoints">{$lang.notice_user_point}</span></td>
  </tr>
   <tr>
    <td class="label">{$lang.label_msn}： </td>
    <td><input type="text"  name="other[msn]" maxlength="60" size="40"  value="{$user.msn}" /></td>
  </tr>
    <tr>
    <td class="label">{$lang.label_qq}：</td>
    <td><input type="text"  name="other[qq]" maxlength="20"  value="{$user.qq}" /></td>
  </tr>

    <tr>
    <td class="label">{$lang.label_phone}： </td>
    <td><input type="text"  name="other[phone]" maxlength="20"  value="{$user.phone}" /></td>
  </tr>
   <tr>
    <td class="label">{$lang.visit_count}： </td>
     <td>{$user.visit_count}</td>
  </tr>
    <tr>
    <td class="label">{$lang.lastactivity}： </td>
     <td>{$user.lastactivity}</td>
  </tr>
   <tr>
    <td class="label">{$lang.lastvisit}： </td>
     <td>{$user.lastvisit}</td>
  </tr>
 <tr>
    <td class="label">{$lang.minute}： </td>
    <td>{$user.minute}</td>
  </tr>
   <tr>
    <td class="label">{$lang.playcount}： </td>
    <td>{$user.playcount}</td>
  </tr>
    <tr>
    <td class="label">{$lang.last_ip}： </td>
     <td>{$user.last_ip}</td>
  </tr>
  <!-- {if $user.firstname} -->
   <tr>
    <td class="label">{$lang.other_firstname}： </td>
     <td>{$user.firstname}</td>
  </tr>
  <!-- {/if} -->
  <!-- {if $user.firstname} -->
    <tr>
    <td class="label">{$lang.other_referrer}： </td>
     <td>{$user.referrer}</td>
  </tr>
  <!-- {/if} -->
  {/if}
  <tr>
    <td colspan="2" align="center">
      <input type="submit" class="button primary submitButton" value="{$lang.button_submit}"  />
      <input type="reset" class="button submitButton"  value="{$lang.button_reset}"  />
      <input type="hidden" name="act" value="{$form_action}" />
      <input type="hidden" name="id" value="{$user.user_id}" />
	  <input type="hidden" name="old_username" value="{$user.user_name}" />
    </td>
  </tr>
</table>

</form>
</div>
{insert_scripts files="skyuc_validator.js"}

<script language="JavaScript">
<!--
document.forms['theForm'].elements['username'].focus();
/**
 * 检查表单输入的数据
 */
function validate()
{
    validator = new Validator("theForm");
	validator.required("username",  no_username);
    validator.isEmail("email", invalid_email, true);
    validator.isInt("pay_point", invalid_pay_point, true);

    if (document.forms['theForm'].elements['act'] == "insert")
    {
        validator.required("password", no_password);
        validator.required("confirm_password", no_confirm_password);
        validator.eqaul("password", "confirm_password", password_not_same);

    }
	 else if (document.forms['theForm'].elements['act'].value == "update")
    {
        var newpass = document.forms['theForm'].elements['password'];
        var confirm_password = document.forms['theForm'].elements['confirm_password'];
        if(newpass.value.length > 0 || confirm_password.value.length)
        {
          if(newpass.value.length >= 6 || confirm_password.value.length >= 6)
          {
            validator.eqaul("password", "confirm_password", password_not_same);
          }
          else
          {
            validator.addErrorMsg(password_len_err);
          }
        }
    }

    return validator.passed();
}
//-->
</script>

{include file="pagefooter.tpl"}
