<?php
  include_once(dirname(__FILE__) ."/include/main.php");
  include_once(dirname(__FILE__) ."/include/json.php");

  // no user specified - redirect to user list page
  if(!getUser())
  {
    $singleUserID = DataAccess::GetSingleUserID();
    if(!$singleUserID) Helper::Redirect("users.php");
    Helper::SetUser(DataAccess::GetUserByID($singleUserID));
  }

  // user is hidden - redirect to user list page
  if(!getUser()->Visible) Helper::Redirect("users.php");

  $searchCriteria = Session::GetSearchCriteria(getUser()->ID);

  if(!isset($searchCriteria))
  {
    // default search criteria
    $searchCriteria = array(
        "selectedYear" => date("Y"),
        "selectedCategoryID" => getUser()->DefaultCategoryID
    );
  }

  $errors = array();

  if($_GET["error"] == "thumbnailCreationFailure")
  {
    // calculate max image size for auto-generation of thumbnail
    $memoryLimit = ini_get("memory_limit");
    if(stripos($memoryLimit, "M")) $memoryLimit = ((int)str_replace("M", "", $memoryLimit)) * 1024 * 1024;
    $memoryLimit -= memory_get_usage();
    $size = round(sqrt($memoryLimit / 4) / 100) * 100;
    $errors[] = sprintf(__("THUMBNAIL_CREATION_FAILURE"), $size. "x". $size);
  }

  // get all categories
  $allCategoriesItem = new Category();
  $allCategoriesItem->ID = 0;
  $allCategoriesItem->Name = __("ALL_CATEGORIES");
  $categories = DataAccess::GetCategoriesByUserID(getUser()->ID);
  $categoriesWithText = array_merge(array(0 => $allCategoriesItem), $categories);

  // get all years
  $years = DataAccess::GetYearsByUserID(getUser()->ID);
  $years = array_reverse($years);
  $yearsWithText[0] = array("value" => 0, "text" => __("ALL_YEARS"));
  foreach($years as $year)
  {
    $yearsWithText[$year] = array("value" => $year, "text" => $year);
  }
  if(!in_array($searchCriteria["selectedYear"], array_keys($yearsWithText))) $searchCriteria["selectedYear"] = $years[0];
  if(!in_array($searchCriteria["selectedCategoryID"], array_keys($categories))) $searchCriteria["selectedCategoryID"] = $categories[0];

  if(isset($_POST["year"])) $searchCriteria["selectedYear"] = $_POST["year"];
  if(isset($_POST["categoryID"])) $searchCriteria["selectedCategoryID"] = $_POST["categoryID"];

  $startDate = ($searchCriteria["selectedYear"] == 0 ? 0 : Helper::StringToTime($searchCriteria["selectedYear"] ."-01-01", true));
  $endDate = ($searchCriteria["selectedYear"] == 0 ? 0 : Helper::StringToTime($searchCriteria["selectedYear"]. "-12-31", true));

  $maps = DataAccess::GetMaps(getUser()->ID, $startDate, $endDate, $searchCriteria["selectedCategoryID"]);

  // todo: make json data objects for google maps, only include necessary info

?>
<?php print '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><?php print __("PAGE_TITLE")?></title>
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
  <link rel="icon" type="image/png" href="gfx/favicon.png" />
  <link rel="stylesheet" href="style.css" type="text/css" />
  <link rel="alternate" type="application/rss+xml" title="RSS" href="rss.php?<?php print Helper::CreateQuerystring(getUser())?>" />
  <!--<script type="text/javascript" src="js/prototype/prototype_1.6.0.3.js"></script>-->
  <script type="text/javascript">
    function toggleComment(id)
    {
      $('shortComment_' + id).toggleClassName('hidden');
      $('longComment_' + id).toggleClassName('hidden');
    }

    function submitForm()
    {
      document.forms[0].submit();
    }

  </script>

