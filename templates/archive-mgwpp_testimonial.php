<?php get_header(); ?>

<div class="mgwpp-testimonials-archive">
    <header class="page-header">
        <h1 class="page-title"><?php post_type_archive_title(); ?></h1>
    </header>

    <div class="testimonials-grid">
        <?php while (have_posts()) :
            the_post(); ?>
            <article class="testimonial-card">
                <a href="<?php the_permalink(); ?>">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="testimonial-thumbnail">
                            <?php the_post_thumbnail('medium'); ?>
                        </div>
                    <?php endif; ?>

                    <div class="testimonial-excerpt">
                        <?php
                        $excerpt = get_the_excerpt();
                        echo wp_trim_words($excerpt, 20);
                        ?>
                    </div>

                    <footer class="testimonial-footer">
                        <h3><?php the_title(); ?></h3>
                        <?php
                        $position = get_post_meta(get_the_ID(), '_mgwpp_position', true);
                        if ($position) :
                            ?>
                            <div class="testimonial-position"><?php echo esc_html($position); ?></div>
                        <?php endif; ?>
                    </footer>
                </a>
            </article>
        <?php endwhile; ?>
    </div>
</div>

<?php get_footer(); ?>
