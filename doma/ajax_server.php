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
    
    $categories = DataAccess::GetCategoriesByUserID(getUser()->ID);
    return Helper::GetOverviewMapData($map, true, $categories);
  }
?>
