<html>
<head>
  <title>Import Contacts - Step 1</title>
</head>
<body>
<p>
Click to import your address book from one of the providers below:
<a href='index.php?service=yahoo'       onclick="return open_popup('yahoo', true);">Yahoo!</a>
<a href='index.php?service=windowslive' onclick="return open_popup('windowslive', true)">Windows Live</a>
<a href='index.php?service=gmail'       onclick="return open_popup('gmail', true)">Gmail</a>
<a href='index.php?service=aol'         onclick="return show_u_p_fields('aol');">AOL</a>
<a href='index.php?service=plaxo'       onclick="return show_u_p_fields('plaxo');">Plaxo</a>
<a href='index.php?service=outlook'     onclick="return open_popup('outlook', false)">Outlook</a>
<a href='index.php?service=addressbook' onclick="return open_popup('addressbook', false)">Mac Address Book</a>
</p>
<form id="u_p_inputs" style="display:none;">
  Username: <input type='text' id='username'/><br />
  Password: <input type='password' id='password'/><br />
  <input type='submit' onclick="return open_popup(input_service, false, document.getElementById('username').value, document.getElementById('password').value);" name='submit'/>
</form>

<script src="prototype.js" type="text/javascript"></script>
<script type="text/javascript">
var input_service;
function show_u_p_fields(service_name) {
  input_service = service_name;
  $('u_p_inputs').show();
  return false;
}
function open_popup(service, focus, username, password, url) {
  if (url == undefined) { url = 'popup.php'; }
  url = url + '?service=' + service;
  if (username != null) {
    url = url + '&username=' + username + '&password=' + password;
  }

  popup_height = '300';
  popup_width = '500';
  
  if (service == 'yahoo') {
    popup_height = '500';
    popup_width = '500';
  } else if (service == 'gmail') {
    popup_height = '600';
    popup_width = '987';
  } else if (service == 'windowslive') {
    popup_height = '600';
    popup_width = '987';
  } else if (service == 'aol' || service == 'plaxo') {
    popup_height = '600';
    popup_width = '987';
  }
  
  popup = window.open(url, "_popupWindow", 'height='+popup_height+',width='+popup_width+',location=no,menubar=no,resizable=no,status=no,toolbar=no');
  if (focus) {
    popup.focus();
  }
  else {
    window.focus();
  }
  
  // wait for the popup window to indicate the import_id to start checking for events...
  
  return (undefined == popup);
}
</script>
</body>
</html>