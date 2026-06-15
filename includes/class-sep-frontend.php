<?php
/**
 * Frontend display of the popup.
 *
 * @package Special_Event_Popup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SEP_Frontend {

	/**
	 * Cached settings for the current request.
	 *
	 * @var array
	 */
	private $settings = array();

	/**
	 * Hook registration.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_assets' ) );
		add_action( 'wp_footer', array( $this, 'maybe_render_popup' ) );
	}

	/**
	 * Determine whether the popup should be shown for the current request.
	 *
	 * @return bool
	 */
	private function should_show() {
		if ( is_admin() ) {
			return false;
		}

		$this->settings = SEP_Helper::get_settings();

		if ( ! SEP_Helper::is_within_schedule( $this->settings ) ) {
			return false;
		}

		if ( ! SEP_Helper::should_display_on_current_page( $this->settings ) ) {
			return false;
		}

		// Require at least an image or a message to display.
		if ( empty( $this->settings['image_id'] ) && '' === trim( wp_strip_all_tags( $this->settings['message'] ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Enqueue frontend assets only when the popup will actually display.
	 */
	public function maybe_enqueue_assets() {
		if ( ! $this->should_show() ) {
			return;
		}

		wp_enqueue_style(
			'sep-frontend',
			SEP_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			SEP_VERSION
		);

		wp_enqueue_script(
			'sep-frontend',
			SEP_PLUGIN_URL . 'assets/js/frontend.js',
			array(),
			SEP_VERSION,
			true
		);

		wp_localize_script(
			'sep-frontend',
			'sepPopupSettings',
			array(
				'delaySeconds'  => absint( $this->settings['delay_seconds'] ),
				'frequency'     => $this->settings['frequency'],
				'closeButton'   => (bool) $this->settings['close_button'],
				'closeOutside'  => (bool) $this->settings['close_outside'],
				'closeEsc'      => (bool) $this->settings['close_esc'],
				'confetti'      => ( 'celebration' === $this->settings['popup_type'] && ! empty( $this->settings['confetti_enabled'] ) ),
				'cookieName'    => 'sep_popup_seen',
				'cookieDays'    => ( 'once_per_day' === $this->settings['frequency'] ) ? 1 : 365,
			)
		);
	}

	/**
	 * Render the popup markup in the footer.
	 */
	public function maybe_render_popup() {
		if ( ! $this->should_show() ) {
			return;
		}

		$settings   = $this->settings;
		$image_url  = $settings['image_id'] ? wp_get_attachment_image_url( $settings['image_id'], 'large' ) : '';
		$image_alt  = $settings['image_id'] ? get_post_meta( $settings['image_id'], '_wp_attachment_image_alt', true ) : '';
		$has_button = ( $settings['button_text'] && $settings['button_url'] );

		// Guard against rendering an empty overlay, e.g. if the selected
		// image attachment was deleted and no message/button is set.
		if ( ! $image_url && ! $settings['message'] && ! $has_button ) {
			return;
		}

		$classes = array( 'sep-popup-overlay' );

		if ( 'memorial' === $settings['popup_type'] ) {
			$classes[] = 'sep-popup-memorial';

			if ( ! empty( $settings['grayscale'] ) ) {
				$classes[] = 'sep-popup-grayscale';
			}

			if ( ! empty( $settings['dark_theme'] ) ) {
				$classes[] = 'sep-popup-dark';
			}
		} elseif ( 'celebration' === $settings['popup_type'] ) {
			$classes[] = 'sep-popup-celebration';
		} else {
			$classes[] = 'sep-popup-' . sanitize_html_class( $settings['popup_type'] );
		}
		?>
		<div id="sep-popup-overlay" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" role="dialog" aria-modal="true" hidden>
			<div class="sep-popup-box">
				<?php if ( ! empty( $settings['close_button'] ) ) : ?>
					<button type="button" class="sep-popup-close" aria-label="<?php esc_attr_e( 'Close', 'special-event-popup' ); ?>">&times;</button>
				<?php endif; ?>

				<?php if ( $image_url ) : ?>
					<div class="sep-popup-image">
						<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" />
					</div>
				<?php endif; ?>

				<?php if ( $settings['message'] || $has_button ) : ?>
					<div class="sep-popup-content">
						<?php if ( $settings['message'] ) : ?>
							<div class="sep-popup-message"><?php echo wp_kses_post( $settings['message'] ); ?></div>
						<?php endif; ?>

						<?php if ( $has_button ) : ?>
							<a class="sep-popup-button" href="<?php echo esc_url( $settings['button_url'] ); ?>"><?php echo esc_html( $settings['button_text'] ); ?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( 'celebration' === $settings['popup_type'] && ! empty( $settings['confetti_enabled'] ) ) : ?>
				<canvas id="sep-confetti-canvas" aria-hidden="true"></canvas>
			<?php endif; ?>
		</div>
		<?php
	}
}
