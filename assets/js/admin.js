/**
 * Herbalife Popup Admin JavaScript
 * Version: 2.0.0
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Tab navigation
    $('.herbalife-popup-settings .nav-tab').on('click', function(e) {
        e.preventDefault();
        
        const target = $(this).attr('href');
        
        // Update active tab
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Show target content
        $('.tab-content').removeClass('active');
        $(target).addClass('active');
        
        // Update URL
        window.history.replaceState({}, '', target);
    });
    
    // Handle initial tab
    const hash = window.location.hash || '#general';
    $('.nav-tab[href="' + hash + '"]').trigger('click');
    
    // Initialize color pickers
    $('.color-picker').wpColorPicker();
    
    // Trigger options visibility
    function updateTriggerOptions() {
        const trigger = $('#herbalife_popup_trigger').val();
        
        $('.trigger-option').removeClass('active');
        $('.trigger-' + trigger).addClass('active');
    }
    
    $('#herbalife_popup_trigger').on('change', updateTriggerOptions);
    updateTriggerOptions();
    
    // Preview popup
    $('#preview-popup').on('click', function(e) {
        e.preventDefault();
        
        // Create preview iframe
        const $preview = $('<div>', {
            id: 'herbalife-popup-preview',
            css: {
                position: 'fixed',
                top: 0,
                left: 0,
                right: 0,
                bottom: 0,
                background: 'rgba(0,0,0,0.8)',
                zIndex: 999999,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center'
            }
        });
        
        const $iframe = $('<iframe>', {
            src: herbalifePopupAdmin.previewUrl,
            css: {
                width: '90%',
                height: '90%',
                maxWidth: '1200px',
                maxHeight: '800px',
                border: 'none',
                borderRadius: '8px',
                boxShadow: '0 10px 50px rgba(0,0,0,0.5)'
            }
        });
        
        const $close = $('<button>', {
            text: '×',
            css: {
                position: 'absolute',
                top: '20px',
                right: '20px',
                background: '#fff',
                border: 'none',
                borderRadius: '50%',
                width: '40px',
                height: '40px',
                fontSize: '24px',
                cursor: 'pointer',
                boxShadow: '0 2px 10px rgba(0,0,0,0.2)'
            },
            click: function() {
                $preview.remove();
            }
        });
        
        $preview.append($iframe, $close);
        $('body').append($preview);
    });
});