<?php
/**
 * Fundgrube Redirect Handler
 * 
 * Verwaltet DSGVO-konforme Weiterleitungen zu externen Websites
 *
 * @package Fundgrube
 * @subpackage Classes
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fundgrube_Redirect Class
 * 
 * Verwaltet die Weiterleitung zu externen Social Media Plattformen
 * mit rechtlich konformer Disclaimer-Seite gemäß DSGVO/TMG
 * 
 * @since 1.0.0
 */
class Fundgrube_Redirect {
    
    /**
     * Erlaubte Services für Weiterleitung
     *
     * @since 1.0.0
     * @var array
     */
    private $allowed_services = array('facebook', 'twitter', 'whatsapp');
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * WordPress-Hooks initialisieren
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        add_action('init', array($this, 'add_rewrite_rules'));
        add_action('template_redirect', array($this, 'handle_redirect_request'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_redirect_styles'));
        
        // Flush rewrite rules bei Plugin-Aktivierung
        add_action('fundgrube_activate', array($this, 'flush_rewrite_rules'));
    }
    
    /**
     * Rewrite Rules für Weiterleitungs-Endpoint hinzufügen
     *
     * @since 1.0.0
     */
    public function add_rewrite_rules() {
        add_rewrite_rule(
            '^fundgrube/redirect/?$',
            'index.php?fundgrube_redirect=1',
            'top'
        );
    }
    
    /**
     * Query-Variablen hinzufügen
     *
     * @param array $vars Bestehende Query-Variablen
     * @return array Erweiterte Query-Variablen
     * @since 1.0.0
     */
    public function add_query_vars($vars) {
        $vars[] = 'fundgrube_redirect';
        return $vars;
    }
    
    /**
     * CSS für Weiterleitungsseite laden
     *
     * @since 1.0.0
     */
    public function enqueue_redirect_styles() {
        // Nur auf Weiterleitungsseite laden
        if (get_query_var('fundgrube_redirect')) {
            wp_enqueue_style(
                'fundgrube-redirect-style',
                FUNDGRUBE_PLUGIN_URL . 'assets/css/redirect.css',
                array(),
                FUNDGRUBE_VERSION
            );
            
            // WordPress Dashicons laden
            wp_enqueue_style('dashicons');
        }
    }
    
    /**
     * Weiterleitungs-Anfragen verarbeiten
     *
     * @since 1.0.0
     */
    public function handle_redirect_request() {
        if (!get_query_var('fundgrube_redirect')) {
            return;
        }
        
        // Debug-Information (nur bei WP_DEBUG)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Fundgrube Redirect: Processing request');
            error_log('Query var fundgrube_redirect: ' . get_query_var('fundgrube_redirect'));
            error_log('GET params: ' . print_r($_GET, true));
        }
        
        // Validate parameters  
        $target_url = isset($_GET['url']) ? esc_url_raw(rawurldecode($_GET['url'])) : '';
        $service = isset($_GET['service']) ? sanitize_text_field($_GET['service']) : '';
        
        // Sicherheitsprüfungen
        if (!$this->is_valid_redirect_request($target_url, $service)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Fundgrube Redirect: Invalid request - URL: ' . $target_url . ' Service: ' . $service);
            }
            wp_redirect(home_url());
            exit;
        }
        