<?php /*********************************************************************************************************************************/ ?>
    <script src="js/jquery/jquery-1.3.2.min.js" type="text/javascript"></script>
    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php print GOOGLE_MAPS_API_KEY; ?>"
      type="text/javascript"></script>
    <script type="text/javascript">

    //<![CDATA[

    var intervalId;
    var markerInfo = new Array();
    var routeLines = new Array();
    var currentLocationMarkers = new Array();
    var currentTime = 0;
    var mapCount = <?php print count($maps); ?>;
    var colors = ['#ff0000', '#0000ff'];

    function load()
    {
      if (GBrowserIsCompatible())
      {
        var map = new GMap2($("#overviewMap").get(0));
        map.addControl(new GLargeMapControl());
        <?php $m = array_values($maps); ?>
        var mapCenter = new GLatLng(<?php print $m[0]->MapCenterLatitude; ?>, <?php print $m[0]->MapCenterLongitude; ?>);

        map.setCenter(mapCenter, 14);
        var markerOptions;
        var ic;
        <?php
          $i = 0;
          foreach($maps as $map)
          {
            if($map->IsGeocoded)
            {
              $mcc = split(",", $map->MapCorners);
              print "markerInfo[$i] = { ".
                "center: [{$map->MapCenterLatitude}, {$map->MapCenterLongitude}], ".
                "corners: [{$mcc[1]}, {$mcc[0]}, {$mcc[3]}, {$mcc[2]}, {$mcc[5]}, {$mcc[4]}, {$mcc[7]}, {$mcc[6]}], ".
                "tabInfo: new Array ( { caption: 'tab1', bodyNode: \$('#map_{$map->ID} .overviewMapThumbnail').get(0) }, ".
                                     "{ caption: 'tab2', bodyNode: \$('#map_{$map->ID} .overviewMapInfo').get(0) } ) ".
                "};\n";

              $data = $map->GetQuickRouteJpegExtensionData();
              $waypoints = $data["Sessions"][0]["Route"]["Segments"][0]["Waypoints"];
              $arr = array();
              $filter = 5;
              for($j=0; $j<count($waypoints); $j+=$filter)
              {
                $arr[] = "[". $waypoints[$j]["Position"]["Latitude"] .", ". $waypoints[$j]["Position"]["Longitude"] ."]";
              }
              print "routeLines[$i] = [". join(",", $arr). "];\n";
              print "ic = new GIcon(G_DEFAULT_ICON);  ic.image = '{Helper::GlobalPath(\"\")}gfx/". ($i == 0 ? "red" : "blue") ."_marker.png'; ic.iconSize = new GSize(7,7); ic.iconAnchor=new GPoint(3,3); ic.shadow=null;";
              print "markerOptions = {icon: ic};\n";
              print "currentLocationMarkers[$i] = new GMarker(new GLatLng(". $waypoints[0]["Position"]["Latitude"] .", ". $waypoints[0]["Position"]["Longitude"] ."), markerOptions);\n";
              print "map.addOverlay(currentLocationMarkers[$i]);\n";

              $i++;
            }
          }
        ?>
        createMapMarkers(map, markerInfo);
        createRouteLines(map, routeLines);

        marker = new GMarker(mapCenter);
        map.addOverlay(marker);

        map.addControl(new GMapTypeControl());
        map.addControl(new GOverviewMapControl());


        intervalId = setInterval(moveCurrentLocationMarkers, 10);



      }
    }

    function moveCurrentLocationMarkers()
    {
      for(var i=0; i<mapCount; i++)
      {
        currentLocationMarkers[i].setLatLng(new GLatLng(routeLines[i][currentTime][0], routeLines[i][currentTime][1]));
      }
      currentTime++;
    }

    function createMapMarkers(map, markerInfo)
    {
      /*
        markerInfo: { double[] center, double[] corners, { string caption, node bodyNode }[] tabInfo }
      */
      for(var i in markerInfo)
      {
        var marker = new GMarker(new GLatLng(markerInfo[i].center[0], markerInfo[i].center[1]));
        map.addOverlay(marker);
        var line = new GPolyline([
          new GLatLng(markerInfo[i].corners[0], markerInfo[i].corners[1]),
          new GLatLng(markerInfo[i].corners[2], markerInfo[i].corners[3]),
          new GLatLng(markerInfo[i].corners[4], markerInfo[i].corners[5]),
          new GLatLng(markerInfo[i].corners[6], markerInfo[i].corners[7]),
          new GLatLng(markerInfo[i].corners[0], markerInfo[i].corners[1])],
          "#ff0000", 5
        );
        map.addOverlay(line);
        var tabs = new Array();
        for(var j in markerInfo[i].tabInfo)
        {
          tabs[j] = new GInfoWindowTab(markerInfo[i].tabInfo[j].caption, markerInfo[i].tabInfo[j].bodyNode);
        }
        marker.bindInfoWindowTabsHtml(tabs, { maxWidth: 800 } );
      }
    }

    function createRouteLines(map, routeLines)
    {
      /*
        double[2] vertices
      */

      for(var i in routeLines)
      {
        var points = new Array(routeLines[i].length);
        for(var j in routeLines[i])
        {
          var vertex = routeLines[i][j];
          points[j] = new GLatLng(vertex[0], vertex[1]);
        }
        var line = new GPolyline(points, colors[i], 3);
        map.addOverlay(line);
      }
    }


    //]]>
    </script>
