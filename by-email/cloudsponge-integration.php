<?php

class Cloudsponge_Integration {
	/**
	 * Whether or not Cloudsponge is enabled.
	 *
	 * @var bool
	 */
	public $enabled;

	/**
	 * Cloudsponge domain key.
	 *
	 * @var string
	 */
	public $key;

	/**
	 * Cloudsponge domain key.
	 *
	 * @var string
	 */
	public $domain_key;

	/**
	 * Cloudsponge account key.
	 *
	 * @var string
	 */
	public $account_key;

	/**
	 * Cloudsponge sources.
	 *
	 * @var array
	 */
	public $sources;

	/**
	 * Whether or not to display deep links.
	 *
	 * @var bool
	 */
	public $deep_links;

	/**
	 * PHP 5 Constructor
	 *
	 * @package Invite Anyone
	 * @since 0.8
	 */
	public function __construct() {

		if ( empty( $options ) ) {
			$options = get_option( 'invite_anyone' );
		}

		$this->enabled     = ! empty( $options['cloudsponge_enabled'] ) ? $options['cloudsponge_enabled'] : false;
		$this->domain_key  = ! empty( $options['cloudsponge_key'] ) ? $options['cloudsponge_key'] : false;
		$this->account_key = ! empty( $options['cloudsponge_account_key'] ) ? $options['cloudsponge_account_key'] : false;
		$this->sources     = ! empty( $options['cloudsponge_sources'] ) ? explode( ',', $options['cloudsponge_sources'] ) : false;
		$this->deep_links  = ! empty( $options['cloudsponge_deep_links'] ) ? $options['cloudsponge_deep_links'] : false;

		if ( $this->enabled && ( $this->domain_key || $this->account_key ) ) {
			define( 'INVITE_ANYONE_CS_ENABLED', true );
			add_action( 'invite_anyone_after_addresses', array( $this, 'import_markup' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ) );
		}
	}

	/**
	 * Registers and loads CS JS.
	 *
	 * @package Invite Anyone
	 * @since 0.8.8
	 */
	public function enqueue_script() {

		// Values available in the JavaScript side
		$strings = array();

		if ( $this->domain_key ) {
			wp_register_script(
				'ia_cloudsponge_address_books',
				'https://api.cloudsponge.com/address_books.js',
				array(),
				BP_INVITE_ANYONE_VER,
				true
			);

			wp_register_script(
				'ia_cloudsponge',
				plugins_url() . '/invite-anyone/by-email/cloudsponge-js.js',
				array( 'ia_cloudsponge_address_books' ),
				BP_INVITE_ANYONE_VER,
				true
			);

			$strings['domain_key']  = $this->domain_key;
			$strings['account_key'] = false;
		} else {
			wp_register_script(
				'ia_cloudsponge',
				plugins_url() . '/invite-anyone/by-email/cloudsponge-js.js',
				array(),
				BP_INVITE_ANYONE_VER,
				true
			);

			$strings['account_key'] = $this->account_key;
			$strings['domain_key']  = false;
		}

		$locale = apply_filters( 'ia_cloudsponge_locale', '' );
		if ( $locale ) {
			$strings['locale'] = $locale;
		}

		$stylesheet = apply_filters( 'ia_cloudsponge_stylesheet', '' );
		if ( $stylesheet ) {
			$strings['stylesheet'] = $stylesheet;
		}

		$strings['sources'] = $this->sources;

		wp_localize_script( 'ia_cloudsponge', 'ia_cloudsponge', $strings );
	}

	/**
	 * Inserts the Cloudsponge markup into the Send Invites front end page.
	 *
	 * Also responsible for enqueuing the necessary assets.
	 *
	 * @package Invite Anyone
	 * @since 0.8
	 */
	public function import_markup() {
		wp_enqueue_script( 'ia_cloudsponge' );

		?>

		<input type="hidden" id="cloudsponge-emails" name="cloudsponge-emails" value="" />

		<?php if ( ! $this->deep_links ) : ?>
			<a class="cs_import"><?php esc_html_e( 'You can also add email addresses from your Address Book.', 'invite-anyone' ); ?></a>
		<?php elseif ( $this->sources ) : ?>
			<?php $sources_list = self::sources_list(); ?>

			<?php esc_html_e( 'You can also add email addresses from one of the following address books:', 'invite-anyone' ); ?>

			<?php
			$sources_display = [];
			foreach ( $this->sources as $source ) {
				if ( ! isset( $sources_list[ $source ] ) ) {
					continue;
				}

				$sources_display[] = '<a class="cloudsponge-launch" data-cloudsponge-source="' . esc_attr( $source ) . '">' . esc_html( $sources_list[ $source ]['name'] ) . '</a>';
			}

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo implode( ', ', $sources_display );
			?>
			<?php
		endif;
	}

	public static function sources_list() {
		$sources = get_transient( 'cloudsponge-services' );
		if ( false === $sources ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$sources = json_decode( file_get_contents( 'https://api.cloudsponge.com/services.json' ), true );

			if ( $sources ) {
				set_transient( 'cloudsponge-services', $sources, HOUR_IN_SECONDS );
			} else {
				$sources = [];
			}
		}

		return $sources;
	}
}
$cloudsponge_integration = new Cloudsponge_Integration();
