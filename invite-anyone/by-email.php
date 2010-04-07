<?php

/* Todo:
	- opt out link
	- reply-to address
		- send $headers to wp_mail
		- set admin option to allow custom reply-to
	- auto-populate email field
	- on invitee join:
		- notifications to inviter(s) that individual has joined
	- admin functions:
		- toggle group link
		- toggle group invitations: standard vs all members
	- email verification - email_exists. If found, give link to the profile (w friend link?)
	- js for inline validation
	- hook into member lists and searches with "not finding?" message
	- Invite Anyone widget
*/

require( dirname(__FILE__) . '/db.php' );

function invite_anyone_setup_globals() {
	global $bp, $wpdb;

	$bp->invite_anyone->id = 'invite_anyone';

	$bp->invite_anyone->table_name = $wpdb->base_prefix . 'bp_invite_anyone';
	$bp->invite_anyone->slug = 'invite-anyone';

	/* Register this in the active components array */
	$bp->active_components[$bp->invite_anyone->slug] = $bp->invite_anyone->id;
}
add_action( 'wp', 'invite_anyone_setup_globals', 2 );
add_action( 'admin_menu', 'invite_anyone_setup_globals', 2 );



function invite_anyone_register_screen_message() {
	global $bp;
?>
	<?php if ( $bp->current_action == 'accept-invitation' && !$bp->action_variables[0] ) : ?>
		<div id="message" class="error"><p><?php _e( "It looks like you're trying to accept an invitation to join the site, but some information is missing. Please try again by clicking on the link in the invitation email.", 'bp-invite-anyone' ) ?></p></div>
	<?php endif; ?>
	
	
	<?php if ( $bp->current_action == 'accept-invitation' && $email = urldecode( $bp->action_variables[0] ) ) : ?>
	
	<script type="text/javascript">
	jQuery(document).ready( function() {
		jQuery("input#signup_email").val("<?php echo $email ?>");
	});
	
	</script>
	
	
		<?php 			
			$invites = invite_anyone_get_invitations_by_invited_email( $email );
			$inviters = array();
			foreach ( $invites as $invite ) {
				if ( !in_array( $invite->inviter_id, $inviters ) )
					$inviters[] = $invite->inviter_id;
			}
			
			$inviters_text = '';
			if ( count( $inviters ) == 1 ) {
				$inviters_text .= bp_core_get_user_displayname( $inviters[0] );
			} else {
				$counter = 1;
				$inviters_text .= bp_core_get_user_displayname( $inviters[0] );
				while ( $counter < count( $inviters ) - 1 ) {
					$inviters_text .= ', ' . bp_core_get_user_displayname( $inviters[$counter] );
					$counter++;
				}
				$inviters_text .= ' and ' . bp_core_get_user_displayname( $inviters[$counter] );
			}
					

/* Todo: make an error happen when the email address in action_variables isn't real */
		
			
			$message = sprintf( __( "Welcome! You've been invited by %s to join the site. Please fill out the information below to create your account.", 'bp-invite-anyone' ), $inviters_text );
				
		?>
		<div id="message" class="success"><p><?php echo $message ?></p></div>	
	<?php endif; ?>
<?php
}
add_action( 'bp_before_register_page', 'invite_anyone_register_screen_message' );


function invite_anyone_activate_user( $user_id, $key, $user ) {
	global $bp;
	
	$email = bp_core_get_user_email( $user_id );

	if ( $invites = invite_anyone_get_invitations_by_invited_email( $email ) ) {
		/* Mark as "is_joined" */
		invite_anyone_mark_as_joined( $email );

		/* Friendship requests */
		$inviters = array();
		foreach ( $invites as $invite ) {
			if ( !in_array( $invite->inviter_id, $inviters ) )
				$inviters[] = $invite->inviter_id;
		}
	
		foreach ( $inviters as $inviter ) {		
			friends_add_friend( $inviter, $user_id );
		}
			
		/* Group invitations */
		$groups = array();
		foreach ( $invites as $invite ) {
			if ( !$invite->group_invitations[0] )
				continue;
			else
				$group_invitations = unserialize( $invite->group_invitations );
			
			foreach ( $group_invitations as $group ) {
				if ( !in_array( $group, array_keys($groups) ) )
					$groups[$group] = $invite->inviter_id;
			}
		}


		foreach ( $groups as $group_id => $inviter_id ) {
			$args = array(
				'user_id' => $user_id,
				'group_id' => $group_id,
				'inviter_id' => $inviter_id
			);
			
			groups_invite_user( $args );
			groups_send_invites( $inviter_id, $group_id );
		}			
	}
}
add_action( 'bp_core_activated_user', 'invite_anyone_activate_user', 10, 3 );

