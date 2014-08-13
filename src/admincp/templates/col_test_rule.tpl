{include file="pageheader.tpl"}
<div class="list-div" id="listDiv">

    	<table cellpadding="3" cellspacing="1">
        <tr>
          <th width="13%" height="24" align="center">{$lang.gathername}：</th>
          <th width="87%" align="left">&nbsp;{$gathername}</th>
        </tr>
        <tr>
          <td height="24" align="center">{$lang.test_list}：</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td height="24" colspan="2" align="center">
 <textarea class="textCtrl" name="r1" rows="15" id="r1" style="width:98%;height:250px">{$test_list}</textarea>
          </td>
        </tr>
        <tr>
          <td height="24" align="center">{$lang.test_rule}：</td>
          <td >&nbsp;</td>
        </tr>
        <tr>
          <td height="24" colspan="2" align="center">
         <textarea class="textCtrl" name="r2" rows="15" id="r2" style="width:98%;height:250px">{$lang.test_url}: {$test_art}</textarea>
		  </td>
        </tr>
      </table>
</div>
{include file="pagefooter.tpl"}
