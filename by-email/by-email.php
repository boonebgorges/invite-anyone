<?php

require( WP_PLUGIN_DIR . '/invite-anyone/by-email/by-email-db.php' );
require( WP_PLUGIN_DIR . '/invite-anyone/widgets/widgets.php' );

function invite_anyone_add_by_email_css() {
	global $bp;
	
	if ( $bp->current_component == BP_INVITE_ANYONE_SLUG ) {
   		$style_url = WP_PLUGIN_URL . '/invite-anyone/by-email/by-email-css.css';
        $style_file = WP_PLUGIN_DIR . '/invite-anyone/by-email/by-email-css.css';
        if (file_exists($style_file)) {
            wp_register_style('invite-anyone-by-email-style', $style_url);
            wp_enqueue_style('invite-anyone-by-email-style');
        }
    }
}
add_action( 'wp_print_styles', 'invite_anyone_add_by_email_css' );

function invite_anyone_add_by_email_js() {
	global $bp;
	
	if ( $bp->current_component == BP_INVITE_ANYONE_SLUG ) {
   		$style_url = WP_PLUGIN_URL . '/invite-anyone/by-email/by-email-js.js';
        $style_file = WP_PLUGIN_DIR . '/invite-anyone/by-email/by-email-js.js';
        if (file_exists($style_file)) {
            wp_register_script('invite-anyone-by-email-scripts', $style_url);
            wp_enqueue_script('invite-anyone-by-email-scripts');
        }
    }
}
add_action( 'wp_print_scripts', 'invite_anyone_add_by_email_js' );

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


