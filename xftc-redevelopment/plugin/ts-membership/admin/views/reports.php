<?php
/**
 * Admin View — Reports
 * WP Admin → Xtreme Force → Reports
 *
 * @package TRACKSUITE_Membership
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$reports = new TRACKSUITE_Reports();
$tab     = in_array( $_GET['tab'] ?? '', TRACKSUITE_Reports::TYPES, true ) ? $_GET['tab'] : 'registration';
$data    = $reports->get( $tab );
if ( is_wp_error( $data ) ) {
    $data = [];
}
?>
<div class="wrap ts-admin-reports">
    <h1>📈 Reports</h1>

    <h2 class="nav-tab-wrapper">
        <?php foreach ( TRACKSUITE_Reports::TYPES as $type ) : ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=ts-reports&tab=' . $type ) ); ?>"
               class="nav-tab <?php echo $tab === $type ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html( ucfirst( $type ) ); ?>
            </a>
        <?php endforeach; ?>
    </h2>

    <?php if ( 'registration' === $tab ) : ?>
        <h3>Registrations by Season</h3>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>Season</th><th>Type</th><th>Total</th><th>Standard</th><th>Premium</th><th>Pending</th><th>Active</th><th>Cancelled</th></tr></thead>
            <tbody>
            <?php if ( empty( $data['by_season'] ) ) : ?>
                <tr><td colspan="8" style="text-align:center;"><em>No seasons yet.</em></td></tr>
            <?php else : foreach ( $data['by_season'] as $row ) : ?>
                <tr>
                    <td><?php echo esc_html( $row['name'] ); ?></td>
                    <td><?php echo esc_html( ucfirst( $row['type'] ) ); ?></td>
                    <td><?php echo (int) $row['total_registrations']; ?></td>
                    <td><?php echo (int) $row['standard_count']; ?></td>
                    <td><?php echo (int) $row['premium_count']; ?></td>
                    <td><?php echo (int) $row['pending_count']; ?></td>
                    <td><?php echo (int) $row['active_count']; ?></td>
                    <td><?php echo (int) $row['cancelled_count']; ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>

    <?php elseif ( 'financial' === $tab ) : ?>
        <h3>Membership Revenue</h3>
        <p>Due: $<?php echo number_format( (float) ( $data['membership_revenue']['total_due'] ?? 0 ), 2 ); ?>
           &nbsp;|&nbsp; Collected: $<?php echo number_format( (float) ( $data['membership_revenue']['total_paid'] ?? 0 ), 2 ); ?></p>

        <h3>Travel Revenue</h3>
        <p>Due: $<?php echo number_format( (float) ( $data['travel_revenue']['total_due'] ?? 0 ), 2 ); ?>
           &nbsp;|&nbsp; Collected: $<?php echo number_format( (float) ( $data['travel_revenue']['total_paid'] ?? 0 ), 2 ); ?></p>

        <?php if ( class_exists( 'WooCommerce' ) ) : ?>
        <h3>Store Revenue</h3>
        <p><?php echo (int) ( $data['store_revenue']['order_count'] ?? 0 ); ?> orders
           &nbsp;|&nbsp; Collected: $<?php echo number_format( (float) ( $data['store_revenue']['total_collected'] ?? 0 ), 2 ); ?></p>
        <?php endif; ?>

        <h3>Payroll Paid Out</h3>
        <p>$<?php echo number_format( (float) ( $data['payroll']['total_paid_out'] ?? 0 ), 2 ); ?></p>

        <h3>Payments by Gateway</h3>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>Gateway</th><th>Status</th><th>Count</th><th>Total</th></tr></thead>
            <tbody>
            <?php if ( empty( $data['payments_by_gateway'] ) ) : ?>
                <tr><td colspan="4" style="text-align:center;"><em>No payments recorded yet.</em></td></tr>
            <?php else : foreach ( $data['payments_by_gateway'] as $row ) : ?>
                <tr>
                    <td><?php echo esc_html( strtoupper( $row['gateway'] ) ); ?></td>
                    <td><?php echo esc_html( ucfirst( $row['status'] ) ); ?></td>
                    <td><?php echo (int) $row['count']; ?></td>
                    <td>$<?php echo number_format( (float) $row['total'], 2 ); ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>

    <?php elseif ( 'performance' === $tab ) : ?>
        <h3>Top Athletes (by PBs)</h3>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>Athlete</th><th>Total Results</th><th>Personal Bests</th><th>Club Records</th></tr></thead>
            <tbody>
            <?php if ( empty( $data['top_athletes'] ) ) : ?>
                <tr><td colspan="4" style="text-align:center;"><em>No results recorded yet.</em></td></tr>
            <?php else : foreach ( $data['top_athletes'] as $row ) : ?>
                <tr>
                    <td><?php echo esc_html( $row['first_name'] . ' ' . $row['last_name'] ); ?></td>
                    <td><?php echo (int) $row['total_results']; ?></td>
                    <td><?php echo (int) $row['pb_count']; ?></td>
                    <td><?php echo (int) $row['record_count']; ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>

        <h3>Participation by Meet</h3>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>Meet</th><th>Date</th><th>Entries</th></tr></thead>
            <tbody>
            <?php if ( empty( $data['participation_by_meet'] ) ) : ?>
                <tr><td colspan="3" style="text-align:center;"><em>No meets yet.</em></td></tr>
            <?php else : foreach ( $data['participation_by_meet'] as $row ) : ?>
                <tr>
                    <td><?php echo esc_html( $row['name'] ); ?></td>
                    <td><?php echo $row['meet_date'] ? esc_html( date( 'M j, Y', strtotime( $row['meet_date'] ) ) ) : '—'; ?></td>
                    <td><?php echo (int) $row['entry_count']; ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
