jQuery(document).ready( function() {
	var j = jQuery;

j("div#invite-anyone-member-list input").click(
		function() {
		
			j('.ajax-loader').toggle();

			var friend_id = j(this).val();

			if ( j(this).attr('checked') == true ) {
				var friend_action = 'invite';
			} else {
				var friend_action = 'uninvite';
			}

			j.post( ajaxurl, {
				action: 'invite_anyone_groups_invite_user',
				'friend_action': friend_action,
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': j("input#_wpnonce_invite_uninvite_user").val(),
				'friend_id': friend_id,
				'group_id': j("input#group_id").val()
			},
			function(response)
			{
				if ( j("#message") )
					j("#message").hide();

				j('.ajax-loader').toggle();

				if ( friend_action == 'invite' ) {
					j('#invite-anyone-invite-list').append(response);
				} else if ( friend_action == 'uninvite' ) {
					j('#invite-anyone-invite-list li#uid-' + friend_id).remove();
				}
			});
		}
	);

	j("#invite-anyone-invite-list li a.remove").livequery('click',
		function() {
			j('.ajax-loader').toggle();

			var friend_id = j(this).attr('id');

			friend_id = friend_id.split('-');
			friend_id = friend_id[1];

			j.post( ajaxurl, {
				action: 'invite_anyone_groups_invite_user',
				'friend_action': 'uninvite',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': j("input#_wpnonce_invite_uninvite_user").val(),
				'friend_id': friend_id,
				'group_id': j("input#group_id").val()
			},
			function(response)
			{
				j('.ajax-loader').toggle();
				j('#invite-anyone-invite-list li#uid-' + friend_id).remove();
				j('#invite-anyone-member-list input#f-' + friend_id).attr('checked', false);
			});

			return false;
		}
	);
	
	j("#invite-anyone-link").click(
		function() {
		
			j('.ajax-loader').toggle();

			var friend_id = j(this).val();

			if ( j(this).attr('checked') == true ) {
				var friend_action = 'invite';
			} else {
				var friend_action = 'uninvite';
			}
		

			j.post( ajaxurl, {
				action: 'invite_anyone_groups_invite_user',
				'friend_action': friend_action,
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': j("input#_wpnonce_invite_uninvite_user").val(),
				'friend_id': friend_id,
				'group_id': j("input#group_id").val()
			},
			function(response)
			{ 
				if ( j("#message") )
					j("#message").hide();

				j('.ajax-loader').toggle();

				if ( friend_action == 'invite' ) {
					j('#invite-anyone-member-list').append(response);
				} else if ( friend_action == 'uninvite' ) {
					j('#invite-anyone-member-list li#uid-' + friend_id).remove();
				}
			});
		}
	);

	
});