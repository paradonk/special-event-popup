<?php
/**
 * Plugin Name:       Special Event Popup
 * Plugin URI:        https://example.com/special-event-popup
 * Description:       Display a schedulable popup (image, title, message, button) for celebrations, memorials, or announcements.
 * Version:           1.0.1
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Your Name
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       special-event-popup
 * Domain Path:       /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SEP_VERSION', '1.0.1' );
define( 'SEP_PLUGIN_FILE', __FILE__ );
define( 'SEP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SEP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SEP_OPTION_NAME', 'sep_settings' );

require_once SEP_PLUGIN_DIR . 'includes/class-sep-helper.php';
require_once SEP_PLUGIN_DIR . 'includes/class-sep-settings.php';
require_once SEP_PLUGIN_DIR . 'includes/class-sep-admin.php';
require_once SEP_PLUGIN_DIR . 'includes/class-sep-frontend.php';

/**
 * Load plugin translations.
 */
function sep_load_textdomain() {
	load_plugin_textdomain( 'special-event-popup', false, dirname( plugin_basename( SEP_PLUGIN_FILE ) ) . '/languages' );
}
add_action( 'init', 'sep_load_textdomain' );

/**
 * Bootstrap the plugin.
 */
function sep_init_plugin() {
	new SEP_Settings();
	new SEP_Admin();
	new SEP_Frontend();
}
add_action( 'plugins_loaded', 'sep_init_plugin' );

/**
 * Set up default options on activation.
 */
function sep_activate_plugin() {
	if ( false === get_option( SEP_OPTION_NAME ) ) {
		add_option( SEP_OPTION_NAME, SEP_Helper::get_default_settings() );
	}
}
register_activation_hook( __FILE__, 'sep_activate_plugin' );
