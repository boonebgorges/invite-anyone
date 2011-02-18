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
		global $bp;
	
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
			'supports' 	=> array( 'title', 'editor', 'custom-fields' )
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
		
		// Stash in $bp because of template tags that need it
		$bp->invite_anyone->invitee_tax_name = $this->invitee_tax_name;
		$bp->invite_anyone->invited_groups_tax_name = $this->invited_groups_tax_name;
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
	var $invitee_tax_name;
	var $post_type_name;
	var $invited_groups_tax_name;
	
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
	 	}
	 
	 	// Define the post type name used throughout
		$this->post_type_name = apply_filters( 'invite_anyone_post_type_name', 'ia_invites' );
		
		// Define the invitee tax name used throughout
		$this->invitee_tax_name = apply_filters( 'invite_anyone_invitee_tax_name', 'ia_invitees' );
		
		// Define the invited group tax name used throughout
		$this->invited_groups_tax_name = apply_filters( 'invite_anyone_invited_group_tax_name', 'ia_invited_groups' );
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
	
	/**
	 * Pulls up a list of existing invitations, based on a set of arguments provided
	 *
	 * See the $defaults array for the potential values of $args
	 *
	 * @package Invite Anyone
	 * @since 0.8
	 *
	 * @param array $args
	 */
	function get( $args = false ) {
		// Set up the default arguments
		$defaults = apply_filters( 'invite_anyone_get_invite_defaults', array(
			'inviter_id' 		=> false,
			'invitee_email'		=> false,
			'message'		=> false,
			'subject'		=> false,
			'groups'		=> false,
			'status'		=> 'publish', // i.e., visible on Sent Invites
			'date_created'		=> false,
			'orderby'		=> 'post_date',
			'order'			=> 'DESC'
		) );
		
		$r = wp_parse_args( $args, $defaults );
		extract( $r );
		
		// Backward compatibility, and to keep the URL args clean
		if ( $orderby == 'email' ) {
			$orderby	= $this->invitee_tax_name;
		} else if ( $orderby == 'date_joined' ) {
			$orderby	= 'date_modified';
		}
		
		// Let plugins stop this process if they want
		do_action( 'invite_anyone_before_invitation_get', $r, $args );
		
		// Set the arguments and get the posts
		$query_post_args = array(
			'author'	=> $inviter_id,
			'post_status'	=> $status,
			'post_type'	=> $this->post_type_name,
			'orderby'	=> $orderby,
			'order'		=> $order
		);
		
		// Add optional arguments, if provided
		// Todo: The tax and meta stuff needs to be updated for 3.1 queries
		$optional_args = array(
			'message' 	=> 'post_content',
			'subject'	=> 'post_title',
			'date_created'	=> 'date_created',
			'invitee_email'	=> $this->invitee_tax_name,
			'meta_key'	=> 'meta_key',
			'meta_value'	=> 'meta_value'
		);
		
		foreach ( $optional_args as $key => $value ) {
			if ( !empty( $r[$key] ) ) {
				$query_post_args[$value] = $r[$key];
			}
		}
		
		query_posts( $query_post_args );
	}
	
	/**
	 * Mark an invitation as accepted
	 *
	 * @package Invite Anyone
	 * @since 0.8
	 *
	 * @param array $args
	 */
	function mark_accepted() {
		$args = array(
			'ID'		=> $this->id,
			'post_modified'	=> current_time('mysql')
		);
		
		if ( wp_update_post( $args ) )
			return true;
			
		return false;
	}
	
	/**
	 * Clear (unpublish) an invitation
	 *
	 * @package Invite Anyone
	 * @since 0.8
	 *
	 * @param array $args
	 */
	function clear() {
		if ( wp_delete_post( $this->id ) )
			return true;
		
		return false;
	}
	
	/**
	 * Mark an invite as being opt-out
	 *
	 * @package Invite Anyone
	 * @since 0.8
	 *
	 * @param array $args
	 */
	function mark_opt_out() {
		if ( update_post_meta( $this->id, 'opt_out', 'yes' ) )
			return true;
		
		return false;		
	}
}

/**
 * Records an invitation
 *
 * @package Invite Anyone
 * @since {@internal Version Unknown}
 *
 * @param int $inviter_id
 * @param str $email The email address of the individual receiving the invitation
 * @param str $message The content of the email message
 * @param array $groups An array of group ids that the invitation invites the user to join
 * @param str $subject Optional The subject line of the email
 */
