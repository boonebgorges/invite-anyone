jQuery(document).ready( function() {
	var j = jQuery;
	
	var toggle = j("input#invite_anyone_toggle_email_limit");
	var offtoggle = j("input#invite_anyone_toggle_email_no_limit");	
	var submitbutton = j("#invite-anyone-settings-submit");
	
	if ( j(toggle).attr('checked') == false ) {
		j("div.invite-anyone-admin-limited input").attr('disabled', 'disabled');
		j("div.invite-anyone-admin-limited select").attr('disabled', 'disabled');
		j("div.invite-anyone-admin-limited").css('color', '#999');
		j("div.invite-anyone-admin-limited input").css('color', '#999');
	}	

	j(offtoggle).click(
		function() {
			j("div.invite-anyone-admin-limited input").attr('disabled', 'disabled');
			j("div.invite-anyone-admin-limited select").attr('disabled', 'disabled');
			
			j("div.invite-anyone-admin-limited").css('color', '#999');
			j("div.invite-anyone-admin-limited input").css('color', '#999');
		}
	);

	j(toggle).click(
		function() {
			j("div.invite-anyone-admin-limited input").removeAttr('disabled');
			j("div.invite-anyone-admin-limited select").removeAttr('disabled');
			
			j("div.invite-anyone-admin-limited").css('color', '#000');	
			j("div.invite-anyone-admin-limited input").css('color', '#000');
		}
	);
	
	/* Undisables inputs and selects on form submit, so that WP saves the disabled options */
	j(submitbutton).click(
		function() {
			j("div.invite-anyone-admin-limited input").removeAttr('disabled');
			j("div.invite-anyone-admin-limited select").removeAttr('disabled');
		}
	);
	
});