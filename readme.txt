=== Invite Anyone ===
Contributors: boonebgorges, cuny-academic-commons
Donate link: http://teleogistic.net/donate
Tags: buddypress, invitations, group, invite, friends, members
Requires at least: 3.2
Tested up to: 6.6
Stable tag: 1.4.10
License: GPLv3

Makes BuddyPress's invitation features more powerful.

== Description ==

Invite Anyone has two components:

1) The ability to invite members to the site by email. The plugin creates a tab on each member's Profile page called "Send Invites", which contains a form where users can invite outsiders to join the site. There is a field for a custom message. Also, inviters can optionally select any number of their groups, and when the invitee accepts the invitation he or she automatically receive invitations to join those groups.

The email invitation part of the plugin is customizable by the BP administrator, via Dashboard > BuddyPress > Invite Anyone.

2) By default, BuddyPress only allows group admins to invite their friends to groups. In some communities, you might want members to be able to invite non-friends to groups as well. This plugin allows you to do so, by populating the invitation checklist with the entire membership of the site, rather than just a friend list.

Because member lists can get very long and hard to navigate, this plugin adds a autosuggest search box to the Send Invites screen - the same one that appears on the Compose Message screen - which allows inviters to navigate directly to the members they want to invite.

Invite Anyone features optional integration with CloudSponge http://cloudsponge.com, a premium address book service, that allows your users to invite their friends to the site in a way that's easy and fun. Enable it at Dashboard > BuddyPress > Invite Anyone.

== Installation ==

* Upload the directory '/invite-anyone/' to your WP plugins directory and activate from the Dashboard of the main blog. Some users have reported problems when activating the plugin sitewide, so consider activating it on the BP blog only.
* Configure the plugin at Dashboard > BuddyPress > Invite Anyone, where you can customize the default invitation message, determine which members are allowed to invite by email, and more. If you are running WordPress Multisite version 3.1 or greater, BuddyPress Dashboard panels are found in the Network Admin area.
* If you are upgrading from a version of Invite Anyone older than 0.8, your data will be migrated from a custom database table (usually wp_bp_invite_anyone) to WordPress custom post types. After upgrading, if you are satisfied that the automatic migration has gone successfully, you can safely archive and remove the wp_bp_invite_anyone table from your database.

== Translation credits ==

* Belarussian: Alexander Ovsov (<a href="http://webhostinggeeks.com/science">Web Geek Science</a>)
* Brazilian Portuguese: Celso Bessa
* Catalan: Mònica Grau and Toni Ginard
* Danish: Mort3n
* Dutch: Jesper Popma, Tim de Hoog
* French: Guillaume Coulon, Nicolas Mollet
* German: Lars Berning, Thorsten Wollenhöfer, Matthias Lunz
* Greek: Lena Stergatou
* Italian: Luca Camellini
* Norwegian: Stig Ulfsby
* Russian: Jettochkin, Roman Leonov
* Serbo-Croatian: Anja Skrba
* Spanish: Mauricio Camayo, Gregor Gimmy
* Swedish: Alexander Berthelsen, Jan Anderson
* Ukrainian: <a href="http://www.coupofy.com/">Ivanka</a>

Additional details about the plugin can be found in the following languages:
* Serbo-Croatian: <a href="http://science.webhostinggeeks.com/teleogistic">http://science.webhostinggeeks.com/teleogistic</a>

== Changelog ==

= 1.4.10 =
* Fixed bug that caused group Send Invites nav item to show to non-members

= 1.4.9 =
* Fixed regression in 1.4.8 that caused group invite template not to load correctly

= 1.4.8 =
* Security fix: Prevent XSS during AJAX autocomplete
* Security hardening
* Internal codebase improvements

= 1.4.7 =
* Fixed regression in 1.4.6 that may cause duplicate nav items in groups

= 1.4.6 =
* BuddyPress 12.0 compatibility
* Improved compatibility with PHP 8.0+
* Accessibility improvements to invitation checklists
* Fixed JS bug that might have prevented invitation checkboxes from working properly in some cases

