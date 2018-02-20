<?php

/**

Rory Gleeson (rory@skygrid.io) https://bitbucket.org/Rorygleeson
 
A PHP Library to support server side SkyGrid platform access.

Configure the config/config.php with the SkyGrid project Id, and the email and password where necessary. 
The config.php file should at a minimum contain the projectID. 


 
Summary
========

getUserAppToken(email, password)									
SkyGrid returns a token which is required for further API requests, valid for 24 hours. The token is based on the email and password sent to this function. Usually used for getting tokens for app users, where the app user signs in and provides his or her username and password to the app. This is how SkyGrid App users will get a token. (See SkyGrid domain model). 


getToken()									
SkyGrid returns a token which is required for further API requests, valid for 24 hours. The token is based on the email and password
stored in the config file. This is how a SkyGrid Admin could will get a token, and is usually used by server side scripts (See SkyGrid domain model). 



listSchema(token, schemaId)					
SkyGrid returns an array containing either all schemas, or a single schema. To get all schemas, set $schemaId = "All". Else set to valid schema Id 

listDevices(token, deviceId)				
SkyGrid returns an array containing either all devices, or a single device. To get all devices set $deviceId = "All". Else set to valid device Id

addDevice(token, schemaId, deviceName) 		
SkyGrid adds a device of the specified schema, with the specified device name. returns 204 for success

remDevice(token, deviceId)					
SkyGrid removes a device from the project, returns 204 for success

updateDevice(token, deviceId, valuesArray)	
SkyGrid updates the device in skygrid, returns 204 for success

getDeviceHistory(token, deviceId, limit, query)			
SkyGrid returns an array containig the history for the device. 
Set limit to max 1000 to get the last 1000 entries. 
Pass a search "where" query, for example only retrieve entires from a certain date. 
Example where queries  
https://api.skygrid.io/history/deviceID/?where={"properties": {"ph": {"$gt":"8.751"}}}    -> example API request
https://api.skygrid.io/history/x7Dv_BIF/?where={"properties": {"ph": {"$eq":"8.7688"}}}   -> example API request



$query={"time":{"$gte":"2017-02-04T01:30:00","$lte":"2017-04-01T02:00:00"}}    -> where date is between ...  
$query={"properties": {"ph": {"$gt":"8.751"}}}                                 -> where ph is greater than 8.751
$query={"properties": {"ph": {"$eq":"8.751"}}} 							       -> where equal to
(same as) 
$query={"properties": {"ph":"8.751"}}      						               -> where equal to

Note: Don't forget to add "\" to special chars in a string in PHP.
Example: $query = "{\"properties\": {\"ph\": {\"\$gt\":\"8.751\"}}}";




getDeviceHistoryWithAggreg(token, deviceId, aggregation, property)	
If the device has aggregation enabled, you can request the aggregated information for the specified property. See SkyGrid user docs for how to turn on
aggregation. 
Set aggregation to either "hourly", "daily" or "monthly".
Returns an array with start date time, end date time, total aggregate, number of values. Dividing total aggreg by number of values provides the average. 



deviceUpdateValue(deviceKey, valuesArray)	
SkyGrid updates the device in skygrid,with the data specified in the valuesArray.  returns 204 for success


deviceGetValue(deviceKey)					
SkyGrid returns an array containing the current values of the device from skygrid
This is specifically designed for device API requests (not server side request which should be from the SkyGrid Admin user). 
Its uses the device key, which does not change. 

deviceGetHistory(deviceKey)					
SkyGrid returns an array containing the history for the device
This is specifically designed for device API requests (not server side request which should be from the SkyGrid Admin user). 
Its uses the device key, which does not change. 




MasterKey
===========

As per SkyGrid documentation, each project has a master key. The master key should only be used by the SkyGrid admin user on the server side. It allows for API operations that are restricted to the admin user, such as the ability to update device schema's. The MasterKey allows access to any devce in the project, so is useful for server side scripts that need to access all devices. 

To use the MasterKey, set it in the config file. Then pass it to any of the above functions as the token. Since the master key is a smaller size than the normal token (24 chars), the SDK will perform a REST API query using the X-Master-Key in the header (and not an X-Access-Token)











**/

require_once('/home/bitnami/htdocs/poolbuddy/pages/php-sdk/config/config.php');				      // import config file containg projectID, email , password

/*
    Set API end point URLS
*/

function getTokenURL() { return 'https://api.skygrid.io/login'; }            // requires email and password
function devicesURL()  { return 'https://api.skygrid.io/devices/'; } 		 // requires token
function schemaURL()   { return 'https://api.skygrid.io/schemas/'; }         // requires token
function historyURL()  { return 'https://api.skygrid.io/history/'; }         // requires token
function devURL()      { return 'https://api.skygrid.io/d/'; }               // requires dev key
function pwResetEmailURL()      { return 'https://api.skygrid.io/users/requestPasswordReset'; }               // requires x key
function resetPasswordURL()      { return 'https://api.skygrid.io/users/resetPassword'; }               // requires x key
  /*
   * Get a request_token from SkyGrid, uses username and password stored in the config file
   * returns the token in a string
   */
  
