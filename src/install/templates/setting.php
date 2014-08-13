<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php echo $lang['setting_title'];?></title>
<link href="styles/general.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../clientscript/skyuc_global.js"></script>
<script type="text/javascript" src="js/common.js"></script>
<script type="text/javascript" src="js/draggable.js"></script>
<script type="text/javascript" src="js/setting.js"></script>
<script type="text/javascript">
var $_LANG = {};
<?php foreach($lang['js_languages'] as $key => $item): ?>
$_LANG["<?php echo $key;?>"] = "<?php echo $item;?>";
<?php endforeach; ?>
</script>
</head>
<body id="checking">
<?php include DIR . '/install/templates/header.php';?>
<form id="js-setting">

<table border="0" cellpadding="0" cellspacing="0" style="margin:0 auto;">
<tr>
<td valign="top">
<div id="wrapper">
  <h3><?php echo $lang['db_account'];?></h3>

<table width="450" class="list">
<tr>
	<td width="90" align="left"><?php echo $lang['db_host'];?></td>
	<td align="left"><input type="text" name="js-db-host"  value="localhost" /></td>
</tr>
<tr>
	<td width="90" align="left"><?php echo $lang['db_port'];?></td>
	<td align="left"><input type="text" name="js-db-port"  value="3306" /></td>
</tr>
<tr>
	<td width="90" align="left"><?php echo $lang['db_user'];?></td>
	<td align="left"><input type="text" name="js-db-user"  value="root" /></td>
</tr>
<tr>
	<td width="90" align="left"><?php echo $lang['db_pass'];?></td>
	<td align="left"><input type="password" name="js-db-pass"  value="" /></td>
</tr>
<tr>
	<td width="90" align="left"><?php echo $lang['db_name'];?></td>
	<td align="left"><input type="text" name="js-db-name"  value="" />
        <select name="js-db-list">
            <option><?php echo $lang['db_list'];?></option>
        </select>
        <input type="button" name="js-go" class="p" value="<?php echo $lang['go'];?>" />
   </td>
</tr>
<tr>
	<td width="90" align="left"><?php echo $lang['db_prefix'];?></td>
	<td align="left"><input type="text" name="js-db-prefix"  value="skyuc_" /></td>
</tr>
<tr>
	<td width="90" align="left"><?php echo $lang['database'];?></td>
	<td align="left">
	<input type="radio" class="p" name="js-db-database"  value="mysqli" <?php echo $mysqli_checked; ?>/><label>MySQLi</label>
	<input type="radio" name="js-db-database" value="mysql" <?php echo $mysql_checked; ?>/><label>MySQL</label>
	</td>
</tr>
</table>
<div id="js-monitor" style="display:none;text-align:left;position:absolute;top:45%;left:35%;width:300px;z-index:1000;border:1px solid #000;">
    <h3 id="js-monitor-title"><?php echo $lang['monitor_title'];?></h3>
    <div style="background:#fff;padding-bottom:20px;">
        <img id="js-monitor-loading" src='images/loading.gif' /><br /><br />
        <strong id="js-monitor-wait-please" style='color:blue;width:65%;float:left;margin-left:3px;'></strong>
        <span id="js-monitor-view-detail" style="color:gray;cursor:pointer;;float:right;margin-right:3px;"></span>
    </div>
    <iframe id="js-monitor-notice" src="templates/notice.htm" style="display:none;"></iframe>
    <img id="js-monitor-close" src='./images/close.gif' style="position:absolute;top:10px;right:10px;cursor:pointer;" />
</div>
<h3><?php echo $lang['admin_account'];?></h3>
<table width="450" class="list">
<tr>
	<td width="90" align="left"><?php echo $lang['admin_name'];?></td>
	<td align="left"><input type="text" name="js-admin-name"  value="" /></td>
</tr>
<tr>
	<td width="90" align="left"><?php echo $lang['admin_password'];?></td>
	<td align="left"><input type="password" name="js-admin-password"  value="" /></td>
</tr>
<tr>
	<td width="90" align="left"><?php echo $lang['admin_password2'];?></td>
	<td align="left"><input type="password" name="js-admin-password2"  value="" /></td>
</tr>
<tr>
	<td width="90" align="left"><?php echo $lang['admin_email'];?></td>
	<td align="left"><input type="text" name="js-admin-email"  value="" /></td>
</tr>
</table>

<h3><?php echo $lang['mix_options'];?></h3>
<table width="450" class="list">
<tr>
	<td width="90" align="left"><?php echo $lang['select_lang_package'];?></td>
	<td align="left"><input type="radio" class="p" name="js-system-lang" id="js-system-lang-zh_cn" value="zh_cn" /><label for="js-system-lang-zh_cn"><?php echo $lang['simplified_chinese'];?></label>
	<input type="radio" name="js-system-lang" id="js-system-lang-zh_tw" value="zh_tw" /><label for="js-system-lang-zh_tw"><?php echo $lang['traditional_chinese'];?></label>
	</td>
</tr>
<tr>
	<td width="90" align="left"><?php echo $lang['disable_captcha'];?></td>
	<td align="left"><input type="checkbox" class="p" name="js-disable-captcha" <?php echo $checked . $disabled;?> /> <span class="comment"> (<?php echo $lang['captcha_notice'];?>)</span></td>
</tr>
<tr>
	<td align="center" colspan="2">
	 </td>
</tr>
</table>

</div>
</td>
<td width="227" valign="top" background="images/install-step3-<?php echo $installer_lang;?>.gif">&nbsp;</td>
</tr>
</table>
<div id="copyright">
    <div id="copyright-inside">
      <span id="install-btn"><input type="button" id="js-pre-step" class="p" value="<?php echo $lang['prev_step'] . $lang['check_system_environment'];?>" />
        <input id="js-install-at-once" type="submit" class="p" value="<?php echo $lang['install_at_once'];?>" /></span>
      <?php include DIR . '/install/templates/copyright.php';?></div>
</div>
</form>
</body>
</html>