function invite_anyone_record_invitation( $inviter_id, $email, $message, $groups, $subject = false ) {
	$args = array(
		'inviter_id' 	=> $inviter_id,
		'invitee_email'	=> $email,
		'message'	=> $message,
		'subject'	=> $subject,
		'groups'	=> $groups
	);
	
	$invite = new Invite_Anyone_Invitation;
	
	$id = $invite->create( $args );
	
	return $id;
}


/**
 * Get the invitations that a user has sent
 *
 * @package Invite Anyone
 * @since {@internal Version Unknown}
 *
 * @param int $inviter_id
 * @param str $orderby Optional The column being ordered by
 * @param str $order Optional ASC or DESC
 */
function invite_anyone_get_invitations_by_inviter_id( $inviter_id, $orderby = false, $order = false ) {
	$args = array(
		'inviter_id'	=> $inviter_id,
		'orderby'	=> $orderby,
		'order'		=> $order
	);
	
	$invite = new Invite_Anyone_Invitation;
	
	$invite->get( $args );
}

/**
 * Get the invitations that have been sent to a given email address
 *
 * @package Invite Anyone
 * @since {@internal Version Unknown}
 *
 * @param str $email The email address being checked
 */
function invite_anyone_get_invitations_by_invited_email( $email ) {
	// hack to make sure that gmail + email addresses work
	$email	= str_replace( ' ', '+', $email );
	
	$args = array(
		'invitee_email'	=> $email
	);
	
	$invite = new Invite_Anyone_Invitation;
	
	$invite->get( $args );
}

/**
 * Clears invitations from the Sent Invites list
 *
 * @package Invite Anyone
 * @since {@internal Version Unknown}
 *
 * @param array $args See below for the definition
 */
function invite_anyone_clear_sent_invite( $args ) {
	global $post;
	
	/* Accepts arguments: array(
		'inviter_id' => id number of the inviter, (required)
		'clear_id' => id number of the item to be cleared,
		'type' => accepted, unaccepted, or all
	); */
	
	extract( $args );
	
	if ( empty( $inviter_id ) )
		return false;
	
	$success = false;
	
	if ( $clear_id ) {
		$invite = new Invite_Anyone_Invitation( $clear_id );
		if ( $invite->clear() )
			$success = true;
	} else {
		array(
			'inviter_id'	=> $inviter_id
		);
		
		$invite = new Invite_Anyone_Invitation;
		
		$invite->get( $args );
		
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();

				$clearme = false;				
				switch ( $type ) {
					case 'accepted' :	
						if ( $post->post_modified != $post->post_date ) {
							$clearme = true;
						}	
						break;
					case 'unaccepted' :	
						if ( $post->post_modified == $post->post_date ) {
							$clearme = true;
						}	
						break;
					case 'all' :
					default :
						$clearme = true;	
						break;
				}
				
				if ( $clearme ) {
					$this_invite = new Invite_Anyone_Invitation( get_the_ID() );
					$this_invite->clear();
				}
			}
		}
	}
	
	return true;

}

/**
 * Mark all of the invitations associated with a given address as joined 
 *
 * @package Invite Anyone
 * @since {@internal Version Unknown}
 *
 * @param str $email The email address being checked
 */
function invite_anyone_mark_as_joined( $email ) {
	invite_anyone_get_invitations_by_invited_email( $email );
	
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			
			$invite = new Invite_Anyone_Invitation( get_the_ID() );
			$invite->mark_accepted();
		}
	}
	
	return true;
}

/**
 * Check to see whether a user has opted out of email invitations from the site
 *
 * @package Invite Anyone
 * @since {@internal Version Unknown}
 *
 * @param str $email The email address being checked
 */
function invite_anyone_check_is_opt_out( $email ) {
	$email = str_replace( ' ', '+', $email );

	$args = array(
		'invitee_email'		=> $email,
		'posts_per_page' 	=> 1,
		'meta_key'		=> 'opt_out',
		'meta_value'		=> 'yes'
	);
	
	$invite = new Invite_Anyone_Invitation;
	
	$invite->get( $args );
	
	if ( have_posts() ) 
		return true;
	else
		return false;
}

/**
 * Mark all of an address's invitations as opt_out so that no others are sent
 *
 * @package Invite Anyone
 * @since {@internal Version Unknown}
 *
 * @param str $email The email address being checked
 */
function invite_anyone_mark_as_opt_out( $email ) {
	invite_anyone_get_invitations_by_invited_email( $email );
	
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			
			$invite = new Invite_Anyone_Invitation( get_the_ID() );
			$invite->mark_opt_out();
		}
	}
	
	return true;
}


?>