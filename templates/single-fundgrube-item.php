<?php
/**
 * Template für einzelne Fundstück-Ansicht
 * 
 * Dieses Template zeigt ein einzelnes Fundstück mit allen Details,
 * Bildern und Meta-Informationen an.
 * 
 * @package Fundgrube
 * @version 1.0.0
 */

get_header(); ?>

<div class="fundgrube-single-container">
    <div class="fundgrube-content-wrapper">
        
        <?php while (have_posts()) : the_post(); ?>
            
            <article id="post-<?php the_ID(); ?>" <?php post_class('fundgrube-single-item'); ?>>
                
                <!-- Breadcrumb / Navigation -->
                <div class="fundgrube-breadcrumb">
                    <a href="<?php echo home_url(); ?>" class="fundgrube-home-link">
                        <span class="dashicons dashicons-admin-home"></span>
                        <?php _e('Start', 'fundgrube'); ?>
                    </a>
                    <span class="fundgrube-separator">›</span>
                    <a href="<?php echo get_post_type_archive_link('fundgrube_item'); ?>" class="fundgrube-archive-link">
                        <?php _e('Fundstücke', 'fundgrube'); ?>
                    </a>
                    <span class="fundgrube-separator">›</span>
                    <span class="fundgrube-current"><?php the_title(); ?></span>
                </div>
                
                <!-- Header mit Titel und Status -->
                <header class="fundgrube-single-header">
                    <h1 class="fundgrube-single-title"><?php the_title(); ?></h1>
                    
                    <div class="fundgrube-status-bar">
                        <?php
                        $kategorie = get_post_meta(get_the_ID(), '_fundgrube_kategorie', true);
                        $status = get_post_meta(get_the_ID(), '_fundgrube_status', true);
                        $funddatum = get_post_meta(get_the_ID(), '_fundgrube_funddatum', true);
                        
                        // Kategorie anzeigen
                        if ($kategorie) :
                            $kategorie_labels = array(
                                'verloren' => array('label' => __('Verloren', 'fundgrube'), 'class' => 'lost'),
                                'gefunden' => array('label' => __('Gefunden', 'fundgrube'), 'class' => 'found'),
                                'zurueckgegeben' => array('label' => __('Zurückgegeben', 'fundgrube'), 'class' => 'returned')
                            );
                            $kategorie_info = $kategorie_labels[$kategorie] ?? array('label' => $kategorie, 'class' => 'default');
                        ?>
                            <span class="fundgrube-kategorie-badge fundgrube-<?php echo esc_attr($kategorie_info['class']); ?>">
                                <?php echo esc_html($kategorie_info['label']); ?>
                            </span>
                        <?php endif; ?>
                        
                        <!-- Status anzeigen -->
                        <?php if ($status) :
                            $status_labels = array(
                                'verfuegbar' => array('label' => __('Verfügbar', 'fundgrube'), 'class' => 'available'),
                                'reserviert' => array('label' => __('Reserviert', 'fundgrube'), 'class' => 'reserved'),
                                'abgeholt' => array('label' => __('Abgeholt', 'fundgrube'), 'class' => 'collected'),
                                'entsorgt' => array('label' => __('Entsorgt', 'fundgrube'), 'class' => 'disposed')
                            );
                            $status_info = $status_labels[$status] ?? array('label' => $status, 'class' => 'default');
                        ?>
                            <span class="fundgrube-status-badge fundgrube-<?php echo esc_attr($status_info['class']); ?>">
                                <?php echo esc_html($status_info['label']); ?>
                            </span>
                        <?php endif; ?>
                        
                        <!-- Funddatum -->
                        <?php if ($funddatum) : ?>
                            <span class="fundgrube-date">
                                <span class="dashicons dashicons-calendar-alt"></span>
                                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($funddatum))); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </header>
                
                <!-- Hauptcontent -->
                <div class="fundgrube-main-content">
                    
                    <!-- Bildergalerie -->
                    <div class="fundgrube-images-section">
                        <?php
                        // Prüfen ob Galerie aktiviert ist
                        $options = get_option('fundgrube_options', array());
                        $gallery_enabled = $options['enable_gallery'] ?? true;
                        
                        if ($gallery_enabled) {
                            // Gallery direkt im Template rendern
                            $has_featured_image = has_post_thumbnail();
                            $gallery_images = get_post_meta(get_the_ID(), '_fundgrube_gallery', true);
                            $gallery_images = is_array($gallery_images) ? $gallery_images : array();
                            
                            // Alle Bilder sammeln
                            $all_images = array();
                            if ($has_featured_image) {
                                $all_images[] = get_post_thumbnail_id();
                            }
                            $all_images = array_merge($all_images, $gallery_images);
                            
                            if (!empty($all_images)) :
                            ?>
                                <div class="fundgrube-single-gallery">
                                    <div class="fundgrube-main-image-container">
                                        <?php 
                                        $main_image_id = $all_images[0];
                                        $main_image_url = wp_get_attachment_image_url($main_image_id, 'large');
                                        $main_image_alt = get_post_meta($main_image_id, '_wp_attachment_image_alt', true);
                                        ?>
                                        <img src="<?php echo esc_url($main_image_url); ?>" 
                                             alt="<?php echo esc_attr($main_image_alt); ?>"
                                             class="fundgrube-single-main-image fundgrube-lightbox-trigger"
                                             data-image-index="0">
                                    </div>
                                    
                                    <?php if (count($all_images) > 1) : ?>
                                        <div class="fundgrube-thumbnail-gallery">
                                            <?php foreach ($all_images as $index => $image_id) : 
                                                $thumb_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                                                $thumb_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                                            ?>
                                                <div class="fundgrube-thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                                    <img src="<?php echo esc_url($thumb_url); ?>" 
                                                         alt="<?php echo esc_attr($thumb_alt); ?>"
                                                         class="fundgrube-thumbnail fundgrube-lightbox-trigger"
                                                         data-image-index="<?php echo $index; ?>">
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <!-- Hidden data für Lightbox -->
                                        <script type="application/json" id="fundgrube-gallery-data">
                                        <?php 
                                        $gallery_data = array();
                                        foreach ($all_images as $image_id) {
                                            $gallery_data[] = array(
                                                'full' => wp_get_attachment_image_url($image_id, 'full'),
                                                'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true),
                                                'title' => get_the_title($image_id)
                                            );
                                        }
                                        echo json_encode($gallery_data);
                                        ?>
                                        </script>
                                    <?php endif; ?>
                                </div>
                            <?php
                            endif;
                        } elseif (has_post_thumbnail()) {
                            ?>
                            <div class="fundgrube-single-featured-image">
                                <?php the_post_thumbnail('large', array('class' => 'fundgrube-main-image')); ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    
                    <!-- Beschreibung -->
                    <div class="fundgrube-description-section">
                        <?php if (get_the_content()) : ?>
                            <h2 class="fundgrube-section-title"><?php _e('Beschreibung', 'fundgrube'); ?></h2>
                            <div class="fundgrube-description-content">
                                <?php the_content(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                </div>
                
                <!-- Sidebar mit Details -->
                <aside class="fundgrube-details-sidebar">
                    
                    <!-- Details-Box -->
                    <div class="fundgrube-details-box">
                        <h3 class="fundgrube-box-title"><?php _e('Details', 'fundgrube'); ?></h3>
                        
                        <div class="fundgrube-details-list">
                            <?php
                            $meta_fields = array(
                                '_fundgrube_fundort' => array(
                                    'label' => __('Fundort', 'fundgrube'),
                                    'icon' => 'location-alt'
                                ),
                                '_fundgrube_farbe' => array(
                                    'label' => __('Farbe', 'fundgrube'),
                                    'icon' => 'art'
                                ),
                                '_fundgrube_groesse' => array(
                                    'label' => __('Größe', 'fundgrube'),
                                    'icon' => 'editor-expand'
                                ),
                                '_fundgrube_zustand' => array(
                                    'label' => __('Zustand', 'fundgrube'),
                                    'icon' => 'star-filled'
                                ),
                                '_fundgrube_beschreibung' => array(
                                    'label' => __('Weitere Details', 'fundgrube'),
                                    'icon' => 'text'
                                ),
                                '_fundgrube_kontakt_person' => array(
                                    'label' => __('Kontaktperson', 'fundgrube'),
                                    'icon' => 'businessman'
                                )
                            );
                            
                            foreach ($meta_fields as $meta_key => $field_info) :
                                $value = get_post_meta(get_the_ID(), $meta_key, true);
                                if (!empty($value)) :
                            ?>
                                <div class="fundgrube-detail-item">
                                    <div class="fundgrube-detail-label">
                                        <span class="dashicons dashicons-<?php echo esc_attr($field_info['icon']); ?>"></span>
                                        <?php echo esc_html($field_info['label']); ?>
                                    </div>
                                    <div class="fundgrube-detail-value">
                                        <?php 
                                        if ($meta_key === '_fundgrube_beschreibung') {
                                            echo wp_kses_post(wpautop($value));
                                        } elseif ($meta_key === '_fundgrube_zustand') {
                                            $zustand_labels = array(
                                                'neu' => __('Neu/Sehr gut', 'fundgrube'),
                                                'gut' => __('Gut', 'fundgrube'),
                                                'gebraucht' => __('Gebraucht', 'fundgrube'),
                                                'beschaedigt' => __('Beschädigt', 'fundgrube')
                                            );
                                            echo esc_html($zustand_labels[$value] ?? $value);
                                        } else {
                                            echo esc_html($value);
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    </div>
                    
                    <!-- Kontakt-Box -->
                    <?php 
                    $kontakt_info = $options['contact_info'] ?? '';
                    if (!empty($kontakt_info)) :
                    ?>
                        <div class="fundgrube-contact-box">
                            <h3 class="fundgrube-box-title">
                                <span class="dashicons dashicons-email-alt"></span>
                                <?php _e('Kontakt', 'fundgrube'); ?>
                            </h3>
                            <div class="fundgrube-contact-content">
                                <?php echo wp_kses_post(wpautop($kontakt_info)); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Social Sharing -->
                    <div class="fundgrube-sharing-box">
                        <h3 class="fundgrube-box-title">
                            <span class="dashicons dashicons-share"></span>
                            <?php _e('Teilen', 'fundgrube'); ?>
                        </h3>
                        <div class="fundgrube-share-buttons">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" 
                               class="fundgrube-share-btn fundgrube-share-facebook" 
                               target="_blank" rel="noopener"
                               title="<?php esc_attr_e('Auf Facebook teilen', 'fundgrube'); ?>">
                                <span class="dashicons dashicons-facebook"></span>
                                Facebook
                            </a>
                            
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" 
                               class="fundgrube-share-btn fundgrube-share-twitter" 
                               target="_blank" rel="noopener"
                               title="<?php esc_attr_e('Auf Twitter teilen', 'fundgrube'); ?>">
                                <span class="dashicons dashicons-twitter"></span>
                                Twitter
                            </a>
                            
                            <a href="https://wa.me/?text=<?php echo urlencode(get_the_title() . ' - ' . get_permalink()); ?>" 
                               class="fundgrube-share-btn fundgrube-share-whatsapp" 
                               target="_blank" rel="noopener"
                               title="<?php esc_attr_e('Per WhatsApp teilen', 'fundgrube'); ?>">
                                <span class="dashicons dashicons-smartphone"></span>
                                WhatsApp
                            </a>
                            
                            <button class="fundgrube-share-btn fundgrube-share-copy" 
                                    data-url="<?php echo esc_attr(get_permalink()); ?>"
                                    title="<?php esc_attr_e('Link kopieren', 'fundgrube'); ?>">
                                <span class="dashicons dashicons-admin-links"></span>
                                <?php _e('Link kopieren', 'fundgrube'); ?>
                            </button>
                        </div>
                    </div>
                    
                </aside>
                
                <!-- Navigation zu anderen Fundstücken -->
                <nav class="fundgrube-post-navigation">
                    <?php
                    $prev_post = get_previous_post(false, '', 'fundgrube_item');
                    $next_post = get_next_post(false, '', 'fundgrube_item');
                    ?>
                    
                    <div class="fundgrube-nav-links">
                        <?php if ($prev_post) : ?>
                            <div class="fundgrube-nav-previous">
                                <a href="<?php echo get_permalink($prev_post->ID); ?>" rel="prev">
                                    <span class="fundgrube-nav-direction">
                                        <span class="dashicons dashicons-arrow-left-alt"></span>
                                        <?php _e('Vorheriges', 'fundgrube'); ?>
                                    </span>
                                    <span class="fundgrube-nav-title"><?php echo get_the_title($prev_post->ID); ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="fundgrube-nav-center">
                            <a href="<?php echo get_post_type_archive_link('fundgrube_item'); ?>" class="fundgrube-back-to-overview">
                                <span class="dashicons dashicons-grid-view"></span>
                                <?php _e('Alle Fundstücke', 'fundgrube'); ?>
                            </a>
                        </div>
                        
                        <?php if ($next_post) : ?>
                            <div class="fundgrube-nav-next">
                                <a href="<?php echo get_permalink($next_post->ID); ?>" rel="next">
                                    <span class="fundgrube-nav-direction">
                                        <?php _e('Nächstes', 'fundgrube'); ?>
                                        <span class="dashicons dashicons-arrow-right-alt"></span>
                                    </span>
                                    <span class="fundgrube-nav-title"><?php echo get_the_title($next_post->ID); ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </nav>
                
            </article>
            
        <?php endwhile; ?>
        
    </div>
</div>

<!-- Lightbox für Galerie -->
<div id="fundgrube-lightbox" class="fundgrube-lightbox" style="display: none;">
    <div class="fundgrube-lightbox-backdrop"></div>
    <div class="fundgrube-lightbox-content">
        <button class="fundgrube-lightbox-close" aria-label="<?php esc_attr_e('Schließen', 'fundgrube'); ?>">
            <span class="dashicons dashicons-no-alt"></span>
        </button>
        <div class="fundgrube-lightbox-image-container">
            <img class="fundgrube-lightbox-image" src="" alt="" />
        </div>
        <div class="fundgrube-lightbox-navigation">
            <button class="fundgrube-lightbox-prev" aria-label="<?php esc_attr_e('Vorheriges Bild', 'fundgrube'); ?>">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
            </button>
            <button class="fundgrube-lightbox-next" aria-label="<?php esc_attr_e('Nächstes Bild', 'fundgrube'); ?>">
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </button>
        </div>
        <div class="fundgrube-lightbox-caption"></div>
    </div>
</div>

<?php get_footer(); ?>
