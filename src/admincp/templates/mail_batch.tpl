{insert_scripts files="../skyuc_utils.js,skyuc_validator.js"}
{include file="pageheader.tpl"}
<div class="main-div">
<form action="mail_batch.php" method="post" name="theForm" onsubmit="return start()">
  <table cellspacing="1" cellpadding="3" width="90%">
  <tr>
	<td class="label">{$lang.addressee}:</td>
    <td><select class="textCtrl"  name="user_rank">
			  <option value="0">{$lang.all_rank}</option>
			  {foreach from=$ranks item=rank}
				<option value="{$rank.id}" {if $user.user_rank eq $rank.id} selected="selected" {/if}>{$rank.name}</option>
              {/foreach}
		</select>
    </td>
  </tr>
  <tr>
    <td class="label">{$lang.mail_subject}:</td>
    <td><input type="text" name="subject" id="subject" style="width: 300px" /></td>
  </tr>
   <tr>
   <td class="label">{$lang.mail_content}:<a href="javascript:showNotice('noticecontent');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
    <td><textarea class="textCtrl" id="content" name='content' style='width:60%;height:350px;word-wrap: break-word;word-break:break-all;' ></textarea>
	<br /><span class="notice-span" id="noticecontent">{$lang.notice_content}</span></td>
  </tr>
  <tr>
  <td></td>
     <td> <input type="submit" class="button primary submitButton"  value="{$lang.button_submit}" />
    </td>
  </tr>
  </table>
</form>
</div>

<div class="list-div" id="listDiv">
  <table cellspacing='1' cellpadding='3' id='listTable'>
    <tr>
      <th>{$lang.page}</th>
      <th>{$lang.total}</th>
      <th>{$lang.time}</th>
    </tr>
  </table>
</div>

<div style="display:none;border: 1px solid rgb(204, 0, 0);margin-top:10px; padding: 4px; background-color: rgb(255, 255, 206); color: rgb(206, 0, 0);" id="errorMsg" ></div>

<script type="Text/Javascript" language="JavaScript">
<!--
var first_act = 'icon';
var restart = 1;
/**
 * 开始处理数据
 */
function start()
{
	var user_rank = document.forms['theForm'].elements['user_rank'].value;
    var subject = document.forms['theForm'].elements['subject'].value;
	var content = document.forms['theForm'].elements['content'].value;

    first_act = 'icon';

    if (subject && content)
    {
        if (restart)
        {
            var tbl = document.getElementById("listTable");
            for (i = tbl.rows.length - 1; i > 0; i--)
            {
              tbl.deleteRow(i);
            }
            restart = 0;
        }
        var elem = document.getElementById('errorMsg');
        elem.style.display = 'none';
        elem.innerHTML = '';
        Ajax.call('mail_batch.php?is_ajax=1&start=1', 'total_' + first_act + '=1&user_rank=' + user_rank + '&subject=' + subject +  '&content=' + content , start_response, 'GET', 'JSON');
    }
    else
    {
        alert(no_subject);
    }
    return false;
}

/**
 * 处理反馈信息
 * @param: result
 * @return
 */
function start_response(result)
{
    //没有执行错误
    if (result.error == 0)
    {
	 if (result.done == 0)
      {
        document.getElementById('time_1').id = first_act + 'done';
        first_act = 'icon';
        restart = 1;
        /* 结束时，删除多余的最后一行 */
        var tbl = document.getElementById("listTable"); //获取表格对象
        tbl.deleteRow(tbl.rows.length - 1);
      }
      else
      {
        var cell;
        var tbl = document.getElementById("listTable"); //获取表格对象

        if (result.done == 1)
        {

          /* 产生一个标题行 */
          var row = tbl.insertRow(-1);

          cell = row.insertCell(0);
          cell.className = 'first-cell';
          cell.colSpan = '3';
          cell.innerHTML = result.title ;
        }
        else
        {
          document.getElementById(result.row.pre_id).innerHTML = result.row.pre_time; //更新上一行执行时间
        }

        //创建新任务行
        var row = tbl.insertRow(-1);
        cell = row.insertCell(0);
        cell.innerHTML = result.row.new_page ;
        cell = row.insertCell(1);
        cell.innerHTML = result.row.new_total ;
        cell = row.insertCell(2);
        cell.id = result.row.cur_id;
        cell.innerHTML = result.row.new_time ;

        //提交任务
        Ajax.call('mail_batch.php?is_ajax=1', 'user_rank=' + result.user_rank + '&subject=' + result.subject + '&page=' + result.page + '&page_size=' + result.page_size + '&total=' + result.total + '&content=' + result.content , start_response, 'GET', 'JSON');
      }

      if (result.silent && result.content.length > 0)
      {
        var elem = document.getElementById('errorMsg');
        elem.style.display = '';
        elem.innerHTML += result.content;
      }

    }

    if (result.message.length > 0)
    {
      //有信息则输出出错信息
      alert(result.message);
    }
}
//-->
</script>
{include file="pagefooter.tpl"}
