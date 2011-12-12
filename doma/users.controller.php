<?php
  include_once(dirname(__FILE__) ."/include/main.php");

  class UsersController
  {
    public function Execute()
    {
      $viewData = array();

      $errors = array();

      if(Helper::IsLoggedInAdmin() && $_GET["loginAsUser"])
      {
        // login as a certain user and redirect to his page
        if(Helper::LoginUserByUsername($_GET["loginAsUser"]))
        {
          Helper::Redirect("index.php?". Helper::CreateQuerystring(getUser()));
        }
      }
      
      $viewData["Users"] = DataAccess::GetAllUsers(!Helper::IsLoggedInAdmin());
      
      $viewData["LastMapForEachUser"] = DataAccess::GetLastMapsForUsers("date");
      
      // last 10 maps
      $viewData["LastMaps"] = DataAccess::GetMaps(0, 0, 0, 0, 10, "date");
      $viewData["LastComments"] = DataAccess::GetLastComments();

      if($_GET["error"] == "email") $errors[] = sprintf(__("ADMIN_EMAIL_ERROR"), ADMIN_EMAIL);
      
      $viewData["Errors"] = $errors;
      
      return $viewData;
    }
  }      
 
?>