= 1.4.5 =
* Fixed bug in routine responsible for saving the CloudSponge configuration settings.

= 1.4.4 =
* Fixed bug that prevented the installation of email templates.
* Improved performance during invitation creation by generating unique post slugs based on microtime.
* Improved compatibility with PHP 8+.

= 1.4.3 =
* Improvements to CloudSponge integration admin panel.

= 1.4.2 =
* Improvements to CloudSponge integration.
* Fixed styling bug on Sent Invites screen.

= 1.4.1 =
* Sane, overridable default for autocomplete minimum-character setting.
* Accessibility improvements.
* Duplicate admin panel link under the custom post type top-level item.
* Improve compatibliity with BP 5.0+.
* Fix PHP notices.

= 1.4.0 =
* Use BP's pretty email formatting system for outgoing email notifications.
* Fix bug that caused admin panel to be added to Network Admin in some incorrect situations.
* Improved PHP 7.2+ compatibility.

= 1.3.20 =
* Add parameters to filters on outgoing email parts.
* Improved compatibility with PHP 7.2.

= 1.3.19 =
* Security fix: Improved encoding of error cookies.

= 1.3.18 =
* Fix regression from 1.3.16 that caused some admin settings not to be sanitized correctly.

= 1.3.17 =
* Fix regression from 1.3.16 that caused admin-customized invitation messages to be corrupted on save.

= 1.3.16 =
* Security fix: Disallow manual bypass of Access settings when inviting users by email. Thanks to Plugin Vulnerabilities for discovering and privately reporting this and other security issues addressed in this release.
* Security fix: Improved CSRF protection in admin panels
* Security fix: Improved output escaping of user-provided content in the Dashboard and on the front end
* Fixed bug that prevented the 'email' sort from working on the Sent Invites screen.

= 1.3.15 =
* Security fix: Disallow manual overriding of non-customizable subject and message lines. Thanks to Ewoud Vlasselaer, Eric Schayes, and Nabeel Ahmed for discovering and privately reporting this issue.
* Improve usage of BP API functions

= 1.3.14 =
* Fixed regression in 1.3.13 that caused friend request invitations to be sent improperly on account activation

= 1.3.13 =
* Fixed broken link on Stats tab in the Dashboard
* Fixed bug that caused internal taxonomies to be indexable by search engines

= 1.3.12 =
* Better compatibility with HTTPS setups
* Fixed compatibility with WP < 4.0
* Fixed CloudSponge integration on the front-end

= 1.3.11 =
* Improvements to CloudSponge signup process

= 1.3.10 =
* Fixed bug that caused irregularities when searching for banned users to invite to group
* Added Catalan translation
* Updated CloudSponge integration to use support new account key format
* Improved compatibility with PHP 7
* Fixed PHP notice when doing LIKE query

= 1.3.9 =
* Don't make the Subject input a textarea if it's not editable
* More selective cookie deletion, for improved cache support
* Sort autocomplete results by display name rather than user_login
* Updated Swedish translation
* Ensure that group invitations go out properly during group creation

= 1.3.8 =
* Improved responsive design on Sent Invites and Send Invites screens
* Fixed PHP notices related to PHP4 constructors
* Removed Facebook from the Cloudsponge integration. See http://www.cloudsponge.com/blog/stories/2015/05/13/goodbye-facebook.html for more details.
* Fixed bug that caused multisite limited-domain checks to be case-sensitive

= 1.3.7 =
* Fixed incorrect function name in widget localization
* Improved localization support
* Fixed a bug that made it impossible to invite users with apostrophes in their email addresses

= 1.3.6 =
* Better loading of CloudSponge JavaScript, so that assets are only loaded when needed
* Fixed some PHP notices when creating new widgets

= 1.3.5 =
* Fix bug that caused inviters' names to appear incorrectly when accepting email invitation
* Fix bug that caused an incorrect "Are you sure?" browser notice during group creation

