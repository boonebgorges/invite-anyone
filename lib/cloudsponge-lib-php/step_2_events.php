<?php

require_once 'csimport.php';

if (array_key_exists('import_id', $_GET)) {
  // Step 2
  $import_id = $_GET['import_id'];
  retrieve_events($import_id, 30000);
} 

function retrieve_events($import_id, $timeout) {
  $continue = false;
  $reload = true;
  
  $events = CSImport::get_events($import_id);
  foreach ($events as $event) {
    // look for an error event... 
    if ($event['status'] == 'ERROR') { 
      $reload = false;
      echo $event['description'];
    }
    // look for the COMPLETED/COMPLETE event... this indicates the import is completely done
    if ($event['event_type'] == "COMPLETE" && $event['status'] == "COMPLETED" && $event['value'] == 0) {
      $contine = true;
      $reload = false;
    }
  }

  if(!is_null($events)) {
    echo "<pre>";
    print_r($events);
    echo "</pre>";
  } 
  
  if ($reload) {
  ?>

<script type="text/javascript">
// only execute the timeout if the popup is still open, if the user canceled by closing it, then we are done...
// This could be cleaned up by using ajax, instead of an entire page refresh
  setTimeout("window.location = '<?php echo bp_displayed_user_domain() . BP_INVITE_ANYONE_SLUG ?>?import_id=<?=$import_id?>'", <?=$timeout?>);
</script>

<?php 
  } else if ($contine) {
    // redirect the page to the final step.
    ?>

<script type="text/javascript">
window.location = '<?php echo bp_displayed_user_domain() . BP_INVITE_ANYONE_SLUG ?>?import_id=<?=$import_id?>&complete=1';
</script>

    <?php
  }
}
?>