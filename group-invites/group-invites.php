<?php

/* Load JS necessary for group invitation pages */

function invite_anyone_add_js() {
	global $bp;

	if ( $bp->current_action == BP_INVITE_ANYONE_SLUG || $bp->action_variables[1] == BP_INVITE_ANYONE_SLUG ) {
		wp_register_script('invite-anyone-js', WP_PLUGIN_URL . '/invite-anyone/group-invites/group-invites-js.js');
		wp_enqueue_script( 'invite-anyone-js' );

		add_action( 'wp_head', 'invite_anyone_autocomplete_init_jsblock' );

		wp_enqueue_script( 'invite-anyone-autocomplete-js', WP_PLUGIN_URL . '/invite-anyone/group-invites/autocomplete/jquery.autocomplete.js', array( 'jquery' ) );
		wp_enqueue_script( 'bp-jquery-autocomplete-fb', WP_PLUGIN_URL . '/invite-anyone/group-invites/autocomplete/jquery.autocompletefb.js' );
		wp_enqueue_script( 'bp-jquery-bgiframe', BP_PLUGIN_URL . '/bp-messages/js/autocomplete/jquery.bgiframe.min.js' );
		wp_enqueue_script( 'bp-jquery-dimensions', BP_PLUGIN_URL . '/bp-messages/js/autocomplete/jquery.dimensions.js' );

		if ( !function_exists( 'bp_post_get_permalink' ) )
			wp_enqueue_script( 'invite-anyone-livequery', WP_PLUGIN_URL . '/invite-anyone/group-invites/autocomplete/jquery.livequery.js' );
	}
}
add_action( 'wp_head', 'invite_anyone_add_js', 1 );


function invite_anyone_autocomplete_init_jsblock() {
?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			var acfb =
			jQuery("ul.first").autoCompletefb({urlLookup:'<?php echo $bp->root_domain . str_replace( 'index.php', 'wp-load.php', $_SERVER['SCRIPT_NAME'] ) ?>'});

			jQuery('#send_message_form').submit( function() {
				var users = document.getElementById('send-to-usernames').className;
				document.getElementById('send-to-usernames').value = String(users);
			});
		});
	</script>
<?php
}


function invite_anyone_add_group_invite_css() {
	global $bp;

	if ( $bp->current_action == BP_INVITE_ANYONE_SLUG || $bp->action_variables[1] == BP_INVITE_ANYONE_SLUG ) {
   		$style_url = WP_PLUGIN_URL . '/invite-anyone/group-invites/group-invites-css.css';
        $style_file = WP_PLUGIN_DIR . '/invite-anyone/group-invites/group-invites-css.css';
        if (file_exists($style_file)) {
            wp_register_style('invite-anyone-group-invites-style', $style_url);
            wp_enqueue_style('invite-anyone-group-invites-style');
        }
    }
}
add_action( 'wp_print_styles', 'invite_anyone_add_group_invite_css' );

function invite_anyone_add_old_css() { ?>
	<style type="text/css">

li a#nav-invite-anyone {
	padding: 0.55em 3.1em 0.55em 0px !important;
	margin-right: 10px;
	background: url(<?php echo WP_PLUGIN_URL, '/invite-anyone/invite-anyone/invite_bullet.gif'; ?>) no-repeat 89% 52%;

}
	</style>
<?php
}

class BP_Invite_Anyone extends BP_Group_Extension {

	var $enable_nav_item = true;
	var $enable_create_step = true;
	var $enable_edit_item = false;

	function bp_invite_anyone() {
		global $bp;

		$this->has_caps = true;

		/* Group API Extension Properties */
		$this->name = __( 'Send Invites', 'buddypress' );
		$this->slug = BP_INVITE_ANYONE_SLUG;

		/* Set as early in the order as possible */
		$this->create_step_position = 42;
		$this->nav_item_position = 71;

		/* Generic check access */
		if ( $this->has_caps == false ) {
			$this->enable_create_step = false;
			$this->enable_edit_step = false;
		}

		$this->enable_nav_item = $this->enable_nav_item();
		$this->enable_create_step = $this->enable_nav_item();
	}

