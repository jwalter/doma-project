<?php
  include_once(dirname(__FILE__) ."/config.php");
  include_once(dirname(__FILE__) ."/include/definitions.php");


  if(($_GET["id"])&&(is_numeric($_GET["id"])))
  {
    $map = new Map();
    $map->Load($_GET["id"]);
    
    $map->AddGeocoding();
    DataAccess::SaveMapWaypoints($map);
    $map->Save();
    Helper::WriteToLog("Upgrading map: ". $_GET["id"]);
  }
  
?>
