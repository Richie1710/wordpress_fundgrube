<?php
/**
 * Plugin-Grundfunktionen Tests
 * 
 * @package Fundgrube
 * @subpackage Tests
 */

class FundgrubePluginTest extends WP_UnitTestCase {
    
    /**
     * Plugin-Instanz für Tests
     * 
     * @var Fundgrube_Plugin
     */
    private $plugin;
    
    /**
     * Setup vor jedem Test
     * 
     * @since 1.0.0
     */
    public function setUp(): void {
        parent::setUp();
        $this->plugin = Fundgrube_Plugin::get_instance();
    }
    
    /**
     * Cleanup nach jedem Test
     * 
     * @since 1.0.0
     */
    public function tearDown(): void {
        parent::tearDown();
    }
    
    /**
     * Test: Plugin-Instanz kann erstellt werden
     * 
     * @since 1.0.0
     */
    public function test_plugin_instance() {
        $this->assertInstanceOf('Fundgrube_Plugin', $this->plugin);
    }
    
    /**
     * Test: Plugin-Konstanten sind definiert
     * 
     * @since 1.0.0
     */
    public function test_plugin_constants() {
        $this->assertTrue(defined('FUNDGRUBE_VERSION'));
        $this->assertTrue(defined('FUNDGRUBE_PLUGIN_FILE'));
        $this->assertTrue(defined('FUNDGRUBE_PLUGIN_URL'));
        $this->assertTrue(defined('FUNDGRUBE_PLUGIN_PATH'));
        $this->assertTrue(defined('FUNDGRUBE_PLUGIN_BASENAME'));
        
        $this->assertEquals('1.0.0', FUNDGRUBE_VERSION);
    }
    
    /**
     * Test: WordPress-Hooks sind registriert
     * 
     * @since 1.0.0
     */
    public function test_wordpress_hooks() {
        // Prüfen ob wichtige Actions registriert sind
        $this->assertGreaterThan(0, has_action('plugins_loaded', 'fundgrube_init'));
        $this->assertGreaterThan(0, has_action('init'));
    }
    
    /**
     * Test: Textdomain wird geladen
     * 
     * @since 1.0.0
     */
    public function test_textdomain_loaded() {
        // Simuliere das Laden der Textdomain
        do_action('plugins_loaded');
        
        // Prüfe ob eine Übersetzung existiert (auch wenn nur Fallback)
        $translated = __('Fundgrube', 'fundgrube');
        $this->assertIsString($translated);
    }
}

/**
 * Custom Post Type Tests
 */
class FundgrubePostTypeTest extends WP_UnitTestCase {
    
    /**
     * Post Type-Instanz
     * 
     * @var Fundgrube_Post_Type
     */
    private $post_type;
    
    /**
     * Setup vor jedem Test
     */
    public function setUp(): void {
        parent::setUp();
        $this->post_type = new Fundgrube_Post_Type();
    }
    
    /**
     * Test: Custom Post Type ist registriert
     */
    public function test_post_type_registered() {
        // Trigger die Post Type Registrierung
        do_action('init');
        
        $this->assertTrue(post_type_exists('fundgrube_item'));
    }
    
    /**
     * Test: Post Type Labels sind korrekt
     */
    public function test_post_type_labels() {
        do_action('init');
        
        $post_type_object = get_post_type_object('fundgrube_item');
        $this->assertIsObject($post_type_object);
        $this->assertEquals('Fundstücke', $post_type_object->labels->name);
    }
    
    /**
     * Test: Fundstück kann erstellt werden
     */
    public function test_create_fundgrube_item() {
        do_action('init');
        
        $post_data = array(
            'post_title'   => 'Test Fundstück',
            'post_content' => 'Dies ist ein Test-Fundstück',
            'post_type'    => 'fundgrube_item',
            'post_status'  => 'publish'
        );
        
        $post_id = wp_insert_post($post_data);
        $this->assertIsInt($post_id);
        $this->assertGreaterThan(0, $post_id);
        
        $post = get_post($post_id);
        $this->assertEquals('fundgrube_item', $post->post_type);
        $this->assertEquals('Test Fundstück', $post->post_title);
    }
    
