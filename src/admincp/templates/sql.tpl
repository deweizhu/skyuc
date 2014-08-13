{include file="pageheader.tpl"}
<div class="form-div">
  <form name="sqlFrom" method="post" action="sql.php" onsubmit="return validate()">
  <table>
    <tr><th>{$lang.title}</th></tr>
    <tr><td><span style="color: rgb(255, 0, 0);"><strong>{$lang.note}</strong></span></td></tr>
    <tr><td><textarea class="textCtrl" name="sql" rows="6" cols="80">{$sql}</textarea></td></tr>
    <tr><td><input type="hidden" name="act" value="query"><input value="{$lang.query}" type="submit" class="button primary submitButton"   /></td></tr>
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

document.forms['sqlFrom'].elements['sql'].focus();
/**
 * 检查表单输入的数据
 */
function validate()
{
  var frm = document.forms['sqlFrom'];
  var sql = frm.elements['sql'].value;
  var msg ='';

  if (sql.length == 0 )
  {
    msg += sql_not_null + "\n";
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
