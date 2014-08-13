{include file="pageheader.tpl"}
{insert_scripts files="skyuc_validator.js"}
<form action="flashplay.php" method="post" enctype="multipart/form-data">
<div class="main-div">
<table cellspacing="1" cellpadding="3" width="100%">
<!-- {if $current_flashtpl eq 'dewei'} -->
 <script language="JavaScript">
<!--
$(document).ready(function(){
	$('#ad_name').focus();

	$('#sel1').click(function(){
		$('#imgsrc').show();
		$('#content').show();
	});

	$('#sel2').click(function(){
		$('#imgsrc').hide();
		$('#content').show();
	});

	$('#sel3').click(function(){
		$('#imgsrc').hide();
		$('#content').hide();
	});

});

//-->
</script>
 <tr>
    <td class="label">{$lang.schp_title}：</td>
    <td><input name="ad_name" id="ad_name" type="text" size="40" value="{$rt.title}" /><span class="require-field">*</span></td>
  </tr>
   <tr>
      <td class="label">{$lang.lable_flash_type}</td>
      <td><input name='ad_type' type='radio' id="sel1" value='1' {if $rt.type eq 1 OR $rt.type eq ''}checked{/if} >{$lang.type_ad_img}<input name='ad_type' type='radio' id="sel2" value='2' {if $rt.type eq 2}checked{/if}> {$lang.type_ad_text}<input name='ad_type' type='radio' id="sel3" value='3' {if $rt.type eq 3}checked{/if}> {$lang.type_ad_new}
      </td>
    </tr>
  <tr id="imgsrc" {if $rt.type gt 1}style="display:none;"{/if}>
    <td class="label">{$lang.img_src}<a href="javascript:showNotice('width_height');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a>：</td>
    <td><input type="file" name="img_file_src" value="" id="some_name" size="40" />
    <br /><input name="img_src" type="text" value="{$rt.img_src}" size="40" />
    <br /><span class="notice-span"  id="width_height">{$width_height}</span>
    </td>
  </tr>
  <tr>
    <td class="label">{$lang.lable_url}</td>
    <td><input name="img_url" type="text" value="{if $smarty.get.ad_link}{$smarty.get.ad_link}{else}{$rt.img_url}{/if}" size="40" /></td>
  </tr>

    <tr id="content">
      <td class="label">{$lang.lable_content}</td>
      <td><textarea class="textCtrl" name="content" cols="50" rows="7">{$rt.text}</textarea></td>
    </tr>
<!-- {else} -->
  <tr>
    <td class="label">{$lang.img_src}<a href="javascript:showNotice('width_height');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a>：</td>
    <td><input type="file" name="img_file_src" value="" id="some_name" size="40" />
    <br /><input name="img_src" type="text" value="{$rt.img_src}" size="40" />
    <br /><span class="notice-span"  id="width_height">{$width_height}</span>
    </td>
  </tr>
  <tr>
    <td class="label">{$lang.img_url}：</td>
    <td><input name="img_url" type="text" value="{if $smarty.get.ad_link}{$smarty.get.ad_link}{else}{$rt.img_url}{/if}" size="40" /></td>
  </tr>
   <tr>
    <td class="label">{$lang.schp_imgdesc}：</td>
    <td><input name="content" type="text" value="{$rt.text}" size="40" /></td>
  </tr>
    <!-- {/if} -->
   <tr>
    <td class="label">{$lang.schp_sort}：</td>
    <td><input name="ad_sort" type="text" value="{$rt.sort}" size="4" maxlength="3"/></td>
  </tr>
  <tr align="center">
    <td colspan="2">
      <input type="hidden"  name="id"       value="{$rt.id}" />
      <input type="hidden"  name="step"     value="2" />
      <input type="hidden"  name="act"      value="{$rt.act}" />
      <input type="submit" class="button primary submitButton"  name="Submit"    value="{$lang.button_submit}" />
      <input type="reset" class="button submitButton"    name="Reset"    value="{$lang.button_reset}" />
    </td>
  </tr>
</table>
</div>
</form>

{include file="pagefooter.tpl"}
