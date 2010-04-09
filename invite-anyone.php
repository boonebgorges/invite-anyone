<?php

/*
Plugin Name: Invite Anyone
Plugin URI: http://teleogistic.net/code/buddypress/invite-anyone/
Description: Allows group admins to invite any BuddyPress member to a group, whether or not they are friends
Version: 0.5.1
Author: Boone Gorges
Author URI: http://teleogistic.net
*/

define( 'BP_INVITE_ANYONE_VER', '0.5' );

if ( !defined( 'BP_INVITE_ANYONE_SLUG' ) )
	define( 'BP_INVITE_ANYONE_SLUG', 'invite-anyone' );

/* Only load the BuddyPress plugin functions if BuddyPress is loaded and initialized. */
function invite_anyone_init() {
	require( dirname( __FILE__ ) . '/invite-anyone-bp-functions.php' );
}
add_action( 'bp_init', 'invite_anyone_init' );

if ( function_exists( 'bp_post_get_permalink' ) )
	require( dirname( __FILE__ ) . '/invite-anyone-bp-functions.php' );


function invite_anyone_locale_init () {
	$plugin_dir = basename(dirname(__FILE__));
	$locale = get_locale();
	$mofile = WP_PLUGIN_DIR . "/invite-anyone/languages/invite-anyone-$locale.mo";
      
      if ( file_exists( $mofile ) )
      		load_textdomain( 'bp-invite-anyone', $mofile );
}
add_action ('plugins_loaded', 'invite_anyone_locale_init');



function invite_anyone_activation() {
	require( dirname( __FILE__ ) . '/invite-anyone/db.php' );
	invite_anyone_create_table();
	
	if ( !$iaoptions = get_option( 'invite_anyone' ) )
		$iaoptions = array(
			'max_invites' => 5,
			'allow_email_invitations' => 'all',
			'message_is_customizable' => 'yes',
			'subject_is_customizable' => 'no'
		);
	
	update_option( 'invite_anyone', $iaoptions );
}
register_activation_hook( __FILE__, 'invite_anyone_activation' );

?>