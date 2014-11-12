<?php global $settings;

$fields = array(
	'notifications-for-all-replies' => array( 'checkbox', __( 'Always notify on replies', 'bp-forum-notifier' ), __( 'In older versions (< 1.4) the plugin would only notify members of new replies if they had "subscribed" to the topic. By checking this box groupmembers will get notifications from all replies in group forums.', 'bp-forum-notifier' ) ),
	'mail-delay' => array( 'textfield', __( 'Delay in minutes before e-mails are sent', 'bp-forum-notifier' ) ),
	'multiple-mail-messages-subject' => array( 'textfield', __( 'E-mail subject when message contains multiple forum activities', 'bp-forum-notifier' ), __( '%1$s = blogname, %2$d = number of activities', 'bp-forum-notifier' ) ),
	'reply-notification-single' => array( 'textfield', __( 'Reply notification (single reply)', 'bp-forum-notifier' ), __( '%1$s = blogname, %2$s = author, %3$s = topic title', 'bp-forum-notifier' ) ),
	'reply-notification-multi' => array( 'textfield', __( 'Reply notification (multiple replies in the same topic)', 'bp-forum-notifier' ), __( '%1$s = blogname, %2$d = number or replies, %3$s = topic title', 'bp-forum-notifier' ) ),
	'reply-mail-subject-single' => array( 'textfield', __( 'Reply e-mail subject (single reply)', 'bp-forum-notifier' ), __( '%1$s = blogname, %2$s = author, %3$s = topic title', 'bp-forum-notifier' ) ),
	'reply-mail-subject-multi' => array( 'textfield', __( 'Reply e-mail subject (multiple replies in the same topic)', 'bp-forum-notifier' ), __( '%1$s = blogname, %2$d = number of replies, %3$s = topic title', 'bp-forum-notifier' ) ),
	'reply-mail-message-line' => array( 'textarea', __( 'Reply e-mail message body', 'bp-forum-notifier' ), __( '%1$s = author, %2$s = topic title, %3$s = reply, %4$s = topic link', 'bp-forum-notifier' ) ),
	'topic-notification-single' => array( 'textfield', __( 'Topic notification (single topic)', 'bp-forum-notifier' ), __( '%1$s = blogname, %2$s = author, %3$s = forum title', 'bp-forum-notifier' ) ),
	'topic-notification-multi' => array( 'textfield', __( 'Topic notification (multiple topics in the same forum)', 'bp-forum-notifier' ), __( '%1$s = blogname, %2$d = number of topics, %3$s = forum title', 'bp-forum-notifier' ) ),
	'topic-mail-subject-single' => array( 'textfield', __( 'Topic e-mail subject (single topic)', 'bp-forum-notifier' ), __( '%1$s = blogname, %2$s = author, %3$s = forum title', 'bp-forum-notifier' ) ),
	'topic-mail-subject-multi' => array( 'textfield', __( 'Topic e-mail subject (multiple topics in the same forum)', 'bp-forum-notifier' ), __( '%1$s = blogname, %2$d = number of topics, %3$s = forum title', 'bp-forum-notifier' ) ),
	'topic-mail-message-line' => array( 'textarea', __( 'Topic e-mail message body', 'bp-forum-notifier' ), __( '%1$s = author, %2$s = forum title, %3$s = topic, %4$s = topic link', 'bp-forum-notifier' ) ),
	'quote-notification-single' => array( 'textfield', __( 'Quote notification (single quote)', 'bp-forum-notifier' ), __( '%1$s = blogname, %2$s = author, %3$s = topic title', 'bp-forum-notifier' ) ),
	'quote-notification-multi' => array( 'textfield', __( 'Quote notification (multiple quotes in the same topic)', 'bp-forum-notifier' ), __( '%1$s = blogname, %2$d = number or quotes, %3$s = topic title', 'bp-forum-notifier' ) ),
	'quote-mail-subject-single' => array( 'textfield', __( 'Quote e-mail subject (single quote)', 'bp-forum-notifier' ), __( '%1$s = blogname, %2$s = author, %3$s = topic title', 'bp-forum-notifier' ) ),
	'quote-mail-subject-multi' => array( 'textfield', __( 'Quote e-mail subject (multiple quotes in the same topic)', 'bp-forum-notifier' ), __( '%1$s = blogname, %2$d = number or quotes, %3$s = topic title', 'bp-forum-notifier' ) ),
	'quote-mail-message-line' => array( 'textarea', __( 'Quote e-mail message body', 'bp-forum-notifier' ), __( '%1$s = author, %2$s = topic title, %3$s = reply, %4$s = topic link', 'bp-forum-notifier' ) ),
	'mail-message-wrap' => array( 'textarea', __( 'E-mail message body wrapper', 'bp-forum-notifier' ), __( '%1$s = message bodies', 'bp-forum-notifier' ) )
);

?>
<div class="wrap">
	<h2><?php _e( 'Forum Notifier Settings', 'bp-forum-notifier' ); ?></h2>
	<form action="" method="post">
		<?php wp_nonce_field( 'bp_forum_notifier_admin' ); ?>

		<table class="form-table">
			<tbody>
				<?php foreach( $fields as $field_name => $field ) : ?>
					<tr>
						<th scope="row">
							<label for="<?php echo $field_name; ?>"><?php echo $field[ 1 ]; ?></label>
						</th>
						<td>
							<?php if( $field[ 0 ] == 'textfield' ) : ?>
								<input id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" type="textfield" class="large-text" value="<?php echo esc_attr( $settings[ $field_name ] ); ?>" />
							<?php elseif( $field[ 0 ] == 'textarea' ) : ?>
								<textarea id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" class="large-text"><?php echo $settings[ $field_name ]; ?></textarea>
							<?php elseif( $field[ 0 ] == 'checkbox' ) : ?>
								<input id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" type="checkbox" value="yes" <?php checked( $settings[ $field_name ], 'yes', true ) ?> />
							<?php endif; ?>

							<?php if( array_key_exists( 2, $field ) ) : ?>
								<br />
								<?php echo $field[ 2 ]; ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<p class="submit clear">
			<input class="button-primary" name="forum-notifier-save" type="submit" value="<?php echo esc_attr( __( 'Save' ) ); ?>" />
		</p>

	</form>
</div>
