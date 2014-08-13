{include file="pageheader.tpl"}
{insert_scripts files="../js/utils.js,selectzone.js"}
<!-- start  form -->
<div class="tab-div">
    <!-- tab bar -->
    <div id="tabbar-div">
      <p>
        <span class="tab-front" id="general-tab">{$lang.tab_general}</span>
      </p>
    </div>

    <!-- tab body -->
    <div id="tabbody-div">
      <form enctype="multipart/form-data" action="" method="post" name="theForm" onsubmit="return validate();">
        <table width="90%" id="general-table" align="center">
		   <tr>
            <td class="label">{$lang.lab_sip}<a href="javascript:showNotice('noticeSip');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a>
			</td>
            <td><input type="text"   name="sip" value="{$ipaccess.sip}" size="20" /><br />
            <span class="notice-span" id="noticeSip">{$lang.notice_sip}</span>
            </td>
          </tr>
		     <tr>
            <td class="label">{$lang.lab_eip}<a href="javascript:showNotice('noticeEip');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a>
			</td>
            <td><input type="text"   name="eip" value="{$ipaccess.eip}" size="20" /><br />
            <span class="notice-span" id="noticeEip">{$lang.notice_eip}</span>
            </td>
          </tr>
	  <tr>
        <td class="label">{$lang.lab_content}</td>
        <td ><textarea class="textCtrl" name="content" cols="28" rows="3">{$ipaccess.content}</textarea></td>
      </tr>
        </table>

        <div class="button-div">
          <input type="hidden"  name="id" value="{$ipaccess.id}"  />
          <input type="submit" class="button primary submitButton" value="{$lang.button_submit}"  />
          <input type="reset" class="button submitButton"  value="{$lang.button_reset}"  />
        </div>
        <input type="hidden" name="act" value="{$form_act}" />
      </form>
    </div>
</div>
<!-- end show form -->
{insert_scripts files="validator.js,tab.js"}

<script language="JavaScript">

  function validate()
  {
      var validator = new Validator('theForm');
	  validator.required('title', ipaccess_title_not_null);
      validator.required('username', ipaccess_name_not_null);
	  validator.required('userpass', ipaccess_pass_not_null);
      return validator.passed();
  }

</script>
{include file="pagefooter.tpl"}
