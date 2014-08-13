{include file="pageheader.tpl"}
{insert_scripts files="skyuc_selectdb.js"}
<div class="list-div" id="listDiv">

  <form name="sqlFrom" method="post" action="sql.php" onsubmit="return validate()">
	<table cellspacing='1' cellpadding='3' id='list-table'>
   <tr><th colspan="2">{$lang.title_replace}</th></tr>
    <tr>
      <td>{$lang.replace_field}</td>
      <td>{$lang.replace_tables}
		<select class="textCtrl"  name="selTables" id="selTables">
		<option value=''>{$lang.select_please}</option>
		{$tablopt}
		</select>&nbsp;&nbsp;
		{$lang.replace_fields}
		<select class="textCtrl"  name="selFields" id="selFields">
		</select>
	</td>
  </tr>
    <tr>
      <td>{$lang.replace_mode}</td>
<td>
<input name='replace_mode' type='radio' id="sel1" value='1' checked  {if $replace_mode eq 1 } checked {/if} >{$lang.replace_string}
<input name='replace_mode' type='radio' id="sel2" value='2'  {if $replace_mode eq 2 } checked {/if} > {$lang.append_head}
<input name='replace_mode' type='radio' id="sel3" value='3' {if $replace_mode eq 3 } checked {/if}> {$lang.append_end}
</td>
</tr>
 <tr id='tab1'>
      <td>{$lang.replace_text}</td>
<td><textarea class="textCtrl" name="search" cols="50" rows="3"></textarea></td>
</tr>
    <tr id='tab2'>
      <td>{$lang.replaced_by}</td>
<td><textarea class="textCtrl" name="replace" cols="50" rows="3"></textarea></td>
</tr>
    <tr id='tab3' style="display:none">
      <td>{$lang.append_string}</td>
<td><textarea class="textCtrl" name="addstr" cols="50" rows="3"></textarea></td>
</tr>
    <tr>
      <td>{$lang.replace_where}</td>
<td><textarea class="textCtrl" name="condition" cols="50" rows="3"></textarea></td>
</tr>
    <tr>
      <td> </td>
      <td><input type= "hidden" name="act" value = "run_replace">
        <input type="submit" class="button primary submitButton"  value="{$lang.button_submit}"></td>
</tr>
</table>

  </form>
</div>

<!-- start users list -->
<div class="list-div" id="listDiv">
  {if $type eq 0}
  <!-- 出错提示-->
  <span style="color: rgb(255, 0, 0);"><strong>{$lang.error}:</strong></span><br />
  {$error}
  {/if}
  {if $type eq 1}
  <!-- 执行成功-->
  <center><h3>{$lang.succeed}</h3></center>
  {/if}
  {if $type eq 2}
  <!--有返回值-->
    {$result}
  {/if}
</div>
<script language="JavaScript">
<!--
region.isAdmin = true;

$(document).ready(function(){
	//$('#selTables').focus();
	$('#selTables').change(function(){
		region.changed(this, 'selFields');
	});
	$('#sel1').click(function(){
		$('#tab1').show();
		$('#tab2').show();
		$('#tab3').hide();
	});

	$('#sel2').click(function(){
		$('#tab1').hide();
		$('#tab2').hide();
		$('#tab3').show();
	});

	$('#sel3').click(function(){
		$('#tab1').hide();
		$('#tab2').hide();
		$('#tab3').show();
	});

});
/**
 * 检查表单输入的数据
 */
function validate()
{
  var frm = document.forms['sqlFrom'];
  var sql = frm.elements['selTables'].value;
  var msg ='';

  if (sql.length == 0 )
  {
    msg += table_not_null + "\n";
  }

  if (msg.length > 0)
  {
    alert (msg);
    return false;
  }

  return true;
}
//-->

</script>
{include file="pagefooter.tpl"}
