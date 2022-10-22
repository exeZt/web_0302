<?php
session_start();
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');
define('MAIN_SCRIPT', basename(__FILE__));
function fireEvent($event_name, $id)
{
   global $events;
   if (isset($events[$event_name]))
   {
      foreach($events[$event_name] as $fn)
      {
         $fn($id);
      }
   } 
}
$mysql_server = '';
$mysql_username = '';
$mysql_password = '';
$mysql_database = '';
$mysql_table = 'CMS_';
$cms_no_results = 'No results';
$cms_content = '';
$cms_page_id = 0;
$events = array();
if (file_exists('./plugins/'))
{
   $handle = opendir("./plugins/");
   while ($name = readdir($handle))
   {
      if ($name != "." && $name != ".." && is_dir("./plugins/".$name) && substr($name, 0, 1) != '_')
      {
         require_once('./plugins/'.$name.'/plugin.php');
         if (isset($plugin['events']))
         {
            foreach($plugin['events'] as $name=>$fn)
            {
               if (!isset($events[$name]))
                  $events[$name] = array();
               $events[$name][] = $fn;
            }
         }
      }
   }
   closedir($handle);
}
$db = mysqli_connect($mysql_server, $mysql_username, $mysql_password);
if (!$db)
{
   die('Failed to connect to database server!<br>'.mysqli_error($db));
}
mysqli_select_db($db, $mysql_database) or die('Failed to select database<br>'.mysqli_error($db));
mysqli_query($db, 'SET NAMES "UTF8"');
mysqli_query($db, "SET collation_connection='utf8_general_ci'");
mysqli_query($db, "SET collation_server='utf8_general_ci'");
mysqli_query($db, "SET character_set_client='utf8'");
mysqli_query($db, "SET character_set_connection='utf8'");
mysqli_query($db, "SET character_set_results='utf8'");
mysqli_query($db, "SET character_set_server='utf8'");
mysqli_set_charset($db, 'utf8');
$id = isset($_REQUEST['page']) ? mysqli_real_escape_string($db, $_REQUEST['page']) : '';
$query = isset($_REQUEST['query']) ? $_REQUEST['query'] : '';
if (!empty($query))
{
   $query = addslashes($query);
   $words = preg_split('/\s+/', $query);
   foreach ($words as $word)
   {
      $word = preg_replace('/\W/u', '', $word);
      if (strlen($word) > 1)
      {
         $terms[] = $word;
      }
   }
   if ($terms)
   {
      if (count($terms) > 4)
      {
         array_splice($terms, 4);
      }
      $sql = "SELECT p.id, p.name, p.content FROM " . $mysql_table . "PAGES p, " . $mysql_table . "SEARCH_WORDS w, " . $mysql_table . "SEARCH_WORDMATCH m WHERE(";
      $where = "w.word LIKE '%" . array_shift($terms) . "%'";
      while ($term = array_shift($terms))
      {
         $where .= " OR w.word LIKE '%" . $term . "%'";
      }
      $sql .= $where . ") AND m.word_id = w.id AND m.page_id = p.id AND p.visible = 1 GROUP BY p.id ORDER BY p.last_update_date DESC";
      $result = mysqli_query($db, $sql) or die(mysqli_error($db));
      if ($num_rows = mysqli_num_rows($result))
      {
         $cms_content .= "  <ol class=\"searchresults\">\n";
         while ($data = mysqli_fetch_array($result))
         {
            $cms_content .= "   <li><a href=\"" . basename(__FILE__) . "?page=" . $data['id'] . "\">" . $data['name'] . "</a><br>";
            $content = substr(strip_tags($data['content']), 0, 200);
            if (strlen($content) > 199)
            {
               $content .= "...";
            }
            $cms_content .= $content;
            $cms_content .= "</li>\n";
         }
         $cms_content .= "  </ol>\n";
      }
      else
      {
         $cms_content = $cms_no_results;
      }
   }
   else
   {
      $cms_content = $cms_no_results;
   }
}
else
{
   if (!empty($id))
   {
      if (is_numeric($id))
      {
         $sql = "SELECT * FROM " . $mysql_table . "PAGES WHERE id = '$id' AND visible = 1";
      }
      else
      {
         $sql = "SELECT * FROM " . $mysql_table . "PAGES WHERE seo_friendly_url = '$id' AND visible = 1";
      }
   }
   else
   {
      $sql = "SELECT * FROM " . $mysql_table . "PAGES WHERE visible = 1 ORDER BY views DESC LIMIT 5";
   }
   $result = mysqli_query($db, $sql);
   if ($result)
   {
      if (empty($id))
      {
         $cms_name = "Latest Posts";
         $cms_title = $cms_name;
         $cms_content .= "<ul class=\"page-list\">\n";
         while ($data = mysqli_fetch_array($result))
         {
            $last_update = date("Y/m/d H:i:s", strtotime($data['last_update_date']));
            $cms_content .= "<li><a class=\"name\" href=\"" . basename(__FILE__) . "?page=" . $data['id'] . "\">" . $data['name'] . "</a><span class=\"last-update\">" . $last_update . "</span><br>";
            $content = substr(strip_tags($data['content']), 0, 200);
            if (strlen($content) > 199)
            {
               $content .= "...";
            }
            $cms_content .= $content;
            $cms_content .= "<br><a class=\"read-more\" href=\"" . basename(__FILE__) . "?page=" . $data['id'] . "\">Read more</a>";
            $cms_content .= "</li>\n";
         }
         $cms_content .= "</ul>\n";
      }
      else
      if ($data = mysqli_fetch_array($result))
      {
         $cms_content = '';
         $cms_page_id = $data['id'];
         fireEvent('onBeforeContent', $data['id']);
         if (!empty($data['url']))
         {
            $cms_content .= "<iframe name=\"cmscontent\" style =\"position:absolute;border-width:0;width:100%;height:100%;\" src=\"" . $data['url'] . "\"></iframe>\n";
         }
         else
         {
            $cms_content .= $data['content'];
         }
         fireEvent('onAfterContent', $data['id']);
         fireEvent('onOverwriteContent', $data['id']);
      }
      if (empty($label) && !empty($cms_page_id))
      {
         if (is_numeric($cms_page_id))
         {
            $sql = "UPDATE " . $mysql_table . "PAGES SET views=views+1 WHERE id = '$cms_page_id'";
         }
         else
         {
            $sql = "UPDATE " . $mysql_table . "PAGES SET views=views+1 WHERE seo_friendly_url = '$cms_page_id'";
         }
      }
      else
      {
         $sql = "UPDATE " . $mysql_table . "PAGES SET views=views+1 WHERE home = 1";
      }
      mysqli_query($db, $sql);
   }
}
mysqli_close($db);
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');
define('MAIN_SCRIPT', basename(__FILE__));
function fireEvent($event_name, $id)
{
   global $events;
   if (isset($events[$event_name]))
   {
      foreach($events[$event_name] as $fn)
      {
         $fn($id);
      }
   } 
}
$mysql_server = '';
$mysql_username = '';
$mysql_password = '';
$mysql_database = '';
$mysql_table = 'CMS_';
$cms_no_results = 'No results';
$cms_content = '';
$cms_page_id = 0;
$events = array();
if (file_exists('./plugins/'))
{
   $handle = opendir("./plugins/");
   while ($name = readdir($handle))
   {
      if ($name != "." && $name != ".." && is_dir("./plugins/".$name) && substr($name, 0, 1) != '_')
      {
         require_once('./plugins/'.$name.'/plugin.php');
         if (isset($plugin['events']))
         {
            foreach($plugin['events'] as $name=>$fn)
            {
               if (!isset($events[$name]))
                  $events[$name] = array();
               $events[$name][] = $fn;
            }
         }
      }
   }
   closedir($handle);
}
$db = mysqli_connect($mysql_server, $mysql_username, $mysql_password);
if (!$db)
{
   die('Failed to connect to database server!<br>'.mysqli_error($db));
}
mysqli_select_db($db, $mysql_database) or die('Failed to select database<br>'.mysqli_error($db));
mysqli_query($db, 'SET NAMES "UTF8"');
mysqli_query($db, "SET collation_connection='utf8_general_ci'");
mysqli_query($db, "SET collation_server='utf8_general_ci'");
mysqli_query($db, "SET character_set_client='utf8'");
mysqli_query($db, "SET character_set_connection='utf8'");
mysqli_query($db, "SET character_set_results='utf8'");
mysqli_query($db, "SET character_set_server='utf8'");
mysqli_set_charset($db, 'utf8');
$id = isset($_REQUEST['page']) ? mysqli_real_escape_string($db, $_REQUEST['page']) : '';
$query = isset($_REQUEST['query']) ? $_REQUEST['query'] : '';
if (!empty($query))
{
   $query = addslashes($query);
   $words = preg_split('/\s+/', $query);
   foreach ($words as $word)
   {
      $word = preg_replace('/\W/u', '', $word);
      if (strlen($word) > 1)
      {
         $terms[] = $word;
      }
   }
   if ($terms)
   {
      if (count($terms) > 4)
      {
         array_splice($terms, 4);
      }
      $sql = "SELECT p.id, p.name, p.content FROM " . $mysql_table . "PAGES p, " . $mysql_table . "SEARCH_WORDS w, " . $mysql_table . "SEARCH_WORDMATCH m WHERE(";
      $where = "w.word LIKE '%" . array_shift($terms) . "%'";
      while ($term = array_shift($terms))
      {
         $where .= " OR w.word LIKE '%" . $term . "%'";
      }
      $sql .= $where . ") AND m.word_id = w.id AND m.page_id = p.id AND p.visible = 1 GROUP BY p.id ORDER BY p.last_update_date DESC";
      $result = mysqli_query($db, $sql) or die(mysqli_error($db));
      if ($num_rows = mysqli_num_rows($result))
      {
         $cms_content .= "  <ol class=\"searchresults\">\n";
         while ($data = mysqli_fetch_array($result))
         {
            $cms_content .= "   <li><a href=\"" . basename(__FILE__) . "?page=" . $data['id'] . "\">" . $data['name'] . "</a><br>";
            $content = substr(strip_tags($data['content']), 0, 200);
            if (strlen($content) > 199)
            {
               $content .= "...";
            }
            $cms_content .= $content;
            $cms_content .= "</li>\n";
         }
         $cms_content .= "  </ol>\n";
      }
      else
      {
         $cms_content = $cms_no_results;
      }
   }
   else
   {
      $cms_content = $cms_no_results;
   }
}
else
{
   if (!empty($id))
   {
      if (is_numeric($id))
      {
         $sql = "SELECT * FROM " . $mysql_table . "PAGES WHERE id = '$id' AND visible = 1";
      }
      else
      {
         $sql = "SELECT * FROM " . $mysql_table . "PAGES WHERE seo_friendly_url = '$id' AND visible = 1";
      }
   }
   else
   {
      $sql = "SELECT * FROM " . $mysql_table . "PAGES WHERE home = 1";
   }
   $result = mysqli_query($db, $sql);
   if ($result)
   {
      if ($data = mysqli_fetch_array($result))
      {
         $cms_content = '';
         $cms_page_id = $data['id'];
         fireEvent('onBeforeContent', $data['id']);
         if (!empty($data['url']))
         {
            $cms_content .= "<iframe name=\"cmscontent\" style =\"position:absolute;border-width:0;width:100%;height:100%;\" src=\"" . $data['url'] . "\"></iframe>\n";
         }
         else
         {
            $cms_content .= $data['content'];
         }
         fireEvent('onAfterContent', $data['id']);
         fireEvent('onOverwriteContent', $data['id']);
      }
      if (empty($label) && !empty($cms_page_id))
      {
         if (is_numeric($cms_page_id))
         {
            $sql = "UPDATE " . $mysql_table . "PAGES SET views=views+1 WHERE id = '$cms_page_id'";
         }
         else
         {
            $sql = "UPDATE " . $mysql_table . "PAGES SET views=views+1 WHERE seo_friendly_url = '$cms_page_id'";
         }
      }
      else
      {
         $sql = "UPDATE " . $mysql_table . "PAGES SET views=views+1 WHERE home = 1";
      }
      mysqli_query($db, $sql);
   }
}
mysqli_close($db);
?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Page</title>
<meta name="generator" content="WYSIWYG Web Builder 17 - https://www.wysiwygwebbuilder.com">
<link href="font-awesome.min.css" rel="stylesheet">
<link href="main_project.css" rel="stylesheet">
<link href="shop.css" rel="stylesheet">
<script src="jquery-3.6.0.min.js"></script>
<script src="wb.overlay.min.js"></script>
<script src="wb.drilldownmenu.min.js"></script>
<?php
$cms_header = '';
fireEvent('onPageHeader', $cms_page_id);
echo $cms_header;
?>
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
<a href="https://www.wysiwygwebbuilder.com" target="_blank"><img src="images/builtwithwwb17.png" alt="WYSIWYG Web Builder" style="position:absolute;left:916px;top:3667px;margin: 0;border-width:0;z-index:250" width="16" height="16"></a>
<div id="wb_CmsView1" style="position:absolute;left:0px;top:222px;width:1918px;height:576px;z-index:8;overflow-y:auto;">
<?php
   echo $cms_content;
