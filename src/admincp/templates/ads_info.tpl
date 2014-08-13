{include file="pageheader.tpl"}
<div class="main-div">
<form action="ads.php" method="post" name="theForm" enctype="multipart/form-data" onsubmit="return validate()">
<table width="100%" id="general-table">
  {if $action eq "add"}
    <tr>
      <td class="label">{$lang.media_type}</td>
      <td>
       <select class="textCtrl"  name="media_type" onchange="showMedia(this.value)">
       <option value='0'>{$lang.ad_img}</option>
       <option value='1'>{$lang.ad_flash}</option>
       <option value='2'>{$lang.ad_html}</option>
       <option value='3'>{$lang.ad_text}</option>
       </select>
      </td>
    </tr>
  {/if}
  <tr>
    <td  class="label">{$lang.position_id}</td>
    <td>
    <select class="textCtrl"  name="position_id">
    <option value='0'>{$lang.outside_posit}</option>
      {html_options options=$position_list selected=$ads.position_id}
    </select>
  </td>
  </tr>
  <tr>
    <td  class="label">{$lang.start_date}</td>
    <td>
      {html_select_date prefix="start_date" field_order="YMD"  time=$ads.start_date start_year="+0" end_year="+3"}
    </td>
  </tr>
    <tr>
      <td class="label">{$lang.end_date}</td>
      <td>
        {html_select_date prefix="end_date" field_order="YMD"  time=$end_date start_year="+0" end_year="+3"}
      </td>
    </tr>
  <tr>
    <td  class="label">
      {$lang.ad_name}<a href="javascript:showNotice('NameNotic');" title="{$lang.form_notice}">
      <img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
    <td>
      <input type="text" name="ad_name" value="{$ads.ad_name}" size="35" />
      <br /><span class="notice-span" id="NameNotic">{$lang.ad_name_notic}</span>
    </td>
  </tr>
  <tr>
    <td  class="label">{$lang.ad_link}</td>
    <td>
      <input type="text" name="ad_link" value="{$ads.ad_link}" size="35" />
    </td>
  </tr>
</table>
{if $action eq "add"}
<!-- img ad form -->
<div id="0" style="display">
<table width="100%">
  <tr>
    <td  class="label">
     {$lang.upfile_img} <a href="javascript:showNotice('AdCodeImg');" title="{$lang.form_notice}">
      <img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
    <td>
      <input type='file' name='ad_img' size='35' />
      <br /><span class="notice-span" id="AdCodeImg">{$lang.ad_code_img}</span>
    </td>
  </tr>
  <tr>
    <td  class="label">{$lang.img_url}</td>
    <td>
      <input type="text" name="img_url" value="" size="35" />
    </td>
  </tr>
</table>
</div>
 <!-- Flash ad form -->
<div id="1" style="display:none">
<table width="100%">
  <tr>
    <td  class="label">
      {$lang.upfile_flash}<a href="javascript:showNotice('AdCodeFlash');" title="{$lang.form_notice}">
      <img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
    <td>
      <input type='file' name='upfile_flash' size='35' />
      <br /><span class="notice-span" id="AdCodeFlash">{$lang.ad_code_flash}</span>
    </td>
  </tr>
  <tr>
    <td  class="label">{$lang.flash_url}</td>
    <td>
      <input type="text" name="flash_url" value="" size="35" />
    </td>
  </tr>
</table>
</div>
<!-- text ad form -->
<div id="3" style="display:none">
<table width="100%">
  <tr>
    <td  class="label">{$lang.ad_code}</td>
    <td>
      <textarea class="textCtrl" name="ad_text" cols="65" rows="7">{$ads.ad_code}</textarea>
    </td>
  </tr>
</table>
</div>
<!-- code ad form -->
<div id="2" style="display:none">
<table width="100%">
  <tr>
    <td  class="label">{$lang.enter_code}</td>
    <td>
      <textarea class="textCtrl" name="ad_code" cols="65" rows="7">{$ads.ad_code}</textarea>
    </td>
  </tr>
