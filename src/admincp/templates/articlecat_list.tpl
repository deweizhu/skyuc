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
    <th>{$lang.type}</th>
    <th>{$lang.cat_desc}</th>
    <th>{$lang.sort_order}</th>
    <th>{$lang.show_in_nav}</th>
    <th>{$lang.handler}</th>
  </tr>
  {foreach from=$articlecat item=cat}
  <tr align="center" class="{$cat.level}">
    <td align="left" class="first-cell nowrap" valign="top" >
      {if $cat.is_leaf neq 1}
      <img src="images/menu_minus.gif" width="9" height="9" border="0" style="margin-left:{$cat.level}em" onclick="rowClicked(this)" />
      {else}
      <img src="images/menu_arrow.gif" width="9" height="9" border="0" style="margin-left:{$cat.level}em" />
      {/if}
      <span><a href="article.php?act=list&amp;cat_id={$cat.cat_id}">{$cat.cat_name|escape}</a></span>
    </td>
    <td class="nowrap" valign="top">
      {$cat.type_name|escape}
    </td>
    <td align="left" valign="top">
      {$cat.cat_desc|escape}
    </td>
    <td width="10%" align="center" class="nowrap" valign="top"><span onclick="listTable.edit(this, 'edit_sort_order', {$cat.cat_id})">{$cat.sort_order}</span></td>
    <td width="10%" class="nowrap" valign="top"><img src="images/{if $cat.show_in_nav eq '1'}yes{else}no{/if}.gif" onclick="listTable.toggle(this, 'toggle_show_in_nav', {$cat.cat_id})" /></td>
    <td width="10%" align="left"  class="nowrap" valign="top">
      <a href="articlecat.php?act=edit&amp;id={$cat.cat_id}"><img src="images/icon_edit.gif" border="0" height="16" width="16" /></a>
      {if $cat.cat_type neq 2 and $cat.cat_type neq 3 and $cat.cat_type neq 4}&nbsp;&nbsp;
      <a href="javascript:;" onclick="listTable.remove({$cat.cat_id}, '{$lang.drop_confirm}')" title="{$lang.remove}"><img src="images/icon_drop.gif" border="0" height="16" width="16"></a>
      {/if}
    </td>
  </tr>
  {/foreach}
</table>

{if $full_page}
</div>
</form>

{literal}
<script language="JavaScript">
<!--
var imgPlus = new Image();
imgPlus.src = "images/menu_plus.gif";

/**
 * 折叠分类列表
 */
function rowClicked(obj)
{
  obj = obj.parentNode.parentNode;

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
{/literal}

{include file="pagefooter.tpl"}
{/if}