<?php
if (!defined('ABSPATH')) {
    exit;
}
get_header(); ?>

<div class="park-content">
    <?php
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            echo '<h1>' . get_the_title() . '</h1>';
            echo '<p><strong>Location:</strong> ' . esc_html(get_post_meta(get_the_ID(), '_park_location', true)) . '</p>';
            echo '<p><strong>Weekday Hours:</strong> ' . esc_html(get_post_meta(get_the_ID(), '_park_weekday_start', true)) . ' - ' . esc_html(get_post_meta(get_the_ID(), '_park_weekday_end', true)) . '</p>';
            echo '<p><strong>Weekend Hours:</strong> ' . esc_html(get_post_meta(get_the_ID(), '_park_weekend_start', true)) . ' - ' . esc_html(get_post_meta(get_the_ID(), '_park_weekend_end', true)) . '</p>';
            echo '<p><strong>Short Description:</strong> ' . esc_html(get_post_meta(get_the_ID(), '_park_short_description', true)) . '</p>';
        }
    }
    ?>
</div>

<?php get_footer(); ?>
