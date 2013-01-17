=== BuddyPress Forum Notifier ===

Contributors: spurge, lakrisgubben, alfreddatakillen
Tags: buddypress, groups, forum, bbpress, notifications
Requires at least: WordPress 3.4.2, BuddyPress 1.6.1, BBPress 2.2
Tested up to: WordPress 3.5, BuddyPress 1.6.2, BBPress 2.2.3
Stable tag: 1.3

Sends on-site notifications on forum events that bbpress doesn't handle.

== Description ==

* Sends notifications using buddpress notification system for new
  replies in subscribed topics, new topics in groups forums.
* Notifications will also be sent as emails, but can be turned of in
  their settings.
* All notification messages are editable.
* Notification emails can be set to be delayed.
* Notification emails can also be merged into one if more than one forum
  activity has occured before it's been sent.

= Available languages =

* English (built-in)
* Swedish

== Installation ==

* Download and upload the plugin to your plugins folder. Then activate
  it in your network administration.
* All the settings is available in the network administration settings
  pages.
* Setup a cron-job calling your wp-cron.php if you want the email
  delay feature to work properly.

== Changelog ==

= v1.3 =

* Fixed issue with double notifications and emails when quoting a
  subscriber.
* Checking if user is group member or allowed to read in hidden or
  private forums before sending notifications.
* Fixed links to right replies in topics.
* Deleting notifications when entering topics.

= v1.2 =

* Fixed issue with admins and moderators not getting group forum
  notifications.
* Fixes issue with author name not visible in new topic notifications.
* Plugin is not a network plugin anymore.
* Moved settings from network settings to general options.
