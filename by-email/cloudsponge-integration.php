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

	/**
	 * Inserts the Cloudsponge markup into the Send Invites front end page
	 *
	 * @package Invite Anyone
	 * @since 0.8
	 *
	 * @param array $options Invite Anyone settings. Check em so we can bail if necessary
	 */
	function import_markup( $options ) {
		if ( empty( $options['cloudsponge_enabled'] ) )
			return false;
		
		if ( empty( $options['cloudsponge_key'] ) )
			return false;
		
		?>		
		
<script type="text/javascript" src="https://api.cloudsponge.com/address_books.js"></script>
<script type="text/javascript" charset="utf-8">
	csInit( { 	
		domain_key:"<?php echo esc_html( $options['cloudsponge_key'] ) ?>",
		referer: 'invite_anyone',
		afterSubmitContacts:function(contacts) {
			emails = [];
			for(var i=0; i < contacts.length; i++) {
				var contact = contacts[i];
				var name = contact.name;
				var email = contact.email;
				emails.push(email);
			}
			document.getElementById('invite-anyone-email-addresses').value = emails.join("\n");
		}
	} );
</script>

<!-- Any link with a class="cs_import" will start the import process -->
<?php _e( 'You can also add email addresses <a class="cs_import">from your Address Book</a>.', 'bp-invite-anyone' ) ?>
		
		
		<?php
	}
}
$cloudsponge_integration = new Cloudsponge_Integration;

?>