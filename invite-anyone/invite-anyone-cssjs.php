<?php

// Todo: figure out what happens when $bp->action_variables is not an array

function invite_anyone_add_js() {
	global $bp;
	
	if ( $bp->current_component == $bp->invite_anyone->slug || $bp->current_action == BP_INVITE_ANYONE_SLUG || in_array( BP_INVITE_ANYONE_SLUG, $bp->action_variables ) ) {
		wp_register_script('invite-anyone-js', WP_PLUGIN_URL . '/invite-anyone/invite-anyone/invite-anyone-js.js');
		wp_enqueue_script( 'invite-anyone-js' );
		
		add_action( 'wp_head', 'invite_anyone_autocomplete_init_jsblock' );

		
		wp_enqueue_script( 'invite-anyone-autocomplete-js', WP_PLUGIN_URL . '/invite-anyone/invite-anyone/jquery.autocomplete.js', array( 'jquery' ) );
		wp_enqueue_script( 'bp-jquery-autocomplete-fb', WP_PLUGIN_URL . '/invite-anyone/invite-anyone/jquery.autocompletefb.js' );
		wp_enqueue_script( 'bp-jquery-bgiframe', BP_PLUGIN_URL . '/bp-messages/js/autocomplete/jquery.bgiframe.min.js' );
		wp_enqueue_script( 'bp-jquery-dimensions', BP_PLUGIN_URL . '/bp-messages/js/autocomplete/jquery.dimensions.js' );
		
		if ( !function_exists( 'bp_post_get_permalink' ) )
			wp_enqueue_script( 'invite-anyone-livequery', WP_PLUGIN_URL . '/invite-anyone/invite-anyone/jquery.livequery.js' );
	}
}
add_action( 'wp_head', 'invite_anyone_add_js', 1 );



function invite_anyone_autocomplete_init_jsblock() {
?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			var acfb =
			jQuery("ul.first").autoCompletefb({urlLookup:'<?php echo $bp->root_domain . str_replace( 'index.php', 'wp-load.php', $_SERVER['SCRIPT_NAME'] ) ?>'});

			jQuery('#send_message_form').submit( function() {
				var users = document.getElementById('send-to-usernames').className;
				document.getElementById('send-to-usernames').value = String(users);
			});
		});
	</script>
<?php
}

function invite_anyone_add_css() {
	global $bp;
	if ( $bp->current_component == $bp->invite_anyone->slug || $bp->current_action == BP_INVITE_ANYONE_SLUG || in_array( BP_INVITE_ANYONE_SLUG, $bp->action_variables ) ) {
   		$style_url = WP_PLUGIN_URL . '/invite-anyone/invite-anyone/invite-anyone.css';
        $style_file = WP_PLUGIN_DIR . '/invite-anyone/invite-anyone/invite-anyone.css';
        if (file_exists($style_file)) {
            wp_register_style('invite-anyone-style', $style_url);
            wp_enqueue_style('invite-anyone-style');
        }
    }
}
add_action( 'wp_print_styles', 'invite_anyone_add_css' );

function invite_anyone_add_old_css() { ?>
	<style type="text/css">

li a#nav-invite-anyone {
	padding: 0.55em 3.1em 0.55em 0px !important;
	margin-right: 10px;
	background: url(<?php echo WP_PLUGIN_URL, '/invite-anyone/invite-anyone/invite_bullet.gif'; ?>) no-repeat 89% 52%;
	
}
	</style>
<?php	
}

?>