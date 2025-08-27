/**
 * HTH Sample Plugin - Admin JavaScript
 * 
 * JavaScript functionality for the WordPress admin area.
 */

(function($) {
    'use strict';

    // Admin namespace
    window.HTHAdmin = window.HTHAdmin || {};

    /**
     * Initialize admin functionality
     */
    HTHAdmin.init = function() {
        console.log('HTH Sample Plugin admin initialized');
        
        HTHAdmin.initDataTable();
        HTHAdmin.initForms();
        HTHAdmin.initMetaBoxes();
        HTHAdmin.initColorPicker();
        HTHAdmin.initMediaUploader();
    };

    /**
     * Initialize data table functionality
     */
    HTHAdmin.initDataTable = function() {
        // Handle delete actions
        $('.hth-data-table').on('click', '.delete-item', function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to delete this item?')) {
                return;
            }
            
            var $link = $(this);
            var $row = $link.closest('tr');
            var itemId = $link.data('id');
            
            $row.addClass('hth-loading');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hth_delete_item',
                    item_id: itemId,
                    nonce: $('#hth_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(function() {
                            $row.remove();
                        });
                        HTHAdmin.showNotice('success', 'Item deleted successfully.');
                    } else {
                        HTHAdmin.showNotice('error', response.data.message || 'Error deleting item.');
                    }
                },
                error: function() {
                    HTHAdmin.showNotice('error', 'Network error occurred.');
                },
                complete: function() {
                    $row.removeClass('hth-loading');
                }
            });
        });

        // Handle bulk actions
        $('#bulk-action-selector-top, #bulk-action-selector-bottom').on('change', function() {
            var action = $(this).val();
            var $checkboxes = $('.hth-data-table input[type="checkbox"]:checked');
            
            if (action === 'delete' && $checkboxes.length > 0) {
                if (confirm('Are you sure you want to delete the selected items?')) {
                    HTHAdmin.bulkDelete($checkboxes);
                }
            }
        });
    };

    /**
     * Handle bulk delete operations
     */
    HTHAdmin.bulkDelete = function($checkboxes) {
        var ids = [];
        $checkboxes.each(function() {
            ids.push($(this).val());
        });
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hth_bulk_delete',
                item_ids: ids,
                nonce: $('#hth_nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    $checkboxes.closest('tr').fadeOut(function() {
                        $(this).remove();
                    });
                    HTHAdmin.showNotice('success', response.data.message);
                } else {
                    HTHAdmin.showNotice('error', response.data.message || 'Error deleting items.');
                }
            },
            error: function() {
                HTHAdmin.showNotice('error', 'Network error occurred.');
            }
        });
    };

    /**
     * Initialize form functionality
     */
    HTHAdmin.initForms = function() {
        // Handle AJAX form submissions
        $('.hth-admin-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submitBtn = $form.find('[type="submit"]');
            var originalText = $submitBtn.val();
            
            // Show loading state
            $submitBtn.prop('disabled', true).val('Saving...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: $form.serialize(),
                success: function(response) {
                    if (response.success) {
                        HTHAdmin.showNotice('success', response.data.message || 'Saved successfully!');
                        
                        // Redirect if specified
                        if (response.data.redirect) {
                            window.location.href = response.data.redirect;
                        }
                    } else {
                        HTHAdmin.showNotice('error', response.data.message || 'An error occurred.');
                    }
                },
                error: function() {
                    HTHAdmin.showNotice('error', 'Network error occurred.');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).val(originalText);
                }
            });
        });

        // Handle form validation
        $('.hth-admin-form input[required], .hth-admin-form textarea[required]').on('blur', function() {
            var $field = $(this);
            var $fieldContainer = $field.closest('.hth-form-field');
            
            if (!$field.val().trim()) {
                $fieldContainer.addClass('error');
                if (!$fieldContainer.find('.error-message').length) {
                    $fieldContainer.append('<span class="error-message">This field is required.</span>');
                }
            } else {
                $fieldContainer.removeClass('error').find('.error-message').remove();
            }
        });
    };

    /**
     * Initialize meta box functionality
     */
    HTHAdmin.initMetaBoxes = function() {
        // Handle repeatable fields
        $('.hth-meta-box').on('click', '.add-field', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $container = $button.siblings('.repeatable-fields');
            var $template = $container.find('.field-template');
            var $newField = $template.clone().removeClass('field-template').show();
            
            // Update field names and IDs
            var index = $container.find('.field-row:not(.field-template)').length;
            $newField.find('input, textarea, select').each(function() {
                var $input = $(this);
                var name = $input.attr('name');
                var id = $input.attr('id');
                
                if (name) {
                    $input.attr('name', name.replace('[0]', '[' + index + ']'));
                }
                if (id) {
                    $input.attr('id', id.replace('_0', '_' + index));
                }
            });
            
            $container.append($newField);
        });

        // Handle field removal
        $('.hth-meta-box').on('click', '.remove-field', function(e) {
            e.preventDefault();
            $(this).closest('.field-row').remove();
        });

        // Handle image upload in meta boxes
        $('.hth-meta-box').on('click', '.upload-image-button', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $input = $button.siblings('input[type="hidden"]');
            var $preview = $button.siblings('.image-preview');
            
            var frame = wp.media({
                title: 'Select Image',
                button: { text: 'Use This Image' },
                multiple: false
            });
            
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $input.val(attachment.id);
                $preview.html('<img src="' + attachment.sizes.thumbnail.url + '" alt="">');
                $button.text('Change Image');
            });
            
            frame.open();
        });
    };

    /**
     * Initialize color picker
     */
    HTHAdmin.initColorPicker = function() {
        if ($.fn.wpColorPicker) {
            $('.hth-color-field').wpColorPicker({
                change: function(event, ui) {
                    // Handle color change
                    $(this).trigger('hth:color:changed', [ui.color.toString()]);
                }
            });
        }
    };

    /**
     * Initialize media uploader
     */
    HTHAdmin.initMediaUploader = function() {
        $('.hth-upload-button').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $input = $button.siblings('input[type="hidden"]');
            var $preview = $button.siblings('.upload-preview');
            var mediaType = $button.data('media-type') || 'image';
            
            var frame = wp.media({
                title: 'Select ' + mediaType.charAt(0).toUpperCase() + mediaType.slice(1),
                button: { text: 'Use This ' + mediaType.charAt(0).toUpperCase() + mediaType.slice(1) },
                multiple: false,
                library: { type: mediaType }
            });
            
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $input.val(attachment.id);
                
                if (mediaType === 'image') {
                    var imageUrl = attachment.sizes && attachment.sizes.thumbnail 
                        ? attachment.sizes.thumbnail.url 
                        : attachment.url;
                    $preview.html('<img src="' + imageUrl + '" alt="" style="max-width: 150px;">');
                } else {
                    $preview.html('<span>' + attachment.filename + '</span>');
                }
                
                $button.text('Change ' + mediaType.charAt(0).toUpperCase() + mediaType.slice(1));
            });
            
            frame.open();
        });
        
        // Handle remove media
        $('.hth-remove-media').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $input = $button.siblings('input[type="hidden"]');
            var $preview = $button.siblings('.upload-preview');
            var $uploadBtn = $button.siblings('.hth-upload-button');
            
            $input.val('');
            $preview.empty();
            $uploadBtn.text($uploadBtn.data('original-text') || 'Upload');
        });
    };

    /**
     * Show admin notice
     */
    HTHAdmin.showNotice = function(type, message) {
        var $notice = $('<div class="notice notice-' + type + ' is-dismissible hth-admin-notice"><p>' + message + '</p></div>');
        
        $('.wrap h1').after($notice);
        
        // Auto dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
                $notice.remove();
            });
        }, 5000);
    };

    /**
     * Handle AJAX search functionality
     */
    HTHAdmin.initSearch = function() {
        var $searchInput = $('#hth-search-input');
        var $searchResults = $('#hth-search-results');
        
        if ($searchInput.length) {
            $searchInput.on('input', HTHAdmin.utils.debounce(function() {
                var query = $(this).val().trim();
                
                if (query.length < 3) {
                    $searchResults.empty();
                    return;
                }
                
                $searchResults.html('<div class="hth-loading">Searching...</div>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'GET',
                    data: {
                        action: 'hth_search',
                        query: query,
                        nonce: $('#hth_search_nonce').val()
                    },
                    success: function(response) {
                        if (response.success && response.data.results) {
                            var html = '';
                            response.data.results.forEach(function(item) {
                                html += '<div class="search-result-item">';
                                html += '<h4>' + item.title + '</h4>';
                                html += '<p>' + item.excerpt + '</p>';
                                html += '</div>';
                            });
                            $searchResults.html(html);
                        } else {
                            $searchResults.html('<div class="no-results">No results found.</div>');
                        }
                    },
                    error: function() {
                        $searchResults.html('<div class="error">Search error occurred.</div>');
                    }
                });
            }, 300));
        }
    };

    /**
     * Utility functions
     */
    HTHAdmin.utils = {
        debounce: function(func, wait) {
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
    };

    // Initialize when document is ready
    $(document).ready(function() {
        HTHAdmin.init();
        HTHAdmin.initSearch();
    });

})(jQuery);
