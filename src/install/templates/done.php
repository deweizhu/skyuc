<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $lang['install_done_title'];?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="styles/general.css" rel="stylesheet" type="text/css" />
</head>

<body style="background:#DDEEF2">
<div id="logos">
  <div id="logos-inside"> <img src="../admincp/images/logo.gif" alt="SKYUC PHP" width="160" height="57" /> </div>
</div>

<div style="margin:10px;padding:20px;border: 1px solid #BBDDE5; background: #F4FAFB; ">

  <p style="font-size: 14px; text-align: center"><?php echo $lang['done'];?></p>
  <div align="center">
  <ul style="text-align:left; width: 260px">
    <li><a href="../"><?php echo $lang['go_to_view_my_skyuc'];?></a></li>
    <li><a href="../admincp"><?php echo $lang['go_to_view_control_panel'];?></a></li>
  </ul>
  </div>
</div>
<div style="padding: 1em; border: 1px solid #BBDDE5; background: #F4FAFB; margin-top: 10px; text-align: center;">
<?php include DIR . '/install/templates/copyright.php';?>
</div>
</body>
</html>