<?php
/**
 * Template für Weiterleitungs-Disclaimer-Seite
 * 
 * Rechtlich konforme Weiterleitung zu externen Websites
 * gemäß DSGVO und Telemediengesetz (TMG)
 *
 * @package Fundgrube
 * @subpackage Templates  
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$target_url = isset($_GET['url']) ? esc_url_raw(rawurldecode($_GET['url'])) : '';
$service = isset($_GET['service']) ? sanitize_text_field($_GET['service']) : '';
$item_title = isset($_GET['title']) ? sanitize_text_field(rawurldecode($_GET['title'])) : '';

// Validate URL
if (empty($target_url) || !filter_var($target_url, FILTER_VALIDATE_URL)) {
    wp_redirect(home_url());
    exit;
}

// Service-spezifische Informationen
$services = array(
    'facebook' => array(
        'name' => 'Facebook',
        'icon' => 'dashicons-facebook-alt',
        'color' => '#1877f2',
        'privacy_url' => 'https://www.facebook.com/privacy/explanation'
    ),
    'twitter' => array(
        'name' => 'Twitter/X',
        'icon' => 'dashicons-twitter',
        'color' => '#1da1f2',
        'privacy_url' => 'https://twitter.com/privacy'
    ),
    'whatsapp' => array(
        'name' => 'WhatsApp',
        'icon' => 'dashicons-whatsapp',
        'color' => '#25d366',
        'privacy_url' => 'https://www.whatsapp.com/legal/privacy-policy'
    )
);

$service_info = $services[$service] ?? array(
    'name' => __('Externe Website', 'fundgrube'),
    'icon' => 'dashicons-external',
    'color' => '#666666',
    'privacy_url' => ''
);

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php printf(__('Weiterleitung zu %s', 'fundgrube'), $service_info['name']); ?> | <?php bloginfo('name'); ?></title>
    <meta name="robots" content="noindex, nofollow">
    <?php wp_head(); ?>
</head>

<body class="fundgrube-redirect-page">
    <div class="fundgrube-redirect-container">
        
        <!-- Header Section -->
        <header class="fundgrube-redirect-header">
            <div class="fundgrube-site-branding">
                <a href="<?php echo home_url(); ?>" class="fundgrube-home-link">
                    <span class="dashicons dashicons-arrow-left-alt"></span>
                    <?php bloginfo('name'); ?>
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="fundgrube-redirect-content">
            <div class="fundgrube-redirect-card">
                
                <!-- Service Icon -->
                <div class="fundgrube-service-icon" style="color: <?php echo esc_attr($service_info['color']); ?>">
                    <span class="dashicons <?php echo esc_attr($service_info['icon']); ?>"></span>
                </div>
                
                <!-- Headline -->
                <h1 class="fundgrube-redirect-title">
                    <?php printf(__('Weiterleitung zu %s', 'fundgrube'), $service_info['name']); ?>
                </h1>
                
                <!-- Item Info -->
                <?php if (!empty($item_title)) : ?>
                <div class="fundgrube-shared-item">
                    <p class="fundgrube-item-label"><?php _e('Geteiltes Fundstück:', 'fundgrube'); ?></p>
                    <p class="fundgrube-item-name"><?php echo esc_html($item_title); ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Legal Notice -->
                <div class="fundgrube-legal-notice">
                    <div class="fundgrube-warning-icon">
                        <span class="dashicons dashicons-info"></span>
                    </div>
                    <div class="fundgrube-legal-text">
                        <h2><?php _e('Hinweis zur Weiterleitung', 'fundgrube'); ?></h2>
                        <p>
                            <?php printf(
                                __('Sie werden in <strong id="countdown">%d</strong> Sekunden zu %s weitergeleitet. Diese externe Website unterliegt nicht unserer Datenschutzerklärung.', 'fundgrube'),
                                5,
                                $service_info['name']
                            ); ?>
                        </p>
                        <p class="fundgrube-external-url">
                            <strong><?php _e('Ziel-URL:', 'fundgrube'); ?></strong><br>
                            <code><?php echo esc_html($target_url); ?></code>
                        </p>
                    </div>
                </div>
                
                <!-- Privacy Information -->
                <?php if (!empty($service_info['privacy_url'])) : ?>
                <div class="fundgrube-privacy-info">
                    <span class="dashicons dashicons-privacy"></span>
                    <a href="<?php echo esc_url($service_info['privacy_url']); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer">
                        <?php printf(__('Datenschutzerklärung von %s', 'fundgrube'), $service_info['name']); ?>
                        <span class="dashicons dashicons-external"></span>
                    </a>
                </div>
                <?php endif; ?>
                
                <!-- Action Buttons -->
                <div class="fundgrube-redirect-actions">
                    <button type="button" 
                            id="redirect-now" 
                            class="fundgrube-btn fundgrube-btn-primary"
                            style="background-color: <?php echo esc_attr($service_info['color']); ?>">
                        <span class="dashicons <?php echo esc_attr($service_info['icon']); ?>"></span>
                        <?php printf(__('Jetzt zu %s', 'fundgrube'), $service_info['name']); ?>
                    </button>
                    
                    <a href="<?php echo home_url(); ?>" 
                       class="fundgrube-btn fundgrube-btn-secondary">
                        <span class="dashicons dashicons-arrow-left-alt"></span>
                        <?php _e('Abbrechen', 'fundgrube'); ?>
                    </a>
                </div>
                
                <!-- Progress Bar -->
                <div class="fundgrube-progress-container">
                    <div class="fundgrube-progress-bar" id="progress-bar"></div>
                    <div class="fundgrube-progress-text">
                        <?php _e('Automatische Weiterleitung läuft...', 'fundgrube'); ?>
                    </div>
                </div>
                
            </div>
            
            <!-- Legal Footer -->
            <footer class="fundgrube-redirect-footer">
                <div class="fundgrube-legal-links">
                    <a href="<?php echo get_privacy_policy_url(); ?>">
                        <?php _e('Datenschutz', 'fundgrube'); ?>
                    </a>
                    <span class="separator">|</span>
                    <a href="<?php echo home_url('/impressum/'); ?>">
                        <?php _e('Impressum', 'fundgrube'); ?>
                    </a>
                    <span class="separator">|</span>
                    <a href="<?php echo home_url(); ?>">
                        <?php _e('Startseite', 'fundgrube'); ?>
                    </a>
                </div>
                <p class="fundgrube-disclaimer">
                    <?php _e('Diese Weiterleitungsseite dient dem Schutz Ihrer Daten gemäß DSGVO und Telemediengesetz.', 'fundgrube'); ?>
                </p>
            </footer>
            
        </main>
    </div>

    <!-- JavaScript -->
    <script>
    (function() {
        'use strict';
        
        const targetUrl = <?php echo json_encode($target_url); ?>;
        const countdownElement = document.getElementById('countdown');
        const progressBar = document.getElementById('progress-bar');
        const redirectButton = document.getElementById('redirect-now');
        
        let timeLeft = 5;
        let countdownInterval;
        let progressInterval;
        
        // Countdown-Timer starten
        function startCountdown() {
            countdownInterval = setInterval(function() {
                timeLeft--;
                countdownElement.textContent = timeLeft;
                
                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    clearInterval(progressInterval);
                    redirect();
                }
            }, 1000);
        }
        
        // Progress Bar Animation
        function startProgress() {
            let progress = 0;
            progressInterval = setInterval(function() {
                progress += 2; // 100% in 5 Sekunden
                progressBar.style.width = progress + '%';
                
                if (progress >= 100) {
                    clearInterval(progressInterval);
                }
            }, 100);
        }
        
        // Weiterleitung durchführen
        function redirect() {
            window.location.href = targetUrl;
        }
        
        // Event Listeners
        redirectButton.addEventListener('click', function(e) {
            e.preventDefault();
            clearInterval(countdownInterval);
            clearInterval(progressInterval);
            redirect();
        });
        
        // Escape-Taste zum Abbrechen
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                clearInterval(countdownInterval);
                clearInterval(progressInterval);
                window.history.back();
            }
        });
        
        // Timer und Progress starten
        startCountdown();
        startProgress();
        
        // Accessibility: Screen Reader Updates
        const announcer = document.createElement('div');
        announcer.setAttribute('aria-live', 'polite');
        announcer.setAttribute('aria-atomic', 'true');
        announcer.style.position = 'absolute';
        announcer.style.left = '-10000px';
        document.body.appendChild(announcer);
        
        // Screen Reader Ankündigungen
        setTimeout(function() {
            announcer.textContent = '<?php printf(__("Weiterleitung zu %s in 5 Sekunden. Drücken Sie Escape zum Abbrechen.", "fundgrube"), $service_info["name"]); ?>';
        }, 500);
        
    })();
    </script>

    <?php wp_footer(); ?>
</body>
</html>
