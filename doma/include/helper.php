<?php
  // Some often-used functions that needs short names are wrapped and placed in global scope

  function __($key, $htmlSpecialChars = false)
  {
    return Helper::__($key, $htmlSpecialChars);
  }
  
  function hsc($string)
  {
    return Helper::Hsc($string);
  }
  
  function getUser()
  {
    return Helper::GetUser();  
  }
  
  class Helper
  {
    public static function __($key, $htmlSpecialChars = false)
    {
      $ls = Session::GetLanguageStrings();
      $value = $ls[$key];
      if($htmlSpecialChars) return hsc($value);
      return $value;
    }

    public static function Hsc($string)
    {
      return htmlspecialchars($string, ENT_QUOTES, "UTF-8");
    }
    
    // creates language strings for a certain user
    public static function GetLanguageStrings($userID = 0)
    {
      // 1. application-wide strings
      $cs = self::GetCustomizableStrings();
      $settings = array_merge($cs["settings"], self::GetNonCustomizableStrings());
      
      // 2. user-specific settings
      $userSettings = array();
      if($userID) $userSettings = DataAccess::GetUserSettings($userID);
      foreach($userSettings as $key => $value)
      {
        $settings[$key] = $value;
      }
      return $settings;
    }
    
    public static function GetCustomizableStrings()
    {
      $settings = array();
      $descriptions = array();
      $languageFileName = self::GetRootUrl(false) ."languages/". Session::GetLanguageFileShort();
      $xml = simplexml_load_file($languageFileName);
      $count = count($xml->customizable->string);
      for($i = 0; $i < $count; $i++) 
      {
        $attrs = $xml->customizable->string[$i]->attributes();
        $key = $attrs["key"];
        $description = $attrs["description"];
        $value = $xml->customizable->string[$i];
        $settings["$key"] = trim("$value");
        $descriptions["$key"] = trim("$description");
      }
      return array("settings" => $settings, "descriptions" => $descriptions);
    }

    private static function GetNonCustomizableStrings()
    {
      $settings = array();
      $languageFileName = self::GetRootUrl(false) ."languages/". Session::GetLanguageFileShort();
      $xml = simplexml_load_file($languageFileName);
      $count = count($xml->nonCustomizable->string);
      for($i = 0; $i < $count; $i++) 
      {
        $attrs = $xml->nonCustomizable->string[$i]->attributes();
        $key = $attrs["key"];
        $value = $xml->nonCustomizable->string[$i];
        $value = trim("$value");
        $value = str_replace("%adminEmail%", ADMIN_EMAIL, $value);
        $settings["$key"] = $value;
      }
      return $settings;
    }
    
    public static function CreateQuerystring($user, $mapID = 0)
    {
      $qs = "user=". urlencode($user->Username);
      if($mapID) $qs .= "&amp;map=". $mapID;
      return $qs;
    }
    
    public static function Redirect($url)
    {
      header("Location: $url");
      die();   
    }
    
    public static function GetRootUrl($webRootMode = true)
    {
      if($webRootMode)
        $dirName = dirname($_SERVER["SCRIPT_NAME"]);
      else
        $dirName = dirname($_SERVER["SCRIPT_FILENAME"]);
      if(substr($dirName, -1) == "/") return $dirName;
      return $dirName ."/";
    }

    public static function GetWebsiteUrl()
    {
      if(isset($_SERVER["SCRIPT_URI"]))
      {
        return dirname($_SERVER["SCRIPT_URI"]);
      }
      else
      {
        return "http://". dirname($_SERVER["HTTP_HOST"] . $_SERVER["SCRIPT_NAME"]);        
      }
    }

    public static function LoginAdmin($username, $password)
    {
      if(stripslashes($username) == ADMIN_USERNAME && stripslashes($password) == ADMIN_PASSWORD)
      {
        Session::SetIsLoggedInAdmin(true);
        return true;
      }
      return false;
    }

    public static function IsLoggedInAdmin()
    {
       return Session::GetIsLoggedInAdmin(true);
    }

    public static function LogoutAdmin()
    {
       Session::SetIsLoggedInAdmin(null);
    }
    
    public static function LoginUser($username, $password)
    {
      $user = DataAccess::GetUserByUsernameAndPassword($username, $password);
      if($user)
      {
        Session::SetLoggedInUser($user);
        self::SetUser($user);
        return true;
      }
      return false;
    }
    
    public static function LoginUserByUsername($username)
    {
      $user = DataAccess::GetUserByUsername($username);
      if($user)
      {
        Session::SetLoggedInUser($user);
        self::SetUser($user);
        return true;
      }
      return false;
    }
    

    public static function IsLoggedInUser()
    {
      $user = self::GetLoggedInUser();
      return isset($user);
    }

    public static function LogoutUser()
    {
      Session::SetLoggedInUser(null);
    }

    public static function GetLoggedInUser()
    {
      return Session::GetLoggedInUser();
    }
    
    // the user as specified by $_GET["user"] / $_POST["user"]
    public static function GetUser()
    {
      return Session::GetDisplayedUser();
    }
    
    // the user as specified by $_GET["user"] / $_POST["user"]
    public static function SetUser($user)
    {
      if($_GET["lang"])
      {
        if(strrpos(strtolower(LANGUAGES_AVAILABLE),$_GET["lang"])!==false) Session::SetLanguageFileShort(strtolower($_GET["lang"]).".xml");
      }
      else
      {
        if(!Session::GetLanguageFileShort()) Session::SetLanguageFileShort(LANGUAGE_FILE);
      }
      
      $languageFileName = self::GetRootUrl(false) ."languages/". Session::GetLanguageFileShort();
      $languageFileNameAndDate = $languageFileName ."_". filemtime($languageFileName);
     
      // some caching logic for language strings
      $previousUser = self::GetUser();
      $loadStrings = ($previousUser || $user || Session::GetLanguageFile() != $languageFileNameAndDate); 

      if(!Session::GetLanguageStrings()) $loadStrings = true;
      
      Session::SetDisplayedUser($user);
      if($loadStrings) 
      {
        Session::SetLanguageStrings(Helper::GetLanguageStrings($user ? $user->ID : 0));
        Session::SetLanguageFile($languageFileNameAndDate);
      }
    }

    public static function GetThumbnailImage(Map $map, $webRootMode = true)
    {
      return self::GetRootUrl($webRootMode) . MAP_IMAGE_PATH ."/". $map->ThumbnailImage;
    }

    public static function GetMapImage(Map $map, $webRootMode = true)
    {
      return self::GetRootUrl($webRootMode) . MAP_IMAGE_PATH ."/". $map->MapImage;
    }
    
    public static function GetBlankMapImage(Map $map, $webRootMode = true)
    {
      return self::GetRootUrl($webRootMode) . MAP_IMAGE_PATH ."/". $map->BlankMapImage;
    }
    
    public static function DatabaseVersionIsValid()
    {
      $databaseVersion = DataAccess::GetSetting("DATABASE_VERSION", "0.0");
      return (version_compare($databaseVersion, DOMA_VERSION) >= 0);
    }

    public static function EncapsulateLink($linkText, $url)
    {
      if($url == "")
      {
        return $linkText;
      }
      else
      {
        return '<a href="'. $url .'">'. $linkText .'</a>';
      }
    }

    public static function DateToLongString($d)
    {
      $dayNames = split(";", __("DAY_NAMES"));
      $monthNames = split(";", __("MONTH_NAMES"));
      return $dayNames[date("w", $d)] ." ".
             date("j", $d) ." ".
             $monthNames[date("n", $d) - 1] ." ".
             date("Y", $d);
    }
    
    public static function StringToTime($string, $utc)
    {
      return strtotime($string . ($utc ? " UTC" : ""));  
    }

    private static function ImageIsResizable($fileName)
    {
      $contents = @file_get_contents(self::GetWebsiteUrl() ."/include/image_is_resizable.php?filename=". $fileName);
      return ($contents == "1");
    }

    public static function ImageCreateFromGeneral($fileName)
    {
      switch(strtolower(self::GetExtension($fileName)))
      {
        case "png":
          $image = ImageCreateFromPng($fileName);
          break;
        case "gif":
          $image = ImageCreateFromGif($fileName);
          break;
        default:
          $image = ImageCreateFromJpeg($fileName);
          break;
      }
      return $image;
    }

    public static function GetExtension($fileName)
    {
      $pathinfo = pathinfo($fileName);
      return $pathinfo["extension"];
    }
    
    public static function GetFilenameWithoutExtension($fileName)
    {
      $extension = self::GetExtension($fileName);
      if($extension) return basename($fileName, ".". self::GetExtension($fileName));
      basename($fileName);
    }

    public static function CreateThumbnail($sourceFileName, $targetFileNameWithoutExtension, $targetWidth, $targetHeight, $targetZoom, &$thumbnailCreatedSuccessfully)
    {
      if(self::ImageIsResizable($sourceFileName))
      {
        $sourceImage = self::ImageCreateFromGeneral($sourceFileName);
        $targetFileName = $targetFileNameWithoutExtension .".". self::GetExtension($sourceFileName);

        $sourceWidth = ImageSX($sourceImage);
        $sourceHeight = ImageSY($sourceImage);

        $targetImage = ImageCreateTrueColor($targetWidth, $targetHeight);

        if($targetZoom * $sourceWidth < $targetWidth) $targetZoom = $targetWidth / $sourceWidth;
        if($targetZoom * $sourceHeight < $targetHeight) $targetZoom = $targetHeight / $sourceHeight;

        $sourceClippedWidth = $targetWidth / $targetZoom;
        $sourceClippedHeight = $targetHeight / $targetZoom;
        $sourceCenterX = $sourceWidth / 2;
        $sourceCenterY = $sourceHeight / 2;

        $sourceX = $sourceCenterX - $sourceClippedWidth / 2;
        $sourceY = $sourceCenterY - $sourceClippedHeight / 2;

        ImageCopyResampled(
          $targetImage,
          $sourceImage,
          0,
          0,
          $sourceX,
          $sourceY,
          $targetWidth,
          $targetHeight,
          $sourceClippedWidth,
          $sourceClippedHeight);

        ImageDestroy($sourceImage);

        @ImageJpeg($targetImage, $targetFileName);
        ImageDestroy($targetImage);
        $thumbnailCreatedSuccessfully = true;
      }
      else
      {
        // make thumbnail displaying standard 64x64 image icon
        $sourceImage = ImageCreateFromPng("gfx/imageFileIcon.png");

        $sourceWidth = ImageSX($sourceImage);
        $sourceHeight = ImageSY($sourceImage);

        $targetImage = ImageCreateTrueColor($targetWidth, $targetHeight);
        $targetFileName = $targetFileNameWithoutExtension .".png";

        $white = imagecolorallocate($targetImage, 255, 255, 255);
        ImageFilledRectangle($targetImage, 0, 0, $targetWidth - 1, $targetHeight - 1, $white);
        imagecolordeallocate($targetImage, $white);

        $targetCenterX = $targetWidth / 2;
        $targetCenterY = $targetHeight / 2;

        $targetX = $targetCenterX - $sourceWidth / 2;
        $targetY = $targetCenterY - $sourceHeight / 2;

        ImageCopy($targetImage,$sourceImage, $targetX, $targetY, 0, 0, $sourceWidth, $sourceHeight);

        ImageDestroy($sourceImage);

        @ImagePng($targetImage, $targetFileName);
        ImageDestroy($targetImage);
        $thumbnailCreatedSuccessfully = false;
      }
      return $targetFileName;
    }
    
    public static function CreateTopbar()
    {
      $isLoggedIn = (Helper::IsLoggedInUser() && Helper::GetLoggedInUser()->ID == getUser()->ID);
      ?>
      <div id="topbar">
        <div class="inner">
          <div class="left">
            <a href="index.php?<?php print Helper::CreateQuerystring(getUser())?>"><?php printf(__("DOMA_FOR_X"), getUser()->FirstName ." ". getUser()->LastName); ?></a>
            <span class="separator">|</span>
            <?php if(!$isLoggedIn) { ?>
              <a href="login.php?<?php print Helper::CreateQuerystring(getUser())?>"><?php print __("LOGIN")?></a>
            <?php } else { ?>
              <a href="edit_map.php?<?php print Helper::CreateQuerystring(getUser())?>"><?php print __("ADD_MAP")?></a>
              <span class="separator">|</span>
              <a href="edit_user.php?<?php print Helper::CreateQuerystring(getUser())?>"><?php print __("USER_PROFILE")?></a>
              <span class="separator">|</span>
              <a href="login.php?<?php print Helper::CreateQuerystring(getUser())?>&amp;action=logout"><?php print __("LOGOUT")?></a>
            <?php } ?>
          </div>
          <div class="right">
            <a href="users.php"><?php print __("ALL_USERS")?></a>
            <span class="separator">|</span>
            <?php Helper::ShowLanguages();?>
            <span class="separator">|</span>
            <a href="http://www.matstroeng.se/doma/?version=<?php print DOMA_VERSION?>"><?php printf(__("DOMA_VERSION_X"), DOMA_VERSION); ?></a>
          </div>
          <div class="clear"></div>
        </div>
      </div>
      <?php
    }

    public static function CreateUserListTopbar()
    {
      $isLoggedIn = Helper::IsLoggedInAdmin();
      ?>
      <div id="topbar">
        <div class="inner">
          <div class="left">
            <a href="users.php"><?php print _SITE_TITLE?></a>
            <span class="separator">|</span>
            <?php if(!$isLoggedIn) { ?>
              <a href="admin_login.php"><?php print __("ADMIN_LOGIN")?></a>
            <?php } else { ?>
              <a href="edit_user.php?mode=admin"><?php print __("ADD_USER")?></a>
              <span class="separator">|</span>
              <a href="admin_login.php?action=logout"><?php print __("ADMIN_LOGOUT")?></a>
            <?php } ?>
          </div>
          <div class="right">
            <?php Helper::ShowLanguages();?>
            <span class="separator">|</span>
            <a href="http://www.matstroeng.se/doma/?<?php print DOMA_VERSION?>"><?php printf(__("DOMA_VERSION_X"), DOMA_VERSION); ?></a>
          </div>
          <div class="clear"></div>
        </div>
      </div>
      <?php
    }
    
    public static function LogUsage($action, $data)
    {
      @file(DOMA_SERVER ."?url=". urlencode(self::GetWebsiteUrl()) ."&action=". urlencode($action) ."&data=". urlencode($data));
    }
    
    public static function SendEmail($fromName, $toEmail, $subject, $body)
    {
      if(ADMIN_EMAIL == "email@yourdomain.com") return false; // the address is the default one, don't send
      $header = "From: ". utf8_decode($fromName) . " <" . ADMIN_EMAIL . ">\r\n";
      ini_set('sendmail_from', ADMIN_EMAIL);
      $result = mail($toEmail, utf8_decode($subject), utf8_decode($body), $header);
      return $result;
    }
    
    public static function IsValidEmailAddress($emailAddress)
    {
      return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $emailAddress);
    }
    
    public static function CreatePassword($length)
    {
      $password = "";
      $chars = "abcdefghijkmnpqrstuvwxyz23456789";
      for($i=0; $i<$length; $i++)
      {
        $password .= substr($chars, rand(0, strlen($chars)-1), 1);
      }
      return $password;
    }

    public static function WriteToLog($message)
    {
      if(defined("LOG"))
      {
        $microtime = split(" ", microtime());
        $timeString = date("Y-m-d H:i:s") . substr($microtime[0], 1, 4);
        $fp = fopen(LOG_FILE_NAME, "a");
        fwrite($fp, $timeString ." ". $message ."\n");
        fclose($fp);
      }
    }

    public static function ClearLog()
    {
      if(defined("LOG"))
      {
        unlink(LOG_FILE_NAME);
      }
    }
    
    public static function DeleteFiles($path, $pattern)
    {
      if(substr($path, strlen($path) - 1, 1) != "/") $path = $path ."/";
      $dirs = glob($path ."*");
      $files = glob($path . $pattern);
      
      if(is_array($files))
      {
        foreach($files as $file)
        {
          if(is_file($file))
          {
            unlink($file);
          }
        }
      }
      if(is_array($dirs))
      {
        foreach($dirs as $dir)
        {
          if(is_dir($dir))
          {
            $dir = basename($dir) . "/";
            self::DeleteFiles($path . $dir, $pattern);
          }
        }
      }
    }
    
    public static function SaveTemporaryFileFromUploadedFile($uploadedFile)
    {
      $temporaryDirectory = Helper::GetRootUrl(false) . TEMP_FILE_PATH ."/";
      if($uploadedFile['name'])
      {
        $extension = Helper::GetExtension($uploadedFile['name']);
        $fileName = $temporaryDirectory . rand(0, 1000000000) .".". $extension;
        if(!move_uploaded_file($uploadedFile['tmp_name'], $fileName))
        {
          $error = "couldNotCopyUploadedFile";
        }
      }
      return array("fileName" => $fileName, "error" => $error);
    }
    
    public static function SaveTemporaryFileFromFileData($fileData, $extension)
    {
      $temporaryDirectory = Helper::GetRootUrl(false) . TEMP_FILE_PATH ."/";
      $fileName = $temporaryDirectory . rand(0, 1000000000) .".". $extension;
      $fp = fopen($fileName, "w");
      fwrite($fp, $fileData);
      fclose($fp); 
      return array("fileName" => $fileName, "error" => $error);
    }
    public static function ShowLanguages()
    {
      $langs = split("\|", LANGUAGES_AVAILABLE);
      if(is_array($langs))
      {
        print __("LANGUAGE").": ";
        $pos = strrpos($_SERVER['REQUEST_URI'], "?");
        $a = ($pos === false) ? "?" : "&";
        foreach ($langs as $lang)
        {
          print '<a href="'.$_SERVER['REQUEST_URI'].$a.'lang='.strtolower($lang).'">'.$lang.'</a>&nbsp;&nbsp;';
        }
      }
    }
  
  }

?>