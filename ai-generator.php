<?php

function royyanweb_ai_generator_menu()
{
    add_menu_page('AI Generator', 'AI Generator', 'manage_options', 'royyanweb_ai_generator', 'royyanweb_ai_generator_page');
}
add_action('admin_menu', 'royyanweb_ai_generator_menu');

function royyanweb_ai_enqueue_media_scripts()
{
    if (isset($_GET['page']) && $_GET['page'] === 'royyanweb_ai_generator') {
        // Enqueue WordPress media scripts
        wp_enqueue_media();

        // Enqueue custom script with proper dependencies
        wp_enqueue_script(
            'royyanweb-ai-media-upload',
            plugins_url('js/media-upload.js', __FILE__),
            array('jquery', 'media-upload', 'wp-media-utils'),
            '1.0.1',
            true
        );

        // Add some custom styling
        wp_add_inline_style('wp-admin', '
            .image-preview-wrapper {
                margin-top: 10px;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                background: #fff;
                display: inline-block;
            }
            .image-preview-wrapper img {
                box-shadow: 0 1px 4px rgba(0,0,0,0.1);
            }
            .image-preview-wrapper .remove-image {
                margin-top: 5px;
            }
        ');
    }
}
add_action('admin_enqueue_scripts', 'royyanweb_ai_enqueue_media_scripts');

// Update the HTML part in your form
function get_featured_image_field($current_image_id = '')
{
    ob_start();
?>
    <tr>
        <th scope="row">Featured Image</th>
        <td>
            <div class="featured-image-container">
                <input type="hidden"
                    name="royyanweb_ai_featured_image_id"
                    id="royyanweb_ai_featured_image_id"
                    value="<?php echo esc_attr($current_image_id); ?>" />

                <button type="button"
                    class="button"
                    id="royyanweb_ai_upload_image_button">
                    <?php echo $current_image_id ? 'Change Featured Image' : 'Upload Featured Image'; ?>
                </button>

                <div id="royyanweb_ai_featured_image_preview"></div>
            </div>
        </td>
    </tr>
<?php
    return ob_get_clean();
}

function royyanweb_ai_generator_page()
{
    $royyanweb_ai_active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'create_article';

?>
    <div class="wrap">
        <h1>AI Generator</h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=royyanweb_ai_generator&tab=create_article" class="nav-tab <?php echo $royyanweb_ai_active_tab == 'create_article' ? 'nav-tab-active' : ''; ?>">Create Article</a>
            <a href="?page=royyanweb_ai_generator&tab=settings" class="nav-tab <?php echo $royyanweb_ai_active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
        </h2>

        <?php
        if ($royyanweb_ai_active_tab == 'create_article') {
            royyanweb_ai_generator_create_article_tab();
        } elseif ($royyanweb_ai_active_tab == 'settings') {
            royyanweb_ai_generator_settings_tab();
        }
        ?>
    </div>
<?php
}

function royyanweb_ai_generator_create_article_tab()
{
    $royyanweb_ai_article_content = '';

    if (isset($_POST['royyanweb_ai_generate'])) {
        $royyanweb_ai_keyword = sanitize_text_field($_POST['royyanweb_ai_keyword']);
        $royyanweb_ai_length = intval($_POST['royyanweb_ai_length']);
        $royyanweb_ai_language = sanitize_text_field($_POST['royyanweb_ai_language']);

        $royyanweb_ai_api_provider = get_option('royyanweb_ai_article_api_provider', 'openai');
        $royyanweb_ai_api_key = get_option('royyanweb_ai_article_api_key', '');

        $royyanweb_ai_article_content = royyanweb_ai_fetch_article($royyanweb_ai_api_provider, $royyanweb_ai_api_key, $royyanweb_ai_keyword, $royyanweb_ai_length, $royyanweb_ai_language);
    }

    if (isset($_POST['royyanweb_ai_publish_article'])) {
        $royyanweb_ai_post_content = wp_kses_post($_POST['royyanweb_ai_article_content']);
        $royyanweb_ai_post_title = sanitize_text_field($_POST['royyanweb_ai_post_title']);
        $royyanweb_ai_post_category = intval($_POST['royyanweb_ai_post_category']);
        $royyanweb_ai_featured_image_id = intval($_POST['royyanweb_ai_featured_image_id']);

        $royyanweb_ai_new_post = [
            'post_title'   => $royyanweb_ai_post_title,
            'post_content' => $royyanweb_ai_post_content,
            'post_status'  => 'publish',
            'post_type'    => 'post',
            'post_category' => [$royyanweb_ai_post_category],
        ];

        $royyanweb_ai_post_id = wp_insert_post($royyanweb_ai_new_post);

        if ($royyanweb_ai_post_id && $royyanweb_ai_featured_image_id) {
            set_post_thumbnail($royyanweb_ai_post_id, $royyanweb_ai_featured_image_id);
        }

        if ($royyanweb_ai_post_id) {
            echo '<div class="updated"><p>Article published successfully! <a href="' . get_permalink($royyanweb_ai_post_id) . '" target="_blank">View Post</a></p></div>';
        } else {
            echo '<div class="error"><p>Failed to publish article.</p></div>';
        }
    }
?>

    <h2>Create Article</h2>
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th scope="row">Keyword</th>
                <td><textarea name="royyanweb_ai_keyword" class="regular-text" required></textarea></td>
            </tr>
            <tr>
                <th scope="row">Article Length</th>
                <td>
                    <select name="royyanweb_ai_length">
                        <option value="250">250 words</option>
                        <option value="500">500 words</option>
                        <option value="750">750 words</option>
                        <option value="1000">1000 words</option>
                        <option value="2000">2000 words</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">Language</th>
                <td>
                    <select name="royyanweb_ai_language">
                        <option value="id">Indonesia</option>
                        <option value="en">English</option>
                    </select>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="royyanweb_ai_generate" class="button-primary" value="Generate Article" />
        </p>
    </form>

    <?php if (!empty($royyanweb_ai_article_content)) : ?>
        <h2>Edit Generated Article</h2>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row">Post Title</th>
                    <td><input type="text" name="royyanweb_ai_post_title" value="<?php echo esc_attr($royyanweb_ai_keyword); ?>" class="regular-text" required /></td>
                </tr>
                <tr>
                    <th scope="row">Content</th>
                    <td>
                        <?php
                        wp_editor(
                            $royyanweb_ai_article_content,
                            'royyanweb_ai_article_content',
                            [
                                'textarea_rows' => 10,
                                'media_buttons' => false,
                                'teeny'         => true,
                            ]
                        );
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Category</th>
                    <td>
                        <?php
                        $royyanweb_ai_categories = get_categories(['hide_empty' => false]);
                        echo '<select name="royyanweb_ai_post_category">';
                        foreach ($royyanweb_ai_categories as $royyanweb_ai_category) {
                            echo '<option value="' . esc_attr($royyanweb_ai_category->term_id) . '">' . esc_html($royyanweb_ai_category->name) . '</option>';
                        }
                        echo '</select>';
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Featured Image</th>
                    <td>
                        <input type="hidden" name="royyanweb_ai_featured_image_id" id="royyanweb_ai_featured_image_id" value="" />
                        <button type="button" class="button" id="royyanweb_ai_upload_image_button">Upload Featured Image</button>
                        <div id="royyanweb_ai_featured_image_preview" style="margin-top: 10px;"></div>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="royyanweb_ai_publish_article" class="button-primary" value="Publish Now" />
            </p>
        </form>
    <?php endif; ?>

<?php
}

function royyanweb_ai_generator_settings_tab()
{
    if (isset($_POST['royyanweb_ai_save_settings'])) {
        update_option('royyanweb_ai_article_api_provider', sanitize_text_field($_POST['royyanweb_ai_api_provider']));
        update_option('royyanweb_ai_article_api_key', sanitize_text_field($_POST['royyanweb_ai_api_key']));
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    $royyanweb_ai_api_provider = get_option('royyanweb_ai_article_api_provider', 'openai');
    $royyanweb_ai_api_key = get_option('royyanweb_ai_article_api_key', '');
?>

    <h2>Settings</h2>
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th scope="row">API Provider</th>
                <td>
                    <select name="royyanweb_ai_api_provider">
                        <option value="openai" <?php selected($royyanweb_ai_api_provider, 'openai'); ?>>OpenAI</option>
                        <option value="gemini" <?php selected($royyanweb_ai_api_provider, 'gemini'); ?>>Gemini</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">API Key</th>
                <td><input type="text" name="royyanweb_ai_api_key" class="regular-text" value="<?php echo esc_attr($royyanweb_ai_api_key); ?>" /></td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="royyanweb_ai_save_settings" class="button-primary" value="Save Settings" />
        </p>
    </form>
<?php
}

function royyanweb_ai_fetch_article($royyanweb_ai_api_provider, $royyanweb_ai_api_key, $royyanweb_ai_keyword, $royyanweb_ai_length, $royyanweb_ai_language)
{
    // Validate API key
    if (empty($royyanweb_ai_api_key)) {
        return "Error: API key is not configured. Please check your settings.";
    }

    // Set longer timeout and larger memory limit
    ini_set('max_execution_time', 300); // 5 minutes
    ini_set('memory_limit', '256M');

    // Determine API endpoint
    $royyanweb_ai_endpoint = ($royyanweb_ai_api_provider === 'gemini')
        ? "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$royyanweb_ai_api_key}"
        : 'https://api.openai.com/v1/completions';

    // Set headers
    $royyanweb_ai_headers = [
        'Content-Type'  => 'application/json',
    ];

    // Prepare prompt based on language
    $language_text = ($royyanweb_ai_language === 'id') ? 'Indonesian' : 'English';
    $prompt = sprintf(
        "Buatlah artikel SEO yang menarik tentang {$royyanweb_ai_keyword}, dengan panjang sekitar {$royyanweb_ai_length} tidak boleh kurang. Jangan mencoba membuatnya kurang dari yang saya sebutkan. Fokus pada kata kunci utama 
        dan optimalkan untuk mesin pencari. Gunakan bahasa {$language_text} dengan gaya bahasa yang mudah dipahami dan menarik, dengan judul yang kuat dan subjudul yang informatif.
        Sertakan paragraf pembuka, penjelasan utama, tips praktis, dan paragraf penutup. Buat artikel ini relevan dengan informasi terbaru. Dan jangan lupa untuk melakukan spinning kata-kata agar tidak terdeteksi oleh AI checker, saya tidak ingin hasilnya seperti generate dari AI, saya ingin terlihat natural.",
        $royyanweb_ai_keyword,
        $royyanweb_ai_length,
        $language_text
    );

    // Prepare request body as per Gemini API's expected structure
    $royyanweb_ai_body = json_encode([
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ]
    ]);

    // Set extended timeout and additional options for wp_remote_post
    $royyanweb_ai_args = [
        'timeout' => 60, // Increase timeout to 60 seconds
        'headers' => $royyanweb_ai_headers,
        'body' => $royyanweb_ai_body,
        'sslverify' => true,
        'redirection' => 5,
        'httpversion' => '1.1',
    ];

    // Add error handling for the API call
    try {
        $royyanweb_ai_response = wp_remote_post($royyanweb_ai_endpoint, $royyanweb_ai_args);

        if (is_wp_error($royyanweb_ai_response)) {
            $error_message = $royyanweb_ai_response->get_error_message();
            error_log('AI Generator API Error: ' . $error_message);
            return "Failed to fetch article. Error: " . $error_message;
        }

        $response_code = wp_remote_retrieve_response_code($royyanweb_ai_response);
        if ($response_code !== 200) {
            $error_message = wp_remote_retrieve_response_message($royyanweb_ai_response);
            error_log('AI Generator API Error: ' . $response_code . ' - ' . $error_message);
            return "API Error: " . $response_code . " - " . $error_message;
        }

        $royyanweb_ai_body_response = json_decode(wp_remote_retrieve_body($royyanweb_ai_response), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('AI Generator JSON Error: ' . json_last_error_msg());
            return "Error parsing API response";
        }

        // Access response content
        if (isset($royyanweb_ai_body_response['candidates'][0]['content']['parts'][0]['text'])) {
            return $royyanweb_ai_body_response['candidates'][0]['content']['parts'][0]['text'] ?? "Error: Empty response from API";
        } else {
            error_log('AI Generator Unexpected Response: ' . print_r($royyanweb_ai_body_response, true));
            return "Error: Unexpected API response format";
        }
    } catch (Exception $e) {
        error_log('AI Generator Exception: ' . $e->getMessage());
        return "An unexpected error occurred: " . $e->getMessage();
    }
}

function my_plugin_enqueue_scripts()
{
    // Enqueue jQuery (biasanya sudah ada di admin)
    wp_enqueue_script('jquery');

    // Enqueue custom script
    wp_enqueue_script(
        'markdown-parser-script',
        plugin_dir_url(__FILE__) . 'js/markdown-parser.js',
        ['jquery'],
        null,
        true
    );

    // Localize script untuk mengirimkan AJAX URL dan nonce
    wp_localize_script('markdown-parser-script', 'myPluginData', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('markdown_parser_nonce')
    ]);
}
add_action('admin_enqueue_scripts', 'my_plugin_enqueue_scripts');

function my_plugin_parse_markdown()
{
    check_ajax_referer('markdown_parser_nonce', 'security');

    // Cek apakah ada data Markdown yang dikirim
    if (isset($_POST['markdown'])) {
        require_once plugin_dir_path(__FILE__) . 'includes/Parsedown.php';

        $markdown_text = wp_unslash($_POST['markdown']);
        $Parsedown = new Parsedown();
        $html_text = $Parsedown->text($markdown_text);

        wp_send_json_success(['html' => $html_text]);
    } else {
        wp_send_json_error(['message' => 'No markdown text provided']);
    }
}
add_action('wp_ajax_parse_markdown', 'my_plugin_parse_markdown');
