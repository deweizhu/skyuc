<div class="form-div">
  <form action="javascript:searchShows()" name="searchForm">
    <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />
    {if $smarty.get.act neq "trash"}
    <!-- 分类 -->
    <select class="textCtrl"  name="cat_id"><option value="0">{$lang.select_cat}</option>{$cat_list}</select>
    <!-- 服务器 -->
    <select class="textCtrl"  name="server_id"><option value="0">{$lang.select_server}</option>{html_options options=$server_list}</select>
	<!--播放器-->
	<select class="textCtrl"  name="player"><option value="0">{$lang.select_player}</option>{html_options options=$player}</select>
    <!-- 推荐 -->
    <select class="textCtrl"  name="intro_type"><option value="0">{$lang.intro_type}</option>{html_options options=$intro_list selected=$smarty.get.intro_type}</select>
    {/if}
    <!-- 关键字 -->
    {$lang.keyword} <input type="text"  name="keyword"  />
    <input type="submit" class="button primary submitButton" value="{$lang.button_search}"/>
  </form>
</div>


<script language="JavaScript">
    function searchShows()
    {

        {if $smarty.get.act neq "trash"}
        listTable.filter['cat_id'] = document.forms['searchForm'].elements['cat_id'].value;
        listTable.filter['server_id'] = document.forms['searchForm'].elements['server_id'].value;
		listTable.filter['player'] = document.forms['searchForm'].elements['player'].value;
        listTable.filter['intro_type'] = document.forms['searchForm'].elements['intro_type'].value;
        {/if}

        listTable.filter['keyword'] = Utils.trim(document.forms['searchForm'].elements['keyword'].value);
        listTable.filter['page'] = 1;

        listTable.loadList();
    }
</script>
