<?php
/**
 * Fired when the plugin is deleted via WP Admin > Plugins.
 * Removes custom roles and plugin options. Custom DB tables are left in
 * place unless the site owner has opted in to full data removal, since
 * they contain athlete/payment records that should not vanish silently.
 *
 * @package TRACKSUITE_Membership
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

require_once __DIR__ . '/includes/class-ts-roles.php';
TRACKSUITE_Roles::remove_roles();

$options = [
    'TRACKSUITE_db_version',
    'TRACKSUITE_club_name',
    'TRACKSUITE_admin_email',
    'TRACKSUITE_stripe_mode',
    'TRACKSUITE_stripe_public_key',
    'TRACKSUITE_stripe_secret_key',
    'TRACKSUITE_stripe_test_mode',
    'TRACKSUITE_stripe_test_publishable_key',
    'TRACKSUITE_stripe_test_secret_key',
    'TRACKSUITE_stripe_live_publishable_key',
    'TRACKSUITE_stripe_live_secret_key',
    'TRACKSUITE_stripe_webhook_secret',
    'TRACKSUITE_travel_fee_bus',
    'TRACKSUITE_travel_fee_hotel',
    'TRACKSUITE_data_retention_years',
    'TRACKSUITE_setup_wizard_complete',
];
foreach ( $options as $option ) {
    delete_option( $option );
}

// Opt-in full data removal: only runs if the admin explicitly checked
// "Delete all XFTC data" in Settings before deleting the plugin.
if ( '1' === get_option( 'TRACKSUITE_delete_data_on_uninstall' ) ) {
    global $wpdb;
    $tables = [
        'TRACKSUITE_athletes', 'TRACKSUITE_seasons', 'TRACKSUITE_memberships',
        'TRACKSUITE_meets', 'TRACKSUITE_meet_entries', 'TRACKSUITE_results',
        'TRACKSUITE_travel', 'TRACKSUITE_staff', 'TRACKSUITE_payroll', 'TRACKSUITE_payments',
    ];
    foreach ( $tables as $table ) {
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$table}" );
    }
    delete_option( 'TRACKSUITE_delete_data_on_uninstall' );
}