<?php /*********************************************************************************************************************************/ ?>

</head>

<body id="indexBody" onload="load()" onunload="GUnload()"> <?php /***onload, onunload ******************************************************************************************************************************/ ?>
<div id="wrapper">
<?php Helper::CreateTopbar() ?>
<div id="content">
<form method="post" action="<?php print $_SERVER["PHP_SELF"]?>?<?php print Helper::CreateQuerystring(getUser())?>">
<?php if(count($errors) > 0) { ?>
<ul class="error">
<?php
  foreach($errors as $e)
  {
    print "<li>$e</li>";
  }
?>
</ul>
<?php } ?>

<div>
  <img id="logo" src="gfx/book.png" alt="" />
</div>

<div id="rssIcon"><a href="rss.php?<?php print Helper::CreateQuerystring(getUser())?>"><img src="gfx/feed-icon-28x28.png" alt="<?php print __("RSS_FEED")?>" title="<?php print __("RSS_FEED")?>" /></a></div>

<div id="intro">
<h1><?php print __("CAPTION")?></h1>
<p><?php print nl2br(__("INTRO"))?></p>

<div id="selectCategoryAndYear"><div class="inner1"><div class="inner2"><div class="inner3">
<?php

  if(count($years) == 0)
  {
    print __("NO_MAPS");
  }
  else
  {
  ?>
  <label for="categoryID"><?php print __("SELECT_CATEGORY")?>:</label>
  <select name="categoryID" id="categoryID" onchange="submitForm();">
  <?php
    foreach($categoriesWithText as $category)
    {
      print '<option value="'. $category->ID .'"'. ($searchCriteria["selectedCategoryID"] == $category->ID? ' selected="selected"' : '') .'>'. $category->Name .'</option>';
    }
  ?>
  </select>
  <label for="year"><?php print __("SELECT_YEAR")?>:</label>
  <select name="year" id="year" onchange="submitForm();">
  <?php
    foreach($yearsWithText as $year)
    {
      print '<option value="'. $year["value"] .'"'. ($searchCriteria["selectedYear"] == $year["value"] ? ' selected="selected"' : '') .'>'. $year["text"] .'</option>';
    }
  ?>
  </select>
<?php } ?>
</div></div></div></div>
</div>

<div id="maps">
  <div id="overviewMap" style="width:100%;height:400px;"></div>
