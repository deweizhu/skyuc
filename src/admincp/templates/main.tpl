{include file="pageheader.tpl"}
<!-- directory install start -->
<ul style="padding:0; margin: 0; list-style-type:none; color: #CC0000;">
  {if $GetNewInfo}
  {$GetNewInfo}
  {/if}
  {foreach from=$warning_arr item=warning}
  <li style="border: 1px solid #CC0000; background: #FFFFCC; padding: 10px; margin-bottom: 5px;" ><img src='images/warning_small.png'> {$warning}</li>
  {/foreach}
</ul>
<!-- directory install end -->
<!-- start system information -->
<div class="list-div">
<table cellspacing='1' cellpadding='3'>
  <tr>
    <th colspan="4" class="group-title">{$lang.system_info}</th>
  </tr>
  <tr>
    <td width="20%">{$lang.os}</td>
    <td width="30%">{$sys_info.os}</td>
    <td width="20%">{$lang.web_server}</td>
    <td width="30%">{$sys_info.web_server}</td>
  </tr>
  <tr>
    <td>{$lang.php_version}</td>
    <td>{$sys_info.php_ver}</td>
    <td>{$lang.mysql_version}</td>
    <td>{$sys_info.mysql_ver}</td>
  </tr>
  <tr>
    <td>{$lang.safe_mode}</td>
    <td>{$sys_info.safe_mode}</td>
    <td>{$lang.safe_mode_gid}</td>
    <td>{$sys_info.safe_mode_gid}</td>
  </tr>
   <tr>
    <td>{$lang.register_globals}</td>
    <td>{$sys_info.register_globals}</td>
    <td>{$lang.magic_quotes_gpc}</td>
    <td>{$sys_info.magic_quotes_gpc}</td>
  </tr>
  <tr>
    <td>{$lang.socket}</td>
    <td>{$sys_info.socket}</td>
    <td>{$lang.allow_url_fopen}</td>
    <td>{$sys_info.allow_url_fopen}</td>
  </tr>
  <tr>
    <td>{$lang.curl}</td>
    <td>{$sys_info.curl}</td>
    <td>{$lang.zlib}</td>
    <td>{$sys_info.zlib}</td>
  </tr>
  <tr>
    <td>{$lang.max_filesize}</td>
    <td>{$sys_info.max_filesize}</td>
	 <td>{$lang.post_max_size}</td>
    <td>{$sys_info.post_max_size}</td>
  </tr>
  <tr>
    <td>{$lang.skyuc_version}</td>
    <td>{$skyuc_version}</td>
	  <td>{$lang.timezone}</td>
    <td>{$sys_info.timezone}</td>
  </tr>
</table>
</div>

{include file="pagefooter.tpl"}
