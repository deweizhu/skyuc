{include file="pageheader.tpl"}
<div class="list-div" id="listDiv">

<!-- {if $current_flashtpl eq 'dewei'} -->
<table cellspacing='1' cellpadding='3' id='list-table'>
  <tr>
    <th width="400px">{$lang.schp_title}</th>
	<th>{$lang.schp_url}</th>
    <th>{$lang.schp_text}</th>
	<th>{$lang.schp_type}</th>
	<th>{$lang.schp_sort}</th>
	<th width="70px">{$lang.handler}</th>
  </tr>
<!--   {foreach from=$playerdb item=item key=key} -->
  <tr>
    <td><a href="{$item.src}" target="_blank">{$item.title}</a></td>
 	<td align="left"><a href="{$item.url}" target="_blank">{$item.url}</a></td>
    <td align="left">{$item.text}</td>
	<td align="left">{if $item.type eq 2}{$lang.type_ad_text}{elseif $item.type eq 3}{$lang.type_ad_new}{else}{$lang.type_ad_img}{/if}</td>
	<td align="left">{$item.sort}</td>
	<td align="center">
      <a href="flashplay.php?act=edit&id={$key}" title="{$lang.edit}"><img src="images/icon_edit.gif" width="16" height="16" border="0" /></a>
      <a href="flashplay.php?act=del&id={$key}" onclick="return check_del();" title="{$lang.trash_img}"><img src="images/no.gif" width="16" height="16" border="0" /></a>
    </td>
  </tr>
<!--   {/foreach} -->
</table>
<!-- {else} -->
<table cellspacing='1' cellpadding='3' id='list-table'>
  <tr>
    <th width="400px">{$lang.schp_imgsrc}</th>
	<th>{$lang.schp_imgurl}</th>
    <th>{$lang.schp_imgdesc}</th>
	<th>{$lang.schp_sort}</th>
	<th width="70px">{$lang.handler}</th>
  </tr>
<!--   {foreach from=$playerdb item=item key=key} -->
  <tr>
    <td><a href="{$item.src}" target="_blank">{$item.src}</a></td>
 	<td align="left"><a href="{$item.url}" target="_blank">{$item.url}</a></td>
    <td align="left">{$item.text}</td>
	<td align="left">{$item.sort}</td>
	<td align="center">
      <a href="flashplay.php?act=edit&id={$key}" title="{$lang.edit}"><img src="images/icon_edit.gif" width="16" height="16" border="0" /></a>
      <a href="flashplay.php?act=del&id={$key}" onclick="return check_del();" title="{$lang.trash_img}"><img src="images/no.gif" width="16" height="16" border="0" /></a>
    </td>
  </tr>
<!--   {/foreach} -->
</table>
<!-- {/if} -->

</div>
<div class="list-div" style="margin-top:15px;">
<table>
<tr><th>{$lang.flash_template}</th></tr>
<tr>
    <td>{foreach from=$flashtpls item=flashtpl}
<table style="float:left;width: 220px;">
<tr>
  <td><strong><center>{$flashtpl.name}&nbsp;{if $flashtpl.code eq $current_flashtpl}<span style="color:red;" id="current_theme">{$lang.current_theme}</span>{/if}</center></strong></td>
</tr>
<tr>
  <td>{if $flashtpl.screenshot}<img src="{$flashtpl.screenshot}" border="0" style="cursor:pointer" onclick="setupFlashTpl('{$flashtpl.code}', this)" />{/if}</td>
</tr>
<tr>
  <td valign="top">{$flashtpl.desc}</td>
</tr>
</table>
{/foreach}</td>
</tr>
</table>
</div>
<script language="JavaScript">
<!--

function check_del()
{
	if (confirm('{$lang.trash_img_confirm}'))
	{
		return true;
	}
	else
	{
		return false;
	}
}

/**
 * 安装Flash样式模板
 */
function setupFlashTpl(tpl, obj)
{
    window.current_tpl_obj = obj;
    if (confirm(setupConfirm))
    {
        Ajax.call('flashplay.php?is_ajax=1&act=install', 'flashtpl=' + tpl, setupFlashTplResponse, 'GET', 'JSON');
    }
}

function setupFlashTplResponse(result)
{
    if (result.message.length > 0)
    {
        alert(result.message);
    }

    if (result.error == 0)
    {
        var tmp_obj = window.current_tpl_obj.parentNode.parentNode.previousSibling;
        while (tmp_obj.nodeName.toLowerCase() != 'tr')
        {
            tmp_obj = tmp_obj.previousSibling;
        }
        tmp_obj = tmp_obj.getElementsByTagName('center');
        tmp_obj[0].appendChild(document.getElementById('current_theme'));
    }

}
//-->
</script>

{include file="pagefooter.tpl"}
