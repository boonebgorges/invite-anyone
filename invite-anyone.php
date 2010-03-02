<?php

/*
Plugin Name: Invite Anyone
Plugin URI: http://teleogistic.net/code/buddypress/invite-anyone/
Description: Allows group admins to invite any BuddyPress member to a group, whether or not they are friends
Version: 0.3.2
Author: Boone Gorges
Author URI: http://teleogistic.net
*/


if ( !defined( 'BP_INVITE_ANYONE_SLUG' ) )
	define( 'BP_INVITE_ANYONE_SLUG', 'invite-anyone' );

/* Only load the BuddyPress plugin functions if BuddyPress is loaded and initialized. */
function invite_anyone_init() {
	require( dirname( __FILE__ ) . '/invite-anyone-bp-functions.php' );
}
add_action( 'bp_init', 'invite_anyone_init' );

if ( function_exists( 'bp_post_get_permalink' ) )
	require( dirname( __FILE__ ) . '/invite-anyone-bp-functions.php' );

?>