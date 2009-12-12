<?php
  include_once(dirname(__FILE__) ."/include/main.php");
  
  class SendNewPasswordController
  {
    public function Execute()
    {
      $viewData = array();  

      $errors = array();

      // no user specified - redirect to user list page
      if(!getUser()) Helper::Redirect("users.php");

      // user is hidden - redirect to user list page
      if(!getUser()->Visible) Helper::Redirect("users.php");
      
      // no email address for user is not specified
      if(!getUser()->Email) Helper::Redirect("users.php");

      if($_POST["cancel"])
      {
        Helper::Redirect("login.php?". Helper::CreateQuerystring(getUser()));
      }

      if($_POST["send"])
      {
        $password = Helper::CreatePassword(6);
        $user = getUser();
        $user->Password = md5($password);
        $user->Save();
        
        $fromName = __("DOMA_ADMIN_EMAIL_NAME");
        $subject = __("NEW_PASSWORD_EMAIL_SUBJECT");
        $baseAddress = Helper::GlobalPath("");
        $userAddress = Helper::GlobalPath("index.php?user=". $user->Username);
        $body = sprintf(__("NEW_PASSWORD_EMAIL_BODY"), $user->FirstName, $baseAddress, $userAddress, $user->Username, $password);  
        $emailSentSuccessfully = Helper::SendEmail($fromName, $user->Email, $subject, $body);
        
        if($emailSentSuccessfully) Helper::Redirect("login.php?". Helper::CreateQuerystring(getUser()) ."&action=newPasswordSent");
        
        $errors[] = __("EMAIL_ERROR");
      }
      
      $viewData["Errors"] = $errors;
      return $viewData;
    }
  }  
?>
