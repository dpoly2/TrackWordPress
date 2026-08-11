<?php
use PHPUnit\Framework\TestCase;

/**
 * Regression test for the exact bug found in review: REST handlers in
 * class-ts-rest-api.php calling methods (get_all_members(), create_season(),
 * etc.) that didn't exist on TRACKSUITE_Members / TRACKSUITE_Seasons.
 *
 * is_callable() on the registered route callback only proves the *handler
 * method itself* exists — it says nothing about what that handler calls
 * internally. So this test actually invokes the list-style GET handlers
 * (the ones safe to call with no path params and a fake, empty-result
 * $wpdb) and asserts they return normally instead of fataling.
 */
class RestRoutesSmokeTest extends TestCase {

    protected function setUp(): void {
        $GLOBALS['wpdb'] = new FakeWpdb();
    }

    public function test_every_registered_route_callback_is_callable(): void {
        $this->assertNotEmpty( $GLOBALS['ts_test_rest_routes'], 'No REST routes were registered — rest_api_init hook did not fire as expected.' );

        foreach ( $GLOBALS['ts_test_rest_routes'] as $route ) {
            $defs = isset( $route['args']['callback'] ) ? [ $route['args'] ] : $route['args'];
            foreach ( $defs as $def ) {
                $this->assertIsCallable(
                    $def['callback'],
                    sprintf( 'Route %s callback is not callable.', $route['route'] )
                );
                $this->assertIsCallable(
                    $def['permission_callback'],
                    sprintf( 'Route %s permission_callback is not callable.', $route['route'] )
                );
            }
        }
    }

    public function test_get_athletes_does_not_fatal(): void {
        $api = new TRACKSUITE_REST_API();
        $response = $api->get_athletes( new WP_REST_Request() );
        $this->assertInstanceOf( WP_REST_Response::class, $response );
        $this->assertSame( 200, $response->status );
    }

    public function test_get_seasons_does_not_fatal(): void {
        $api = new TRACKSUITE_REST_API();
        $response = $api->get_seasons( new WP_REST_Request() );
        $this->assertInstanceOf( WP_REST_Response::class, $response );
        $this->assertSame( 200, $response->status );
    }

    public function test_get_meets_does_not_fatal(): void {
        $api = new TRACKSUITE_REST_API();
        $response = $api->get_meets( new WP_REST_Request() );
        $this->assertInstanceOf( WP_REST_Response::class, $response );
        $this->assertSame( 200, $response->status );
    }

    public function test_create_season_calls_the_real_create_method(): void {
        // Members/Seasons only expose create()/get()/get_all()/update() — the
        // REST handler must call those, not the old create_season()/get_all_seasons()
        // names that never existed on TRACKSUITE_Seasons.
        $GLOBALS['wpdb']->insert_queue = [ 9 ];

        $req = new WP_REST_Request();
        $req->set_json_params( [ 'name' => 'Test Season', 'type' => 'outdoor' ] );

        $api = new TRACKSUITE_REST_API();
        $response = $api->create_season( $req );

        $this->assertInstanceOf( WP_REST_Response::class, $response );
        $this->assertSame( 201, $response->status );
        $this->assertSame( 9, $response->data['id'] );
    }
}
