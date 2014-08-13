{include file="pageheader.tpl"}
<div class="tab-div">
    <!-- tab bar -->
    <div id="tabbar-div">
      <p>
        <span class="tab-front" id="general-tab">{$lang.general-tab}</span>
		<span class="tab-back" id="url-tab">{$lang.url-tab}</span>
      </p>
    </div>
    <!-- tab body -->
    <div id="tabbody-div">
      <form  action="" method="post" name="theForm" onsubmit="return validate();">

	<table width="100%" id="general-table" align="center">
 		<tr>
            <td width="21%" height="24">{$lang.gathername}：</td>
            <td width="38%" ><input name="notename" type="text"  value="{$col.notename}" style="width:150px" >
          </td>
            <td width="43%">{$lang.charset}：
            <input type="radio" name="language" value="gb2312"
			{if $col.language eq "gb2312"} checked="checked" {/if}>GB2312
            <input type="radio" name="language" value="utf-8" {if $col.language eq "utf-8"}checked="checked" {/if}>UTF-8
            <input type="radio" name="language" value="big5" {if $col.language eq "big5"} checked="checked" {/if}>BIG5</td>
        </tr>
       <tr>
            <td>{$lang.split_type}：</td>
            <td> <input type="radio"  name="macthtype" value="regex" {if $col.macthtype neq "string"} checked="checked" {/if}/>
              {$lang.split_regex}
            <input name="macthtype" type="radio" value="string" {if $col.macthtype eq "string"} checked="checked" {/if}/>
              {$lang.split_string}
         	</td>
            <td> {$lang.cosort}：
          <input type="radio" name="cosort" value="asc" {if $col.cosort eq "asc"} checked="checked" {/if} />
             {$lang.cosort_asc}
             <input type="radio" name="cosort" value="desc" {if $col.cosort neq "asc"} checked="checked" {/if}/>
             {$lang.cosort_desc}</td>
       </tr>
    <tr>
      <td>{$lang.server}：</td>
      <td> <select class="textCtrl"  name="server_id">{html_options options=$server_list selected=$col.server_id}</select>
            </td>
      <td> {$lang.player}： <select class="textCtrl"  name="player">{html_options options=$player selected=$col.player}</select></td>
    </tr>
     <tr>
      <td>{$lang.cat_id}：</td>
      <td >  <select class="textCtrl"  name="cat_id" >
			<option value="0">{$lang.select_please}</option>
			{$cat_list}
			</select>{$lang.require_field}
          </td>
      <td >&nbsp;</td>
     </tr>
    <tr>
      <td  valign="top" >{$lang.generate_list}：<br>
        <font color="#666666">{$lang.generate_note}</font></td>
      <td colspan="2"> <table width="99%" border="0" cellspacing="1" cellpadding="3">
        <tr>
          <td>
            <input name="varurl" type="text" value="{$col.varurl}" style="width:500px" >
            </td>
          </tr>
        <tr>
          <td><font color="#666666">{$lang.generate_example}</font></td>
          </tr>
        <tr>
          <td>{$lang.varstart}
            <input name="varstart" type="text"  value="{$col.varstart}" style="width:30px">
            {$lang.varend}
            <input name="varend" type="text" value="{$col.varend}" style="width:30px" >
            {$lang.varpage}&nbsp;
            {$lang.addv}：
            <input type="text" name="addv" id="addv" style="width:30px" value="{$col.addv}" />
            </td>
          </tr>
      </table></td>
      </tr>

     <tr>
      <td  valign="top" >{$lang.manual_list}：<br>
        <font color="#666666">{$lang.manual_note}<br>
        <br>
        </font></td>
      <td colspan="2"> <textarea class="textCtrl" name="listurl" cols="72" rows="10">{$col.listurl}</textarea></td>
      </tr>
    <tr>
      <td  valign="top" >{$lang.pagerepad}：<br><font color="#666666"> {$lang.pagerepad_note}</font>

	  </td>
      <td colspan="2"> <table width="90%" border="0" cellspacing="1" cellpadding="3">
        <tr>
          <td> <textarea class="textCtrl" name="pagerepad" cols="60" rows="10" id="textarea">{$col.pagerepad}</textarea>
            </td>
          <td valign="top"><table width="90%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td><a href="#" onclick="AddRepAd(1,'{suc:trim}<iframe(.*)</iframe>{/suc}');">IFRAME</a></td>
              <td><a href="#" onclick="AddRepAd(1,'{suc:trim}<table(.*)>{/suc}{suc:trim}</table>{/suc}');">TABLE</a></td>
              <td><a href="#" onclick="AddRepAd(1,'{suc:trim}<form(.*)</form>{/suc}');">FORM</a></td>
              </tr>
            <tr>
              <td><a href="#" onclick="AddRepAd(1,'{suc:trim}<object(.*)</object>{/suc}');">OBJECT</a></td>
              <td><a href="#" onclick="AddRepAd(1,'{suc:trim}<tr(.*)>{/suc}{suc:trim}</tr>{/suc}');">TR</a></td>
              <td><a href="#" onclick="AddRepAd(1,'{suc:trim}<tbody(.*)>{/suc}{suc:trim}</tbody>{/suc}');">TBODY</a></td>
              </tr>
            <tr>
              <td><a href="#" onclick="AddRepAd(1,'{suc:trim}<script(.*)</script>{/suc}');">SCRIPT</a></td>
              <td><a href="#" onclick="AddRepAd(1,'{suc:trim}<td(.*)>{/suc}{suc:trim}</td>{/suc}');">TD</a></td>
              <td><a href="#" onclick="AddRepAd(1,'{suc:trim}<ul(.*)>{/suc}{suc:trim}</ul>{/suc}');">UL</a></td>
              </tr>
            <tr>
              <td><a href="#" onclick="AddRepAd(1,'{suc:trim}<style(.*)</style>{/suc}');">STYLE</a></td>
              <td><a href="#" onclick="AddRepAd(1,'{suc:trim}<a(.*)>{/suc}{suc:trim}</a>{/suc}');">A</a></td>
              <td><a href="#" onclick="AddRepAd(1,'{suc:trim}<li(.*)>{/suc}{suc:trim}</li>{/suc}');">LI</a></td>
              </tr>
            <tr>
              <td><a href="#" onclick="AddRepAd(1,'{suc:trim}<div(.*)>{/suc}{suc:trim}</div>{/suc}');">DIV</a></td>
              <td><a href="#" onclick="AddRepAd(1,'{suc:trim}<font(.*)>{/suc}{suc:trim}</font>{/suc}');">FONT</a></td>
              <td><a href="#" onclick="AddRepAd(1,'{suc:trim}<dl(.*)>{/suc}{suc:trim}</dl>{/suc}');">DL</a></td>
              </tr>
            <tr>
              <td><a href="#" onclick="AddRepAd(1,'{suc:trim}<span(.*)>{/suc}{suc:trim}</span>{/suc}');">SPAN</a></td>
              <td><a href="#" onclick="AddRepAd(1,'{suc:trim}<img(.*)>{/suc}');">IMG</a></td>
              <td><a href="#" onclick="AddRepAd(1,'{suc:trim}<dd(.*)>{/suc}{suc:trim}</dd>{/suc}');">DD</a></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
    </tr>
    </table>
	<table width="100%" id="url-table"  style="display:none">
	<tr>
     <td width="20%" valign="top" >{$lang.detail_list}：
     <td width="80%" colspan="2">  <font color="#666666">{$lang.detail_note}</font></td>
	</tr>
	  <tr>
      <td valign="top">&nbsp;</td>
      <td colspan="2" > <textarea class="textCtrl" name="linkarea" cols="79"  rows="3" >{$col.linkarea}</textarea></td>
    </tr>
	<tr>
	  <td height="24">{$lang.detail_url}：</td>
	  <td colspan="2">
			{$lang.detail_need}：
			<input name="need" type="text"  size="15" style="width:150px" value="{$col.need}" >
		  　{$lang.detail_cannot}：
		  <input name="cannot" type="text" size="15" style="width:150px" value="{$col.cannot}" >
	 </td>
     </tr>
	<tr>
    <td  valign="top">&nbsp;</td>
    <td>{$lang.note_rule}</td>
    <td>{$lang.note_rule_filter}</td>
  </tr>
   <tr>
    <td width="100%" colspan="3"><a href="javascript:showNotice('notice');" title="{$lang.form_notice}"><img src="images/notice.gif" width="16" height="16" border="0" alt="{$lang.form_notice}"></a><span class="notice-span" id="notice">{$lang.notice}</span></td>
    </tr>
  <tr>
    <td  valign="top"><strong>{$lang.title}：</strong></td>
    <td><textarea class="textCtrl" name="title" cols="40"  rows="3" >{$col.title|escape:html}</textarea></td>
    <td><textarea class="textCtrl" name="title_trim" id="title_trim" cols="40"  rows="3" >{$col.title_trim|escape:html}</textarea><input type="button" class="button"   value="{$lang.selectrule}" onclick="SelTrim('title_trim')" /></td>
  </tr>

  <tr>
    <td  valign="top"><strong>{$lang.actor}：</strong><br></td>
    <td><textarea class="textCtrl" name="actor" cols="40"  rows="3" >{$col.actor|escape:html}</textarea></td>
    <td><textarea class="textCtrl" name="actor_trim" id="actor_trim" cols="40"  rows="3" >{$col.actor_trim|escape:html}</textarea><input type="button" class="button"   value="{$lang.selectrule}" onclick="SelTrim('actor_trim')" /></td>
  </tr>
    <tr>
    <td  valign="top"><strong>{$lang.director}：</strong><br></td>
    <td><textarea class="textCtrl" name="director" cols="40"  rows="3" >{$col.director|escape:html}</textarea></td>
    <td><textarea class="textCtrl" name="director_trim" id="director_trim" cols="40"  rows="3" >{$col.director_trim|escape:html}</textarea><input type="button" class="button"   value="{$lang.selectrule}" onclick="SelTrim('director_trim')" /></td>
  </tr>
 <tr>
    <td  valign="top"><strong>{$lang.image}：</strong><br></td>
    <td><input name="savepic" type="checkbox" value="1"{if $col.savepic eq 1}checked="checked"{/if}>
        {$lang.savepic}
    <td>
 </tr>
   <tr>
      <td></td>
      <td><textarea class="textCtrl" name="image" cols="40"  rows="3" >{$col.image|escape:html}</textarea></td>
      <td><textarea class="textCtrl" name="image_trim" id="image_trim" cols="40"  rows="3" >{$col.image_trim|escape:html}</textarea><input type="button" class="button"   value="{$lang.selectrule}" onclick="SelTrim('image_trim')" /></td>
    </tr>
  <tr>
    <td  valign="top"><strong>{$lang.pubdate}：</strong><br></td>
    <td><textarea class="textCtrl" name="pubdate" cols="40"  rows="3" >{$col.pubdate|escape:html}</textarea></td>
    <td><textarea class="textCtrl" name="pubdate_trim" id="pubdate_trim" cols="40"  rows="3">{$col.pubdate_trim|escape:html}</textarea><input type="button" class="button"   value="{$lang.selectrule}" onclick="SelTrim('pubdate_trim')" /></td>
  </tr>
    <tr>
    <td  valign="top"><strong>{$lang.status}：</strong><br></td>
    <td><textarea class="textCtrl" name="status" cols="40"  rows="3" >{$col.status|escape:html}</textarea></td>
    <td><textarea class="textCtrl" name="status_trim" id="pubdate_trim" cols="40"  rows="3">{$col.status_trim|escape:html}</textarea><input type="button" class="button"   value="{$lang.selectrule}" onclick="SelTrim('status_trim')" /></td>
  </tr>
  <tr>
    <td  valign="top"><strong>{$lang.area}：</strong><br></td>
    <td><textarea class="textCtrl" name="area" cols="40"  rows="3">{$col.area|escape:html}</textarea></td>
    <td><textarea class="textCtrl" name="area_trim" id="area_trim" cols="40"  rows="3" >{$col.area_trim|escape:html}</textarea><input type="button" class="button"   value="{$lang.selectrule}" onclick="SelTrim('area_trim')" /></td>
  </tr>
    <tr>
    <td  valign="top"><strong>{$lang.lang}：</strong><br></td>
    <td><textarea class="textCtrl" name="lang" cols="40"  rows="3">{$col.lang|escape:html}</textarea></td>
    <td><textarea class="textCtrl" name="lang_trim" cols="40"  rows="3" >{$col.lang_trim|escape:html}</textarea><input type="button" class="button"   value="{$lang.selectrule}" onclick="SelTrim('area_trim')" /></td>
  </tr>
    <tr>
    <td  valign="top"><strong>{$lang.detail}：</strong><br></td>
    <td><textarea class="textCtrl" name="detail" cols="40"  rows="3">{$col.detail|escape:html}</textarea></td>
    <td><textarea class="textCtrl" name="detail_trim" id="detail_trim" cols="40"  rows="3" >{$col.detail_trim|escape:html}</textarea><input type="button" class="button"   value="{$lang.selectrule}" onclick="SelTrim('detail_trim')" /></td>
  </tr>
  <tr>
    <td  valign="top"><strong>{$lang.show_url}：</strong><br></td>
    <td><input name="runphp" type="radio" value="0"  onclick="JavaScript:document.getElementById('runphp_true').style.display='none';document.getElementById('runphp_false').style.display=(Browser.isIE) ? 'block' : '';"
    {if $col.runphp eq 0} checked="checked"{/if} />{$lang.runphp_false}<input name="runphp" type="radio" value="1"  onclick="JavaScript:document.getElementById('runphp_true').style.display=(Browser.isIE) ? 'block' : '';document.getElementById('runphp_false').style.display='none';"
    {if $col.runphp eq 1} checked="checked"{/if} />{$lang.runphp_true}</td>
    <td></td>
  </tr>
   <tr id="runphp_false" {if $col.runphp eq 1}style="display:none"{/if}>
    <td  valign="top"></td>
    <td><textarea class="textCtrl" name="url" cols="40"  rows="3" >{$col.url|escape:html}</textarea></td>
    <td><textarea class="textCtrl" name="url_trim" id="url_trim" cols="40"  rows="3">{$col.url_trim|escape:html}</textarea><input type="button" class="button"   value="{$lang.selectrule}" onclick="SelTrim('url_trim')" /></td>
  </tr>
    <tr id="runphp_true" {if $col.runphp eq 0}style="display:none"{/if}>
    <td  valign="top">{$lang.runphp}：<br>{$lang.runphp_desc}</td>
    <td colspan="2"><textarea class="textCtrl" name="runphp_code" cols="100"  rows="10" >{$col.runphp_code|escape:html}</textarea></td>
  </tr>
  </table>

   <div class="button-div">
        <input type="hidden" name="act" value="{$form_act}" />
		  <input type="hidden" name="nid" value="{$nid}" />
          <input type="submit" class="button primary submitButton" value="{$lang.button_submit}"  />
          <input type="reset" class="button submitButton"  value="{$lang.button_reset}"  />
   </div>

</form>
    </div>
</div>
{insert_scripts files="skyuc_validator.js,skyuc_tab.js"}
<script>
function AddRepAd(obj,val){
	var dh='';
	if(obj==1)
	{
		if(document.theForm.pagerepad.value!='')
		{
			dh="\r\n";
		}
		document.theForm.pagerepad.value+=dh+val;
	}
	else
	{
		if(document.theForm.repad.value!='')
		{
			dh="\r\n";
		}
		document.theForm.repad.value+=dh+val;
	}
}

function Nav(){
	if(window.navigator.userAgent.indexOf("MSIE")>=1) return 'IE';
  else if(window.navigator.userAgent.indexOf("Firefox")>=1) return 'FF';
  else return "OT";
}
function SelTrim(selfield)
{
	var tagobj = $(selfield);
	if(Nav()=='IE'){ var posLeft = window.event.clientX-200; var posTop = window.event.clientY; }
      else{ var posLeft = 100;var posTop = 100; }
	window.open("templates/col_trimrule.html?"+selfield, "coRule", "scrollbars=no,resizable=yes,statebar=no,width=320,height=180,left="+posLeft+", top="+posTop);
}
</script>
{include file="pagefooter.tpl"}
