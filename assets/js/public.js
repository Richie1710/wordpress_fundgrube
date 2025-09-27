/**
 * Frontend-JavaScript für das Fundgrube-Plugin
 * 
 * @package Fundgrube
 * @version 1.0.0
 */

(function($) {
    'use strict';
    
    /**
     * Initialisiert Frontend-Funktionen beim DOM-Ready
     */
    $(document).ready(function() {
        initializeFundgrubePublic();
    });
    
    /**
     * Hauptinitialisierung für Frontend
     * 
     * @since 1.0.0
     */
    function initializeFundgrubePublic() {
        initializeSearch();
        initializeFilters();
        initializeImageGallery();
        initializeLazyLoading();
        initializeContactForm();
        initializeSocialSharing();
    }
    
    /**
     * Suchfunktionalitäten initialisieren
     * 
     * @since 1.0.0
     */
    function initializeSearch() {
        var searchForm = $('.fundgrube-suchform');
        var searchField = $('.fundgrube-suchfeld');
        
        // Auto-Complete für Suchbegriffe
        if (searchField.length) {
            searchField.on('input', debounce(function() {
                var query = $(this).val();
                if (query.length >= 3) {
                    getSuggestions(query);
                }
            }, 300));
        }
        
        // Live-Suche ohne Seitenreload
        searchForm.on('submit', function(e) {
            if ($(this).hasClass('live-search-enabled')) {
                e.preventDefault();
                performLiveSearch(searchField.val());
            }
        });
        
        // Erweiterte Suchoptionen Toggle
        $('.fundgrube-erweiterte-suche-toggle').on('click', function(e) {
            e.preventDefault();
            $('.fundgrube-erweiterte-optionen').slideToggle();
            $(this).text($(this).text() === 'Erweiterte Suche' ? 'Einfache Suche' : 'Erweiterte Suche');
        });
    }
    
    /**
     * Suchvorschläge via AJAX abrufen
     * 
     * @param {string} query Suchbegriff
     * @since 1.0.0
     */
    function getSuggestions(query) {
        $.ajax({
            url: fundgrube_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'fundgrube_search_suggestions',
                query: query,
                nonce: fundgrube_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    showSuggestions(response.data);
                } else {
                    hideSuggestions();
                }
            }
        });
    }
    
    /**
     * Live-Suche durchführen
     * 
     * @param {string} query Suchbegriff
     * @since 1.0.0
     */
    function performLiveSearch(query) {
        var resultsContainer = $('.fundgrube-liste');
        
        $.ajax({
            url: fundgrube_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'fundgrube_live_search',
                query: query,
                nonce: fundgrube_ajax.nonce
            },
            beforeSend: function() {
                resultsContainer.html('<div class="fundgrube-loading">Suche läuft...</div>');
            },
            success: function(response) {
                if (response.success) {
                    resultsContainer.html(response.data.html);
                    initializeLazyLoading(); // Lazy loading für neue Bilder
                } else {
                    resultsContainer.html('<div class="fundgrube-keine-items">Keine Ergebnisse gefunden.</div>');
                }
            },
            error: function() {
                resultsContainer.html('<div class="fundgrube-fehler">Fehler bei der Suche. Bitte versuchen Sie es erneut.</div>');
            }
        });
    }
    
    /**
     * Filter-Funktionalitäten initialisieren
     * 
     * @since 1.0.0
     */
    function initializeFilters() {
        var filterForm = $('.fundgrube-filter-form');
        
        if (filterForm.length) {
            // Filter-Änderungen überwachen
            filterForm.on('change', 'select, input[type="checkbox"]', function() {
                if (filterForm.hasClass('auto-submit')) {
                    filterForm.submit();
                }
            });
            
            // Filter zurücksetzen
            $('.fundgrube-filter-reset').on('click', function(e) {
                e.preventDefault();
                filterForm[0].reset();
                if (filterForm.hasClass('auto-submit')) {
                    filterForm.submit();
                }
            });
        }
        
        // Sortierung
        $('.fundgrube-sortierung').on('change', function() {
            var sortBy = $(this).val();
            var currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('orderby', sortBy);
            window.location.href = currentUrl.toString();
        });
    }
    
    /**
     * Bildergalerie initialisieren
     * 
     * @since 1.0.0
     */
    function initializeImageGallery() {
        var galleryImages = $('.fundgrube-galerie img');
        
        if (galleryImages.length) {
            // Lightbox-Funktionalität
            galleryImages.on('click', function(e) {
                e.preventDefault();
                openLightbox($(this));
            });
            
            // Keyboard-Navigation für Lightbox
            $(document).on('keydown', function(e) {
                if ($('.fundgrube-lightbox').is(':visible')) {
                    switch(e.which) {
                        case 37: // Links
                            navigateLightbox('prev');
                            break;
                        case 39: // Rechts
                            navigateLightbox('next');
                            break;
                        case 27: // ESC
                            closeLightbox();
                            break;
                    }
                }
            });
        }
        
        // Bild-Zoom bei Hover
        $('.fundgrube-item-bild img').on('mouseenter', function() {
            $(this).addClass('zoomed');
        }).on('mouseleave', function() {
            $(this).removeClass('zoomed');
        });
    }
    
    /**
     * Lightbox öffnen
     * 
     * @param {jQuery} image Bild-Element
     * @since 1.0.0
     */
    function openLightbox(image) {
        var lightbox = $('.fundgrube-lightbox');
        
        if (lightbox.length === 0) {
            lightbox = $(`
                <div class="fundgrube-lightbox">
                    <div class="lightbox-content">
                        <img class="lightbox-image" src="" alt="">
                        <div class="lightbox-controls">
                            <button class="lightbox-prev">&lt;</button>
                            <button class="lightbox-next">&gt;</button>
                            <button class="lightbox-close">&times;</button>
                        </div>
                        <div class="lightbox-caption"></div>
                    </div>
                </div>
            `);
            
            $('body').append(lightbox);
            
            // Event-Handler für Lightbox-Controls
            lightbox.on('click', '.lightbox-close', closeLightbox);
            lightbox.on('click', '.lightbox-prev', function() { navigateLightbox('prev'); });
            lightbox.on('click', '.lightbox-next', function() { navigateLightbox('next'); });
            lightbox.on('click', function(e) {
                if (e.target === this) {
                    closeLightbox();
                }
            });
        }
        
        var imageSrc = image.attr('data-full-size') || image.attr('src');
        var imageAlt = image.attr('alt');
        var imageCaption = image.attr('data-caption') || image.closest('.fundgrube-item').find('.fundgrube-item-titel a').text();
        
        lightbox.find('.lightbox-image').attr('src', imageSrc).attr('alt', imageAlt);
        lightbox.find('.lightbox-caption').text(imageCaption);
        lightbox.fadeIn(300);
        
        $('body').addClass('lightbox-open');
    }
    
    /**
     * Lightbox schließen
     * 
     * @since 1.0.0
     */
    function closeLightbox() {
        $('.fundgrube-lightbox').fadeOut(300);
        $('body').removeClass('lightbox-open');
    }
    
    /**
     * Lightbox-Navigation
     * 
     * @param {string} direction Navigation-Richtung (prev/next)
     * @since 1.0.0
     */
    function navigateLightbox(direction) {
        var currentImage = $('.lightbox-image').attr('src');
        var allImages = $('.fundgrube-galerie img, .fundgrube-item-bild img');
        var currentIndex = -1;
        
        allImages.each(function(index) {
            var imageSrc = $(this).attr('data-full-size') || $(this).attr('src');
            if (imageSrc === currentImage) {
                currentIndex = index;
                return false;
            }
        });
        
        var newIndex;
        if (direction === 'prev') {
            newIndex = currentIndex > 0 ? currentIndex - 1 : allImages.length - 1;
        } else {
            newIndex = currentIndex < allImages.length - 1 ? currentIndex + 1 : 0;
        }
        
        if (newIndex !== -1) {
            openLightbox(allImages.eq(newIndex));
        }
    }
    
    /**
     * Lazy Loading für Bilder
     * 
     * @since 1.0.0
     */
    function initializeLazyLoading() {
        var lazyImages = $('.fundgrube-item-bild img[data-src]');
        
        if (lazyImages.length && 'IntersectionObserver' in window) {
            var imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        img.classList.add('loaded');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            lazyImages.each(function() {
                imageObserver.observe(this);
            });
        } else {
            // Fallback für ältere Browser
            lazyImages.each(function() {
                var img = $(this);
                img.attr('src', img.data('src')).removeClass('lazy').addClass('loaded');
            });
        }
    }
    
    /**
     * Kontaktformular initialisieren
     * 
     * @since 1.0.0
     */
    function initializeContactForm() {
        var contactForm = $('.fundgrube-kontakt-form');
        
        if (contactForm.length) {
            contactForm.on('submit', function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                var submitButton = $(this).find('[type="submit"]');
                
                $.ajax({
                    url: fundgrube_ajax.ajax_url,
                    type: 'POST',
                    data: formData + '&action=fundgrube_contact_form&nonce=' + fundgrube_ajax.nonce,
                    beforeSend: function() {
                        submitButton.prop('disabled', true).text('Wird gesendet...');
                    },
                    success: function(response) {
                        if (response.success) {
                            contactForm.html('<div class="fundgrube-success">Ihre Nachricht wurde erfolgreich gesendet!</div>');
                        } else {
                            showFormError(response.data);
                        }
                    },
                    error: function() {
                        showFormError('Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.');
                    },
                    complete: function() {
                        submitButton.prop('disabled', false).text('Senden');
                    }
                });
            });
            
            // Formular-Validierung
            contactForm.find('input[required], textarea[required]').on('blur', function() {
                validateField($(this));
            });
        }
    }
    
    /**
     * Feld validieren
     * 
     * @param {jQuery} field Feld-Element
     * @since 1.0.0
     */
    function validateField(field) {
        var value = field.val().trim();
        var errorElement = field.siblings('.field-error');
        
        if (!value && field.prop('required')) {
            if (errorElement.length === 0) {
                field.after('<div class="field-error">Dieses Feld ist erforderlich</div>');
            }
            field.addClass('error');
            return false;
        } else {
            errorElement.remove();
            field.removeClass('error');
            return true;
        }
    }
    
    /**
     * Formular-Fehler anzeigen
     * 
     * @param {string} message Fehlermeldung
     * @since 1.0.0
     */
    function showFormError(message) {
        var errorDiv = $('.fundgrube-kontakt-form .form-error');
        if (errorDiv.length === 0) {
            $('.fundgrube-kontakt-form').prepend('<div class="form-error"></div>');
            errorDiv = $('.fundgrube-kontakt-form .form-error');
        }
        errorDiv.text(message).show();
    }
    
    /**
     * Social Sharing initialisieren
     * 
     * @since 1.0.0
     */
    function initializeSocialSharing() {
        $('.fundgrube-social-share a').on('click', function(e) {
            e.preventDefault();
            
            var url = $(this).attr('href');
            var width = 600;
            var height = 400;
            var left = (screen.width / 2) - (width / 2);
            var top = (screen.height / 2) - (height / 2);
            
            window.open(url, 'share', 
                'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top + 
                ',toolbar=no,menubar=no,scrollbars=yes,resizable=yes'
            );
        });
    }
    
    /**
     * Debounce-Funktion für Performance
     * 
     * @param {Function} func Funktion
     * @param {number} wait Wartezeit in ms
     * @returns {Function} Debounced Funktion
     * @since 1.0.0
     */
    function debounce(func, wait) {
        var timeout;
        return function executedFunction() {
            var context = this;
            var args = arguments;
            var later = function() {
                timeout = null;
                func.apply(context, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    /**
     * Suchvorschläge anzeigen
     * 
     * @param {Array} suggestions Array von Vorschlägen
     * @since 1.0.0
     */
    function showSuggestions(suggestions) {
        var searchField = $('.fundgrube-suchfeld');
        var suggestionList = $('#fundgrube-suggestions');
        
        if (suggestionList.length === 0) {
            suggestionList = $('<ul id="fundgrube-suggestions" class="fundgrube-suggestions"></ul>');
            searchField.after(suggestionList);
        }
        
        suggestionList.empty();
        
        suggestions.forEach(function(suggestion) {
            var listItem = $('<li></li>').text(suggestion.title);
            listItem.on('click', function() {
                searchField.val(suggestion.title);
                hideSuggestions();
                searchField.closest('form').submit();
            });
            suggestionList.append(listItem);
        });
        
        suggestionList.show();
        
        // Vorschläge verstecken beim Klick außerhalb
        $(document).on('click.suggestions', function(e) {
            if (!$(e.target).closest('.fundgrube-suchfeld, #fundgrube-suggestions').length) {
                hideSuggestions();
            }
        });
    }
    
    /**
     * Suchvorschläge verstecken
     * 
     * @since 1.0.0
     */
    function hideSuggestions() {
        $('#fundgrube-suggestions').hide();
        $(document).off('click.suggestions');
    }
    
    /**
     * Öffentliche API für erweiterte Funktionen
     * 
     * @since 1.0.0
     */
    window.FundgrubePublic = {
        /**
         * Lightbox öffnen
         */
        openLightbox: openLightbox,
        
        /**
         * Live-Suche auslösen
         */
        performSearch: performLiveSearch,
        
        /**
         * Filter anwenden
         */
        applyFilters: function(filters) {
            // Implementation für programmatische Filter-Anwendung
            console.log('Filter angewendet:', filters);
        }
    };

})(jQuery);
