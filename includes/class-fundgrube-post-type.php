<?php
/**
 * Custom Post Type-Klasse für das Fundgrube-Plugin
 * 
 * Registriert und verwaltet den Custom Post Type für Fundstücke
 * sowie die dazugehörigen Meta-Felder und Funktionalitäten.
 * 
 * @package Fundgrube
 * @subpackage PostType
 * @since 1.0.0
 */

// Direkte Ausführung verhindern
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fundgrube Post Type-Klasse
 * 
 * Behandelt die Registrierung und Verwaltung des Custom Post Types
 * 
 * @since 1.0.0
 */
class Fundgrube_Post_Type {
    
    /**
     * Post Type Bezeichnung
     * 
     * @var string
     * @since 1.0.0
     */
    private $post_type = 'fundgrube_item';
    
    /**
     * Meta-Felder für Fundstücke
     * 
     * @var array
     * @since 1.0.0
     */
    private $meta_fields = array();
    
    /**
     * Konstruktor
     * 
     * @since 1.0.0
     */
    public function __construct() {
        $this->init_hooks();
        $this->setup_meta_fields();
    }
    
    /**
     * WordPress-Hooks initialisieren
     * 
     * @since 1.0.0
     */
    private function init_hooks() {
        // Post Type registrieren
        add_action('init', array($this, 'register_post_type'));
        
        // Meta-Boxen hinzufügen
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        
        // Meta-Daten speichern
        add_action('save_post', array($this, 'save_meta_fields'));
        
        // Admin-Spalten anpassen
        add_filter('manage_' . $this->post_type . '_posts_columns', array($this, 'add_admin_columns'));
        add_action('manage_' . $this->post_type . '_posts_custom_column', array($this, 'populate_admin_columns'), 10, 2);
        
        // Sortierbare Spalten
        add_filter('manage_edit-' . $this->post_type . '_sortable_columns', array($this, 'sortable_columns'));
        
        // REST API Meta-Felder registrieren
        add_action('rest_api_init', array($this, 'register_rest_fields'));
    }
    
    /**
     * Meta-Felder definieren
     * 
     * @since 1.0.0
     */
    private function setup_meta_fields() {
        $this->meta_fields = array(
            '_fundgrube_kategorie' => array(
                'label'       => __('Kategorie', 'fundgrube'),
                'type'        => 'select',
                'options'     => array(
                    'verloren'    => __('Verloren', 'fundgrube'),
                    'gefunden'    => __('Gefunden', 'fundgrube'),
                    'zurueckgegeben' => __('Zurückgegeben', 'fundgrube')
                ),
                'description' => __('Wählen Sie die Kategorie des Fundstücks.', 'fundgrube')
            ),
            '_fundgrube_fundort' => array(
                'label'       => __('Fundort', 'fundgrube'),
                'type'        => 'text',
                'placeholder' => __('z.B. Parkplatz, Büro, Straße...', 'fundgrube'),
                'description' => __('Wo wurde das Fundstück gefunden?', 'fundgrube')
            ),
            '_fundgrube_funddatum' => array(
                'label'       => __('Funddatum', 'fundgrube'),
                'type'        => 'date',
                'description' => __('Wann wurde das Fundstück gefunden?', 'fundgrube')
            ),
            '_fundgrube_beschreibung' => array(
                'label'       => __('Detaillierte Beschreibung', 'fundgrube'),
                'type'        => 'textarea',
                'rows'        => 4,
                'description' => __('Ausführliche Beschreibung des Fundstücks (Farbe, Größe, Besonderheiten).', 'fundgrube')
            ),
            '_fundgrube_farbe' => array(
                'label'       => __('Hauptfarbe', 'fundgrube'),
                'type'        => 'text',
                'placeholder' => __('z.B. rot, blau, schwarz...', 'fundgrube'),
                'description' => __('Hauptfarbe des Fundstücks.', 'fundgrube')
            ),
            '_fundgrube_groesse' => array(
                'label'       => __('Größe/Abmessungen', 'fundgrube'),
                'type'        => 'text',
                'placeholder' => __('z.B. klein, 20cm x 15cm...', 'fundgrube'),
                'description' => __('Ungefähre Größe oder Abmessungen.', 'fundgrube')
            ),
            '_fundgrube_zustand' => array(
                'label'       => __('Zustand', 'fundgrube'),
                'type'        => 'select',
                'options'     => array(
                    'neu'         => __('Neu/Sehr gut', 'fundgrube'),
                    'gut'         => __('Gut', 'fundgrube'),
                    'gebraucht'   => __('Gebraucht', 'fundgrube'),
                    'beschaedigt' => __('Beschädigt', 'fundgrube')
                ),
                'description' => __('In welchem Zustand befindet sich das Fundstück?', 'fundgrube')
            ),
            '_fundgrube_kontakt_person' => array(
                'label'       => __('Kontaktperson', 'fundgrube'),
                'type'        => 'text',
                'placeholder' => __('Name der Kontaktperson', 'fundgrube'),
                'description' => __('Wer ist für dieses Fundstück zuständig?', 'fundgrube')
            ),
            '_fundgrube_status' => array(
                'label'       => __('Status', 'fundgrube'),
                'type'        => 'select',
                'options'     => array(
                    'verfuegbar'    => __('Verfügbar', 'fundgrube'),
                    'reserviert'    => __('Reserviert', 'fundgrube'),
                    'abgeholt'      => __('Abgeholt', 'fundgrube'),
                    'entsorgt'      => __('Entsorgt', 'fundgrube')
                ),
                'description' => __('Aktueller Status des Fundstücks.', 'fundgrube')
            ),
            '_fundgrube_gallery' => array(
                'label'       => __('Bildergalerie', 'fundgrube'),
                'type'        => 'gallery',
                'description' => __('Zusätzliche Bilder für das Fundstück (neben dem Hauptbild).', 'fundgrube')
            )
        );
    }
    
