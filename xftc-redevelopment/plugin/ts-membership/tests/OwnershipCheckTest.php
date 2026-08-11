<?php
use PHPUnit\Framework\TestCase;

/**
 * Regression test for the IDOR found in review: REST athlete endpoints
 * originally had no check that the requesting parent actually owned the
 * athlete record being read/updated. owns_athlete_or_admin() is the fix;
 * this exercises it directly via reflection since it's intentionally
 * private (an internal helper, not part of the REST API's public surface).
 */
class OwnershipCheckTest extends TestCase {

    private function callOwnsAthleteOrAdmin( object $athlete ): bool {
        $api    = new TRACKSUITE_REST_API();
        $method = new ReflectionMethod( TRACKSUITE_REST_API::class, 'owns_athlete_or_admin' );
        $method->setAccessible( true );
        return $method->invoke( $api, $athlete );
    }

    protected function setUp(): void {
        $GLOBALS['ts_test_current_user_id'] = 0;
        $GLOBALS['ts_test_user_caps']       = [];
    }

    public function test_parent_owns_their_own_athlete(): void {
        $GLOBALS['ts_test_current_user_id'] = 42;
        $athlete = (object) [ 'parent_id' => 42 ];
        $this->assertTrue( $this->callOwnsAthleteOrAdmin( $athlete ) );
    }

    public function test_parent_does_not_own_another_parents_athlete(): void {
        $GLOBALS['ts_test_current_user_id'] = 42;
        $athlete = (object) [ 'parent_id' => 99 ];
        $this->assertFalse( $this->callOwnsAthleteOrAdmin( $athlete ) );
    }

    public function test_admin_can_access_any_athlete(): void {
        $GLOBALS['ts_test_current_user_id'] = 1;
        $GLOBALS['ts_test_user_caps']       = [ 'TRACKSUITE_admin' ];
        $athlete = (object) [ 'parent_id' => 99 ];
        $this->assertTrue( $this->callOwnsAthleteOrAdmin( $athlete ) );
    }

    public function test_unrelated_logged_in_user_is_denied(): void {
        $GLOBALS['ts_test_current_user_id'] = 7;
        $athlete = (object) [ 'parent_id' => 8 ];
        $this->assertFalse( $this->callOwnsAthleteOrAdmin( $athlete ) );
    }
}
