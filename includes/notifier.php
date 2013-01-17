<?php

/**
 * Notifier component
 */
class BP_Forum_Notifier extends BP_Component {

	/**
	 * Forum notifier component setup. Creates component object
	 * and inserts it in buddpress.
	 */
	static public function __setup() {
		global $bp;
		$bp->forum_notifier = new BP_Forum_Notifier();
	}

	/**
	 * Settings
	 */
	public $settings = array();

	/**
	 * Start the notifier component creation process
	 */
	public function __construct() {
		parent::start(
			'forum_notifier',
			__( 'Forum Notifier', 'bp_forum_notifier' ),
			BP_PLUGIN_DIR
		);

		// Load settings
		$this->settings = BP_Forum_Notifier_Admin::get_settings();

		// Wordpress init
		add_action( 'init', array( &$this, 'init' ) );

		/**
		 * Actions and filters for notification adding and deleting
		 */

		// Add new topic notification when a new topic is created
		add_action( 'bbp_new_topic', array( &$this, 'add_topic_notification' ), 10, 4 );
		// Add new reply notification when a new reply is created
		add_action( 'bbp_new_reply', array( &$this, 'add_reply_notification' ), 10, 5 );
		// Delete all notifications connected to a forum, topic or reply.
		// Forums, topics and replies are posts with post_types forum, topic and reply.
		// That's why we're using the_post action.
		add_action( 'the_post', array( &$this, 'delete_notifications' ) );
		// Delete notifications if post is deleted
		add_action( 'delete_post', array( &$this, 'delete_notifications' ) );
		// Delete notifications if post is trashed
		add_action( 'wp_trash_post', array( &$this, 'delete_notifications' ) );

		/**
		 * Actions and filters for settings
		 */

		// Action run when displaying notification settings (enable or disable emails)
		add_action( 'bp_notification_settings', array( &$this, 'settings_screen' ) );
		// Filter that modifies new reply notification for topic subscribers subject within bbpress
		// Used atm to disable bbpress own e-mail notification as this plugin does it instead.
		add_filter( 'bbp_subscription_mail_title', array( &$this, 'reply_mail_subject' ), 2, 4 );
		// Filter that modifies new reply notification for topic subscribers message body within bbpress
		//add_filter( 'bbp_subscription_mail_message', array( &$this, 'reply_mail_message' ), 2, 4 );
	}

	/**
	 *
	 */
	public function init() {
		load_plugin_textdomain( 'bp-forum-notifier', false, plugin_basename( BP_FORUM_NOTIFIER_PLUGIN_DIR ) . "/languages/" );
	}

	/**
	 * Setting up buddypress component properties
	 * This is an override
	 * @return void
	 */
	public function setup_globals() {
		if ( ! defined( 'BP_FORUM_NOTIFIER_SLUG' ) ) {
			define( 'BP_FORUM_NOTIFIER_SLUG', $this->id );
		}

		$globals = array(
			'slug' => BP_FORUM_NOTIFIER_SLUG,
			'has_directory' => false,
			'notification_callback' => 'bp_forum_notifier_messages_format'
		);

		parent::setup_globals( $globals );
	}

	/**
	 * Locates and loads a template by using Wordpress locate_template.
	 * If no template is found, it loads a template from this plugins template
	 * directory.
	 * @see locate_template
	 * @param string $slug
	 * @param string $name
	 * @return void
	 */
	public static function get_template( $slug, $name = '' ) {
		$template_names = array(
			$slug . '-' . $name . '.php',
			$slug . '.php'
		);

		$located = locate_template( $template_names );

		if ( empty( $located ) ) {
			foreach( $template_names as $name ) {
				if ( file_exists( BP_FORUM_NOTIFIER_TEMPLATE_DIR . '/' . $name ) ) {
					load_template( BP_FORUM_NOTIFIER_TEMPLATE_DIR . '/' . $name, false );
					return;
				}
			}
		} else {
			load_template( $located, false );
		}
	}

	/**
	 * Adds a new topic notification
	 * At the moment this functions looks for a group connection and
	 * notifies all the group members.
	 * @uses bbp_new_topic action
	 * @see bbp_insert_topic
	 * @see add_action
	 * @param int $topic_id
	 * @param int $forum_id
	 * @param string $anonymous_data ???
	 * @param int $topic_author author user id
	 * @return void
	 */
	public function add_topic_notification( $topic_id, $forum_id, $anonymous_data, $topic_author ) {
		// Get groups if there are any
		$groups = get_post_meta( $forum_id, '_bbp_group_ids', array() );

		foreach( $groups as $group_ids ) {
			if( ! is_array( $group_ids ) ) {
				$group_ids = array( $group_ids );
			}

			foreach( $group_ids as $group_id ) {
				$users = groups_get_group_members( $group_id );
				// For some reason, admins and moderators are not included in the members array :(
				$users = array_merge( $users[ 'members' ], groups_get_group_admins( $group_id ), groups_get_group_mods( $group_id ) ) ;
				// Used for checking duplicates
				$sent = array();

				foreach( $users as $member ) {
					if( $member->user_id != $topic_author && ! in_array( $member->user_id, $sent ) ) {
						// Add for duplicate check
						$sent[] = $member->user_id;
						// Send
						bp_core_add_notification( $topic_id, $member->user_id, $this->id, 'new_topic_' . $topic_id, $forum_id );
						$this->add_notification_email( $member->user_id, 0, $topic_id, $forum_id, $topic_author, 'new_topic_' . $topic_id, 'notification_forum_group_new_topic' );
					}
				}

				unset( $sent );
				unset( $users );
			}
		}
	}