    /**
     * Custom Post Type registrieren
     * 
     * @since 1.0.0
     */
    public function register_post_type() {
        $labels = array(
            'name'               => __('Fundstücke', 'fundgrube'),
            'singular_name'      => __('Fundstück', 'fundgrube'),
            'menu_name'          => __('Fundstücke', 'fundgrube'),
            'name_admin_bar'     => __('Fundstück', 'fundgrube'),
            'add_new'            => __('Neu hinzufügen', 'fundgrube'),
            'add_new_item'       => __('Neues Fundstück hinzufügen', 'fundgrube'),
            'new_item'           => __('Neues Fundstück', 'fundgrube'),
            'edit_item'          => __('Fundstück bearbeiten', 'fundgrube'),
            'view_item'          => __('Fundstück anzeigen', 'fundgrube'),
            'all_items'          => __('Alle Fundstücke', 'fundgrube'),
            'search_items'       => __('Fundstücke suchen', 'fundgrube'),
            'parent_item_colon'  => __('Übergeordnetes Fundstück:', 'fundgrube'),
            'not_found'          => __('Keine Fundstücke gefunden.', 'fundgrube'),
            'not_found_in_trash' => __('Keine Fundstücke im Papierkorb gefunden.', 'fundgrube'),
            'archives'           => __('Fundstück-Archive', 'fundgrube'),
            'attributes'         => __('Fundstück-Attribute', 'fundgrube'),
            'insert_into_item'   => __('In Fundstück einfügen', 'fundgrube'),
            'uploaded_to_this_item' => __('Zu diesem Fundstück hochgeladen', 'fundgrube'),
            'featured_image'     => __('Hauptbild', 'fundgrube'),
            'set_featured_image' => __('Hauptbild setzen', 'fundgrube'),
            'remove_featured_image' => __('Hauptbild entfernen', 'fundgrube'),
            'use_featured_image' => __('Als Hauptbild verwenden', 'fundgrube'),
            'filter_items_list'  => __('Fundstücke-Liste filtern', 'fundgrube'),
            'items_list_navigation' => __('Fundstücke-Listen-Navigation', 'fundgrube'),
            'items_list'         => __('Fundstücke-Liste', 'fundgrube')
        );
        
        $args = array(
            'labels'             => $labels,
            'description'        => __('Verwaltung von Fundstücken', 'fundgrube'),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => false, // Wird über das Hauptmenü verwaltet
            'query_var'          => true,
            'rewrite'            => array('slug' => 'fundstueck'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-search',
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest'       => true, // Gutenberg-Unterstützung
            'rest_base'          => 'fundstuecke',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'rest_namespace'     => 'wp/v2'
        );
        
        register_post_type($this->post_type, $args);
    }
    
    /**
     * Meta-Boxen hinzufügen
     * 
     * @since 1.0.0
     */
    public function add_meta_boxes() {
        add_meta_box(
            'fundgrube_details',
            __('Fundstück-Details', 'fundgrube'),
            array($this, 'render_meta_box'),
            $this->post_type,
            'normal',
            'high'
        );
    }
    
    /**
     * Meta-Box rendern
     * 
     * @param WP_Post $post Aktueller Post
     * @since 1.0.0
     */
    public function render_meta_box($post) {
        // Nonce-Feld für Sicherheit
        wp_nonce_field('fundgrube_meta_box', 'fundgrube_meta_nonce');
        
        echo '<table class="form-table">';
        
        foreach ($this->meta_fields as $field_key => $field) {
            $value = get_post_meta($post->ID, $field_key, true);
            
            echo '<tr>';
            echo '<th scope="row"><label for="' . esc_attr($field_key) . '">' . esc_html($field['label']) . '</label></th>';
            echo '<td>';
            
            $this->render_field($field_key, $field, $value);
            
            if (!empty($field['description'])) {
                echo '<p class="description">' . esc_html($field['description']) . '</p>';
            }
            
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
    }
    
    /**
     * Einzelnes Feld rendern
     * 
     * @param string $field_key Feld-Schlüssel
     * @param array $field Feld-Konfiguration
     * @param mixed $value Aktueller Wert
     * @since 1.0.0
     */
    private function render_field($field_key, $field, $value) {
        switch ($field['type']) {
            case 'text':
                echo '<input type="text" id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '" value="' . esc_attr($value) . '" class="regular-text"';
                if (!empty($field['placeholder'])) {
                    echo ' placeholder="' . esc_attr($field['placeholder']) . '"';
                }
                echo '>';
                break;
                
            case 'textarea':
                $rows = $field['rows'] ?? 3;
                echo '<textarea id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '" rows="' . intval($rows) . '" class="large-text">' . esc_textarea($value) . '</textarea>';
                break;
                
            case 'select':
                echo '<select id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '">';
                echo '<option value="">' . __('Bitte wählen...', 'fundgrube') . '</option>';
                foreach ($field['options'] as $option_value => $option_label) {
                    echo '<option value="' . esc_attr($option_value) . '"' . selected($value, $option_value, false) . '>' . esc_html($option_label) . '</option>';
                }
                echo '</select>';
                break;
                
            case 'date':
                echo '<input type="date" id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '" value="' . esc_attr($value) . '">';
                break;
                
            case 'gallery':
                $this->render_gallery_field($field_key, $value);
                break;
        }
    }
    
    /**
     * Gallery-Feld rendern
     * 
     * @param string $field_key Feld-Schlüssel
     * @param mixed $value Aktuelle Werte (Array von Attachment-IDs)
     * @since 1.0.0
     */
    private function render_gallery_field($field_key, $value) {
        // Prüfen ob Galerie-Feature aktiviert ist
        $options = get_option('fundgrube_options', array());
        $gallery_enabled = $options['enable_gallery'] ?? true;
        
        if (!$gallery_enabled) {
            echo '<p class="description">' . __('Bildergalerie ist in den Plugin-Einstellungen deaktiviert.', 'fundgrube') . '</p>';
            return;
        }
        
        $attachment_ids = is_array($value) ? $value : array();
        ?>
        <div class="fundgrube-gallery-field" id="<?php echo esc_attr($field_key); ?>_container">
            <!-- Hidden Input für die Attachment-IDs -->
            <input type="hidden" name="<?php echo esc_attr($field_key); ?>" id="<?php echo esc_attr($field_key); ?>" value="<?php echo esc_attr(implode(',', $attachment_ids)); ?>">
            
            <!-- Gallery Preview Container -->
            <div class="fundgrube-gallery-preview" id="<?php echo esc_attr($field_key); ?>_preview">
                <?php if (!empty($attachment_ids)) : ?>
                    <?php foreach ($attachment_ids as $attachment_id) : ?>
                        <div class="fundgrube-gallery-item" data-attachment-id="<?php echo esc_attr($attachment_id); ?>">
                            <?php echo wp_get_attachment_image($attachment_id, 'thumbnail'); ?>
                            <div class="fundgrube-gallery-item-actions">
                                <button type="button" class="fundgrube-remove-image" title="<?php esc_attr_e('Bild entfernen', 'fundgrube'); ?>">×</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Add Images Button -->
            <p>
                <button type="button" class="button fundgrube-add-images" data-field-id="<?php echo esc_attr($field_key); ?>">
                    <?php _e('Bilder hinzufügen', 'fundgrube'); ?>
                </button>
                <?php if (!empty($attachment_ids)) : ?>
                    <button type="button" class="button fundgrube-clear-gallery" data-field-id="<?php echo esc_attr($field_key); ?>">
                        <?php _e('Alle entfernen', 'fundgrube'); ?>
                    </button>
                <?php endif; ?>
            </p>
        </div>
        
        <style>
        .fundgrube-gallery-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 10px 0;
            min-height: 50px;
            padding: 10px;
            border: 2px dashed #ddd;
            border-radius: 4px;
        }
        .fundgrube-gallery-item {
            position: relative;
            display: inline-block;
        }
        .fundgrube-gallery-item img {
            max-width: 100px;
            max-height: 100px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .fundgrube-gallery-item-actions {
            position: absolute;
            top: -5px;
            right: -5px;
        }
        .fundgrube-remove-image {
            background: #dc3232;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            cursor: pointer;
            line-height: 1;
        }
        .fundgrube-remove-image:hover {
            background: #a00;
        }
        </style>
        
        <?php
    }
    
    /**
     * Meta-Felder speichern
     * 
     * @param int $post_id Post-ID
     * @since 1.0.0
     */
    public function save_meta_fields($post_id) {
        // Sicherheitsprüfungen
        if (!isset($_POST['fundgrube_meta_nonce']) || !wp_verify_nonce($_POST['fundgrube_meta_nonce'], 'fundgrube_meta_box')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        if (get_post_type($post_id) !== $this->post_type) {
            return;
        }
        
        // Meta-Felder speichern
        foreach ($this->meta_fields as $field_key => $field) {
            if (isset($_POST[$field_key])) {
                $value = $_POST[$field_key];
                
                // Wert bereinigen basierend auf Feld-Typ
                switch ($field['type']) {
                    case 'textarea':
                        $value = sanitize_textarea_field($value);
                        break;
                    case 'date':
                        $value = sanitize_text_field($value);
                        // Zusätzliche Datumsvalidierung könnte hier erfolgen
                        break;
                    case 'gallery':
                        // Gallery-IDs als Array speichern
                        if (is_string($value)) {
                            $attachment_ids = array_filter(explode(',', $value));
                            $attachment_ids = array_map('intval', $attachment_ids);
                            $value = $attachment_ids;
                        } elseif (!is_array($value)) {
                            $value = array();
                        }
                        break;
                    default:
                        $value = sanitize_text_field($value);
                        break;
                }
                
                update_post_meta($post_id, $field_key, $value);
            } else {
                // Für Gallery-Felder leeres Array setzen statt löschen
                if ($field['type'] === 'gallery') {
                    update_post_meta($post_id, $field_key, array());
                } else {
                    delete_post_meta($post_id, $field_key);
                }
            }
        }
    }
    
    /**
     * Admin-Spalten hinzufügen
     * 
     * @param array $columns Bestehende Spalten
     * @return array Erweiterte Spalten
     * @since 1.0.0
     */
    public function add_admin_columns($columns) {
        // Position nach dem Titel
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['kategorie'] = __('Kategorie', 'fundgrube');
                $new_columns['fundort'] = __('Fundort', 'fundgrube');
                $new_columns['funddatum'] = __('Funddatum', 'fundgrube');
                $new_columns['status'] = __('Status', 'fundgrube');
            }
        }
        
        return $new_columns;
    }
    
    /**
     * Admin-Spalten befüllen
     * 
     * @param string $column Spalten-Name
     * @param int $post_id Post-ID
     * @since 1.0.0
     */
    public function populate_admin_columns($column, $post_id) {
        switch ($column) {
            case 'kategorie':
                $kategorie = get_post_meta($post_id, '_fundgrube_kategorie', true);
                if (!empty($kategorie) && isset($this->meta_fields['_fundgrube_kategorie']['options'][$kategorie])) {
                    echo esc_html($this->meta_fields['_fundgrube_kategorie']['options'][$kategorie]);
                } else {
                    echo '—';
                }
                break;
                
            case 'fundort':
                $fundort = get_post_meta($post_id, '_fundgrube_fundort', true);
                echo !empty($fundort) ? esc_html($fundort) : '—';
                break;
                
            case 'funddatum':
                $funddatum = get_post_meta($post_id, '_fundgrube_funddatum', true);
                if (!empty($funddatum)) {
                    echo esc_html(date_i18n(get_option('date_format'), strtotime($funddatum)));
                } else {
                    echo '—';
                }
                break;
                
            case 'status':
                $status = get_post_meta($post_id, '_fundgrube_status', true);
                if (!empty($status) && isset($this->meta_fields['_fundgrube_status']['options'][$status])) {
                    echo '<span class="fundgrube-status fundgrube-status-' . esc_attr($status) . '">';
                    echo esc_html($this->meta_fields['_fundgrube_status']['options'][$status]);
                    echo '</span>';
                } else {
                    echo '—';
                }
                break;
        }
    }
    
    /**
     * Sortierbare Spalten definieren
     * 
     * @param array $columns Bestehende sortierbare Spalten
     * @return array Erweiterte sortierbare Spalten
     * @since 1.0.0
     */
    public function sortable_columns($columns) {
        $columns['kategorie'] = '_fundgrube_kategorie';
        $columns['fundort'] = '_fundgrube_fundort';
        $columns['funddatum'] = '_fundgrube_funddatum';
        $columns['status'] = '_fundgrube_status';
        
        return $columns;
    }
    
    /**
     * REST API-Felder für Meta-Daten registrieren
     * 
     * @since 1.0.0
     */
    public function register_rest_fields() {
        // Meta-Felder für REST API registrieren
        foreach ($this->meta_fields as $field_key => $field_config) {
            $clean_key = str_replace('_fundgrube_', '', $field_key);
            
            register_rest_field($this->post_type, $clean_key, array(
                'get_callback' => array($this, 'get_rest_meta_field'),
                'update_callback' => array($this, 'update_rest_meta_field'),
                'schema' => array(
                    'description' => $field_config['label'],
                    'type'        => $this->get_rest_field_type($field_config['type']),
                    'context'     => array('view', 'edit')
                )
            ));
        }
    }
    
    /**
     * REST API Meta-Feld abrufen
     * 
     * @param array $post_data Post-Daten
     * @param string $field_name Feld-Name
     * @param WP_REST_Request $request Request-Objekt
     * @return mixed Meta-Wert
     * @since 1.0.0
     */
    public function get_rest_meta_field($post_data, $field_name, $request) {
        $meta_key = '_fundgrube_' . $field_name;
        return get_post_meta($post_data['id'], $meta_key, true);
    }
    
    /**
     * REST API Meta-Feld aktualisieren
     * 
     * @param mixed $value Neuer Wert
     * @param WP_Post $post Post-Objekt
     * @param string $field_name Feld-Name
     * @param WP_REST_Request $request Request-Objekt
     * @return bool|WP_Error
     * @since 1.0.0
     */
    public function update_rest_meta_field($value, $post, $field_name, $request) {
        $meta_key = '_fundgrube_' . $field_name;
        
        if (!current_user_can('edit_post', $post->ID)) {
            return new WP_Error(
                'rest_cannot_edit',
                __('Sie haben nicht die Berechtigung, dieses Fundstück zu bearbeiten.', 'fundgrube'),
                array('status' => rest_authorization_required_code())
            );
        }
        
        // Meta-Wert bereinigen basierend auf Feld-Typ
        $sanitized_value = $this->sanitize_meta_field_value($value, $field_name);
        
        return update_post_meta($post->ID, $meta_key, $sanitized_value);
    }
    
    /**
     * Meta-Feldwert bereinigen
     * 
     * @param mixed $value Zu bereinigender Wert
     * @param string $field_name Feld-Name
     * @return mixed Bereinigter Wert
     * @since 1.0.0
     */
    private function sanitize_meta_field_value($value, $field_name) {
        $meta_key = '_fundgrube_' . $field_name;
        
        if (!isset($this->meta_fields[$meta_key])) {
            return sanitize_text_field($value);
        }
        
        $field_config = $this->meta_fields[$meta_key];
        
        switch ($field_config['type']) {
            case 'textarea':
                return sanitize_textarea_field($value);
            case 'date':
                // Datumsvalidierung
                if (empty($value)) {
                    return '';
                }
                $date = DateTime::createFromFormat('Y-m-d', $value);
                return $date && $date->format('Y-m-d') === $value ? $value : '';
            case 'select':
                // Prüfen ob Wert in den erlaubten Optionen ist
                if (isset($field_config['options']) && !empty($field_config['options'])) {
                    return array_key_exists($value, $field_config['options']) ? $value : '';
                }
                return sanitize_text_field($value);
            default:
                return sanitize_text_field($value);
        }
    }
    
    /**
     * REST API-Feldtyp basierend auf Meta-Feld-Typ ermitteln
     * 
     * @param string $field_type Plugin-Feld-Typ
     * @return string REST API-Feld-Typ
     * @since 1.0.0
     */
    private function get_rest_field_type($field_type) {
        switch ($field_type) {
            case 'textarea':
                return 'string';
            case 'date':
                return 'string';
            case 'select':
                return 'string';
            default:
                return 'string';
        }
    }
}
