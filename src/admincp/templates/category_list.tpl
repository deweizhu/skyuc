{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}

<form method="post" action="" name="listForm">
<!-- start ad position list -->
<div class="list-div" id="listDiv">
{/if}

<table width="100%" cellspacing="1" cellpadding="2" id="list-table">
  <tr>
    <th>{$lang.cat_name}</th>
	<th>{$lang.show_number}</th>
    <th>{$lang.is_show}</th>
    <th>{$lang.nav}</th>
    <th>{$lang.sort_order}</th>
    <th>{$lang.handler}</th>
  </tr>
  {foreach from=$cat_info item=cat}
  <tr align="center" {if $cat.is_leaf neq 1}onclick="rowClicked(this)"{/if} class="{$cat.level}">
    <td align="left" class="first-cell" >
      {if $cat.is_leaf neq 1}
      <img src="images/menu_minus.gif" width="9" height="9" border="0" style="margin-left:{$cat.level}em" />
      {else}
      <img src="images/menu_arrow.gif" width="9" height="9" border="0" style="margin-left:{$cat.level}em" />
      {/if}
      <span><a href="show.php?act=list&cat_id={$cat.cat_id}">{$cat.cat_name}</a></span>
    </td>
	<td width="5%">{$cat.show_num}</td>
   <td width="10%"><img src="images/{if $cat.is_show eq '1'}yes{else}no{/if}.gif" onclick="listTable.toggle(this, 'toggle_is_show', {$cat.cat_id})" /></td>
    <td width="10%"><img src="images/{if $cat.show_in_nav eq '1'}yes{else}no{/if}.gif" onclick="listTable.toggle(this, 'toggle_show_in_nav', {$cat.cat_id})" /></td>
    <td width="10%" align="right"><span onclick="listTable.edit(this, 'edit_sort_order', {$cat.cat_id})">{$cat.sort_order}</span></td>
    <td width="29%" align="right">

	  <a href="show.php?act=add&cat_id={$cat.cat_id}">{$lang.add_show}</a> |
      <a href="category.php?act=move&cat_id={$cat.cat_id}">{$lang.move_show}</a> |
      <a href="category.php?act=edit&amp;cat_id={$cat.cat_id}">{$lang.edit}</a> |
      <a href="javascript:;" onclick="listTable.remove({$cat.cat_id}, '{$lang.drop_confirm}')" title="{$lang.remove}">{$lang.remove}</a>
    </td>
  </tr>
  {/foreach}
</table>

{if $full_page}
</div>
</form>


<script language="JavaScript">
<!--
var imgPlus = new Image();
imgPlus.src = "images/menu_plus.gif";

/**
 * 折叠分类列表
 */
function rowClicked(obj)
{
    var tbl = document.getElementById("list-table");
    var lvl = parseInt(obj.className);
    var fnd = false;

    for (i = 0; i < tbl.rows.length; i++)
    {
        var row = tbl.rows[i];

        if (tbl.rows[i] == obj)
        {
            fnd = true;
        }
        else
        {
            if (fnd == true)
            {
                var cur = parseInt(row.className);
                if (cur > lvl)
                {
                    row.style.display = (row.style.display != 'none') ? 'none' : (Browser.isIE) ? 'block' : 'table-row';
                }
                else
                {
                    fnd = false;
                    break;
                }
            }
        }
    }

    for (i = 0; i < obj.cells[0].childNodes.length; i++)
    {
        var imgObj = obj.cells[0].childNodes[i];
        if (imgObj.tagName == "IMG" && imgObj.src != 'images/menu_arrow.gif')
        {
            imgObj.src = (imgObj.src == imgPlus.src) ? 'images/menu_minus.gif' : imgPlus.src;
        }
    }
}
//-->
</script>


{include file="pagefooter.tpl"}
{/if}