?>
</div>
<div id="Layer1" style="position:absolute;text-align:left;left:0px;top:90px;width:1920px;height:82px;z-index:9;">
<div id="wb_Text4" style="position:absolute;left:568px;top:9px;width:785px;height:64px;text-align:center;z-index:3;">
<span style="color:#FFFFFF;font-family:Bahnschrift;font-size:53px;">The most popular</span></div>
</div>
<form method="get" name="CmsSearch1_form" id="CmsSearch1_form" accept-charset="UTF-8" action="<?php echo basename(__FILE__); ?>">
<input type="text" id="CmsSearch1" style="position:absolute;left:0px;top:172px;width:1910px;height:40px;z-index:10;" name="query" value="" spellcheck="false" placeholder="Search article" role="searchbox"></form>
<div id="wb_CmsView2" style="position:absolute;left:0px;top:800px;width:1918px;height:671px;z-index:11;overflow-y:auto;">
<?php
   echo $cms_content;
?>
</div>
<div id="PageFooter2" style="position:absolute;overflow:hidden;text-align:left;left:0px;top:3386px;width:100%;height:314px;z-index:12;">
<div id="wb_Text2" style="position:absolute;left:838px;top:12px;width:245px;height:26px;text-align:center;z-index:4;">
<span style="color:#FFFFFF;font-family:'Bahnschrift Condensed';font-size:21px;">7evenStudios 2022</span></div>
</div>
<div id="PageFooter1" style="position:absolute;overflow:hidden;text-align:left;left:0px;top:3386px;width:100%;height:314px;z-index:13;">
<div id="wb_Text3" style="position:absolute;left:838px;top:12px;width:245px;height:26px;text-align:center;z-index:5;">
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