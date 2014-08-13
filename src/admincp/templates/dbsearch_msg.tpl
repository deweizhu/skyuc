{include file="pageheader.tpl"}
<div class="list-div">
  <div style="background:#FFF; padding: 20px 50px; margin: 2px;">
    <table align="center" width="400">
      <tr>
        <td width="100%" valign="top">
          <img src="images/information.gif" width="32" height="32" border="0" alt="information" />
          <span style="font-size: 14px; font-weight: bold">{$title}</span>
        </td>
      </tr>
      <tr>
        <td>
          <a href="{$auto_link}">{$lang.jump_notice}</a>
          <script>setTimeout("window.location.replace('{$auto_link}');", 1250);</script>
        </td>
      </tr>
    </table>
  </div>
</div>
{include file="pagefooter.tpl"}