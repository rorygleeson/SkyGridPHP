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

Note: Don't forget to add " \ " to special chars in a string in PHP.
Example: $query = "{ \"properties \": { \"ph \": { \" \$gt \": \"8.751 \"}}}";




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






