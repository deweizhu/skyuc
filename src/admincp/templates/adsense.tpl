{include file="pageheader.tpl"}
<!-- start ads_stats list -->
<div class="list-div" id="listDiv">
<table width="100%" border="0" cellpadding="3" cellspacing="1">
  <tr>
    <th>{$lang.adsense_name}</th>
    <th>{$lang.cleck_referer}</th>
    <th>{$lang.click_count}</th>
  </tr>
  {foreach from=$ads_stats item=list}
  <tr>
    <td>{$list.ad_name}</td>
    <td>{$list.referer}</td>
    <td>{$list.clicks}</td>
  </tr>
  {/foreach}
   {foreach from=$show_stats item=info}
  <tr>
    <td>{$info.ad_name}</td>
    <td>{$info.referer}</td>
    <td align="right">{$info.clicks}</td>
  </tr>
  {foreachelse}
    <tr><td class="no-records" colspan="3">{$lang.no_records}</td></tr>
  {/foreach}
</table>
</div>
<!-- end ads_stats list -->
{insert_scripts files="skyuc_validator.js"}
{include file="pagefooter.tpl"}