/**
 * Admin-JavaScript für das Fundgrube-Plugin
 * 
 * @package Fundgrube
 * @version 1.0.0
 */

(function($) {
    'use strict';
    
    /**
     * Initialisiert Admin-Funktionen beim DOM-Ready
     */
    $(document).ready(function() {
        initializeFundgrubeAdmin();
    });
    
    /**
     * Hauptinitialisierung für Admin-Bereich
     * 
     * @since 1.0.0
     */
    function initializeFundgrubeAdmin() {
        initializeMetaBoxes();
        initializeStatusUpdates();
        initializeDashboardWidgets();
        initializeFormValidation();
        initializeImageHandling();
        initializeGalleryFunctions();
    }
    
    /**
     * Meta-Box Funktionalitäten initialisieren
     * 
     * @since 1.0.0
     */
    function initializeMetaBoxes() {
        // Auto-Datum setzen wenn leer
        var funddatumField = $('#_fundgrube_funddatum');
        if (funddatumField.length && !funddatumField.val()) {
            var today = new Date();
            var dd = String(today.getDate()).padStart(2, '0');
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var yyyy = today.getFullYear();
            funddatumField.val(yyyy + '-' + mm + '-' + dd);
        }
        
        // Kategorie-abhängige Feldanzeige
        var kategorieField = $('#_fundgrube_kategorie');
        var statusField = $('#_fundgrube_status').closest('tr');
        
        function toggleStatusField() {
            var kategorie = kategorieField.val();
            if (kategorie === 'verloren') {
                statusField.hide();
            } else {
                statusField.show();
            }
        }
        
        kategorieField.on('change', toggleStatusField);
        toggleStatusField(); // Initial ausführen
        
        // Zeichen-Zähler für Textarea-Felder
        $('#_fundgrube_beschreibung').on('input', function() {
            var maxLength = 500;
            var currentLength = $(this).val().length;
            var remaining = maxLength - currentLength;
            
            var counter = $(this).siblings('.character-counter');
            if (counter.length === 0) {
                counter = $('<div class="character-counter"></div>');
                $(this).after(counter);
            }
            
            counter.text(remaining + ' Zeichen verbleibend');
            
            if (remaining < 50) {
                counter.addClass('warning');
            } else {
                counter.removeClass('warning');
            }
        });
    }
    
    /**
     * Status-Update-Funktionalitäten
     * 
     * @since 1.0.0
     */
    function initializeStatusUpdates() {
        // Quick-Status-Update in der Übersichtsliste
        $('.fundgrube-status').on('click', function(e) {
            e.preventDefault();
            
            var $this = $(this);
            var postId = $this.closest('tr').find('.check-column input').val();
            
            if (!postId) {
                return;
            }
            
            // Modal für Status-Änderung anzeigen
            showStatusModal(postId, $this);
        });
        
        // Bulk-Actions für Status-Updates
        if ($('#bulk-action-selector-top').length) {
            $('<option value="fundgrube_mark_found">Als gefunden markieren</option>').appendTo('#bulk-action-selector-top, #bulk-action-selector-bottom');
            $('<option value="fundgrube_mark_returned">Als zurückgegeben markieren</option>').appendTo('#bulk-action-selector-top, #bulk-action-selector-bottom');
        }
    }
    
    /**
     * Status-Modal anzeigen
     * 
     * @param {number} postId Post-ID
     * @param {jQuery} statusElement Status-Element
     * @since 1.0.0
     */
    function showStatusModal(postId, statusElement) {
        var modal = $('<div class="fundgrube-modal-overlay"></div>');
        var modalContent = $('<div class="fundgrube-modal"></div>');
        
        modalContent.html(`
            <div class="fundgrube-modal-header">
                <h3>Status ändern</h3>
                <button class="fundgrube-modal-close">&times;</button>
            </div>
            <div class="fundgrube-modal-body">
                <p>Neuen Status für das Fundstück auswählen:</p>
                <select id="new-status" class="widefat">
                    <option value="verfuegbar">Verfügbar</option>
                    <option value="reserviert">Reserviert</option>
                    <option value="abgeholt">Abgeholt</option>
                    <option value="entsorgt">Entsorgt</option>
                </select>
            </div>
            <div class="fundgrube-modal-footer">
                <button class="button" id="cancel-status">Abbrechen</button>
                <button class="button-primary" id="update-status">Status aktualisieren</button>
            </div>
        `);
        
        modal.append(modalContent);
        $('body').append(modal);
        
        // Event-Handler für Modal
        modal.on('click', '.fundgrube-modal-close, #cancel-status', function() {
            modal.remove();
        });
        
        modal.on('click', '#update-status', function() {
            var newStatus = $('#new-status').val();
            updateItemStatus(postId, newStatus, statusElement);
            modal.remove();
        });
        
        // ESC-Taste zum Schließen
        $(document).on('keyup.fundgrube-modal', function(e) {
            if (e.keyCode === 27) {
                modal.remove();
                $(document).off('keyup.fundgrube-modal');
            }
        });
    }
    
    /**
     * Item-Status via AJAX aktualisieren
     * 
     * @param {number} postId Post-ID
     * @param {string} newStatus Neuer Status
     * @param {jQuery} statusElement Status-Element zum Aktualisieren
     * @since 1.0.0
     */
    function updateItemStatus(postId, newStatus, statusElement) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'fundgrube_update_status',
                post_id: postId,
                status: newStatus,
                nonce: fundgrube_admin.nonce
            },
            beforeSend: function() {
                statusElement.addClass('updating-message');
            },
            success: function(response) {
                if (response.success) {
                    statusElement
                        .removeClass('updating-message')
                        .removeClass(function(index, className) {
                            return (className.match(/(^|\s)fundgrube-status-\S+/g) || []).join(' ');
                        })
                        .addClass('fundgrube-status-' + newStatus)
                        .text(response.data.status_label);
                    
                    showNotice('Status erfolgreich aktualisiert', 'success');
                } else {
                    showNotice('Fehler beim Aktualisieren des Status: ' + response.data, 'error');
                }
            },
            error: function() {
                statusElement.removeClass('updating-message');
                showNotice('AJAX-Fehler beim Aktualisieren des Status', 'error');
            }
        });
    }
    
    /**
     * Dashboard-Widget-Funktionalitäten
     * 
     * @since 1.0.0
     */
    function initializeDashboardWidgets() {
        // Statistiken automatisch aktualisieren
        setInterval(updateDashboardStats, 300000); // Alle 5 Minuten
        
        // Quick-Actions mit Keyboard-Shortcuts
        $(document).on('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.which) {
                    case 78: // Ctrl+N - Neues Fundstück
                        e.preventDefault();
                        window.location.href = $('#wp-admin-bar-new-fundgrube_item a').attr('href');
                        break;
                    case 70: // Ctrl+F - Fundstücke anzeigen
                        if (e.shiftKey) {
                            e.preventDefault();
                            window.location.href = $('a[href*="edit.php?post_type=fundgrube_item"]').first().attr('href');
                        }
                        break;
                }
            }
        });
    }
    
    /**
     * Dashboard-Statistiken aktualisieren
     * 
     * @since 1.0.0
     */
    function updateDashboardStats() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'fundgrube_get_stats',
                nonce: fundgrube_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.stat-number').text(response.data.total_items);
                }
            }
        });
    }
    
    /**
     * Formular-Validierung initialisieren
     * 
     * @since 1.0.0
     */
    function initializeFormValidation() {
        // Validierung beim Speichern von Fundstücken
        $('#post').on('submit', function(e) {
            var errors = [];
            
            // Titel prüfen
            var title = $('#title').val().trim();
            if (!title) {
                errors.push('Bitte geben Sie einen Titel für das Fundstück ein.');
            }
            
            // Kategorie prüfen
            var kategorie = $('#_fundgrube_kategorie').val();
            if (!kategorie) {
                errors.push('Bitte wählen Sie eine Kategorie aus.');
            }
            
            // Fundort prüfen
            var fundort = $('#_fundgrube_fundort').val().trim();
            if (!fundort) {
                errors.push('Bitte geben Sie einen Fundort an.');
            }
            
            // Fehler anzeigen
            if (errors.length > 0) {
                e.preventDefault();
                showValidationErrors(errors);
            }
        });
        
        // Echtzeit-Validierung für wichtige Felder
        $('#_fundgrube_fundort').on('blur', function() {
            var value = $(this).val().trim();
            var errorElement = $(this).siblings('.field-error');
            
            if (!value) {
                if (errorElement.length === 0) {
                    $('<div class="field-error">Fundort ist erforderlich</div>').insertAfter($(this));
                }
                $(this).addClass('error');
            } else {
                errorElement.remove();
                $(this).removeClass('error');
            }
        });
    }
    
    /**
     * Validierungsfehler anzeigen
     * 
     * @param {Array} errors Array von Fehlermeldungen
     * @since 1.0.0
     */
    function showValidationErrors(errors) {
        var errorList = '<ul>';
        errors.forEach(function(error) {
            errorList += '<li>' + error + '</li>';
        });
        errorList += '</ul>';
        
        var errorNotice = $('<div class="notice notice-error is-dismissible"><p><strong>Bitte korrigieren Sie die folgenden Fehler:</strong></p>' + errorList + '</div>');
        
        $('.wrap h1').after(errorNotice);
        
        // Scroll zum ersten Fehler
        $('html, body').animate({
            scrollTop: errorNotice.offset().top - 100
        }, 500);
    }
    
    /**
     * Bild-Handling initialisieren
     * 
     * @since 1.0.0
     */
    function initializeImageHandling() {
        // Featured Image Preview verbessern
        $(document).on('click', '#set-post-thumbnail', function() {
            setTimeout(function() {
                // Bild-Upload-Modal mit Fundgrube-spezifischen Einstellungen
                if (typeof wp !== 'undefined' && wp.media) {
                    var frame = wp.media.frame;
                    if (frame) {
                        frame.on('select', function() {
                            showNotice('Hauptbild erfolgreich ausgewählt', 'success');
                        });
                    }
                }
            }, 100);
        });
        
        // Drag & Drop für Bilder
        var dropZone = $('#postimagediv');
        if (dropZone.length) {
            dropZone.on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('drag-over');
            });
            
            dropZone.on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('drag-over');
            });
            
            dropZone.on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('drag-over');
                
                // Trigger nativen Upload
                if (e.originalEvent.dataTransfer.files.length > 0) {
                    showNotice('Drag & Drop Upload wird verarbeitet...', 'info');
                }
            });
        }
    }
    
    /**
     * Admin-Notice anzeigen
     * 
     * @param {string} message Nachricht
     * @param {string} type Notice-Typ (success, error, warning, info)
     * @since 1.0.0
     */
    function showNotice(message, type) {
        type = type || 'info';
        
        var notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap .wp-header-end').after(notice);
        
        // Auto-Hide nach 5 Sekunden
        setTimeout(function() {
            notice.fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    /**
     * Gallery-Funktionen initialisieren
     * 
     * @since 1.0.0
     */
    function initializeGalleryFunctions() {
        // WordPress Media Uploader für Gallery
        var fundgrubeGalleryFrame;
        var currentFieldId;
        
        // Add Images Button
        $(document).on('click', '.fundgrube-add-images', function(e) {
            e.preventDefault();
            currentFieldId = $(this).data('field-id');
            
            // Media Frame erstellen wenn noch nicht vorhanden
            if (fundgrubeGalleryFrame) {
                fundgrubeGalleryFrame.open();
                return;
            }
            
            fundgrubeGalleryFrame = wp.media({
                title: 'Bilder für Galerie auswählen',
                button: {
                    text: 'Bilder hinzufügen'
                },
                multiple: true,
                library: {
                    type: 'image'
                }
            });
            
            // Wenn Bilder ausgewählt werden
            fundgrubeGalleryFrame.on('select', function() {
                var attachments = fundgrubeGalleryFrame.state().get('selection').toJSON();
                var existingIds = $('#' + currentFieldId).val().split(',').filter(Boolean);
                
                attachments.forEach(function(attachment) {
                    if (existingIds.indexOf(attachment.id.toString()) === -1) {
                        existingIds.push(attachment.id.toString());
                        addImageToPreview(currentFieldId, attachment);
                    }
                });
                
                $('#' + currentFieldId).val(existingIds.join(','));
                updateClearButton(currentFieldId);
            });
            
            fundgrubeGalleryFrame.open();
        });
        
        // Remove einzelnes Bild
        $(document).on('click', '.fundgrube-remove-image', function(e) {
            e.preventDefault();
            var $item = $(this).closest('.fundgrube-gallery-item');
            var attachmentId = $item.data('attachment-id');
            var fieldId = $item.closest('.fundgrube-gallery-field').find('input[type="hidden"]').attr('id');
            
            var currentIds = $('#' + fieldId).val().split(',').filter(Boolean);
            var newIds = currentIds.filter(function(id) { return id !== attachmentId.toString(); });
            
            $('#' + fieldId).val(newIds.join(','));
            $item.remove();
            updateClearButton(fieldId);
        });
        
        // Clear All Button
        $(document).on('click', '.fundgrube-clear-gallery', function(e) {
            e.preventDefault();
            var fieldId = $(this).data('field-id');
            $('#' + fieldId).val('');
            $('#' + fieldId + '_preview').empty();
            updateClearButton(fieldId);
        });
        
        function addImageToPreview(fieldId, attachment) {
            var previewHtml = '<div class="fundgrube-gallery-item" data-attachment-id="' + attachment.id + '">' +
                '<img src="' + (attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url) + '" alt="' + attachment.alt + '">' +
                '<div class="fundgrube-gallery-item-actions">' +
                '<button type="button" class="fundgrube-remove-image" title="Bild entfernen">×</button>' +
                '</div>' +
                '</div>';
            
            $('#' + fieldId + '_preview').append(previewHtml);
        }
        
        function updateClearButton(fieldId) {
            var $container = $('#' + fieldId + '_container');
            var hasImages = $('#' + fieldId).val().length > 0;
            
            if (hasImages) {
                if ($container.find('.fundgrube-clear-gallery').length === 0) {
                    $container.find('.fundgrube-add-images').after(' <button type="button" class="button fundgrube-clear-gallery" data-field-id="' + fieldId + '">Alle entfernen</button>');
                }
            } else {
                $container.find('.fundgrube-clear-gallery').remove();
            }
        }
    }
    
    /**
     * Utility-Funktionen für Export/Import
     * 
     * @since 1.0.0
     */
    window.FundgrubeAdmin = {
        /**
         * Fundstücke als CSV exportieren
         */
        exportCSV: function() {
            window.location.href = ajaxurl + '?action=fundgrube_export_csv&nonce=' + fundgrube_admin.nonce;
        },
        
        /**
         * Statistiken manuell aktualisieren
         */
        refreshStats: function() {
            updateDashboardStats();
            showNotice('Statistiken werden aktualisiert...', 'info');
        }
    };

})(jQuery);
