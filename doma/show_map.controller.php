<?php
  include_once(dirname(__FILE__) ."/include/main.php");
  include_once(dirname(__FILE__) ."/include/quickroute_jpeg_extension_data.php");
  
  class ShowMapController
  {
    public function Execute()
    {
      $viewData = array();  

      // no user specified - redirect to user list page
      if(!getUser()) Helper::Redirect("users.php");
      
      // user is hidden - redirect to user list page
      if(!getUser()->Visible) Helper::Redirect("users.php");

      // the requested map
      $map = new Map();
      $map->Load($_GET["map"]);
      
      if(!$map->ID) die("The map has been removed.");
      
      DataAccess::UnprotectMapIfNeeded($map);
      
      if(Helper::MapIsProtected($map)) die("The map is protected until ". date("Y-m-d H:i:s", Helper::StringToTime($map->ProtectedUntil, true)) .".");
            
      if($map->UserID != getUser()->ID) die();
      
      $viewData["Comments"] = DataAccess::GetCommentsByMapId($map->ID);

      $viewData["Name"] = $map->Name .' ('. date(__("DATE_FORMAT"), Helper::StringToTime($map->Date, true)) .')';

      // previous map in archive
      $previous = DataAccess::GetPreviousMap(getUser()->ID, $map->ID, Helper::GetLoggedInUserID());
      $viewData["PreviousName"] = $previous->Name .' ('. date(__("DATE_FORMAT"), Helper::StringToTime($previous->Date, true)) .')';

      // next map in archive
      $next = DataAccess::GetNextMap(getUser()->ID, $map->ID, Helper::GetLoggedInUserID());
      $viewData["NextName"] = $next->Name .' ('. date(__("DATE_FORMAT"), Helper::StringToTime($next->Date, true)) .')';

      $size = $map->GetMapImageSize();
      $viewData["ImageWidth"] = $size["Width"];
      $viewData["ImageHeight"] = $size["Height"];
      
      DataAccess::IncreaseMapViews($map);

      $viewData["Map"] = $map;
      
      $viewData["BackUrl"] = isset($_SERVER["HTTP_REFERER"]) && basename($_SERVER["HTTP_REFERER"]) == "users.php"
        ? "users.php"
        : "index.php?". Helper::CreateQuerystring(getUser());
      
      $viewData["Previous"] = $previous;
      $viewData["Next"] = $next;
      
      $viewData["FirstMapImageName"] = Helper::GetMapImage($map);
      if($map->BlankMapImage) $viewData["SecondMapImageName"] = Helper::GetBlankMapImage($map);
      
      $viewData["QuickRouteJpegExtensionData"] = $map->GetQuickRouteJpegExtensionData();
      
      if(isset($viewData["QuickRouteJpegExtensionData"]) && $viewData["QuickRouteJpegExtensionData"]->IsValid)
      {
        $categories = DataAccess::GetCategoriesByUserID(getUser()->ID);
        $viewData["OverviewMapData"][] = Helper::GetOverviewMapData($map, true, false, false, $categories);
      }
      return $viewData;
    }
  }
?>