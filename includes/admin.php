<?php

class BP_Forum_Notifier_Admin {

	static public function __setup() {
		add_action( 'init', array( BP_Forum_Notifier_Admin, 'init' ) );
	}

	static public function init() {
		if( is_super_admin() ) {
			add_action( 'admin_init', array( BP_Forum_Notifier_Admin, 'admin_page_save' ) );
			add_action( 'admin_menu', array( BP_Forum_Notifier_Admin, 'admin_menu' ) );
		}
	}

	/**
	 * Retrieves current settings
	 * @return array
	 */
	static public function get_settings() {
		$settings = get_option( 'bp_forum_notifier_settings', get_site_option( 'bp_forum_notifier_settings', array() ) );
		$defaults = array(
			'mail-delay' => '15',
			'multiple-mail-messages-subject' => __( '[%1$s] %2$d new forum activities', 'bp-forum-notifier' ),
			'reply-notification-single' => __( '%2$s wrote a new reply in %3$s', 'bp-forum-notifier' ),
			'reply-notification-multi' => __( '%2$d new replies in %3$s', 'bp-forum-notifier' ),
			'reply-mail-subject-single' => __( '[%1$s] One new reply in %3$s', 'bp-forum-notifier' ),
			'reply-mail-subject-multi' => __( '[%1$s] %2$d new replies in %3$s', 'bp-forum-notifier' ),
			'reply-mail-message-line' => __( '%1$s wrote a reply in %2$s:
%3$s
Post Link: %4$s', 'bp-forum-notifier' ),
			'topic-notification-single' => __( '%2$s wrote a new topic in %3$s', 'bp-forum-notifier' ),
			'topic-notification-multi' => __( '%2$d new topics in %3$s', 'bp-forum-notifier' ),
			'topic-mail-subject-single' => __( '[%1$s] One new topic in %3$s', 'bp-forum-notifier' ),
			'topic-mail-subject-multi' => __( '[%1$s] %2$d new topics in %3$s', 'bp-forum-notifier' ),
			'topic-mail-message-line' => __( '%1$s wrote a topic in %2$s:
%3$s
Post Link: %4$s', 'bp-forum-notifier' ),
			'quote-notification-single' => __( '%2$s quoted you in %3$s', 'bp-forum-notifier' ),
			'quote-notification-multi' => __( 'You\'ve been quoted %2$d times in %3$s', 'bp-forum-notifier' ),
			'quote-mail-subject-single' => __( '[%1$s] You\'ve been quoted in %3$s', 'bp-forum-notifier' ),
			'quote-mail-subject-multi' => __( '[%1$s] You\'ve been quoted %2$d times in %3$s', 'bp-forum-notifier' ),
			'quote-mail-message-line' => __( '%1$s quoted you in %2$s:
%3$s
Post Link: %4$s', 'bp-forum-notifier' ),
			'mail-message-wrap' => __( '%1$s

--------------------

You are receiving this email because you subscribed to a forum topic.

Login and visit the topic to unsubscribe from these emails.', 'bp-forum-notifier' )
		);

		if( is_array( $settings ) ) {
			foreach( $defaults as $key => $val ) {
				if( array_key_exists( $key, $settings ) ) {
					$defaults[ $key ] = $settings[ $key ];
				}
			}
		}

		return $defaults;
	}

	/**
	 * Adds menu item
	 * @return void
	 */
	public static function admin_menu() {
		add_submenu_page(
			'options-general.php',
			__( 'Forum Notifier Settings', 'bp-forum-notifier' ),
			__( 'Forum Notifier', 'bp-forum-notifier' ),
			'manage_options',
			'forum-notifier',
			array( BP_Forum_Notifier_Admin, 'admin_page' )
		);
	}

	/**
	 * Prints an admin page through template
	 * @return void
	 */
	public static function admin_page() {
		global $settings;
		$settings = self::get_settings();
		BP_Forum_Notifier::get_template( 'bp-forum-notifier-admin' );
	}

	/**
	 * Receives the posted admin form and saved the settings
	 * @return void
	 */
	public static function admin_page_save() {
		if( array_key_exists( 'forum-notifier-save', $_POST ) ) {
			check_admin_referer( 'bp_forum_notifier_admin' );
			$settings = self::get_settings();
			
			foreach( $settings as $key => $val ) {
				if( array_key_exists( $key, $_POST ) ) {
					$settings[ $key ] = $_POST[ $key ];
				}
			}

			update_option( 'bp_forum_notifier_settings', $settings );
			wp_redirect( add_query_arg( array( 'forum-notifier-updated' => '1' ) ) );
		} elseif( array_key_exists( 'forum-notifier-updated', $_GET ) ) {
			add_action( 'admin_notices', create_function( '', sprintf(
				'echo "<div class=\"updated\"><p>%s</p></div>";',
				__( 'Settings updated.', 'bp-forum-notifier' )
			) ) );
		}
	}

}
