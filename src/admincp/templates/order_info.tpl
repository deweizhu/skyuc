{include file="pageheader.tpl"}
{insert_scripts files="topbar.js,../js/utils.js,listtable.js,selectzone.js"}

<form action="order.php?act=operate" method="post" name="theForm">
<div class="list-div" style="margin-bottom: 5px">
<table width="100%" cellpadding="3" cellspacing="1">
  <tr>
    <td colspan="4">
      <div align="center">
        <input name="prev" type="button" class="button"   onClick="location.href='order.php?act=info&order_id={$prev_id}';" value="{$lang.prev}" {if !$prev_id}disabled{/if} />
        <input name="next" type="button" class="button"   onClick="location.href='order.php?act=info&order_id={$next_id}';" value="{$lang.next}" {if !$next_id}disabled{/if} />
    </div></td>
  </tr>
  <tr>
    <th colspan="4">{$lang.base_info}</th>
  </tr>
  <tr>
    <td width="15%"><div align="right"><strong>{$lang.label_order_sn}</strong></div></td>
    <td width="34%">{$order.order_sn}</td>
    <td width="15%"><div align="right"><strong>{$lang.label_pay_status}</strong></div></td>
    <td>{$order.status}</td>
  </tr>
  <tr>
    <td><div align="right"><strong>{$lang.label_user_name}</strong></div></td>
    <td>{$order.user_name|default:$lang.anonymous}</td>
    <td><div align="right"><strong>{$lang.label_order_time}</strong></div></td>
    <td>{$order.order_time}</td>
  </tr>
  <tr>
    <td><div align="right"><strong>{$lang.label_payment}</strong></div></td>
    <td>{if $order.pay_id > 0}{$order.pay_name}{else}{$lang.require_field}{/if}</td>
    <td><div align="right"><strong>{$lang.label_pay_time}</strong></div></td>
    <td>{$order.pay_time}</td>
  </tr>
   <tr>
    <td><div align="right"><strong>{$lang.label_order_amount}</strong></div></td>
    <td>{$order.formated_money_refund}</td>
    <td><div align="right"><strong>{$lang.label_pay_amount}</strong></div></td>
    <td>{$order.formated_pay_amount}</td>
  </tr>
    <tr>
    <td><div align="right"><strong>{$lang.label_surplus}</strong></div></td>
    <td>{$order.formated_surplus}</td>
    <td><div align="right"><strong>{$lang.label_integral}</strong></div></td>
    <td>{$order.integral}</td>
  </tr>
      <tr>
    <td><div align="right"><strong>{$lang.label_order_buyinfo}</strong></div></td>
    <td>{$order.order_buyinfo}</td>
    <td><div align="right"><strong>{$lang.label_user_ip}</strong></div></td>
    <td>{$order.user_ip}</td>
  </tr>
  </table>
</div>

</form>
{include file="pagefooter.tpl"}
