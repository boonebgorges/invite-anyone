<?php

class Cloudsponge_Integration {
	var $enabled;
	var $key;

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

		if ( empty( $options ) )
			$options = get_option( 'invite_anyone' );
		
		$this->enabled = !empty( $options['cloudsponge_enabled'] ) ? $options['cloudsponge_enabled'] : false;
		$this->key     = !empty( $options['cloudsponge_key'] ) ? $options['cloudsponge_key'] : false;
		
		if ( $this->enabled && $this->key ) {
			add_action( 'invite_anyone_after_addresses', array( $this, 'import_markup' ) );	
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ) );
		}
	}

	/**
	 * Registers and loads CS JS.
	 *
	 * For now, this is overly generous to account for the fact that people can have the IA
	 * widget installed on any page. In the future I'll try to clean it up a bit.
	 *
	 * @package Invite Anyone
	 * @since 0.8.8
	 */
	function enqueue_script() {
		wp_enqueue_script( 'ia_cloudsponge', 'https://api.cloudsponge.com/address_books.js', array(), false, true );
	}

	/**
	 * Inserts the Cloudsponge markup into the Send Invites front end page
	 *
	 * @package Invite Anyone
	 * @since 0.8
	 *
	 * @param array $options Invite Anyone settings. Check em so we can bail if necessary
	 */
	function import_markup( $options = false ) {
		?>		
		
<script type="text/javascript" charset="utf-8">
	csInit( { 	
		domain_key:"<?php echo esc_html( $this->key ) ?>",
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

<input type="hidden" id="cloudsponge-emails" name="cloudsponge-emails" value="" />

<?php _e( 'You can also add email addresses <a class="cs_import">from your Address Book</a>.', 'bp-invite-anyone' ) ?>
		
		<?php
	}
}
$cloudsponge_integration = new Cloudsponge_Integration;

?>
