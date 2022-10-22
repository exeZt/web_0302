<?php
session_start();
if (!isset($_SESSION['username']))
{
   $accessdenied_page = './index.php';
   header('Location: '.$accessdenied_page);
   exit;
}
$mysql_server = 'localhost';
$mysql_username = 'root';
$mysql_password = 'root';
$mysql_database = 'web_data';
$mysql_table = 'users';
$error_message = '';
$db_username = '';
$db_fullname = '';
$db_email = '';
$db_avatar = '';
$avatar_folder = 'avatars';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_name']) && $_POST['form_name'] == 'editprofileform')
{
   $success_page = './account_settings.php';
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
      $avatar_max_width = 256;
      $avatar_max_height = 256;
      $newavatar = '';
      if (isset($_FILES['avatar']) && $_FILES['avatar']['name'] != "")
      {
         switch ($_FILES['avatar']['error'])
         {
            case UPLOAD_ERR_OK:
               if ($_FILES['avatar']['type'] == 'image/gif' || $_FILES['avatar']['type'] == 'image/jpeg' || $_FILES['avatar']['type'] == 'image/pjpeg' || $_FILES['avatar']['type'] == 'image/png' || $_FILES['avatar']['type'] == 'image/x-png')
               {
                  list($width, $height) = getimagesize($_FILES['avatar']['tmp_name']);
                  if ($width <= $avatar_max_width && $height <= $avatar_max_height)
                  {
                     $prefix = rand(111111, 999999);
                     $newavatar = $prefix . "_" . str_replace(" ", "_", $_FILES['avatar']['name']);
                     if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_folder . "/" . $newavatar))
                     {
                        $error_message = "Upload failed, please verify the folder's permissions.";
                     }
                  }
                  else
                  {
                     $error_message = "The image is too big.";
                  }
               }
               else
               {
                  $error_message = "Wrong file type, please only use jpg, gif or png images.";
               }
               break;
            case UPLOAD_ERR_INI_SIZE:
               $error_message = "The uploaded file exceeds the 'upload_max_filesize' directive.";
               break;
            case UPLOAD_ERR_FORM_SIZE:
               $error_message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
               break;
            case UPLOAD_ERR_PARTIAL:
               $error_message = "The uploaded file was only partially uploaded.";
               break;
            case UPLOAD_ERR_NO_FILE:
               $error_message = "No file was uploaded.";
               break;
            case UPLOAD_ERR_NO_TMP_DIR:
               $error_message = "Missing a temporary folder.";
               break;
            case UPLOAD_ERR_CANT_WRITE:
               $error_message = "Failed to write file to disk.";
               break;
            case UPLOAD_ERR_EXTENSION:
               $error_message = "File upload stopped by extension.";
               break;
         }
      }
      $db = mysqli_connect($mysql_server, $mysql_username, $mysql_password);
      if (!$db)
      {
         die('Failed to connect to database server!<br>'.mysqli_error($db));
      }
      mysqli_select_db($db, $mysql_database) or die('Failed to select database<br>'.mysqli_error($db));
      mysqli_set_charset($db, 'utf8');
      if ($oldusername != $newusername)
      {
         $sql = "SELECT username FROM ".$mysql_table." WHERE username = '".mysqli_real_escape_string($db, $newusername)."'";
         $result = mysqli_query($db, $sql);
         if ($data = mysqli_fetch_array($result))
         {
            $error_message = 'Username already used. Please select another username.';
         }
      }
      if (empty($error_message))
      {
         $crypt_pass = md5($newpassword);
         $newusername = mysqli_real_escape_string($db, $newusername);
         $newemail = mysqli_real_escape_string($db, $newemail);
         $newfullname = mysqli_real_escape_string($db, $newfullname);
         $sql = "UPDATE `".$mysql_table."` SET `username` = '$newusername', `fullname` = '$newfullname', `email` = '$newemail' WHERE `username` = '$oldusername'";
         mysqli_query($db, $sql);
         if (!empty($newpassword))
         {
            $sql = "UPDATE `".$mysql_table."` SET `password` = '$crypt_pass' WHERE `username` = '$oldusername'";
            mysqli_query($db, $sql);
         }
         if (!empty($newavatar))
         {
            $sql = "UPDATE `".$mysql_table."` SET `avatar` = '$newavatar' WHERE `username` = '$oldusername'";
            mysqli_query($db, $sql);
             $_SESSION['avatar'] = "http://".$_SERVER['HTTP_HOST'] . "avatars/" . $newavatar;
         }
      }
      mysqli_close($db);
      if (empty($error_message))
      {
         $_SESSION['username'] = $newusername;
         $_SESSION['fullname'] = $newfullname;
         header('Location: '.$success_page);
         exit;
      }
   }
}
$db = mysqli_connect($mysql_server, $mysql_username, $mysql_password);
if (!$db)
{
   die('Failed to connect to database server!<br>'.mysqli_error($db));
}
mysqli_select_db($db, $mysql_database) or die('Failed to select database<br>'.mysqli_error($db));
mysqli_set_charset($db, 'utf8');
$sql = "SELECT * FROM ".$mysql_table." WHERE username = '".$_SESSION['username']."'";
$result = mysqli_query($db, $sql);
if ($data = mysqli_fetch_array($result))
{
   $db_username = $data['username'];
   $db_fullname = $data['fullname'];
   $db_email = $data['email'];
   $db_avatar = $data['avatar'];
}
mysqli_close($db);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Page</title>
<meta name="generator" content="WYSIWYG Web Builder 17 - https://www.wysiwygwebbuilder.com">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="font-awesome.min.css" rel="stylesheet">
<link href="Untitled1.css" rel="stylesheet">
<link href="account_settings.css" rel="stylesheet">
<script src="jquery-3.6.0.min.js"></script>
<script src="jquery.ui.effect.min.js"></script>
<script src="popper.min.js"></script>
<script src="util.min.js"></script>
<script src="collapse.min.js"></script>
<script src="dropdown.min.js"></script>
<script>
$(document).ready(function()
{
   $("#avatar :file").on('change', function()
   {
      var input = $(this).parents('.input-group').find(':text');
      input.val($(this).val());
   });
   $("a[href*='#header']").click(function(event)
   {
      event.preventDefault();
      $('html, body').stop().animate({ scrollTop: $('#wb_header').offset().top }, 600, 'easeOutCirc');
   });
   $('#ThemeableMenu1 .dropdown-toggle').dropdown({popperConfig:{placement:'bottom-start',modifiers:{computeStyle:{gpuAcceleration:false}}}});
   $(document).on('click','.ThemeableMenu1-navbar-collapse.show',function(e)
   {
      if ($(e.target).is('a') && ($(e.target).attr('class') != 'dropdown-toggle')) 
      {
         $(this).collapse('hide');
      }
   });
});
</script>
</head>
<body onload=";return false;">
<a href="https://www.wysiwygwebbuilder.com" target="_blank"><img src="images/builtwithwwb17.png" alt="WYSIWYG Web Builder" style="position:absolute;left:916px;top:2967px;margin: 0;border-width:0;z-index:250" width="16" height="16"></a>
<div id="wb_EditAccount1" style="position:absolute;left:822px;top:255px;width:277px;height:454px;z-index:5;">
<form name="editprofileform" method="post" accept-charset="UTF-8" enctype="multipart/form-data" action="<?php echo basename(__FILE__); ?>" id="editprofileform">
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
   <td class="label"><label for="email">Avatar</label></td>
