<?php
/**
 * Template für Fundstück-Archiv
 * 
 * Zeigt eine Übersicht aller Fundstücke mit Filteroptionen
 * und Suchfunktionalität.
 * 
 * @package Fundgrube
 * @version 1.0.0
 */

get_header(); ?>

<div class="fundgrube-archive-container">
    
    <!-- Archive Header -->
    <header class="fundgrube-archive-header">
        <div class="fundgrube-archive-title-section">
            <h1 class="fundgrube-archive-title">
                <span class="dashicons dashicons-search"></span>
                <?php _e('Fundstücke', 'fundgrube'); ?>
            </h1>
            <div class="fundgrube-archive-description">
                <?php _e('Durchsuchen Sie unsere Sammlung von Fundstücken oder nutzen Sie die Filter unten.', 'fundgrube'); ?>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="fundgrube-archive-stats">
            <?php
            $total_posts = wp_count_posts('fundgrube_item');
            $published_count = $total_posts->publish ?? 0;
            ?>
            <div class="fundgrube-stat-item">
                <span class="fundgrube-stat-number"><?php echo number_format_i18n($published_count); ?></span>
                <span class="fundgrube-stat-label"><?php _e('Fundstücke insgesamt', 'fundgrube'); ?></span>
            </div>
        </div>
    </header>
    
    <!-- Search and Filter Bar -->
    <div class="fundgrube-archive-filters">
        <div class="fundgrube-filter-wrapper">
            
            <!-- Search Form -->
            <form method="get" class="fundgrube-search-form" role="search">
                <div class="fundgrube-search-input-wrapper">
                    <input type="text" 
                           name="s" 
                           value="<?php echo esc_attr(get_search_query()); ?>"
                           placeholder="<?php esc_attr_e('Fundstück suchen...', 'fundgrube'); ?>"
                           class="fundgrube-search-input">
                    <input type="hidden" name="post_type" value="fundgrube_item">
                    <button type="submit" class="fundgrube-search-button">
                        <span class="dashicons dashicons-search"></span>
                        <span class="screen-reader-text"><?php _e('Suchen', 'fundgrube'); ?></span>
                    </button>
                </div>
            </form>
            
            <!-- Category Filter -->
            <div class="fundgrube-filter-categories">
                <?php
                $current_category = isset($_GET['kategorie']) ? sanitize_text_field($_GET['kategorie']) : '';
                $categories = array(
                    '' => __('Alle Kategorien', 'fundgrube'),
                    'verloren' => __('Verloren', 'fundgrube'),
                    'gefunden' => __('Gefunden', 'fundgrube'),
                    'zurueckgegeben' => __('Zurückgegeben', 'fundgrube')
                );
                ?>
                <select name="kategorie" id="fundgrube-category-filter" onchange="fundgrubeFilterChange()">
                    <?php foreach ($categories as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current_category, $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Status Filter -->
            <div class="fundgrube-filter-status">
                <?php
                $current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
                $statuses = array(
                    '' => __('Alle Status', 'fundgrube'),
                    'verfuegbar' => __('Verfügbar', 'fundgrube'),
                    'reserviert' => __('Reserviert', 'fundgrube'),
                    'abgeholt' => __('Abgeholt', 'fundgrube')
                );
                ?>
                <select name="status" id="fundgrube-status-filter" onchange="fundgrubeFilterChange()">
                    <?php foreach ($statuses as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current_status, $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Sort Options -->
            <div class="fundgrube-filter-sort">
                <?php
                $current_orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'date';
                $sort_options = array(
                    'date' => __('Neueste zuerst', 'fundgrube'),
                    'title' => __('A-Z', 'fundgrube'),
                    'modified' => __('Zuletzt bearbeitet', 'fundgrube')
                );
                ?>
                <select name="orderby" id="fundgrube-sort-filter" onchange="fundgrubeFilterChange()">
                    <?php foreach ($sort_options as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current_orderby, $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Clear Filters -->
            <?php if (!empty(array_filter(array($current_category, $current_status, get_search_query())))) : ?>
                <div class="fundgrube-filter-clear">
                    <a href="<?php echo get_post_type_archive_link('fundgrube_item'); ?>" class="fundgrube-clear-filters">
                        <span class="dashicons dashicons-dismiss"></span>
                        <?php _e('Filter zurücksetzen', 'fundgrube'); ?>
                    </a>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
    
    <!-- Results Info -->
    <?php if (have_posts()) : ?>
        <div class="fundgrube-results-info">
            <?php
            global $wp_query;
            $total_results = $wp_query->found_posts;
            $paged = get_query_var('paged') ? get_query_var('paged') : 1;
            $posts_per_page = get_query_var('posts_per_page');
            $start_result = (($paged - 1) * $posts_per_page) + 1;
            $end_result = min($start_result + $posts_per_page - 1, $total_results);
            
            if (get_search_query()) {
                printf(
                    _n(
                        '%1$d Ergebnis für "%2$s" gefunden',
                        '%1$d Ergebnisse für "%2$s" gefunden',
                        $total_results,
                        'fundgrube'
                    ),
                    number_format_i18n($total_results),
                    esc_html(get_search_query())
                );
            } else {
                printf(
                    _n(
                        '%1$d Fundstück (%2$d-%3$d angezeigt)',
                        '%1$d Fundstücke (%2$d-%3$d angezeigt)',
                        $total_results,
                        'fundgrube'
                    ),
                    number_format_i18n($total_results),
                    number_format_i18n($start_result),
                    number_format_i18n($end_result)
                );
            }
            ?>
        </div>
    <?php endif; ?>
    
    <!-- Grid/List View Toggle -->
    <div class="fundgrube-view-toggle">
        <button class="fundgrube-view-btn active" data-view="grid" title="<?php esc_attr_e('Rasteransicht', 'fundgrube'); ?>">
            <span class="dashicons dashicons-grid-view"></span>
        </button>
        <button class="fundgrube-view-btn" data-view="list" title="<?php esc_attr_e('Listenansicht', 'fundgrube'); ?>">
            <span class="dashicons dashicons-list-view"></span>
        </button>
    </div>
    
    <!-- Items Grid -->
    <div class="fundgrube-archive-content">
        
        <?php if (have_posts()) : ?>
            
            <div class="fundgrube-items-grid" data-view="grid">
                
                <?php while (have_posts()) : the_post(); ?>
                    
                    <article id="post-<?php the_ID(); ?>" <?php post_class('fundgrube-archive-item'); ?>>
                        
                        <!-- Image Section -->
                        <div class="fundgrube-item-image">
                            <?php
                            // Gallery-enabled check
                            $options = get_option('fundgrube_options', array());
                            $gallery_enabled = $options['enable_gallery'] ?? true;
                            
                            if ($gallery_enabled) {
                                // Render mini gallery für Archive
                                $has_featured = has_post_thumbnail();
                                $gallery_images = get_post_meta(get_the_ID(), '_fundgrube_gallery', true);
                                $gallery_images = is_array($gallery_images) ? $gallery_images : array();
                                
                                $all_images = array();
                                if ($has_featured) $all_images[] = get_post_thumbnail_id();
                                $all_images = array_merge($all_images, $gallery_images);
                                
                                if (!empty($all_images)) {
                                    $main_image = wp_get_attachment_image_url($all_images[0], 'medium');
                                    $alt_text = get_post_meta($all_images[0], '_wp_attachment_image_alt', true);
                                    ?>
                                    <a href="<?php the_permalink(); ?>" class="fundgrube-item-image-link">
                                        <img src="<?php echo esc_url($main_image); ?>" 
                                             alt="<?php echo esc_attr($alt_text); ?>"
                                             class="fundgrube-item-img">
                                        <?php if (count($all_images) > 1) : ?>
                                            <div class="fundgrube-image-count-badge">
                                                <span class="dashicons dashicons-camera"></span>
                                                <?php echo count($all_images); ?>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                    <?php
                                } else {
                                    echo '<div class="fundgrube-no-image"><span class="dashicons dashicons-format-image"></span></div>';
                                }
                            } elseif (has_post_thumbnail()) {
                                ?>
                                <a href="<?php the_permalink(); ?>" class="fundgrube-item-image-link">
                                    <?php the_post_thumbnail('medium', array('class' => 'fundgrube-item-img')); ?>
                                </a>
                                <?php
                            } else {
                                echo '<div class="fundgrube-no-image"><span class="dashicons dashicons-format-image"></span></div>';
                            }
                            ?>
                        </div>
                        
                        <!-- Content Section -->
                        <div class="fundgrube-item-content">
                            
                            <!-- Title -->
                            <h2 class="fundgrube-item-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            
                            <!-- Meta Info -->
                            <div class="fundgrube-item-meta">
                                <?php
                                $kategorie = get_post_meta(get_the_ID(), '_fundgrube_kategorie', true);
                                $status = get_post_meta(get_the_ID(), '_fundgrube_status', true);
                                $fundort = get_post_meta(get_the_ID(), '_fundgrube_fundort', true);
                                $funddatum = get_post_meta(get_the_ID(), '_fundgrube_funddatum', true);
                                ?>
                                
                                <?php if ($kategorie) : ?>
                                    <span class="fundgrube-meta-kategorie fundgrube-kategorie-<?php echo esc_attr($kategorie); ?>">
                                        <?php 
                                        $kategorie_labels = array(
                                            'verloren' => __('Verloren', 'fundgrube'),
                                            'gefunden' => __('Gefunden', 'fundgrube'),
                                            'zurueckgegeben' => __('Zurückgegeben', 'fundgrube')
                                        );
                                        echo esc_html($kategorie_labels[$kategorie] ?? $kategorie);
                                        ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($status) : ?>
                                    <span class="fundgrube-meta-status fundgrube-status-<?php echo esc_attr($status); ?>">
                                        <?php 
                                        $status_labels = array(
                                            'verfuegbar' => __('Verfügbar', 'fundgrube'),
                                            'reserviert' => __('Reserviert', 'fundgrube'),
                                            'abgeholt' => __('Abgeholt', 'fundgrube'),
                                            'entsorgt' => __('Entsorgt', 'fundgrube')
                                        );
                                        echo esc_html($status_labels[$status] ?? $status);
                                        ?>
                                    </span>
                                <?php endif; ?>
                                
                            </div>
                            
                            <!-- Additional Details -->
                            <div class="fundgrube-item-details">
                                <?php if ($fundort) : ?>
                                    <div class="fundgrube-meta-item">
                                        <span class="dashicons dashicons-location-alt"></span>
                                        <span class="fundgrube-meta-label"><?php _e('Fundort:', 'fundgrube'); ?></span>
                                        <span class="fundgrube-meta-value"><?php echo esc_html($fundort); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($funddatum) : ?>
                                    <div class="fundgrube-meta-item">
                                        <span class="dashicons dashicons-calendar-alt"></span>
                                        <span class="fundgrube-meta-label"><?php _e('Funddatum:', 'fundgrube'); ?></span>
                                        <span class="fundgrube-meta-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($funddatum))); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Excerpt -->
                            <?php if (has_excerpt() || get_the_content()) : ?>
                                <div class="fundgrube-item-excerpt">
                                    <?php 
                                    if (has_excerpt()) {
                                        the_excerpt();
                                    } else {
                                        echo wp_trim_words(get_the_content(), 20, '...');
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Read More -->
                            <div class="fundgrube-item-actions">
                                <a href="<?php the_permalink(); ?>" class="fundgrube-read-more">
                                    <?php _e('Details anzeigen', 'fundgrube'); ?>
                                    <span class="dashicons dashicons-arrow-right-alt"></span>
                                </a>
                            </div>
                            
                        </div>
                        
                    </article>
                    
                <?php endwhile; ?>
                
            </div>
            
            <!-- Pagination -->
            <div class="fundgrube-pagination">
                <?php
                echo paginate_links(array(
                    'total' => $wp_query->max_num_pages,
                    'current' => max(1, get_query_var('paged')),
                    'prev_text' => '<span class="dashicons dashicons-arrow-left-alt"></span>' . __('Vorherige', 'fundgrube'),
                    'next_text' => __('Nächste', 'fundgrube') . '<span class="dashicons dashicons-arrow-right-alt"></span>',
                    'type' => 'list'
                ));
                ?>
            </div>
            
        <?php else : ?>
            
            <!-- No Results -->
            <div class="fundgrube-no-results">
                <div class="fundgrube-no-results-icon">
                    <span class="dashicons dashicons-search"></span>
                </div>
                <h2 class="fundgrube-no-results-title">
                    <?php if (get_search_query()) : ?>
                        <?php _e('Keine Ergebnisse gefunden', 'fundgrube'); ?>
                    <?php else : ?>
                        <?php _e('Noch keine Fundstücke vorhanden', 'fundgrube'); ?>
                    <?php endif; ?>
                </h2>
                <p class="fundgrube-no-results-text">
                    <?php if (get_search_query()) : ?>
                        <?php printf(__('Für "%s" wurden keine passenden Fundstücke gefunden. Versuchen Sie es mit anderen Suchbegriffen oder entfernen Sie die Filter.', 'fundgrube'), esc_html(get_search_query())); ?>
                    <?php else : ?>
                        <?php _e('Es wurden noch keine Fundstücke angelegt. Schauen Sie später noch einmal vorbei.', 'fundgrube'); ?>
                    <?php endif; ?>
                </p>
                
                <?php if (!empty(array_filter(array($current_category, $current_status, get_search_query())))) : ?>
                    <div class="fundgrube-no-results-actions">
                        <a href="<?php echo get_post_type_archive_link('fundgrube_item'); ?>" class="fundgrube-clear-all-filters">
                            <?php _e('Alle Filter zurücksetzen', 'fundgrube'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
        <?php endif; ?>
        
    </div>
    
</div>

<script>
// Filter ändern und Form absenden
function fundgrubeFilterChange() {
    const url = new URL(window.location);
    const categorySelect = document.getElementById('fundgrube-category-filter');
    const statusSelect = document.getElementById('fundgrube-status-filter');
    const sortSelect = document.getElementById('fundgrube-sort-filter');
    
    // Parameter setzen oder entfernen
    if (categorySelect.value) {
        url.searchParams.set('kategorie', categorySelect.value);
    } else {
        url.searchParams.delete('kategorie');
    }
    
    if (statusSelect.value) {
        url.searchParams.set('status', statusSelect.value);
    } else {
        url.searchParams.delete('status');
    }
    
    if (sortSelect.value && sortSelect.value !== 'date') {
        url.searchParams.set('orderby', sortSelect.value);
    } else {
        url.searchParams.delete('orderby');
    }
    
    // Seite zurücksetzen bei Filter-Änderung
    url.searchParams.delete('paged');
    
    window.location = url.toString();
}

// View Toggle
document.addEventListener('DOMContentLoaded', function() {
    const viewButtons = document.querySelectorAll('.fundgrube-view-btn');
    const itemsContainer = document.querySelector('.fundgrube-items-grid');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const view = this.dataset.view;
            
            // Button states
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Container view
            itemsContainer.setAttribute('data-view', view);
            
            // Save preference
            localStorage.setItem('fundgrube-view-preference', view);
        });
    });
    
    // Load saved preference
    const savedView = localStorage.getItem('fundgrube-view-preference');
    if (savedView) {
        const button = document.querySelector(`[data-view="${savedView}"]`);
        if (button) button.click();
    }
});
</script>

<?php get_footer(); ?>
