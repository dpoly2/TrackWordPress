<?php
/**
 * Minimal stand-in for $wpdb, scriptable enough for unit tests that need
 * to exercise insert()/get_var() branching (e.g. the duplicate-registration
 * fallback path) without a real MySQL connection.
 *
 * Queue return values via the public *_queue arrays; if a queue is empty,
 * sensible defaults are returned (insert succeeds, reads return null/[]).
 */
class FakeWpdb {

    public string $prefix = 'wp_';
    public string $users = 'wp_users';
    public int $insert_id = 0;
    public string $last_error = '';

    /** @var array<int,mixed> */
    public array $insert_queue = [];
    /** @var array<int,mixed> */
    public array $get_var_queue = [];
    /** @var array<int,mixed> */
    public array $get_row_queue = [];
    /** @var array<int,mixed> */
    public array $get_results_queue = [];
    /** @var array<int,mixed> */
    public array $get_col_queue = [];

    /** @var array<int,array{0:string,1:array}> Every insert() call, for assertions. */
    public array $inserts = [];

    public function prepare( string $query, ...$args ): string {
        return $query; // Fake — no real interpolation needed for these tests.
    }

    public function insert( string $table, array $data ) {
        $this->inserts[] = [ $table, $data ];
        $result = array_key_exists( 0, $this->insert_queue ) ? array_shift( $this->insert_queue ) : true;
        if ( false === $result ) {
            $this->last_error = 'Duplicate entry';
            return false;
        }
        // An int in the queue sets insert_id explicitly; `true` leaves
        // insert_id as whatever the test already configured it to.
        if ( is_int( $result ) ) {
            $this->insert_id = $result;
        }
        return 1;
    }

    public function update( string $table, array $data, array $where ) {
        return 1;
    }

    public function delete( string $table, array $where, $format = null ) {
        return 1;
    }

    public function query( string $sql ) {
        return 0;
    }

    public function get_var( ?string $query = null ) {
        return array_key_exists( 0, $this->get_var_queue ) ? array_shift( $this->get_var_queue ) : null;
    }

    public function get_row( ?string $query = null, $output = OBJECT ) {
        return array_key_exists( 0, $this->get_row_queue ) ? array_shift( $this->get_row_queue ) : null;
    }

    public function get_results( ?string $query = null, $output = OBJECT ) {
        return array_key_exists( 0, $this->get_results_queue ) ? array_shift( $this->get_results_queue ) : [];
    }

    public function get_col( ?string $query = null ) {
        return array_key_exists( 0, $this->get_col_queue ) ? array_shift( $this->get_col_queue ) : [];
    }

    public function get_charset_collate(): string {
        return '';
    }

    public function esc_like( string $text ): string {
        return addcslashes( $text, '_%\\' );
    }
}
