{include file="pageheader.tpl"}

<div class="form-div">
  <form method="post" action="template.php">
  {$lang.select_template}
  <select class="textCtrl"  name="template_file">
    {html_options options=$lang.template_files selected=$curr_template_file}
  </select>
  <input type="submit" class="button primary submitButton" value="{$lang.button_submit}"  />
  <input type="hidden" name="act" value="setup" />
  </form>
</div>

<!-- start template options list -->
<div class="list-div">
<form name="theForm" action="template.php" method="post">
  <table width="100%" cellpadding="3" cellspacing="1">
  <tr>
    <th>{$lang.library_name}</th>
    <th>{$lang.region_name}</th>
    <th>{$lang.sort_order}</th>
    <th>{$lang.contents}</th>
    <th>{$lang.number}</th>
    <th>{$lang.display}</th>
  </tr>
  {foreach from=$temp_options item=library key=lib_name}
  <tr>
    <td class="first-cell">{$library.desc}</td>
	<td><select class="textCtrl"  name="regions[{$lib_name}]">{if $library.editable eq 1}<option value="">{$lang.not_editable}</option>{else}<option value="">{$lang.select_plz}</option>{html_options values=$temp_regions output=$temp_regions selected=$library.region}{/if}</select></td>
    <td><input type="text" name="sort_order[{$lib_name}]" value="{$library.sort_order}" size="4" /></td>
    <td><input type="text" name="map[{$lib_name}]" value="{$library.library}"  size="40" readonly style="color:#aaa;"/></td>
    <td>{if $library.number_enabled}<input type="text" name="number[{$lib_name}]" value="{$library.number}" size="4" />{else}&nbsp;{/if}</td>
     <td align="center"><input type="checkbox" name="display[{$lib_name}]" value="1" {if $library.editable eq 1} disabled {/if}{if $library.display eq 1} checked="true" {/if} /></td>
  </tr>
  {/foreach}

  <!-- 分类影片 -->
  <tr>
    <td colspan="6" style="background-color: #F4FBFB; font-weight: bold" align="left"><a href="javascript:;" onclick="addCatShows(this)">[+]</a> {$lang.template_libs.cat_show} </td>
  </tr>
  {foreach from=$cate_show item=library key=lib_name}
  <tr>
    <td class="first-cell" align="right"><a href="javascript:;" onclick="removeRow(this)">[-]</a></td>
    <td><select class="textCtrl"  name="regions[cat_show][]"><option value="">{$lang.select_plz}</option>{html_options values=$temp_regions output=$temp_regions selected=$library.region}</select></td>
    <td><input type="text" name="sort_order[cat_show][]" value="{$library.sort_order}" size="4" /></td>
    <td><select class="textCtrl"  name="categories[cat_show][]"><option value="">{$lang.select_plz}</option>{$library.cats}</select>
		<input type="text" name="maps[cat_show][]" value="{$library.library}" size="40" />e.g： /library/cat_show***.lbi</td>
    <td><input type="text" name="number[cat_show][]" value="{$library.number}" size="4"  /></td>
    <td></td>
  </tr>
  {/foreach}
 <!-- 分类点播排行榜 -->
    <tr>
    <td colspan="6" style="background-color: #F4FBFB; font-weight: bold" align="left"><a href="javascript:;" onclick="addCatHots(this)">[+]</a> {$lang.template_libs.cat_hot} </td>
  </tr>
  {foreach from=$cate_hot item=library key=lib_name}
  <tr>
    <td class="first-cell" align="right"><a href="javascript:;" onclick="removeRow(this)">[-]</a></td>
    <td><select class="textCtrl"  name="regions[cat_hot][]"><option value="">{$lang.select_plz}</option>{html_options values=$temp_regions output=$temp_regions selected=$library.region}</select></td>
    <td><input type="text" name="sort_order[cat_hot][]" value="{$library.sort_order}" size="4" /></td>
    <td><select class="textCtrl"  name="catehots[cat_hot][]"><option value="">{$lang.select_plz}</option>{$library.cats}</select>
	<input type="text" name="maps[cat_hot][]" value="{$library.library}" size="40" />e.g： /library/cat_hot***.lbi</td>
    <td><input type="text" name="number[cat_hot][]" value="{$library.number}" size="4"  /></td>
    <td></td>
  </tr>
  {/foreach}
    <!-- 连载影片 -->
 <tr>
    <td colspan="6" style="background-color: #F4FBFB; font-weight: bold" align="left"><a href="javascript:;" onclick="addSeries(this)">[+]</a> {$lang.template_libs.series} </td>
  </tr>
  {foreach from=$series item=library key=lib_name}
  <tr>
    <td class="first-cell" align="right"><a href="javascript:;" onclick="removeRow(this)">[-]</a></td>
    <td><select class="textCtrl"  name="regions[series][]"><option value="">{$lang.select_plz}</option>{html_options values=$temp_regions output=$temp_regions selected=$library.region}</select></td>
    <td><input type="text" name="sort_order[series][]" value="{$library.sort_order}" size="4" /></td>
    <td><select class="textCtrl"  name="series_cat[series][]"><option value="0">{$lang.select_plz}</option>{$library.cat}</select>
	<input type="text" name="maps[series][]" value="{$library.library}" size="40" />e.g： /library/series***.lbi</td>
    <td><input type="text" name="number[series][]" value="{$library.number}" size="4" /></td>
    <td></td>
  </tr>
  {/foreach}
  <!-- 广告 -->
  <tr>
    <td colspan="6" style="background-color: #F4FBFB; font-weight: bold" align="left"><a href="javascript:;" onclick="addAdPosition(this)">[+]</a> {$lang.template_libs.ad_position} </td>
  </tr>
  {foreach from=$ad_positions item=ad_position key=lib_name}
  <tr>
    <td class="first-cell" align="right"><a href="javascript:;" onclick="removeRow(this)">[-]</a></td>
    <td><select class="textCtrl"  name="regions[ad_position][]"><option value="">{$lang.select_plz}</option>{html_options values=$temp_regions output=$temp_regions selected=$ad_position.region}</select></td>
    <td><input type="text" name="sort_order[ad_position][]" value="{$ad_position.sort_order}" size="4" /></td>
    <td><select class="textCtrl"  name="ad_position[]"><option value="0">{$lang.select_plz}</option>{html_options options=$arr_ad_positions selected=$ad_position.ad_pos}</select></td>
    <td><input type="text" name="number[ad_position][]" value="{$ad_position.number}" size="4" /></td>
    <td></td>
  </tr>
  {/foreach}

  </table>
  <div class="button-div ">
    <input type="submit" class="button primary submitButton" value="{$lang.button_submit}"  />
    <input type="reset" class="button submitButton"  value="{$lang.button_reset}"  />
    <input type="hidden" name="act" value="setting" />
    <input type="hidden" name="template_file" value="{$curr_template_file}" />
  </div>
