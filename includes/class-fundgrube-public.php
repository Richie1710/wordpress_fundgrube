<?php
/**
 * Public-Klasse für das Fundgrube-Plugin
 * 
 * Verwaltet alle Frontend-Funktionalitäten des Plugins wie Shortcodes,
 * Template-Integration und öffentliche Anzeige der Fundstücke.
 * 
 * @package Fundgrube
 * @subpackage Public
 * @since 1.0.0
 */

// Direkte Ausführung verhindern
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fundgrube Public-Klasse
 * 
 * Behandelt alle öffentlichen Funktionen des Plugins
 * 
 * @since 1.0.0
 */
class Fundgrube_Public {
    
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
        // Shortcodes registrieren
        add_action('init', array($this, 'register_shortcodes'));
        
        // Frontend-Styles und Scripts einbinden
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
        
        // Template-Hooks
        add_filter('single_template', array($this, 'load_single_template'));
        add_filter('archive_template', array($this, 'load_archive_template'));
        
        // Content-Filter
        add_filter('the_content', array($this, 'add_fundgrube_content'));
    }
    
    /**
     * Shortcodes registrieren
     * 
     * @since 1.0.0
     */
    public function register_shortcodes() {
        add_shortcode('fundgrube_liste', array($this, 'shortcode_fundgrube_liste'));
        add_shortcode('fundgrube_suche', array($this, 'shortcode_fundgrube_suche'));
        add_shortcode('fundgrube_einzeln', array($this, 'shortcode_fundgrube_einzeln'));
    }
    
    /**
     * Shortcode für Fundgrube-Liste
     * 
     * Verwendung: [fundgrube_liste anzahl="5" kategorie="verloren"]
     * 
     * @param array $atts Shortcode-Attribute
     * @param string $content Shortcode-Inhalt
     * @return string HTML-Output
     * @since 1.0.0
     */
    public function shortcode_fundgrube_liste($atts, $content = '') {
        $atts = shortcode_atts(array(
            'anzahl'     => 10,
            'kategorie'  => '',
            'sortierung' => 'datum',
            'reihenfolge' => 'desc'
        ), $atts, 'fundgrube_liste');
        
        // Query-Argumente aufbauen
        $args = array(
            'post_type'      => 'fundgrube_item',
            'posts_per_page' => intval($atts['anzahl']),
            'post_status'    => 'publish'
        );
        
        // Sortierung
        switch ($atts['sortierung']) {
            case 'titel':
                $args['orderby'] = 'title';
                break;
            case 'datum':
            default:
                $args['orderby'] = 'date';
                break;
        }
        
        $args['order'] = ($atts['reihenfolge'] === 'asc') ? 'ASC' : 'DESC';
        
        // Kategorie-Filter
        if (!empty($atts['kategorie'])) {
            $args['meta_query'] = array(
                array(
                    'key'     => '_fundgrube_kategorie',
                    'value'   => sanitize_text_field($atts['kategorie']),
                    'compare' => '='
                )
            );
        }
        
        // Query ausführen
        $query = new WP_Query($args);
        
        if (!$query->have_posts()) {
            return '<p class="fundgrube-keine-items">' . __('Keine Fundstücke gefunden.', 'fundgrube') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="fundgrube-liste">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <div class="fundgrube-item">
                    <div class="fundgrube-item-header">
                        <h3 class="fundgrube-item-titel">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        <div class="fundgrube-item-meta">
                            <span class="fundgrube-datum"><?php echo get_the_date(); ?></span>
                            <?php
                            $kategorie = get_post_meta(get_the_ID(), '_fundgrube_kategorie', true);
                            if (!empty($kategorie)) {
                                echo '<span class="fundgrube-kategorie">' . esc_html($kategorie) . '</span>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <?php 
                    // Prüfen ob Galerie aktiviert ist
                    $options = get_option('fundgrube_options', array());
                    $gallery_enabled = $options['enable_gallery'] ?? true;
                    
                    if ($gallery_enabled) {
                        $this->render_item_gallery(get_the_ID());
                    } elseif (has_post_thumbnail()) {
                        ?>
                        <div class="fundgrube-item-bild">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium'); ?>
                            </a>
                        </div>
                        <?php
                    }
                    ?>
                    
                    <div class="fundgrube-item-content">
                        <?php the_excerpt(); ?>
                    </div>
                    
                    <div class="fundgrube-item-actions">
                        <a href="<?php the_permalink(); ?>" class="fundgrube-mehr-button">
                            <?php _e('Mehr Details', 'fundgrube'); ?>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <?php
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Shortcode für Fundgrube-Suche
     * 
     * Verwendung: [fundgrube_suche]
     * 
     * @param array $atts Shortcode-Attribute
     * @param string $content Shortcode-Inhalt
     * @return string HTML-Output
     * @since 1.0.0
     */
    public function shortcode_fundgrube_suche($atts, $content = '') {
        $atts = shortcode_atts(array(
            'platzhalter' => __('Fundstück suchen...', 'fundgrube')
        ), $atts, 'fundgrube_suche');
        
        ob_start();
        ?>
        <div class="fundgrube-suche">
            <form method="get" action="<?php echo home_url('/'); ?>" class="fundgrube-suchform">
                <input type="hidden" name="post_type" value="fundgrube_item">
                <div class="fundgrube-suchfeld-wrapper">
                    <input type="text" 
                           name="s" 
                           value="<?php echo esc_attr(get_search_query()); ?>" 
                           placeholder="<?php echo esc_attr($atts['platzhalter']); ?>"
                           class="fundgrube-suchfeld">
                    <button type="submit" class="fundgrube-such-button">
                        <?php _e('Suchen', 'fundgrube'); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Shortcode für einzelnes Fundstück
     * 
     * Verwendung: [fundgrube_einzeln id="123"]
     * 
     * @param array $atts Shortcode-Attribute
     * @param string $content Shortcode-Inhalt
     * @return string HTML-Output
     * @since 1.0.0
     */
    public function shortcode_fundgrube_einzeln($atts, $content = '') {
        $atts = shortcode_atts(array(
            'id' => 0
        ), $atts, 'fundgrube_einzeln');
        
        $post_id = intval($atts['id']);
        if (!$post_id) {
            return '<p class="fundgrube-fehler">' . __('Keine gültige ID angegeben.', 'fundgrube') . '</p>';
        }
        
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'fundgrube_item') {
            return '<p class="fundgrube-fehler">' . __('Fundstück nicht gefunden.', 'fundgrube') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="fundgrube-einzeln">
            <h2 class="fundgrube-titel"><?php echo esc_html($post->post_title); ?></h2>
            
            <?php if (has_post_thumbnail($post_id)) : ?>
                <div class="fundgrube-bild">
                    <?php echo get_the_post_thumbnail($post_id, 'large'); ?>
                </div>
            <?php endif; ?>
            
            <div class="fundgrube-content">
                <?php echo apply_filters('the_content', $post->post_content); ?>
            </div>
            
            <div class="fundgrube-meta">
                <?php
                $kategorie = get_post_meta($post_id, '_fundgrube_kategorie', true);
                $fundort = get_post_meta($post_id, '_fundgrube_fundort', true);
                $funddatum = get_post_meta($post_id, '_fundgrube_funddatum', true);
                ?>
                
                <?php if (!empty($kategorie)) : ?>
                    <p><strong><?php _e('Kategorie:', 'fundgrube'); ?></strong> <?php echo esc_html($kategorie); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($fundort)) : ?>
                    <p><strong><?php _e('Fundort:', 'fundgrube'); ?></strong> <?php echo esc_html($fundort); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($funddatum)) : ?>
                    <p><strong><?php _e('Funddatum:', 'fundgrube'); ?></strong> <?php echo esc_html($funddatum); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Frontend-Assets einbinden
     * 
     * @since 1.0.0
     */
    public function enqueue_public_assets() {
        wp_enqueue_style(
            'fundgrube-public-style',
            FUNDGRUBE_PLUGIN_URL . 'assets/css/public.css',
            array(),
            FUNDGRUBE_VERSION
        );
        
        wp_enqueue_script(
            'fundgrube-public-script',
            FUNDGRUBE_PLUGIN_URL . 'assets/js/public.js',
            array('jquery'),
            FUNDGRUBE_VERSION,
            true
        );
        
        // Lokalisierung für JavaScript
        wp_localize_script('fundgrube-public-script', 'fundgrube_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('fundgrube_nonce')
        ));
    }
    
    /**
     * Template für Einzelansicht laden
     * 
     * @param string $single_template Aktuelles Template
     * @return string Template-Pfad
     * @since 1.0.0
     */
    public function load_single_template($single_template) {
        global $post;
        
        if ($post->post_type === 'fundgrube_item') {
            $single_template = FUNDGRUBE_PLUGIN_PATH . 'templates/single-fundgrube-item.php';
            if (!file_exists($single_template)) {
                $single_template = FUNDGRUBE_PLUGIN_PATH . 'templates/single-fundgrube.php';
            }
        }
        
        return $single_template;
    }
    
    /**
     * Template für Archiv-Ansicht laden
     * 
     * @param string $archive_template Aktuelles Template
     * @return string Template-Pfad
     * @since 1.0.0
     */
    public function load_archive_template($archive_template) {
        if (is_post_type_archive('fundgrube_item')) {
            $archive_template = FUNDGRUBE_PLUGIN_PATH . 'templates/archive-fundgrube-item.php';
            if (!file_exists($archive_template)) {
                $archive_template = FUNDGRUBE_PLUGIN_PATH . 'templates/archive-fundgrube.php';
            }
        }
        
        return $archive_template;
    }
    
    /**
     * Zusätzlichen Content zu Fundstücken hinzufügen
     * 
     * @param string $content Bestehender Content
     * @return string Erweiteter Content
     * @since 1.0.0
     */
    public function add_fundgrube_content($content) {
        if (is_singular('fundgrube_item')) {
            $meta_content = $this->get_fundgrube_meta_content(get_the_ID());
            $content .= $meta_content;
        }
        
        return $content;
    }
    
    /**
     * Meta-Content für Fundstücke generieren
     * 
     * @param int $post_id Post-ID
     * @return string HTML-Content
     * @since 1.0.0
     */
    private function get_fundgrube_meta_content($post_id) {
        $kategorie = get_post_meta($post_id, '_fundgrube_kategorie', true);
        $fundort = get_post_meta($post_id, '_fundgrube_fundort', true);
        $funddatum = get_post_meta($post_id, '_fundgrube_funddatum', true);
        $kontakt = get_option('fundgrube_options')['contact_info'] ?? '';
        
        ob_start();
        ?>
        <div class="fundgrube-meta-info">
            <h3><?php _e('Fundstück-Details', 'fundgrube'); ?></h3>
            <div class="fundgrube-details">
                <?php if (!empty($kategorie)) : ?>
                    <div class="detail-item">
                        <span class="label"><?php _e('Kategorie:', 'fundgrube'); ?></span>
                        <span class="value"><?php echo esc_html($kategorie); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($fundort)) : ?>
                    <div class="detail-item">
                        <span class="label"><?php _e('Fundort:', 'fundgrube'); ?></span>
                        <span class="value"><?php echo esc_html($fundort); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($funddatum)) : ?>
                    <div class="detail-item">
                        <span class="label"><?php _e('Funddatum:', 'fundgrube'); ?></span>
                        <span class="value"><?php echo esc_html($funddatum); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($kontakt)) : ?>
                    <div class="detail-item kontakt-info">
                        <span class="label"><?php _e('Kontakt:', 'fundgrube'); ?></span>
                        <div class="value"><?php echo wp_kses_post(wpautop($kontakt)); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Gallery für Fundstück rendern
     * 
     * @param int $post_id Post-ID
     * @since 1.0.0
     */
    private function render_item_gallery($post_id) {
        // Hauptbild
        $has_featured_image = has_post_thumbnail($post_id);
        
        // Gallery-Bilder
        $gallery_images = get_post_meta($post_id, '_fundgrube_gallery', true);
        $gallery_images = is_array($gallery_images) ? $gallery_images : array();
        
        // Alle Bilder sammeln (Featured Image + Gallery)
        $all_images = array();
        
        if ($has_featured_image) {
            $all_images[] = array(
                'id' => get_post_thumbnail_id($post_id),
                'type' => 'featured'
            );
        }
        
        foreach ($gallery_images as $attachment_id) {
            $all_images[] = array(
                'id' => $attachment_id,
                'type' => 'gallery'
            );
        }
        
        if (empty($all_images)) {
            return; // Keine Bilder vorhanden
        }
        
        ?>
        <div class="fundgrube-item-gallery">
            <div class="fundgrube-gallery-main">
                <?php
                // Erstes Bild als Hauptbild anzeigen
                $main_image = $all_images[0];
                $image_url = wp_get_attachment_image_url($main_image['id'], 'medium');
                $image_full = wp_get_attachment_image_url($main_image['id'], 'full');
                $image_alt = get_post_meta($main_image['id'], '_wp_attachment_image_alt', true);
                ?>
                <div class="fundgrube-main-image-container">
                    <a href="<?php the_permalink($post_id); ?>" class="fundgrube-main-link">
                        <img src="<?php echo esc_url($image_url); ?>" 
                             alt="<?php echo esc_attr($image_alt); ?>"
                             class="fundgrube-main-image"
                             data-full-size="<?php echo esc_url($image_full); ?>">
                    </a>
                    
                    <?php if (count($all_images) > 1) : ?>
                        <div class="fundgrube-image-count">
                            <span class="fundgrube-count-badge">
                                <span class="dashicons dashicons-camera"></span>
                                <?php echo count($all_images); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (count($all_images) > 1) : ?>
                <div class="fundgrube-gallery-thumbs">
                    <?php 
                    // Maximal 4 weitere Thumbnails anzeigen
                    $thumb_images = array_slice($all_images, 1, 4);
                    foreach ($thumb_images as $index => $image) :
                        $thumb_url = wp_get_attachment_image_url($image['id'], 'thumbnail');
                        $thumb_alt = get_post_meta($image['id'], '_wp_attachment_image_alt', true);
                        $full_url = wp_get_attachment_image_url($image['id'], 'full');
                    ?>
                        <div class="fundgrube-gallery-thumb">
                            <img src="<?php echo esc_url($thumb_url); ?>" 
                                 alt="<?php echo esc_attr($thumb_alt); ?>"
                                 class="fundgrube-thumb-image"
                                 data-full-size="<?php echo esc_url($full_url); ?>">
                                 
                            <?php 
                            // Wenn mehr als 5 Bilder, zeige "+X" Overlay auf dem letzten Thumbnail
                            if ($index === 3 && count($all_images) > 5) :
                                $remaining = count($all_images) - 5;
                            ?>
                                <div class="fundgrube-more-images">
                                    +<?php echo $remaining; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
        .fundgrube-item-gallery {
            margin: 15px 0;
            position: relative;
        }
        
        .fundgrube-main-image-container {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        
        .fundgrube-main-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .fundgrube-main-link:hover .fundgrube-main-image {
            transform: scale(1.05);
        }
        
        .fundgrube-image-count {
            position: absolute;
            top: 8px;
            right: 8px;
        }
        
        .fundgrube-count-badge {
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .fundgrube-count-badge .dashicons {
            width: 16px;
            height: 16px;
            font-size: 16px;
        }
        
        .fundgrube-gallery-thumbs {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 4px;
        }
        
        .fundgrube-gallery-thumb {
            position: relative;
            overflow: hidden;
            border-radius: 4px;
            aspect-ratio: 1;
        }
        
        .fundgrube-thumb-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            cursor: pointer;
            transition: opacity 0.3s ease;
        }
        
        .fundgrube-thumb-image:hover {
            opacity: 0.8;
        }
        
        .fundgrube-more-images {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        
        /* Responsive Design */
        @media (max-width: 480px) {
            .fundgrube-main-image {
                height: 150px;
            }
            
            .fundgrube-gallery-thumbs {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        </style>
        <?php
    }
}
