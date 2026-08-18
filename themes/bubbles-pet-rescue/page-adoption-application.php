<?php
/* Template Name: Adoption Application */
get_header(); ?>
<main class="bpr-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <?php while (have_posts()) : the_post(); ?>
                    <article <?php post_class('bpr-card p-4 p-lg-5'); ?>>
                        <h1 class="bpr-section-title mb-4"><?php the_title(); ?></h1>
                        <div class="entry-content">
                            <?php
                            $content = get_the_content();
                            if (trim($content) !== '') {
                                the_content();
                            } else {
                                echo '<p class="bpr-lede mb-4">Tell the rescue team about your home, lifestyle, and the pet you would like to adopt.</p>';
                                echo do_shortcode('[bubbles_adoption_application]');
                            }
                            ?>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</main>
<?php get_footer(); ?>
