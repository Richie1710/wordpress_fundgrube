<?php
/**
 * Plugin Name: Fundgrube
 * Plugin URI: https://example.com/fundgrube
 * Description: Ein WordPress-Plugin zur Verwaltung von Fundstücken mit Bildern und Metadaten. Ermöglicht das einfache Anlegen, Verwalten und Anzeigen von gefundenen Gegenständen.
 * Version: 1.0.0
 * Author: Ihr Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: fundgrube
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 * Network: false
 *
 * @package Fundgrube
 * @version 1.0.0
 * @author Ihr Name
 * @license GPL-2.0-or-later
 */

// Direkte Ausführung verhindern
if (!defined('ABSPATH')) {
    exit;
}

// Plugin-Konstanten definieren
define('FUNDGRUBE_VERSION', '1.0.0');
define('FUNDGRUBE_PLUGIN_FILE', __FILE__);
define('FUNDGRUBE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FUNDGRUBE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FUNDGRUBE_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Composer Autoloader einbinden
if (file_exists(FUNDGRUBE_PLUGIN_PATH . 'vendor/autoload.php')) {
    require_once FUNDGRUBE_PLUGIN_PATH . 'vendor/autoload.php';
}

/**
 * Hauptklasse des Fundgrube-Plugins
 * 
 * Diese Klasse koordiniert alle Plugin-Funktionen und initialisiert
 * die verschiedenen Komponenten des Plugins.
 * 
 * @since 1.0.0
 */
class Fundgrube_Plugin {
    
    /**
     * Plugin-Instanz (Singleton Pattern)
     * 
     * @var Fundgrube_Plugin|null
     * @since 1.0.0
     */
    private static $instance = null;
    
    /**
     * Admin-Handler
     * 
     * @var Fundgrube_Admin|null
     * @since 1.0.0
     */
    private $admin = null;
    
    /**
     * Public-Handler
     * 
     * @var Fundgrube_Public|null
     * @since 1.0.0
     */
    private $public = null;
    
    /**
     * Singleton-Instanz abrufen
     * 
     * @return Fundgrube_Plugin Die Plugin-Instanz
     * @since 1.0.0
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Konstruktor - Initialisiert das Plugin
     * 
     * @since 1.0.0
     */
    private function __construct() {
        $this->setup_hooks();
        $this->load_dependencies();
        $this->init_components();
    }
    
    /**
     * WordPress-Hooks einrichten
     * 
     * @since 1.0.0
     */
    private function setup_hooks() {
        // Plugin-Aktivierung und -Deaktivierung
        register_activation_hook(FUNDGRUBE_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(FUNDGRUBE_PLUGIN_FILE, array($this, 'deactivate'));
        
        // Plugin-Initialisierung
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Plugin-Dependencies laden
     * 
     * @since 1.0.0
     */
    private function load_dependencies() {
        // Kern-Klassen laden
        require_once FUNDGRUBE_PLUGIN_PATH . 'includes/class-fundgrube-admin.php';
        require_once FUNDGRUBE_PLUGIN_PATH . 'includes/class-fundgrube-public.php';
        require_once FUNDGRUBE_PLUGIN_PATH . 'includes/class-fundgrube-post-type.php';
        require_once FUNDGRUBE_PLUGIN_PATH . 'includes/class-fundgrube-redirect.php';
    }
    
    /**
     * Plugin-Komponenten initialisieren
     * 
     * @since 1.0.0
     */
    private function init_components() {
        // Admin-Bereich nur im Backend laden
        if (is_admin()) {
            $this->admin = new Fundgrube_Admin();
        }
        
        // Public-Bereich immer laden
        $this->public = new Fundgrube_Public();
        
        // Custom Post Type registrieren
        new Fundgrube_Post_Type();
        
        // Redirect-Handler registrieren
        new Fundgrube_Redirect();
    }
    
    /**
     * Plugin-Initialisierung
     * 
     * @since 1.0.0
     */
    public function init() {
        // Hier können weitere Initialisierungen vorgenommen werden
        do_action('fundgrube_init');
    }
    
    /**
     * Textdomain für Übersetzungen laden
     * 
     * @since 1.0.0
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'fundgrube',
            false,
            dirname(FUNDGRUBE_PLUGIN_BASENAME) . '/languages/'
        );
    }
    
    /**
     * Plugin-Aktivierung
     * 
     * @since 1.0.0
     */
    public function activate() {
        // Custom Post Type registrieren vor Rewrite-Rules Flush
        $this->init_components();
        
        // Flush Rewrite-Rules für Custom Post Types
        flush_rewrite_rules();
        
        // REST API Cache leeren
        wp_cache_delete('alloptions', 'options');
        
        // Plugin-Aktivierungs-Hook
        do_action('fundgrube_activate');
    }
    
    /**
     * Plugin-Deaktivierung
     * 
     * @since 1.0.0
     */
    public function deactivate() {
        // Flush Rewrite-Rules
        flush_rewrite_rules();
        
        // Plugin-Deaktivierungs-Hook
        do_action('fundgrube_deactivate');
    }
}

// Plugin initialisieren
function fundgrube_init() {
    return Fundgrube_Plugin::get_instance();
}

// Plugin beim Laden von WordPress starten
add_action('plugins_loaded', 'fundgrube_init');
