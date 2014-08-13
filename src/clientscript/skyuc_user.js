/* *
 * 充值卡信息
 */
function card()
{
  var frm = document.forms['formEdit'];
  var cardid = frm.elements['cardid'].value;
  var cardpwd = frm.elements['cardpwd'].value;
  var msg = '';

  if (cardid.length == 0)
  {
    msg += cardid_empty + '\n';
  }
  if (cardpwd.length == 0)
  {
    msg += cardpwd_empty + '\n';
  }
  if (msg.length > 0)
  {
    alert(msg);
    return false;
  }
  else
  {
    return true;
  }
}

/* *
 * 修改会员信息
 */
function userEdit()
{
  var frm = document.forms['formEdit'];
  var email = frm.elements['email'].value;
  var msg = '';
  var reg = null;

  if (email.length == 0)
  {
    msg += email_empty + '\n';
  }
  else
  {
    if ( ! (Utils.isEmail(email)))
    {
      msg += email_error + '\n';
    }
  }

  if (msg.length > 0)
  {
    alert(msg);
    return false;
  }
  else
  {
    return true;
  }
}

/* 会员修改密码 */
function editPassword()
{
  var frm              = document.forms['formPassword'];
  var old_password     = frm.elements['old_password'].value;
  var new_password     = frm.elements['new_password'].value;
  var confirm_password = frm.elements['comfirm_password'].value;

  var msg = '';
  var reg = null;

  if (old_password.length == 0)
  {
    msg += old_password_empty + '\n';
  }

  if (new_password.length == 0)
  {
    msg += new_password_empty + '\n';
  }

  if (confirm_password.length == 0)
  {
    msg += confirm_password_empty + '\n';
  }

  if (new_password.length > 0 && confirm_password.length > 0)
  {
    if (new_password != confirm_password)
    {
      msg += both_password_error + '\n';
    }
  }

  if (msg.length > 0)
  {
    alert(msg);
    return false;
  }
  else
  {
    return true;
  }
}

/* *
 * 对会员的留言输入作处理
 */
function submitMsg()
{
  var frm         = document.forms['formMsg'];
  var msg_title   = frm.elements['msg_title'].value;
  var msg_content = frm.elements['msg_content'].value;
  var msg = '';

  if (msg_title.length == 0)
  {
    msg += msg_title_empty + '\n';
  }
  if (msg_content.length == 0)
  {
    msg += msg_content_empty + '\n'
  }
  if (msg.length > 0)
  {
    alert(msg);
    return false;
  }
  else
  {
    return true;
  }
}


function chkstr(str)
{
  for (var i = 0; i < str.length; i++)
  {
    if (str.charCodeAt(i) < 127 && !str.substr(i,1).match(/^\w+$/ig))
    {
      return false;
    }
  }
  return true;
}



/* *
 * 会员余额申请
 */
function submitSurplus()
{
  var frm            = document.forms['formSurplus'];
  var surplus_type   = frm.elements['surplus_type'].value;
  var surplus_amount = frm.elements['amount'].value;
  var process_notic  = frm.elements['user_note'].value;
  var payment_id     = 0;
  var msg = '';

  if (surplus_amount.length == 0 )
  {
    msg += surplus_amount_empty + "\n";
  }
  else
  {
    var reg = /^[\.0-9]+/;
    if ( ! reg.test(surplus_amount))
    {
      msg += surplus_amount_error + '\n';
    }
  }

  if (process_notic.length == 0)
  {
    msg += process_desc + "\n";
  }

  if (msg.length > 0)
  {
    alert(msg);
    return false;
  }

  if (surplus_type == 0)
  {
    for (i = 0; i < frm.elements.length ; i ++)
    {
      if (frm.elements[i].name=="payment_id" && frm.elements[i].checked)
      {
        payment_id = frm.elements[i].value;
        break;
      }
    }

    if (payment_id == 0)
    {
      alert(payment_empty);
      return false;
    }
  }

  return true;
}



var selectedSurplus  = '';
var selectedIntegral = 0;

