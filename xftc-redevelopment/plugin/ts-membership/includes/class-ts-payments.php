<?php
/**
 * Class TRACKSUITE_Payments
 *
 * Handles Stripe Checkout session creation, webhook processing,
 * and manual payment entry for XFTC membership, travel, and meet fees.
 *
 * @package TRACKSUITE_Membership
 * @since   0.2.0
 *
 * ─── STRIPE SETUP REQUIRED ───────────────────────────────────────────────────
 * Before this class will function, you must:
 *
 * 1. Create a Stripe account at https://dashboard.stripe.com/register
 * 2. Retrieve your API keys from https://dashboard.stripe.com/apikeys
 * 3. Add keys to WordPress via WP Admin → Xtreme Force → Settings → Payments:
 *    - Publishable Key (pk_live_... or pk_test_... for testing)
 *    - Secret Key     (sk_live_... or sk_test_... for testing)
 * 4. Set your Webhook Secret from https://dashboard.stripe.com/webhooks
 *    - Endpoint URL: https://xtremeforcetrackclub.org/wp-json/xftc/v1/payments/webhook
 *    - Events to listen for:
 *        checkout.session.completed
 *        payment_intent.payment_failed
 * 5. Run `composer install` in the plugin root (installs stripe/stripe-php).
 * ─────────────────────────────────────────────────────────────────────────────
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TRACKSUITE_Payments {

    private string $publishable_key;
    private string $secret_key;
    private string $webhook_secret;
    private bool $test_mode;
    private string $payments_table;
    private string $memberships_table;
    private string $travel_table;

    public function __construct() {
        global $wpdb;
        $this->test_mode       = (bool) get_option( 'TRACKSUITE_stripe_test_mode', true );
        $this->publishable_key = $this->test_mode
            ? get_option( 'TRACKSUITE_stripe_test_publishable_key', '' )
            : get_option( 'TRACKSUITE_stripe_live_publishable_key', '' );
        $this->secret_key      = $this->test_mode
            ? get_option( 'TRACKSUITE_stripe_test_secret_key', '' )
            : get_option( 'TRACKSUITE_stripe_live_secret_key', '' );
        $this->webhook_secret  = get_option( 'TRACKSUITE_stripe_webhook_secret', '' );

        $this->payments_table    = $wpdb->prefix . 'TRACKSUITE_payments';
        $this->memberships_table = $wpdb->prefix . 'TRACKSUITE_memberships';
        $this->travel_table      = $wpdb->prefix . 'TRACKSUITE_travel';
    }

    /**
     * Check if Stripe is configured and ready to use.
     */
    public function is_configured(): bool {
        return ! empty( $this->secret_key ) && ! empty( $this->publishable_key );
    }

    /**
     * Load the Stripe PHP SDK.
     * Looks for Composer autoloader first, then manual vendor path.
     */
    private function load_stripe_sdk(): bool {
        $composer_autoload = TRACKSUITE_PLUGIN_DIR . 'vendor/autoload.php';
        if ( file_exists( $composer_autoload ) ) {
            require_once $composer_autoload;
            return class_exists( '\Stripe\Stripe' );
        }

        $manual_sdk = TRACKSUITE_PLUGIN_DIR . 'vendor/stripe/stripe-php/init.php';
        if ( file_exists( $manual_sdk ) ) {
            require_once $manual_sdk;
            return class_exists( '\Stripe\Stripe' );
        }

        error_log( 'TS Payments: Stripe PHP SDK not found. Run `composer install` in the plugin root.' );
        return false;
    }

    /**
     * Resolve the amount owed and the WP user who owns the underlying record,
     * looked up server-side from the DB — the amount charged and who it's billed
     * to must never be trusted from client-supplied request data, or a caller
     * could under-pay (fake `amount`) or mark another family's balance paid
     * (fake `reference_id`).
     *
     * @return array{amount:float,owner_id:int,description:string}|WP_Error
     */
    private function resolve_reference( string $type, int $reference_id ) {
        global $wpdb;

        if ( 'membership' === $type ) {
            $row = $wpdb->get_row( $wpdb->prepare(
                "SELECT m.amount_due, m.amount_paid, a.parent_id, a.first_name, a.last_name, s.name AS season_name
                 FROM {$this->memberships_table} m
                 JOIN {$wpdb->prefix}TRACKSUITE_athletes a ON m.athlete_id = a.id
                 JOIN {$wpdb->prefix}TRACKSUITE_seasons s  ON m.season_id  = s.id
                 WHERE m.id = %d", $reference_id
            ) );
            if ( ! $row ) return new \WP_Error( 'not_found', 'Membership not found.' );
            $balance = round( (float) $row->amount_due - (float) $row->amount_paid, 2 );
            if ( $balance <= 0 ) return new \WP_Error( 'already_paid', 'This membership has no balance due.' );
            return [
                'amount'      => $balance,
                'owner_id'    => (int) $row->parent_id,
                'description' => sprintf( '%s %s — %s membership', $row->first_name, $row->last_name, $row->season_name ),
            ];
        }

        if ( 'travel' === $type ) {
            $row = $wpdb->get_row( $wpdb->prepare(
                "SELECT t.travel_fee, t.payment_status, a.parent_id, a.first_name, a.last_name, m.name AS meet_name
                 FROM {$this->travel_table} t
                 JOIN {$wpdb->prefix}TRACKSUITE_athletes a ON t.athlete_id = a.id
                 JOIN {$wpdb->prefix}TRACKSUITE_meets m    ON t.meet_id    = m.id
                 WHERE t.id = %d", $reference_id
            ) );
            if ( ! $row ) return new \WP_Error( 'not_found', 'Travel booking not found.' );
            if ( 'paid' === $row->payment_status ) return new \WP_Error( 'already_paid', 'This travel booking is already paid.' );
            return [
                'amount'      => (float) $row->travel_fee,
                'owner_id'    => (int) $row->parent_id,
                'description' => sprintf( '%s %s — %s travel', $row->first_name, $row->last_name, $row->meet_name ),
            ];
        }

        return new \WP_Error( 'unsupported_type', 'Unsupported payment type.' );
    }

    /**
     * Create a Stripe Checkout Session.
     *
     * @param array $args {
     *   @type string $type          Payment type: 'membership' | 'travel'
     *   @type int    $reference_id  ID of the related record (membership_id, travel_id)
     *   @type int    $user_id       WP user ID of the payer (from the authenticated request, not client input)
     *   @type string $success_url   URL to redirect after successful payment
     *   @type string $cancel_url    URL to redirect if user cancels
     * }
     * @return array|WP_Error Stripe session data or WP_Error on failure.
     */
    public function create_checkout_session( array $args ): array|\WP_Error {
        if ( ! $this->is_configured() ) {
            return new \WP_Error(
                'stripe_not_configured',
                'Stripe API keys are not set. Go to WP Admin → Xtreme Force → Settings → Payments to configure.'
            );
        }
        if ( ! $this->load_stripe_sdk() ) {
            return new \WP_Error( 'stripe_sdk_missing', 'Stripe PHP SDK is not installed. Run `composer install` in the plugin root.' );
        }

        $type         = sanitize_key( $args['type'] ?? '' );
        $reference_id = (int) ( $args['reference_id'] ?? 0 );
        $user_id      = (int) ( $args['user_id'] ?? 0 );

        $reference = $this->resolve_reference( $type, $reference_id );
        if ( is_wp_error( $reference ) ) {
            return $reference;
        }

        $is_admin = user_can( $user_id, 'TRACKSUITE_admin' ) || user_can( $user_id, 'administrator' );
        if ( ! $is_admin && $reference['owner_id'] !== $user_id ) {
            return new \WP_Error( 'forbidden', 'You do not have permission to pay this balance.' );
        }

        try {
            \Stripe\Stripe::setApiKey( $this->secret_key );

            $session = \Stripe\Checkout\Session::create( [
                'payment_method_types' => [ 'card' ],
                'line_items'           => [ [
                    'price_data' => [
                        'currency'     => 'usd',
                        'unit_amount'  => (int) round( $reference['amount'] * 100 ),
                        'product_data' => [ 'name' => $reference['description'] ],
                    ],
                    'quantity' => 1,
                ] ],
                'mode'                 => 'payment',
                'success_url'          => $args['success_url'],
                'cancel_url'           => $args['cancel_url'],
                'customer_email'       => get_userdata( $user_id )->user_email ?? null,
                'metadata'             => [
                    'TRACKSUITE_type'         => $type,
                    'TRACKSUITE_reference_id' => $reference_id,
                    'TRACKSUITE_user_id'      => $user_id,
                ],
            ] );
        } catch ( \Exception $e ) {
            return new \WP_Error( 'stripe_error', $e->getMessage() );
        }

        $this->log_payment_pending( [
            'user_id'      => $user_id,
            'type'         => $type,
            'reference_id' => $reference_id,
            'amount'       => $reference['amount'],
        ], $session->id );

        return [ 'session_id' => $session->id, 'url' => $session->url ];
    }

    /**
     * Handle incoming Stripe webhook events.
     * Registered as REST endpoint: POST /wp-json/xftc/v1/payments/webhook
     */
    public function handle_webhook( \WP_REST_Request $request ): \WP_REST_Response {
        if ( ! $this->load_stripe_sdk() ) {
            return new \WP_REST_Response( [ 'error' => 'Stripe SDK missing' ], 500 );
        }
        if ( empty( $this->webhook_secret ) ) {
            return new \WP_REST_Response( [ 'error' => 'Webhook secret not configured' ], 500 );
        }

        $payload    = $request->get_body();
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        try {
            $event = \Stripe\Webhook::constructEvent( $payload, $sig_header, $this->webhook_secret );
        } catch ( \Exception $e ) {
            // Signature (or payload) verification failed — never trust an unverified event.
            return new \WP_REST_Response( [ 'error' => 'Invalid signature' ], 400 );
        }

        switch ( $event->type ) {
            case 'checkout.session.completed':
                $this->handle_payment_completed( $event->data->object );
                break;
            case 'payment_intent.payment_failed':
                $this->handle_payment_failed( $event->data->object );
                break;
        }

        return new \WP_REST_Response( [ 'status' => 'ok' ], 200 );
    }

    /**
     * Mark a payment as completed after webhook confirmation.
     * Updates wp_TRACKSUITE_payments and the underlying membership/travel row.
     */
    private function handle_payment_completed( object $session ): void {
        global $wpdb;

        $meta   = $session->metadata;
        $type   = $meta->TRACKSUITE_type ?? '';
        $ref_id = (int) ( $meta->TRACKSUITE_reference_id ?? 0 );
        $user_id = (int) ( $meta->TRACKSUITE_user_id ?? 0 );
        $transaction = $session->payment_intent ?? $session->id;
        $amount      = ( $session->amount_total ?? 0 ) / 100;

        $wpdb->update( $this->payments_table,
            [ 'status' => 'completed', 'transaction_id' => $transaction ],
            [ 'reference_type' => $type, 'reference_id' => $ref_id, 'status' => 'pending' ]
        );

        if ( 'membership' === $type ) {
            $wpdb->query( $wpdb->prepare(
                "UPDATE {$this->memberships_table}
                 SET amount_paid = amount_paid + %f,
                     payment_status = IF( amount_paid + %f >= amount_due, 'paid', 'partial' ),
                     status = IF( amount_paid + %f >= amount_due, 'active', status )
                 WHERE id = %d",
                $amount, $amount, $amount, $ref_id
            ) );
        } elseif ( 'travel' === $type ) {
            $wpdb->update( $this->travel_table, [ 'payment_status' => 'paid' ], [ 'id' => $ref_id ] );
        }

        if ( $user_id ) {
            $emails = new TRACKSUITE_Emails();
            $emails->send_payment_receipt( $user_id, $amount, [
                'Type'      => ucfirst( $type ),
                'Reference' => '#' . $ref_id,
            ] );
        }
    }

    /**
     * Handle a failed payment event from Stripe.
     */
    private function handle_payment_failed( object $intent ): void {
        global $wpdb;
        $wpdb->update( $this->payments_table,
            [ 'status' => 'failed' ],
            [ 'transaction_id' => $intent->id ?? '', 'status' => 'pending' ]
        );
        error_log( 'TS Payments: payment failed — ' . ( $intent->id ?? 'unknown' ) );
    }

    /**
     * Log a pending payment record to wp_TRACKSUITE_payments.
     */
    private function log_payment_pending( array $args, string $session_id ): void {
        global $wpdb;
        $wpdb->insert( $this->payments_table, [
            'user_id'        => $args['user_id'],
            'reference_type' => $args['type'],
            'reference_id'   => $args['reference_id'],
            'amount'         => $args['amount'],
            'gateway'        => 'stripe',
            'transaction_id' => $session_id,
            'status'         => 'pending',
            'created_at'     => current_time( 'mysql' ),
        ] );
    }

    /**
     * Record a manual payment (cash/check) entered by admin, and apply it to
     * the underlying membership/travel balance the same way a Stripe webhook would.
     *
     * @param array $args {
     *   @type int    $user_id       WP user ID
     *   @type string $type          'membership' | 'travel'
     *   @type int    $reference_id  Related record ID
     *   @type float  $amount        Amount received
     *   @type string $notes         Optional notes (e.g. "Check #1042")
     * }
     * @return int|false Inserted record ID or false on failure.
     */
    public function record_manual_payment( array $args ): int|false {
        global $wpdb;

        $inserted = $wpdb->insert( $this->payments_table, [
            'user_id'        => (int) $args['user_id'],
            'reference_type' => sanitize_key( $args['type'] ),
            'reference_id'   => (int) $args['reference_id'],
            'amount'         => (float) $args['amount'],
            'gateway'        => 'manual',
            'transaction_id' => 'MANUAL-' . time(),
            'status'         => 'completed',
            'created_at'     => current_time( 'mysql' ),
        ] );

        if ( ! $inserted ) {
            return false;
        }

        $amount = (float) $args['amount'];
        if ( 'membership' === $args['type'] ) {
            $wpdb->query( $wpdb->prepare(
                "UPDATE {$this->memberships_table}
                 SET amount_paid = amount_paid + %f,
                     payment_status = IF( amount_paid + %f >= amount_due, 'paid', 'partial' ),
                     status = IF( amount_paid + %f >= amount_due, 'active', status )
                 WHERE id = %d",
                $amount, $amount, $amount, (int) $args['reference_id']
            ) );
        } elseif ( 'travel' === $args['type'] ) {
            $wpdb->update( $this->travel_table, [ 'payment_status' => 'paid' ], [ 'id' => (int) $args['reference_id'] ] );
        }

        return $wpdb->insert_id;
    }

    /**
     * Get the Stripe publishable key for use in front-end JS.
     */
    public function get_publishable_key(): string {
        return $this->publishable_key;
    }

    /**
     * Return payment history for a given user.
     */
    public function get_payment_history( int $user_id ): array {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$this->payments_table} WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ), ARRAY_A ) ?: [];
    }

    /**
     * Return payment history across all users (admin reporting).
     */
    public function get_all_payments( int $limit = 100 ): array {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT p.*, u.display_name, u.user_email
             FROM {$this->payments_table} p
             JOIN {$wpdb->users} u ON p.user_id = u.ID
             ORDER BY p.created_at DESC LIMIT %d",
            $limit
        ), ARRAY_A ) ?: [];
    }
}