	/**
	 * Adds a new reply notification to all topic subscribers
	 * and eventually the quoted users if there are any.
	 * @uses bbp_new_reply action
	 * @see bbp_insert_reply
	 * @see add_action
	 * @param int $reply_id
	 * @param int $topic_id
	 * @param int $forum_id
	 * @param string $anonymous_data ???
	 * @param int $reply_author author user id
	 * @return void
	 */
	public function add_reply_notification( $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author ) {
		// Get topic subscribers
		$user_ids = bbp_get_topic_subscribers( $topic_id, true );

		if( is_array( $user_ids ) ) {
			foreach( $user_ids as $user_id ) {
				if( $user_id != $reply_author ) {
					bp_core_add_notification( $reply_id, $user_id, $this->id, 'new_reply_' . $topic_id, $topic_id );
					$this->add_notification_email( $user_id, $reply_id, $topic_id, $forum_id, $reply_author, 'new_reply_' . $topic_id, 'notification_forum_topic_subscribe' );
				}
			}
		}

		// See if there's any quotes
		$quote_user_id = get_post_meta( $reply_id, '_bbpqor_replyuserid' );

		if( is_array( $quote_user_id ) ) {
			foreach( $quote_user_id as $user_id ) {
				if( $user_id && $user_id != $reply_author ) {
					bp_core_add_notification( $reply_id, $user_id, $this->id, 'new_quote_' . $topic_id, $topic_id );
					$this->add_notification_email( $user_id, $reply_id, $topic_id, $forum_id, $reply_author, 'new_quote_' . $topic_id, 'notification_forum_quoted' );
				}
			}
		} elseif( is_numeric( $quote_user_id ) && $quote_user_id && $quote_user_id != $reply_author ) {
			bp_core_add_notification( $reply_id, $quote_user_id, $this->id, 'new_quote_' . $topic_id, $topic_id );
			$this->add_notification_email( $quote_user_id, $reply_id, $topic_id, $forum_id, $reply_author, 'new_quote_' . $topic_id, 'notification_forum_quoted' );
		}
	}

	/**
	 * Delete all notifications connected to post
	 * @param array|int $post
	 * @return void
	 */
	public function delete_notifications( $post ) {
		if( is_object( $post ) ) {
			$post_id = $post->ID;
		} elseif( is_array( $post ) ) {
			$post_id = $post[ 'ID' ];
		} elseif( is_numeric( $post ) ) {
			$post_id = $post;
		}

		if( isset( $post_id ) ) {
			bp_core_delete_notifications_by_type( get_current_user_id(), $this->id, 'new_topic_' . $post_id );
			bp_core_delete_notifications_by_type( get_current_user_id(), $this->id, 'new_reply_' . $post_id );
			bp_core_delete_notifications_by_type( get_current_user_id(), $this->id, 'new_quote_' . $post_id );
		}
	}

	/**
	 * Makes a reply notification email subject
	 * Disables bbpress own e-mail notification by returning an empty subject
	 * @param string $subject
	 * @param int $reply_id
	 * @param int $topic_id
	 * @param int $user_id
	 * @return string
	 */
	public function reply_mail_subject( $subject, $reply_id, $topic_id, $user_id ) {
		return false;

		/*
		$author = get_userdata( bbp_get_reply_author_id( $reply_id ) );
		return sprintf(
			$this->settings[ 'reply-mail-subject-single' ],
			get_option( 'blogname' ),
			$author->display_name,
			bbp_get_forum_title( $forum_id )
		);
		*/
	}

	/**
	 * Makes a reply notification email message
	 * @param string $subject
	 * @param int $reply_id
	 * @param int $topic_id
	 * @param int $user_id
	 * @return string
	 */
	public function reply_mail_message( $subject, $reply_id, $topic_id, $user_id ) {
		$author = get_userdata( bbp_get_reply_author_id( $reply_id ) );
		return sprintf(
			$this->settings[ 'mail-message-wrap' ],
			sprintf(
				$this->settings[ 'reply-mail-message-line' ],
				$author->display_name,
				bbp_get_topic_title( $topic_id ),
				strip_tags( bbp_get_reply_content( $reply_id ) ),
				bbp_get_topic_permalink( $topic_id )
			)
		);
	}

