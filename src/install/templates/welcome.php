<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php echo $lang['welcome_title'];?></title>
<link href="styles/general.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="./js/common.js"></script>
<script type="text/javascript" src="./js/welcome.js"></script>
</head>
<body id="welcome">
<?php include DIR . '/install/templates/header.php';?>
<form method="post">
<table border="0" cellpadding="0" cellspacing="0" style="margin:0 auto;">
<tr>
<td valign="top"><div id="wrapper" style="padding:10px 0;">
  <iframe id="iframe" src="templates/license_<?php echo $installer_lang;?>.htm" width="730" height="350"></iframe>
</div></td>
<td width="227" valign="top" background="images/install-step1-<?php echo $installer_lang;?>.gif">&nbsp;</td>
</tr>
<tr>
<td align="center" style="padding-top:10px;"><input type="checkbox" id="js-agree" class="p" />
  <label for="js-agree"> <?php echo $lang['agree_license'];?></label></td>
<td>&nbsp;</td>
</tr>
</table>
<div id="copyright">
    <div id="copyright-inside">
      <span id="install-btn"><input type="submit" id="js-submit" class="p" value="<?php echo $lang['next_step'];?><?php echo $lang['check_system_environment'];?>" /></span>
     <?php include DIR . '/install/templates/copyright.php';?></div>
</div>
</form>
</body>
</html>