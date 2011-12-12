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
      
      if($map->UserID != getUser()->ID) die();
      
      $viewData["Comments"] = DataAccess::GetCommentsByMapId($map->ID);

      $viewData["Name"] = $map->Name .' ('. date(__("DATE_FORMAT"), Helper::StringToTime($map->Date, true)) .')';

      // previous map in archive
      $previous = DataAccess::GetPreviousMap(getUser()->ID, $map->ID);
      $viewData["PreviousName"] = $previous->Name .' ('. date(__("DATE_FORMAT"), Helper::StringToTime($previous->Date, true)) .')';

      // next map in archive
      $next = DataAccess::GetNextMap(getUser()->ID, $map->ID);
      $viewData["NextName"] = $next->Name .' ('. date(__("DATE_FORMAT"), Helper::StringToTime($next->Date, true)) .')';

      $size = $map->GetMapImageSize();
      $viewData["ImageWidth"] = $size["Width"];
      $viewData["ImageHeight"] = $size["Height"];
      
      DataAccess::IncreaseMapViews($map);

      $viewData["Map"] = $map;
      
      $viewData["BackUrl"] = (basename($_SERVER["HTTP_REFERER"]) == "users.php"
        ? "users.php"
        : "index.php?". Helper::CreateQuerystring(getUser()));
      
      $viewData["Previous"] = $previous;
      $viewData["Next"] = $next;
      
      $viewData["FirstMapImageName"] = Helper::GetMapImage($map);
      if($map->BlankMapImage) $viewData["SecondMapImageName"] = Helper::GetBlankMapImage($map);
      
      //***********************
      $viewData["QuickRouteJpegExtensionData"] = $map->GetQuickRouteJpegExtensionData();
      //echo $viewData["QuickRouteJpegExtensionData"]->Sessions[0]->Route->ElapsedTime ."**";
      //echo $viewData["QuickRouteJpegExtensionData"]->Sessions[0]->Route->Distance ."**";
      //echo $viewData["QuickRouteJpegExtensionData"]->Sessions[0]->StraightLineDistance ."**";
      //echo $viewData["QuickRouteJpegExtensionData"]->ExecutionTime;
      //***********************
      
      return $viewData;
    }
  }
?>