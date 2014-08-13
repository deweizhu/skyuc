{if $full_page}
{include file="pageheader.tpl"}
<div class="form-div">
  <form action="javascript:searchShows()" name="searchForm">
    <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />
    <!-- 分类 -->{$lang.url_title}：
    <!-- 关键字 -->
    {$lang.keyword} <input type="text"   name="keyword"  />
    <input type="submit" class="button primary submitButton" value="{$lang.button_search}"  />

	<input type="button" class="button"   value="{$lang.clearall}"  style="width:100px;margin-left:350px;" onClick="location.href='col_url.php?act=clearall';" />
	<input type="button" class="button"   value="{$lang.delete_trash}"  onClick="location.href='col_url.php?act=delete_trash';" />
  </form>
</div>


<script language="JavaScript">
    function searchShows()
    {
        listTable.filter['keyword'] = Utils.trim(document.forms['searchForm'].elements['keyword'].value);
        listTable.filter['page'] = 1;
        listTable.loadList();
    }
</script>

{/if}
{insert_scripts files="../skyuc_global.js,../skyuc_utils.js,skyuc_listtable.js,skyuc_common.js"}
<script language="JavaScript">
<!--
// 这里把JS用到的所有语言都赋值到这里
{foreach from=$lang.js_languages key=key item=item}
var {$key} = "{$item}";
{/foreach}
//-->
</script>
<link href="styles/general.css" rel="stylesheet" type="text/css" />
<link href="styles/main.css" rel="stylesheet" type="text/css" />
<!-- 采集种子列表 -->
  <!-- 列表开始-->
<div class="list-div" id="listDiv">
<form method="post" action="" name="listForm" >
<table cellpadding="3" cellspacing="1">
  <tr>
    <th><input onclick='listTable.selectAll(this, "checkboxes")' type="checkbox" />
      <a href="javascript:listTable.sort('aid'); ">{$lang.record_id}</a>{$sort_aid}</th>
    <th><a href="javascript:listTable.sort('title'); ">{$lang.url_title}</a>{$sort_title}</th>
    <th><a href="javascript:listTable.sort('gathername'); ">{$lang.gathername}</a>{$sort_gathername}</th>
    <th><a href="javascript:listTable.sort('dtime'); ">{$lang.dtime}</a>{$sort_dtime}</th>
	<th><a href="javascript:listTable.sort('isdown'); ">{$lang.isdown}</a>{$sort_isdown}</th>
	<th><a href="javascript:listTable.sort('isexport'); ">{$lang.isexport}</a>{$sort_isexport}</th>
    <th>{$lang.sourcepage}</th>
  <tr>
  {foreach from=$col_list item=col}
  <tr>
    <td><input type="checkbox" name="checkboxes" value="{$col.aid}" />{$col.aid}</td>
    <td class="first-cell"><A HREF="col_url.php?act=view&aid={$col.aid}">{$col.title|escape:html}</A></td>
    <td>{$col.gathername}</td>
    <td align="right">{$col.dtime}</td>
	<td align="right">{$col.isdown}</td>
	<td align="right">{$col.isexport}</td>
	<td align="center"><a href='{$col.url}' target='_blank'>{$lang.sourceurl}</a></td>
  </tr>
  {foreachelse}
  <tr><td class="no-records" colspan="10">{$lang.no_records}</td></tr>
  {/foreach}
</table>
<!-- 列表结束 -->

<!-- 分页 -->
<table id="page-table" cellspacing="0">
  <tr>
    <td><input type="button" class="button"  name="b7" value="{$lang.select_all}"  style="width:40" onClick="ReSel();">&nbsp;&nbsp;
	  <input type="button" class="button"  name="b12" value="{$lang.delete_url}"  style="width:80px" onClick="DelSel();" />
	  <input type="button" class="button"  name="b13" value="{$lang.clear_content}"  style="width:80px" onClick="ClearCt();" />
	  <input type="button" class="button"  name="b14" value="{$lang.delete_url_history}"  style="width:130px" onClick="DelSel2();" />

    </td>
    <td align="right" nowrap="true">
    {include file="page.tpl"}
    </td>
  </tr>
</table>
</div>
</form>
<script type="text/javascript">
  listTable.recordCount = {$record_count};
  listTable.pageCount = {$page_count};

  {foreach from=$filter item=item key=key}
  listTable.filter.{$key} = '{$item}';
  {/foreach}
</script>
<script language="javascript">
//获得选中文件的文件名
function getCheckboxItem()
{
	var allSel="";
	if(document.listForm.checkboxes.value) return document.listForm.checkboxes.value;
	for(i=0;i<document.listForm.checkboxes.length;i++)
	{
		if(document.listForm.checkboxes[i].checked)
		{
			if(allSel=="")
				allSel=document.listForm.checkboxes[i].value;
			else
				allSel=allSel+","+document.listForm.checkboxes[i].value;
		}
	}
	return allSel;
}
function ReSel()
{
	for(i=0;i<document.listForm.checkboxes.length;i++)
	{
		if(document.listForm.checkboxes[i].checked) document.listForm.checkboxes[i].checked = false;
		else document.listForm.checkboxes[i].checked = true;
	}
}
function DelSel()
{
	var nid = getCheckboxItem();
	location.href = "col_url.php?act=clear&ids="+nid;
}
function DelSel2()
{
	var nid = getCheckboxItem();
	location.href = "col_url.php?act=clear&clshash=true&ids="+nid;
}
function ClearCt()
{
	var nid = getCheckboxItem();
	location.href = "col_url.php?act=clearct&ids="+nid;
}
</script>
{if $full_page}
{include file="pagefooter.tpl"}
{/if}
