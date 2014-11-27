<?php

/**
 * Class for sending e-mail notifications and digestions
 */
class BP_Forum_Notifier_Mailer {

	/**
	 */
	static public function __setup() {
		// Log emails going out
		//add_filter( 'wp_mail', array( 'BP_Forum_Notifier_Mailer', 'log' ), 10, 5 );

		// Add scheduled action for delayed e-mails
		add_action( 'bp_forum_notifier_scheduled_email', array( 'BP_Forum_Notifier_Mailer', 'send_notification_email' ), 1, 1 );
	}

	/**
	 * Log whatever and return it
	 * @param mixed $whatever
	 * @return mixes $whatever
	 */
	static public function log( $whatever ) {
		$file = fopen( '/tmp/bp-forum-notifier.log', 'a' );
		fwrite( $file, var_export( $whatever, true ) );
		fclose( $file );
		return $whatever;
	}

	/**
	 * Sends notification email stored as a user-meta or attached as an argument
	 * @param int $user_id
	 * @param array $mail_properties optional, if used, user-meta will not be used
	 * @return void
	 */
	static public function send_notification_email( $user_id, $mail_properties = array() ) {
		if( empty( $mail_properties ) || ! is_array( $mail_properties ) ) {
			$mail_properties = get_user_meta( $user_id, 'bp_forum_notifier_emails' );
		}

		//self::log( $mail_properties );

		if( ! is_array( $mail_properties ) ) {
			// Fail, no e-mails found
			return;
		}

		if( array_key_exists( 'reply_id', $mail_properties ) ) {
			$mail_properties = array( $mail_properties );
		}

		$settings = BP_Forum_Notifier_Admin::get_settings();
		$messages = array();
		$user = get_userdata( $user_id );
		$blogname = get_option( 'blogname' );

		foreach( $mail_properties as $props ) {
			extract( $props );
			$author = get_userdata( $author_id );

			switch( substr( $action, 4, 5 ) ) {
				case 'topic' :
					$subject = sprintf(
						$settings[ 'topic-mail-subject-single' ],
						$blogname,
						$author->display_name,
						bbp_get_forum_title( $forum_id )
					);
					$messages[] = sprintf(
						$settings[ 'topic-mail-message-line' ],
						$author->display_name,
						bbp_get_forum_title( $forum_id ),
						strip_tags( bbp_get_topic_content( $topic_id ) ),
						bbp_get_topic_permalink( $topic_id )
					);
					break;

				case 'reply' :
					$subject = sprintf(
						$settings[ 'reply-mail-subject-single' ],
						$blogname,
						$author->display_name,
						bbp_get_topic_title( $topic_id )
					);
					$messages[] = sprintf(
						$settings[ 'reply-mail-message-line' ],
						$author->display_name,
						bbp_get_topic_title( $topic_id ),
						strip_tags( bbp_get_reply_content( $reply_id ) ),
						bbp_get_reply_url( $reply_id )
					);
					break;

				case 'quote' :
					$link = bbp_get_topic_permalink( $topic_id );
					$subject = sprintf(
						$settings[ 'quote-mail-subject-single' ],
						$blogname,
						$author->display_name,
						bbp_get_topic_title( $topic_id )
					);
					$messages[] = sprintf(
						$settings[ 'quote-mail-message-line' ],
						$author->display_name,
						bbp_get_topic_title( $topic_id ),
						strip_tags( bbp_get_reply_content( $reply_id ) ),
						bbp_get_reply_url( $reply_id )
					);
					break;
			}

			if( count( $messages ) > 1 ) {
				$subject = sprintf( $settings[ 'multiple-mail-messages-subject' ], $blogname, count( $messages ) );
			}

			$message = sprintf( $settings[ 'mail-message-wrap' ], implode( "\n\n--------------------\n\n", $messages ) );

		}
		if( wp_mail( $user->user_email, $subject, $message ) ) {
			delete_user_meta( $user_id, 'bp_forum_notifier_emails' );
		}
	}

}
