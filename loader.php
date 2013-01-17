<?php

/*
Plugin Name: BuddyPress Forum Notifier
Plugin URI: https://github.com/klandestino/
Description: Sends on-site notifications on forum subscriptions
Version: 1.3
Requires at least: 3.4.2
Tested up to: 3.5
License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
Author: spurge
Author URI: https://github.com/spurge
*/

// Where am I?
$plugin_dir = ! empty( $network_plugin ) ? $network_plugin : $plugin;

// Set symlink friendly dir constant
define( 'BP_FORUM_NOTIFIER_PLUGIN_DIR', dirname( $plugin_dir ) );

// Where to find plugin templates
// Used by BP_Forum_Notifier::get_template
define( 'BP_FORUM_NOTIFIER_TEMPLATE_DIR', dirname( __FILE__ ) . '/templates' );

// Handles mail events
require_once( dirname( __FILE__ ) . '/includes/mailer.php' );
BP_Forum_Notifier_Mailer::__setup();

// Add admin interface if current user is a site admin
require_once( dirname( __FILE__ ) . '/includes/admin.php' );
BP_Forum_Notifier_Admin::__setup();

/**
 * Initiates this plugin by setting up the forum notifier component.
 * @return void
 */
function bp_forum_notifier_init() {
	if( version_compare( BP_VERSION, '1.3', '>' ) ) {
		// Buddypress component that handles the notifications
		require_once( dirname( __FILE__ ) . '/includes/notifier.php' );
		BP_Forum_Notifier::__setup();
	}
}

// Setup component with bp_setup_components action
add_action( 'bp_setup_components', 'bp_forum_notifier_init' );

/**
 * Adds forum notifier component to the active components list.
 * This is a must do if we want the notifications to work.
 * @param array $components alread activated components
 * @return array
 */
function bp_forum_notifier_add_active_component( $components ) {
	return array_merge( $components, array( 'forum_notifier' => true ) );
}

// Setup active components with bp_active_components filter
add_filter( 'bp_active_components', 'bp_forum_notifier_add_active_component' );
