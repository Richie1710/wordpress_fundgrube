<?php
/**
 * Admin-Klasse für das Fundgrube-Plugin
 * 
 * Verwaltet alle Admin-Funktionalitäten des Plugins wie Menüs,
 * Einstellungsseiten und Backend-spezifische Features.
 * 
 * @package Fundgrube
 * @subpackage Admin
 * @since 1.0.0
 */

// Direkte Ausführung verhindern
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fundgrube Admin-Klasse
 * 
 * Behandelt alle administrativen Funktionen des Plugins
 * 
 * @since 1.0.0
 */
class Fundgrube_Admin {
    
    /**
     * Admin-Menü Slug
     * 
     * @var string
     * @since 1.0.0
     */
    private $menu_slug = 'fundgrube';
    
    /**
     * Settings-Gruppe
     * 
     * @var string
     * @since 1.0.0
     */
    private $settings_group = 'fundgrube_settings';
    
    /**
     * Konstruktor
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
        // Admin-Menü hinzufügen
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Admin-Einstellungen registrieren
        add_action('admin_init', array($this, 'register_settings'));
        
        // Admin-Styles und Scripts einbinden
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Plugin-Aktionslinks hinzufügen
        add_filter('plugin_action_links_' . FUNDGRUBE_PLUGIN_BASENAME, array($this, 'add_plugin_action_links'));
    }
    
    /**
     * Admin-Menü hinzufügen
     * 
     * @since 1.0.0
     */
    public function add_admin_menu() {
        // Hauptmenü-Seite
        add_menu_page(
            __('Fundgrube', 'fundgrube'),                    // Seitentitel
            __('Fundgrube', 'fundgrube'),                    // Menütitel
            'manage_options',                                 // Berechtigung
            $this->menu_slug,                                // Menü-Slug
            array($this, 'display_main_page'),               // Callback-Funktion
            'dashicons-search',                              // Icon
            30                                               // Position
        );
        
        // Unterseiten hinzufügen
        add_submenu_page(
            $this->menu_slug,
            __('Alle Fundstücke', 'fundgrube'),
            __('Alle Fundstücke', 'fundgrube'),
            'manage_options',
            'edit.php?post_type=fundgrube_item'
        );
        
        add_submenu_page(
            $this->menu_slug,
            __('Neues Fundstück', 'fundgrube'),
            __('Neues Fundstück', 'fundgrube'),
            'manage_options',
            'post-new.php?post_type=fundgrube_item'
        );
        
        add_submenu_page(
            $this->menu_slug,
            __('Einstellungen', 'fundgrube'),
            __('Einstellungen', 'fundgrube'),
            'manage_options',
            $this->menu_slug . '-settings',
            array($this, 'display_settings_page')
        );
        
        // Debug-Seite nur für Administratoren mit WP_DEBUG aktiv
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('manage_options')) {
            add_submenu_page(
                $this->menu_slug,
                __('Debug Info', 'fundgrube'),
                __('Debug Info', 'fundgrube'),
                'manage_options',
                $this->menu_slug . '-debug',
                array($this, 'display_debug_page')
            );
        }
    }
    
    /**
     * Hauptseite anzeigen
     * 
     * @since 1.0.0
     */
    public function display_main_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <div class="fundgrube-dashboard">
                <div class="fundgrube-dashboard-widgets">
                    <div class="fundgrube-widget">
                        <h2><?php _e('Willkommen bei Fundgrube!', 'fundgrube'); ?></h2>
                        <p><?php _e('Verwalten Sie Ihre Fundstücke einfach und übersichtlich.', 'fundgrube'); ?></p>
                        <div class="fundgrube-stats">
                            <?php
                            $total_items = wp_count_posts('fundgrube_item');
                            $published_items = $total_items->publish ?? 0;
                            ?>
                            <div class="stat-box">
                                <div class="stat-number"><?php echo esc_html($published_items); ?></div>
                                <div class="stat-label"><?php _e('Veröffentlichte Fundstücke', 'fundgrube'); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="fundgrube-widget">
                        <h2><?php _e('Schnellaktionen', 'fundgrube'); ?></h2>
                        <p>
                            <a href="<?php echo admin_url('post-new.php?post_type=fundgrube_item'); ?>" class="button button-primary">
                                <?php _e('Neues Fundstück hinzufügen', 'fundgrube'); ?>
                            </a>
                        </p>
                        <p>
                            <a href="<?php echo admin_url('edit.php?post_type=fundgrube_item'); ?>" class="button">
                                <?php _e('Alle Fundstücke anzeigen', 'fundgrube'); ?>
                            </a>
                        </p>
                        <p>
                            <a href="<?php echo admin_url('admin.php?page=' . $this->menu_slug . '-settings'); ?>" class="button">
                                <?php _e('Plugin-Einstellungen', 'fundgrube'); ?>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Einstellungsseite anzeigen
     * 
     * @since 1.0.0
     */
    public function display_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields($this->settings_group);
                do_settings_sections($this->settings_group);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Einstellungen registrieren
     * 
     * @since 1.0.0
     */
    public function register_settings() {
        // Einstellungsgruppe registrieren
        register_setting(
            $this->settings_group,
            'fundgrube_options',
            array($this, 'sanitize_settings')
        );
        
        // Hauptsektion hinzufügen
        add_settings_section(
            'fundgrube_main_section',
            __('Allgemeine Einstellungen', 'fundgrube'),
            array($this, 'main_section_callback'),
            $this->settings_group
        );
        
        // Anzeigeeinstellungen
        add_settings_field(
            'items_per_page',
            __('Fundstücke pro Seite', 'fundgrube'),
            array($this, 'items_per_page_callback'),
            $this->settings_group,
            'fundgrube_main_section'
        );
        
        add_settings_field(
            'enable_gallery',
            __('Bildergalerie aktivieren', 'fundgrube'),
            array($this, 'enable_gallery_callback'),
            $this->settings_group,
            'fundgrube_main_section'
        );
        
        add_settings_field(
            'contact_info',
            __('Kontaktinformationen', 'fundgrube'),
            array($this, 'contact_info_callback'),
            $this->settings_group,
            'fundgrube_main_section'
        );
        
        // Datenschutz-Sektion
        add_settings_section(
            'fundgrube_privacy_section',
            __('Datenschutz & Rechtliches', 'fundgrube'),
            array($this, 'privacy_section_callback'),
            $this->settings_group
        );
        
        add_settings_field(
            'enable_redirect_disclaimer',
            __('DSGVO-konforme Weiterleitungsseite aktivieren', 'fundgrube'),
            array($this, 'enable_redirect_disclaimer_callback'),
            $this->settings_group,
            'fundgrube_privacy_section'
        );
        
        add_settings_field(
            'redirect_delay',
            __('Weiterleitungszeit (Sekunden)', 'fundgrube'),
            array($this, 'redirect_delay_callback'),
            $this->settings_group,
            'fundgrube_privacy_section'
        );
    }
    
    /**
     * Hauptsektion Beschreibung
     * 
     * @since 1.0.0
     */
    public function main_section_callback() {
        echo '<p>' . __('Konfigurieren Sie hier die grundlegenden Einstellungen für das Fundgrube-Plugin.', 'fundgrube') . '</p>';
    }
    
    /**
     * Callback für "Fundstücke pro Seite"
     * 
     * @since 1.0.0
     */
    public function items_per_page_callback() {
        $options = get_option('fundgrube_options', array());
        $value = isset($options['items_per_page']) ? $options['items_per_page'] : 10;
        ?>
        <input type="number" 
               name="fundgrube_options[items_per_page]" 
               value="<?php echo esc_attr($value); ?>" 
               min="1" 
               max="50" 
               class="small-text">
        <p class="description"><?php _e('Anzahl der Fundstücke, die pro Seite angezeigt werden sollen.', 'fundgrube'); ?></p>
        <?php
    }
    
    /**
     * Callback für "Bildergalerie aktivieren"
     * 
     * @since 1.0.0
     */
    public function enable_gallery_callback() {
        $options = get_option('fundgrube_options', array());
        $value = isset($options['enable_gallery']) ? $options['enable_gallery'] : true;
        ?>
        <input type="checkbox" 
               name="fundgrube_options[enable_gallery]" 
               value="1" 
               <?php checked($value, true); ?>>
        <label><?php _e('Bildergalerie für Fundstücke aktivieren', 'fundgrube'); ?></label>
        <?php
    }
    
    /**
     * Callback für "Kontaktinformationen"
     * 
     * @since 1.0.0
     */
    public function contact_info_callback() {
        $options = get_option('fundgrube_options', array());
        $value = isset($options['contact_info']) ? $options['contact_info'] : '';
        ?>
        <textarea name="fundgrube_options[contact_info]" 
                  rows="4" 
                  cols="50" 
                  class="large-text"><?php echo esc_textarea($value); ?></textarea>
        <p class="description"><?php _e('Kontaktinformationen, die bei Fundstücken angezeigt werden sollen.', 'fundgrube'); ?></p>
        <?php
    }
    
    /**
     * Callback für Datenschutz-Sektion
     * 
     * @since 1.0.0
     */
    public function privacy_section_callback() {
        echo '<p>' . __('Einstellungen für Datenschutz und rechtliche Compliance.', 'fundgrube') . '</p>';
    }
    
    /**
     * Callback für "DSGVO-Weiterleitung aktivieren"
     * 
     * @since 1.0.0
     */
    public function enable_redirect_disclaimer_callback() {
        $options = get_option('fundgrube_options', array());
        $value = isset($options['enable_redirect_disclaimer']) ? $options['enable_redirect_disclaimer'] : true;
        ?>
        <input type="checkbox" 
               name="fundgrube_options[enable_redirect_disclaimer]" 
               value="1" 
               <?php checked($value, true); ?>>
        <label><?php _e('DSGVO-konforme Weiterleitungsseite für externe Social Media Links aktivieren', 'fundgrube'); ?></label>
        <p class="description">
            <?php _e('Zeigt eine Disclaimer-Seite vor der Weiterleitung zu Facebook, Twitter und WhatsApp gemäß deutschem Recht (DSGVO/TMG).', 'fundgrube'); ?>
        </p>
        <?php
    }
    
    /**
     * Callback für "Weiterleitungszeit"
     * 
     * @since 1.0.0
     */
    public function redirect_delay_callback() {
        $options = get_option('fundgrube_options', array());
        $value = isset($options['redirect_delay']) ? $options['redirect_delay'] : 5;
        ?>
        <input type="number" 
               name="fundgrube_options[redirect_delay]" 
               value="<?php echo esc_attr($value); ?>" 
               min="1" 
               max="30" 
               class="small-text">
        <label><?php _e('Sekunden', 'fundgrube'); ?></label>
        <p class="description">
            <?php _e('Zeit in Sekunden, die vor der automatischen Weiterleitung gewartet wird. Empfohlen: 3-10 Sekunden.', 'fundgrube'); ?>
        </p>
        <?php
    }
    
    /**
     * Einstellungen validieren und bereinigen
     * 
     * @param array $input Eingabedaten
     * @return array Bereinigte Daten
     * @since 1.0.0
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        if (isset($input['items_per_page'])) {
            $sanitized['items_per_page'] = absint($input['items_per_page']);
            if ($sanitized['items_per_page'] < 1 || $sanitized['items_per_page'] > 50) {
                $sanitized['items_per_page'] = 10;
            }
        }
        
        if (isset($input['enable_gallery'])) {
            $sanitized['enable_gallery'] = (bool) $input['enable_gallery'];
        } else {
            $sanitized['enable_gallery'] = false;
        }
        
        if (isset($input['contact_info'])) {
            $sanitized['contact_info'] = sanitize_textarea_field($input['contact_info']);
        }
        
        if (isset($input['enable_redirect_disclaimer'])) {
            $sanitized['enable_redirect_disclaimer'] = (bool) $input['enable_redirect_disclaimer'];
        } else {
            $sanitized['enable_redirect_disclaimer'] = false;
        }
        
        if (isset($input['redirect_delay'])) {
            $sanitized['redirect_delay'] = absint($input['redirect_delay']);
            if ($sanitized['redirect_delay'] < 1 || $sanitized['redirect_delay'] > 30) {
                $sanitized['redirect_delay'] = 5;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Admin-Assets einbinden
     * 
     * @param string $hook_suffix Aktuelle Admin-Seite
     * @since 1.0.0
     */
    public function enqueue_admin_assets($hook_suffix) {
        // Auf Fundgrube-Admin-Seiten und Post-Edit-Seiten laden
        $should_load = false;
        
        // Fundgrube-Admin-Seiten
        if (strpos($hook_suffix, $this->menu_slug) !== false) {
            $should_load = true;
        }
        
        // Post-Edit-Seiten für Fundgrube Items
        if (in_array($hook_suffix, array('post.php', 'post-new.php'))) {
            global $post;
            if ($post && $post->post_type === 'fundgrube_item') {
                $should_load = true;
            }
            // Auch laden wenn kein Post gesetzt ist (bei post-new.php mit post_type Parameter)
            if (isset($_GET['post_type']) && $_GET['post_type'] === 'fundgrube_item') {
                $should_load = true;
            }
        }
        
        if (!$should_load) {
            return;
        }
        
        wp_enqueue_style(
            'fundgrube-admin-style',
            FUNDGRUBE_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            FUNDGRUBE_VERSION
        );
        
        // WordPress Media Library einbinden
        wp_enqueue_media();
        
        wp_enqueue_script(
            'fundgrube-admin-script',
            FUNDGRUBE_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'media-upload', 'media-views'),
            FUNDGRUBE_VERSION,
            true
        );
    }
    
    /**
     * Plugin-Aktionslinks hinzufügen
     * 
     * @param array $links Bestehende Links
     * @return array Erweiterte Links
     * @since 1.0.0
     */
    public function add_plugin_action_links($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=' . $this->menu_slug . '-settings'),
            __('Einstellungen', 'fundgrube')
        );
        
        array_unshift($links, $settings_link);
        
        return $links;
    }
    
    /**
     * Debug-Seite anzeigen
     * 
     * @since 1.0.0
     */
    public function display_debug_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <div class="fundgrube-debug-info">
                
                <h2><?php _e('REST API Status', 'fundgrube'); ?></h2>
                <?php
                $rest_url = rest_url('wp/v2/fundstuecke');
                $rest_response = wp_remote_get($rest_url);
                ?>
                <p><strong><?php _e('REST API URL:', 'fundgrube'); ?></strong> <code><?php echo esc_html($rest_url); ?></code></p>
                <p><strong><?php _e('REST API Status:', 'fundgrube'); ?></strong> 
                    <?php if (is_wp_error($rest_response)) : ?>
                        <span style="color: red;"><?php _e('Fehler', 'fundgrube'); ?>: <?php echo esc_html($rest_response->get_error_message()); ?></span>
                    <?php else : ?>
                        <span style="color: green;"><?php _e('OK', 'fundgrube'); ?> (<?php echo wp_remote_retrieve_response_code($rest_response); ?>)</span>
                    <?php endif; ?>
                </p>
                
                <h2><?php _e('Permalink-Struktur', 'fundgrube'); ?></h2>
                <p><strong><?php _e('Permalink-Struktur:', 'fundgrube'); ?></strong> <code><?php echo esc_html(get_option('permalink_structure')); ?></code></p>
                <p><strong><?php _e('Fundstück-URL:', 'fundgrube'); ?></strong> <code><?php echo esc_html(home_url('/fundstueck/')); ?></code></p>
                
                <h2><?php _e('Post Type Status', 'fundgrube'); ?></h2>
                <?php
                $post_type_object = get_post_type_object('fundgrube_item');
                ?>
                <p><strong><?php _e('Post Type registriert:', 'fundgrube'); ?></strong> 
                    <?php echo $post_type_object ? '<span style="color: green;">' . __('Ja', 'fundgrube') . '</span>' : '<span style="color: red;">' . __('Nein', 'fundgrube') . '</span>'; ?>
                </p>
                <p><strong><?php _e('REST API aktiviert:', 'fundgrube'); ?></strong> 
                    <?php echo ($post_type_object && $post_type_object->show_in_rest) ? '<span style="color: green;">' . __('Ja', 'fundgrube') . '</span>' : '<span style="color: red;">' . __('Nein', 'fundgrube') . '</span>'; ?>
                </p>
                
                <h2><?php _e('Plugin-Informationen', 'fundgrube'); ?></h2>
                <p><strong><?php _e('Plugin-Version:', 'fundgrube'); ?></strong> <?php echo esc_html(FUNDGRUBE_VERSION); ?></p>
                <p><strong><?php _e('WordPress-Version:', 'fundgrube'); ?></strong> <?php echo esc_html(get_bloginfo('version')); ?></p>
                <p><strong><?php _e('PHP-Version:', 'fundgrube'); ?></strong> <?php echo esc_html(phpversion()); ?></p>
                
                <h2><?php _e('Plugin-Konstanten', 'fundgrube'); ?></h2>
                <ul>
                    <li><strong>FUNDGRUBE_VERSION:</strong> <code><?php echo esc_html(FUNDGRUBE_VERSION); ?></code></li>
                    <li><strong>FUNDGRUBE_PLUGIN_URL:</strong> <code><?php echo esc_html(FUNDGRUBE_PLUGIN_URL); ?></code></li>
                    <li><strong>FUNDGRUBE_PLUGIN_PATH:</strong> <code><?php echo esc_html(FUNDGRUBE_PLUGIN_PATH); ?></code></li>
                </ul>
                
                <h2><?php _e('Aktionen', 'fundgrube'); ?></h2>
                <p>
                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=' . $this->menu_slug . '-debug&action=flush_rewrite'), 'fundgrube_flush_rewrite'); ?>" class="button">
                        <?php _e('Rewrite-Rules aktualisieren', 'fundgrube'); ?>
                    </a>
                </p>
                
                <?php
                // Handle flush rewrite action
                if (isset($_GET['action']) && $_GET['action'] === 'flush_rewrite' && wp_verify_nonce($_GET['_wpnonce'], 'fundgrube_flush_rewrite')) {
                    flush_rewrite_rules();
                    echo '<div class="notice notice-success"><p>' . __('Rewrite-Rules wurden aktualisiert.', 'fundgrube') . '</p></div>';
                }
                ?>
                
            </div>
        </div>
        <?php
    }
}
