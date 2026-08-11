<?php
/**
 * Public view — Staff/Coach Portal
 * Used by: [TRACKSUITE_staff_portal]
 * Variables available: $user, $is_coach, $is_staff, $coach_meets, $staff_record, $staff_payroll
 * @package TRACKSUITE_Membership
 */
defined( 'ABSPATH' ) || exit;

$event_options = [ '100m', '200m', '400m', '800m', '1500m', '3000m', '100m Hurdles', '110m Hurdles', '400m Hurdles', 'Long Jump', 'High Jump', 'Triple Jump', 'Shot Put', 'Discus', 'Javelin', '4x100 Relay', '4x400 Relay' ];
?>
<div class="ts-staff-portal">
    <div class="portal-header">
        <h1 class="portal-header__title">👋 Welcome, <?php echo esc_html( $user->display_name ); ?></h1>
    </div>

    <?php if ( $is_coach ) : ?>
    <section class="ts-staff-portal__section">
        <h2>🏟️ Upcoming &amp; Active Meets</h2>
        <?php if ( empty( $coach_meets ) ) : ?>
            <p class="ts-notice ts-notice--info">No upcoming or active meets right now.</p>
        <?php else : ?>
            <ul class="ts-staff-meet-list">
                <?php foreach ( $coach_meets as $meet ) : ?>
                    <li>
                        <strong><?php echo esc_html( $meet['name'] ); ?></strong>
                        — <?php echo $meet['meet_date'] ? esc_html( date_i18n( 'M j, Y', strtotime( $meet['meet_date'] ) ) ) : 'TBD'; ?>
                        <span class="ts-badge"><?php echo esc_html( ucfirst( $meet['status'] ) ); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <h3>Enter a Result</h3>
        <form class="ts-form" id="ts-staff-result-form" novalidate>
            <?php wp_nonce_field( 'TRACKSUITE_public_nonce', 'TRACKSUITE_nonce' ); ?>
            <input type="hidden" name="action" value="TRACKSUITE_staff_add_result">

            <div class="ts-form__row ts-form__row--2">
                <div class="ts-form__group">
                    <label for="staff_meet_id">Meet</label>
                    <select id="staff_meet_id" name="meet_id" required>
                        <option value="">— Select Meet —</option>
                        <?php foreach ( $coach_meets as $meet ) : ?>
                            <option value="<?php echo esc_attr( $meet['id'] ); ?>"><?php echo esc_html( $meet['name'] ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ts-form__group">
                    <label for="staff_athlete_id">Athlete ID</label>
                    <input type="number" id="staff_athlete_id" name="athlete_id" required placeholder="Athlete DB ID">
                </div>
            </div>

            <div class="ts-form__row ts-form__row--2">
                <div class="ts-form__group">
                    <label for="staff_event">Event</label>
                    <select id="staff_event" name="event_category" required>
                        <?php foreach ( $event_options as $evt ) : ?>
                            <option value="<?php echo esc_attr( $evt ); ?>"><?php echo esc_html( $evt ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ts-form__group">
                    <label for="staff_placement">Placement</label>
                    <input type="number" id="staff_placement" name="placement" min="1" placeholder="Optional">
                </div>
            </div>

            <div class="ts-form__row ts-form__row--2">
                <div class="ts-form__group">
                    <label for="staff_result_value">Result</label>
                    <input type="text" id="staff_result_value" name="result_value" required placeholder="e.g. 12.45 or 15'6&quot;">
                </div>
                <div class="ts-form__group">
                    <label for="staff_result_unit">Unit</label>
                    <select id="staff_result_unit" name="result_unit">
                        <option value="time">Time</option>
                        <option value="distance">Distance</option>
                        <option value="points">Points</option>
                    </select>
                </div>
            </div>

            <div class="ts-form__actions">
                <button type="submit" class="ts-btn ts-btn--primary">Save Result</button>
            </div>
            <div class="ts-form__feedback" aria-live="polite"></div>
        </form>
    </section>
    <?php endif; ?>

    <?php if ( $is_staff ) : ?>
    <section class="ts-staff-portal__section">
        <h2>💰 My Hours &amp; Pay</h2>
        <?php if ( ! $staff_record ) : ?>
            <p class="ts-notice ts-notice--info">No staff record is linked to your account yet — contact an admin.</p>
        <?php elseif ( empty( $staff_payroll ) ) : ?>
            <p class="ts-notice ts-notice--info">No payroll entries yet.</p>
        <?php else : ?>
            <table class="ts-table">
                <thead><tr><th>Period</th><th>Hours</th><th>Gross</th><th>Net</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ( $staff_payroll as $entry ) : ?>
                    <tr>
                        <td><?php echo esc_html( $entry['period_start'] . ' – ' . $entry['period_end'] ); ?></td>
                        <td><?php echo esc_html( $entry['hours_worked'] ); ?></td>
                        <td>$<?php echo number_format( (float) $entry['gross_pay'], 2 ); ?></td>
                        <td>$<?php echo number_format( (float) $entry['net_pay'], 2 ); ?></td>
                        <td><span class="ts-status ts-status-<?php echo esc_attr( $entry['status'] ); ?>"><?php echo esc_html( ucfirst( $entry['status'] ) ); ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
    <?php endif; ?>
</div>
