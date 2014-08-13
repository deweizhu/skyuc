{include file="pageheader.tpl"}
<div class="tab-div">
  <!-- tab bar -->
  <div id="tabbar-div">
    <p>
      {foreach from=$group_list item=group name="bar_group"}<span class="{if $smarty.foreach.bar_group.iteration eq 1}tab-front{else}tab-back{/if}" id="{$group.code}-tab">{$group.name}</span>{/foreach}
    </p>
  </div>
  <!-- tab body -->
  <div id="tabbody-div">
    <form enctype="multipart/form-data" name="theForm" action="?act=post" method="post">
    {foreach from=$group_list item=group name="body_group"}
    <table width="90%" id="{$group.code}-table" {if $smarty.foreach.body_group.iteration neq 1}style="display:none"{/if}>
	   {if $group.code eq "optimization"}
	   <tr>
	   <td colspan="2"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.cfg_name.optimization}" />{$lang.cfg_desc.optimization}<td>
	   </tr>
		{/if}
      {foreach from=$group.vars item=var}
      <tr>
        <td class="label" valign="top">
          {$var.name}: {if $var.desc}
          <a href="javascript:showNotice('notice{$var.code}');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}" /></a>
          {/if}
        </td>
        <td>
          {if $var.type eq "text"}
          <input name="value[{$var.id}]" type="text"   value="{$var.value}" size="40" />

          {elseif $var.type eq "password"}
          <input name="value[{$var.id}]"   type="password" value="{$var.value}" size="40" />

          {elseif $var.type eq "textarea"}
          <textarea class="textCtrl" name="value[{$var.id}]" cols="40" rows="5">{$var.value}</textarea>
		  {elseif $var.type eq "select"}
		  	<select class="textCtrl"  name="value[{$var.id}]">
			    {foreach from=$var.site_options key=key item=opt}
				<option value="{$opt}" {if $opt eq $var.value}selected{/if}>{$var.display_options[$key]}</option>
				{/foreach}
			</select>
          {elseif $var.type eq "radio"}
            {foreach from=$var.site_options key=key item=opt}
            <label for="value_{$var.id}_{$key}"><input type="radio" name="value[{$var.id}]" id="value_{$var.id}_{$key}" value="{$opt}"
            {if $var.value eq $opt}checked="true"{/if}
			   {if $var.code eq 'rewrite'}
              onclick="return ReWriterConfirm(this);"
            {/if}
			{if $var.code eq 'smtp_ssl' and $opt eq 1}
              onclick="return confirm('{$lang.smtp_ssl_confirm}');"
            {/if}
            />{$var.display_options[$key]}</label>
            {/foreach}
          {elseif $var.type eq "file"}

          <input name="{$var.code}" type="file" size="40" />
          {if ($var.code eq "site_logo" or $var.code eq "no_picture" or $var.code eq "watermarkimg") and $var.value}
            <img src="images/yes.gif" border="0" onmouseover="showImg('{$var.code}_layer', 'show')" onmouseout="showImg('{$var.code}_layer', 'hide')" />
            <div id="{$var.code}_layer" style="position:absolute; width:100px; height:100px; z-index:1; visibility:hidden" border="1">
              <img src="{$var.value}" border="0" />
            </div>
          {else}
            {if $var.value neq ""}
            <img src="images/yes.gif" alt="yes" />
            {else}
            <img src="images/no.gif" alt="yes" />
            {/if}
          {/if}
          {elseif $var.type eq "manual"}
			{if $var.code eq "ipcheck"}
			      <select class="textCtrl"  name="value[{$var.id}]">
					{html_options values=$ipcheck output=$ipcheck selected=$var.value}
                  </select>
			{elseif $var.code eq "gziplevel"}
			      <select class="textCtrl"  name="value[{$var.id}]">
					{html_options values=$gziplevel output=$gziplevel selected=$var.value}
                  </select>
			{elseif $var.code eq "user_rank"}
			     <select class="textCtrl"  name="value[{$var.id}]">
              {foreach from=$user_rank item=item}
                  <option value="{$item.rank_id}" {if $item.rank_id eq $cfg.user_rank}selected{/if}>{$item.rank_name}</option>
                {/foreach}
                  </select>
            {elseif $var.code eq "lang"}
                  <select class="textCtrl"  name="value[{$var.id}]">
					{html_options values=$lang_list output=$lang_list selected=$var.value}
                  </select>
            {/if}
          {/if}
          {if $var.desc}
          <br />
          <span class="notice-span" id="notice{$var.code}">{$var.desc|nl2br}</span>
          {/if}
        </td>
      </tr>
      {/foreach}
      {if $group.code eq "smtp"}
        <tr>
          <td class="label">{$lang.cfg_name.test_mail_address}:</td>
          <td>
            <input type="text"   name="test_mail_address" size="30" />
            <input type="button" class="button"  value="{$lang.cfg_name.send}" onclick="sendTestEmail();"  />
          </td>
        </tr>
      {/if}
    </table>
    {/foreach}

    <div class="button-div">
		<input type="hidden" name="s" value="{$session.sessionhash}" />
      <input name="submit" type="submit" class="button primary submitButton"  value="{$lang.button_submit}" />
      <input name="reset" type="reset" class="button submitButton"  value="{$lang.button_reset}" />
    </div>
    </form>
  </div>
