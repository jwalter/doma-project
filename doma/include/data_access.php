<?php
  include_once(dirname(__FILE__) ."/helper.php");

  class DataAccess
  {
    public static function GetAllMaps($userID = 0)
    {
      $where[] = "U.Visible=1";
      if($userID) $where[] = "M.UserID=$userID";

      $sql = "SELECT M.*, M.ID AS MapID, M.Name AS Map_Name, C.*, C.Name AS CategoryName FROM `". DB_MAP_TABLE ."` M ".
             "LEFT JOIN `". DB_CATEGORY_TABLE ."` C ON C.ID=M.CategoryID ".
             "LEFT JOIN `". DB_USER_TABLE ."` U ON U.ID=M.UserID ".
             (count($where) > 0 ? "WHERE ". join(" AND ", $where) ." " : "").
             "ORDER BY M.Date DESC, M.ID DESC";
      return self::GetMapsUsersAndCategoriesFromSql($sql);
    }

    public static function GetMaps($userID = 0, $startDate = 0, $endDate = 0, $categoryID = 0, $count = 0, $orderBy = "date")
    {
      $startDateString = date(__("DATE_FORMAT_MYSQL"), $startDate);
      $endDateString = date(__("DATE_FORMAT_MYSQL"), $endDate);

      switch($orderBy)
      {
        case "lastChangedTime": $ob = "M.LastChangedTime DESC"; break;
        case "createdTime": $ob = "M.CreatedTime DESC"; break;
        case "ID": $ob = "M.ID DESC"; break;
        default: $ob = "M.Date DESC"; break;
      }

      $where[] = "U.Visible=1";
      if($userID) $where[] = "M.UserID=$userID";
      if($startDate) $where[] = "DATE(M.Date)>='$startDateString'";
      if($endDate) $where[] = "DATE(M.Date)<='$endDateString'";
      if($categoryID) $where[] = "M.CategoryID=$categoryID";

      $sql = "SELECT M.*, M.ID AS MapID, M.Name AS Map_Name, C.*, C.Name AS CategoryName, U.* FROM `". DB_MAP_TABLE ."` M ".
             "LEFT JOIN `". DB_CATEGORY_TABLE ."` C ON C.ID=M.CategoryID ".
             "LEFT JOIN `". DB_USER_TABLE ."` U ON U.ID=M.UserID ".
             (count($where) > 0 ? "WHERE ". join(" AND ", $where) ." " : "").
             "ORDER BY $ob, M.ID DESC".
             ($count ? " LIMIT 0, $count" : "");
      return self::GetMapsUsersAndCategoriesFromSql($sql);
    }

    public static function GetCloseMaps($latitude, $longitude, $startTime, $endTime, $maxDistance, $orderBy = "closeness")
    {
      $startTimeString = date(__("DATE_FORMAT_MYSQL"), $startTime);
      $endTimeString = date(__("DATE_FORMAT_MYSQL"), $endTime);

      switch($orderBy)
      {
        case "closeness": $ob = "Closeness ASC"; break;
        default: $ob = "M.Date ASC"; break;
      }

      $pi180 = M_PI/180;
      $latR = $latitude*$pi180;
      $lonR = $longitude*$pi180;
      $closenessSql = "ACOS(SIN(M.MapCenterLatitude*$pi180) * SIN($latR) + COS(M.MapCenterLatitude*$pi180) * COS($latR) * COS($lonR-M.MapCenterLongitude*$pi180)) * 6378200";

      $where[] = "IsGeocoded=1";
      $where[] = "$closenessSql < $maxDistance";
      if($startTime) $where[] = "M.SessionEndTime>='$startTimeString'";
      if($endTime) $where[] = "M.SessionStartTime<='$endTimeString'";

      $sql = "SELECT M.*, M.ID AS MapID, M.Name AS Map_Name, C.*, C.Name AS CategoryName, U.*, $closenessSql AS Closeness FROM `". DB_MAP_TABLE ."` M ".
             "LEFT JOIN `". DB_CATEGORY_TABLE ."` C ON C.ID=M.CategoryID ".
             "LEFT JOIN `". DB_USER_TABLE ."` U ON U.ID=M.UserID ".
             "WHERE ". join(" AND ", $where). " ".
             "ORDER BY $ob, M.ID DESC";
      return self::GetMapsUsersAndCategoriesFromSql($sql);
    }

    private static function GetMapsUsersAndCategoriesFromSql($sql)
    {
      $rs = self::Query($sql);
      $maps = array();
      while($r = mysql_fetch_assoc($rs))
      {
        $map = new Map();
        $r["ID"] = $r["MapID"];
        $r["Name"] = $r["Map_Name"];
        $map->LoadFromArray($r);

        $category = new Category();
        $r["ID"] = $r["CategoryID"];
        $r["Name"] = $r["CategoryName"];
        $category->LoadFromArray($r);
        $map->SetCategory($category);

        $user = new User();
        $r["ID"] = $r["UserID"];
        $user->LoadFromArray($r);
        $map->SetUser($user);

        $maps[$map->ID] = $map;
      }
      return $maps;
    }

    public static function GetMapByID($id)
    {
      $sql = "SELECT M.*, M.ID AS MapID, M.Name AS MapName, C.*, C.Name AS CategoryName FROM `". DB_MAP_TABLE ."` M ".
             "LEFT JOIN `". DB_CATEGORY_TABLE ."` C ON C.ID=M.CategoryID ".
             "WHERE M.ID=$id";
      $rs = self::Query($sql);

      if($r = mysql_fetch_assoc($rs))
      {
        $map = new Map();
        $r["ID"] = $r["MapID"];
        $map->LoadFromArray($r);

        $category = new Category();
        $r["ID"] = $r["CategoryID"];
        $r["Name"] = $r["CategoryName"];
        $category->LoadFromArray($r);
        $map->SetCategory($category);
        return $map;
      }
      return null;
    }

    public static function GetYearsByUserIDAndCategoryID($userID, $categoryID)
    {
      $sql = "SELECT DISTINCT YEAR(Date) AS Year FROM `". DB_MAP_TABLE ."` ".
             "WHERE UserID=$userID ". ($categoryID ? "AND CategoryID=$categoryID " : "").
             "ORDER BY Date ASC";
      $rs = self::Query($sql);

      $years = array();
      while($r = mysql_fetch_assoc($rs))
      {
        $years[] = $r["Year"];
      }
      return $years;
    }

    public static function GetYearsByUserID($userID)
    {
      return self::GetYearsByUserIDAndCategoryID($userID, 0);
    }

    public static function GetLastChangedTime($userID = 0)
    {
      $sql = "SELECT MAX(LastChangedTime) AS LastChangedTime FROM `". DB_MAP_TABLE ."` ".
             ($userID ? "WHERE UserID=$userID" : "");
      $r = mysql_fetch_assoc(self::Query($sql));
      return Helper::StringToTime($r["LastChangedTime"], true);
    }

    public static function GetLastCreatedTime($userID = 0)
    {
      $sql = "SELECT MAX(CreatedTime) AS LastCreatedTime FROM `". DB_MAP_TABLE ."` ".
             ($userID ? "WHERE UserID=$userID" : "");
      $r = mysql_fetch_assoc(self::Query($sql));
      return Helper::StringToTime($r["LastCreatedTime"], true);
    }

    public static function GetPreviousMap($userID, $mapID)
    {
      $sql = "SELECT * FROM `". DB_MAP_TABLE ."` WHERE (Date<(SELECT Date FROM `". DB_MAP_TABLE ."` WHERE ID=$mapID) OR (Date=(SELECT Date FROM `". DB_MAP_TABLE ."` WHERE ID=$mapID) AND ID<$mapID)) AND UserID=$userID ORDER BY Date DESC, ID DESC";
      if($r = mysql_fetch_assoc(self::Query($sql)))
      {
        $map = new Map();
        $map->LoadFromArray($r);
        return $map;
      }
      return null;
    }

    public static function GetNextMap($userID, $mapID)
    {
      $sql = "SELECT * FROM `". DB_MAP_TABLE ."` WHERE (Date>(SELECT Date FROM `". DB_MAP_TABLE ."` WHERE ID=$mapID) OR (Date=(SELECT Date FROM `". DB_MAP_TABLE ."` WHERE ID=$mapID) AND ID>$mapID)) AND UserID=$userID ORDER BY Date ASC, ID ASC";
      $r = mysql_fetch_assoc(self::Query($sql));
      if($r = mysql_fetch_assoc(self::Query($sql)))
      {
        $map = new Map();
        $map->LoadFromArray($r);
        return $map;
      }
      return null;
    }

    public static function DeleteMap($map)
    {
      $uploadDir = Helper::LocalPath(MAP_IMAGE_PATH ."/");
      self::DeleteMapImage($map);
      self::DeleteThumbnailImage($map);
      $map->Delete();
    }

    public static function SaveMapAndThumbnailImage($map, $mapImageFileName, $blankMapImageFileName, $thumbnailImageFileName, &$error, &$thumbnailCreatedSuccessfully)
    {
      $inputMapImageFileName = $mapImageFileName;
      $inputBlankMapImageFileName = $blankMapImageFileName;
      $inputThumbnailImageFileName = $thumbnailImageFileName;
      $isNewMap = !($map->ID);
      $uploadDir = Helper::LocalPath(MAP_IMAGE_PATH ."/");
      $thumbnailCreatedSuccessfully = true;
      $map->Save();
      $id = $map->ID;

      if($inputMapImageFileName)
      {
        // map image
        $extension = Helper::GetExtension($inputMapImageFileName);
        $uploadFileName = $uploadDir . $id . "." . $extension;
        self::DeleteMapImage($map);

        @chmod($uploadDir, 0777);
        copy($inputMapImageFileName, $uploadFileName);
        @chmod($uploadFileName, 0777);

        $map->MapImage = "$id.$extension";
        if(!$inputThumbnailImageFileName)
        {
          // auto-create thumbnail
          self::DeleteThumbnailImage($map);
          $thumbnailImageName = Helper::CreateThumbnail(
            Helper::LocalPath(MAP_IMAGE_PATH ."/$id.$extension"),
            Helper::LocalPath(MAP_IMAGE_PATH. "/$id.thumbnail"),
            THUMBNAIL_WIDTH,
            THUMBNAIL_HEIGHT,
            THUMBNAIL_SCALE,
            $thumbnailCreatedSuccessfully);
          $map->ThumbnailImage = basename($thumbnailImageName);
        }

        $map->AddGeocoding();
      }

      if($inputBlankMapImageFileName)
      {
        // blank map image
        $extension = Helper::GetExtension($inputBlankMapImageFileName);
        $uploadFileName = $uploadDir . $id . ".blank." . $extension;
        self::DeleteBlankMapImage($map);

        @chmod($uploadDir, 0777);
        copy($inputBlankMapImageFileName, $uploadFileName);
        @chmod($uploadFileName, 0777);

        $map->BlankMapImage = "$id.blank.$extension";
        if(!$inputThumbnailImageFileName && !$thumbnailImageName)
        {
          // autc-create thumbnail
          self::DeleteThumbnailImage($map);
          $thumbnailImageName = Helper::CreateThumbnail(
            Helper::LocalPath(MAP_IMAGE_PATH ."/$id.blank.$extension"),
            Helper::LocalPath(MAP_IMAGE_PATH. "/$id.thumbnail"),
            THUMBNAIL_WIDTH,
            THUMBNAIL_HEIGHT,
            THUMBNAIL_SCALE,
            $thumbnailCreatedSuccessfully);
          $map->ThumbnailImage = basename($thumbnailImageName);
        }

        if(!$map->IsGeocoded)
        {
          // add geocoding if it didn't exist in map image
          $map->AddGeocoding();
        }
      }


      if($inputThumbnailImageFileName)
      {
        // custom thumbnail image
        $extension = Helper::GetExtension($inputThumbnailImageFileName);
        $uploadFileName = $uploadDir . $id . ".thumbnail." . $extension;
        self::DeleteThumbnailImage($map);

        @chmod($uploadDir, 0777);
        copy($inputThumbnailImageFileName, $uploadFileName);
        @chmod($uploadFileName, 0777);
        $map->ThumbnailImage = "$id.thumbnail.$extension";
      }

      $map->LastChangedTime = gmdate("Y-m-d H:i:s");
      if($isNewMap) $map->CreatedTime = gmdate("Y-m-d H:i:s");

      self::SaveMapWaypoints($map);
      $map->Save();
      return true;

      if($isNewMap)
      {
        $user = self::GetUserByID($map->UserID);
        //todo: border gps coords
        $data = "user=". urlencode($user->Username) .
                "&map=". $map->ID.
                ($gpsData ? "&longitude=". $gpsData["Longitude"] ."&latitude=". $gpsData["Latitude"] : "");
        Helper::LogUsage("addMap", $data);
      }
    }
    
    public static function SaveMapWaypoints($map)
    {
      $ed = $map->GetQuickRouteJpegExtensionData();
      
      // first delete all existing waypoints
      $sql = "DELETE FROM `". DB_WAYPOINT_TABLE ."` WHERE MapID=". $map->ID; 
      self::Query($sql);
      if($ed->IsValid)
      {
        $waypoints = array();
        foreach($ed->Sessions[0]->Route->Segments as $segment)
        {
          foreach($segment->Waypoints as $w)
          {
            $values = array($map->ID, $w->Time, round($w->Position->Latitude * 3600000), round($w->Position->Longitude * 3600000));
            $waypoints[] = "(" . join(",", $values) . ")";
          }
        }
        if(count($waypoints) > 0)
        {
          $sql = "INSERT INTO `". DB_WAYPOINT_TABLE ."` (`MapID`, `Time`, `Latitude`, `Longitude`) VALUES ". join(",", $waypoints);
          self::Query($sql);
        }
      }
    }
    

    public static function DeleteMapImage($map)
    {
      $uploadDir = Helper::LocalPath(MAP_IMAGE_PATH ."/");
      if($map->MapImage) @unlink($uploadDir . $map->MapImage);
    }

    public static function DeleteBlankMapImage($map)
    {
      $uploadDir = Helper::LocalPath(MAP_IMAGE_PATH ."/");
      if($map->BlankMapImage) @unlink($uploadDir . $map->BlankMapImage);
    }

    public static function DeleteThumbnailImage($map)
    {
      $uploadDir = Helper::LocalPath(MAP_IMAGE_PATH ."/");
      if($map->ThumbnailImage) @unlink($uploadDir . $map->ThumbnailImage);
    }

    public static function IncreaseMapViews($map)
    {
      $sql = "UPDATE `". DB_MAP_TABLE ."` SET Views=Views+1 WHERE ID=". $map->ID;
      self::Query($sql);
    }

    public static function GetUserByID($id)
    {
      $sql = "SELECT * FROM `". DB_USER_TABLE ."` WHERE ID=$id";
      $rs = self::Query($sql);

      if($r = mysql_fetch_assoc($rs))
      {
        $user = new User();
        $user->LoadFromArray($r);
        return $user;
      }
      else
      {
        return null;
      }
    }

    public static function GetSingleUserID()
    {
      $sql = "SELECT ID FROM `". DB_USER_TABLE ."` WHERE Visible=1";
      $rs = self::Query($sql);

      if(mysql_num_rows($rs) == 1)
      {
        $r = mysql_fetch_assoc($rs);
        return $r["ID"];
      }
      return null;
    }

    public static function GetUserByUsernameAndPassword($username, $password)
    {
      $password = md5($password);
      $sql = "SELECT * FROM `". DB_USER_TABLE ."` WHERE Username='". addslashes($username) ."' AND Password='". addslashes($password)  ."' AND Visible=1";
      $rs = self::Query($sql);

      if($r = mysql_fetch_assoc($rs))
      {
        $user = new User();
        $user->LoadFromArray($r);
        return $user;
      }
      else
      {
        return null;
      }
    }

    public static function GetUserByUsername($username)
    {
      $sql = "SELECT * FROM `". DB_USER_TABLE ."` WHERE Username='". addslashes($username) ."'";
      $rs = self::Query($sql);

      if($r = mysql_fetch_assoc($rs))
      {
        $user = new User();
        $user->LoadFromArray($r);
        return $user;
      }
      else
      {
        return null;
      }
    }

    public static function UsernameExists($username, $excludeUserID)
    {
      if(!$excludeUserID) $excludeUserID = 0;
      $sql = "SELECT * FROM `". DB_USER_TABLE ."` WHERE LCASE(Username)='". addslashes(strtolower($username)) ."' AND NOT(ID=$excludeUserID)";
      $rs = self::Query($sql);

      return (mysql_num_rows($rs) > 0);
    }

    public static function GetUserSettings($userID)
    {
      $ret = array();
      $sql = "SELECT `Key`, `Value` FROM `". DB_USER_SETTING_TABLE ."` WHERE UserID=$userID";
      $rs = self::Query($sql);
      $user = self::GetUserByID($userID);

      while($r = mysql_fetch_assoc($rs))
      {
        $r["Value"] = str_replace("%userEmail%", $user->Email, $r["Value"]);
        $ret[$r["Key"]] = $r["Value"];
      }
      return $ret;
    }

    public static function GetAllUsers($visibleOnly)
    {
      $sql = "SELECT U.*, COUNT(M.ID) AS NoOfMaps ".
             "FROM `". DB_USER_TABLE ."` U ".
             "LEFT JOIN `". DB_MAP_TABLE ."` M ON U.ID=M.UserID ".
             ($visibleOnly ? "WHERE U.Visible=1 " : "").
             "GROUP BY U.ID ".
             "ORDER BY U.LastName, U.FirstName, U.ID";

      $rs = self::Query($sql);

      $users = array();
      while($r = mysql_fetch_assoc($rs))
      {
        $user = new User();
        $user->LoadFromArray($r);
        $user->NoOfMaps = $r["NoOfMaps"];
        $users[$user->ID] = $user;
      }

      return $users;
    }

    public static function DeleteUserByID($id)
    {
      // delete all map images
      $maps = self::GetAllMaps($id);
      foreach($maps as $m)
      {
        self::DeleteMapImage($m);
        self::DeleteThumbnailImage($m);
      }
      $sql = "DELETE FROM `". DB_MAP_TABLE ."` WHERE UserID=$id";
      self::Query($sql);
      $sql = "DELETE FROM `". DB_USER_SETTING_TABLE ."` WHERE UserID=$id";
      self::Query($sql);
      $sql = "DELETE FROM `". DB_USER_TABLE ."` WHERE ID=$id";
      self::Query($sql);
    }

    public static function GetLastMapsForUsers($param = "date")
    {
      switch($param)
      {
        case "lastChangedTime": $field = "LastChangedTime"; break;
        case "createdTime": $field = "CreatedTime"; break;
        default: $field = "Date"; break;
      }

      $ret = array();
      $sql = "SELECT * FROM `". DB_MAP_TABLE ."` a ".
             "INNER JOIN `". DB_MAP_TABLE ."` b ".
             "ON a.ID=b.ID ".
             "WHERE a.`$field`=(SELECT MAX(`$field`) from `". DB_MAP_TABLE ."` WHERE UserID=b.UserID)";
      $rs = self::Query($sql);
      while($r = mysql_fetch_assoc($rs))
      {
        $map = new Map();
        $map->LoadFromArray($r);
        $ret[$map->UserID] = $map;
      }
      return $ret;
    }

    public static function SaveUser($user, $categories, $defaultCategoryIndex, $userSettings)
    {
      $newUser = (!$user->ID);
      $user->Save();

      self::SaveUserCategories($user->ID, $categories);
      $categoriesIndexed = array_values($categories);
      $defaultCategoryID = $categoriesIndexed[$defaultCategoryIndex]->ID;
      if($defaultCategoryID)
      {
        $user->DefaultCategoryID = $defaultCategoryID;
        $user->Save();
      }

      self::SaveUserSettings($user->ID, $userSettings);
      if($newUser) Helper::LogUsage("createUser", "user=". urlencode($user->Username));
    }

    public static function SaveUserCategories($userID, &$categories)
    {
      // 1. get all existing categories for this user
      $existingCategories = self::GetCategoriesByUserID($userID);
      $existingCategoryIDs = array();
      // 2. Extract the ids
      foreach($existingCategories as $ec)
      {
        $existingCategoryIDs[$ec->ID] = $ec->ID;
      }

      // 3. Save all categories in $categories
      $count = 0;
      foreach($categories as &$c)
      {
        $c->UserID = $userID; // update with user id
        $c->Save();
        unset($existingCategoryIDs[$c->ID]);
      }

      // 4. Delete existing categories that are not found in $categories
      foreach($existingCategoryIDs as $ecID)
      {
        self::DeleteCategoryByID($ecID);
      }
    }

    public static function SaveUserSettings($userID, $settings)
    {
      // first delete all settings for this user
      $sql = "DELETE FROM `". DB_USER_SETTING_TABLE ."` WHERE UserID=$userID";
      self::Query($sql);
      // then insert new settings
      foreach($settings as $key => $value)
      {
        $sql = "INSERT INTO `". DB_USER_SETTING_TABLE ."` (`UserID`, `Key`, `Value`) ".
               "VALUES ($userID, '". addslashes($key) ."', '". addslashes($value) ."')";
        self::Query($sql);
      }
    }

    public static function GetCategoryByID($id)
    {
      $sql = "SELECT * FROM `". DB_CATEGORY_TABLE ."` WHERE ID=$id";
      $rs = self::Query($sql);

      if($r = mysql_fetch_assoc($rs))
      {
        $category = new Category();
        $category->LoadFromArray($r);
        return $category;
      }
      else
      {
        return null;
      }
    }

    public static function GetCategoriesByUserID($userID = 0)
    {
      $sql = "SELECT * FROM `". DB_CATEGORY_TABLE ."` ".
             ($userID ? "WHERE UserID=$userID " : "").
             "ORDER BY ID";
      $rs = self::Query($sql);

      $categories = array();
      while($r = mysql_fetch_assoc($rs))
      {
        $category = new Category();
        $category->LoadFromArray($r);
        $categories[$category->ID] = $category;
      }
      return $categories;
    }

    public static function GetCategoriesByUserIDAndYear($userID, $year)
    {
      if($year == 0) return self::GetCategoriesByUserID($userID);

      $sql = "SELECT * FROM `". DB_CATEGORY_TABLE ."` ".
             "WHERE ID IN(SELECT DISTINCT(CategoryID) FROM `". DB_MAP_TABLE ."` WHERE UserID=$userID AND YEAR(Date)=$year) ".
             "ORDER BY ID";
      $rs = self::Query($sql);

      $categories = array();
      while($r = mysql_fetch_assoc($rs))
      {
        $category = new Category();
        $category->LoadFromArray($r);
        $categories[$category->ID] = $category;
      }
      return $categories;
    }

    // returns false and doesn't delete when there are maps in the category
    public static function DeleteCategoryByID($id)
    {
      if(self::NoOfMapsInCategory($id) == 0)
      {
        $sql = "DELETE FROM `". DB_CATEGORY_TABLE ."` WHERE ID=$id";
        self::Query($sql);
        return true;
      }
      return false;
    }

    public static function NoOfMapsInCategory($id)
    {
      if(!$id) return 0;
      $sql = "SELECT COUNT(*) AS NoOfMaps FROM `". DB_MAP_TABLE ."` ".
             "WHERE CategoryID=$id";
      $r = mysql_fetch_assoc(self::Query($sql));

      return $r["NoOfMaps"];
    }

    public static function DeleteAllUsers()
    {
      self::Query('DELETE FROM `'. DB_MAP_TABLE ."`");
      self::Query('DELETE FROM `'. DB_USER_TABLE ."`");
      self::Query('DELETE FROM `'. DB_USER_SETTING_TABLE ."`");
      self::Query('DELETE FROM `'. DB_CATEGORY_TABLE ."`");
      Helper::DeleteFiles(MAP_IMAGE_PATH, "*.*");
    }

    public static function GetSetting($key, $defaultValue)
    {
      $sql = "SELECT `Value` FROM `". DB_SETTING_TABLE ."` WHERE `Key`='". addslashes($key) ."'";
      $rs = self::Query($sql);
      if($r = @mysql_fetch_assoc($rs))
      {
        return $r["Value"];
      }
      return $defaultValue;
    }

    public static function SetSetting($key, $value)
    {
      $sql = "REPLACE INTO `". DB_SETTING_TABLE ."` (`Key`, `Value`) VALUES ('". addslashes($key) ."', '". addslashes($value) ."')";
      self::Query($sql);
    }

    private static function Query($sql)
    {
      $result = @mysql_query($sql);
      Helper::WriteToLog($sql);
      if(mysql_error()) Helper::WriteToLog("MYSQL ERROR: ". mysql_error());
      return $result;
    }
  }
?>