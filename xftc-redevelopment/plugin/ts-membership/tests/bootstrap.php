<?php
/**
 * PHPUnit bootstrap for ts-membership plugin tests.
 *
 * This is intentionally a hand-rolled WordPress stub, not a full WP test
 * install (no MySQL/wp-phpunit available in CI) — but it's a *real* hook/
 * filter/REST-route registry, not a no-op stub, specifically so that
 * requiring the plugin's actual bootstrap file (ts-membership.php) and
 * firing plugins_loaded/init/rest_api_init here will fatal loudly if any
 * require_once target is missing or any class method referenced doesn't
 * exist — which is exactly the class of bug (renamed files, mismatched
 * method names) that shipped previously and went undetected by CI.
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'WPINC', 'wp-includes' );
define( 'OBJECT', 'OBJECT' );
define( 'OBJECT_K', 'OBJECT_K' );
define( 'ARRAY_A', 'ARRAY_A' );
define( 'ARRAY_N', 'ARRAY_N' );
define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'DAY_IN_SECONDS', 86400 );

require_once __DIR__ . '/fakes/FakeWpdb.php';
require_once __DIR__ . '/fakes/wp-error.php';

// ─── Hook / filter registries ─────────────────────────────────────────────
// Real enough to exercise the plugin's actual init flow, not a no-op.
$GLOBALS['ts_test_actions'] = [];
$GLOBALS['ts_test_filters'] = [];
$GLOBALS['ts_test_shortcodes'] = [];
$GLOBALS['ts_test_rest_routes'] = [];
$GLOBALS['ts_test_options'] = [];
$GLOBALS['ts_test_theme_mods'] = [];
$GLOBALS['ts_test_current_user_id'] = 0;
$GLOBALS['ts_test_user_caps'] = [];
$GLOBALS['ts_test_logged_in'] = false;

if ( ! function_exists( 'plugin_dir_path' ) ) {
    function plugin_dir_path( $file ) { return trailingslashit( dirname( $file ) ); }
}
if ( ! function_exists( 'plugin_dir_url' ) ) {
    function plugin_dir_url( $file ) { return 'https://example.test/wp-content/plugins/' . basename( dirname( $file ) ) . '/'; }
}
if ( ! function_exists( 'plugin_basename' ) ) {
    function plugin_basename( $file ) { return basename( dirname( $file ) ) . '/' . basename( $file ); }
}
if ( ! function_exists( 'trailingslashit' ) ) {
    function trailingslashit( $string ) { return rtrim( $string, '/\\' ) . '/'; }
}

// ─── Activation / hooks ────────────────────────────────────────────────────
if ( ! function_exists( 'register_activation_hook' ) ) {
    function register_activation_hook( $file, $callback ) {}
}
if ( ! function_exists( 'register_deactivation_hook' ) ) {
    function register_deactivation_hook( $file, $callback ) {}
}
if ( ! function_exists( 'add_action' ) ) {
    function add_action( $hook, $callback, $priority = 10, $args = 1 ) {
        $GLOBALS['ts_test_actions'][ $hook ][] = $callback;
    }
}
if ( ! function_exists( 'do_action' ) ) {
    function do_action( $hook, ...$args ) {
        foreach ( $GLOBALS['ts_test_actions'][ $hook ] ?? [] as $callback ) {
            call_user_func_array( $callback, $args );
        }
    }
}
if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $hook, $callback, $priority = 10, $args = 1 ) {
        $GLOBALS['ts_test_filters'][ $hook ][] = $callback;
    }
}
if ( ! function_exists( 'apply_filters' ) ) {
    function apply_filters( $hook, $value, ...$args ) {
        foreach ( $GLOBALS['ts_test_filters'][ $hook ] ?? [] as $callback ) {
            $value = call_user_func_array( $callback, array_merge( [ $value ], $args ) );
        }
        return $value;
    }
}
if ( ! function_exists( 'remove_action' ) ) {
    function remove_action( $hook, $callback, $priority = 10 ) {}
}

// ─── Shortcodes ────────────────────────────────────────────────────────────
if ( ! function_exists( 'add_shortcode' ) ) {
    function add_shortcode( $tag, $callback ) { $GLOBALS['ts_test_shortcodes'][ $tag ] = $callback; }
}
if ( ! function_exists( 'shortcode_atts' ) ) {
    function shortcode_atts( $defaults, $atts, $shortcode = '' ) { return array_merge( $defaults, (array) $atts ); }
}

// ─── REST API ──────────────────────────────────────────────────────────────
if ( ! function_exists( 'register_rest_route' ) ) {
    function register_rest_route( $namespace, $route, $args = [] ) {
        $GLOBALS['ts_test_rest_routes'][] = [ 'namespace' => $namespace, 'route' => $route, 'args' => $args ];
    }
}

// ─── Roles / capabilities ──────────────────────────────────────────────────
if ( ! function_exists( 'add_role' ) ) {
    function add_role( $role, $display_name, $capabilities = [] ) {}
}
if ( ! function_exists( 'get_role' ) ) {
    function get_role( $role ) { return null; }
}
if ( ! function_exists( 'remove_role' ) ) {
    function remove_role( $role ) {}
}
if ( ! function_exists( 'current_user_can' ) ) {
    function current_user_can( $cap ) {
        return in_array( $cap, $GLOBALS['ts_test_user_caps'], true );
    }
}
if ( ! function_exists( 'user_can' ) ) {
    function user_can( $user_id, $cap ) {
        return current_user_can( $cap );
    }
}
if ( ! function_exists( 'is_user_logged_in' ) ) {
    function is_user_logged_in() { return $GLOBALS['ts_test_logged_in']; }
}
if ( ! function_exists( 'get_current_user_id' ) ) {
    function get_current_user_id() { return $GLOBALS['ts_test_current_user_id']; }
}
if ( ! function_exists( 'is_admin' ) ) {
    function is_admin() { return false; }
}
if ( ! function_exists( 'wp_doing_ajax' ) ) {
    function wp_doing_ajax() { return false; }
}

// ─── Options / theme mods ───────────────────────────────────────────────────
if ( ! function_exists( 'get_option' ) ) {
    function get_option( $key, $default = false ) {
        if ( $key === 'TRACKSUITE_db_version' && ! array_key_exists( $key, $GLOBALS['ts_test_options'] ) ) {
            // Pretend the DB is already up to date so maybe_upgrade() (which
            // needs a real wp-admin/includes/upgrade.php + dbDelta()) no-ops.
            return defined( 'TRACKSUITE_VERSION' ) ? TRACKSUITE_VERSION : $default;
        }
        return $GLOBALS['ts_test_options'][ $key ] ?? $default;
    }
}
if ( ! function_exists( 'update_option' ) ) {
    function update_option( $key, $value ) { $GLOBALS['ts_test_options'][ $key ] = $value; return true; }
}
if ( ! function_exists( 'add_option' ) ) {
    function add_option( $key, $value = '' ) {
        if ( ! array_key_exists( $key, $GLOBALS['ts_test_options'] ) ) { $GLOBALS['ts_test_options'][ $key ] = $value; }
        return true;
    }
}
if ( ! function_exists( 'delete_option' ) ) {
    function delete_option( $key ) { unset( $GLOBALS['ts_test_options'][ $key ] ); return true; }
}
if ( ! function_exists( 'get_theme_mod' ) ) {
    function get_theme_mod( $key, $default = false ) { return $GLOBALS['ts_test_theme_mods'][ $key ] ?? $default; }
}
if ( ! function_exists( 'set_theme_mod' ) ) {
    function set_theme_mod( $key, $value ) { $GLOBALS['ts_test_theme_mods'][ $key ] = $value; }
}

// ─── Cron ──────────────────────────────────────────────────────────────────
if ( ! function_exists( 'wp_next_scheduled' ) ) {
    function wp_next_scheduled( $hook ) { return false; }
}
if ( ! function_exists( 'wp_schedule_event' ) ) {
    function wp_schedule_event( $timestamp, $recurrence, $hook ) {}
}
if ( ! function_exists( 'wp_unschedule_event' ) ) {
    function wp_unschedule_event( $timestamp, $hook ) {}
}

// ─── Sanitization / escaping (real-enough minimal implementations) ─────────
if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field( $str ) { return trim( strip_tags( (string) $str ) ); }
}
if ( ! function_exists( 'sanitize_textarea_field' ) ) {
    function sanitize_textarea_field( $str ) { return trim( (string) $str ); }
}
if ( ! function_exists( 'sanitize_email' ) ) {
    function sanitize_email( $str ) { return filter_var( (string) $str, FILTER_SANITIZE_EMAIL ); }
}
if ( ! function_exists( 'sanitize_key' ) ) {
    function sanitize_key( $str ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', (string) $str ) ); }
}
if ( ! function_exists( 'sanitize_user' ) ) {
    function sanitize_user( $str ) { return preg_replace( '/[^a-zA-Z0-9_.\-@]/', '', (string) $str ); }
}
if ( ! function_exists( 'sanitize_hex_color' ) ) {
    function sanitize_hex_color( $color ) { return preg_match( '/^#[0-9a-fA-F]{3,6}$/', (string) $color ) ? $color : ''; }
}
if ( ! function_exists( 'sanitize_file_name' ) ) {
    function sanitize_file_name( $name ) { return preg_replace( '/[^a-zA-Z0-9_\-\.]/', '-', (string) $name ); }
}
if ( ! function_exists( 'wp_unslash' ) ) {
    function wp_unslash( $value ) { return $value; }
}
if ( ! function_exists( 'absint' ) ) {
    function absint( $n ) { return abs( (int) $n ); }
}
if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
}
if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
}
if ( ! function_exists( 'esc_url' ) ) {
    function esc_url( $s ) { return filter_var( (string) $s, FILTER_SANITIZE_URL ); }
}
if ( ! function_exists( 'esc_url_raw' ) ) {
    function esc_url_raw( $s ) { return filter_var( (string) $s, FILTER_SANITIZE_URL ); }
}
if ( ! function_exists( 'wp_kses_post' ) ) {
    function wp_kses_post( $s ) { return $s; }
}
if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = 'default' ) { return $text; }
}
if ( ! function_exists( '_e' ) ) {
    function _e( $text, $domain = 'default' ) { echo $text; }
}
if ( ! function_exists( 'esc_html__' ) ) {
    function esc_html__( $text, $domain = 'default' ) { return esc_html( $text ); }
}
if ( ! function_exists( 'esc_html_e' ) ) {
    function esc_html_e( $text, $domain = 'default' ) { echo esc_html( $text ); }
}

// ─── Misc runtime helpers used at class-load/init time ─────────────────────
if ( ! function_exists( 'wp_json_encode' ) ) {
    function wp_json_encode( $data ) { return json_encode( $data ); }
}
if ( ! function_exists( 'current_time' ) ) {
    function current_time( $type = 'mysql' ) { return $type === 'timestamp' ? time() : date( 'Y-m-d H:i:s' ); }
}
if ( ! function_exists( 'home_url' ) ) {
    function home_url( $path = '' ) { return 'https://example.test' . $path; }
}
if ( ! function_exists( 'admin_url' ) ) {
    function admin_url( $path = '' ) { return 'https://example.test/wp-admin/' . $path; }
}
if ( ! function_exists( 'get_rest_url' ) ) {
    function get_rest_url( $blog_id = null, $path = '' ) { return 'https://example.test/wp-json/' . ltrim( $path, '/' ); }
}
if ( ! function_exists( 'wp_rand' ) ) {
    function wp_rand( $min = 0, $max = 0 ) { return random_int( $min, $max ?: mt_getrandmax() ); }
}
if ( ! function_exists( 'flush_rewrite_rules' ) ) {
    function flush_rewrite_rules() {}
}
if ( ! function_exists( 'wp_mail' ) ) {
    function wp_mail( $to, $subject, $message, $headers = '' ) { return true; }
}
if ( ! function_exists( 'get_option_users_can_register' ) ) {
    // placeholder to keep the list grep-able; intentionally unused.
}

// ─── $wpdb ──────────────────────────────────────────────────────────────────
global $wpdb;
$wpdb = new FakeWpdb();

// ─── Load the real plugin and run its actual boot sequence ─────────────────
// This is the point of the whole stub: if any include target is missing, or
// any TRACKSUITE_* class method referenced elsewhere doesn't exist, this
// require (and the do_action calls below) will fatal here, in CI.
require_once dirname( __DIR__ ) . '/ts-membership.php';

do_action( 'plugins_loaded' );
do_action( 'init' );
do_action( 'rest_api_init' );