	/**
	 * Adds a notification email if user settings allows it
	 * E-mail params are stored in a user-meta array if the mail-delayed setting
	 * is set for later deliviery through wp_schedule_single_event.
	 * If mail-delay is not set, e-mail will be sent immediately.
	 * @param int $user_id
	 * @param int $item_id
	 * @param int $secondary_item_id
	 * @param string $action
	 * @param string $setting
	 * @return void
	 */
	public function add_notification_email( $user_id, $reply_id, $topic_id, $forum_id, $author_id, $action, $setting ) {
		if( bp_get_user_meta( $user_id, $setting, true ) != 'no' ) {
			if( $this->settings[ 'mail-delay' ] ) {
				add_user_meta( $user_id, 'bp_forum_notifier_emails', compact( 'reply_id', 'topic_id', 'forum_id', 'author_id', 'action', 'setting' ) );

				if( ! wp_next_scheduled( 'bp_forum_notifier_scheduled_email', $user_id ) ) {
					wp_schedule_single_event( microtime( true ) + ( ( ( int ) $this->settings[ 'mail-delay' ] ) * 60 ), 'bp_forum_notifier_scheduled_email', array( $user_id ) );
				}
			} else {
				BP_Forum_Notifier_Mailer::send_notification_email( $user_id, compact( 'reply_id', 'topic_id', 'forum_id', 'author_id', 'action', 'setting' ) );
			}
		}
	}

	/**
	 * Displays a edit screen for notifications inside the buddypress notification settings form
	 * @return void
	 */
	public function settings_screen() {
		global $topic_subscribe, $group_new_topic, $quoted;

		if ( ! $topic_subscribe = bp_get_user_meta( bp_displayed_user_id(), 'notification_forum_topic_subscribe', true ) ) {
			$topic_subscribe = 'yes';
		}

		if ( ! $group_new_topic = bp_get_user_meta( bp_displayed_user_id(), 'notification_forum_group_new_topic', true ) ) {
			$group_new_topic = 'yes';
		}

		if ( ! $quoted = bp_get_user_meta( bp_displayed_user_id(), 'notification_forum_quoted', true ) ) {
			$quoted = 'yes';
		}

		self::get_template( 'bp-forum-notifier-settings' );
	}

}

/**
 * Formats notification messages. Used as a callback by buddypress
 * @param string $action usually new_[topic|reply|quote]_[ID]
 * @param int $item_id the post id usually
 * @param int $secondary_item_id the parent post id usually
 * @param int $total_items total item count of how many notifications there are with the same $action
 * @param string $format string, array or object
 * @return array formatted messages
 */
function bp_forum_notifier_messages_format( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {
	$settings = BP_Forum_Notifier_Admin::get_settings();
	$blogname = get_option( 'blogname' );

	switch( substr( $action, 4, 5 ) ) {
		case 'topic':
			$link = bbp_get_topic_permalink( $item_id );
			$setting_string = 'topic-notification-single';

			if( $total_items > 1 ) {
				$link = bbp_get_forum_permalink( $secondary_item_id );
				$setting_string = 'topic-notification-multi';
			} else {
				$total_items = bbp_get_topic_author_display_name( $item_id );
			}

			$text = sprintf(
				$settings[ $setting_string ],
				$blogname,
				$total_items,
				bbp_get_forum_title( $secondary_item_id )
			);
			break;

		case 'reply':
			$link = bbp_get_reply_url( $item_id );
			$setting_string = 'reply-notification-single';

			if( $total_items > 1 ) {
				$setting_string = 'reply-notification-multi';
			} else {
				$total_items = bbp_get_reply_author_display_name( $item_id );
			}

			$text = sprintf(
				$settings[ $setting_string ],
				$blogname,
				$total_items,
				bbp_get_topic_title( $secondary_item_id )
			);
			break;

		case 'quote':
			$link = bbp_get_reply_url( $item_id );
			$setting_string = 'quote-notification-single';

			if( $total_items > 1 ) {
				$setting_string = 'quote-notification-multi';
			} else {
				$total_items = bbp_get_reply_author_display_name( $item_id );
			}

			$text = sprintf(
				$settings[ $setting_string ],
				$blogname,
				$total_items,
				bbp_get_topic_title( $secondary_item_id )
			);
			break;
	}

	switch( $format ) {
		case 'string':
			$return = sprintf(
				'<a href="%s" title="%s">%s</a>',
				$link,
				esc_attr( $text ),
				$text
			);
			break;

		case 'email':
			$return = sprintf(
				"%s\n%s",
				$text,
				$link
			);
			break;

		default:
			$return = array(
				'text' => $text,
				'link' => $link
			);
	}

	return $return;
}
