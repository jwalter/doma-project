<?php
  include_once(dirname(__FILE__) ."/show_map.controller.php");
  
  $controller = new ShowMapController();
  $vd = $controller->Execute();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><?php print __("PAGE_TITLE")?> :: <?php print strip_tags($vd["name"])?></title>
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
  <link rel="stylesheet" href="style.css" type="text/css" />
  <link rel="icon" type="image/png" href="gfx/favicon.png" />
  <link rel="alternate" type="application/rss+xml" title="RSS" href="rss.php?<?php print Helper::CreateQuerystring(getUser())?>" />
  <script src="js/jquery/jquery-1.3.min.js" type="text/javascript"></script>
  <script src="js/show_map.js.php" type="text/javascript"></script>
</head>
<body id="showMapBody">
<div id="wrapper">
<?php Helper::CreateTopbar() ?>
<div id="content">
<form method="post" action="<?php print $PHP_SELF?>">

<div id="name"><?php print $vd["Name"]?></div>

<div id="zoomButtonDiv">
  <div id="zoomIn" class="zoomButton"></div>
  <div id="zoomOut" class="zoomButton"></div>
</div>

<div id="previousAndNext">
  <?php if($vd["Previous"]) { ?><?php print __("PREVIOUS")?>: <a href="show_map.php?<?php print Helper::CreateQuerystring(getUser(), $vd["Previous"]->ID)?>"><?php print $vd["PreviousName"]?></a><?php } ?>
  <br />
  <?php if($vd["Next"]) { ?><?php print __("NEXT")?>: <a href="show_map.php?<?php print Helper::CreateQuerystring(getUser(), $vd["Next"]->ID)?>"><?php print $vd["NextName"]?></a><?php } ?>
  <br />
  <a href="<?php print $vd["BackUrl"]?>"><?php print __("BACK")?></a>
	<?php if($vd["SecondMapImageName"]) {?>
	<br />
	<a href="javascript:ToggleImage();" title="<?php print __("TOGGLE_IMAGE_TOOLTIP")?>"><?php print __("TOGGLE_IMAGE")?></a>
	<?php }?>
</div>

<div id="propertyContainer">
<?php
  $map = $vd["Map"];
  print '<div class="property">'. __("CATEGORY") .": ". $map->GetCategory()->Name .'</div>'; 
  if(__("SHOW_MAP_AREA_NAME") && $map->MapName != "") print '<div class="property">'. __("MAP_AREA_NAME") .': '. $map->MapName .'</div>';
  if(__("SHOW_ORGANISER") && $map->Organiser != "") print '<div class="property">'. __("ORGANISER") .': '. $map->Organiser .'</div>';
  if(__("SHOW_COUNTRY") && $map->Country != "") print '<div class="property">'. __("COUNTRY") .': '. $map->Country .'</div>';
  if(__("SHOW_DISCIPLINE") && $map->Discipline != "") print '<div class="property">'. __("DISCIPLINE") .': '. $map->Discipline .'</div>';
  if(__("SHOW_RELAY_LEG") && $map->RelayLeg != "") print '<div class="property">'. __("RELAY_LEG") .': '. $map->RelayLeg .'</div>';
  if(__("SHOW_RESULT_LIST_URL") && $map->ResultListUrl != "") print '<div class="property"><a href="'. hsc($map->CreateResultListUrl()) .'">'. __("RESULTS") .'</a></div>';
?>
</div>
<?php
  if(__("SHOW_COMMENT") && $map->Comment != "") print '<div id="comment">'. nl2br($map->Comment) .'</div>';
?>
<div class="clear"></div>

</form>
</div>
</div>

<div>
  <img id="mapImage" src="<?php print $vd["FirstMapImageName"]; ?>" alt="<?php print hsc(strip_tags($vd["Name"]))?>" />
  <?php if($vd["SecondMapImageName"]) { ?>
  <img id="hiddenMapImage" src="<?php print $vd["SecondMapImageName"]; ?>" alt="<?php print hsc(strip_tags($vd["Name"]))?>" />
  <?php } ?>
  <input type="hidden" id="imageWidth" value="<?php print $vd["ImageWidth"] ?>" />
  <input type="hidden" id="imageHeight" value="<?php print $vd["ImageHeight"] ?>" />
</div>
</body>
</html>