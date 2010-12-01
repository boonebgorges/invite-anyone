=== Plugin Name ===
Contributors: boonebgorges, cuny-academic-commons
Donate link: http://teleogistic.net/donate
Tags: buddypress, invitations, group, invite, friends, members
Requires at least: WP 2.8, BuddyPress 1.1
Tested up to: WP 3.0.1, BuddyPress 1.2.7
Stable tag: 0.7.1

Makes BuddyPress's invitation features more powerful.

== Description ==

Invite Anyone has two components:

1) The ability to invite members to the site by email. The plugin creates a tab on each member's Profile page called "Send Invites", which contains a form where users can invite outsiders to join the site. There is a field for a custom message. Also, inviters can optionally select any number of their groups, and when the invitee accepts the invitation he or she automatically receive invitations to join those groups.

The email invitation part of the plugin is customizable by the BP administrator, via Dashboard > BuddyPress > Invite Anyone.

2) By default, BuddyPress only allows group admins to invite their friends to groups. In some communities, you might want members to be able to invite non-friends to groups as well. This plugin allows you to do so, by populating the invitation checklist with the entire membership of the site, rather than just a friend list.

Because member lists can get very long and hard to navigate, this plugin adds a autosuggest search box to the Send Invites screen - the same one that appears on the Compose Message screen - which allows inviters to navigate directly to the members they want to invite.

== Installation ==

* Upload the directory '/invite-anyone/' to your WP plugins directory and activate from the Dashboard of the main blog. Some users have reported problems when activating the plugin sitewide, so consider activating it on the BP blog only.
* Configure the plugin at Dashboard > BuddyPress > Invite Anyone, where you can customize the default invitation message, determine which members are allowed to invite by email, and more.


== Translation credits ==

* Dutch: Jesper Popma
* Italian: Luca Camellini
* French: Guillaume Coulon
* Russian: Jettochkin
* Norwegian: Stig Ulfsby

== Changelog ==

= 0.7.1 =
* Norwegian translation added - props Stig Ulfsby
* Fixed bug that made group creation bypass IA settings
* Fixed bug that broke the way that the BP core (friends only) tab rendered

= 0.7 =
* Big markup improvements to email invitations screens - huge props hnla
* Toggle to allow email invitation and registration when general registration is turned off

= 0.6.7 =
* Added hooks to provide support for Achievements
* Improved checking for deactivated components
* BuddyPress Followers support

= 0.6.6 = 
* Updated hooks to work with more recent versions of BuddyPress
* Increased number of results returned to user on group invite autocomplete

= 0.6.5 =
* Workaround for "headers already sent" issue on group invites
* Fixed a number of variable type problems with email invitation pages

= 0.6.4 =
* Fixed bug that kept item group invitations from being sent
* Fixed bug that prevented Send Invites profile tab from being hidden when access control was set to Administrator

= 0.6.3 =
* Fixed bug that showed non-activated users in group invitation list on some instances of single WP
* Fixed bug that limited number of displayed groups on invite by email screen
* Cleaned up the appearance of the group list on the invite by email screen
* Fixed bug that may have cause foreach problem in email invitation

= 0.6.2 =
* Fixed bug that kept group invitation member list from being populated on some non-MU setups
* Fixed bug that kept non-admins from seeing Send Invites group tab
* Fixed bug that prevented JS and CSS from loading on invitation step in group creation
* Fixed bug that caused email fields not to load properly in IE - thanks, techguy!
* Added do_action hooks for other plugins (eg Cubepoints) to access
* Added filter on acceptance URL and action hook before accept invitation screen for plugins to access

= 0.6.1 =
* Added checks to allow email invitations to work when groups component is disabled
* Fixed l18n bugs with error messages
* French translation added - thanks, Guillaume!
* Russian translation added - thanks, Jettochkin!
* Updated translations

= 0.6 =
* Plugin now includes a widget for email invitation from any page
* Sent Invites sortable by email address, date invited, date accepted
* Invites can be cleared from Sent Invites list: individually, all accepted, all invitations
* Created admin controls over who group admins/mods/members can invite to groups
* Admins can now allow customization of invitation's main message but still have control over a non-editable footer
* CSS issues fixed

= 0.5.2 =
* Added Italian translation (thanks, Luca!)
* Removed "Want to invite..." prompt from Send Invites screen during group creation
* Attempted a fix for certain in_array errors in css/js loader file

= 0.5.1 =
* Fixed bug with subject/message content when email is returned as an error
* Fixed error with email error messages when no groups were selected
* Changed width of textareas on Invite New Members tab

= 0.5 =
* Enabled Opt Out option for invitees
* Subject line is now customizable by the admin
* Admin can toggle whether users can customize subject line and message body of invitation emails
* Some localization bugs fixed
* Filtered spammers from group invitation list
* Fixed bug that may have caused problems with some MU limited email domain lists
* Email Address field is now auto-populated on Accept Invitation screen
* Created admin toggle for group invites attached to email screen
* Added hook for additional fields on Invite New Members screen (as well as a hook for processing the additional data)

= 0.4.1 =
* Fixed problem with email validation causing fatal errors on single WP
* Fixed bug that allows members to see Send Invites tab on profiles other than their own

= 0.4 =
* New feature: Invite by email from new Send Invites profile tab
* Links from group invite pages to Invite By Email page
* Removed "Send Invites" button during group creation

= 0.3.5 =
* Corrected localization function (d'oh)
* Added Dutch translation - thanks, Jesper!

= 0.3.4 =
* Added POT file and localization function

= 0.3.3 =
* Fixed bug that kept non-active users from appearing in member list

= 0.3.2 =
* Made it possible to use the plugin with friends component turned off
* Turned off Site Wide Only to remove PHP errors on some subdomain blogs

= 0.3.1 =
* Added a "successfully created" message when no invites are sent on group creation

= 0.3 =
* Compatibility with BP 1.2.1, including new bp-default theme
* Rearranged files to ensure BP is loaded before plugin is

= 0.2 =
* Compatibility with BP 1.2 trunk
* Bugfixes regarding file locations

= 0.1 =
* Initial release