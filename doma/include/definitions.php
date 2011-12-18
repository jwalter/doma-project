<?php
  define('DOMA_VERSION', '3.0.2');
  define('DOMA_SERVER', 'http://www.matstroeng.se/doma/domaserver.php');
  
  $rootPath =  dirname(dirname(__FILE__));
  if ($rootPath[strlen($rootPath)-1] != '/') 
  {
    $rootPath .= '/';
  }
  
  $projectDirectory = implode('/', array_intersect(explode('/', $_SERVER["REQUEST_URI"]), explode('/', str_replace('\\', '/', $rootPath))));
  if ($projectDirectory[strlen($projectDirectory)-1] != '/') 
  {
    $projectDirectory .= '/';
  }
  define('ROOT_PATH', $rootPath);
  define('PROJECT_DIRECTORY', $projectDirectory);
  define('BASE_URL', ($_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $projectDirectory);

  // set default values to new configuration settings added in DOMA version 3 and later that the user has not specified in config.php
  $newConfigurationSettings = array(
    'DB_COMMENT_TABLE' => 'doma_comments',
    'TEMP_FILE_PATH' => 'temp',
    'SHOW_LANGUAGES_IN_TOPBAR' => '1',
    'LANGUAGES_AVAILABLE' => 'English;en;en|svenska;sv;se|español;es;es|česky;cz;cz|norsk;no_NB;no',
    'IMAGE_RESIZING_METHOD' => '1'
  );
  while(list($key, $value) = each($newConfigurationSettings))
  {
    if(!defined($key)) define($key, $value);  
  }
  
  include_once(dirname(__FILE__) ."/db_connect.php");
  include_once(dirname(__FILE__) ."/helper.php");
  include_once(dirname(__FILE__) ."/data_access.php");
  include_once(dirname(__FILE__) ."/map_class.php");
  include_once(dirname(__FILE__) ."/user_class.php");
  include_once(dirname(__FILE__) ."/session_class.php");
  include_once(dirname(__FILE__) ."/category_class.php");
  include_once(dirname(__FILE__) ."/comment_class.php");
?>