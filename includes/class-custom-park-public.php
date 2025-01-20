<?php
if ( ! class_exists( 'Custom_Park_Public' ) ) {

    class Custom_Park_Public {

        public function __construct() {
            add_shortcode( 'park_list', array( $this, 'cpt_display_parks_shortcode' ) );
            add_filter('template_include', array($this, 'cpt_park_template_loader'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_park_shortcode_script'));
        }

        /**
         * Display the Park List for Shortcode
         * 
         * @param array $atts The attribute of Shortcode for filtering
         * 
         * @return string The HTML content of the list
         */
        public function cpt_display_parks_shortcode($atts) {
            $atts = shortcode_atts(array(
                'name' => '',
                'location' => '',
                'facility' => ''
            ), $atts, 'park_list');
        
            $query_args = array(
                'post_type' => 'park',
                'posts_per_page' => -1,
            );
        
            if (!empty($atts['name'])) {
                $query_args['s'] = sanitize_text_field($atts['name']);
            }
        
            if (!empty($atts['location'])) {
                $query_args['meta_query'][] = array(
                    'key' => '_park_location',
                    'value' => sanitize_text_field($atts['location']),
                    'compare' => 'LIKE',
                );
            }
        
            if (!empty($atts['facility'])) {
                $query_args['tax_query'][] = array(
                    'taxonomy' => 'facility',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field($atts['facility']),
                    'operator' => 'IN',
                );
            }
        
            $query = new WP_Query($query_args);
            $output = '<div class="park-list">';
        
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $location = get_post_meta(get_the_ID(), '_park_location', true);
                    $weekday_start = get_post_meta(get_the_ID(), '_park_weekday_start', true);
                    $weekday_end = get_post_meta(get_the_ID(), '_park_weekday_end', true);
                    $weekend_start = get_post_meta(get_the_ID(), '_park_weekend_start', true);
                    $weekend_end = get_post_meta(get_the_ID(), '_park_weekend_end', true);
                    $full_description = get_post_meta(get_the_ID(), '_park_short_description', true);
                    $short_description = wp_trim_words($full_description, 10, '...');
                    $facilities = get_the_terms(get_the_ID(), 'facility');
                    $facility_list = [];
                    if ($facilities && !is_wp_error($facilities)) {
                        foreach ($facilities as $facility) {
                            $facility_list[] = $facility->name;
                        }
                    }
                    $facility_names = implode(', ', $facility_list);
                    $output .= '<div class="park-item" style="border:1px solid #ccc;
                    padding:1rem 2rem;
                    border-radius:8px;
                    margin-bottom: 2rem;">';
                    $output .= '<h3 style="text-align: right;
                    text-transform: uppercase;">Name: ' . get_the_title() . '</h3>';
                    $output .= '<p>Location: ' . esc_html($location) . '</p>';
                    $output .= '<p>Weekday Hours: ' . esc_html($weekday_start) . ' - ' . esc_html($weekday_end) . '</p>';
                    $output .= '<p>Weekend Hours: ' . esc_html($weekend_start) . ' - ' . esc_html($weekend_end) . '</p>';
                    $output .= '<p>Facilities: ' . esc_html($facility_names) . '</p>';
                    if (strlen($short_description) < strlen($full_description)) {
                        $output .= '<p>Short Description: <span class="short-desc">' . esc_html($short_description) . '</span><span class="full-desc" style="display:none;">' . esc_html($full_description) . '</span> <a href="#" class="see-more" style="font-size:1rem;margin-left:8px">See More</a></p>';
                    } else {
                        $output .= '<p>Short Description: <span class="short-desc">' . esc_html($short_description) . '</span></p>';
                    }
                    $output .= '</div>';
                }
            } else {
                $output .= '<p>No parks found.</p>';
            }
        
            $output .= '</div>';
            wp_reset_postdata();
        
            return $output;
        }

        /**
         * Embedded the js code to show more / show less the Short Description
         */
        public function enqueue_park_shortcode_script() {
            if (has_shortcode(get_post()->post_content, 'park_list')) {
                wp_enqueue_script( 'custom-park-public', plugin_dir_url( __FILE__ ) . '../assets/js/park-shortcode.js', array(), false, true );
            }
        }

        /**
         * Load the template for a Part Post
         * 
         * @param string $template The default template content
         * 
         * @return string If it is a Park Post, will use the this plugin's template to display the data
         */
        public function cpt_park_template_loader($template) {
            if (is_singular('park')) {
                $plugin_template = plugin_dir_path(__FILE__) . '../templates/single-park.php';
                if (file_exists($plugin_template)) {
                    return $plugin_template;
                }
            }
            return $template;
        }
    }

    $custom_park_public = new Custom_Park_Public();
}

?>