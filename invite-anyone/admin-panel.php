<?php

function invite_anyone_admin_add() {
	$plugin_page = add_submenu_page( 'bp-general-settings', __('Invite Anyone','bp-invite-anyone'), __('Invite Anyone','bp-invite-anyone'), 'manage_options', __FILE__, 'invite_anyone_admin_panel' );
	add_action( "admin_print_scripts-$plugin_page", 'invite_anyone_admin_scripts' );
	add_action( "admin_print_styles-$plugin_page", 'invite_anyone_admin_styles' );
}
add_action( 'admin_menu', 'invite_anyone_admin_add', 80 );


function invite_anyone_admin_scripts() {
	wp_enqueue_script( 'invite-anyone-admin-js', WP_PLUGIN_URL . '/invite-anyone/invite-anyone/admin-js.js' );
}
 
function invite_anyone_admin_styles() {
	wp_enqueue_style( 'invite-anyone-admin-css', WP_PLUGIN_URL . '/invite-anyone/invite-anyone/admin-css.css' );
}

function invite_anyone_admin_panel() {
?>
	<div class="wrap">
    	<h2><?php _e( 'Invite Anyone Settings', 'bp-invite-anyone' ) ?></h2>
    
    	<form action="options.php" method="post">
        	<?php settings_fields( 'invite_anyone' ); ?>
            <?php do_settings_sections( 'invite_anyone' ); ?>
            
            <input id="invite-anyone-settings-submit" name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />           
		</form>
    
    </div>
<?php 
}



function invite_anyone_settings_setup() {
	register_setting( 'invite_anyone', 'invite_anyone', 'invite_anyone_settings_check' );
	
	/* General Settings */
	add_settings_section('invite_anyone_general_settings', __('General Settings', 'bp-invite-anyone'), 'invite_anyone_settings_main_content', 'invite_anyone');

	add_settings_field('invite_anyone_settings_replacement_patterns', __('Replacement patterns for email text fields', 'bp-invite-anyone'), 'invite_anyone_settings_replacement_patterns', 'invite_anyone', 'invite_anyone_general_settings');
		
	add_settings_field('invite_anyone_settings_default_invitation_subject', __('Text of email invitation subject line', 'bp-invite-anyone'), 'invite_anyone_settings_default_invitation_subject', 'invite_anyone', 'invite_anyone_general_settings');
	
	
	add_settings_field('invite_anyone_settings_default_invitation_message', __('Text of email invitation message', 'bp-invite-anyone'), 'invite_anyone_settings_default_invitation_message', 'invite_anyone', 'invite_anyone_general_settings');
	
		
	
	add_settings_field('invite_anyone_settings_is_customizable', __('Allow users to customize invitation', 'bp-invite-anyone'), 'invite_anyone_settings_is_customizable', 'invite_anyone', 'invite_anyone_general_settings');	

	
	add_settings_field('invite_anyone_settings_number_of_invitations', __('Number of email invitations users are permitted to send at a time', 'bp-invite-anyone'), 'invite_anyone_settings_number_of_invitations', 'invite_anyone', 'invite_anyone_general_settings');
		
	/* Access Settings */
	add_settings_section('invite_anyone_access_settings', __('Access Settings', 'bp-invite-anyone'), 'invite_anyone_settings_access_content', 'invite_anyone');
	
	add_settings_field('invite_anyone_settings_email_visibility', __('Allow email invitations to be sent by', 'bp-invite-anyone'), 'invite_anyone_settings_email_visibility', 'invite_anyone', 'invite_anyone_access_settings');
	
	add_settings_field('invite_anyone_settings_groups_per_page', __('Number of groups to display per page', 'bp-invite-anyone'), 'invite_anyone_settings_groups_per_page', 'invite_anyone', 'invite_anyone_general');




}
add_action('admin_init', 'invite_anyone_settings_setup');


function invite_anyone_settings_main_content() {

global $current_user;
?>
	<p><?php _e( 'Control the default behavior of Invite Anyone.', 'bp-invite-anyone' ) ?></p>


<?php
}

function invite_anyone_settings_replacement_patterns() {
?>

		<ul>
			<li><strong>%%INVITERNAME%%</strong> - display name of the inviter</li>
			<li><strong>%%INVITERURL%%</strong> - URL to the profile of the inviter</li>
			<li><strong>%%SITENAME%%</strong> - name of your site (<?php bloginfo('name') ?>)</li>
		</ul>
<?php
}


/* Max number of email invitations at a time */

function invite_anyone_settings_number_of_invitations() {
	$options = get_option( 'invite_anyone' );
	echo "<input id='invite_anyone_settings_number_of_invitations' name='invite_anyone[max_invites]' size='10' type='text' value='{$options['max_invites']}' />";
}

