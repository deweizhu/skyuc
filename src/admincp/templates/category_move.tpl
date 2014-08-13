{include file="pageheader.tpl"}
<div class="main-div">
<table width="100%">
  <tr>
    <td><img src="images/notice.gif" width="16" height="16" border="0">{$lang.cat_move_desc}
     <ul>
       <li>{$lang.cat_move_notic}</li>
     </ul>
    </td>
  </tr>
</table>
</div>

<div class="form-div">
<form action="category.php" method="post" name="theForm" enctype="multipart/form-data">
      <strong>{$lang.source_cat}</strong>&nbsp;&nbsp;
      <select class="textCtrl"  name="cat_id" >
       <option value="0">{$lang.select_please}</option>
       {$cat_select}
      </select>&nbsp;&nbsp;
      <strong>{$lang.target_cat}</strong>
      <select class="textCtrl"  name="target_cat_id">
       <option value="0">{$lang.select_please}</option>
       {$cat_select}
      </select>&nbsp;&nbsp;&nbsp;&nbsp;
      <input type="submit" class="button primary submitButton" name="move_cat" value="{$lang.start_move_cat}" >
      <input type="reset" class="button submitButton"  value="{$lang.button_reset}"  />
      <input type="hidden" name="act" value="{$form_act}" />
</form>
</div>
{insert_scripts files="skyuc_validator.js"}
{include file="pagefooter.tpl"}
