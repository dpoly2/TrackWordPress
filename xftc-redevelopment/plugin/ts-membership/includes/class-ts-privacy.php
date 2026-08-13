<?php
/**
 * Class TRACKSUITE_Privacy
 *
 * Wires the plugin's athlete/payment/travel data into WordPress's built-in
 * privacy tools (Tools > Export Personal Data, Tools > Erase Personal Data),
 * suggests privacy-policy language, enforces an admin-visible SSL check, and
 * runs a scheduled data-retention pass.
 *
 * This implements the standard technical mechanisms WordPress provides for
 * handling personal data (GDPR-style export/erase, retention). It is not a
 * substitute for the club's own legal/privacy-policy review — the data here
 * includes information about minors, which carries its own considerations
 * beyond what an automated export/erase flow can satisfy.
 *
 * @package TRACKSUITE_Membership
 */

defined( 'ABSPATH' ) || exit;

class TRACKSUITE_Privacy {

    public function init(): void {
        add_filter( 'wp_privacy_personal_data_exporters', [ $this, 'register_exporter' ] );
        add_filter( 'wp_privacy_personal_data_erasers',   [ $this, 'register_eraser' ] );
        add_action( 'admin_init', [ $this, 'add_privacy_policy_content' ] );
        add_action( 'admin_notices', [ $this, 'maybe_show_ssl_notice' ] );

        add_action( 'TRACKSUITE_data_retention_cleanup', [ $this, 'run_retention_cleanup' ] );
        if ( ! wp_next_scheduled( 'TRACKSUITE_data_retention_cleanup' ) ) {
            wp_schedule_event( time(), 'daily', 'TRACKSUITE_data_retention_cleanup' );
        }
    }

    /** ─── EXPORT ────────────────────────────────────────────── */

    public function register_exporter( array $exporters ): array {
        $exporters['ts-membership'] = [
            'exporter_friendly_name' => __( 'XFTC Membership Data', 'ts-membership' ),
            'callback'                => [ $this, 'export_data' ],
        ];
        return $exporters;
    }

    public function export_data( string $email_address, int $page = 1 ): array {
        $user = get_user_by( 'email', $email_address );
        if ( ! $user ) {
            return [ 'data' => [], 'done' => true ];
        }

        global $wpdb;
        $export_items = [];

        // Athletes linked to this parent.
        $members  = new TRACKSUITE_Members();
        $athletes = $members->get_by_parent( $user->ID );
        foreach ( $athletes as $athlete ) {
            $export_items[] = [
                'group_id'    => 'ts-athletes',
                'group_label' => __( 'XFTC Athlete Profiles', 'ts-membership' ),
                'item_id'     => 'ts-athlete-' . $athlete->id,
                'data'        => [
                    [ 'name' => __( 'Name', 'ts-membership' ), 'value' => $athlete->first_name . ' ' . $athlete->last_name ],
                    [ 'name' => __( 'Date of Birth', 'ts-membership' ), 'value' => $athlete->dob ],
                    [ 'name' => __( 'School', 'ts-membership' ), 'value' => $athlete->school ],
                    [ 'name' => __( 'Team Level', 'ts-membership' ), 'value' => $athlete->team_level ],
                    [ 'name' => __( 'Emergency Contact', 'ts-membership' ), 'value' => $athlete->emergency_contact_name . ' — ' . $athlete->emergency_contact_phone ],
                ],
            ];
        }

        // Payments made by this user.
        $payments = ( new TRACKSUITE_Payments() )->get_payment_history( $user->ID );
        foreach ( $payments as $payment ) {
            $export_items[] = [
                'group_id'    => 'ts-payments',
                'group_label' => __( 'XFTC Payment History', 'ts-membership' ),
                'item_id'     => 'ts-payment-' . $payment['id'],
                'data'        => [
                    [ 'name' => __( 'Date', 'ts-membership' ), 'value' => $payment['created_at'] ],
                    [ 'name' => __( 'Type', 'ts-membership' ), 'value' => $payment['reference_type'] ],
                    [ 'name' => __( 'Amount', 'ts-membership' ), 'value' => '$' . number_format( (float) $payment['amount'], 2 ) ],
                    [ 'name' => __( 'Status', 'ts-membership' ), 'value' => $payment['status'] ],
                ],
            ];
        }

        // Travel bookings for this user's athletes.
        $travel_obj = new TRACKSUITE_Travel();
        foreach ( $athletes as $athlete ) {
            foreach ( $travel_obj->get_athlete_travel( (int) $athlete->id ) as $booking ) {
                $export_items[] = [
                    'group_id'    => 'ts-travel',
                    'group_label' => __( 'XFTC Travel Bookings', 'ts-membership' ),
                    'item_id'     => 'ts-travel-' . $booking['id'],
                    'data'        => [
                        [ 'name' => __( 'Meet', 'ts-membership' ), 'value' => $booking['meet_name'] ],
                        [ 'name' => __( 'Type', 'ts-membership' ), 'value' => $booking['travel_type'] ],
                        [ 'name' => __( 'Fee', 'ts-membership' ), 'value' => '$' . number_format( (float) $booking['travel_fee'], 2 ) ],
                    ],
                ];
            }
        }

        return [ 'data' => $export_items, 'done' => true ];
    }

