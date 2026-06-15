<?php
/**
 * Registers the plugin settings with the WordPress Settings API.
 *
 * @package Special_Event_Popup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SEP_Settings {

	/**
	 * Hook registration.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register the single settings array option.
	 */
	public function register_settings() {
		register_setting(
			'sep_settings_group',
			SEP_OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => SEP_Helper::get_default_settings(),
			)
		);
	}

	/**
	 * Sanitize and validate all settings before they are saved.
	 *
	 * @param array $input Raw posted values.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$defaults = SEP_Helper::get_default_settings();
		$output   = array();

		$output['enabled']    = ! empty( $input['enabled'] );

		$valid_types          = array_keys( SEP_Helper::get_popup_types() );
		$output['popup_type'] = ( isset( $input['popup_type'] ) && in_array( $input['popup_type'], $valid_types, true ) )
			? $input['popup_type']
			: $defaults['popup_type'];

		$output['image_id'] = isset( $input['image_id'] ) ? absint( $input['image_id'] ) : 0;

		$output['message'] = isset( $input['message'] )
			? wp_kses_post( $input['message'] )
			: '';

		$output['button_text'] = isset( $input['button_text'] ) ? sanitize_text_field( $input['button_text'] ) : '';
		$output['button_url']  = isset( $input['button_url'] ) ? sanitize_url( $input['button_url'] ) : '';

		$output['start_date'] = isset( $input['start_date'] ) ? $this->sanitize_datetime( $input['start_date'] ) : '';
		$output['end_date']   = isset( $input['end_date'] ) ? $this->sanitize_datetime( $input['end_date'] ) : '';

		$output['display_entire']   = ! empty( $input['display_entire'] );
		$output['display_homepage'] = ! empty( $input['display_homepage'] );
		$output['display_posts']    = ! empty( $input['display_posts'] );
		$output['display_pages']    = ! empty( $input['display_pages'] );

		$output['exclude_pages'] = isset( $input['exclude_pages'] )
			? implode( ',', SEP_Helper::parse_id_list( $input['exclude_pages'] ) )
			: '';

		$output['delay_seconds'] = isset( $input['delay_seconds'] ) ? max( 0, absint( $input['delay_seconds'] ) ) : 0;

		$valid_frequencies   = array_keys( SEP_Helper::get_frequency_options() );
		$output['frequency'] = ( isset( $input['frequency'] ) && in_array( $input['frequency'], $valid_frequencies, true ) )
			? $input['frequency']
			: $defaults['frequency'];

		$output['close_button']  = ! empty( $input['close_button'] );
		$output['close_outside'] = ! empty( $input['close_outside'] );
		$output['close_esc']     = ! empty( $input['close_esc'] );

		$output['grayscale']        = ! empty( $input['grayscale'] );
		$output['dark_theme']       = ! empty( $input['dark_theme'] );
		$output['confetti_enabled'] = ! empty( $input['confetti_enabled'] );

		return $output;
	}

	/**
	 * Validate a datetime string in `Y-m-d\TH:i` format (from a
	 * datetime-local input). Returns an empty string if invalid.
	 *
	 * @param string $value Raw datetime value.
	 * @return string
	 */
	private function sanitize_datetime( $value ) {
		$value = sanitize_text_field( $value );

		if ( '' === $value ) {
			return '';
		}

		$date = DateTime::createFromFormat( 'Y-m-d\TH:i', $value );

		if ( ! $date ) {
			return '';
		}

		return $date->format( 'Y-m-d H:i:s' );
	}
}
