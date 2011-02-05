<?php
/* Invite Anyone database functions */

class Invite_Anyone_Data {
	var $post_type_name;
	var $invitee_tax_name;
	
	/**
	 * PHP4 Constructor
	 *
	 * @package Invite Anyone
	 * @since 0.8
	 */
	function bpgr_settings() {
		$this->construct();
	}
	
	/**
	 * PHP5 Constructor
	 *
	 * @package Invite Anyone
	 * @since 0.8
	 */
	function __construct() {
		// Define the post type name used throughout
		$this->post_type_name = apply_filters( 'invite_anyone_post_type_name', 'ia_invites' );
		
		// Define the invitee tax name used throughout
		$this->invitee_tax_name = apply_filters( 'invite_anyone_invitee_tax_name', 'ia_invitees' );
		
		// Hooks into the 'init' action to register our WP custom post type and tax
		add_action( 'init', array( $this, 'register_post_type' ) );	
	}

	/**
	 * Registers Invite Anyone's post type
	 *
	 * @package BuddyPress Docs
	 * @since 1.0
	 */
	function register_post_type() {
		// Define the labels to be used by the post type		
		$post_type_labels = apply_filters( 'invite_anyone_post_type_labels', array(
			'name' 			=> _x( 'BuddyPress Invitations', 'post type general name', 'bp-invite-anyone' ),
			'singular_name' 	=> _x( 'Invitation', 'post type singular name', 'bp-invite-anyone' ),
			'add_new' 		=> _x( 'Add New', 'add new', 'bp-invite-anyone' ),
			'add_new_item' 		=> __( 'Add New Invitation', 'bp-invite-anyone' ),
			'edit_item' 		=> __( 'Edit Invitation', 'bp-invite-anyone' ),
			'new_item' 		=> __( 'New Invitation', 'bp-invite-anyone' ),
			'view_item' 		=> __( 'View Invitation', 'bp-invite-anyone' ),
			'search_items' 		=> __( 'Search Invitation', 'bp-invite-anyone' ),
			'not_found' 		=>  __( 'No Invitations found', 'bp-invite-anyone' ),
			'not_found_in_trash' 	=> __( 'No Invitations found in Trash', 'bp-invite-anyone' ),
			'parent_item_colon' 	=> ''
		), &$this );
	
		// Register the invitation post type
		register_post_type( $this->post_type_name, apply_filters( 'invite_anyone_post_type_args', array(
			'label' 	=> __( 'BuddyPress Invitations', 'bp-invite-anyone' ),
			'labels' 	=> $post_type_labels,
			'public' 	=> false,
			'_builtin' 	=> false,
			'show_ui' 	=> true,
			'hierarchical' 	=> false,
			'supports' 	=> array( 'title', 'editor' )
		), &$this ) );
		
		// Define the labels to be used by the invitee taxonomy
		$invitee_labels = apply_filters( 'invite_anyone_invitee_labels', array(
			'name' 		=> __( 'Invitees', 'bp-invite-anyone' ),
			'singular_name' => __( 'Invitee', 'bp-invite-anyone' ),
			'search_items' 	=>  __( 'Search Invitees', 'bp-invite-anyone' ),
			'all_items' 	=> __( 'All Invitees', 'bp-invite-anyone' ),
			'edit_item' 	=> __( 'Edit Invitee', 'bp-invite-anyone' ), 
			'update_item' 	=> __( 'Update Invitee', 'bp-invite-anyone' ),
			'add_new_item' 	=> __( 'Add New Invitee', 'bp-invite-anyone' ),
			'new_item_name' => __( 'New Invitee Name', 'bp-invite-anyone' ),
			'menu_name' 	=> __( 'Invitee' ),
		), &$this );
		
		// Register the invitee taxonomy
		register_taxonomy( $this->invitee_tax_name, $this->post_type_name, apply_filters( 'invite_anyone_invitee_tax_args', array(
			'label'		=> __( 'Invitees', 'bp-invite-anyone' ),
			'labels' 	=> $invitee_labels,
			'hierarchical' 	=> false,
			'show_ui' 	=> true,
		), &$this ) );
	}
}

$invite_anyone_data = new Invite_Anyone_Data;

function invite_anyone_create_table() {
	global $wpdb;
	
	$table_name = $wpdb->base_prefix . 'bp_invite_anyone';  
  
	if ( get_option('bp_invite_anyone_ver') != BP_INVITE_ANYONE_VER ) {
		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			inviter_id bigint(20) NOT NULL,
			email varchar(75) NOT NULL,
			message longtext NOT NULL,
			group_invitations longtext,
			date_invited datetime NOT NULL,
			is_joined tinyint(1) NOT NULL,
			date_joined datetime,
			is_opt_out tinyint(1) NOT NULL,
			is_hidden tinyint(1) NOT NULL
			) {$charset_collate};";   		
	
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		update_option( 'bp_invite_anyone_ver', BP_INVITE_ANYONE_VER );
	}
}

