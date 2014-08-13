{include file="pageheader.tpl"}
<!-- start add new category form -->
<div class="main-div">
  <form action="category.php" method="post" name="theForm" enctype="multipart/form-data" onsubmit="return validate()">

  <table width="100%" id="general-table">
      <tr>
        <td class="label">{$lang.cat_name}:</td>
        <td>
          <input type='text' name='cat_name' maxlength="20" value='{$cat_info.cat_name|escape:html}' size='27' class='text'/> <font color="red">*</font>
        </td>
      </tr>
      <tr>
        <td class="label">{$lang.parent_id}:</td>
        <td>
          <select class="textCtrl"  name="parent_id">
          <option value="0">{$lang.cat_top}</option>
            {$cat_select}
          </select>
        </td>
      </tr>

      <tr>
        <td class="label">{$lang.sort_order}:</td>
        <td>
          <input type="text"   name='sort_order' {if $cat_info.sort_order}value='{$cat_info.sort_order}'{else} value="0"{/if} size="15" />
        </td>
      </tr>
	   <tr>
        <td class="label">{$lang.is_show}:</td>
        <td>
          <input type="radio" name="is_show" value="1" {if $cat_info.is_show neq 0} checked="true"{/if}/> {$lang.yes}
          <input type="radio" name="is_show" value="0" {if $cat_info.is_show eq 0} checked="true"{/if} /> {$lang.no}
        </td>
      </tr>

      <tr>
        <td class="label">{$lang.show_in_nav}:</td>
        <td>
          <input type="radio" name="show_in_nav" value="1" {if $cat_info.show_in_nav neq 0} checked="true"{/if}/> {$lang.yes}
          <input type="radio" name="show_in_nav" value="0" {if $cat_info.show_in_nav eq 0} checked="true"{/if} /> {$lang.no}
        </td>
      </tr>
	   <tr>
        <td class="label"><a href="javascript:showNotice('noticeshowSN');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.notice_style}"></a>{$lang.cat_style}:</td>
        <td>
          <input type="text" name="style" value="{$cat_info.style|escape}" size="40" class='text' /> <br />
          <span class="notice-span" style="display:none" id="noticeshowSN">{$lang.notice_style}</span>
        </td>
      </tr>
      <tr>
        <td class="label">{$lang.keywords}:</td>
        <td><input type="text" name="keywords" value='{$cat_info.keywords}' size="50" class='text' />
        </td>
      </tr>

      <tr>
        <td class="label">{$lang.cat_desc}:</td>
        <td>
          <textarea class="textCtrl" name='cat_desc' rows="6" cols="48">{$cat_info.cat_desc}</textarea>
        </td>
      </tr>

      </table>
      <div class="button-div">
        <input type="submit" class="button primary submitButton"  value="{$lang.button_submit}" />
        <input type="reset" class="button submitButton"    value="{$lang.button_reset}" />
      </div>
    <input type="hidden" name="act" value="{$form_act}" />
	<input type="hidden" name="old_cat_name" value="{$cat_info.cat_name}" />
    <input type="hidden" name="cat_id" value="{$cat_info.cat_id}" />
  </form>
</div>
{insert_scripts files="skyuc_validator.js"}

<script language="JavaScript">
<!--
document.forms['theForm'].elements['cat_name'].focus();
/**
 * 检查表单输入的数据
 */
function validate()
{
      validator = new Validator("theForm");
    validator.required("cat_name",      catname_empty);
    return validator.passed();
}
onload = function()
{
}

//-->
</script>

{include file="pagefooter.tpl"}