/* *
 * 改变余额
 */
function changeSurplus(val)
{
  if (selectedSurplus == val)
  {
    return;
  }
  else
  {
    selectedSurplus = val;
  }

  Ajax.call('user.php?act=check_surplus', 'surplus=' + val, changeSurplusResponse, 'GET', 'JSON');
}

/* *
 * 改变余额回调函数
 */
function changeSurplusResponse(obj)
{
  if (obj.error)
  {
    try
    {
      document.getElementById("SKYUC_SURPLUS_NOTICE").innerHTML = obj.error;
      document.getElementById('SKYUC_SURPLUS').value = '0';
      document.getElementById('SKYUC_SURPLUS').focus();
    }
    catch (ex) { }
  }
  else
  {
    try
    {
      document.getElementById("SKYUC_SURPLUS_NOTICE").innerHTML = '';
    }
    catch (ex) { }
    
  }
}

/* *
 * 改变积分
 */
function changeIntegral(val)
{
  if (selectedIntegral == val)
  {
    return;
  }
  else
  {
    selectedIntegral = val;
  }

  Ajax.call('user.php?act=check_integral', 'points=' + val, changeIntegralResponse, 'GET', 'JSON');
}

/* *
 * 改变积分回调函数
 */
function changeIntegralResponse(obj)
{
  if (obj.error)
  {
    try
    {
      document.getElementById('SKYUC_INTEGRAL_NOTICE').innerHTML = obj.error;
      document.getElementById('SKYUC_INTEGRAL').value = '0';
      document.getElementById('SKYUC_INTEGRAL').focus();
    }
    catch (ex) { }
  }
  else
  {
    try
    {
      document.getElementById('SKYUC_INTEGRAL_NOTICE').innerHTML = '';
    }
    catch (ex) { }

  }
}

/* *
 * 检查提交的订单表单
 */
function checkOrderForm(frm)
{
  var paymentSelected = false;
    // 检查是否选择了支付方式
  for (i = 0; i < frm.elements.length; i ++ )
  {
    if (frm.elements[i].name == 'payment' && frm.elements[i].checked)
    {
      paymentSelected = true;
    }
  }

  if ( ! paymentSelected)
  {
    alert(flow_no_payment);
    return false;
  }


  return true;
}

/* *
 * 检测密码强度
 * @param       string     pwd     密码
 */
function checkIntensity(pwd)
{
  var Mcolor = "#FFF",Lcolor = "#FFF",Hcolor = "#FFF";
  var m=0;

  var Modes = 0;
  for (i=0; i<pwd.length; i++)
  {
    var charType = 0;
    var t = pwd.charCodeAt(i);
    if (t>=48 && t <=57)
    {
      charType = 1;
    }
    else if (t>=65 && t <=90)
    {
      charType = 2;
    }
    else if (t>=97 && t <=122)
      charType = 4;
    else
      charType = 4;
    Modes |= charType;
  }

  for (i=0;i<4;i++)
  {
    if (Modes & 1) m++;
      Modes>>>=1;
  }

  if (pwd.length<=4)
  {
    m = 1;
  }

  switch(m)
  {
    case 1 :
      Lcolor = "2px solid red";
      Mcolor = Hcolor = "2px solid #DADADA";
    break;
    case 2 :
      Mcolor = "2px solid #f90";
      Lcolor = Hcolor = "2px solid #DADADA";
    break;
    case 3 :
      Hcolor = "2px solid #3c0";
      Lcolor = Mcolor = "2px solid #DADADA";
    break;
    case 4 :
      Hcolor = "2px solid #3c0";
      Lcolor = Mcolor = "2px solid #DADADA";
    break;
    default :
      Hcolor = Mcolor = Lcolor = "";
    break;
  }
  document.getElementById("pwd_lower").style.borderBottom  = Lcolor;
  document.getElementById("pwd_middle").style.borderBottom = Mcolor;
  document.getElementById("pwd_high").style.borderBottom   = Hcolor;

}

