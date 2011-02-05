<?php
/* Invite Anyone database functions */

/**
 * Defines the data schema for IA Invitations
 *
 * @package Invite Anyone
 * @since 0.8
 */
class Invite_Anyone_Schema {
	var $post_type_name;
	var $invitee_tax_name;
	var $invited_groups_tax_name;
	
	/**
	 * PHP4 Constructor
	 *
	 * @package Invite Anyone
	 * @since 0.8
	 */
	function invite_anyone_schema() {
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
		
		// Define the invited group tax name used throughout
		$this->invited_groups_tax_name = apply_filters( 'invite_anyone_invited_group_tax_name', 'ia_invited_groups' );
		
		// Hooks into the 'init' action to register our WP custom post type and tax
		add_action( 'init', array( $this, 'register_post_type' ) );	
	}

	/**
	 * Registers Invite Anyone's post type and taxonomies
	 *
	 * Data schema:
	 * - The ia_invites post type represents individual invitations, with post data divvied up
	 *   as follows:
	 *     	- post_title is the subject of the email sent
	 *     	- post_content is the content of the email
	 *     	- post_author is the person sending the invitation
	 *     	- post_date is the date/time when the invitation is sent
	 *     	- post_status represents 'is_hidden' on the old custom table schema:
	 *      	- Default is 'publish' - i.e. the user sees the invitation on Sent Invites
	 *     		- When the invitation is hidden, it is switched to 'draft'
	 *     	- date_modified is the date when the user joins the site (makes for easy sorting)
	 * - The ia_invitees taxonomy represents invited email addresses
	 * - The ia_invited_groups taxonomy represents the groups that a user has been invited to
	 *   when the group invitation is sent
	 * - The following data is stored in postmeta:
	 * 	- opt_out (corresponds to old is_opt_out) is stored at opt_out time
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
		
		// Define the labels to be used by the invited groups taxonomy
		$invited_groups_labels = apply_filters( 'invite_anyone_invited_groups_labels', array(
			'name' 		=> __( 'Invited Groups', 'bp-invite-anyone' ),
			'singular_name' => __( 'Invited Group', 'bp-invite-anyone' ),
			'search_items' 	=>  __( 'Search Invited Groups', 'bp-invite-anyone' ),
			'all_items' 	=> __( 'All Invited Groups', 'bp-invite-anyone' ),
			'edit_item' 	=> __( 'Edit Invited Group', 'bp-invite-anyone' ), 
			'update_item' 	=> __( 'Update Invited Group', 'bp-invite-anyone' ),
			'add_new_item' 	=> __( 'Add New Invited Group', 'bp-invite-anyone' ),
			'new_item_name' => __( 'New Invited Group Name', 'bp-invite-anyone' ),
			'menu_name' 	=> __( 'Invited Group' ),
		), &$this );
		
		// Register the invited groups taxonomy
		register_taxonomy( $this->invited_groups_tax_name, $this->post_type_name, apply_filters( 'invite_anyone_invited_group_tax_args', array(
			'label'		=> __( 'Invited Groups', 'bp-invite-anyone' ),
			'labels' 	=> $invited_groups_labels,
			'hierarchical' 	=> false,
			'show_ui' 	=> true,
		), &$this ) );
	}
}

$invite_anyone_data = new Invite_Anyone_Schema;

/**
 * Defines the invitation object and its methods
 *
 * @package Invite Anyone
 * @since 0.8
 */
class Invite_Anyone_Invitation {
	var $id;
	
	/**
	 * PHP4 Constructor
	 *
	 * @package Invite Anyone
	 * @since 0.8
	 *
	 * @param int $id Optional. The unique id of the invitation post
	 */
	function invite_anyone_schema( $id = false ) {
		$this->construct( $id );
	}
	
	/**
	 * PHP5 Constructor
	 *
	 * @package Invite Anyone
	 * @since 0.8
	 *
	 * @param int $id Optional. The unique id of the invitation post
	 */
	 function __construct( $id = false ) {
	 	if ( $id ) {
	 		$this->id = $id;
	 		$this->populate( $id );
	 	}
	 
	 	// Define the post type name used throughout
		$this->post_type_name = apply_filters( 'invite_anyone_post_type_name', 'ia_invites' );
		
		// Define the invitee tax name used throughout
		$this->invitee_tax_name = apply_filters( 'invite_anyone_invitee_tax_name', 'ia_invitees' );
		
		// Define the invited group tax name used throughout
		$this->invited_groups_tax_name = apply_filters( 'invite_anyone_invited_group_tax_name', 'ia_invited_groups' );
	}
	
	/**
	 * Populates the data for an existing invitation invitation
	 *
	 * @package Invite Anyone
	 * @since 0.8
	 */
	function populate( $id ) {
		
	}
	
	/**
	 * Creates a new invitation
	 *
	 * See the $defaults array for the potential values of $args
	 *
	 * @package Invite Anyone
	 * @since 0.8
	 *
	 * @param array $args
	 */
	function create( $args = false ) {
		// Set up the default arguments
		$defaults = apply_filters( 'invite_anyone_create_invite_defaults', array(
			'inviter_id' 	=> bp_loggedin_user_id(),
			'invitee_email'	=> false,
			'message'	=> false,
			'subject'	=> false,
			'groups'	=> false,
			'status'	=> 'publish', // i.e., visible on Sent Invites
			'date_created'	=> bp_core_current_time()
		) );
		
		$r = wp_parse_args( $args, $defaults );
		extract( $r );
		
		// Let plugins stop this process if they want
		do_action( 'invite_anyone_before_invitation_create', $r, $args );
		
		// We can't record an invitation without a few key pieces of data
		if ( empty( $inviter_id ) || empty( $invitee_email ) || empty( $message ) || empty( $subject ) )
			return false;
		
		// Set the arguments and create the post
		$insert_post_args = array(
			'post_author'	=> $inviter_id,
			'post_content'	=> $message,
			'post_title'	=> $subject,
			'post_status'	=> $status,
			'post_type'	=> $this->post_type_name
		);
		
		if ( !$this->id = wp_insert_post( $insert_post_args ) )
			return false;
	
		// Now set up the taxonomy terms
		
		// Invitee
		wp_set_post_terms( $this->id, $invitee_email, $this->invitee_tax_name );
	
		// Groups included in the invitation
		if ( !empty( $groups ) )
			wp_set_post_terms( $this->id, $groups, $this->invited_groups_tax_name );
	
		do_action( 'invite_anyone_after_invitation_create', $this->id, $r, $args );
		
		return $this->id;		
	}
}


function invite_anyone_record_invitation( $inviter_id, $email, $message, $groups ) {
	global $wpdb, $bp;
	
	$args = array(
			'inviter_id' 	=> $inviter_id,
			'invitee_email'	=> $email,
			'message'	=> $message,
			'subject'	=> 'Phat yo',
			'groups'	=> $groups
		);
	
	$invite = new Invite_Anyone_Invitation;
	$id = $invite->create( $args );
	return true;
	
	
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