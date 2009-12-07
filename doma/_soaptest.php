<?php
// Nusoap library 'nusoap.php' should be available through include_path directive
require_once('include/nusoap.php');

// set the URL or path to the WSDL document
$wsdl = "http://www.matstroeng.se/domadev/webservice.php?wsdl";

// instantiate the SOAP client object
$soap = new nusoap_client($wsdl,"wsdl");

// get the SOAP proxy object, which allows you to call the methods directly
$proxy = $soap->getProxy();

// set parameter request (GetAllMapsRequest)
$request = array(Username=>"newUser",Password=>"newUser");

// get the result, a native PHP type, such as an array or string
$result = $proxy->GetAllMaps($request);

print_r($result);


?>