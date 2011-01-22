CloudSponge.com PHP Library 0.9 Beta

This library consists of one php file that provides an interface to CloudSponge.com API. Other files are included to present an example of the usage of the library and CloudSponge.com API.

Files:
csimport.php - library that interfaces with CloudSponge.com
csconstants.php - example constants definition that should be modified to include your own CloudSponge.com credentials.
step_1_start.php - Example start page that displays options to a user to import their contacts.
step_2_events.php - Example progress page that displays events as they become available.
step_3_contacts.php - Example completion page that displays the imported contacts.
popup.php - Example page that displays a popup to the user, to take them through the OAuth or DelAuth processes.
auth.php - Callback endpoint responsible for proxying WindowsLive authentication for CloudSponge.com.
prototype.js - dependency for the sample pages to interact with the DOM. 

Installation:
Unzip all files into a folder in your web directory. 
Modify the contents of csconstants.php. Move this file to a central location where you will not copy over it with subsequent installs, and modify the require_once line in csimport.php to reflect this new location. 
Point your browser at step_1_start.php in your web directory and run the sample import. 

For additional support or feedback contact graeme@cloudsponge.com.

