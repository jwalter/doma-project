<?php
  include_once(dirname(__FILE__) ."/config.php");
  include_once(dirname(__FILE__) ."/include/definitions.php");


  if(($_GET["id"])&&(is_numeric($_GET["id"])))
  {
    $map = new Map();
    $map->Load($_GET["id"]);
    if(!$map->IsGeocoded)
    {
      $map->AddGeocoding();
      if($map->IsGeocoded)
      {
        $map->Save();
        Helper::WriteToLog("Added geocoding data to database for map with id ". $_GET["id"] .".");
      }
      else
      {
        Helper::WriteToLog("Failed to add geocoding data to database for map with id ". $_GET["id"] .". Probably no QuickRoute jpeg file.");
      }
    }
  }
  
?>
