<?php
if ( ! class_exists( 'Custom_Park_Admin' ) ) {

    class Custom_Park_Admin {

        public function __construct() {
            add_filter('manage_park_posts_columns', array($this, 'customize_park_columns'));
            add_filter('manage_edit-park_sortable_columns', array($this, 'park_sortable_columns'));
            add_action('manage_park_posts_custom_column', array($this, 'display_park_custom_column_content'), 10, 2);
            add_action('pre_get_posts', array($this, 'park_location_name_sorting'));
            add_action('init', array($this, 'cpt_register_park_post_type'));
            add_action('init', array($this, 'cpt_register_facilities_taxonomy'));
            add_action('add_meta_boxes', array($this, 'cpt_add_park_custom_fields'));
            add_action('save_post', array($this, 'cpt_save_park_custom_fields'));
            add_action('admin_head', array($this, 'customize_title_label_for_parks'));
        }

        /**
         * Customize the Admin's Park List
         *
         * @param array The default columns of the list
         * 
         * @return array The columns with the correct content
         */
        public function customize_park_columns($columns) {
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

        /**
         * Display the content for Admin's Park List
         * 
         * @param array $columns The column list
         * @param string $post_id The Id of the Park Post
         */
        public function display_park_custom_column_content($column, $post_id) {
            switch ($column) {
                case 'name':
                    echo get_the_title($post_id);
                    break;
                case 'location':
                    echo esc_html(get_post_meta($post_id, '_park_location', true));
                    break;
                case 'hours_weekday':
                    $weekday_start = get_post_meta($post_id, '_park_weekday_start', true);
                    $weekday_end = get_post_meta($post_id, '_park_weekday_end', true);
                    if ($weekday_start && $weekday_end) {
                        echo esc_html($weekday_start) . ' - ' . esc_html($weekday_end);
                    } else {
                        echo '—';
                    }
                    break;
                case 'hours_weekend':
                    $weekend_start = get_post_meta($post_id, '_park_weekend_start', true);
                    $weekend_end = get_post_meta($post_id, '_park_weekend_end', true);
                    if ($weekend_start && $weekend_end) {
                        echo esc_html($weekend_start) . ' - ' . esc_html($weekend_end);
                    } else {
                        echo '—';
                    }
                    break;
                case 'short_description':
                    echo esc_html(wp_trim_words(get_post_meta($post_id, '_park_short_description', true), 20, '...'));
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

        /**
         * Define the sorting columns
         * 
         * @param array $columns The columns for sorting
         * 
         * @return array The sorting columns' string id
         */
        public function park_sortable_columns($columns) {
            $columns['name'] = 'title';
            $columns['location'] = 'location';
            return $columns;
        }

        /**
         * Sort Location and Name in Admin's Park List
         * 
         * @param object $query The query condition of sorting
         */
        public function park_location_name_sorting($query) {
            if (!is_admin()) {
                return;
            }
        
            if ($query->is_main_query() && $query->get('post_type') == 'park') {
                $orderby = $query->get('orderby');
        
                if ('title' == $orderby) {
                    $query->set('orderby', 'title');
                }
        
                if ('location' == $orderby) {
                    $query->set('meta_key', '_park_location');
                    $query->set('orderby', 'meta_value');
                }
            }
        }

        /**
         * Register Park Post
         */
        public function cpt_register_park_post_type() {
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

        /**
         * Register Facility Taxonomy
         */
        public function cpt_register_facilities_taxonomy() {
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

        /**
         * Add the Custom Fields (location, hours, short description) the the Park Post
         */
        public function cpt_add_park_custom_fields() {
            add_meta_box(
                'park_location',
                'Park Location',
                array($this, 'cpt_park_location_field'),
                'park',
                'normal',
                'high'
            );
            
            add_meta_box(
                'park_hours',
                'Park Hours',
                array($this, 'cpt_park_hours_field'),
                'park',
                'normal',
                'high'
            );
        
            add_meta_box(
                'park_short_description',
                'Short Description',
                array($this, 'cpt_park_short_description_field'),
                'park',
                'normal',
                'high'
            );
        }

        /**
         * Display Location Input in the Park Post
         * 
         * @param object $post The Post object
         */
        public function cpt_park_location_field($post) {
            $location = get_post_meta($post->ID, '_park_location', true);
            echo '<input type="text" name="park_location" value="' . esc_attr($location) . '" style="width:100%;" />';
        }
        
        /**
         * Display Park Hours in the Park Post. Having the valication for the time, as end time could not be earlier then start time.
         * 
         * @param object $post The Post object
         */
        public function cpt_park_hours_field($post) {
            $weekday_start = get_post_meta($post->ID, '_park_weekday_start', true);
            $weekday_end = get_post_meta($post->ID, '_park_weekday_end', true);
            $weekend_start = get_post_meta($post->ID, '_park_weekend_start', true);
            $weekend_end = get_post_meta($post->ID, '_park_weekend_end', true);
        
            echo '<label for="park_weekday_start" style="margin-bottom:8px;display:block;">Weekday Hours:</label>';
            echo '<div style="display:flex;flex-wrap:nowrap;align-items:center;">';
            echo '<input type="time" id="park_weekday_start" name="park_weekday_start" value="' . esc_attr($weekday_start) . '" placeholder="HH:mm" style="max-width:300px;margin-right:20px" />';
            echo ' - ';
            echo '<input type="time" id="park_weekday_end" name="park_weekday_end" value="' . esc_attr($weekday_end) . '" placeholder="HH:mm" style="max-width:300px;margin-left:20px" />';
            echo '</div>';
            echo '<div id="weekday_time_error" style="color: #b32d2e;margin-top:8px;display: none;">End time must be after start time.</div>';
            echo '<br><br>';
            echo '<label for="park_weekend_start" style="margin-bottom:8px;display:block;">Weekend Hours:</label>';
            echo '<div style="display:flex;flex-wrap:nowrap;align-item:center;">';
            echo '<input type="time" id="park_weekend_start" name="park_weekend_start" value="' . esc_attr($weekend_start) . '" placeholder="HH:mm" style="max-width:300px; margin-right:20px;" />';
            echo ' - ';
            echo '<input type="time" id="park_weekend_end" name="park_weekend_end" value="' . esc_attr($weekend_end) . '" placeholder="HH:mm" style="max-width:300px;margin-left:20px" />';
            echo '</div>';
            echo '<div id="weekend_time_error" style="color: #b32d2e; margin-top:8px; display: none;">End time must be after start time.</div>';
        
            ?>
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function () {
                    const weekdayStartInput = document.getElementById('park_weekday_start');
                    const weekdayEndInput = document.getElementById('park_weekday_end');
                    const weekendStartInput = document.getElementById('park_weekend_start');
                    const weekendEndInput = document.getElementById('park_weekend_end');
                    const weekdayError = document.getElementById('weekday_time_error');
                    const weekendError = document.getElementById('weekend_time_error');
                    
                    function validateTime(startTimeInput, endTimeInput, errorElement) {
                        const startTime = startTimeInput.value;
                        const endTime = endTimeInput.value;
        
                        if (startTime && endTime) {
                            if (endTime <= startTime) {
                                errorElement.style.display = 'block';
                                return false;
                            } else {
                                errorElement.style.display = 'none';
                                return true;
                            }
                        }
                        
                        errorElement.style.display = 'none';
                        return true;
                    }
        
                    weekdayStartInput.addEventListener('change', function () {
                        validateTime(weekdayStartInput, weekdayEndInput, weekdayError);
                    });
        
                    weekdayEndInput.addEventListener('change', function () {
                        validateTime(weekdayStartInput, weekdayEndInput, weekdayError);
                    });
        
                    weekendStartInput.addEventListener('change', function () {
                        validateTime(weekendStartInput, weekendEndInput, weekendError);
                    });
        
                    weekendEndInput.addEventListener('change', function () {
                        validateTime(weekendStartInput, weekendEndInput, weekendError);
                    });
                });
            </script>
            <?php
        }
        
        /**
         * Display Short Description Text Area in the Park Post
         * 
         * @param object $post The Post object
         */
        public function cpt_park_short_description_field($post) {
            $short_description = get_post_meta($post->ID, '_park_short_description', true);
            echo '<textarea name="park_short_description" rows="5" style="width:100%;">' . esc_textarea($short_description) . '</textarea>';
        }
        
        /**
         * Save the custom fields (location, hours, short description) of Park Post
         * 
         * @param string $post_id The Id of the Park Post
         */
        public function cpt_save_park_custom_fields($post_id) {
            if (array_key_exists('park_location', $_POST)) {
                update_post_meta($post_id, '_park_location', sanitize_text_field($_POST['park_location']));
            }
            if (array_key_exists('park_weekday_end', $_POST) && array_key_exists('park_weekday_start', $_POST)) {
                $weekday_start = $_POST['park_weekday_start'];
                $weekday_end = $_POST['park_weekday_end'];
                if ($weekday_start < $weekday_end) {
                    update_post_meta($post_id, '_park_weekday_end', sanitize_text_field($_POST['park_weekday_end']));
                    update_post_meta($post_id, '_park_weekday_start', sanitize_text_field($_POST['park_weekday_start']));
                }
            }
            if (array_key_exists('park_weekend_end', $_POST) && array_key_exists('park_weekend_start', $_POST)) {
                $weekend_start = $_POST['park_weekend_start'];
                $weekend_end = $_POST['park_weekend_end'];
                if ($weekend_start < $weekend_end) {
                    update_post_meta($post_id, '_park_weekend_end', sanitize_text_field($_POST['park_weekend_end']));
                    update_post_meta($post_id, '_park_weekend_start', sanitize_text_field($_POST['park_weekend_start']));
                }
            }
            if (array_key_exists('park_short_description', $_POST)) {
                update_post_meta($post_id, '_park_short_description', sanitize_textarea_field($_POST['park_short_description']));
            }
        }

        /**
         * Change the label Title of a Park Post to Name
         */
        public function customize_title_label_for_parks() {
            $screen = get_current_screen();
            if ('park' === $screen->post_type) {
                wp_enqueue_script( 'custom-park-admin', plugin_dir_url( __FILE__ ) . '../assets/js/custom-park-post.js', array(), false, true );
            }
        }
    }

    $custom_park_admin = new Custom_Park_Admin();
}

?>