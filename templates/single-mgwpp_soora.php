<?php
get_header();

the_post();

echo '<div class="mgwpp-gallery-template">';
echo '<h1>' . get_the_title() . '</h1>';
echo apply_filters('the_content', get_the_content());
echo '</div>';

get_footer();