        // Template laden
        $this->load_redirect_template();
        exit;
    }
    
    /**
     * Weiterleitungs-Anfrage validieren
     *
     * @param string $target_url Ziel-URL
     * @param string $service Service-Name
     * @return bool True wenn gültig
     * @since 1.0.0
     */
    private function is_valid_redirect_request($target_url, $service) {
        // URL validieren
        if (empty($target_url) || !filter_var($target_url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Service validieren
        if (!in_array($service, $this->allowed_services)) {
            return false;
        }
        
        // Domain-Whitelist prüfen
        if (!$this->is_allowed_domain($target_url, $service)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Domain-Whitelist für Services prüfen
     *
     * @param string $url URL zum Prüfen
     * @param string $service Service-Name  
     * @return bool True wenn erlaubt
     * @since 1.0.0
     */
    private function is_allowed_domain($url, $service) {
        $parsed_url = parse_url($url);
        $domain = isset($parsed_url['host']) ? strtolower($parsed_url['host']) : '';
        
        $allowed_domains = array(
            'facebook' => array(
                'facebook.com',
                'www.facebook.com',
                'm.facebook.com'
            ),
            'twitter' => array(
                'twitter.com',
                'www.twitter.com',
                'x.com',
                'www.x.com'
            ),
            'whatsapp' => array(
                'wa.me',
                'api.whatsapp.com',
                'web.whatsapp.com'
            )
        );
        
        if (!isset($allowed_domains[$service])) {
            return false;
        }
        
        return in_array($domain, $allowed_domains[$service]);
    }
    
    /**
     * Redirect-Template laden
     *
     * @since 1.0.0
     */
    private function load_redirect_template() {
        $template_path = FUNDGRUBE_PLUGIN_PATH . 'templates/redirect-disclaimer.php';
        
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            // Fallback bei fehlendem Template
            $this->show_fallback_page();
        }
    }
    
    /**
     * Fallback-Seite anzeigen
     *
     * @since 1.0.0
     */
    private function show_fallback_page() {
        $target_url = isset($_GET['url']) ? esc_url($_GET['url']) : home_url();
        
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?php _e('Weiterleitung', 'fundgrube'); ?></title>
            <meta name="robots" content="noindex, nofollow">
            <meta http-equiv="refresh" content="3;url=<?php echo esc_attr($target_url); ?>">
        </head>
        <body style="font-family: Arial, sans-serif; text-align: center; padding: 50px;">
            <h1><?php _e('Sie werden weitergeleitet...', 'fundgrube'); ?></h1>
            <p><?php _e('Falls die Weiterleitung nicht automatisch erfolgt:', 'fundgrube'); ?></p>
            <a href="<?php echo esc_url($target_url); ?>"><?php _e('Hier klicken', 'fundgrube'); ?></a>
        </body>
        </html>
        <?php
    }
    
    /**
     * URL für Weiterleitungs-Seite generieren
     *
     * @param string $target_url Ziel-URL
     * @param string $service Service-Name
     * @param string $title Optional: Titel des geteilten Items
     * @return string Weiterleitungs-URL
     * @since 1.0.0
     */
    public static function get_redirect_url($target_url, $service, $title = '') {
        $args = array(
            'url' => $target_url, // Nicht doppelt encodieren!
            'service' => $service
        );
        
        if (!empty($title)) {
            $args['title'] = $title; // Auch hier nicht doppelt encodieren
        }
        
        return home_url('/fundgrube/redirect/?' . http_build_query($args));
    }
    
    /**
     * Social Sharing URLs generieren
     *
     * @param int $post_id Post-ID
     * @return array Array mit Sharing-URLs
     * @since 1.0.0
     */
    public static function get_social_sharing_urls($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return array();
        }
        
        $url = get_permalink($post_id);
        $title = get_the_title($post_id);
        $description = wp_trim_words(get_the_content(null, false, $post), 20);
        
        // Original Social Media URLs
        $original_urls = array(
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?' . http_build_query(array(
                'u' => $url,
                'quote' => $title
            )),
            'twitter' => 'https://twitter.com/intent/tweet?' . http_build_query(array(
                'text' => $title,
                'url' => $url
            )),
            'whatsapp' => 'https://wa.me/?' . http_build_query(array(
                'text' => $title . ' - ' . $url
            ))
        );
        
        // Prüfen ob Disclaimer aktiviert ist
        $options = get_option('fundgrube_options', array());
        $disclaimer_enabled = isset($options['enable_redirect_disclaimer']) ? $options['enable_redirect_disclaimer'] : true;
        
        if (!$disclaimer_enabled) {
            // Direkte Links ohne Disclaimer
            return $original_urls;
        }
        
        // Mit Disclaimer-Seite umhüllen
        $redirect_urls = array();
        foreach ($original_urls as $service => $original_url) {
            $redirect_urls[$service] = self::get_redirect_url($original_url, $service, $title);
        }
        
        return $redirect_urls;
    }
    
    /**
     * Rewrite Rules neu laden
     *
     * @since 1.0.0
     */
    public function flush_rewrite_rules() {
        $this->add_rewrite_rules();
        flush_rewrite_rules();
    }
}
