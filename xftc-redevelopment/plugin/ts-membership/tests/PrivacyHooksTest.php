<?php
use PHPUnit\Framework\TestCase;

/**
 * Confirms TRACKSUITE_Privacy actually registered with WordPress's built-in
 * privacy tools (Tools > Export/Erase Personal Data) during plugin boot —
 * the GDPR-style mechanism this plugin relies on instead of a bespoke one.
 */
class PrivacyHooksTest extends TestCase {

    public function test_personal_data_exporter_is_registered(): void {
        $exporters = apply_filters( 'wp_privacy_personal_data_exporters', [] );
        $this->assertArrayHasKey( 'ts-membership', $exporters );
        $this->assertIsCallable( $exporters['ts-membership']['callback'] );
    }

    public function test_personal_data_eraser_is_registered(): void {
        $erasers = apply_filters( 'wp_privacy_personal_data_erasers', [] );
        $this->assertArrayHasKey( 'ts-membership', $erasers );
        $this->assertIsCallable( $erasers['ts-membership']['callback'] );
    }
}