function getToken() 
{
$postData = array(
'email' => email,
'password' => password);
			
$projectID = projectID;
						
$context = stream_context_create(array(
'http' => array(
'method' => 'POST',
'header' => 
"X-Project-Id: {$projectID}\r\n".
"Content-Type: application/json\r\n",'content' => json_encode($postData)
)));

$response = file_get_contents(getTokenURL(), FALSE, $context);
$responseData = json_decode($response, TRUE);
$token = $responseData['token'];
return $token;
}

 /*
   * Get a request_token from SkyGrid. This uses the email and password (corresponding to App user accounts in skygrid. Pass it the 
   * user email and password collected at login. 
   */
  
function getUserAppToken($email, $password) 
{
$postData = array(
'email' => $email,
'password' => $password);
			
$projectID = projectID;
						
$context = stream_context_create(array(
'http' => array(
'method' => 'POST',
'header' => 
"X-Project-Id: {$projectID}\r\n".
"Content-Type: application/json\r\n",'content' => json_encode($postData)
)));

$response = file_get_contents(getTokenURL(), FALSE, $context);
$responseData = json_decode($response, TRUE);
$token = $responseData['token'];
return $token;
}



 /**
   * Get schema information from SkyGrid. Either get schema information for an individual schema by passing it a valid schemaId,
   * or get information for all Schema by setting schemaID = "ALL" 
   *
   * returns the schema in an array
   */
  
function listSchema($token, $schemaID) 
{
$url = schemaURL();
if ($schemaID != "ALL")
    $url .= $schemaID;
                 
$projectID = projectID;
				
if( strlen($token) != 24 )
	$headerName = "X-Access-Token";
else
	$headerName = "x-master-key";	
	
					
$context = stream_context_create(array(
'http' => array(
'method' => 'GET',
'header' => "X-Project-Id: {$projectID}\r\n".
"{$headerName}: {$token}\r\n".
"Content-Type: application/json\r\n"
)
));

$response = file_get_contents($url, FALSE, $context);
$responseData = json_decode($response, TRUE);		
return $responseData;
}

 /**
   * Get device information from SkyGrid. Either get device information for an individual device by passing it a valid deviceID,
   * or get information for all Devices by setting deviceID = "ALL" 
   *
   * returns the devices in an array
   */



function listDevices($token, $deviceID) 
{
   		
$url = devicesURL();
if ($deviceID != "ALL") 
   $url .= $deviceID;

$projectID = projectID;
			
if( strlen($token) != 24 )
	$headerName = "X-Access-Token";
else
	$headerName = "x-master-key";	
	
				
$context = stream_context_create(array(
'http' => array(
'method' => 'GET',
'header' => "X-Project-Id: {$projectID}\r\n".
"{$headerName}: {$token}\r\n".
"Content-Type: application/json\r\n"
)
));


$response = file_get_contents($url, FALSE, $context);
$responseData = json_decode($response, TRUE);			
return $responseData;
}


  
   /**
   * Add a device of the specified schema, with the specified deviceName. 
   *
   * returns the device ID of the created device. 
   */
   
   
function addDevice($token, $schemaID, $deviceName) 
{
    		
$projectID = projectID;

			
if( strlen($token) != 24 )
	$headerName = "X-Access-Token";
else
	$headerName = "x-master-key";	
	


$postData = array('schemaId' =>  $schemaID,'name' => $deviceName);

$context = stream_context_create(array(
'http' => array(
'method' => 'POST',
'header' => "X-Project-Id: {$projectID}\r\n".
"{$headerName}: {$token}\r\n".
"Content-Type: application/json\r\n",
'content' => json_encode($postData)
)
));

$response = file_get_contents(devicesURL(), FALSE, $context);
$responseData = json_decode($response, TRUE);
return $responseData;
}
  
  
  /**
   * Removes a device of the specified deviceID.
   * returns 204 for success, no array returned. 
   */
  
function remDevice($token, $deviceID) 
{
    		
$projectID = projectID;		

			
if( strlen($token) != 24 )
	$headerName = "X-Access-Token";
else
	$headerName = "x-master-key";	
	

$url = devicesURL();
	$url .= $deviceID;
			
$context = stream_context_create(array(
'http' => array(
'method' => 'DELETE',
'header' => "X-Project-Id: {$projectID}\r\n".
"{$headerName}: {$token}\r\n".
"Content-Type: application/json\r\n"
)
));


$response = file_get_contents($url, FALSE, $context);
$responseData = json_decode($response, TRUE);
return $responseData;
}
  
  
   
  /**
   * Updates the device of the specified deviceID, with the values contained in the valuesArray. The values array must 
   * match the properties of the device as per its schema
   * returns 204 for success, no array returned. 
   */
  
