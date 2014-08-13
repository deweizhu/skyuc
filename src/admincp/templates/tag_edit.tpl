{include file="pageheader.tpl"}
{insert_scripts files="validator.js,../js/transport.js}
<div class="main-div">
<form method="post" action="tag_manage.php" name="theForm" onSubmit="return validate()">
<table cellspacing="1" cellpadding="3" width="100%">
  <tr>
    <td class="label">{$lang.tag_words}</td>
    <td><input name="tag_name" type="text" id="tag_name" value="{$tag.tag_words}" maxlength="60" />
    </td>
  </tr>
  <tr>
    <td align="right">{$lang.name_search}</td>
    <td><input name="keyword" type="text" id="keyword">
      <input name="search" type="button" class="button"  id="search" value="{$lang.button_search}"  onclick="searchshow()" /></td>
  </tr>
  <tr>
    <td class="label">{$lang.title}</td>
    <td><select class="textCtrl"  name="show_id" id="show_id">
      <option value="{$tag.show_id}" selected="selected">{$tag.title}</option>
    </select>
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <input type="submit" class="button primary submitButton"  value="{$lang.button_submit}" />
      <input type="reset" class="button submitButton"   value="{$lang.button_reset}" />
      <input type="hidden" name="id" value="{$tag.tag_id}" /></td>
      <input type="hidden" name="act" value="{$insert_or_update}" />
  </tr>
</table>
</form>
</div>
<script language="JavaScript">
<!--
onload = function()
{
}
/**
 * 检查表单输入的数据
 */
function validate()
{
    validator = new Validator("theForm");
    validator.required('tag_name', tag_name_not_empty);
    return validator.passed();
}
function searchshow()
{
  var filter = new Object;
  filter.keyword  = document.forms['theForm'].elements['keyword'].value;
  Ajax.call('tag_manage.php?is_ajax=1&act=search_show', filter, searchshowResponse, 'GET', 'JSON');
}
function searchshowResponse(result)
{
  if (result.error == '1' && result.message != '')
  {
    alert(result.message);
	return;
  }
  var sel = document.forms['theForm'].elements['show_id'];
  sel.length = 0;
  /* 创建 options */
  var show = result.content;
  if (show)
  {
    for (i = 0; i < show.length; i++)
    {
      var opt = document.createElement("OPTION");
      opt.value = show[i].show_id;
      opt.text  = show[i].title;
      sel.options.add(opt);
    }
  }
  return;
}
//-->
</script>
{include file="pagefooter.tpl"}
