{include file="pageheader.tpl"}
{insert_scripts files="../skyuc_utils.js,skyuc_selectzone.js,skyuc_colorselector.js"}
<script language="JavaScript">
<!--
 $(document).ready(function()
 {
	$('#cke_detail input').removeAttr('class');
 });
//-->
</script>
{if $warning}
<ul style="padding:0; margin: 0; list-style-type:none; color: #CC0000;">
  <li style="border: 1px solid #CC0000; background: #FFFFCC; padding: 10px; margin-bottom: 5px;" >{$warning}</li>
</ul>
{/if}

<!-- start show form -->
<div class="tab-div">
    <!-- tab bar -->
    <div id="tabbar-div">
      <p>
        <span class="tab-front" id="general-tab">{$lang.tab_general}</span>
		<span class="tab-back" id="url-tab">{$lang.tab_url}</span>
	    <span class="tab-back" id="detail-tab">{$lang.tab_detail}</span>
      </p>
    </div>

    <!-- tab body -->
    <div id="tabbody-div">
      <form enctype="multipart/form-data" action="" method="post" name="theForm" onsubmit="return validate();">
	     <input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
        <table width="100%" id="general-table" align="center">
          <tr>
            <td class="label">{$lang.lab_title}
			<a href="javascript:showNotice('notice_title');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
            <td><input type="text" name="title" value="{$show.title|escape}" style="float:left;color:{$title_color};" size="40" /><div style="background-color:{$title_color};float:left;margin-left:2px;" id="font_color" onclick="ColorSelecter.Show(this);"><img src="images/color_selecter.gif" style="margin-top:-1px;" /></div><input type="hidden" id="title_color" name="title_color" value="{$title_color}" />&nbsp;
            <select class="textCtrl"  name="title_style">
              <option value="">{$lang.select_font}</option>
              {html_options options=$lang.font_styles selected=$title_style}
            </select>&nbsp;{$lang.title_nostyle}：<input type="checkbox" name="title_nostyle" value="1"> {$lang.require_field}
			<br />
            <span class="notice-span" id="notice_title">{$lang.notice_title}</span>
			</td>
          </tr>
		   <tr>
            <td class="label">{$lang.lab_title_alias} </td>
            <td><input type="text"   name="title_alias" value="{$show.title_alias|escape}" size="50" /><br />
          </td>
          </tr>
		   <tr>
            <td class="label">{$lang.lab_name_english} </td>
            <td><input type="text"   name="title_english" value="{$show.title_english|escape}" size="50" /><br />
          </td>
          </tr>
          <tr>
            <td class="label">{$lang.lab_actor} </td>
            <td><input type="text" name="actor" value="{$show.actor|escape}" size="50" />
			<select class="textCtrl" style="width: 10em" onChange="javascript:ChangeInput(this,document.theForm.actor, ' ')">
			<option selected="selected">{$lang.select_actor}</option>
			{$actor_list}
		   </select>

          </td>
          </tr>
		   <tr>
            <td class="label">{$lang.lab_director} </td>
            <td><input type="text"   name="director" value="{$show.director|escape}" size="50" />
			<select class="textCtrl" style="width: 10em"       onChange="javascript:ChangeInput(this,document.theForm.director, ' ')">
			<option selected="selected">{$lang.select_director}</option>
			{$director_list}
		   </select>
          </td>
          </tr>
		  	<tr>
            <td class="label">{$lang.lab_status} </td>
            <td><input type="text"   name="status" value="{$show.status|escape}" size="50" />
			<select class="textCtrl" style="width: 10em"   onChange="javascript:ChangeInput(this,document.theForm.status, ' ')">
			<option selected="selected">{$lang.select_status}</option>
			{$status_list}
		   </select>
          </td>
          </tr>
          <tr>
            <td class="label">{$lang.lab_show_cat}</td>
            <td><select class="textCtrl"  name="cat_id" id="cat_id">
			<option value="0">{$lang.select_please}</option>
			{$cat_list}
			</select>{$lang.require_field} &nbsp;&nbsp;<strong>{$lang.lab_other_cat}</strong>
			 <input type="button" class="button"  value="{$lang.add}" class="button" onclick="javascript:addOtherCat(this.parentNode);"  />
              {foreach from=$show.other_cat item=cat_id}
              <select class="textCtrl"  name="other_cat[]"><option value="0">{$lang.select_please}</option>{$other_cat_list.$cat_id}</select>
              {/foreach}
			</td>
          </tr>

		    <tr>
            <td class="label">{$lang.lab_area}</td>
            <td>
			<select class="textCtrl"  name="area">{$area_list}</select> &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;<strong>{$lang.lab_lang}</strong>
			<select class="textCtrl"  name="lang">{$lang_list}</select>
			</td>
          </tr>
		    <tr>
            <td class="label">{$lang.lab_pubdate}</td>
            <td><input type="text" name="pubdate" value="{$show.pubdate}" size="20" />
			<!-- {if $form_act eq 'update'} -->
			&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;<strong>{$lang.lab_addtime}</strong> <input type="checkbox" name="addtime" value='1' checked="checked"> <a href="javascript:showNotice('noticeaddtime');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a><br />
            <span class="notice-span" id="noticeaddtime">{$lang.notice_addtime}
			<!-- {/if} -->
			</td>
          </tr>
          <tr>
            <td class="label">{$lang.lab_runtime} </td>
            <td><input type="text"   name="runtime" value="{$show.runtime}" size="10" /> {$lang.minute}
			&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;<b>{$lang.lab_points}</b><a href="javascript:showNotice('noticeshowpoints');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a>
			<input type="text"   name="points" value="{$show.points}" size="10" /> {$lang.count}
			&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;<b>{$lang.lab_online}</b><input type="text"   name="click_count" value="{$show.click_count}" size="10" /> {$lang.hits}
				<br />
            <span class="notice-span" id="noticeshowpoints">{$lang.notice_points}
            </td>
          </tr>

          <tr>
            <td class="label">{$lang.lab_picture}</td>
            <td>
              <input type="file" name="poster" id="poster" size="35" />
              {if $show.image}
                 <a href="show.php?act=show_image&img_url={$show.image}" target="_blank"><img src="images/yes.gif" border="0" /></a>
              {else}
                <img src="images/no.gif" />
              {/if}
			    <br /><input type="text" size="40" value="" style="color:#aaa;" onfocus="if (this.value == ''){this.value='http://';this.style.color='#000';}" name="poster_url" id="poster_url"/>
            </td>
          </tr>
		  <tr id="auto_thumb_1">
            <td class="label"> {$lang.lab_thumb}</td>
            <td id="auto_thumb_3">
              <input type="file" name="thumb" id="thumb" size="35" />
              {if $show.thumb}
                <a href="show.php?act=show_image&img_url={$show.thumb}" target="_blank"><img src="images/yes.gif" border="0" /></a>
              {else}
                <img src="images/no.gif" />
              {/if}
			   <br /><input type="text" size="40" value="" style="color:#aaa;" onfocus="if (this.value == ''){this.value='http://';this.style.color='#000';}" name="thumb_url" id="thumb_url"/>
              <br /><label for="auto_thumb"><input type="checkbox" id="auto_thumb" name="auto_thumb" checked="true" value="1" onclick="handleAutoThumb(this.checked)" />{$lang.auto_thumb}</label>
            </td>
          </tr>