</form>
</div>

<!-- end template options list -->

<script language="JavaScript">
<!--
var currTemplateFile = '{$curr_template_file}';
var selCategories    = '{$arr_cates}';
var selRegions       = new Array();
var selAdPositions   = new Array();

{foreach from=$temp_regions item=region key=id}
selRegions[{$id}] = '{$region|escape:quotes}';
{/foreach}

{foreach from=$arr_ad_positions item=ad_position key=id}
selAdPositions[{$id}] = '{$ad_position|escape:quotes}';
{/foreach}




/**
 * 创建区域选择的下拉列表
 */
function buildRegionSelect(selName)
{
    var sel = '<select class="textCtrl"  name="' + selName + '">';

    sel += '<option value="">' + selectPlease + '</option>';

    for (i=0; i < selRegions.length; i++)
    {
        sel += '<option value="' + selRegions[i] + '">' + selRegions[i] + '</option>';
    }

    sel += '</select>';

    return sel;
}



/**
 * 创建选择广告位置的列表
 */
function buildAdPositionsSelect(selName)
{
    var sel = '<select class="textCtrl"  name="' + selName + '">';

    sel += '<option value="">' + selectPlease + '</option>';

    for (ap in selAdPositions)
    {
        if (ap != "______array" && ap != "toJSONString")
        {
          sel += '<option value="' + ap + '">' + selAdPositions[ap] + '</option>';
        }
    }

    sel += '</select>';

    return sel;
}
/**
 * 增加一个分类的影片
 */
