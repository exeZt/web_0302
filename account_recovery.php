<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_name']) && $_POST['form_name'] == 'forgotpasswordform')
{
   $email = isset($_POST['email']) ? addslashes($_POST['email']) : '';
   $found = false;
   $items = array();
   $success_page = '';
   $error_page = basename(__FILE__);
   $database = './usersdb.php';
   if (filesize($database) == 0 || empty($email))
   {
      header('Location: '.$error_page);
      exit;
   }
   else
   {
      $items = file($database, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      foreach($items as $line)
      {
         list($username, $password, $emailaddress, $fullname, $active) = explode('|', trim($line));
         if ($email == $emailaddress && $active != "0")
         {
            $found = true;
         }
      }
   }
   if ($found == true)
   {
      $newpassword = '';
      $alphanum = array('a','b','c','d','e','f','g','h','i','j','k','m','n','o','p','q','r','s','t','u','v','x','y','z','A','B','C','D','E','F','G','H','I','J','K','M','N','P','Q','R','S','T','U','V','W','X','Y','Z','2','3','4','5','6','7','8','9');
      $chars = sizeof($alphanum);
      $a = time();
      mt_srand($a);
      for ($i=0; $i < 6; $i++)
      {
         $randnum = intval(mt_rand(0,55));
         $newpassword .= $alphanum[$randnum];
      }
      $crypt_pass = md5($newpassword);
      $file = fopen($database, 'w');
      foreach($items as $line)
      {
         $values = explode('|', trim($line));
         if ($email == $values[2])
         {
            $values[1] = $crypt_pass;
            $line = '';
            for ($i=0; $i < count($values); $i++)
            {
               if ($i != 0)
                  $line .= '|';
               $line .= $values[$i];
            }
         }
         fwrite($file, $line);
         fwrite($file, "\r\n");
      }
      fclose($file);
      $mailto = $_POST['email'];
      $subject = 'New password';
      $message = 'Your new password for http://www.yourwebsite.com/ is:';
      $message .= $newpassword;
      $header  = "From: webmaster@yourwebsite.com"."\r\n";
      $header .= "Reply-To: webmaster@yourwebsite.com"."\r\n";
      $header .= "MIME-Version: 1.0"."\r\n";
      $header .= "Content-Type: text/plain; charset=utf-8"."\r\n";
      $header .= "Content-Transfer-Encoding: 8bit"."\r\n";
      $header .= "X-Mailer: PHP v".phpversion();
      mail($mailto, $subject, $message, $header);
      header('Location: '.$success_page);
   }
   else
   {
      header('Location: '.$error_page);
   }
   exit;
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
<link href="account_recovery.css" rel="stylesheet">
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
<div id="wb_PasswordRecovery1" style="position:absolute;left:744px;top:435px;width:433px;height:141px;z-index:6;">
<form name="forgotpasswordform" method="post" accept-charset="UTF-8" action="<?php echo basename(__FILE__); ?>" id="forgotpasswordform">
<input type="hidden" name="form_name" value="forgotpasswordform">
<table id="PasswordRecovery1">
<tr>
   <td class="header">Forgot your password?</td>
</tr>
<tr>
   <td class="label"><label for="email">Email</label></td>
</tr>
<tr>
   <td class="row"><input class="input" name="email" type="text" id="email"></td>
</tr>
<tr>
   <td style="text-align:center;vertical-align:bottom"><input class="button" type="submit" name="submit" value="Submit" id="submit"></td>
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