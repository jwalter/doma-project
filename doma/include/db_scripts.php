<?php
  include_once(dirname(__FILE__) ."/definitions.php");
  
  // returns an array with database scripts that have to be executed to update the database to current version
  function getScripts()
  {
    $collation = (defined("DB_COLLATION") ? DB_COLLATION : "utf8_general_ci");
    
    // update this array with version numbers and scripts as time goes by
    $allScripts = array(
      // 2.0
      array('version' => '2.0', 'script' => 'DROP TABLE IF EXISTS `'. DB_MAP_TABLE .'`'),
      array('version' => '2.0', 'script' => 'CREATE TABLE `'. DB_MAP_TABLE .'` (`ID` int(10) unsigned NOT NULL auto_increment, `UserID` int(10) unsigned NOT NULL, `CategoryID` INT UNSIGNED NOT NULL, `Date` datetime NOT NULL, `Name` varchar(50) character set utf8 NOT NULL, `Organiser` varchar(50) character set utf8 NOT NULL, `Country` varchar(20) character set utf8 NOT NULL, `Discipline` varchar(40) character set utf8 NOT NULL, `RelayLeg` varchar(10) character set utf8 NOT NULL, `MapName` varchar(50) character set utf8 NOT NULL, `ResultListUrl` text character set utf8 NOT NULL, `MapImage` varchar(100) character set utf8 NOT NULL, `ThumbnailImage` varchar(100) collate utf8_swedish_ci NOT NULL, `Comment` mediumtext character set utf8 NOT NULL, `Views` int(10) unsigned NOT NULL, `LastChangedTime` datetime NOT NULL, PRIMARY KEY  (`ID`), KEY `I_UserID` (`UserID`)) ENGINE=MyISAM DEFAULT CHARSET=utf8'),
      array('version' => '2.0', 'script' => 'DROP TABLE IF EXISTS `'. DB_SETTING_TABLE .'`'),
      array('version' => '2.0', 'script' => 'CREATE TABLE `'. DB_SETTING_TABLE .'` (`Key` VARCHAR(100) NOT NULL, `Value` text NOT NULL, PRIMARY KEY (`Key`)) ENGINE=MyISAM DEFAULT CHARSET=utf8'),
      array('version' => '2.0', 'script' => 'DROP TABLE IF EXISTS `'. DB_USER_TABLE .'`'),
      array('version' => '2.0', 'script' => 'CREATE TABLE `'. DB_USER_TABLE .'` (`ID` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `Username` VARCHAR(30) character set utf8 NOT NULL, `Password` TEXT character set utf8 NOT NULL , `FirstName` VARCHAR(30) character set utf8 NOT NULL, `LastName` VARCHAR(30) character set utf8 NOT NULL, `Email` VARCHAR(60) character set utf8 NOT NULL, `Visible` TINYINT UNSIGNED NOT NULL DEFAULT \'1\', `DefaultCategoryID` INT UNSIGNED NOT NULL, UNIQUE (`Username`)) ENGINE = MYISAM DEFAULT CHARSET=utf8'),
      array('version' => '2.0', 'script' => 'DROP TABLE IF EXISTS `'. DB_USER_SETTING_TABLE .'`'),
      array('version' => '2.0', 'script' => 'CREATE TABLE `'. DB_USER_SETTING_TABLE .'` (`UserID` INT UNSIGNED NOT NULL, `Key` VARCHAR(50) character set utf8 NOT NULL, `Value` TEXT character set utf8 NOT NULL, PRIMARY KEY ( `UserID` , `Key` )) ENGINE = MYISAM DEFAULT CHARSET=utf8'),
      array('version' => '2.0', 'script' => 'DROP TABLE IF EXISTS `'. DB_CATEGORY_TABLE .'`'),
      array('version' => '2.0', 'script' => 'CREATE TABLE `'. DB_CATEGORY_TABLE .'` (`ID` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `UserID` INT UNSIGNED NOT NULL, `Name` VARCHAR(40) NOT NULL, KEY `I_UserID` (`UserID`)) ENGINE = MYISAM DEFAULT CHARSET=utf8'),
      
      // 2.1
      // updating collation to user-customizable $collation
      array("version" => "2.1", "script" => "ALTER TABLE `". DB_MAP_TABLE ."` CHANGE `Name` `Name` varchar(50) character set utf8 collate $collation NOT NULL, CHANGE `Organiser` `Organiser` varchar(50) character set utf8 collate $collation NOT NULL, CHANGE `Country` `Country` varchar(20) character set utf8 collate $collation NOT NULL, CHANGE `Discipline` `Discipline` varchar(40) character set utf8 collate $collation NOT NULL, CHANGE `RelayLeg` `RelayLeg` varchar(10) character set utf8 collate $collation NOT NULL, CHANGE `MapName` `MapName` varchar(50) character set utf8 collate $collation NOT NULL, CHANGE `ResultListUrl` `ResultListUrl` text character set utf8 collate $collation NOT NULL, CHANGE `MapImage` `MapImage` varchar(100) character set utf8 collate $collation NOT NULL, CHANGE `ThumbnailImage` `ThumbnailImage` varchar(100) character set utf8 collate $collation NOT NULL, CHANGE `Comment` `Comment` mediumtext character set utf8 collate $collation NOT NULL"),
      array("version" => "2.1", "script" => "ALTER TABLE `". DB_SETTING_TABLE ."` CHANGE `Key` `Key` VARCHAR(100) character set utf8 collate $collation NOT NULL, CHANGE `Value` `Value` text character set utf8 collate $collation NOT NULL"),
      array("version" => "2.1", "script" => "ALTER TABLE `". DB_USER_TABLE ."` CHANGE `Username` `Username` VARCHAR(30) character set utf8 collate $collation NOT NULL, CHANGE `Password` `Password` TEXT character set utf8 collate $collation NOT NULL, CHANGE `FirstName` `FirstName` VARCHAR(30) character set utf8 collate $collation NOT NULL, CHANGE `LastName` `LastName` VARCHAR(30) character set utf8 collate $collation NOT NULL, CHANGE `Email` `Email` VARCHAR(60) character set utf8 collate $collation NOT NULL"),
      array("version" => "2.1", "script" => "ALTER TABLE `". DB_USER_SETTING_TABLE ."` CHANGE `Key` `Key` VARCHAR(50) character set utf8 collate $collation NOT NULL, CHANGE `Value` `Value` TEXT character set utf8 collate $collation NOT NULL"),
      array("version" => "2.1", "script" => "ALTER TABLE `". DB_CATEGORY_TABLE ."` CHANGE `Name` `Name` VARCHAR(40) character set utf8 collate $collation NOT NULL"),
      // adding CreatedTime field for map table
      array("version" => "2.1", "script" => "ALTER TABLE `". DB_MAP_TABLE ."` ADD `CreatedTime` datetime NOT NULL"),
      array("version" => "2.1", "script" => "UPDATE `". DB_MAP_TABLE ."` SET `CreatedTime`=`LastChangedTime` WHERE `CreatedTime`<'2000-01-01'"),
      
      // 3.0
      // adding map position
      array("version" => "2.99.0", "script" => "ALTER TABLE `". DB_MAP_TABLE ."` ADD `MapCenterLatitude` double NULL, ADD `MapCenterLongitude` double NULL, ADD `MapCorners` text character set utf8 collate $collation NULL"),
      // changing last changed and created time to nullable fields
      array("version" => "2.99.0", "script" => "ALTER TABLE `". DB_MAP_TABLE ."` CHANGE `LastChangedTime` `LastChangedTime` datetime NULL, CHANGE `CreatedTime` `CreatedTime` datetime NULL"),
      // adding file name for blank map image
      array("version" => "2.99.0", "script" => "ALTER TABLE `". DB_MAP_TABLE ."` ADD `BlankMapImage` varchar(100) character set utf8 collate $collation NULL"),
      // adding flag for IsGeocoded
      array("version" => "2.99.0", "script" => "ALTER TABLE `". DB_MAP_TABLE ."` ADD `IsGeocoded` tinyint NOT NULL"),
      // adding parameters (geocoded) session
      array("version" => "2.99.0", "script" => "ALTER TABLE `". DB_MAP_TABLE ."` ADD `SessionStartTime` datetime NULL, ADD `SessionEndTime` datetime NULL"),
      array("version" => "2.99.0", "script" => "ALTER TABLE `". DB_MAP_TABLE ."` ADD `Distance` double NULL, ADD `StraightLineDistance` double NULL, ADD `ElapsedTime` double NULL"),
      // longer name
      array("version" => "2.99.0", "script" => "ALTER TABLE `". DB_MAP_TABLE ."` CHANGE `Name` `Name` VARCHAR(100) character set utf8 collate $collation NOT NULL"),
      // create waypoint table
      array("version" => "2.99.1", "script" => "CREATE TABLE `". DB_WAYPOINT_TABLE ."` (`MapID` INTEGER UNSIGNED NOT NULL, `Latitude` INTEGER UNSIGNED NOT NULL, `Longitude` INTEGER UNSIGNED NOT NULL, `Time` INTEGER UNSIGNED NOT NULL) ENGINE = MyISAM DEFAULT CHARSET=utf8"),
      // add an index to MapID
      array("version" => "2.99.1", "script" => "ALTER TABLE `". DB_WAYPOINT_TABLE ."` ADD INDEX `Index_MapID`(`MapID`)"),
      // allow to use negative coordinates
      array("version" => "3.0.1", "script" => "ALTER TABLE  `". DB_WAYPOINT_TABLE ."` CHANGE  `Latitude`  `Latitude` DECIMAL( 13, 0 ) NOT NULL"),
      array("version" => "3.0.1", "script" => "ALTER TABLE  `". DB_WAYPOINT_TABLE ."` CHANGE  `Longitude`  `Longitude` DECIMAL( 13, 0 ) NOT NULL"),
      array("version" => "3.0.1", "script" => "ALTER TABLE  `". DB_WAYPOINT_TABLE ."` ADD INDEX  `Index_MapID_Time` (  `MapID` ,  `Time` )"),
      //comments
      array("version" => "3.0.1", "script" => "CREATE TABLE `". DB_COMMENT_TABLE ."` (  `ID` int(10) NOT NULL AUTO_INCREMENT,  `MapID` int(10) NOT NULL,  `Name` varchar(200) CHARACTER SET utf8 NOT NULL,  `Email` varchar(200) CHARACTER SET utf8 DEFAULT NULL,  `Comment` text CHARACTER SET utf8 NOT NULL,  `DateCreated` datetime NOT NULL,  `UserIP` varchar(200) CHARACTER SET utf8 NOT NULL,  PRIMARY KEY (`ID`)) ENGINE = MYISAM DEFAULT CHARSET=utf8")

    );
    return array_filter($allScripts, "filter");
  }
  
  function filter($value)
  {
    static $dbVersion;
    if(!isset($dbVersion))
    {
      $dbVersion = DataAccess::GetSetting("DATABASE_VERSION", "0.0");
    }
    return(version_compare($value["version"], $dbVersion) > 0);
  }
  
  function executeDatabaseScripts()
  {
    $scripts = getScripts();
    
    $errors = array();
    foreach($scripts as $s)
    {
      mysql_query($s["script"]);
      Helper::WriteToLog($s["script"]);
      $error = getMySQLErrorIfAny();
      if($error) 
      {
        $errors[] = $error; 
        Helper::WriteToLog($error);
      }
    }
    DataAccess::SetSetting("DATABASE_VERSION", DOMA_VERSION);
    return array("errors" => $errors);
  }
  
  
  function getMySQLErrorIfAny()
  {
    if(mysql_error())
    {
      return sprintf(__("MYSQL_ERROR_X"), mysql_error());
    }
    return null;
  }
  
  
?>