{include file="pageheader.tpl"}
{insert_scripts files="../skyuc_utils.js,skyuc_selectzone.js"}
<script language="JavaScript">
<!--
 $(document).ready(function()
 {
	$('#cke_article_content input').removeAttr('class');
 });
//-->
</script>
<!-- start show form -->
<div class="tab-div">
  <div id="tabbar-div">
    <p>
      <span class="tab-front" id="general-tab">{$lang.tab_general}</span>
	  <span class="tab-back" id="detail-tab">{$lang.tab_content}</span>
	  <span class="tab-back" id="show-tab">{$lang.tab_show}</span>
    </p>
  </div>

  <div id="tabbody-div">
    <form  action="article.php" method="post" enctype="multipart/form-data" name="theForm" onsubmit="return validate();">
    <table width="100%" id="general-table">
      <tr>
        <td class="narrow-label">{$lang.title}</td>
        <td><input type="text"   name="title" size ="40" maxlength="60" value="{$article.title|escape}" />{$lang.require_field}</td>
      </tr>
	 <!-- {if $article.cat_id >= 0} -->
      <tr>
        <td class="narrow-label">{$lang.cat} </td>
        <td>
          <select class="textCtrl"  name="article_cat" onchange="catChanged()">
            <option value="0">{$lang.select_plz}</option>
            {$cat_select}
          </select>
         {$lang.require_field}</td>
      </tr>
      <!-- {else} -->
      <input type="hidden" name="article_cat" value="-1" />
      <!-- {/if} -->
	  <!--{if $article.cat_id >= 0}-->
      <tr>
        <td class="narrow-label">{$lang.article_type}</td>
        <td><input type="radio" name="article_type" value="0" {if $article.article_type eq 0}checked{/if}>{$lang.common}
      <input type="radio" name="article_type" value="1" {if $article.article_type eq 1}checked{/if}>{$lang.top}
        {$lang.require_field}
        </td>
      </tr>
      <tr>
        <td class="narrow-label">{$lang.is_open}</td>
        <td>
        <input type="radio" name="is_open" value="1" {if $article.is_open eq 1}checked{/if}> {$lang.isopen}
      <input type="radio" name="is_open" value="0" {if $article.is_open eq 0}checked{/if}> {$lang.isclose}{$lang.require_field}
        </td>
      </tr>
	  <!--{else}-->
      <tr style="display:none">
      <td colspan="2"><input type="hidden" name="article_type" value="0" /><input type="hidden" name="is_open" value="1" /></td>
      </tr>
      <!--{/if}-->
      <tr>
        <td class="narrow-label">{$lang.author}</td>
        <td><input type="text"   name="author" maxlength="30" value="{$article.author|escape}" /></td>
      </tr>
      <tr>
        <td class="narrow-label">{$lang.email}</td>
        <td><input type="text"   name="author_email" maxlength="60" value="{$article.email|escape}" /></td>
      </tr>
      <tr>
        <td class="narrow-label">{$lang.keywords}</td>
        <td><input type="text"   name="keywords" size ="40" maxlength="80" value="{$article.keywords|escape}" /></td>
      </tr>
	   <tr>
        <td class="narrow-label">{$lang.external_links}</td>
        <td><input name="link_url" type="text"   id="link_url" value="{if $article.link neq ''}{$article.link|escape}{else}http://{/if}"  size ="40" maxlength="80" /></td>
      </tr>
      <tr>
        <td class="narrow-label">{$lang.upload_file}</td>
        <td><input type="file" name="file">
          <span class="narrow-label">{$lang.file_url}
          <input name="file_url" type="text"   value="{$article.file_url|escape}" size ="40" maxlength="255" />
          </span></td>
      </tr>
    </table>

    <table width="100%" id="detail-table" style="display:none">
     <tr>
		<td>{$FCKeditor}</td></tr>
    </table>

    <table width="100%" id="show-table" style="display:none">
      <!-- 影片搜索 -->
      <tr>
      <td colspan="5">
        <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />
        <!-- 分类 -->
        <select class="textCtrl"  name="cat_id"><option value="0">{$lang.all_category}</caption>{$show_cat_list}</select>
        <!-- 服务器 -->
        <select class="textCtrl"  name="server_id"><option value="0">{$lang.all_server}</caption>{html_options options=$server_list}</select>
        <!-- 关键字 -->
        <input type="text"   name="keyword" size="30" />
        <input type="button" class="button"  value="{$lang.button_search}" onclick="searchShow()"  />
      <td>
      </tr>
      <!-- 影片列表 -->
      <tr>
        <th>{$lang.all_show}</th>
        <th>{$lang.handler}</th>
        <th>{$lang.send_bouns_show}</th>
      </tr>
      <tr>
        <td width="45%" align="center">
          <select class="textCtrl"  name="source_select" size="20" style="width:90%" ondblclick="sz.addItem(false, 'add_link_show', articleId)" multiple="true">
          </select>
        </td>
        <td align="center">
          <p><input type="button" class="button"  value="&gt;&gt;" onclick="sz.addItem(true, 'add_link_show', articleId)"  /></p>
          <p><input type="button" class="button"  value="&gt;" onclick="sz.addItem(false, 'add_link_show', articleId)"  /></p>
          <p><input type="button" class="button"  value="&lt;" onclick="sz.dropItem(false, 'drop_link_show', articleId)"  /></p>
          <p><input type="button" class="button"  value="&lt;&lt;" onclick="sz.dropItem(true, 'drop_link_show', articleId)"  /></p>
        </td>
        <td width="45%" align="center">
          <select class="textCtrl"  name="target_select" multiple="true" size="20" style="width:90%" ondblclick="sz.dropItem(false, 'drop_link_show', articleId)">
            {foreach from=$show_list item=show}
            <option value="{$show.show_id}">{$show.title}</option>
            {/foreach}
          </select>
        </td>
      </tr>
    </table>
    <div class="button-div">
      <input type="hidden" name="act" value="{$form_action}" />
      <input type="hidden" name="old_title" value="{$article.title}"/>
      <input type="hidden" name="id" value="{$article.article_id}" />
      <input type="submit" class="button primary submitButton" value="{$lang.button_submit}"   />
      <input type="reset" class="button submitButton"  value="{$lang.button_reset}"  />
    </div>
    </form>
  </div>

