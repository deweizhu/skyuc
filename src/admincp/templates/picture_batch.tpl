{insert_scripts files="../skyuc_utils.js"}
{include file="pageheader.tpl"}
<div class="main-div">
<form action="picture_batch.php" method="post" name="theForm" onsubmit="return start()">
  <table cellspacing="1" cellpadding="3" width="100%">
  <tr>
    <td>{$lang.notes}</td>
  </tr>
  <tr>
    <td ><select class="textCtrl"  name="cat_id" onchange="show_list(this);"><option value="0">{$lang.all_category}</option>{$cat_list}</select>
          <select class="textCtrl"  name="lastday" onchange="show_list(this);"><option value="0">{$lang.all_date}</option>{$date_list}</select>
          <span id="list"><select class="textCtrl"  name="show_id"><option value="0">{$lang.all_show}</option></select></span>
          <input  type="button" class="button"  value=" + " onclick="add_search_show();" />
    </td>
  </tr>
   <tr>
    <td id="show_list">

    </td>
  </tr>

  <tr>
    <td><label for="process_thumb"><input type="checkbox" name="process_thumb" value="1" id="process_thumb" checked="true" />{$lang.thumb}</label></td>
  </tr>
  <tr>
    <td><label for="process_thumb_big"><input type="checkbox" name="process_thumb_big" value="1" id="process_thumb_big" checked="true" />{$lang.thumb_big}</label></td>
  </tr>
  <tr>
    <td>
        <label for="yes_change"><input type="radio" name="change_link" value="1" id="yes_change" />{$lang.yes_change}</label>
        <label for="no_change"><input type="radio" name="change_link" value="0" checked="true" id="no_change" />{$lang.no_change}</label>
    </td>
  </tr>
  <tr>
    <td>
        <label for="silent"><input type="radio" name="silent" value="1" id="silent" checked="checked" />{$lang.silent}</label>
        <label for="no_silent"><input type="radio" name="silent" value="0"  id="no_silent" />{$lang.no_silent}</label>
    </td>
  </tr>
  <tr>
    <td align="center">
      <input type="submit" class="button primary submitButton"  value="{$lang.button_submit}" />
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

/**
* 取得影片数据并生成option
*/
function show_list(obj)
{
    var lastday = obj.form.elements['lastday'].value;
    var cat_id = obj.form.elements['cat_id'].value;

    Ajax.call('picture_batch.php?is_ajax=1&get_show=1', 'lastday=' + lastday + '&cat_id=' + cat_id, make_show_option, 'GET', 'JSON');
}

function make_show_option(result)
{
    var len = result.length;
    var opt = '<select class="textCtrl"  name="show_id"><option value="0">{$lang.all_show}</option>';

    for (var i = 0; i < len; ++i)
    {
       opt += '<option value="' + result[i].show_id + '">' +  result[i].title + '</option>';
    }
    opt += '</select>';
    document.getElementById('list').innerHTML = opt;
}

function add_search_show(obj)
{
    var show_id = document.forms['theForm'].elements['show_id'].value;
    var title = '';
    var len = document.forms['theForm'].elements['show_id'].options.length;
    for (var i = 0; i < len; ++i)
    {
        if (document.forms['theForm'].elements['show_id'].options[i].selected)
        {
            title = document.forms['theForm'].elements['show_id'].options[i].innerHTML;
            break;
        }
    }
    if (show_id == '0' || document.getElementById('show_' + show_id))
    {
        return ;
    }
    var show_div = document.createElement("div");
    show_div.id = 'show_' + show_id;
    show_div.innerHTML = '<input type="hidden" name="multi_show_id[]" value="' + show_id + '">' + title + '&nbsp;&nbsp;<img style="cursor: pointer;" onclick="del_search_show(\'' + 'show_' + show_id + '\');" src="images/no.gif"/>';
    document.getElementById('show_list').appendChild(show_div);
}
function del_search_show(gid)
{
    var boldElm = document.getElementById(gid);
    if (boldElm)
    {
        var removed = document.getElementById(gid).parentNode.removeChild(boldElm);
    }

}
var first_act = 'icon';
var restart = 1;
/**
 * 开始处理数据
 */
function start()
{
    var thumb = document.forms['theForm'].elements['process_thumb'].checked ? 1 : 0;
    var thumb_big = document.forms['theForm'].elements['process_thumb_big'].checked ? 1 : 0;
    var change = document.forms['theForm'].elements['change_link'][0].checked? 1 : 0;
    var silent = document.forms['theForm'].elements['silent'][0].checked? 1 : 0;
    var cat_id = document.forms['theForm'].elements['cat_id'].value;
    var lastday = document.forms['theForm'].elements['lastday'].value;

    var show_id = 0;
    var multi_show = document.forms['theForm'].elements['multi_show_id[]'];
    if (!multi_show)
    {
        show_id = document.forms['theForm'].elements['show_id'].value;;
    }
    else
    {
 		if( multi_show.length > 0)
		{
            show_id = '';
			for(var i = 0; i < multi_show.length; i++)
			{
                show_id += (multi_show.length != i + 1) ?( multi_show[i].value + ',') : multi_show[i].value;
			}
		}
		else
		{
            show_id = multi_show.value
		}

    }

    first_act = 'icon';

    if (thumb || thumb_big )
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
        Ajax.call('picture_batch.php?is_ajax=1&start=1', 'total_' + first_act + '=1&thumb=' + thumb + '&thumb_big=' + thumb_big + '&change=' + change + '&silent=' + silent + '&show_id=' + show_id + '&lastday=' + lastday + '&cat_id=' + cat_id , start_response, 'GET', 'JSON');
    }
    else
    {
        alert(no_action);
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
        Ajax.call('picture_batch.php?is_ajax=1', 'thumb=' + result.thumb + '&thumb_big=' + result.thumb_big + '&change=' + result.change + '&page=' + result.page + '&page_size=' + result.page_size + '&total=' + result.total + '&silent=' + result.silent + '&show_id=' + result.show_id + '&lastday=' + result.lastday + '&cat_id=' + result.cat_id  , start_response, 'GET', 'JSON');
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
{/literal}
//-->
</script>
{include file="pagefooter.tpl"}
