<?php get_header(); ?>
<main class="bpr-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8 mx-auto">
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <article <?php post_class('bpr-card p-4 p-lg-5 mb-4'); ?>>
                        <h1 class="bpr-section-title"><a class="text-decoration-none" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                        <div class="entry-content"><?php the_excerpt(); ?></div>
                    </article>
                <?php endwhile; the_posts_pagination(); else : ?>
                    <div class="bpr-card p-5"><h1 class="bpr-section-title">Nothing Found</h1><p>No content has been published yet.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php get_footer(); ?>