</div>
<!-- end show form -->
{insert_scripts files="skyuc_validator.js,skyuc_tab.js"}

<script language="JavaScript">

var articleId = {$article.article_id|default:0};
var elements  = document.forms['theForm'].elements;
var sz        = new SelectZone(1, elements['source_select'], elements['target_select'], '');


function validate()
{
  var validator = new Validator('theForm');
  validator.required('title', no_title);
  validator.isNullOption('article_cat',no_cat)

  return validator.passed();
}

/**
 *搜索相关影片
 */
function searchShow()
{
    var elements  = document.forms['theForm'].elements;
    var filters   = new Object;

    filters.cat_id = elements['cat_id'].value;
    filters.brand_id = elements['server_id'].value;
    filters.keyword = Utils.trim(elements['keyword'].value);

    sz.loadOptions('get_show_list', filters);
}
/**
 * 选取上级分类时判断选定的分类是不是底层分类
 */
function catChanged()
{
  var obj = document.forms['theForm'].elements['article_cat'];

  cat_type = obj.options[obj.selectedIndex].getAttribute('cat_type');
  if (cat_type == undefined)
  {
    cat_type = 1;
  }

  if ((obj.selectedIndex > 0) && (cat_type == 2 || cat_type == 4))
  {
    alert(not_allow_add);
    obj.selectedIndex = 0;
    return false;
  }

  return true;
}
</script>
{include file="pagefooter.tpl"}