<tr>
<td class="label">{$lang.lab_intro}</td>
<td>
<input type="radio" name="attribute" value="0" {if $show.attribute eq 0}checked="checked"{/if} />{$lang.none}
<input type="radio" name="attribute" value="1" {if $show.attribute eq 1}checked="checked"{/if} />{$lang.is_best}
<input type="radio" name="attribute" value="2" {if $show.attribute eq 2}checked="checked"{/if} />{$lang.is_hot}
<input type="radio" name="attribute" value="3" {if $show.attribute eq 3}checked="checked"{/if} />{$lang.is_series}
<input type="radio" name="attribute" value="4" {if $show.attribute eq 4}checked="checked"{/if} />{$lang.is_done}
</td>
</tr>
 </table>

 	<!-- 影片地址 -->
	<table width="100%" id="url-table" style="display:none">
	<tr>
	<td>
		<div id="playarea">
		{foreach from=$show.data item=data name='data'}
		<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" id="{$smarty.foreach.data.iteration}">
		  {if $smarty.foreach.data.first}
		  	<tr>
            <td class="label">{$lang.lab_player}</td>
            <td>  <select class="textCtrl"  name="player[]"  id="player{$smarty.foreach.data.iteration}">
            {html_options options=$player selected=$data.player}
                  </select>&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
				  <b>{$lang.lab_show_server}</b>
				  <select class="textCtrl"  name="server_id[]" id="server_id{$smarty.foreach.data.iteration}">
				{html_options options=$server selected=$data.server}
                  </select>
            </td>
          </tr>
		  <tr>
            <td class="label">{$lang.lab_show_url}<a href="javascript:showNotice('noticeurlmode');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
            <td>
			<input type="text"  name="thisurl" id="thisurl" size="40" />
			&nbsp;&nbsp;{$lang.lab_startnum}
			<label><input type="text"  name="startnum" id="startnum" value="1" size="4" /></label>
			{$lang.lab_endnum}
			<input type="text"   name="endnum" id="endnum" value="2" size="4" />{$lang.lab_rmvb}
			<input type="text"   name="skyuc_ext"  id="skyuc_ext" value=".rmvb" size="5" />
			<input name="submit2" type="button" class="button"   value="{$lang.lab_set}"  class="button submitButton" onclick="javascript:makeUrls();" />
			<input name="submit3" type="button" class="button"   class="button"  value="{$lang.regulate}"  onclick="javascript:regulate({$smarty.foreach.data.iteration});" />
			<br />
            <span class="notice-span" id="noticeurlmode">{$lang.notice_urlmode}
			</td>
		</tr>
		{else}
		<tr>
            <td class="label">{$lang.lab_show_url} ({$smarty.foreach.data.iteration})</td>
            <td>  <b>{$lang.lab_player}</b><select class="textCtrl"  name="player[]" id="player{$smarty.foreach.data.iteration}">
            {html_options options=$player selected=$data.player}
                  </select>&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
				  <b>{$lang.lab_show_server}</b>
				  <select class="textCtrl"  name="server_id[]" id="server_id{$smarty.foreach.data.iteration}">
				{html_options options=$server selected=$data.server}
                  </select>
				  &nbsp;&nbsp;<img src='images/btn_dec.gif' style="cursor: pointer;" title="{$lang.delplayurl}{$smarty.foreach.data.iteration}" align="absmiddle" onclick="RemoveData({$smarty.foreach.data.iteration})" />&nbsp;&nbsp;<font color='red'>＊</font></
            </td>
          </tr>
		{/if}
		<tr>
			<td colspan='2'> <textarea class="textCtrl" name="data[]" id="data{$smarty.foreach.data.iteration}" style='width:99%;height:150px;word-wrap: break-word;word-break:break-all;' >{$data.url}</textarea></td>
		</tr>
		{if $smarty.foreach.data.last}
		<tr id="btn_addpaly0">
			<td colspan='2'>&nbsp;<img src='images/btn_add.gif' style="cursor: pointer;" align='absmiddle' onClick="javascript:appendplay({$smarty.foreach.data.iteration})" title="{$lang.addplayurl}"/>&nbsp;&nbsp;<font color="red">{$lang.addendplay_desc}</font></td>
		</tr>
		{/if}
		</table>
		{/foreach}
	  </div>
	</td>
	</tr>
	 </table>
	<!-- 详细描述 -->
	<table width="100%" id="detail-table" style="display:none">
	  <tr>
		<td class="label">{$lang.lab_keywords}<a href="javascript:showNotice('notice_keywords');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
		<td><input type="text" name="keywords" value="{$show.keywords}" size="80"  /> <span class="notice-span" id="notice_keywords">{$lang.notice_keywords}</span></td>
	  </tr>
	  <tr>
		<td class="label">{$lang.lab_description}<a href="javascript:showNotice('notice_description');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a></td>
		<td><textarea class="textCtrl" name="description" cols="80" rows="3">{$show.description}</textarea>
		<span class="notice-span" id="notice_description">{$lang.notice_description}</span></td>
	  </tr>
		<tr>
            <td colspan="2">{$FCKeditor}</td>
          </tr>
	 </table>


        <div class="button-div">
          <input type="hidden" name="show_id" value="{$show.show_id}" />
          <input type="submit" class="button primary submitButton" value="{$lang.button_submit}" />
          <input type="reset" class="button submitButton" value="{$lang.button_reset}" />
        </div>
        <input type="hidden" name="act" value="{$form_act}" />
      </form>
    </div>
