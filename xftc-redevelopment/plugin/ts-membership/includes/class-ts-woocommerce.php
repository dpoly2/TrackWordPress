<?php
/**
 * Class TRACKSUITE_WooCommerce
 *
 * Optional integration with WooCommerce for the club merchandise store
 * (uniforms, gear). Only loaded when WooCommerce is active — see
 * ts-membership.php. Sizes are handled entirely by WooCommerce's own
 * variable-product/attribute system, not reinvented here; this class only
 * adds the athlete name/number customization and syncs completed orders
 * into the same wp_TRACKSUITE_payments log used by memberships/travel so
 * admins see all club revenue in one place.
 *
 * @package TRACKSUITE_Membership
 */

defined( 'ABSPATH' ) || exit;

class TRACKSUITE_WooCommerce {

    /** Product category slug used to identify "customizable" club merch. */
    private const CUSTOM_CATEGORY = 'xftc-uniforms';

    public function init(): void {
        add_action( 'init', [ $this, 'register_uniform_category' ] );
        add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'render_customization_field' ] );
        add_filter( 'woocommerce_add_cart_item_data', [ $this, 'add_cart_item_data' ], 10, 2 );
        add_filter( 'woocommerce_get_item_data', [ $this, 'display_cart_item_data' ], 10, 2 );
        add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'add_order_item_meta' ], 10, 4 );
        add_action( 'woocommerce_order_status_completed', [ $this, 'sync_order_to_payments' ] );
    }

    /**
     * Ensure the "XFTC Uniforms" product category exists, so club staff have
     * a consistent place to put customizable merch (jerseys, singlets, etc.)
     * without any manual taxonomy setup.
     */
    public function register_uniform_category(): void {
        if ( ! taxonomy_exists( 'product_cat' ) || term_exists( self::CUSTOM_CATEGORY, 'product_cat' ) ) {
            return;
        }
        wp_insert_term( 'XFTC Uniforms', 'product_cat', [ 'slug' => self::CUSTOM_CATEGORY ] );
    }

    private function is_customizable_product( int $product_id ): bool {
        return has_term( self::CUSTOM_CATEGORY, 'product_cat', $product_id );
    }

    /**
     * Add a name/number field to the product page for uniform-category products.
     * Sizes are a normal WooCommerce variation attribute — not handled here.
     */
    public function render_customization_field(): void {
        global $product;
        if ( ! $product || ! $this->is_customizable_product( $product->get_id() ) ) {
            return;
        }
        ?>
        <div class="ts-uniform-customization">
            <p class="form-row form-row-wide">
                <label for="ts_athlete_name"><?php esc_html_e( 'Name for jersey (optional)', 'ts-membership' ); ?></label>
                <input type="text" id="ts_athlete_name" name="ts_athlete_name" maxlength="20" class="input-text">
            </p>
            <p class="form-row form-row-wide">
                <label for="ts_athlete_number"><?php esc_html_e( 'Number for jersey (optional)', 'ts-membership' ); ?></label>
                <input type="text" id="ts_athlete_number" name="ts_athlete_number" maxlength="3" class="input-text">
            </p>
        </div>
        <?php
    }

    public function add_cart_item_data( array $cart_item_data, int $product_id ): array {
        if ( ! empty( $_POST['ts_athlete_name'] ) ) {
            $cart_item_data['ts_athlete_name'] = sanitize_text_field( wp_unslash( $_POST['ts_athlete_name'] ) );
        }
        if ( ! empty( $_POST['ts_athlete_number'] ) ) {
            $cart_item_data['ts_athlete_number'] = sanitize_text_field( wp_unslash( $_POST['ts_athlete_number'] ) );
        }
        return $cart_item_data;
    }

    public function display_cart_item_data( array $item_data, array $cart_item ): array {
        if ( ! empty( $cart_item['ts_athlete_name'] ) ) {
            $item_data[] = [ 'name' => __( 'Jersey Name', 'ts-membership' ), 'value' => $cart_item['ts_athlete_name'] ];
        }
        if ( ! empty( $cart_item['ts_athlete_number'] ) ) {
            $item_data[] = [ 'name' => __( 'Jersey Number', 'ts-membership' ), 'value' => $cart_item['ts_athlete_number'] ];
        }
        return $item_data;
    }

    public function add_order_item_meta( \WC_Order_Item_Product $item, string $cart_item_key, array $values, \WC_Order $order ): void {
        if ( ! empty( $values['ts_athlete_name'] ) ) {
            $item->add_meta_data( __( 'Jersey Name', 'ts-membership' ), $values['ts_athlete_name'] );
        }
        if ( ! empty( $values['ts_athlete_number'] ) ) {
            $item->add_meta_data( __( 'Jersey Number', 'ts-membership' ), $values['ts_athlete_number'] );
        }
    }

    /**
     * Log completed store orders into wp_TRACKSUITE_payments (reference_type
     * 'uniform') so admins see merch revenue alongside membership/travel/payroll
     * in the same Payments screen and reports engine, instead of a second,
     * disconnected ledger living only in WooCommerce.
     */
    public function sync_order_to_payments( int $order_id ): void {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        global $wpdb;
        $payments_table = $wpdb->prefix . 'TRACKSUITE_payments';

        // Idempotent: an order can fire woocommerce_order_status_completed more
        // than once (e.g. manual status changes) — don't double-log it.
        $already_logged = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$payments_table} WHERE reference_type = 'uniform' AND reference_id = %d",
            $order_id
        ) );
        if ( $already_logged ) {
            return;
        }

        $user_id = $order->get_customer_id();
        if ( ! $user_id ) {
            return; // Guest checkout — nothing to attribute this to in the portal.
        }

        $wpdb->insert( $payments_table, [
            'user_id'        => $user_id,
            'reference_type' => 'uniform',
            'reference_id'   => $order_id,
            'amount'         => (float) $order->get_total(),
            'gateway'        => 'manual',
            'transaction_id' => $order->get_transaction_id() ?: ( 'WC-' . $order_id ),
            'status'         => 'completed',
            'created_at'     => current_time( 'mysql' ),
        ] );
    }

    /**
     * Order history for the logged-in parent's Store Orders portal tab.
     */
    public static function get_orders_for_user( int $user_id ): array {
        if ( ! function_exists( 'wc_get_orders' ) ) {
            return [];
        }
        return wc_get_orders( [
            'customer_id' => $user_id,
            'limit'       => 25,
            'orderby'     => 'date',
            'order'       => 'DESC',
        ] );
    }
}
