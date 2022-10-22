<?php
session_start();
if (!isset($_SESSION['username']))
{
   $accessdenied_page = '';
   header('Location: '.$accessdenied_page);
   exit;
}
$database = './usersdb.php';
if (filesize($database) == 0)
{
   die('User database not found!');
}
$error_message = '';
$db_username = '';
$db_fullname = '';
$db_email = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_name']) && $_POST['form_name'] == 'editprofileform')
{
   $success_page = '';
   $oldusername = $_SESSION['username'];
   $newusername = $_POST['username'];
   $newemail = $_POST['email'];
   $newpassword = $_POST['password'];
   $confirmpassword = $_POST['confirmpassword'];
   $newfullname = $_POST['fullname'];
   if ($newpassword != $confirmpassword)
   {
      $error_message = 'Password and Confirm Password are not the same!';
   }
   else
   if (!preg_match("/^[A-Za-z0-9-_!@$]{1,50}$/", $newusername))
   {
      $error_message = 'Username is not valid, please check and try again!';
   }
   else
   if (!empty($newpassword) && !preg_match("/^[A-Za-z0-9-_!@$]{1,50}$/", $newpassword))
   {
      $error_message = 'Password is not valid, please check and try again!';
   }
   else
   if (!preg_match("/^[A-Za-z0-9-_!@$.' &]{1,50}$/", $newfullname))
   {
      $error_message = 'Fullname is not valid, please check and try again!';
   }
   else
   if (!preg_match("/^.+@.+\..+$/", $newemail))
   {
      $error_message = 'Email is not a valid email address. Please check and try again.';
   }
   else
   {
      $items = file($database, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      if ($oldusername != $newusername)
      {
         foreach($items as $line)
         {
            list($username) = explode('|', trim($line));
            if ($newusername == $username)
            {
               $error_message = 'Username already used. Please select another username.';
                break;
            }
         }
      }
      if (empty($error_message))
      {
         $file = fopen($database, 'w');
         foreach($items as $line)
         {
            list($username, $password) = explode('|', trim($line));
            if ($oldusername == $username)
            {
               $line = $newusername;
               $line .= "|";
               if (empty($newpassword))
               {
                  $line .= $password;
               }
               else
               {
                  $line .= md5($newpassword);
               }
               $line .= "|";
               $line .= $newemail;
               $line .= "|";
               $line .= $newfullname;
               $line .= "|1|";
            }
            fwrite($file, $line);
            fwrite($file, "\r\n");
         }
         fclose($file);
         $_SESSION['username'] = $newusername;
         $_SESSION['fullname'] = $newfullname;
         header('Location: '.$success_page);
         exit;
      }
   }
}
$items = file($database, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach($items as $line)
{
   list($username, $password, $email, $name, $active, $code) = explode('|', trim($line));
   if ($username == $_SESSION['username'])
   {
      $db_username = $username;
      $db_fullname = $name;
      $db_email = $email;
   }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Page</title>
<meta name="generator" content="WYSIWYG Web Builder 17 - https://www.wysiwygwebbuilder.com">
<link href="font-awesome.min.css" rel="stylesheet">
<link href="main_project.css" rel="stylesheet">
<link href="account_edit.css" rel="stylesheet">
<script src="jquery-3.6.0.min.js"></script>
<script src="wb.overlay.min.js"></script>
<script src="wb.drilldownmenu.min.js"></script>
<script>
$(document).ready(function()
{
   $('#OverlayMenu1-overlay').overlay({popup:true, hideTransition:true});
   $('.OverlayMenu1').drilldownmenu({backText: 'Back'});
   $('#OverlayMenu1').on('click', function(e)
   {
      $.overlay.toggle($('#OverlayMenu1-overlay'));
      return false;
   });
});
</script>
</head>
<body>
<div id="PageHeader1" style="position:absolute;text-align:left;left:0px;top:0px;width:100%;height:90px;z-index:7777;">
<div id="wb_Image1" style="position:absolute;left:783px;top:19px;width:148px;height:53px;z-index:0;">
<img src="images/logo.png" loading="lazy" id="Image1" alt="" width="148" height="54"></div>
<div id="wb_Text1" style="position:absolute;left:887px;top:0px;width:250px;height:90px;text-align:center;z-index:1;">
<span style="color:#FFFFFF;font-family:Bahnschrift;font-size:75px;"><strong><em>NIKE</em></strong></span></div>
<div id="wb_OverlayMenu1" style="position:absolute;left:0px;top:0px;width:85px;height:90px;z-index:2;">
<a href="#" id="OverlayMenu1">
<span class="line"></span>
<span class="line">
</span><span class="line"></span>
</a>
</div>
</div>
<a href="https://www.wysiwygwebbuilder.com" target="_blank"><img src="images/builtwithwwb17.png" alt="WYSIWYG Web Builder" style="position:absolute;left:441px;top:967px;margin: 0;border-width:0;z-index:250" width="16" height="16"></a>
<div id="wb_EditAccount1" style="position:absolute;left:822px;top:342px;width:277px;height:396px;z-index:6;">
<form name="editprofileform" method="post" accept-charset="UTF-8" action="<?php echo basename(__FILE__); ?>" id="editprofileform">
<input type="hidden" name="form_name" value="editprofileform">
<table id="EditAccount1">
<tr>
   <td class="header">Edit Account</td>
</tr>
<tr>
   <td class="label"><label for="fullname">Full Name</label></td>
</tr>
<tr>
   <td class="row"><input class="input" name="fullname" type="text" id="fullname" value="<?php echo $db_fullname; ?>"></td>
</tr>
<tr>
   <td class="label"><label for="username">User Name</label></td>
</tr>
<tr>
   <td class="row"><input class="input" name="username" type="text" id="username" value="<?php echo $db_username; ?>"></td>
</tr>
<tr>
   <td class="label"><label for="password">Password</label></td>
</tr>
<tr>
   <td class="row"><input class="input" name="password" type="password" id="password"></td>
</tr>
<tr>
   <td class="label"><label for="confirmpassword">Confirm Password</label></td>
</tr>
<tr>
   <td class="row"><input class="input" name="confirmpassword" type="password" id="confirmpassword"></td>
</tr>
<tr>
   <td class="label"><label for="email">E-mail</label></td>
</tr>
<tr>
   <td class="row"><input class="input" name="email" type="text" id="email" value="<?php echo $db_email; ?>"></td>
</tr>
<tr>
   <td><?php echo $error_message; ?></td>
</tr>
<tr>
   <td style="text-align:center;vertical-align:bottom"><input class="button" type="submit" name="update" value="Update" id="update"></td>
</tr>
</table>
</form>
</div>
<div id="PageFooter1" style="position:absolute;overflow:hidden;text-align:left;left:0px;top:1186px;width:100%;height:314px;z-index:7;">
<div id="wb_Text3" style="position:absolute;left:838px;top:12px;width:245px;height:26px;text-align:center;z-index:3;">
<span style="color:#FFFFFF;font-family:'Bahnschrift Condensed';font-size:21px;">7evenStudios 2022</span></div>
</div>
<div id="OverlayMenu1-overlay">
<div class="OverlayMenu1">
<ul class="drilldown-menu" role="menu">
<li><a role="menuitem" href="#" class="OverlayMenu1-effect"><i class="fa fa-home overlay-icon"></i>Home</a></li>
<li>
<a role="menuitem" href="#" class="OverlayMenu1-effect"><i class="fa fa-book overlay-icon"></i>Blog</a>
<ul class="drilldown-submenu" role="menu">
<li>
<a role="menuitem" href="#" class="OverlayMenu1-effect"><i class="fa fa-eye overlay-icon"></i>Design</a>
<ul class="drilldown-submenu" role="menu">
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;1</a></li>
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;2</a></li>
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;3</a></li>
</ul>
</li>
<li>
<a role="menuitem" href="#" class="OverlayMenu1-effect"><i class="fa fa-html5 overlay-icon"></i>HTML</a>
<ul class="drilldown-submenu" role="menu">
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;1</a></li>
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;2</a></li>
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;3</a></li>
</ul>
</li>
<li>
<a role="menuitem" href="#" class="OverlayMenu1-effect"><i class="fa fa-css3 overlay-icon"></i>CSS</a>
<ul class="drilldown-submenu" role="menu">
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;1</a></li>
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;2</a></li>
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;3</a></li>
</ul>
</li>
<li>
<a role="menuitem" href="#" class="OverlayMenu1-effect"><i class="fa fa-code overlay-icon"></i>JavaScript</a>
<ul class="drilldown-submenu" role="menu">
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;1</a></li>
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;2</a></li>
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;3</a></li>
</ul>
</li>
</ul>
</li>
<li>
<a role="menuitem" href="#" class="OverlayMenu1-effect"><i class="fa fa-gear overlay-icon"></i>Work</a>
<ul class="drilldown-submenu" role="menu">
<li>
<a role="menuitem" href="#" class="OverlayMenu1-effect"><i class="fa fa-desktop overlay-icon"></i>Web&nbsp;Design</a>
<ul class="drilldown-submenu" role="menu">
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;1</a></li>
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;2</a></li>
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;3</a></li>
</ul>
</li>
<li>
<a role="menuitem" href="#" class="OverlayMenu1-effect"><i class="fa fa-picture-o overlay-icon"></i>Typography</a>
<ul class="drilldown-submenu" role="menu">
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;1</a></li>
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;2</a></li>
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;3</a></li>
</ul>
</li>
<li>
<a role="menuitem" href="#" class="OverlayMenu1-effect"><i class="fa fa-envelope overlay-icon"></i>Applications</a>
<ul class="drilldown-submenu" role="menu">
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;1</a></li>
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;2</a></li>
<li><a role="menuitem" href="#" class="OverlayMenu1-effect">Sub&nbsp;Menu&nbsp;3</a></li>
</ul>
</li>
</ul>
</li>
<li><a role="menuitem" href="#" class="OverlayMenu1-effect"><i class="fa fa-user overlay-icon"></i>About</a></li>
   </ul>
</div>
</div>
</body>
</html>