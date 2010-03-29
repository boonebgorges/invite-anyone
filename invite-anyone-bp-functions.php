<?php

class BP_Invite_Anyone extends BP_Group_Extension {

	var $enable_nav_item = true;
	var $enable_create_step = true;
	var $enable_edit_item = false;

	function bp_invite_anyone() {
		global $bp;

		$this->has_caps = true;

		/* Group API Extension Properties */
		$this->name = __( 'Send Invites', 'buddypress' );
		$this->slug = BP_INVITE_ANYONE_SLUG;

		/* Set as early in the order as possible */
		$this->create_step_position = 42;
		$this->nav_item_position = 71;

		/* Generic check access */
		if ( $this->has_caps == false ) {
			$this->enable_create_step = false;
			$this->enable_edit_step = false;
		}
		
		$this->enable_nav_item = $this->enable_nav_item();

	}

	function display() {
		invite_anyone_create_screen_content( 'invite' );
	}

	function create_screen() {
		global $bp;

		/* If we're not at this step, go bye bye */
		if ( !bp_is_group_creation_step( $this->slug ) )
			return false;

		invite_anyone_create_screen_content( 'create' );

		wp_nonce_field( 'groups_create_save_' . $this->slug );
	}

	function create_screen_save( ) {
		global $bp;
	
		/* Always check the referer */
		check_admin_referer( 'groups_create_save_' . $this->slug );

		/* Set method and save */
		if ( bp_group_has_invites() )
			$this->has_invites = true;
		$this->method = 'create';
		$this->save();
	}

	function save() {
		global $bp;

		/* Set error redirect based on save method */
		if ( $this->method == 'create' )
			$redirect_url = $bp->loggedin_user->domain . $bp->groups->slug . '/create/step/' . $this->slug;
		else
			$redirect_url = bp_get_group_permalink( $bp->groups->current_group ) . '/admin/' . $this->slug;

		groups_send_invites( $bp->loggedin_user->id, $bp->groups->current_group->id ); 

		if ( $this->has_invites )
			bp_core_add_message( __('Group invites sent.', 'buddypress') );	
		else
			bp_core_add_message( __('Group created successfully.', 'buddypress') );
	}
	
	function enable_nav_item() {
		global $bp;
		
		if ( bp_group_is_admin() || bp_group_is_mod() )
			return true;
		else
			return false;
	}
	
	function widget_display() {}
}
bp_register_group_extension( 'BP_Invite_Anyone' );