    /** ─── ERASE ─────────────────────────────────────────────── */

    public function register_eraser( array $erasers ): array {
        $erasers['ts-membership'] = [
            'eraser_friendly_name' => __( 'XFTC Membership Data', 'ts-membership' ),
            'callback'              => [ $this, 'erase_data' ],
        ];
        return $erasers;
    }

    public function erase_data( string $email_address, int $page = 1 ): array {
        $user = get_user_by( 'email', $email_address );
        $messages = [];
        $removed  = false;

        if ( $user ) {
            global $wpdb;
            $members  = new TRACKSUITE_Members();
            $athletes = $members->get_by_parent( $user->ID );

            foreach ( $athletes as $athlete ) {
                // Anonymize rather than hard-delete: meet results/history referencing
                // this athlete_id stay intact for club records, but personal fields go.
                $wpdb->update( $wpdb->prefix . 'TRACKSUITE_athletes', [
                    'first_name'              => 'Redacted',
                    'last_name'               => 'Redacted',
                    'dob'                     => null,
                    'school'                  => null,
                    'emergency_contact_name'  => null,
                    'emergency_contact_phone' => null,
                ], [ 'id' => $athlete->id ] );
                $removed = true;
            }

            $messages[] = __( 'Athlete profile details have been redacted. Meet participation and results are retained in anonymized form for club records.', 'ts-membership' );
        }

        return [
            'items_removed'  => $removed,
            'items_retained' => false,
            'messages'       => $messages,
            'done'           => true,
        ];
    }

    /** ─── PRIVACY POLICY SUGGESTED CONTENT ──────────────────── */

    public function add_privacy_policy_content(): void {
        if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
            return;
        }

        $content = '<p class="privacy-policy-tutorial">' . esc_html__(
            'The following is suggested language for the XFTC Membership plugin. As this collects information about minors, review it with your own legal counsel before publishing.',
            'ts-membership'
        ) . '</p>';

        $content .= '<p>' . esc_html__(
            'When a parent/guardian registers an athlete, we collect: the athlete\'s name, date of birth, school, team level, and emergency contact information. We also retain a record of season registrations, meet participation, results, travel bookings, and payment history associated with the parent\'s account.',
            'ts-membership'
        ) . '</p>';

        $content .= '<p>' . esc_html__(
            'This data is used to administer club operations: registration, meet entry, results tracking, travel logistics, and fee collection. Payment card details are handled directly by our payment processor (Stripe) and are never stored on this site.',
            'ts-membership'
        ) . '</p>';

        wp_add_privacy_policy_content( 'XFTC Membership', wp_kses_post( wpautop( $content, false ) ) );
    }

    /** ─── SSL NOTICE ────────────────────────────────────────── */

    public function maybe_show_ssl_notice(): void {
        if ( is_ssl() || ! current_user_can( 'manage_options' ) ) {
            return;
        }
        echo '<div class="notice notice-warning"><p>'
            . esc_html__( 'XFTC Membership: this site is not being served over HTTPS. Athlete and payment data should never be collected over an unencrypted connection — enable SSL before going live.', 'ts-membership' )
            . '</p></div>';
    }

    /** ─── DATA RETENTION ────────────────────────────────────── */

    /**
     * Flag (does not delete) athletes with no season activity in the
     * configured retention window, by writing a usermeta-style marker the
     * admin can review on the Members screen. Deletion is a deliberate
     * separate action (Tools > Erase Personal Data), not automatic.
     */
    public function run_retention_cleanup(): void {
        $years = (int) get_option( 'TRACKSUITE_data_retention_years', 0 );
        if ( $years <= 0 ) {
            return; // Retention cleanup disabled.
        }

        global $wpdb;
        $cutoff = gmdate( 'Y-m-d', strtotime( "-{$years} years" ) );

        $stale_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT a.id FROM {$wpdb->prefix}TRACKSUITE_athletes a
             WHERE a.id NOT IN (
                SELECT athlete_id FROM {$wpdb->prefix}TRACKSUITE_memberships WHERE registered_at >= %s
             )
             AND a.created_at < %s",
            $cutoff, $cutoff
        ) );

        if ( $stale_ids ) {
            update_option( 'TRACKSUITE_stale_athlete_ids', $stale_ids );
        }
    }
}
