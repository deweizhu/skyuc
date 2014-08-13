{include file="pageheader.tpl"}
	<div class="main-div">
      <form enctype="multipart/form-data" action="" method="post" name="theForm" onsubmit="return validate();">
        <table width="90%" id="general-table" align="center">
		    <tr>
            <td class="label">{$lang.lab_title}</td>
            <td><input type="text"    name="title" value="{$netbar.title|escape}"  />
			</td>
          </tr>
          <tr>
            <td class="label">{$lang.lab_username}<a href="javascript:showNotice('noticeUsername');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
            <td><input type="text"    name="username" value="{$netbar.username}"  /><br />
            <span class="notice-span" id="noticeUsername">{$lang.notice_username}</span>
			</td>
          </tr>
		  <tr>
            <td class="label">{$lang.lab_userpass}<a href="javascript:showNotice('noticeUserpass');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
            <td><input type="text"    name="userpass" value="{$netbar.userpass}"  /><br />
            <span class="notice-span" id="noticeUserpass">{$lang.notice_userpass}</span>
			</td>
          </tr>
		   <tr>
            <td class="label">{$lang.lab_sip}<a href="javascript:showNotice('noticeSip');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a>
			</td>
            <td><input type="text"   name="sip" value="{$netbar.sip}"  /><br />
            <span class="notice-span" id="noticeSip">{$lang.notice_sip}</span>
            </td>
          </tr>
		     <tr>
            <td class="label">{$lang.lab_eip}<a href="javascript:showNotice('noticeEip');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a>
			</td>
            <td><input type="text"   name="eip" value="{$netbar.eip}"  /><br />
            <span class="notice-span" id="noticeEip">{$lang.notice_eip}</span>
            </td>
          </tr>
		  {if $form_act neq 'insert'}
		  <tr>
            <td class="label">{$lang.lab_addtime}</td>
            <td>
			{html_select_date field_order="YMD" prefix="add_date"  start_year=+0  time=$netbar.addtime}
            </td>
          </tr>
		   <tr>
            <td class="label">{$lang.lab_endtime}</td>
            <td>
			 {html_select_date field_order="YMD" prefix="end_date"  start_year=+0  end_year=+3 time=$netbar.endtime}
            </td>
          </tr>
		   <tr>
            <td class="label">{$lang.lab_maxuser}<a href="javascript:showNotice('noticeMaxuser');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
            <td><input type="text"   name="maxuser" value="{$netbar.maxuser}"  /><br />
            <span class="notice-span" id="noticeMaxuser">{$lang.notice_maxuser}</span>
            </td>
          </tr>
		  {else}
		   <tr>
            <td class="label">{$lang.lab_endtime}</td>
            <td> {html_select_date field_order="YMD" prefix="end_date" start_year=+0  end_year=+3 time=$today}
            </td>
          </tr>
		   <tr>
            <td class="label">{$lang.lab_maxuser}<a href="javascript:showNotice('noticeMaxuser');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
            <td><input type="text"   name="maxuser" value="0"  /><br />
            <span class="notice-span" id="noticeMaxuser">{$lang.notice_maxuser}</span>
            </td>
          </tr>
		  {/if}

		 <tr>
            <td class="label">{$lang.lab_intro}</td>
            <td>
			<input type="radio" name="is_ok" value="1" {if $netbar.is_ok eq 1}checked="checked"{/if} />{$lang.lab_is_ok}
			<input type="radio" name="is_ok" value="0" {if $netbar.is_ok neq 1}checked="checked"{/if} />
			{$lang.lab_is_no}
		</td>
          </tr>
	  <tr>
        <td class="label">{$lang.lab_content}</td>
        <td ><textarea class="textCtrl" name="content" cols="28" rows="3">{$netbar.content}</textarea></td>
      </tr>
        </table>

        <div class="button-div">
          <input type="hidden"  name="id" value="{$netbar.id}"  />
          <input type="submit" class="button primary submitButton" value="{$lang.button_submit}"  />
          <input type="reset" class="button submitButton"  value="{$lang.button_reset}"  />
        </div>
        <input type="hidden" name="act" value="{$form_act}" />
      </form>

</div>

{insert_scripts files="skyuc_validator.js"}

<script language="JavaScript">

  function validate()
  {
      var validator = new Validator('theForm');
	  validator.required('title', netbar_title_not_null);
      validator.required('username', netbar_name_not_null);
	  validator.required('userpass', netbar_pass_not_null);
      return validator.passed();
  }

</script>
{include file="pagefooter.tpl"}