= 1.3.4 =
* BuddyPress 2.1 compatibility
* Fix bug that broke accept-invitation and opt-out pages on BuddyPress 2.1
* Improved handling for emails with plus signs
* Fix bug that prevented Opt Out button from submitting properly

= 1.3.3 =
* Improved appearance for autosuggest spinner
* Disable Submit button on group invitation page when there's nothing to submit, helping to avoid user error
* Warn users before leaving group invitation page without clicking Send Invites

= 1.3.2 =
* AJAX spinner when autosuggest request is in process
* Better loading of assets over SSL

= 1.3.1 =
* CloudSponge integration now has access to LinkedIn and Facebook address books

= 1.3 =
* Improved behavior after redirects
* Fix bug that prevented error messages from displaying on invitation page
* Fix bug that caused error messages to be reset when forbidden email addresses are entered
* Enforce BuddyPress's 'invite_status' group setting when adding Invite Anyone menu
* Enfore BP's 'invite_status' setting for individual groups when creating group checkboxes on email invitation page

= 1.2.1 =
* Allow is_large_network value to be filtered
* Localization improvements with pagination strings
* Update ru_RU

= 1.2 =
* Group creation step can now be toggled from admin panel
* Fix bug that prevented autocomplete from working properly during group creation
* Don't show group invite checkbox list if network is very large (rely on autocomplete only)
* Fix "headers already sent" warnings when IA is used in connection with certain plugins
* Fix "bypass registration lock" setting on recent versions of BP+Multisite
* Enforce registration lock when user manually changes email address after reaching accept-invitation page
* Fix incorrect links when removing newly created group invitations
* Improved localization on "accept-invitation" screen

= 1.1.1 =
* Fix some PHP notices
* Update nl_NL localization
* Fix bug with group invitation autocomplete in some BP/WP setups

= 1.1.0 =
* Fix bug in pagination after sending email invitations
* Fix broken sprite
* Improve compatibility with template overrides when using theme compatibility
* Improved responsiveness for group invitations
* Error data is stored in a cookie rather than the URL, for better support across hosting environments

= 1.0.26 =
* Allow + signs to appear in invited email addresses
* Improves icon appearance and performance
* Improves appearance of group list on Send Invites screen

= 1.0.25 =
* Fixes broken Sent Invites screen

= 1.0.24 =
* Allow the access to Send Invites to the site admin
* Better PHP 5.4+ adherence
* Fix bug that prevented invitation step from appearing in group creation on some setups

= 1.0.23 =
* Adds sr_RS
* Fixes bug that prevented the admin options from appearing on some multisite setups
* Allows locale and stylesheet options to be set via filter in Cloudsponge widget

= 1.0.22 =
* When sending a group invitation to a non-friend, remove the "One of your friends..." text from invitation content

= 1.0.21 =
* Fixed redirection bug after submitting group invites on some setups
* Fixed bug that caused incorrect accept-invitation links on some setups
* Updated sv_SE

= 1.0.20 =
* Added friendly redirects for logged-in users attempting to reaccept invitations
* Updated it_IT

= 1.0.19 =
* Better scaling of group member invite lists
* Fixes WP 3.5 $wpdb->prepare() issues

= 1.0.18 =
* Added Brazilian Portuguese translation
* Updated Russian translation
* Added filters to control whether friend/follow requests are sent on invitation acceptance

= 1.0.17 =
* Moved admin menu to under Settings
* Added a "maximum of x people" notice to the by-email form

= 1.0.16 =
* Added Danish translation
* Fixed bug with total email invites toggle

= 1.0.15 =
* BuddyPress 1.6 compatibility
* Better PHP 5.4+ compatibility

= 1.0.14 =
* Prevents call-by-reference errors on PHP 5.4 setups

= 1.0.13 =
* Prevents fatal errors on activation in some setups, by changing where the upgrade process is run

