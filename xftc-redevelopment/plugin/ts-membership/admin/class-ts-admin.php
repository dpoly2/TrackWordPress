<?php
/**
 * Admin panel — registers WP Admin menus, pages, and assets.
 *
 * @package TRACKSUITE_Membership
 */

defined( 'ABSPATH' ) || exit;

class TRACKSUITE_Admin {

    public function init() {
        add_action( 'admin_menu',            [ $this, 'register_menus' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'admin_init',            [ $this, 'maybe_redirect_to_wizard' ] );
    }

    /**
     * Send a freshly-activated site straight to the setup wizard, once,
     * so a new club can configure their name/colors/first season without
     * touching any code — the concrete "packaged for use by other AAU
     * clubs" deliverable. Skipped on bulk activation, same as WooCommerce
     * and similar plugins do it.
     */
    public function maybe_redirect_to_wizard() {
        if ( ! get_transient( 'TRACKSUITE_activation_redirect' ) ) {
            return;
        }
        delete_transient( 'TRACKSUITE_activation_redirect' );

        if ( isset( $_GET['activate-multi'] ) || wp_doing_ajax() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        wp_safe_redirect( admin_url( 'admin.php?page=ts-setup-wizard' ) );
        exit;
    }

    /**
     * Register the top-level WP Admin menu and sub-menus.
     */
    public function register_menus() {
        // Top-level menu
        add_menu_page(
            __( 'Xtreme Force', 'ts-membership' ),
            __( 'Xtreme Force', 'ts-membership' ),
            'TRACKSUITE_manage_members',
            'ts-dashboard',
            [ $this, 'page_dashboard' ],
            'dashicons-groups',
            30
        );

        // Dashboard (same as top-level)
        add_submenu_page(
            'ts-dashboard',
            __( 'Dashboard', 'ts-membership' ),
            __( 'Dashboard', 'ts-membership' ),
            'TRACKSUITE_manage_members',
            'ts-dashboard',
            [ $this, 'page_dashboard' ]
        );

        // Members
        add_submenu_page(
            'ts-dashboard',
            __( 'Members', 'ts-membership' ),
            __( 'Members', 'ts-membership' ),
            'TRACKSUITE_manage_members',
            'ts-members',
            [ $this, 'page_members' ]
        );

        // Seasons
        add_submenu_page(
            'ts-dashboard',
            __( 'Seasons', 'ts-membership' ),
            __( 'Seasons', 'ts-membership' ),
            'TRACKSUITE_manage_seasons',
            'ts-seasons',
            [ $this, 'page_seasons' ]
        );

        // Meets
        add_submenu_page(
            'ts-dashboard',
            __( 'Meets', 'ts-membership' ),
            __( 'Meets', 'ts-membership' ),
            'TRACKSUITE_manage_meets',
            'ts-meets',
            [ $this, 'page_meets' ]
        );

        // Travel
        add_submenu_page(
            'ts-dashboard',
            __( 'Travel', 'ts-membership' ),
            __( 'Travel', 'ts-membership' ),
            'TRACKSUITE_manage_travel',
            'ts-travel',
            [ $this, 'page_travel' ]
        );

        // Results
        add_submenu_page(
            'ts-dashboard',
            __( 'Results', 'ts-membership' ),
            __( 'Results', 'ts-membership' ),
            'TRACKSUITE_enter_results',
            'ts-results',
            [ $this, 'page_results' ]
        );

        // Payroll
        add_submenu_page(
            'ts-dashboard',
            __( 'Payroll', 'ts-membership' ),
            __( 'Payroll', 'ts-membership' ),
            'TRACKSUITE_manage_payroll',
            'ts-payroll',
            [ $this, 'page_payroll' ]
        );

        // Payments
        add_submenu_page(
            'ts-dashboard',
            __( 'Payments', 'ts-membership' ),
            __( 'Payments', 'ts-membership' ),
            'TRACKSUITE_manage_payments',
            'ts-payments',
            [ $this, 'page_payments' ]
        );

        // Reports
        add_submenu_page(
            'ts-dashboard',
            __( 'Reports', 'ts-membership' ),
            __( 'Reports', 'ts-membership' ),
            'TRACKSUITE_view_reports',
            'ts-reports',
            [ $this, 'page_reports' ]
        );

        // Settings
        add_submenu_page(
            'ts-dashboard',
            __( 'Settings', 'ts-membership' ),
            __( 'Settings', 'ts-membership' ),
            'TRACKSUITE_manage_settings',
            'ts-settings',
            [ $this, 'page_settings' ]
        );

        // Setup Wizard — reachable via the activation redirect or Settings,
        // intentionally not shown in the menu itself (parent slug: null).
        add_submenu_page(
            null,
            __( 'XFTC Setup Wizard', 'ts-membership' ),
            __( 'Setup Wizard', 'ts-membership' ),
            'manage_options',
            'ts-setup-wizard',
            [ $this, 'page_setup_wizard' ]
        );
    }

    /**
     * Enqueue admin CSS and JS.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_assets( string $hook ) {
        if ( strpos( $hook, 'ts-' ) === false ) {
            return;
        }
        wp_enqueue_style(
            'ts-admin',
            TRACKSUITE_PLUGIN_URL . 'admin/assets/admin.css',
            [],
            TRACKSUITE_VERSION
        );
        wp_enqueue_script(
            'ts-admin',
            TRACKSUITE_PLUGIN_URL . 'admin/assets/admin.js',
            [ 'jquery' ],
            TRACKSUITE_VERSION,
            true
        );
        wp_localize_script( 'ts-admin', 'xftcAdmin', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'TRACKSUITE_admin_nonce' ),
        ] );
    }

    // ── Page Renderers ────────────────────────────────────────────────────────

    public function page_dashboard() {
        $members  = new TRACKSUITE_Members();
        $seasons  = new TRACKSUITE_Seasons();
        $total    = $members->count();
        $active   = $seasons->get_active();
        include TRACKSUITE_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    public function page_members() {
        $members = new TRACKSUITE_Members();
        $search  = sanitize_text_field( $_GET['s'] ?? '' );
        $list    = $members->get_all( [ 'search' => $search, 'limit' => 50 ] );
        include TRACKSUITE_PLUGIN_DIR . 'admin/views/members.php';
    }

    public function page_seasons() {
        $seasons = new TRACKSUITE_Seasons();
        $list    = $seasons->get_all();
        include TRACKSUITE_PLUGIN_DIR . 'admin/views/seasons.php';
    }

    public function page_meets() {
        include TRACKSUITE_PLUGIN_DIR . 'admin/views/meets.php';
    }

    public function page_travel() {
        include TRACKSUITE_PLUGIN_DIR . 'admin/views/travel.php';
    }

    public function page_payroll() {
        include TRACKSUITE_PLUGIN_DIR . 'admin/views/payroll.php';
    }

    public function page_payments() {
        include TRACKSUITE_PLUGIN_DIR . 'admin/views/payments.php';
    }

    public function page_results() {
        include TRACKSUITE_PLUGIN_DIR . 'admin/views/results.php';
    }

    public function page_reports() {
        include TRACKSUITE_PLUGIN_DIR . 'admin/views/reports.php';
    }

    public function page_settings() {
        // Stripe keys are configured on the dedicated Payments screen (admin/views/payments.php),
        // which uses the test/live key pairs TRACKSUITE_Payments actually reads.
        if ( isset( $_POST['TRACKSUITE_save_settings'] ) && check_admin_referer( 'TRACKSUITE_settings_nonce' ) ) {
            update_option( 'TRACKSUITE_club_name',   sanitize_text_field( $_POST['TRACKSUITE_club_name'] ?? '' ) );
            update_option( 'TRACKSUITE_admin_email', sanitize_email( $_POST['TRACKSUITE_admin_email'] ?? '' ) );
            update_option( 'TRACKSUITE_delete_data_on_uninstall', isset( $_POST['TRACKSUITE_delete_data_on_uninstall'] ) ? '1' : '0' );
            update_option( 'TRACKSUITE_data_retention_years', absint( $_POST['TRACKSUITE_data_retention_years'] ?? 0 ) );
            add_settings_error( 'TRACKSUITE_settings', 'saved', __( 'Settings saved.', 'ts-membership' ), 'success' );
        }
        include TRACKSUITE_PLUGIN_DIR . 'admin/views/settings.php';
    }

    public function page_setup_wizard() {
        $created_season = false;

        if ( isset( $_POST['TRACKSUITE_wizard_nonce'] ) && check_admin_referer( 'TRACKSUITE_setup_wizard' ) ) {
            update_option( 'TRACKSUITE_club_name',   sanitize_text_field( $_POST['TRACKSUITE_club_name'] ?? '' ) );
            update_option( 'TRACKSUITE_admin_email', sanitize_email( $_POST['TRACKSUITE_admin_email'] ?? '' ) );

            set_theme_mod( 'TRACKSUITE_color_gold', sanitize_hex_color( $_POST['TRACKSUITE_color_gold'] ?? '' ) ?: '#F5A623' );
            set_theme_mod( 'TRACKSUITE_color_dark', sanitize_hex_color( $_POST['TRACKSUITE_color_dark'] ?? '' ) ?: '#1A1A2E' );
            set_theme_mod( 'TRACKSUITE_color_blue', sanitize_hex_color( $_POST['TRACKSUITE_color_blue'] ?? '' ) ?: '#0F3460' );

            if ( ! empty( $_POST['season_name'] ) ) {
                $seasons = new TRACKSUITE_Seasons();
                $result  = $seasons->create( [
                    'name'         => sanitize_text_field( $_POST['season_name'] ),
                    'type'         => sanitize_text_field( $_POST['season_type'] ?? 'outdoor' ),
                    'start_date'   => sanitize_text_field( $_POST['season_start'] ?? '' ),
                    'end_date'     => sanitize_text_field( $_POST['season_end'] ?? '' ),
                    'fee_standard' => (float) ( $_POST['season_fee_standard'] ?? 0 ),
                    'fee_premium'  => (float) ( $_POST['season_fee_premium'] ?? 0 ),
                    'is_active'    => 1,
                ] );
                $created_season = ! is_wp_error( $result );
            }

            update_option( 'TRACKSUITE_setup_wizard_complete', '1' );
        }

        include TRACKSUITE_PLUGIN_DIR . 'admin/views/setup-wizard.php';
    }
}

