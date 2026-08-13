<?php defined( 'ABSPATH' ) || exit; ?>
<div class="wrap ts-admin-wrap">
    <h1 class="ts-page-title">
        <span class="dashicons dashicons-admin-generic"></span>
        <?php esc_html_e( 'XFTC Settings', 'ts-membership' ); ?>
    </h1>

    <?php settings_errors( 'TRACKSUITE_settings' ); ?>

    <form method="post">
        <?php wp_nonce_field( 'TRACKSUITE_settings_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e( 'Club Name', 'ts-membership' ); ?></th>
                <td><input type="text" name="TRACKSUITE_club_name" value="<?php echo esc_attr( get_option( 'TRACKSUITE_club_name' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Admin Email', 'ts-membership' ); ?></th>
                <td><input type="email" name="TRACKSUITE_admin_email" value="<?php echo esc_attr( get_option( 'TRACKSUITE_admin_email' ) ); ?>" class="regular-text"></td>
            </tr>
        </table>

        <h2><?php esc_html_e( 'Privacy & Data Retention', 'ts-membership' ); ?></h2>
        <p class="description"><?php esc_html_e( 'Athlete records include minors\' personal information. These settings control how long inactive records are kept and what happens if the plugin is removed.', 'ts-membership' ); ?></p>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e( 'Data Retention (years)', 'ts-membership' ); ?></th>
                <td>
                    <input type="number" min="0" name="TRACKSUITE_data_retention_years" value="<?php echo esc_attr( get_option( 'TRACKSUITE_data_retention_years', 3 ) ); ?>" class="small-text">
                    <p class="description"><?php esc_html_e( 'Athletes with no season activity for this many years are flagged for review/anonymization. 0 disables automatic retention cleanup.', 'ts-membership' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'On Uninstall', 'ts-membership' ); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="TRACKSUITE_delete_data_on_uninstall" value="1" <?php checked( get_option( 'TRACKSUITE_delete_data_on_uninstall' ), '1' ); ?>>
                        <?php esc_html_e( 'Permanently delete all XFTC data (athletes, payments, results, etc.) when this plugin is deleted from Plugins > Installed Plugins.', 'ts-membership' ); ?>
                    </label>
                    <p class="description"><?php esc_html_e( 'Leave unchecked to keep data in the database if the plugin is removed and later reinstalled.', 'ts-membership' ); ?></p>
                </td>
            </tr>
        </table>

        <p class="description">
            <?php echo wp_kses_post( sprintf(
                __( 'Stripe API keys are configured on the <a href="%s">Payments</a> screen.', 'ts-membership' ),
                esc_url( admin_url( 'admin.php?page=ts-payments' ) )
            ) ); ?>
        </p>
        <p class="description">
            <?php echo wp_kses_post( sprintf(
                __( 'Setting up a new club? Re-run the <a href="%s">Setup Wizard</a>.', 'ts-membership' ),
                esc_url( admin_url( 'admin.php?page=ts-setup-wizard' ) )
            ) ); ?>
        </p>

        <p class="submit">
            <input type="submit" name="TRACKSUITE_save_settings" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'ts-membership' ); ?>">
        </p>
    </form>
</div>

