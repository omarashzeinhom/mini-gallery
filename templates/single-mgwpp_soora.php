<?php
get_header();

the_post();

echo '<div class="mgwpp-gallery-template">';
echo '<h1>' . esc_attr(get_the_title()) . '</h1>';
echo esc_html(apply_filters('the_content', get_the_content()), 'mini-gallery');
echo '</div>';

get_footer();