</tr>
<tr>
   <td class="row"><div class="input-group" id="avatar"><input class="input" type="text" readonly=""><label class="input-group-btn"><input type="file" name="avatar" style="display:none;"><span class="button">Browse...</span></label></div></td>
</tr>
<tr><td><div class="thumbnail"><div class="frame"><img alt="<?php echo $db_avatar; ?>" src="<?php echo $avatar_folder.'/'.$db_avatar; ?>"></div></div></td></tr>
<tr>
   <td><?php echo $error_message; ?></td>
</tr>
<tr>
   <td style="text-align:center;vertical-align:bottom"><input class="button" type="submit" name="update" value="Update" id="update"></td>
</tr>
</table>
</form>
</div>
<div id="wb_header">
<div id="header">
<div class="row">
<div class="col-1">
<div id="wb_Breadcrumb1" style="display:inline-block;width:100%;z-index:0;vertical-align:top;">
<ul id="Breadcrumb1">
<li><a href="">Corporate</a></li>
</ul>

</div>
<div id="wb_LoginName1" style="display:inline-block;width:96px;height:86px;z-index:1;">
<?php
if (isset($_SESSION['username']) && isset($_SESSION['avatar']))
{
   echo "<img alt=\"".$_SESSION['username']."\" src=\"".$_SESSION['avatar']."\" title=\"".$_SESSION['fullname']."\">";
}
?>
</div>
<div id="wb_LoginName2" style="display:inline-block;width:268px;height:29px;z-index:2;">
<span id="LoginName2"><?php
if (isset($_SESSION['username']))
{
   echo $_SESSION['username'];
}
else
{
   echo 'Not logged in';
}
?></span>
</div>
</div>
<div class="col-2">
<div id="wb_ThemeableMenu1" style="display:inline-block;width:100%;z-index:3;">
<div id="ThemeableMenu1" class="ThemeableMenu1" style="width:100%;height:auto !important;">
<div class="container">
<div class="navbar-header">
<button title="Hamburger Menu" type="button" class="navbar-toggle" data-toggle="collapse" data-target=".ThemeableMenu1-navbar-collapse">
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
</button>
</div>
<div class="ThemeableMenu1-navbar-collapse collapse">
<ul class="nav navbar-nav">
<li class="nav-item">
<a href="./index.php#home" class="nav-link">Home</a>
</li>
<li class="nav-item">
<a href="" class="nav-link">Services</a>
</li>
<li class="nav-item">
<a href="./index.php#about" class="nav-link">About</a>
</li>
<li class="nav-item">
<a href="./index.php#contact" class="nav-link">Contact</a>
</li>
<li class="nav-item dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown" >Account<b class="caret"></b></a>
<ul class="dropdown-menu">
<li class="nav-item dropdown-item">
<a href="./log-in.php" class="nav-link">Log In</a>
</li>
<li class="nav-item dropdown-item">
<a href="./account_settings.php" class="nav-link">Settings</a>
</li>
<li class="nav-item dropdown-item">
<a href="./signup.php" class="nav-link">Sign up</a>
</li>
</ul>
</li>
</ul>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</body>
</html>