<?php

/*
Plugin Name: Invite Anyone
Plugin URI: http://teleogistic.net/code/buddypress/invite-anyone/
Description: Allows group admins to invite any BuddyPress member to a group, whether or not they are friends
Version: 0.7.1
Author: Boone Gorges
Author URI: http://teleogistic.net
*/

define( 'BP_INVITE_ANYONE_VER', '0.7' );

if ( !defined( 'BP_INVITE_ANYONE_SLUG' ) )
	define( 'BP_INVITE_ANYONE_SLUG', 'invite-anyone' );

/* Only load the BuddyPress plugin functions if BuddyPress is loaded and initialized. */
function invite_anyone_init() {
	if ( function_exists( 'bp_is_active' ) ) {
		if ( bp_is_active( 'groups' ) )
			require( dirname( __FILE__ ) . '/group-invites/group-invites.php' );
	} else if ( function_exists( 'groups_install' ) ) {
		require( dirname( __FILE__ ) . '/group-invites/group-invites.php' );
	}

	require( dirname( __FILE__ ) . '/by-email/by-email.php' );

	if ( is_admin() )
		require( dirname( __FILE__ ) . '/admin/admin-panel.php' );
}
add_action( 'bp_include', 'invite_anyone_init' );

if ( function_exists( 'bp_post_get_permalink' ) )
	require( dirname( __FILE__ ) . '/group-invites/group-invites.php' );


function invite_anyone_locale_init () {
	$plugin_dir = basename(dirname(__FILE__));
	$locale = get_locale();
	$mofile = WP_PLUGIN_DIR . "/invite-anyone/languages/invite-anyone-$locale.mo";

      if ( file_exists( $mofile ) )
      		load_textdomain( 'bp-invite-anyone', $mofile );
}
add_action ('plugins_loaded', 'invite_anyone_locale_init');



function invite_anyone_activation() {
	require( dirname( __FILE__ ) . '/by-email/by-email-db.php' );
	invite_anyone_create_table();

	if ( !$iaoptions = get_option( 'invite_anyone' ) )
		$iaoptions = array();

	if ( !$iaoptions['max_invites'] )
		$iaoptions['max_invites'] = 5;

	if ( !$iaoptions['allow_email_invitations'] )
		$iaoptions['allow_email_invitations'] = 'all';

	if ( !$iaoptions['message_is_customizable'] )
		$iaoptions['message_is_customizable'] = 'yes';

	if ( !$iaoptions['subject_is_customizable'] )
		$iaoptions['subject_is_customizable'] = 'no';

	if ( !$iaoptions['can_send_group_invites_email'] )
		$iaoptions['can_send_group_invites_email'] = 'yes';
	
	if ( !$iaoptions['bypass_registration_lock'] )
		$iaoptions['bypass_registration_lock'] = 'yes';

	update_option( 'invite_anyone', $iaoptions );
}
register_activation_hook( __FILE__, 'invite_anyone_activation' );

?>