<?php
  include_once(dirname(__FILE__) ."/include/main.php");
  
  class EditMapController
  {
    public function Execute()
    {
      $viewData = array();  
  
      $errors = array();
      
      // no user specified - redirect to user list page
      if(!getUser()) Helper::Redirect("users.php");

      if(!Helper::IsLoggedInUser()) Helper::Redirect("users.php");
      
      if($_GET["map"]) $mapID = $_GET["map"];

      foreach($_GET as $variable => $value) $$variable = stripslashes($value);
      foreach($_POST as $variable => $value) $$variable = stripslashes($value);
      
      if(isset($cancel))
      {
        Helper::Redirect("index.php?". Helper::CreateQuerystring(getUser()));
      }

      if(isset($save) || isset($delete) || isset($deleteConfirmed))
      {
        $map = new Map();
        if($mapID) 
        {
          $map->Load($mapID);
          if($map->UserID != getUser()->ID) die("Access denied");    
          $isNewMap = false;
        }
        else
        {
          $isNewMap = true;
        }
        $map->UserID = getUser()->ID;    
        $map->CategoryID = $categoryID;
        $map->Date = $date;
        $map->Name = $name; 
        $map->Organiser = $organiser;     
        $map->Country = $country;     
        $map->Discipline = $discipline;     
        $map->RelayLeg = $relayLeg;     
        $map->MapName = $mapName;
        $map->ResultListUrl = $resultListUrl;
        $map->Comment = $comment;   
      }
      else
      {
        // first page load
        if(isset($_GET["map"]))
        {
          $map = new Map();
          $map->Load($mapID);
          $isNewMap = false;
        }
        else
        {
          $map = new Map();
          $map->Date = date(__("DATE_FORMAT"));
          $map->CategoryID = getUser()->DefaultCategoryID;
          $isNewMap = true;
        }
      }
      
      if(isset($save))
      {
        // validate
        // name
        if(trim($map->Name) == "") $errors[] = __("NO_MAP_NAME_ENTERED");
        // date
        if(trim($map->Date) == "") $errors[] = __("NO_DATE_ENTERED");
        if(!Helper::StringToTime($map->Date, true)) 
        {
          $errors[] = __("INVALID_DATE");
        }
        else
        {
          $map->Date = gmdate("Y-m-d H:i:s", Helper::StringToTime($map->Date, false));
        }

        // images
        $validMimeTypes = array("image/jpeg", "image/gif", "image/png");
        // map image
        $mapImageUploaded = ($_FILES["mapImage"]["tmp_name"] != "");
        if($mapImageUploaded) $mapImageInfo = getimagesize($_FILES["mapImage"]["tmp_name"]);
        if($mapImageUploaded && !in_array($mapImageInfo["mime"], $validMimeTypes)) $errors[] = sprintf(__("INVALID_MAP_IMAGE_FORMAT"), $_FILES["mapImage"]["name"]);
        // map image
        $blankMapImageUploaded = ($_FILES["blankMapImage"]["tmp_name"] != "");
        if($blankMapImageUploaded) $blankMapImageInfo = getimagesize($_FILES["blankMapImage"]["tmp_name"]);
        if($blankMapImageUploaded && !in_array($blankMapImageInfo["mime"], $validMimeTypes)) $errors[] = sprintf(__("INVALID_BLANK_MAP_IMAGE_FORMAT"), $_FILES["mapImage"]["name"]);
        if($isNewMap && !$mapImageUploaded && !$blankMapImageUploaded) $errors[] = __("NO_MAP_FILE_ENTERED");
        // thumbnail image
        $thumbnailImageUploaded = ($_FILES["thumbnailImage"]["tmp_name"] != "");
        if($thumbnailImageUploaded) $thumbnailImageInfo = getimagesize($_FILES["thumbnailImage"]["tmp_name"]);
        if($thumbnailImageUploaded && !in_array($thumbnailImageInfo["mime"], $validMimeTypes)) $errors[] = sprintf(__("INVALID_THUMBNAIL_IMAGE_FORMAT"), $_FILES["thumbnailImage"]["name"]);
        
        if(count($errors) == 0)
        {
          $thumbnailCreatedSuccessfully = false;
          $mapImageData = Helper::SaveTemporaryFileFromUploadedFile($_FILES["mapImage"]);
          if($mapImageData["error"] == "couldNotCopyUploadedFile") $errors[] = sprintf(__("MAP_IMAGE_COULD_NOT_BE_UPLOADED"), $_FILES["mapImage"]["name"]);  
          $blankMapImageData = Helper::SaveTemporaryFileFromUploadedFile($_FILES["blankMapImage"]);
          if($blankMapImageData["error"] == "couldNotCopyUploadedFile") $errors[] = sprintf(__("BLANK_MAP_IMAGE_COULD_NOT_BE_UPLOADED"), $_FILES["blankMapImage"]["name"]);  
          $thumbnailImageData = Helper::SaveTemporaryFileFromUploadedFile($_FILES["thumbnailImage"]);
          if($thumbnailImageData["error"] ==  "couldNotCopyUploadedFile") $errors[] = sprintf(__("THUMBNAIL_IMAGE_COULD_NOT_BE_UPLOADED"), $_FILES["thumbnailImage"]["name"]);  

          $error = null;
          if(count($errors) == 0) DataAccess::SaveMapAndThumbnailImage($map, $mapImageData["fileName"], $blankMapImageData["fileName"], $thumbnailImageData["fileName"], $error, $thumbnailCreatedSuccessfully);

          if($error) $errors[] = $error;  
          
          if($mapImageData["fileName"]) unlink($mapImageData["fileName"]);
          if($blankMapImageData["fileName"]) unlink($blankMapImageData["fileName"]);
          if($thumbnailImageData["fileName"]) unlink($thumbnailImageData["fileName"]);
          if(count($errors) == 0) Helper::Redirect("index.php?". Helper::CreateQuerystring(getUser()) . (!$thumbnailCreatedSuccessfully ? "&error=thumbnailCreationFailure" : ""));
        }
      }
      elseif(isset($deleteConfirmed))
      {
        DataAccess::DeleteMap($map);
        Helper::Redirect("index.php?". Helper::CreateQuerystring(getUser()));
      }

      $viewData["Errors"] = $errors;
      $viewData["Categories"] = getUser()->GetCategories();
      $viewData["Map"] = $map;
      $viewData["MapID"] = $mapID;
      $viewData["ConfirmDeletionButtonVisible"] = isset($delete);
      $viewData["Title"] = ($mapID ? sprintf(__("EDIT_MAP_X"), $map->Name) : __("ADD_MAP"));

      return $viewData;
    }
  }
?>