//忘记密码
//页面需要加一个form表单，在input后面加一个<span id="errinfo"></span>,input的id写为email
function sendmail()
{
	if (Utils.isEmpty(document.getElementById('user_name').value))
	{
		document.getElementById('errinfo_name').innerHTML = user_name_empty;
		return false;
		document.getElementById('user_name').focus();
	}
	if (Utils.isEmpty(document.getElementById('email').value))
	{
		document.getElementById('errinfo').innerHTML = email_address_empty;
		document.getElementById('email').focus();
	}
	if(Utils.isEmail(document.getElementById('email').value))
	{
		document.frm_sdmail.submit();
	}else
	{
		document.getElementById('errinfo').innerHTML = email_address_error;
		return false;
		document.getElementById('email').focus();
	}
}
/* *
 * 重设密码
 */
function resetpwd()
{  
	var password = document.getElementById('new_password').value;
	var confirm_password = document.getElementById('confirm_password').value;
	if (Utils.isEmpty(password))
	{
		document.getElementById('errinfo_newpwd').innerHTML = new_password_empty;
		return false;
		document.getElementById('new_password').focus();
	}
	if (Utils.isEmpty(confirm_password))
	{
		document.getElementById('errinfo').innerHTML = confirm_password_empty;
		return false;
		document.getElementById('confirm_password').focus();
	}
	if (confirm_password != password)
	{  
		document.getElementById('errinfo').innerHTML = both_password_error;
	    alert(both_password_error + '\n');
	}
	else
	{
		document.frm_resetpwd.submit();
	}
}

/* *
 * 处理注册用户
 */

function chk_frm(_Sid){
	var imgstr='<img src="data/images/dot_gou.gif" />';
	var _s = '_notice';
	var data = Utils.trim(document.getElementById(_Sid).value);
	switch (_Sid){
		case 'email':
			if (Utils.isEmpty(data))
			{
			    document.getElementById(_Sid+_s).className = 'redbox';
				document.getElementById(_Sid+_s).innerHTML  = msg_email_blank;
			}
			else if (!Utils.isEmail(data))
			{
				document.getElementById(_Sid+_s).className = 'redbox';
			    document.getElementById(_Sid+_s).innerHTML  = msg_email_format;
			}
			else
			{	
				Ajax.call( 'user.php?act=check_email', 'email=' + data, check_email_callback , 'GET', 'TEXT', true, true );
			}
		break;
		case 'password':
			if (Utils.isEmpty(data) || data.length < 6 )
			{
			   document.getElementById(_Sid+_s).className = 'redbox';
				document.getElementById(_Sid+_s).innerHTML = password_shorter;
			}
			else
			{
				document.getElementById(_Sid+_s).className = 'greebox';
				document.getElementById(_Sid+_s).innerHTML = imgstr + msg_can_rg;
			}
			break;
		case 'conform_password':
			  if (Utils.isEmpty(data) || data.length < 6 )
				{
					document.getElementById(_Sid+_s).className = 'redbox';
					document.getElementById(_Sid+_s).innerHTML = password_shorter;
				}
				else if ( data != Utils.trim(document.getElementById('password').value) )
				{
					document.getElementById(_Sid+_s).className = 'redbox';
					document.getElementById(_Sid+_s).innerHTML = confirm_password_invalid;
				}
				else
				{
					document.getElementById(_Sid+_s).className = 'greebox';
					document.getElementById(_Sid+_s).innerHTML = imgstr + msg_can_rg;
				}
			break;
		case 'username':
			if (Utils.isEmpty(data))
			{
			    document.getElementById(_Sid+_s).className = 'redbox';
			    document.getElementById(_Sid+_s).innerHTML = msg_un_blank;
			}
			else if ( !chkstr(data) )
			{ 
				document.getElementById(_Sid+_s).className = 'redbox';
				document.getElementById(_Sid+_s).innerHTML = msg_un_format;
			}
			else if (data.length < 3 )
			{
				document.getElementById(_Sid+_s).className = 'redbox';
				document.getElementById(_Sid+_s).innerHTML = username_shorter;
			}
			else if (data.length > 14 )
			{
				document.getElementById(_Sid+_s).className = 'redbox';
				document.getElementById(_Sid+_s).innerHTML = msg_un_length;
			}
			else{
				Ajax.call( 'user.php?act=is_registered', 'username=' + data, registed_callback , 'GET', 'TEXT', true, true );
			}
			break;
		case 'verifycode':
			if(Utils.DataLength(data) == 6)
			{
				document.getElementById(_Sid+_s).innerHTML =  imgstr;
			}
			else
			{
				document.getElementById(_Sid+_s).className = 'redbox';
				document.getElementById(_Sid+_s).innerHTML = msg_invalid;
			}

		    break;
		case 'other[msn]':
			  if (data.length > 0 && (!Utils.isEmail(data)))
			  {
				document.getElementById(_Sid+_s).className = 'redbox';
				document.getElementById(_Sid+_s).innerHTML = msn_invalid;
			  }
			break;
		case 'other[qq]':
			  if (data.length > 0 && (!Utils.isNumber(data)))
			  {
				document.getElementById(_Sid+_s).className = 'redbox';
				document.getElementById(_Sid+_s).innerHTML = qq_invalid;
			  }
			break;
		case 'other[phone]':
			  if (data.length>0)
			  {
				if (!Utils.isTel(data))
				{
				  document.getElementById(_Sid+_s).className = 'redbox';
				  document.getElementById(_Sid+_s).innerHTML = phone_invalid;
				}
			  }
			break;
	}
}