function invite_anyone_opt_out_screen() {
	global $bp;
	
	if ( $_POST['oops_submit'] ) {
		bp_core_redirect( site_url( BP_REGISTER_SLUG ) . '/accept-invitation/' . urlencode( $_POST['opt_out_email'] ) );
	}
	
	$opt_out_button_text = __('Opt Out', 'bp-invite-anyone');
	$oops_button_text =  __('Accept Invitation', 'bp-invite-anyone'); 
	
	$sitename = get_bloginfo('name');
	
	$opt_out_message = sprintf( __( 'To opt out of future invitations to %s, make sure that your email is entered in the field below and click "Opt Out".', 'bp-invite-anyone' ), $sitename );
	
	$oops_message = sprintf( __( 'If you are here by mistake and would like to accept your invitation to %s, click "Accept Invitation" instead.', 'bp-invite-anyone' ), $sitename );
	
	if ( $bp->current_component == BP_REGISTER_SLUG && $bp->current_action == 'opt-out' ) {
		get_header();
?>
		<div id="content">
		<div class="padder">
		<?php if ( $bp->action_variables[1] == 'submit' ) : ?>
			<?php if ( $_POST['opt_out_submit'] == $opt_out_button_text && $email = urldecode( $_POST['opt_out_email'] ) ) : ?>
			
				<?php check_admin_referer( 'invite_anyone_opt_out' ) ?>
			
				<?php if ( invite_anyone_mark_as_opt_out( $email ) ) : ?>
					<?php $opted_out_message = __( 'You have successfully opted out. No more invitation emails will be sent to you by this site.', 'bp-invite-anyone' ) ?>
					<p><?php echo $opted_out_message ?></p>
				<?php else : ?>
					<p><?php _e( 'Sorry, there was an error in processing your request', 'bp-invite-anyone' ) ?></p>
				<?php endif; ?>			
			<?php else : ?>
				<?php /* I guess this should be some sort of error message? */ ?>
			<?php endif; ?>
		
		<?php else : ?>
			<?php if ( $email = $bp->action_variables[0] ) : ?>
				<script type="text/javascript">
				jQuery(document).ready( function() {
					jQuery("input#opt_out_email").val("<?php echo urldecode($email) ?>");
				});
				</script> 
			<?php endif; ?>
			
			<form action="<?php echo $email ?>/submit" method="post">
				<p><?php echo $opt_out_message ?></p>
				
				<p><?php echo $oops_message ?></p>
				
				<?php wp_nonce_field( 'invite_anyone_opt_out' ) ?>
				<p><?php _e( 'Email:', 'bp-invite-anyone' ) ?> <input type="text" id="opt_out_email" name="opt_out_email" size="50" /></p>
				
				<p><input type="submit" name="opt_out_submit" value="<?php echo $opt_out_button_text ?>" /> <input type="submit" name="oops_submit" value="<?php echo $oops_button_text ?>" /> 
				</p>
				
			</form>
		<?php endif; ?>
		</div>
		</div>
<?php
		get_footer();
		die();
	
	}
}
add_action( 'wp', 'invite_anyone_opt_out_screen', 1 );


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
			if ( count( $inviters ) == 0 ) {
				$inviters_text = '';
			} else if ( count( $inviters ) == 1 ) {
				$inviters_text .= 'by ';
				$inviters_text .= bp_core_get_user_displayname( $inviters[0] );
			} else {
				$counter = 1;
				$inviters_text .= 'by ';
				$inviters_text .= bp_core_get_user_displayname( $inviters[0] );
				while ( $counter < count( $inviters ) - 1 ) {
					$inviters_text .= ', ' . bp_core_get_user_displayname( $inviters[$counter] );
					$counter++;
				}
				$inviters_text .= ' and ' . bp_core_get_user_displayname( $inviters[$counter] );
			}
					

		
			
			$message = sprintf( __( "Welcome! You've been invited %s to join the site. Please fill out the information below to create your account.", 'bp-invite-anyone' ), $inviters_text );
				
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
		
		/* This handles the email addresses sent back when there is an error */
		$returned_emails = array();
		$counter = 0;
		while ( $_GET['email' . $counter] ) {
			$returned_emails[] = urldecode( $_GET['email' . $counter] );
			$counter++;
		}
		
		/* If the user is coming from the widget, $returned_emails is populated with those email addresses */
		if ( $_POST['invite_anyone_widget'] ) {
			check_admin_referer( 'invite-anyone-widget_' . $bp->loggedin_user->id );
			
			if ( is_array( $_POST['emails'] ) ) {
				foreach( $_POST['emails'] as $email ) {
					if ( $email != '' && $email != __( 'email address', 'bp-invite-anyone' ) )
						$returned_emails[] = $email;	
				}
			}			
		}
		
		/* $returned_groups is padded so that array_search (below) returns true for first group */
		$returned_groups = array( 0 );
		$counter = 0;
		while ( $_GET['group' . $counter] ) {
			$returned_groups[] = urldecode( $_GET['group' . $counter] );
			$counter++;
		}
		
		if ( $_GET['subject'] )
			$returned_subject = stripslashes( urldecode( $_GET['subject'] ) );
		
		if ( $_GET['message'] )
			$returned_message = stripslashes( urldecode( $_GET['message'] ) );
				
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
					<textarea name="invite_anyone_custom_subject" id="invite-anyone-custom-subject"><?php echo invite_anyone_invitation_subject( $returned_subject ) ?></textarea>	
			<?php else : ?>
				<p><strong>Subject: </strong><?php echo invite_anyone_invitation_subject( $returned_subject ) ?></p>
				<input type="hidden" name="invite_anyone_custom_subject" value="<?php echo invite_anyone_invitation_subject() ?>" />
			<?php endif; ?>
		</li>
		
		<li>
			<?php if ( $iaoptions['message_is_customizable'] == 'yes' ) : ?>
				<p><?php _e( '(optional) Customize the text of the invitation.', 'bp-invite-anyone' ) ?></p>
					<textarea name="invite_anyone_custom_message" id="invite-anyone-custom-message"><?php echo invite_anyone_invitation_message( $returned_message ) ?></textarea>		
			<?php else : ?>
				<p><strong>Message: </strong><?php echo invite_anyone_invitation_message( $returned_message ) ?></p>
				<input type="hidden" name="invite_anyone_custom_message" value="<?php echo invite_anyone_invitation_message() ?>" />
			<?php endif; ?>
		
		</li>
		
		<?php if ( $iaoptions['can_send_group_invites_email'] == 'yes' && bp_has_groups( "type=alphabetical&user_id=" . bp_loggedin_user_id() ) ) : ?>
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
		
		<?php do_action( 'invite_anyone_addl_fields' ) ?>
		
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
		global $bp; 
		
		$inviter_id = bp_loggedin_user_id();
		
		if ( $clear_id = $_GET['clear'] ) {
			check_admin_referer( 'invite_anyone_clear' );
			
			if ( (int)$clear_id )
				invite_anyone_clear_sent_invite( array( 'inviter_id' => $inviter_id, 'clear_id' => $clear_id ) );
			else
				invite_anyone_clear_sent_invite( array( 'inviter_id' => $inviter_id, 'type' => $clear_id ) );
		}
		
		if ( !$sort_by = $_GET['sort_by'] )
			$sort_by = 'date_invited';
		
		if ( !$order = $_GET['order'] )
			$order = 'DESC';		
		
		$base_url = $bp->displayed_user->domain . $bp->invite_anyone->slug . '/sent-invites/';
		
		?>

		<h4><?php _e( 'Sent Invites', 'bp-invite-anyone' ) ?></h4>

		<?php if ( $invites = invite_anyone_get_invitations_by_inviter_id( bp_loggedin_user_id(), $sort_by, $order ) ) : ?>

		<p><?php _e( 'You have sent invitations to the following people.', 'bp-invite-anyone' ) ?></p>
		
		<table class="invite-anyone-sent-invites">
			<tr>
				<th scope="column"></th>
				<th scope="column"><a href="?sort_by=email&order=<?php if ( $_GET['sort_by'] == 'email' && $_GET['order'] == 'ASC' ) : ?>DESC<?php else : ?>ASC<?php endif; ?>"><?php _e( 'Invited email address', 'bp-invite-anyone' ) ?></a></th>
				<th scope="column"><?php _e( 'Group invitations', 'bp-invite-anyone' ) ?></th>
				<th scope="column"><a href="?sort_by=date_invited&order=<?php if ( $_GET['sort_by'] == 'date_invited' && $_GET['order'] == 'DESC' ) : ?>ASC<?php else : ?>DESC<?php endif; ?>"><?php _e( 'Sent', 'bp-invite-anyone' ) ?></a></th>
				<th scope="column"><a href="?sort_by=date_joined&order=<?php if ( $_GET['sort_by'] == 'date_joined' && $_GET['order'] == 'DESC' ) : ?>ASC<?php else : ?>DESC<?php endif; ?>"><?php _e( 'Accepted', 'bp-invite-anyone' ) ?></a></th>
				
			</tr>
			
			<?php foreach( $invites as $invite ) : ?>
			<?php
				$query_string = preg_replace( "|clear=[0-9]+|", '', $_SERVER['QUERY_STRING'] );
				
				$clear_url = ( $query_string ) ? $base_url . '?' . $query_string . '&clear=' . $invite->id : $base_url . '?clear=' . $invite->id;
				$clear_url = wp_nonce_url( $clear_url, 'invite_anyone_clear' );
				$clear_link = '<a class="confirm" alt="' . __( 'Clear this invitation', 'bp-invite-anyone' ) . '" href="' . $clear_url . '">x</a>';
				
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
				<td><?php echo $clear_link ?></td>
				<td><?php echo $invite->email ?></td>
				<td><?php echo $group_names ?></td>
				<td><?php echo $date_invited ?></td>
				<td><?php echo $date_joined ?></td>
			</tr>
			<?php endforeach; ?>
		</table>
		
		<p id="invite-anyone-clear-links">
			<a class="confirm" href="<?php echo wp_nonce_url( $base_url . '?clear=accepted', 'invite_anyone_clear' ) ?>"><?php _e( 'Clear all accepted invitations', 'bp-invite-anyone' ) ?></a> | 
			<a class="confirm" href="<?php echo wp_nonce_url( $base_url . '?clear=all', 'invite_anyone_clear' ) ?>"><?php _e( 'Clear all invitations', 'bp-invite-anyone' ) ?></a>
		</p>
		
		<?php else : ?>
		
		<p><?php _e( "You haven't sent any email invitations yet.", 'bp-invite-anyone' ) ?></p>
		
		<?php endif; ?>
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
	
	if ( count( $returned_emails  ) > $max_invites  )
		$max_invites = count( $returned_emails );
	
