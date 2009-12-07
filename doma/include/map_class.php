<?php
  include_once(dirname(__FILE__) ."/database_object.php");
  include_once(dirname(__FILE__) ."/data_access.php");

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
      $this->QuickRouteJpegExtensionData = new QuickRouteJpegExtensionData(MAP_IMAGE_PATH ."/" . $this->MapImage);
      if(!$this->QuickRouteJpegExtensionData) $this->QuickRouteJpegExtensionDataNotPresent = true;
      if($calculate) $this->QuickRouteJpegExtensionData->Calculate();
      return $this->QuickRouteJpegExtensionData;
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

    public function CreateKml($mapImagePath)
    {
      $ed = $this->GetQuickRouteJpegExtensionData();
      if(!$ed) return null;

      $doc = new DOMDocument("1.0", "utf-8");
      $doc->formatOutput = true;

      $kml = $doc->createElement("kml");
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

      $latLonBox = $doc->createElement("LatLonBox");
      $corners = $ed->ImageCornerPositions;
      $e = $doc->createElement("north");
      $e->appendChild($doc->createTextNode(max($corners["NW"]->Latitude, $corners["NE"]->Latitude)));
      $latLonBox->appendChild($e);
      $e = $doc->createElement("south");
      $e->appendChild($doc->createTextNode(min($corners["SW"]->Latitude, $corners["SE"]->Latitude)));
      $latLonBox->appendChild($e);
      $e = $doc->createElement("east");
      $e->appendChild($doc->createTextNode(max($corners["NW"]->Longitude, $corners["SW"]->Longitude)));
      $latLonBox->appendChild($e);
      $e = $doc->createElement("west");
      $e->appendChild($doc->createTextNode(min($corners["NE"]->Longitude, $corners["SE"]->Longitude)));
      $latLonBox->appendChild($e);
      //todo: rotation          <rotation>-0.466708391838466</rotation>
      $groundOverlay->appendChild($latLonBox);

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

      return $doc->saveXML();
  /*
<kml>
  <Folder>
    <Style id="lineStyle">
      <LineStyle>
        <color>7f0000ff</color>
        <width>4</width>
      </LineStyle>
    </Style>
    <Placemark>
      <name>Route</name>
      <styleUrl>#lineStyle</styleUrl>
      <LineString>
        <extrude>1</extrude>
        <tesselate>1</tesselate>
        <coordinates>10.2888163179159,63.4188752342016 10.2888095285743,63.4188825264573 10.288806091994,63.4189037326723 10.2888062596321,63.4189301356673 10.2887911722064,63.4189585503191 10.2887555491179,63.
      </LineString>
    </Placemark>
  </Folder>
</kml>
  */


/*
     private void CreateKml(XmlWriter writer, LongLat[] corners, string groundOverlayFileName, double rotation)
    {
      var formatProvider = new NumberFormatInfo { NumberDecimalSeparator = "." };

      writer.WriteStartDocument();
      writer.WriteStartElement("kml");

      writer.WriteStartElement("Folder");

      // GroundOverlay
      writer.WriteStartElement("GroundOverlay");
      writer.WriteElementString("name", "Map"); // todo: language string
      if (groundOverlayFileName != null)
      {
        writer.WriteStartElement("Icon");
        writer.WriteElementString("href", "map.jpg");
        writer.WriteEndElement();
      }
      if (corners != null)
      {
        writer.WriteStartElement("LatLonBox");
        writer.WriteElementString("north", Math.Max(corners[1].Latitude, corners[2].Latitude).ToString(formatProvider));
        writer.WriteElementString("south", Math.Min(corners[0].Latitude, corners[3].Latitude).ToString(formatProvider));
        writer.WriteElementString("east",
                                  Math.Max(corners[2].Longitude, corners[3].Longitude).ToString(formatProvider));
        writer.WriteElementString("west",
                                  Math.Min(corners[0].Longitude, corners[1].Longitude).ToString(formatProvider));
        writer.WriteElementString("rotation", rotation.ToString(formatProvider));
        writer.WriteEndElement();
      }
      writer.WriteEndElement(); // GroundOverlay

      // Line style
      writer.WriteStartElement("Style");
      writer.WriteAttributeString("id", "lineStyle");
      writer.WriteStartElement("LineStyle");
      writer.WriteElementString("color", "7f0000ff");
      writer.WriteElementString("width", "4");
      writer.WriteEndElement();
      writer.WriteEndElement();

      // Route line(s)
      foreach(var segment in Sessions[0].Route.Segments)
      {
        writer.WriteStartElement("Placemark");
        writer.WriteElementString("name", "Route"); // todo: language string
        writer.WriteElementString("styleUrl", "#lineStyle");
        writer.WriteStartElement("LineString");
        writer.WriteElementString("extrude", "1");
        writer.WriteElementString("tesselate", "1");
        writer.WriteStartElement("coordinates");
        foreach (var waypoint in segment.Waypoints)
        {
          writer.WriteString(waypoint.LongLat.Longitude.ToString(formatProvider) + "," +
                             waypoint.LongLat.Latitude.ToString(formatProvider) + " ");
        }
        writer.WriteEndElement();
        writer.WriteEndElement();
        writer.WriteEndElement();
      }

      writer.WriteEndElement();

      writer.WriteEndElement();
      writer.WriteEndDocument();
      writer.Flush();
    }

    private double GetImageRotationD(ImageExportData imageExportData)
    {
      var corners = GetImageCornersLongLat(imageExportData);
      var sw = new PointD(corners[0].Longitude, corners[0].Latitude);
      var se = new PointD(corners[3].Longitude, corners[3].Latitude);
      return LinearAlgebraUtil.GetAngleD(se-sw);
    }
*/

    }

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