{include file="pageheader.tpl"}
{insert_scripts files="../js/utils.js"}
<div class="main-div">
<form name="theForm" method="post" action="">
  <table cellspacing="1" cellpadding="3" width="100%">
    <tr>
      <td class="label">{$lang.label_category}</td>
      <td><select class="textCtrl"  name="category" id="category">
        <option value="0" selected>{$lang.all_category}</option>
        {$cat_list}
      </select></td>
    </tr>
    <tr>
      <td class="label">{$lang.label_server}</td>
      <td><select class="textCtrl"  name="server" id="server">
        <option value="0" selected>{$lang.all_server}</option>
        {html_options options=$server_list}
      </select></td>
    </tr>
    <tr>
      <td class="label">{$lang.label_intro_type}</td>
      <td><select class="textCtrl"  name="intro_type" id="intro_type">
        <option value="all" selected>{$lang.all_intro_type}</option>
        {html_options options=$intro_list}
      </select></td>
    </tr>
    <tr>
      <td class="label">{$lang.label_need_image}</td>
      <td>        <label>
        <select class="textCtrl"  name="need_image" id="need_image">
          <option value="true" selected>{$lang.need}</option>
          <option value="false">{$lang.need_not}</option>
        </select>
      </label></td>
    </tr>
    <tr>
      <td class="label">{$lang.label_show_num}</td>
      <td><input name="show_num" type="text" id="show_num" value="1" /></td>
    </tr>
    <tr>
      <td class="label">{$lang.label_arrange}</td>
      <td><select class="textCtrl"  name="arrange" id="arrange">
        <option value="h" selected>{$lang.horizontal}</option>
        <option value="v">{$lang.verticle}</option>
      </select></td>
    </tr>
    <tr>
      <td class="label">{$lang.label_charset}</td>
      <td><select class="textCtrl"  name="charset" id="charset">
        {html_options options=$lang_list}
      </select></td>
    </tr>
    <tr>
      <td class="label">{$lang.label_sitename}</td>
      <td><input name="sitename" type="text" id="sitename"></td>
    </tr>
    <tr>
      <td colspan="2" align="center"><input type="button" class="button primary submitButton"    name="gen_code" value="{$lang.generate}" onclick="genCode()" />        </td>
      </tr>
    <tr>
      <td colspan="2" align="center"><textarea class="textCtrl" name="code" cols="80" rows="5" id="code"></textarea></td>
      </tr>
  </table>
</form>
</div>
<script language="JavaScript">
    var elements = document.forms['theForm'].elements;
    var url = '{$url}';
    /**
     * 生成代码
     */
    function genCode()
    {
        // 检查输入
        if (isNaN(parseInt(elements['show_num'].value)))
        {
            alert(show_num_must_be_int);
            return;
        }
        if (elements['show_num'].value < 1)
        {
            alert(show_num_must_over_0);
            return;
        }

        // 生成代码
        var code = '\<script src=\"' + url + 'show_script.php?';
        if (elements['category'].value > 0)
        {
            code += 'cat_id=' + elements['category'].value + '&';
        }
        if (elements['server'].value > 0)
        {
            code += 'server_id=' + elements['server'].value + '&';
        }
        if (elements['intro_type'].value != 'all')
        {
            code += 'intro_type=' + elements['intro_type'].value + '&';
        }
        code += 'need_image=' + elements['need_image'].value + '&';
        code += 'show_num=' + elements['show_num'].value + '&';
        code += 'arrange=' + elements['arrange'].value + '&';
        code += 'charset=' + elements['charset'].value + '&';
        code += 'sitename=' + encodeURI(elements['sitename'].value);
        code += '\"\>\</script\>';
        elements['code'].value = code;
        elements['code'].select();
        if (Browser.isIE)
        {
            window.clipboardData.setData("Text",code);
        }
    }
</script>
{include file="pagefooter.tpl"}
