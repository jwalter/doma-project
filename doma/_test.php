<?php
include_once("include/quickroute_jpeg_extension_data.php");
echo date("Y-m-d H:i:s", QuickRouteJpegExtensionData::test());

die();

/*
  En resizable karta med en eller flera markers. Klickar man på en marker visas kartinfo och ev thumbnail.
*/
$id = $_GET["id"];

include_once("include/exif_helper.php");

  $apiKey = "ABQIAAAAlI1bJ5aXT0aFXKwkcHPb3BQHhVxNDfwyUx5FOj8nBdhMKlzkihRmxYFEJabc3g8lzxJiX92URhLLwg";

//$exif = exif_read_data("kbh.jpg", 0, true);
$exif = exif_read_data("map_images/$id.jpg", 0, true);

$coord = $exif["GPS"]["GPSLongitude"];
$ds = split("/", $coord[0]);
$ms = split("/", $coord[1]);
$ss = split("/", $coord[2]);
$lon = $ds[0] / $ds[1] +
       $ms[0] / $ms[1] / 60 +
       $ss[0] / $ss[1] / 3600;
if($exif["GPS"]["GPSLongitudeRef"] == "W") $lat = -$lat;

$coord = $exif["GPS"]["GPSLatitude"];
$ds = split("/", $coord[0]);
$ms = split("/", $coord[1]);
$ss = split("/", $coord[2]);
$lat = $ds[0] / $ds[1] +
       $ms[0] / $ms[1] / 60 +
       $ss[0] / $ss[1] / 3600;
if($exif["GPS"]["GPSLatitudeRef"] == "S") $lat = -$lat;

$data = $exif["EXIF"]["UserComment"];

$extensionData = ExifHelper::GetQuickRouteJpegExtensionData($data);


$swLong = $extensionData["ImageCornerPositions"]["SW"]["Longitude"];
$swLat =  $extensionData["ImageCornerPositions"]["SW"]["Latitude"];
$nwLong = $extensionData["ImageCornerPositions"]["NW"]["Longitude"];
$nwLat =  $extensionData["ImageCornerPositions"]["NW"]["Latitude"];
$neLong = $extensionData["ImageCornerPositions"]["NE"]["Longitude"];
$neLat =  $extensionData["ImageCornerPositions"]["NE"]["Latitude"];
$seLong = $extensionData["ImageCornerPositions"]["SE"]["Longitude"];
$seLat =  $extensionData["ImageCornerPositions"]["SE"]["Latitude"];

$routeCoords = array();

$waypoints = $extensionData["Sessions"][0]["Route"]["Segments"][0]["Waypoints"];

for($i=0; $i<count($waypoints); $i+=1)
{
 $routeCoords[] = "new GLatLng(" . $waypoints[$i]["Position"]["Latitude"] .", ". $waypoints[$i]["Position"]["Longitude"] .")";
 //echo date("Y-m-d H:i:s", $waypoints[$i]["Time"]) ."<br>";
}

$routeCoordsString = join(", ", $routeCoords);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Google Maps JavaScript API Example</title>
    <script src="js/jquery/jquery-1.3.min.js" type="text/javascript"></script>
    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAlI1bJ5aXT0aFXKwkcHPb3BRmSL4ZGLw2NzN1xMOB8-pafnC30BRZY8XHncs50tsHSQlaxCwIHTbfbg"
      type="text/javascript"></script>
    <script type="text/javascript">

    //<![CDATA[

    function load() 
    {
      if (GBrowserIsCompatible()) 
      {
        var map = new GMap2(document.getElementById("map"));
        var mapCenter = new GLatLng(<?php print $lat?>, <?php print $lon?>);
        
        map.setCenter(mapCenter, 13);
        
        /*
        var marker = new GMarker(mapCenter);
        map.addOverlay(marker);
        
        var tabs = [ new GInfoWindowTab("Tab1", $(".window").get(0)), new GInfoWindowTab("Tab2", $(".window").get(1)) ];
        
        
        //GEvent.addListener(marker, "mouseover") 
        
        marker.bindInfoWindowTabsHtml(tabs, { maxWidth: 800 } );
        */
        
        var markerInfo = new Array();
        <?php
          $i = 0;
          print "markerInfo[$i] = { position: new GLatLng($lat, $lon), tabInfo: new Array ( { caption: 'tab1', bodyNode: \$('.window').get(0) }, { caption: 'tab2', bodyNode: \$('.window').get(1) } ) };";
        ?>
        var border = new GPolyline([
          <?php print "new GLatLng($swLat, $swLong),"?>
          <?php print "new GLatLng($nwLat, $nwLong),"?>
          <?php print "new GLatLng($neLat, $neLong),"?>
          <?php print "new GLatLng($seLat, $seLong),"?>
          <?php print "new GLatLng($swLat, $swLong),"?>
        ], "#ff0000", 10);

        map.addOverlay(border);        
        
        var route = new GPolyline([ <?php print $routeCoordsString ?> ] , "#0000ff", 3);
        
        map.addOverlay(route);        
        

        var boundaries = new GLatLngBounds(new GLatLng(<?php print $swLat?>, <?php print $swLong?>), new GLatLng(<?php print $neLat?>, <?php print $neLong?>));
var oldmap = new GGroundOverlay("http://www.matstroeng.se/maps/map_images/$id.jpg", boundaries);
map.addControl(new GSmallMapControl());
map.addControl(new GMapTypeControl());
map.addOverlay(oldmap);
        
        
        createMapMarkers(map, markerInfo);
        
        $("#toggle1").click(function() { map.removeOverlay(oldmap); });
        $("#toggle2").click(function() { map.addOverlay(oldmap); });
        
      }
    }
    
    function createMapMarkers(map, markerInfo)
    {
      /*
        markerInfo: { GLatLng position, { string caption, node bodyNode }[] tabInfo }
      */
      for(var i in markerInfo)
      {
        var marker = new GMarker(markerInfo[i].position);
        map.addOverlay(marker);
        var tabs = new Array();
        for(var j in markerInfo[i].tabInfo)
        {
          tabs[j] = new GInfoWindowTab(markerInfo[i].tabInfo[j].caption, markerInfo[i].tabInfo[j].bodyNode);
        }
        marker.bindInfoWindowTabsHtml(tabs, { maxWidth: 800 } );
      }
    }

    //]]>
    </script>
  </head>
  <body onload="load()" onunload="GUnload()">
    <div id="map" style="width:100%; height: 800px"></div>
    
    <div style="display:none;"><div class="window"><a href="http://www.oklinne.nu"><img src="map_images/1.thumbnail.png"></a></div><div class="window"><span style="font-family: Trebuchet MS">Info...</span></div></div>
  </body>
</html>