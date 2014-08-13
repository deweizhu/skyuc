{include file="pageheader.tpl"}
{if $warning}
<ul style="padding:0; margin: 0; list-style-type:none; color: #CC0000;">
  <li style="border: 1px solid #CC0000; background: #FFFFCC; padding: 10px; margin-bottom: 5px;" >{$warning}</li>
</ul>
{/if}
<form  name="theForm" method="post"  action="database.php" onsubmit="return validate()">
<!-- start  list -->
<div class="list-div" id="listDiv">

<table cellspacing='1' cellpadding='3' >
  <tr>
    <th colspan="2">{$lang.backup_type}</th>
  </tr>
  <tr>
    <td><input type="radio" name="type" value="full"  onclick="findobj('showtables').style.display='none'">{$lang.full_backup}</td>
    <td>{$lang.full_backup_note}</td>
  </tr>
  <tr>
    <td><input type="radio" name="type" value="stand"  checked="checked" onclick="findobj('showtables').style.display='none'">{$lang.stand_backup}</td>
    <td>{$lang.stand_backup_note}</td>
  </tr>
  <tr>
    <td><input type="radio" name="type" value="min"  onclick="findobj('showtables').style.display='none'">{$lang.min_backup}</td>
    <td>{$lang.min_backup_note}</td>
  </tr>
  <tr>
    <td><input type="radio" name="type" value="custom"  onclick="findobj('showtables').style.display=''">{$lang.custom_backup}</td>
    <td>{$lang.custom_backup_note}</td>
  </tr>
  <tbody id="showtables" style="display:none">
  <tr>
    <td colspan="2">
      <table>
        <tr>
          <td colspan="4"><input name="chkall" onclick="checkall(this.form, 'customtables[]')" type="checkbox"><b>{$lang.check_all}</b></td>
        </tr>
        <tr>
        {foreach from=$tables item=table name=table_name}
          {if $smarty.foreach.table_name.iteration > 1 and ($smarty.foreach.table_name.iteration-1) % 4 eq 0}
          </tr><tr>
          {/if}
          <td><input name="customtables[]" value="{$table}"  type="checkbox">{$table}</td>
        {/foreach}
        </tr>
      </table>
    </td>
  </tr>
  </tbody>
</table>

<table cellspacing='1' cellpadding='3' >
  <tr>
    <th colspan="2">{$lang.option}</th>
  </tr>
   <tr>
    <td>{$lang.ext_insert}</td>
    <td><input type="radio" name="ext_insert"  value='1'>{$lang.yes}&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="ext_insert"  value='0' checked="checked">{$lang.no}</td>
  </tr>
  <tr>
    <td>{$lang.db_export_usehex}</td>
    <td><input type="radio" name="usehex"  value='1' checked="checked">{$lang.yes}&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="usehex"  value='0'>{$lang.no}</td>
  </tr>
  <tr>
    <td>{$lang.db_export_usezip}</td>
    <td><input  type="radio" name="usezip" value="1"> {$lang.db_export_zip_1} <input  type="radio" name="usezip" value="2"> {$lang.db_export_zip_2} <input  type="radio" name="usezip" value="0" checked> {$lang.db_export_zip_3} </td>
  </tr>
  <tr>
    <td>{$lang.db_export_multivol}</td>
    <td><input type="text" name="vol_size" value="{$vol_size}"></td>
  </tr>
  <tr>
    <td>{$lang.db_export_filename}</td>
    <td><input type="text" name="sql_file_name" value="{$sql_name}">.sql</td>
  </tr>
</table>
<input type="hidden" name="act" value="dumpsql">
<center><input type="submit" class="button primary submitButton" value="{$lang.start_backup}" /></center>
</div>
<!-- end  list -->
</form>

{insert_scripts files="skyuc_validator.js"}

<script language="JavaScript">
<!--
/**
 * 检查表单输入的数据
 */
function validate()
{
  validator = new Validator("theForm");
  validator.required("sql_file_name", sql_name_not_null);
  validator.required("vol_size", vol_size_not_null);
  return validator.passed();
}


function findobj(str)
{
    return  document.getElementById(str);
}

function checkall(frm, chk)
{
    for (i = 0; i < frm.elements.length; i++)
    {
        if (frm.elements[i].name == chk)
        {
            frm.elements[i].checked = frm.elements['chkall'].checked;
        }
    }
}

function radioClicked(n)
{
    if (n > 0)
    {
        document.forms['theForm'].elements["vol_size"].disabled = false;
        var str = document.forms['theForm'].elements["sql_name"].value ;
        document.forms['theForm'].elements["sql_name"].value = str.slice(0, -4) + '.zip' ;
    }
    else
    {
        document.forms['theForm'].elements["vol_size"].disabled = true;
        var str = document.forms['theForm'].elements["sql_name"].value ;
        document.forms['theForm'].elements["sql_name"].value = str.slice(0, -4) + '.sql' ;
    }
}
//-->
</script>

{include file="pagefooter.tpl"}
