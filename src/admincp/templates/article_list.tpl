{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}
<div class="form-div">
  <form action="javascript:searchArticle()" name="searchForm">
    <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />
    <select class="textCtrl"  name="cat_id" >
      <option value="0">{$lang.all_cat}</option>
        {$cat_select}
    </select>
    {$lang.title} <input type="text"  name="keyword" id="keyword" />
    <input type="submit" class="button primary submitButton" value="{$lang.button_search}"  />
  </form>
</div>

<form method="POST" action="article.php?act=batch_remove" name="listForm">
<!-- start cat list -->
<div class="list-div" id="listDiv">
{/if}

<table cellspacing='1' cellpadding='3' id='list-table'>
  <tr>
    <th><input onclick='listTable.selectAll(this, "checkboxes")' type="checkbox">
      <a href="javascript:listTable.sort('article_id'); ">{$lang.article_id}</a>{$sort_article_id}</th>
    <th><a href="javascript:listTable.sort('title'); ">{$lang.title}</a>{$sort_title}</th>
    <th><a href="javascript:listTable.sort('cat_id'); ">{$lang.cat}</a>{$sort_cat_id}</th>
    <th><a href="javascript:listTable.sort('article_type'); ">{$lang.article_type}</a>{$sort_article_type}</th>
    <th><a href="javascript:listTable.sort('is_open'); ">{$lang.is_open}</a>{$sort_is_open}</th>
    <th><a href="javascript:listTable.sort('add_time'); ">{$lang.add_time}</a>{$sort_add_time}</th>
    <th>{$lang.handler}</th>
  </tr>
  {foreach from=$article_list item=list}
  <tr>
    <td><span><input name="checkboxes[]" type="checkbox" value="{$list.article_id}" />{$list.article_id}</span></td>
    <td class="first-cell">
    <span onclick="javascript:listTable.edit(this, 'edit_title', {$list.article_id})">{$list.title|escape:html}</span></td>
    <td align="left"><span><!-- {if $list.cat_id > 0} -->{$list.cat_name|escape:html}<!-- {else} -->{$lang.reserve}<!-- {/if} --></span></td>
    <td align="center"><span>{if $list.article_type eq 0}{$lang.common}{else}{$lang.top}{/if}</span></td>
    <td align="center">{if $list.cat_id > 0}<span>
    <img src="images/{if $list.is_open eq 1}yes{else}no{/if}.gif" onclick="listTable.toggle(this, 'toggle_show', {$list.article_id})" /></span>{else}<img src="images/yes.gif" alt="yes" />{/if}</td>
    <td align="center"><span>{$list.date}</span></td>
    <td align="center" nowrap="true"><span>
      <a href="../article.php?id={$list.article_id}" target="_blank" title="{$lang.view}"><img src="images/icon_view.gif" border="0" height="16" width="16" /></a>&nbsp;
      <a href="article.php?act=edit&id={$list.article_id}" title="{$lang.edit}"><img src="images/icon_edit.gif" border="0" height="16" width="16" /></a>&nbsp;
     <!-- {if $list.cat_id > 0} --><a href="javascript:;" onclick="listTable.remove({$list.article_id}, '{$lang.drop_confirm}')" title="{$lang.remove}"><img src="images/icon_drop.gif" border="0" height="16" width="16"></a><!-- {/if} --></span>
    </td>
   </tr>
   {foreachelse}
    <tr><td class="no-records" colspan="10">{$lang.no_article}</td></tr>
  {/foreach}
  <tr>
    <td colspan="2"><input type="submit" class="button primary submitButton"  id="btnSubmit" value="{$lang.button_remove}" disabled="true" /></td>
    <td align="right" nowrap="true" colspan="8">{include file="page.tpl"}</td>
  </tr>
</table>

{if $full_page}
</div>
<!-- end cat list -->
<script type="text/javascript" language="JavaScript">
  listTable.recordCount = {$record_count};
  listTable.pageCount = {$page_count};

  {foreach from=$filter item=item key=key}
  listTable.filter.{$key} = '{$item}';
  {/foreach}

 /* 搜索文章 */
 function searchArticle()
 {
    listTable.filter.keyword = Utils.trim(document.forms['searchForm'].elements['keyword'].value);
    listTable.filter.cat_id = parseInt(document.forms['searchForm'].elements['cat_id'].value);
    listTable.filter.page = 1;
    listTable.loadList();
 }
</script>
{include file="pagefooter.tpl"}
{/if}