</div>
<!-- end show form -->
{insert_scripts files="skyuc_validator.js,skyuc_tab.js"}

<script language="JavaScript">
  var showId = '{$show.show_id}';

  onload = function()
  {
      if (document.getElementById('auto_thumb'))
      {
          handleAutoThumb(document.getElementById('auto_thumb').checked);
      }
      document.forms['theForm'].reset();
  }

  function handleAutoThumb(checked)
  {
      document.getElementById('thumb').disabled = checked;
  }


  function validate()
  {
      var validator = new Validator('theForm');
      validator.required('title', title_not_null);
      if (document.forms['theForm'].elements['cat_id'].value == 0)
      {
          validator.addErrorMsg(show_cat_not_null);
      }

      return validator.passed();
  }

function ChangeInput (objSelect,objInput, skyuc)
{
    if (!objInput) return;
    var str = objInput.value;
    var arr = str.split(skyuc);
    for (var i=0; i<arr.length; i++){
      if(objSelect.value==arr[i])return;
    }
    if(objInput.value =='' || objInput.value==0 || objSelect.value==0){
       objInput.value=objSelect.value
    }else{
       objInput.value += skyuc+objSelect.value
    }
}


/**
   * 添加扩展分类
   */
  function addOtherCat(conObj)
  {
      var sel = document.createElement('SELECT');
	  var selCat = document.forms['theForm'].elements['cat_id'];

      for (i = 0; i < selCat.length; i++)
      {
          var opt = document.createElement('OPTION');
          opt.text = selCat.options[i].text;
          opt.value = selCat.options[i].value;
          if (Browser.isIE)
          {
              sel.add(opt);
          }
          else
          {
              sel.appendChild(opt);
          }
      }
      conObj.appendChild(sel);
      sel.name = "other_cat[]";
      sel.onChange = function() {checkIsLeaf(this);};
  }



  /**
   * 添加影片地址
   */
