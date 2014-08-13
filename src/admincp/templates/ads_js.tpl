{include file="pageheader.tpl"}
<div class="form-div">
<form action="" method="post" name="js_code">
  <table width="100%">
    <tr>
      <td class="label">{$lang.outside_address}</td>
      <td><input type="text" name="outside_address" size="30" /></td>
    </tr>
    <tr>
      <td class="label">{$lang.label_charset}</td>
      <td><select class="textCtrl"  name="charset" id="charset">
        {html_options options=$lang_list}
      </select></td>
    </tr>
    <tr>
      <td colspan="2"><div align="center">
        <input type="button" class="button"  name="gen_code" value="{$lang.add_js_code}" onclick="validate(); genCode(); autocopy()"  />
      </div></td>
      </tr>
    <tr>
      <td colspan="2">
        <div align="center">
          <textarea class="textCtrl" name="ads_js" cols="70" rows="5">{$js_code}</textarea>
        </div></td>
    </tr>
  </table>
 </form>
</div>
{insert_scripts files="skyuc_validator.js"}
<script language="JavaScript">
var elements = document.forms['js_code'].elements;
var url = '{$url}';

<!--

document.forms['js_code'].elements['outside_address'].focus();
/**
 * 检查表单输入的数据
 */
function validate()
{
    validator = new Validator("js_code");
    validator.required("outside_address",  no_outside_address);
    return validator.passed();
}
/**
 * 生成代码
 */
function genCode()
{
    // 生成代码
    var code = '<script src="' + url;
        code += '&from=' + encodeURI(elements['outside_address'].value);
        code += '&charset=' + elements['charset'].value;
        code += '"></script\>';
        elements['ads_js'].value = code;
}

function autocopy()
{
    if (Browser.isIE)
    {
        window.clipboardData.setData('text', document.js_code.ads_js.value);
    }
    document.forms['js_code'].elements['ads_js'].select();
}

//-->

</script>
{include file="pagefooter.tpl"}
