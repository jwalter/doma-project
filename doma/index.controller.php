<?php
  include_once(dirname(__FILE__) ."/include/main.php");

  class IndexController
  {
    public function Execute()
    {
      $viewData = array();
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
      
      $viewData["Errors"] = array();

      if($_GET["error"] == "thumbnailCreationFailure")
      {
        // calculate max image size for auto-generation of thumbnail
        $memoryLimit = ini_get("memory_limit");
        if(stripos($memoryLimit, "M")) $memoryLimit = ((int)str_replace("M", "", $memoryLimit)) * 1024 * 1024;
        $memoryLimit -= memory_get_usage();
        $size = round(sqrt($memoryLimit / 4) / 100) * 100; 
        $viewData["Errors"][] = sprintf(__("THUMBNAIL_CREATION_FAILURE"), $size. "x". $size);
      }
      
      // get all categories
      $allCategoriesItem = new Category();
      $allCategoriesItem->ID = 0;
      $allCategoriesItem->Name = __("ALL_CATEGORIES");
      $categories = DataAccess::GetCategoriesByUserID(getUser()->ID);
      $viewData["Categories"] = $categories;
      $viewData["CategoriesWithText"] = array_merge(array(0 => $allCategoriesItem), $categories);

      // get all years
      $years = DataAccess::GetYearsByUserID(getUser()->ID);
      $years = array_reverse($years);
      $viewData["YearsWithText"][0] = array("value" => 0, "text" => __("ALL_YEARS"));
      foreach($years as $year)
      {
        $viewData["YearsWithText"][$year] = array("value" => $year, "text" => $year);
      }
      if(!in_array($searchCriteria["selectedYear"], array_keys($viewData["YearsWithText"]))) $searchCriteria["selectedYear"] = $years[0];
      if(!in_array($searchCriteria["selectedCategoryID"], array_keys($categories))) $searchCriteria["selectedCategoryID"] = $categories[0];

      if(isset($_POST["year"])) $searchCriteria["selectedYear"] = $_POST["year"];
      if(isset($_POST["categoryID"])) $searchCriteria["selectedCategoryID"] = $_POST["categoryID"];
      if(isset($_POST["displayMode"])) 
      {
        $viewData["DisplayMode"] = $_POST["displayMode"];
      }
      else
      {
        $viewData["DisplayMode"] = "list";
      }

      $startDate = ($searchCriteria["selectedYear"] == 0 ? 0 : Helper::StringToTime($searchCriteria["selectedYear"] ."-01-01", true));
      $endDate = ($searchCriteria["selectedYear"] == 0 ? 0 : Helper::StringToTime($searchCriteria["selectedYear"]. "-12-31", true));
      $viewData["SearchCriteria"] = $searchCriteria;
      
      // get map data
      $viewData["Maps"] = DataAccess::GetMaps(getUser()->ID, $startDate, $endDate, $searchCriteria["selectedCategoryID"]);  
      $viewData["GeocodedMapsExist"] = false;
      foreach($viewData["Maps"] as $map)
      {
        $mapInfo = array();
        $mapInfo["URL"] = ($map->MapImage ? 'show_map.php?'. Helper::CreateQuerystring(getUser(), $map->ID) : "");
        $mapInfo["Name"] = $map->Name .' ('. date(__("DATE_FORMAT"), Helper::StringToTime($map->Date, true)) .')';
        $mapInfo["MapThumbnailHtml"] = Helper::EncapsulateLink('<img src="'. Helper::GetThumbnailImage($map) .'" alt="'. $mapInfo["Name"] .'" height="'. THUMBNAIL_HEIGHT .'" width="'. THUMBNAIL_WIDTH .'" />', $mapInfo["URL"]);

        $atoms = array();
        if(__("SHOW_MAP_AREA_NAME") && $map->MapName) $atoms[] = $map->MapName;
        if(__("SHOW_ORGANISER") && $map->Organiser) $atoms[] = $map->Organiser;
        if(__("SHOW_COUNTRY") && $map->Country) $atoms[] = $map->Country;
        $mapInfo["MapAreaOrganiserCountry"] = join(", ", $atoms);
        
        if($map->Comment)
        {
          $maxLength = 130;
          $strippedComment = strip_tags($map->Comment);
          $mapInfo["IsExpandableComment"] = !($strippedComment == $map->Comment && strlen($map->Comment) <= $maxLength);
          if($mapInfo["IsExpandableComment"])
          {
            $mapInfo["ContractedComment"] = substr($strippedComment, 0, $maxLength) ."...";
          }
        }
        $viewData["MapInfo"][$map->ID] = $mapInfo;
        
        if(($viewData["DisplayMode"] == "overviewMap")&&($map->IsGeocoded))
        {
          $corners = $map->GetMapCornerArray();
          $overviewMapData = array();
          $overviewMapData["MapCenter"] = new LongLat($map->MapCenterLongitude, $map->MapCenterLatitude);
          $overviewMapData["Corners"][] = new LongLat($corners["SW"]["Longitude"], $corners["SW"]["Latitude"]);
          $overviewMapData["Corners"][] = new LongLat($corners["NW"]["Longitude"], $corners["NW"]["Latitude"]);
          $overviewMapData["Corners"][] = new LongLat($corners["NE"]["Longitude"], $corners["NE"]["Latitude"]);
          $overviewMapData["Corners"][] = new LongLat($corners["SE"]["Longitude"], $corners["SE"]["Latitude"]);
          $overviewMapData["BorderColor"] = '#ff0000';
          $overviewMapData["BorderWidth"] = 2;
          $overviewMapData["BorderOpacity"] = 0.8;
          $overviewMapData["FillColor"] = '#ff0000';
          $overviewMapData["FillOpacity"] = 0.3;
          $overviewMapData["MapThumbnailImageCaption"] = __("MAP");
          $overviewMapData["MapThumbnailImage"] = 
            '<div class="gmInfoWindow">'.
            '<div class="mapName">'. $map->Name .' ('. date(__("DATE_FORMAT"), Helper::StringToTime($map->Date, true)) .')</div>'.
            '<div class="thumbnail">'. $mapInfo["MapThumbnailHtml"] .'</div>'.
            '</div>';
          $overviewMapData["MapInfoCaption"] = __("INFORMATION");
          
          //******************************
          $ed = $map->GetQuickrouteJpegExtensionData();
          $overviewMapData["RouteSegments"] = $ed->Sessions[0]->Route->GetWaypointPositionsAsArray(5, 6);
          //******************************
          
          $info = '<div class="gmInfoWindow">';
          $info .= '<div class="mapName">'. $map->Name .' ('. date(__("DATE_FORMAT"), Helper::StringToTime($map->Date, true)) .')</div>';
          if($viewData["SearchCriteria"]["selectedCategoryID"] == 0) $info .= __("CATEGORY") .": ". $viewData["Categories"][$map->CategoryID]->Name ."<br/>";
          if(__("SHOW_MAP_AREA_NAME") || __("SHOW_ORGANISER") || __("SHOW_COUNTRY"))
          {
            $info.= $mapInfo["MapAreaOrganiserCountry"] ."<br/>";
          }
          if(__("SHOW_DISCIPLINE"))
          {
             $info .= $map->Discipline;
             if(__("SHOW_RELAY_LEG") && $map->RelayLeg) $info .= ', '. __("RELAY_LEG_LOWERCASE") .' '. $map->RelayLeg;
             $info .= "<br/>";
          }
          if(__("SHOW_RESULT_LIST_URL") && $map->CreateResultListUrl()) 
          {
            $info .= '<a href="'. $map->CreateResultListUrl() .'">'. __("RESULTS") .'</a><br/>';
          }          
          $info .= '</div>';
          $overviewMapData["MapInfo"] = $info;
          $viewData["OverviewMapData"][] = $overviewMapData;
        }
        if($map->IsGeocoded) $viewData["GeocodedMapsExist"] = true;
      }
      if(!$viewData["GeocodedMapsExist"] && count($viewData["Maps"]) > 0) $viewData["DisplayMode"] = "list";
      
      Session::SetSearchCriteria(getUser()->ID, $searchCriteria);
      
      return $viewData;
    }
  }
  
?>
