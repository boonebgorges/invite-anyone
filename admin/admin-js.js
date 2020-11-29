jQuery(document).ready( function() {
	var j = jQuery;
	
	/* Cloudsponge */
	var cstoggle = j("input#cloudsponge-enabled");
	var cscopy = j("#cloudsponge-copy");
	var cstable = j("div.cs-settings");
	var csSourcesStore = j("#csSourcesStore");
	var csSources = j("input[name='csSources']");
	
	if ( j(cstoggle).prop('checked') == false ) {
		j(cstable).hide();
	}

	csSources.change(function(){
		var csSourcesTemp = [];
		csSources.each( function () {
	        if(this.checked) {
	            csSourcesTemp.push(j(this).val());
	        }
	    });
	    csSourcesStore.val(csSourcesTemp.join());
	});
	
	j(cstoggle).click(function(){
		j(cstable).slideToggle(300);
	});	

	j(cscopy).click(
		function() {
			var copyText = document.getElementById("cloudsponge-proxy");
			copyText.select();
			copyText.setSelectionRange(0, 99999);
			document.execCommand("copy");
		}
	);
	
	/* Access settings */
	var toggle = j("input#invite_anyone_toggle_email_limit");
	var offtoggle = j("input#invite_anyone_toggle_email_no_limit");	
	var submitbutton = j("#invite-anyone-settings-submit");
	
	if ( j(toggle).prop('checked') == false ) {
		j("div.invite-anyone-admin-limited input").prop('disabled', 'disabled');
		j("div.invite-anyone-admin-limited select").prop('disabled', 'disabled');
		j("div.invite-anyone-admin-limited").css('color', '#999');
		j("div.invite-anyone-admin-limited input").css('color', '#999');
	}	

	j(offtoggle).click(
		function() {
			j("div.invite-anyone-admin-limited input").prop('disabled', 'disabled');
			j("div.invite-anyone-admin-limited select").prop('disabled', 'disabled');
			
			j("div.invite-anyone-admin-limited").css('color', '#999');
			j("div.invite-anyone-admin-limited input").css('color', '#999');
		}
	);

	j(toggle).click(
		function() {
			j("div.invite-anyone-admin-limited input").prop('disabled',false);
			j("div.invite-anyone-admin-limited select").prop('disabled',false);
			
			j("div.invite-anyone-admin-limited").css('color', '#000');	
			j("div.invite-anyone-admin-limited input").css('color', '#000');
		}
	);
	
	/* Undisables inputs and selects on form submit, so that WP saves the disabled options */
	j(submitbutton).click(
		function() {
			j("div.invite-anyone-admin-limited input").prop('disabled',false);
			j("div.invite-anyone-admin-limited select").prop('disabled',false);
		}
	);
	
});