    /**
     * Test: Meta-Felder können gesetzt werden
     */
    public function test_meta_fields() {
        do_action('init');
        
        $post_id = wp_insert_post(array(
            'post_title' => 'Meta Test',
            'post_type'  => 'fundgrube_item',
            'post_status' => 'publish'
        ));
        
        // Meta-Felder setzen
        update_post_meta($post_id, '_fundgrube_kategorie', 'verloren');
        update_post_meta($post_id, '_fundgrube_fundort', 'Teststraße 123');
        update_post_meta($post_id, '_fundgrube_funddatum', '2023-12-01');
        
        // Meta-Felder prüfen
        $this->assertEquals('verloren', get_post_meta($post_id, '_fundgrube_kategorie', true));
        $this->assertEquals('Teststraße 123', get_post_meta($post_id, '_fundgrube_fundort', true));
        $this->assertEquals('2023-12-01', get_post_meta($post_id, '_fundgrube_funddatum', true));
    }
}

/**
 * Shortcode Tests
 */
class FundgrubeShortcodeTest extends WP_UnitTestCase {
    
    /**
     * Public-Instanz
     * 
     * @var Fundgrube_Public
     */
    private $public;
    
    /**
     * Setup vor jedem Test
     */
    public function setUp(): void {
        parent::setUp();
        $this->public = new Fundgrube_Public();
        
        // Simuliere Plugin-Initialisierung
        do_action('init');
    }
    
    /**
     * Test: Shortcodes sind registriert
     */
    public function test_shortcodes_registered() {
        $this->assertTrue(shortcode_exists('fundgrube_liste'));
        $this->assertTrue(shortcode_exists('fundgrube_suche'));
        $this->assertTrue(shortcode_exists('fundgrube_einzeln'));
    }
    
    /**
     * Test: Shortcode fundgrube_liste funktioniert
     */
    public function test_fundgrube_liste_shortcode() {
        // Erstelle Test-Fundstücke
        $post_id = wp_insert_post(array(
            'post_title'  => 'Test Fundstück Liste',
            'post_type'   => 'fundgrube_item',
            'post_status' => 'publish'
        ));
        
        $output = do_shortcode('[fundgrube_liste anzahl="5"]');
        
        $this->assertStringContainsString('fundgrube-liste', $output);
        $this->assertStringContainsString('Test Fundstück Liste', $output);
    }
    
    /**
     * Test: Shortcode fundgrube_suche funktioniert
     */
    public function test_fundgrube_suche_shortcode() {
        $output = do_shortcode('[fundgrube_suche]');
        
        $this->assertStringContainsString('fundgrube-suche', $output);
        $this->assertStringContainsString('type="text"', $output);
        $this->assertStringContainsString('name="s"', $output);
    }
    
    /**
     * Test: Shortcode fundgrube_einzeln mit gültiger ID
     */
    public function test_fundgrube_einzeln_shortcode() {
        $post_id = wp_insert_post(array(
            'post_title'  => 'Einzeltest Fundstück',
            'post_content' => 'Test Content für Einzelansicht',
            'post_type'   => 'fundgrube_item',
            'post_status' => 'publish'
        ));
        
        $output = do_shortcode('[fundgrube_einzeln id="' . $post_id . '"]');
        
        $this->assertStringContainsString('fundgrube-einzeln', $output);
        $this->assertStringContainsString('Einzeltest Fundstück', $output);
    }
    
    /**
     * Test: Shortcode fundgrube_einzeln mit ungültiger ID
     */
    public function test_fundgrube_einzeln_shortcode_invalid_id() {
        $output = do_shortcode('[fundgrube_einzeln id="99999"]');
        
        $this->assertStringContainsString('fundgrube-fehler', $output);
        $this->assertStringContainsString('nicht gefunden', $output);
    }
}

/**
 * Admin-Funktionen Tests
 */
class FundgrubeAdminTest extends WP_UnitTestCase {
    
    /**
     * Admin-Instanz
     * 
     * @var Fundgrube_Admin
     */
    private $admin;
    
    /**
     * Setup vor jedem Test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Admin-User erstellen und anmelden
        $user_id = $this->factory->user->create(array('role' => 'administrator'));
        wp_set_current_user($user_id);
        
        $this->admin = new Fundgrube_Admin();
    }
    
    /**
     * Test: Admin-Menü wird hinzugefügt
     */
    public function test_admin_menu_added() {
        global $_parent_pages;
        
        // Simuliere Admin-Init
        do_action('admin_menu');
        
        $this->assertArrayHasKey('fundgrube', $_parent_pages);
    }
    
    /**
     * Test: Plugin-Einstellungen können gespeichert werden
     */
    public function test_plugin_settings() {
        $test_options = array(
            'items_per_page' => 15,
            'enable_gallery' => true,
            'contact_info'   => 'Test Kontakt Info'
        );
        
        update_option('fundgrube_options', $test_options);
        $saved_options = get_option('fundgrube_options');
        
        $this->assertEquals($test_options, $saved_options);
    }
}
