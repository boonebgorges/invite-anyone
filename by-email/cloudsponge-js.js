csInit( { 	
	domain_key: ia_cloudsponge.domain.key,
	referrer: 'invite-anyone',
	afterSubmitContacts:function(contacts) {
		var emails = [];
		var contact, name, email;
		for(var i=0; i < contacts.length; i++) {
			contact = contacts[i];
			name = contact.fullName();
			email = contact.selectedEmail();
			emails.push(email);
		}
		
		var textarea = document.getElementById('invite-anyone-email-addresses');
		/* Strip any manually entered whitespace */
		var already_emails = textarea.value.replace(/^\s+|\s+$/g,"");
		
		var new_emails;
		if ( already_emails == false ) {
			new_emails = emails.join("\n");
		} else {
			new_emails = already_emails + "\n" + emails.join("\n")
		}
		document.getElementById('invite-anyone-email-addresses').value = new_emails;
	}
} );