{include file="pageheader.tpl"}
{insert_scripts files="skyuc_utils.js"}

{if $warning}
<ul style="padding:0; margin: 0; list-style-type:none; color: #CC0000;">
  <li style="border: 1px solid #CC0000; background: #FFFFCC; padding: 10px; margin-bottom: 5px;" >{$warning}</li>
</ul>
{/if}

<!-- start form -->
<div class="tab-div">
    <!-- tab bar -->
    <div id="tabbar-div">
      <p>
        <span class="tab-front" id="general-tab">{$lang.tab_general}</span>
	    <span class="tab-back" id="detail-tab">{$lang.tab_detail}</span>
      </p>
    </div>

    <!-- tab body -->
    <div id="tabbody-div">
      <form enctype="multipart/form-data" action="" method="post" name="theForm" onsubmit="return validate();">
	     <input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
        <table width="100%" id="general-table" align="center">
          <tr>
            <td class="label">{$lang.lab_title}</td>
            <td><input type="text"    name="title" value="{$subject.title|escape}" size="30" />{$lang.require_field}
			</td>
          </tr>
          <tr>
            <td class="label">{$lang.link}：</td>
            <td><input type="text"    name="link" value="{$subject.link}" size="50" />
			</td>
          </tr>
		   <tr>
            <td class="label">{$lang.uselink}：<a href="javascript:showNotice('notice_uselink');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a>
			</td>
            <td><input type="radio" name="uselink" value="0" {if $subject.link eq 0}checked="checked"{/if}/>{$lang.uselink_no}
			<input type="radio" name="uselink" value="1" {if $subject.link eq 1}checked="checked"{/if}/>{$lang.uselink_yes}
			<span class="notice-span" id="notice_uselink">{$lang.notice_uselink}</span>
			</td>
          </tr>
          <tr>
            <td class="label">{$lang.lab_poster}</td>
            <td>
              <input type="file" name="poster" size="35" />
              {if $subject.poster}
                 <a href="subject.php?act=show_image&img_url={$subject.poster}" target="_blank"><img src="images/yes.gif" border="0" /></a>
              {else}
                <img src="images/no.gif" />
              {/if}
			    <br /><input type="text" size="40" value="" style="color:#aaa;" onfocus="if (this.value == ''){this.value='http://';this.style.color='#000';}" name="poster_url"/>
            </td>
          </tr>
		  <tr id="auto_thumb_1">
            <td class="label"> {$lang.lab_thumb}</td>
            <td id="auto_thumb_3">
              <input type="file" name="thumb" size="35" />
              {if $subject.thumb}
                <a href="subject.php?act=show_image&img_url={$subject.thumb}" target="_blank"><img src="images/yes.gif" border="0" /></a>
              {else}
                <img src="images/no.gif" />
              {/if}
			   <br /><input type="text" size="40" value="" style="color:#aaa;" onfocus="if (this.value == ''){this.value='http://';this.style.color='#000';}" name="thumb_url"/>

              <br /><label for="auto_thumb"><input type="checkbox" id="auto_thumb" name="auto_thumb" checked="true" value="1" onclick="handleAutoThumb(this.checked)" />{$lang.auto_thumb}</label>
            </td>
          </tr>
		<tr>
			<td class="label">{$lang.intro}：</td>
			<td>
			<textarea class="textCtrl" name="intro" rows="6" cols="40">{$subject.intro}</textarea>
			</td>
		  </tr>
			<tr>
			<td class="label">{$lang.lab_recom}：</td>
			<td><input type="radio" name="recom" value="0" {if $subject.recom eq 0}checked="checked"{/if}>{$lang.recom_no}<input type="radio" name="recom" value="1" {if $subject.recom eq 1}checked="checked"{/if}>{$lang.recom_yes}</td>
			</tr>
 </table>


	<!-- 专题信息 -->
	<table width="100%" id="detail-table" style="display:none">
	<tr>
            <td colspan="2">{$FCKeditor}</td>
          </tr>
	 </table>


        <div class="button-div">
          <input type="hidden" name="id" value="{$subject.id}" />
          <input type="submit" class="button primary submitButton" value="{$lang.button_submit}"  />
          <input type="reset" class="button submitButton"  value="{$lang.button_reset}"  />
        </div>
        <input type="hidden" name="act" value="{$form_act}" />
      </form>
    </div>
</div>
<!-- end form -->
{insert_scripts files="skyuc_validator.js,skyuc_tab.js"}

<script language="JavaScript">
  var showId = '{$subject.id}';

  onload = function()
  {
      if (document.forms['theForm'].elements['auto_thumb'])
      {
          handleAutoThumb(document.forms['theForm'].elements['auto_thumb'].checked);
      }
      document.forms['theForm'].reset();
  }

  function handleAutoThumb(checked)
  {
      document.forms['theForm'].elements['thumb'].disabled = checked;
  }


  function validate()
  {
      var validator = new Validator('theForm');
      validator.required('title', title_not_null);
      return validator.passed();
  }
</script>
{include file="pagefooter.tpl"}
