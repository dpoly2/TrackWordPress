<?php
use PHPUnit\Framework\TestCase;

/**
 * Regression test for the duplicate-registration race described in review:
 * register_athlete() used to SELECT-then-INSERT, which two concurrent
 * requests could both pass before either insert lands. The fix relies on a
 * UNIQUE KEY at the DB layer (see class-ts-activator.php) with the
 * application code catching the resulting insert failure and returning the
 * row that won the race. This test exercises that fallback path — it can't
 * exercise MySQL's actual uniqueness enforcement without a real DB, but it
 * does verify the application-level contract: insert failure -> re-lookup
 * -> return existing id, rather than silently returning false.
 */
class MeetRegistrationDedupeTest extends TestCase {

    protected function setUp(): void {
        $GLOBALS['wpdb'] = new FakeWpdb();
    }

    public function test_first_registration_inserts_and_returns_new_id(): void {
        $GLOBALS['wpdb']->insert_queue = [ 15 ];

        $meets = new TRACKSUITE_Meets();
        $id    = $meets->register_athlete( 1, 2, [ 'event_category' => '100m' ] );

        $this->assertSame( 15, $id );
        $this->assertCount( 1, $GLOBALS['wpdb']->inserts );
    }

    public function test_duplicate_registration_falls_back_to_existing_row(): void {
        // Simulate the UNIQUE KEY rejecting the second insert, then the
        // fallback SELECT finding the row the first request already created.
        $GLOBALS['wpdb']->insert_queue  = [ false ];
        $GLOBALS['wpdb']->get_var_queue = [ 15 ];

        $meets = new TRACKSUITE_Meets();
        $id    = $meets->register_athlete( 1, 2, [ 'event_category' => '100m' ] );

        $this->assertSame( 15, $id, 'Expected the existing entry id to be returned instead of false.' );
    }

    public function test_travel_booking_has_the_same_fallback(): void {
        $GLOBALS['wpdb']->insert_queue  = [ false ];
        $GLOBALS['wpdb']->get_var_queue = [ 7 ];

        $travel = new TRACKSUITE_Travel();
        $id     = $travel->create_booking( [ 'meet_id' => 1, 'athlete_id' => 2, 'travel_type' => 'bus' ] );

        $this->assertSame( 7, $id );
    }
}
