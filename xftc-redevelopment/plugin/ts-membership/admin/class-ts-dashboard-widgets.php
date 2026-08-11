<?php
/**
 * Class TS_Dashboard_Widgets
 * Registers admin dashboard widgets: Upcoming meets, Recent payments, Payroll due, New registrations
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TS_Dashboard_Widgets {
    public static function init() {
        add_action( 'wp_dashboard_setup', [ __CLASS__, 'register_widgets' ] );
    }

    public static function register_widgets() {
        wp_add_dashboard_widget( 'ts_upcoming_meets', 'Upcoming Meets', [ __CLASS__, 'widget_upcoming_meets' ] );
        wp_add_dashboard_widget( 'ts_recent_payments', 'Recent Payments', [ __CLASS__, 'widget_recent_payments' ] );
        wp_add_dashboard_widget( 'ts_payroll_due', 'Payroll Due', [ __CLASS__, 'widget_payroll_due' ] );
        wp_add_dashboard_widget( 'ts_new_registrations', 'New Registrations', [ __CLASS__, 'widget_new_registrations' ] );
    }

    public static function widget_upcoming_meets() {
        $meets = ( new TRACKSUITE_Meets() )->get_upcoming_meets();
        if ( empty( $meets ) ) {
            echo '<p>No upcoming meets scheduled.</p>';
            return;
        }
        echo '<ul>';
        foreach ( array_slice( $meets, 0, 5 ) as $meet ) {
            printf(
                '<li><strong>%s</strong> — %s <a href="%s">View</a></li>',
                esc_html( $meet['name'] ),
                esc_html( $meet['meet_date'] ? date_i18n( 'M j, Y', strtotime( $meet['meet_date'] ) ) : 'TBD' ),
                esc_url( admin_url( 'admin.php?page=ts-meets' ) )
            );
        }
        echo '</ul>';
    }

    public static function widget_recent_payments() {
        $payments = ( new TRACKSUITE_Payments() )->get_all_payments( 5 );
        if ( empty( $payments ) ) {
            echo '<p>No payments recorded yet.</p>';
            return;
        }
        echo '<ul>';
        foreach ( $payments as $payment ) {
            printf(
                '<li>%s — <strong>$%s</strong> (%s, %s)</li>',
                esc_html( $payment['display_name'] ?? 'Unknown' ),
                number_format( (float) $payment['amount'], 2 ),
                esc_html( ucfirst( $payment['reference_type'] ) ),
                esc_html( ucfirst( $payment['status'] ) )
            );
        }
        echo '</ul>';
        printf( '<p><a href="%s">View all payments &rarr;</a></p>', esc_url( admin_url( 'admin.php?page=ts-payments' ) ) );
    }

    public static function widget_payroll_due() {
        $payroll  = new TRACKSUITE_Payroll();
        $pending  = $payroll->get_pending_payroll();
        $count    = $payroll->get_pending_count();
        if ( ! $count ) {
            echo '<p>No pending payroll entries.</p>';
            return;
        }
        $total_net = array_sum( array_column( $pending, 'net_pay' ) );
        printf(
            '<p><strong>%d</strong> pending entries totaling <strong>$%s</strong>.</p><p><a href="%s">Review payroll &rarr;</a></p>',
            (int) $count,
            number_format( (float) $total_net, 2 ),
            esc_url( admin_url( 'admin.php?page=ts-payroll' ) )
        );
    }

    public static function widget_new_registrations() {
        $recent = ( new TRACKSUITE_Meets() )->get_recent_registrations( 5 );
        if ( empty( $recent ) ) {
            echo '<p>No recent meet registrations.</p>';
            return;
        }
        echo '<ul>';
        foreach ( $recent as $entry ) {
            printf(
                '<li>%s %s &rarr; %s</li>',
                esc_html( $entry['first_name'] ),
                esc_html( $entry['last_name'] ),
                esc_html( $entry['meet_name'] )
            );
        }
        echo '</ul>';
    }
}

TS_Dashboard_Widgets::init();
