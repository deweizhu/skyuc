<!-- {if $arrange eq "h"} 横排 -->
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    {foreach from=$show_list item=show}
    <td><table width="100%">
      {if $need_image}
      <tr>
        <td align="center"><a href="{$show_url}{$show.show_id}" target="_blank"><img src="{$url}{$show.thumb}" alt="{$show.title|escape:html}" border="0" {if $thumb_width and $thumb_height}width="{$thumb_width}" height="{$thumb_height}"{/if}></a></td>
      </tr>
      {/if}
      <tr>
        <td align="center"><a href="{$show_url}{$show.show_id}" target="_blank">{$show.title}</a></td>
      </tr>
    </table></td>
    {/foreach}
  </tr>
</table>
<!-- {else} 竖排 -->
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  {foreach from=$show_list item=show}
  <tr>
    <td><table width="100%">
      {if $need_image}
      <tr>
        <td align="center"><a href="{$show_url}{$show.show_id}" target="_blank"><img src="{$url}{$show.thumb}" alt="{$show.title|escape:html}" border="0" width="{$thumb_width}" height="{$thumb_height}"></a></td>
      </tr>
      {/if}
      <tr>
        <td align="center"><a href="{$show_url}{$show.show_id}" target="_blank">{$show.title}</a></td>
      </tr>
    </table></td>
  </tr>
  {/foreach}
</table>
<!-- {/if} -->