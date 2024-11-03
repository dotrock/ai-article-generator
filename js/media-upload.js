jQuery(function($) {
    // Cache DOM elements
    const $uploadButton = $('#royyanweb_ai_upload_image_button');
    const $imageIdInput = $('#royyanweb_ai_featured_image_id');
    const $imagePreview = $('#royyanweb_ai_featured_image_preview');
    
    // Initialize media uploader immediately
    const mediaUploader = wp.media({
        title: 'Select Featured Image',
        button: {
            text: 'Use this image'
        },
        multiple: false,
        library: {
            type: 'image'
        }
    });

    // Handle image selection
    mediaUploader.on('select', function() {
        const attachment = mediaUploader.state().get('selection').first().toJSON();
        
        // Update hidden input and preview
        updateImageSelection(attachment);
    });

    // Handle button click
    $uploadButton.on('click', function(e) {
        e.preventDefault();
        mediaUploader.open();
    });

    // Handle remove image click
    $(document).on('click', '.remove-image', function(e) {
        e.preventDefault();
        clearImageSelection();
    });

    // Function to update image selection
    function updateImageSelection(attachment) {
        if (!attachment || !attachment.id) return;

        $imageIdInput.val(attachment.id);
        
        const previewHtml = `
            <div class="image-preview-wrapper">
                <img src="${attachment.url}" 
                     alt="Featured image preview" 
                     style="max-width: 300px; height: auto; margin-bottom: 10px; display: block;">
                <button type="button" class="button button-secondary remove-image">
                    Remove Image
                </button>
            </div>
        `;
        
        $imagePreview.html(previewHtml);

        // Trigger change event for any listeners
        $imageIdInput.trigger('change');
    }

    // Function to clear image selection
    function clearImageSelection() {
        $imageIdInput.val('').trigger('change');
        $imagePreview.empty();
    }

    // If there's an existing image ID, load its preview
    const existingImageId = $imageIdInput.val();
    if (existingImageId) {
        wp.media.attachment(existingImageId).fetch().then(function() {
            const attachment = wp.media.attachment(existingImageId);
            updateImageSelection(attachment.attributes);
        });
    }
});