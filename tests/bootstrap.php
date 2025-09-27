<?php
/**
 * PHPUnit Bootstrap-Datei für Fundgrube Plugin Tests
 * 
 * Diese Datei wird vor allen Tests ausgeführt und bereitet die
 * WordPress Test-Umgebung vor.
 * 
 * @package Fundgrube
 * @subpackage Tests
 */

// WordPress Test-Umgebung laden
$_tests_dir = getenv('WP_TESTS_DIR');

// Fallback-Pfade für WordPress Tests
if (!$_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

// WordPress Test-Suite laden
if (!file_exists($_tests_dir . '/includes/functions.php')) {
    echo "Konnte WordPress Test-Suite nicht finden in: " . $_tests_dir . "\n";
    echo "Installieren Sie die WordPress Test-Suite mit:\n";
    echo "bash bin/install-wp-tests.sh wordpress_test root '' localhost latest\n";
    exit(1);
}

// Testfunktionen laden
require_once $_tests_dir . '/includes/functions.php';

/**
 * Plugin manuell für Tests laden
 * 
 * @since 1.0.0
 */
function _manually_load_plugin() {
    require dirname(dirname(__FILE__)) . '/fundgrube.php';
}

// Plugin vor WordPress-Installation laden
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// WordPress Test-Umgebung starten
require $_tests_dir . '/includes/bootstrap.php';
