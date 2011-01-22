<?php

class Cloudsponge_Integration {
	/**
	 * PHP 4 Constructor
	 *
	 * @package Invite Anyone
	 * @since 0.8
	 */
	function cloudsponge_integration() {
		$this->__construct();
	}

	/**
	 * PHP 5 Constructor
	 *
	 * @package Invite Anyone
	 * @since 0.8
	 */
	function __construct() {
		add_action( 'invite_anyone_after_addresses', array( $this, 'import_markup' ) );
	}

	function import_markup() {
		?>		
		
		
		<!-- Include these scripts to import address books with CloudSponge -->

<script type="text/javascript" src="https://api.cloudsponge.com/address_books.js"></script>
<script type="text/javascript" charset="utf-8">csInit({domain_key:"D8UDAPCEKMULKJE83EPN", textarea_id:'contact_list'});</script>

<!-- Any link with a class="cs_import" will start the import process -->
<a class="cs_import">Add from Address Book</a>

<!-- This textarea will be populated with the contacts returned by CloudSponge -->
<textarea id="contact_list" style="width:450px;height:82px"></textarea>
		
		
		<?php
	}


}
$cloudsponge_integration = new Cloudsponge_Integration;

?>