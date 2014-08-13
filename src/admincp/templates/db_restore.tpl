{include file="pageheader.tpl"}
{if $warning}
<ul style="padding:0; margin: 0; list-style-type:none; color: #CC0000;">
  <li style="border: 1px solid #CC0000; background: #FFFFCC; padding: 10px; margin-bottom: 5px;" >{$warning}</li>
</ul>
{/if}
<!-- start list -->
<div class="list-div" id="listDiv">
  <form method="post" action="database.php" enctype="multipart/form-data">
  <table cellspacing='1' cellpadding='3'>
  <tr>
    <th colspan="2">{$lang.restore}</th>
  </tr>
  <tr>
    <td>{$lang.sqlfile}</td>
    <td><input type="file" name="sqlfile" size="50"></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><input type="hidden" name = "act" value = "upload_sql"><input type="submit" class="button primary" value={$lang.upload_and_exe} ></td>
  </tr>
  </table>
  </form>

</div>
<br />
<br />
<div class="list-div" id="listDiv">
<form action="database.php" name="file_list" method="POST" onsubmit="return confirm('{$lang.confirm_remove}');" >
<table>
  <tr>
    <th colspan=7>{$lang.server_sql}</th>
  </tr>
  <tr>
    <th width=48 align="right"><input type="checkbox" name="chkall" onclick="checkall(this.form, 'file[]')">{$lang.remove}</th>
    <th>{$lang.name}</th>
    <th>{$lang.ver}</th>
    <th>{$lang.add_time}</th>
    <th>{$lang.file_size}</th>
    <th>{$lang.vol}</th>
    <th>{$lang.handler}</th>
  </tr>
  {foreach from=$list item=item}
  <tr {if $item.mark eq 2}style="display:none"{/if}>
   <td><input type="checkbox" name="file[]" value="{$item.name}" /></td>
   <td>{if $item.mark eq 1}<img src="images/menu_plus.gif" onclick="rowClicked(this)">{/if}{if $item.mark eq 2}<img src="images/menu_arrow.gif">{/if}<a href="../data/sqldata/{$item.name}">{$item.name}</a></td>
   <td>{$item.ver}</td>
   <td>{$item.add_time}</td>
   <td>{$item.file_size}</td>
   <td>vol:{$item.vol}</td>
   <td align="center">{if $item.mark eq 3}<a href="database.php?act=importzip&file_name={$item.name}">[{$lang.db_import_unzip}]</a>{elseif $item.mark neq 2}<a href="database.php?act=import&file_name={$item.name}">[{$lang.import}]</a>{else}&nbsp;{/if}</td>
  </tr>
  {/foreach}
  <tr>
    <td colspan=7 align="center"><input type="hidden" name="act" value="remove"><input type="submit" class="button primary submitButton" value="{$lang.submit_remove}"  />
  </tr>
</table>
</from>
</div>


{insert_scripts files="skyuc_validator.js"}

<script language="JavaScript">
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

function rowClicked(obj)
{
  var row = obj.parentNode.parentNode;
  var tbl = row.parentNode.parentNode;
  var test = false;
  var img = '';

  if (obj.src.substr(obj.src.lastIndexOf('/') + 1) == "menu_minus.gif")
  obj.src = "images/menu_plus.gif";
  else
  obj.src = "images/menu_minus.gif";



  for (i = 0; i < tbl.rows.length; i++)
  {
    var cell = tbl.rows[i].cells[1];

    if (cell && cell.childNodes[0].src)
    {
      img = cell.childNodes[0].src.substr(cell.childNodes[0].src.lastIndexOf('/') + 1);
    }
    else
    {
      img = '';
    }

    if (test && img)
    {
      if (img == "menu_arrow.gif")
      {
        tbl.rows[i].style.display = tbl.rows[i].style.display != 'none' ? 'none' : (Browser.isIE) ? 'block' : 'table-row';
      }
      else
      {
        test=false;
      }
    }

    if (tbl.rows[i] == row)
    {
      test = true;
    }
  }
}

//-->
</script>

{include file="pagefooter.tpl"}
