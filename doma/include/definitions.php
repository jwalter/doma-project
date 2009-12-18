<?php
  define('DOMA_VERSION', '2.99.1');
  define('DOMA_SERVER', 'http://www.matstroeng.se/doma/domaserver.php');

  $rootPath =  dirname(dirname(__FILE__));
  if ($rootPath[strlen($projectDir)-1] != '/') 
  {
    $rootPath .= '/';
  }
  $projectDirectory = implode('/', array_intersect(explode('/', $_SERVER["REQUEST_URI"]), explode('/', str_replace('\\', '/', $rootPath))));
  if ($projectDirectory[strlen($projectDir)-1] != '/') 
  {
    $projectDirectory .= '/';
  }
  define('ROOT_PATH', $rootPath);
  define('PROJECT_DIRECTORY', $projectDirectory);
  define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . $projectDirectory);

  
  include_once(dirname(__FILE__) ."/db_connect.php");
  include_once(dirname(__FILE__) ."/helper.php");
  include_once(dirname(__FILE__) ."/data_access.php");
  include_once(dirname(__FILE__) ."/map_class.php");
  include_once(dirname(__FILE__) ."/user_class.php");
  include_once(dirname(__FILE__) ."/session_class.php");
  include_once(dirname(__FILE__) ."/category_class.php");
?>