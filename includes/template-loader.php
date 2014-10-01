<?php 

/** TEMPLATE LOADER ************************************************/
/**
* Invite Anyone template loader.
*
* If a template does not exist in the current theme, we will use our own
* bundled templates.
*
* We're doing two things here:
* 1) Support the older template format for themes that are using them
* for backwards-compatibility (the template passed in
* {@link bp_core_load_template()}).
* 2) Route older template names to use our new template locations and
* format.
*
* From work by r-a-y
*
* @since 1.4
*/
function invite_anyone_load_template_filter( $found_template, $templates ) {
	// Only filter the template location when we're on the group invitation screen, the group creation invite-anyone step, or the members component
	if ( ! ( invite_anyone_is_group_screen() || bp_is_group_creation_step( BP_INVITE_ANYONE_SLUG ) ||  bp_is_current_component( BP_INVITE_ANYONE_SLUG ) ) )
		return $found_template;

	// $found_template may not empty if template files were found in the
	// parent or child theme
	if ( empty( $found_template ) ) {
		// locate_template() will attempt to find the plugins.php template in the
		// child and parent theme and return the located template when found
		if ( invite_anyone_is_group_screen() || bp_is_group_creation_step( BP_INVITE_ANYONE_SLUG ) ) {
			$found_template = locate_template( 'members/groups/plugins.php', false, false );
		} else if ( bp_is_current_component( BP_INVITE_ANYONE_SLUG ) ) {
			$found_template = locate_template( 'members/single/plugins.php', false, false );
		}
	}

	// Register our theme compat directory.
	// This tells BP to look for templates in our plugin directory 
	// if the template isn't found in the parent/child theme.
	bp_register_template_stack( 'invite_anyone_get_template_directory', 14 );

	if ( invite_anyone_is_group_screen() || bp_is_group_creation_step( BP_INVITE_ANYONE_SLUG ) ) {
		// add our hook to inject content into BP's group screen
		add_action( 'bp_template_content', create_function( '', "
			bp_get_template_part( 'groups/single/invite-anyone' );
		" ) );
	} else if ( bp_is_current_component( BP_INVITE_ANYONE_SLUG ) ) {
		// add our hook to inject content into BP's member screens
		if ( bp_is_current_action( 'invite-new-members' ) ) {
			add_action( 'bp_template_content', create_function( '', "
				bp_get_template_part( 'members/single/invite-anyone/invite-new-members' );
			" ) );
		} else if ( bp_is_current_action( 'sent-invites' ) ) {
			add_action( 'bp_template_content', create_function( '', "
				bp_get_template_part( 'members/single/invite-anyone/sent-invites' );
			" ) );
		}
	}

	return apply_filters( 'invite_anyone_load_template_filter', $found_template );
}
add_filter( 'bp_located_template', 'invite_anyone_load_template_filter', 12, 2 );

/**
* Get the Invite Anyone template directory.
*
* @since 1.4
*
* @uses apply_filters()
* @return string
*/
function invite_anyone_get_template_directory() {
return apply_filters( 'invite_anyone_get_template_directory', BP_INVITE_ANYONE_DIR . '/includes/templates' );
}