?>
	<ol id="invite-anyone-email-fields">
	<?php for( $i = 0; $i < $max_invites; $i++ ) : ?>
		<li>
			<input type="text" name="invite_anyone_email[]" class="invite-anyone-email-field" <?php if ( $returned_emails[$i] ) : ?>value="<?php echo $returned_emails[$i] ?>"<?php endif; ?>" />
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
			$text = sprintf( __( "An invitation to join the %s community.", 'bp-invite-anyone' ), $site_name );
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
		$blogname = get_bloginfo('name');
		
		if ( !$iaoptions = get_option( 'invite_anyone' ) )
			$iaoptions = array();
		
		if ( !$text = $iaoptions['default_invitation_message'] ) {
			$text = sprintf( __( "You have been invited by %%INVITERNAME%% to join the %s community. \n\r\n\rVisit %%INVITERNAME%%'s profile at %%INVITERURL%%.", 'bp-invite-anyone' ), $blogname ); /* Do not translate the strings embedded in %% ... %% ! */ 
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
	$text = str_replace( '%%INVITERURL%%', $inviter_url, $text );
	$text = str_replace( '%%SITENAME%%', $site_name, $text );
	
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
			
			case 'opt_out' :
				$error_message = __( "Sorry, $email has opted out of email invitations from this site.", 'bp-invite-anyone' );
				$is_error = 1;
				break;
			
			case 'used' :
				$error_message = __( "Sorry, $email is already a registered user of the site. ", 'bp-invite-anyone' );
				$is_error = 1;
				break;
				
			case 'unsafe' :
				$error_message = __( "Sorry, $email is not a permitted email address.", 'bp-invite-anyone' );
				$is_error = 1;
				break;
			
			case 'invalid' :
				$error_message = __( "Sorry, $email is not a valid email address. Please make sure that you have typed it correctly.", 'bp-invite-anyone' );
				$is_error = 1;
				break;
			
			case 'limited_domain' :
				$error_message = __( "Sorry, $email is not a permitted email address. Please make sure that you have typed the domain name correctly.", 'bp-invite-anyone' );
				$is_error = 1;
				break;	
		}
		
		if ( $is_error ) {
			$error_message .= " " . __( 'Please remove the email address and try again.', 'bp-invite-anyone' );
			bp_core_add_message( $error_message, 'error' );
		
			$d = '';
			if ( $emails ) {
				foreach ( $emails as $key => $email )
					$d .= "email$key=" . urlencode($email) . '&';
			}
		
			if ( $data['invite_anyone_groups'] ) {
				foreach ( $data['invite_anyone_groups'] as $key => $group )
					$d .= "group$key=" . $group . '&';
			}
			
			if ( $data['invite_anyone_custom_subject'] )
				$d .= 'subject=' . urlencode( stripslashes( $data['invite_anyone_custom_subject'] ) ) . '&';
		
			if ( $data['invite_anyone_custom_message'] )
				$d .= 'message=' . urlencode( stripslashes( $data['invite_anyone_custom_message'] ) );
				
			bp_core_redirect( $bp->loggedin_user->domain . $bp->invite_anyone->slug . '/invite-new-members?' . $d  );
		}		
	}
	
	/* send and record invitations */
	
	do_action( 'invite_anyone_process_addl_fields' );
	
	$groups = $data['invite_anyone_groups'];	
	$is_error = 0;
	
	foreach( $emails as $email ) {
		$subject = stripslashes( strip_tags( $data['invite_anyone_custom_subject'] ) );

		$message = stripslashes( strip_tags( $data['invite_anyone_custom_message'] ) );
				
		$accept_link =  site_url( BP_REGISTER_SLUG ) . '/accept-invitation/' . urlencode($email);
		
		$opt_out_link = site_url( BP_REGISTER_SLUG ) . '/opt-out/' . urlencode( $email );
	
		$message .= sprintf( __( '

To accept this invitation, please visit %s', 'bp-invite-anyone' ), $accept_link );
		
		$message .= sprintf( __( '

To opt out of future invitations to this site, please visit %s', 'bp-invite-anyone' ), $opt_out_link );
		
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
	
	if ( invite_anyone_check_is_opt_out( $user_email ) )
		return 'opt_out';

	if ( $user = get_user_by_email( $user_email ) )
		return 'used';

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