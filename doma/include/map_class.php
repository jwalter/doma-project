<?php
  require_once(dirname(__FILE__) ."/database_object.php");
  require_once(dirname(__FILE__) ."/data_access.php");
  require_once(dirname(__FILE__) ."/../entities/GeocodedMap.php");
  require_once(dirname(__FILE__) ."/../lib/Matrix.php");
  require_once(dirname(__FILE__) ."/../entities/LatLng.php");
  require_once(dirname(__FILE__) ."/../entities/Point.php");

  class Map extends DatabaseObject
  {
    protected $DBTableName = DB_MAP_TABLE;
    protected $ClassName = "Map";
    public $Data = array(
      "ID" => 0,
      "UserID" => 0,
      "CategoryID" => 0,
      "Date" => 0,
      "Name" => "",
      "Organiser" => "",
      "Country" => "",
      "Discipline" => "",
      "RelayLeg" => "",
      "MapName" => "",
      "ResultListUrl" => "",
      "MapImage" => "",
      "BlankMapImage" => "",
      "ThumbnailImage" => "",
      "Comment" => "",
      "Views" => 0,
      "LastChangedTime" => null,
      "CreatedTime" => null,
      "IsGeocoded" => 0,
      "MapCenterLatitude" => null,
      "MapCenterLongitude" => null,
      "MapCorners" => null,
      "SessionStartTime" => null,
      "SessionEndTime" => null,
      "Distance" => null,
      "StraightLineDistance" => null,
      "ElapsedTime" => null
    );
    private $User;
    private $Category;
    private $QuickRouteJpegExtensionData;
    private $QuickRouteJpegExtensionDataNotPresent;
    private $Exif;
    private $ExifNotPresent;


    public function CreateResultListUrl()
    {
      if($this->ResultListUrl != "")
      {
        if(strtolower(substr($this->ResultListUrl, 0, 4)) != "http")
        {
          return "http://". $this->ResultListUrl;
        }
        return $this->ResultListUrl;
      }
      return "";
    }

    public function GetUser()
    {
      if(!$this->User) $this->User = DataAccess::GetUserByID($this->UserID);
      return $this->User;
    }

    public function SetUser($user)
    {
      $this->User = $user;
    }

    public function GetCategory()
    {
      if(!$this->Category) $this->Category = DataAccess::GetCategoryByID($this->CategoryID);
      return $this->Category;
    }

    public function SetCategory($category)
    {
      $this->Category = $category;
    }

    public function GetMapCornerArray()
    {
      if($this->IsGeocoded)
      {
        $ed = $this->GetQuickRouteJpegExtensionData();
        $arr = split(",", $this->MapCorners);
        return array("SW" => array("Longitude" => $arr[0], "Latitude" => $arr[1]),
                     "NW" => array("Longitude" => $arr[2], "Latitude" => $arr[3]),
                     "NE" => array("Longitude" => $arr[4], "Latitude" => $arr[5]),
                     "SE" => array("Longitude" => $arr[6], "Latitude" => $arr[7]));
      }
      return null;
    }

    public function GetQuickRouteJpegExtensionData($calculate = true)
    {
      if(!$this->IsGeocoded) $this->QuickRouteJpegExtensionDataNotPresent = true;
      if($this->QuickRouteJpegExtensionDataNotPresent) return null;
      
      // is there a cached value?
      if($this->QuickRouteJpegExtensionData != null) return $this->QuickRouteJpegExtensionData; // yes, use it
      // no cached value, get it
      $this->QuickRouteJpegExtensionData = new QuickRouteJpegExtensionData(MAP_IMAGE_PATH ."/" . $this->MapImage);
      if($this->QuickRouteJpegExtensionData->IsValid) 
      {
        if($calculate) $this->QuickRouteJpegExtensionData->Calculate();
        return $this->QuickRouteJpegExtensionData;
      }
      else
      {
        // this should not happen
        $this->QuickRouteJpegExtensionDataNotPresent = true;
        return null;
      }
    }

    public function GetExifData()
    {
      if(!$this->Exif && !$this->ExifNotPresent)
      {
        $this->Exif = @exif_read_data(Helper::GetMapImage($map), 0, true);
        $this->ExifNotPresent = ($this->Exif == null);
      }
      return $this->Exif;
    }

    public function GetExifGpsData()
    {
      $exif = $this->GetExifData();
      if($exif["GPS"])
      {
        $coord = $exif["GPS"]["GPSLongitude"];
        $ds = split("/", $coord[0]);
        $ms = split("/", $coord[1]);
        $ss = split("/", $coord[2]);
        $lon = $ds[0] / $ds[1] +
               $ms[0] / $ms[1] / 60 +
               $ss[0] / $ss[1] / 3600;
        if($exif["GPS"]["GPSLongitudeRef"] == "W") $lon = -$lon;

        $coord = $exif["GPS"]["GPSLatitude"];
        $ds = split("/", $coord[0]);
        $ms = split("/", $coord[1]);
        $ss = split("/", $coord[2]);
        $lat = $ds[0] / $ds[1] +
               $ms[0] / $ms[1] / 60 +
               $ss[0] / $ss[1] / 3600;
        if($exif["GPS"]["GPSLatitudeRef"] == "S") $lat = -$lat;

        return array("Longitude" => $lon, "Latitude" => $lat);
      }
      return null;
    }

    public function CreateKmlString($mapImagePath = null)
    {
      if(!$this->IsGeocoded) return null;

      if($mapImagePath == null) $mapImagePath = MAP_IMAGE_PATH;

      $ed = $this->GetQuickRouteJpegExtensionData();
      
      $size = $this->GetMapImageSize();
      $latLngs = array(
        new LatLng($ed->ImageCornerPositions["NW"]->Latitude, $ed->ImageCornerPositions["NW"]->Longitude),
        new LatLng($ed->ImageCornerPositions["SE"]->Latitude, $ed->ImageCornerPositions["SE"]->Longitude));
      $points = array(
        new Point(0, 0),
        new Point($size["Width"]-1, $size["Height"]-1));
      
      $geocodedMap = new GeocodedMap();
      $geocodedMap->createFromCoordinatePairs($latLngs, $points, $mapImagePath ."/". $this->MapImage);
      return $geocodedMap->saveToString("kml");
    }
    
    public function GetMapImageSize()
    {
      $size = getimagesize(Helper::LocalPath(MAP_IMAGE_PATH ."/" . $this->MapImage));
      return array("Width" => $size[0], "Height" => $size[1]);
    }
    
    /*
    public function CreateKml($mapImagePath, $includeRouteLine = false)
    {
      $ed = $this->GetQuickRouteJpegExtensionData();
      if(!$ed->IsValid) return null;

      $doc = new DOMDocument("1.0", "utf-8");
      $doc->formatOutput = true;

      $kml = $doc->createElement("kml");
      // todo: add xmlns
      $doc->appendChild($kml);
      $folder = $doc->createElement("Folder");
      $kml->appendChild($folder);
      // todo: name element

      $groundOverlay = $doc->createElement("GroundOverlay");
      $folder->appendChild($groundOverlay);
      $e = $doc->createElement("name");
      $e->appendChild($doc->createTextNode("Map")); // todo: language
      $groundOverlay->appendChild($e);

      $href = $doc->createElement("href");
      $href->appendChild($doc->createTextNode($mapImagePath . "/". $this->MapImage));
      $icon = $doc->createElement("Icon");
      $icon->appendChild($href);
      $groundOverlay->appendChild($icon);

      // todo: algorithm
      $latLonBox = $doc->createElement("LatLonBox");
      $corners = $ed->ImageCornerPositions;
      $e = $doc->createElement("north");
      $e->appendChild($doc->createTextNode((doubleval($corners["NW"]->Latitude)+doubleval($corners["NE"]->Latitude))/2));
      $latLonBox->appendChild($e);
      $e = $doc->createElement("south");
      $e->appendChild($doc->createTextNode((doubleval($corners["SW"]->Latitude)+doubleval($corners["SE"]->Latitude))/2));
      $latLonBox->appendChild($e);
      $e = $doc->createElement("east");
      $e->appendChild($doc->createTextNode((doubleval($corners["NE"]->Longitude)+doubleval($corners["SE"]->Longitude))/2));
      $latLonBox->appendChild($e);
      $e = $doc->createElement("west");
      $e->appendChild($doc->createTextNode((doubleval($corners["NW"]->Longitude)+doubleval($corners["SW"]->Longitude))/2));
      $latLonBox->appendChild($e);
	  
      $e = $doc->createElement("rotation");
      $e->appendChild($doc->createTextNode(self::Rotation($corners)));
      $latLonBox->appendChild($e); 
      //todo: rotation          <rotation>-0.466708391838466</rotation>
      $groundOverlay->appendChild($latLonBox);

      if($includeRouteLine)
      {
        // draw the route line
        $style = $doc->createElement("Style");
        $attr = $doc->createAttribute("id");
        $attr->appendChild($doc->createTextNode("lineStyle"));
        $style->appendChild($attr);

        $lineStyle = $doc->createElement("LineStyle");

        $e = $doc->createElement("color");
        $e->appendChild($doc->createTextNode("7f0000ff"));
        $lineStyle->appendChild($e);

        $e = $doc->createElement("width");
        $e->appendChild($doc->createTextNode("4"));
        $lineStyle->appendChild($e);

        $style->appendChild($lineStyle);
        $folder->appendChild($style);

        foreach($ed->Sessions[0]->Route->Segments as $segment)
        {
          $placemark = $doc->createElement("Placemark");

          $e = $doc->createElement("name");
          $e->appendChild($doc->createTextNode("Route")); // todo: language
          $placemark->appendChild($e);

          $e = $doc->createElement("styleUrl");
          $e->appendChild($doc->createTextNode("#lineStyle"));
          $placemark->appendChild($e);

          $lineString = $doc->createElement("LineString");

          $e = $doc->createElement("extrude");
          $e->appendChild($doc->createTextNode("1"));
          $lineString->appendChild($e);

          $e = $doc->createElement("tessellate");
          $e->appendChild($doc->createTextNode("1"));
          $lineString->appendChild($e);

          $coords = array();
          foreach($segment->Waypoints as $waypoint)
          {
            $coords[] = $waypoint->Position->Longitude .",". $waypoint->Position->Latitude;
          }
          $e = $doc->createElement("coordinates");
          $e->appendChild($doc->createTextNode(join(" ", $coords)));
          $lineString->appendChild($e);

          $placemark->appendChild($lineString);
          $folder->appendChild($placemark);
        }
      }
      
      return $doc->saveXML();
    }

    private function Rotation($corner)
    {
      $b = doubleval($corner["NW"]->Latitude) - doubleval($corner["SW"]->Latitude);
      $a = doubleval($corner["NW"]->Longitude) - doubleval($corner["SW"]->Longitude);
      $alpha = asin($a/(sqrt($a*$a+$b*$b)));
          Helper::WriteToLog("Rotation: ".rad2deg($alpha));
      return -1*rad2deg($alpha);
    }
    
    */

    public function GetDistanceToLongLat($longitude, $latitude)
    {
      if(!$this->IsGeocoded) return null;

      $pi180 = M_PI/180;
      $latR = $latitude*$pi180;
      $lonR = $longitude*$pi180;
      return acos(sin($this->MapCenterLatitude*$pi180) * sin($latR) +
                  cos($this->MapCenterLatitude*$pi180) * cos($latR) * cos($lonR-$this->MapCenterLongitude*$pi180)) *
             6378200;
    }
  }


?>