function invite_anyone_setup_nav() {
	global $bp;
	
	/* Add 'Send Invites' to the main user profile navigation */
	bp_core_new_nav_item( array(
		'name' => __( 'Send Invites', 'buddypress' ),
		'slug' => $bp->invite_anyone->slug,
		'position' => 80,
		'screen_function' => 'invite_anyone_screen_one',
		'default_subnav_slug' => 'invite-new-members',
		'show_for_displayed_user' => invite_anyone_access_test()
	) );

	$invite_anyone_link = $bp->loggedin_user->domain . $bp->invite_anyone->slug . '/';

	/* Create two sub nav items for this component */
	bp_core_new_subnav_item( array(
		'name' => __( 'Invite New Members', 'bp-invite-anyone' ),
		'slug' => 'invite-new-members',
		'parent_slug' => $bp->invite_anyone->slug,
		'parent_url' => $invite_anyone_link,
		'screen_function' => 'invite_anyone_screen_one',
		'position' => 10,
		'user_has_access' => invite_anyone_access_test()
	) );

	bp_core_new_subnav_item( array(
		'name' => __( 'Sent Invites', 'bp-invite-anyone' ),
		'slug' => 'sent-invites',
		'parent_slug' => $bp->invite_anyone->slug,
		'parent_url' => $invite_anyone_link,
		'screen_function' => 'invite_anyone_screen_two',
		'position' => 20,
		'user_has_access' => invite_anyone_access_test()
	) );
}

if ( invite_anyone_access_test() ) {
	add_action( 'wp', 'invite_anyone_setup_nav', 2 );
	add_action( 'admin_menu', 'invite_anyone_setup_nav', 2 );
}

function invite_anyone_access_test() {
	global $current_user, $bp;

	if ( !is_user_logged_in() )
		return false;
		
	if ( $bp->displayed_user->id && !bp_is_my_profile() )
		return false;
	
	if ( !$iaoptions = get_option( 'invite_anyone' ) )
		$iaoptions = array();
	
	/* This is the last of the general checks: logged in, looking at own profile, and finally admin has set to "All Users".*/
	if ( $iaoptions['email_visibility_toggle'] == 'no_limit' )
		return true;
	
	/* Minimum number of days since joined the site */
	if ( $iaoptions['email_since_toggle'] == 'yes' ) {
		if ( $since = $iaoptions['days_since'] ) {
			$since = $since * 86400;

			$date_registered = strtotime($current_user->data->user_registered);
			$time = time();
			
			if ( $time - $date_registered < $since )
				return false;
		}
	}
	
	/* Minimum role on this blog. Users who are at the necessary role or higher should move right through this toward the 'return true' at the end of the function. */
	if ( $iaoptions['email_role_toggle'] == 'yes' ) {
		if ( $role = $iaoptions['minimum_role'] ) {
			switch ( $role ) {
				case 'Subscriber' :
					if ( !current_user_can( 'read' ) )
						return false;
					break;
				
				case 'Contributor' :
					if ( !current_user_can( 'edit_posts' ) )
						return false;
					break;
				
				case 'Author' :
					if ( !current_user_can( 'publish_posts' ) )
						return false;
					break;
				
				case 'Editor' :
					if ( !current_user_can( 'delete_others_pages' ) )
						return false;
					break;
			}
		}
	}
	
	/* User blacklist */
	if ( $iaoptions['email_blacklist_toggle'] == 'yes' ) {
		if ( $blacklist = $iaoptions['email_blacklist'] ) {
			$blacklist = explode( ",", $blacklist );
			$user_id = $current_user->ID;
			if ( in_array( $user_id, $blacklist ) )
				return false;			
		}
	}
	
	/* Todo: flesh this out. User blacklist; minimum role */
	
	return true;
		
}
add_action( 'wp_head', 'invite_anyone_access_test' );



