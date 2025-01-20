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

function customize_park_columns($columns) {
    // Reset default columns
    $columns = array(
        'cb'               => '<input type="checkbox" />',
        'name'             => 'Name',
        'location'         => 'Location',
        'hours_weekday'    => 'Weekday Hours',
        'hours_weekend'    => 'Weekend Hours',
        'short_description' => 'Short Description',
        'facilities'       => 'Facilities',
        'date'             => 'Date',
    );
    return $columns;
}
add_filter('manage_park_posts_columns', 'customize_park_columns');

function display_park_custom_column_content($column, $post_id) {
    switch ($column) {
        case 'name':
            echo get_the_title($post_id);
            break;
        case 'location':
            echo esc_html(get_post_meta($post_id, '_park_location', true));
            break;
        case 'hours_weekday':
            echo esc_html(get_post_meta($post_id, '_park_hours_weekday', true));
            break;
        case 'hours_weekend':
            echo esc_html(get_post_meta($post_id, '_park_hours_weekend', true));
            break;
        case 'short_description':
            echo esc_html(wp_trim_words(get_post_meta($post_id, '_park_short_description', true), 10, '...'));
            break;
        case 'facilities':
            $terms = get_the_terms($post_id, 'facility');
            if ($terms && !is_wp_error($terms)) {
                $facilities_list = array();
                foreach ($terms as $term) {
                    $facilities_list[] = $term->name;
                }
                echo implode(', ', $facilities_list);
            } else {
                echo '—';
            }
            break;
        case 'date':
            echo get_the_date('', $post_id);
            break;
    }
}
add_action('manage_park_posts_custom_column', 'display_park_custom_column_content', 10, 2);

function park_sortable_columns($columns) {
    $columns['name'] = 'title';
    return $columns;
}
add_filter('manage_edit-park_sortable_columns', 'park_sortable_columns');


function cpt_register_park_post_type() {
    $labels = array(
        'name'               => 'Parks',
        'singular_name'      => 'Park',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Park',
        'edit_item'          => 'Edit Park',
        'new_item'           => 'New Park',
        'view_item'          => 'View Park',
        'search_items'       => 'Search Parks',
        'not_found'          => 'No parks found',
        'not_found_in_trash' => 'No parks found in Trash',
        'all_items'          => 'All Parks',
        'archives'           => 'Park Archives',
        'attributes'         => 'Park Attributes',
        'insert_into_item'   => 'Insert into park',
        'uploaded_to_this_item' => 'Uploaded to this park',
        'featured_image'     => 'Park Image',
        'set_featured_image' => 'Set park image',
        'remove_featured_image' => 'Remove park image',
        'use_featured_image' => 'Use as park image',
        'menu_name'          => 'Parks',
        'name_admin_bar'     => 'Park',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'supports'           => array("title"),
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
            // $name = get_post_meta(get_the_ID(), '_park_name', true);
            $location = get_post_meta(get_the_ID(), '_park_location', true);
            $hours_weekday = get_post_meta(get_the_ID(), '_park_hours_weekday', true);
            $hours_weekend = get_post_meta(get_the_ID(), '_park_hours_weekend', true);
            // $short_description = wp_trim_words(get_the_content(), 20, '...');
            $short_description = get_post_meta(get_the_ID(), '_park_short_description', true);
            $facilities = get_the_terms(get_the_ID(), 'facility');
            $facility_list = [];
            if ($facilities && !is_wp_error($facilities)) {
                foreach ($facilities as $facility) {
                    $facility_list[] = $facility->name;
                }
            }
            $facility_names = implode(', ', $facility_list);
            $output .= '<div class="park-item">';
            $output .= '<h3>' . get_the_title() . '</h3>';
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

function customize_title_label_for_parks() {
    $screen = get_current_screen();
    if ('park' === $screen->post_type) {
        ?>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                const titleLabel = document.querySelector('#title-prompt-text');
                if (titleLabel) {
                    titleLabel.setAttribute('aria-label', 'Add Name');
                    titleLabel.parentElement.querySelector('label').textContent = 'Add Name';
                }
            });
        </script>
        <?php
    }
}
add_action('admin_head', 'customize_title_label_for_parks');