function makeUrls()
{
	var str='';
	var txt = '';
	var prefix = document.forms['theForm'].elements['thisurl'].value;
	var ext = document.forms['theForm'].elements['skyuc_ext'].value;
	var startnum = document.forms['theForm'].elements['startnum'].value;
	var endnum = document.forms['theForm'].elements['endnum'].value;

	if(!startnum)
	{
		startnum=1;
	}

	for(i=startnum;i<=endnum;i++)
	{
	    if (endnum == 1)
		{
			filename = prefix+ext;
		}
		if (i<10)
		{
			txt = '{$lang.show_txt_pre}'+ '0'+i + '{$lang.show_txt_ext}';
			filename = prefix+'0'+i+ext;
		}
		else
		{
			txt = '{$lang.show_txt_pre}' + i + '{$lang.show_txt_ext}';
			filename = prefix+i+ext;
		}


		str+= txt+'@@'+filename+'\r';
	}

	document.getElementById('data1').value=str;
}
  /**
   * 添加一组播放地址
   */
function appendplay(i)
{
	var players='{html_options options=$player selected=$show.player}';
	var servers='{html_options options=$server selected=$show.server}';
	var n=i-1
	var m=i+1
    source="<table border='0' cellpadding='0' cellspacing='0' width='100%'><tr><td class='label'>&nbsp;{$lang.lab_show_url} ("+m+")</td> <td><b>{$lang.lab_player}</b>&nbsp;<select class='textCtrl'  id='player"+i+"' name='player[]'>"+players+"</select>&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;<b>{$lang.lab_show_server}</b><select class='textCtrl'  id='server_id"+i+"' name='server_id[]'>"+servers+"</select>&nbsp;<input name='submits"+m+"' type='button' class='button'  value='{$lang.regulate}'  onclick='javascript:regulate("+m+");' /></td></tr><tr><td colspan='2'><textarea class='textCtrl' name='data[]' id='data"+m+"' style='width:99%;height:150px;word-wrap: break-word;word-break:break-all;'></textarea></td></tr><tr id='btn_addpaly"+i+"'><td colspan='2'>&nbsp;<img src='images/btn_add.gif'  id='btn_addplay' onclick=javascript:appendplay("+m+") value='{$lang.addplayurl}"+n+"' style='cursor:pointer' title='{$lang.addplayurl}'>&nbsp;&nbsp;&nbsp;&nbsp;<img  src='images/btn_dec.gif' title='{$lang.delplayurl}' onclick='javascript:removeplay("+m+","+n+")' style='cursor:pointer' alt='{$lang.delplayurl}'></td></tr></table>"
	var playdiv=document.createElement("div");
		playdiv.id="skyucplaydiv"+m
		playdiv.innerHTML=source
	    $("#playarea").append(playdiv)
		$("#btn_addpaly"+n).css("display","none")

}
  /**
   * 移除一组播放地址
   */
function removeplay(m,n)
{
	$('#skyucplaydiv'+m).remove();
	$("#btn_addpaly"+n).css("display","")
}
function RemoveData(pid)
{
	if(confirm(delplayurl))
	{
		$('#'+pid).remove();
	}
}

function regulate(id)
{
		//var id=$(this).attr("id");
		var content=$("#data"+id).attr("value");
		if(content.length==0){alert(regulate_msg);return false;}
		if(Browser.isIE)
		{
			var contentarr=content.split("\r\n");
		}else
		{
			var contentarr=content.split("\n");
		}
		var newadd="";
		for(var i=0;i<contentarr.length;i++){
			if(contentarr[i].length>0){
				var videoadd=contentarr[i].split('@@');
				if(videoadd.length==1){
					contentarr[i]='{$lang.show_txt_pre}'+(i+1)+'{$lang.show_txt_ext}@@'+contentarr[i];
				}else if(videoadd.length==2){
					contentarr[i]='{$lang.show_txt_pre}'+(i+1)+'{$lang.show_txt_ext}@@'+videoadd[1];
				}
			}
			newadd+=contentarr[i]+"\r\n";
		}
		newadd=trim(newadd,"\r\n")
		$("#data"+id).attr("value",newadd);
	}
function trim(str,filter){
	var len
	len=filter.length;
	if(str.substr(0,len)==filter){
		str=str.substr(len)
		}
	if(str.substr(str.length-len)==filter){
		str=str.substr(0,str.length-len)
		}
	return str
}
//-->
</script>
{include file="pagefooter.tpl"}
