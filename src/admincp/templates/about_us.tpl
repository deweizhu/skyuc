{include file="pageheader.tpl"}
<SCRIPT LANGUAGE="JavaScript">
<!--
	 $(document).ready(function()
			 {
				$('#cacheclean').click(function(){
					$('#theForm').submit();
				});
			 });
//-->
</SCRIPT>

<div class="list-div">

<table cellspacing='1' cellpadding='3'>
  <tr>
    <th colspan="2" class="group-title">{$lang.cache_name}：{$pagedata.cache}&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" class="button"  id="cacheclean" value="{$lang.clear_cache}" />
	  <form action="index.php" method="post" id="theForm">
		<input type="hidden" name="act" value="clear_cache" />
       </form>

	</th>
  </tr>
  <tr>
    <td width="10%" class="first-cell"></td>
	<td>
    <img src="http://chart.apis.google.com/chart?cht=p3&chd=t:{$pagedata.status}&chs=500x120&chl=Used:{$pagedata.curMBytes}|Free:{$pagedata.freeMBytes}&chf=bg,s,f9f9f9&chco=0000ff&time={$pagedata.random}" />
    </td>
  </tr>
  <tr>
   <td width="10%" class="first-cell"></td>
   <td>
      {$lang.cache_desc}
    </td>
  </tr>

  {foreach from=$pagedata.cache_status item=item}
  <tr>
  <td class="first-cell">{$item.name}</td>
  <td>{$item.value}</td>
  </tr>
  {/foreach}

</table>
</div>
<br />

<div class="list-div">
<table cellspacing='1' cellpadding='3'>
  <tr>
    <th colspan="2" class="group-title">{$lang.official_site}</th>
  </tr>
  <tr>
    <td class="first-cell" width="20%">{$lang.site_url}</td><td><a href="http://www.skyuc.com" target="_blank">http://www.skyuc.com</a></td>
  </tr>
  <tr>
    <td class="first-cell" width="20%">{$lang.support_forum}</td><td><a href="http://bbs.skyuc.com" target="_blank">http://bbs.skyuc.com</a></td>
  </tr>
</table>
</div>
{include file="pagefooter.tpl"}