function addCatShows(obj)
{
    var rowId = rowindex(obj.parentNode.parentNode);

    var table = obj.parentNode.parentNode.parentNode.parentNode;

    var row  = table.insertRow(rowId + 1);
    var cell = row.insertCell(-1);
    cell.innerHTML = '<a href="javascript:;" onclick="removeRow(this)">[-]</a>';
    cell.className = 'first-cell';
    cell.align     = 'right';

    cell           = row.insertCell(-1);
    cell.innerHTML = buildRegionSelect('regions[cat_show][]');

    cell           = row.insertCell(-1);
    cell.innerHTML = '<input type="text" name="sort_order[cat_show][]" value="0" size="4" />';

    cell           = row.insertCell(-1);
    cell.innerHTML = '<select class="textCtrl"  name="categories[cat_show][]"><option value="">' + selectPlease + '</option>' + selCategories + '</select><input type="text" name="maps[cat_show][]" value="/library/cat_show.lbi" size="40" />';

    cell           = row.insertCell(-1);
    cell.innerHTML = '<input type="text" name="number[cat_show][]" value="10" size="4" />';

    cell           = row.insertCell(-1);
}
/**
 * 增加一个分类点播排行榜
 */
function addCatHots(obj)
{
    var rowId = rowindex(obj.parentNode.parentNode);

    var table = obj.parentNode.parentNode.parentNode.parentNode;

    var row  = table.insertRow(rowId + 1);
    var cell = row.insertCell(-1);
    cell.innerHTML = '<a href="javascript:;" onclick="removeRow(this)">[-]</a>';
    cell.className = 'first-cell';
    cell.align     = 'right';

    cell           = row.insertCell(-1);
    cell.innerHTML = buildRegionSelect('regions[cat_hot][]');

    cell           = row.insertCell(-1);
    cell.innerHTML = '<input type="text" name="sort_order[cat_hot][]" value="0" size="4" />';

    cell           = row.insertCell(-1);
    cell.innerHTML = '<select class="textCtrl"  name="catehots[cat_hot][]"><option value="">' + selectPlease + '</option>' + selCategories + '</select><input type="text" name="maps[cat_hot][]" value="/library/cat_hot.lbi" size="40" />';

    cell           = row.insertCell(-1);
    cell.innerHTML = '<input type="text" name="number[cat_hot][]" value="10" size="4" />';

    cell           = row.insertCell(-1);
}

/**
 * 增加一个连载影片
 */
function addSeries(obj)
{
    var rowId = rowindex(obj.parentNode.parentNode);

    var table = obj.parentNode.parentNode.parentNode.parentNode;

    var row  = table.insertRow(rowId + 1);
    var cell = row.insertCell(-1);
    cell.innerHTML = '<a href="javascript:;" onclick="removeRow(this)">[-]</a>';
    cell.className = 'first-cell';
    cell.align     = 'right';

    cell           = row.insertCell(-1);
    cell.innerHTML = buildRegionSelect('regions[series][]');

    cell           = row.insertCell(-1);
    cell.innerHTML = '<input type="text" name="sort_order[series][]" value="0" size="4" />';

    cell           = row.insertCell(-1);
	cell.innerHTML = '<select class="textCtrl"  name="series_cat[series][]"><option value="">' + selectPlease + '</option>' + selCategories + '</select><input type="text" name="maps[series][]" value="/library/series.lbi" size="40" />';

    cell           = row.insertCell(-1);
    cell.innerHTML = '<input type="text" name="number[series][]" value="10" size="4" />';

    cell           = row.insertCell(-1);
}
/**
 * 增加一个广告位
 */
function addAdPosition(obj)
{
    var rowId = rowindex(obj.parentNode.parentNode);

    var table = obj.parentNode.parentNode.parentNode.parentNode;

    var row  = table.insertRow(rowId + 1);
    var cell = row.insertCell(-1);
    cell.innerHTML = '<a href="javascript:;" onclick="removeRow(this)">[-]</a>';
    cell.className = 'first-cell';
    cell.align     = 'right';

    cell           = row.insertCell(-1);
    cell.innerHTML = buildRegionSelect('regions[ad_position][]');

    cell           = row.insertCell(-1);
    cell.innerHTML = '<input type="text" name="sort_order[ad_position][]" value="0" size="4" />';

    cell           = row.insertCell(-1);
    cell.innerHTML = buildAdPositionsSelect('ad_position[]');

    cell           = row.insertCell(-1);
    cell.innerHTML = '<input type="text" name="number[ad_position][]" value="1" size="4" />';

    cell           = row.insertCell(-1);
}

/**
 * 删除一行
 */
function removeRow(obj)
{
    if (confirm(removeConfirm))
    {
        var table = obj.parentNode.parentNode.parentNode;
        var row   = obj.parentNode.parentNode;

        for (i = 0; i < table.childNodes.length; i++)
        {
            if (table.childNodes[i] == row)
            {
                table.removeChild(table.childNodes[i]);
            }
        }
    }
}

//-->
</script>
{include file="pagefooter.tpl"}
