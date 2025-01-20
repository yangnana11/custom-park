<?php
/**
 * Plugin Name: Park
 * Description: A simple plugin to register a custom post type, custom taxonomy, and shortcode to display and filter posts.
 * Version: 1.0
 * Author: Nana
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-park-admin.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-park-public.php';