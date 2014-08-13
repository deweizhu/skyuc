{include file="pageheader.tpl"}
<!-- start integrate setup form -->
<div style="border: 1px solid #CC0000;background-color:#FFFFCE;color:#CE0000;padding:4px;" >{$lang.check_notice}</div>
<div class="main-div" style="padding:5px;">
  <form action="integrate.php" method="post" name="theForm">
  <h3>{$lang_total}</h3>
  <ul>
    <li style="list-style-type:none;">{$lang.lable_size}&nbsp;&nbsp;<input type="text" name="size" size="5" value="{$size}" id="SKYUC_SIZE"></li>
  </ul>
  <h3>{$lang.default_method}</h3>
  <ul>
    <li style="list-style-type:none;"><input type="radio" name="method" value="2" />{$lang.lable_rename}&nbsp;&nbsp;<input type="type" name="domain" value="{$domain}" /></li>
    <li style="list-style-type:none;"><input type="radio" name="method" value="3" />{$lang.lable_delete}</li>
    <li style="list-style-type:none;"><input type="radio" name="method" value="4" />{$lang.lable_ignore}</li>
  </ul>
  <h3 id="SKYUC_LOADING" style="display:none">{$lang.checking}</h3>
  <p id="SKYUC_NOTICE"></p>
  <input type="button" class="button"  value="{$lang.start_check}"  onclick="check_start(this)">
  </form>
</div>
<!-- end integrate setup form -->
{insert_scripts files="skyuc_validator.js"}
<script language="JavaScript">
<!--

function check_start(obj)
{
  var frm = document.forms['theForm'];
  var method = -1;
  for (var i=0; i<frm.elements['method'].length; i++)
  {
    if (frm.elements['method'][i].checked)
    {
      method = frm.elements['method'][i].value;
    }
  }
  if (method < 0)
  {
    alert(no_method);
    return;
  }

  var size = parseInt(frm.elements['size'].value)
  var domain = frm.elements['domain'].value;

  var loading = document.getElementById('SKYUC_LOADING');
  loading.style.display = '';
  obj.disabled = true;
  Ajax.call('integrate.php?act=check_user', 'start=0&size=' + size + '&method=' + method + '&domain=' + domain, checkResponse, 'GET', 'JSON');
}

function checkResponse(result)
{
  if (result.message.length > 0)
  {
    alert(result.message);
  }
  if (result.error == 0)
  {
    var notice = document.getElementById('SKYUC_NOTICE');
    notice.innerHTML = result.content;
    if (result.is_end == 0)
    {
      Ajax.call('integrate.php?act=check_user', 'start=' + result.start + '&size=' + result.size + '&method=' + result.method + '&domain=' + result.domain, checkResponse, 'GET', 'JSON');
    }
    else
    {
      location.href = result.href;
    }
  }
}
//-->
</script>
{include file="pagefooter.tpl"}
