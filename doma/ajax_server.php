<?php
  include_once(dirname(__FILE__) ."/config.php");
  include_once(dirname(__FILE__) ."/include/definitions.php");
  include_once(dirname(__FILE__) ."/include/json.php");

  switch($_GET["action"])
  {
    case "getMapCornerPositionsAndRouteCoordinates":
      $id = $_GET["id"];
      $r = getMapCornerPositionsAndRouteCoordinates($id);
      print json_encode($r);
      break;
  }
  
  function getMapCornerPositionsAndRouteCoordinates($id)
  {
    $map = new Map();
    $map->Load($id);
    //$ed = $map->GetQuickRouteJpegExtensionData(false);
    return array(
      "ID" => $id,
      "MapCornerPositions" => $map->GetMapCornerArray(), 
      "RouteCoordinates" => DataAccess::GetWaypointPositionsAsArray($id,5, 6),
      "BorderColor" => '#ff0000',
      "BorderWidth" => 2,
      "BorderOpacity" => 0.8,
      "FillColor" => '#ff0000',
      "FillOpacity" => 0.3
    );
  }
  
?>
