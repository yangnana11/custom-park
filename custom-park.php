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

function cpt_register_park_post_type() {
    $labels = array(
        'name'               => 'Parks',
        'singular_name'      => 'Park',
        'menu_name'          => 'Parks',
        'name_admin_bar'     => 'Park',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'supports'           => array(""),
        'menu_icon'          => 'dashicons-location-alt',
    );

    register_post_type('park', $args);
}
add_action('init', 'cpt_register_park_post_type');

function cpt_register_facilities_taxonomy() {
    $labels = array(
        'name'              => 'Facilities',
        'singular_name'     => 'Facility',
        'search_items'      => 'Search Facilities',
        'all_items'         => 'All Facilities',
        'parent_item'       => 'Parent Facility',
        'parent_item_colon' => 'Parent Facility:',
        'edit_item'         => 'Edit Facility',
        'update_item'       => 'Update Facility',
        'add_new_item'      => 'Add New Facility',
        'new_item_name'     => 'New Facility Name',
        'menu_name'         => 'Facilities',
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'facility'),
    );

    register_taxonomy('facility', 'park', $args);
}
add_action('init', 'cpt_register_facilities_taxonomy');

function cpt_display_parks_shortcode() {
    $query_args = array(
        'post_type' => 'park',
        'posts_per_page' => -1,
    );

    $query = new WP_Query($query_args);
    $output = '<div class="park-list">';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $name = get_post_meta(get_the_ID(), '_park_name', true);
            $location = get_post_meta(get_the_ID(), '_park_location', true);
            $hours_weekday = get_post_meta(get_the_ID(), '_park_hours_weekday', true);
            $hours_weekend = get_post_meta(get_the_ID(), '_park_hours_weekend', true);
            // $short_description = wp_trim_words(get_the_content(), 20, '...');
            $short_description = get_post_meta(get_the_ID(), '_park_short_description', true);
            $output .= '<div class="park-item">';
            $output .= '<h3>' . esc_html($name) . '</h3>';
            $output .= '<p><strong>Location:</strong> ' . esc_html($location) . '</p>';
            $output .= '<p><strong>Weekday Hours:</strong> ' . esc_html($hours_weekday) . '</p>';
            $output .= '<p><strong>Weekend Hours:</strong> ' . esc_html($hours_weekend) . '</p>';
            $output .= '<p><strong>Facilities:</strong> ' . esc_html($facility_names) . '</p>';
            $output .= '<p><strong>Short Description:</strong> ' . esc_html($short_description) . '</p>';
            $output .= '</div>';
        }
    } else {
        $output .= '<p>No parks found.</p>';
    }

    $output .= '</div>';
    wp_reset_postdata();

    return $output;
}
add_shortcode('park_list', 'cpt_display_parks_shortcode');

function cpt_add_park_custom_fields() {
    add_meta_box(
        'park_name',
        'Park Name',
        'cpt_park_name_field',
        'park',
        'normal',
        'high'
    );

    add_meta_box(
        'park_location',
        'Park Location',
        'cpt_park_location_field',
        'park',
        'normal',
        'high'
    );
    
    add_meta_box(
        'park_hours',
        'Park Hours',
        'cpt_park_hours_field',
        'park',
        'normal',
        'high'
    );

    add_meta_box(
        'park_short_description',
        'Short Description',
        'cpt_park_short_description_field',
        'park',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cpt_add_park_custom_fields');

function cpt_park_name_field($post) {
    $name = get_post_meta($post->ID, '_park_name', true);
    echo '<input type="text" name="park_name" value="' . esc_attr($name) . '" style="width:100%;" />';
}

function cpt_park_location_field($post) {
    $location = get_post_meta($post->ID, '_park_location', true);
    echo '<input type="text" name="park_location" value="' . esc_attr($location) . '" style="width:100%;" />';
}

function cpt_park_hours_field($post) {
    $weekday_hours = get_post_meta($post->ID, '_park_hours_weekday', true);
    $weekend_hours = get_post_meta($post->ID, '_park_hours_weekend', true);
    echo '<label for="park_hours_weekday">Weekday Hours:</label>';
    echo '<input type="text" name="park_hours_weekday" value="' . esc_attr($weekday_hours) . '" style="width:100%;" />';
    echo '<br><br>';
    echo '<label for="park_hours_weekend">Weekend Hours:</label>';
    echo '<input type="text" name="park_hours_weekend" value="' . esc_attr($weekend_hours) . '" style="width:100%;" />';
}

function cpt_park_short_description_field($post) {
    $short_description = get_post_meta($post->ID, '_park_short_description', true);
    echo '<textarea name="park_short_description" rows="5" style="width:100%;">' . esc_textarea($short_description) . '</textarea>';
}

function cpt_save_park_custom_fields($post_id) {
    if (array_key_exists('park_name', $_POST)) {
        update_post_meta($post_id, '_park_name', sanitize_text_field($_POST['park_name']));
    }
    if (array_key_exists('park_location', $_POST)) {
        update_post_meta($post_id, '_park_location', sanitize_text_field($_POST['park_location']));
    }
    if (array_key_exists('park_hours_weekday', $_POST)) {
        update_post_meta($post_id, '_park_hours_weekday', sanitize_text_field($_POST['park_hours_weekday']));
    }
    if (array_key_exists('park_hours_weekend', $_POST)) {
        update_post_meta($post_id, '_park_hours_weekend', sanitize_text_field($_POST['park_hours_weekend']));
    }
    if (array_key_exists('park_short_description', $_POST)) {
        update_post_meta($post_id, '_park_short_description', sanitize_textarea_field($_POST['park_short_description']));
    }
}
add_action('save_post', 'cpt_save_park_custom_fields');
