
<a href='step_1_start.php'>Start Again</a>
<?php 
require_once 'csimport.php';

if (array_key_exists('import_id', $_GET)) {
  // Step 2
  $import_id = $_GET['import_id'];
  retrieve_contacts($import_id, 2000);
}
function retrieve_contacts($import_id, $timeout) {
  $contacts_result = CSImport::get_contacts($import_id);
  $contacts = $contacts_result['contacts'];
  $contacts_owner = $contacts_result['contacts_owner'];
  if(!is_null($contacts_owner)) {
    echo "<p><strong>Contacts Owner</strong>:";
    echo $contacts_owner->name();
    echo "&lt;".$contacts_owner->email()."&gt;";
    echo "</p>";
  }
  if(!is_null($contacts)) {
    echo "<pre>";
    print_r($contacts);
    echo "</pre>";
    ?>
<script type="text/javascript">
  popup = window.open('', '_popupWindow');
  if (undefined != popup) {
    popup.close();
  }
</script>
    <?php
  }
}
?>