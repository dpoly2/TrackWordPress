<?php
/**
 * PHPUnit bootstrap for xftc-membership plugin tests.
 * Loads the plugin without a full WordPress environment for unit tests.
 */

define( 'ABSPATH', __DIR__ . '/' );

require_once dirname( __DIR__ ) . '/xftc-membership.php';