</div>
{insert_scripts files="skyuc_tab.js,skyuc_validator.js"}

<script language="JavaScript">
/**
 * 测试邮件的发送
 */
function sendTestEmail()
{
  var eles              = document.forms['theForm'].elements;
  var smtp_host         = eles['value[503]'].value;
  var smtp_port         = eles['value[504]'].value;
  var smtp_user         = eles['value[505]'].value;
  var smtp_pass         = eles['value[506]'].value;
  var reply_email       = eles['value[507]'].value;
  var test_mail_address = eles['test_mail_address'].value;

  var mail_charset = 0;

  for (i = 0; i < eles['value[508]'].length; i++)
  {
    if (eles['value[508]'][i].checked)
    {
      mail_charset = eles['value[508]'][i].value;
    }
  }

 var use_smtp = 0;

  for (i = 0; i < eles['value[509]'].length; i++)
  {
    if (eles['value[509]'][i].checked)
    {
      use_smtp = eles['value[509]'][i].value;
    }
  }

  var msg = '';
  if (smtp_host.length == 0)
  {
    msg += smtp_host_empty + "\n";
  }
  if (smtp_port.length == 0)
  {
    msg += smtp_port_empty + "\n";
  }
  if (reply_email.length == 0)
  {
    msg += reply_email_empty + "\n";
  }
  if (test_mail_address.length == 0)
  {
    msg += test_email_empty + "\n";
  }
  if (reply_email.length > 0 && test_mail_address.length > 0)
  {
    if (reply_email == test_mail_address)
    {
      msg += email_address_same + "\n";
    }
  }

  if (msg.length > 0)
  {
    alert(msg);
    return;
  }


Ajax.call('setting.php?is_ajax=1&act=send_test_email',
    'test_mail_address=' + test_mail_address +  '&use_smtp=' + use_smtp + '&smtp_host=' + smtp_host + '&smtp_port=' + smtp_port +
    '&smtp_user=' + smtp_user + '&smtp_pass=' + smtp_pass + '&reply_email=' + reply_email + '&mail_charset=' + mail_charset,
	function(result){
	alert(result.message);
	}, 'POST', 'JSON');



}

/**
 * URL重写
 */
var ReWriteSelected = null;
var ReWriteRadiobox = document.getElementsByName("value[320]");

for (var i=0; i<ReWriteRadiobox.length; i++)
{
  if (ReWriteRadiobox[i].checked)
  {
    ReWriteSelected = ReWriteRadiobox[i];
  }
}

function ReWriterConfirm(sender)
{
  if (sender == ReWriteSelected) return true;
  var res = true;
  if (sender != ReWriteRadiobox[0]) {
    var res = confirm('{$lang.rewrite_confirm}');
  }

  if (res==false)
  {
      ReWriteSelected.checked = true;
  }
  else
  {
    ReWriteSelected = sender;
  }
  return res;
}
</script>

{include file="pagefooter.tpl"}
