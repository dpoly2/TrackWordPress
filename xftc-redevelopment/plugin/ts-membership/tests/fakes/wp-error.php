<?php
/**
 * Minimal stand-ins for WP_Error / WP_REST_Response / WP_REST_Request so
 * plugin code that type-hints or instantiates them can be loaded and, where
 * needed, actually invoked in tests without a full WordPress install.
 */

if ( ! class_exists( 'WP_Error' ) ) {
    class WP_Error {
        public array $errors = [];
        public array $error_data = [];

        public function __construct( $code = '', $message = '', $data = '' ) {
            if ( $code ) {
                $this->errors[ $code ][] = $message;
                if ( '' !== $data ) {
                    $this->error_data[ $code ] = $data;
                }
            }
        }

        public function get_error_code() {
            $codes = array_keys( $this->errors );
            return $codes[0] ?? '';
        }

        public function get_error_message( $code = '' ) {
            if ( ! $code ) {
                $code = $this->get_error_code();
            }
            return $this->errors[ $code ][0] ?? '';
        }
    }
}

if ( ! function_exists( 'is_wp_error' ) ) {
    function is_wp_error( $thing ) {
        return $thing instanceof WP_Error;
    }
}

if ( ! class_exists( 'WP_REST_Response' ) ) {
    class WP_REST_Response {
        public $data;
        public int $status;

        public function __construct( $data = null, int $status = 200 ) {
            $this->data   = $data;
            $this->status = $status;
        }
    }
}

if ( ! class_exists( 'WP_REST_Request' ) ) {
    #[\AllowDynamicProperties]
    class WP_REST_Request implements \ArrayAccess {
        private array $params = [];
        private array $json_params = [];

        public function __construct( $method = 'GET', $route = '' ) {}

        public function set_param( $key, $value ) { $this->params[ $key ] = $value; }
        public function get_param( $key ) { return $this->params[ $key ] ?? null; }
        public function set_json_params( array $params ) { $this->json_params = $params; }
        public function get_json_params() { return $this->json_params; }
        public function get_body() { return ''; }

        public function offsetExists( $offset ): bool { return isset( $this->params[ $offset ] ); }
        public function offsetGet( $offset ): mixed { return $this->params[ $offset ] ?? null; }
        public function offsetSet( $offset, $value ): void { $this->params[ $offset ] = $value; }
        public function offsetUnset( $offset ): void { unset( $this->params[ $offset ] ); }
    }
}
