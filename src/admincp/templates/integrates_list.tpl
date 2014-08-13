{include file="pageheader.tpl"}

<div class="form-div">
  <pre>{$lang.help_notice}</pre></div>

<div class="list-div" id="listDiv">
<table cellspacing='1' cellpadding='3'>
  <tr>
    <th>{$lang.integrate_name}</th>
    <th>{$lang.integrate_version}</th>
    <th>{$lang.integrate_author}</th>
    <th>{$lang.handler}</th>
  </tr>
  {foreach from=$modules item=module}
  <tr>
    <td class="first-cell">{$module.name}</td>
    <td>{$module.version}</td>
    <td><a href="{$module.website}">{$module.author}</a></td>
    <td align="center">
      {if $module.installed == 1}
      <a href="integrate.php?act=setup&code={$module.code}">{$lang.setup}</a>{if $allow_set_points}&nbsp;<a href="integrate.php?act=points_set&code={$module.code}">{$lang.points_set}</a>{/if}
      {else}
      <a {if $module.code neq "SKYUChop"}href="javascript:confirm_redirect('{$lang.install_confirm}', 'integrate.php?act=install&code={$module.code}')"{else}href="integrate.php?act=install&code={$module.code}" {/if}>{$lang.install}</a>
      {/if}
    </td>
  </tr>
  {/foreach}
</table>
</div>


{include file="pagefooter.tpl"}