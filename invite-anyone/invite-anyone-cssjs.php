<?php

function invite_anyone_add_js() {
	global $bp;
	
	if ( $bp->current_component == $bp->groups->slug ) {
		wp_register_script('invite-anyone-js', WP_PLUGIN_URL . '/invite-anyone/invite-anyone/invite-anyone-js.js');
		wp_enqueue_script( 'invite-anyone-js' );
		
		add_action( 'wp_head', 'invite_anyone_autocomplete_init_jsblock' );


		
		wp_enqueue_script( 'invite-anyone-autocomplete-js', WP_PLUGIN_URL . '/invite-anyone/invite-anyone/jquery.autocomplete.js', array( 'jquery' ) );
		wp_enqueue_script( 'bp-jquery-autocomplete-fb', WP_PLUGIN_URL . '/invite-anyone/invite-anyone/jquery.autocompletefb.js' );
		wp_enqueue_script( 'bp-jquery-bgiframe', BP_PLUGIN_URL . '/bp-messages/js/autocomplete/jquery.bgiframe.min.js' );
		wp_enqueue_script( 'bp-jquery-dimensions', BP_PLUGIN_URL . '/bp-messages/js/autocomplete/jquery.dimensions.js' );
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



function invite_anyone_add_css() { ?>
	<style type="text/css">
	
.invite-anyone .left-menu, .group-create .left-menu {
	width: 250px;
}

.invite-anyone .main-column, .group-create .main-column {
	margin-left: 250px;
}

#create-group-form ul.first {
	margin: 0;
}

#create-group-form ul.first li {
	padding-left: 0;
}

#invite-anyone-member-list { margin-bottom: 25px; }

#invite-anyone-member-list ul, ul#friend-list {
    margin: 0;
}

#invite-anyone-member-list input { width: auto !important; }

#invite-anyone-member-list ul li {
    list-style: none;
    padding: 0;
}

#invite-anyone-member-list {
	height: 275px;
	overflow: auto;
	border: 1px solid #ccc;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
	padding: 5px;
	background: #f5f5f5;
	width: 225px;
}

.ac_results {
	padding: 0px;
	overflow: hidden;
	z-index: 99999;
	background: #fff;
	border: 1px solid #ccc;
	-moz-border-radius-bottomleft: 3px;
	-khtml-border-bottom-left-radius: 3px;
	-webkit-border-bottom-left-radius: 3px;
	border-bottom-left-radius: 3px;
	-moz-border-radius-bottomright: 3px;
	-khtml-border-bottom-right-radius: 3px;
	-webkit-border-bottom-right-radius: 3px;
	border-bottom-right-radius: 3px;
}
	.ac_results ul {
		width: 100%;
		list-style-position: outside;
		list-style: none;
		padding: 0;
		margin: 0;
	}
	
	.ac_results li {
		margin: 0px;
		padding: 5px 10px;
		cursor: pointer;
		display: block;
		font-size: 1em;
		line-height: 16px;
		overflow: hidden;
	}

	
.ac_loading {
	background : url('../../images/ajax-loader.gif') right center no-repeat;
}

.ac_odd {
	background-color: #f0f0f0;
}

.ac_over {
	background-color: #888;
	color: #fff;
}

ul.acfb-holder { 
	margin  : 0; 
	height  : auto !important; 
	height  : 1%; 
	overflow: hidden; 
	padding: 0;
	list-style: none;
}
	ul.acfb-holder li { 
		float   : left; 
		margin  : 0 5px 4px 0; 
		list-style-type: none; 
	}
	
	ul.acfb-holder li.friend-tab { 
		border-radius         : 3px; 
		-moz-border-radius    : 3px; 
		-webkit-border-radius : 3px; 
		border     : 1px solid #ffe7c7; 
		padding    : 2px 7px 2px; 
		background : #FFF9DF; 
		font-size: 1em;
	}
		li.friend-tab img.avatar {
			border-width: 2px !important;
			vertical-align: middle;
		}
		
		li.friend-tab span.p {
			padding-left: 5px;
			font-size: 0.8em;
			cursor: pointer;
		}

input#send-to-input { width: 225px !important; }

li a#nav-invite-anyone {
	padding: 0.55em 3.1em 0.55em 0px !important;
	margin-right: 10px;
	background: url(<?php echo WP_PLUGIN_URL, '/invite-anyone/invite-anyone/invite_bullet.gif'; ?>) no-repeat 89% 52%;
	
}
	</style>
<?php	
}
add_action( 'wp_footer', 'invite_anyone_add_css' );

?>