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
		referrer: 'invite-anyone',
		afterSubmitContacts:function(contacts) {
			var emails = [];
			var contact, name, email;
			for(var i=0; i < contacts.length; i++) {
				contact = contacts[i];
				name = contact.fullName();
				email = contact.selectedEmail();
				emails.push(email);
			}
			
			var textarea = document.getElementById('invite-anyone-email-addresses');
			/* Strip any manually entered whitespace */
			var already_emails = textarea.value.replace(/^\s+|\s+$/g,"");
			
			var new_emails;
			if ( already_emails == false ) {
				new_emails = emails.join("\n");
			} else {
				new_emails = already_emails + "\n" + emails.join("\n")
			}
			document.getElementById('invite-anyone-email-addresses').value = new_emails;
		}
	} );
</script>

<?php _e( 'You can also add email addresses <a class="cs_import">from your Address Book</a>.', 'bp-invite-anyone' ) ?>
		
		<?php
	}
}
$cloudsponge_integration = new Cloudsponge_Integration;

?>