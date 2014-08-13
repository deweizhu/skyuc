{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}
<form method="post" name="theForm" action="javascript:saveTemplate()">

<div class="main-div">
  <table id="general-table" align="center">
  <tr>
    <td style="font-weight: bold; " width="15%">{$lang.select_template}</td>
    <td>
      <select class="textCtrl"  id="selTemplate" onchange="loadTemplate()">
        {html_options options=$templates}
      </select>
    </td>
  </tr>
  <tr>
    <td style="font-weight: bold; " width="15%">{$lang.mail_subject}:</td>
    <td><input type="text" name="subject" id="subject" style="width: 300px" value="{$template.template_subject}"/></td>
  </tr>
  <tr>
    <td style="font-weight: bold" >{$lang.mail_type}:</td>
    <td>
      <input type="radio" name="mail_type" value="0" {if $template.is_html eq '0'}checked="true"{/if} />{$lang.mail_plain_text}
      <input type="radio" name="mail_type" value="1" {if $template.is_html eq '1'}checked="true"{/if} />{$lang.mail_html}
    </td>
  </tr>
  <tr>
    <td colspan="2"><textarea class="textCtrl" id="content" style="width:90%" rows="16" >{$template.template_content|escape:html}</textarea></td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input type="submit" class="button primary submitButton" value="{$lang.button_submit}"  /></td>
  </tr>
  </table>
</div>
</form>

<script language="JavaScript">
var orgContent = '';
onload = function()
{
    document.getElementById('selTemplate').focus();
    document.forms['theForm'].reset();
}

/**
 * 载入模板
 */
function loadTemplate()
{
  curContent = document.getElementById('content').value;

  if (orgContent != curContent && orgContent != '')
  {
    if (!confirm(save_confirm))
    {
      return;
    }
  }

  var tpl = document.getElementById('selTemplate').value;

  Ajax.call('template.php?is_ajax=1&act=loat_template', 'tpl=' + tpl, loadTemplateResponse, "GET", "JSON");
}

/**
 * 将模板的内容载入到文本框中
 */
function loadTemplateResponse(result, textResult)
{
  var elems = document.forms['theForm'].elements;
  if (result.error == 0)
  {
    elems['subject'].value = result.content.template_subject;

    for (i = 0; i < elems.length; i++)
    {
        if (elems[i].type=="radio" && elems[i].name=="mail_type" && elems[i].value == result.content.is_html)
        {
            elems[i].checked = true;
            break;
        }
    }

    elems['content'].value = result.content.template_content;

    orgContent = elems['content'].value;
  }

  if (result.message.length > 0)
  {
    alert(result.message);
  }
}

/**
 * 保存模板内容
 */
function saveTemplate()
{
    var selTemp = document.getElementById('selTemplate').value;
    var subject = document.getElementById('subject').value;
    var content = document.getElementById('content').value;
    var type    = 0;
    var em      = document.forms['theForm'].elements;

    for (i = 0; i < em.length; i++)
    {
        if (em[i].type == 'radio' && em[i].name == 'mail_type' && em[i].checked)
        {
            type = em[i].value;
        }
    }
    Ajax.call('template.php?{$session.sessionurl_js}act=save_template&is_ajax=1', 'tpl=' + selTemp + "&subject=" + subject + "&content=" + content + "&is_html=" + type, saveTemplateResponse, "POST", "JSON");
}

/**
 * 提示用户保存成功或失败
 */
function saveTemplateResponse(result)
{
  if (result.error == 0)
  {
    orgContent = document.getElementById('content').value;
  }
  else
  {
    document.getElementById('content').value = orgContent;
  }

  if (result.message.length > 0)
  {
    alert(result.message);
  }
}

</script>
{include file="pagefooter.tpl"}
