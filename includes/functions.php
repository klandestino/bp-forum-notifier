<?php

function bp_forum_notifier_the_mail_subscription_button( $group_id = '' ) {
	echo bp_forum_notifier_get_mail_subscription_button( $group_id );
}

function bp_forum_notifier_get_mail_subscription_button( $group_id = '' ) {
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

		return sprintf( '<span id="bp-forum-notifier-wrapper"><a href="#" class="subscription-toggle" id="bp-forum-notifier-toggle-subscription" data-nonce="%s" data-group_id="%d" data-action="%s">%s</a></span>', $nonce, $group_id, $action, $string );
	}
}
add_action( 'bbp_template_before_single_forum', 'bp_forum_notifier_the_mail_subscription_button' );

function bp_forum_notifier_toggle_subscription() {
	check_ajax_referer( 'bp-forum-notifier-toggle-subscription', 'nonce' );

	$group_id = absint( $_POST['group_id'] );

	if ( groups_is_user_member( bp_loggedin_user_id(), $group_id ) ) {
		if ( ! $users = groups_get_groupmeta( $group_id, 'bp-forum-notifier-mail-unsubscribe' ) ) {
			$users = array();
		}
		if ( $_POST['subscribe_or_unsubscribe'] == 'unsubscribe' && ! in_array( bp_loggedin_user_id(), $users ) ) {
			$users = array_push( $users, bp_loggedin_user_id() );
		} elseif ( $_POST['subscribe_or_unsubscribe'] == 'subscribe' && is_int( $key = array_search( bp_loggedin_user_id(), $users ) ) ) {
			unset( $users[$key] );
		}
		groups_update_groupmeta( $group_id, 'bp-forum-notifier-mail-unsubscribe', (array) $users );
		echo bp_forum_notifier_get_mail_subscription_button( $group_id );
		die();
	} else {
		echo '-1';
		die();
	}
}
add_action( 'wp_ajax_bp_forum_notifier_toggle_subscription', 'bp_forum_notifier_toggle_subscription' );

?>