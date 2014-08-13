{include file="pageheader.tpl"}
{insert_scripts files="skyuc_validator.js"}
<form action="payment.php" method="post">
<div class="main-div">
<table cellspacing="1" cellpadding="3">
  <tr>
    <td class="label">{$lang.payment_name}</td>
    <td><input name="pay_name" type="text" value="{$pay.pay_name|escape}" size="40" /></td>
  </tr>
  <tr>
    <td class="label">{$lang.payment_desc}</td>
    <td><textarea class="textCtrl" name="pay_desc" cols="60" rows="8">{$pay.pay_desc|escape}</textarea></td>
  </tr>
  {foreach from=$pay.pay_config item=config}
  <tr>
    <td class="label">
      <span class="label">{$config.label}</span> {if $config.desc}
      <a href="javascript:showNotice('notice{$config.name}');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a>
      {/if}
    </td>
    <td>
      <!-- {if $config.type == "text"} -->
      <input name="cfg_value[]" type="{$config.type}" value="{$config.value}" size="40" />
      <!-- {elseif $config.type == "textarea"} -->
      <textarea class="textCtrl" name="cfg_value[]" cols="80" rows="5">{$config.value}</textarea>
      <!-- {elseif $config.type == "select"} -->
      <select class="textCtrl"  name="cfg_value[]">{html_options options=$config.range selected=$config.value}</select>
      <!-- {/if} -->
      <input name="cfg_name[]" type="hidden" value="{$config.name}" />
      <input name="cfg_type[]" type="hidden" value="{$config.type}" />
      <input name="cfg_lang[]" type="hidden" value="{$config.lang}" />
      {if $config.desc}
      <br /><span class="notice-span" id="notice{$config.name}">{$config.desc}</span>
      {/if}
    </td>
  </tr>
  {/foreach}
  <tr>
    <td class="label">{$lang.pay_fee}</td>
    <td>{if $pay.is_cod }<input name="pay_fee" type="text" value="{$pay.pay_fee|default:0}" />
        {else}<input name="pay_fee" type="hidden" value="{$pay.pay_fee|default:0}" />{$lang.decide_by_ship}{/if}
    </td>

  </tr>
  <tr>
    <td class="label">{$lang.payment_is_cod}</td>
    <td>{if $pay.is_cod == "1"}{$lang.yes}{else}{$lang.no}{/if}</td>
  </tr>
  <tr align="center">
    <td colspan="2">
      <input type="hidden"  name="pay_id"       value="{$pay.pay_id}" />
      <input type="hidden"  name="pay_code"     value="{$pay.pay_code}" />
      <input type="hidden"  name="is_cod"       value="{$pay.is_cod}" />
      <input type="submit" class="button primary submitButton"  name="Submit"       value="{$lang.button_submit}" />
      <input type="reset" class="button submitButton"    name="Reset"        value="{$lang.button_reset}" />
    </td>
  </tr>
</table>
</div>
</form>
{include file="pagefooter.tpl"}
