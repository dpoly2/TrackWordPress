<?php
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * If the plugin's bootstrap file requires a file that doesn't exist, or a
 * class references a method that doesn't exist on another class, PHP fatals
 * during tests/bootstrap.php (which requires ts-membership.php and fires
 * plugins_loaded/init/rest_api_init) — before any test in this file even
 * runs. That's deliberate: it's what actually catches the "renamed files,
 * mismatched method names" class of bug. The assertions below just confirm,
 * once we get this far, that every class the plugin is supposed to define
 * really did get loaded.
 */
class BootstrapLoadTest extends TestCase {

    public static function classProvider(): array {
        return [
            [ 'TRACKSUITE_Activator' ],
            [ 'TRACKSUITE_Deactivator' ],
            [ 'TRACKSUITE_Roles' ],
            [ 'TRACKSUITE_Members' ],
            [ 'TRACKSUITE_Seasons' ],
            [ 'TRACKSUITE_Registration' ],
            [ 'TRACKSUITE_Emails' ],
            [ 'TRACKSUITE_Meets' ],
            [ 'TRACKSUITE_Results' ],
            [ 'TRACKSUITE_Travel' ],
            [ 'TRACKSUITE_Payroll' ],
            [ 'TRACKSUITE_Payments' ],
            [ 'TRACKSUITE_Reports' ],
            [ 'TRACKSUITE_Privacy' ],
            [ 'TRACKSUITE_Admin' ],
            [ 'TS_Dashboard_Widgets' ],
            [ 'TRACKSUITE_Public' ],
            [ 'TRACKSUITE_REST_API' ],
        ];
    }

    #[DataProvider( 'classProvider' )]
    public function test_class_is_defined( string $class ): void {
        $this->assertTrue( class_exists( $class ), "Expected class {$class} to be defined after plugin boot." );
    }

    public function test_plugin_constants_defined(): void {
        $this->assertTrue( defined( 'TRACKSUITE_VERSION' ) );
        $this->assertTrue( defined( 'TRACKSUITE_PLUGIN_DIR' ) );
        $this->assertTrue( defined( 'TRACKSUITE_PLUGIN_URL' ) );
    }

    public function test_shortcodes_were_registered(): void {
        $expected = [
            'TRACKSUITE_meets', 'TRACKSUITE_register_form', 'TRACKSUITE_portal',
            'TRACKSUITE_my_athletes', 'TRACKSUITE_staff_portal', 'TRACKSUITE_my_orders',
        ];
        foreach ( $expected as $tag ) {
            $this->assertArrayHasKey( $tag, $GLOBALS['ts_test_shortcodes'], "Shortcode [{$tag}] was not registered." );
            $this->assertIsCallable( $GLOBALS['ts_test_shortcodes'][ $tag ] );
        }
    }

    public function test_ajax_actions_were_registered(): void {
        $this->assertNotEmpty( $GLOBALS['ts_test_actions']['wp_ajax_TRACKSUITE_register_athlete'] ?? [] );
        $this->assertNotEmpty( $GLOBALS['ts_test_actions']['wp_ajax_nopriv_TRACKSUITE_register_athlete'] ?? [] );
    }
}
