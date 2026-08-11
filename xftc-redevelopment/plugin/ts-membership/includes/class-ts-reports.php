<?php
/**
 * Class TRACKSUITE_Reports
 *
 * Registration, financial, and performance reporting — pulls from the
 * memberships, payments, travel, payroll, and results tables. Used by
 * the admin Reports screen and the REST /reports/{type} endpoint.
 *
 * @package TRACKSUITE_Membership
 */

defined( 'ABSPATH' ) || exit;

class TRACKSUITE_Reports {

    /** @var string[] Report types this class can generate. */
    public const TYPES = [ 'registration', 'financial', 'performance' ];

    /**
     * Dispatch to the correct report by type.
     *
     * @return array|WP_Error
     */
    public function get( string $type ) {
        return match ( $type ) {
            'registration' => $this->registration_summary(),
            'financial'    => $this->financial_summary(),
            'performance'  => $this->performance_summary(),
            default        => new WP_Error( 'unknown_report', 'Unknown report type. Valid types: ' . implode( ', ', self::TYPES ) ),
        };
    }

    /**
     * Registrations broken down by season, tier, and status.
     */
    public function registration_summary( int $season_id = 0 ): array {
        global $wpdb;
        $memberships_table = $wpdb->prefix . 'TRACKSUITE_memberships';
        $seasons_table      = $wpdb->prefix . 'TRACKSUITE_seasons';

        $where = $season_id ? $wpdb->prepare( 'WHERE m.season_id = %d', $season_id ) : '';

        $by_season = $wpdb->get_results(
            "SELECT s.id, s.name, s.type,
                    COUNT(m.id) AS total_registrations,
                    SUM(m.tier = 'standard') AS standard_count,
                    SUM(m.tier = 'premium')  AS premium_count,
                    SUM(m.status = 'pending')   AS pending_count,
                    SUM(m.status = 'active')    AS active_count,
                    SUM(m.status = 'cancelled') AS cancelled_count
             FROM {$seasons_table} s
             LEFT JOIN {$memberships_table} m ON m.season_id = s.id
             GROUP BY s.id
             ORDER BY s.id DESC",
            ARRAY_A
        ) ?: [];

        $totals = $wpdb->get_row( "SELECT COUNT(*) AS total, SUM(status = 'active') AS active FROM {$memberships_table} {$where}", ARRAY_A )
            ?: [ 'total' => 0, 'active' => 0 ];

        return [
            'by_season' => $by_season,
            'totals'    => $totals,
        ];
    }

    /**
     * Revenue collected/outstanding across memberships, travel, and payments log.
     */
    public function financial_summary(): array {
        global $wpdb;
        $memberships_table = $wpdb->prefix . 'TRACKSUITE_memberships';
        $travel_table       = $wpdb->prefix . 'TRACKSUITE_travel';
        $payments_table     = $wpdb->prefix . 'TRACKSUITE_payments';
        $payroll_table      = $wpdb->prefix . 'TRACKSUITE_payroll';

        $membership_totals = $wpdb->get_row(
            "SELECT COALESCE(SUM(amount_due),0) AS total_due, COALESCE(SUM(amount_paid),0) AS total_paid
             FROM {$memberships_table}", ARRAY_A
        );

        $travel_totals = $wpdb->get_row(
            "SELECT COALESCE(SUM(travel_fee),0) AS total_due,
                    COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN travel_fee ELSE 0 END),0) AS total_paid
             FROM {$travel_table}", ARRAY_A
        );

        $by_gateway = $wpdb->get_results(
            "SELECT gateway, status, COUNT(*) AS count, COALESCE(SUM(amount),0) AS total
             FROM {$payments_table}
             GROUP BY gateway, status
             ORDER BY gateway", ARRAY_A
        ) ?: [];

        $payroll_totals = $wpdb->get_row(
            "SELECT COALESCE(SUM(net_pay),0) AS total_paid_out
             FROM {$payroll_table} WHERE status = 'paid'", ARRAY_A
        );

        // Store (WooCommerce uniform/gear) revenue is logged into the same
        // payments table by TRACKSUITE_WooCommerce::sync_order_to_payments().
        $store_totals = $wpdb->get_row(
            "SELECT COALESCE(SUM(amount),0) AS total_collected, COUNT(*) AS order_count
             FROM {$payments_table} WHERE reference_type = 'uniform' AND status = 'completed'", ARRAY_A
        );

        return [
            'membership_revenue' => $membership_totals,
            'travel_revenue'     => $travel_totals,
            'store_revenue'      => $store_totals,
            'payments_by_gateway'=> $by_gateway,
            'payroll'            => $payroll_totals,
        ];
    }

    /**
     * Club performance highlights: top PB earners, recent club records, participation by meet.
     */
    public function performance_summary(): array {
        global $wpdb;
        $results_table  = $wpdb->prefix . 'TRACKSUITE_results';
        $athletes_table = $wpdb->prefix . 'TRACKSUITE_athletes';
        $meets_table    = $wpdb->prefix . 'TRACKSUITE_meets';
        $entries_table  = $wpdb->prefix . 'TRACKSUITE_meet_entries';

        $top_athletes = $wpdb->get_results(
            "SELECT a.id, a.first_name, a.last_name,
                    COUNT(r.id) AS total_results,
                    SUM(r.is_personal_best) AS pb_count,
                    SUM(r.is_club_record)   AS record_count
             FROM {$results_table} r
             JOIN {$athletes_table} a ON r.athlete_id = a.id
             GROUP BY r.athlete_id
             ORDER BY pb_count DESC, record_count DESC
             LIMIT 10", ARRAY_A
        ) ?: [];

        $club_records = ( new TRACKSUITE_Results() )->get_club_records();

        $participation_by_meet = $wpdb->get_results(
            "SELECT m.id, m.name, m.meet_date, COUNT(e.id) AS entry_count
             FROM {$meets_table} m
             LEFT JOIN {$entries_table} e ON e.meet_id = m.id
             GROUP BY m.id
             ORDER BY m.meet_date DESC
             LIMIT 10", ARRAY_A
        ) ?: [];

        return [
            'top_athletes'           => $top_athletes,
            'club_records'           => $club_records,
            'participation_by_meet'  => $participation_by_meet,
        ];
    }
}
