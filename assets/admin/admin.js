/**
 * Product Reviews Importer - Admin Scripts
 *
 * @package Product_Reviews_Importer
 * @since   1.0.0
 */

(function($) {
    'use strict';

    /**
     * Tab navigation handler
     */
    const TabNavigation = {
        init: function() {
            this.bindEvents();
            this.activateFromHash();
            this.handleHashChange();
        },

        bindEvents: function() {
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                const tabName = $(this).data('tab');
                window.location.hash = tabName;
                TabNavigation.activateTab(tabName);
            });
        },

        activateFromHash: function() {
            const hash = window.location.hash.substring(1);
            const tabName = hash || 'import';
            this.activateTab(tabName);
        },

        handleHashChange: function() {
            $(window).on('hashchange', function() {
                const hash = window.location.hash.substring(1);
                const tabName = hash || 'import';
                TabNavigation.activateTab(tabName);
            });
        },

        activateTab: function(tabName) {
            // Update nav tabs
            $('.nav-tab').removeClass('nav-tab-active');
            $('.nav-tab[data-tab="' + tabName + '"]').addClass('nav-tab-active');

            // Show/hide panels
            $('.tab-panel').hide().removeClass('active');
            $('#' + tabName + '-panel').show().addClass('active');
        }
    };

    /**
     * CSV Import handler
     */
    const CSVImport = {
        uploadId: null,
        totalRows: 0,
        processed: 0,

        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            $('#pri-upload-form').on('submit', this.handleUpload.bind(this));
            $('#pri-start-import').on('click', this.startImport.bind(this));
        },

        handleUpload: function(e) {
            e.preventDefault();

            const fileInput = $('#pri-csv-file')[0];
            const file = fileInput.files[0];

            if (!file) {
                this.showMessage('Please select a CSV file.', 'error');
                return;
            }

            // Validate file type
            if (!file.name.toLowerCase().endsWith('.csv')) {
                this.showMessage('Please select a valid CSV file.', 'error');
                return;
            }

            // Show loading state
            $('#pri-validation-results').show();
            $('#pri-validation-messages').html('<p>Uploading and validating CSV file...</p>');
            $('#pri-upload-btn').prop('disabled', true);

            // Prepare form data
            const formData = new FormData();
            formData.append('action', 'pri_upload_csv');
            formData.append('nonce', productReviewsImporter.uploadNonce);
            formData.append('csv_file', file);

            // Upload via AJAX
            $.ajax({
                url: productReviewsImporter.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    if (response.success) {
                        this.uploadId = response.data.uploadId;
                        this.totalRows = response.data.totalRows;
                        this.processed = 0;

                        this.showMessage(
                            `File uploaded successfully! Found ${this.totalRows} reviews to import.`,
                            'success'
                        );
                        $('#pri-import-controls').show();
                        $('#pri-upload-form').hide();
                    } else {
                        this.showMessage(response.message, 'error');
                        $('#pri-upload-btn').prop('disabled', false);
                    }
                },
                error: (xhr) => {
                    this.showMessage('Upload failed. Please try again.', 'error');
                    $('#pri-upload-btn').prop('disabled', false);
                }
            });
        },

        startImport: function() {
            if (!this.uploadId) {
                this.showMessage('No file uploaded. Please upload a CSV file first.', 'error');
                return;
            }

            // Disable and hide import controls
            const $importBtn = $('#pri-start-import');
            $importBtn.prop('disabled', true).text('Importing...');
            $('#pri-import-controls').hide();
            $('#pri-validation-results').hide();

            // Show progress section
            $('#pri-progress-section').show();
            $('#pri-results-section').hide();

            // Reset progress
            this.processed = 0;
            this.updateProgress(0, 'Starting import...');

            // Start batch processing
            this.processBatch(0);
        },

        processBatch: function(offset) {
            const ajaxData = {
                action: 'pri_import_batch',
                nonce: productReviewsImporter.importNonce,
                uploadId: this.uploadId,
                offset: offset
            };
            
            $.ajax({
				url: productReviewsImporter.ajaxUrl,
				type: 'POST',
				data: ajaxData,
				success: (response) => {
					if (!response.success) {
						this.showResults(false, response.message);
						return;
					}

					if (response.data.complete) {
						// Import complete
						this.showResults(true, response.message, response.data);
						return;
					}

					// Update progress
					this.processed = response.data.processed;
					const percentage = Math.round((this.processed / this.totalRows) * 100);
					this.updateProgress(percentage, response.message);

					// Process next batch
					this.processBatch(response.data.processed);
				},
				error: (xhr, status, error) => {
					console.error('Import batch failed:', {
						status: status,
						error: error,
						response: xhr.responseText
					});
					let errorMsg = 'Import failed. ';
					if (xhr.responseText) {
						try {
							const errorData = JSON.parse(xhr.responseText);
							errorMsg += errorData.message || error;
						} catch (e) {
							errorMsg += error;
						}
					} else {
						errorMsg += error;
					}
					this.showResults(false, errorMsg);
				}
			});
		},

		updateProgress: function(percentage, message) {
			$('#pri-progress-bar-fill').css('width', percentage + '%');
			$('#pri-progress-text').text(percentage + '%');
			$('#pri-progress-message').text(message);
		},

		showResults: function(success, message, data) {
			$('#pri-progress-section').hide();
			$('#pri-results-section').show();
			
			const resultClass = success ? 'notice-success' : 'notice-error';
			let resultHTML = `<div class=\"notice ${resultClass}\"><p>${message}</p></div>`;
			
			// Add detailed error list if errors occurred
			if (data && data.errorList && data.errorList.length > 0) {
				resultHTML += '<div class=\"pri-error-details\">';
				resultHTML += `<h4>Error Details (${data.errorList.length} errors)</h4>`;
				resultHTML += '<ul class=\"pri-error-list\">';
				
				data.errorList.forEach((error) => {
					resultHTML += `<li><strong>Row ${error.row}:</strong> ${error.message}</li>`;
				});
				
				resultHTML += '</ul></div>';
			}
			
			$('#pri-results-content').html(resultHTML);

			// Show reset button
			$('#pri-reset-import').show().on('click', () => {
				location.reload();
			});
		},

		showMessage: function(message, type) {
			const noticeClass = type === 'error' ? 'notice-error' : 'notice-success';
			const html = `<div class=\"notice ${noticeClass}\"><p>${message}</p></div>`;
			$('#pri-validation-messages').html(html);
		}
	};

	/**
	 * Initialize on document ready
	 */
	$(document).ready(function() {
		TabNavigation.init();
		CSVImport.init();
	});

})(jQuery);