function updateDevice($token, $deviceID, $valuesArray)
{
    		
$projectID = projectID;
$url = devicesURL();
	$url .= $deviceID;
			
			
if( strlen($token) != 24 )
	$headerName = "X-Access-Token";
else
	$headerName = "x-master-key";	
	
			
$context = stream_context_create(array(
'http' => array(
'method' => 'PUT',
'header' => "X-Project-Id: {$projectID}\r\n".
"{$headerName}: {$token}\r\n".
"Content-Type: application/json\r\n",
'content' => json_encode($valuesArray)
)
));

$response = file_get_contents($url, FALSE, $context);
$responseData = json_decode($response, TRUE);
return $responseData;
}
  
   /**
   * returns an array of the device history for the specified device. 
   */

function getDeviceHistory($token, $deviceID, $limit, $query)
{
	
$projectID = projectID;
$url = historyURL();
$url .= $deviceID;

			
if( strlen($token) != 24 )
	$headerName = "X-Access-Token";
else
	$headerName = "x-master-key";	
	

if( isset($limit)  AND   isset($query)  )
{
$url .= "?where=";	
$url .= urlencode($query);
$url .= "&limit=";	
$url .= $limit;	
}

else if(isset($query))
{
$url .= "?where=";	
$url .= urlencode($query);	
}
	
else if(isset($limit))
{
$url .= "?limit=";	
$url .= $limit;	
}
	
$context = stream_context_create(array(
'http' => array(
'method' => 'GET',
'header' => "X-Project-Id: {$projectID}\r\n".
"{$headerName}: {$token}\r\n".
"Content-Type: application/json\r\n"
)
));

$response = file_get_contents($url, FALSE, $context);
$responseData = json_decode($response, TRUE);
return $responseData;
}









 /**
   * returns an array of the aggreagted counts for the specified property 
   */

function getDeviceHistoryWithAggreg($token, $deviceID, $aggregation, $property)
{
	
$projectID = projectID;
$url = historyURL();
$url .= $deviceID;
$url .= "/";
$url .= $property;
$url .= "/?aggregation=";
$url .= $aggregation;


$context = stream_context_create(array(
'http' => array(
'method' => 'GET',
'header' => "X-Project-Id: {$projectID}\r\n".
"X-Access-Token: {$token}\r\n"
)
));


$response = file_get_contents($url, FALSE, $context);


$responseData = json_decode($response, TRUE);
return $responseData;
}




 /**
   * send password reset email  
   */



function sendPWresetEmail($valuesArray)
{
$masterKey = masterkey;	
$projectID = projectID;
$url = pwResetEmailURL();

$headerName = "x-master-key";	

	
$context = stream_context_create(array(
'http' => array(
'method' => 'POST',
'header' => "X-Project-Id: {$projectID}\r\n".
"{$headerName}: {$masterKey}\r\n".
"Content-Type: application/json\r\n",
'content' => json_encode($valuesArray)
)
));

$response = file_get_contents($url, FALSE, $context);
$responseData = json_decode($response, TRUE);

return $responseData;
}




/**
   * send password reset email  
   */



function resetPassword($valuesArray)
{
$masterKey = masterkey;	
$projectID = projectID;
$url = resetPasswordURL();

$headerName = "x-master-key";	

	
$context = stream_context_create(array(
'http' => array(
'method' => 'POST',
'header' => "X-Project-Id: {$projectID}\r\n".
"{$headerName}: {$masterKey}\r\n".
"Content-Type: application/json\r\n",
'content' => json_encode($valuesArray)
)
));

$response = file_get_contents($url, FALSE, $context);
$responseData = json_decode($response, TRUE);

return $responseData;
}






  /**
   * Updates the device of the specified device key, with the values contained in the valuesArray. The values array must 
   * match the properties of the device as per its schema
   * returns 204 for success, no array returned.  Used by devices.
   */
  
function deviceUpdateValue($deviceKey, $valuesArray)
{		
$projectID = projectID;
$url = devURL();
$url .= $deviceKey;
			
			
$context = stream_context_create(array(
'http' => array(
'method' => 'PUT',
'header' =>  "Content-Type: application/json\r\n",
'content' => json_encode($valuesArray)
)
));

$response = file_get_contents($url, FALSE, $context);
$responseData = json_decode($response, TRUE);
return $responseData;
 }
  
  
  
  
  
  
  
   /**
   * returns an array containing the current values of the device
   */
  
function deviceGetValue($deviceKey)
{		
$projectID = projectID;
$url = devURL();
$url .= $deviceKey;
			
			
$context = stream_context_create(array(
'http' => array(
'method' => 'GET'
)
));

$response = file_get_contents($url, FALSE, $context);
$responseData = json_decode($response, TRUE);
return $responseData;
}
  
  
  
  
  
   /**
   * returns an array of the device history for the specified device. 
   */

  
function deviceGetHistory($deviceKey)
{		
$projectID = projectID;
$url = historyURL();
$url .= $deviceKey;
			
			
$context = stream_context_create(array(
'http' => array(
'method' => 'GET'
)
));

$response = file_get_contents($url, FALSE, $context);
$responseData = json_decode($response, TRUE);
return $responseData;
}
  
  
  
  
  
  
  
  
?>
