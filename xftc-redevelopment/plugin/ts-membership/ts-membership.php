<?php
/**
 * Plugin Name:       XFTC Membership
 * Plugin URI:        https://xtremeforcetrackclub.org/
 * Description:       Custom membership and athlete management system for Xtreme Force Track Club.
 * Version:           1.0.0
 * Author:            wordpresspluginsagent
 * Author URI:        https://dpoly2.github.io/AgentHarness/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ts-membership
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and update it as soon as you release a new version.
 */
define( 'TS_MEMBERSHIP_VERSION', '1.0.0' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing hooks.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-ts-membership.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then there isn't any need to explicitly call any action or filter hook.
 *
 * @since    1.0.0
 */
function run_ts_membership() {

    $plugin = new TS_Membership();
    $plugin->run();

}
run_ts_membership();

// Activation hook
function activate_ts_membership() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-ts-activator.php';
    TS_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_ts_membership' );

// Deactivation hook
function deactivate_ts_membership() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-ts-deactivator.php';
    TS_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_ts_membership' );
