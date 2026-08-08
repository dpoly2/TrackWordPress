<?php
/**
 * PHPUnit bootstrap for ts-membership plugin tests.
 * Stubs required WordPress functions so unit tests run without a full WP environment.
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'WPINC', 'wp-includes' );

// Stub core WordPress functions used by the plugin.
if ( ! function_exists( 'plugin_dir_path' ) ) {
    function plugin_dir_path( $file ) {
        return trailingslashit( dirname( $file ) );
    }
}
if ( ! function_exists( 'trailingslashit' ) ) {
    function trailingslashit( $string ) {
        return rtrim( $string, '/\\' ) . '/';
    }
}
if ( ! function_exists( 'register_activation_hook' ) ) {
    function register_activation_hook( $file, $callback ) {}
}
if ( ! function_exists( 'register_deactivation_hook' ) ) {
    function register_deactivation_hook( $file, $callback ) {}
}
if ( ! function_exists( 'add_action' ) ) {
    function add_action( $hook, $callback, $priority = 10, $args = 1 ) {}
}
if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $hook, $callback, $priority = 10, $args = 1 ) {}
}
