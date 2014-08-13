{include file="pageheader.tpl"}

<div class="main-div">
      <form enctype="multipart/form-data" action="" method="post" name="theForm" onsubmit="return validate();">
        <table width="90%" id="general-table" align="center">
		    <tr>
            <td class="label">{$lang.lab_num}</td>
            <td><input type="text"    name="num" value="10"  />
			</td>
          </tr>
		  <tr>
            <td class="label">{$lang.lab_rank_id}</td>
            <td>
              <select class="textCtrl"  name="rank_id">
			  <option value="0">{$lang.select_please}</option>
			  {foreach from=$ranks item=rank}
				<option value="{$rank.id}">{$rank.name}</option>
              {/foreach}
			  </select></td>
		</td>
          </tr>
          <tr>
            <td class="label">{$lang.lab_prefix}<a href="javascript:showNotice('noticePrefix');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
            <td><input type="text"    name="prefix" value="{$cardprefix}"  /><br />
            <span class="notice-span" id="noticePrefix">{$lang.notice_prefix}</span>
			</td>
          </tr>
		    <tr>
            <td class="label">{$lang.lab_maxv}<a href="javascript:showNotice('noticeMaxv');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
            <td><input type="text"   name="maxv" value="16"  /><br />
            <span class="notice-span" id="noticeMaxv">{$lang.notice_maxv}</span>
            </td>
          </tr>
		   <tr>
            <td class="label">{$lang.lab_money}<a href="javascript:showNotice('noticeMoney');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a>
			</td>
            <td><input type="text"   name="money" value="0"  /><br />
            <span class="notice-span" id="noticeMoney">{$lang.notice_money}</span>
            </td>
          </tr>
		  <tr>
            <td class="label">{$lang.lab_cardvalue}<a href="javascript:showNotice('noticeCardvalue');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a>
			</td>
            <td><input type="text"   name="cardvalue" value="0"  /><br />
            <span class="notice-span" id="noticeCardvalue">{$lang.notcie_cardvalue}</span>
            </td>
          </tr>
		   <tr>
            <td class="label">{$lang.lab_endtime}<a href="javascript:showNotice('noticeEndtime');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
            <td> {html_select_date prefix="end_date" field_order="YMD" start_year=+0 end_year=+3  time=$end_date}<br />
            <span class="notice-span" id="noticeEndtime">{$lang.notice_endtime}</span>
            </td>
          </tr>
        </table>

        <div class="button-div">
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
	   if (document.forms['theForm'].elements['rank_id'].value == 0)
      {
          validator.addErrorMsg(rank_id_not_null);
      }
      return validator.passed();
  }

</script>
{include file="pagefooter.tpl"}