function registed_callback(result)
{
  var imgstr='<img src="data/images/dot_gou.gif" />';
  if ( result == "true" )
  {
	  document.getElementById('username_notice').className = 'greenbox';
	  document.getElementById('username_notice').innerHTML = imgstr + msg_can_rg;
  }
  else
  {
	  document.getElementById('username_notice').className = 'redbox';
	  document.getElementById('username_notice').innerHTML = msg_un_registered;
  }
}

function check_email_callback(result)
{
  var imgstr='<img src="data/images/dot_gou.gif" />';
  if ( result == "true" )
  {
	document.getElementById('email_notice').className = 'greenbox';
    document.getElementById('email_notice').innerHTML = imgstr + msg_can_rg;
  }
  else
  {
	document.getElementById('email_notice').className = 'redbox';
    document.getElementById('email_notice').innerHTML = msg_email_registered;
  }
}

function chek_reg()
{
	if(!Utils.isEmail(Utils.trim(document.getElementById('email').value))||Utils.isEmpty(Utils.trim(document.getElementById('email').value)))
	{
		chk_frm('email');
		document.getElementById('email').focus();
		return false;
	}else if(Utils.isEmpty(Utils.trim(document.getElementById('password').value)))
	{
		chk_frm('password');
		document.getElementById('password').focus();
		return false;
	}else if(Utils.isEmpty(Utils.trim(document.getElementById('conform_password').value)) || Utils.trim(document.getElementById('password').value)!=Utils.trim(document.getElementById('conform_password').value))
	{
		chk_frm('conform_password');
		document.getElementById('conform_password').focus();
		return false;
	}else if(Utils.isEmpty(Utils.trim(document.getElementById('username').value)))
	{
		chk_frm('username');
		document.getElementById('username').focus();
		return false;
	}else if(Utils.DataLength(Utils.trim(document.getElementById('verifycode').value))!=6)
	{
		chk_frm('verifycode');
		document.getElementById('verifycode').focus();
		return false;
	}else
	{
		return true;
		//document.frm_reg.submit();
	}
}

function check_login(frm)
{
	if(Utils.isEmpty(Utils.trim(frm.username.value)))
	{
		document.getElementById('username').focus();
		return false;
		alert(username_empty);
	}
	else if(Utils.isEmpty(Utils.trim(frm.password.value)))
	{
		document.getElementById('password').focus();
		return false;
		 alert(password_empty);
	}
	else
	{
		return true;
	}
}