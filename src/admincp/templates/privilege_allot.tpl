{include file="pageheader.tpl"}
<form method="POST" action="privilege.php" name="theFrom">
<div class="list-div">
<table cellspacing='1' id="list-table">
{foreach from=$priv_arr item=priv}
 <tr>
  <td width="18%" valign="top" class="first-cell">
    <input name="chkGroup" type="checkbox" value="checkbox" onclick="check('{$priv.priv_list}',this);" class="checkbox">{$lang[$priv.action_code]}
  </td>
  <td>
    {foreach from=$priv.priv key=priv_list item=list}
    <div style="width:200px;float:left;">
    <label for="{$priv_list}"><input type="checkbox" name="action_code[]" value="{$priv_list}" id="{$priv_list}" class="checkbox" {if $list.cando eq 1} checked="true" {/if} />
    {$lang[$list.action_code]}</label>
    </div>
    {/foreach}
</td></tr>
{/foreach}
  <tr>
    <td align="center" colspan="2" >
      <input type="checkbox" name="checkall" value="checkbox" onclick="checkAll(this.form, this);"  />{$lang.check_all}
      &nbsp;&nbsp;&nbsp;&nbsp;
      <input type="submit" class="button primary submitButton"   name="Submit"   value="{$lang.button_save}"  />
      <input type="hidden"   name="id"    value="{$user_id}" />
      <input type="hidden"   name="act"   value="{$form_act}" />
    </td>
  </tr>
</table>
</div>
</form>


<script language="javascript">
function checkAll(frm, checkbox)
{
  for (i = 0; i < frm.elements.length; i++)
  {
    if (frm.elements[i].name == 'action_code[]' || frm.elements[i].name == 'chkGroup')
    {
      frm.elements[i].checked = checkbox.checked;
    }
  }
}

function check(list, obj)
{
  var frm = obj.form;

    for (i = 0; i < frm.elements.length; i++)
    {
      if (frm.elements[i].name == "action_code[]")
      {
          var regx = new RegExp(frm.elements[i].value + "(?!_)", "i");

          if (list.search(regx) > -1) frm.elements[i].checked = obj.checked;
      }
    }
}
</script>

{include file="pagefooter.tpl"}