function invite_anyone_settings_default_invitation_subject() {
	$options = get_option( 'invite_anyone' );
	echo "<textarea name='invite_anyone[default_invitation_subject]' cols=60 rows=2 >" . invite_anyone_invitation_subject() . "</textarea>";
}

function invite_anyone_settings_default_invitation_message() {
	$options = get_option( 'invite_anyone' );
	echo "<textarea name='invite_anyone[default_invitation_message]' cols=60 rows=5 >" . invite_anyone_invitation_message() . "</textarea>";
}

function invite_anyone_settings_is_customizable() {
	$options = get_option( 'invite_anyone' );
?>
	<ul>
		<li>
			<input type="checkbox" name="invite_anyone[subject_is_customizable]" value="yes" <?php if( $options['subject_is_customizable'] == 'yes' ) : ?>checked="checked"<?php endif; ?> /> Subject line
		</li>
		
		<li>
			<input type="checkbox" name="invite_anyone[message_is_customizable]" value="yes" <?php if( $options['message_is_customizable'] == 'yes' ) : ?>checked="checked"<?php endif; ?> /> Message body
		</li>
	</ul>
<?php
}

function invite_anyone_settings_access_content() {
?>
	<p><?php _e( 'Control which members are able to send various kinds of invitations.', 'bp-invite-anyone' ) ?></p>
<?php
}

function invite_anyone_settings_email_visibility() {
	$options = get_option( 'invite_anyone' );
?>

	<ul>
		<li><input type='radio' name='invite_anyone[email_visibility_toggle]' id='invite_anyone_toggle_email_no_limit' value='no_limit' <?php if( $options['email_visibility_toggle'] == 'no_limit' ) : ?>checked="checked"<?php endif; ?> /> <?php _e( 'All users', 'bp-invite-anyone' ) ?></li>
		
		<li><input type='radio' name='invite_anyone[email_visibility_toggle]' id='invite_anyone_toggle_email_limit' value='limit' <?php if( $options['email_visibility_toggle'] == 'limit' ) : ?>checked="checked"<?php endif; ?> /> <?php _e( 'A limited set of users', 'bp-invite-anyone' ) ?>
			<div class="invite-anyone-admin-limited">
			<ul>
				<li>
					<input type="checkbox" name="invite_anyone[email_since_toggle]" value="yes" <?php if( $options['email_since_toggle'] == 'yes' ) : ?>checked="checked"<?php endif; ?> /> <?php _e( 'Only users who have been members of the site for a minimum number of days:', 'bp-invite-anyone' ) ?> 
					<input name='invite_anyone[days_since]' size='10' type='text' value='<?php echo $options['days_since'] ?>' />			
				</li>
				
				<li>
					<input type="checkbox" name="invite_anyone[email_role_toggle]" value="yes"  <?php if( $options['email_role_toggle'] == 'yes' ) : ?>checked="checked"<?php endif; ?> /> <?php _e( 'Only users who have at least the following role on this blog:', 'bp-invite-anyone' ) ?> 
					<select name="invite_anyone[minimum_role]">
						<option value="Subscriber" <?php if( $options['minimum_role'] == 'Subscriber' ) : ?>selected="selected"<?php endif; ?> ><?php _e( 'Subscriber' ) ?></option>
						<option value="Contributor" <?php if( $options['minimum_role'] == 'Contributor' ) : ?>selected="selected"<?php endif; ?>><?php _e( 'Contributor' ) ?></option>
						<option value="Author" <?php if( $options['minimum_role'] == 'Author' ) : ?>selected="selected"<?php endif; ?>><?php _e( 'Author' ) ?></option>
						<option value="Editor" <?php if( $options['minimum_role'] == 'Editor' ) : ?>selected="selected"<?php endif; ?>><?php _e( 'Editor' ) ?></option>
						<option value="Administrator" <?php if( $options['minimum_role'] == 'Administrator' ) : ?>selected="selected"<?php endif; ?>><?php _e( 'Administrator' ) ?></option>
					</select>					
				</li>		
				
				<li>
					<input type="checkbox" name="invite_anyone[email_blacklist_toggle]" value="yes"  <?php if( $options['email_blacklist_toggle'] == 'yes' ) : ?>checked="checked"<?php endif; ?> /> <?php _e( 'Provide a comma-separated list of users (identified by their numerical user ids) who <strong>cannot</strong> send invitations by email:', 'bp-invite-anyone' ) ?>
					<input name='invite_anyone[email_blacklist]' size='40' type='text' value='<?php echo $options['email_blacklist'] ?>' />
				</li>		
			</ul>
			</div>
		</li>
	</ul>

<?php
}


function invite_anyone_settings_check($input) {
	return $input;
/*print_r($input);
	$newinput['number_of_invitations'] = trim($input['number_of_invitations']);
	$newinput['groups_per_page'] = trim($input['groups_per_page']);
	return $newinput;*/
}





?>