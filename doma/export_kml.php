<?php
  include_once(dirname(__FILE__) ."/include/main.php");
  
  $format = $_GET["format"];
  if($format != "kmz") $format = "kml";
  
  $id = $_GET["id"];
  
  $map = new Map();
  $map->Load($id);

  $data = $map->CreateKmlString(Helper::GlobalPath(MAP_IMAGE_PATH), $format);

  if($format == "kml")
  {
    header("Content-Type: application/vnd.google-earth.kml+xml");
    header('Content-Disposition: attachment; filename="map_'. $id .'.kml";');
  }
  else
  {
    header("Content-Type: application/vnd.google-earth.kmz");
    header('Content-Disposition: attachment; filename="map_'. $id .'.kmz";');
  }
  
  print $data;
?>