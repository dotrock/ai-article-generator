<?php
/*
Plugin Name: AI Article Generator
Plugin URI: https://github.com/mroyyan/ai-article-generator
Description: Plugin untuk membuat artikel otomatis menggunakan OpenAI dan Gemini API.
Version: 1.0.0
Author: Muhammad Royyan
Author URI: https://royyan.net
*/

// Include the main file for the plugin functionality
require_once(plugin_dir_path(__FILE__) . 'ai-generator.php');

// Aktivasi plugin untuk menambahkan default options
function royyanweb_ai_article_generator_activate()
{
    add_option('royyanweb_ai_article_api_key', '');
    add_option('royyanweb_ai_article_api_provider', 'openai');
}
register_activation_hook(__FILE__, 'royyanweb_ai_article_generator_activate');
