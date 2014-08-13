{include file="pageheader.tpl"}
<div class="list-div" id="listDiv">

    	<table cellpadding="3" cellspacing="1">
        <tr>
          <th width="13%" height="24" align="center">{$lang.url_title}：</th>
          <th width="87%" align="left">&nbsp;{$url.title}</th>
        </tr>
        <tr>
          <td height="24" align="center">{$lang.sourcepage}：</td>
          <td >{$url.url}</td>
        </tr>
        <tr>
          <td height="24" colspan="2" align="center">
         <textarea class="textCtrl" name="r2" rows="80"  style="width:98%;height:250px">{$url.result}</textarea>
		  </td>
        </tr>
      </table>
</div>
{include file="pagefooter.tpl"}
