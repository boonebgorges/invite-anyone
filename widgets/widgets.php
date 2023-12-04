<?php
/* Invite Anyone widgets */

function invite_anyone_add_widget_css() {
	global $bp;

	$style_url  = plugins_url() . '/invite-anyone/widgets/widgets-css.css';
	$style_file = WP_PLUGIN_DIR . '/invite-anyone/widgets/widgets-css.css';
	if ( file_exists( $style_file ) ) {
		wp_register_style( 'invite-anyone-widget-style', $style_url, array(), filemtime( $style_file ) );
		wp_enqueue_style( 'invite-anyone-widget-style' );
	}
}

/**
 * Invite Anyone widget.
 */
class InviteAnyoneWidget extends WP_Widget {
	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'invite-anyone',
			'description' => 'Invite Anyone widget',
		);

		/* Widget control settings. */
		$control_ops = array(
			'width'   => 300,
			'height'  => 350,
			'id_base' => 'invite-anyone-widget',
		);

		/* Create the widget. */
		parent::__construct( 'invite-anyone-widget', 'Invite Anyone', $widget_ops, $control_ops );

		if ( is_active_widget( false, false, $this->id_base ) ) {
			wp_enqueue_style( 'invite-anyone-widget-style', plugins_url() . '/invite-anyone/widgets/widgets-css.css', array(), filemtime( WP_PLUGIN_DIR . '/invite-anyone/widgets/widgets-css.css' ) );
		}
	}

	/**
	 * Markup for public widget.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget instance.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		global $bp;
		extract( $args );

		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		if ( ! apply_filters( 'widget_title', $instance['title'] ) ) {
			$title = __( 'Invite Anyone', 'invite-anyone' );
		}

		$instruction_text = esc_attr( $instance['instruction_text'] );
		if ( ! $instruction_text ) {
			$instruction_text = __( 'Enter one email address per line to invite friends to join this site.', 'invite-anyone' );
		}

		?>

		<?php /* Non-logged-in and unauthorized users should not see the widget */ ?>
		<?php if ( invite_anyone_access_test() && $bp->current_component !== $bp->invite_anyone->slug ) : ?>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $before_widget;

				if ( $title ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $before_title . esc_html( $title ) . $after_title;
				}
				?>

					<p><?php echo wp_kses_post( $instruction_text ); ?></p>

					<?php
					$form_action = bp_members_get_user_url(
						bp_displayed_user_id(),
						bp_members_get_path_chunks( [ buddypress()->invite_anyone->slug ] )
					);
					?>

					<form class="standard-form" action="<?php echo esc_url( $form_action ); ?>" method="post">

					<?php invite_anyone_email_fields(); ?>

					<?php /* If we're on a group page, send the group_id as well */ ?>
					<?php if ( bp_is_group() ) : ?>
						<?php global $bp; ?>
						<input type="hidden" name="invite_anyone_widget_group" id="invite_anyone_widget_group" value="<?php echo esc_attr( $bp->groups->current_group->id ); ?>" />
					<?php endif; ?>

					<input type="hidden" name="invite_anyone_widget" id="invite_anyone_widget" value="1" />

					<?php do_action( 'invite_anyone_after_addresses' ); ?>

					<?php wp_nonce_field( 'invite-anyone-widget_' . $bp->loggedin_user->id ); ?>
					<p id="invite-anyone-widget-submit" >
						<input class="button" type="submit" value="<?php esc_html_e( 'Continue', 'invite-anyone' ); ?>" />
					</p>
					</form>

				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $after_widget;
				?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Callback for updating widget.
	 *
	 * @param array $new_instance New widget instance.
	 * @param array $old_instance Old widget instance.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}

	/**
	 * Admin form markup.
	 *
	 * @param array $instance Widget instance.
	 * @return void
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : __( 'Invite Anyone', 'invite-anyone' );

		$email_fields = isset( $instance['email_fields'] ) ? (int) $instance['email_fields'] : 3;

		$instruction_text = isset( $instance['instruction_text'] ) ? esc_attr( $instance['instruction_text'] ) : __( 'Invite friends to join the site by entering their email addresses below.', 'invite-anyone' );

		?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:' ); ?> <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>

			<p><label for="<?php echo esc_attr( $this->get_field_id( 'instruction_text' ) ); ?>"><?php esc_html_e( 'Text to display in widget:', 'invite-anyone' ); ?>
			<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'instruction_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'instruction_text' ) ); ?>"><?php echo wp_kses_post( $instruction_text ); ?></textarea>
			</label></p>

			<p><label for="<?php echo esc_attr( $this->get_field_id( 'email_fields' ) ); ?>"><?php esc_html_e( 'Number of email fields to display in widget:', 'invite-anyone' ); ?> <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'email_fields' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'email_fields' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $email_fields ); ?>" /></label></p>

		<?php
	}
}

/**
 * Register widget.
 *
 * @return void
 */
function invite_anyone_register_widget() {
	return register_widget( 'InviteAnyoneWidget' );
}
add_action( 'widgets_init', 'invite_anyone_register_widget' );
