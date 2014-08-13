
<div id="turn-page">
     {$lang.total_records} <span id="totalRecords">{$record_count}</span>
     {$lang.total_pages} <span id="totalPages">{$page_count}</span>
     {$lang.page_current} <span id="pageCurrent">{$filter.page}</span>
     {$lang.page_size} <input type='text' size='3' id='pageSize' value="{$filter.page_size}" onkeypress="return listTable.changePageSize(event)" />
  <span id="page-link">
    <a href="javascript:listTable.gotoPageFirst()">{$lang.page_first}</a>
    <a href="javascript:listTable.gotoPagePrev()">{$lang.page_prev}</a>
    <a href="javascript:listTable.gotoPageNext()">{$lang.page_next}</a>
    <a href="javascript:listTable.gotoPageLast()">{$lang.page_last}</a>
     <select class="textCtrl"  id="gotoPage" onchange="listTable.gotoPage(this.value)">
       {create_pages count=$page_count page=$filter.page}
    </select>
  </span>
 </div>
