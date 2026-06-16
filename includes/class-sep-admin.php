<?php
/**
 * Admin settings page for Special Event Popup.
 *
 * @package Special_Event_Popup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SEP_Admin {

	/**
	 * The settings page hook suffix, used to scope asset loading.
	 *
	 * @var string
	 */
	private $page_hook = '';

	/**
	 * Hook registration.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Register the settings page under the Dashboard menu.
	 */
	public function add_admin_menu() {
		$this->page_hook = add_submenu_page(
			'index.php',
			__( 'Special Event Popup', 'special-event-popup' ),
			__( 'Special Event Popup', 'special-event-popup' ),
			'manage_options',
			'special-event-popup',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Enqueue admin assets only on the plugin's settings page.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( $hook !== $this->page_hook ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_style(
			'sep-admin',
			SEP_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			SEP_VERSION
		);

		wp_enqueue_script(
			'sep-admin',
			SEP_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			SEP_VERSION,
			true
		);

		wp_localize_script(
			'sep-admin',
			'sepAdmin',
			array(
				'mediaTitle'  => __( 'Select or upload popup image', 'special-event-popup' ),
				'mediaButton' => __( 'Use this image', 'special-event-popup' ),
			)
		);
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings    = SEP_Helper::get_settings();
		$popup_types = SEP_Helper::get_popup_types();
		$frequencies = SEP_Helper::get_frequency_options();
		$image_url   = $settings['image_id'] ? wp_get_attachment_image_url( $settings['image_id'], 'medium' ) : '';
		?>
		<div class="wrap sep-admin-wrap">
			<h1><?php esc_html_e( 'Special Event Popup', 'special-event-popup' ); ?></h1>

			<?php $this->render_status_notice( $settings ); ?>

			<form action="options.php" method="post">
				<?php settings_fields( 'sep_settings_group' ); ?>

				<h2 class="title"><?php esc_html_e( 'General Settings', 'special-event-popup' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable Popup', 'special-event-popup' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="sep_settings[enabled]" value="1" <?php checked( $settings['enabled'] ); ?> />
								<?php esc_html_e( 'Enable the popup on the site', 'special-event-popup' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sep_popup_type"><?php esc_html_e( 'Popup Type', 'special-event-popup' ); ?></label>
						</th>
						<td>
							<select id="sep_popup_type" name="sep_settings[popup_type]">
								<?php foreach ( $popup_types as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $settings['popup_type'], $key ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Popup Content', 'special-event-popup' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Popup Image', 'special-event-popup' ); ?></th>
						<td>
							<div class="sep-image-preview" style="margin-bottom:10px;<?php echo $image_url ? '' : 'display:none;'; ?>">
								<img src="<?php echo esc_url( $image_url ); ?>" style="max-width:300px;height:auto;display:block;" />
							</div>
							<input type="hidden" class="sep-image-id" name="sep_settings[image_id]" value="<?php echo esc_attr( $settings['image_id'] ); ?>" />
							<button type="button" class="button sep-select-image"><?php esc_html_e( 'Select Image', 'special-event-popup' ); ?></button>
							<button type="button" class="button sep-remove-image" <?php echo $image_url ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Remove Image', 'special-event-popup' ); ?></button>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Popup Message', 'special-event-popup' ); ?></th>
						<td>
							<?php
							wp_editor(
								$settings['message'],
								'sep_message',
								array(
									'textarea_name' => 'sep_settings[message]',
									'textarea_rows' => 6,
									'media_buttons' => false,
								)
							);
							?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sep_button_text"><?php esc_html_e( 'Button Text', 'special-event-popup' ); ?></label>
						</th>
						<td>
							<input type="text" id="sep_button_text" class="regular-text" name="sep_settings[button_text]" value="<?php echo esc_attr( $settings['button_text'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sep_button_url"><?php esc_html_e( 'Button URL', 'special-event-popup' ); ?></label>
						</th>
						<td>
							<input type="url" id="sep_button_url" class="regular-text" name="sep_settings[button_url]" value="<?php echo esc_attr( $settings['button_url'] ); ?>" placeholder="https://" />
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Display Schedule', 'special-event-popup' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="sep_start_date"><?php esc_html_e( 'Start Date and Time', 'special-event-popup' ); ?></label>
						</th>
						<td>
							<input type="datetime-local" id="sep_start_date" name="sep_settings[start_date]" value="<?php echo esc_attr( $this->format_for_input( $settings['start_date'] ) ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sep_end_date"><?php esc_html_e( 'End Date and Time', 'special-event-popup' ); ?></label>
						</th>
						<td>
							<input type="datetime-local" id="sep_end_date" name="sep_settings[end_date]" value="<?php echo esc_attr( $this->format_for_input( $settings['end_date'] ) ); ?>" />
							<p class="description">
								<?php
								printf(
									/* translators: %s: site timezone string. */
									esc_html__( 'Times use the site timezone (%s). Leave both fields empty to keep the popup active until manually disabled.', 'special-event-popup' ),
									esc_html( wp_timezone_string() )
								);
								?>
							</p>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Display Rules', 'special-event-popup' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Show On', 'special-event-popup' ); ?></th>
						<td>
							<label style="display:block;">
								<input type="checkbox" name="sep_settings[display_entire]" value="1" <?php checked( $settings['display_entire'] ); ?> />
								<?php esc_html_e( 'Entire Website', 'special-event-popup' ); ?>
							</label>
							<label style="display:block;">
								<input type="checkbox" name="sep_settings[display_homepage]" value="1" <?php checked( $settings['display_homepage'] ); ?> />
								<?php esc_html_e( 'Homepage Only', 'special-event-popup' ); ?>
							</label>
							<label style="display:block;">
								<input type="checkbox" name="sep_settings[display_posts]" value="1" <?php checked( $settings['display_posts'] ); ?> />
								<?php esc_html_e( 'Posts Only', 'special-event-popup' ); ?>
							</label>
							<label style="display:block;">
								<input type="checkbox" name="sep_settings[display_pages]" value="1" <?php checked( $settings['display_pages'] ); ?> />
								<?php esc_html_e( 'Pages Only', 'special-event-popup' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'If "Entire Website" is checked, the other options below are ignored.', 'special-event-popup' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sep_exclude_pages"><?php esc_html_e( 'Exclude Page IDs', 'special-event-popup' ); ?></label>
						</th>
						<td>
							<input type="text" id="sep_exclude_pages" class="regular-text" name="sep_settings[exclude_pages]" value="<?php echo esc_attr( $settings['exclude_pages'] ); ?>" placeholder="12, 34, 56" />
							<p class="description"><?php esc_html_e( 'Comma-separated list of page or post IDs where the popup should never appear.', 'special-event-popup' ); ?></p>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Popup Behavior', 'special-event-popup' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="sep_delay_seconds"><?php esc_html_e( 'Delay (seconds)', 'special-event-popup' ); ?></label>
						</th>
						<td>
							<input type="number" id="sep_delay_seconds" class="small-text" min="0" step="1" name="sep_settings[delay_seconds]" value="<?php echo esc_attr( $settings['delay_seconds'] ); ?>" />
							<p class="description"><?php esc_html_e( 'Set to 0 to show the popup immediately after page load.', 'special-event-popup' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Display Frequency', 'special-event-popup' ); ?></th>
						<td>
							<?php foreach ( $frequencies as $key => $label ) : ?>
								<label style="display:block;">
									<input type="radio" name="sep_settings[frequency]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $settings['frequency'], $key ); ?> />
									<?php echo esc_html( $label ); ?>
								</label>
							<?php endforeach; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Close Options', 'special-event-popup' ); ?></th>
						<td>
							<label style="display:block;">
								<input type="checkbox" name="sep_settings[close_button]" value="1" <?php checked( $settings['close_button'] ); ?> />
								<?php esc_html_e( 'Close button', 'special-event-popup' ); ?>
							</label>
							<label style="display:block;">
								<input type="checkbox" name="sep_settings[close_outside]" value="1" <?php checked( $settings['close_outside'] ); ?> />
								<?php esc_html_e( 'Clicking outside popup', 'special-event-popup' ); ?>
							</label>
							<label style="display:block;">
								<input type="checkbox" name="sep_settings[close_esc]" value="1" <?php checked( $settings['close_esc'] ); ?> />
								<?php esc_html_e( 'ESC key', 'special-event-popup' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Special Modes', 'special-event-popup' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr class="sep-mode-memorial">
						<th scope="row"><?php esc_html_e( 'Memorial Options', 'special-event-popup' ); ?></th>
						<td>
							<label style="display:block;">
								<input type="checkbox" name="sep_settings[grayscale]" value="1" <?php checked( $settings['grayscale'] ); ?> />
								<?php esc_html_e( 'Display the popup image in grayscale', 'special-event-popup' ); ?>
							</label>
							<label style="display:block;">
								<input type="checkbox" name="sep_settings[dark_theme]" value="1" <?php checked( $settings['dark_theme'] ); ?> />
								<?php esc_html_e( 'Use dark theme styling', 'special-event-popup' ); ?>
							</label>
						</td>
					</tr>
					<tr class="sep-mode-celebration">
						<th scope="row"><?php esc_html_e( 'Celebration Options', 'special-event-popup' ); ?></th>
						<td>
							<label style="display:block;">
								<input type="checkbox" name="sep_settings[confetti_enabled]" value="1" <?php checked( $settings['confetti_enabled'] ); ?> />
								<?php esc_html_e( 'Show confetti animation', 'special-event-popup' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render a status notice above the form showing whether the popup is
	 * currently active and why, so admins are never confused by empty date fields.
	 *
	 * @param array $settings Plugin settings.
	 */
	private function render_status_notice( array $settings ) {
		if ( empty( $settings['enabled'] ) ) {
			echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Popup Status: DISABLED', 'special-event-popup' ) . '</strong> &mdash; ' . esc_html__( 'Enable the popup below to start showing it.', 'special-event-popup' ) . '</p></div>';
			return;
		}

		$start = trim( $settings['start_date'] );
		$end   = trim( $settings['end_date'] );

		if ( '' === $start && '' === $end ) {
			echo '<div class="notice notice-success inline"><p><strong>' . esc_html__( 'Popup Status: ACTIVE', 'special-event-popup' ) . '</strong> &mdash; ' . esc_html__( 'No schedule is set. The popup will show on every visit until you disable it or set an End Date.', 'special-event-popup' ) . '</p></div>';
			return;
		}

		try {
			$timezone = wp_timezone();
			$now      = new DateTimeImmutable( 'now', $timezone );
			$fmt      = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

			$start_obj = '' !== $start ? new DateTimeImmutable( $start, $timezone ) : null;
			$end_obj   = '' !== $end   ? new DateTimeImmutable( $end,   $timezone ) : null;

			if ( $start_obj && $now < $start_obj ) {
				/* translators: %s: formatted start date/time */
				$msg = sprintf( esc_html__( 'Popup Status: SCHEDULED — Will become active on %s.', 'special-event-popup' ), esc_html( wp_date( $fmt, $start_obj->getTimestamp() ) ) );
				echo '<div class="notice notice-info inline"><p><strong>' . $msg . '</strong></p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
				return;
			}

			if ( $end_obj && $now > $end_obj ) {
				/* translators: %s: formatted end date/time */
				$msg = sprintf( esc_html__( 'Popup Status: SCHEDULE ENDED — The schedule expired on %s. Update the dates or disable the popup.', 'special-event-popup' ), esc_html( wp_date( $fmt, $end_obj->getTimestamp() ) ) );
				echo '<div class="notice notice-error inline"><p><strong>' . $msg . '</strong></p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
				return;
			}

			$parts = array();
			if ( $start_obj ) {
				/* translators: %s: formatted start date/time */
				$parts[] = sprintf( esc_html__( 'from %s', 'special-event-popup' ), esc_html( wp_date( $fmt, $start_obj->getTimestamp() ) ) );
			}
			if ( $end_obj ) {
				/* translators: %s: formatted end date/time */
				$parts[] = sprintf( esc_html__( 'until %s', 'special-event-popup' ), esc_html( wp_date( $fmt, $end_obj->getTimestamp() ) ) );
			}

			$range = implode( ' ', $parts );
			/* translators: %s: schedule range e.g. "from June 1 until June 30" */
			$msg = sprintf( esc_html__( 'Popup Status: ACTIVE — Running %s.', 'special-event-popup' ), $range );
			echo '<div class="notice notice-success inline"><p><strong>' . $msg . '</strong></p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput

		} catch ( Exception $e ) {
			echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Popup Status: ACTIVE', 'special-event-popup' ) . '</strong></p></div>';
		}
	}

	/**
	 * Convert a stored `Y-m-d H:i:s` datetime to the `Y-m-d\TH:i` format
	 * expected by a `datetime-local` input.
	 *
	 * @param string $value Stored datetime value.
	 * @return string
	 */
	private function format_for_input( $value ) {
		if ( '' === $value ) {
			return '';
		}

		$date = DateTime::createFromFormat( 'Y-m-d H:i:s', $value );

		if ( ! $date ) {
			return '';
		}

		return $date->format( 'Y-m-d\TH:i' );
	}
}
