<?php
  include_once(dirname(__FILE__) ."/config.php");
  include_once(dirname(__FILE__) ."/include/helper.php");
  
  if($_GET["clear"]) Helper::ClearLog();
  
  print '<p><a href="_log.php">Refresh</a> | <a href="_log.php?clear=1">Clear log</a></p>';
  
  print '<pre>'. file_get_contents(LOG_FILE_NAME) .'</pre>';
?>