function invite_anyone_screen_one() {
	global $bp;

	/*
	print "<pre>";
	print_r($bp);
	*/
	
	/* Add a do action here, so your component can be extended by others. */
	do_action( 'invite_anyone_screen_one' );

	add_action( 'bp_template_content', 'invite_anyone_screen_one_content' );

	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

function invite_anyone_screen_one_content() {
		global $bp;
			
		if ( !$iaoptions = get_option( 'invite_anyone' ) )
			$iaoptions = array();
			
		if ( !$max_invites = $iaoptions['max_invites'] )
			$max_invites = 5;
		
		if ( 'group-invites' == $bp->action_variables[0] )
			$from_group = $bp->action_variables[1];
		
		/* Grabs any information previously entered but returned because of an error */
		$returned_emails = array();
		$counter = 0;
		while ( $_GET['email' . $counter] ) {
			$returned_emails[] = urldecode( $_GET['email' . $counter] );
			$counter++;
		}
		
		// $returned_groups is padded so that array_search (below) returns true for first group
		$returned_groups = array( 0 );
		$counter = 0;
		while ( $_GET['group' . $counter] ) {
			$returned_groups[] = urldecode( $_GET['group' . $counter] );
			$counter++;
		}
		
		
		if ( $_GET['subject'] )
			$returned_subject = urldecode( $_GET['subject'] );
		
		if ( $_GET['message'] )
			$returned_message = urldecode( $_GET['message'] );
				
		$blogname = get_bloginfo('name');
		$welcome_message = sprintf( __( "Invite friends to join %s by following these steps:", 'bp-invite-anyone' ), $blogname );
	?>
	<form action="<?php echo $bp->displayed_user->domain . $bp->invite_anyone->slug . '/sent-invites/send/' ?>" method="post">
	
	<ol id="invite-anyone-steps">
		<h4><?php _e( 'Invite New Members', 'bp-invite-anyone' ) ?></h4>
		<p><?php echo $welcome_message ?></p>
		
		<li>
			<p><?php _e( 'Enter email addresses in the fields below.', 'bp-invite-anyone' ) ?> <?php if( invite_anyone_allowed_domains() ) : ?> <?php _e( 'You can only invite people whose email addresses end in one of the following domains:', 'bp-invite-anyone' ) ?> <?php echo invite_anyone_allowed_domains(); ?><?php endif; ?></p>
		</li>
		
		<?php invite_anyone_email_fields( $returned_emails ) ?>
		
		<li>
			<?php if ( $iaoptions['subject_is_customizable'] == 'yes' ) : ?>
				<p><?php _e( '(optional) Customize the subject line of the invitation email.', 'bp-invite-anyone' ) ?></p>
					<textarea rows="2" cols="60" name="invite_anyone_custom_subject" id="invite-anyone-custom-subject"><?php echo invite_anyone_invitation_subject( $returned_subject ) ?></textarea>	
			<?php else : ?>
				<p><strong>Subject: </strong><?php echo invite_anyone_invitation_subject( $returned_message ) ?></p>
				<input type="hidden" name="invite_anyone_custom_subject" value="<?php echo invite_anyone_invitation_subject() ?>" />
			<?php endif; ?>
		</li>
		
		<li>
			<?php if ( $iaoptions['message_is_customizable'] == 'yes' ) : ?>
				<p><?php _e( '(optional) Customize the text of the invitation.', 'bp-invite-anyone' ) ?></p>
					<textarea rows="7" cols="60" name="invite_anyone_custom_message" id="invite-anyone-custom-message"><?php echo invite_anyone_invitation_message( $returned_message ) ?></textarea>		
			<?php else : ?>
				<p><strong>Message: </strong><?php echo invite_anyone_invitation_message( $returned_message ) ?></p>
				<input type="hidden" name="invite_anyone_custom_message" value="<?php echo invite_anyone_invitation_message() ?>" />
			<?php endif; ?>
		
		</li>
		
		<?php if ( bp_has_groups( "type=alphabetical&user_id=" . bp_loggedin_user_id() ) ) : ?>
		<li>
			<p><?php _e( '(optional) Select some groups. Invitees will receive invitations to these groups when they join the site.', 'bp-invite-anyone' ) ?></p>
			<ul id="invite-anyone-group-list">
				<?php while ( bp_groups() ) : bp_the_group(); ?>
					<li>
					<input type="checkbox" name="invite_anyone_groups[]" id="invite_anyone_groups[]" value="<?php bp_group_id() ?>" <?php if ( $from_group == bp_get_group_id() || array_search( bp_get_group_id(), $returned_groups) ) : ?>checked<?php endif; ?> />
					<?php bp_group_avatar_mini() ?>
					<?php bp_group_name() ?>

					</li>
				<?php endwhile; ?>
			
			</ul>
		
		</li>
		<?php endif; ?>
		
	</ol>
		
	<div class="submit">
		<input type="submit" name="invite-anyone-submit" id="invite-anyone-submit" value="<?php _e( 'Send Invites', 'buddypress' ) ?> " />
	</div>
	
	
	</form>
	<?php
	}

/**
 * invite_anyone_screen_two()
 *
 */
function invite_anyone_screen_two() {
	global $bp;
	
	
	/* Todo: "Are you sure" page after "Send Invites" */
	if ( $bp->current_component == $bp->invite_anyone->slug && $bp->current_action == 'sent-invites' && $bp->action_variables[0] == 'send' ) {
		if ( invite_anyone_process_invitations( $_POST ) )
			bp_core_add_message( __( 'Your invitations were sent successfully!', 'bp-invite-anyone' ), 'success' );
		else
			bp_core_add_message( __( 'Sorry, there was a problem sending your invitations. Please try again.', 'bp-invite-anyone' ), 'error' );
	}
	
	do_action( 'invite_anyone_sent_invites_screen' );

	add_action( 'bp_template_content', 'invite_anyone_screen_two_content' );

	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}


	function invite_anyone_screen_two_content() {
		global $bp; ?>

		<h4><?php _e( 'Sent Invites', 'bp-invite-anyone' ) ?></h4>

		<p><?php _e( 'You have sent invitations to the following people.', 'bp-invite-anyone' ) ?></p>
		
		<?php $invites = invite_anyone_get_invitations_by_inviter_id( bp_loggedin_user_id() ) ?>
		
		<table class="invite-anyone-sent-invites">
			<tr>
				<th scope="column"><?php _e( 'Invited email address', 'bp-invite-anyone' ) ?></th>
				<th scope="column"><?php _e( 'Group invitations', 'bp-invite-anyone' ) ?></th>
				<th scope="column"><?php _e( 'Sent', 'bp-invite-anyone' ) ?></th>
				<th scope="column"><?php _e( 'Accepted', 'bp-invite-anyone' ) ?></th>
			</tr>
			
			<?php foreach( $invites as $invite ) : ?>
			<?php
				if ( $invite->group_invitations ) {
					$groups = unserialize( $invite->group_invitations );
					$group_names = '<ul>';
					foreach( $groups as $group_id ) {
						$group = new BP_Groups_Group( $group_id );
						$group_names .= '<li>' . bp_get_group_name( $group ) . '</li>';
					}
					$group_names .= '</ul>';
				} else {
					$group_names = '-';
				}
			
				$date_invited = invite_anyone_format_date( $invite->date_invited );
			
				if ( $invite->date_joined )
					$date_joined = invite_anyone_format_date( $invite->date_joined );
				else
					$date_joined = '-';
			?>
			
			<tr>
				<td><?php echo $invite->email ?></td>
				<td><?php echo $group_names ?></td>
				<td><?php echo $date_invited ?></td>
				<td><?php echo $date_joined ?></td>
			</tr>
			<?php endforeach; ?>
			
		
		</table>
		
	<?php
	}

/**
 * invite_anyone_email_fields()
 *
 */
function invite_anyone_email_fields( $returned_emails = false ) {
	if ( !$iaoptions = get_option( 'invite_anyone' ) )
		$iaoptions = array();
		
	if ( !$max_invites = $iaoptions['max_invites'] )
		$max_invites = 5;
	
?>
	<ol id="invite-anyone-email-fields">
	<?php for( $i = 0; $i < $max_invites; $i++ ) : ?>
		<li>
			<input type="text" name="invite_anyone_email[]" class="invite-anyone-email-field" size="30" <?php if ( $returned_emails[$i] ) : ?>value="<?php echo $returned_emails[$i] ?>"<?php endif; ?>" />
		</li>
	<?php endfor; ?>
	</ol>
<?php
}


function invite_anyone_invitation_subject( $returned_message = false ) {
	global $bp;
	
	if ( !$returned_message ) {
		$site_name = get_bloginfo('name');
		
		if ( !$iaoptions = get_option( 'invite_anyone' ) )
			$iaoptions = array();
		
		if ( !$text = $iaoptions['default_invitation_subject'] ) {
			$text = __( "An invitation to join the %%SITENAME%% community.", 'bp-invite-anyone' ); /* Do not translate the string %%SITENAME%%! */ 
		}
		
		if ( !is_admin() ) {
			$text = invite_anyone_wildcard_replace( $text );
		}
	} else {
		$text = $returned_message;	
	}
	
	return $text;
}

function invite_anyone_invitation_message( $returned_message = false ) {
	global $bp;
	
	if ( !$returned_message ) {
		$inviter_name = $bp->loggedin_user->userdata->display_name;
		$site_name = get_bloginfo('name');
		
		if ( !$iaoptions = get_option( 'invite_anyone' ) )
			$iaoptions = array();
		
		if ( !$text = $iaoptions['default_invitation_message'] ) {
			$text = __( "You have been invited by %%INVITERNAME%% to join the %%SITENAME%% community. \n\r\n\rVisit %%INVITERNAME%%'s profile at %%INVITERURL%%.", 'bp-invite-anyone' ); /* Do not translate the strings embedded in %% ... %% ! */ 
		}
		
		if ( !is_admin() ) {
			$text = invite_anyone_wildcard_replace( $text );
		}
	} else {
		$text = $returned_message;	
	}
	
	return $text;
}

function invite_anyone_wildcard_replace( $text ) {
	global $bp;
	
	$inviter_name = $bp->loggedin_user->userdata->display_name;
	$site_name = get_bloginfo('name');
	$inviter_url = bp_loggedin_user_domain();
	
	$text = str_replace( '%%INVITERNAME%%', $inviter_name, $text );
	$text = str_replace( '%%SITENAME%%', $site_name, $text );
	$text = str_replace( '%%INVITERURL%%', $inviter_url, $text );
	
	return $text;
}

function invite_anyone_allowed_domains() {
	
	$domains = '';
	
	if ( function_exists( 'get_site_option' ) ) {
		$limited_email_domains = get_site_option( 'limited_email_domains' );
		
		if ( !$limited_email_domains || !is_array( $limited_email_domains ) )
			return $domains;
		
		foreach( $limited_email_domains as $domain )
			$domains .= "<strong>$domain</strong> ";
	}
	
	return $domains;
}


function invite_anyone_format_date( $date ) {
	$thetime = strtotime( $date );	
	$format = get_option('date_format'); 
	$thetime = date( "$format", $thetime );
	return $thetime;
}

function invite_anyone_process_invitations( $data ) {
	global $bp;
	
	$emails = array();
	foreach ( $data['invite_anyone_email'] as $email ) {
		if ( $email != '' )
			$emails[] = $email;
	}
	
	if ( empty($emails) ) {
		bp_core_add_message( __( "You didn't include any email addresses!", 'bp-invite-anyone' ), 'error' );
		bp_core_redirect( $bp->loggedin_user->domain . $bp->invite_anyone->slug . '/invite-new-members' );
	}
	
	/* validate email addresses */
	foreach( $emails as $email ) {
		$check = invite_anyone_validate_email( $email );
		switch ( $check ) {
			case 'unsafe' :
				bp_core_add_message( __("Sorry, $email is not a permitted email address.", 'bp-invite-anyone' ), 'error' );
				$is_error = 1;
				break;
			
			case 'invalid' :
				bp_core_add_message( __("Sorry, $email is not a valid email address. Please make sure that you have typed it correctly.", 'bp-invite-anyone' ), 'error' );
				$is_error = 1;
				break;
			
			case 'limited_domain' :
				bp_core_add_message( __( "Sorry, $email is not a permitted email address. Please make sure that you have typed the domain name correctly.", 'bp-invite-anyone' ), 'error');
				$is_error = 1;
				break;
			
			case 'used' :
				bp_core_add_message( __( "$email is already a registered user of this site.", 'bp-invite_anyone'), 'error');
				$is_error = 1;
				break;		
		}
		
		if ( $is_error ) {
			$d = '';
			foreach ( $emails as $key => $email )
				$d .= "email$key=" . urlencode($email) . '&';
		
			foreach ( $data['invite_anyone_groups'] as $key => $group )
				$d .= "group$key=" . $group . '&';
			
			if ( $data['invite_anyone_custom_subject'] )
				$d .= 'subject=' . urlencode($data['invite_anyone_custom_subject']);
		
			$d .= 'message=' . urlencode($data['invite_anyone_custom_message']);
				
			bp_core_redirect( $bp->loggedin_user->domain . $bp->invite_anyone->slug . '/invite-new-members?' . $d  );
		}		
	}
	
	/* send and record invitations */
	
	$groups = $data['invite_anyone_groups'];	
	$is_error = 0;
	
	foreach( $emails as $email ) {
		$subject = stripslashes( strip_tags( $data['invite_anyone_custom_subject'] ) );

		$message = stripslashes( strip_tags( $data['invite_anyone_custom_message'] ) );
				
		$accept_link =  site_url( BP_REGISTER_SLUG ) . '/accept-invitation/' . urlencode($email);
		
		$message .= sprintf( __( '

To accept this invitation, please visit %s', 'bp-invite-anyone' ), $accept_link );
		
		$to = apply_filters( 'invite_anyone_invitee_email', $email );
		$subject = apply_filters( 'invite_anyone_invitation_subject', $subject );
		$message = apply_filters( 'invite_anyone_invitation_message', $message, $accept_link );
				
		wp_mail( $to, $subject, $message );
			
		/* todo: isolate which email(s) cause problems, and send back to user */
	/*	if ( !invite_anyone_send_invitation( $bp->loggedin_user->id, $email, $message, $groups ) )
			$is_error = 1; */
		
		invite_anyone_record_invitation( $bp->loggedin_user->id, $email, $message, $groups );
		
		unset( $message, $to );
	}
	
	
	return true;
}



function invite_anyone_send_invitation( $inviter_id, $email, $message, $groups ) {
	global $bp;

}


function invite_anyone_validate_email( $user_email ) {
	
	//if ( email_exists($user_email) )
	//	return 'used';
	
	// Many of he following checks can only be run on WPMU
	if ( function_exists( 'is_email_address_unsafe' ) ) {
		if ( is_email_address_unsafe( $user_email ) )
			return 'unsafe';
	}
		
	if ( function_exists( 'validate_email' ) ) {
		if ( !validate_email( $user_email ) )
			return 'invalid';
	}
	
	if ( function_exists( 'get_site_option' ) ) {
		if ( $limited_email_domains = get_site_option( 'limited_email_domains' ) ) {
			if ( is_array( $limited_email_domains ) && empty( $limited_email_domains ) == false ) {
				$emaildomain = substr( $user_email, 1 + strpos( $user_email, '@' ) );
				if( in_array( $emaildomain, $limited_email_domains ) == false ) {
					return 'limited_domain';
				}
			}
		}
	}
	
	return 'safe';
}

?>