	function display() {
		global $bp;

		if ( BP_INVITE_ANYONE_SLUG == $bp->current_action && 'send' == $bp->action_variables[0] ) {
			if ( !check_admin_referer( 'groups_send_invites', '_wpnonce_send_invites' ) )
				return false;

			// Send the invites.
			groups_send_invites( $bp->loggedin_user->id, $bp->groups->current_group->id );

			do_action( 'groups_screen_group_invite', $bp->groups->current_group->id );

			// Hack to imitate bp_core_add_message, since bp_core_redirect is giving me such hell
			echo '<div id="message" class="updated"><p>' . __( 'Group invites sent.', 'buddypress' ) . '</p></div>';
		}

		invite_anyone_create_screen_content('invite');
	}

	function create_screen() {
		global $bp;

		/* If we're not at this step, go bye bye */
		if ( !bp_is_group_creation_step( $this->slug ) )
			return false;

		invite_anyone_create_screen_content( 'create' );

		wp_nonce_field( 'groups_create_save_' . $this->slug );
	}

	function create_screen_save( ) {
		global $bp;

		/* Always check the referer */
		check_admin_referer( 'groups_create_save_' . $this->slug );

		/* Set method and save */
		if ( bp_group_has_invites() )
			$this->has_invites = true;
		$this->method = 'create';
		$this->save();
	}

	function save() {
		global $bp;

		/* Set error redirect based on save method */
		if ( $this->method == 'create' )
			$redirect_url = $bp->loggedin_user->domain . $bp->groups->slug . '/create/step/' . $this->slug;
		else
			$redirect_url = bp_get_group_permalink( $bp->groups->current_group ) . '/admin/' . $this->slug;

		groups_send_invites( $bp->loggedin_user->id, $bp->groups->current_group->id );

		if ( $this->has_invites )
			bp_core_add_message( __('Group invites sent.', 'buddypress') );
		else
			bp_core_add_message( __('Group created successfully.', 'buddypress') );
	}

	function enable_nav_item() {
		global $bp;

		if ( invite_anyone_group_invite_access_test() == 'anyone' )
			return true;
		else
			return false;
	}

	function widget_display() {}
}
bp_register_group_extension( 'BP_Invite_Anyone' );


function invite_anyone_catch_group_invites() {
	global $bp;

	if ( BP_INVITE_ANYONE_SLUG == $bp->current_action && 'send' == $bp->action_variables[0] ) {
		if ( !check_admin_referer( 'groups_send_invites', '_wpnonce_send_invites' ) )
			return false;

		// Send the invites.
		groups_send_invites( $bp->loggedin_user->id, $bp->groups->current_group->id );

		bp_core_add_message( __('Group invites sent.', 'buddypress') );

		do_action( 'groups_screen_group_invite', $bp->groups->current_group->id );

		bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) );
	}
}
//add_action( 'wp', 'invite_anyone_catch_group_invites', 1 );



function invite_anyone_create_screen_content( $event ) {
	if ( !$template = locate_template( 'groups/single/invite-anyone.php', true ) ) {
		include_once( 'templates/invite-anyone.php' );
	}
}

