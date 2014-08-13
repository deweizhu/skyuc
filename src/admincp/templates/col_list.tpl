{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}

<!-- 采集节点列表 -->
<form method="post" action="" name="listForm" >
  <!-- 节点列表开始-->
  <div class="list-div" id="listDiv">
{/if}

<table cellpadding="3" cellspacing="1">
  <tr>
    <th><input onclick='listTable.selectAll(this, "checkboxes")' type="checkbox" />
      <a href="javascript:listTable.sort('nid'); ">{$lang.record_id}</a>{$sort_nid}</th>
    <th><a href="javascript:listTable.sort('gathername'); ">{$lang.gathername}</a>{$sort_gathername}</th>
    <th><a href="javascript:listTable.sort('cat_id'); ">{$lang.cat_id}</a>{$sort_cat_id}</th>
    <th><a href="javascript:listTable.sort('lasttime'); ">{$lang.lasttime}</a>{$sort_lasttime}</th>
	<th><a href="javascript:listTable.sort('savetime'); ">{$lang.savetime}</a>{$sort_savetime}</th>
	<th><a href="javascript:listTable.sort('language'); ">{$lang.language}</a>{$sort_language}</th>
    <th>{$lang.notes}</th>
  <tr>
  {foreach from=$col_list item=col}
  <tr>
    <td><input type="checkbox" name="checkboxes" value="{$col.nid}" />{$col.nid}</td>
    <td class="first-cell">{$col.gathername|escape:html}</td>
    <td>{$col.typename}</td>
    <td align="right">{$col.lasttime}</td>
	<td align="right">{$col.savetime}</td>
	<td align="right">{$col.language}</td>
	<td align="center">{$col.notes}</td>
  </tr>
  {foreachelse}
  <tr><td class="no-records" colspan="10">{$lang.no_records}</td></tr>
  {/foreach}
</table>
<!-- 节点列表结束 -->

<!-- 分页 -->
<table id="page-table" cellspacing="0">
 <tr>
    <td  colspan="2">
	         &nbsp;
			<input type="button" class="button"  value="{$lang.importrule}"   onClick="javascript:GetRule();" />
			<input type="button" class="button"  value="{$lang.exportrule}"   onClick="javascript:ExportRule('');" />
			 |
			<input type="button" class="button primary submitButton"  value="{$lang.collection}"   onClick="javascript:GatherSel('');" />
			<input type="button" class="button"  value="{$lang.exportdown}"   onClick="javascript:ExportDown('');" />
             |
			 <input type="button" class="button"  value="{$lang.editnote}"   onClick="javascript:EditNote('');" />
			 <input type="button" class="button"  value="{$lang.testrule}"   onClick="javascript:TestRule('');" />
          	 |
			 <input type="button" class="button"  value="{$lang.viewdown}"   onClick="javascript:ViewDown('');" />
             |
			 <input type="button" class="button"  value="{$lang.copynote}"   onClick="javascript:CopyNote('');" />
			 <input type="button" class="button"  value="{$lang.clearnote}"  onClick="javascript:ClearNote('');" />
			 <input type="button" class="button"  value="{$lang.delnote}"   onClick="javascript:DelNote('');" />
    </td>
  </tr>
  <tr>
    <td>
    </td>
    <td align="right" nowrap="true">
    {include file="page.tpl"}
    </td>
  </tr>
</table>

{if $full_page}
</div>
</form>
<script language="javascript">
//编辑节点
function EditNote(nid)
{
	if(nid=="") nid = getOneItem();
  if(nid==""){ alert("{$lang.selectnote}"); return;}
	location.href = "?act=edit&nid="+nid;
}

//清空节点
function ClearNote(nid)
{
	if(nid=="") nid = getOneItem();
	if(nid==""){ alert("{$lang.selectnote}"); return;}
	location.href = "col_url.php?act=clear&nid="+nid;
}
//删除节点
function DelNote(nid)
{
	if(nid=="") nid = getOneItem();
	if(nid==""){ alert("{$lang.selectnote}"); return;}
	if(window.confirm('{$lang.confirm}'))
	{ location.href = "col_main.php?act=delete&nid="+nid; }
}
//查看已下载的内容
function ViewDown(nid)
{
	if(nid=="") nid = getOneItem();
	location.href = "col_url.php?act=list&nid="+nid;
}
//测试规则
function TestRule(nid)
{
	if(nid=="") nid = getOneItem();
	if(nid==""){ alert("{$lang.selectnote}"); return;}
	location.href = "col_url.php?act=test_rule&nid="+nid;
}
//导出采集
function ExportDown(nid)
{
	if(nid=="") nid = getOneItem();
	if(nid==""){ alert("{$lang.selectnote}"); return;}
	location.href = "col_main.php?act=export&nid="+nid;;
}
//导入规则
function GetRule()
{
	location.href = "col_main.php?act=importrule";
}
//导出规则
function ExportRule(nid)
{
	if(nid=="") nid = getOneItem();
	if(nid==""){ alert("{$lang.selectnote}"); return;}
	location.href = "col_main.php?act=exportrule&nid="+nid;
}
//采集所选节点
function GatherSel(nid)
{
	if(nid=="") nid = getOneItem();
	if(nid==""){ alert("{$lang.selectnote}"); return;}
	location.href = "col_main.php?act=gather&nid="+nid;
}
//复制所选节点
function CopyNote(nid)
{
	if(nid=="") nid = getOneItem();
	if(nid==""){ alert("{$lang.selectnote}"); return;}
	location.href = "col_main.php?act=copy&nid="+nid;
}

//获得选中其中一个的id
function getOneItem()
{
	var allSel="";
	if(document.listForm.checkboxes.value) return document.listForm.checkboxes.value;
	for(i=0;i<document.listForm.checkboxes.length;i++)
	{
		if(document.listForm.checkboxes[i].checked)
		{
				allSel = document.listForm.checkboxes[i].value;
				break;
		}
	}
	return allSel;
}
</script>
<script type="text/javascript">
  listTable.recordCount = {$record_count};
  listTable.pageCount = {$page_count};

  {foreach from=$filter item=item key=key}
  listTable.filter.{$key} = '{$item}';
  {/foreach}
</script>
{include file="pagefooter.tpl"}
{/if}
