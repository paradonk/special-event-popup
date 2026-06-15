<?php
/**
 * Uninstall handler for Special Event Popup.
 *
 * Removes the plugin's settings when the plugin is deleted via the
 * WordPress admin.
 *
 * @package Special_Event_Popup
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'sep_settings' );

// Also clean up the option on any sites in a multisite network.
if ( is_multisite() ) {
	global $wpdb;

	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );
		delete_option( 'sep_settings' );
		restore_current_blog();
	}
}