/* Creates the list of members on the Sent Invite screen */
function bp_new_group_invite_member_list() {
	echo bp_get_new_group_invite_member_list();
}
	function bp_get_new_group_invite_member_list( $args = '' ) {
		global $bp;

		$defaults = array(
			'group_id' => false,
			'separator' => 'li'
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		if ( !$group_id )
			$group_id = ( $bp->groups->new_group_id ) ? $bp->groups->new_group_id : $bp->groups->current_group->id;

		$friends = get_members_invite_list( $bp->loggedin_user->id, $group_id );

		if ( $friends ) {
			$invites = groups_get_invites_for_group( $bp->loggedin_user->id, $group_id );

			for ( $i = 0; $i < count( $friends ); $i++ ) {
				if ( $invites ) {
					if ( in_array( $friends[$i]['id'], $invites ) ) {
						$checked = ' checked="checked"';
					} else {
						$checked = '';
					}
				}

				$items[] = '<' . $separator . '><input' . $checked . ' type="checkbox" name="friends[]" id="f-' . $friends[$i]['id'] . '" value="' . attribute_escape( $friends[$i]['id'] ) . '" /> ' . $friends[$i]['full_name'] . '</' . $separator . '>';
			}
		}

		return implode( "\n", (array)$items );
	}


function get_members_invite_list( $user_id = false, $group_id ) {
	global $bp, $wpdb;

	if ( !$user_id )
		$user_id = $bp->loggedin_user->id;

	$query = "SELECT * FROM {$wpdb->users} WHERE spam=0";
	$members = $wpdb->get_results( $query, ARRAY_A );

	if ( !count($members) ) {
		$query = "SELECT * FROM {$wpdb->users} WHERE user_status = 0";
		$members = $wpdb->get_results( $query, ARRAY_A );
	}

	if ( !count($members) )
		return false;

	foreach( $members as $member ) {
		$user_id = $member['ID'];

		if ( groups_is_user_member( $user_id, $group_id ) )
			continue;

		$display_name = bp_core_get_user_displayname( $user_id );

		if ( $display_name != '' ) {
			$friends[] = array(
				'id' => $user_id,
				'full_name' => $display_name
			);
		}
	}


	if ( !$friends )
		return false;

	return $friends;
}

function invite_anyone_ajax_invite_user() {
	global $bp;

	check_ajax_referer( 'groups_invite_uninvite_user' );

	if ( !$_POST['friend_id'] || !$_POST['friend_action'] || !$_POST['group_id'] )
		return false;



/*	if ( !groups_is_user_admin( $bp->loggedin_user->id, $_POST['group_id'] ) )
		return false; */

	if ( 'invite' == $_POST['friend_action'] ) {

		if ( !groups_invite_user( array( 'user_id' => $_POST['friend_id'], 'group_id' => $_POST['group_id'] ) ) )
			return false;

		$user = new BP_Core_User( $_POST['friend_id'] );

		echo '<li id="uid-' . $user->id . '">';
		echo $user->avatar_thumb;
		echo '<h4>' . $user->user_link . '</h4>';
		echo '<span class="activity">' . attribute_escape( $user->last_active ) . '</span>';
		echo '<div class="action">
				<a class="remove" href="' . wp_nonce_url( $bp->loggedin_user->domain . $bp->groups->slug . '/' . $_POST['group_id'] . '/invites/remove/' . $user->id, 'groups_invite_uninvite_user' ) . '" id="uid-' . attribute_escape( $user->id ) . '">' . __( 'Remove Invite', 'buddypress' ) . '</a>
			  </div>';
		echo '</li>';

	} else if ( 'uninvite' == $_POST['friend_action'] ) {

		if ( !groups_uninvite_user( $_POST['friend_id'], $_POST['group_id'] ) )
			return false;

		return true;

	} else {
		return false;
	}
}
add_action( 'wp_ajax_invite_anyone_groups_invite_user', 'invite_anyone_ajax_invite_user' );

function invite_anyone_ajax_autocomplete_results() {
	global $bp;

	$friends = false;

	// Get the friend ids based on the search terms
	$friends = BP_Core_User::search_users( $_GET['q'], 500, 1 );
		
	$friends = apply_filters( 'bp_friends_autocomplete_list', $friends, $_GET['q'], $_GET['limit'] );

	if ( $friends['users'] ) {
		foreach ( $friends['users'] as $user ) {
			if ( $user->user_id ) // For BP < 1.2
				$user_id = $user->user_id;
			else
				$user_id = $user->id;
			$ud = get_userdata($user_id);
			$username = $ud->user_login;
			echo bp_core_fetch_avatar( array( 'item_id' => $user_id, 'type' => 'thumb', 'width' => 25, 'height' => 25 ) ) . ' ' . bp_core_get_user_displayname( $user->user_id ) . ' (' . $username . ')
			';
		}
	}
}
add_action( 'wp_ajax_invite_anyone_autocomplete_results', 'invite_anyone_ajax_autocomplete_results' );

function invite_anyone_remove_group_creation_invites( $a ) {

	foreach ( $a as $key => $value ) {
		if ( $key == 'group-invites' ) {
			unset( $a[$key] );
		}
	}
	return $a;
}

function invite_anyone_remove_invite_subnav() {
	global $bp;
	
	if ( invite_anyone_group_invite_access_test() == 'friends' )
		return;

	if ( $bp->groups->group_creation_steps['group-invites'] )
		unset( $bp->groups->group_creation_steps['group-invites'] );

	bp_core_remove_subnav_item( $bp->groups->slug, 'send-invites' );
	
	// BP 1.3 compat - remove the subnav item
	bp_core_remove_subnav_item( $bp->groups->current_group->slug, 'send-invites' );
}
add_filter( 'groups_create_group_steps', 'invite_anyone_remove_group_creation_invites', 1 );
add_action( 'bp_setup_nav', 'invite_anyone_remove_invite_subnav', 10 );


/* Utility function to test which members the current user can invite to a group */
function invite_anyone_group_invite_access_test() {
	global $current_user, $bp;

	if ( !is_user_logged_in() )
		return 'noone';

	if ( !$iaoptions = get_option( 'invite_anyone' ) )
		$iaoptions = array();

	if ( bp_is_group_create() ) {
		if ( $iaoptions['group_invites_can_group_admin'] == 'anyone' || !$iaoptions['group_invites_can_group_admin'] )
			return 'anyone';
		if ( $iaoptions['group_invites_can_group_admin'] == 'friends' )
			return 'friends';
		if ( $iaoptions['group_invites_can_group_admin'] == 'noone' )
			return 'noone';	
	}		

	if ( !groups_is_user_member( $bp->loggedin_user->id, $bp->groups->current_group->id ) )
		return 'noone';

	if ( is_super_admin() ) {
		if ( $iaoptions['group_invites_can_admin'] == 'anyone' || !$iaoptions['group_invites_can_admin'] )
			return 'anyone';
		if ( $iaoptions['group_invites_can_admin'] == 'friends' )
			return 'friends';
		if ( $iaoptions['group_invites_can_admin'] == 'noone' )
			return 'noone';
	} else if ( bp_group_is_admin() || bp_is_group_create() ) {
		if ( $iaoptions['group_invites_can_group_admin'] == 'anyone' || !$iaoptions['group_invites_can_group_admin'] )
			return 'anyone';
		if ( $iaoptions['group_invites_can_group_admin'] == 'friends' )
			return 'friends';
		if ( $iaoptions['group_invites_can_group_admin'] == 'noone' )
			return 'noone';
	} else if ( bp_group_is_mod() ) {
		if ( $iaoptions['group_invites_can_group_mod'] == 'anyone' || !$iaoptions['group_invites_can_group_mod'] )
			return 'anyone';
		if ( $iaoptions['group_invites_can_group_mod'] == 'friends' )
			return 'friends';
		if ( $iaoptions['group_invites_can_group_mod'] == 'noone' )
			return 'noone';
	} else {
		if ( $iaoptions['group_invites_can_group_member'] == 'anyone' || !$iaoptions['group_invites_can_group_member'] )
			return 'anyone';
		if ( $iaoptions['group_invites_can_group_member'] == 'friends' )
			return 'friends';
		if ( $iaoptions['group_invites_can_group_member'] == 'noone' )
			return 'noone';
	}

	return 'noone';
}

?>