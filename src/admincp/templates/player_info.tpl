{include file="pageheader.tpl"}
<div class="main-div">
<form method="post" action="player.php" name="theForm" enctype="multipart/form-data" onsubmit="return validate()">
<table >
  <tr>
    <td class="label">{$lang.player_title}:</td>
    <td><input type="text"   name="player_title" maxlength="60" value="{$player.title}" /></td>
  </tr>
  <tr>
    <td class="label">{$lang.player_tag}:<a href="javascript:showNotice('noticeplayer_tag');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
    <td><input type="text"   name="player_tag" maxlength="60" value="{$player.tag}" />{$lang.require_field} <br /><span class="notice-span" id="noticeplayer_tag">{$lang.notice_player_tag}</td>
  </tr>
   <tr>
    <td class="label">{$lang.user_rank}:<a href="javascript:showNotice('noticeuser_rank');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
    <td>

		{foreach from=$ranks item=rank name='rank'}
	<input name="user_rank[]" type="checkbox" value="{$rank.id}"
	{if in_array($rank.id, $player.user_rank) !== false}checked="checked"{/if}
	/>{$rank.name}
	{/foreach}
	 <br /><span class="notice-span" id="noticeuser_rank">{$lang.notice_user_rank}
	</td>
  </tr>
  <tr>
    <td class="label">{$lang.player_code}:<a href="javascript:showNotice('noticeplayer_code');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
    <td><textarea class="textCtrl"  name="player_code" style='width:99%;height:450px;word-wrap: break-word;word-break:break-all;'  >{$player.player_code}</textarea>
	<br /><span class="notice-span" id="noticeplayer_code">{$lang.notice_player_code}</td>
  </tr>

  <tr>
    <td class="label">{$lang.sort_order}:</td>
    <td><input type="text"   name="sort_order" maxlength="40" size="15" value="{$player.sort_order}" /></td>
  </tr>
  <tr>
    <td class="label">{$lang.is_show}:</td>
    <td><input type="radio" name="is_show" value="1" {if $player.is_show eq 1}checked="checked"{/if} /> {$lang.yes}
        <input type="radio" name="is_show" value="0" {if $player.is_show eq 0}checked="checked"{/if} /> {$lang.no}
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center"><br />
	  <input type="hidden" name="s" value="{$session.sessionhash}" />
      <input type="submit" class="button primary submitButton" value="{$lang.button_submit}" />
      <input type="reset" class="button submitButton"   value="{$lang.button_reset}" />
      <input type="hidden" name="act" value="{$form_action}" />
      <input type="hidden" name="old_playername" value="{$player.title}" />
      <input type="hidden" name="id" value="{$player.id}" />
    </td>
  </tr>
</table>
</form>
</div>
{insert_scripts files="skyuc_validator.js"}

<script language="JavaScript">
<!--
document.forms['theForm'].elements['player_title'].focus();
/**
 * 检查表单输入的数据
 */
function validate()
{
    validator = new Validator("theForm");
    validator.required("player_tag",  no_playertitle);
    validator.isNumber("sort_order", require_num, true);
    return validator.passed();
}
//-->
</script>

{include file="pagefooter.tpl"}
