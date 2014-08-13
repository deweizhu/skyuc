<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{$lang.cp_home}{if $ur_here} - {$ur_here}{/if}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="styles/general.css" rel="stylesheet" type="text/css" />
<link href="styles/main.css" rel="stylesheet" type="text/css" />

<style type="text/css">
body {
  color: white;
}
</style>

{insert_scripts files="../skyuc_global.js,../skyuc_utils.js,skyuc_validator.js"}

<script language="JavaScript">
<!--
// 这里把JS用到的所有语言都赋值到这里
{foreach from=$lang.js_languages key=key item=item}
var {$key} = "{$item}";
{/foreach}

if (window.parent != window)
{
  window.top.location.href = location.href;
}

//-->
</script>
</head>
<body style="background: #278296">
<form method="post" id="loginControls" action="privilege.php" name='theForm' onsubmit="return validate()">
  <table cellspacing="0" cellpadding="0" style="margin-top: 100px" align="center">
  <tr>
    <td><img src="images/login.gif" width="178" height="256" border="0" alt="{$lang.app_name}" /></td>
    <td style="padding-left: 50px">
      <table>
      <tr>
        <td>{$lang.label_username}</td>
        <td><input type="text"  name="username" id="username" class="textCtrl" /></td>
      </tr>
      <tr>
        <td>{$lang.label_password}</td>
        <td><input type="password"  name="password" id="password" class="textCtrl" /></td>
      </tr>
	<!-- {if $humanverify} -->
	<tr>
        <td colspan="2">
	  <fieldset class="fieldset">
	<legend>{$lang.label_captcha}</legend>
	<table cellpadding="0" cellspacing="3"  border="0" width="100%" >

	<tr>
		<td width="100%" valign="top">
			{$lang.input_captcha}<br /><br />
			<input type="text"  name="humanverify[input]"  id="verifycode"  size="10" maxlength="6" class="textCtrl" />
			<input type="hidden" name="humanverify[hash]"  id="hash"  value="{$humanverify.hash}" />
		</td>
		<td valign="bottom" align="center">
			<img id="verifyimage" src="../image.php?{$session.sessionurl}type=hv&amp;hash={$humanverify.hash}"
			 style="cursor: pointer;" title="{$lang.click_for_another}"  width="201" height="61" border="0"/>
			<SCRIPT LANGUAGE="JavaScript">
			<!--
			 $(document).ready(function()
			 {
				$(':text').addClass('input text');
				$(':password').addClass('input text');
				$('#username').focus();
				$('#verifyimage').click(function(){getimage();});

				/* Ajax设置 */
				Ajax.onRunning = null;
				Ajax.onComplete = null;

			 });
			function getimage()
			 {
				 if ($('#verifyimage').attr('alt') != 'undefined')
				 {
				   var url = "../ajax.php?{$session.sessionurl_js}type=hv&do=imagereg&hash=" + $('#verifyimage').attr('alt');
				 }else
				 {
				   var url = "../ajax.php?{$session.sessionurl_js}type=hv&hash={$humanverify.hash}&do=imagereg";
				 }

				Ajax.call(url, '', function(data){
							var src= '../image.php?{$session.sessionurl_js}type=hv&hash=' + data.message;
							$('#verifyimage').attr('src', src);
							$('#verifyimage').attr('alt', data.message);
							$('#hash').attr('value', data.message);
						}, 'POST', 'JSON');
			}

			//-->
			</SCRIPT>

		</td>
	</tr>
	</table>
</fieldset>
</td>
      </tr>
<!-- {/if} -->
      <tr><td>&nbsp;</td><td><input type="submit" class="button primary submitButton" value="{$lang.signin_now}" /></td></tr>
      <tr>
        <td colspan="2" align="right">&raquo; <a href="../" style="color:white">{$lang.back_home}</a> &raquo; <a href="get_password.php?act=forget_pwd" style="color:white">{$lang.forget_pwd}</a></td>
      </tr>
      </table>
    </td>
  </tr>
  </table>
  <input type="hidden" name="act" value="signin" />
  <input type="hidden" name="url" value="{$scriptpath}" />
  <input type="hidden" name="s" value="{$sessionhash}" />
  <input type="hidden" name="securitytoken" value="{$securitytoken}" />
</form>
<script language="JavaScript">
<!--
  /**
   * 检查表单输入的内容
   */
  function validate()
  {
    var validator = new Validator('theForm');
    validator.required('username', user_name_empty);

	if (document.forms['theForm'].elements['verifycode'])
    {
      validator.required('verifycode', captcha_empty);
    }
    return validator.passed();
  }

//-->
</script>
</body>