function invite_anyone_create_screen_content( $event ) {
	if ( function_exists( 'bp_post_get_permalink' ) ) { // ugly ugly ugly hack to check for pre-1.2 versions of BP
		
		add_action( 'wp_footer', 'invite_anyone_add_old_css' );
	?>
		
		
		
		
		<?php if ( bp_has_groups() ) : while ( bp_groups() ) : bp_the_group(); ?>

			<?php do_action( 'bp_before_group_send_invites_content' ) ?>
				
				<?php if ( $event != 'create' ) : ?>
					<form action="<?php bp_group_send_invite_form_action() ?>" method="post" id="send-invite-form">
				<?php endif; ?>

					<div class="left-menu">
						
						<p><?php _e("Search for members to invite:", 'bp-invite-anyone') ?> &nbsp; <span class="ajax-loader"></span></p>
						
						<ul class="first acfb-holder">
							<li>
								<input type="text" name="send-to-input" class="send-to-input" id="send-to-input" />
							</li>
						</ul>
						
						<p><?php _e( 'Or select members from the directory:', 'bp-invite-anyone' ) ?> <span class="ajax-loader"></span></p>

						<div id="invite-anyone-member-list">
							<ul>
								<?php bp_new_group_invite_member_list() ?>
							</ul>

							<?php wp_nonce_field( 'groups_invite_uninvite_user', '_wpnonce_invite_uninvite_user' ) ?>
						</div>

					</div>

					<div class="main-column">

						<div id="message" class="info">
							<p><?php _e('Select people to invite.', 'bp-invite-anyone'); ?></p>
						</div>

						<?php do_action( 'bp_before_group_send_invites_list' ) ?>

						<?php /* The ID 'friend-list' is important for AJAX support. */ ?>
						<ul id="invite-anyone-invite-list" class="item-list">
						<?php if ( bp_group_has_invites() ) : ?>

							<?php while ( bp_group_invites() ) : bp_group_the_invite(); ?>

								<li id="<?php bp_group_invite_item_id() ?>">
									<?php bp_group_invite_user_avatar() ?>

									<h4><?php bp_group_invite_user_link() ?></h4>
									<span class="activity"><?php bp_group_invite_user_last_active() ?></span>

									<?php do_action( 'bp_group_send_invites_item' ) ?>

									<div class="action">
										<a class="remove" href="<?php bp_group_invite_user_remove_invite_url() ?>" id="<?php bp_group_invite_item_id() ?>"><?php _e( 'Remove Invite', 'buddypress' ) ?></a>

										<?php do_action( 'bp_group_send_invites_item_action' ) ?>
									</div>
								</li>

							<?php endwhile; ?>
						<?php endif; ?>
						</ul>

						<?php do_action( 'bp_after_group_send_invites_list' ) ?>

					</div>

					<div class="clear"></div>
				
				<?php if ( $event != 'create' ) : ?>
					<p class="clear"><input type="submit" name="submit" id="submit" value="<?php _e( 'Send Invites', 'buddypress' ) ?>" /></p>
					<?php wp_nonce_field( 'groups_send_invites', '_wpnonce_send_invites') ?>
				<?php endif; ?>

							
				
					<input type="hidden" name="group_id" id="group_id" value="<?php bp_group_id() ?>" />
				<?php if ( $event != 'create' ) : ?>
					</form>
				<?php endif; ?>
		

			<?php do_action( 'bp_before_group_send_invites_content' ); ?>
	<?php endwhile; endif;
	
	
	
	} else { // Begin BP 1.2 code
	
	?>
	<?php do_action( 'bp_before_group_send_invites_content' ) ?>


	<?php if ( $event != 'create' ) : ?>
			<form action="<?php bp_group_send_invite_form_action() ?>" method="post" id="send-invite-form">
	<?php endif; ?>

		<div class="left-menu">

		<p><?php _e("Search for members to invite:", 'bp-invite-anyone') ?> &nbsp; <span class="ajax-loader"></span></p>
						
						<ul class="first acfb-holder">
							<li>
								<input type="text" name="send-to-input" class="send-to-input" id="send-to-input" />
							</li>
						</ul>

		<p><?php _e( 'Or select members from the directory:', 'bp-invite-anyone' ) ?> <span class="ajax-loader"></span></p>

			<div id="invite-anyone-member-list">
				<ul>
					<?php bp_new_group_invite_member_list() ?>
				</ul>

				<?php wp_nonce_field( 'groups_invite_uninvite_user', '_wpnonce_invite_uninvite_user' ) ?>
			</div>

		</div>

		<div class="main-column">

			<div id="message" class="info">
				<p><?php _e('Select people to invite from your friends list.', 'buddypress'); ?></p>
			</div>

			<?php do_action( 'bp_before_group_send_invites_list' ) ?>

			<?php /* The ID 'friend-list' is important for AJAX support. */ ?>
			<ul id="invite-anyone-invite-list" class="item-list">
			<?php if ( bp_group_has_invites() ) : ?>

				<?php while ( bp_group_invites() ) : bp_group_the_invite(); ?>

					<li id="<?php bp_group_invite_item_id() ?>">
						<?php bp_group_invite_user_avatar() ?>

						<h4><?php bp_group_invite_user_link() ?></h4>
						<span class="activity"><?php bp_group_invite_user_last_active() ?></span>

						<?php do_action( 'bp_group_send_invites_item' ) ?>

						<div class="action">
							<a class="remove" href="<?php bp_group_invite_user_remove_invite_url() ?>" id="<?php bp_group_invite_item_id() ?>"><?php _e( 'Remove Invite', 'buddypress' ) ?></a>

							<?php do_action( 'bp_group_send_invites_item_action' ) ?>
						</div>
					</li>

				<?php endwhile; ?>

			<?php endif; ?>
			</ul>

			<?php do_action( 'bp_after_group_send_invites_list' ) ?>

		</div>

		<div class="clear"></div>

		<div class="submit">
			<input type="submit" name="submit" id="submit" value="<?php _e( 'Send Invites', 'buddypress' ) ?>" />
		</div>

		<?php wp_nonce_field( 'groups_send_invites', '_wpnonce_send_invites') ?>

		<!-- Don't leave out this sweet field -->
			<?php
					if ( !bp_get_new_group_id() ) {
						?><input type="hidden" name="group_id" id="group_id" value="<?php bp_group_id() ?>" /><?php
					} else {
						?><input type="hidden" name="group_id" id="group_id" value="<?php bp_new_group_id() ?>" /><?php
					}
				?>
	
	<?php if ( $event != 'create' ) : ?>
		</form>
	<?php endif; ?>

<?php do_action( 'bp_after_group_send_invites_content' ) ?>

	
	
	<?php
	}
}