</table>
</div>
{else}
  {if $ads.media_type eq 0}
    <table width="100%">
      <tr>
        <td  class="label">
         {$lang.upfile_img} <a href="javascript:showNotice('AdCodeImg');" title="{$lang.form_notice}">
          <img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
        <td>
          <input type='file' name='ad_img' size='35' />
          <br /><span class="notice-span" id="AdCodeImg">{$lang.ad_code_img}</span>
        </td>
      </tr>
      <tr>
        <td  class="label">{$lang.img_url}</td>
        <td>
          <input type="text" name="img_url" value="{$url_src}" size="35" />
        </td>
      </tr>
      <tr>
        <td  class="label">{$lang.ad_code}</td>
        <td>
        {if $img_src}<img src="{$img_src}" />{else}<img src="{$url_src}" />{/if}
        </td>
      </tr>
    </table>
  {/if}
  {if $ads.media_type eq 1}
    <table width="100%">
      <tr>
        <td  class="label">
          {$lang.upfile_flash}<a href="javascript:showNotice('AdCodeFlash');" title="{$lang.form_notice}">
          <img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
        <td>
          <input type='file' name='upfile_flash' size='35' />
          <br /><span class="notice-span" id="AdCodeFlash">{$lang.ad_code_flash}</span>
        </td>
      </tr>
      <tr>
        <td class="label">{$lang.flash_url}</td>
        <td>
          <input type="text" name="flash_url" value="{$flash_url}" size="35" />
        </td>
      </tr>
       <tr>
        <td class="label">{$lang.ad_code}</td>
        <td>
          <object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0' width="450" height="150">
             <param name='movie' value='{$src}'>
             <param name='quality' value='high'>
             <embed src='{$src}' quality='high' pluginspage='http://www.macromedia.com/go/getflashplayer' type='application/x-shockwave-flash' width="450" height="150">
             </embed>
          </object>
        </td>
       </tr>
    </table>
  {/if}
  {if $ads.media_type eq 3}
   <table width="100%">
    <tr>
      <td  class="label">{$lang.ad_code}</td>
      <td>
        <textarea class="textCtrl" name="ad_text" cols="65" rows="7">{$ads.ad_code}</textarea>
      </td>
    </tr>
  </table>
 {/if}
 {if $ads.media_type eq 2}
  <table width="100%">
    <tr>
      <td  class="label">{$lang.enter_code}</td>
      <td>
        <textarea class="textCtrl" name="ad_code" cols="65" rows="7">{$ads.ad_code}</textarea>
      </td>
    </tr>
  </table>
{/if}
{/if}
<table width="100%" id="general-table">
  <tr>
    <td  class="label">{$lang.enabled}</td>
    <td>
      <input type="radio" name="enabled" value="1" {if $ads.enabled eq 1} checked="true" {/if} />{$lang.is_enabled}
      <input type="radio" name="enabled" value="0" {if $ads.enabled eq 0} checked="true" {/if} />{$lang.no_enabled}
    </td>
  </tr>
  <tr>
    <td  class="label">{$lang.link_man}</td>
    <td>
      <input type="text" name="link_man" value="{$ads.link_man}" size="35" />
    </td>
  </tr>
  <tr>
    <td  class="label">{$lang.link_email}</td>
    <td>
      <input type="text" name="link_email" value="{$ads.link_email}" size="35" />
    </td>
  </tr>
  <tr>
    <td  class="label">{$lang.link_phone}</td>
    <td>
      <input type="text" name="link_phone" value="{$ads.link_phone}" size="35" />
    </td>
  </tr>
  <tr>
     <td class="label">&nbsp;</td>
     <td>
      <input type="submit" class="button primary submitButton" value="{$lang.button_submit}"  />
      <input type="reset" class="button submitButton"  value="{$lang.button_reset}"  />
    </td>
  </tr>
  <tr>
    <td class="label">&nbsp;</td>
    <td>
      <input type="hidden" name="act" value="{$form_act}" />
      <input type="hidden" name="type" value="{$ads.media_type}" />
      <input type="hidden" name="id" value="{$ads.ad_id}" />
    </td>
  </tr>
</table>
</form>
</div>
{insert_scripts files="skyuc_validator.js"}
<script language="JavaScript">
document.forms['theForm'].elements['ad_name'].focus();
<!--
var MediaList = new Array('0', '1', '2', '3');

function showMedia(AdMediaType)
{
    for (I = 0; I < MediaList.length; I ++)
    {
        if (MediaList[I] == AdMediaType)
        {
            document.getElementById(AdMediaType).style.display = "";
        }
        else
        {
            document.getElementById(MediaList[I]).style.display = "none";
        }
    }
}

/**
 * 检查表单输入的数据
 */
function validate()
{
    validator = new Validator("theForm");
    validator.required("ad_name",     ad_name_empty);
    return validator.passed();
}
//-->

</script>
{include file="pagefooter.tpl"}