function invite_anyone_record_invitation( $inviter_id, $email, $message, $groups ) {
	global $wpdb, $bp;
	
	$group_invitations = maybe_serialize( $groups );
	$date_invited = gmdate( "Y-m-d H:i:s" );
	$is_joined = 0;
	
	$sql = $wpdb->prepare( "INSERT INTO {$bp->invite_anyone->table_name} ( inviter_id, email, message,  group_invitations, date_invited, is_joined ) VALUES ( %d, %s, %s, %s, %s, %d )", $inviter_id, $email, $message, $group_invitations, $date_invited, $is_joined );
			
	if ( !$wpdb->query($sql) )
		return false;
	
	return true;
}

function invite_anyone_get_invitations_by_inviter_id( $id, $sort_by = false, $order = false ) {
	global $wpdb, $bp;
		
	$sql = $wpdb->prepare( "SELECT * FROM {$bp->invite_anyone->table_name} WHERE inviter_id = %s AND is_hidden = 0", $id );
	
	switch ( $sort_by ) {
		case 'date_invited' :
			$sql .= ' ORDER BY date_invited';
			break;
		case 'date_joined' :
			$sql .= ' ORDER BY date_joined';
			break;
		case 'email' :
			$sql .= ' ORDER BY email';
			break;	
	}
	
	if ( $order )
		$sql .= ' ' . $order;
	
	$results = $wpdb->get_results($sql);
	return $results;	

}

function invite_anyone_get_invitations_by_invited_email( $email ) {
	global $wpdb, $bp;
		
	$sql = $wpdb->prepare( "SELECT * FROM {$bp->invite_anyone->table_name} WHERE email = %s", $email );
	
	$results = $wpdb->get_results($sql);
	return $results;	
}

function invite_anyone_clear_sent_invite( $args ) {
	global $wpdb, $bp;
	
	/* Accepts arguments: array(
		'inviter_id' => id number of the inviter, (required)
		'clear_id' => id number of the item to be cleared,
		'type' => accepted, unaccepted, or all
		
			); */
	
	extract( $args );
	
	if ( !$inviter_id )
		return false;
	
	if ( $clear_id )
		$sql = $wpdb->prepare( "UPDATE {$bp->invite_anyone->table_name} SET is_hidden = 1 WHERE id = %d", $clear_id );
	else if ( $type == 'accepted' )
		$sql = $wpdb->prepare( "UPDATE {$bp->invite_anyone->table_name} SET is_hidden = 1 WHERE inviter_id = %d AND is_joined = 1", $inviter_id );
	else if ( $type == 'unaccepted' )
		$sql = $wpdb->prepare( "UPDATE {$bp->invite_anyone->table_name} SET is_hidden = 1 WHERE inviter_id = %d AND is_joined = 0", $inviter_id );
	else if ( $type == 'all' )
		$sql = $wpdb->prepare( "UPDATE {$bp->invite_anyone->table_name} SET is_hidden = 1 WHERE inviter_id = %d", $inviter_id );
	else
		return false;	
	
	if ( !$wpdb->query($sql) )
		return false;
	
	return true;

}

function invite_anyone_mark_as_joined( $email ) {
	global $wpdb, $bp;
	
	$is_joined = 1;
	$date_joined = gmdate( "Y-m-d H:i:s" );
		
	$sql = $wpdb->prepare( "UPDATE {$bp->invite_anyone->table_name} SET is_hidden = 0, is_joined = %d, date_joined = %s WHERE email = %s", $is_joined, $date_joined, $email ); 
	
	if ( !$wpdb->query($sql) )
		return false;
	
	return true;
}

function invite_anyone_check_is_opt_out( $email ) {
	global $wpdb, $bp;
	
	$sql = $wpdb->prepare( "SELECT * FROM {$bp->invite_anyone->table_name} WHERE email = %s AND is_opt_out = 1", $email );
	
	$results = $wpdb->get_results($sql);
	
	if ( !$results )
		return false;
	
	return true;
}


function invite_anyone_mark_as_opt_out( $email ) {
	global $wpdb, $bp;
	
	$is_opt_out = 1;
	
	$table_name = $wpdb->base_prefix . 'bp_invite_anyone';  
	
	$sql = $wpdb->prepare( "UPDATE {$table_name} SET is_opt_out = %d WHERE email = %s", $is_opt_out, $email );
	
	if ( !$wpdb->query($sql) )
		return false;
	
	return true;
}


?>