<?php
/**
 * Fired during plugin deactivation.
 *
 * @package TRACKSUITE_Membership
 */

defined( 'ABSPATH' ) || exit;

class TRACKSUITE_Deactivator {

    /**
     * Run on plugin deactivation.
     * Note: DB tables and data are preserved on deactivation.
     * Tables are only dropped on full uninstall (uninstall.php).
     */
    public static function deactivate() {
        $timestamp = wp_next_scheduled( 'TRACKSUITE_data_retention_cleanup' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'TRACKSUITE_data_retention_cleanup' );
        }
        flush_rewrite_rules();
    }
}

