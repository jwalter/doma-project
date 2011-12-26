<?php
  include_once(dirname(__FILE__) ."/include/main.php");
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
    
    $user = DAtaAccess::GetUserByID($map->UserID);
    
    $categories = DataAccess::GetCategoriesByUserID($user->ID);
    return Helper::GetOverviewMapData($map, true, false, false, $categories);
  }
?>