<?php

  $width = THUMBNAIL_WIDTH;
  $height = THUMBNAIL_HEIGHT;

  if(count($maps) == 0 && count($years) > 0) print '<p class="clear">'. __("NO_MATCHING_MAPS") .'</p>';

  foreach($maps as $map)
  {
    $url = ($map->MapImage ? 'show_map.php?'. Helper::CreateQuerystring(getUser(), $map->ID) : "");
    $name = $map->Name .' ('. date(__("DATE_FORMAT"), Helper::StringToTime($map->Date, true)) .')';
    $image = '<img src="'. Helper::GetThumbnailImage($map) .'" alt="'. $name  .'" height="'. $height .'" width="'. $width .'" />';
    $linkedName = Helper::EncapsulateLink($map->Name, $url);
    $linkedImage = Helper::EncapsulateLink($image, $url);
    $atoms = array();
    if(__("SHOW_MAP_AREA_NAME") && $map->MapName) $atoms[] = $map->MapName;
    if(__("SHOW_ORGANISER") && $map->Organiser) $atoms[] = $map->Organiser;
    if(__("SHOW_COUNTRY") && $map->Country) $atoms[] = $map->Country;
    $mapAreaOrganiserCountry = join(", ", $atoms);
?>

  <div class="map" id="map_<?php print $map->ID?>">
    <div class="inner">
      <div class="thumbnail">
        <?php print $linkedImage?>
      </div>
      <div class="info">
        <div class="date">
          <?php print Helper::DateToLongString(Helper::StringToTime($map->Date, true))?>
        </div>
        <div class="name">
          <?php print $linkedName?>
        </div>
        <?php if(__("SHOW_MAP_AREA_NAME") || __("SHOW_ORGANISER") || __("SHOW_COUNTRY")) { ?>
        <?php if($searchCriteria["selectedCategoryID"] == 0) print '<div class="category">'. __("CATEGORY") .": ". $categories[$map->CategoryID]->Name .'</div>'; ?>
        <div class="organiser">
          <?php print $mapAreaOrganiserCountry?>
        </div>
        <?php } ?>
        <?php if(__("SHOW_DISCIPLINE")) { ?>
        <div class="discipline">
          <?php print $map->Discipline?><?php if(__("SHOW_RELAY_LEG") && $map->RelayLeg) print ', '. __("RELAY_LEG_LOWERCASE") .' '. $map->RelayLeg; ?>
        </div>
        <?php } ?>
        <?php if(__("SHOW_RESULT_LIST_URL") && $map->CreateResultListUrl()) { ?>
        <div class="resultListUrl">
          <a href="<?php print $map->CreateResultListUrl()?>"><?php print __("RESULTS")?></a>
        </div>
        <?php } ?>
        <?php if(Helper::IsLoggedInUser() && Helper::GetLoggedInUser()->ID == getUser()->ID) { ?>
          <div class="admin">
            <?php print $map->Views?> <?php print __("VIEWS")?> <span class="separator">|</span> <a href="edit_map.php?<?php print Helper::CreateQuerystring(getUser(), $map->ID)?>"><?php print __("EDIT_MAP")?></a>
          </div>
        <?php } ?>
      </div>
      <?php
        if(__("SHOW_COMMENT"))
        {
          createComment($map);
        }
        ?>
      <div class="clear"></div>
    </div>
    <div class="overviewMapData" style="display:none;">
      <div class="overviewMapThumbnail"><?php print $linkedImage?></div>
      <div class="overviewMapInfo"><?php print $linkedName?>, <?php print $mapAreaOrganiserCountry?></div>
    </div>
  </div>

<?php
  }
?>
  </div>
<div class="clear"></div>

<p id="footer"><?php print __("FOOTER")?></p>

</form>
</div>
</div>
</body>
</html>
<?php

  Session::SetSearchCriteria(getUser()->ID, $searchCriteria);

  function createComment($map)
  {
    if(!$map->Comment) return;
    $maxLength = 130;

    $strippedComment = strip_tags($map->Comment);

    if($strippedComment == $map->Comment && strlen($map->Comment) <= $maxLength)
    {
      print '<div class="comment">'. $map->Comment .'</div>';
    }
    else
    {
      ?>
      <div class="comment" id="shortComment_<?php print $map->ID?>">
        <img src="gfx/plus.png" class="button" onclick="toggleComment(<?php print $map->ID?>);" alt="" />
        <div class="indent"><?php print substr($strippedComment, 0, $maxLength)?>...</div>
      </div>
      <div class="comment hidden" id="longComment_<?php print $map->ID?>">
        <img src="gfx/minus.png" class="button" onclick="toggleComment(<?php print $map->ID?>);" alt="" />
        <div class="indent"><?php print nl2br($map->Comment)?></div>
      </div>
      <?php
    }
  }
?>