/* Creates the list of members on the Sent Invite screen */
function bp_new_group_invite_member_list() {
	echo bp_get_new_group_invite_member_list();
}
	function bp_get_new_group_invite_member_list( $args = '' ) {
		global $bp;

		$defaults = array(
			'group_id' => false,
			'separator' => 'li'
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		if ( !$group_id )
			$group_id = ( $bp->groups->new_group_id ) ? $bp->groups->new_group_id : $bp->groups->current_group->id;

		$friends = get_members_invite_list( $bp->loggedin_user->id, $group_id );

		if ( $friends ) {
			$invites = groups_get_invites_for_group( $bp->loggedin_user->id, $group_id );
			
			for ( $i = 0; $i < count( $friends ); $i++ ) {
				if ( $invites ) {
					if ( in_array( $friends[$i]['id'], $invites ) ) {
						$checked = ' checked="checked"';
					} else {
						$checked = '';
					}
				}

				$items[] = '<' . $separator . '><input' . $checked . ' type="checkbox" name="friends[]" id="f-' . $friends[$i]['id'] . '" value="' . attribute_escape( $friends[$i]['id'] ) . '" /> ' . $friends[$i]['full_name'] . '</' . $separator . '>';
			}
		}

		return implode( "\n", (array)$items );
	}


function get_members_invite_list( $user_id = false, $group_id ) {
	global $bp, $wpdb;

	if ( !$user_id )
		$user_id = $bp->loggedin_user->id;
	
	$query = "SELECT * FROM {$wpdb->users}";
	$members = $wpdb->get_results( $query, ARRAY_A );

	if ( !count($members) )
		return false;

	foreach( $members as $member ) {
		$user_id = $member['ID'];
		
		if ( groups_is_user_member( $user_id, $group_id ) )
			continue;
		
		$display_name = bp_core_get_user_displayname( $user_id );
		
		if ( $display_name != '' ) {
			$friends[] = array(
				'id' => $user_id,
				'full_name' => $display_name
			);
		}
	}


	if ( !$friends )
		return false;

	return $friends;
}

function invite_anyone_ajax_invite_user() {
	global $bp;
	
	check_ajax_referer( 'groups_invite_uninvite_user' );

	if ( !$_POST['friend_id'] || !$_POST['friend_action'] || !$_POST['group_id'] )
		return false;
		
	
	
/*	if ( !groups_is_user_admin( $bp->loggedin_user->id, $_POST['group_id'] ) )
		return false; */
	
	if ( 'invite' == $_POST['friend_action'] ) {
				
		if ( !groups_invite_user( array( 'user_id' => $_POST['friend_id'], 'group_id' => $_POST['group_id'] ) ) )
			return false;
		
		$user = new BP_Core_User( $_POST['friend_id'] );
		
		echo '<li id="uid-' . $user->id . '">';
		echo $user->avatar_thumb;
		echo '<h4>' . $user->user_link . '</h4>';
		echo '<span class="activity">' . attribute_escape( $user->last_active ) . '</span>';
		echo '<div class="action">
				<a class="remove" href="' . wp_nonce_url( $bp->loggedin_user->domain . $bp->groups->slug . '/' . $_POST['group_id'] . '/invites/remove/' . $user->id, 'groups_invite_uninvite_user' ) . '" id="uid-' . attribute_escape( $user->id ) . '">' . __( 'Remove Invite', 'buddypress' ) . '</a> 
			  </div>';
		echo '</li>';
		
	} else if ( 'uninvite' == $_POST['friend_action'] ) {
		
		if ( !groups_uninvite_user( $_POST['friend_id'], $_POST['group_id'] ) )
			return false;
		
		return true;
		
	} else {
		return false;
	}
}
add_action( 'wp_ajax_invite_anyone_groups_invite_user', 'invite_anyone_ajax_invite_user' );

function invite_anyone_ajax_autocomplete_results() {
	global $bp;
	
	$friends = false;

	// Get the friend ids based on the search terms
	if ( function_exists( 'invite_anyone_search_members' ) )
		$friends = invite_anyone_search_members( $_GET['q'], 500, 1 );
	//print_r($friends);
	
	$friends = apply_filters( 'bp_friends_autocomplete_list', $friends, $_GET['q'], $_GET['limit'] );

	if ( $friends['users'] ) {
		foreach ( $friends['users'] as $user ) {
			if ( $user->user_id ) // For BP < 1.2
				$user_id = $user->user_id;
			else
				$user_id = $user->id;
			$ud = get_userdata($user_id);
			$username = $ud->user_login;
			echo bp_core_fetch_avatar( array( 'item_id' => $user_id, 'type' => 'thumb', 'width' => 25, 'height' => 25 ) ) . ' ' . bp_core_get_user_displayname( $user->user_id ) . ' (' . $username . ')
			';
		}		
	}
}
add_action( 'wp_ajax_invite_anyone_autocomplete_results', 'invite_anyone_ajax_autocomplete_results' );

function invite_anyone_search_members( $search_terms, $pag_num = 10, $pag_page = 1 ) {
	return BP_Core_User::search_users( $search_terms, $pag_num, $pag_page );
}

require ( WP_PLUGIN_DIR . '/invite-anyone/invite-anyone/invite-anyone-cssjs.php' );
function invite_anyone_js_loader() {
	wp_enqueue_script( 'inviteAnyoneJS' );
}
add_action( 'init', 'invite_anyone_js_loader' );

function invite_anyone_remove_group_creation_invites( $a ) {
	
	foreach ( $a as $key => $value ) {
		if ( $key == 'group-invites' ) {
			unset( $a[$key] );
		}
	}
	return $a;
}
add_filter( 'groups_create_group_steps', 'invite_anyone_remove_group_creation_invites', 1 );

function invite_anyone_remove_invite_subnav() {
	global $bp;
	
	if ( $bp->groups->group_creation_steps['group-invites'] )
		unset( $bp->groups->group_creation_steps['group-invites'] );
	
	bp_core_remove_subnav_item( $bp->groups->slug, 'send-invites' );
}
add_action( 'wp', 'invite_anyone_remove_invite_subnav', 2 );
add_action( 'admin_menu', 'invite_anyone_remove_invite_subnav', 2 );
?>