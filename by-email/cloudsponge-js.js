
if ( ia_cloudsponge.account_key ) {
	(function(u){
	  var d=document,s='script',a=d.createElement(s),m=d.getElementsByTagName(s)[0];
	  a.async=1;a.src=u;m.parentNode.insertBefore(a,m);
	})('//api.cloudsponge.com/widget/'+ia_cloudsponge.account_key+'.js');
}

var csPageOptions = {
	referrer: 'invite-anyone',
	sources: [ 'linkedin', 'yahoo', 'gmail', 'windowslive', 'aol', 'plaxo', 'addressbook', 'outlook' ],
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
		var new_emails_for_input;
		if ( already_emails == false ) {
			new_emails = emails.join("\n");
			new_emails_for_input = emails;
		} else {
			new_emails = already_emails + "\n" + emails.join("\n")
			new_emails_for_input = already_emails.replace(/\s/,',') + ',' + emails;
		}
		document.getElementById('invite-anyone-email-addresses').value = new_emails;
		document.getElementById('cloudsponge-emails').value = new_emails_for_input;
	}
}

if ( ia_cloudsponge.domain_key ) {
	csPageOptions.domain_key = ia_cloudsponge.domain_key;
}


if ( ia_cloudsponge.locale ) {
	cloudsponge.init( { locale: 'es' } );
}

if ( ia_cloudsponge.stylesheet ) {
	cloudsponge.init( { stylesheet: 'http://foo.com' } );
}
