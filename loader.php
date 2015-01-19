<?php

/*
Plugin Name: BuddyPress Forum Notifier
Plugin URI: https://github.com/klandestino/
Description: Sends on-site notifications on forum subscriptions
Version: 1.4.2
Requires at least: 3.4.2
Tested up to: 4.1
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

if ( bp_forum_notifier_notify_on_all_replies() ) :
	// Custom plugin functions for new notify on all replies functionality
	require_once( dirname( __FILE__ ) . '/includes/functions.php' );
endif;

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

// Enqueues js if new notify on all replies functionality is activated
function bp_forum_notifier_enqueue_scripts() {
	if ( bp_forum_notifier_notify_on_all_replies() && bp_is_groups_component() && bp_is_current_action( 'forum' ) ) {
		wp_enqueue_script( 'bp-forum-notifier', WP_PLUGIN_URL . '/' . basename( __DIR__ ) . '/js/bp-forum-notifier.js', array( 'jquery' ), '1.4', true );
	}
}
add_action( 'wp_enqueue_scripts', 'bp_forum_notifier_enqueue_scripts' );

/**
 * Check if admin has enabled the "new functionality" for notifying group members on all forum replies
 * @return bool
 */
function bp_forum_notifier_notify_on_all_replies() {
	$settings = get_option( 'bp_forum_notifier_settings', get_site_option( 'bp_forum_notifier_settings', array() ) );

	if ( $settings['notifications-for-all-replies'] == 'on' ) {
		return true;
	} else {
		return false;
	}
}