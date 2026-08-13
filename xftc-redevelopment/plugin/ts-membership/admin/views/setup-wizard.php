<?php
/**
 * Admin View — First-Run Setup Wizard
 * Reached via the post-activation redirect, or Settings > Setup Wizard link.
 * Lets a new club configure branding + their first season without touching code.
 *
 * @package TRACKSUITE_Membership
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap ts-setup-wizard" style="max-width:720px;margin:40px auto;">
    <h1><?php esc_html_e( '👋 Welcome — Let\'s Set Up Your Club', 'ts-membership' ); ?></h1>
    <p class="description"><?php esc_html_e( 'This plugin/theme pair works for any track club — just tell it who you are. You can change any of this later under Xtreme Force > Settings or Appearance > Customize.', 'ts-membership' ); ?></p>

    <?php if ( isset( $_POST['TRACKSUITE_wizard_nonce'] ) ) : ?>
        <div class="notice notice-success"><p>
            <strong><?php esc_html_e( 'All set!', 'ts-membership' ); ?></strong>
            <?php echo $created_season
                ? esc_html__( 'Your club details and first season are saved.', 'ts-membership' )
                : esc_html__( 'Your club details are saved.', 'ts-membership' ); ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=ts-dashboard' ) ); ?>"><?php esc_html_e( 'Go to Dashboard →', 'ts-membership' ); ?></a>
        </p></div>
    <?php endif; ?>

    <form method="post">
        <?php wp_nonce_field( 'TRACKSUITE_setup_wizard', 'TRACKSUITE_wizard_nonce' ); ?>

        <h2><?php esc_html_e( 'Club Details', 'ts-membership' ); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="TRACKSUITE_club_name"><?php esc_html_e( 'Club Name', 'ts-membership' ); ?></label></th>
                <td><input type="text" id="TRACKSUITE_club_name" name="TRACKSUITE_club_name" class="regular-text" required
                        value="<?php echo esc_attr( get_option( 'TRACKSUITE_club_name', '' ) ); ?>"></td>
            </tr>
            <tr>
                <th><label for="TRACKSUITE_admin_email"><?php esc_html_e( 'Club Admin Email', 'ts-membership' ); ?></label></th>
                <td><input type="email" id="TRACKSUITE_admin_email" name="TRACKSUITE_admin_email" class="regular-text" required
                        value="<?php echo esc_attr( get_option( 'TRACKSUITE_admin_email', get_option( 'admin_email' ) ) ); ?>"></td>
            </tr>
        </table>

        <h2><?php esc_html_e( 'Brand Colors', 'ts-membership' ); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="TRACKSUITE_color_gold"><?php esc_html_e( 'Accent Color', 'ts-membership' ); ?></label></th>
                <td><input type="text" id="TRACKSUITE_color_gold" name="TRACKSUITE_color_gold" value="<?php echo esc_attr( get_theme_mod( 'TRACKSUITE_color_gold', '#F5A623' ) ); ?>" class="ts-color-field"></td>
            </tr>
            <tr>
                <th><label for="TRACKSUITE_color_dark"><?php esc_html_e( 'Dark Background', 'ts-membership' ); ?></label></th>
                <td><input type="text" id="TRACKSUITE_color_dark" name="TRACKSUITE_color_dark" value="<?php echo esc_attr( get_theme_mod( 'TRACKSUITE_color_dark', '#1A1A2E' ) ); ?>" class="ts-color-field"></td>
            </tr>
            <tr>
                <th><label for="TRACKSUITE_color_blue"><?php esc_html_e( 'Secondary Accent', 'ts-membership' ); ?></label></th>
                <td><input type="text" id="TRACKSUITE_color_blue" name="TRACKSUITE_color_blue" value="<?php echo esc_attr( get_theme_mod( 'TRACKSUITE_color_blue', '#0F3460' ) ); ?>" class="ts-color-field"></td>
            </tr>
        </table>
        <p class="description"><?php esc_html_e( 'Full color picker + logo upload are available under Appearance > Customize.', 'ts-membership' ); ?></p>

        <h2><?php esc_html_e( 'Your First Season (optional)', 'ts-membership' ); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="season_name"><?php esc_html_e( 'Season Name', 'ts-membership' ); ?></label></th>
                <td><input type="text" id="season_name" name="season_name" class="regular-text" placeholder="e.g. 2026 Outdoor"></td>
            </tr>
            <tr>
                <th><label for="season_type"><?php esc_html_e( 'Season Type', 'ts-membership' ); ?></label></th>
                <td>
                    <select id="season_type" name="season_type">
                        <option value="indoor">Indoor</option>
                        <option value="outdoor" selected>Outdoor</option>
                        <option value="summer">Summer</option>
                        <option value="fall">Fall</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="season_start"><?php esc_html_e( 'Start / End Date', 'ts-membership' ); ?></label></th>
                <td>
                    <input type="date" id="season_start" name="season_start">
                    &mdash;
                    <input type="date" id="season_end" name="season_end">
                </td>
            </tr>
            <tr>
                <th><label for="season_fee_standard"><?php esc_html_e( 'Fees', 'ts-membership' ); ?></label></th>
                <td>
                    Standard: $<input type="number" id="season_fee_standard" name="season_fee_standard" step="0.01" min="0" class="small-text">
                    &nbsp; Premium: $<input type="number" name="season_fee_premium" step="0.01" min="0" class="small-text">
                </td>
            </tr>
        </table>

        <?php submit_button( __( 'Save & Finish Setup', 'ts-membership' ) ); ?>
    </form>
</div>
