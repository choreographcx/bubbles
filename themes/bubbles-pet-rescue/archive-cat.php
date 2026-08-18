<?php get_header(); ?>
<main id="bpr-main" class="bpr-section">
    <div class="container">
        <nav class="bpr-breadcrumb" aria-label="Breadcrumb">
            <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
            <span aria-hidden="true">&raquo;</span>
            <span>Cats</span>
        </nav>
        <div class="mb-5"><span class="bpr-pill mb-3">Meet the Cats</span><h1 class="display-5 bpr-heading">Adoptable Cats</h1><p class="bpr-lede">Every cat listed here is waiting for a calm foster home or a forever family.</p></div>
        <div class="row g-4">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?><div class="col-md-6 col-lg-4"><?php bpr_pet_card(); ?></div><?php endwhile; else : ?>
                <div class="col-12">
                    <div class="bpr-card p-5 text-center">
                        <p class="h5 bpr-section-title mb-2"><?php esc_html_e('No cats are listed right now.', 'bubbles-pet-rescue'); ?></p>
                        <p class="mb-4"><?php esc_html_e('New arrivals appear on Instagram first. Follow us to see them early, or tell us you are open to adopting and we will get in touch when a cat needs a home.', 'bubbles-pet-rescue'); ?></p>
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            <a class="btn btn-bpr-primary" href="<?php echo esc_url(get_theme_mod('bpr_instagram_url') ?: 'https://www.instagram.com/bubbles.petsrescue/'); ?>"><?php esc_html_e('Follow on Instagram', 'bubbles-pet-rescue'); ?></a>
                            <a class="btn btn-bpr-secondary" href="<?php echo esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Register Your Interest', 'bubbles-pet-rescue'); ?></a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="mt-5"><?php the_posts_pagination(); ?></div>
    </div>
</main>
<?php get_footer(); ?>
