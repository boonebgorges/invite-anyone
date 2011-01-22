<?php

interface iCSConstants {
  // DOMAIN_KEY and DOMAIN_PASSWORD should contain your domain key and domain password for CloudSponge.com access.
 const DOMAIN_KEY = "D8UDAPCEKMULKJE83EPN";
 const DOMAIN_PASSWORD = "RrW02dfJgjdkSAg";
}

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
		add_action( 'invite_anyone_setup_nav', array( $this, 'setup_nav' ) );
		
		add_action( 'wp', array( $this, 'catch_popup' ), 1 );
		add_action( 'invite_anyone_after_addresses', array( $this, 'import_markup_1' ) );
		
		
		// Hack to keep the Import Contacts screen out of the nav
		add_filter( 'bp_get_options_nav_import-contacts', array( $this, 'hide_subnav' ) );
	}

	function catch_popup() {
		global $bp;
		
		$file = '';
		
		if ( 'import-contacts' == $bp->current_action && BP_INVITE_ANYONE_SLUG == $bp->current_component ) {
				
			require_once( WP_PLUGIN_DIR . '/invite-anyone/lib/cloudsponge-lib-php/popup.php' );
			die();
		}
	}
	
	function import_markup_1() {
		?>		
		
		
		<!-- Include these scripts to import address books with CloudSponge -->

<script type="text/javascript" src="https://api.cloudsponge.com/address_books.js"></script>
<script type="text/javascript" charset="utf-8">csInit({domain_key:"D8UDAPCEKMULKJE83EPN", textarea_id:'contact_list'});</script>

<!-- Any link with a class="cs_import" will start the import process -->
<a class="cs_import">Add from Address Book</a>

<!-- This textarea will be populated with the contacts returned by CloudSponge -->
<textarea id="contact_list" style="width:450px;height:82px"></textarea>
		
		
		
			<p><?php _e( 'Or, import addresses from your address book', 'bp-invite-anyone' ) ?></p>
			<div class="address-book-import">
				<a href='?service=yahoo'       onclick="return open_popup('yahoo', true);">Yahoo!</a>
				<a href='?service=windowslive' onclick="return open_popup('windowslive', true)">Windows Live</a>
				<a href='?service=gmail'       onclick="return open_popup('gmail', true)">Gmail</a>
				<a href='?service=aol'         onclick="return show_u_p_fields('aol');">AOL</a>
				<a href='?service=plaxo'       onclick="return show_u_p_fields('plaxo');">Plaxo</a>
				<a href='?service=outlook'     onclick="return open_popup('outlook', false)">Outlook</a>
				<a href='?service=addressbook' onclick="return open_popup('addressbook', false)">Mac Address Book</a>
			</div>
			
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
			  if (url == undefined) { url = '<?php echo bp_loggedin_user_domain() . BP_INVITE_ANYONE_SLUG  ?>/import-contacts'; }
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
			  
			  popup = window.open(url, "_popupWindow", 'height='+popup_height+',width='+popup_width+',location=yes,menubar=no,resizable=no,status=no,toolbar=no');
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

		<?php
	}


}
$cloudsponge_integration = new Cloudsponge_Integration;

?>