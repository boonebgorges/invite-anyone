/*
 * jQuery plugin: autoCompletefb(AutoComplete Facebook)
 * @requires jQuery v1.2.2 or later
 * using plugin:jquery.autocomplete.js
 *
 * Credits:
 * - Idea: Facebook
 * - Guillermo Rauch: Original MooTools script
 * - InteRiders <http://interiders.com/> 
 *
 * Copyright (c) 2008 Widi Harsojo <wharsojo@gmail.com>, http://wharsojo.wordpress.com/
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */

jQuery.fn.autoCompletefb = function(options) 
{
	var tmp = this;
	var settings = 
	{
		ul         : tmp,
		urlLookup  : [""],
		acOptions  : {},
		//foundClass : ".friend-tab",
		inputClass : ".send-to-input"
	}
	
	if(options) jQuery.extend(settings, options);
	
	/*var acfb = 
	{
		params  : settings,
		removeFind : function(o){
			acfb.removeUsername(o);
			jQuery(o).unbind('click').parent().remove();
			jQuery(settings.inputClass,tmp).focus();
			return tmp.acfb;
		},
		removeUsername: function(o){
			var newID = o.parentNode.id.split('-');
			jQuery('#send-to-usernames').removeClass(newID[1]);
		}
	}*/
	
	/*jQuery(settings.foundClass+" img.p").click(function(){
		acfb.removeFind(this);
	});*/
				
	jQuery(settings.inputClass,tmp).autocomplete(settings.urlLookup,settings.acOptions);
	jQuery(settings.inputClass,tmp).result(function(e,d,f){
		//var f = settings.foundClass.replace(/\./,'');
		var z = String(d).indexOf('user-');
		var z = String(d).substring(z+5);
		var y = z.indexOf('-');
		var friend_id = z.substring(0,y);
		var d = String(d).split(' (');
		var un = d[1].substr(0, d[1].length-1);
		var friend_action = 'invite';
		
		jQuery.post( ajaxurl, {
				action: 'invite_anyone_groups_invite_user',
				'friend_action': friend_action,
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': jQuery("input#_wpnonce_invite_uninvite_user").val(),
				'friend_id': friend_id,
				'group_id': jQuery("input#group_id").val()
			},
			function(response)
			{ 
				if ( jQuery("#message") )
					jQuery("#message").hide();

				jQuery('.ajax-loader').toggle();

				if ( friend_action == 'invite' ) {
					jQuery('#invite-anyone-invite-list').append(response);
				} else if ( friend_action == 'uninvite' ) {
					jQuery('#invite-anyone-invite-list li#uid-' + friend_id).remove();
				}
			});
		
		
		
		
		jQuery('#invite-anyone-member-list input#f-' + friend_id).attr('checked', true);
		var v = '';
		var x = jQuery(settings.inputClass,tmp).before(v);
		jQuery(settings.inputClass,tmp).val('');
	});
	
	
	
	
	
	jQuery(settings.inputClass,tmp).focus();
	//return acfb;
}
