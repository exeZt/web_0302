<?php
$database = './usersdb.php';
$success_page = '';
$error_message = "";
if (!file_exists($database))
{
   die('User database not found!');
   exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_name']) && $_POST['form_name'] == 'signupform')
{
   $newusername = $_POST['username'];
   $newemail = $_POST['email'];
   $newpassword = $_POST['password'];
   $confirmpassword = $_POST['confirmpassword'];
   $newfullname = $_POST['fullname'];
   $code = 'NA';
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
   if (!preg_match("/^[A-Za-z0-9-_!@$]{1,50}$/", $newpassword))
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
   $items = file($database, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
   foreach($items as $line)
   {
      list($username, $password, $email, $fullname) = explode('|', trim($line));
      if ($newusername == $username)
      {
         $error_message = 'Username already used. Please select another username.';
         break;
      }
   }
   if (empty($error_message))
   {
      $file = fopen($database, 'a');
      fwrite($file, $newusername);
      fwrite($file, '|');
      fwrite($file, md5($newpassword));
      fwrite($file, '|');
      fwrite($file, $newemail);
      fwrite($file, '|');
      fwrite($file, $newfullname);
      fwrite($file, '|1|');
      fwrite($file, $code);
      fwrite($file, "\r\n");
      fclose($file);
      $subject = 'Your new account';
      $message = 'A new account has been setup.';
      $message .= "\r\nUsername: ";
      $message .= $newusername;
      $message .= "\r\nPassword: ";
      $message .= $newpassword;
      $message .= "\r\n";
      $header  = "From: webmaster@yourwebsite.com"."\r\n";
      $header .= "Reply-To: webmaster@yourwebsite.com"."\r\n";
      $header .= "MIME-Version: 1.0"."\r\n";
      $header .= "Content-Type: text/plain; charset=utf-8"."\r\n";
      $header .= "Content-Transfer-Encoding: 8bit"."\r\n";
      $header .= "X-Mailer: PHP v".phpversion();
      mail($newemail, $subject, $message, $header);
      header('Location: '.$success_page);
      exit;
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
<link href="account_signup.css" rel="stylesheet">
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
<a href="https://www.wysiwygwebbuilder.com" target="_blank"><img src="images/builtwithwwb17.png" alt="WYSIWYG Web Builder" style="position:absolute;left:916px;top:967px;margin: 0;border-width:0;z-index:250" width="16" height="16"></a>
<div id="wb_Signup1" style="position:absolute;left:822px;top:343px;width:277px;height:396px;z-index:6;">
<form name="signupform" method="post" accept-charset="UTF-8" action="<?php echo basename(__FILE__); ?>" id="signupform">
<input type="hidden" name="form_name" value="signupform">
<table id="Signup1">
<tr>
   <td class="header">Sign up for a new account</td>
</tr>
<tr>
   <td class="label"><label for="fullname">Full Name</label></td>
</tr>
<tr>
   <td class="row"><input class="input" name="fullname" type="text" id="fullname"></td>
</tr>
<tr>
   <td class="label"><label for="username">User Name</label></td>
</tr>
<tr>
   <td class="row"><input class="input" name="username" type="text" id="username"></td>
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
   <td class="row"><input class="input" name="email" type="text" id="email"></td>
</tr>
<tr>
   <td><?php echo $error_message; ?></td>
</tr>
<tr>
   <td style="text-align:center;vertical-align:bottom"><input class="button" type="submit" name="signup" value="Create User" id="signup"></td>
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