<?php
/**
 * Helper functions shared between admin and frontend.
 *
 * @package Special_Event_Popup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SEP_Helper {

	/**
	 * Default settings used on activation and as a fallback.
	 *
	 * @return array
	 */
	public static function get_default_settings() {
		return array(
			'enabled'           => false,
			'popup_type'        => 'announcement', // celebration | memorial | announcement | custom
			'image_id'          => 0,
			'message'           => '',
			'button_text'       => '',
			'button_url'        => '',
			'start_date'        => '',
			'end_date'          => '',
			'display_entire'    => true,
			'display_homepage'  => false,
			'display_posts'     => false,
			'display_pages'     => false,
			'exclude_pages'     => '',
			'delay_seconds'     => 0,
			'frequency'         => 'every_visit', // once_per_visitor | once_per_day | every_visit
			'close_button'      => true,
			'close_outside'     => true,
			'close_esc'         => true,
			'grayscale'         => false,
			'dark_theme'        => false,
			'confetti_enabled'  => false,
		);
	}

	/**
	 * Get plugin settings merged with defaults.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$settings = get_option( SEP_OPTION_NAME, array() );

		return wp_parse_args( $settings, self::get_default_settings() );
	}

	/**
	 * Determine whether the popup should be active right now, based on the
	 * enable flag and the configured schedule (using the site timezone).
	 *
	 * @param array $settings Plugin settings.
	 * @return bool
	 */
	public static function is_within_schedule( array $settings ) {
		if ( empty( $settings['enabled'] ) ) {
			return false;
		}

		$start = trim( $settings['start_date'] );
		$end   = trim( $settings['end_date'] );

		// No schedule defined: stays active until manually disabled.
		if ( '' === $start && '' === $end ) {
			return true;
		}

		try {
			$timezone = wp_timezone();
			$now      = new DateTimeImmutable( 'now', $timezone );

			if ( '' !== $start ) {
				$start_date = new DateTimeImmutable( $start, $timezone );
				if ( $now < $start_date ) {
					return false;
				}
			}

			if ( '' !== $end ) {
				$end_date = new DateTimeImmutable( $end, $timezone );
				if ( $now > $end_date ) {
					return false;
				}
			}
		} catch ( Exception $e ) {
			// If dates are malformed, fail safe and don't display the popup.
			return false;
		}

		return true;
	}

	/**
	 * Determine whether the popup should display on the current page,
	 * based on the configured display rules.
	 *
	 * @param array $settings Plugin settings.
	 * @return bool
	 */
	public static function should_display_on_current_page( array $settings ) {
		$post_id = get_queried_object_id();

		// Excluded pages always win.
		if ( ! empty( $settings['exclude_pages'] ) ) {
			$excluded = self::parse_id_list( $settings['exclude_pages'] );
			if ( $post_id && in_array( $post_id, $excluded, true ) ) {
				return false;
			}
		}

		if ( ! empty( $settings['display_entire'] ) ) {
			return true;
		}

		if ( ! empty( $settings['display_homepage'] ) && is_front_page() ) {
			return true;
		}

		if ( ! empty( $settings['display_posts'] ) && is_singular( 'post' ) ) {
			return true;
		}

		if ( ! empty( $settings['display_pages'] ) && is_singular( 'page' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Parse a comma/whitespace separated list of IDs into an array of ints.
	 *
	 * @param string $value Raw list of IDs.
	 * @return int[]
	 */
	public static function parse_id_list( $value ) {
		$parts = preg_split( '/[\s,]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY );

		return array_map( 'absint', $parts );
	}

	/**
	 * Get the list of valid popup type keys.
	 *
	 * @return array
	 */
	public static function get_popup_types() {
		return array(
			'celebration'  => __( 'Celebration', 'special-event-popup' ),
			'memorial'     => __( 'Memorial', 'special-event-popup' ),
			'announcement' => __( 'Announcement', 'special-event-popup' ),
			'custom'       => __( 'Custom', 'special-event-popup' ),
		);
	}

	/**
	 * Get the list of valid frequency keys.
	 *
	 * @return array
	 */
	public static function get_frequency_options() {
		return array(
			'every_visit'    => __( 'Show every visit', 'special-event-popup' ),
			'once_per_day'   => __( 'Show once per day', 'special-event-popup' ),
			'once_per_visitor' => __( 'Show only once per visitor', 'special-event-popup' ),
		);
	}
}
