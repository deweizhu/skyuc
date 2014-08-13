{if $full_page}
{include file="pageheader.tpl"}
{insert_scripts files="skyuc_listtable.js"}
<!-- 搜索 -->
<div class="form-div">
  <form action="javascript:searchList()" name="searchForm">
    <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />
    <!-- 过滤器 -->
		<select class="textCtrl"  name="intro_type">
			  <option value="0">{$lang.select_please}</option>
			  {foreach from=$ranks item=rank}
				<option value="{$rank.id}">{$rank.name}</option>
              {/foreach}
	  </select>
	 <!-- 关键字 -->
    {$lang.keyword} <input type="text"   name="keyword" size="15" />
    <input type="submit" class="button primary submitButton" value="{$lang.button_search}"  />
	&nbsp;&nbsp;
	 <input type="button" class="button"  value="{$lang.export}"  onclick="javascript:doexport();" />
  </form>
</div>


<script language="JavaScript">
    function searchList()
    {
		listTable.filter['intro_type'] = document.forms['searchForm'].elements['intro_type'].value;
		listTable.filter['keyword'] = Utils.trim(document.forms['searchForm'].elements['keyword'].value);
        listTable.filter['page'] = 1;
        listTable.loadList();
    }
	function doexport()
	{
		var rid = document.forms['searchForm'].elements['intro_type'].value;
		window.location.href='?act=doexport&rid='+rid;
	}
</script>

<!-- 列表 -->
<form method="post" action="" name="listForm" onsubmit="return confirmSubmit(this)">
  <!-- start card list -->
  <div class="list-div" id="listDiv">
{/if}

<table cellpadding="3" cellspacing="1">
  <tr>
    <th><input onclick='listTable.selectAll(this, "checkboxes")' type="checkbox" />
      <a href="javascript:listTable.sort('id'); ">{$lang.record_id}</a>{$sort_id}</th>
    <th><a href="javascript:listTable.sort('cardid'); ">{$lang.cardid}</a>{$sort_cardid}</th>
    <th><a href="javascript:listTable.sort('cardpass'); ">{$lang.cardpass}</a>{$sort_cardpass}</th>
    <th><a href="javascript:listTable.sort('rank_id'); ">{$lang.rank_id}</a>{$sort_rank_id}</th>
	<th><a href="javascript:listTable.sort('cardvalue'); ">{$lang.cardvalue}</a>{$sort_cardvalue}</th>
	<th><a href="javascript:listTable.sort('money'); ">{$lang.money}</a>{$sort_money}</th>
	<th><a href="javascript:listTable.sort('addtime'); ">{$lang.issue}</a>{$sort_addtime}</th>
	<th><a href="javascript:listTable.sort('endtime'); ">{$lang.endtime}</a>{$sort_endtime}</th>
    <th>{$lang.handler}</th>
  <tr>
  {foreach from=$card_list item=card}
  <tr>
    <td align="center"><input type="checkbox" name="checkboxes[]" value="{$card.id}" />{$card.id}</td>
    <td class="first-cell"><span onclick="listTable.edit(this, 'edit_cardid', {$card.id})">{$card.cardid}</span></td>
    <td align="right"><span onclick="listTable.edit(this, 'edit_cardpass', {$card.id})">{$card.cardpass}</span></td>
    <td align="center">{$card.rank_name}</td>
	<td align="right"><span onclick="listTable.edit(this, 'edit_cardvalue', {$card.id})">{$card.cardvalue}</span></td>
	<td align="right"><span onclick="listTable.edit(this, 'edit_money', {$card.id})">{$card.money}</span></td>
	<td align="right"><span>{$card.addtime}</span></td>
	<td align="right"><span onclick="listTable.edit(this, 'edit_endtime', {$card.id})">{$card.endtime}</span></td>
    <td align="center">
      <a href="javascript:;" onclick="listTable.remove({$card.id}, '{$lang.drop_confirm}', 'drop_card')">{$lang.drop}</a>
    </td>
  </tr>
  {foreachelse}
  <tr><td class="no-records" colspan="10">{$lang.no_records}</td></tr>
  {/foreach}
</table>
<!-- end card list -->

<!-- 分页 -->
<table id="page-table" cellspacing="0">
  <tr>
    <td> <input type="hidden" name="act" value="batch" />
      <select class="textCtrl"  name="type" id="selAction" onchange="changeAction()">
        <option value="">{$lang.select_please}</option>
        <option value="drop">{$lang.remove}</option>
      </select>
      <input type="submit" class="button primary submitButton" value="{$lang.button_submit}" id="btnSubmit" name="btnSubmit"  disabled="true" />
    </td>
    <td align="right" nowrap="true">
    {include file="page.tpl"}
    </td>
  </tr>
</table>

{if $full_page}
</div>
</form>

<script type="text/javascript">
  listTable.recordCount = {$record_count};
  listTable.pageCount = {$page_count};

  {foreach from=$filter item=item key=key}
  listTable.filter.{$key} = '{$item}';
  {/foreach}


  onload = function()
  {
    document.forms['listForm'].reset();
  }
    /**
   * @param: bool ext 其他条件：用于批量操作
   */
  function confirmSubmit(frm, ext)
  {
      if (frm.elements['type'].value == 'drop')
      {

      return confirm("{$lang.batch_drop_confirm}");

      }
      else if (frm.elements['type'].value == '')
      {
          return false;
      }
      else
      {
          return true;
      }
  }
    function changeAction()
  {
      var frm = document.forms['listForm'];

      // 批量操作后的显示

      if (!document.getElementById('btnSubmit').disabled &&
          confirmSubmit(frm, false))
      {
          frm.submit();
      }
  }


</script>
{include file="pagefooter.tpl"}
{/if}
