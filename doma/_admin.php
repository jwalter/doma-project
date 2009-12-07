<?php
  include_once(dirname(__FILE__) ."/config.php");
  include_once(dirname(__FILE__) ."/include/definitions.php");

  if($_GET["action"] == "deleteAllUsers") DataAccess::DeleteAllUsers();

  if($_GET["action"] == "clearAll") 
  {
    DataAccess::DeleteAllUsers();
    mysql_query('DROP TABLE IF EXISTS `'. DB_MAP_TABLE .'`');
    mysql_query('DROP TABLE IF EXISTS `'. DB_SETTING_TABLE .'`');
    mysql_query('DROP TABLE IF EXISTS `'. DB_USER_TABLE .'`');
    mysql_query('DROP TABLE IF EXISTS `'. DB_USER_SETTING_TABLE .'`');
    mysql_query('DROP TABLE IF EXISTS `'. DB_CATEGORY_TABLE .'`');
  }
?>