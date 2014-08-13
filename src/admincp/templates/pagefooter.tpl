<div id="footer">
{$query_info}{$gzip_enabled}{$memory_info}<br />
{$lang.copyright}
</div>
<script language="JavaScript">
<!--
document.onmousemove=function(e)
{
  var obj = Utils.srcElement(e);
  if (typeof(obj.onclick) == 'function' && obj.onclick.toString().indexOf('listTable.edit') != -1)
  {
    obj.title = '{$lang.span_edit_help}';
    obj.style.cssText = 'background: #278296;';
    obj.onmouseout = function(e)
    {
      this.style.cssText = '';
    }
  }
  else if (typeof(obj.href) != 'undefined' && obj.href.indexOf('listTable.sort') != -1)
  {
    obj.title = '{$lang.href_sort_help}';
  }
}

var MyTodolist;
function showTodoList(adminid)
{
  if(!MyTodolist)
  {
      if(this.readyState && this.readyState=="loading")return;
      var md5 = $import("clientscript/skyuc_md5.js","js");
      md5.onload = md5.onreadystatechange= function()
      {
        if(this.readyState && this.readyState=="loading")return;
        var todolist = $import("clientscript/skyuc_todolist.js","js");
        todolist.onload = todolist.onreadystatechange = function()
        {
          if(this.readyState && this.readyState=="loading")return;
          MyTodolist = new Todolist();
          MyTodolist.show();
        }
      }
  }
  else
  {
    if(MyTodolist.visibility)
    {
      MyTodolist.hide();
    }
    else
    {
      MyTodolist.show();
    }
  }
}

//-->
</script>
{insert_scripts files="skyuc_listdiv.js"}
</body>
</html>