= 1.0.12 =
* Ensure that spammed/inactive users are not returned by group invite autocomplete
* Updates German translation

= 1.0.11 =
* Adds per-user invitation limit option. Props Jeradin
* Reworks the way that returned_data is returned, to avoid 'headers already sent' errors

= 1.0.10 =
* Updates French translation. Props Nicolas Mollet

= 1.0.9 =
* Fixes some annoying PHP notices

= 1.0.8 =
* Yet another attempt at fixing activation problem
* Adds Belarussian translation. Props Alexander Ovsov

= 1.0.7 =
* Another attempt at fixing problem that some users are having when activating plugin

= 1.0.6 =
* Fixes bug that showed invitation message on registration screen when no invitation was found
* Fixes bug that allowed users to bypass registration lock on some setups
* Fixes some PHP notices

= 1.0.5 =
* Adds filters to some settings fields
* Removes repeated sent_email_invite action
* Adds updated Spanish translation

= 1.0.4 =
* Fixes errant autocomplete for group invitations
* Prevents 404s for shadow image in autocomplet. Props defunctl
* Fixes issue with Remove Invite link for items added with AJAX. Props defunctl

= 1.0.3 =
* Removes unneeded code block. Props defunctl

= 1.0.2 =
* Fixes syntax error that caused "invalid header" errors when activated on some setups

= 1.0.1 =
* Fixes problem that prevented settings from being saved properly on 1.2.x
* Fixes Settings link on Plugins page

= 1.0 =
* Compatibility with BuddyPress 1.5
* Rewritten autocomplete script for group invitations
* Adds Spanish translation

= 0.9.3 =
* Fixed some PHP warnings on Manage Invitations and Stats panels
* Added a 'no invitations' message to Manage Invitations when none have been sent yet

= 0.9.2 =
* Fixed bug that caused settings from being properly saved in some cases

= 0.9.1 =
* Updated .pot file for translators

= 0.9 =
* Revamped admin screens, including admin view of all sent invites and stats about invitations
* Improved support for sorting by accepted date and by email address
* Pagination added to Sent Invites screen
* Fixed bug that caused Send Invites button to appear incorrectly on group create screen in some cases
* Fixed bug that caused group create form not to submit in some browsers

= 0.8.9 =
* Fixed bug that made Cloudsponge scripts load even when CS integration was turned off
* Fixed bug that prevented Cloudsponge authorization to happen because of a problem in script loading order
* Cleaned up markup in the admin panel

= 0.8.8 =
* Added an icon to the custom post type. Props Bowe for whipping it up
* Fixed bug that caused sent invites to be recorded as sent at GMT rather than properly offset for time zone
* Refactored the widget to use a single email box, like the regular invites page
* Put the CloudSponge link into the widget

= 0.8.7 =
* Fixed bug where an undeclared global was causing the custom post type not to be loaded on multisite installations

= 0.8.6 =
* Fixed bug that made update nav appear on non-root-blogs of Multisite installations
* Fixed bug that made CPT register on non-root-blogs on Multisite, which meant that there was a confusing empty BuddyPress Invitations section

= 0.8.5 =
* Moved group invitations tab content into a separate template file so that it can be easily overridden in a theme (with a file at [theme]/groups/single/invite-anyone.php).

= 0.8.4 =
* Fixed bug that caused update nag to show after updating options in some cases

= 0.8.3	=
* Improved support for migrating large numbers of legacy invitations

= 0.8.2 =
* Adds German translation - props Lars Berning

= 0.8.1 =
* Fixes the way admin menus are hooked to ensure compatibility with WP Multisite < 3.1 and BP < 1.2.8

= 0.8 =
* Integration with CloudSponge, which allows users to pull email addresses from their address books for sending email invitations
* Sent invitation data has been converted to custom post types, which gives admins an easy way to manage invitations from the WP Dashboard

= 0.7.1 =
* Norwegian translation added - props Stig Ulfsby
* Greek translation added - props Lena Stergatou
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
