<html>
<head>
  <title>Import Contacts - Popup</title>
</head>
<body>
<?php 

if (array_key_exists('service', $_GET)) {
  // Step 1
  $username = NULL;
  $password = NULL;
  if (array_key_exists('username', $_GET))
    $username = $_GET['username'];
  if (array_key_exists('password', $_GET))
    $password = $_GET['password'];
    
  // Call to the CloudSponge.com for the import_id and redirect url (if applicable)
  $output = CSImport::begin_import($_GET['service'], $username, $password, NULL);//, 'http://powerbook.local/~graemerouse/index.php');
  if (isset($output['import_id']))
  {
    $import_id = $output['import_id'];
    if (!is_null($output['consent_url'])) {
      $url = $output['consent_url'];
      // header("Location: $url"); // not here since we need to redirec the opener window...
    } else if (!is_null($output['applet_tag'])) {
      // TODO: add description here of what to do next...
      echo $output['applet_tag'];
    }
  } else {
    echo "trouble...";
  }
} 
?>
<p>Contacts are being imported. Please do not close this popup window. </p>
<script type="text/javascript">
  // redirect the opener to start fetching events and updating with the current progress
  window.opener.location = 'step_2_events.php?import_id=<?=$import_id?>';
<?php if (isset($url)) { ?>
  // redirect the popup to the appropriate url
  this.location = '<?=$url?>';
<?php } ?>
</script>
</body>
</html>