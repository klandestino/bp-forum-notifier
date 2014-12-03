<?php

function bp_forum_notifier_the_mail_subscription_button( $group_id = '' ) {
	echo bp_forum_notifier_get_mail_subscription_button( $group_id );
}

/**
 * Returns the button for toggling per-group-forum subscriptions
 */

function bp_forum_notifier_get_mail_subscription_button( $group_id = '', $js = false ) {
	if ( function_exists( 'is_buddypress' ) && is_buddypress() ) {
		$nonce = wp_create_nonce( 'bp-forum-notifier-toggle-subscription' );
		if ( ! $group_id ) {
			$group_id = bp_get_group_id();
		}
		$string = __( 'Unsubscribe to new topics & replies', 'bp-forum-notifier' );
		$action = 'unsubscribe';

		if ( $users = groups_get_groupmeta( $group_id, 'bp-forum-notifier-mail-unsubscribe' ) ) {
			if ( in_array( bp_loggedin_user_id(), $users ) ) {
				$string = __( 'Subscribe to new topics & replies', 'bp-forum-notifier' );
				$action = 'subscribe';
			}
		}

		if ( ! $js ) {
			return sprintf( '<span id="bp-forum-notifier-wrapper"><a href="#" class="subscription-toggle" id="bp-forum-notifier-toggle-subscription" data-nonce="%s" data-group_id="%d" data-action="%s">%s</a></span>', $nonce, $group_id, $action, $string );
		} else {
			return sprintf( '<a href="#" class="subscription-toggle" id="bp-forum-notifier-toggle-subscription" data-nonce="%s" data-group_id="%d" data-action="%s">%s</a>', $nonce, $group_id, $action, $string );
		}

	}
}
add_action( 'bbp_template_before_single_forum', 'bp_forum_notifier_the_mail_subscription_button' );

/**
 * Handles the ajax request for toggling subscriptions on a per-group-forum basis
 */

function bp_forum_notifier_toggle_subscription() {
	check_ajax_referer( 'bp-forum-notifier-toggle-subscription', 'nonce' );

	$group_id = absint( $_POST['group_id'] );

	if ( groups_is_user_member( bp_loggedin_user_id(), $group_id ) ) {
		$users = groups_get_groupmeta( $group_id, 'bp-forum-notifier-mail-unsubscribe' );
		if ( $_POST['subscribe_or_unsubscribe'] == 'unsubscribe' && ! in_array( bp_loggedin_user_id(), $users ) ) {
			$users[] = bp_loggedin_user_id();
		} elseif ( $_POST['subscribe_or_unsubscribe'] == 'subscribe' && is_int( $key = array_search( bp_loggedin_user_id(), $users ) ) ) {
			unset( $users[$key] );
		}
		groups_update_groupmeta( $group_id, 'bp-forum-notifier-mail-unsubscribe', (array) $users );
		echo bp_forum_notifier_get_mail_subscription_button( $group_id, true );
		die();
	} else {
		echo '-1';
		die();
	}
}
add_action( 'wp_ajax_bp_forum_notifier_toggle_subscription', 'bp_forum_notifier_toggle_subscription' );

/**
 * Remove bbpress' notifications if on a group forum since this plugin takes care of those...
 */
function bp_forum_notifier_maybe_remove_bbp_notifications() {
	if ( bp_forum_notifier_notify_on_all_replies() && bp_is_groups_component() && bp_is_current_action( 'forum' ) ) {
		remove_action( 'bbp_new_reply', 'bbp_buddypress_add_notification', 10, 7 );
	}
}
add_action( 'bbp_new_reply', 'bp_forum_notifier_maybe_remove_bbp_notifications', 1 );

?>