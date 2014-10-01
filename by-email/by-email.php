<?php

require( BP_INVITE_ANYONE_DIR . 'by-email/by-email-db.php' );
require( BP_INVITE_ANYONE_DIR . 'widgets/widgets.php' );
require( BP_INVITE_ANYONE_DIR . 'by-email/cloudsponge-integration.php' );

// Temporary function until bp_is_active is fully integrated
function invite_anyone_are_groups_running() {
	if ( function_exists( 'groups_install' ) )
		return true;

	if ( function_exists( 'bp_is_active' ) ) {
		if ( bp_is_active( 'groups' ) )
			return true;
	}

	return false;
}

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

	if ( !isset( $bp->invite_anyone ) ) {
		$bp->invite_anyone = new stdClass;
	}

	$bp->invite_anyone->id = 'invite_anyone';

	$bp->invite_anyone->table_name = $wpdb->base_prefix . 'bp_invite_anyone';
	$bp->invite_anyone->slug = 'invite-anyone';

	/* Register this in the active components array */
	$bp->active_components[$bp->invite_anyone->slug] = $bp->invite_anyone->id;
}
add_action( 'bp_setup_globals', 'invite_anyone_setup_globals', 2 );


function invite_anyone_opt_out_screen() {
	global $bp;

	if ( isset( $_POST['oops_submit'] ) ) {
		$oops_email = urlencode( stripslashes( $_POST['opt_out_email'] ) );
		$opt_out_link = add_query_arg( array(
			'iaaction' => 'accept-invitation',
			'email'    => $oops_email,
		), bp_get_root_domain() . '/' . bp_get_signup_slug() . '/' );
		bp_core_redirect( $opt_out_link );
	}

	$opt_out_button_text 	= __( 'Opt Out', 'bp-invite-anyone' );
	$oops_button_text 	= __( 'Accept Invitation', 'bp-invite-anyone' );

	$sitename 		= get_bloginfo( 'name' );

	$opt_out_message 	= sprintf( __( 'To opt out of future invitations to %s, make sure that your email is entered in the field below and click "Opt Out".', 'bp-invite-anyone' ), $sitename );

	$oops_message 		= sprintf( __( 'If you are here by mistake and would like to accept your invitation to %s, click "Accept Invitation" instead.', 'bp-invite-anyone' ), $sitename );

	if ( bp_is_register_page() && isset( $_GET['iaaction'] ) && 'opt-out' === urldecode( $_GET['iaaction'] ) ) {
		get_header();
?>
		<div id="content">
		<div class="padder">
		<?php if ( ! empty( $_POST['opt_out_submit'] ) ) : ?>
			<?php if ( $_POST['opt_out_submit'] == $opt_out_button_text && $email = urldecode( $_POST['opt_out_email'] ) ) : ?>
				<?php $email = str_replace( ' ', '+', $email ) ?>

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
			<?php if ( isset( $_GET['email'] ) && $email = $_GET['email'] ) : ?>
				<script type="text/javascript">
				jQuery(document).ready( function() {
					jQuery("input#opt_out_email").val("<?php echo str_replace( ' ', '+', urldecode( $email ) ) ?>");
				});
				</script>
			<?php endif; ?>

			<form action="" method="post">

				<?php do_action( 'invite_anyone_before_optout_messages' ) ?>

				<p><?php echo $opt_out_message ?></p>

				<p><?php echo $oops_message ?></p>

				<?php do_action( 'invite_anyone_after_optout_messages' ) ?>

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

	if ( ! invite_anyone_is_accept_invitation_page() ) {
		return;
	}

	if ( isset( $_GET['email'] ) ) {
		$email = urldecode( $_GET['email'] );
	} else {
		$email = '';
	}

?>
	<?php if ( empty( $email ) ) : ?>
		<div id="message" class="error"><p><?php _e( "It looks like you're trying to accept an invitation to join the site, but some information is missing. Please try again by clicking on the link in the invitation email.", 'bp-invite-anyone' ) ?></p></div>
	<?php endif; ?>

	<?php if ( $bp->signup->step == 'request-details' && ! empty( $email ) ) : ?>

		<?php do_action( 'accept_email_invite_before' ) ?>

		<script type="text/javascript">
		jQuery(document).ready( function() {
			jQuery("input#signup_email").val("<?php echo str_replace( ' ', '+', $email ) ?>");
		});

		</script>


		<?php
			$ia_obj = invite_anyone_get_invitations_by_invited_email( $email );

			$inviters = array();
			if ( $ia_obj->have_posts() ) {
				while ( $ia_obj->have_posts() ) {
					$ia_obj->the_post();
					$inviters[] = get_the_author_meta( 'ID' );
				}
			}
			$inviters = array_unique( $inviters );

			$inviters_names = array();
			foreach ( $inviters as $inviter ) {
				$inviters_names[] = bp_core_get_user_displayname( $inviter );
			}

			if ( ! empty( $inviters_names ) ) {
				$message = sprintf( _n( 'Welcome! You&#8217;ve been invited to join the site by the following user: %s. Please fill out the information below to create your account.', 'Welcome! You&#8217;ve been invited to join the site by the following users: %s. Please fill out the information below to create your account.', count( $inviters_names ), 'bp-invite-anyone' ), implode( ', ', $inviters_names ) );
			} else {
				$message = __( 'Welcome! You&#8217;ve been invited to join the site. Please fill out the information below to create your account.', 'bp-invite-anyone' );
			}

			echo '<div id="message" class="success"><p>' . esc_html( $message ) . '</p></div>';

		?>

	<?php endif; ?>
<?php
}
add_action( 'bp_before_register_page', 'invite_anyone_register_screen_message' );


function invite_anyone_activate_user( $user_id, $key, $user ) {
	global $bp;

	$email = bp_core_get_user_email( $user_id );

	$inviters 	= array();

	// Fire the query
	$invites = invite_anyone_get_invitations_by_invited_email( $email );

	if ( $invites->have_posts() ) {
		// From the posts returned by the query, get a list of unique inviters
		$groups		= array();
		while ( $invites->have_posts() ) {
			$invites->the_post();

			$inviter_id	= get_the_author_meta( 'ID' );
			$inviters[] 	= $inviter_id;

			$groups_data	= wp_get_post_terms( get_the_ID(), invite_anyone_get_invited_groups_tax_name() );
			foreach ( $groups_data as $group_data ) {
				if ( !isset( $groups[$group_data->name] ) ) {
					// Keyed by inviter, which means they'll only get one invite per group
					$groups[$group_data->name] = $inviter_id;
				}
			}

			// Mark as accepted
			update_post_meta( get_the_ID(), 'bp_ia_accepted', date( 'Y-m-d H:i:s' ) );
		}

		$inviters 	= array_unique( $inviters );

		// Friendship requests
		if ( bp_is_active( 'friends' ) && apply_filters( 'invite_anyone_send_friend_requests_on_acceptance', true ) ) {
			if ( function_exists( 'friends_add_friend' ) ) {
				foreach ( $inviters as $inviter ) {
					friends_add_friend( $inviter, $user_id );
				}
			}
		}

		// BuddyPress Followers support
		if ( function_exists( 'bp_follow_start_following' ) && apply_filters( 'invite_anyone_send_follow_requests_on_acceptance', true ) ) {
			foreach ( $inviters as $inviter ) {
				bp_follow_start_following( array( 'leader_id' => $user_id, 'follower_id' => $inviter ) );
				bp_follow_start_following( array( 'leader_id' => $inviter, 'follower_id' => $user_id ) );
			}
		}

		// Group invitations
		if ( bp_is_active( 'groups' ) ) {
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

	do_action( 'accepted_email_invite', $user_id, $inviters );
}
add_action( 'bp_core_activated_user', 'invite_anyone_activate_user', 10, 3 );

function invite_anyone_setup_nav() {
	global $bp;

	if ( !invite_anyone_access_test() )
		return;

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
add_action( 'bp_setup_nav', 'invite_anyone_setup_nav' );

function invite_anyone_access_test() {
	global $current_user, $bp;

	if ( !is_user_logged_in() )
		return false;

	// The site admin can see all
	if ( current_user_can( 'bp_moderate' ) ) {
		return true;
	}

	if ( bp_displayed_user_id() && !bp_is_my_profile() )
		return false;

	$iaoptions = invite_anyone_options();

	/* This is the last of the general checks: logged in, looking at own profile, and finally admin has set to "All Users".*/
	if ( isset( $iaoptions['email_visibility_toggle'] ) && $iaoptions['email_visibility_toggle'] == 'no_limit' )
		return true;

	/* Minimum number of days since joined the site */
	if ( isset( $iaoptions['email_since_toggle'] ) && $iaoptions['email_since_toggle'] == 'yes' ) {
		if ( isset( $iaoptions['days_since'] ) && $since = $iaoptions['days_since'] ) {
			$since = $since * 86400;

			$date_registered = strtotime($current_user->data->user_registered);
			$time = time();

			if ( $time - $date_registered < $since )
				return false;
		}
	}

	/* Minimum role on this blog. Users who are at the necessary role or higher should move right through this toward the 'return true' at the end of the function. */
	if ( isset( $iaoptions['email_role_toggle'] ) && $iaoptions['email_role_toggle'] == 'yes' ) {
		if ( isset( $iaoptions['minimum_role'] ) && $role = $iaoptions['minimum_role'] ) {
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

				case 'Administrator' :
					if ( !current_user_can( 'switch_themes' ) )
						return false;
					break;
			}
		}
	}

	/* User blacklist */
	if ( isset( $iaoptions['email_blacklist_toggle'] ) && $iaoptions['email_blacklist_toggle'] == 'yes' ) {
		if ( isset( $iaoptions['email_blacklist'] ) ) {
			$blacklist = explode( ",", $iaoptions['email_blacklist'] );
			$user_id = $current_user->ID;
			if ( in_array( $user_id, $blacklist ) )
				return false;
		}
	}

	return true;

}
add_action( 'wp_head', 'invite_anyone_access_test' );

/**
 * Catch and process email sends.
 *
 * @since 1.1.0
 */
function invite_anyone_catch_send() {
	global $bp;

	if ( ! bp_is_current_component( $bp->invite_anyone->slug ) ) {
		return;
	}

	if ( ! bp_is_current_action( 'sent-invites' ) ) {
		return;
	}

	if ( ! bp_is_action_variable( 'send', 0 ) ) {
		return;
	}

	if ( ! invite_anyone_process_invitations( $_POST ) ) {
		bp_core_add_message( __( 'Sorry, there was a problem sending your invitations. Please try again.', 'bp-invite-anyone' ), 'error' );
	}

	bp_core_redirect( bp_displayed_user_domain() . $bp->invite_anyone->slug . '/sent-invites' );
}
add_action( 'wp', 'invite_anyone_catch_send' );

function invite_anyone_catch_clear() {
	global $bp;

	$returned_data = isset( $_COOKIE['invite-anyone'] ) ? unserialize( stripslashes( $_COOKIE['invite-anyone'] ) ) : '';
	if ( $returned_data ) {
		// We'll take a moment nice and early in the loading process to get returned_data
		$keys = array(
			'error_message',
			'error_emails',
			'subject',
			'message',
			'groups',
		);

		foreach ( $keys as $key ) {
			$bp->invite_anyone->returned_data[ $key ] = null;
			if ( isset( $returned_data[ $key ] ) ) {
				$value = stripslashes_deep( $returned_data[ $key ] );
				$bp->invite_anyone->returned_data[ $key ] = $value;
			}
		}
	}
	@setcookie( 'invite-anyone', '', time() - 3600, '/' );

	if ( isset( $_GET['clear'] ) ) {
		$clear_id = $_GET['clear'];

		$inviter_id = bp_loggedin_user_id();

		check_admin_referer( 'invite_anyone_clear' );

		if ( (int)$clear_id ) {
			if ( invite_anyone_clear_sent_invite( array( 'inviter_id' => $inviter_id, 'clear_id' => $clear_id ) ) )
				bp_core_add_message( __( 'Invitation cleared', 'bp-invite-anyone' ) );
			else
				bp_core_add_message( __( 'There was a problem clearing the invitation.', 'bp-invite-anyone' ), 'error' );
		} else {
			if ( invite_anyone_clear_sent_invite( array( 'inviter_id' => $inviter_id, 'type' => $clear_id ) ) )
				bp_core_add_message( __( 'Invitations cleared.', 'bp-invite-anyone' ) );
			else
				bp_core_add_message( __( 'There was a problem clearing the invitations.', 'bp-invite-anyone' ), 'error' );
		}

		bp_core_redirect( $bp->displayed_user->domain . $bp->invite_anyone->slug . '/sent-invites/' );
	}
}
add_action( 'bp_template_redirect', 'invite_anyone_catch_clear', 5 );

function invite_anyone_screen_one() {
	global $bp;

	/* Add a do action here, so your component can be extended by others. */
	do_action( 'invite_anyone_screen_one' );

	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

/**
 * invite_anyone_screen_two()
 *
 */
function invite_anyone_screen_two() {
	global $bp;

	do_action( 'invite_anyone_sent_invites_screen' );

	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );

}

/**
 * Displays the field where email addresses are entered on the Send Invites page
 *
 * In version 0.8, this field was changed to be a textarea rather than individual fields.
 *
 * @package Invite Anyone
 *
 * @param array $returned_emails Optional. Emails returned because of a processing error
 */
function invite_anyone_email_fields( $returned_emails = false ) {
	if ( is_array( $returned_emails ) )
		$returned_emails = implode( "\n", $returned_emails );
?>
	<textarea name="invite_anyone_email_addresses" class="invite-anyone-email-addresses" id="invite-anyone-email-addresses"><?php echo $returned_emails ?></textarea>
<?php
}


function invite_anyone_invitation_subject( $returned_message = false ) {
	global $bp;

	if ( !$returned_message ) {
		$site_name = get_bloginfo('name');

		$iaoptions = invite_anyone_options();

		if ( empty( $iaoptions['default_invitation_subject'] ) ) {
			$text = sprintf( __( 'An invitation to join the %s community.', 'bp-invite-anyone' ), $site_name );
		} else {
			$text = $iaoptions['default_invitation_subject'];
		}

		if ( !is_admin() ) {
			$text = invite_anyone_wildcard_replace( $text );
		}
	} else {
		$text = $returned_message;
	}

	return stripslashes( $text );
}

function invite_anyone_invitation_message( $returned_message = false ) {
	global $bp;

	if ( !$returned_message ) {
		$inviter_name = $bp->loggedin_user->userdata->display_name;
		$blogname = get_bloginfo('name');

		$iaoptions = invite_anyone_options();

		if ( empty( $iaoptions['default_invitation_message'] ) ) {
			$text = sprintf( __( 'You have been invited by %%INVITERNAME%% to join the %s community.

Visit %%INVITERNAME%%\'s profile at %%INVITERURL%%.', 'bp-invite-anyone' ), $blogname ); /* Do not translate the strings embedded in %% ... %% ! */
		} else {
			$text = $iaoptions['default_invitation_message'];
		}

		if ( !is_admin() ) {
			$text = invite_anyone_wildcard_replace( $text );
		}
	} else {
		$text = $returned_message;
	}

	return apply_filters( 'invite_anyone_get_invitation_message', stripslashes( $text ) );
}

function invite_anyone_process_footer( $email ) {

	$iaoptions = invite_anyone_options();

	if ( empty( $iaoptions['addl_invitation_message'] ) ) {

		$footer = apply_filters( 'invite_anyone_accept_invite_footer_message', __( 'To accept this invitation, please visit %%ACCEPTURL%%', 'bp-invite-anyone' ) );
		$footer .= '

';
		$footer .= apply_filters( 'invite_anyone_opt_out_footer_message', __( 'To opt out of future invitations to this site, please visit %%OPTOUTURL%%', 'bp-invite-anyone' ) );
	} else {
		$footer = $iaoptions['addl_invitation_message'];
	}

	return stripslashes( $footer );
}

function invite_anyone_wildcard_replace( $text, $email = false ) {
	global $bp;

	$inviter_name = $bp->loggedin_user->userdata->display_name;
	$site_name    = get_bloginfo( 'name' );
	$inviter_url  = bp_loggedin_user_domain();

	$email = urlencode( $email );

	$accept_link  = add_query_arg( array(
		'iaaction' => 'accept-invitation',
		'email'    => $email,
	), bp_get_root_domain() . '/' . bp_get_signup_slug() . '/' );
	$accept_link  = apply_filters( 'invite_anyone_accept_url', $accept_link );

	$opt_out_link = add_query_arg( array(
		'iaaction' => 'opt-out',
		'email'    => $email,
	), bp_get_root_domain() . '/' . bp_get_signup_slug() . '/' );

	$text = str_replace( '%%INVITERNAME%%', $inviter_name, $text );
	$text = str_replace( '%%INVITERURL%%', $inviter_url, $text );
	$text = str_replace( '%%SITENAME%%', $site_name, $text );
	$text = str_replace( '%%OPTOUTURL%%', $opt_out_link, $text );
	$text = str_replace( '%%ACCEPTURL%%', $accept_link, $text );

	/* Adding single % replacements because lots of people are making the mistake */
	$text = str_replace( '%INVITERNAME%', $inviter_name, $text );
	$text = str_replace( '%INVITERURL%', $inviter_url, $text );
	$text = str_replace( '%SITENAME%', $site_name, $text );
	$text = str_replace( '%OPTOUTURL%', $opt_out_link, $text );
	$text = str_replace( '%ACCEPTURL%', $accept_link, $text );

	return $text;
}

/**
 * Get the max allowed invites
 */
function invite_anyone_max_invites() {
	$options = invite_anyone_options();
	return isset( $options['max_invites'] ) ? intval( $options['max_invites'] ) : false;
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

/**
 * Fetches the invitee taxonomy name out of the $bp global so it can be queried in the template
 *
 * @package Invite Anyone
 * @since 0.8
 *
 * @return str $tax_name
 */
function invite_anyone_get_invitee_tax_name() {
	global $bp;

	$tax_name = '';

	if ( !empty( $bp->invite_anyone->invitee_tax_name ) )
		$tax_name = $bp->invite_anyone->invitee_tax_name;

	return $tax_name;
}

/**
 * Fetches the groups taxonomy name out of the $bp global so it can be queried in the template
 *
 * @package Invite Anyone
 * @since 0.8
 *
 * @return str $tax_name
 */
function invite_anyone_get_invited_groups_tax_name() {
	global $bp;

	$tax_name = '';

	if ( !empty( $bp->invite_anyone->invited_groups_tax_name ) )
		$tax_name = $bp->invite_anyone->invited_groups_tax_name;

	return $tax_name;
}

function invite_anyone_format_date( $date ) {
	$thetime = strtotime( $date );
	$format = get_option('date_format');
	$thetime = date( "$format", $thetime );
	return $thetime;
}

/**
 * Parses email addresses, comma-separated or line-separated, into an array
 *
 * @package Invite Anyone
 * @since 0.8.8
 *
 * @param str $address_string The raw string from the input box
 * @return array $emails An array of addresses
 */
function invite_anyone_parse_addresses( $address_string ) {

	$emails = array();

	// First, split by line breaks
	$rows = explode( "\n", $address_string );

	// Then look through each row to split by comma
	foreach( $rows as $row ) {
		$row_addresses = explode( ',', $row );

		// Then walk through and add each address to the array
		foreach( $row_addresses as $row_address ) {
			$row_address_trimmed = trim( $row_address );

			// We also have to make sure that the email address isn't empty
			if ( ! empty( $row_address_trimmed ) && ! in_array( $row_address_trimmed, $emails ) )
				$emails[] = $row_address_trimmed;
		}
	}

	return apply_filters( 'invite_anyone_parse_addresses', $emails, $address_string );
}

function invite_anyone_process_invitations( $data ) {
	global $bp;

	$emails = false;
	// Parse out the individual email addresses
	if ( !empty( $data['invite_anyone_email_addresses'] ) ) {
		$emails = invite_anyone_parse_addresses( $data['invite_anyone_email_addresses'] );
	}

	// Filter the email addresses so that plugins can have a field day
	$emails = apply_filters( 'invite_anyone_submitted_email_addresses', $emails, $data );

	// Set up a wrapper for any data to return to the Send Invites screen in case of error
	$returned_data = array(
		'error_message' => false,
		'error_emails'  => array(),
		'subject' 	=> $data['invite_anyone_custom_subject'],
		'message' 	=> $data['invite_anyone_custom_message'],
		'groups' 	=> isset( $data['invite_anyone_groups'] ) ? $data['invite_anyone_groups'] : ''
	);

	// Check against the max number of invites. Send back right away if there are too many
	$options 	= invite_anyone_options();
	$max_invites 	= !empty( $options['max_invites'] ) ? $options['max_invites'] : 5;

	if ( count( $emails ) > $max_invites ) {

		$returned_data['error_message']	= sprintf( __( 'You are only allowed to invite up to %s people at a time. Please remove some addresses and try again', 'bp-invite-anyone' ), $max_invites );
		$returned_data['error_emails'] 	= $emails;

		setcookie( 'invite-anyone', serialize( $returned_data ), 0, '/' );
		$redirect = bp_loggedin_user_domain() . $bp->invite_anyone->slug . '/invite-new-members/';
		bp_core_redirect( $redirect );
		die();
	}

	if ( empty( $emails ) ) {
		bp_core_add_message( __( 'You didn\'t include any email addresses!', 'bp-invite-anyone' ), 'error' );
		bp_core_redirect( $bp->loggedin_user->domain . $bp->invite_anyone->slug . '/invite-new-members' );
		die();
	}

	// Max number of invites sent
	$limit_total_invites = !empty( $options['email_limit_invites_toggle'] ) && 'no' != $options['email_limit_invites_toggle'];
	if ( $limit_total_invites && !current_user_can( 'delete_others_pages' ) ) {
		$sent_invites = invite_anyone_get_invitations_by_inviter_id( bp_loggedin_user_id() );
		$sent_invites_count      = (int) $sent_invites->post_count;
		$remaining_invites_count = (int) $options['limit_invites_per_user'] - $sent_invites_count;

		if ( count( $emails ) > $remaining_invites_count ) {
			$returned_data['error_message'] = sprintf( __( 'You are only allowed to invite %s more people. Please remove some addresses and try again', 'bp-invite-anyone' ), $remaining_invites_count );
			$returned_data['error_emails'] = $emails;

			setcookie( 'invite-anyone', serialize( $returned_data ), 0, '/' );
			$redirect = bp_loggedin_user_domain() . $bp->invite_anyone->slug . '/invite-new-members/';
			bp_core_redirect( $redirect );
			die();
		}
	}

	// Turn the CS emails into an array so that they can be matched against the main list
	if ( isset( $_POST['cloudsponge-emails'] ) ) {
		$cs_emails = explode( ',', $_POST['cloudsponge-emails'] );
	}

	// validate email addresses
	foreach( $emails as $key => $email ) {
		$check = invite_anyone_validate_email( $email );
		switch ( $check ) {

			case 'opt_out' :
				$returned_data['error_message'] .= sprintf( __( '<strong>%s</strong> has opted out of email invitations from this site.', 'bp-invite-anyone' ), $email );
				break;

			case 'used' :
				$returned_data['error_message'] .= sprintf( __( "<strong>%s</strong> is already a registered user of the site.", 'bp-invite-anyone' ), $email );
				break;

			case 'unsafe' :
				$returned_data['error_message'] .= sprintf( __( '<strong>%s</strong> is not a permitted email address.', 'bp-invite-anyone' ), $email );
				break;

			case 'invalid' :
				$returned_data['error_message'] .= sprintf( __( '<strong>%s</strong> is not a valid email address. Please make sure that you have typed it correctly.', 'bp-invite-anyone' ), $email );
				break;

			case 'limited_domain' :
				$returned_data['error_message'] .= sprintf( __( '<strong>%s</strong> is not a permitted email address. Please make sure that you have typed the domain name correctly.', 'bp-invite-anyone' ), $email );
				break;
		}

		// If there was an error in validation, we won't process this email
		if ( $check != 'okay' ) {
			$returned_data['error_message'] .= '<br />';
			$returned_data['error_emails'][] = $email;
			unset( $emails[$key] );
		}
	}

	if ( ! empty( $emails ) ) {

		unset( $message, $to );

		/* send and record invitations */

		do_action( 'invite_anyone_process_addl_fields' );

		$groups = ! empty( $data['invite_anyone_groups'] ) ? $data['invite_anyone_groups'] : array();
		$is_error = 0;

		foreach( $emails as $email ) {
			$subject = stripslashes( strip_tags( $data['invite_anyone_custom_subject'] ) );

			$message = stripslashes( strip_tags( $data['invite_anyone_custom_message'] ) );

			$footer = invite_anyone_process_footer( $email );
			$footer = invite_anyone_wildcard_replace( $footer, $email );

			$message .= '

================
';
			$message .= $footer;

			$to = apply_filters( 'invite_anyone_invitee_email', $email );
			$subject = apply_filters( 'invite_anyone_invitation_subject', $subject );
			$message = apply_filters( 'invite_anyone_invitation_message', $message );

			wp_mail( $to, $subject, $message );

			/* todo: isolate which email(s) cause problems, and send back to user */
		/*	if ( !invite_anyone_send_invitation( $bp->loggedin_user->id, $email, $message, $groups ) )
				$is_error = 1; */

			// Determine whether this address came from CloudSponge
			$is_cloudsponge = isset( $cs_emails ) && in_array( $email, $cs_emails ) ? true : false;

			invite_anyone_record_invitation( $bp->loggedin_user->id, $email, $message, $groups, $subject, $is_cloudsponge );

			do_action( 'sent_email_invite', $bp->loggedin_user->id, $email, $groups );

			unset( $message, $to );
		}

		// Set a success message

		$success_message = sprintf( __( "Invitations were sent successfully to the following email addresses: %s", 'bp-invite-anyone' ), implode( ", ", $emails ) );
		bp_core_add_message( $success_message );

		do_action( 'sent_email_invites', $bp->loggedin_user->id, $emails, $groups );
	} else {
		$success_message = sprintf( __( "Please correct your errors and resubmit.", 'bp-invite-anyone' ) );
		bp_core_add_message( $success_message, 'error' );
	}

	// If there are errors, redirect to the Invite New Members page
	if ( ! empty( $returned_data['error_emails'] ) ) {
		setcookie( 'invite-anyone', serialize( $returned_data ), 0, '/' );
		$redirect = bp_loggedin_user_domain() . $bp->invite_anyone->slug . '/invite-new-members/';
		bp_core_redirect( $redirect );
		die();
	}

	return true;
}

function invite_anyone_send_invitation( $inviter_id, $email, $message, $groups ) {
	global $bp;

}

/**
 * Redirect from old 'accept-invitation' and 'opt-out' email formats
 *
 * Invite Anyone used to use the current_action and action_variables for
 * subpages of the registration screen. This caused some problems with URL
 * encoding, and it also broke with BP 2.1. In IA 1.3.4, this functionality
 * was moved to URL arguments; the current function handles backward
 * compatibility with the old addresses.
 *
 * @since 1.3.4
 */
function invite_anyone_accept_invitation_backward_compatibility() {
	if ( ! bp_is_register_page() ) {
		return;
	}

	if ( ! bp_current_action() ) {
		return;
	}

	$action = bp_current_action();

	if ( ! in_array( $action, array( 'accept-invitation', 'opt-out' ) ) ) {
		return;
	}

	$redirect_to = add_query_arg( 'iaaction', $action, bp_get_root_domain() . '/' . bp_get_signup_slug() . '/' );

	$email = bp_action_variable( 0 );
	$email = str_replace( ' ', '+', $email );

	if ( ! empty( $email ) ) {
		$redirect_to = add_query_arg( 'email', $email, $redirect_to );
	}

	bp_core_redirect( $redirect_to );
	die();
}
add_action( 'bp_actions', 'invite_anyone_accept_invitation_backward_compatibility', 0 );

/**
 * Is this the 'accept-invitation' page?
 *
 * @since 1.3.4
 *
 * @return bool
 */
function invite_anyone_is_accept_invitation_page() {
	$retval = false;

	if ( bp_is_register_page() && ! empty( $_GET['iaaction'] ) && 'accept-invitation' === urldecode( $_GET['iaaction'] ) ) {
		$retval = true;
	}

	return apply_filters( 'invite_anyone_is_accept_invitation_page', $retval );
}

function invite_anyone_bypass_registration_lock() {
	global $bp;

	if ( ! invite_anyone_is_accept_invitation_page() ) {
		return;
	}

	if ( ! isset( $_GET['email'] ) || ! $email = urldecode( $_GET['email'] ) ) {
		return;
	}

	$options = invite_anyone_options();

	if ( empty( $options['bypass_registration_lock'] ) || $options['bypass_registration_lock'] != 'yes' )
		return;

	// Check to make sure that it's actually a valid email
	$ia_obj = invite_anyone_get_invitations_by_invited_email( $email );

	if ( !$ia_obj->have_posts() ) {
		bp_core_add_message( __( "We couldn't find any invitations associated with this email address.", 'bp-invite-anyone' ), 'error' );
		return;
	}

	// To support old versions of BP, we have to force the overloaded
	// site_options property in some cases
	if ( is_multisite() ) {
		$site_options = $bp->site_options;
		if ( !empty( $bp->site_options['registration'] ) && $bp->site_options['registration'] == 'blog' ) {
			$site_options['registration'] = 'all';
		} else if ( !empty( $bp->site_options['registration'] ) && $bp->site_options['registration'] == 'none' ) {
			$site_options['registration'] = 'user';
		}
		$bp->site_options = $site_options;

		add_filter( 'bp_get_signup_allowed', '__return_true' );
	} else {
		add_filter( 'option_users_can_register', create_function( false, 'return true;' ) );
	}
}
add_action( 'wp', 'invite_anyone_bypass_registration_lock', 1 );

/**
 * Double check that passed email address matches an existing invitation when registration lock bypass is on.
 *
 * @since 1.2
 *
 * @param array $results Error results from user signup validation
 * @return array
 */
function invite_anyone_check_invitation( $results ) {
	if ( ! invite_anyone_is_accept_invitation_page() ) {
		return $results;
	}

	// Check to make sure that it's actually a valid email
	$ia_obj = invite_anyone_get_invitations_by_invited_email( $results['user_email'] );

	if ( !$ia_obj->have_posts() ) {
		$errors = new WP_Error();
		$errors->add( 'user_email', __( "We couldn't find any invitations associated with this email address.", 'bp-invite-anyone' ) );
		$results['errors'] = $errors;
	}

	return $results;
}
add_filter( 'bp_core_validate_user_signup', 'invite_anyone_check_invitation' );

function invite_anyone_validate_email( $user_email ) {

	$status = 'okay';

	if ( invite_anyone_check_is_opt_out( $user_email ) ) {
		$status = 'opt_out';
	} else if ( $user = get_user_by( 'email', $user_email ) ) {
		$status = 'used';
	} else if ( function_exists( 'is_email_address_unsafe' ) && is_email_address_unsafe( $user_email ) ) {
		$status = 'unsafe';
	} else if ( function_exists( 'is_email' ) && !is_email( $user_email ) ) {
		$status = 'invalid';
	}

	if ( function_exists( 'get_site_option' ) ) {
		if ( $limited_email_domains = get_site_option( 'limited_email_domains' ) ) {
			if ( is_array( $limited_email_domains ) && empty( $limited_email_domains ) == false ) {
				$emaildomain = substr( $user_email, 1 + strpos( $user_email, '@' ) );
				if( in_array( $emaildomain, $limited_email_domains ) == false ) {
					$status = 'limited_domain';
				}
			}
		}
	}

	return apply_filters( 'invite_anyone_validate_email', $status, $user_email );
}

/**
 * Catches attempts to reaccept an invitation, and redirects appropriately
 *
 * If you attempt to access the register page when logged in, you get bounced
 * to the home page. This is a BP feature. Because accept-invitation is a
 * subpage of register, this happens for accept-invitation pages as well.
 * However, people are more likely to try to visit this page than the vanilla
 * register page, because they've gotten an email inviting them to the site.
 *
 * So this function catches logged-in visits to /register/accept-invitation,
 * and if the email address in the URL matches the logged-in user's email
 * address, redirects them to their invite-anyone page to see the a message.
 *
 * @since 1.0.20
 */
function invite_anyone_already_accepted_redirect( $redirect ) {
	global $bp;

	if ( ! invite_anyone_is_accept_invitation_page() ) {
		return $redirect;
	}

	if ( empty( $_GET['email'] ) ) {
		return $redirect;
	}

	$reg_email = urldecode( $_GET['email'] );

	if ( bp_core_get_user_email( bp_loggedin_user_id() ) !== $reg_email ) {
		return $redirect;
	}

	$redirect = add_query_arg( 'already', 'accepted', trailingslashit( bp_loggedin_user_domain() . $bp->invite_anyone->slug ) );

	return $redirect;
}
add_filter( 'bp_loggedin_register_page_redirect_to', 'invite_anyone_already_accepted_redirect' );

