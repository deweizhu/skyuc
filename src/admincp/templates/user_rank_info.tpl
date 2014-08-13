{include file="pageheader.tpl"}

<div class="main-div">
<form action="user_rank.php" method="post" name="theForm" onsubmit="return validate()">
<table width="100%">
  <tr>
    <td class="label">{$lang.rank_name}: </td>
    <td><input type="text" name="rank_name" value="{$rank.rank_name}"  maxlength="30" />{$lang.require_field}</td>
  </tr>
  <tr>
    <td class="label">{$lang.rank_type}: </td>
    <td><input type="radio" name="rank_type" value="1"  {if $rank.rank_type}checked="checked" {/if}/>{$lang.rank_day}
			<input type="radio" name="rank_type" value="0"  {if $rank.rank_type eq 0}checked="checked" {/if} />{$lang.rank_count}</td>
  </tr>
  <tr>
    <td class="label">{$lang.count}: </td>
    <td><input type="text" name="count" value="{$rank.count}"  size="10" maxlength="20" />&nbsp;&nbsp;{$lang.dayorpoint}</td>
  </tr>
    <tr>
    <td class="label">{$lang.money}: </td>
    <td><input type="text" name="money" value="{$rank.money}"  size="10" maxlength="20" /></td>
  </tr>
   <tr>
    <td class="label">{$lang.day_play}: </td>
    <td><input type="text" name="day_play" value="{$rank.day_play}"   size="10" maxlength="20" />&nbsp;&nbsp;{$lang.day_film}</td>
  </tr>
  <tr>
    <td class="label">{$lang.day_down}: </td>
    <td><input type="text" name="day_down" value="{$rank.day_down}"  size="10" maxlength="20" />&nbsp;&nbsp;{$lang.day_film}</td>
  </tr>
  <tr>
	<td class="label">{$lang.allow_cat}：</td>
	<td>
	{foreach from=$cat_list item=cat name='cat'}
	<input name="allow_cate[]" type="checkbox" value="{$cat.cat_id}"
	{if in_array($cat.cat_id,$rank.allow_cate) !== false}
			checked="checked"
		{/if}
	/>{$cat.cat_name}

	{/foreach}
	{$lang.require_field}</td>
   </tr>
   <tr>
    <td class="label">{$lang.allow_hours}: <a href="javascript:showNotice('notice_allow_hours');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
    <td><input type="text" name="allow_hours" value="{$rank.allow_hours}"  size="50" maxlength="50" />&nbsp;&nbsp; <span class="notice-span" id="notice_allow_hours">{$lang.allow_hours_notice}</span></td>
  </tr>
    <tr>
    <td class="label">{$lang.content}：</td>
    <td><input type="text"  name="content" value="{$rank.content}" maxlength="254" size="50" /> {$lang.require_field}</td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <input type="hidden" name="act" value="{$form_action}" />
      <input type="hidden" name="id" value="{$rank.rank_id}" />
      <input type="submit" class="button primary submitButton" value="{$lang.button_submit}"  />
      <input type="reset" class="button submitButton"  value="{$lang.button_reset}"  />
    </td>
  </tr>
</table>
</form>
</div>
{insert_scripts files="skyuc_validator.js"}
{literal}
<script language="JavaScript">
<!--
document.forms['theForm'].elements['rank_name'].focus();

/**
 * 检查表单输入的数据
 */
function validate()
{
    validator = new Validator("theForm");
    validator.required('rank_name', rank_name_empty);
    validator.isInt('day_play', day_play_invalid, true);
	validator.isInt('day_down', day_down_invalid, true);
	validator.isInt('count', count_invalid, true);
	validator.isInt('money', money_invalid, true);
    validator.required("content",  no_content);
    return validator.passed();
}
//-->
</script>
{/literal}
{include file="pagefooter.tpl"}
